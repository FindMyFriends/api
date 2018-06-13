CREATE FUNCTION tests.throwing_on_overstepped_max() RETURNS void
AS $BODY$
BEGIN
  PERFORM assert.throws(
    FORMAT ('SELECT is_approximate_interval_in_range(ROW(%L, %L, %L))', NOW(), 'sooner', 'P3M'),
    ROW($$Overstepped maximum of 2 days$$, 'P0001')::error
  );
END
$BODY$
LANGUAGE plpgsql;

CREATE FUNCTION tests.passing_on_approximation_in_range() RETURNS void
AS $$
BEGIN
  PERFORM assert.true(
    (SELECT is_approximate_interval_in_range(ROW(NOW(), 'sooner', 'PT10H')))
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.passing_on_approximation_same_as_max() RETURNS void
AS $$
BEGIN
  PERFORM assert.true(
    (SELECT is_approximate_interval_in_range(ROW(NOW(), 'sooner', 'P2D')))
  );
END
$$
LANGUAGE plpgsql;