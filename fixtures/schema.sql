--
-- PostgreSQL database dump
--

-- Dumped from database version 10.0
-- Dumped by pg_dump version 10.0

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: http; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA http;


ALTER SCHEMA http OWNER TO postgres;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner:
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner:
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: citext; Type: EXTENSION; Schema: -; Owner:
--

CREATE EXTENSION IF NOT EXISTS citext WITH SCHEMA public;


--
-- Name: EXTENSION citext; Type: COMMENT; Schema: -; Owner:
--

COMMENT ON EXTENSION citext IS 'data type for case-insensitive character strings';


--
-- Name: hstore; Type: EXTENSION; Schema: -; Owner:
--

CREATE EXTENSION IF NOT EXISTS hstore WITH SCHEMA public;


--
-- Name: EXTENSION hstore; Type: COMMENT; Schema: -; Owner:
--

COMMENT ON EXTENSION hstore IS 'data type for storing sets of (key, value) pairs';


SET search_path = public, pg_catalog;

--
-- Name: face_shapes; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE face_shapes AS ENUM (
    'oval',
    'long',
    'round',
    'square'
);


ALTER TYPE face_shapes OWNER TO postgres;

--
-- Name: genders; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE genders AS ENUM (
    'man',
    'woman'
);


ALTER TYPE genders OWNER TO postgres;

--
-- Name: length_units; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE length_units AS ENUM (
    'mm',
    'cm'
);


ALTER TYPE length_units OWNER TO postgres;

--
-- Name: length; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE length AS (
	value numeric,
	unit length_units
);


ALTER TYPE length OWNER TO postgres;

--
-- Name: flat_description; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE flat_description AS (
	id integer,
	general_gender genders,
	general_race_id smallint,
	general_birth_year int4range,
	general_firstname text,
	general_lastname text,
	hair_style text,
	hair_color_id smallint,
	hair_length length,
	hair_highlights boolean,
	hair_roots boolean,
	hair_nature boolean,
	body_build_id smallint,
	body_skin_color_id smallint,
	body_weight smallint,
	body_height smallint,
	beard_color_id smallint,
	beard_length length,
	beard_style text,
	eyebrow_color_id smallint,
	eyebrow_care smallint,
	left_eye_color_id smallint,
	left_eye_lenses boolean,
	right_eye_color_id smallint,
	right_eye_lenses boolean,
	face_freckles boolean,
	face_care smallint,
	face_shape face_shapes,
	hand_care smallint,
	hand_vein_visibility smallint,
	hand_joint_visibility smallint,
	hand_hair_color_id smallint,
	hand_hair_amount smallint,
	nail_color_id smallint,
	nail_length length,
	nail_care smallint,
	tooth_care smallint,
	tooth_braces boolean
);


ALTER TYPE flat_description OWNER TO postgres;

--
-- Name: printed_color; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE printed_color AS (
	id smallint,
	name text,
	hex text
);


ALTER TYPE printed_color OWNER TO postgres;

--
-- Name: age_to_year(int4range, timestamp with time zone); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION age_to_year(age int4range, now timestamp with time zone) RETURNS int4range
    LANGUAGE plpgsql IMMUTABLE
    AS $$
BEGIN
	RETURN int4range(
		(EXTRACT('year' FROM now) - upper(age))::INTEGER,
		(EXTRACT('year' FROM now) - lower(age))::INTEGER
	);
END
$$;


ALTER FUNCTION public.age_to_year(age int4range, now timestamp with time zone) OWNER TO postgres;

--
-- Name: age_to_year(int4range, tstzrange); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION age_to_year(age int4range, now tstzrange) RETURNS int4range
    LANGUAGE plpgsql IMMUTABLE
    AS $$
BEGIN
	RETURN int4range(
		(EXTRACT('year' FROM lower(now)) - upper(age))::INTEGER,
		(EXTRACT('year' FROM lower(now)) - lower(age))::INTEGER
	);
END
$$;


ALTER FUNCTION public.age_to_year(age int4range, now tstzrange) OWNER TO postgres;

--
-- Name: birth_year_in_range(int4range); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION birth_year_in_range(range int4range) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
BEGIN
	RETURN range <@ int4range(1850, date_part('year', CURRENT_DATE)::INTEGER);
END
$$;


ALTER FUNCTION public.birth_year_in_range(range int4range) OWNER TO postgres;

