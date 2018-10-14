CREATE FUNCTION tests.sync_seen_and_seen_at() RETURNS void
AS $$
DECLARE
  v_notification_id notifications.id%type;
  v_seen_at notifications.seen_at%type;
  v_seeker_id seekers.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  INSERT INTO notifications (seeker_id, type, seen, seen_at) VALUES (v_seeker_id, 'soulmate-found', FALSE, NULL)
  RETURNING id INTO v_notification_id;

  PERFORM assert.false((SELECT seen FROM notifications WHERE id = v_notification_id));
  PERFORM assert.null((SELECT seen_at FROM notifications WHERE id = v_notification_id));

  UPDATE notifications SET seen = TRUE WHERE id = v_notification_id;
  PERFORM assert.true((SELECT seen FROM notifications WHERE id = v_notification_id));
  PERFORM assert.not_null((SELECT seen_at FROM notifications WHERE id = v_notification_id));

  SELECT seen_at INTO v_seen_at FROM notifications WHERE id = v_notification_id;
  UPDATE notifications SET seen = TRUE WHERE id = v_notification_id;
  PERFORM assert.same(v_seen_at, (SELECT seen_at FROM notifications WHERE id = v_notification_id));

  UPDATE notifications SET seen_at = '2010-01-01' WHERE id = v_notification_id;
  PERFORM assert.same(2010, (SELECT EXTRACT('year' FROM seen_at)::integer FROM notifications WHERE id = v_notification_id));

  UPDATE notifications SET seen = FALSE WHERE id = v_notification_id;
  PERFORM assert.null((SELECT seen_at FROM notifications WHERE id = v_notification_id));

  UPDATE notifications SET seen_at = '2010-01-01' WHERE id = v_notification_id;
  PERFORM assert.true((SELECT seen FROM notifications WHERE id = v_notification_id));
END
$$
LANGUAGE plpgsql;
