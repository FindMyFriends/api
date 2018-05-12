CREATE FUNCTION unit_tests.trigger_before_insert() RETURNS test_result
AS $$
BEGIN
  INSERT INTO beards (color_id, length, style) VALUES (8, ROW(100, 'cm'::length_units), 'ok');
  RETURN message FROM assert.is_equal(
    (SELECT LENGTH FROM beards),
    ROW(1000, 'mm'::length_units):: LENGTH
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
  SET length = ROW(200, 'cm'::length_units)
  WHERE id = v_beard_id;
  RETURN message FROM assert.is_equal(
    (SELECT LENGTH FROM beards WHERE id = v_beard_id),
    ROW(2000, 'mm'::length_units):: LENGTH
  );
END
$$
LANGUAGE plpgsql;