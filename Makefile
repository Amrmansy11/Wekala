DOCKER_COMPOSE := docker compose
STAGING_COMPOSE_FILE := ./infra/deployment/staging/docker-compose.yml

up-local:
	$(DOCKER_COMPOSE) up -d

up-local-build:
	$(DOCKER_COMPOSE) up -d --build

up-stg:
	$(DOCKER_COMPOSE) -f $(STAGING_COMPOSE_FILE) up -d

up-stg-build:
	$(DOCKER_COMPOSE) -f $(STAGING_COMPOSE_FILE) up -d --build
