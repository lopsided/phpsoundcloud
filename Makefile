.PHONY: check test

vendor:
	composer install

check:
	vendor/bin/phpcs -v --standard=PSR2 source/ tests/
	vendor/bin/phpmd source/ xml codesize,controversial,design,naming,unusedcode

test:
	vendor/bin/phpunit --strict --testdox
