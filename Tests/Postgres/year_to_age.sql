CREATE FUNCTION unit_tests.timestamp_to_age_range() RETURNS test_result
AS $$
DECLARE
  messages text[];
BEGIN
  messages = messages || message FROM assert.is_equal(
    year_to_age(int4range(1993, 2000), tstzrange('2015-01-01', NOW())),
    int4range(15, 22)
  );
  messages = messages || message FROM assert.is_equal(
    year_to_age(int4range(1993, 2000), '2015-01-01':: TIMESTAMP ),
    int4range(15, 22)
  );
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;