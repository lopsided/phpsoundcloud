.PHONY: all check clean test

all: vendor check test

check:
	vendor/bin/phpcs -v --standard=PSR2 source/
	vendor/bin/phpmd source/ xml codesize,controversial,design,naming,unusedcode

clean:
	rm -rf vendor

test:
	vendor/bin/phpunit --strict --testdox

vendor:
	composer install
