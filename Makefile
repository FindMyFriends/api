.DEFAULT_GOAL := check
.PHONY: lint phpcpd phpstan phpcs phpcbf tests tester-coverage echo-failed-tests validate-composer.lock move-schemas generate-schemas composer-install, count-postgres-tests generate-routes check-test-extensions generate-nginx-conf check-changed-conf

PHPCS_ARGS := --standard=ruleset.xml --extensions=php,phpt --encoding=utf-8 --tab-width=4 -sp App Tests www
TESTER_ARGS := -o console -s -p php -c Tests/php.ini
CHECK_TEST_EXTENSIONS := find Tests/Unit/ Tests/Integration/ Tests/Functional/ Tests/Elastic/ -name '*.php' | grep -v '\Test.php$$'

check: validate-composer.lock check-changed-conf check-test-extensions lint phpcpd phpstan phpcs tests count-postgres-tests
ci: validate-composer.lock check-changed-conf check-test-extensions lint phpcpd phpstan phpcs tests count-postgres-tests tester-coverage
init: lint generate-schemas move-schemas

help:               ## help
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

lint:               ## lint
	vendor/bin/parallel-lint -e php,phpt App Tests www

phpcpd:             ## phpcpd
	vendor/bin/phpcpd App --exclude Endpoint/ --exclude Sql/ --exclude Task/

phpstan:            ## phpstan
	vendor/bin/phpstan analyse -l max -c phpstan.neon App Tests/Misc Tests/TestCase

phpcs:              ## phpcs
	vendor/bin/phpcs $(PHPCS_ARGS)

phpcbf:             ## phpcbf
	vendor/bin/phpcbf $(PHPCS_ARGS)

check-test-extensions:
	@echo "Checking PHP test extensions..."
	@if $(CHECK_TEST_EXTENSIONS) ; then exit 1 ; else echo "Test filenames are OK" ; fi

tests:              ## tests
	vendor/bin/tester $(TESTER_ARGS) Tests/

count-postgres-tests:
	@printf "Number of PostgreSQL tests: "
	@cat Tests/Postgres/*.sql | grep -c "CREATE FUNCTION tests."
	@printf "Number of PostgreSQL assertions: "
	@cat Tests/Postgres/*.sql | grep -c "PERFORM assert."

tester-coverage:
	vendor/bin/tester $(TESTER_ARGS) -d extension=xdebug.so Tests/ --coverage tester-coverage.xml --coverage-src App/

echo-failed-tests:
	@for i in $(find Tests -name \*.actual); do echo "--- $i"; cat $i; echo; echo; done
	@for i in $(find Tests -name \*.expected); do echo "--- $i"; cat $i; echo; echo; done

validate-composer.lock:
	composer validate --no-check-all --strict

generate-schemas:   ## generate JSON schemas
	php App/Scheduling/index.php GenerateJsonSchema

generate-routes:    ## generate nginx routes
	php App/Scheduling/index.php GenerateNginxRoutes

generate-nginx-conf:## generate nginx configs
	php App/Scheduling/index.php GenerateNginxConfiguration

check-changed-conf: ## check changed configs
	php App/Scheduling/index.php CheckChangedConfiguration

cron:               ## run cron tasks
	php App/Scheduling/index.php Cron

composer-install:
	composer install --no-interaction --prefer-dist --no-scripts --no-progress --no-suggest --classmap-authoritative

move-schemas:       ## move JSON schemas
	mkdir -p www/schema/activation
	mkdir -p www/schema/demand
	mkdir -p www/schema/demand/soulmate
	mkdir -p www/schema/demand/soulmate_request
	mkdir -p www/schema/demand/spot
	mkdir -p www/schema/description
	mkdir -p www/schema/evolution
	mkdir -p www/schema/evolution/spot
	mkdir -p www/schema/refresh_token
	mkdir -p www/schema/seeker
	mkdir -p www/schema/soulmate
	mkdir -p www/schema/spot
	mkdir -p www/schema/token

	ln -sfn $(PWD)/App/Endpoint/Activations/schema/post.json www/schema/activation/post.json
	ln -sfn $(PWD)/App/Endpoint/Demand/SoulmateRequests/schema/get.json www/schema/demand/soulmate_request/get.json
	ln -sfn $(PWD)/App/Endpoint/Demand/Soulmates/schema/get www/schema/demand/soulmate/get.json
	ln -sfn $(PWD)/App/Endpoint/Demand/Spot/schema/get.json www/schema/demand/spot/get.json
	ln -sfn $(PWD)/App/Endpoint/Demand/Spot/schema/post.json www/schema/demand/spot/post.json
	ln -sfn $(PWD)/App/Endpoint/Demand/schema/get.json www/schema/demand/get.json
	ln -sfn $(PWD)/App/Endpoint/Demand/schema/patch.json www/schema/demand/patch.json
	ln -sfn $(PWD)/App/Endpoint/Demand/schema/put.json www/schema/demand/put.json
	ln -sfn $(PWD)/App/Endpoint/Demands/schema/post.json www/schema/demand/post.json
	ln -sfn $(PWD)/App/Endpoint/Description/schema/get.json www/schema/description/get.json
	ln -sfn $(PWD)/App/Endpoint/Description/schema/put.json www/schema/description/put.json
	ln -sfn $(PWD)/App/Endpoint/Descriptions/schema/post.json www/schema/description/post.json
	ln -sfn $(PWD)/App/Endpoint/Evolution/Spot/schema/get.json www/schema/evolution/spot/get.json
	ln -sfn $(PWD)/App/Endpoint/Evolution/Spot/schema/post.json www/schema/evolution/spot/post.json
	ln -sfn $(PWD)/App/Endpoint/Evolution/schema/get.json www/schema/evolution/get.json
	ln -sfn $(PWD)/App/Endpoint/Evolution/schema/put.json www/schema/evolution/put.json
	ln -sfn $(PWD)/App/Endpoint/Evolutions/schema/post.json www/schema/evolution/post.json
	ln -sfn $(PWD)/App/Endpoint/RefreshTokens/schema/post.json www/schema/refresh_token/post.json
	ln -sfn $(PWD)/App/Endpoint/Seeker/schema/get.json www/schema/seeker/get.json
	ln -sfn $(PWD)/App/Endpoint/Seekers/schema/post.json www/schema/seeker/post.json
	ln -sfn $(PWD)/App/Endpoint/Soulmate/schema/patch.json www/schema/soulmate/patch.json
	ln -sfn $(PWD)/App/Endpoint/Spot/schema/patch.json www/schema/spot/patch.json
	ln -sfn $(PWD)/App/Endpoint/Spot/schema/put.json www/schema/spot/put.json
	ln -sfn $(PWD)/App/Endpoint/Tokens/schema/post.json www/schema/token/post.json

