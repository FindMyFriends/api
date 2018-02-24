SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;
CREATE EXTENSION IF NOT EXISTS citext WITH SCHEMA public;
CREATE EXTENSION IF NOT EXISTS hstore WITH SCHEMA public;
SET search_path = public, pg_catalog;

-- TYPES --
CREATE TYPE timeline_sides AS ENUM (
    'exactly',
    'sooner',
    'later',
    'sooner or later'
);

CREATE TYPE approximate_timestamptz AS (
  moment timestamp with time zone,
  timeline_side timeline_sides,
  approximation interval
);

CREATE TYPE breast_sizes AS ENUM (
    'A',
    'B',
    'C',
    'D'
);


CREATE TYPE genders AS ENUM (
    'man',
    'woman'
);


CREATE TYPE length_units AS ENUM (
    'mm',
    'cm'
);

CREATE TYPE length AS (
  value numeric,
  unit length_units
);


CREATE TYPE mass_units AS ENUM (
    'kg'
);


CREATE TYPE mass AS (
  value numeric,
  unit mass_units
);

CREATE TYPE flat_description AS (
  id integer,
  general_gender genders,
  general_ethnic_group_id smallint,
  general_birth_year int4range,
  general_firstname text,
  general_lastname text,
  hair_style_id smallint,
  hair_color_id smallint,
  hair_length length,
  hair_highlights boolean,
  hair_roots boolean,
  hair_nature boolean,
  body_build_id smallint,
  body_weight mass,
  body_height length,
  body_breast_size breast_sizes,
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
  face_shape_id smallint,
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

-----



-- FUNCTIONS --
CREATE FUNCTION validate_approximate_timestamptz(approximate_timestamptz) RETURNS boolean
LANGUAGE plpgsql IMMUTABLE
AS $$
BEGIN
  IF $1.timeline_side = 'exactly'::timeline_sides AND $1.approximation IS NOT NULL THEN
    RAISE EXCEPTION '"Exactly" timeline_side can not have approximation';
  END IF;
  RETURN TRUE;
END
$$;

CREATE FUNCTION validate_approximate_max_interval(approximate_timestamptz) RETURNS boolean
LANGUAGE plpgsql IMMUTABLE
AS $$
BEGIN
  IF $1.approximation > '2 days'::interval THEN
    RAISE EXCEPTION 'Overstepped maximum of 2 days';
  END IF;
  RETURN TRUE;
END
$$;

CREATE FUNCTION validate_length(length) RETURNS boolean
    LANGUAGE plpgsql IMMUTABLE
    AS $$
BEGIN
  IF ($1.value IS NULL AND $1.unit IS NULL) OR ($1.value IS NOT NULL AND $1.unit IS NOT NULL) THEN
    RETURN TRUE;
  ELSIF $1.value IS NULL THEN
    RAISE EXCEPTION 'Length with unit must contain value';
  ELSIF $1.unit IS NULL THEN
    RAISE EXCEPTION 'Length with value must contain unit';
  END IF;
END
$$;

CREATE FUNCTION validate_mass(mass) RETURNS boolean
LANGUAGE plpgsql IMMUTABLE
AS $$
BEGIN
  IF ($1.value IS NULL AND $1.unit IS NULL) OR ($1.value IS NOT NULL AND $1.unit IS NOT NULL) THEN
    RETURN TRUE;
  ELSIF $1.value IS NULL THEN
    RAISE EXCEPTION 'Mass with unit must contain value';
  ELSIF $1.unit IS NULL THEN
    RAISE EXCEPTION 'Mass with value must contain unit';
  END IF;
END
$$;

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

CREATE FUNCTION birth_year_in_range(int4range) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
BEGIN
  RETURN $1 <@ int4range(1850, date_part('year', CURRENT_DATE)::INTEGER);
END
$$;


CREATE FUNCTION is_hex(text) RETURNS boolean
LANGUAGE plpgsql
AS $$
BEGIN
  RETURN $1 ~ '^#[a-f0-9]{6}';
END
$$;


CREATE FUNCTION is_rating(integer) RETURNS boolean
LANGUAGE plpgsql
AS $$
BEGIN
  RETURN int4range(0, 10, '[]') @> $1;
END
$$;

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

CREATE FUNCTION united_length_trigger() RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
  new."length" = united_length(new."length");
  RETURN new;
END;
$$;

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

CREATE FUNCTION deleted_description(v_description_id integer) RETURNS integer
LANGUAGE plpgsql
AS $$
DECLARE
  description RECORD;
  hand RECORD;
BEGIN
  DELETE FROM descriptions
  WHERE id = v_description_id
  RETURNING face_id, general_id, body_id, hand_id, hair_id, beard_id, eyebrow_id, tooth_id, left_eye_id, right_eye_id
    INTO description;

  DELETE FROM faces
  WHERE id = description.face_id;

  DELETE FROM teeth WHERE id = description.tooth_id;
  DELETE FROM beards WHERE id = description.beard_id;
  DELETE FROM eyes WHERE id IN(description.left_eye_id, description.right_eye_id);
  DELETE FROM eyebrows WHERE id = description.eyebrow_id;

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
  INSERT INTO general (gender, ethnic_group_id, birth_year, firstname, lastname) VALUES (
    description.general_gender,
    description.general_ethnic_group_id,
    description.general_birth_year,
    description.general_firstname,
    description.general_lastname
  )
  RETURNING id
    INTO v_general_id;
  INSERT INTO hair (style_id, color_id, length, highlights, roots, nature) VALUES (
    description.hair_style_id,
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
  INSERT INTO faces (freckles, care, shape_id) VALUES (
    description.face_freckles,
    description.face_care,
    description.face_shape_id
  )
  RETURNING id
    INTO v_face_id;
  INSERT INTO bodies (build_id, weight, height, breast_size) VALUES (
    description.body_build_id,
    description.body_weight,
    description.body_height,
    description.body_breast_size
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
  INSERT INTO descriptions (general_id, body_id, face_id, hand_id, hair_id, beard_id, eyebrow_id, tooth_id, left_eye_id, right_eye_id) VALUES (
    v_general_id,
    v_body_id,
    v_face_id,
    v_hand_id,
    v_hair_id,
    v_beard_id,
    v_eyebrow_id,
    v_tooth_id,
    v_left_eye_id,
    v_right_eye_id
  ) RETURNING id INTO v_description_id;
  RETURN v_description_id;
END $$;

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
    ethnic_group_id = description.general_ethnic_group_id,
    birth_year = description.general_birth_year,
    firstname = description.general_firstname,
    lastname = description.general_lastname
  WHERE id = parts.general_id;
  UPDATE hair
  SET style_id = description.hair_style_id,
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
  SET freckles = description.face_freckles,
    care = description.face_care,
    shape_id = description.face_shape_id
  WHERE id = parts.face_id;

  UPDATE bodies
  SET build_id = description.body_build_id,
    weight = description.body_weight,
    height = description.body_height,
    breast_size = description.body_breast_size
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

  UPDATE descriptions
  SET beard_id = v_beard_id,
    eyebrow_id = v_eyebrow_id,
    tooth_id = v_tooth_id,
    left_eye_id = v_left_eye_id,
    right_eye_id = v_right_eye_id
  WHERE id = description.id;

  RETURN parts.id;
END $$;
-----

-- TABLES --
CREATE TABLE face_shapes (
  id smallint NOT NULL,
  name text NOT NULL
);
ALTER TABLE face_shapes ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME face_shapes_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY face_shapes ADD CONSTRAINT face_shapes_pkey PRIMARY KEY(id);

CREATE TABLE colors (
  id smallint NOT NULL,
  name text NOT NULL,
  hex text NOT NULL,
  CONSTRAINT colors_hex_check CHECK ((is_hex(hex) AND (lower(hex) = hex)))
);
ALTER TABLE colors ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME colors_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY colors ADD CONSTRAINT colors_pkey PRIMARY KEY(id);


CREATE TABLE body_builds (
  id smallint NOT NULL,
  name text NOT NULL
);
ALTER TABLE body_builds ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME body_builds_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY body_builds ADD CONSTRAINT body_builds_pkey PRIMARY KEY(id);


CREATE TABLE ethnic_groups (
  id smallint NOT NULL,
  name text NOT NULL
);
ALTER TABLE ethnic_groups ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME ethnic_groups_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY ethnic_groups ADD CONSTRAINT ethnic_groups_pkey PRIMARY KEY(id);


CREATE TABLE general (
  id integer NOT NULL,
  gender genders NOT NULL,
  ethnic_group_id smallint NOT NULL,
  birth_year int4range NOT NULL,
  firstname text,
  lastname text,
  CONSTRAINT general_birth_year_check CHECK (birth_year_in_range(birth_year))
);
ALTER TABLE general ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME general_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY general ADD CONSTRAINT general_pkey PRIMARY KEY(id);
ALTER TABLE ONLY general ADD CONSTRAINT general_ethnic_groups_id_fk FOREIGN KEY (ethnic_group_id) REFERENCES ethnic_groups(id);


CREATE TABLE beard_colors (
  id smallint NOT NULL,
  color_id smallint NOT NULL
);
ALTER TABLE beard_colors ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME beard_colors_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
CREATE UNIQUE INDEX beard_colors_color_id_uindex ON beard_colors USING btree (color_id);
ALTER TABLE ONLY beard_colors ADD CONSTRAINT beard_colors_pkey PRIMARY KEY(id);
ALTER TABLE ONLY beard_colors ADD CONSTRAINT beard_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);



CREATE TABLE beards (
  id integer NOT NULL,
  color_id smallint,
  length length,
  style text,
  CONSTRAINT beards_length_check CHECK (validate_length(length))
);
ALTER TABLE beards ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME beards_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY beards ADD CONSTRAINT beards_pkey PRIMARY KEY(id);
ALTER TABLE ONLY beards ADD CONSTRAINT beards_beard_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES beard_colors(color_id);
CREATE TRIGGER beards_row_abiu_trigger BEFORE INSERT OR UPDATE ON beards FOR EACH ROW EXECUTE PROCEDURE united_length_trigger();


CREATE TABLE bodies (
  id integer NOT NULL,
  build_id smallint,
  weight mass,
  height length,
  breast_size breast_sizes,
  CONSTRAINT bodies_height_check CHECK (validate_length(height)),
  CONSTRAINT bodies_weight_check CHECK (validate_mass(weight))
);
ALTER TABLE bodies ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME bodies_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY bodies ADD CONSTRAINT bodies_pkey PRIMARY KEY(id);
ALTER TABLE ONLY bodies ADD CONSTRAINT bodies_body_builds_id_fk FOREIGN KEY (build_id) REFERENCES body_builds(id);


CREATE TABLE eyebrows (
  id integer NOT NULL,
  color_id smallint,
  care smallint,
  CONSTRAINT eyebrows_care_check CHECK (is_rating((care)::integer))
);
ALTER TABLE eyebrows ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME eyebrows_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY eyebrows ADD CONSTRAINT eyebrows_pkey PRIMARY KEY(id);

CREATE TABLE eye_colors (
  id smallint NOT NULL,
  color_id smallint NOT NULL
);
ALTER TABLE eye_colors ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME eye_colors_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY eye_colors ADD CONSTRAINT eye_colors_pkey PRIMARY KEY(id);
CREATE UNIQUE INDEX eye_colors_color_id_uindex ON eye_colors USING btree (color_id);
ALTER TABLE ONLY eye_colors ADD CONSTRAINT eye_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


CREATE TABLE eyes (
  id integer NOT NULL,
  color_id smallint,
  lenses boolean
);
ALTER TABLE eyes ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME eyes_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY eyes ADD CONSTRAINT eyes_pkey PRIMARY KEY(id);
ALTER TABLE ONLY eyes ADD CONSTRAINT eyes_eye_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES eye_colors(color_id);


CREATE TABLE faces (
  id integer NOT NULL,
  freckles boolean,
  care smallint,
  shape_id smallint,
  CONSTRAINT faces_care_check CHECK (is_rating((care)::integer))
);
ALTER TABLE faces ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME faces_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY faces ADD CONSTRAINT faces_pkey PRIMARY KEY(id);
ALTER TABLE ONLY faces ADD CONSTRAINT faces_face_shapes_id_fk FOREIGN KEY (shape_id) REFERENCES face_shapes(id);


CREATE TABLE hair_styles (
  id smallint NOT NULL,
  name text NOT NULL
);
ALTER TABLE hair_styles ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME hair_styles_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY hair_styles ADD CONSTRAINT hair_styles_pkey PRIMARY KEY(id);


CREATE TABLE hair_colors (
  id smallint NOT NULL,
  color_id smallint NOT NULL
);
ALTER TABLE hair_colors ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME hair_colors_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY hair_colors ADD CONSTRAINT hair_colors_pkey PRIMARY KEY(id);
CREATE UNIQUE INDEX hair_colors_color_id_uindex ON hair_colors USING btree (color_id);
ALTER TABLE ONLY hair_colors ADD CONSTRAINT hair_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


CREATE TABLE hair (
  id integer NOT NULL,
  style_id smallint,
  color_id smallint,
  length length,
  highlights boolean,
  roots boolean,
  nature boolean,
  CONSTRAINT hair_length_check CHECK (validate_length(length))
);
ALTER TABLE hair ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME hair_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY hair ADD CONSTRAINT hair_pkey PRIMARY KEY(id);
ALTER TABLE ONLY hair ADD CONSTRAINT hair_hair_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES hair_colors(color_id);
ALTER TABLE ONLY hair ADD CONSTRAINT hair_hair_styles_id_fk FOREIGN KEY (style_id) REFERENCES hair_styles(id);
CREATE TRIGGER hair_row_abiu_trigger BEFORE INSERT OR UPDATE ON hair FOR EACH ROW EXECUTE PROCEDURE united_length_trigger();


CREATE TABLE hand_hair_colors (
  id smallint NOT NULL,
  color_id smallint NOT NULL
);
ALTER TABLE hand_hair_colors ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME hand_hair_colors_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY hand_hair_colors ADD CONSTRAINT hand_hair_colors_pkey PRIMARY KEY(id);
CREATE UNIQUE INDEX hand_hair_colors_color_id_uindex ON hand_hair_colors USING btree (color_id);
ALTER TABLE ONLY hand_hair_colors ADD CONSTRAINT hand_hair_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


CREATE TABLE hand_hair (
  id integer NOT NULL,
  color_id smallint,
  amount smallint,
  CONSTRAINT hand_hair_amount_check CHECK (is_rating((amount)::integer))
);
ALTER TABLE hand_hair ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME hand_hair_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY hand_hair ADD CONSTRAINT hand_hair_pkey PRIMARY KEY(id);
ALTER TABLE ONLY hand_hair ADD CONSTRAINT hand_hair_hand_hair_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES hand_hair_colors(color_id);


CREATE TABLE nail_colors (
  id smallint NOT NULL,
  color_id smallint NOT NULL
);
ALTER TABLE nail_colors ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME nail_colors_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY nail_colors ADD CONSTRAINT nail_colors_pkey PRIMARY KEY(id);
CREATE UNIQUE INDEX nail_colors_color_id_uindex ON nail_colors USING btree (color_id);
ALTER TABLE ONLY nail_colors ADD CONSTRAINT nail_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);


CREATE TABLE nails (
  id integer NOT NULL,
  color_id smallint,
  length length,
  care smallint,
  CONSTRAINT nails_care_check CHECK (is_rating((care)::integer)),
  CONSTRAINT nails_length_check CHECK (validate_length(length))
);
ALTER TABLE nails ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME nails_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY nails ADD CONSTRAINT nails_pkey PRIMARY KEY(id);
CREATE TRIGGER nails_row_abiu_trigger BEFORE INSERT OR UPDATE ON nails FOR EACH ROW EXECUTE PROCEDURE united_length_trigger();
ALTER TABLE ONLY nails ADD CONSTRAINT nails_nail_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES nail_colors(color_id);


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
ALTER TABLE hands ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME hands_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY hands ADD CONSTRAINT hands_pkey PRIMARY KEY(id);
CREATE UNIQUE INDEX hands_id_uindex ON hands USING btree (id);
ALTER TABLE ONLY hands ADD CONSTRAINT hands_hand_hair_id_fk FOREIGN KEY (hand_hair_id) REFERENCES hand_hair(id);
ALTER TABLE ONLY hands ADD CONSTRAINT hands_nails_id_fk FOREIGN KEY (nail_id) REFERENCES nails(id);


CREATE TABLE teeth (
  id integer NOT NULL,
  care smallint,
  braces boolean,
  CONSTRAINT teeth_care_check CHECK (is_rating((care)::integer))
);
ALTER TABLE teeth ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME teeth_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY teeth ADD CONSTRAINT teeth_pkey PRIMARY KEY(id);


CREATE TABLE descriptions (
  id integer NOT NULL,
  general_id integer NOT NULL,
  body_id integer NOT NULL,
  face_id integer NOT NULL,
  hand_id integer NOT NULL,
  hair_id integer NOT NULL,
  beard_id integer NOT NULL,
  eyebrow_id integer NOT NULL,
  tooth_id integer NOT NULL,
  left_eye_id integer NOT NULL,
  right_eye_id integer NOT NULL
);
ALTER TABLE descriptions ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME descriptions_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY descriptions ADD CONSTRAINT descriptions_pkey PRIMARY KEY(id);
CREATE UNIQUE INDEX descriptions_beard_id_uindex ON descriptions USING btree (beard_id);
CREATE UNIQUE INDEX descriptions_body_id_uindex ON descriptions USING btree (body_id);
CREATE UNIQUE INDEX descriptions_eyebrow_id_uindex ON descriptions USING btree (eyebrow_id);
CREATE UNIQUE INDEX descriptions_face_id_uindex ON descriptions USING btree (face_id);
CREATE UNIQUE INDEX descriptions_general_id_uindex ON descriptions USING btree (general_id);
CREATE UNIQUE INDEX descriptions_hair_id_uindex ON descriptions USING btree (hair_id);
CREATE UNIQUE INDEX descriptions_hand_id_uindex ON descriptions USING btree (hand_id);
CREATE UNIQUE INDEX descriptions_left_eye_id_uindex ON descriptions USING btree (left_eye_id);
CREATE UNIQUE INDEX descriptions_right_eye_id_uindex ON descriptions USING btree (right_eye_id);
CREATE UNIQUE INDEX descriptions_tooth_id_uindex ON descriptions USING btree (tooth_id);
ALTER TABLE ONLY descriptions ADD CONSTRAINT descriptions_beards_id_fk FOREIGN KEY (beard_id) REFERENCES beards(id);
ALTER TABLE ONLY descriptions ADD CONSTRAINT descriptions_bodies_id_fk FOREIGN KEY (body_id) REFERENCES bodies(id) ON DELETE CASCADE;
ALTER TABLE ONLY descriptions ADD CONSTRAINT descriptions_eyebrows_id_fk FOREIGN KEY (eyebrow_id) REFERENCES eyebrows(id);
ALTER TABLE ONLY descriptions ADD CONSTRAINT descriptions_eyes_left_id_id_fk FOREIGN KEY (left_eye_id) REFERENCES eyes(id);
ALTER TABLE ONLY descriptions ADD CONSTRAINT descriptions_eyes_right_id_id_fk FOREIGN KEY (right_eye_id) REFERENCES eyes(id);
ALTER TABLE ONLY descriptions ADD CONSTRAINT descriptions_faces_id_fk FOREIGN KEY (face_id) REFERENCES faces(id) ON DELETE CASCADE;
ALTER TABLE ONLY descriptions ADD CONSTRAINT descriptions_general_id_fk FOREIGN KEY (general_id) REFERENCES general(id) ON DELETE CASCADE;
ALTER TABLE ONLY descriptions ADD CONSTRAINT descriptions_hair_id_fk FOREIGN KEY (hair_id) REFERENCES hair(id) ON DELETE CASCADE;
ALTER TABLE ONLY descriptions ADD CONSTRAINT descriptions_hands_id_fk FOREIGN KEY (hand_id) REFERENCES hands(id) ON DELETE CASCADE;
ALTER TABLE ONLY descriptions ADD CONSTRAINT descriptions_teeth_id_fk FOREIGN KEY (tooth_id) REFERENCES teeth(id);


CREATE TABLE seekers (
  id integer NOT NULL,
  email citext NOT NULL,
  password text NOT NULL
);
ALTER TABLE seekers ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME seekers_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY seekers ADD CONSTRAINT seekers_pkey PRIMARY KEY(id);
CREATE UNIQUE INDEX seekers_email_uindex ON seekers USING btree (email);


CREATE TABLE evolutions (
  id integer NOT NULL,
  seeker_id integer NOT NULL,
  description_id integer NOT NULL,
  evolved_at timestamp with time zone NOT NULL
);
ALTER TABLE evolutions ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME evolutions_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY evolutions ADD CONSTRAINT evolutions_pkey PRIMARY KEY(id);
CREATE INDEX evolutions_description_id_index ON evolutions USING btree (description_id);
CREATE INDEX evolutions_seeker_id_index ON evolutions USING btree (seeker_id);
ALTER TABLE ONLY evolutions ADD CONSTRAINT evolutions_descriptions_id_fk FOREIGN KEY (description_id) REFERENCES descriptions(id) ON DELETE CASCADE;
ALTER TABLE ONLY evolutions ADD CONSTRAINT evolutions_seekers_id_fk FOREIGN KEY (seeker_id) REFERENCES seekers(id) ON DELETE CASCADE;

CREATE FUNCTION evolutions_trigger_row_ad() RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
  PERFORM deleted_description(old.description_id);
  RETURN old;
END;
$$;

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

CREATE TRIGGER evolutions_row_ad_trigger AFTER DELETE ON evolutions FOR EACH ROW EXECUTE PROCEDURE evolutions_trigger_row_ad();
CREATE TRIGGER evolutions_row_bd_trigger BEFORE DELETE ON evolutions FOR EACH ROW EXECUTE PROCEDURE evolutions_trigger_row_bd();


CREATE TABLE locations (
  id integer NOT NULL,
  coordinates point NOT NULL,
  place text,
  met_at approximate_timestamptz NOT NULL,
  CONSTRAINT locations_met_at_approximation_mix_check CHECK (validate_approximate_timestamptz(met_at)),
  CONSTRAINT locations_met_at_approximation_max_interval_check CHECK (validate_approximate_max_interval(met_at))
);
ALTER TABLE locations ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME locations_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY locations ADD CONSTRAINT locations_pkey PRIMARY KEY(id);


CREATE TABLE demands (
  id integer NOT NULL,
  seeker_id integer NOT NULL,
  description_id integer NOT NULL,
  created_at timestamp with time zone NOT NULL,
  location_id integer NOT NULL
);
ALTER TABLE demands ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME demands_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY demands ADD CONSTRAINT demands_pkey PRIMARY KEY(id);
CREATE UNIQUE INDEX demands_description_id_uindex ON demands USING btree (description_id);
CREATE UNIQUE INDEX demands_location_id_uindex ON demands USING btree (location_id);
ALTER TABLE ONLY demands ADD CONSTRAINT demands_descriptions_id_fk FOREIGN KEY (description_id) REFERENCES descriptions(id) ON DELETE CASCADE;
ALTER TABLE ONLY demands ADD CONSTRAINT demands_locations_id_fk FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE;

CREATE FUNCTION demands_trigger_row_ad() RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
  PERFORM deleted_description(old.description_id);
  DELETE FROM locations WHERE id = old.location_id;
  RETURN old;
END;
$$;


CREATE FUNCTION demands_trigger_row_bu() RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
  RAISE EXCEPTION 'Column created_at is read only';
  RETURN new;
END;
$$;

CREATE TRIGGER demands_row_ad_trigger AFTER DELETE ON demands FOR EACH ROW EXECUTE PROCEDURE demands_trigger_row_ad();
CREATE TRIGGER demands_row_bu_trigger BEFORE UPDATE OF created_at ON demands FOR EACH ROW EXECUTE PROCEDURE demands_trigger_row_bu();


CREATE TABLE relationships (
  id integer NOT NULL,
  demand_id integer NOT NULL,
  evolution_id integer NOT NULL,
  score numeric NOT NULL
);

ALTER TABLE relationships ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME relationships_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY relationships ADD CONSTRAINT relationships_pkey PRIMARY KEY(id);
CREATE UNIQUE INDEX relationships_demand_id_evolution_id_uindex ON relationships USING btree (demand_id, evolution_id);
ALTER TABLE ONLY relationships ADD CONSTRAINT relationships_demands_demand_id_fk FOREIGN KEY (demand_id) REFERENCES demands(id) ON DELETE CASCADE;
ALTER TABLE ONLY relationships ADD CONSTRAINT relationships_evolutions_evolution_id_fk FOREIGN KEY (evolution_id) REFERENCES evolutions(id) ON DELETE CASCADE;



CREATE TABLE eyebrow_colors (
  id smallint NOT NULL,
  color_id smallint NOT NULL
);
ALTER TABLE eyebrow_colors ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME eyebrow_colors_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY eyebrow_colors ADD CONSTRAINT eyebrow_colors_pkey PRIMARY KEY(id);
ALTER TABLE ONLY eyebrow_colors ADD CONSTRAINT eyebrow_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id);
-----

