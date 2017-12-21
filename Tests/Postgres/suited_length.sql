CREATE FUNCTION unit_tests.keeping_millimeters_if_not_necessary_to_convert() RETURNS TEST_RESULT AS $$
BEGIN
	RETURN message FROM assert.is_equal(
		suited_length(ROW(1, 'mm')),
		ROW(1, 'mm')::length
	);
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.converting_too_big_millimeter_to_centimeter() RETURNS TEST_RESULT AS $$
BEGIN
	RETURN message FROM assert.is_equal(
		suited_length(ROW(10, 'mm')),
		ROW(1.00000000000000000000, 'cm')::length
	);
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.converting_too_big_millimeter_to_centimeter_with_rest() RETURNS TEST_RESULT AS $$
BEGIN
	RETURN message FROM assert.is_equal(
		suited_length(ROW(16, 'mm')),
		ROW(1.60000000000000000000, 'cm')::length
	);
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.converting_always_centimeters_to_millimeters() RETURNS TEST_RESULT AS $$
BEGIN
	RETURN message FROM assert.is_equal(
		suited_length(ROW(16, 'cm')),
		ROW(160, 'mm')::length
	);
END
$$
LANGUAGE plpgsql;