CREATE SCHEMA IF NOT EXISTS test_utils;

CREATE FUNCTION test_utils.tables_not_matching_count(table_counts hstore) RETURNS hstore
LANGUAGE plpgsql
AS $$
DECLARE
	v_table_name TEXT;
	counts hstore DEFAULT '';
	zero_counts hstore DEFAULT '';
	count INTEGER;
BEGIN
	FOR v_table_name IN SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
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

CREATE OR REPLACE FUNCTION test_utils.better_random(low INT = 1 ,high INT = 2147483647) RETURNS INT
AS $$
BEGIN
	RETURN floor(random()* (high-low + 1) + low);
END;
$$ language plpgsql STRICT;