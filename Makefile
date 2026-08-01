.PHONY: analyse audit check coverage dist fix install lint test validate

install:
	composer install --no-interaction --prefer-dist

validate:
	composer validate --strict --no-check-lock

lint:
	composer lint

fix:
	composer fix

analyse:
	composer analyse

test:
	composer test

coverage:
	composer coverage

audit:
	composer audit

check: validate lint analyse test audit

dist:
	mkdir -p artifacts
	composer archive --format=zip --dir=artifacts
