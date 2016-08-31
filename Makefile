.PHONY: cs test

all: cs test

cs: ## Lints your Code
	vendor/bin/php-cs-fixer fix --config=.php_cs --verbose --diff

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

test: ## Runs Unit, Integration and Functional Tests
	vendor/bin/phpunit --configuration test/Unit/phpunit.xml