-- VIEWS --
CREATE VIEW base_evolution AS
  SELECT general.birth_year,
  general.id AS general_id,
  evolutions.seeker_id
  FROM general
  JOIN descriptions ON descriptions.general_id = general.id
  JOIN evolutions ON evolutions.description_id = descriptions.id;

CREATE VIEW complete_descriptions AS
  SELECT description.id,
      ROW(general.*)::general AS general,
      ROW(hair.*)::hair AS hair,
      ROW(hair_style.*)::hair_styles AS hair_style,
      ROW(body.*)::bodies AS body,
      ROW(body_build.*)::body_builds AS body_build,
      ROW(face.*)::faces AS face,
      ROW(beard.*)::beards AS beard,
      ROW(ethnic_group.*)::ethnic_groups AS ethnic_group,
      ROW(hand.*)::hands AS hand,
      ROW(hand_hair.*)::hand_hair AS hand_hair,
      ROW(nail.*)::nails AS nail,
      ROW(tooth.*)::teeth AS tooth,
      ROW(eyebrow.*)::eyebrows AS eyebrow,
      ROW(left_eye.*)::eyes AS left_eye,
      ROW(right_eye.*)::eyes AS right_eye,
      ROW(face_shape.*)::face_shapes AS face_shape
  FROM descriptions description
  LEFT JOIN hair ON hair.id = description.hair_id
  LEFT JOIN bodies body ON body.id = description.body_id
  LEFT JOIN body_builds body_build ON body_build.id = body.build_id
  LEFT JOIN faces face ON face.id = description.face_id
  LEFT JOIN face_shapes face_shape ON face.shape_id = face_shape.id
  LEFT JOIN beards beard ON beard.id = description.beard_id
  LEFT JOIN general ON general.id = description.general_id
  LEFT JOIN ethnic_groups ethnic_group ON ethnic_group.id = general.ethnic_group_id
  LEFT JOIN hair_styles hair_style ON hair_style.id = hair.style_id
  LEFT JOIN hands hand ON hand.id = description.hand_id
  LEFT JOIN hand_hair ON hand_hair.id = hand.hand_hair_id
  LEFT JOIN nails nail ON nail.id = hand.nail_id
  LEFT JOIN teeth tooth ON tooth.id = description.tooth_id
  LEFT JOIN eyebrows eyebrow ON eyebrow.id = description.eyebrow_id
  LEFT JOIN eyes left_eye ON left_eye.id = description.left_eye_id
  LEFT JOIN eyes right_eye ON right_eye.id = description.right_eye_id;


