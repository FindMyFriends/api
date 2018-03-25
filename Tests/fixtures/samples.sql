CREATE SCHEMA IF NOT EXISTS samples;

CREATE OR REPLACE FUNCTION samples.random_if_not_exists(random text, replacements jsonb, field text) RETURNS text
LANGUAGE plpgsql
AS $$
DECLARE
	v_replacement text;
BEGIN
	IF replacements ? field THEN
		v_replacement = trim(both '"' from CAST (replacements -> field AS TEXT));
		RETURN SUBSTRING(v_replacement, 1, LENGTH(v_replacement));
	ELSE
		RETURN random;
	END IF;
END;
$$;

CREATE OR REPLACE FUNCTION samples.random_if_not_exists(random integer, replacements jsonb, field text) RETURNS integer
LANGUAGE plpgsql
AS $$
BEGIN
	IF replacements ? field THEN
		RETURN CAST (replacements -> field AS TEXT);
	ELSE
		RETURN random;
	END IF;
END;
$$;

CREATE OR REPLACE FUNCTION samples.random_if_not_exists(random boolean, replacements jsonb, field text) RETURNS boolean
LANGUAGE plpgsql
AS $$
BEGIN
  IF replacements ? field THEN
    RETURN CAST (replacements -> field AS TEXT);
  ELSE
    RETURN random;
  END IF;
END;
$$;

CREATE OR REPLACE FUNCTION samples.hair(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO hair (style_id, color_id, length, highlights, roots, nature) VALUES (
		(SELECT id FROM hair_styles ORDER BY random() LIMIT 1),
		(SELECT color_id FROM hair_colors ORDER BY random() LIMIT 1),
		ROW(test_utils.better_random('smallint'), 'mm')::length,
		random() > 0.5,
		random() > 0.5,
		random() > 0.5
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;


CREATE OR REPLACE FUNCTION samples.eyebrow(replacements jsonb = '{}') RETURNS INTEGER
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


CREATE OR REPLACE FUNCTION samples.eye(replacements jsonb = '{}') RETURNS INTEGER
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

CREATE OR REPLACE FUNCTION samples.tooth(replacements jsonb = '{}') RETURNS INTEGER
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

CREATE OR REPLACE FUNCTION samples.beard(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO beards (color_id, length, style) VALUES (
		(SELECT color_id FROM beard_colors ORDER BY random() LIMIT 1),
		ROW(test_utils.better_random('smallint'), 'mm')::length,
		md5(random()::text)
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.body(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO bodies (build_id, weight, height, breast_size) VALUES (
		samples.random_if_not_exists((SELECT id FROM body_builds ORDER BY random() LIMIT 1), replacements, 'build_id'),
		ROW(test_utils.better_random('smallint'), 'kg')::mass,
		ROW(test_utils.better_random('smallint'), 'mm')::length,
		NULL
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.seeker(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO seekers (email, password) VALUES (
		samples.random_if_not_exists(md5(random()::text), replacements, 'email'),
		samples.random_if_not_exists(md5(random()::text), replacements, 'password')
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.location(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO locations (coordinates, place, met_at) VALUES (
		POINT(random(), random()),
		samples.random_if_not_exists(md5(random()::text), replacements, 'place'),
		ROW(NOW(), 'sooner'::timeline_sides, format('PT%sH', test_utils.better_random(1, 48)))::approximate_timestamptz
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.hand_hair(replacements jsonb = '{}') RETURNS INTEGER
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

CREATE OR REPLACE FUNCTION samples.nail(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO nails (color_id, length, care) VALUES (
		(SELECT color_id FROM nail_colors ORDER BY random() LIMIT 1),
		ROW(test_utils.better_random('smallint'), 'mm')::length,
		test_utils.better_random(0, 10)
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.hand(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO hands (nail_id, care, vein_visibility, joint_visibility, hand_hair_id) VALUES (
		(SELECT nail FROM samples.nail()),
		test_utils.better_random(0, 10),
		test_utils.better_random(0, 10),
		test_utils.better_random(0, 10),
		(SELECT hand_hair FROM samples.hand_hair())
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.general(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO general (gender, ethnic_group_id, birth_year, firstname, lastname) VALUES (
		samples.random_if_not_exists(test_utils.random_enum('genders'), replacements, 'gender')::genders,
		(SELECT id FROM ethnic_groups ORDER BY random() LIMIT 1),
		samples.random_if_not_exists('[1996,1998)', replacements, 'birth_year')::int4range,
		samples.random_if_not_exists(md5(random()::text), replacements, 'firstname'),
		samples.random_if_not_exists(md5(random()::text), replacements, 'lastname')
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.face(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO faces (freckles, care, shape_id) VALUES (
		random() > 0.5,
		test_utils.better_random(0, 10),
		(SELECT id FROM face_shapes ORDER BY random() LIMIT 1)
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

CREATE OR REPLACE FUNCTION samples.demand(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO demands (seeker_id, description_id, created_at, location_id) VALUES (
		samples.random_if_not_exists((SELECT seeker FROM samples.seeker()), replacements, 'seeker_id')::integer,
		(SELECT description FROM samples.description()),
		NOW(),
		(SELECT location FROM samples.location())
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.evolution(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO evolutions (seeker_id, description_id, evolved_at) VALUES (
    samples.random_if_not_exists((SELECT seeker FROM samples.seeker()), replacements, 'seeker_id')::integer,
		(SELECT description FROM samples.description()),
		 NOW()
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.soulmate(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO soulmates (demand_id, evolution_id, score, is_correct) VALUES (
		samples.random_if_not_exists((SELECT demand FROM samples.demand()), replacements, 'demand_id')::integer,
		samples.random_if_not_exists((SELECT evolution FROM samples.evolution()), replacements, 'evolution_id')::integer,
		test_utils.better_random('integer'),
    samples.random_if_not_exists(test_utils.random_boolean(), replacements, 'is_correct')::boolean
	)
	RETURNING id
		INTO v_id;
	RETURN v_id;
END;
$$;
