output "resource_group" {
  value = azurerm_resource_group.vm_k8s_quadlet.name
}

output "public_ip" {
  value = azurerm_public_ip.ipv4.ip_address
}

output "hostname" {
  value = azurerm_linux_virtual_machine.main.name
}

output "ssh_private_key_file" {
  value = local_file.ssh_private_key.filename
}

output "ssh_user" {
  value = var.vm_username
}

output "inventory_file" {
  value = local_file.inventory.filename
}
