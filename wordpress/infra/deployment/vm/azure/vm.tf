resource "azurerm_linux_virtual_machine" "main" {
  name                            = "${local.name_prefix}-main"
  resource_group_name             = azurerm_resource_group.vm_k8s_quadlet.name
  location                        = azurerm_resource_group.vm_k8s_quadlet.location
  admin_username                  = var.vm_username
  size                            = var.vm_size
  disable_password_authentication = true
  tags                            = local.tags
  network_interface_ids = [
    azurerm_network_interface.nic1_public.id,
  ]

  source_image_reference {
    publisher = "Canonical"
    offer     = var.vm_image_offer
    sku       = "minimal-arm64"
    version   = var.vm_image_version
  }

  admin_ssh_key {
    username   = var.vm_username
    public_key = tls_private_key.generated_ssh_key.public_key_openssh
  }

  os_disk {
    storage_account_type = "Standard_LRS"
    caching              = "ReadWrite"
  }
}
