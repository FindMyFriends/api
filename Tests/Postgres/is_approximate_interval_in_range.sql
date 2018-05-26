CREATE FUNCTION unit_tests.throwing_on_overstepped_max() RETURNS test_result
AS $BODY$
BEGIN
  RETURN message FROM assert.throws(
    FORMAT ('SELECT is_approximate_interval_in_range(ROW(%L, %L, %L))', NOW(), 'sooner', 'P3M'),
    ROW($$Overstepped maximum of 2 days$$, 'P0001')::error
  );
END
$BODY$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.passing_on_approximation_in_range() RETURNS test_result
AS $$
BEGIN
  RETURN message FROM assert.is_true(
    (SELECT is_approximate_interval_in_range(ROW(NOW(), 'sooner', 'PT10H')))
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.passing_on_approximation_same_as_max() RETURNS test_result
AS $$
BEGIN
  RETURN message FROM assert.is_true(
    (SELECT is_approximate_interval_in_range(ROW(NOW(), 'sooner', 'P2D')))
  );
END
$$
LANGUAGE plpgsql;