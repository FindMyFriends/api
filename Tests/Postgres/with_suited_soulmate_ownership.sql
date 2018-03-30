CREATE FUNCTION unit_tests.suited_soulmates_with_yours_and_theirs() RETURNS TEST_RESULT AS $$
DECLARE
  v_yours_demand_id demands.id%TYPE;
  v_theirs_demand_id demands.id%TYPE;
  v_theirs_evolution_id evolutions.id%TYPE;
  v_yours_evolution_id evolutions.id%TYPE;
  v_yours_seeker_id seekers.id%TYPE;
  v_theirs_seeker_id seekers.id%TYPE;
  v_soulmate_id1 soulmates.id%TYPE;
  v_soulmate_id2 soulmates.id%TYPE;
  messages TEXT[];
BEGIN
  SELECT samples.seeker() INTO v_yours_seeker_id;
  SELECT samples.seeker() INTO v_theirs_seeker_id;
  SELECT samples.demand(json_build_object('seeker_id', v_yours_seeker_id)::jsonb) INTO v_yours_demand_id;
  SELECT samples.demand(json_build_object('seeker_id', v_theirs_seeker_id)::jsonb) INTO v_theirs_demand_id;
  SELECT samples.evolution(json_build_object('seeker_id', v_theirs_seeker_id)::jsonb) INTO v_theirs_evolution_id;
  SELECT samples.evolution(json_build_object('seeker_id', v_yours_seeker_id)::jsonb) INTO v_yours_evolution_id;

  INSERT INTO soulmates (demand_id, evolution_id, score, version, related_at) VALUES (
    (SELECT samples.demand()),
    (SELECT samples.evolution()),
    6,
    1,
    NOW()
  );
  INSERT INTO soulmates (demand_id, evolution_id, score, version, related_at) VALUES (
    v_yours_demand_id,
    v_theirs_evolution_id,
    6,
    1,
    NOW()
  )
  RETURNING id INTO v_soulmate_id1;
  INSERT INTO soulmates (demand_id, evolution_id, score, version, related_at) VALUES (
    v_theirs_demand_id,
    v_yours_evolution_id,
    6,
    1,
    NOW()
  )
  RETURNING id INTO v_soulmate_id2;
  messages = messages || message FROM assert.is_equal(
    ARRAY[v_soulmate_id1, v_soulmate_id2],
    (SELECT array_agg(id) FROM with_suited_soulmate_ownership(v_yours_seeker_id))
  );
  messages = messages || message FROM assert.is_equal(
    ARRAY['yours'::ownerships, 'theirs'::ownerships],
    (SELECT array_agg(ownership) FROM with_suited_soulmate_ownership(v_yours_seeker_id))
  );
   RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;