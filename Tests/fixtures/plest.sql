CREATE SCHEMA IF NOT EXISTS tests;
CREATE SCHEMA IF NOT EXISTS assert;

CREATE FUNCTION assert.same(expected anyelement, actual anyelement) RETURNS void
AS $$
BEGIN
  IF (actual IS DISTINCT FROM expected) THEN
    RAISE EXCEPTION USING
      MESSAGE = format(
        'Expected %s, actual %s',
        COALESCE(expected::text, 'NULL'),
        COALESCE(actual::text, 'NULL')
      );
  END IF;
END
$$
LANGUAGE plpgsql
VOLATILE;

CREATE FUNCTION assert.not_same(expected anyelement, actual anyelement) RETURNS void
AS $$
BEGIN
  IF (actual IS NOT DISTINCT FROM expected) THEN
    RAISE EXCEPTION USING
      MESSAGE = format(
        'Expected %s, actual %s',
        COALESCE(expected::text, 'NULL'),
        COALESCE(actual::text, 'NULL')
      );
  END IF;
END
$$
LANGUAGE plpgsql
VOLATILE;

CREATE FUNCTION assert.true(actual anyelement) RETURNS void
AS $$
BEGIN
  PERFORM assert.same(TRUE, actual);
END
$$
LANGUAGE plpgsql
VOLATILE;

CREATE FUNCTION assert.false(actual anyelement) RETURNS void
AS $$
BEGIN
  PERFORM assert.same(FALSE, actual);
END
$$
LANGUAGE plpgsql
VOLATILE;

CREATE FUNCTION assert.null(actual anyelement) RETURNS void
AS $$
BEGIN
  PERFORM assert.same(NULL, actual);
END
$$
LANGUAGE plpgsql
VOLATILE;

CREATE FUNCTION assert.not_null(actual anyelement) RETURNS void
AS $$
BEGIN
  PERFORM assert.not_same(NULL, actual);
END
$$
LANGUAGE plpgsql
VOLATILE;

CREATE TYPE error AS (message text, state text);
CREATE FUNCTION assert.throws(query text, expected error) RETURNS void
AS $BODY$
BEGIN
    EXECUTE query;
    RAISE EXCEPTION USING
      ERRCODE = 'XX000',
      MESSAGE = 'EXCEPTION WAS NOT THROWN',
      HINT = format('EXPECTED EXCEPTION WAS "%s"', expected);
    EXCEPTION WHEN OTHERS THEN
    IF (expected.message IS NOT NULL AND expected.message != SQLERRM) THEN
        RAISE EXCEPTION USING MESSAGE = format('EXPECTED EXCEPTION WITH MESSAGE "%s", BUT GIVEN "%s"', expected.message, SQLERRM);
    END IF;
    IF (expected.state IS NOT NULL AND expected.state != SQLSTATE) THEN
        RAISE EXCEPTION USING MESSAGE = format('EXPECTED EXCEPTION WITH SQLSTATE "%s", BUT GIVEN "%s"', expected.state, SQLSTATE);
    END IF;
END
$BODY$
LANGUAGE plpgsql
VOLATILE;