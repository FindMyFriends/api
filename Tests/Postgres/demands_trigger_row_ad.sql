CREATE FUNCTION tests.deleting_all_evidences_after_demand() RETURNS void
AS $$
DECLARE
  inserted_demand_id demands.id%type;
BEGIN
  INSERT INTO demands (seeker_id, description_id, created_at) VALUES (
    (SELECT samples.seeker()),
    (SELECT samples.description()),
    NOW()
  )
  RETURNING id
  INTO inserted_demand_id;

  DELETE FROM demands WHERE id = inserted_demand_id;

  PERFORM assert.same('', (SELECT test_utils.tables_not_matching_count('seekers=>1')));
END
$$
LANGUAGE plpgsql;