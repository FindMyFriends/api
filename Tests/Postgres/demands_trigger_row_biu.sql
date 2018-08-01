CREATE FUNCTION tests.null_for_empty_note() RETURNS void
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  INSERT INTO demands (seeker_id, description_id, created_at, note) VALUES (
    (SELECT samples.seeker()),
    (SELECT samples.description()),
    NOW() - INTERVAL '10 MINUTE',
    ''
  )
  RETURNING id
  INTO v_demand_id;
  PERFORM assert.true((SELECT note IS NULL note FROM demands WHERE id = v_demand_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.null_for_empty_note_update() RETURNS void
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  INSERT INTO demands (seeker_id, description_id, created_at, note) VALUES (
    (SELECT samples.seeker()),
    (SELECT samples.description()),
    NOW() - INTERVAL '10 MINUTE',
    'foo'
  )
  RETURNING id
  INTO v_demand_id;
  UPDATE demands
  SET note = ''
  WHERE id = v_demand_id;
  PERFORM assert.true((SELECT note IS NULL note FROM demands WHERE id = v_demand_id));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.null_for_empty_space_note() RETURNS void
AS $$
DECLARE
  v_demand_id demands.id%type;
BEGIN
  INSERT INTO demands (seeker_id, description_id, created_at, note) VALUES (
    (SELECT samples.seeker()),
    (SELECT samples.description()),
    NOW() - INTERVAL '10 MINUTE',
    '  '
  )
  RETURNING id
  INTO v_demand_id;
  PERFORM assert.true((SELECT note IS NULL note FROM demands WHERE id = v_demand_id));
END
$$
LANGUAGE plpgsql;