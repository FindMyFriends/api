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

CREATE TYPE roles AS ENUM (
  'member'
);

CREATE TYPE ownerships AS ENUM (
  'yours',
  'theirs'
);

CREATE TYPE approximate_timestamptz AS (
  moment timestamp WITH TIME ZONE,
  timeline_side timeline_sides,
  approximation interval
);

CREATE TYPE breast_sizes AS ENUM (
  'A',
  'B',
  'C',
  'D'
);


CREATE TYPE sex AS ENUM (
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

CREATE TYPE job_statuses AS ENUM (
  'pending',
  'processing',
  'succeed',
  'failed'
);


CREATE TYPE mass AS (
  value numeric,
  unit mass_units
);

CREATE TYPE flat_description AS (
  id integer,
  general_sex sex,
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

CREATE FUNCTION validate_approximate_timestamptz(approximate_timestamptz) RETURNS boolean
AS $$
BEGIN
  IF $1.timeline_side = 'exactly'::timeline_sides AND $1.approximation IS NOT NULL THEN
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

CREATE FUNCTION validate_approximate_max_interval(approximate_timestamptz) RETURNS boolean
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

CREATE FUNCTION validate_length(length) RETURNS boolean
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
$$
LANGUAGE plpgsql
IMMUTABLE;

CREATE FUNCTION validate_mass(mass) RETURNS boolean
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

CREATE FUNCTION birth_year_in_range(int4range) RETURNS boolean
AS $$
BEGIN
  RETURN $1 <@ int4range(1850, date_part('year', CURRENT_DATE)::integer);
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
  RETURN int4range(0, 10, '[]') @> $1;
END
$$
LANGUAGE plpgsql
IMMUTABLE;


CREATE FUNCTION approximated_rating(integer) RETURNS int4range
AS $$
BEGIN
  RETURN int4range(abs($1 - 2), least($1 + 2, 10), '[]');
END
$$
LANGUAGE plpgsql
IMMUTABLE
STRICT;


CREATE FUNCTION suited_length(length) RETURNS length
AS $$
BEGIN
  IF (($1).unit = 'mm' AND ($1).value >= 10) THEN
    RETURN ROW (($1).value / 10, 'cm'::length_units);
  END IF;
  RETURN $1;
END
$$
LANGUAGE plpgsql
IMMUTABLE;


CREATE FUNCTION united_length(length) RETURNS length
AS $$
BEGIN
  IF (($1).unit = 'cm') THEN
    RETURN ROW(($1).value * 10, 'mm'::length_units);
  END IF;
  RETURN $1;
END
$$
LANGUAGE plpgsql
IMMUTABLE;

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
  INSERT INTO general (sex, ethnic_group_id, birth_year, firstname, lastname) VALUES (
    description.general_sex,
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
  SET sex = description.general_sex,
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

-- DOMAINS --
CREATE DOMAIN hex_color AS text
  CHECK ((is_hex(VALUE) AND (lower(VALUE) = VALUE)));

CREATE DOMAIN real_birth_year AS int4range
  CHECK (birth_year_in_range(VALUE));

CREATE DOMAIN rating AS smallint
  CHECK (is_rating((VALUE)::integer));
-----

-- TABLES --
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
  CONSTRAINT similar_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id),
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
  birth_year real_birth_year NOT NULL,
  firstname text,
  lastname text,
  CONSTRAINT general_ethnic_groups_id_fk FOREIGN KEY (ethnic_group_id) REFERENCES ethnic_groups(id)
);


CREATE TABLE beard_colors (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint NOT NULL,
  CONSTRAINT beard_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id)
);
CREATE UNIQUE INDEX beard_colors_color_id_uindex ON beard_colors USING btree (color_id);


CREATE TABLE beards (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint,
  length length,
  style text,
  CONSTRAINT beards_length_check CHECK (validate_length(length)),
  CONSTRAINT beards_beard_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES beard_colors(color_id)
);
CREATE TRIGGER beards_row_abiu_trigger BEFORE INSERT OR UPDATE ON beards FOR EACH ROW EXECUTE PROCEDURE united_length_trigger();