--
-- Name: collective_demands_trigger_row_ii(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION collective_demands_trigger_row_ii() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	v_location_id INTEGER;
	v_description_id INTEGER;
BEGIN
	v_description_id = inserted_description(
			ROW(
			NULL,
			new.general_gender,
			new.general_race_id,
			age_to_year(new.general_age, new.location_met_at),
			new.general_firstname,
			new.general_lastname,
			new.hair_style,
			new.hair_color_id,
			new.hair_length,
			new.hair_highlights,
			new.hair_roots,
			new.hair_nature,
			new.body_build_id,
			new.body_skin_color_id,
			new.body_weight,
			new.body_height,
			new.face_beard_color_id,
			new.face_beard_length,
			new.face_beard_style,
			new.face_eyebrow_color_id,
			new.face_eyebrow_care,
			new.face_left_eye_color_id,
			new.face_left_eye_lenses,
			new.face_right_eye_color_id,
			new.face_right_eye_lenses,
			new.face_freckles,
			new.face_care,
			new.face_shape,
			new.hands_care,
			new.hands_vein_visibility,
			new.hands_joint_visibility,
			new.hands_hair_color_id,
			new.hands_hair_amount,
			new.hands_nails_color_id,
			new.hands_nails_length,
			new.hands_nails_care,
			new.face_tooth_care,
			new.face_tooth_braces
		)::flat_description
	);
	INSERT INTO locations (coordinates, place, met_at) VALUES (
		new.location_coordinates,
		NULL,
		new.location_met_at
	)
	RETURNING id
	INTO v_location_id;

	INSERT INTO demands (seeker_id, description_id, created_at, location_id) VALUES (
		new.seeker_id,
		v_description_id,
		NOW(),
		v_location_id
	)
	RETURNING id INTO new.id;

	RETURN new;
END
$$;


ALTER FUNCTION public.collective_demands_trigger_row_ii() OWNER TO postgres;

--
-- Name: collective_demands_trigger_row_iu(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION collective_demands_trigger_row_iu() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	v_location_id INTEGER;
	v_description_id INTEGER;
BEGIN
	SELECT location_id, description_id
	FROM demands
	JOIN locations ON locations.id = demands.location_id
	WHERE demands.id = new.id
	INTO v_location_id, v_description_id;

	UPDATE locations
	SET coordinates = new.location_coordinates,
		met_at = new.location_met_at
	WHERE id = v_location_id;

	PERFORM updated_description(
			ROW(
			v_description_id,
			new.general_gender,
			new.general_race_id,
			age_to_year(new.general_age, new.location_met_at),
			new.general_firstname,
			new.general_lastname,
			new.hair_style,
			new.hair_color_id,
			new.hair_length,
			new.hair_highlights,
			new.hair_roots,
			new.hair_nature,
			new.body_build_id,
			new.body_skin_color_id,
			new.body_weight,
			new.body_height,
			new.face_beard_color_id,
			new.face_beard_length,
			new.face_beard_style,
			new.face_eyebrow_color_id,
			new.face_eyebrow_care,
			new.face_left_eye_color_id,
			new.face_left_eye_lenses,
			new.face_right_eye_color_id,
			new.face_right_eye_lenses,
			new.face_freckles,
			new.face_care,
			new.face_shape,
			new.hands_care,
			new.hands_vein_visibility,
			new.hands_joint_visibility,
			new.hands_hair_color_id,
			new.hands_hair_amount,
			new.hands_nails_color_id,
			new.hands_nails_length,
			new.hands_nails_care,
			new.face_tooth_care,
			new.face_tooth_braces
		)::flat_description
	);

	RETURN new;
END
$$;


ALTER FUNCTION public.collective_demands_trigger_row_iu() OWNER TO postgres;

--
-- Name: collective_evolutions_trigger_row_ii(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION collective_evolutions_trigger_row_ii() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	v_description_id INTEGER;
BEGIN
	v_description_id = inserted_description(
			ROW(
			NULL,
			new.general_gender,
			new.general_race_id,
			(
				SELECT birth_year
				FROM base_evolution
				WHERE seeker_id = new.seeker_id
				LIMIT 1
			),
			new.general_firstname,
			new.general_lastname,
			new.hair_style,
			new.hair_color_id,
			new.hair_length,
			new.hair_highlights,
			new.hair_roots,
			new.hair_nature,
			new.body_build_id,
			new.body_skin_color_id,
			new.body_weight,
			new.body_height,
			new.face_beard_color_id,
			new.face_beard_length,
			new.face_beard_style,
			new.face_eyebrow_color_id,
			new.face_eyebrow_care,
			new.face_left_eye_color_id,
			new.face_left_eye_lenses,
			new.face_right_eye_color_id,
			new.face_right_eye_lenses,
			new.face_freckles,
			new.face_care,
			new.face_shape,
			new.hands_care,
			new.hands_vein_visibility,
			new.hands_joint_visibility,
			new.hands_hair_color_id,
			new.hands_hair_amount,
			new.hands_nails_color_id,
			new.hands_nails_length,
			new.hands_nails_care,
			new.face_tooth_care,
			new.face_tooth_braces
		)::flat_description
	);
	INSERT INTO evolutions (seeker_id, description_id, evolved_at) VALUES (
		new.seeker_id,
		v_description_id,
		new.evolved_at
	)
	RETURNING id INTO new.id;

	RETURN new;
END
$$;


ALTER FUNCTION public.collective_evolutions_trigger_row_ii() OWNER TO postgres;

--
-- Name: collective_evolutions_trigger_row_iu(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION collective_evolutions_trigger_row_iu() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	v_description_id INTEGER;
BEGIN
	SELECT description_id
	FROM evolutions
	WHERE evolutions.id = new.id
	INTO v_description_id;

	PERFORM updated_description(
		ROW(
		v_description_id,
		new.general_gender,
		new.general_race_id,
		new.general_birth_year,
		new.general_firstname,
		new.general_lastname,
		new.hair_style,
		new.hair_color_id,
		new.hair_length,
		new.hair_highlights,
		new.hair_roots,
		new.hair_nature,
		new.body_build_id,
		new.body_skin_color_id,
		new.body_weight,
		new.body_height,
		new.face_beard_color_id,
		new.face_beard_length,
		new.face_beard_style,
		new.face_eyebrow_color_id,
		new.face_eyebrow_care,
		new.face_left_eye_color_id,
		new.face_left_eye_lenses,
		new.face_right_eye_color_id,
		new.face_right_eye_lenses,
		new.face_freckles,
		new.face_care,
		new.face_shape,
		new.hands_care,
		new.hands_vein_visibility,
		new.hands_joint_visibility,
		new.hands_hair_color_id,
		new.hands_hair_amount,
		new.hands_nails_color_id,
		new.hands_nails_length,
		new.hands_nails_care,
		new.face_tooth_care,
		new.face_tooth_braces
		)
	);

	UPDATE general
	SET birth_year = (SELECT birth_year FROM descriptions WHERE id = v_description_id)
	WHERE id IN (
		SELECT general_id
		FROM base_evolution
		WHERE seeker_id = new.seeker_id
	);

	RETURN new;
END
$$;


ALTER FUNCTION public.collective_evolutions_trigger_row_iu() OWNER TO postgres;

--
-- Name: deleted_description(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION deleted_description(v_description_id integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
	description RECORD;
	face RECORD;
	hand RECORD;
BEGIN
	DELETE FROM descriptions
	WHERE id = v_description_id
	RETURNING face_id, general_id, body_id, hand_id, hair_id
	INTO description;

	DELETE FROM faces
	WHERE id = description.face_id
	RETURNING tooth_id, beard_id, left_eye_id, right_eye_id, eyebrow_id
	INTO face;

	DELETE FROM teeth WHERE id = face.tooth_id;
	DELETE FROM beards WHERE id = face.beard_id;
	DELETE FROM eyes WHERE id IN(face.left_eye_id, face.right_eye_id);
	DELETE FROM eyebrows WHERE id = face.eyebrow_id;

	DELETE FROM general WHERE id = description.general_id;

	DELETE FROM bodies WHERE id = description.body_id;

	DELETE FROM hands
	WHERE id = description.hand_id
	RETURNING nail_id, hand_hair_id
	INTO hand;

	DELETE FROM hand_hair WHERE id = hand.hand_hair_id;
	DELETE FROM nails WHERE id = hand.nail_id;
	DELETE FROM hair WHERE id = description.hair_id;

	RETURN v_description_id;
END;
$$;


ALTER FUNCTION public.deleted_description(v_description_id integer) OWNER TO postgres;

--
-- Name: demands_trigger_row_ad(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION demands_trigger_row_ad() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	PERFORM deleted_description(old.description_id);
	DELETE FROM locations WHERE id = old.location_id;
	RETURN old;
END;
$$;


ALTER FUNCTION public.demands_trigger_row_ad() OWNER TO postgres;

--
-- Name: demands_trigger_row_bu(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION demands_trigger_row_bu() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	RAISE EXCEPTION 'Column created_at is read only';
	RETURN new;
END;
$$;


ALTER FUNCTION public.demands_trigger_row_bu() OWNER TO postgres;

--
-- Name: evolutions_trigger_row_ad(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION evolutions_trigger_row_ad() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	PERFORM deleted_description(old.description_id);
	RETURN old;
END;
$$;


ALTER FUNCTION public.evolutions_trigger_row_ad() OWNER TO postgres;

--
-- Name: evolutions_trigger_row_bd(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION evolutions_trigger_row_bd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF (SELECT (COUNT(*) = 1) FROM evolutions WHERE seeker_id = old.seeker_id LIMIT 2) THEN
		RAISE EXCEPTION 'Base evolution can not be reverted';
	END IF;
	RETURN old;
END;
$$;


ALTER FUNCTION public.evolutions_trigger_row_bd() OWNER TO postgres;

--
-- Name: inserted_description(flat_description); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION inserted_description(description flat_description) RETURNS integer
    LANGUAGE plpgsql
    AS $$
	DECLARE
		v_beard_id INTEGER;
		v_tooth_id INTEGER;
		v_eyebrow_id INTEGER;
		v_left_eye_id INTEGER;
		v_right_eye_id INTEGER;
		v_hand_nail_id INTEGER;
		v_hand_hair_id INTEGER;
		v_face_id INTEGER;
		v_body_id INTEGER;
		v_hand_id INTEGER;
		v_general_id INTEGER;
		v_hair_id INTEGER;
		v_description_id INTEGER;
	BEGIN
		INSERT INTO general (gender, race_id, birth_year, firstname, lastname) VALUES (
			description.general_gender,
			description.general_race_id,
			description.general_birth_year,
			description.general_firstname,
			description.general_lastname
		)
		RETURNING id
		INTO v_general_id;
		INSERT INTO hair (style, color_id, length, highlights, roots, nature) VALUES (
			description.hair_style,
			description.hair_color_id,
			description.hair_length,
			description.hair_highlights,
			description.hair_roots,
			description.hair_nature
		)
		RETURNING id
		INTO v_hair_id;
		INSERT INTO beards (color_id, length, style) VALUES (
			description.beard_color_id,
			description.beard_length,
			description.beard_style
		)
		RETURNING id
		INTO v_beard_id;
		INSERT INTO teeth (care, braces) VALUES (
			description.tooth_care,
			description.tooth_braces
		)
		RETURNING id
		INTO v_tooth_id;
		INSERT INTO eyebrows (color_id, care) VALUES (
			description.eyebrow_color_id,
			description.eyebrow_care
		)
		RETURNING id
		INTO v_eyebrow_id;
		INSERT INTO eyes (color_id, lenses) VALUES (
			description.left_eye_color_id,
			description.left_eye_lenses
		)
		RETURNING id
		INTO v_left_eye_id;
		INSERT INTO eyes (color_id, lenses) VALUES (
			description.right_eye_color_id,
			description.right_eye_lenses
		)
		RETURNING id
		INTO v_right_eye_id;
		INSERT INTO nails (color_id, length, care) VALUES (
			description.nail_color_id,
			description.nail_length,
			description.nail_care
		)
		RETURNING id
		INTO v_hand_nail_id;
		INSERT INTO hand_hair (color_id, amount) VALUES (
			description.hand_hair_color_id,
			description.hand_hair_amount
		)
		RETURNING id
		INTO v_hand_hair_id;
		INSERT INTO faces (tooth_id, freckles, beard_id, care, shape, eyebrow_id, left_eye_id, right_eye_id) VALUES (
			v_tooth_id,
			description.face_freckles,
			v_beard_id,
			description.face_care,
			description.face_shape,
			v_eyebrow_id,
			v_left_eye_id,
			v_right_eye_id
		)
		RETURNING id
		INTO v_face_id;
		INSERT INTO bodies (build_id, skin_color_id, weight, height) VALUES (
			description.body_build_id,
			description.body_skin_color_id,
			description.body_weight,
			description.body_height
		)
		RETURNING id
		INTO v_body_id;
		INSERT INTO hands (nail_id, care, vein_visibility, joint_visibility, hand_hair_id) VALUES (
			v_hand_nail_id,
			description.hand_care,
			description.hand_vein_visibility,
			description.hand_joint_visibility,
			v_hand_hair_id
		)
		RETURNING id
		INTO v_hand_id;
		INSERT INTO descriptions (general_id, body_id, face_id, hand_id, hair_id) VALUES (
			v_general_id,
			v_body_id,
			v_face_id,
			v_hand_id,
			v_hair_id
		) RETURNING id INTO v_description_id;
		RETURN v_description_id;
	END $$;


ALTER FUNCTION public.inserted_description(description flat_description) OWNER TO postgres;

--
-- Name: is_hex_color(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION is_hex_color(color text) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
BEGIN
	RETURN color ~ '^#[a-f0-9]{6}';
END
$$;


ALTER FUNCTION public.is_hex_color(color text) OWNER TO postgres;

--
-- Name: is_rating(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION is_rating(rating integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
BEGIN
	RETURN int4range(0, 10, '[]') @> rating;
END
$$;


ALTER FUNCTION public.is_rating(rating integer) OWNER TO postgres;

--
-- Name: suited_length(length); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION suited_length(length) RETURNS length
    LANGUAGE plpgsql IMMUTABLE
    AS $_$
BEGIN
	IF (($1).unit = 'mm' AND ($1).value >= 10) THEN
		RETURN ROW(($1).value / 10, 'cm'::length_units);
	END IF;
	RETURN $1;
END
$_$;


ALTER FUNCTION public.suited_length(length) OWNER TO postgres;

--
-- Name: united_length(length); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION united_length(length) RETURNS length
    LANGUAGE plpgsql IMMUTABLE
    AS $_$
BEGIN
	IF (($1).unit = 'cm') THEN
		RETURN ROW(($1).value * 10, 'mm'::length_units);
	END IF;
	RETURN $1;
END
$_$;


ALTER FUNCTION public.united_length(length) OWNER TO postgres;

--
-- Name: united_length_trigger(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION united_length_trigger() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	new."length" = united_length(new."length");
	RETURN new;
END;
$$;


ALTER FUNCTION public.united_length_trigger() OWNER TO postgres;

--
-- Name: updated_description(flat_description); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION updated_description(description flat_description) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
	v_beard_id INTEGER;
	v_tooth_id INTEGER;
	v_eyebrow_id INTEGER;
	v_left_eye_id INTEGER;
	v_right_eye_id INTEGER;
	v_hand_nail_id INTEGER;
	v_hand_hair_id INTEGER;
	parts RECORD;
BEGIN
	SELECT *
	FROM description_parts
	WHERE id = description.id
	INTO parts;

	UPDATE general
	SET gender = description.general_gender,
		race_id = description.general_race_id,
		birth_year = description.general_birth_year,
		firstname = description.general_firstname,
		lastname = description.general_lastname
	WHERE id = parts.general_id;
	UPDATE hair
	SET style = description.hair_style,
		color_id = description.hair_color_id,
		length = description.hair_length,
		highlights = description.hair_highlights,
		roots = description.hair_roots,
		nature = description.hair_nature
	WHERE id = parts.hair_id;

	INSERT INTO beards (color_id, length, style) VALUES (
		description.beard_color_id,
		description.beard_length,
		description.beard_style
	)
	RETURNING id
	INTO v_beard_id;

	INSERT INTO teeth (care, braces) VALUES (
		description.tooth_care,
		description.tooth_braces
	)
	RETURNING id
	INTO v_tooth_id;

	INSERT INTO eyebrows (color_id, care) VALUES (
		description.eyebrow_color_id,
		description.eyebrow_care
	)
	RETURNING id
	INTO v_eyebrow_id;

	INSERT INTO eyes (color_id, lenses) VALUES (
		description.left_eye_color_id,
		description.left_eye_lenses
	)
	RETURNING id
	INTO v_left_eye_id;

	INSERT INTO eyes (color_id, lenses) VALUES (
		description.right_eye_color_id,
		description.right_eye_lenses
	)
	RETURNING id
	INTO v_right_eye_id;

	UPDATE faces
	SET tooth_id = v_tooth_id,
		freckles = description.face_freckles,
		beard_id = v_beard_id,
		care = description.face_care,
		shape = description.face_shape,
		eyebrow_id = v_eyebrow_id,
		left_eye_id = v_left_eye_id,
		right_eye_id = v_right_eye_id
	WHERE id = parts.face_id;

	UPDATE bodies
	SET build_id = description.body_build_id,
		skin_color_id = description.body_skin_color_id,
		weight = description.body_weight,
		height = description.body_height
	WHERE id = parts.body_id;

	INSERT INTO nails (color_id, length, care) VALUES (
		description.nail_color_id,
		description.nail_length,
		description.nail_care
	)
	RETURNING id
	INTO v_hand_nail_id;

	INSERT INTO hand_hair (color_id, amount) VALUES (
		description.hand_hair_color_id,
		description.hand_hair_amount
	)
	RETURNING id
	INTO v_hand_hair_id;

	UPDATE hands
	SET nail_id = v_hand_nail_id,
		care = description.hand_care,
		vein_visibility = description.hand_vein_visibility,
		joint_visibility = description.hand_joint_visibility,
		hand_hair_id = v_hand_hair_id
	WHERE id = parts.hand_id;

	RETURN parts.id;
END $$;


ALTER FUNCTION public.updated_description(description flat_description) OWNER TO postgres;

--
-- Name: year_to_age(int4range, timestamp with time zone); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION year_to_age(year int4range, now timestamp with time zone) RETURNS int4range
    LANGUAGE plpgsql IMMUTABLE
    AS $$
BEGIN
	RETURN int4range(
		(EXTRACT('year' from now) - upper(year))::INTEGER,
		(EXTRACT('year' from now) - lower(year))::INTEGER
	);
END
$$;


ALTER FUNCTION public.year_to_age(year int4range, now timestamp with time zone) OWNER TO postgres;

--
-- Name: year_to_age(int4range, tstzrange); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION year_to_age(year int4range, now tstzrange) RETURNS int4range
    LANGUAGE plpgsql IMMUTABLE
    AS $$
BEGIN
	RETURN int4range(
		(SELECT extract('year' from lower(now)) - upper(year))::INTEGER,
		(SELECT extract('year' from lower(now)) - lower(year))::INTEGER
	);
END
$$;


ALTER FUNCTION public.year_to_age(year int4range, now tstzrange) OWNER TO postgres;

SET search_path = http, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: etags; Type: TABLE; Schema: http; Owner: postgres
--

CREATE TABLE etags (
    id integer NOT NULL,
    entity character varying NOT NULL,
    tag character varying(34) NOT NULL,
    created_at timestamp with time zone NOT NULL
);


ALTER TABLE etags OWNER TO postgres;

--
-- Name: etags_id_seq; Type: SEQUENCE; Schema: http; Owner: postgres
--

ALTER TABLE etags ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME etags_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


SET search_path = public, pg_catalog;

--
-- Name: skin_colors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE skin_colors (
    id smallint NOT NULL,
    color_id smallint NOT NULL
);


ALTER TABLE skin_colors OWNER TO postgres;

--
-- Name: alter_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE skin_colors ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME alter_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: descriptions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE descriptions (
    id integer NOT NULL,
    general_id integer NOT NULL,
    body_id integer NOT NULL,
    face_id integer NOT NULL,
    hand_id integer NOT NULL,
    hair_id integer NOT NULL
);


ALTER TABLE descriptions OWNER TO postgres;

--
-- Name: evolutions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE evolutions (
    id integer NOT NULL,
    seeker_id integer NOT NULL,
    description_id integer NOT NULL,
    evolved_at timestamp with time zone NOT NULL
);


ALTER TABLE evolutions OWNER TO postgres;

--
-- Name: general; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE general (
    id integer NOT NULL,
    gender genders NOT NULL,
    race_id smallint NOT NULL,
    birth_year int4range NOT NULL,
    firstname text,
    lastname text,
    CONSTRAINT general_birth_year_check CHECK (birth_year_in_range(birth_year))
);


ALTER TABLE general OWNER TO postgres;

--
-- Name: base_evolution; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW base_evolution AS
 SELECT general.birth_year,
    general.id AS general_id,
    evolutions.seeker_id
   FROM ((general
     JOIN descriptions ON ((descriptions.general_id = general.id)))
     JOIN evolutions ON ((evolutions.description_id = descriptions.id)));


ALTER TABLE base_evolution OWNER TO postgres;

--
-- Name: beard_colors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE beard_colors (
    id smallint NOT NULL,
    color_id smallint NOT NULL
);


ALTER TABLE beard_colors OWNER TO postgres;

--
-- Name: beard_colors_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE beard_colors ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME beard_colors_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: beards; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE beards (
    id integer NOT NULL,
    color_id smallint,
    length length,
    style text
);


ALTER TABLE beards OWNER TO postgres;

--
-- Name: beards_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE beards ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME beards_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: bodies; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE bodies (
    id integer NOT NULL,
    build_id smallint,
    skin_color_id smallint,
    weight smallint,
    height smallint
);


ALTER TABLE bodies OWNER TO postgres;

--
-- Name: bodies_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE bodies ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME bodies_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: body_builds; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE body_builds (
    id smallint NOT NULL,
    name text NOT NULL
);


ALTER TABLE body_builds OWNER TO postgres;

--
-- Name: body_builds_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE body_builds ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME body_builds_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: colors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE colors (
    id smallint NOT NULL,
    name text NOT NULL,
    hex text NOT NULL,
    CONSTRAINT colors_hex_check CHECK ((is_hex_color(hex) AND (lower(hex) = hex)))
);


ALTER TABLE colors OWNER TO postgres;

--
-- Name: eyebrows; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE eyebrows (
    id integer NOT NULL,
    color_id smallint,
    care smallint,
    CONSTRAINT eyebrows_care_check CHECK (is_rating((care)::integer))
);


ALTER TABLE eyebrows OWNER TO postgres;

--
-- Name: eyes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE eyes (
    id integer NOT NULL,
    color_id smallint,
    lenses boolean
);


ALTER TABLE eyes OWNER TO postgres;

--
-- Name: faces; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE faces (
    id integer NOT NULL,
    tooth_id integer,
    freckles boolean,
    beard_id integer,
    care smallint,
    shape face_shapes,
    eyebrow_id integer,
    left_eye_id integer,
    right_eye_id integer,
    CONSTRAINT faces_care_check CHECK (is_rating((care)::integer))
);


ALTER TABLE faces OWNER TO postgres;

--
-- Name: hair; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE hair (
    id integer NOT NULL,
    style text,
    color_id smallint,
    length length,
    highlights boolean,
    roots boolean,
    nature boolean
);


ALTER TABLE hair OWNER TO postgres;

--
-- Name: hand_hair; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE hand_hair (
    id integer NOT NULL,
    color_id smallint,
    amount smallint,
    CONSTRAINT hand_hair_amount_check CHECK (is_rating((amount)::integer))
);


ALTER TABLE hand_hair OWNER TO postgres;

--
-- Name: hands; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE hands (
    id integer NOT NULL,
    nail_id integer,
    care smallint,
    vein_visibility smallint,
    joint_visibility smallint,
    hand_hair_id integer,
    CONSTRAINT hands_care_check CHECK (is_rating((care)::integer)),
    CONSTRAINT hands_joint_visibility_check CHECK (is_rating((joint_visibility)::integer)),
    CONSTRAINT hands_vein_visibility_check CHECK (is_rating((vein_visibility)::integer))
);


ALTER TABLE hands OWNER TO postgres;

--
-- Name: nails; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE nails (
    id integer NOT NULL,
    color_id smallint,
    length length,
    care smallint,
    CONSTRAINT nails_care_check CHECK (is_rating((care)::integer))
);


ALTER TABLE nails OWNER TO postgres;

--
-- Name: races; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE races (
    id smallint NOT NULL,
    name text NOT NULL
);


ALTER TABLE races OWNER TO postgres;

--
-- Name: teeth; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE teeth (
    id integer NOT NULL,
    care smallint,
    braces boolean,
    CONSTRAINT teeth_care_check CHECK (is_rating((care)::integer))
);


ALTER TABLE teeth OWNER TO postgres;

--
-- Name: complete_descriptions; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW complete_descriptions AS
 SELECT description.id,
    ROW(general.id, general.gender, general.race_id, general.birth_year, general.firstname, general.lastname)::general AS general,
    ROW(hair.id, hair.style, hair.color_id, hair.length, hair.highlights, hair.roots, hair.nature)::hair AS hair,
    ROW(body.id, body.build_id, body.skin_color_id, body.weight, body.height)::bodies AS body,
    ROW(body_build.id, body_build.name)::body_builds AS body_build,
    ROW(face.id, face.tooth_id, face.freckles, face.beard_id, face.care, face.shape, face.eyebrow_id, face.left_eye_id, face.right_eye_id)::faces AS face,
    ROW(beard.id, beard.color_id, beard.length, beard.style)::beards AS beard,
    ROW(race.id, race.name)::races AS race,
    ROW(hand.id, hand.nail_id, hand.care, hand.vein_visibility, hand.joint_visibility, hand.hand_hair_id)::hands AS hand,
    ROW(hand_hair.id, hand_hair.color_id, hand_hair.amount)::hand_hair AS hand_hair,
    ROW(nail.id, nail.color_id, nail.length, nail.care)::nails AS nail,
    ROW(tooth.id, tooth.care, tooth.braces)::teeth AS tooth,
    ROW(eyebrow.id, eyebrow.color_id, eyebrow.care)::eyebrows AS eyebrow,
    ROW(left_eye.id, left_eye.color_id, left_eye.lenses)::eyes AS left_eye,
    ROW(right_eye.id, right_eye.color_id, right_eye.lenses)::eyes AS right_eye,
    ROW(body_skin_color.id, body_skin_color.name, body_skin_color.hex)::colors AS body_skin_color,
    ROW(left_eye_color.id, left_eye_color.name, left_eye_color.hex)::colors AS left_eye_color,
    ROW(right_eye_color.id, right_eye_color.name, right_eye_color.hex)::colors AS right_eye_color,
    ROW(beard_color.id, beard_color.name, beard_color.hex)::colors AS beard_color,
    ROW(hair_color.id, hair_color.name, hair_color.hex)::colors AS hair_color,
    ROW(eyebrow_color.id, eyebrow_color.name, eyebrow_color.hex)::colors AS eyebrow_color,
    ROW(hand_hair_color.id, hand_hair_color.name, hand_hair_color.hex)::colors AS hand_hair_color,
    ROW(nail_color.id, nail_color.name, nail_color.hex)::colors AS nail_color
   FROM ((((((((((((((((((((((descriptions description
     LEFT JOIN hair ON ((hair.id = description.hair_id)))
     LEFT JOIN bodies body ON ((body.id = description.body_id)))
     LEFT JOIN body_builds body_build ON ((body_build.id = body.build_id)))
     LEFT JOIN faces face ON ((face.id = description.face_id)))
     LEFT JOIN beards beard ON ((beard.id = face.beard_id)))
     LEFT JOIN general ON ((general.id = description.general_id)))
     LEFT JOIN races race ON ((race.id = general.race_id)))
     LEFT JOIN hands hand ON ((hand.id = description.hand_id)))
     LEFT JOIN hand_hair ON ((hand_hair.id = hand.hand_hair_id)))
     LEFT JOIN nails nail ON ((nail.id = hand.nail_id)))
     LEFT JOIN teeth tooth ON ((tooth.id = face.tooth_id)))
     LEFT JOIN eyebrows eyebrow ON ((eyebrow.id = face.eyebrow_id)))
     LEFT JOIN eyes left_eye ON ((left_eye.id = face.left_eye_id)))
     LEFT JOIN eyes right_eye ON ((right_eye.id = face.right_eye_id)))
     LEFT JOIN colors body_skin_color ON ((body_skin_color.id = body.skin_color_id)))
     LEFT JOIN colors left_eye_color ON ((left_eye_color.id = left_eye.color_id)))
     LEFT JOIN colors right_eye_color ON ((right_eye_color.id = left_eye.color_id)))
     LEFT JOIN colors beard_color ON ((beard_color.id = beard.color_id)))
     LEFT JOIN colors hair_color ON ((hair_color.id = hair.color_id)))
     LEFT JOIN colors eyebrow_color ON ((eyebrow_color.id = eyebrow.color_id)))
     LEFT JOIN colors hand_hair_color ON ((hand_hair_color.id = hand_hair.color_id)))
     LEFT JOIN colors nail_color ON ((nail_color.id = nail.color_id)));


ALTER TABLE complete_descriptions OWNER TO postgres;

--
-- Name: demands; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE demands (
    id integer NOT NULL,
    seeker_id integer NOT NULL,
    description_id integer NOT NULL,
    created_at timestamp with time zone NOT NULL,
    location_id integer NOT NULL
);


ALTER TABLE demands OWNER TO postgres;

--
-- Name: printed_descriptions; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW printed_descriptions AS
 SELECT complete_descriptions.id,
    ROW((complete_descriptions.general).id, (complete_descriptions.general).gender, (complete_descriptions.general).race_id, (complete_descriptions.general).birth_year, (complete_descriptions.general).firstname, (complete_descriptions.general).lastname)::general AS general,
    (complete_descriptions.general).birth_year AS general_birth_year,
    (complete_descriptions.general).race_id AS general_race_id,
    (complete_descriptions.general).firstname AS general_firstname,
    (complete_descriptions.general).lastname AS general_lastname,
    (complete_descriptions.general).gender AS general_gender,
    complete_descriptions.race AS general_race,
    complete_descriptions.body,
    complete_descriptions.body_build,
    ROW((complete_descriptions.body_skin_color).id, (complete_descriptions.body_skin_color).name, (complete_descriptions.body_skin_color).hex)::printed_color AS body_skin_color,
    ROW((complete_descriptions.nail_color).id, (complete_descriptions.nail_color).name, (complete_descriptions.nail_color).hex)::printed_color AS hands_nails_color,
    (complete_descriptions.face).freckles AS face_freckles,
    (complete_descriptions.face).care AS face_care,
    ROW((complete_descriptions.beard).id, (complete_descriptions.beard).color_id, suited_length((complete_descriptions.beard).length), (complete_descriptions.beard).style)::beards AS face_beard,
    ROW((complete_descriptions.beard_color).id, (complete_descriptions.beard_color).name, (complete_descriptions.beard_color).hex)::printed_color AS face_beard_color,
    complete_descriptions.eyebrow AS face_eyebrow,
    (complete_descriptions.face).shape AS face_shape,
    complete_descriptions.tooth AS face_tooth,
    complete_descriptions.left_eye AS face_left_eye,
    complete_descriptions.right_eye AS face_right_eye,
    ROW((complete_descriptions.left_eye_color).id, (complete_descriptions.left_eye_color).name, (complete_descriptions.left_eye_color).hex)::printed_color AS left_eye_color,
    ROW((complete_descriptions.right_eye_color).id, (complete_descriptions.right_eye_color).name, (complete_descriptions.right_eye_color).hex)::printed_color AS right_eye_color,
    ROW((complete_descriptions.nail).id, (complete_descriptions.nail).color_id, suited_length((complete_descriptions.nail).length), (complete_descriptions.nail).care)::nails AS hands_nails,
    ROW((complete_descriptions.eyebrow_color).id, (complete_descriptions.eyebrow_color).name, (complete_descriptions.eyebrow_color).hex)::printed_color AS face_eyebrow_color,
    (complete_descriptions.hand).vein_visibility AS hands_vein_visibility,
    (complete_descriptions.hand).joint_visibility AS hands_joint_visibility,
    (complete_descriptions.hand).care AS hands_care,
    ROW((complete_descriptions.hand_hair_color).id, (complete_descriptions.hand_hair_color).name, (complete_descriptions.hand_hair_color).hex)::printed_color AS hands_hair_color,
    complete_descriptions.hand_hair AS hands_hair,
    ROW((complete_descriptions.hair_color).id, (complete_descriptions.hair_color).name, (complete_descriptions.hair_color).hex)::printed_color AS hair_color,
    ROW((complete_descriptions.hair).id, (complete_descriptions.hair).style, (complete_descriptions.hair).color_id, suited_length((complete_descriptions.hair).length), (complete_descriptions.hair).highlights, (complete_descriptions.hair).roots, (complete_descriptions.hair).nature)::hair AS hair
   FROM complete_descriptions;


ALTER TABLE printed_descriptions OWNER TO postgres;

--
-- Name: flat_descriptions; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW flat_descriptions AS
 SELECT printed_descriptions.id,
    printed_descriptions.general_birth_year,
    printed_descriptions.general_race,
    printed_descriptions.general_firstname,
    printed_descriptions.general_lastname,
    printed_descriptions.general_gender,
    (printed_descriptions.body).weight AS body_weight,
    (printed_descriptions.body).height AS body_height,
    printed_descriptions.body_skin_color,
    printed_descriptions.body_build,
    (printed_descriptions.hair).color_id AS hair_color_id,
    (printed_descriptions.hair).style AS hair_style,
    (printed_descriptions.hair).length AS hair_length,
    (printed_descriptions.hair).highlights AS hair_highlights,
    (printed_descriptions.hair).roots AS hair_roots,
    (printed_descriptions.hair).nature AS hair_nature,
    (printed_descriptions.hands_nails).color_id AS hands_nails_color_id,
    (printed_descriptions.hands_nails).care AS hands_nails_care,
    (printed_descriptions.face_beard).color_id AS face_beard_color_id,
    (printed_descriptions.face_beard).length AS face_beard_length,
    (printed_descriptions.face_beard).style AS face_beard_style,
    (printed_descriptions.face_eyebrow).color_id AS face_eyebrow_color_id,
    (printed_descriptions.face_eyebrow).care AS face_eyebrow_care,
    (printed_descriptions.face_tooth).care AS face_tooth_care,
    (printed_descriptions.face_tooth).braces AS face_tooth_braces,
    (printed_descriptions.face_left_eye).color_id AS face_left_eye_color_id,
    (printed_descriptions.face_left_eye).lenses AS face_left_eye_lenses,
    (printed_descriptions.face_right_eye).color_id AS face_right_eye_color_id,
    (printed_descriptions.face_right_eye).lenses AS face_right_eye_lenses,
    (printed_descriptions.hands_hair).color_id AS hands_hair_color_id,
    (printed_descriptions.hands_hair).amount AS hands_hair_amount,
    printed_descriptions.hands_nails_color,
    printed_descriptions.face_freckles,
    printed_descriptions.face_care,
    printed_descriptions.face_beard_color,
    printed_descriptions.face_eyebrow,
    printed_descriptions.face_shape,
    printed_descriptions.face_tooth,
    printed_descriptions.face_left_eye,
    printed_descriptions.face_right_eye,
    printed_descriptions.left_eye_color,
    printed_descriptions.right_eye_color,
    (printed_descriptions.hands_nails).length AS hands_nails_length,
    printed_descriptions.face_eyebrow_color,
    printed_descriptions.hands_vein_visibility,
    printed_descriptions.hands_joint_visibility,
    printed_descriptions.hands_care,
    printed_descriptions.hands_hair_color,
    printed_descriptions.hair_color
   FROM printed_descriptions;


ALTER TABLE flat_descriptions OWNER TO postgres;

--
-- Name: locations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE locations (
    id integer NOT NULL,
    coordinates point NOT NULL,
    place text,
    met_at tstzrange NOT NULL
);


ALTER TABLE locations OWNER TO postgres;

--
-- Name: collective_demands; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW collective_demands AS
 SELECT printed_description.general_birth_year,
    year_to_age(printed_description.general_birth_year, location.met_at) AS general_age,
    printed_description.body_build,
    printed_description.face_shape,
    printed_description.face_eyebrow_color,
    printed_description.hands_vein_visibility,
    printed_description.hands_joint_visibility,
    printed_description.hands_care,
    printed_description.hands_hair_color,
    printed_description.hands_hair,
    printed_description.hair_color,
    (printed_description.body).build_id AS body_build_id,
    (printed_description.body).skin_color_id AS body_skin_color_id,
    printed_description.general_race_id,
    flat_description.face_beard_color,
    flat_description.general_firstname,
    flat_description.general_lastname,
    flat_description.general_gender,
    flat_description.body_weight,
    flat_description.body_height,
    flat_description.hands_nails_color_id,
    flat_description.hands_nails_length,
    flat_description.hands_nails_care,
    flat_description.face_beard_color_id,
    flat_description.face_beard_length,
    flat_description.face_beard_style,
    flat_description.face_eyebrow_color_id,
    flat_description.face_eyebrow_care,
    flat_description.face_tooth_care,
    flat_description.face_tooth_braces,
    flat_description.face_left_eye_color_id,
    flat_description.face_left_eye_lenses,
    flat_description.face_right_eye_color_id,
    flat_description.face_right_eye_lenses,
    flat_description.hands_hair_color_id,
    flat_description.hands_hair_amount,
    flat_description.hands_nails_color,
    flat_description.body_skin_color,
    flat_description.general_race,
    flat_description.face_freckles,
    flat_description.face_care,
    flat_description.face_left_eye,
    flat_description.face_right_eye,
    flat_description.left_eye_color,
    flat_description.right_eye_color,
    flat_description.hair_color_id,
    flat_description.hair_style,
    flat_description.hair_length,
    flat_description.hair_highlights,
    flat_description.hair_roots,
    flat_description.hair_nature,
    location.met_at AS location_met_at,
    location.coordinates AS location_coordinates,
    demand.id,
    demand.seeker_id,
    demand.created_at
   FROM (((demands demand
     JOIN printed_descriptions printed_description ON ((demand.description_id = printed_description.id)))
     JOIN flat_descriptions flat_description ON ((flat_description.id = printed_description.id)))
     JOIN locations location ON ((location.id = demand.location_id)));


ALTER TABLE collective_demands OWNER TO postgres;

--
-- Name: collective_evolutions; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW collective_evolutions AS
 SELECT printed_description.general_birth_year,
    year_to_age(printed_description.general_birth_year, evolution.evolved_at) AS general_age,
    printed_description.body_build,
    printed_description.face_shape,
    printed_description.face_eyebrow_color,
    printed_description.hands_vein_visibility,
    printed_description.hands_joint_visibility,
    printed_description.hands_care,
    printed_description.hands_hair_color,
    printed_description.hands_hair,
    printed_description.hair_color,
    (printed_description.body).build_id AS body_build_id,
    (printed_description.body).skin_color_id AS body_skin_color_id,
    printed_description.general_race_id,
    flat_description.face_beard_color,
    flat_description.general_firstname,
    flat_description.general_lastname,
    flat_description.general_gender,
    flat_description.body_weight,
    flat_description.body_height,
    flat_description.hands_nails_color_id,
    flat_description.hands_nails_length,
    flat_description.hands_nails_care,
    flat_description.face_beard_color_id,
    flat_description.face_beard_length,
    flat_description.face_beard_style,
    flat_description.face_eyebrow_color_id,
    flat_description.face_eyebrow_care,
    flat_description.face_tooth_care,
    flat_description.face_tooth_braces,
    flat_description.face_left_eye_color_id,
    flat_description.face_left_eye_lenses,
    flat_description.face_right_eye_color_id,
    flat_description.face_right_eye_lenses,
    flat_description.hands_hair_color_id,
    flat_description.hands_hair_amount,
    flat_description.hands_nails_color,
    flat_description.body_skin_color,
    flat_description.general_race,
    flat_description.face_freckles,
    flat_description.face_care,
    flat_description.face_left_eye,
    flat_description.face_right_eye,
    flat_description.left_eye_color,
    flat_description.right_eye_color,
    flat_description.hair_color_id,
    flat_description.hair_style,
    flat_description.hair_length,
    flat_description.hair_highlights,
    flat_description.hair_roots,
    flat_description.hair_nature,
    evolution.id,
    evolution.seeker_id,
    evolution.evolved_at
   FROM ((evolutions evolution
     JOIN printed_descriptions printed_description ON ((evolution.description_id = printed_description.id)))
     JOIN flat_descriptions flat_description ON ((flat_description.id = printed_description.id)));


ALTER TABLE collective_evolutions OWNER TO postgres;

--
-- Name: colors_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE colors ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME colors_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: demands_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE demands ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME demands_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: description_parts; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW description_parts AS
 SELECT description.id,
    face.beard_id,
    description.body_id,
    description.general_id,
    description.face_id,
    description.hair_id,
    face.eyebrow_id,
    face.left_eye_id,
    face.right_eye_id,
    hand.hand_hair_id,
    description.hand_id,
    demand.location_id,
    hand.nail_id,
    face.tooth_id
   FROM (((((((((((((((demands demand
     RIGHT JOIN descriptions description ON ((description.id = demand.description_id)))
     LEFT JOIN hair ON ((hair.id = description.hair_id)))
     LEFT JOIN bodies body ON ((body.id = description.body_id)))
     LEFT JOIN body_builds body_build ON ((body_build.id = body.build_id)))
     LEFT JOIN faces face ON ((face.id = description.face_id)))
     LEFT JOIN beards beard ON ((beard.id = face.beard_id)))
     LEFT JOIN general ON ((general.id = description.general_id)))
     LEFT JOIN races race ON ((race.id = general.race_id)))
     LEFT JOIN hands hand ON ((hand.id = description.hand_id)))
     LEFT JOIN hand_hair ON ((hand_hair.id = hand.hand_hair_id)))
     LEFT JOIN nails nail ON ((nail.id = hand.nail_id)))
     LEFT JOIN teeth tooth ON ((tooth.id = face.tooth_id)))
     LEFT JOIN eyebrows eyebrow ON ((eyebrow.id = face.eyebrow_id)))
     LEFT JOIN eyes left_eye ON ((left_eye.id = face.left_eye_id)))
     LEFT JOIN eyes right_eye ON ((right_eye.id = face.right_eye_id)));


ALTER TABLE description_parts OWNER TO postgres;

--
-- Name: descriptions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE descriptions ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME descriptions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: evolution_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE evolutions ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME evolution_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: eye_colors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE eye_colors (
    id smallint NOT NULL,
    color_id smallint NOT NULL
);


ALTER TABLE eye_colors OWNER TO postgres;

--
-- Name: eye_colors_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE eye_colors ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME eye_colors_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: eyebrow_colors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE eyebrow_colors (
    id smallint NOT NULL,
    color_id smallint NOT NULL
);


ALTER TABLE eyebrow_colors OWNER TO postgres;

--
-- Name: eyebrow_colors_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE eyebrow_colors ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME eyebrow_colors_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: eyebrows_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE eyebrows ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME eyebrows_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: eyes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE eyes ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME eyes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: faces_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE faces ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME faces_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: general_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE general ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME general_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: hair_colors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE hair_colors (
    id smallint NOT NULL,
    color_id smallint NOT NULL
);


ALTER TABLE hair_colors OWNER TO postgres;

--
-- Name: hair_colors_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE hair_colors ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hair_colors_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: hair_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE hair ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hair_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: hand_hair_colors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE hand_hair_colors (
    id smallint NOT NULL,
    color_id smallint NOT NULL
);


ALTER TABLE hand_hair_colors OWNER TO postgres;

--
-- Name: hand_hair_colors_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE hand_hair_colors ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hand_hair_colors_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: hand_hair_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE hand_hair ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hand_hair_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: hands_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE hands ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME hands_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: locations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE locations ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME locations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: nail_colors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE nail_colors (
    id smallint NOT NULL,
    color_id smallint NOT NULL
);


ALTER TABLE nail_colors OWNER TO postgres;

--
-- Name: nail_colors_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE nail_colors ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME nail_colors_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: nails_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE nails ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME nails_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: races_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE races ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME races_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: seekers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE seekers (
    id integer NOT NULL,
    email citext NOT NULL,
    password text NOT NULL
);


ALTER TABLE seekers OWNER TO postgres;

--
-- Name: seekers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE seekers ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME seekers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: teeth_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE teeth ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME teeth_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


SET search_path = http, pg_catalog;

--
-- Name: etags etags_pkey; Type: CONSTRAINT; Schema: http; Owner: postgres
--

ALTER TABLE ONLY etags
    ADD CONSTRAINT etags_pkey PRIMARY KEY (id);


SET search_path = public, pg_catalog;

--
-- Name: beard_colors beard_colors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY beard_colors
    ADD CONSTRAINT beard_colors_pkey PRIMARY KEY (id);


--
-- Name: beards beards_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY beards
    ADD CONSTRAINT beards_pkey PRIMARY KEY (id);


--
-- Name: bodies bodies_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY bodies
    ADD CONSTRAINT bodies_pkey PRIMARY KEY (id);


--
-- Name: body_builds body_builds_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY body_builds
    ADD CONSTRAINT body_builds_pkey PRIMARY KEY (id);


--
-- Name: colors colors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY colors
    ADD CONSTRAINT colors_pkey PRIMARY KEY (id);


--
-- Name: demands demands_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY demands
    ADD CONSTRAINT demands_pkey PRIMARY KEY (id);


--
-- Name: descriptions descriptions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY descriptions
    ADD CONSTRAINT descriptions_pkey PRIMARY KEY (id);


--
-- Name: evolutions evolution_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY evolutions
    ADD CONSTRAINT evolution_pkey PRIMARY KEY (id);


--
-- Name: eye_colors eye_colors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY eye_colors
    ADD CONSTRAINT eye_colors_pkey PRIMARY KEY (id);


--
-- Name: eyebrow_colors eyebrow_colors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY eyebrow_colors
    ADD CONSTRAINT eyebrow_colors_pkey PRIMARY KEY (id);


--
-- Name: eyebrows eyebrows_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY eyebrows
    ADD CONSTRAINT eyebrows_pkey PRIMARY KEY (id);


--
-- Name: eyes eyes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY eyes
    ADD CONSTRAINT eyes_pkey PRIMARY KEY (id);


--
-- Name: faces faces_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY faces
    ADD CONSTRAINT faces_pkey PRIMARY KEY (id);


--
-- Name: general general_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY general
    ADD CONSTRAINT general_pkey PRIMARY KEY (id);


--
-- Name: hair_colors hair_colors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY hair_colors
    ADD CONSTRAINT hair_colors_pkey PRIMARY KEY (id);


--
-- Name: hair hair_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY hair
    ADD CONSTRAINT hair_pkey PRIMARY KEY (id);


--
-- Name: hand_hair_colors hand_hair_colors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY hand_hair_colors
    ADD CONSTRAINT hand_hair_colors_pkey PRIMARY KEY (id);


--
-- Name: hand_hair hand_hair_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY hand_hair
    ADD CONSTRAINT hand_hair_pkey PRIMARY KEY (id);


--
-- Name: hands hands_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY hands
    ADD CONSTRAINT hands_pkey PRIMARY KEY (id);


--
-- Name: locations locations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY locations
    ADD CONSTRAINT locations_pkey PRIMARY KEY (id);


--
-- Name: nail_colors nail_colors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY nail_colors
    ADD CONSTRAINT nail_colors_pkey PRIMARY KEY (id);


--
-- Name: nails nails_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY nails
    ADD CONSTRAINT nails_pkey PRIMARY KEY (id);


--
-- Name: races races_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY races
    ADD CONSTRAINT races_pkey PRIMARY KEY (id);


--
-- Name: seekers seekers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY seekers
    ADD CONSTRAINT seekers_pkey PRIMARY KEY (id);


--
-- Name: skin_colors skin_colors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY skin_colors
    ADD CONSTRAINT skin_colors_pkey PRIMARY KEY (id);


--
-- Name: teeth teeth_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY teeth
    ADD CONSTRAINT teeth_pkey PRIMARY KEY (id);


SET search_path = http, pg_catalog;

--
-- Name: etags_entity_uindex; Type: INDEX; Schema: http; Owner: postgres
--

CREATE UNIQUE INDEX etags_entity_uindex ON etags USING btree (lower((entity)::text));


--
-- Name: etags_id_uindex; Type: INDEX; Schema: http; Owner: postgres
--

CREATE UNIQUE INDEX etags_id_uindex ON etags USING btree (id);


SET search_path = public, pg_catalog;

--
-- Name: beard_colors_color_id_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX beard_colors_color_id_uindex ON beard_colors USING btree (color_id);


--
-- Name: demands_description_id_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX demands_description_id_uindex ON demands USING btree (description_id);


--
-- Name: demands_location_id_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX demands_location_id_uindex ON demands USING btree (location_id);


--
-- Name: descriptions_body_id_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX descriptions_body_id_uindex ON descriptions USING btree (body_id);


--
-- Name: descriptions_face_id_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX descriptions_face_id_uindex ON descriptions USING btree (face_id);


--
-- Name: descriptions_general_id_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX descriptions_general_id_uindex ON descriptions USING btree (general_id);


--
-- Name: descriptions_hair_id_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX descriptions_hair_id_uindex ON descriptions USING btree (hair_id);


--
-- Name: descriptions_hand_id_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX descriptions_hand_id_uindex ON descriptions USING btree (hand_id);


--
-- Name: evolutions_description_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX evolutions_description_id_index ON evolutions USING btree (description_id);


--
-- Name: evolutions_seeker_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX evolutions_seeker_id_index ON evolutions USING btree (seeker_id);


--
-- Name: eye_colors_color_id_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX eye_colors_color_id_uindex ON eye_colors USING btree (color_id);


--
-- Name: hair_colors_color_id_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX hair_colors_color_id_uindex ON hair_colors USING btree (color_id);


--
-- Name: hand_hair_colors_color_id_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX hand_hair_colors_color_id_uindex ON hand_hair_colors USING btree (color_id);


--
-- Name: hands_id_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX hands_id_uindex ON hands USING btree (id);


--
-- Name: nail_colors_color_id_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX nail_colors_color_id_uindex ON nail_colors USING btree (color_id);


--
-- Name: seekers_email_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX seekers_email_uindex ON seekers USING btree (email);


--
-- Name: skin_colors_color_id_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX skin_colors_color_id_uindex ON skin_colors USING btree (color_id);


--
-- Name: beards beards_row_abiu_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER beards_row_abiu_trigger BEFORE INSERT OR UPDATE ON beards FOR EACH ROW EXECUTE PROCEDURE united_length_trigger();


--
-- Name: collective_demands collective_demands_row_ii_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER collective_demands_row_ii_trigger INSTEAD OF INSERT ON collective_demands FOR EACH ROW EXECUTE PROCEDURE collective_demands_trigger_row_ii();


--
-- Name: collective_demands collective_demands_row_iu_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER collective_demands_row_iu_trigger INSTEAD OF UPDATE ON collective_demands FOR EACH ROW EXECUTE PROCEDURE collective_demands_trigger_row_iu();


--
-- Name: collective_evolutions collective_evolutions_row_ii_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER collective_evolutions_row_ii_trigger INSTEAD OF INSERT ON collective_evolutions FOR EACH ROW EXECUTE PROCEDURE collective_evolutions_trigger_row_ii();


--
-- Name: collective_evolutions collective_evolutions_row_iu_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER collective_evolutions_row_iu_trigger INSTEAD OF UPDATE ON collective_evolutions FOR EACH ROW EXECUTE PROCEDURE collective_evolutions_trigger_row_iu();


--
-- Name: demands demands_row_ad_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER demands_row_ad_trigger AFTER DELETE ON demands FOR EACH ROW EXECUTE PROCEDURE demands_trigger_row_ad();


--
-- Name: demands demands_row_bu_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER demands_row_bu_trigger BEFORE UPDATE OF created_at ON demands FOR EACH ROW EXECUTE PROCEDURE demands_trigger_row_bu();


--
-- Name: evolutions evolutions_row_ad_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER evolutions_row_ad_trigger AFTER DELETE ON evolutions FOR EACH ROW EXECUTE PROCEDURE evolutions_trigger_row_ad();


--
-- Name: evolutions evolutions_row_bd_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER evolutions_row_bd_trigger BEFORE DELETE ON evolutions FOR EACH ROW EXECUTE PROCEDURE evolutions_trigger_row_bd();


--
-- Name: hair hair_row_abiu_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER hair_row_abiu_trigger BEFORE INSERT OR UPDATE ON hair FOR EACH ROW EXECUTE PROCEDURE united_length_trigger();


--
-- Name: nails nails_row_abiu_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER nails_row_abiu_trigger BEFORE INSERT OR UPDATE ON nails FOR EACH ROW EXECUTE PROCEDURE united_length_trigger();


--
-- Name: beard_colors beard_colors_colors_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY beard_colors
    ADD CONSTRAINT beard_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


--
-- Name: beards beards_beard_colors_color_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY beards
    ADD CONSTRAINT beards_beard_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES beard_colors(color_id);


--
-- Name: bodies bodies_body_builds_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY bodies
    ADD CONSTRAINT bodies_body_builds_id_fk FOREIGN KEY (build_id) REFERENCES body_builds(id);


--
-- Name: bodies bodies_skin_colors_color_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY bodies
    ADD CONSTRAINT bodies_skin_colors_color_id_fk FOREIGN KEY (skin_color_id) REFERENCES skin_colors(color_id);


--
-- Name: demands demands_descriptions_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY demands
    ADD CONSTRAINT demands_descriptions_id_fk FOREIGN KEY (description_id) REFERENCES descriptions(id) ON DELETE CASCADE;


--
-- Name: demands demands_locations_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY demands
    ADD CONSTRAINT demands_locations_id_fk FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE;


--
-- Name: descriptions descriptions_bodies_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY descriptions
    ADD CONSTRAINT descriptions_bodies_id_fk FOREIGN KEY (body_id) REFERENCES bodies(id) ON DELETE CASCADE;


--
-- Name: descriptions descriptions_faces_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY descriptions
    ADD CONSTRAINT descriptions_faces_id_fk FOREIGN KEY (face_id) REFERENCES faces(id) ON DELETE CASCADE;


--
-- Name: descriptions descriptions_general_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY descriptions
    ADD CONSTRAINT descriptions_general_id_fk FOREIGN KEY (general_id) REFERENCES general(id) ON DELETE CASCADE;


--
-- Name: descriptions descriptions_hair_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY descriptions
    ADD CONSTRAINT descriptions_hair_id_fk FOREIGN KEY (hair_id) REFERENCES hair(id) ON DELETE CASCADE;


--
-- Name: descriptions descriptions_hands_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY descriptions
    ADD CONSTRAINT descriptions_hands_id_fk FOREIGN KEY (hand_id) REFERENCES hands(id) ON DELETE CASCADE;


--
-- Name: evolutions evolutions_descriptions_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY evolutions
    ADD CONSTRAINT evolutions_descriptions_id_fk FOREIGN KEY (description_id) REFERENCES descriptions(id) ON DELETE CASCADE;


--
-- Name: evolutions evolutions_seekers_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY evolutions
    ADD CONSTRAINT evolutions_seekers_id_fk FOREIGN KEY (seeker_id) REFERENCES seekers(id) ON DELETE CASCADE;


--
-- Name: eye_colors eye_colors_colors_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY eye_colors
    ADD CONSTRAINT eye_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


--
-- Name: eyebrow_colors eyebrow_colors_colors_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY eyebrow_colors
    ADD CONSTRAINT eyebrow_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


--
-- Name: eyes eyes_eye_colors_color_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY eyes
    ADD CONSTRAINT eyes_eye_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES eye_colors(color_id);


--
-- Name: faces faces_beards_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY faces
    ADD CONSTRAINT faces_beards_id_fk FOREIGN KEY (beard_id) REFERENCES beards(id);


--
-- Name: faces faces_eyebrows_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY faces
    ADD CONSTRAINT faces_eyebrows_id_fk FOREIGN KEY (eyebrow_id) REFERENCES eyebrows(id);


--
-- Name: faces faces_eyes_left_id_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY faces
    ADD CONSTRAINT faces_eyes_left_id_id_fk FOREIGN KEY (left_eye_id) REFERENCES eyes(id);


--
-- Name: faces faces_eyes_right_id_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY faces
    ADD CONSTRAINT faces_eyes_right_id_id_fk FOREIGN KEY (right_eye_id) REFERENCES eyes(id);


--
-- Name: faces faces_teeth_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY faces
    ADD CONSTRAINT faces_teeth_id_fk FOREIGN KEY (tooth_id) REFERENCES teeth(id);


--
-- Name: general general_races_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY general
    ADD CONSTRAINT general_races_id_fk FOREIGN KEY (race_id) REFERENCES races(id);


--
-- Name: hair_colors hair_colors_colors_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY hair_colors
    ADD CONSTRAINT hair_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


--
-- Name: hair hair_hair_colors_color_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY hair
    ADD CONSTRAINT hair_hair_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES hair_colors(color_id);


--
-- Name: hand_hair_colors hand_hair_colors_colors_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY hand_hair_colors
    ADD CONSTRAINT hand_hair_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


--
-- Name: hand_hair hand_hair_hand_hair_colors_color_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY hand_hair
    ADD CONSTRAINT hand_hair_hand_hair_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES hand_hair_colors(color_id);


--
-- Name: hands hands_hand_hair_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY hands
    ADD CONSTRAINT hands_hand_hair_id_fk FOREIGN KEY (hand_hair_id) REFERENCES hand_hair(id);


--
-- Name: hands hands_nails_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY hands
    ADD CONSTRAINT hands_nails_id_fk FOREIGN KEY (nail_id) REFERENCES nails(id);


--
-- Name: nail_colors nail_colors_colors_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY nail_colors
    ADD CONSTRAINT nail_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


--
-- Name: nails nails_nail_colors_color_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY nails
    ADD CONSTRAINT nails_nail_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES nail_colors(color_id);


--
-- Name: skin_colors skin_colors_colors_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY skin_colors
    ADD CONSTRAINT skin_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


--
-- PostgreSQL database dump complete
--

