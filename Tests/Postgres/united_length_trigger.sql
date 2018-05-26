CREATE FUNCTION unit_tests.trigger_before_insert() RETURNS test_result
AS $$
BEGIN
  INSERT INTO beards (color_id, length, style) VALUES (8, ROW(100, 'cm'::length_units), 'ok');
  RETURN message FROM assert.is_equal(
    (SELECT length FROM beards),
    ROW(100, 'cm'::length_units)::length
  );
END
$$
LANGUAGE plpgsql;


CREATE FUNCTION unit_tests.trigger_before_update() RETURNS test_result
AS $$
DECLARE
  v_beard_id beards.id%type;
BEGIN
  INSERT INTO beards (color_id, length, style) VALUES (8, ROW(100, 'cm'::length_units), 'ok')
  RETURNING id
  INTO v_beard_id;
  UPDATE beards
  SET length = ROW(20, 'mm'::length_units)
  WHERE id = v_beard_id;
  RETURN message FROM assert.is_equal(
    (SELECT length FROM beards WHERE id = v_beard_id),
    ROW(2, 'cm'::length_units)::length
  );
END
$$
LANGUAGE plpgsql;