# EMAIL
# WordPress -> Postfix relay container (in the pod) -> Azure Communication
# Services Email at smtp.azurecomm.net:587. Azure blocks outbound port 25 from
# VMs, so the VM cannot deliver mail itself.
#
# The sending domain must be verified before it can be linked:
#   1. apply (dns.tf publishes Azure's verification records in the DNS zone)
#   2. once the zone is delegated (`make dns`), `make email-verify`
#   3. set email_domain_verified = true in <env>.tfvars and run `make deploy`

resource "azurerm_email_communication_service" "main" {
  name                = "${local.name_prefix}-email"
  resource_group_name = azurerm_resource_group.vm_k8s_quadlet.name
  data_location       = "United States"
  tags                = local.tags
}

resource "azurerm_email_communication_service_domain" "main" {
  name              = var.email_domain
  email_service_id  = azurerm_email_communication_service.main.id
  domain_management = "CustomerManaged"
  tags              = local.tags
}

resource "azurerm_communication_service" "main" {
  name                = "${local.name_prefix}-acs"
  resource_group_name = azurerm_resource_group.vm_k8s_quadlet.name
  data_location       = "United States"
  tags                = local.tags
}

resource "azurerm_communication_service_email_domain_association" "main" {
  count                    = var.email_domain_verified ? 1 : 0
  communication_service_id = azurerm_communication_service.main.id
  email_service_domain_id  = azurerm_email_communication_service_domain.main.id
}

# MAIL FROM address: <email_sender_username>@<email_domain>, WordPress's default sender.
resource "azurerm_email_communication_service_domain_sender_username" "main" {
  count                   = var.email_domain_verified ? 1 : 0
  name                    = var.email_sender_username
  email_service_domain_id = azurerm_email_communication_service_domain.main.id
  display_name            = var.email_sender_display_name
}

# SMTP credentials: an Entra application with send rights on the ACS resource,
# linked to an SMTP username. The password is the application's client secret.
data "azuread_client_config" "current" {}

resource "azuread_application" "smtp" {
  display_name = "${local.name_prefix}-smtp-relay"
  owners       = [data.azuread_client_config.current.object_id]
}

resource "azuread_service_principal" "smtp" {
  client_id = azuread_application.smtp.client_id
  owners    = [data.azuread_client_config.current.object_id]
}

# Expires after two years (the provider default); `tofu apply -replace` rotates it.
resource "azuread_application_password" "smtp" {
  application_id = azuread_application.smtp.id
  display_name   = "postfix relay"
}

# Least privilege for SMTP sending, per
# https://learn.microsoft.com/azure/communication-services/quickstarts/email/send-email-smtp/smtp-authentication
resource "azurerm_role_definition" "smtp_sender" {
  name              = "${local.name_prefix} SMTP sender"
  scope             = azurerm_resource_group.vm_k8s_quadlet.id
  assignable_scopes = [azurerm_resource_group.vm_k8s_quadlet.id]
  permissions {
    actions = [
      "Microsoft.Communication/CommunicationServices/Read",
      "Microsoft.Communication/CommunicationServices/Write",
      "Microsoft.Communication/EmailServices/write",
    ]
  }
}

resource "azurerm_role_assignment" "smtp_sender" {
  scope              = azurerm_communication_service.main.id
  role_definition_id = azurerm_role_definition.smtp_sender.role_definition_resource_id
  principal_id       = azuread_service_principal.smtp.object_id
  principal_type     = "ServicePrincipal"
}

# No azurerm resource exists for SMTP usernames yet.
resource "azapi_resource" "smtp_username" {
  type      = "Microsoft.Communication/communicationServices/smtpUsernames@2025-09-01"
  name      = "relay"
  parent_id = azurerm_communication_service.main.id
  body = {
    properties = {
      username           = "${local.name_prefix}-relay"
      entraApplicationId = azuread_application.smtp.client_id
      tenantId           = data.azuread_client_config.current.tenant_id
    }
  }
}

# Read by the deploy playbook to add the relay container; written only once the
# domain is linked, so the relay never sends from an unverified domain.
resource "local_sensitive_file" "smtp" {
  count           = var.email_domain_verified ? 1 : 0
  filename        = "${path.module}/../../../secrets/${var.environment}/smtp.yml"
  file_permission = "0600"
  content = yamlencode({
    smtp_relay_host     = "smtp.azurecomm.net"
    smtp_relay_port     = 587
    smtp_relay_username = azapi_resource.smtp_username.body.properties.username
    smtp_relay_password = azuread_application_password.smtp.value
    smtp_sender         = "${var.email_sender_username}@${var.email_domain}"
  })
}

output "email_domain_id" {
  value = azurerm_email_communication_service_domain.main.id
}
