CREATE FUNCTION unit_tests.throwing_on_exactly_timeline_side_with_approximation() RETURNS test_result
AS $BODY$
BEGIN
  RETURN message FROM assert.throws(
    FORMAT ('SELECT validate_approximate_timestamptz(ROW(%L, %L, %L))', NOW(), 'exactly', 'PT10H'),
    ROW($$"Exactly" timeline_side can not have approximation$$, 'P0001')::error
  );
END
$BODY$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.passing_with_approximation_without_exactly_timeline_side() RETURNS test_result
AS $$
BEGIN
  RETURN message FROM assert.is_true(
    (SELECT validate_approximate_timestamptz(ROW(NOW(), 'sooner', 'PT10H')))
  );
END
$$
LANGUAGE plpgsql;