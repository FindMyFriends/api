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
-- Name: care; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE care AS ENUM (
    'low',
    'medium',
    'high'
);


ALTER TYPE care OWNER TO postgres;

--
-- Name: colors; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE colors AS ENUM (
    'blue',
    'black'
);


ALTER TYPE colors OWNER TO postgres;

--
-- Name: eye; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE eye AS (
	color colors,
	lenses boolean
);


ALTER TYPE eye OWNER TO postgres;

--
-- Name: genders; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE genders AS ENUM (
    'man',
    'woman'
);


ALTER TYPE genders OWNER TO postgres;

--
-- Name: glasses; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE glasses AS (
	dioptric boolean,
	style character varying(50),
	color character varying(50),
	uv boolean,
	description text
);


ALTER TYPE glasses OWNER TO postgres;

--
-- Name: hair; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE hair AS (
	style text,
	color colors,
	length integer,
	highlights boolean,
	roots boolean,
	nature boolean
);


ALTER TYPE hair OWNER TO postgres;

--
-- Name: piercing; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE piercing AS (
	place text,
	color colors,
	size integer
);


ALTER TYPE piercing OWNER TO postgres;

--
-- Name: races; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE races AS ENUM (
    'european',
    'asian',
    'other'
);


ALTER TYPE races OWNER TO postgres;

--
-- Name: tooth; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE tooth AS (
	care care,
	braces boolean
);


