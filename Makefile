# Run any sub-project's targets from the repo root, addressed by path:
#   make <dir>/<target>   runs <target> from <dir>/Makefile, e.g. `make wordpress/infra/https`
#   make <dir>/           runs that Makefile's default goal (its target list)
# Variables pass through: `make wordpress/infra/deploy ENV=dev APPROVE=1`.
# `make` lists every target this reaches.
SHELL := /bin/bash
.SHELLFLAGS := -eu -o pipefail -c
.DEFAULT_GOAL := help

# Sub-project Makefiles, skipping anything gitignored (.venv, .terraform, ...).
MAKEFILES := $(filter-out Makefile,$(shell git ls-files --cached --others --exclude-standard -- Makefile '*/Makefile'))

# .PHONY can't hold the forwarded goals (make skips pattern rules for phony
# targets), so they depend on FORCE: they run even when a same-named path exists.
.PHONY: help FORCE
FORCE: ;

help:
	@for f in $(MAKEFILES); do \
		awk -v dir="$${f%Makefile}" 'BEGIN {FS = ":.*## "} /^[a-z-]+:.*## / {printf "  make %-32s %s\n", dir $$1, $$2}' "$$f"; \
	done

# Keep make from trying to rebuild this file through the catch-all rule below.
Makefile: ;

# Like the Drupal Makefile's `cd $(dirname $@)` SHELLFLAGS trick, but the
# target runs from the sub-project's own Makefile instead of a copy of its recipe here.
%: FORCE
	@if [[ '$(@D)' == . ]]; then \
		echo "make: '$@' needs a directory prefix, e.g. 'make wordpress/infra/$@'. Run 'make' to list targets." >&2; exit 2; \
	elif [[ ! -f '$(@D)/Makefile' ]]; then \
		echo "make: no Makefile in '$(@D)/'. Run 'make' to list targets." >&2; exit 2; \
	fi
	@$(MAKE) --no-print-directory -C '$(@D)' $(@F)
