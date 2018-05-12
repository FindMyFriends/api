CREATE FUNCTION unit_tests.suited_soulmates_with_yours_and_theirs() RETURNS test_result
AS $$
DECLARE
  v_yours_demand_id demands.id%type;
  v_theirs_demand_id demands.id%type;
  v_theirs_evolution_id evolutions.id%type;
  v_yours_evolution_id evolutions.id%type;
  v_yours_seeker_id seekers.id%type;
  v_theirs_seeker_id seekers.id%type;
  v_soulmate_id1 soulmates.id%type;
  v_soulmate_id2 soulmates.id%type;
  messages text[];
BEGIN
  SELECT samples.seeker()
  INTO v_yours_seeker_id;
  SELECT samples.seeker()
  INTO v_theirs_seeker_id;
  SELECT samples.demand(json_build_object('seeker_id', v_yours_seeker_id)::jsonb)
  INTO v_yours_demand_id;
  SELECT samples.demand(json_build_object('seeker_id', v_theirs_seeker_id)::jsonb)
  INTO v_theirs_demand_id;
  SELECT samples.evolution(json_build_object('seeker_id', v_theirs_seeker_id)::jsonb)
  INTO v_theirs_evolution_id;
  SELECT samples.evolution(json_build_object('seeker_id', v_yours_seeker_id)::jsonb)
  INTO v_yours_evolution_id;

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
  RETURNING id
  INTO v_soulmate_id1;
  INSERT INTO soulmates (demand_id, evolution_id, score, version, related_at) VALUES (
    v_theirs_demand_id,
    v_yours_evolution_id,
    6,
    1,
    NOW()
  )
  RETURNING id
  INTO v_soulmate_id2;
  INSERT INTO soulmate_requests (demand_id, searched_at, status) VALUES (v_yours_demand_id, NOW(), 'pending');
  INSERT INTO soulmate_requests (demand_id, searched_at, status) VALUES (v_theirs_demand_id, NOW(), 'pending');
  messages = messages || message FROM assert.is_equal(
    ARRAY [v_soulmate_id1, v_soulmate_id2],
    (SELECT array_agg(id) FROM with_suited_soulmate_ownership(v_yours_seeker_id))
  );
  messages = messages || message FROM assert.is_equal(
    ARRAY ['yours'::ownerships, 'theirs'::ownerships],
    (SELECT array_agg(ownership) FROM with_suited_soulmate_ownership(v_yours_seeker_id))
  );
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;