CREATE VIEW printed_descriptions AS
  SELECT complete_descriptions.id,
      ROW((complete_descriptions.general).id, (complete_descriptions.general).gender, (complete_descriptions.general).ethnic_group_id, (complete_descriptions.general).birth_year, (complete_descriptions.general).firstname, (complete_descriptions.general).lastname)::general AS general,
    (complete_descriptions.general).birth_year AS general_birth_year,
    (complete_descriptions.general).ethnic_group_id AS general_ethnic_group_id,
    (complete_descriptions.general).firstname AS general_firstname,
    (complete_descriptions.general).lastname AS general_lastname,
    (complete_descriptions.general).gender AS general_gender,
    complete_descriptions.ethnic_group AS general_ethnic_group,
    complete_descriptions.body,
    complete_descriptions.body_build,
    (complete_descriptions.face).freckles AS face_freckles,
    (complete_descriptions.face).care AS face_care,
      ROW((complete_descriptions.beard).id, (complete_descriptions.beard).color_id, suited_length((complete_descriptions.beard).length), (complete_descriptions.beard).style)::beards AS beard,
    complete_descriptions.eyebrow,
    (complete_descriptions.face).shape_id AS face_shape_id,
    complete_descriptions.face_shape AS face_shape,
    complete_descriptions.tooth,
    complete_descriptions.left_eye,
    complete_descriptions.right_eye,
      ROW((complete_descriptions.nail).id, (complete_descriptions.nail).color_id, suited_length((complete_descriptions.nail).length), (complete_descriptions.nail).care)::nails AS hands_nails,
    (complete_descriptions.hand).vein_visibility AS hands_vein_visibility,
    (complete_descriptions.hand).joint_visibility AS hands_joint_visibility,
    (complete_descriptions.hand).care AS hands_care,
    complete_descriptions.hand_hair AS hands_hair,
      ROW((complete_descriptions.hair).id, (complete_descriptions.hair).style_id, (complete_descriptions.hair).color_id, suited_length((complete_descriptions.hair).length), (complete_descriptions.hair).highlights, (complete_descriptions.hair).roots, (complete_descriptions.hair).nature)::hair AS hair,
    complete_descriptions.hair_style
  FROM complete_descriptions;

