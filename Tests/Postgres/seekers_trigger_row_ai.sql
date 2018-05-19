CREATE FUNCTION unit_tests.creating_verification_code() RETURNS test_result
AS $$
DECLARE
  v_seeker_id seekers.id%type;
BEGIN
  INSERT INTO seekers (email, password) VALUES ('foo@bar.cz', 'heslo123')
  RETURNING id
  INTO v_seeker_id;
  RETURN message FROM assert.is_equal(
    1,
    (SELECT COUNT(*) FROM verification_codes WHERE seeker_id = v_seeker_id)::integer
  );
END
$$
LANGUAGE plpgsql;