CREATE FUNCTION tests.visible_as_found() RETURNS void
AS $$
DECLARE
  v_seeker_id seekers.id%type;
  v_seeker2_id seekers.id%type;
  v_evolution_id evolutions.id%type;
  v_demand_id demands.id%type;
  v_soulmate_id soulmates.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT samples.seeker() INTO v_seeker2_id;

  SELECT samples.demand(json_build_object('seeker_id', v_seeker2_id)::jsonb) INTO v_demand_id;
  SELECT samples.evolution(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_evolution_id;

  SELECT samples.soulmate(json_build_object('demand_id', v_demand_id, 'evolution_id', v_evolution_id)::jsonb) INTO v_soulmate_id;
  PERFORM assert.true(is_evolution_visible(v_evolution_id, v_seeker2_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.not_visible_unknown() RETURNS void
AS $$
DECLARE
  v_seeker_id seekers.id%type;
  v_seeker2_id seekers.id%type;
  v_foreign_seeker_id seekers.id%type;
  v_evolution_id evolutions.id%type;
  v_demand_id demands.id%type;
  v_soulmate_id soulmates.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT samples.seeker() INTO v_seeker2_id;
  SELECT samples.seeker() INTO v_foreign_seeker_id;

  SELECT samples.demand(json_build_object('seeker_id', v_seeker2_id)::jsonb) INTO v_demand_id;
  SELECT samples.evolution(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_evolution_id;

  SELECT samples.soulmate(json_build_object('demand_id', v_demand_id, 'evolution_id', v_evolution_id)::jsonb) INTO v_soulmate_id;
  PERFORM assert.false(is_evolution_visible(v_evolution_id, v_foreign_seeker_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.visible_for_owner() RETURNS void
AS $$
DECLARE
  v_seeker_id seekers.id%type;
  v_evolution_id evolutions.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT samples.evolution(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_evolution_id;
  PERFORM assert.true(is_evolution_visible(v_evolution_id, v_seeker_id));
END
$$
LANGUAGE plpgsql;