CREATE VIEW flat_descriptions AS
  SELECT printed_descriptions.id,
    printed_descriptions.general_birth_year,
    printed_descriptions.general_ethnic_group,
    printed_descriptions.general_firstname,
    printed_descriptions.general_lastname,
    printed_descriptions.general_gender,
    (printed_descriptions.body).weight AS body_weight,
    (printed_descriptions.body).height AS body_height,
    (printed_descriptions.body).breast_size AS body_breast_size,
    printed_descriptions.body_build,
    (printed_descriptions.hair).color_id AS hair_color_id,
    (printed_descriptions.hair).style_id AS hair_style_id,
    (printed_descriptions.hair).length AS hair_length,
    (printed_descriptions.hair).highlights AS hair_highlights,
    (printed_descriptions.hair).roots AS hair_roots,
    (printed_descriptions.hair).nature AS hair_nature,
    printed_descriptions.hair_style,
    (printed_descriptions.hands_nails).color_id AS hands_nails_color_id,
    (printed_descriptions.hands_nails).care AS hands_nails_care,
    (printed_descriptions.beard).color_id AS beard_color_id,
    (printed_descriptions.beard).length AS beard_length,
    (printed_descriptions.beard).style AS beard_style,
    (printed_descriptions.eyebrow).color_id AS eyebrow_color_id,
    (printed_descriptions.eyebrow).care AS eyebrow_care,
    (printed_descriptions.tooth).care AS tooth_care,
    (printed_descriptions.tooth).braces AS tooth_braces,
    (printed_descriptions.left_eye).color_id AS left_eye_color_id,
    (printed_descriptions.left_eye).lenses AS left_eye_lenses,
    (printed_descriptions.right_eye).color_id AS right_eye_color_id,
    (printed_descriptions.right_eye).lenses AS right_eye_lenses,
    (printed_descriptions.hands_hair).color_id AS hands_hair_color_id,
    (printed_descriptions.hands_hair).amount AS hands_hair_amount,
    printed_descriptions.face_freckles,
    printed_descriptions.face_care,
    printed_descriptions.eyebrow,
    printed_descriptions.face_shape,
    printed_descriptions.tooth,
    printed_descriptions.left_eye,
    printed_descriptions.right_eye,
    (printed_descriptions.hands_nails).length AS hands_nails_length,
    printed_descriptions.hands_vein_visibility,
    printed_descriptions.hands_joint_visibility,
    printed_descriptions.hands_care
  FROM printed_descriptions;

