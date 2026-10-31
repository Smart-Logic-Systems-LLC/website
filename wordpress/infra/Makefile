# Deploy the WordPress site to an Azure VM, run as a Podman quadlet.
# `make` lists the targets. ENV=<env> selects deployment/vm/azure/<env>.tfvars
# and env/<env>.yml (default: dev).
# Requires: az (signed in to the target subscription), tofu, uv, rsync, openssl.
SHELL := /bin/bash
.SHELLFLAGS := -eu -o pipefail -c
.DEFAULT_GOAL := help

ENV ?= dev
APPROVE ?=
RESEED ?=
RESET_PASSWORD ?=
TO ?=

TF_DIR := deployment/vm/azure
TF := tofu -chdir=$(TF_DIR)
TF_STATE := -state=$(ENV).tfstate
TF_OUT := $(TF) output $(TF_STATE) -raw
# Values that must not be committed (public repo): the subscription id and the
# deployer's address for the SSH rule.
TF_VARS = -var-file=$(ENV).tfvars \
	-var "azure_subscription_id=$$(az account show --query id -o tsv)" \
	-var 'ssh_allowed_cidrs=["'"$$(curl -fsS https://api.ipify.org)"'/32"]'
TF_APPROVE = $(if $(APPROVE),-auto-approve)
PLAYBOOK := uv run ansible-playbook -i $(TF_DIR)/$(ENV).inventory.ini -e environment_level=$(ENV)
SSH = ssh -i $(TF_DIR)/$(ENV).pem -o StrictHostKeyChecking=accept-new -o UserKnownHostsFile=.known_hosts \
	"$$($(TF_OUT) ssh_user)@$$($(TF_OUT) public_ip)"
DOMAIN = $(shell sed -n 's/^domain_name: //p' env/$(ENV).yml)

.PHONY: help install check infra-plan infra-deploy app-deploy deploy https login \
	email-dns email-verify mail-test status logs ssh restart app-destroy infra-destroy

help:
	@awk 'BEGIN {FS = ":.*## "} /^[a-z-]+:.*## / {printf "  make %-14s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install: ## Install Ansible (uv) and OpenTofu providers
	@uv sync --quiet
	@$(TF) init -upgrade -input=false >/dev/null

check: install ## tofu fmt/validate + ansible-lint
	$(TF) fmt -check -recursive
	$(TF) validate
	uv run ansible-lint playbooks roles

infra-plan: install ## Preview Azure changes
	$(TF) plan $(TF_STATE) $(TF_VARS)

infra-deploy: install ## Create/update Azure resources (APPROVE=1 skips the prompt)
	$(TF) apply $(TF_STATE) $(TF_VARS) $(TF_APPROVE)

app-deploy: install ## Deploy the site to the VM (RESEED=1 resets page content)
	$(PLAYBOOK) playbooks/deploy_k8s_quadlet.yml \
		-e "admin_email=$$(az account show --query user.name -o tsv)" \
		-e "slswebsite_k8s__reseed=$(if $(RESEED),true,false)"
	@echo "Site: https://$(DOMAIN)  (public IP $$($(TF_OUT) public_ip))"

deploy: infra-deploy app-deploy ## infra-deploy + app-deploy

https: install ## After DNS points at the VM: get the certificate and verify HTTPS
	$(PLAYBOOK) playbooks/enable_https.yml

login: install ## Verify the wp-admin login and print it (RESET_PASSWORD=1 resets it)
	$(PLAYBOOK) playbooks/admin_login.yml -e "login__reset_password=$(if $(RESET_PASSWORD),true,false)"

email-dns: ## DNS records to add for the mail sending domain
	@$(TF) output $(TF_STATE) email_dns_records

email-verify: install ## After adding those records: have Azure verify the domain
	$(PLAYBOOK) playbooks/email_verify.yml -e "email_domain_id=$$($(TF_OUT) email_domain_id)"

mail-test: install ## Send a test email (TO=address; default: your Azure login)
	$(PLAYBOOK) playbooks/mail_test.yml -e "mail__to=$(if $(TO),$(TO),$$(az account show --query user.name -o tsv))"

status: ## Service, containers and memory on the VM
	@$(SSH) 'systemctl --no-pager status slswebsite | head -n 5; sudo podman ps --format "table {{.Names}}\t{{.Status}}"; free -m'

logs: ## Recent journal of the slswebsite service
	@$(SSH) 'sudo journalctl -u slswebsite --no-pager -n 100'

ssh: ## Shell on the VM (CMD='...' runs a command)
	@$(SSH) $(if $(CMD),'$(CMD)')

restart: ## Restart the site's pod
	@$(SSH) 'sudo systemctl restart slswebsite'

app-destroy: install ## Remove the site, database and uploads from the VM
	$(PLAYBOOK) playbooks/destroy_k8s_quadlet.yml

infra-destroy: install ## Delete the resource group and everything in it
	$(TF) destroy $(TF_STATE) $(TF_VARS) $(TF_APPROVE)
	rm -f .known_hosts
