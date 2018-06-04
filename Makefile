.DEFAULT_GOAL := check
.PHONY: lint phpcpd phpstan phpcs phpcbf tests tester-coverage echo-failed-tests validate-composer.lock move-schemas generate-schemas composer-install

PHPCS_ARGS := --standard=ruleset.xml --extensions=php,phpt --encoding=utf-8 --tab-width=4 -sp App Tests Commands www

check: validate-composer.lock lint phpcpd phpstan phpcs generate-schemas tests
ci: validate-composer.lock lint phpcpd phpstan phpcs tests tester-coverage
init: lint generate-schemas move-schemas

lint:
	vendor/bin/parallel-lint -e php,phpt App Tests Commands www

phpcpd:
	vendor/bin/phpcpd App --exclude V1/ --exclude Sql/ --exclude Task/

phpstan:
	vendor/bin/phpstan analyse -l max -c phpstan.neon App Tests/Misc Tests/TestCase Commands

phpcs:
	vendor/bin/phpcs $(PHPCS_ARGS)

phpcbf:
	vendor/bin/phpcbf $(PHPCS_ARGS)

tests:
	vendor/bin/tester -o console -s -p php -c Tests/php.ini Tests/

tester-coverage:
	vendor/bin/tester -o console -s -p php -d extension=xdebug.so -c Tests/php.ini Tests/ --coverage tester-coverage.xml --coverage-src App/

echo-failed-tests:
	@for i in $(find Tests -name \*.actual); do echo "--- $i"; cat $i; echo; echo; done
	@for i in $(find Tests -name \*.expected); do echo "--- $i"; cat $i; echo; echo; done

validate-composer.lock:
	composer validate --no-check-all --strict

generate-schemas:
	php Commands/schema.php

composer-install:
	composer install --no-interaction --prefer-dist --no-scripts --no-progress --no-suggest --optimize-autoloader --classmap-authoritative

move-schemas:
	mkdir -p www/schema/v1/demand
	mkdir -p www/schema/v1/demand/soulmate_request
	mkdir -p www/schema/v1/demand/soulmate
	mkdir -p www/schema/v1/evolution
	mkdir -p www/schema/v1/description
	mkdir -p www/schema/v1/soulmate
	mkdir -p www/schema/v1/seeker
	mkdir -p www/schema/v1/token

	ln -sfn $(PWD)/App/V1/Demand/schema/get.json www/schema/v1/demand/get.json
	ln -sfn $(PWD)/App/V1/Demand/schema/put.json www/schema/v1/demand/put.json
	ln -sfn $(PWD)/App/V1/Demand/schema/patch.json www/schema/v1/demand/patch.json
	ln -sfn $(PWD)/App/V1/Demand/Soulmates/schema/get www/schema/v1/demand/soulmate/get.json
	ln -sfn $(PWD)/App/V1/Demand/SoulmateRequests/schema/get.json www/schema/v1/demand/soulmate_request/get.json
	ln -sfn $(PWD)/App/V1/Demands/schema/post.json www/schema/v1/demand/post.json
	ln -sfn $(PWD)/App/V1/Evolutions/schema/post.json www/schema/v1/evolution/post.json
	ln -sfn $(PWD)/App/V1/Evolution/schema/put.json www/schema/v1/evolution/put.json
	ln -sfn $(PWD)/App/V1/Evolution/schema/get.json www/schema/v1/evolution/get.json
	ln -sfn $(PWD)/App/V1/Description/schema/get.json www/schema/v1/description/get.json
	ln -sfn $(PWD)/App/V1/Description/schema/put.json www/schema/v1/description/put.json
	ln -sfn $(PWD)/App/V1/Descriptions/schema/post.json www/schema/v1/description/post.json
	ln -sfn $(PWD)/App/V1/Soulmate/schema/patch.json www/schema/v1/soulmate/patch.json
	ln -sfn $(PWD)/App/V1/Seeker/schema/get.json www/schema/v1/seeker/get.json
	ln -sfn $(PWD)/App/V1/Seekers/schema/post.json www/schema/v1/seeker/post.json
	ln -sfn $(PWD)/App/V1/Token/schema/get.json www/schema/v1/token/get.json
	ln -sfn $(PWD)/App/V1/Tokens/schema/post.json www/schema/v1/token/post.json