CREATE VIEW elasticsearch_demands AS
  SELECT demands.id,
    ROW((complete_descriptions.general).*)::general AS general
  FROM demands
  JOIN complete_descriptions ON complete_descriptions.id = demands.description_id;


CREATE VIEW collective_demands AS
  SELECT printed_description.general_birth_year,
    year_to_age(printed_description.general_birth_year, (location.met_at).moment) AS general_age,
    printed_description.face_shape_id,
    printed_description.hands_vein_visibility,
    printed_description.hands_joint_visibility,
    printed_description.hands_care,
    printed_description.hands_hair,
    (printed_description.body).build_id AS body_build_id,
    printed_description.general_ethnic_group_id,
    flat_description.general_firstname,
    flat_description.general_lastname,
    flat_description.general_gender,
    flat_description.body_weight,
    flat_description.body_height,
    flat_description.body_breast_size,
    flat_description.hands_nails_color_id,
    flat_description.hands_nails_length,
    flat_description.hands_nails_care,
    flat_description.beard_color_id,
    flat_description.beard_length,
    flat_description.beard_style,
    flat_description.eyebrow_color_id,
    flat_description.eyebrow_care,
    flat_description.tooth_care,
    flat_description.tooth_braces,
    flat_description.left_eye_color_id,
    flat_description.left_eye_lenses,
    flat_description.right_eye_color_id,
    flat_description.right_eye_lenses,
    flat_description.hands_hair_color_id,
    flat_description.hands_hair_amount,
    flat_description.face_freckles,
    flat_description.face_care,
    flat_description.hair_color_id,
    flat_description.hair_style_id,
    flat_description.hair_length,
    flat_description.hair_highlights,
    flat_description.hair_roots,
    flat_description.hair_nature,
    flat_description.hair_style,
    location.met_at AS location_met_at,
    location.coordinates AS location_coordinates,
    demand.id,
    demand.seeker_id,
    demand.created_at
  FROM demands demand
  JOIN printed_descriptions printed_description ON demand.description_id = printed_description.id
  JOIN flat_descriptions flat_description ON flat_description.id = printed_description.id
  JOIN locations location ON location.id = demand.location_id;

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
      new.general_ethnic_group_id,
      age_to_year(new.general_age, (new.location_met_at).moment),
      new.general_firstname,
      new.general_lastname,
      new.hair_style_id,
      new.hair_color_id,
      new.hair_length,
      new.hair_highlights,
      new.hair_roots,
      new.hair_nature,
      new.body_build_id,
      new.body_weight,
      new.body_height,
      new.body_breast_size,
      new.beard_color_id,
      new.beard_length,
      new.beard_style,
      new.eyebrow_color_id,
      new.eyebrow_care,
      new.left_eye_color_id,
      new.left_eye_lenses,
      new.right_eye_color_id,
      new.right_eye_lenses,
      new.face_freckles,
      new.face_care,
      new.face_shape_id,
      new.hands_care,
      new.hands_vein_visibility,
      new.hands_joint_visibility,
      new.hands_hair_color_id,
      new.hands_hair_amount,
      new.hands_nails_color_id,
      new.hands_nails_length,
      new.hands_nails_care,
      new.tooth_care,
      new.tooth_braces
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
      new.general_ethnic_group_id,
      age_to_year(new.general_age, (new.location_met_at).moment),
      new.general_firstname,
      new.general_lastname,
      new.hair_style_id,
      new.hair_color_id,
      new.hair_length,
      new.hair_highlights,
      new.hair_roots,
      new.hair_nature,
      new.body_build_id,
      new.body_weight,
      new.body_height,
      new.body_breast_size,
      new.beard_color_id,
      new.beard_length,
      new.beard_style,
      new.eyebrow_color_id,
      new.eyebrow_care,
      new.left_eye_color_id,
      new.left_eye_lenses,
      new.right_eye_color_id,
      new.right_eye_lenses,
      new.face_freckles,
      new.face_care,
      new.face_shape_id,
      new.hands_care,
      new.hands_vein_visibility,
      new.hands_joint_visibility,
      new.hands_hair_color_id,
      new.hands_hair_amount,
      new.hands_nails_color_id,
      new.hands_nails_length,
      new.hands_nails_care,
      new.tooth_care,
      new.tooth_braces
    )::flat_description
  );

  RETURN new;
