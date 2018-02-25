CREATE FUNCTION unit_tests.making_range_by_one() RETURNS TEST_RESULT AS $$
BEGIN
  RETURN message FROM ASSERT.is_equal(
    approximated_breast_size('C'::breast_sizes),
    int4range(2, 4, '[]')
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.not_overstepping_min() RETURNS TEST_RESULT AS $$
BEGIN
  RETURN message FROM ASSERT.is_equal(
    approximated_breast_size('A'::breast_sizes),
    int4range(1, 2, '[]')
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.not_overstepping_max() RETURNS TEST_RESULT AS $$
BEGIN
  RETURN message FROM ASSERT.is_equal(
    approximated_breast_size('D'::breast_sizes),
    int4range(3, 4, '[]')
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.keeping_null() RETURNS TEST_RESULT AS $$
BEGIN
  RETURN message FROM ASSERT.is_equal(
    approximated_breast_size(NULL),
    NULL
  );
END
$$
LANGUAGE plpgsql;