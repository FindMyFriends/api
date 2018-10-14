CREATE FUNCTION tests.changing_exposed() RETURNS void
AS $$
DECLARE
  v_soulmate_id soulmates.id%type;
BEGIN
  SELECT samples.soulmate(json_build_object('is_exposed', FALSE)::jsonb) INTO v_soulmate_id;
  UPDATE soulmates SET is_exposed = FALSE WHERE id = v_soulmate_id; -- ok
  UPDATE soulmates SET is_exposed = FALSE WHERE id = v_soulmate_id; -- ok
  UPDATE soulmates SET is_exposed = TRUE WHERE id = v_soulmate_id; -- ok
    PERFORM assert.throws(
    format('UPDATE soulmates SET is_exposed = FALSE WHERE id = %L', v_soulmate_id),
    ROW('You can not revert expose decision', 'P0001')::error
  );
END
$$
LANGUAGE plpgsql;
