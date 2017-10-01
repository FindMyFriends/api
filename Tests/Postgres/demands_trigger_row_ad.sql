CREATE OR REPLACE FUNCTION unit_tests.deleting_all_evidences() RETURNS TEST_RESULT AS $$
DECLARE
	evidences_count INTEGER;
	seekers_count INTEGER;
	messages TEXT[];
BEGIN
	INSERT INTO general (gender, race, age, firstname, lastname) VALUES (
		'man',
		'european',
		'[20,22)',
		NULL,
		NULL
	);
	INSERT INTO bodies (build, skin, weight, height) VALUES (
		NULL,
		NULL,
		NULL,
		NULL
	);
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
	);
	INSERT INTO descriptions (general_id, body_id, face_id) VALUES (1, 1, 1);
	INSERT INTO demands (seeker_id, description_id, created_at) VALUES (1, 1, NOW());
	INSERT INTO seekers (email, password, description_id) VALUES ('whatever@email.cz', '123', 1);

	DELETE FROM demands WHERE id = 1;

	SELECT
	(SELECT COUNT(*) FROM general)
	+ (SELECT COUNT(*) FROM bodies)
	+ (SELECT COUNT(*) FROM faces)
	+ (SELECT COUNT(*) FROM descriptions)
	+ (SELECT COUNT(*) FROM demands)
	INTO evidences_count;

	SELECT COUNT(*) FROM seekers
	INTO seekers_count;

	messages = messages || message FROM assert.is_equal(evidences_count, 0);
	messages = messages || message FROM assert.is_equal(seekers_count, 1);

	RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;