CREATE FUNCTION tests.last_search_time() RETURNS void
AS $$
DECLARE
  v_demand_id demands.id%type;
  v_soulmate_request_id soulmate_requests.id%type;
BEGIN
  SELECT samples.demand()
  INTO v_demand_id;
  INSERT INTO soulmates (demand_id, evolution_id, score, version, related_at) VALUES (
    v_demand_id,
    (SELECT samples.evolution()),
    6,
    1,
    '2010-01-01'
  );
  INSERT INTO soulmate_requests (demand_id, searched_at, status) VALUES (v_demand_id, '2005-01-01', 'pending')
  RETURNING id
  INTO v_soulmate_request_id;
  INSERT INTO soulmate_requests (demand_id, searched_at, status, self_id) VALUES (v_demand_id, '2006-01-01', 'processing', v_soulmate_request_id);
  INSERT INTO soulmate_requests (demand_id, searched_at, status, self_id) VALUES (v_demand_id, NOW(), 'succeed', v_soulmate_request_id);
  -- ADDED BY TRIGGER
  PERFORM assert.same(
    EXTRACT(YEAR FROM NOW()),
    EXTRACT(YEAR FROM ( SELECT searched_at FROM suited_soulmates WHERE demand_id = v_demand_id))
  );
  PERFORM assert.same(
    '2010-01-01'::timestamptz,
    (SELECT related_at FROM suited_soulmates WHERE demand_id = v_demand_id)
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.new_for_every_first() RETURNS void
AS $$
DECLARE
  v_demand_id demands.id%type;
  v_soulmate record;
    new_soulmates CURSOR FOR
    SELECT "new"
    FROM suited_soulmates
    WHERE demand_id = v_demand_id;
BEGIN
  SELECT samples.demand()
  INTO v_demand_id;
  INSERT INTO soulmates (demand_id, evolution_id, score, version) VALUES (
    v_demand_id,
    (SELECT samples.evolution()),
    7,
    1
  ), (
    v_demand_id,
    (SELECT samples.evolution()),
    6,
    1
  );
  INSERT INTO soulmate_requests (demand_id, searched_at, status) VALUES (v_demand_id, NOW(), 'pending');
  OPEN new_soulmates;
  FETCH new_soulmates INTO v_soulmate;
  PERFORM assert.true(( SELECT v_soulmate.new));
  FETCH new_soulmates INTO v_soulmate;
  PERFORM assert.true(( SELECT v_soulmate.new));
  CLOSE new_soulmates;
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.not_new_for_second_and_more_version() RETURNS void
AS $$
DECLARE
  v_demand_id demands.id%type;
  v_soulmate record;
    new_soulmates CURSOR FOR
    SELECT "new"
    FROM suited_soulmates
    WHERE demand_id = v_demand_id;
BEGIN
  SELECT samples.demand()
  INTO v_demand_id;
  INSERT INTO soulmates (demand_id, evolution_id, score, version) VALUES (
    v_demand_id,
    (SELECT samples.evolution()),
    6,
    2
  ), (
    v_demand_id,
    (SELECT samples.evolution()),
    8,
    1
  );
  INSERT INTO soulmate_requests (demand_id, searched_at, status) VALUES (v_demand_id, NOW(), 'pending');
  OPEN new_soulmates;
  FETCH new_soulmates INTO v_soulmate;
  PERFORM assert.true(( SELECT v_soulmate.new));
  FETCH new_soulmates INTO v_soulmate;
  PERFORM assert.false(( SELECT v_soulmate.new));
  CLOSE new_soulmates;
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.position_by_score() RETURNS void
AS $$
DECLARE
  v_demand_id demands.id%type;
  v_soulmate record;
    new_soulmates CURSOR FOR
    SELECT
      score,
      position
    FROM suited_soulmates
    WHERE demand_id = v_demand_id;
BEGIN
  SELECT samples.demand()
  INTO v_demand_id;
  INSERT INTO soulmates (demand_id, evolution_id, score, version) VALUES (
    v_demand_id,
    (SELECT samples.evolution()),
    6,
    2
  ), (
    v_demand_id,
    (SELECT samples.evolution()),
    8,
    1
  ), (
    v_demand_id,
    (SELECT samples.evolution()),
    9,
    1
  );
  INSERT INTO soulmate_requests (demand_id, searched_at, status) VALUES (v_demand_id, NOW(), 'pending');
  OPEN new_soulmates;
  FETCH new_soulmates INTO v_soulmate;
  PERFORM assert.same(1:: BIGINT, ( SELECT v_soulmate.position));
  PERFORM assert.same(9:: NUMERIC, ( SELECT v_soulmate.score));
  FETCH new_soulmates INTO v_soulmate;
  PERFORM assert.same(2:: BIGINT, ( SELECT v_soulmate.position));
  PERFORM assert.same(8:: NUMERIC, ( SELECT v_soulmate.score));
  FETCH new_soulmates INTO v_soulmate;
  PERFORM assert.same(3:: BIGINT, ( SELECT v_soulmate.position));
  PERFORM assert.same(6:: NUMERIC, ( SELECT v_soulmate.score));
  CLOSE new_soulmates;
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.assigned_seeker() RETURNS void
AS $$
DECLARE
  v_seeker_id seekers.id%type;
  v_demand_id demands.id%type;
  v_soulmate record;
    new_soulmates CURSOR FOR
    SELECT seeker_id
    FROM suited_soulmates
    WHERE demand_id = v_demand_id;
BEGIN
  SELECT samples.seeker()
  INTO v_seeker_id;
  SELECT samples.demand(json_build_object('seeker_id', v_seeker_id)::jsonb)
  INTO v_demand_id;
  INSERT INTO soulmates (demand_id, evolution_id, score, version) VALUES (
    v_demand_id, (SELECT samples.evolution()),
    6,
    2
  ), (
    v_demand_id,
    (SELECT samples.evolution()),
    8,
    1
  ), (
    v_demand_id,
    (SELECT samples.evolution()),
    9,
    1
  );
  INSERT INTO soulmate_requests (demand_id, searched_at, status) VALUES (v_demand_id, NOW(), 'pending');
  OPEN new_soulmates;
  FETCH new_soulmates INTO v_soulmate;
  PERFORM assert.same(v_seeker_id, (SELECT v_soulmate.seeker_id));
  CLOSE new_soulmates;
END
$$
LANGUAGE plpgsql;