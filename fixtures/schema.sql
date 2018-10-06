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
CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;

CREATE SCHEMA access;
CREATE SCHEMA http;
CREATE SCHEMA log;
CREATE SCHEMA meta;
CREATE SCHEMA audit;
CREATE SCHEMA constant;

SET search_path = public, pg_catalog, access, http, log, meta;

-- schema constant
CREATE FUNCTION constant.age_min() RETURNS integer AS $$SELECT 15;$$ LANGUAGE sql IMMUTABLE;
CREATE FUNCTION constant.age_max() RETURNS integer AS $$SELECT 130;$$ LANGUAGE sql IMMUTABLE;
CREATE FUNCTION constant.rating_min() RETURNS integer AS $$SELECT 0;$$ LANGUAGE sql IMMUTABLE;
CREATE FUNCTION constant.rating_max() RETURNS integer AS $$SELECT 10;$$ LANGUAGE sql IMMUTABLE;
CREATE FUNCTION constant.birth_year_range_min() RETURNS integer AS $$SELECT date_part('year', CURRENT_DATE)::integer - constant.age_max();$$ LANGUAGE sql STABLE;
CREATE FUNCTION constant.birth_year_range_max() RETURNS integer AS $$SELECT date_part('year', CURRENT_DATE)::integer - constant.age_min();$$ LANGUAGE sql STABLE;
CREATE FUNCTION constant.timeline_sides() RETURNS text[] AS $$SELECT ARRAY['exactly', 'sooner', 'later', 'sooner or later'];$$ LANGUAGE sql IMMUTABLE;
CREATE FUNCTION constant.roles() RETURNS text[] AS $$SELECT ARRAY['member'];$$ LANGUAGE sql IMMUTABLE;
CREATE FUNCTION constant.breast_sizes() RETURNS text[] AS $$SELECT ARRAY['A', 'B', 'C', 'D'];$$ LANGUAGE sql IMMUTABLE;
CREATE FUNCTION constant.sex() RETURNS text[] AS $$SELECT ARRAY['man', 'woman'];$$ LANGUAGE sql IMMUTABLE;
CREATE FUNCTION constant.ownerships() RETURNS text[] AS $$SELECT ARRAY['yours', 'theirs'];$$ LANGUAGE sql IMMUTABLE;
CREATE FUNCTION constant.guest_id() RETURNS integer AS $$SELECT 0$$ LANGUAGE sql IMMUTABLE;


-- schema audit
CREATE DOMAIN audit.operation AS text CHECK (VALUE IN ('INSERT', 'UPDATE', 'DELETE'));

CREATE TABLE audit.history (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  "table" text NOT NULL,
  operation audit.operation NOT NULL,
  changed_at timestamp with time zone NOT NULL DEFAULT now(),
  old jsonb,
  new jsonb
);

CREATE OR REPLACE FUNCTION audit.trigger_table_audit() RETURNS trigger
AS $$
DECLARE
  r record;
BEGIN
  IF (TG_OP = 'DELETE') THEN
    r = old;
  ELSE
    r = new;
  END IF;

  EXECUTE format(
    'INSERT INTO audit.history ("table", operation, old, new) VALUES (%L, %L, %L, %L)',
    TG_TABLE_NAME,
    TG_OP,
    CASE WHEN TG_OP IN ('UPDATE', 'DELETE') THEN row_to_json(old) ELSE NULL END,
    CASE WHEN TG_OP IN ('UPDATE') THEN row_to_json(new) ELSE NULL END
  );

  RETURN r;
END;
$$
LANGUAGE plpgsql;



-- types

-- enums
CREATE TYPE job_statuses AS ENUM (
  'pending',
  'processing',
  'succeed',
  'failed'
);

-- domain functions
CREATE FUNCTION is_birth_year_in_range(int4range) RETURNS boolean
AS $$
BEGIN
  RETURN $1 <@ int4range(constant.birth_year_range_min(), constant.birth_year_range_max());
END
$$
LANGUAGE plpgsql
STABLE;

CREATE FUNCTION is_birth_year_in_range(smallint) RETURNS boolean
AS $$
BEGIN
  RETURN $1::integer <@ int4range(constant.birth_year_range_min(), constant.birth_year_range_max());
END
$$
LANGUAGE plpgsql
STABLE;

CREATE FUNCTION is_hex(text) RETURNS boolean
AS $$
BEGIN
  RETURN $1 ~ '^#[a-f0-9]{6}';
END
$$
LANGUAGE plpgsql
IMMUTABLE;

CREATE FUNCTION is_rating(integer) RETURNS boolean
AS $$
BEGIN
  RETURN int4range(constant.rating_min(), constant.rating_max(), '[]') @> $1;
END
$$
LANGUAGE plpgsql
IMMUTABLE;

-- domains
CREATE DOMAIN hex_color AS text CHECK ((is_hex(VALUE) AND (lower(VALUE) = VALUE)));
CREATE DOMAIN real_birth_year_range AS int4range CHECK (is_birth_year_in_range(VALUE));
CREATE DOMAIN real_birth_year AS smallint CHECK (is_birth_year_in_range(VALUE));
CREATE DOMAIN rating AS smallint CHECK (is_rating((VALUE)::integer));
CREATE DOMAIN timeline_sides AS text CHECK (VALUE = ANY(constant.timeline_sides()));
CREATE DOMAIN roles AS text CHECK (VALUE = ANY(constant.roles()));
CREATE DOMAIN breast_sizes AS text CHECK (VALUE = ANY(constant.breast_sizes()));
CREATE DOMAIN sex AS text CHECK (VALUE = ANY(constant.sex()));

-- compound types
CREATE TYPE approximate_timestamptz AS (
  moment timestamp with time zone,
  timeline_side timeline_sides,
  approximation interval
);

CREATE TYPE flat_description AS (
  id integer,
  general_sex sex,
  general_ethnic_group_id smallint,
  general_birth_year_range int4range,
  general_birth_year smallint,
  general_firstname text,
  general_lastname text,
  hair_style_id smallint,
  hair_color_id smallint,
  hair_length_id smallint,
  hair_highlights boolean,
  hair_roots boolean,
  hair_nature boolean,
  body_build_id smallint,
  body_breast_size breast_sizes,
  beard_color_id smallint,
  beard_length_id smallint,
  beard_style_id smallint,
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
  hand_visible_veins boolean,
  nail_color_id smallint,
  nail_length_id smallint,
  tooth_care smallint,
  tooth_braces boolean
);


-- functions
CREATE FUNCTION globals_get_variable(in_variable text) RETURNS text
AS $$
BEGIN
  RETURN nullif(current_setting(format('globals.%s', in_variable)), '');
  EXCEPTION WHEN OTHERS THEN
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;


CREATE FUNCTION globals_get_seeker() RETURNS integer
AS $$
  SELECT globals_get_variable('seeker')::integer;
$$ LANGUAGE sql;


CREATE FUNCTION globals_set_variable(in_variable text, in_value text) RETURNS text
AS $$
  SELECT set_config(format('globals.%s', in_variable), in_value, false);
$$ LANGUAGE sql;


CREATE FUNCTION globals_set_seeker(in_seeker integer) RETURNS void
AS $$
BEGIN
  PERFORM globals_set_variable('seeker', nullif(in_seeker, constant.guest_id())::text);
END;
$$ LANGUAGE plpgsql;


CREATE FUNCTION similar_colors(integer) RETURNS smallint[]
AS $$
SELECT array_agg(colors.color_id)
FROM (
  SELECT similar_color_id AS color_id
  FROM similar_colors
  WHERE color_id = $1
  UNION ALL
  SELECT color_id
  FROM similar_colors
  WHERE similar_color_id = $1
) AS colors;
$$
LANGUAGE SQL
STABLE
STRICT;

CREATE FUNCTION is_approximate_timestamptz_valid(approximate_timestamptz) RETURNS boolean
AS $$
BEGIN
  IF $1.timeline_side = 'exactly' AND $1.approximation IS NOT NULL THEN
    RAISE EXCEPTION '"Exactly" timeline_side can not have approximation';
  END IF;
  RETURN TRUE;
END
$$
LANGUAGE plpgsql
IMMUTABLE;

CREATE FUNCTION approximated_breast_size(breast_sizes) RETURNS int4range
AS $$
DECLARE
  size CONSTANT integer DEFAULT 'A=>1,B=>2,C=>3,D=>4'::hstore -> $1::char;
BEGIN
  RETURN int4range(greatest(size - 1, 1), least(size + 1, 4), '[]');
END
$$
LANGUAGE plpgsql
IMMUTABLE
STRICT;

