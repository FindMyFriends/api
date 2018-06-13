CREATE FUNCTION tests.throwing_on_exactly_timeline_side_with_approximation() RETURNS void
AS $BODY$
BEGIN
  PERFORM assert.throws(
    FORMAT ('SELECT is_approximate_timestamptz_valid(ROW(%L, %L, %L))', NOW(), 'exactly', 'PT10H'),
    ROW($$"Exactly" timeline_side can not have approximation$$, 'P0001')::error
  );
END
$BODY$
LANGUAGE plpgsql;

CREATE FUNCTION tests.passing_with_approximation_without_exactly_timeline_side() RETURNS void
AS $$
BEGIN
  PERFORM assert.true((SELECT is_approximate_timestamptz_valid(ROW(NOW(), 'sooner', 'PT10H'))));
END
$$
LANGUAGE plpgsql;