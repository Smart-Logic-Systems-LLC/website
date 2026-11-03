# Non-secret settings only: this repository is public. The subscription id and
# the SSH source address are passed by the Makefile.
environment = "dev"
project     = "slswebsite"
# Central US (prod's region) has no Bpsv2 vCPU quota left; North Central US is the nearest with quota.
# https://learn.microsoft.com/en-us/azure/reliability/regions-list?tabs=americas
location    = "North Central US"
vm_username = "slsadmin"
# Same cheap ARM SKU as prod: 2 vCPU, 1 GiB.
# https://learn.microsoft.com/en-us/azure/virtual-machines/sizes/general-purpose/bpsv2-series?tabs=sizebasic
vm_size = "Standard_B2pts_v2"
# Ubuntu 26.04 LTS (prod's 25.10 reached end of life in July 2026).
vm_image_offer   = "ubuntu-26_04-lts"
vm_image_version = "26.04.202610280"

# Delegated from GoDaddy (dns.tf); `make dns` lists the NS records to add there.
dns_zone = "dev.smartlogicsystems.com"

# Outbound mail (email.tf). Flip email_domain_verified once `make email-verify` passes.
email_domain              = "dev.smartlogicsystems.com"
email_sender_display_name = "Smart Logic Systems (dev)"
email_domain_verified     = true