CREATE FUNCTION is_approximate_interval_in_range(approximate_timestamptz) RETURNS boolean
AS $$
BEGIN
  IF $1.approximation > '2 days'::interval THEN
    RAISE EXCEPTION 'Overstepped maximum of 2 days';
  END IF;
  RETURN TRUE;
END
$$
LANGUAGE plpgsql
IMMUTABLE;

CREATE FUNCTION age_to_year(age int4range, now timestamp WITH TIME ZONE) RETURNS int4range
AS $$
BEGIN
  RETURN int4range(
    (EXTRACT('year' FROM now) - upper(age))::integer,
    (EXTRACT('year' FROM now) - lower(age))::integer
  );
END
$$
LANGUAGE plpgsql
STABLE;


CREATE FUNCTION age_to_year(age int4range, now tstzrange) RETURNS int4range
AS $$
BEGIN
  RETURN int4range(
    (EXTRACT('year' FROM lower(now)) - upper(age))::integer,
    (EXTRACT('year' FROM lower(now)) - lower(age))::integer
  );
END
$$
LANGUAGE plpgsql
STABLE;

CREATE FUNCTION age_to_year(age smallint, now timestamp WITH TIME ZONE) RETURNS smallint
AS $$
BEGIN
  RETURN (EXTRACT('year' FROM now) - age)::smallint;
END
$$
LANGUAGE plpgsql
STABLE;

CREATE FUNCTION approximated_rating(integer) RETURNS int4range
AS $$
BEGIN
  RETURN int4range(abs($1 - 2), least($1 + 2, constant.rating_max()), '[]');
END
$$
LANGUAGE plpgsql
IMMUTABLE
STRICT;

CREATE FUNCTION year_to_age(year int4range, now timestamp with time zone) RETURNS int4range
AS $$
BEGIN
  RETURN int4range(
    (EXTRACT('year' from now) - upper(year))::integer,
    (EXTRACT('year' from now) - lower(year))::integer
  );
END
$$
LANGUAGE plpgsql
IMMUTABLE;

CREATE FUNCTION year_to_age(year int4range, now tstzrange) RETURNS int4range
AS $$
BEGIN
  RETURN int4range(
    (SELECT extract('year' from lower(now)) - upper(year))::integer,
    (SELECT extract('year' from lower(now)) - lower(year))::integer
  );
END
$$
LANGUAGE plpgsql
IMMUTABLE;

CREATE FUNCTION year_to_age(year smallint, now timestamp with time zone) RETURNS smallint
AS $$
BEGIN
  RETURN (EXTRACT('year' from now) - year)::smallint;
END
$$
LANGUAGE plpgsql
IMMUTABLE;

CREATE FUNCTION deleted_description(v_description_id integer) RETURNS integer
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
  RETURNING nail_id
  INTO hand;

  DELETE FROM nails WHERE id = hand.nail_id;
  DELETE FROM hair WHERE id = description.hair_id;

  RETURN v_description_id;
END;
$$
LANGUAGE plpgsql;

CREATE FUNCTION inserted_description(description flat_description) RETURNS integer
AS $$
DECLARE
  v_beard_id integer;
  v_tooth_id integer;
  v_eyebrow_id integer;
  v_left_eye_id integer;
  v_right_eye_id integer;
  v_hand_nail_id integer;
  v_face_id integer;
  v_body_id integer;
  v_hand_id integer;
  v_general_id integer;
  v_hair_id integer;
  v_description_id integer;
