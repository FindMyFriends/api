CREATE FUNCTION tests.number_of_changed_columns() RETURNS void
AS $$
DECLARE
  v_seeker_id seekers.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  PERFORM samples.evolution(json_build_object('seeker_id', v_seeker_id, 'firstname', 'Dom', 'sex', 'man')::jsonb);
  PERFORM samples.evolution(json_build_object('seeker_id', v_seeker_id, 'firstname', 'Dominik', 'sex', 'man')::jsonb);
  PERFORM samples.evolution();
  REFRESH MATERIALIZED VIEW prioritized_evolution_fields;
  PERFORM assert.same(2, (SELECT COUNT(*) FROM prioritized_evolution_fields)::integer);
  PERFORM assert.same(
    2,
    (
      SELECT max(each.value::integer)
      FROM prioritized_evolution_fields, jsonb_each(columns) AS each
      WHERE seeker_id = v_seeker_id
    )
  );
    PERFORM assert.same(
    1,
    (
      SELECT min(each.value::integer)
      FROM prioritized_evolution_fields, jsonb_each(columns) AS each
      WHERE seeker_id = v_seeker_id
    )
  );
  PERFORM assert.same(
    3,
    (
      SELECT array_length(array_agg(each.value)::text::integer[], 1)
      FROM prioritized_evolution_fields, jsonb_each(columns) AS each
      WHERE seeker_id = v_seeker_id
    )
  );
END
$$
LANGUAGE plpgsql;