CREATE TABLE bodies (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  build_id smallint,
  weight mass,
  height length,
  breast_size breast_sizes,
  CONSTRAINT bodies_height_check CHECK (validate_length(height)),
  CONSTRAINT bodies_weight_check CHECK (validate_mass(weight)),
  CONSTRAINT bodies_body_builds_id_fk FOREIGN KEY (build_id) REFERENCES body_builds(id)
);


CREATE TABLE eyebrows (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint,
  care smallint,
  CONSTRAINT eyebrows_care_check CHECK (is_rating((care)::integer))
);

CREATE TABLE eye_colors (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint NOT NULL,
  CONSTRAINT eye_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id)
);
CREATE UNIQUE INDEX eye_colors_color_id_uindex ON eye_colors USING btree (color_id);


CREATE TABLE eyes (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint,
  lenses boolean,
  CONSTRAINT eyes_eye_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES eye_colors(color_id)
);

CREATE FUNCTION heterochromic_eyes(eyes, eyes) RETURNS boolean
AS $$
BEGIN
  RETURN (row_to_json($1)::jsonb - 'id') != (row_to_json($2)::jsonb - 'id');
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
);


CREATE TABLE hair_styles (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name text NOT NULL
);


CREATE TABLE hair_colors (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint NOT NULL,
  CONSTRAINT hair_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id)
);
CREATE UNIQUE INDEX hair_colors_color_id_uindex ON hair_colors USING btree (color_id);


CREATE TABLE hair (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  style_id smallint,
  color_id smallint,
  length length,
  highlights boolean,
  roots boolean,
  nature boolean,
  CONSTRAINT hair_length_check CHECK (validate_length(length)),
  CONSTRAINT hair_hair_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES hair_colors(color_id),
  CONSTRAINT hair_hair_styles_id_fk FOREIGN KEY (style_id) REFERENCES hair_styles(id)
);
CREATE TRIGGER hair_row_abiu_trigger BEFORE INSERT OR UPDATE ON hair FOR EACH ROW EXECUTE PROCEDURE united_length_trigger();


CREATE TABLE hand_hair_colors (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint NOT NULL,
  CONSTRAINT hand_hair_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id)
);
CREATE UNIQUE INDEX hand_hair_colors_color_id_uindex ON hand_hair_colors USING btree (color_id);


CREATE TABLE hand_hair (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint,
  amount rating,
  CONSTRAINT hand_hair_hand_hair_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES hand_hair_colors(color_id)
);


CREATE TABLE nail_colors (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint NOT NULL,
  CONSTRAINT nail_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id)
);
CREATE UNIQUE INDEX nail_colors_color_id_uindex ON nail_colors USING btree (color_id);


CREATE TABLE nails (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint,
  length length,
  care rating,
  CONSTRAINT nails_length_check CHECK (validate_length(length)),
  CONSTRAINT nails_nail_colors_color_id_fk FOREIGN KEY (color_id) REFERENCES nail_colors(color_id)
);
CREATE TRIGGER nails_row_abiu_trigger BEFORE INSERT OR UPDATE ON nails FOR EACH ROW EXECUTE PROCEDURE united_length_trigger();


CREATE TABLE hands (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  nail_id integer,
  care rating,
  vein_visibility rating,
  joint_visibility rating,
  hand_hair_id integer,
  CONSTRAINT hands_hand_hair_id_fk FOREIGN KEY (hand_hair_id) REFERENCES hand_hair(id),
  CONSTRAINT hands_nails_id_fk FOREIGN KEY (nail_id) REFERENCES nails(id)
);
CREATE UNIQUE INDEX hands_id_uindex ON hands USING btree (id);


CREATE TABLE teeth (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  care rating,
  braces boolean
);


