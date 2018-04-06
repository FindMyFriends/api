CREATE TYPE name_type AS ENUM ('Dom', 'Kat');
CREATE TYPE brand_type AS ENUM ('Dell', 'Apple');

CREATE FUNCTION unit_tests.fail_on_value_out_of_enum() RETURNS TEST_RESULT AS $$
DECLARE
  messages text[];
BEGIN
  messages = messages || message FROM assert.throws(
    format('SELECT check_enum_value(%L, %L)', 'name_type', 'foo'),
    ROW('"name_type" must be one of: "Dom", "Kat" - "foo" was given', 'P0001')::error
  );
  messages = messages || message FROM assert.throws(
    format(
      'SELECT check_enum_value(%L::json, %L::json)',
      json_build_object('brand', 'brand_type'),
      json_build_object('brand', 'bar')
    ),
    ROW('"brand_type" must be one of: "Dell", "Apple" - "bar" was given', 'P0001')::error
  );
  messages = messages || message FROM assert.is_false(
    (SELECT is_enum_value('name_type', 'foo'))
  );
  messages = messages || message FROM assert.is_false(
    (SELECT is_enum_value(json_build_object('brand', 'brand_type'), json_build_object('brand', 'bar')))
  );
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.passing_on_value_in_format() RETURNS TEST_RESULT AS $$
DECLARE
  messages text[];
BEGIN
  PERFORM check_enum_value('name_type', 'Dom');
  messages = messages || message FROM assert.is_true((SELECT is_enum_value('name_type', 'Dom')));
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;