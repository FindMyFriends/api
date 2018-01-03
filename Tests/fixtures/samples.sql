CREATE SCHEMA IF NOT EXISTS samples;

CREATE OR REPLACE FUNCTION samples.hair(replacement hstore = '') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO hair (style, color_id, length, highlights, roots, nature) VALUES (
		md5(random()::TEXT),
		(SELECT color_id FROM hair_colors ORDER BY random() LIMIT 1),
		ROW(COALESCE(CAST(replacement -> 'length' AS INTEGER), test_utils.better_random('smallint')), 'mm')::length,
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
		(SELECT color_id FROM eyebrow_colors ORDER BY random() LIMIT 1),
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
		(SELECT color_id FROM eye_colors ORDER BY random() LIMIT 1),
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
		COALESCE(CAST(replacement -> 'color_id' AS INTEGER), (SELECT color_id FROM beard_colors ORDER BY random() LIMIT 1)),
		ROW(COALESCE(CAST(replacement -> 'length' AS INTEGER), test_utils.better_random('smallint')), 'mm')::length,
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
		COALESCE(CAST(replacement -> 'skin_color_id' AS SMALLINT), (SELECT color_id FROM skin_colors ORDER BY random() LIMIT 1)),
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
		ROW(NOW(), 'sooner'::timeline_sides, format('PT%sH', test_utils.better_random(0, 100)))::approximate_timestamptz
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
		(SELECT color_id FROM hand_hair_colors ORDER BY random() LIMIT 1),
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
		(SELECT color_id FROM nail_colors ORDER BY random() LIMIT 1),
		ROW(COALESCE(CAST(replacement -> 'length' AS INTEGER), test_utils.better_random('smallint')), 'mm')::length,
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
	INSERT INTO faces (freckles, care, shape) VALUES (
		random() > 0.5,
		test_utils.better_random(0, 10),
		test_utils.random_enum('face_shapes')::face_shapes
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
	INSERT INTO descriptions (general_id, body_id, face_id, hand_id, hair_id, left_eye_id, right_eye_id, beard_id, tooth_id, eyebrow_id) VALUES (
		(SELECT general FROM samples.general()),
		(SELECT body FROM samples.body()),
		(SELECT face FROM samples.face()),
		(SELECT hand FROM samples.hand()),
		(SELECT hair FROM samples.hair()),
		(SELECT eye FROM samples.eye()),
		(SELECT eye FROM samples.eye()),
		(SELECT beard FROM samples.beard()),
		(SELECT tooth FROM samples.tooth()),
		(SELECT eyebrow FROM samples.eyebrow())
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