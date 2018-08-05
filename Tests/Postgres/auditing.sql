CREATE FUNCTION tests.checking_after_update() RETURNS void
AS $$
BEGIN
  INSERT INTO spots (coordinates, met_at) VALUES (POINT(50.5, 50.4), ROW('2005-05-05 05:05:05', 'exactly', NULL));
  UPDATE spots SET coordinates = POINT(10.1, 10.0), met_at = ROW('2001-01-01 01:01:01', 'sooner', 'PT1H');
  PERFORM assert.same(1, (SELECT COUNT(*)::integer FROM audit.history));
  PERFORM assert.same(
   '{"id": 1, "met_at": {"moment": "2005-05-05T05:05:05+00:00", "approximation": null, "timeline_side": "exactly"}, "coordinates": "(50.5,50.4)"}',
   (SELECT old FROM audit.history)
  );
  PERFORM assert.same(
   '{"id": 1, "met_at": {"moment": "2001-01-01T01:01:01+00:00", "approximation": "PT1H", "timeline_side": "sooner"}, "coordinates": "(10.1,10)"}',
   (SELECT new FROM audit.history)
  );
  PERFORM assert.same('UPDATE', (SELECT operation FROM audit.history));
  PERFORM assert.same('spots', (SELECT "table" FROM audit.history));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.checking_after_delete() RETURNS void
AS $$
BEGIN
  INSERT INTO spots (coordinates, met_at) VALUES (POINT(50.5, 50.4), ROW('2005-05-05 05:05:05', 'exactly', NULL));
  DELETE FROM spots;
  PERFORM assert.same(1, (SELECT COUNT(*)::integer FROM audit.history));
  PERFORM assert.same(
   '{"id": 2, "met_at": {"moment": "2005-05-05T05:05:05+00:00", "approximation": null, "timeline_side": "exactly"}, "coordinates": "(50.5,50.4)"}',
   (SELECT old FROM audit.history)
  );
  PERFORM assert.same(NULL, (SELECT new FROM audit.history));
  PERFORM assert.same('DELETE', (SELECT operation FROM audit.history));
  PERFORM assert.same('spots', (SELECT "table" FROM audit.history));
END
$$
LANGUAGE plpgsql;