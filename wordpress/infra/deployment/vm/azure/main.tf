terraform {
  required_providers {
    azurerm = {
      source  = "hashicorp/azurerm"
      version = "4.81.0"
    }
    tls = {
      source  = "hashicorp/tls"
      version = "4.4.2"
    }
    local = {
      source  = "hashicorp/local"
      version = "2.9.1"
    }
    azuread = {
      source  = "hashicorp/azuread"
      version = "3.10.0"
    }
    azapi = {
      source  = "azure/azapi"
      version = "2.13.0"
    }
  }
  required_version = ">= 1.8"
}

locals {
  name_prefix = "${var.project}-${var.environment}"
  tags = {
    project     = var.project
    environment = var.environment
    managed_by  = "opentofu"
    source      = "Smart-Logic-Systems-LLC/website:wordpress/infra"
  }
}

# PROVIDERS
# All authenticate with the Azure CLI session (`az login`); no client secret needed.
provider "azurerm" {
  features {}
  subscription_id = var.azure_subscription_id
}

provider "azuread" {}

provider "azapi" {
  subscription_id = var.azure_subscription_id
}

# MISC RESOURCES
resource "azurerm_resource_group" "vm_k8s_quadlet" {
  name     = "${local.name_prefix}-vm_k8s_quadlet"
  location = var.location
  tags     = local.tags
}

resource "tls_private_key" "generated_ssh_key" {
  algorithm = "RSA"
  rsa_bits  = 4096
}

resource "local_file" "ssh_private_key" {
  content         = tls_private_key.generated_ssh_key.private_key_pem
  filename        = "${path.module}/${var.environment}.pem"
  file_permission = "0400"
}

# Ansible inventory for this environment (used by ../../../Makefile).
resource "local_file" "inventory" {
  filename        = "${path.module}/${var.environment}.inventory.ini"
  file_permission = "0600"
  content         = <<-EOT
    [slswebsite]
    ${azurerm_linux_virtual_machine.main.name} ansible_host=${azurerm_public_ip.ipv4.ip_address} ansible_user=${var.vm_username} ansible_ssh_private_key_file=${abspath(local_file.ssh_private_key.filename)}
  EOT
}
