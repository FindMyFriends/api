CREATE FUNCTION unit_tests.null_for_empty_note() RETURNS test_result
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  INSERT INTO demands (seeker_id, description_id, created_at, location_id, note) VALUES (
    (SELECT samples.seeker()),
    (SELECT samples.description()),
    NOW() - INTERVAL '10 MINUTE',
    (SELECT samples.location()),
    ''
  )
  RETURNING id
  INTO v_demand_id;
  RETURN message FROM assert.is_true((SELECT note IS NULL note FROM demands WHERE id = v_demand_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.null_for_empty_note_update() RETURNS test_result
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  INSERT INTO demands (seeker_id, description_id, created_at, location_id, note) VALUES (
    (SELECT samples.seeker()),
    (SELECT samples.description()),
    NOW() - INTERVAL '10 MINUTE',
    (SELECT samples.location()),
    'foo'
  )
  RETURNING id
  INTO v_demand_id;
  UPDATE demands
  SET note = ''
  WHERE id = v_demand_id;
  RETURN message FROM assert.is_true((SELECT note IS NULL note FROM demands WHERE id = v_demand_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.null_for_empty_space_note() RETURNS test_result
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  INSERT INTO demands (seeker_id, description_id, created_at, location_id, note) VALUES (
    (SELECT samples.seeker()),
    (SELECT samples.description()),
    NOW() - INTERVAL '10 MINUTE',
    (SELECT samples.location()),
    '  '
  )
  RETURNING id
  INTO v_demand_id;
  RETURN message FROM assert.is_true((SELECT note IS NULL note FROM demands WHERE id = v_demand_id));
END
$$
LANGUAGE plpgsql;