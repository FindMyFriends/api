CREATE FUNCTION tests.throwing_on_deleting_base() RETURNS void
AS $$
DECLARE
  inserted_evolution_id evolutions.id%type;
BEGIN
  INSERT INTO evolutions (seeker_id, description_id, evolved_at) VALUES (
    (SELECT samples.seeker()),
    (SELECT samples.description()),
    NOW()
  )
  RETURNING id
  INTO inserted_evolution_id;

  PERFORM assert.throws(
    FORMAT ('DELETE FROM evolutions WHERE id = %L', inserted_evolution_id),
    ROW('Base evolution can not be reverted', 'P0001')::error
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.passing_on_not_deleting_base() RETURNS void
AS $$
DECLARE
  inserted_evolution_id evolutions.id%type;
  inserted_seeker_id seekers.id%type;
BEGIN
  SELECT samples.seeker()
  INTO inserted_seeker_id;

  INSERT INTO evolutions (seeker_id, description_id, evolved_at) VALUES (
    inserted_seeker_id,
    (SELECT samples.description()),
    NOW()
  )
  RETURNING id
  INTO inserted_evolution_id;

  INSERT INTO evolutions (seeker_id, description_id, evolved_at) VALUES (
    inserted_seeker_id,
    (SELECT samples.description()),
    NOW()
  );

  DELETE FROM evolutions
  WHERE id = inserted_evolution_id;
END
$$
LANGUAGE plpgsql;