BEGIN
  INSERT INTO general (sex, ethnic_group_id, birth_year_range, birth_year, firstname, lastname) VALUES (
    description.general_sex,
    description.general_ethnic_group_id,
    description.general_birth_year_range,
    description.general_birth_year,
    description.general_firstname,
    description.general_lastname
  )
  RETURNING id
  INTO v_general_id;
  INSERT INTO hair (style_id, color_id, length_id, highlights, roots, nature) VALUES (
    description.hair_style_id,
    description.hair_color_id,
    description.hair_length_id,
    description.hair_highlights,
    description.hair_roots,
    description.hair_nature
  )
  RETURNING id
  INTO v_hair_id;
  INSERT INTO beards (color_id, length_id, style_id) VALUES (
    description.beard_color_id,
    description.beard_length_id,
    description.beard_style_id
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
  INSERT INTO nails (color_id, length_id) VALUES (
    description.nail_color_id,
    description.nail_length_id
  )
  RETURNING id
  INTO v_hand_nail_id;
  INSERT INTO faces (freckles, care, shape_id) VALUES (
    description.face_freckles,
    description.face_care,
    description.face_shape_id
  )
  RETURNING id
  INTO v_face_id;
  INSERT INTO bodies (build_id, breast_size) VALUES (
    description.body_build_id,
    description.body_breast_size
  )
  RETURNING id
  INTO v_body_id;
  INSERT INTO hands (nail_id, care, visible_veins) VALUES (
    v_hand_nail_id,
    description.hand_care,
    description.hand_visible_veins
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
END $$
LANGUAGE plpgsql;

CREATE FUNCTION updated_description(description flat_description) RETURNS integer
AS $$
DECLARE
  v_beard_id integer;
  v_tooth_id integer;
  v_eyebrow_id integer;
  v_left_eye_id integer;
  v_right_eye_id integer;
  v_hand_nail_id integer;
  parts RECORD;
BEGIN
  SELECT *
  FROM description_parts
  WHERE id = description.id
  INTO parts;

  UPDATE general
  SET sex = description.general_sex,
    ethnic_group_id = description.general_ethnic_group_id,
    birth_year = description.general_birth_year,
    birth_year_range = description.general_birth_year_range,
    firstname = description.general_firstname,
    lastname = description.general_lastname
  WHERE id = parts.general_id;
  UPDATE hair
  SET style_id = description.hair_style_id,
    color_id = description.hair_color_id,
    length_id = description.hair_length_id,
    highlights = description.hair_highlights,
    roots = description.hair_roots,
    nature = description.hair_nature
  WHERE id = parts.hair_id;

  INSERT INTO beards (color_id, length_id, style_id) VALUES (
    description.beard_color_id,
    description.beard_length_id,
    description.beard_style_id
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
    breast_size = description.body_breast_size
  WHERE id = parts.body_id;

  INSERT INTO nails (color_id, length_id) VALUES (
    description.nail_color_id,
    description.nail_length_id
  )
  RETURNING id
  INTO v_hand_nail_id;

  UPDATE hands
  SET nail_id = v_hand_nail_id,
    care = description.hand_care,
    visible_veins = description.hand_visible_veins
  WHERE id = parts.hand_id;

  UPDATE descriptions
  SET beard_id = v_beard_id,
    eyebrow_id = v_eyebrow_id,
    tooth_id = v_tooth_id,
    left_eye_id = v_left_eye_id,
    right_eye_id = v_right_eye_id
  WHERE id = description.id;

  RETURN parts.id;
END $$
LANGUAGE plpgsql;
-----

CREATE FUNCTION check_existing_object_column_trigger() RETURNS trigger
AS $$
BEGIN
  IF (
    NOT EXISTS(
      SELECT 1
      FROM information_schema.columns
      WHERE format('%s.%s', table_schema, table_name)::regclass::oid = new.object
      AND column_name = new.column
    )
  ) THEN
    RAISE EXCEPTION 'Relation does not exist';
  END IF;
  RETURN new;
END;
$$
LANGUAGE plpgsql;

-- schema public
-- tables
CREATE TABLE meta.prioritized_columns (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  object oid NOT NULL,
  "column" text NOT NULL,
  priority smallint NOT NULL DEFAULT 0,
  CONSTRAINT prioritized_columns_priority_in_range CHECK (priority >= -5 AND priority <= 5)
);

CREATE TABLE meta.prioritized_application_columns (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  object oid NOT NULL,
  "column" text NOT NULL,
  prioritized_column_id smallint,
  CONSTRAINT prioritized_application_columns_prioritized_column_id_fk FOREIGN KEY (prioritized_column_id) REFERENCES meta.prioritized_columns(id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE VIEW meta.columns AS
  SELECT meta.prioritized_application_columns.object, meta.prioritized_application_columns.column, priority
  FROM meta.prioritized_columns
  JOIN meta.prioritized_application_columns ON meta.prioritized_application_columns.prioritized_column_id = meta.prioritized_columns.id
  UNION ALL
  SELECT object, "column", priority
  FROM meta.prioritized_columns;

CREATE TRIGGER prioritized_columns_row_biu_trigger BEFORE INSERT OR UPDATE ON meta.prioritized_columns FOR EACH ROW EXECUTE PROCEDURE check_existing_object_column_trigger();
CREATE TRIGGER prioritized_application_columns_row_biu_trigger BEFORE INSERT OR UPDATE ON meta.prioritized_application_columns FOR EACH ROW EXECUTE PROCEDURE check_existing_object_column_trigger();


CREATE TABLE face_shapes (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name text NOT NULL
);

CREATE TABLE colors (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name text NOT NULL,
  hex hex_color NOT NULL
);

CREATE TABLE similar_colors (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint NOT NULL,
  similar_color_id smallint NOT NULL,
  CONSTRAINT similar_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id)
    ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT similar_colors_colors_similar_color_id_fk FOREIGN KEY (similar_color_id) REFERENCES colors(id)
);

CREATE TABLE body_builds (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name text NOT NULL
);

CREATE TABLE ethnic_groups (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name text NOT NULL
);

CREATE TABLE general (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  sex sex NOT NULL,
  ethnic_group_id smallint NOT NULL,
  birth_year_range real_birth_year_range,
  birth_year real_birth_year,
  firstname text,
  lastname text,
  CONSTRAINT general_ethnic_groups_id_fk FOREIGN KEY (ethnic_group_id) REFERENCES ethnic_groups(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT general_birth_years_check CHECK ((birth_year IS NOT NULL AND birth_year_range IS NULL) OR (birth_year_range IS NOT NULL AND birth_year IS NULL))
);

CREATE TABLE beard_colors (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint NOT NULL UNIQUE,
  CONSTRAINT beard_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id)
    ON DELETE CASCADE ON UPDATE RESTRICT
);

CREATE TABLE beard_lengths (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name text NOT NULL UNIQUE
);

CREATE TABLE beard_styles (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name text NOT NULL UNIQUE
);

CREATE TABLE beards (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint,
  length_id smallint,
  style_id smallint,
  CONSTRAINT beards_beard_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES beard_colors(color_id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT beards_beard_length_ids_color_id_fk FOREIGN KEY (length_id) REFERENCES beard_lengths(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT beards_beard_style_ids_color_id_fk FOREIGN KEY (style_id) REFERENCES beard_styles(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT
);

CREATE TABLE bodies (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  build_id smallint,
  breast_size breast_sizes,
  CONSTRAINT bodies_body_builds_id_fk FOREIGN KEY (build_id) REFERENCES body_builds(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT
);

CREATE TABLE eyebrows (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint,
  care rating
);

CREATE TABLE eye_colors (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint NOT NULL UNIQUE,
  CONSTRAINT eye_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id)
    ON DELETE CASCADE ON UPDATE RESTRICT
);

CREATE TABLE eyes (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint,
  lenses boolean,
  CONSTRAINT eyes_eye_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES eye_colors(color_id)
    ON DELETE RESTRICT ON UPDATE RESTRICT
);

CREATE FUNCTION heterochromic_eyes(eyes, eyes) RETURNS boolean
AS $$
BEGIN
  RETURN (row_to_json($1)::jsonb - 'id') IS DISTINCT FROM (row_to_json($2)::jsonb - 'id');
END
$$
LANGUAGE plpgsql
IMMUTABLE;

CREATE TABLE faces (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  freckles boolean,
  care rating,
  shape_id smallint,
  CONSTRAINT faces_face_shapes_id_fk FOREIGN KEY (shape_id) REFERENCES face_shapes(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT
);

CREATE TABLE hair_styles (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name text NOT NULL
);

CREATE TABLE hair_colors (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint NOT NULL UNIQUE,
  CONSTRAINT hair_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id)
    ON DELETE CASCADE ON UPDATE RESTRICT
);

CREATE TABLE hair_lengths (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name text NOT NULL UNIQUE
);

CREATE TABLE hair (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  style_id smallint,
  color_id smallint,
  length_id smallint,
  highlights boolean,
  roots boolean,
  nature boolean,
  CONSTRAINT hair_hair_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES hair_colors(color_id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT hair_hair_styles_id_fk FOREIGN KEY (style_id) REFERENCES hair_styles(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT hair_hair_length_ids_id_fk FOREIGN KEY (length_id) REFERENCES hair_lengths(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT
);

CREATE TABLE nail_colors (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint NOT NULL UNIQUE,
  CONSTRAINT nail_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id)
    ON DELETE CASCADE ON UPDATE RESTRICT
);

CREATE TABLE nail_lengths (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name text NOT NULL UNIQUE
);

CREATE TABLE nails (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint,
  length_id smallint,
  CONSTRAINT nails_nail_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES nail_colors(color_id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT nails_nail_lengths_color_id_fk FOREIGN KEY (length_id) REFERENCES nail_lengths(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT
);

CREATE TABLE hands (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  nail_id integer,
  care rating,
  visible_veins boolean,
  CONSTRAINT hands_nails_id_fk FOREIGN KEY (nail_id) REFERENCES nails(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT
);

CREATE TABLE teeth (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  care rating,
  braces boolean
);

CREATE TABLE descriptions (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  general_id integer NOT NULL UNIQUE,
  body_id integer NOT NULL UNIQUE,
  face_id integer NOT NULL UNIQUE,
  hand_id integer NOT NULL UNIQUE,
  hair_id integer NOT NULL UNIQUE,
  beard_id integer NOT NULL UNIQUE,
  eyebrow_id integer NOT NULL UNIQUE,
  tooth_id integer NOT NULL UNIQUE,
  left_eye_id integer NOT NULL UNIQUE,
  right_eye_id integer NOT NULL UNIQUE,
  CONSTRAINT descriptions_beards_id_fk FOREIGN KEY (beard_id) REFERENCES beards(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT descriptions_bodies_id_fk FOREIGN KEY (body_id) REFERENCES bodies(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT descriptions_eyebrows_id_fk FOREIGN KEY (eyebrow_id) REFERENCES eyebrows(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT descriptions_eyes_left_id_fk FOREIGN KEY (left_eye_id) REFERENCES eyes(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT descriptions_eyes_right_id_fk FOREIGN KEY (right_eye_id) REFERENCES eyes(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT descriptions_faces_id_fk FOREIGN KEY (face_id) REFERENCES faces(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT descriptions_general_id_fk FOREIGN KEY (general_id) REFERENCES general(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT descriptions_hair_id_fk FOREIGN KEY (hair_id) REFERENCES hair(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT descriptions_hands_id_fk FOREIGN KEY (hand_id) REFERENCES hands(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT descriptions_teeth_id_fk FOREIGN KEY (tooth_id) REFERENCES teeth(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT
);

CREATE TABLE seekers (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  email citext NOT NULL,
  password text NOT NULL,
  role roles NOT NULL DEFAULT 'member'::roles
);

CREATE INDEX seekers_facebook_index ON seekers USING btree (email);

CREATE FUNCTION seekers_trigger_row_ai() RETURNS trigger
AS $$
BEGIN
  INSERT INTO verification_codes (seeker_id, code) VALUES (
    new.id,
    format('%s:%s', encode(gen_random_bytes(25), 'hex'), encode(digest(new.id::text, 'sha1'), 'hex'))
  );
  RETURN new;
END;
$$
LANGUAGE plpgsql;

CREATE FUNCTION seekers_trigger_row_biu() RETURNS trigger
AS $$
BEGIN
  IF EXISTS(SELECT 1 FROM seekers WHERE email = new.email AND id IS DISTINCT FROM CASE WHEN TG_OP = 'INSERT' THEN NULL ELSE new.id END) THEN
    RAISE EXCEPTION USING MESSAGE = format('Email %s already exists', new.email);
  END IF;

  RETURN new;
END;
$$
LANGUAGE plpgsql;

CREATE TRIGGER seekers_row_ai_trigger AFTER INSERT ON seekers FOR EACH ROW EXECUTE PROCEDURE seekers_trigger_row_ai();
CREATE TRIGGER seekers_row_biu_trigger BEFORE INSERT OR UPDATE ON seekers FOR EACH ROW EXECUTE PROCEDURE seekers_trigger_row_biu();


CREATE TABLE seeker_contacts (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  seeker_id integer NOT NULL,
  facebook citext,
  instagram citext,
  phone_number citext,
  CONSTRAINT seeker_contacts_seeker_id_fk FOREIGN KEY (seeker_id) REFERENCES seekers(id) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT seeker_contacts_one_of_check CHECK (COALESCE(facebook, instagram, phone_number) IS NOT NULL)
);
CREATE INDEX seeker_contacts_facebook_index ON seeker_contacts USING btree (facebook);
CREATE INDEX seeker_contacts_instagram_index ON seeker_contacts USING btree (instagram);
CREATE INDEX seeker_contacts_phone_number_index ON seeker_contacts USING btree (phone_number);

CREATE FUNCTION seeker_contacts_trigger_row_biu() RETURNS trigger
AS $$
BEGIN
  IF EXISTS(SELECT 1 FROM seeker_contacts WHERE facebook = new.facebook) THEN
    RAISE EXCEPTION USING MESSAGE = format('Facebook %s already exists', new.facebook);
  ELSIF EXISTS(SELECT 1 FROM seeker_contacts WHERE instagram = new.instagram) THEN
    RAISE EXCEPTION USING MESSAGE = format('Instagram %s already exists', new.instagram);
  ELSIF EXISTS(SELECT 1 FROM seeker_contacts WHERE phone_number = new.phone_number) THEN
    RAISE EXCEPTION USING MESSAGE = format('Phone number %s already exists', new.phone_number);
  END IF;

  RETURN new;
END;
$$
LANGUAGE plpgsql;


CREATE TRIGGER seeker_contacts_row_biu_trigger BEFORE INSERT OR UPDATE ON seeker_contacts FOR EACH ROW EXECUTE PROCEDURE seeker_contacts_trigger_row_biu();


CREATE TABLE spots (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  coordinates point NOT NULL,
  met_at approximate_timestamptz NOT NULL,
  CONSTRAINT spots_met_at_approximation_mix_check CHECK (is_approximate_timestamptz_valid(met_at)),
  CONSTRAINT spots_met_at_approximation_max_interval_check CHECK (is_approximate_interval_in_range(met_at))
);

CREATE TRIGGER spots_audit_trigger AFTER UPDATE OR DELETE ON spots FOR EACH ROW EXECUTE PROCEDURE audit.trigger_table_audit();


CREATE TABLE evolutions (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  seeker_id integer NOT NULL,
  description_id integer NOT NULL,
  evolved_at timestamp WITH TIME ZONE NOT NULL,
  CONSTRAINT evolutions_descriptions_id_fk FOREIGN KEY (description_id) REFERENCES descriptions(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT evolutions_seekers_id_fk FOREIGN KEY (seeker_id) REFERENCES seekers(id)
    ON DELETE CASCADE ON UPDATE RESTRICT
);
CREATE INDEX evolutions_description_id_index ON evolutions USING btree (description_id);
CREATE INDEX evolutions_seeker_id_index ON evolutions USING btree (seeker_id);

CREATE FUNCTION is_evolution_owned(in_evolution_id evolutions.id%type, in_seeker_id seekers.id%type) RETURNS boolean
AS $$
SELECT EXISTS(
  SELECT 1
  FROM evolutions
  WHERE id = in_evolution_id AND seeker_id = in_seeker_id
);
$$
LANGUAGE SQL
VOLATILE;

CREATE FUNCTION is_evolution_visible(in_evolution_id evolutions.id%type, in_seeker_id seekers.id%type) RETURNS boolean
AS $$
SELECT is_evolution_owned(in_evolution_id, in_seeker_id) OR EXISTS(
  SELECT 1
  FROM soulmates
  JOIN demands ON demands.id = soulmates.demand_id
  WHERE soulmates.evolution_id = in_evolution_id AND demands.seeker_id = in_seeker_id
)
$$
LANGUAGE SQL
VOLATILE;

CREATE FUNCTION created_base_evolution(
  in_seeker_id seekers.id%type,
  in_sex general.sex%type,
  in_ethnic_group_id general.ethnic_group_id%type,
  in_birth_year general.birth_year%type,
  in_firstname general.firstname%type,
  in_lastname general.lastname%type
) RETURNS integer
AS $$
DECLARE
  v_evolution_id evolutions.id%type;
BEGIN
  IF (EXISTS (SELECT 1 FROM evolutions WHERE seeker_id = in_seeker_id)) THEN
    RAISE EXCEPTION USING MESSAGE = FORMAT('Base evolution for seeker %L is already created.', in_seeker_id);
  END IF;
  WITH general AS (
    INSERT INTO general (sex, ethnic_group_id, birth_year, firstname, lastname) VALUES (
      in_sex,
      in_ethnic_group_id,
      in_birth_year,
      in_firstname,
      in_lastname
    )
    RETURNING id
  ),
  body AS (INSERT INTO bodies DEFAULT VALUES RETURNING id),
  face AS (INSERT INTO faces DEFAULT VALUES RETURNING id),
  nail AS (INSERT INTO nails DEFAULT VALUES RETURNING id),
  hand AS (
    INSERT INTO hands (nail_id, care, visible_veins) VALUES (
      (SELECT id FROM nail),
      DEFAULT,
      DEFAULT
    )
    RETURNING id
  ),
  hair AS (INSERT INTO hair DEFAULT VALUES RETURNING id),
  beard AS (INSERT INTO beards DEFAULT VALUES RETURNING id),
  eyebrow AS (INSERT INTO eyebrows DEFAULT VALUES RETURNING id),
  tooth AS (INSERT INTO teeth DEFAULT VALUES RETURNING id),
  left_eye AS (INSERT INTO eyes DEFAULT VALUES RETURNING id),
  right_eye AS (INSERT INTO eyes DEFAULT VALUES RETURNING id),
  description AS (
    INSERT INTO descriptions (general_id, body_id, face_id, hand_id, hair_id, beard_id, eyebrow_id, tooth_id, left_eye_id, right_eye_id) VALUES (
      (SELECT id FROM general),
      (SELECT id FROM body),
      (SELECT id FROM face),
      (SELECT id FROM hand),
      (SELECT id FROM hair),
      (SELECT id FROM beard),
      (SELECT id FROM eyebrow),
      (SELECT id FROM tooth),
      (SELECT id FROM left_eye),
      (SELECT id FROM right_eye)
    )
    RETURNING id
  )
  INSERT INTO evolutions (seeker_id, description_id, evolved_at) VALUES (
    in_seeker_id,
    (SELECT id FROM description),
    now()
  )
  RETURNING id
  INTO v_evolution_id;

  RETURN v_evolution_id;
END
$$
LANGUAGE plpgsql
VOLATILE;

CREATE FUNCTION evolutions_trigger_row_ad() RETURNS trigger
AS $$
BEGIN
  PERFORM deleted_description(old.description_id);
  RETURN old;
END;
$$
LANGUAGE plpgsql;

CREATE FUNCTION evolutions_trigger_row_bd() RETURNS trigger
AS $$
BEGIN
  IF (
    SELECT COUNT(*) = 1
    FROM (
      SELECT 1
      FROM evolutions
      WHERE seeker_id = old.seeker_id
      LIMIT 2
    ) AS i
  ) THEN
    RAISE EXCEPTION 'Base evolution can not be reverted';
  END IF;
  RETURN old;
END;
$$
LANGUAGE plpgsql;

CREATE TRIGGER evolutions_row_ad_trigger AFTER DELETE ON evolutions FOR EACH ROW EXECUTE PROCEDURE evolutions_trigger_row_ad();
CREATE TRIGGER evolutions_row_bd_trigger BEFORE DELETE ON evolutions FOR EACH ROW EXECUTE PROCEDURE evolutions_trigger_row_bd();


CREATE TABLE evolution_spots (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  evolution_id integer NOT NULL,
  spot_id integer NOT NULL,
  assigned_at timestamp WITH TIME ZONE NOT NULL DEFAULT now(),
  CONSTRAINT evolution_spots_evolution_id_fk FOREIGN KEY (evolution_id) REFERENCES evolutions(id)
    ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT evolution_spots_spot_id_fk FOREIGN KEY (spot_id) REFERENCES spots(id)
    ON DELETE CASCADE ON UPDATE RESTRICT
);

CREATE VIEW collective_evolution_spots AS
  SELECT spot_id AS id, evolution_id, coordinates, met_at, assigned_at
  FROM spots
  JOIN evolution_spots ON evolution_spots.spot_id = spots.id;

CREATE FUNCTION collective_evolution_spots_trigger_row_ii() RETURNS trigger
AS $$
BEGIN
  WITH inserted_spot AS (
    INSERT INTO spots (coordinates, met_at) VALUES (new.coordinates, new.met_at)
    RETURNING id
  )
  INSERT INTO evolution_spots (evolution_id, spot_id) VALUES (new.evolution_id, (SELECT id FROM inserted_spot));
  RETURN new;
END
$$
LANGUAGE plpgsql;

CREATE TRIGGER collective_evolution_spots_trigger_row_ii INSTEAD OF INSERT ON collective_evolution_spots FOR EACH ROW EXECUTE PROCEDURE collective_evolution_spots_trigger_row_ii();

CREATE TABLE demands (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  seeker_id integer NOT NULL,
  description_id integer NOT NULL UNIQUE,
  created_at timestamp WITH TIME ZONE NOT NULL,
  note character varying(150),
  CONSTRAINT demands_descriptions_id_fk FOREIGN KEY (description_id) REFERENCES descriptions(id)
    ON DELETE RESTRICT ON UPDATE RESTRICT
);

CREATE FUNCTION demands_trigger_row_ad() RETURNS trigger
AS $$
BEGIN
  PERFORM deleted_description(old.description_id);
  RETURN old;
END;
$$
LANGUAGE plpgsql;


CREATE FUNCTION demands_trigger_row_bu() RETURNS trigger
AS $$
BEGIN
  RAISE EXCEPTION 'Column created_at is read only';
  RETURN new;
END;
$$
LANGUAGE plpgsql;

CREATE FUNCTION demands_trigger_row_biu() RETURNS trigger
AS $$
BEGIN
  new.note = nullif(trim(new.note), '');
  RETURN new;
END;
$$
LANGUAGE plpgsql;

CREATE TRIGGER demands_row_biu_trigger BEFORE INSERT OR UPDATE ON demands FOR EACH ROW EXECUTE PROCEDURE demands_trigger_row_biu();
CREATE TRIGGER demands_row_ad_trigger AFTER DELETE ON demands FOR EACH ROW EXECUTE PROCEDURE demands_trigger_row_ad();
CREATE TRIGGER demands_row_bu_trigger BEFORE UPDATE OF created_at ON demands FOR EACH ROW EXECUTE PROCEDURE demands_trigger_row_bu();

CREATE FUNCTION is_demand_owned(in_demand_id demands.id%type, in_seeker_id seekers.id%type) RETURNS boolean
AS $$
SELECT EXISTS(
  SELECT 1
  FROM demands
  WHERE id = in_demand_id AND seeker_id = in_seeker_id
);
$$
LANGUAGE SQL
STABLE;


CREATE FUNCTION is_common_seeker(in_demand_id demands.id%type, in_evolution_id evolutions.id%type) RETURNS boolean
AS $$
WITH demand_seeker AS (
  SELECT seeker_id
  FROM demands
  WHERE id = in_demand_id
), evolution_seeker AS (
  SELECT seeker_id
  FROM evolutions
  WHERE id = in_evolution_id
)
SELECT seeker_id = (SELECT seeker_id FROM evolution_seeker) FROM demand_seeker
$$
LANGUAGE SQL
STABLE;

CREATE TABLE demand_spots (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  demand_id integer NOT NULL,
  spot_id integer NOT NULL,
  assigned_at timestamp WITH TIME ZONE NOT NULL DEFAULT now(),
  CONSTRAINT demand_spots_demand_id_fk FOREIGN KEY (demand_id) REFERENCES demands(id)
    ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT demand_spots_spot_id_fk FOREIGN KEY (spot_id) REFERENCES spots(id)
    ON DELETE CASCADE ON UPDATE RESTRICT
);

CREATE VIEW collective_demand_spots AS
  SELECT spot_id AS id, demand_id, coordinates, met_at, assigned_at
  FROM spots
  JOIN demand_spots ON demand_spots.spot_id = spots.id;

CREATE FUNCTION is_spot_owned(in_spot_id spots.id%type, in_seeker_id seekers.id%type) RETURNS boolean
AS $$
SELECT EXISTS(
  SELECT 1
  FROM demand_spots
  JOIN demands ON demands.id = demand_spots.demand_id
  WHERE spot_id = in_spot_id AND demands.seeker_id = in_seeker_id
) OR EXISTS (
  SELECT 1
  FROM evolution_spots
  JOIN evolutions ON evolutions.id = evolution_spots.evolution_id
  WHERE spot_id = in_spot_id AND evolutions.seeker_id = in_seeker_id
);
$$
LANGUAGE SQL
VOLATILE;

CREATE FUNCTION collective_demand_spots_trigger_row_ii() RETURNS trigger
AS $$
BEGIN
  WITH inserted_spot AS (
    INSERT INTO spots (coordinates, met_at) VALUES (new.coordinates, new.met_at)
    RETURNING id
  )
  INSERT INTO demand_spots (demand_id, spot_id) VALUES (new.demand_id, (SELECT id FROM inserted_spot));
  RETURN new;
END
$$
LANGUAGE plpgsql;

CREATE TRIGGER collective_demand_spots_trigger_row_ii INSTEAD OF INSERT ON collective_demand_spots FOR EACH ROW EXECUTE PROCEDURE collective_demand_spots_trigger_row_ii();


CREATE TABLE soulmates (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  demand_id integer NOT NULL,
  evolution_id integer NOT NULL,
  score numeric NOT NULL,
  version integer NOT NULL DEFAULT 1,
  related_at timestamp WITH TIME ZONE NOT NULL DEFAULT now(),
  is_correct boolean NOT NULL DEFAULT TRUE,
  is_exposed boolean NOT NULL DEFAULT FALSE,
  CONSTRAINT soulmates_demand_id_evolution_id_ukey UNIQUE (demand_id, evolution_id),
  CONSTRAINT soulmates_demands_demand_id_fk FOREIGN KEY (demand_id) REFERENCES demands(id)
    ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT soulmates_evolutions_evolution_id_fk FOREIGN KEY (evolution_id) REFERENCES evolutions(id)
    ON DELETE CASCADE ON UPDATE RESTRICT
);

CREATE FUNCTION is_demanding_soulmate_owned(in_soulmate_id soulmates.id%type, in_seeker_id seekers.id%type) RETURNS boolean
AS $$
SELECT EXISTS(
  SELECT 1
  FROM soulmates
  WHERE id = in_soulmate_id AND demand_id IN (
    SELECT id
    FROM demands
    WHERE seeker_id = in_seeker_id
  )
);
$$
LANGUAGE SQL
VOLATILE;

CREATE FUNCTION is_evolving_soulmate_owned(in_soulmate_id soulmates.id%type, in_seeker_id seekers.id%type) RETURNS boolean
AS $$
SELECT EXISTS(
  SELECT 1
  FROM soulmates
  WHERE id = in_soulmate_id AND evolution_id IN (
    SELECT id
    FROM evolutions
    WHERE seeker_id = in_seeker_id
  )
);
$$
LANGUAGE SQL
VOLATILE;

CREATE FUNCTION is_soulmate_owned(in_soulmate_id soulmates.id%type, in_seeker_id seekers.id%type) RETURNS boolean
AS $$
  SELECT is_evolving_soulmate_owned(in_soulmate_id, in_seeker_id) OR is_demanding_soulmate_owned(in_soulmate_id, in_seeker_id);
$$
LANGUAGE SQL
VOLATILE;

CREATE FUNCTION soulmates_trigger_row_ad() RETURNS trigger
AS $$
BEGIN
  DELETE FROM soulmate_requests
  WHERE demand_id = new.demand_id;
  RETURN old;
END;
$$
LANGUAGE plpgsql;

CREATE FUNCTION soulmates_trigger_row_bui() RETURNS trigger
AS $$
BEGIN
  IF (is_common_seeker(new.demand_id, new.evolution_id)) THEN
    RAISE EXCEPTION USING
      MESSAGE = 'Demand and evolution can not belong to the same seeker',
      HINT = format('Demand ID %L and evolution ID %L belongs to the same seeker', new.demand_id, new.evolution_id);
  END IF;
  RETURN new;
END;
$$
LANGUAGE plpgsql;


CREATE TRIGGER soulmates_row_ad_trigger AFTER DELETE ON soulmates FOR EACH ROW EXECUTE PROCEDURE soulmates_trigger_row_ad();
CREATE TRIGGER soulmates_row_bui_trigger BEFORE UPDATE OR INSERT ON soulmates FOR EACH ROW EXECUTE PROCEDURE soulmates_trigger_row_bui();


CREATE TABLE soulmate_requests (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  demand_id integer NOT NULL,
  searched_at timestamp WITH TIME ZONE NOT NULL DEFAULT now(),
  self_id integer,
  status job_statuses NOT NULL,
  CONSTRAINT soulmate_requests_demands_demand_id_fk FOREIGN KEY (demand_id) REFERENCES demands(id)
    ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT soulmate_requests_soulmate_requests_id_fk FOREIGN KEY (self_id) REFERENCES soulmate_requests(id)
    ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE INDEX soulmate_requests_demand_id_index ON soulmate_requests USING btree (demand_id);
CREATE INDEX soulmate_requests_id_index ON soulmate_requests USING btree (self_id);


CREATE FUNCTION soulmate_request_refreshable_in(timestamptz) RETURNS integer
AS $$
SELECT greatest(EXTRACT(EPOCH FROM $1) - EXTRACT(EPOCH FROM now() - INTERVAL '20 MINUTES'), 0)::integer;
$$
LANGUAGE SQL
IMMUTABLE;

CREATE OR REPLACE FUNCTION is_soulmate_request_refreshable(timestamptz) RETURNS boolean
AS $$
SELECT 0 = soulmate_request_refreshable_in($1);
$$
LANGUAGE SQL
IMMUTABLE;

CREATE FUNCTION is_soulmate_request_refreshable(in_demand_id demands.id%type) RETURNS boolean
AS $$
WITH refrehes AS (
  SELECT
    MAX(searched_at) AS searched_at,
    status
  FROM soulmate_requests
  WHERE demand_id = in_demand_id
  GROUP BY demand_id, status
  ORDER BY searched_at DESC
  LIMIT 1
), done_refreshes AS (
  SELECT *
  FROM refrehes
  WHERE status IN ('succeed', 'failed')
)
SELECT NOT EXISTS(SELECT 1 FROM refrehes) OR (
  EXISTS(SELECT 1 FROM done_refreshes) AND (
    SELECT is_soulmate_request_refreshable(searched_at)
    FROM done_refreshes
  )
);
$$
LANGUAGE SQL
VOLATILE;

CREATE FUNCTION soulmate_requests_trigger_row_bi() RETURNS trigger
AS $$
BEGIN
  IF (NOT is_soulmate_request_refreshable(new.demand_id) AND new.self_id IS NULL) THEN
    RAISE EXCEPTION 'Seeking for soulmate is already in progress';
  END IF;
  RETURN NEW;
END
$$
LANGUAGE plpgsql
VOLATILE;

CREATE TRIGGER soulmate_requests_row_bi_trigger BEFORE INSERT ON soulmate_requests FOR EACH ROW EXECUTE PROCEDURE soulmate_requests_trigger_row_bi();


CREATE VIEW suited_soulmates AS
  SELECT
    soulmates.id,
    CASE WHEN soulmates.is_exposed THEN soulmates.evolution_id ELSE NULL END AS evolution_id,
    soulmates.is_correct,
    soulmates.is_exposed,
    soulmate_requests.demand_id,
    soulmates.score,
    soulmates.related_at,
    soulmate_requests.searched_at,
    version = 1 AS new,
    row_number() OVER (PARTITION BY soulmate_requests.demand_id ORDER BY score DESC) AS position,
    seeker_id,
    CASE WHEN seeker_id = globals_get_seeker() THEN 'yours' ELSE 'theirs' END AS ownership
  FROM soulmates
  LEFT JOIN (
    SELECT demand_id, max(searched_at) AS searched_at
    FROM soulmate_requests
    GROUP BY demand_id
  ) AS soulmate_requests ON soulmate_requests.demand_id = soulmates.demand_id
  LEFT JOIN demands ON demands.id = soulmate_requests.demand_id
  ORDER BY position ASC;



CREATE TABLE eyebrow_colors (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint NOT NULL,
  CONSTRAINT eyebrow_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id)
    ON DELETE CASCADE ON UPDATE RESTRICT
);
-----

-- VIEWS --
CREATE VIEW complete_descriptions AS
  SELECT
    description.id,
      ROW(general.*)::general AS general,
      ROW(hair.*)::hair AS hair,
      ROW(hair_style.*)::hair_styles AS hair_style,
      ROW(body.*)::bodies AS body,
      ROW(body_build.*)::body_builds AS body_build,
      ROW(face.*)::faces AS face,
      ROW(beard.*)::beards AS beard,
      ROW(ethnic_group.*)::ethnic_groups AS ethnic_group,
      ROW(hand.*)::hands AS hand,
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
    LEFT JOIN nails nail ON nail.id = hand.nail_id
    LEFT JOIN teeth tooth ON tooth.id = description.tooth_id
    LEFT JOIN eyebrows eyebrow ON eyebrow.id = description.eyebrow_id
    LEFT JOIN eyes left_eye ON left_eye.id = description.left_eye_id
    LEFT JOIN eyes right_eye ON right_eye.id = description.right_eye_id;


CREATE VIEW flat_descriptions AS
  SELECT
    (complete_descriptions.beard).color_id AS beard_color_id,
    (complete_descriptions.beard).length_id AS beard_length_id,
    (complete_descriptions.beard).style_id AS beard_style_id,
    (complete_descriptions.body).breast_size AS body_breast_size,
    (complete_descriptions.body).build_id AS body_build_id,
    (complete_descriptions.eyebrow).care AS eyebrow_care,
    (complete_descriptions.eyebrow).color_id AS eyebrow_color_id,
    (complete_descriptions.face).care AS face_care,
    (complete_descriptions.face).freckles AS face_freckles,
    (complete_descriptions.face).shape_id AS face_shape_id,
    (complete_descriptions.general).birth_year_range AS general_birth_year_range,
    (complete_descriptions.general).birth_year AS general_birth_year,
    (complete_descriptions.general).ethnic_group_id AS general_ethnic_group_id,
    (complete_descriptions.general).firstname AS general_firstname,
    (complete_descriptions.general).lastname AS general_lastname,
    (complete_descriptions.general).sex AS general_sex,
    (complete_descriptions.hair).color_id AS hair_color_id,
    (complete_descriptions.hair).highlights AS hair_highlights,
    (complete_descriptions.hair).length_id AS hair_length_id,
    (complete_descriptions.hair).nature AS hair_nature,
    (complete_descriptions.hair).roots AS hair_roots,
    (complete_descriptions.hair).style_id AS hair_style_id,
    (complete_descriptions.hand).care AS hands_care,
    (complete_descriptions.hand).visible_veins AS hands_visible_veins,
    (complete_descriptions.left_eye).color_id AS left_eye_color_id,
    (complete_descriptions.left_eye).lenses AS left_eye_lenses,
    (complete_descriptions.nail).color_id AS hands_nails_color_id,
    (complete_descriptions.nail).length_id AS hands_nails_length_id,
    (complete_descriptions.right_eye).color_id AS right_eye_color_id,
    (complete_descriptions.right_eye).lenses AS right_eye_lenses,
    (complete_descriptions.tooth).braces AS tooth_braces,
    (complete_descriptions.tooth).care AS tooth_care,
    complete_descriptions.id
  FROM complete_descriptions;

CREATE TYPE elasticsearch_body AS (
  build_id smallint,
  breast_size int4range
);

CREATE TYPE elasticsearch_hair AS (
  style_id smallint,
  color_id smallint,
  similar_colors_id smallint[],
  "length_id" smallint,
  highlights boolean,
  roots boolean,
  nature boolean
);

CREATE TYPE elasticsearch_face AS (
  freckles boolean,
  care int4range,
  shape_id smallint
);

CREATE TYPE elasticsearch_nail AS (
  color_id smallint,
  similar_colors_id smallint[],
  "length_id" smallint
);

CREATE TYPE elasticsearch_hand AS (
  nail elasticsearch_nail,
  care int4range,
  visible_veins boolean
);

CREATE TYPE elasticsearch_beard AS (
  color_id smallint,
  similar_colors_id smallint[],
  "length_id" smallint -- TODO: int4range based on genre
);

CREATE TYPE elasticsearch_eyebrow AS (
  color_id smallint,
  similar_colors_id smallint[],
  care int4range
);

CREATE TYPE elasticsearch_tooth AS (
  care int4range,
  braces boolean
);

CREATE TYPE elasticsearch_eye AS (
  color_id smallint,
  similar_colors_id smallint[],
  lenses boolean
);

CREATE VIEW elasticsearch_demands AS
  SELECT
    demands.id,
    demands.seeker_id,
      ROW((complete_descriptions.general).*)::general AS general,
      ROW(
        (complete_descriptions.body).build_id,
        approximated_breast_size((complete_descriptions.body).breast_size)
      )::elasticsearch_body AS body,
      ROW(
        (complete_descriptions.hair).style_id,
        (complete_descriptions.hair).color_id,
        similar_colors((complete_descriptions.hair).color_id),
        (complete_descriptions.hair).length_id,
        (complete_descriptions.hair).highlights,
        (complete_descriptions.hair).roots,
        (complete_descriptions.hair).nature
      )::elasticsearch_hair AS hair,
      ROW(
        (complete_descriptions.face).freckles,
        approximated_rating((complete_descriptions.face).care),
        (complete_descriptions.face).shape_id
      )::elasticsearch_face AS face,
      ROW(
        ROW(
          (complete_descriptions.nail).color_id,
          similar_colors((complete_descriptions.nail).color_id),
          (complete_descriptions.nail).length_id
        )::elasticsearch_nail,
        approximated_rating((complete_descriptions.hand).care),
        (complete_descriptions.hand).visible_veins
      )::elasticsearch_hand AS hand,
      ROW(
        (complete_descriptions.beard).color_id,
        similar_colors((complete_descriptions.beard).color_id),
        (complete_descriptions.beard).length_id
      )::elasticsearch_beard AS beard,
      ROW(
        (complete_descriptions.eyebrow).color_id,
        similar_colors((complete_descriptions.eyebrow).color_id),
        approximated_rating((complete_descriptions.eyebrow).care)
      )::elasticsearch_eyebrow AS eyebrow,
      ROW(
        approximated_rating((complete_descriptions.tooth).care),
        (complete_descriptions.tooth).braces
      )::elasticsearch_tooth AS tooth,
      heterochromic_eyes(complete_descriptions.right_eye, complete_descriptions.left_eye) AS heterochromic_eyes,
      ROW(
        (complete_descriptions.left_eye).color_id,
        similar_colors((complete_descriptions.left_eye).color_id),
        (complete_descriptions.left_eye).lenses
      )::elasticsearch_eye AS left_eye,
      ROW(
        (complete_descriptions.right_eye).color_id,
        similar_colors((complete_descriptions.right_eye).color_id),
        (complete_descriptions.right_eye).lenses
      )::elasticsearch_eye AS right_eye
  FROM demands
    JOIN complete_descriptions ON complete_descriptions.id = demands.description_id;


CREATE VIEW collective_demands AS
  SELECT
    demands.created_at,
    demands.id,
    demands.note,
    demands.seeker_id,
    flat_descriptions.beard_color_id,
    flat_descriptions.beard_length_id,
    flat_descriptions.beard_style_id,
    flat_descriptions.body_breast_size,
    flat_descriptions.body_build_id,
    flat_descriptions.eyebrow_care,
    flat_descriptions.eyebrow_color_id,
    flat_descriptions.face_care,
    flat_descriptions.face_freckles,
    flat_descriptions.face_shape_id,
    flat_descriptions.general_birth_year_range,
    flat_descriptions.general_ethnic_group_id,
    flat_descriptions.general_firstname,
    flat_descriptions.general_lastname,
    flat_descriptions.general_sex,
    flat_descriptions.hair_color_id,
    flat_descriptions.hair_highlights,
    flat_descriptions.hair_length_id,
    flat_descriptions.hair_nature,
    flat_descriptions.hair_roots,
    flat_descriptions.hair_style_id,
    flat_descriptions.hands_care,
    flat_descriptions.hands_nails_color_id,
    flat_descriptions.hands_nails_length_id,
    flat_descriptions.hands_visible_veins,
    flat_descriptions.left_eye_color_id,
    flat_descriptions.left_eye_lenses,
    flat_descriptions.right_eye_color_id,
    flat_descriptions.right_eye_lenses,
    flat_descriptions.tooth_braces,
    flat_descriptions.tooth_care,
    year_to_age(flat_descriptions.general_birth_year_range, demands.created_at) AS general_age
  FROM demands
    JOIN flat_descriptions ON flat_descriptions.id = demands.description_id;

CREATE FUNCTION collective_demands_trigger_row_ii() RETURNS trigger
AS $$
DECLARE
  v_description_id integer;
BEGIN
  v_description_id = inserted_description(
      ROW(
      NULL,
      new.general_sex,
      new.general_ethnic_group_id,
      age_to_year(new.general_age, COALESCE(new.created_at, now())),
      NULL,
      new.general_firstname,
      new.general_lastname,
      new.hair_style_id,
      new.hair_color_id,
      new.hair_length_id,
      new.hair_highlights,
      new.hair_roots,
      new.hair_nature,
      new.body_build_id,
      new.body_breast_size,
      new.beard_color_id,
      new.beard_length_id,
      new.beard_style_id,
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
      new.hands_visible_veins,
      new.hands_nails_color_id,
      new.hands_nails_length_id,
      new.tooth_care,
      new.tooth_braces
    )::flat_description
  );

  INSERT INTO demands (seeker_id, description_id, created_at, note) VALUES (
    new.seeker_id,
    v_description_id,
    now(),
    new.note
  )
  RETURNING id
  INTO new.id;

  RETURN new;
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION collective_demands_trigger_row_iu() RETURNS trigger
AS $$
DECLARE
  v_description_id integer;
BEGIN
  SELECT
    description_id
  FROM demands
  WHERE demands.id = new.id
  INTO v_description_id;

  UPDATE demands
  SET note = new.note
  WHERE demands.id = new.id;

  PERFORM updated_description(
    ROW(
      v_description_id,
      new.general_sex,
      new.general_ethnic_group_id,
      age_to_year(new.general_age, COALESCE(new.created_at, now())),
      NULL,
      new.general_firstname,
      new.general_lastname,
      new.hair_style_id,
      new.hair_color_id,
      new.hair_length_id,
      new.hair_highlights,
      new.hair_roots,
      new.hair_nature,
      new.body_build_id,
      new.body_breast_size,
      new.beard_color_id,
      new.beard_length_id,
      new.beard_style_id,
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
      new.hands_visible_veins,
      new.hands_nails_color_id,
      new.hands_nails_length_id,
      new.tooth_care,
      new.tooth_braces
    )::flat_description
  );

  RETURN new;
END
$$
LANGUAGE plpgsql;

CREATE TRIGGER collective_demands_row_ii_trigger INSTEAD OF INSERT ON collective_demands FOR EACH ROW EXECUTE PROCEDURE collective_demands_trigger_row_ii();
CREATE TRIGGER collective_demands_row_iu_trigger INSTEAD OF UPDATE ON collective_demands FOR EACH ROW EXECUTE PROCEDURE collective_demands_trigger_row_iu();


CREATE VIEW collective_evolutions AS
  SELECT
    evolutions.evolved_at,
    evolutions.id,
    evolutions.seeker_id,
    flat_descriptions.beard_color_id,
    flat_descriptions.beard_length_id,
    flat_descriptions.beard_style_id,
    flat_descriptions.body_breast_size,
    flat_descriptions.body_build_id,
    flat_descriptions.eyebrow_care,
    flat_descriptions.eyebrow_color_id,
    flat_descriptions.face_care,
    flat_descriptions.face_freckles,
    flat_descriptions.face_shape_id,
    flat_descriptions.general_birth_year,
    flat_descriptions.general_ethnic_group_id,
    flat_descriptions.general_firstname,
    flat_descriptions.general_lastname,
    flat_descriptions.general_sex,
    flat_descriptions.hair_color_id,
    flat_descriptions.hair_highlights,
    flat_descriptions.hair_length_id,
    flat_descriptions.hair_nature,
    flat_descriptions.hair_roots,
    flat_descriptions.hair_style_id,
    flat_descriptions.hands_care,
    flat_descriptions.hands_nails_color_id,
    flat_descriptions.hands_nails_length_id,
    flat_descriptions.hands_visible_veins,
    flat_descriptions.left_eye_color_id,
    flat_descriptions.left_eye_lenses,
    flat_descriptions.right_eye_color_id,
    flat_descriptions.right_eye_lenses,
    flat_descriptions.tooth_braces,
    flat_descriptions.tooth_care,
    year_to_age(flat_descriptions.general_birth_year, evolutions.evolved_at) AS general_age
  FROM evolutions
    JOIN flat_descriptions ON flat_descriptions.id = evolutions.description_id;

CREATE FUNCTION prioritized_evolution_columns() RETURNS TABLE (
  columns jsonb,
  seeker_id integer
)
AS $BODY$
DECLARE
  v_columns CONSTANT text[] DEFAULT ARRAY[
     'beard_color_id',
     'beard_length_id',
     'beard_style_id',
     'body_breast_size',
     'body_build_id',
     'eyebrow_care',
     'eyebrow_color_id',
     'face_care',
     'face_freckles',
     'face_shape_id',
     'general_age',
     'general_ethnic_group_id',
     'general_firstname',
     'general_lastname',
     'general_sex',
     'hair_color_id',
     'hair_highlights',
     'hair_length_id',
     'hair_nature',
     'hair_roots',
     'hair_style_id',
     'hands_care',
     'hands_nails_color_id',
     'hands_nails_length_id',
     'hands_visible_veins',
     'left_eye_color_id',
     'left_eye_lenses',
     'right_eye_color_id',
     'right_eye_lenses',
     'tooth_braces',
     'tooth_care'
  ];
  v_full_query text;
  column_parts record;
BEGIN
  SELECT string_agg(format('COUNT(DISTINCT %I) AS %I', column_name, column_name), ',') AS distinct_part,
    string_agg(format('%L,%I', column_name, column_name), ',') AS row_part
  FROM unnest(v_columns) AS column_name
  INTO column_parts;

  v_full_query = format($$
    WITH distinct_part AS (
      SELECT %s, seeker_id
      FROM collective_evolutions
      GROUP BY seeker_id
    ), json_query AS (
      SELECT json_build_object(%s) AS json_row, seeker_id
      FROM distinct_part
    ), each_from_json_query AS (
      SELECT (json_each(json_query.json_row)).key::text, (json_each(json_query.json_row)).value::text::integer, seeker_id
      FROM json_query
    ), prioritized_columns AS (
      SELECT each_from_json_query.*, priority
      FROM each_from_json_query
      JOIN meta.columns ON meta.columns.column = key
      WHERE meta.columns.object = 'collective_evolutions'::regclass::oid
    ), ranked_rows AS (
      SELECT *, row_number() OVER (PARTITION BY seeker_id ORDER BY value DESC, priority DESC) AS position
      FROM prioritized_columns
    )
    SELECT jsonb_object_agg(key, value), seeker_id
    FROM ranked_rows
    GROUP BY seeker_id
  $$, column_parts.distinct_part, column_parts.row_part);
  RETURN QUERY EXECUTE v_full_query;
END
$BODY$
LANGUAGE plpgsql
VOLATILE
STRICT;

CREATE MATERIALIZED VIEW prioritized_evolution_fields AS
  SELECT * FROM prioritized_evolution_columns();
CREATE UNIQUE INDEX prioritized_evolution_fields_view_ukey ON prioritized_evolution_fields (seeker_id);
REFRESH MATERIALIZED VIEW CONCURRENTLY prioritized_evolution_fields;

CREATE FUNCTION base_evolution(in_seeker_id seekers.id%type) RETURNS TABLE (
  id evolutions.id%type,
  description_id evolutions.description_id%type
)
AS $$
SELECT id, description_id
FROM evolutions
WHERE seeker_id = in_seeker_id
ORDER BY evolved_at DESC
LIMIT 1
$$
LANGUAGE SQL
STABLE
ROWS 1;

CREATE FUNCTION cascade_update_birth_year(
  in_description_id descriptions.id%type,
  in_seeker_id seekers.id%type
) RETURNS void
AS $$
WITH new_birth_year AS (
  SELECT birth_year
  FROM general
  JOIN descriptions ON descriptions.general_id = general.id
  WHERE descriptions.id = in_description_id
), related_descriptions AS (
  SELECT descriptions.id
  FROM descriptions
  JOIN evolutions ON evolutions.description_id = descriptions.id
  WHERE evolutions.seeker_id = in_seeker_id
), related_generals AS (
  SELECT general.id
  FROM general
  JOIN descriptions ON descriptions.general_id = general.id
  WHERE descriptions.id IN (SELECT id FROM related_descriptions)
)
UPDATE general
SET birth_year = (SELECT birth_year FROM new_birth_year)
WHERE id IN (SELECT id FROM related_generals);
$$
LANGUAGE SQL
VOLATILE;

CREATE FUNCTION collective_evolutions_trigger_row_ii() RETURNS trigger
AS $$
DECLARE
  v_description_id descriptions.id%type;
  v_birth_year general.birth_year%type;
BEGIN
  SELECT birth_year
  FROM general
  JOIN descriptions ON descriptions.general_id = general.id
  WHERE descriptions.id = (SELECT description_id FROM base_evolution(new.seeker_id))
  INTO v_birth_year;

  IF NOT FOUND THEN
    RAISE EXCEPTION USING MESSAGE = format('Base evolution for seeker %L was not created.', new.seeker_id);
  END IF;

  v_description_id = inserted_description(
    ROW(
      NULL,
      new.general_sex,
      new.general_ethnic_group_id,
      NULL,
      v_birth_year,
      new.general_firstname,
      new.general_lastname,
      new.hair_style_id,
      new.hair_color_id,
      new.hair_length_id,
      new.hair_highlights,
      new.hair_roots,
      new.hair_nature,
      new.body_build_id,
      new.body_breast_size,
      new.beard_color_id,
      new.beard_length_id,
      new.beard_style_id,
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
      new.hands_visible_veins,
      new.hands_nails_color_id,
      new.hands_nails_length_id,
      new.tooth_care,
      new.tooth_braces
    )::flat_description
  );
  INSERT INTO evolutions (seeker_id, description_id, evolved_at) VALUES (
    new.seeker_id,
    v_description_id,
    new.evolved_at
  )
  RETURNING id
  INTO new.id;

  RETURN new;
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION collective_evolutions_trigger_row_iu() RETURNS trigger
AS $$
DECLARE
  v_description_id integer;
BEGIN
  SELECT description_id
  FROM evolutions
  WHERE evolutions.id = new.id
  INTO v_description_id;

  PERFORM updated_description(
    ROW(
      v_description_id,
      new.general_sex,
      new.general_ethnic_group_id,
      NULL,
      age_to_year(new.general_age, now()),
      new.general_firstname,
      new.general_lastname,
      new.hair_style_id,
      new.hair_color_id,
      new.hair_length_id,
      new.hair_highlights,
      new.hair_roots,
      new.hair_nature,
      new.body_build_id,
      new.body_breast_size,
      new.beard_color_id,
      new.beard_length_id,
      new.beard_style_id,
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
      new.hands_visible_veins,
      new.hands_nails_color_id,
      new.hands_nails_length_id,
      new.tooth_care,
      new.tooth_braces
    )
  );

  PERFORM cascade_update_birth_year(v_description_id, new.seeker_id);

  RETURN new;
END
$$
LANGUAGE plpgsql;

CREATE TRIGGER collective_evolutions_row_ii_trigger INSTEAD OF INSERT ON collective_evolutions FOR EACH ROW EXECUTE PROCEDURE collective_evolutions_trigger_row_ii();
CREATE TRIGGER collective_evolutions_row_iu_trigger INSTEAD OF UPDATE ON collective_evolutions FOR EACH ROW EXECUTE PROCEDURE collective_evolutions_trigger_row_iu();


CREATE VIEW description_parts AS
  SELECT
    description.id,
    description.beard_id,
    description.body_id,
    description.general_id,
    description.face_id,
    description.hair_id,
    description.eyebrow_id,
    description.left_eye_id,
    description.right_eye_id,
    description.hand_id,
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
    LEFT JOIN nails nail ON nail.id = hand.nail_id
    LEFT JOIN teeth tooth ON tooth.id = description.tooth_id
    LEFT JOIN eyebrows eyebrow ON eyebrow.id = description.eyebrow_id
    LEFT JOIN eyes left_eye ON left_eye.id = description.left_eye_id
    LEFT JOIN eyes right_eye ON right_eye.id = description.right_eye_id;
-----


-- TABLES --
CREATE TABLE http.etags (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  entity text NOT NULL,
  tag text NOT NULL,
  created_at timestamp WITH TIME ZONE NOT NULL
);
CREATE UNIQUE INDEX etags_entity_ukey ON etags USING btree (lower((entity)::text));
-----


CREATE TABLE access.forgotten_passwords (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  seeker_id integer NOT NULL,
  reminder text NOT NULL UNIQUE,
  used_at timestamp with time zone,
  reminded_at timestamp with time zone NOT NULL,
  expire_at timestamp with time zone NOT NULL,
  CONSTRAINT forgotten_passwords_seeker_id_fkey FOREIGN KEY (seeker_id) REFERENCES seekers(id)
    ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT forgotten_passwords_reminder_exact_length CHECK (length(reminder) = 141),
  CONSTRAINT forgotten_passwords_expire_at_future CHECK (expire_at >= NOW()),
  CONSTRAINT forgotten_passwords_expire_at_greater_than_reminded_at CHECK (expire_at > reminded_at)
);
CREATE INDEX forgotten_passwords_seeker_id ON forgotten_passwords USING btree (seeker_id);


CREATE TABLE access.verification_codes (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  seeker_id integer NOT NULL UNIQUE,
  code text NOT NULL UNIQUE,
  used_at timestamp with time zone,
  CONSTRAINT verification_codes_seeker_id_fkey FOREIGN KEY (seeker_id) REFERENCES seekers(id)
    ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT verification_codes_code_exact_length CHECK (length(code) = 91)
);
CREATE INDEX verification_codes_seeker_id ON verification_codes USING btree (seeker_id);

-- TABLES --
CREATE TABLE log.cron_jobs (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  marked_at timestamp with time zone NOT NULL DEFAULT now(),
  name text NOT NULL,
  self_id integer,
  status job_statuses NOT NULL,
  CONSTRAINT cron_jobs_id_fk FOREIGN KEY (self_id) REFERENCES log.cron_jobs(id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE FUNCTION cron_jobs_trigger_row_bi() RETURNS trigger
AS $$
BEGIN
  IF (
    new.status = 'processing' AND (
      SELECT status NOT IN ('succeed', 'failed')
      FROM log.cron_jobs
      WHERE name = new.name
      ORDER BY id DESC
      LIMIT 1
    )
  ) THEN
    RAISE EXCEPTION USING MESSAGE = format('Job "%s" can not be run, because previous is not fulfilled.', new.name);
  END IF;
  RETURN new;
END;
$$
LANGUAGE plpgsql;

CREATE TRIGGER cron_jobs_row_bi_trigger BEFORE INSERT ON log.cron_jobs FOR EACH ROW EXECUTE PROCEDURE cron_jobs_trigger_row_bi();
-----