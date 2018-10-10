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
	INSERT INTO hair (style_id, color_id, length_id, highlights, roots, nature) VALUES (
		(SELECT id FROM hair_styles ORDER BY random() LIMIT 1),
		(SELECT color_id FROM hair_colors ORDER BY random() LIMIT 1),
		(SELECT id FROM hair_lengths ORDER BY random() LIMIT 1),
		random() > 0.5,
		random() > 0.5,
		random() > 0.5
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;


CREATE OR REPLACE FUNCTION samples.nullable_hair(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_id integer;
BEGIN
  INSERT INTO hair (style_id, color_id, length_id, highlights, roots, nature) VALUES (
    NULL,
    samples.random_if_not_exists((SELECT color_id FROM hair_colors ORDER BY random() LIMIT 1), replacements, 'color_id'),
    NULL,
    NULL,
    NULL,
    NULL
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


CREATE OR REPLACE FUNCTION samples.nullable_eyebrow(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_id integer;
BEGIN
  INSERT INTO eyebrows (color_id, care) VALUES (
    NULL,
    NULL
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

CREATE OR REPLACE FUNCTION samples.nullable_eye(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_id integer;
BEGIN
  INSERT INTO eyes (color_id, lenses) VALUES (
    samples.random_if_not_exists(NULL, replacements, 'color_id')::integer,
    NULL
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

CREATE OR REPLACE FUNCTION samples.nullable_tooth(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_id integer;
BEGIN
  INSERT INTO teeth (care, braces) VALUES (
    NULL,
    NULL
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
	INSERT INTO beards (color_id, length_id, style_id) VALUES (
		(SELECT color_id FROM beard_colors ORDER BY random() LIMIT 1),
		(SELECT id FROM beard_lengths ORDER BY random() LIMIT 1),
		(SELECT id FROM beard_styles ORDER BY random() LIMIT 1)
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.nullable_beard(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_id integer;
BEGIN
  INSERT INTO beards (color_id, length_id, style_id) VALUES (
    NULL,
	NULL,
    NULL
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
	INSERT INTO bodies (build_id, breast_size) VALUES (
		samples.random_if_not_exists((SELECT id FROM body_builds ORDER BY random() LIMIT 1), replacements, 'build_id'),
		NULL
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.nullable_body(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_id integer;
BEGIN
  INSERT INTO bodies (build_id, breast_size) VALUES (
    samples.random_if_not_exists(NULL, replacements, 'build_id')::integer,
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
	INSERT INTO seekers (email, password, role) VALUES (
		samples.random_if_not_exists(md5(random()::text), replacements, 'email'),
		samples.random_if_not_exists(md5(random()::text), replacements, 'password'),
		samples.random_if_not_exists(test_utils.random_array_pick(constant.roles()), replacements, 'role')::roles
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.seeker_contact(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO seeker_contacts (seeker_id, facebook, instagram, phone_number) VALUES (
		samples.random_if_not_exists((SELECT samples.seeker()), replacements, 'seeker_id'),
		CASE WHEN replacements ->> 'facebook' IS NULL THEN NULL ELSE samples.random_if_not_exists(md5(random()::text), replacements, 'facebook') END,
		CASE WHEN replacements ->> 'instagram' IS NULL THEN NULL ELSE samples.random_if_not_exists(md5(random()::text), replacements, 'instagram') END,
		CASE WHEN replacements ->> 'phone_number' IS NULL THEN NULL ELSE samples.random_if_not_exists(md5(random()::text), replacements, 'phone_number') END
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.verification_code(replacements jsonb = '{}') RETURNS INTEGER
AS $$
DECLARE
  v_id integer;
BEGIN
  INSERT INTO verification_codes (seeker_id, code, used_at) VALUES (
    samples.random_if_not_exists((SELECT samples.seeker()), replacements, 'seeker_id'),
    samples.random_if_not_exists(substr(md5(random()::text) || md5(random()::text) || md5(random()::text), 1, 91), replacements, 'code'),
    CASE WHEN replacements ->> 'used_at' IS NULL THEN NULL ELSE samples.random_if_not_exists(NOW()::text, replacements, 'used_at')::timestamptz END
  )
  RETURNING id
  INTO v_id;
  RETURN v_id;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION samples.forgotten_password(replacements jsonb = '{}') RETURNS INTEGER
AS $$
DECLARE
  v_id integer;
BEGIN
  INSERT INTO forgotten_passwords (seeker_id, reminded_at, reminder, used_at, expire_at) VALUES (
    samples.random_if_not_exists((SELECT samples.seeker()), replacements, 'seeker_id'),
    samples.random_if_not_exists(NOW()::text, replacements, 'reminded_at')::timestamptz,
    samples.random_if_not_exists(substr(md5(random()::text) || md5(random()::text) || md5(random()::text) || md5(random()::text) || md5(random()::text), 1, 141), replacements, 'reminder'),
    CASE WHEN replacements ->> 'used_at' IS NULL THEN NULL ELSE samples.random_if_not_exists(NOW()::text, replacements, 'used_at')::timestamptz END,
    samples.random_if_not_exists((NOW() + INTERVAL '2 MINUTE')::text, replacements, 'expire_at')::timestamptz
  )
  RETURNING id
  INTO v_id;
  RETURN v_id;
END;
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION samples.nullable_seeker(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_id integer;
BEGIN
  INSERT INTO seekers (email, password) VALUES (
    samples.random_if_not_exists(NULL, replacements, 'email'),
    samples.random_if_not_exists(NULL, replacements, 'password')
  )
  RETURNING id
  INTO v_id;
  RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.spot(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO spots (coordinates, met_at) VALUES (
		POINT(random(), random()),
		ROW(NOW(), 'sooner'::timeline_sides, format('PT%sH', test_utils.better_random(1, 48)))::approximate_timestamptz
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
	INSERT INTO nails (color_id, length_id) VALUES (
		(SELECT color_id FROM nail_colors ORDER BY random() LIMIT 1),
		(SELECT id FROM nail_lengths ORDER BY random() LIMIT 1)
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.nullable_nail(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_id integer;
BEGIN
  INSERT INTO nails (color_id, length_id) VALUES (
    NULL,
    NULL
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
	INSERT INTO hands (nail_id, care, visible_veins) VALUES (
		(SELECT nail FROM samples.nail()),
		test_utils.better_random(0, 10),
		FALSE
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.nullable_hand(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_id integer;
BEGIN
  INSERT INTO hands (nail_id, care, visible_veins) VALUES (
    (SELECT nullable_nail FROM samples.nullable_nail()),
    NULL,
    NULL
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
	v_birth_year general.birth_year%type;
	v_birth_year_range general.birth_year_range%type;
	v_decision boolean;
BEGIN
	IF replacements->>'birth_year_range' IS NULL AND replacements->>'birth_year' IS NULL THEN
		IF test_utils.random_boolean() = TRUE THEN
			v_birth_year_range = samples.random_if_not_exists('[1996,1998)', replacements, 'birth_year_range')::int4range;
		ELSE
			v_birth_year = samples.random_if_not_exists(1991, replacements, 'birth_year')::smallint;
		END IF;
	ELSIF replacements->>'birth_year_range' IS NOT NULL THEN
		v_birth_year_range = samples.random_if_not_exists('[1996,1998)', replacements, 'birth_year_range')::int4range;
	ELSIF replacements->>'birth_year' IS NOT NULL THEN
		v_birth_year = samples.random_if_not_exists(1991, replacements, 'birth_year')::smallint;
	ELSE
		RAISE EXCEPTION 'wrong state';
	END IF;

	INSERT INTO general (sex, ethnic_group_id, birth_year_range, birth_year, firstname, lastname) VALUES (
		samples.random_if_not_exists(test_utils.random_array_pick(constant.sex()), replacements, 'sex')::sex,
		samples.random_if_not_exists((SELECT id FROM ethnic_groups ORDER BY random() LIMIT 1), replacements, 'ethnic_group_id')::integer,
		v_birth_year_range,
		v_birth_year,
		samples.random_if_not_exists(md5(random()::text), replacements, 'firstname'),
		samples.random_if_not_exists(md5(random()::text), replacements, 'lastname')
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.nullable_general(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_id integer;
BEGIN
  INSERT INTO general (sex, ethnic_group_id, birth_year_range, firstname, lastname) VALUES (
    samples.random_if_not_exists(test_utils.random_array_pick(constant.sex()), replacements, 'sex')::sex,
    samples.random_if_not_exists((SELECT id FROM ethnic_groups ORDER BY random() LIMIT 1), replacements, 'ethnic_group_id')::integer,
    samples.random_if_not_exists('[1996,1998)', replacements, 'birth_year_range')::int4range,
    samples.random_if_not_exists(NULL, replacements, 'firstname'),
    samples.random_if_not_exists(NULL, replacements, 'lastname')
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

CREATE OR REPLACE FUNCTION samples.nullable_face(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_id integer;
BEGIN
  INSERT INTO faces (freckles, care, shape_id) VALUES (
    NULL,
    NULL,
    NULL
  )
  RETURNING id
  INTO v_id;
  RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.description(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO descriptions (general_id, body_id, face_id, hand_id, hair_id, left_eye_id, right_eye_id, beard_id, tooth_id, eyebrow_id) VALUES (
		(SELECT general FROM samples.general(replacements)),
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

CREATE OR REPLACE FUNCTION samples.nullable_description(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_id integer;
BEGIN
  INSERT INTO descriptions (general_id, body_id, face_id, hand_id, hair_id, left_eye_id, right_eye_id, beard_id, tooth_id, eyebrow_id) VALUES (
    (SELECT nullable_general FROM samples.nullable_general(replacements -> 'general')),
    (SELECT nullable_body FROM samples.nullable_body()),
    (SELECT nullable_face FROM samples.nullable_face()),
    (SELECT nullable_hand FROM samples.nullable_hand()),
    (SELECT nullable_hair FROM samples.nullable_hair(replacements -> 'hair')),
    (SELECT nullable_eye FROM samples.nullable_eye(replacements -> 'left_eye')),
    (SELECT nullable_eye FROM samples.nullable_eye(replacements -> 'right_eye')),
    (SELECT nullable_beard FROM samples.nullable_beard()),
    (SELECT nullable_tooth FROM samples.nullable_tooth()),
    (SELECT nullable_eyebrow FROM samples.nullable_eyebrow())
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
	INSERT INTO demands (seeker_id, description_id, created_at) VALUES (
		samples.random_if_not_exists((SELECT seeker FROM samples.seeker()), replacements, 'seeker_id')::integer,
		(SELECT description FROM samples.description()),
		NOW()
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.nullable_demand(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
  v_id integer;
BEGIN
  INSERT INTO demands (seeker_id, description_id, created_at) VALUES (
    samples.random_if_not_exists((SELECT seeker FROM samples.seeker()), replacements, 'seeker_id')::integer,
    (SELECT nullable_description FROM samples.nullable_description(replacements)),
    NOW()
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
		(SELECT description FROM samples.description(replacements)),
		NOW()
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.evolution_spot(replacements jsonb = '{}') RETURNS smallint
LANGUAGE plpgsql
AS $$
DECLARE
	v_id smallint;
BEGIN
	INSERT INTO evolution_spots (evolution_id, spot_id) VALUES (
		samples.random_if_not_exists((SELECT samples.evolution(replacements)), replacements, 'evolution_id')::integer,
		samples.random_if_not_exists((SELECT samples.spot(replacements)), replacements, 'spot_id')::integer
	)
	RETURNING id
	INTO v_id;
	RETURN v_id;
END;
$$;

CREATE OR REPLACE FUNCTION samples.demand_spot(replacements jsonb = '{}') RETURNS smallint
LANGUAGE plpgsql
AS $$
DECLARE
	v_id smallint;
BEGIN
	INSERT INTO demand_spots (demand_id, spot_id) VALUES (
		samples.random_if_not_exists((SELECT samples.demand(replacements)), replacements, 'demand_id')::integer,
		samples.random_if_not_exists((SELECT samples.spot(replacements)), replacements, 'spot_id')::integer
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

CREATE OR REPLACE FUNCTION samples.soulmate_request(replacements jsonb = '{}') RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
	v_id integer;
BEGIN
	INSERT INTO soulmate_requests (demand_id, status, searched_at, self_id) VALUES (
		samples.random_if_not_exists((SELECT demand FROM samples.demand()), replacements, 'demand_id')::integer,
		samples.random_if_not_exists(test_utils.random_enum('job_statuses'), replacements, 'status')::job_statuses,
		samples.random_if_not_exists(NOW()::text, replacements, 'searched_at')::timestamptz,
    samples.random_if_not_exists(NULL, replacements, 'self_id')::integer
	)
	RETURNING id
		INTO v_id;
	RETURN v_id;
END;
$$;