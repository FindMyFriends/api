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
-- Name: genders; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE genders AS ENUM (
    'man',
    'woman'
);


ALTER TYPE genders OWNER TO postgres;

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
			new.general_birth_year,
			new.general_race_id,
			new.general_firstname,
			new.general_lastname,
			new.general_gender,
			new.body_build_id,
			new.body,
			new.body_build,
			new.hand_nail_color,
			new.body_skin_color,
			new.body_skin_color_id,
			new.general_race,
			new.face_freckles,
			new.face_care,
			new.face_beard,
			new.face_beard_color,
			new.face_eyebrow,
			new.face_shape,
			new.face_tooth,
			new.face_left_eye,
			new.face_right_eye,
			new.face_left_eye_color,
			new.face_right_eye_color,
			new.hands_nails,
			new.face_eyebrow_color,
			new.hands_vein_visibility,
			new.hands_joint_visibility,
			new.hands_care,
			new.hand_hair_color,
			new.hand_hair_amount,
			new.hands_hair,
			new.hair_color,
			new.hair_color_id,
			new.hair_style,
			new.hair_length,
			new.hair_highlights,
			new.hair_roots,
			new.hair_nature
		)
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
			new.general_birth_year,
			new.general_race_id,
			new.general_firstname,
			new.general_lastname,
			new.general_gender,
			new.body_build_id,
			new.body,
			new.body_build,
			new.hand_nail_color,
			new.body_skin_color,
			new.body_skin_color_id,
			new.general_race,
			new.face_freckles,
			new.face_care,
			new.face_beard,
			new.face_beard_color,
			new.face_eyebrow,
			new.face_shape,
			new.face_tooth,
			new.face_left_eye,
			new.face_right_eye,
			new.face_left_eye_color,
			new.face_right_eye_color,
			new.hands_nails,
			new.face_eyebrow_color,
			new.hands_vein_visibility,
			new.hands_joint_visibility,
			new.hands_care,
			new.hand_hair_color,
			new.hand_hair_amount,
			new.hands_hair,
			new.hair_color,
			new.hair_color_id,
			new.hair_style,
			new.hair_length,
			new.hair_highlights,
			new.hair_roots,
			new.hair_nature
		)
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
			(
				SELECT birth_year
				FROM base_evolution
				WHERE seeker_id = new.seeker_id
				LIMIT 1
			),
			new.general_race_id,
			new.general_firstname,
			new.general_lastname,
			new.general_gender,
			new.body_build_id,
			new.body,
			new.body_build,
			new.hand_nail_color,
			new.body_skin_color,
			new.body_skin_color_id,
			new.general_race,
			new.face_freckles,
			new.face_care,
			new.face_beard,
			new.face_beard_color,
			new.face_eyebrow,
			new.face_shape,
			new.face_tooth,
			new.face_left_eye,
			new.face_right_eye,
			new.face_left_eye_color,
			new.face_right_eye_color,
			new.hands_nails,
			new.face_eyebrow_color,
			new.hands_vein_visibility,
			new.hands_joint_visibility,
			new.hands_care,
			new.hand_hair_color,
			new.hand_hair_amount,
			new.hands_hair,
			new.hair_color,
			new.hair_color_id,
			new.hair_style,
			new.hair_length,
			new.hair_highlights,
			new.hair_roots,
			new.hair_nature
		)
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
			new.general_birth_year,
			new.general_race_id,
			new.general_firstname,
			new.general_lastname,
			new.general_gender,
			new.body_build_id,
			new.body,
			new.body_build,
			new.hand_nail_color,
			new.body_skin_color,
			new.body_skin_color_id,
			new.general_race,
			new.face_freckles,
			new.face_care,
			new.face_beard,
			new.face_beard_color,
			new.face_eyebrow,
			new.face_shape,
			new.face_tooth,
			new.face_left_eye,
			new.face_right_eye,
			new.face_left_eye_color,
			new.face_right_eye_color,
			new.hands_nails,
			new.face_eyebrow_color,
			new.hands_vein_visibility,
			new.hands_joint_visibility,
			new.hands_care,
			new.hand_hair_color,
			new.hand_hair_amount,
			new.hands_hair,
			new.hair_color,
			new.hair_color_id,
			new.hair_style,
			new.hair_length,
			new.hair_highlights,
			new.hair_roots,
			new.hair_nature
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
-- Name: is_hex_color(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION is_hex_color(color text) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
BEGIN
	RETURN color ~ '[a-f0-9]{6}';
END
$$;


ALTER FUNCTION public.is_hex_color(color text) OWNER TO postgres;

--
-- Name: is_rating(smallint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION is_rating(rating smallint) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
BEGIN
	RETURN rating <= 10 AND rating >= 0;
END
$$;


ALTER FUNCTION public.is_rating(rating smallint) OWNER TO postgres;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: beards; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE beards (
    id integer NOT NULL,
    color_id smallint,
    length smallint,
    style text
);


ALTER TABLE beards OWNER TO postgres;

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
-- Name: body_builds; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE body_builds (
    id smallint NOT NULL,
    value text NOT NULL
);


ALTER TABLE body_builds OWNER TO postgres;

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
-- Name: eyebrows; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE eyebrows (
    id integer NOT NULL,
    color_id smallint,
    care smallint,
    CONSTRAINT eyebrows_care_check CHECK (is_rating(care))
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
    shape text,
    eyebrow_id integer,
    left_eye_id integer,
    right_eye_id integer,
    CONSTRAINT faces_care_check CHECK (is_rating(care))
);


ALTER TABLE faces OWNER TO postgres;

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
-- Name: hair; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE hair (
    id integer NOT NULL,
    style text,
    color_id smallint,
    length smallint,
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
    CONSTRAINT hand_hair_amount_check CHECK (is_rating(amount))
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
    CONSTRAINT hands_care_check CHECK (is_rating(care)),
    CONSTRAINT hands_joint_visibility_check CHECK (is_rating(joint_visibility)),
    CONSTRAINT hands_vein_visibility_check CHECK (is_rating(vein_visibility))
);


ALTER TABLE hands OWNER TO postgres;

--
-- Name: nails; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE nails (
    id integer NOT NULL,
    color_id smallint,
    length smallint,
    care smallint,
    CONSTRAINT nails_care_check CHECK (is_rating(care))
);


ALTER TABLE nails OWNER TO postgres;

--
-- Name: races; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE races (
    id smallint NOT NULL,
    value text NOT NULL
);


ALTER TABLE races OWNER TO postgres;

--
-- Name: teeth; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE teeth (
    id integer NOT NULL,
    care smallint,
    braces boolean,
    CONSTRAINT teeth_care_check CHECK (is_rating(care))
);


ALTER TABLE teeth OWNER TO postgres;

--
-- Name: complete_descriptions; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW complete_descriptions AS
 SELECT description.id,
    general.birth_year AS general_birth_year,
    general.race_id AS general_race_id,
    general.firstname AS general_firstname,
    general.lastname AS general_lastname,
    general.gender AS general_gender,
    body.build_id AS body_build_id,
    body.*::bodies AS body,
    body_build.*::body_builds AS body_build,
    nail_color.*::colors AS hand_nail_color,
    body_skin_color.*::colors AS body_skin_color,
    body.skin_color_id AS body_skin_color_id,
    race.*::races AS general_race,
    face.freckles AS face_freckles,
    face.care AS face_care,
    beard.*::beards AS face_beard,
    beard_color.*::colors AS face_beard_color,
    eyebrow.*::eyebrows AS face_eyebrow,
    face.shape AS face_shape,
    tooth.*::teeth AS face_tooth,
    left_eye.*::eyes AS face_left_eye,
    right_eye.*::eyes AS face_right_eye,
    face_left_eye_color.*::colors AS face_left_eye_color,
    face_right_eye_color.*::colors AS face_right_eye_color,
    nail.*::nails AS hands_nails,
    eyebrow_color.*::colors AS face_eyebrow_color,
    hand.vein_visibility AS hands_vein_visibility,
    hand.joint_visibility AS hands_joint_visibility,
    hand.care AS hands_care,
    hand_hair_color.*::colors AS hand_hair_color,
    hand_hair.amount AS hand_hair_amount,
    hand_hair.*::hand_hair AS hands_hair,
    hair_color.*::colors AS hair_color,
    hair.color_id AS hair_color_id,
    hair.style AS hair_style,
    hair.length AS hair_length,
    hair.highlights AS hair_highlights,
    hair.roots AS hair_roots,
    hair.nature AS hair_nature
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
     LEFT JOIN colors face_left_eye_color ON ((face_left_eye_color.id = left_eye.color_id)))
     LEFT JOIN colors face_right_eye_color ON ((face_right_eye_color.id = left_eye.color_id)))
     LEFT JOIN colors beard_color ON ((beard_color.id = beard.color_id)))
     LEFT JOIN colors hair_color ON ((hair_color.id = hair.color_id)))
     LEFT JOIN colors eyebrow_color ON ((eyebrow_color.id = eyebrow.color_id)))
     LEFT JOIN colors hand_hair_color ON ((hand_hair_color.id = hand_hair.color_id)))
     LEFT JOIN colors nail_color ON ((nail_color.id = nail.color_id)));


