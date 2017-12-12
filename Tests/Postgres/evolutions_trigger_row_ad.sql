CREATE OR REPLACE FUNCTION unit_tests.deleting_all_evidences() RETURNS TEST_RESULT AS $$
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

	RETURN message FROM assert.is_equal(
		'',
		(SELECT test_utils.tables_not_matching_count(
			hstore(
				ARRAY['hair', 'nails', 'faces', 'teeth', 'hands', 'beards', 'bodies', 'general', 'evolutions', 'descriptions', 'hand_hair', 'seekers', 'eyebrows', 'eyes'],
				ARRAY[1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2]::TEXT[]
			)
		))
	);
END
$$
LANGUAGE plpgsql;