CREATE TABLE descriptions (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  general_id integer NOT NULL,
  body_id integer NOT NULL,
  face_id integer NOT NULL,
  hand_id integer NOT NULL,
  hair_id integer NOT NULL,
  beard_id integer NOT NULL,
  eyebrow_id integer NOT NULL,
  tooth_id integer NOT NULL,
  left_eye_id integer NOT NULL,
  right_eye_id integer NOT NULL,
  CONSTRAINT descriptions_beards_id_fk FOREIGN KEY (beard_id) REFERENCES beards(id),
  CONSTRAINT descriptions_bodies_id_fk FOREIGN KEY (body_id) REFERENCES bodies(id) ON DELETE CASCADE,
  CONSTRAINT descriptions_eyebrows_id_fk FOREIGN KEY (eyebrow_id) REFERENCES eyebrows(id),
  CONSTRAINT descriptions_eyes_left_id_id_fk FOREIGN KEY (left_eye_id) REFERENCES eyes(id),
  CONSTRAINT descriptions_eyes_right_id_id_fk FOREIGN KEY (right_eye_id) REFERENCES eyes(id),
  CONSTRAINT descriptions_faces_id_fk FOREIGN KEY (face_id) REFERENCES faces(id) ON DELETE CASCADE,
  CONSTRAINT descriptions_general_id_fk FOREIGN KEY (general_id) REFERENCES general(id) ON DELETE CASCADE,
  CONSTRAINT descriptions_hair_id_fk FOREIGN KEY (hair_id) REFERENCES hair(id) ON DELETE CASCADE,
  CONSTRAINT descriptions_hands_id_fk FOREIGN KEY (hand_id) REFERENCES hands(id) ON DELETE CASCADE,
  CONSTRAINT descriptions_teeth_id_fk FOREIGN KEY (tooth_id) REFERENCES teeth(id)
);
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


CREATE TABLE seekers (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  email citext NOT NULL,
  password text NOT NULL,
  role roles NOT NULL DEFAULT 'member'::roles
);
CREATE UNIQUE INDEX seekers_email_uindex ON seekers USING btree (email);

CREATE TABLE forgotten_passwords (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  seeker_id integer NOT NULL,
  reminder text NOT NULL,
  used boolean NOT NULL,
  reminded_at timestamp with time zone NOT NULL,
  expire_at timestamp with time zone NOT NULL,
  CONSTRAINT forgotten_passwords_seeker_id_fkey FOREIGN KEY (seeker_id) REFERENCES seekers(id),
  CONSTRAINT forgotten_passwords_reminder_exact_length CHECK (LENGTH(reminder) = 141),
  CONSTRAINT forgotten_passwords_expire_at_future CHECK (expire_at >= NOW()),
  CONSTRAINT forgotten_passwords_expire_at_greater_than_reminded_at CHECK (expire_at > reminded_at)
);
CREATE INDEX forgotten_passwords_seeker_id ON forgotten_passwords USING btree (seeker_id);


CREATE TABLE verification_codes (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  seeker_id integer NOT NULL,
  code text NOT NULL,
  used_at timestamp with time zone,
  CONSTRAINT verification_codes_seeker_id UNIQUE (seeker_id),
  CONSTRAINT verification_codes_seeker_id_fkey FOREIGN KEY (seeker_id) REFERENCES seekers(id),
  CONSTRAINT verification_codes_code_exact_length CHECK (LENGTH(code) = 91)
);
CREATE INDEX verification_codes_seeker_id ON verification_codes USING btree (seeker_id);


CREATE TABLE evolutions (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  seeker_id integer NOT NULL,
  description_id integer NOT NULL,
  evolved_at timestamp WITH TIME ZONE NOT NULL,
  CONSTRAINT evolutions_descriptions_id_fk FOREIGN KEY (description_id) REFERENCES descriptions(id) ON DELETE CASCADE,
  CONSTRAINT evolutions_seekers_id_fk FOREIGN KEY (seeker_id) REFERENCES seekers(id) ON DELETE CASCADE
);
CREATE INDEX evolutions_description_id_index ON evolutions USING btree (description_id);
CREATE INDEX evolutions_seeker_id_index ON evolutions USING btree (seeker_id);

