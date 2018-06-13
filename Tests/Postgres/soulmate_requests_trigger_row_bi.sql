CREATE FUNCTION tests.throwing_on_running_task() RETURNS void
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  SELECT samples.demand()
  INTO v_demand_id;
  INSERT INTO soulmate_requests (demand_id, searched_at, self_id, status) VALUES (v_demand_id, NOW(), NULL, 'pending');
  PERFORM assert.throws(
    FORMAT (
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

CREATE FUNCTION tests.passing_on_subsequent() RETURNS void
AS $$
DECLARE
  v_demand_id demands.id%type;
  v_soulmate_request_id soulmate_requests.id%type;
BEGIN
  SELECT samples.demand()
  INTO v_demand_id;
  INSERT INTO soulmate_requests (demand_id, searched_at, self_id, status) VALUES (v_demand_id, '2015-01-01'::timestamptz, NULL, 'pending')
  RETURNING id
  INTO v_soulmate_request_id;
  INSERT INTO soulmate_requests (demand_id, searched_at, self_id, status) VALUES (v_demand_id, NOW(), v_soulmate_request_id, 'succeed');
END
$$
LANGUAGE plpgsql;