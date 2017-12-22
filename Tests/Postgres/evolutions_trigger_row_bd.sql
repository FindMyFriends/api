CREATE FUNCTION unit_tests.throwing_on_deleting_base() RETURNS TEST_RESULT AS $$
DECLARE
	inserted_evolution_id evolutions.id%TYPE;
BEGIN
	INSERT INTO evolutions (seeker_id, description_id, evolved_at) VALUES (
		(SELECT seeker FROM samples.seeker()),
		(SELECT description FROM samples.description()),
		NOW()
	)
	RETURNING id INTO inserted_evolution_id;

	RETURN message FROM assert.throws(
		format('DELETE FROM evolutions WHERE id = %L', inserted_evolution_id),
		ROW('Base evolution can not be reverted', 'P0001')::error
	);
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.passing_on_not_deleting_base() RETURNS TEST_RESULT AS $$
DECLARE
	inserted_evolution_id evolutions.id%TYPE;
	inserted_seeker_id seekers.id%TYPE;
BEGIN
	SELECT seeker FROM samples.seeker() INTO inserted_seeker_id;

	INSERT INTO evolutions (seeker_id, description_id, evolved_at) VALUES (
		inserted_seeker_id,
		(SELECT description FROM samples.description()),
		NOW()
	)
	RETURNING id INTO inserted_evolution_id;

	INSERT INTO evolutions (seeker_id, description_id, evolved_at) VALUES (
		inserted_seeker_id,
		(SELECT description FROM samples.description()),
		NOW()
	);

	DELETE FROM evolutions WHERE id = inserted_evolution_id;

	RETURN '';
END
$$
LANGUAGE plpgsql;