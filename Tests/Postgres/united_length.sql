CREATE FUNCTION unit_tests.keeping_cm() RETURNS test_result
AS $$
DECLARE
  messages text[];
BEGIN
  messages = messages || message FROM assert.is_equal(
    united_length(ROW(160, 'cm')),
    ROW(160, 'cm'):: LENGTH
  );
  messages = messages || message FROM assert.is_equal(
    united_length(ROW(16, 'cm')),
    ROW(16, 'cm'):: LENGTH
  );
  messages = messages || message FROM assert.is_equal(
    united_length(ROW(1, 'cm')),
    ROW(1, 'cm'):: LENGTH
  );
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.keeping_mm() RETURNS test_result
AS $$
DECLARE
  messages text[];
BEGIN
  messages = messages || message FROM assert.is_equal(
    united_length(ROW(1, 'mm')),
    ROW(1, 'mm'):: LENGTH
  );
  messages = messages || message FROM assert.is_equal(
    united_length(ROW(9, 'mm')),
    ROW(9, 'mm'):: LENGTH
  );
  messages = messages || message FROM assert.is_equal(
    united_length(ROW(12, 'mm')),
    ROW(12, 'mm'):: LENGTH
  );
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.mm_to_cm_if_long() RETURNS test_result
AS $$
DECLARE
  messages text[];
BEGIN
  messages = messages || message FROM assert.is_equal(
    united_length(ROW(10, 'mm')),
    ROW(1, 'cm'):: LENGTH
  );
  messages = messages || message FROM assert.is_equal(
    united_length(ROW(100, 'mm')),
    ROW(10, 'cm'):: LENGTH
  );
  RETURN array_to_string(messages, '');
END
$$
LANGUAGE plpgsql;