CREATE FUNCTION unit_tests.same_eyes_with_different_id() RETURNS TEST_RESULT AS $$
BEGIN
  RETURN message FROM assert.is_false(
    heterochromic_eyes(
      ROW(1, 2, TRUE)::eyes,
      ROW(2, 2, TRUE)::eyes
    )
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.different_eyes_on_not_matching_one_column() RETURNS TEST_RESULT AS $$
BEGIN
  RETURN message FROM assert.is_true(
    heterochromic_eyes(
      ROW(1, 2, FALSE)::eyes,
      ROW(2, 2, TRUE)::eyes
    )
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.one_of_null_leading_to_false() RETURNS TEST_RESULT AS $$
BEGIN
  RETURN message FROM assert.is_true(
    heterochromic_eyes(
      NULL,
      ROW(2, 2, TRUE)::eyes
    )
  );
END
$$
LANGUAGE plpgsql;