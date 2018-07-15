CREATE FUNCTION tests.is_evolution_owned_as_owner() RETURNS void
AS $$
DECLARE
  v_seeker_id seekers.id%type;
  v_evolution_id evolutions.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT samples.evolution(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_evolution_id;
  PERFORM assert.true(is_evolution_owned(v_evolution_id, v_seeker_id));
END
$$
LANGUAGE plpgsql;