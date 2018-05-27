CREATE FUNCTION unit_tests.filled_tables() RETURNS test_result
AS $$
DECLARE
  v_seeker_id seekers.id%type;
  v_evolution_id evolutions.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT created_base_evolution(v_seeker_id, 'man'::sex, 1::smallint, '[1996,1996]'::real_birth_year, 'Dom'::text, 'Self'::text) INTO v_evolution_id;
  RETURN message FROM assert.is_equal(
    '',
    (
      SELECT test_utils.tables_not_matching_count(
        hstore(
          ARRAY['hair', 'nails', 'faces', 'teeth', 'hands', 'beards', 'bodies', 'general', 'evolutions', 'descriptions', 'hand_hair', 'seekers', 'eyebrows', 'eyes'],
          ARRAY[1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2]::text[]
        )
      )
    )
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.throwing_on_created_evolution() RETURNS test_result
AS $$
DECLARE
  v_seeker_id seekers.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  PERFORM samples.evolution(json_build_object('seeker_id', v_seeker_id)::jsonb);
  RETURN message FROM assert.throws(
    FORMAT('SELECT created_base_evolution(%L, %L, %L, %L, %L, %L)', v_seeker_id, 'man', 1, '[1996,1996]', 'Dom', 'Self'),
    ROW(FORMAT('Base evolution for seeker %L is already created.', v_seeker_id), 'P0001')::error
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.successfully_created_base_evolution() RETURNS test_result
AS $$
DECLARE
  v_seeker_id seekers.id%type;
  v_evolution_id evolutions.id%type;
BEGIN
  SELECT samples.seeker() INTO v_seeker_id;
  SELECT created_base_evolution(v_seeker_id, 'man'::sex, 1::smallint, '[1996,1996]'::real_birth_year, 'Dom'::text, 'Self'::text) INTO v_evolution_id;
  RETURN message FROM assert.is_true((SELECT v_seeker_id = seeker_id FROM evolutions WHERE id = v_evolution_id));
END
$$
LANGUAGE plpgsql;