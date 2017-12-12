CREATE SCHEMA IF NOT EXISTS samples;

CREATE OR REPLACE FUNCTION samples.hair(replacement hstore = '') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO hair (style, color_id, length, highlights, roots, nature) VALUES (
		md5(random()::TEXT),
		(SELECT id FROM colors ORDER BY random() LIMIT 1),
		test_utils.better_random('smallint'),
		random() > 0.5,
		random() > 0.5,
		random() > 0.5
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;


CREATE OR REPLACE FUNCTION samples.eyebrow(replacement hstore = '') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO eyebrows (color_id, care) VALUES (
		(SELECT id FROM colors ORDER BY random() LIMIT 1),
		test_utils.better_random(0, 10)
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;


CREATE OR REPLACE FUNCTION samples.eye(replacement hstore = '') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO eyes (color_id, lenses) VALUES (
		(SELECT id FROM colors ORDER BY random() LIMIT 1),
		random() > 0.5
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.tooth(replacement hstore = '') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO teeth (care, braces) VALUES (
		test_utils.better_random(0, 10),
		random() > 0.5
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.beard(replacement hstore = '') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO beards (color_id, length, style) VALUES (
		COALESCE(CAST(replacement -> 'color_id' AS INTEGER), (SELECT id FROM colors ORDER BY random() LIMIT 1)),
		COALESCE(CAST(replacement -> 'length' AS INTEGER), test_utils.better_random('smallint')),
		COALESCE(replacement -> 'style', md5(random()::text))
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.body(replacement hstore = '') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO bodies (build_id, skin_color_id, weight, height) VALUES (
		COALESCE(CAST(replacement -> 'build_id' AS SMALLINT), (SELECT id FROM body_builds ORDER BY random() LIMIT 1)),
		COALESCE(CAST(replacement -> 'skin_color_id' AS SMALLINT), (SELECT id FROM colors ORDER BY random() LIMIT 1)),
		COALESCE(CAST(replacement -> 'weight' AS SMALLINT), test_utils.better_random('smallint')),
		COALESCE(CAST(replacement -> 'height' AS SMALLINT), test_utils.better_random('smallint'))
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
	INSERT INTO seekers (email, password) VALUES (
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

CREATE OR REPLACE FUNCTION samples.hand_hair(replacement hstore = '') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO hand_hair (color_id, amount) VALUES (
		(SELECT id FROM colors ORDER BY random() LIMIT 1),
		test_utils.better_random(0, 10)
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.nail(replacement hstore = '') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO nails (color_id, length, care) VALUES (
		(SELECT id FROM colors ORDER BY random() LIMIT 1),
		test_utils.better_random('smallint'),
		test_utils.better_random(0, 10)
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
	INSERT INTO hands (nail_id, care, vein_visibility, joint_visibility, hand_hair_id) VALUES (
		COALESCE(CAST(replacement -> 'nail_id' AS integer), (SELECT nail FROM samples.nail())),
		COALESCE(CAST(replacement -> 'care' AS smallint), test_utils.better_random(0, 10)),
		COALESCE(CAST(replacement -> 'vein_visibility' AS smallint), test_utils.better_random(0, 10)),
		COALESCE(CAST(replacement -> 'joint_visibility' AS smallint), test_utils.better_random(0, 10)),
		COALESCE(CAST(replacement -> 'hand_hair_id' AS integer), (SELECT hand_hair FROM samples.hand_hair()))
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
	INSERT INTO general (gender, race_id, birth_year, firstname, lastname) VALUES (
		COALESCE(CAST(replacement -> 'gender' AS genders), test_utils.random_enum('genders')::genders),
		COALESCE(CAST(replacement -> 'race_id' AS integer), (SELECT id FROM races ORDER BY random() LIMIT 1)),
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
	INSERT INTO faces (tooth_id, eyebrow_id, freckles, beard_id, care, shape, left_eye_id, right_eye_id) VALUES (
		(SELECT tooth FROM samples.tooth()),
		(SELECT eyebrow FROM samples.eyebrow()),
		random() > 0.5,
		(SELECT beard FROM samples.beard()),
		test_utils.better_random(0, 10),
		md5(random()::TEXT),
		(SELECT eye FROM samples.eye()),
		(SELECT eye FROM samples.eye())
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
	INSERT INTO descriptions (general_id, body_id, face_id, hand_id, hair_id) VALUES (
		(SELECT general FROM samples.general()),
		(SELECT body FROM samples.body()),
		(SELECT face FROM samples.face()),
		(SELECT hand FROM samples.hand()),
		(SELECT hair FROM samples.hair())
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.demand() RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO demands (seeker_id, description_id, created_at, location_id) VALUES (
		(SELECT seeker FROM samples.seeker()),
		(SELECT description FROM samples.description()),
		NOW(),
		(SELECT location FROM samples.location())
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;