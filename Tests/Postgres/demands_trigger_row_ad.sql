CREATE FUNCTION unit_tests.deleting_all_evidences() RETURNS TEST_RESULT AS $$
DECLARE
	inserted_demand_id demands.id%TYPE;
BEGIN
	INSERT INTO demands (seeker_id, description_id, created_at, location_id) VALUES (
		(SELECT samples.seeker()),
		(SELECT samples.description()),
		NOW(),
		(SELECT samples.location())
	)
	RETURNING id INTO inserted_demand_id;

	DELETE FROM demands WHERE id = inserted_demand_id;

	RETURN message FROM assert.is_equal(
		'',
		(SELECT test_utils.tables_not_matching_count('seekers=>1'))
	);
END
$$
LANGUAGE plpgsql;