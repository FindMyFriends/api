CREATE FUNCTION unit_tests.throwing_on_running_task() RETURNS TEST_RESULT AS $$
DECLARE
  v_demand_id demands.id%TYPE;
BEGIN
  SELECT samples.demand() INTO v_demand_id;
  INSERT INTO soulmate_requests (demand_id, searched_at, self_id, status) VALUES (v_demand_id, NOW(), NULL, 'pending');
  RETURN message FROM assert.throws(
    format(
      'INSERT INTO soulmate_requests (demand_id, searched_at, self_id, status) VALUES (%L, %L, %L, %L)',
      v_demand_id,
      NOW(),
      NULL,
      'pending'
    ),
    ROW('Seeking for soulmate is already in progress', 'P0001')::error
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.passing_on_subsequent() RETURNS TEST_RESULT AS $$
DECLARE
  v_demand_id demands.id%TYPE;
  v_soulmate_request_id soulmate_requests.id%TYPE;
BEGIN
  SELECT samples.demand() INTO v_demand_id;
  INSERT INTO soulmate_requests (demand_id, searched_at, self_id, status) VALUES (v_demand_id, '2015-01-01'::timestamptz, NULL, 'pending')
  RETURNING id
  INTO v_soulmate_request_id;
  INSERT INTO soulmate_requests (demand_id, searched_at, self_id, status) VALUES (v_demand_id, NOW(), v_soulmate_request_id, 'succeed');
  RETURN '';
END
$$
LANGUAGE plpgsql;