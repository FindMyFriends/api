CREATE FUNCTION unit_tests.age_year_same_return() RETURNS test_result
AS $$
DECLARE
  v_now CONSTANT timestamptz DEFAULT '2018-01-01'::timestamptz;
  v_age CONSTANT int4range DEFAULT int4range(19, 21);
BEGIN
  RETURN message FROM assert.is_equal(year_to_age(age_to_year(v_age, v_now), v_now), v_age);
END
$$
LANGUAGE plpgsql;
