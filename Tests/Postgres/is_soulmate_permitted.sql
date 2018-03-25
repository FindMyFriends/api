CREATE FUNCTION unit_tests.is_permitted_as_owner() RETURNS TEST_RESULT AS $$
DECLARE
  v_demand_id seekers.id%TYPE;
  v_seeker_id seekers.id%TYPE;
  v_soulmate_id soulmates.id%TYPE;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT samples.demand(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_demand_id;
  SELECT samples.soulmate(json_build_object('demand_id', v_demand_id)::jsonb) INTO v_soulmate_id;
  RETURN message FROM assert.is_true(is_soulmate_permitted(v_soulmate_id, v_seeker_id));
END
$$
LANGUAGE plpgsql;
