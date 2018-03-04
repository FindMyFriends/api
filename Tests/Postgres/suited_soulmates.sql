CREATE FUNCTION unit_tests.new_for_every_first() RETURNS TEST_RESULT AS $$
DECLARE
  v_demand_id integer;
  messages text[];
  v_soulmate record;
  new_soulmates CURSOR FOR
    SELECT "new"
    FROM suited_soulmates
    WHERE demand_id = v_demand_id;
BEGIN
  SELECT samples.demand() INTO v_demand_id;
  INSERT INTO soulmates (demand_id, evolution_id, score, version) VALUES
    (v_demand_id, (SELECT samples.evolution()), 7, 1),
    (v_demand_id, (SELECT samples.evolution()), 6, 1);
  OPEN new_soulmates;
  FETCH new_soulmates INTO v_soulmate;
  messages = messages || message FROM assert.is_true((SELECT v_soulmate.new));
  FETCH new_soulmates INTO v_soulmate;
  messages = messages || message FROM assert.is_true((SELECT v_soulmate.new));
  CLOSE new_soulmates;
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.not_new_for_second_and_more_version() RETURNS TEST_RESULT AS $$
DECLARE
  v_demand_id integer;
  messages text[];
  v_soulmate record;
  new_soulmates CURSOR FOR
    SELECT "new"
    FROM suited_soulmates
    WHERE demand_id = v_demand_id;
BEGIN
  SELECT samples.demand() INTO v_demand_id;
  INSERT INTO soulmates (demand_id, evolution_id, score, version) VALUES
    (v_demand_id, (SELECT samples.evolution()), 6, 2),
    (v_demand_id, (SELECT samples.evolution()), 8, 1);
  OPEN new_soulmates;
  FETCH new_soulmates INTO v_soulmate;
  messages = messages || message FROM assert.is_true((SELECT v_soulmate.new));
  FETCH new_soulmates INTO v_soulmate;
  messages = messages || message FROM assert.is_false((SELECT v_soulmate.new));
  CLOSE new_soulmates;
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.position_by_score() RETURNS TEST_RESULT AS $$
DECLARE
  v_demand_id integer;
  messages text[];
  v_soulmate record;
  new_soulmates CURSOR FOR
    SELECT score, position
    FROM suited_soulmates
    WHERE demand_id = v_demand_id;
BEGIN
  SELECT samples.demand() INTO v_demand_id;
  INSERT INTO soulmates (demand_id, evolution_id, score, version) VALUES
    (v_demand_id, (SELECT samples.evolution()), 6, 2),
    (v_demand_id, (SELECT samples.evolution()), 8, 1),
    (v_demand_id, (SELECT samples.evolution()), 9, 1);
  OPEN new_soulmates;
  FETCH new_soulmates INTO v_soulmate;
  messages = messages || message FROM assert.is_equal(1::bigint, (SELECT v_soulmate.position));
  messages = messages || message FROM assert.is_equal(9::numeric, (SELECT v_soulmate.score));
  FETCH new_soulmates INTO v_soulmate;
  messages = messages || message FROM assert.is_equal(2::bigint, (SELECT v_soulmate.position));
  messages = messages || message FROM assert.is_equal(8::numeric, (SELECT v_soulmate.score));
  FETCH new_soulmates INTO v_soulmate;
  messages = messages || message FROM assert.is_equal(3::bigint, (SELECT v_soulmate.position));
  messages = messages || message FROM assert.is_equal(6::numeric, (SELECT v_soulmate.score));
  CLOSE new_soulmates;
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.assigned_seeker() RETURNS TEST_RESULT AS $$
DECLARE
  v_seeker_id integer;
  v_demand_id integer;
  messages text[];
  v_soulmate record;
  new_soulmates CURSOR FOR
    SELECT seeker_id
    FROM suited_soulmates
    WHERE demand_id = v_demand_id;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT samples.demand(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_demand_id;
  INSERT INTO soulmates (demand_id, evolution_id, score, version) VALUES
    (v_demand_id, (SELECT samples.evolution()), 6, 2),
    (v_demand_id, (SELECT samples.evolution()), 8, 1),
    (v_demand_id, (SELECT samples.evolution()), 9, 1);
  OPEN new_soulmates;
  FETCH new_soulmates INTO v_soulmate;
  messages = messages || message FROM assert.is_equal(v_seeker_id, (SELECT v_soulmate.seeker_id));
  CLOSE new_soulmates;
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;