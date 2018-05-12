CREATE FUNCTION unit_tests.is_in_interval_range() RETURNS test_result
AS $$
DECLARE
  messages text[];
BEGIN
  messages = messages || message FROM assert.is_true(is_soulmate_request_refreshable('2015-01-01'::timestamptz));
  messages = messages || message FROM assert.is_equal(0, soulmate_request_refreshable_in('2015-01-01'::timestamptz));
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.is_out_of_allowed_interval() RETURNS test_result
AS $$
DECLARE
  messages text[];
BEGIN
  messages = messages || message FROM assert.is_false(is_soulmate_request_refreshable(NOW()));
  messages = messages || message FROM assert.is_not_equal(0, soulmate_request_refreshable_in(NOW()));
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.demand_not_refreshable_as_max() RETURNS test_result
AS $$
DECLARE
  v_demand_id demands.id%type;
  v_soulmate_request_id soulmate_requests.id%type;
BEGIN
  SELECT samples.demand()
  INTO v_demand_id;
  SELECT *
  FROM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', '2015-01-01'::timestamptz, 'status', 'pending')::jsonb)
  INTO v_soulmate_request_id;
  PERFORM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', NOW(), 'status', 'succeed', 'self_id', v_soulmate_request_id)::jsonb);
  RETURN message FROM assert.is_false(is_soulmate_request_refreshable(v_demand_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.demand_available_to_refresh() RETURNS test_result
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  SELECT samples.demand()
  INTO v_demand_id;
  PERFORM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', '2015-01-01'::timestamptz, 'status', 'succeed')::jsonb);
  RETURN message FROM assert.is_true(is_soulmate_request_refreshable(v_demand_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.demand_not_refreshable_pending() RETURNS test_result
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  SELECT samples.demand()
  INTO v_demand_id;
  PERFORM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', '2015-01-01'::timestamptz, 'status', 'pending')::jsonb);
  RETURN message FROM assert.is_false(is_soulmate_request_refreshable(v_demand_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.demand_not_refreshable_processing() RETURNS test_result
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  SELECT samples.demand()
  INTO v_demand_id;
  PERFORM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', '2015-01-01'::timestamptz, 'status', 'processing')::jsonb);
  RETURN message FROM assert.is_false(is_soulmate_request_refreshable(v_demand_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.demand_not_past_refreshable_not_now() RETURNS test_result
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  SELECT samples.demand()
  INTO v_demand_id;
  PERFORM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', '2015-01-01'::timestamptz, 'status', 'succeed')::jsonb);
  PERFORM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', NOW(), 'status', 'processing')::jsonb);
  RETURN message FROM assert.is_false(is_soulmate_request_refreshable(v_demand_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.refreshable_for_no_demand() RETURNS test_result
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  SELECT samples.demand()
  INTO v_demand_id;
  RETURN message FROM assert.is_true(is_soulmate_request_refreshable(v_demand_id));
END
$$
LANGUAGE plpgsql;