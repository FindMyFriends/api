CREATE FUNCTION unit_tests.throwing_on_changing_created_at() RETURNS test_result
AS $$
DECLARE
  messages text[];
BEGIN
  INSERT INTO demands (seeker_id, description_id, created_at, location_id) VALUES (
    (SELECT samples.seeker()),
    (SELECT samples.description()),
    NOW(),
    (SELECT samples.location())
  );

  messages = messages || message FROM assert.throws(
    'UPDATE demands SET created_at = NOW()',
    ROW('Column created_at is read only', 'P0001')::error
  );
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;