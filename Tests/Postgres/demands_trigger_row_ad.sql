CREATE OR REPLACE FUNCTION unit_tests.deleting_all_evidences() RETURNS TEST_RESULT AS $$
DECLARE
	inserted_demand_id demands.id%TYPE;
BEGIN
	WITH inserted_general AS (
		INSERT INTO general (gender, race, birth_year, firstname, lastname) VALUES (
			'man',
			'european',
			'[1996,1997)',
			NULL,
			NULL
		)
		RETURNING id
	), inserted_body AS (
		INSERT INTO bodies (build, skin, weight, height) VALUES (
			NULL,
			NULL,
			NULL,
			NULL
		)
		RETURNING id
	), inserted_face AS (
		INSERT INTO faces (teeth, freckles, complexion, beard, acne, shape, hair, eyebrow, left_eye, right_eye) VALUES (
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL
		)
		RETURNING  id
	), inserted_description AS (
		INSERT INTO descriptions (general_id, body_id, face_id) VALUES (
			(SELECT id FROM inserted_general),
			(SELECT id FROM inserted_body),
			(SELECT id FROM inserted_face)
		)
		RETURNING id
	), inserted_seeker AS (
		INSERT INTO seekers (email, password) VALUES (
			'whatever@email.cz',
			'123'
		)
		RETURNING id
	), inserted_location AS (
		INSERT INTO locations (coordinates, place, met_at) VALUES (
			POINT(10,20),
			NULL,
			tstzrange(NOW(), NOW())
		)
		RETURNING id
	)
	INSERT INTO demands (seeker_id, description_id, created_at, location_id) VALUES (
		(SELECT id FROM inserted_seeker),
		(SELECT id FROM inserted_description),
		NOW(),
		(SELECT id FROM inserted_location)
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