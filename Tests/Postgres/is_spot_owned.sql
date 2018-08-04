CREATE FUNCTION tests.is_evolution_spot_owned_as_owner() RETURNS void
AS $$
DECLARE
  v_seeker_id seekers.id%type;
  v_evolution_id evolutions.id%type;
  v_spot_id spots.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT samples.evolution(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_evolution_id;
  SELECT samples.spot() INTO v_spot_id;
  PERFORM samples.evolution_spot(json_build_object('evolution_id', v_evolution_id, 'spot_id', v_spot_id)::jsonb);
  PERFORM assert.true(is_spot_owned(v_spot_id, v_seeker_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.is_demand_spot_owned_as_owner() RETURNS void
AS $$
DECLARE
  v_seeker_id seekers.id%type;
  v_demand_id demands.id%type;
  v_spot_id spots.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT samples.demand(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_demand_id;
  SELECT samples.spot() INTO v_spot_id;
  PERFORM samples.demand_spot(json_build_object('demand_id', v_demand_id, 'spot_id', v_spot_id)::jsonb);
  PERFORM assert.true(is_spot_owned(v_spot_id, v_seeker_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.is_spot_owned_as_foreign() RETURNS void
AS $$
BEGIN
  PERFORM assert.false(is_spot_owned(1, 1));
END
$$
LANGUAGE plpgsql;