END
$$;

CREATE TRIGGER collective_demands_row_ii_trigger INSTEAD OF INSERT ON collective_demands FOR EACH ROW EXECUTE PROCEDURE collective_demands_trigger_row_ii();
CREATE TRIGGER collective_demands_row_iu_trigger INSTEAD OF UPDATE ON collective_demands FOR EACH ROW EXECUTE PROCEDURE collective_demands_trigger_row_iu();


CREATE VIEW collective_evolutions AS
  SELECT printed_description.general_birth_year,
    year_to_age(printed_description.general_birth_year, evolution.evolved_at) AS general_age,
    printed_description.body_build,
    printed_description.face_shape,
    printed_description.face_shape_id,
    printed_description.hands_vein_visibility,
    printed_description.hands_joint_visibility,
    printed_description.hands_care,
    printed_description.hands_hair,
    (printed_description.body).build_id AS body_build_id,
    printed_description.general_ethnic_group_id,
    flat_description.general_firstname,
    flat_description.general_lastname,
    flat_description.general_gender,
    flat_description.body_weight,
    flat_description.body_height,
    flat_description.body_breast_size,
    flat_description.hands_nails_color_id,
    flat_description.hands_nails_length,
    flat_description.hands_nails_care,
    flat_description.beard_color_id,
    flat_description.beard_length,
    flat_description.beard_style,
    flat_description.eyebrow_color_id,
    flat_description.eyebrow_care,
    flat_description.tooth_care,
    flat_description.tooth_braces,
    flat_description.left_eye_color_id,
    flat_description.left_eye_lenses,
    flat_description.right_eye_color_id,
    flat_description.right_eye_lenses,
    flat_description.hands_hair_color_id,
    flat_description.hands_hair_amount,
    flat_description.general_ethnic_group,
    flat_description.face_freckles,
    flat_description.face_care,
    flat_description.left_eye,
    flat_description.right_eye,
    flat_description.hair_color_id,
    flat_description.hair_style_id,
    flat_description.hair_length,
    flat_description.hair_highlights,
    flat_description.hair_roots,
    flat_description.hair_nature,
    flat_description.hair_style,
    evolution.id,
    evolution.seeker_id,
    evolution.evolved_at
  FROM evolutions evolution
  JOIN printed_descriptions printed_description ON evolution.description_id = printed_description.id
  JOIN flat_descriptions flat_description ON flat_description.id = printed_description.id;

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
      new.general_ethnic_group_id,
      (
        SELECT birth_year
        FROM base_evolution
        WHERE seeker_id = new.seeker_id
        LIMIT 1
      ),
      new.general_firstname,
      new.general_lastname,
      new.hair_style_id,
      new.hair_color_id,
      new.hair_length,
      new.hair_highlights,
      new.hair_roots,
      new.hair_nature,
      new.body_build_id,
      new.body_weight,
      new.body_height,
      new.body_breast_size,
      new.beard_color_id,
      new.beard_length,
      new.beard_style,
      new.eyebrow_color_id,
      new.eyebrow_care,
      new.left_eye_color_id,
      new.left_eye_lenses,
      new.right_eye_color_id,
      new.right_eye_lenses,
      new.face_freckles,
      new.face_care,
      new.face_shape_id,
      new.hands_care,
      new.hands_vein_visibility,
      new.hands_joint_visibility,
      new.hands_hair_color_id,
      new.hands_hair_amount,
      new.hands_nails_color_id,
      new.hands_nails_length,
      new.hands_nails_care,
      new.tooth_care,
      new.tooth_braces
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
      new.general_ethnic_group_id,
      new.general_birth_year,
      new.general_firstname,
      new.general_lastname,
      new.hair_style_id,
      new.hair_color_id,
      new.hair_length,
      new.hair_highlights,
      new.hair_roots,
      new.hair_nature,
      new.body_build_id,
      new.body_weight,
      new.body_height,
      new.body_breast_size,
      new.beard_color_id,
      new.beard_length,
      new.beard_style,
      new.eyebrow_color_id,
      new.eyebrow_care,
      new.left_eye_color_id,
      new.left_eye_lenses,
      new.right_eye_color_id,
      new.right_eye_lenses,
      new.face_freckles,
      new.face_care,
      new.face_shape_id,
      new.hands_care,
      new.hands_vein_visibility,
      new.hands_joint_visibility,
      new.hands_hair_color_id,
      new.hands_hair_amount,
      new.hands_nails_color_id,
      new.hands_nails_length,
      new.hands_nails_care,
      new.tooth_care,
      new.tooth_braces
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