CREATE FUNCTION is_evolution_permitted(in_evolution_id evolutions.id%type, in_seeker_id seekers.id%type) RETURNS boolean
AS $$
SELECT EXISTS(
  SELECT 1
  FROM evolutions
  WHERE id = in_evolution_id AND seeker_id = in_seeker_id
) OR (
  SELECT EXISTS(
    SELECT 1
    FROM soulmates
    WHERE evolution_id = in_evolution_id AND demand_id IN (
      SELECT id
      FROM demands
      WHERE seeker_id = in_seeker_id
    )
  )
);
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
  hand AS (INSERT INTO hands DEFAULT VALUES RETURNING id),
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
    NOW()
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

CREATE TABLE locations (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  coordinates point NOT NULL,
  place text,
  met_at approximate_timestamptz NOT NULL,
  CONSTRAINT locations_met_at_approximation_mix_check CHECK (validate_approximate_timestamptz(met_at)),
  CONSTRAINT locations_met_at_approximation_max_interval_check CHECK (validate_approximate_max_interval(met_at))
);


CREATE TABLE demands (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  seeker_id integer NOT NULL,
  description_id integer NOT NULL,
  created_at timestamp WITH TIME ZONE NOT NULL,
  location_id integer NOT NULL,
  note character varying(150),
  CONSTRAINT demands_descriptions_id_fk FOREIGN KEY (description_id) REFERENCES descriptions(id) ON DELETE CASCADE,
  CONSTRAINT demands_locations_id_fk FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
);
CREATE UNIQUE INDEX demands_description_id_uindex ON demands USING btree (description_id);
CREATE UNIQUE INDEX demands_location_id_uindex ON demands USING btree (location_id);

CREATE FUNCTION demands_trigger_row_ad() RETURNS trigger
AS $$
BEGIN
  PERFORM deleted_description(old.description_id);
  DELETE FROM locations
  WHERE id = old.location_id;
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

CREATE TABLE soulmates (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  demand_id integer NOT NULL,
  evolution_id integer NOT NULL,
  score numeric NOT NULL,
  version integer NOT NULL DEFAULT 1,
  related_at timestamp WITH TIME ZONE NOT NULL DEFAULT now(),
  is_correct boolean NOT NULL DEFAULT TRUE,
  CONSTRAINT soulmates_demands_demand_id_fk FOREIGN KEY (demand_id) REFERENCES demands(id) ON DELETE CASCADE,
  CONSTRAINT soulmates_evolutions_evolution_id_fk FOREIGN KEY (evolution_id) REFERENCES evolutions(id) ON DELETE CASCADE
);
CREATE UNIQUE INDEX soulmates_demand_id_evolution_id_uindex ON soulmates USING btree (demand_id, evolution_id);

CREATE FUNCTION is_soulmate_permitted(in_soulmate_id soulmates.id%type, in_seeker_id seekers.id%type) RETURNS boolean
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

CREATE FUNCTION soulmates_trigger_row_ad() RETURNS trigger
AS $$
BEGIN
  DELETE FROM soulmate_requests
  WHERE demand_id = new.demand_id;
  RETURN old;
END;
$$
LANGUAGE plpgsql;

CREATE TRIGGER soulmates_row_ad_trigger AFTER INSERT OR DELETE ON soulmates FOR EACH ROW EXECUTE PROCEDURE soulmates_trigger_row_ad();


CREATE TABLE soulmate_requests (
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  demand_id integer NOT NULL,
  searched_at timestamp WITH TIME ZONE NOT NULL DEFAULT now(),
  self_id integer,
  status job_statuses NOT NULL,
  CONSTRAINT soulmate_requests_demands_demand_id_fk FOREIGN KEY (demand_id) REFERENCES demands(id) ON DELETE CASCADE,
  CONSTRAINT soulmate_requests_soulmate_requests_id_fk FOREIGN KEY (self_id) REFERENCES soulmate_requests(id) ON DELETE CASCADE
);
CREATE INDEX soulmate_requests_demand_id_index ON soulmate_requests USING btree (demand_id);
CREATE INDEX soulmate_requests_id_index ON soulmate_requests USING btree (self_id);


