# website

## Make

Run any folder's Makefile targets from here by prefixing them with its path:

```sh
make                           # list every target
make wordpress/infra/https     # runs `make https` in wordpress/infra
make wordpress/infra/deploy APPROVE=1
```
