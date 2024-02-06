# ----- Colors -----
GREEN = /bin/echo -e "\x1b[32m\#\# $1\x1b[0m"
RED = /bin/echo -e "\x1b[31m\#\# $1\x1b[0m"

# ----- Programs -----
COMPOSER = composer
PHP = php
SYMFONY = symfony
SYMFONY_CONSOLE = $(PHP) bin/console
PHP_UNIT = $(PHP) bin/phpunit

## ----- Composer -----
composer-install: ## Install the dependencies
	@$(call GREEN, "Installing dependencies...")
	$(COMPOSER) install

composer-update: ## Update the dependencies
	@$(call GREEN, "Updating dependencies...")
	$(COMPOSER) update

database-migrate: ## Migrate the database
	@$(call GREEN, "Migrating database...")
	$(SYMFONY_CONSOLE) doctrine:migrations:migrate --no-interaction

## ----- Help -----
help: ## Display this help
	@$(call GREEN, "Available commands:")
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "3[32m%-30s3[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'