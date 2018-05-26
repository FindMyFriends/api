CREATE FUNCTION unit_tests.throwing_on_missing_value() RETURNS test_result
AS $$
BEGIN
  RETURN message FROM assert.throws(
    FORMAT('SELECT is_mass_valid(ROW(%L, %L))', NULL, 'kg'),
    ROW('Mass with unit must contain value', 'P0001')::error
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.throwing_on_missing_unit() RETURNS test_result
AS $$
BEGIN
  RETURN message FROM assert.throws(
    FORMAT ('SELECT is_mass_valid(ROW(%L, %L))', 10, NULL ),
    ROW('Mass with value must contain unit', 'P0001')::error
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.passing_on_both_null() RETURNS test_result
AS $$
BEGIN
  RETURN message FROM assert.is_true((SELECT is_mass_valid(ROW(NULL, NULL ))));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.passing_on_both_filled() RETURNS test_result
AS $$
BEGIN
  RETURN message FROM assert.is_true((SELECT is_mass_valid(ROW(10, 'kg'))));
END
$$
LANGUAGE plpgsql;