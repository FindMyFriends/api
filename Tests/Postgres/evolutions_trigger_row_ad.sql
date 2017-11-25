CREATE OR REPLACE FUNCTION unit_tests.deleting_all_evidences() RETURNS TEST_RESULT AS $$
DECLARE
	inserted_evolution_id evolutions.id%TYPE;
	inserted_seeker_id seekers.id%TYPE;
BEGIN
	INSERT INTO seekers (email, password) VALUES (
		'whatever2@email.cz',
		'123'
	)
	RETURNING id INTO inserted_seeker_id;

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
	)
	INSERT INTO evolutions (seeker_id, description_id, evolved_at) VALUES (
		inserted_seeker_id,
		(SELECT id FROM inserted_description),
		NOW()
	)
	RETURNING id INTO inserted_evolution_id;

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
	)
	INSERT INTO evolutions (seeker_id, description_id, evolved_at) VALUES (
		inserted_seeker_id,
		(SELECT id FROM inserted_description),
		NOW()
	);

	DELETE FROM evolutions WHERE id = inserted_evolution_id;

	RETURN message FROM assert.is_equal(
		'',
		(SELECT test_utils.tables_not_matching_count('general=>1,bodies=>1,faces=>1,descriptions=>1,evolutions=>1,seekers=>1,hands=>1'))
	);
END
$$
LANGUAGE plpgsql;