resource "azurerm_virtual_network" "main" {
  name                = "${local.name_prefix}-main"
  address_space       = ["10.0.0.0/16"]
  location            = azurerm_resource_group.vm_k8s_quadlet.location
  resource_group_name = azurerm_resource_group.vm_k8s_quadlet.name
  tags                = local.tags
}

resource "azurerm_subnet" "main" {
  name                 = "${local.name_prefix}-main"
  resource_group_name  = azurerm_resource_group.vm_k8s_quadlet.name
  virtual_network_name = azurerm_virtual_network.main.name
  address_prefixes     = ["10.0.2.0/24"]
}

resource "azurerm_public_ip" "ipv4" {
  name                = "${local.name_prefix}-ipv4"
  resource_group_name = azurerm_resource_group.vm_k8s_quadlet.name
  location            = azurerm_resource_group.vm_k8s_quadlet.location
  allocation_method   = "Static"
  tags                = local.tags
}

resource "azurerm_network_interface" "nic1_public" {
  name                = "${local.name_prefix}-nic1_public"
  resource_group_name = azurerm_resource_group.vm_k8s_quadlet.name
  location            = azurerm_resource_group.vm_k8s_quadlet.location
  tags                = local.tags

  ip_configuration {
    name                          = "public"
    subnet_id                     = azurerm_subnet.main.id
    private_ip_address_allocation = "Dynamic"
    public_ip_address_id          = azurerm_public_ip.ipv4.id
  }
}

resource "azurerm_network_security_group" "webserver" {
  name                = "${local.name_prefix}-webserver"
  location            = azurerm_resource_group.vm_k8s_quadlet.location
  resource_group_name = azurerm_resource_group.vm_k8s_quadlet.name
  tags                = local.tags
  security_rule {
    access                     = "Allow"
    direction                  = "Inbound"
    name                       = "https"
    priority                   = 100
    protocol                   = "Tcp"
    source_port_range          = "*"
    source_address_prefix      = "*"
    destination_port_range     = "443"
    destination_address_prefix = azurerm_network_interface.nic1_public.private_ip_address
  }
  security_rule {
    access                     = "Allow"
    direction                  = "Inbound"
    name                       = "http"
    priority                   = 101
    protocol                   = "Tcp"
    source_port_range          = "*"
    source_address_prefix      = "*"
    destination_port_range     = "80"
    destination_address_prefix = azurerm_network_interface.nic1_public.private_ip_address
  }
  # SSH only from the deployer's address (prod allows any source).
  security_rule {
    access                     = "Allow"
    direction                  = "Inbound"
    name                       = "ssh"
    priority                   = 102
    protocol                   = "Tcp"
    source_port_range          = "*"
    source_address_prefixes    = var.ssh_allowed_cidrs
    destination_port_range     = "22"
    destination_address_prefix = azurerm_network_interface.nic1_public.private_ip_address
  }
}

resource "azurerm_network_interface_security_group_association" "main" {
  network_interface_id      = azurerm_network_interface.nic1_public.id
  network_security_group_id = azurerm_network_security_group.webserver.id
}
