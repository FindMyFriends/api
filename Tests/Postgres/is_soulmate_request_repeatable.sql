CREATE FUNCTION unit_tests.is_in_interval_range() RETURNS TEST_RESULT AS $$
BEGIN
  RETURN message FROM assert.is_true(is_soulmate_request_repeatable('2015-01-01'));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.is_out_of_allowed_interval() RETURNS TEST_RESULT AS $$
BEGIN
  RETURN message FROM assert.is_false(is_soulmate_request_repeatable(NOW()));
END
$$
LANGUAGE plpgsql;
