CREATE FUNCTION tests.owned_as_demand_owner() RETURNS void
AS $$
DECLARE
  v_demand_id seekers.id%type;
  v_seeker_id seekers.id%type;
  v_soulmate_id soulmates.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT samples.demand(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_demand_id;
  SELECT samples.soulmate(json_build_object('demand_id', v_demand_id)::jsonb) INTO v_soulmate_id;
  PERFORM assert.true(is_demanding_soulmate_owned(v_soulmate_id, v_seeker_id));
  PERFORM assert.true(is_soulmate_owned(v_soulmate_id, v_seeker_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.owned_as_evolution_owner() RETURNS void
AS $$
DECLARE
  v_evolution_id seekers.id%type;
  v_seeker_id seekers.id%type;
  v_soulmate_id soulmates.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT samples.evolution(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_evolution_id;
  SELECT samples.soulmate(json_build_object('evolution_id', v_evolution_id)::jsonb) INTO v_soulmate_id;
  PERFORM assert.true(is_evolving_soulmate_owned(v_soulmate_id, v_seeker_id));
  PERFORM assert.true(is_soulmate_owned(v_soulmate_id, v_seeker_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.not_soulmate_owner_at_all() RETURNS void
AS $$
BEGIN
  PERFORM assert.false(is_demanding_soulmate_owned(1, 2));
  PERFORM assert.false(is_evolving_soulmate_owned(1, 2));
  PERFORM assert.false(is_soulmate_owned(1, 2));
END
$$
LANGUAGE plpgsql;