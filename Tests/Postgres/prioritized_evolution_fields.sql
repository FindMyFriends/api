CREATE FUNCTION tests.number_of_changed_columns() RETURNS void
AS $$
DECLARE
  v_seeker_id seekers.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  PERFORM samples.evolution(json_build_object('seeker_id', v_seeker_id, 'firstname', 'Dom')::jsonb);
  PERFORM samples.evolution(json_build_object('seeker_id', v_seeker_id, 'firstname', 'Dominik')::jsonb);
  PERFORM samples.evolution();
  REFRESH MATERIALIZED VIEW prioritized_evolution_fields;
  PERFORM assert.same(2, (SELECT COUNT(*) FROM prioritized_evolution_fields)::integer);
  PERFORM assert.same(
    2,
    (
      SELECT (array_agg(each.value))[1]::integer
      FROM prioritized_evolution_fields, jsonb_each(columns) AS each
      WHERE seeker_id = v_seeker_id
    )
  );
  PERFORM assert.same(
    5,
    (
      SELECT array_length(array_agg(each.value)::text::integer[], 1)
      FROM prioritized_evolution_fields, jsonb_each(columns) AS each
      WHERE seeker_id = v_seeker_id
    )
  );
END
$$
LANGUAGE plpgsql;