CREATE FUNCTION tests.throwing_on_missing_value_length() RETURNS void
AS $$
BEGIN
  PERFORM assert.throws(
    format('SELECT is_length_valid(ROW(%L, %L))', NULL, 'mm'),
    ROW('Length with unit must contain value', 'P0001')::error
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.throwing_on_missing_length_unit() RETURNS void
AS $$
BEGIN
  PERFORM assert.throws(
    format('SELECT is_length_valid(ROW(%L, %L))', 10, NULL ),
    ROW('Length with value must contain unit', 'P0001')::error
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.passing_on_value_length_null() RETURNS void
AS $$
BEGIN
  PERFORM assert.true((SELECT is_length_valid(ROW(NULL, NULL))));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.passing_on_value_length_filled() RETURNS void
AS $$
BEGIN
  PERFORM assert.true((SELECT is_length_valid(ROW(0, 'mm'))));
END
$$
LANGUAGE plpgsql;