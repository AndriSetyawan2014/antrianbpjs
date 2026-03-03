variable "proxmox_api_url" {
  type        = string
  description = "variable dari url api proxmox"
}

variable "proxmox_api_token_id" {
  type        = string
  description = "variable dari token ID proxmox"
}

variable "proxmox_api_token_secret" {
  type        = string
  sensitive   = true
  description = "variable dari secret token ID proxmox"
}

variable "ci_user" {
  type        = string
  description = "variable dari ci_user cloud init"
}


variable "ci_password" {
  type        = string
  sensitive   = true
  description = "variable dari ci_password cloud init"
}


variable "ci_ssh_privat_key" {
  type        = string
  description = "variable dari ci_ssh_privat_key cloud init"
}

variable "ci_ssh_public_key" {
  type        = string
  description = "variable dari ci_ssh_public_key cloud init"
}

variable "vm_count" {
  type = number
  default = 3
}