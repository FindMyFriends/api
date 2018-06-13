CREATE FUNCTION tests.throwing_on_missing_value_mass() RETURNS void
AS $$
BEGIN
  PERFORM assert.throws(
    format('SELECT is_mass_valid(ROW(%L, %L))', NULL, 'kg'),
    ROW('Mass with unit must contain value', 'P0001')::error
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.throwing_on_missing_mass_unit() RETURNS void
AS $$
BEGIN
  PERFORM assert.throws(
    FORMAT ('SELECT is_mass_valid(ROW(%L, %L))', 10, NULL ),
    ROW('Mass with value must contain unit', 'P0001')::error
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.passing_on_mass_value_null() RETURNS void
AS $$
BEGIN
  PERFORM assert.true((SELECT is_mass_valid(ROW(NULL, NULL))));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.passing_on_mass_value_filled() RETURNS void
AS $$
BEGIN
  PERFORM assert.true((SELECT is_mass_valid(ROW(10, 'kg'))));
END
$$
LANGUAGE plpgsql;