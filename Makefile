## help: print this help message
.PHONY: help
help:
	@echo 'Usage:'
	@sed -n 's/^##//p' ${MAKEFILE_LIST} | column -t -s ':' |  sed -e 's/^/ /'

## update-zola: re-installs/updates Zola via Cargo
.PHONY: update-zola
update-zola:
    @cargo install --locked --git https://github.com/getzola/zola

## deploy-stg: builds the project and deploys it to the staging site
.PHONY: deploy-stg
deploy-stg:
	@zola build --base-url https://ryanjh.home.arpa && \
	rsync -a --info=progress2 --no-inc-recursive --human-readable --delete public/ lab-srv11:/var/www/ryanjh/

## deploy-prd: builds the project and deploys it to the production site
.PHONY: deploy-prd
deploy-prd:
	@zola build && \
	rsync -a --info=progress2 --no-inc-recursive --human-readable --delete public/ hetz-ryanjh:/var/www/ryanjh/