ALTER TYPE tooth OWNER TO postgres;

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
-- Name: demands_trigger_row_ad(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION demands_trigger_row_ad() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	description RECORD;
BEGIN
	DELETE FROM descriptions
	WHERE id = old.description_id
	RETURNING face_id, general_id, body_id
		INTO description;
	DELETE FROM evolutions WHERE description_id = old.description_id;
	DELETE FROM faces WHERE id = description.face_id;
	DELETE FROM general WHERE id = description.general_id;
	DELETE FROM bodies WHERE id = description.body_id;
	DELETE FROM locations WHERE id = old.location_id;
	RETURN old;
END;
$$;


ALTER FUNCTION public.demands_trigger_row_ad() OWNER TO postgres;

--
-- Name: demands_trigger_row_ai(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION demands_trigger_row_ai() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	INSERT INTO evolutions (seeker_id, description_id, evolved_at) VALUES (
		new.seeker_id,
		new.description_id,
		NOW()
	);
	RETURN new;
END;
$$;


ALTER FUNCTION public.demands_trigger_row_ai() OWNER TO postgres;

--
-- Name: demands_trigger_row_bu(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION demands_trigger_row_bu() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF (old.created_at <> new.created_at) THEN
		RAISE EXCEPTION 'Column created_at is read only';
	END IF;
	RETURN new;
END;
$$;


ALTER FUNCTION public.demands_trigger_row_bu() OWNER TO postgres;

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

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: bodies; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE bodies (
    id integer NOT NULL,
    build text,
    skin text,
    weight integer,
    height integer
);


ALTER TABLE bodies OWNER TO postgres;

--
-- Name: bodies_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE bodies ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME bodies_id_seq
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
-- Name: descriptions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE descriptions (
    id integer NOT NULL,
    general_id integer NOT NULL,
    body_id integer NOT NULL,
    face_id integer NOT NULL
);


ALTER TABLE descriptions OWNER TO postgres;

--
-- Name: faces; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE faces (
    id integer NOT NULL,
    teeth tooth,
    freckles boolean,
    complexion care,
    beard text,
    acne boolean,
    shape text,
    hair hair,
    eyebrow text,
    left_eye eye,
    right_eye eye
);


ALTER TABLE faces OWNER TO postgres;

--
-- Name: general; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE general (
    id integer NOT NULL,
    gender genders NOT NULL,
    race races NOT NULL,
    birth_year int4range NOT NULL,
    firstname character varying(100),
    lastname character varying(100),
    CONSTRAINT check_birth_year_in_range CHECK (birth_year_in_range(birth_year))
);


ALTER TABLE general OWNER TO postgres;

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
 SELECT demands.id,
    demands.seeker_id,
    demands.created_at,
    bodies.build,
    bodies.skin,
    bodies.weight,
    bodies.height,
    faces.acne,
    faces.beard,
    faces.complexion,
    faces.eyebrow,
    faces.freckles,
    faces.hair,
    faces.left_eye,
    faces.right_eye,
    faces.shape,
    faces.teeth,
    range_to_hstore(year_to_age(general.birth_year, locations.met_at)) AS age,
    general.firstname,
    general.lastname,
    general.gender,
    general.race,
    locations.coordinates,
    range_to_hstore(locations.met_at) AS met_at
   FROM (((((demands
     JOIN locations ON ((locations.id = demands.location_id)))
     JOIN descriptions ON ((descriptions.id = demands.description_id)))
     JOIN bodies ON ((bodies.id = descriptions.body_id)))
     JOIN faces ON ((faces.id = descriptions.face_id)))
     JOIN general ON ((general.id = descriptions.general_id)));


ALTER TABLE collective_demands OWNER TO postgres;

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
-- Name: collective_evolutions; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW collective_evolutions AS
 SELECT evolutions.id,
    evolutions.evolved_at,
    evolutions.seeker_id,
    bodies.build,
    bodies.skin,
    bodies.weight,
    bodies.height,
    faces.acne,
    faces.beard,
    faces.complexion,
    faces.eyebrow,
    faces.freckles,
    faces.hair,
    faces.left_eye,
    faces.right_eye,
    faces.shape,
    faces.teeth,
    range_to_hstore(year_to_age(general.birth_year, evolutions.evolved_at)) AS age,
    general.firstname,
    general.lastname,
    general.gender,
    general.race
   FROM ((((evolutions
     JOIN descriptions ON ((descriptions.id = evolutions.description_id)))
     JOIN bodies ON ((bodies.id = descriptions.body_id)))
     JOIN faces ON ((faces.id = descriptions.face_id)))
     JOIN general ON ((general.id = descriptions.general_id)));


ALTER TABLE collective_evolutions OWNER TO postgres;

--
-- Name: demands_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE demands ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME demands_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: descriptions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE descriptions ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
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

ALTER TABLE evolutions ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME evolution_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: faces_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE faces ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
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

ALTER TABLE general ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME general_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: locations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE locations ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME locations_id_seq
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
    password character varying(255) NOT NULL
);


ALTER TABLE seekers OWNER TO postgres;

--
-- Name: seekers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE seekers ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME seekers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: bodies bodies_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY bodies
    ADD CONSTRAINT bodies_pkey PRIMARY KEY (id);


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
-- Name: locations locations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY locations
    ADD CONSTRAINT locations_pkey PRIMARY KEY (id);


--
-- Name: seekers seekers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY seekers
    ADD CONSTRAINT seekers_pkey PRIMARY KEY (id);


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
-- Name: evolutions_description_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX evolutions_description_id_index ON evolutions USING btree (description_id);


--
-- Name: evolutions_seeker_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX evolutions_seeker_id_index ON evolutions USING btree (seeker_id);


--
-- Name: seekers_email_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX seekers_email_uindex ON seekers USING btree (email);


--
-- Name: demands demands_row_ad_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER demands_row_ad_trigger AFTER DELETE ON demands FOR EACH ROW EXECUTE PROCEDURE demands_trigger_row_ad();


--
-- Name: demands demands_row_ai_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER demands_row_ai_trigger AFTER INSERT ON demands FOR EACH ROW EXECUTE PROCEDURE demands_trigger_row_ai();


--
-- Name: demands demands_row_bu_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER demands_row_bu_trigger BEFORE UPDATE ON demands FOR EACH ROW EXECUTE PROCEDURE demands_trigger_row_bu();


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
-- PostgreSQL database dump complete
--

