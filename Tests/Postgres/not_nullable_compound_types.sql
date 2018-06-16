CREATE FUNCTION tests.not_nullable_and_default_compound_types() RETURNS void
AS $$
DECLARE
  c_schema CONSTANT text DEFAULT 'public';
  c_types CONSTANT text[] DEFAULT ARRAY['mass', 'length'];
BEGIN
  PERFORM assert.same(
    NULL,
    (
      SELECT json_agg(table_name)
      FROM information_schema.columns
      WHERE table_schema = c_schema
        AND (is_nullable = 'YES' OR column_default IS NULL)
        AND udt_name = ANY(c_types)
        AND table_name NOT IN (
          SELECT table_name
          FROM information_schema.views
          WHERE table_schema = c_schema
      )
    )::text
  );
END
$$
LANGUAGE plpgsql;