CREATE FUNCTION soulmate_request_refreshable_in(timestamptz) RETURNS integer
AS $$
SELECT greatest(EXTRACT(EPOCH FROM $1) - EXTRACT(EPOCH FROM NOW() - INTERVAL '20 MINUTES'), 0)::integer;
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
    soulmates.evolution_id,
    soulmates.is_correct,
    soulmate_requests.demand_id,
    soulmates.score,
    soulmates.related_at,
    soulmate_requests.searched_at,
    version = 1 AS new,
    row_number()
    OVER (PARTITION BY soulmate_requests.demand_id ORDER BY score DESC ) AS position,
    seeker_id
  FROM soulmates
  LEFT JOIN (
    SELECT
    demand_id,
    MAX(searched_at) AS searched_at
    FROM soulmate_requests
    GROUP BY demand_id
  ) AS soulmate_requests ON soulmate_requests.demand_id = soulmates.demand_id
  LEFT JOIN demands ON demands.id = soulmate_requests.demand_id
  ORDER BY position ASC;


CREATE FUNCTION with_suited_soulmate_ownership(in_seeker_id seekers.id%type) RETURNS table(
  id suited_soulmates.id%type,
  evolution_id suited_soulmates.evolution_id%type,
  is_correct suited_soulmates.is_correct%type,
  demand_id suited_soulmates.demand_id%type,
  score suited_soulmates.score%type,
  related_at suited_soulmates.related_at%type,
  searched_at suited_soulmates.searched_at%type,
  new suited_soulmates.new%type,
  "position" suited_soulmates.position%type,
  seeker_id suited_soulmates.seeker_id%type,
  ownership ownerships
)
AS $$
SELECT
  suited_soulmates.*,
  CASE
  WHEN demands.seeker_id = in_seeker_id THEN
    'yours'::ownerships
  ELSE
    'theirs'::ownerships
  END
FROM suited_soulmates
  LEFT JOIN demands ON demands.id = suited_soulmates.demand_id
  LEFT JOIN evolutions ON evolutions.id = suited_soulmates.evolution_id
WHERE demands.seeker_id = in_seeker_id OR evolutions.seeker_id = in_seeker_id;
$$
LANGUAGE SQL
VOLATILE;



