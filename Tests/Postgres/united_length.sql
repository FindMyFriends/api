CREATE FUNCTION unit_tests.centimeters_to_millimeters() RETURNS test_result
AS $$
BEGIN
  RETURN message FROM assert.is_equal(
    united_length(ROW(160, 'cm')),
    ROW(1600, 'mm'):: LENGTH
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.millimeters_as_millimeters() RETURNS test_result
AS $$
BEGIN
  RETURN message FROM assert.is_equal(
    united_length(ROW(160, 'mm')),
    ROW(160, 'mm'):: LENGTH
  );
END
$$
LANGUAGE plpgsql;