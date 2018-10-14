CREATE FUNCTION tests.recording_soulmate_notifications() RETURNS void
AS $$
DECLARE
  v_soulmate_id soulmates.id%type;
  v_demand_id demands.id%type;
  v_evolution_id evolutions.id%type;
BEGIN
  SELECT samples.soulmate(json_build_object('is_exposed', FALSE)::jsonb) INTO v_soulmate_id;
  SELECT demand_id INTO v_demand_id FROM soulmates WHERE id = v_soulmate_id;
  SELECT evolution_id INTO v_evolution_id FROM soulmates WHERE id = v_soulmate_id;

  PERFORM assert.same('soulmate-found', (SELECT type FROM notifications LIMIT 1));
  PERFORM assert.null((SELECT involved_seeker_id FROM notifications LIMIT 1));

  PERFORM assert.same('soulmate-pending_expose_permission', (SELECT type FROM notifications LIMIT 1 OFFSET 1));
  PERFORM assert.not_same((SELECT seeker_id FROM notifications LIMIT 1), (SELECT seeker_id FROM notifications LIMIT 1 OFFSET 1));

  UPDATE soulmates SET is_exposed = TRUE WHERE id = v_soulmate_id;
  PERFORM assert.same((SELECT seeker_id FROM notifications LIMIT 1 OFFSET 2), (SELECT seeker_id FROM notifications LIMIT 1));
  PERFORM assert.same('soulmate-exposed', (SELECT type FROM notifications LIMIT 1 OFFSET 2));
END
$$
LANGUAGE plpgsql;
