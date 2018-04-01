CREATE FUNCTION unit_tests.soulmates_logged_as_searched() RETURNS TEST_RESULT AS $$
DECLARE
  v_demand_id demands.id%TYPE;
  v_seeker_id seekers.id%TYPE;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT samples.demand(json_build_object('seeker_id', v_seeker_id)::jsonb) INTO v_demand_id;
  INSERT INTO soulmate_requests (demand_id, searched_at, status) VALUES (v_demand_id, '2006-01-01', 'pending');
  RETURN message FROM assert.is_equal(
    1,
    (SELECT COUNT(*)::integer FROM suited_soulmates WHERE demand_id = v_demand_id AND seeker_id = v_seeker_id)
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.last_search_time() RETURNS TEST_RESULT AS $$
DECLARE
  v_demand_id demands.id%TYPE;
  messages text[];
BEGIN
  SELECT samples.demand() INTO v_demand_id;
  INSERT INTO soulmates (demand_id, evolution_id, score, version, related_at) VALUES
    (v_demand_id, (SELECT samples.evolution()), 6, 1, '2010-01-01');
  INSERT INTO soulmate_requests (demand_id, searched_at, status) VALUES (v_demand_id, '2005-01-01', 'pending');
  INSERT INTO soulmate_requests (demand_id, searched_at, status) VALUES (v_demand_id, '2006-01-01', 'pending');
  INSERT INTO soulmate_requests (demand_id, searched_at, status) VALUES (v_demand_id, NOW(), 'pending');
  -- ADDED BY TRIGGER
  messages = messages || message FROM assert.is_equal(
    EXTRACT(YEAR FROM NOW()),
    EXTRACT(YEAR FROM (SELECT searched_at FROM suited_soulmates WHERE demand_id = v_demand_id))
  );
  messages = messages || message FROM assert.is_equal(
    '2010-01-01'::timestamptz,
    (SELECT related_at FROM suited_soulmates WHERE demand_id = v_demand_id)
  );
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.new_for_every_first() RETURNS TEST_RESULT AS $$
DECLARE
  v_demand_id demands.id%TYPE;
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
  INSERT INTO soulmate_requests (demand_id, searched_at, status) VALUES (v_demand_id, NOW(), 'pending');
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
  v_demand_id demands.id%TYPE;
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
  INSERT INTO soulmate_requests (demand_id, searched_at, status) VALUES (v_demand_id, NOW(), 'pending');
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
  v_demand_id demands.id%TYPE;
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
  INSERT INTO soulmate_requests (demand_id, searched_at, status) VALUES (v_demand_id, NOW(), 'pending');
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
  v_seeker_id seekers.id%TYPE;
  v_demand_id demands.id%TYPE;
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
  INSERT INTO soulmate_requests (demand_id, searched_at, status) VALUES (v_demand_id, NOW(), 'pending');
  OPEN new_soulmates;
  FETCH new_soulmates INTO v_soulmate;
  messages = messages || message FROM assert.is_equal(v_seeker_id, (SELECT v_soulmate.seeker_id));
  CLOSE new_soulmates;
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;