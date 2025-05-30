SHELL := bash
.DEFAULT_GOAL := build

app = docker compose run --rm php

# Define behavior to safely source file (1) to dist file (2), without overwriting
# if the dist file already exists. This is more portable than using `cp --no-clobber`.
define copy-safe
	if [ ! -f "$(2)" ]; then \
		echo "Copying $(1) to $(2)"; \
		cp "$(1)" "$(2)"; \
	else \
		echo "$(2) already exists, not overwriting."; \
	fi
endef

# Define behavior to check if a token (1) is set in .env, and prompt user to set it if not.
# If the token is already set, inform the user. If the token name is not found in .env,
# it will be appended, otherwise, the existing value will be updated.
define check-token
	if grep -q "^$(1)=" ".env"; then \
		TOKEN_VALUE=$$(grep "^$(1)=" ".env" | cut -d'=' -f2); \
		if [ -z "$$TOKEN_VALUE" ]; then \
			read -p "Please enter your $(1): " NEW_TOKEN; \
			sed -i "s/^$(1)=.*/$(1)=$$NEW_TOKEN/" ".env"; \
			echo "$(1) updated successfully!"; \
		else \
			echo "$(1) is already set."; \
		fi; \
	else \
		read -p "$(1) not found. Please enter your $(1): " NEW_TOKEN; \
		echo -e "\n$(1)=$$NEW_TOKEN" >> ".env"; \
		echo "$(1) added successfully!"; \
	fi
endef

define generate-key
	if grep -q "^$(1)=" ".env"; then \
		KEY_VALUE=$$(grep "^$(1)=" ".env" | cut -d'=' -f2); \
		if [ -z "$$KEY_VALUE" ]; then \
			NEW_KEY=$$(docker run --rm php:8.4-fpm php -r 'echo "base64:" . \base64_encode(\random_bytes(32));'); \
			sed -i "s;^$(1)=.*;$(1)=$$NEW_KEY;" ".env"; \
			echo "New $(1) generated successfully!"; \
		else \
			echo "$(1) is already set."; \
		fi; \
	else \
		NEW_KEY=$$(docker run --rm php:8.4-fpm php -r 'echo "base64:" . \base64_encode(\random_bytes(32));'); \
		echo -e "\$(1)=$$NEW_KEY" >> ".env"; \
		echo "New $(1) generated successfully!"; \
	fi
endef

phpunit.xml:
	@$(call copy-safe,phpunit.dist.xml,phpunit.xml)

phpstan.neon:
	@$(call copy-safe,phpstan.dist.neon,phpstan.neon)

.env:
	@$(call copy-safe,.env.dist,.env)
	@$(call generate-key,SALT_APP_KEY)
	@$(call check-token,GITHUB_TOKEN)

vendor: | .env
	@docker compose pull
	@docker compose build --pull
	@$(app) composer install

# The build target dependencies must be set as "order-only" prerequisites to prevent
# the target from being rebuilt everytime the dependencies are updated.
build: vendor | phpstan.neon phpunit.xml .env
	@$(app) mkdir --parents build
	@touch build

.PHONY: clean
clean:
	$(app) rm -rf ./build ./vendor
	$(app) find /app/ -type f -not -name .gitignore -delete

# Rebuild the Docker images and reinstall dependencies. Note that this target only
# works _after_ the initial build target has been run at least once, so it does not
# actually duplicate the "vendor" target.
.PHONY: install
install: build
	@docker compose pull
	@docker compose build --pull
	@$(app) composer install
	$(app) find /app/storage/bootstrap -type f -not -name .gitignore -delete
	$(app) find /app/storage/cache -type f -not -name .gitignore -delete
	$(app) find /app/storage/doctrine -type f -not -name .gitignore -delete

.PHONY: up
up:
	docker compose up --detach

.PHONY: down
down:
	docker compose down --remove-orphans

.PHONY: bash
bash: build
	@$(app) bash

.PHONY: lint
lint: build
	@$(app) composer run-script lint

# Run tests, aliased to "phpunit" for consistency with other tooling targets.
.PHONY: test phpunit
phpunit: test
test: build
	@$(app) composer run-script test

# Generate HTML PHPUnit test coverage report, aliased to "phpunit-coverage" for consistency with other tooling targets.
.PHONY: test-coverage phpunit-coverage
phpunit-coverage: test-coverage
test-coverage: build
	@$(app) composer run-script test-coverage

.PHONY: serve-coverage
serve-coverage:
	@docker compose run --rm --publish 8000:80 php php -S 0.0.0.0:80 -t /app/build/phpunit

.PHONY: phpcs
phpcs: build
	@$(app) composer run-script phpcs

.PHONY: phpcbf
phpcbf: build
	@$(app) composer run-script phpcbf

.PHONY: phpstan
phpstan: build
	@$(app) composer run-script phpstan

.PHONY: rector
rector: build
	@$(app) composer run-script rector

.PHONY: rector-dry-run
rector-dry-run: build
	@$(app) composer run-script rector-dry-run

# Runs all the code quality checks: lint, phpstan, phpcs, and rector-dry-run".
.PHONY: ci
ci: build
	@$(app) composer run-script ci

# Runs the automated fixer tools, then run the code quality checks in one go, aliased to "preci".
.PHONY: pre-ci preci
preci: pre-ci
pre-ci: build phpcbf rector ci

# Run the PsySH REPL shell
.PHONY: shell
shell: build up
	@$(app) ./bin/salt shell
