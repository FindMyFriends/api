CREATE SCHEMA IF NOT EXISTS test_utils;

CREATE FUNCTION test_utils.tables_not_matching_count(table_counts hstore) RETURNS hstore
LANGUAGE plpgsql
AS $$
DECLARE
	v_table_name TEXT;
	counts hstore DEFAULT '';
	zero_counts hstore DEFAULT '';
	table_enums TEXT[] DEFAULT ARRAY['colors', 'similar_colors', 'ethnic_groups', 'body_builds', 'eye_colors', 'hair_colors', 'nail_colors', 'face_shapes', 'beard_colors', 'eyebrow_colors', 'hair_styles', 'beard_styles' , 'hair_lengths', 'nail_lengths', 'beard_lengths'];
	count INTEGER;
BEGIN
	FOR v_table_name IN (
		SELECT table_name
		FROM information_schema.tables
		WHERE table_schema = 'public'
		AND table_type = 'BASE TABLE'
		AND NOT (table_name = ANY(table_enums))
	)
	LOOP
		EXECUTE format('SELECT COUNT(*) FROM %I', v_table_name) INTO count;
		counts = counts || hstore(v_table_name, count::TEXT);
		zero_counts = zero_counts || hstore(v_table_name, '0');
	END LOOP;
	RETURN (counts - table_counts) - zero_counts;
END;
$$;

CREATE OR REPLACE FUNCTION test_utils.json_to_hstore(json JSONB) RETURNS HSTORE
LANGUAGE SQL
AS $$
	SELECT hstore(array_agg(key), array_agg(value)) FROM jsonb_each_text(json);
$$;

CREATE OR REPLACE FUNCTION test_utils.random_enum(enum TEXT) RETURNS TEXT
LANGUAGE plpgsql
AS $$
DECLARE
	v_output TEXT;
BEGIN
	EXECUTE(format('SELECT enum FROM unnest(enum_range(NULL::%I)) enum ORDER BY random() LIMIT 1', enum)) INTO v_output;
	RETURN v_output;
END;
$$;

CREATE OR REPLACE FUNCTION test_utils.better_random(low INT = 1, high BIGINT = 2147483647) RETURNS INT
AS $$
BEGIN
	RETURN floor(random()* (high - low + 1) + low);
END;
$$ language plpgsql STRICT;

CREATE OR REPLACE FUNCTION test_utils.better_random(type TEXT) RETURNS INT
AS $$
DECLARE
	types CONSTANT hstore DEFAULT 'smallint=>32767,integer=>2147483647,bigint=>9223372036854775807'::hstore ;
BEGIN
	RETURN test_utils.better_random(1, CAST(types -> lower(type) AS BIGINT));
END;
$$ language plpgsql STRICT;

CREATE OR REPLACE FUNCTION test_utils.random_boolean() RETURNS boolean
LANGUAGE plpgsql
AS $$
BEGIN
  RETURN random() > 0.5;
END;
$$;

CREATE FUNCTION test_utils.is_function_exist(name text, schema text) RETURNS boolean
AS $$
SELECT EXISTS (
  SELECT 1
  FROM pg_proc p
  JOIN pg_namespace n ON p.pronamespace = n.oid
  WHERE n.nspname = schema AND p.proname = TRIM(LEADING format('%s.', schema) FROM name)
);
$$
LANGUAGE sql
STABLE;