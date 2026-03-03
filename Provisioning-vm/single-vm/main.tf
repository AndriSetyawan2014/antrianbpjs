terraform {
  required_providers {
    proxmox = {
      source = "Telmate/proxmox"
      version = "3.0.1-rc4"
    }
  }
}

provider "proxmox" {
  pm_api_url           = var.proxmox_api_url
  pm_api_token_id      = var.proxmox_api_token_id
  pm_api_token_secret  = var.proxmox_api_token_secret
  pm_tls_insecure      = true
}

resource "proxmox_vm_qemu" "vm" {
    vmid        = 900
    name        = "srv-widad"
    target_node = "luna"

    clone       = "server-template"
    full_clone  = true

    os_type     = "cloud-init"
    
    ciuser      = var.ci_user
    cipassword  = var.ci_password
    sshkeys     = file(var.ci_ssh_public_key)

    cores       = 1
    memory      = 2048
    agent       = 1
    agent_timeout = 120  # Menambahkan timeout untuk agent

    bootdisk    = "scsi0"
    scsihw      = "virtio-scsi-pci"
    ipconfig0   = "ip=dhcp"

    disk {
        size    = "10G"
        type    = "disk"  
        storage = "local-lvm"
        slot    = "scsi0"
    }

    network {
        model   = "virtio"
        bridge  = "vmbr0"
    }

    lifecycle {
        ignore_changes = [
            network
        ]
    }
}
