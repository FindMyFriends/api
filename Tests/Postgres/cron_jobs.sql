CREATE FUNCTION tests.throwing_on_running_previous() RETURNS void
AS $BODY$
BEGIN
  INSERT INTO log.cron_jobs (name, status) VALUES ('basic', 'processing');
  PERFORM assert.throws(
    $$INSERT INTO log.cron_jobs (name, status) VALUES ('basic', 'processing')$$,
    ROW('Job "basic" can not be run, because previous is not fulfilled.', 'P0001')::error
  );
END
$BODY$
LANGUAGE plpgsql;

CREATE FUNCTION tests.passing_on_running_each() RETURNS void
AS $BODY$
BEGIN
  INSERT INTO log.cron_jobs (name, status) VALUES ('basic', 'processing');
  INSERT INTO log.cron_jobs (name, status) VALUES ('basic', 'succeed');

  INSERT INTO log.cron_jobs (name, status) VALUES ('basic', 'processing');
  INSERT INTO log.cron_jobs (name, status) VALUES ('basic', 'failed');
END
$BODY$
LANGUAGE plpgsql;
