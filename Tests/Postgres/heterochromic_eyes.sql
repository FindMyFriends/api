CREATE FUNCTION tests.same_eyes_with_different_id() RETURNS void
AS $$
BEGIN
  PERFORM assert.false(
    heterochromic_eyes(
      ROW(1, 2, TRUE)::eyes,
      ROW(2, 2, TRUE)::eyes
    )
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.different_eyes_on_not_matching_one_column() RETURNS void
AS $$
BEGIN
  PERFORM assert.true(
    heterochromic_eyes(
      ROW(1, 2, FALSE)::eyes,
      ROW(2, 2, TRUE)::eyes
    )
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.one_of_null_leading_to_false() RETURNS void
AS $$
BEGIN
  PERFORM assert.true(
    heterochromic_eyes(
      NULL,
      ROW(2, 2, TRUE)::eyes
    )
  );
END
$$
LANGUAGE plpgsql;