CREATE TABLE eyebrow_colors (
  id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  color_id smallint NOT NULL,
  CONSTRAINT eyebrow_colors_colors_id_fk FOREIGN KEY (color_id) REFERENCES colors(id)
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
  SELECT
    complete_descriptions.id,
      ROW((complete_descriptions.general).id, (complete_descriptions.general).sex, (complete_descriptions.general).ethnic_group_id, (complete_descriptions.general).birth_year, (complete_descriptions.general).firstname, (complete_descriptions.general).lastname)::general AS general,
    (complete_descriptions.general).birth_year AS general_birth_year,
    (complete_descriptions.general).ethnic_group_id AS general_ethnic_group_id,
    (complete_descriptions.general).firstname AS general_firstname,
    (complete_descriptions.general).lastname AS general_lastname,
    (complete_descriptions.general).sex AS general_sex,
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
  SELECT
    printed_descriptions.id,
    printed_descriptions.general_birth_year,
    printed_descriptions.general_ethnic_group,
    printed_descriptions.general_firstname,
    printed_descriptions.general_lastname,
    printed_descriptions.general_sex,
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

CREATE TYPE elasticsearch_body AS (
  build_id smallint,
  weight smallint, -- TODO: int4range based on build and genre
  height smallint, -- TODO: int4range based on build and genre
  breast_size int4range
);

CREATE TYPE elasticsearch_hair AS (
  style_id smallint,
  color_id smallint,
  similar_colors_id smallint[],
  "length" smallint, -- TODO: int4range based on genre
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
  "length" smallint, -- TODO: int4range based on genre
  care int4range
);

CREATE TYPE elasticsearch_hand_hair AS (
  color_id smallint,
  similar_colors_id smallint[],
  amount int4range
);

CREATE TYPE elasticsearch_hand AS (
  nail elasticsearch_nail,
  care int4range,
  vein_visibility int4range,
  joint_visibility int4range,
  hair elasticsearch_hand_hair
);

CREATE TYPE elasticsearch_beard AS (
  color_id smallint,
  similar_colors_id smallint[],
  "length" smallint -- TODO: int4range based on genre
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
        (complete_descriptions.body).weight.value,
        (united_length((complete_descriptions.body).height)).value,
        approximated_breast_size((complete_descriptions.body).breast_size)
      )::elasticsearch_body AS body,
      ROW(
        (complete_descriptions.hair).style_id,
        (complete_descriptions.hair).color_id,
        similar_colors((complete_descriptions.hair).color_id),
        (united_length((complete_descriptions.hair).length)).value,
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
          (united_length((complete_descriptions.nail).length)).value,
          approximated_rating((complete_descriptions.nail).care)
        )::elasticsearch_nail,
        approximated_rating((complete_descriptions.hand).care),
        approximated_rating((complete_descriptions.hand).vein_visibility),
        approximated_rating((complete_descriptions.hand).joint_visibility),
        ROW(
          (complete_descriptions.hand_hair).color_id,
          similar_colors((complete_descriptions.hand_hair).color_id),
          approximated_rating((complete_descriptions.hand_hair).amount)
        )::elasticsearch_hand_hair
      )::elasticsearch_hand AS hand,
      ROW(
        (complete_descriptions.beard).color_id,
        similar_colors((complete_descriptions.beard).color_id),
        (united_length((complete_descriptions.beard).length)).value
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
    printed_description.general_birth_year,
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
    flat_description.general_sex,
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
    demand.created_at,
    demand.note
  FROM demands demand
    JOIN printed_descriptions printed_description ON demand.description_id = printed_description.id
    JOIN flat_descriptions flat_description ON flat_description.id = printed_description.id
    JOIN locations location ON location.id = demand.location_id;

CREATE FUNCTION collective_demands_trigger_row_ii() RETURNS trigger
AS $$
DECLARE
  v_location_id integer;
  v_description_id integer;
BEGIN
  v_description_id = inserted_description(
      ROW(
      NULL,
      new.general_sex,
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

  INSERT INTO demands (seeker_id, description_id, created_at, location_id, note) VALUES (
    new.seeker_id,
    v_description_id,
    NOW(),
    v_location_id,
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
  v_location_id integer;
  v_description_id integer;
BEGIN
  SELECT
    location_id,
    description_id
  FROM demands
    JOIN locations ON locations.id = demands.location_id
  WHERE demands.id = new.id
  INTO v_location_id, v_description_id;

  UPDATE demands
  SET note = new.note
  WHERE demands.id = new.id;

  UPDATE locations
  SET coordinates = new.location_coordinates,
    met_at = new.location_met_at
  WHERE id = v_location_id;

  PERFORM updated_description(
    ROW(
      v_description_id,
      new.general_sex,
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
$$
LANGUAGE plpgsql;

CREATE TRIGGER collective_demands_row_ii_trigger INSTEAD OF INSERT ON collective_demands FOR EACH ROW EXECUTE PROCEDURE collective_demands_trigger_row_ii();
CREATE TRIGGER collective_demands_row_iu_trigger INSTEAD OF UPDATE ON collective_demands FOR EACH ROW EXECUTE PROCEDURE collective_demands_trigger_row_iu();


CREATE VIEW collective_evolutions AS
  SELECT
    printed_description.general_birth_year,
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
    flat_description.general_sex,
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
STABLE;

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
  SELECT general.id
  FROM general
    JOIN descriptions ON descriptions.general_id = general.id
  WHERE descriptions.id = (SELECT description_id FROM base_evolution(in_seeker_id))
)
UPDATE general
SET birth_year = (
  SELECT birth_year
  FROM new_birth_year
)
WHERE id IN (
  SELECT id
  FROM related_descriptions
);
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

  IF (v_birth_year IS NULL) THEN
    RAISE EXCEPTION USING MESSAGE = FORMAT('Base evolution for seeker %L was not created.', new.seeker_id);
  END IF;

  v_description_id = inserted_description(
    ROW(
      NULL,
      new.general_sex,
      new.general_ethnic_group_id,
      v_birth_year,
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
  id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  entity text NOT NULL,
  tag text NOT NULL,
  created_at timestamp WITH TIME ZONE NOT NULL
);
CREATE UNIQUE INDEX etags_entity_uindex ON etags USING btree (lower((entity)::text));
-----