ALTER TABLE complete_descriptions OWNER TO postgres;

--
-- Name: inserted_description(complete_descriptions); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION inserted_description(description complete_descriptions) RETURNS integer
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
			(description.face_beard).color_id,
			(description.face_beard).length,
			(description.face_beard).style
		)
		RETURNING id
			INTO v_beard_id;
		INSERT INTO teeth (care, braces) VALUES (
			(description.face_tooth).care,
			(description.face_tooth).braces
		)
		RETURNING id
			INTO v_tooth_id;
		INSERT INTO eyebrows (color_id, care) VALUES (
			(description.face_eyebrow).color_id,
			(description.face_eyebrow).care
		)
		RETURNING id
			INTO v_eyebrow_id;
		INSERT INTO eyes (color_id, lenses) VALUES (
			(description.face_left_eye).color_id,
			(description.face_left_eye).lenses
		)
		RETURNING id
			INTO v_left_eye_id;
		INSERT INTO eyes (color_id, lenses) VALUES (
			(description.face_right_eye).color_id,
			(description.face_right_eye).lenses
		)
		RETURNING id
			INTO v_right_eye_id;
		INSERT INTO nails (color_id, length, care) VALUES (
			(description.hands_nails).color_id,
			(description.hands_nails).LENGTH,
			(description.hands_nails).care
		)
		RETURNING id
			INTO v_hand_nail_id;
		INSERT INTO hand_hair (color_id, amount) VALUES (
			(description.hands_hair).color_id,
			(description.hands_hair).amount
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
			(description.body).build_id,
			(description.body).skin_color_id,
			(description.body).weight,
			(description.body).height
		)
		RETURNING id
			INTO v_body_id;
		INSERT INTO hands (nail_id, care, vein_visibility, joint_visibility, hand_hair_id) VALUES (
			v_hand_nail_id,
			description.hands_care,
			description.hands_vein_visibility,
			description.hands_joint_visibility,
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


ALTER FUNCTION public.inserted_description(description complete_descriptions) OWNER TO postgres;

--
-- Name: range_to_hstore(anyrange); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION range_to_hstore(range anyrange) RETURNS hstore
    LANGUAGE plpgsql IMMUTABLE STRICT
    AS $$
BEGIN
	RETURN hstore(
		ARRAY['from', 'to'],
		(SELECT ARRAY[lower(range)::TEXT, upper(range)::TEXT])
	);
END
$$;


ALTER FUNCTION public.range_to_hstore(range anyrange) OWNER TO postgres;

--
-- Name: to_range(integer, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION to_range(integer, integer) RETURNS int4range
    LANGUAGE plpgsql IMMUTABLE STRICT
    AS $_$
BEGIN
	RETURN int4range($1, $2);
END
$_$;


ALTER FUNCTION public.to_range(integer, integer) OWNER TO postgres;

--
-- Name: to_range(timestamp with time zone, timestamp with time zone); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION to_range(timestamp with time zone, timestamp with time zone) RETURNS tstzrange
    LANGUAGE plpgsql IMMUTABLE STRICT
    AS $_$
BEGIN
	RETURN tstzrange($1, $2);
END
$_$;


ALTER FUNCTION public.to_range(timestamp with time zone, timestamp with time zone) OWNER TO postgres;

--
-- Name: updated_description(complete_descriptions); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION updated_description(description complete_descriptions) RETURNS integer
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

	IF description.face_beard IS NOT NULL THEN
		SELECT id
		FROM beards
		WHERE id = parts.beard_id
			  AND beards.color_id = (description.face_beard).color_id
			  AND beards.length = (description.face_beard).length
			  AND beards.style = (description.face_beard).style
		INTO v_beard_id;
	END IF;
	IF v_beard_id IS NULL THEN
		INSERT INTO beards (color_id, length, style) VALUES (
			(description.face_beard).color_id,
			(description.face_beard).length,
			(description.face_beard).style
		)
		RETURNING id
			INTO v_beard_id;
	END IF;

	IF description.face_tooth IS NOT NULL THEN
		SELECT id
		FROM teeth
		WHERE id = parts.tooth_id
			  AND teeth.care = (description.face_tooth).care
			  AND teeth.braces = (description.face_tooth).braces
		INTO v_tooth_id;
	END IF;
	IF v_tooth_id IS NULL THEN
		INSERT INTO teeth (care, braces) VALUES (
			(description.face_tooth).care,
			(description.face_tooth).braces
		)
		RETURNING id
			INTO v_tooth_id;
	END IF;

	IF description.face_eyebrow IS NOT NULL THEN
		SELECT id
		FROM eyebrows
		WHERE id = parts.eyebrow_id
			  AND eyebrows.care = (description.face_eyebrow).care
			  AND eyebrows.color_id = (description.face_eyebrow).color_id
		INTO v_eyebrow_id;
	END IF;
	IF v_eyebrow_id IS NULL THEN
		INSERT INTO eyebrows (color_id, care) VALUES (
			(description.face_eyebrow).color_id,
			(description.face_eyebrow).care
		)
		RETURNING id
			INTO v_eyebrow_id;
	END IF;

	IF description.face_left_eye IS NOT NULL THEN
		SELECT id
		FROM eyes
		WHERE id = parts.left_eye_id
			  AND eyes.color_id = (description.face_left_eye).color_id
			  AND eyes.lenses = (description.face_left_eye).lenses
		INTO v_left_eye_id;
	END IF;
	IF v_left_eye_id IS NULL THEN
		INSERT INTO eyes (color_id, lenses) VALUES (
			(description.face_left_eye).color_id,
			(description.face_left_eye).lenses
		)
		RETURNING id
			INTO v_left_eye_id;
	END IF;

	IF description.face_right_eye IS NOT NULL THEN
		SELECT id
		FROM eyes
		WHERE id = parts.right_eye_id
			  AND eyes.color_id = (description.face_right_eye).color_id
			  AND eyes.lenses = (description.face_right_eye).lenses
		INTO v_right_eye_id;
	END IF;
	IF v_right_eye_id IS NULL
	THEN
		INSERT INTO eyes (color_id, lenses) VALUES (
			(description.face_right_eye).color_id,
			(description.face_right_eye).lenses
		)
		RETURNING id
			INTO v_right_eye_id;
	END IF;

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
	SET build_id = (description.body).build_id,
		skin_color_id = (description.body).skin_color_id,
		weight = (description.body).weight,
		height = (description.body).height
	WHERE id = parts.body_id;

	IF description.hands_nails IS NOT NULL THEN
		SELECT id
		FROM nails
		WHERE id = parts.nail_id
			  AND nails.color_id = (description.hands_nails).color_id
			  AND nails.length = (description.hands_nails).length
			  AND nails.care = (description.hands_nails).care
		INTO v_hand_nail_id;
	END IF;

	IF v_hand_nail_id IS NULL THEN
		INSERT INTO nails (color_id, length, care) VALUES (
			(description.hands_nails).color_id,
			(description.hands_nails).LENGTH,
			(description.hands_nails).care
		)
		RETURNING id
			INTO v_hand_nail_id;
	END IF;

	IF description.hands_hair IS NOT NULL THEN
		SELECT id
		FROM hand_hair
		WHERE id = parts.hand_hair_id
			  AND hand_hair.color_id = (description.hands_hair).color_id
			  AND hand_hair.amount = (description.hands_hair).amount
		INTO v_hand_hair_id;
	END IF;

	IF v_hand_hair_id IS NULL THEN
		INSERT INTO hand_hair (color_id, amount) VALUES (
			(description.hands_hair).color_id,
			(description.hands_hair).amount
		)
		RETURNING id
			INTO v_hand_hair_id;
	END IF;

	UPDATE hands
	SET nail_id = v_hand_nail_id,
		care = description.hands_care,
		vein_visibility = description.hands_vein_visibility,
		joint_visibility = description.hands_joint_visibility,
		hand_hair_id = v_hand_hair_id
	WHERE id = parts.hand_id;

	RETURN parts.id;
END $$;


ALTER FUNCTION public.updated_description(description complete_descriptions) OWNER TO postgres;

--
-- Name: year_to_age(int4range, timestamp with time zone); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION year_to_age(year int4range, now timestamp with time zone) RETURNS int4range
    LANGUAGE plpgsql IMMUTABLE STRICT
    AS $$
BEGIN
	RETURN int4range(
		(SELECT extract('year' from now) - upper(year))::INTEGER,
		(SELECT extract('year' from now) - lower(year))::INTEGER
	);
END
$$;


ALTER FUNCTION public.year_to_age(year int4range, now timestamp with time zone) OWNER TO postgres;

--
-- Name: year_to_age(int4range, tstzrange); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION year_to_age(year int4range, now tstzrange) RETURNS int4range
    LANGUAGE plpgsql IMMUTABLE STRICT
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
 SELECT complete_description.general_birth_year,
    complete_description.general_race_id,
    complete_description.general_firstname,
    complete_description.general_lastname,
    complete_description.general_gender,
    complete_description.body_build_id,
    complete_description.body,
    complete_description.body_build,
    complete_description.hand_nail_color,
    complete_description.body_skin_color,
    complete_description.body_skin_color_id,
    complete_description.general_race,
    complete_description.face_freckles,
    complete_description.face_care,
    complete_description.face_beard,
    complete_description.face_beard_color,
    complete_description.face_eyebrow,
    complete_description.face_shape,
    complete_description.face_tooth,
    complete_description.face_left_eye,
    complete_description.face_right_eye,
    complete_description.face_left_eye_color,
    complete_description.face_right_eye_color,
    complete_description.hands_nails,
    complete_description.face_eyebrow_color,
    complete_description.hands_vein_visibility,
    complete_description.hands_joint_visibility,
    complete_description.hands_care,
    complete_description.hand_hair_color,
    complete_description.hand_hair_amount,
    complete_description.hands_hair,
    complete_description.hair_color,
    complete_description.hair_color_id,
    complete_description.hair_style,
    complete_description.hair_length,
    complete_description.hair_highlights,
    complete_description.hair_roots,
    complete_description.hair_nature,
    location.met_at AS location_met_at,
    location.coordinates AS location_coordinates,
    range_to_hstore(year_to_age(complete_description.general_birth_year, location.met_at)) AS age,
    range_to_hstore(location.met_at) AS met_at,
    demand.id,
    demand.seeker_id,
    demand.created_at
   FROM ((demands demand
     JOIN complete_descriptions complete_description ON ((demand.description_id = complete_description.id)))
     JOIN locations location ON ((location.id = demand.location_id)));


ALTER TABLE collective_demands OWNER TO postgres;

--
-- Name: collective_evolutions; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW collective_evolutions AS
 SELECT evolution.id,
    evolution.seeker_id,
    evolution.evolved_at,
    complete_description.general_birth_year,
    complete_description.general_race_id,
    complete_description.general_firstname,
    complete_description.general_lastname,
    complete_description.general_gender,
    complete_description.body_build_id,
    complete_description.body,
    complete_description.body_build,
    complete_description.hand_nail_color,
    complete_description.body_skin_color,
    complete_description.body_skin_color_id,
    complete_description.general_race,
    complete_description.face_freckles,
    complete_description.face_care,
    complete_description.face_beard,
    complete_description.face_beard_color,
    complete_description.face_eyebrow,
    complete_description.face_shape,
    complete_description.face_tooth,
    complete_description.face_left_eye,
    complete_description.face_right_eye,
    complete_description.face_left_eye_color,
    complete_description.face_right_eye_color,
    complete_description.hands_nails,
    complete_description.face_eyebrow_color,
    complete_description.hands_vein_visibility,
    complete_description.hands_joint_visibility,
    complete_description.hands_care,
    complete_description.hand_hair_color,
    complete_description.hand_hair_amount,
    complete_description.hands_hair,
    complete_description.hair_color,
    complete_description.hair_color_id,
    complete_description.hair_style,
    complete_description.hair_length,
    complete_description.hair_highlights,
    complete_description.hair_roots,
    complete_description.hair_nature,
    range_to_hstore(year_to_age(complete_description.general_birth_year, evolution.evolved_at)) AS age
   FROM (evolutions evolution
     JOIN complete_descriptions complete_description ON ((evolution.description_id = complete_description.id)));


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
-- Name: hair hair_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY hair
    ADD CONSTRAINT hair_pkey PRIMARY KEY (id);


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
-- Name: hands_id_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX hands_id_uindex ON hands USING btree (id);


--
-- Name: seekers_email_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX seekers_email_uindex ON seekers USING btree (email);


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
-- Name: beards beards_colors_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY beards
    ADD CONSTRAINT beards_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


--
-- Name: bodies bodies_body_builds_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY bodies
    ADD CONSTRAINT bodies_body_builds_id_fk FOREIGN KEY (build_id) REFERENCES body_builds(id);


--
-- Name: bodies bodies_colors_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY bodies
    ADD CONSTRAINT bodies_colors_id_fk FOREIGN KEY (skin_color_id) REFERENCES colors(id);


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
-- Name: eyebrows eyebrows_colors_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY eyebrows
    ADD CONSTRAINT eyebrows_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


--
-- Name: eyes eyes_colors_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY eyes
    ADD CONSTRAINT eyes_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


--
-- Name: faces faces_beards_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY faces
    ADD CONSTRAINT faces_beards_id_fk FOREIGN KEY (beard_id) REFERENCES beards(id);


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
-- Name: hair hair_colors_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY hair
    ADD CONSTRAINT hair_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


--
-- Name: hand_hair hand_hair_colors_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY hand_hair
    ADD CONSTRAINT hand_hair_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


--
-- Name: hands hands_nails_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY hands
    ADD CONSTRAINT hands_nails_id_fk FOREIGN KEY (nail_id) REFERENCES nails(id);


--
-- Name: nails nails_colors_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY nails
    ADD CONSTRAINT nails_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


--
-- PostgreSQL database dump complete
--

