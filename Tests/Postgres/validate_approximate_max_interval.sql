CREATE FUNCTION unit_tests.throwing_on_overstepped_max() RETURNS test_result
AS $BODY$
BEGIN
  RETURN message FROM assert.throws(
    FORMAT ('SELECT validate_approximate_max_interval(ROW(%L, %L, %L))', NOW(), 'sooner', 'P3M'),
    ROW($$Overstepped maximum of 2 days$$, 'P0001')::error
  );
END
$BODY$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.passing_on_approximation_in_range() RETURNS test_result
AS $$
BEGIN
  RETURN message FROM assert.is_true(
    (SELECT validate_approximate_max_interval(ROW(NOW(), 'sooner', 'PT10H')))
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.passing_on_approximation_same_as_max() RETURNS test_result
AS $$
BEGIN
  RETURN message FROM assert.is_true(
    (SELECT validate_approximate_max_interval(ROW(NOW(), 'sooner', 'P2D')))
  );
END
$$
LANGUAGE plpgsql;