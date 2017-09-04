--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.4
-- Dumped by pg_dump version 9.6.4

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
    DELETE FROM faces WHERE id = description.face_id;
    DELETE FROM general WHERE id = description.general_id;
    DELETE FROM bodies WHERE id = description.body_id;
  RETURN old;
END;
$$;


ALTER FUNCTION public.demands_trigger_row_ad() OWNER TO postgres;

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

CREATE SEQUENCE bodies_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE bodies_id_seq OWNER TO postgres;

--
-- Name: bodies_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE bodies_id_seq OWNED BY bodies.id;


--
-- Name: demands; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE demands (
    id integer NOT NULL,
    seeker_id integer NOT NULL,
    description_id integer NOT NULL,
    created_at timestamp with time zone NOT NULL
);


ALTER TABLE demands OWNER TO postgres;

--
-- Name: demands_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE demands_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE demands_id_seq OWNER TO postgres;

--
-- Name: demands_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE demands_id_seq OWNED BY demands.id;


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
-- Name: descriptions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE descriptions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE descriptions_id_seq OWNER TO postgres;

--
-- Name: descriptions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE descriptions_id_seq OWNED BY descriptions.id;


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
-- Name: faces_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE faces_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE faces_id_seq OWNER TO postgres;

--
-- Name: faces_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE faces_id_seq OWNED BY faces.id;


--
-- Name: general; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE general (
    id integer NOT NULL,
    gender genders NOT NULL,
    race races NOT NULL,
    age integer NOT NULL,
    firstname character varying(100),
    lastname character varying(100),
    CONSTRAINT general_age_check CHECK ((age > 0))
);


ALTER TABLE general OWNER TO postgres;

--
-- Name: general_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE general_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE general_id_seq OWNER TO postgres;

--
-- Name: general_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE general_id_seq OWNED BY general.id;


--
-- Name: seekers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE seekers (
    id integer NOT NULL,
    email citext NOT NULL,
    password character varying(255) NOT NULL,
    description_id integer NOT NULL
);


ALTER TABLE seekers OWNER TO postgres;

--
-- Name: seekers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE seekers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE seekers_id_seq OWNER TO postgres;

--
-- Name: seekers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE seekers_id_seq OWNED BY seekers.id;


--
-- Name: bodies id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY bodies ALTER COLUMN id SET DEFAULT nextval('bodies_id_seq'::regclass);


--
-- Name: demands id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY demands ALTER COLUMN id SET DEFAULT nextval('demands_id_seq'::regclass);


--
-- Name: descriptions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY descriptions ALTER COLUMN id SET DEFAULT nextval('descriptions_id_seq'::regclass);


--
-- Name: faces id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY faces ALTER COLUMN id SET DEFAULT nextval('faces_id_seq'::regclass);


--
-- Name: general id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY general ALTER COLUMN id SET DEFAULT nextval('general_id_seq'::regclass);


--
-- Name: seekers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY seekers ALTER COLUMN id SET DEFAULT nextval('seekers_id_seq'::regclass);


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
-- Name: seekers seekers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY seekers
    ADD CONSTRAINT seekers_pkey PRIMARY KEY (id);


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
-- Name: seekers_description_id_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX seekers_description_id_uindex ON seekers USING btree (description_id);


--
-- Name: seekers_email_uindex; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX seekers_email_uindex ON seekers USING btree (email);


--
-- Name: demands demands_row_ad_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER demands_row_ad_trigger AFTER DELETE ON demands FOR EACH ROW EXECUTE PROCEDURE demands_trigger_row_ad();


--
-- Name: demands demands_descriptions_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY demands
    ADD CONSTRAINT demands_descriptions_id_fk FOREIGN KEY (description_id) REFERENCES descriptions(id);


--
-- PostgreSQL database dump complete
--

