CREATE FUNCTION unit_tests.compare_as_common_seeker() RETURNS test_result
AS $$
DECLARE
  v_demand_id demands.id%type;
  v_seeker_id seekers.id%type;
  v_evolution_id evolutions.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT samples.evolution(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_evolution_id;
  SELECT samples.demand(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_demand_id;
  RETURN message FROM assert.is_true((SELECT is_common_seeker(v_demand_id, v_evolution_id)));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.compare_as_different_seeker() RETURNS test_result
AS $$
DECLARE
  v_demand_id demands.id%type;
  v_evolution_id evolutions.id%type;
BEGIN
  SELECT samples.evolution() INTO v_evolution_id;
  SELECT samples.demand() INTO v_demand_id;
  RETURN message FROM assert.is_false((SELECT is_common_seeker(v_demand_id, v_evolution_id)));
END
$$
LANGUAGE plpgsql;
