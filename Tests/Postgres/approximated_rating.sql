CREATE FUNCTION unit_tests.making_range_by_two() RETURNS TEST_RESULT AS $$
BEGIN
  RETURN message FROM ASSERT.is_equal(
    approximated_rating(5),
    int4range(3, 7, '[]')
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.not_overstepping_min() RETURNS TEST_RESULT AS $$
BEGIN
  RETURN message FROM ASSERT.is_equal(
  approximated_rating(1),
  int4range(1, 3, '[]')
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.not_overstepping_max() RETURNS TEST_RESULT AS $$
BEGIN
  RETURN message FROM ASSERT.is_equal(
    approximated_rating(9),
    int4range(7, 10, '[]')
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.keeping_null() RETURNS TEST_RESULT AS $$
BEGIN
  RETURN message FROM ASSERT.is_equal(
    approximated_rating(NULL),
    NULL
  );
END
$$
LANGUAGE plpgsql;