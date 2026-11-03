# DNS
# The site's subdomain (dns_zone) is delegated from GoDaddy, which keeps the
# root domain, to this Azure DNS zone: GoDaddy holds only an NS record per
# `name_servers` entry under that subdomain (`make dns` lists them and checks
# the delegation). Everything below it is managed here.

resource "azurerm_dns_zone" "main" {
  name                = var.dns_zone
  resource_group_name = azurerm_resource_group.vm_k8s_quadlet.name
  tags                = local.tags
}

resource "azurerm_dns_a_record" "site" {
  name                = "@"
  zone_name           = azurerm_dns_zone.main.name
  resource_group_name = azurerm_resource_group.vm_k8s_quadlet.name
  ttl                 = 3600
  records             = [azurerm_public_ip.ipv4.ip_address]
  tags                = local.tags
}

# Records Azure Communication Services checks before the sending domain can be
# used (email.tf). Azure gives names either as FQDNs or relative to the email domain.
locals {
  email_subdomain = trimsuffix(trimsuffix(var.email_domain, var.dns_zone), ".")
  email_records = {
    for kind in ["domain", "spf", "dkim", "dkim2"] : kind => {
      value = local.email_azure_records[kind].value
      name = (
        local.email_azure_records[kind].name == var.dns_zone ? "@" :
        endswith(local.email_azure_records[kind].name, ".${var.dns_zone}") ? trimsuffix(local.email_azure_records[kind].name, ".${var.dns_zone}") :
        join(".", compact([local.email_azure_records[kind].name, local.email_subdomain]))
      )
    }
  }
  email_azure_records = {
    for kind, records in azurerm_email_communication_service_domain.main.verification_records[0] : kind => {
      name  = trimsuffix(records[0].name, ".")
      value = records[0].value
    } if length(records) > 0
  }
}

# Ownership and SPF are both TXT records on the email domain, so they share a record set.
resource "azurerm_dns_txt_record" "email" {
  name                = local.email_records.domain.name
  zone_name           = azurerm_dns_zone.main.name
  resource_group_name = azurerm_resource_group.vm_k8s_quadlet.name
  ttl                 = 3600
  tags                = local.tags
  record {
    value = local.email_records.domain.value
  }
  record {
    value = local.email_records.spf.value
  }
  lifecycle {
    precondition {
      condition     = local.email_records.domain.name == local.email_records.spf.name
      error_message = "Azure returned different names for the domain and SPF records; give each its own record set."
    }
  }
}

resource "azurerm_dns_cname_record" "email" {
  for_each            = toset(["dkim", "dkim2"])
  name                = local.email_records[each.key].name
  zone_name           = azurerm_dns_zone.main.name
  resource_group_name = azurerm_resource_group.vm_k8s_quadlet.name
  ttl                 = 3600
  record              = local.email_records[each.key].value
  tags                = local.tags
}

output "name_servers" {
  description = "NS records for the dns_zone subdomain at the parent domain's DNS provider"
  value       = azurerm_dns_zone.main.name_servers
}
