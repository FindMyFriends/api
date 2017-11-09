CREATE OR REPLACE FUNCTION unit_tests.to_int4range() RETURNS TEST_RESULT AS $$
BEGIN
	RETURN (
		SELECT message FROM assert.is_equal(
			int4range(10, 20),
			(SELECT to_range(10, 20))
		)
	);
END
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION unit_tests.to_tstzrange() RETURNS TEST_RESULT AS $$
BEGIN
	RETURN (
		SELECT message FROM assert.is_equal(
			tstzrange('2017-01-01', '2018-01-01'),
			(SELECT to_range('2017-01-01'::TIMESTAMPTZ, '2018-01-01'::TIMESTAMPTZ))
		)
	);
END
$$
LANGUAGE plpgsql;