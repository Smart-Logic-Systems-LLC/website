variable "environment" {
  type        = string
  default     = "dev"
  description = "dev, staging, or prod"
}

variable "azure_subscription_id" {
  type        = string
  description = "Azure subscription id (the Makefile reads it from `az account show`)"
  sensitive   = true
}

variable "project" {
  type        = string
  description = "Resource group short name"
}

variable "location" {
  type        = string
  description = "Data center location"
}

variable "vm_username" {
  type        = string
  description = "VM username"
}

variable "vm_size" {
  type        = string
  description = "VM size SKU"
}

variable "vm_image_offer" {
  type        = string
  description = "Canonical image offer, e.g. ubuntu-26_04-lts (sku minimal-arm64)"
}

variable "vm_image_version" {
  type        = string
  description = "Image version (`az vm image show -l <location> --urn Canonical:<offer>:minimal-arm64:latest`)"
}

variable "ssh_allowed_cidrs" {
  type        = list(string)
  description = "Source addresses allowed to reach SSH (the Makefile passes the deployer's public IP)"
}

variable "dns_zone" {
  type        = string
  description = "Subdomain delegated to Azure DNS (dns.tf); the site is served at its apex"
}

variable "email_domain" {
  type        = string
  description = "Domain WordPress sends mail from through Azure Communication Services"
}

variable "email_sender_username" {
  type        = string
  default     = "wordpress"
  description = "Local part of the sender address (WordPress sends as wordpress@<site domain>)"
}

variable "email_sender_display_name" {
  type        = string
  description = "Sender display name registered with Azure Communication Services"
}

variable "email_domain_verified" {
  type        = bool
  default     = false
  description = "Set true once `make email-verify` passes; links the domain and enables the relay"
}
