CREATE FUNCTION unit_tests.throwing_on_changing_created_at() RETURNS TEST_RESULT AS $$
DECLARE
	v_freezed_now TIMESTAMPTZ DEFAULT NOW() - INTERVAL '10 MINUTE';
	messages TEXT[];
BEGIN
	INSERT INTO demands (seeker_id, description_id, created_at, location_id) VALUES (
		(SELECT samples.seeker()),
		(SELECT samples.description()),
		NOW() - INTERVAL '10 MINUTE',
		(SELECT samples.location())
	);

	messages = messages || message FROM assert.throws(
		'UPDATE demands SET created_at = NOW()',
		ROW('Column created_at is read only', 'P0001')::error
	);
	messages = messages || message FROM assert.throws(
		format('UPDATE demands SET created_at = %L', v_freezed_now),
		ROW('Column created_at is read only', 'P0001')::error
	);
	RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;