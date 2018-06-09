.DEFAULT_GOAL := check
.PHONY: lint phpcpd phpstan phpcs phpcbf tests tester-coverage echo-failed-tests validate-composer.lock move-schemas generate-schemas composer-install

PHPCS_ARGS := --standard=ruleset.xml --extensions=php,phpt --encoding=utf-8 --tab-width=4 -sp App Tests Commands www
TESTER_ARGS := -o console -s -p php -c Tests/php.ini

check: validate-composer.lock lint phpcpd phpstan phpcs generate-schemas tests
ci: validate-composer.lock lint phpcpd phpstan phpcs tests tester-coverage
init: lint generate-schemas move-schemas

lint:
	vendor/bin/parallel-lint -e php,phpt App Tests Commands www

phpcpd:
	vendor/bin/phpcpd App --exclude Endpoint/ --exclude Sql/ --exclude Task/

phpstan:
	vendor/bin/phpstan analyse -l max -c phpstan.neon App Tests/Misc Tests/TestCase Commands

phpcs:
	vendor/bin/phpcs $(PHPCS_ARGS)

phpcbf:
	vendor/bin/phpcbf $(PHPCS_ARGS)

tests:
	vendor/bin/tester $(TESTER_ARGS) Tests/

tester-coverage:
	vendor/bin/tester $(TESTER_ARGS) -d extension=xdebug.so Tests/ --coverage tester-coverage.xml --coverage-src App/

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
	mkdir -p www/schema/demand
	mkdir -p www/schema/demand/soulmate_request
	mkdir -p www/schema/demand/soulmate
	mkdir -p www/schema/evolution
	mkdir -p www/schema/description
	mkdir -p www/schema/soulmate
	mkdir -p www/schema/seeker
	mkdir -p www/schema/token

	ln -sfn $(PWD)/App/Endpoint/Demand/schema/get.json www/schema/demand/get.json
	ln -sfn $(PWD)/App/Endpoint/Demand/schema/put.json www/schema/demand/put.json
	ln -sfn $(PWD)/App/Endpoint/Demand/schema/patch.json www/schema/demand/patch.json
	ln -sfn $(PWD)/App/Endpoint/Demand/Soulmates/schema/get www/schema/demand/soulmate/get.json
	ln -sfn $(PWD)/App/Endpoint/Demand/SoulmateRequests/schema/get.json www/schema/demand/soulmate_request/get.json
	ln -sfn $(PWD)/App/Endpoint/Demands/schema/post.json www/schema/demand/post.json
	ln -sfn $(PWD)/App/Endpoint/Evolutions/schema/post.json www/schema/evolution/post.json
	ln -sfn $(PWD)/App/Endpoint/Evolution/schema/put.json www/schema/evolution/put.json
	ln -sfn $(PWD)/App/Endpoint/Evolution/schema/get.json www/schema/evolution/get.json
	ln -sfn $(PWD)/App/Endpoint/Description/schema/get.json www/schema/description/get.json
	ln -sfn $(PWD)/App/Endpoint/Description/schema/put.json www/schema/description/put.json
	ln -sfn $(PWD)/App/Endpoint/Descriptions/schema/post.json www/schema/description/post.json
	ln -sfn $(PWD)/App/Endpoint/Soulmate/schema/patch.json www/schema/soulmate/patch.json
	ln -sfn $(PWD)/App/Endpoint/Seeker/schema/get.json www/schema/seeker/get.json
	ln -sfn $(PWD)/App/Endpoint/Seekers/schema/post.json www/schema/seeker/post.json
	ln -sfn $(PWD)/App/Endpoint/Token/schema/get.json www/schema/token/get.json
	ln -sfn $(PWD)/App/Endpoint/Tokens/schema/post.json www/schema/token/post.json

