CREATE FUNCTION unit_tests.is_in_interval_range() RETURNS TEST_RESULT AS $$
DECLARE
  messages text[];
BEGIN
  messages = messages || message FROM assert.is_true(is_soulmate_request_refreshable('2015-01-01'::timestamptz));
  messages = messages || message FROM assert.is_equal(0, soulmate_request_refreshable_in('2015-01-01'::timestamptz));
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.is_out_of_allowed_interval() RETURNS TEST_RESULT AS $$
DECLARE
  messages text[];
BEGIN
  messages = messages || message FROM assert.is_false(is_soulmate_request_refreshable(NOW()));
  messages = messages || message FROM assert.is_not_equal(0, soulmate_request_refreshable_in(NOW()));
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.demand_not_yet_refreshable_as_max() RETURNS TEST_RESULT AS $$
DECLARE
  v_demand_id demands.id%TYPE;
BEGIN
  SELECT samples.demand() INTO v_demand_id;
  PERFORM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', NOW())::jsonb);
  PERFORM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', '2015-01-01'::timestamptz)::jsonb);
  RETURN message FROM assert.is_false(is_soulmate_request_refreshable(v_demand_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.demand_available_to_refresh() RETURNS TEST_RESULT AS $$
DECLARE
  v_demand_id demands.id%TYPE;
BEGIN
  SELECT samples.demand() INTO v_demand_id;
  PERFORM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', '2015-01-01'::timestamptz)::jsonb);
  RETURN message FROM assert.is_true(is_soulmate_request_refreshable(v_demand_id));
END
$$
LANGUAGE plpgsql;