CREATE SCHEMA IF NOT EXISTS samples;

CREATE OR REPLACE FUNCTION samples.body(replacement hstore = '') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO bodies (build, skin, weight, height) VALUES (
		COALESCE(replacement -> 'build', md5(random()::text)),
		COALESCE(replacement -> 'skin', md5(random()::text)),
		COALESCE(CAST(replacement -> 'weight' AS INTEGER), test_utils.better_random()),
		COALESCE(CAST(replacement -> 'height' AS INTEGER), test_utils.better_random())
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.seeker(replacement hstore = '') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO seekers (id, email, password) VALUES (
		COALESCE(CAST(replacement -> 'id' AS INTEGER), test_utils.better_random()),
		COALESCE(replacement -> 'email', md5(random()::text)),
		COALESCE(replacement -> 'password', md5(random()::text))
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.location(replacement hstore = '') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO locations (coordinates, place, met_at) VALUES (
		COALESCE(CAST(replacement -> 'coordinates' AS POINT), POINT(random(), random())),
		COALESCE(replacement -> 'place', md5(random()::text)),
		COALESCE(CAST(replacement -> 'met_at' AS TSTZRANGE), '[2017-01-01,2017-01-02)'::tstzrange)
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.hand(replacement hstore = '') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO hands (nails, care, veins, joint, hair) VALUES (
		ROW(test_utils.random_enum('colors'), random() * 100, test_utils.random_enum('care'))::nail,
		COALESCE(CAST(replacement -> 'care' AS hand_care), test_utils.random_enum('hand_care')::hand_care),
		COALESCE(CAST(replacement -> 'veins' AS vein_visibility), test_utils.random_enum('vein_visibility')::vein_visibility),
		COALESCE(CAST(replacement -> 'joint' AS joint_visibility), test_utils.random_enum('joint_visibility')::joint_visibility),
		COALESCE(CAST(replacement -> 'hair' AS hand_hair), test_utils.random_enum('hand_hair')::hand_hair)
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.general(replacement hstore = '') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO general (gender, race, birth_year, firstname, lastname) VALUES (
		COALESCE(CAST(replacement -> 'gender' AS genders), test_utils.random_enum('genders')::genders),
		COALESCE(CAST(replacement -> 'race' AS races), test_utils.random_enum('races')::races),
		COALESCE(CAST(replacement -> 'birth_year' AS int4range), int4range(1996, 1998)),
		COALESCE(replacement -> 'firstname', md5(random()::TEXT)),
		COALESCE(replacement -> 'lastname', md5(random()::TEXT))
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.face(replacement hstore = '') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO faces (teeth, freckles, complexion, beard, acne, shape, hair, eyebrow, left_eye, right_eye) VALUES (
		ROW(
			test_utils.random_enum('care')::care,
			random() > 0.5
		)::tooth,
		random() > 0.5,
		test_utils.random_enum('care')::care,
		md5(random()::TEXT),
		random() > 0.5,
		md5(random()::TEXT),
		ROW(
			md5(random()::TEXT),
			test_utils.random_enum('colors')::colors,
			test_utils.better_random(),
			random() > 0.5,
			random() > 0.5,
			random() > 0.5
		)::hair,
		md5(random()::TEXT),
		ROW(test_utils.random_enum('colors')::colors, random() > 0.5)::eye,
		ROW(test_utils.random_enum('colors')::colors, random() > 0.5)::eye
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.description() RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO descriptions (general_id, body_id, face_id, hands_id) VALUES (
		(SELECT general FROM samples.general()),
		(SELECT body FROM samples.body()),
		(SELECT face FROM samples.face()),
		(SELECT hand FROM samples.hand())
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;