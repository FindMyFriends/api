CREATE FUNCTION unit_tests.trigger_before_insert() RETURNS TEST_RESULT AS $$
BEGIN
	INSERT INTO beards (color_id, length, style) VALUES (1, ROW(100, 'cm'::length_units), 'ok');
	RETURN message FROM assert.is_equal(
		(SELECT length FROM beards),
		ROW(1000, 'mm'::length_units)::length
	);
END
$$
LANGUAGE plpgsql;


CREATE FUNCTION unit_tests.trigger_before_update() RETURNS TEST_RESULT AS $$
DECLARE
	v_beard_id beards.id%TYPE;
BEGIN
	INSERT INTO beards (color_id, length, style) VALUES (1, ROW(100, 'cm'::length_units), 'ok') RETURNING id INTO v_beard_id;
	UPDATE beards SET length = ROW(200, 'cm'::length_units) WHERE id = v_beard_id;
	RETURN message FROM assert.is_equal(
		(SELECT length FROM beards WHERE id = v_beard_id),
		ROW(2000, 'mm'::length_units)::length
	);
END
$$
LANGUAGE plpgsql;