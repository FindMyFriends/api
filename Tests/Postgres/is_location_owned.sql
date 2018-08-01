CREATE FUNCTION tests.is_evolution_location_owned_as_owner() RETURNS void
AS $$
DECLARE
  v_seeker_id seekers.id%type;
  v_evolution_id evolutions.id%type;
  v_location_id locations.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT samples.evolution(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_evolution_id;
  SELECT samples.location() INTO v_location_id;
  PERFORM samples.evolution_location(json_build_object('evolution_id', v_evolution_id, 'location_id', v_location_id)::jsonb);
  PERFORM assert.true(is_location_owned(v_location_id, v_seeker_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.is_demand_location_owned_as_owner() RETURNS void
AS $$
DECLARE
  v_seeker_id seekers.id%type;
  v_demand_id demands.id%type;
  v_location_id locations.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT samples.demand(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_demand_id;
  SELECT samples.location() INTO v_location_id;
  PERFORM samples.demand_location(json_build_object('demand_id', v_demand_id, 'location_id', v_location_id)::jsonb);
  PERFORM assert.true(is_location_owned(v_location_id, v_seeker_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.is_location_owned_as_foreign() RETURNS void
AS $$
BEGIN
  PERFORM assert.false(is_location_owned(1, 1));
END
$$
LANGUAGE plpgsql;

