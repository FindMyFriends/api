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