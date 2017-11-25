CREATE OR REPLACE FUNCTION unit_tests.throwing_on_changing_created_at() RETURNS TEST_RESULT AS $$
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
	), inserted_hand AS (
		INSERT INTO hands (nails, care, veins, joint, hair) VALUES (
			NULL,
			NULL,
			NULL,
			NULL,
			NULL
		)
		RETURNING  id
	), inserted_description AS (
		INSERT INTO descriptions (general_id, body_id, face_id, hands_id) VALUES (
			(SELECT id FROM inserted_general),
			(SELECT id FROM inserted_body),
			(SELECT id FROM inserted_face),
			(SELECT id FROM inserted_hand)
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
		NOW() - INTERVAL '10 MINUTE',
		(SELECT id FROM inserted_location)
	);

	RETURN message FROM assert.throws(
		'UPDATE demands SET created_at = NOW()',
		ROW('Column created_at is read only', 'P0001')::error
	);
END
$$
LANGUAGE plpgsql;