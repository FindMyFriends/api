CREATE FUNCTION tests.throwing_on_changing_created_at() RETURNS void
AS $$
BEGIN
  INSERT INTO demands (seeker_id, description_id, created_at) VALUES (
    (SELECT samples.seeker()),
    (SELECT samples.description()),
    NOW()
  );

  PERFORM assert.throws(
    'UPDATE demands SET created_at = NOW()',
    ROW('Column created_at is read only', 'P0001')::error
  );
END
$$
LANGUAGE plpgsql;