CREATE FUNCTION tests.is_soulmate_permitted_as_owner() RETURNS void
AS $$
DECLARE
  v_demand_id seekers.id%type;
  v_seeker_id seekers.id%type;
  v_soulmate_id soulmates.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT samples.demand(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_demand_id;
  SELECT samples.soulmate(json_build_object('demand_id', v_demand_id)::jsonb) INTO v_soulmate_id;
  PERFORM assert.true(is_soulmate_permitted(v_soulmate_id, v_seeker_id));
END
$$
LANGUAGE plpgsql;