CREATE TRIGGER collective_evolutions_row_ii_trigger INSTEAD OF INSERT ON collective_evolutions FOR EACH ROW EXECUTE PROCEDURE collective_evolutions_trigger_row_ii();
CREATE TRIGGER collective_evolutions_row_iu_trigger INSTEAD OF UPDATE ON collective_evolutions FOR EACH ROW EXECUTE PROCEDURE collective_evolutions_trigger_row_iu();


CREATE VIEW description_parts AS
  SELECT description.id,
    description.beard_id,
    description.body_id,
    description.general_id,
    description.face_id,
    description.hair_id,
    description.eyebrow_id,
    description.left_eye_id,
    description.right_eye_id,
    hand.hand_hair_id,
    description.hand_id,
    demand.location_id,
    hand.nail_id,
    description.tooth_id
  FROM demands demand
  RIGHT JOIN descriptions description ON description.id = demand.description_id
  LEFT JOIN hair ON hair.id = description.hair_id
  LEFT JOIN bodies body ON body.id = description.body_id
  LEFT JOIN body_builds body_build ON body_build.id = body.build_id
  LEFT JOIN faces face ON face.id = description.face_id
  LEFT JOIN beards beard ON beard.id = description.beard_id
  LEFT JOIN general ON general.id = description.general_id
  LEFT JOIN ethnic_groups ethnic_group ON ethnic_group.id = general.ethnic_group_id
  LEFT JOIN hands hand ON hand.id = description.hand_id
  LEFT JOIN hand_hair ON hand_hair.id = hand.hand_hair_id
  LEFT JOIN nails nail ON nail.id = hand.nail_id
  LEFT JOIN teeth tooth ON tooth.id = description.tooth_id
  LEFT JOIN eyebrows eyebrow ON eyebrow.id = description.eyebrow_id
  LEFT JOIN eyes left_eye ON left_eye.id = description.left_eye_id
  LEFT JOIN eyes right_eye ON right_eye.id = description.right_eye_id;
-----



CREATE SCHEMA http;
SET search_path = http, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = false;

-- TABLES --
CREATE TABLE etags (
    id integer NOT NULL,
    entity text NOT NULL,
    tag text NOT NULL,
    created_at timestamp with time zone NOT NULL
);
ALTER TABLE etags ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
  SEQUENCE NAME etags_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1
);
ALTER TABLE ONLY etags ADD CONSTRAINT etags_pkey PRIMARY KEY (id);
CREATE UNIQUE INDEX etags_entity_uindex ON etags USING btree (lower((entity)::text));
CREATE UNIQUE INDEX etags_id_uindex ON etags USING btree (id);
-----
