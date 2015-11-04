default: run-unit-tests

.PHONY: \
	default \
	run-unit-tests

vendor:
	composer install

run-unit-tests: vendor
	vendor/bin/phpunit --bootstrap vendor/autoload.php test
