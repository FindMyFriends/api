CREATE FUNCTION tests.deleting_all_evidences() RETURNS void
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

  PERFORM assert.same(
    '',
    (
      SELECT test_utils.tables_not_matching_count(
        hstore(
          ARRAY ['hair', 'nails', 'faces', 'teeth', 'hands', 'beards', 'bodies', 'general', 'evolutions', 'descriptions', 'hand_hair', 'seekers', 'eyebrows', 'eyes'],
          ARRAY [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2]:: text[]
        )
      )
    )
  );
END
$$
LANGUAGE plpgsql;