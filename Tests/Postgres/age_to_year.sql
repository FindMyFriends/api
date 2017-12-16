CREATE OR REPLACE FUNCTION unit_tests.age_to_year_as_ranges() RETURNS TEST_RESULT AS $$
DECLARE
	messages TEXT[];
BEGIN
	messages = messages || message FROM assert.is_equal(
		age_to_year(int4range(19, 21), tstzrange('2017-01-01', '2020-01-01')),
		int4range(1996, 1998)
	);
	RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION unit_tests.age_to_year_as_timestamptz() RETURNS TEST_RESULT AS $$
DECLARE
	messages TEXT[];
BEGIN
	messages = messages || message FROM assert.is_equal(
	age_to_year(int4range(19, 21), '2017-01-01'::timestamptz),
	int4range(1996, 1998)
	);
	RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;