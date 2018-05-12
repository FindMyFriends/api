CREATE FUNCTION unit_tests.is_permitted_as_owner() RETURNS test_result
AS $$
DECLARE
  v_seeker_id seekers.id%type;
  v_evolution_id evolutions.id%type;
BEGIN
  SELECT samples.seeker()
  INTO v_seeker_id;
  SELECT samples.evolution(json_build_object('seeker_id', v_seeker_id)::jsonb)
  INTO v_evolution_id;
  RETURN message FROM assert.is_true(is_evolution_permitted(v_evolution_id, v_seeker_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.permitted_as_soulmate_match() RETURNS test_result
AS $$
DECLARE
  v_seeker_id seekers.id%type;
  v_demand_id demands.id%type;
  v_evolution_id evolutions.id%type;
BEGIN
  SELECT samples.seeker()
  INTO v_seeker_id;
  SELECT samples.demand(json_build_object('seeker_id', v_seeker_id)::jsonb)
  INTO v_demand_id;
  SELECT samples.evolution()
  INTO v_evolution_id;
  PERFORM samples.soulmate(json_build_object('demand_id', v_demand_id, 'evolution_id', v_evolution_id)::jsonb);
  RETURN message FROM assert.is_true(is_evolution_permitted(v_evolution_id, v_seeker_id));
END
$$
LANGUAGE plpgsql;