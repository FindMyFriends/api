CREATE FUNCTION unit_tests.summary_for_demands() RETURNS TEST_RESULT AS $$
DECLARE
  v_seeker_id seekers.id%TYPE;
  v_demand_id demands.id%TYPE;
  v_soulmate_id1 soulmates.id%TYPE;
  v_soulmate_id2 soulmates.id%TYPE;
  messages text[];
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT samples.demand(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_demand_id;
  SELECT samples.soulmate(json_build_object('demand_id', v_demand_id, 'evolution_id', (SELECT samples.evolution()))::jsonb) INTO v_soulmate_id1;
  SELECT samples.soulmate(json_build_object('demand_id', v_demand_id, 'evolution_id', (SELECT samples.evolution()))::jsonb) INTO v_soulmate_id2;
  messages = messages || message FROM assert.is_equal(1, (SELECT COUNT(*)::integer FROM demand_summary WHERE demand_id = v_demand_id));
  messages = messages || message FROM assert.is_equal(ARRAY[v_soulmate_id1, v_soulmate_id2], (SELECT soulmates FROM demand_summary WHERE demand_id = v_demand_id));
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;