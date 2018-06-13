CREATE FUNCTION tests.is_in_interval_range() RETURNS void
AS $$
BEGIN
  PERFORM assert.true(is_soulmate_request_refreshable('2015-01-01'::timestamptz));
  PERFORM assert.same(0, soulmate_request_refreshable_in('2015-01-01'::timestamptz));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.is_out_of_allowed_interval() RETURNS void
AS $$
BEGIN
  PERFORM assert.false(is_soulmate_request_refreshable(NOW()));
  PERFORM assert.not_same(0, soulmate_request_refreshable_in(NOW()));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.demand_not_refreshable_as_max() RETURNS void
AS $$
DECLARE
  v_demand_id demands.id%type;
  v_soulmate_request_id soulmate_requests.id%type;
BEGIN
  SELECT samples.demand() INTO v_demand_id;
  SELECT *
  FROM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', '2015-01-01'::timestamptz, 'status', 'pending')::jsonb)
  INTO v_soulmate_request_id;
  PERFORM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', NOW(), 'status', 'succeed', 'self_id', v_soulmate_request_id)::jsonb);
  PERFORM assert.false(is_soulmate_request_refreshable(v_demand_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.demand_available_to_refresh() RETURNS void
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  SELECT samples.demand() INTO v_demand_id;
  PERFORM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', '2015-01-01'::timestamptz, 'status', 'succeed')::jsonb);
  PERFORM assert.true(is_soulmate_request_refreshable(v_demand_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.demand_not_refreshable_pending() RETURNS void
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  SELECT samples.demand() INTO v_demand_id;
  PERFORM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', '2015-01-01'::timestamptz, 'status', 'pending')::jsonb);
  PERFORM assert.false(is_soulmate_request_refreshable(v_demand_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.demand_not_refreshable_processing() RETURNS void
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  SELECT samples.demand() INTO v_demand_id;
  PERFORM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', '2015-01-01'::timestamptz, 'status', 'processing')::jsonb);
  PERFORM assert.false(is_soulmate_request_refreshable(v_demand_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.demand_not_past_refreshable_not_now() RETURNS void
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  SELECT samples.demand() INTO v_demand_id;
  PERFORM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', '2015-01-01'::timestamptz, 'status', 'succeed')::jsonb);
  PERFORM samples.soulmate_request(json_build_object('demand_id', v_demand_id, 'searched_at', NOW(), 'status', 'processing')::jsonb);
  PERFORM assert.false(is_soulmate_request_refreshable(v_demand_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.refreshable_for_no_demand() RETURNS void
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  SELECT samples.demand() INTO v_demand_id;
  PERFORM assert.true(is_soulmate_request_refreshable(v_demand_id));
END
$$
LANGUAGE plpgsql;