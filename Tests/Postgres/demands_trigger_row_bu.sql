CREATE OR REPLACE FUNCTION unit_tests.throwing_on_changing_created_at() RETURNS TEST_RESULT AS $$
BEGIN
	INSERT INTO demands (seeker_id, description_id, created_at, location_id) VALUES (
		(SELECT seeker FROM samples.seeker()),
		(SELECT description FROM samples.description()),
		NOW() - INTERVAL '10 MINUTE',
		(SELECT location FROM samples.location())
	);

	RETURN message FROM assert.throws(
		'UPDATE demands SET created_at = NOW()',
		ROW('Column created_at is read only', 'P0001')::error
	);
END
$$
LANGUAGE plpgsql;