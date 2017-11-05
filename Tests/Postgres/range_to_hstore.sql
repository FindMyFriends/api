CREATE OR REPLACE FUNCTION unit_tests.all_ranges_to_hstore() RETURNS TEST_RESULT AS $$
DECLARE
	messages TEXT[];
BEGIN
	messages = messages || message FROM assert.is_equal(
		(SELECT range_to_hstore(int4range(10, 20))),
		'from=>10,to=>20'::hstore
	);
	messages = messages || message FROM assert.is_equal(
		(SELECT range_to_hstore('[10,20)'::int4range)),
		'from=>10,to=>20'::hstore
	);
	messages = messages || message FROM assert.is_equal(
		(SELECT range_to_hstore(tstzrange('2017-01-01', '2018-01-01'))),
		'from=>"2017-01-01 00:00:00+00",to=>"2018-01-01 00:00:00+00"'::hstore
	);
	RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION unit_tests.unspecified_bound_leading_to_null() RETURNS TEST_RESULT AS $$
DECLARE
	messages TEXT[];
BEGIN
	messages = messages || message FROM assert.is_equal(
		(SELECT range_to_hstore('[10,)'::int4range)),
		'from=>10,to=>NULL'::hstore
	);
	messages = messages || message FROM assert.is_equal(
		(SELECT range_to_hstore('[,10)'::int4range)),
		'from=>NULL,to=>10'::hstore
	);
	messages = messages || message FROM assert.is_equal(
		(SELECT range_to_hstore('[,)'::int4range)),
		'from=>NULL,to=>NULL'::hstore
	);
	RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;