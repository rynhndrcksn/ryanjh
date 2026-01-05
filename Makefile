## help: print this help message
.PHONY: help
help:
	@echo 'Usage:'
	@sed -n 's/^##//p' ${MAKEFILE_LIST} | column -t -s ':' |  sed -e 's/^/ /'

## start: start the docker container(s) and Symfony web server
.PHONY: start
start:
	echo 'Running "docker compose up -d" and "symfony server:start -d"'
	@docker compose up -d
	@symfony server:start -d

## stop: stop the docker container(s) and Symfony web server
.PHONY: stop
stop:
	echo 'Running "docker compose down" and "symfony server:stop"'
	@docker compose down
	@symfony server:stop
