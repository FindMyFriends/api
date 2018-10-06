CREATE FUNCTION tests.checking_after_update() RETURNS void
AS $$
BEGIN
  INSERT INTO spots (coordinates, met_at) VALUES (POINT(50.5, 50.4), ROW('2005-05-05 05:05:05', 'exactly', NULL));
  UPDATE spots SET coordinates = POINT(10.1, 10.0), met_at = ROW('2001-01-01 01:01:01', 'sooner', 'PT1H');

  PERFORM assert.same(2, (SELECT COUNT(*)::integer FROM audit.history));
  PERFORM assert.same(
   '{"id": 1, "met_at": {"moment": "2005-05-05T05:05:05+00:00", "approximation": null, "timeline_side": "exactly"}, "coordinates": "(50.5,50.4)"}',
   (SELECT old FROM audit.history OFFSET 1)
  );
  PERFORM assert.same(
   '{"id": 1, "met_at": {"moment": "2001-01-01T01:01:01+00:00", "approximation": "PT1H", "timeline_side": "sooner"}, "coordinates": "(10.1,10)"}',
   (SELECT new FROM audit.history OFFSET 1)
  );

  PERFORM assert.same('INSERT', (SELECT operation FROM audit.history LIMIT 1));
  PERFORM assert.same('spots', (SELECT "table" FROM audit.history LIMIT 1));
  PERFORM assert.null((SELECT seeker_id FROM audit.history LIMIT 1));

  PERFORM assert.same('UPDATE', (SELECT operation FROM audit.history OFFSET 1));
  PERFORM assert.same('spots', (SELECT "table" FROM audit.history OFFSET 1));
  PERFORM assert.null((SELECT seeker_id FROM audit.history OFFSET 1));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.checking_after_delete() RETURNS void
AS $$
BEGIN
  INSERT INTO spots (coordinates, met_at) VALUES (POINT(50.5, 50.4), ROW('2005-05-05 05:05:05', 'exactly', NULL));
  DELETE FROM spots;

  PERFORM assert.same(2, (SELECT COUNT(*)::integer FROM audit.history));
  PERFORM assert.same(
   '{"id": 2, "met_at": {"moment": "2005-05-05T05:05:05+00:00", "approximation": null, "timeline_side": "exactly"}, "coordinates": "(50.5,50.4)"}',
   (SELECT old FROM audit.history OFFSET 1)
  );

  PERFORM assert.same('INSERT', (SELECT operation FROM audit.history LIMIT 1));
  PERFORM assert.same('spots', (SELECT "table" FROM audit.history LIMIT 1));
  PERFORM assert.null((SELECT seeker_id FROM audit.history LIMIT 1));

  PERFORM assert.same(NULL, (SELECT new FROM audit.history OFFSET 1));
  PERFORM assert.same('DELETE', (SELECT operation FROM audit.history OFFSET 1));
  PERFORM assert.same('spots', (SELECT "table" FROM audit.history OFFSET 1));
  PERFORM assert.null((SELECT seeker_id FROM audit.history OFFSET 1));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.auditing_with_seeker_id() RETURNS void
AS $$
BEGIN
  PERFORM globals_set_seeker(10);
  INSERT INTO spots (coordinates, met_at) VALUES (POINT(50.5, 50.4), ROW('2005-05-05 05:05:05', 'exactly', NULL));
  PERFORM assert.same(10, (SELECT seeker_id FROM audit.history));
END
$$
LANGUAGE plpgsql;