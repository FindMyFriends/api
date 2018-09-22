CREATE FUNCTION tests.allowed_year_combination() RETURNS void
AS $BODY$
BEGIN
  INSERT INTO general (sex, ethnic_group_id, birth_year_range, birth_year) VALUES ('man', 1, int4range(1996, 1998), NULL);
  INSERT INTO general (sex, ethnic_group_id, birth_year_range, birth_year) VALUES ('man', 1, NULL, 1996);
  PERFORM assert.throws(
    $$INSERT INTO general (sex, ethnic_group_id, birth_year_range, birth_year) VALUES ('man', 1, NULL, NULL)$$,
    ROW('new row for relation "general" violates check constraint "general_birth_years_check"', '23514')::error
  );
  PERFORM assert.throws(
    $$INSERT INTO general (sex, ethnic_group_id, birth_year_range, birth_year) VALUES ('man', 1, int4range(1996, 1998), 1996)$$,
    ROW('new row for relation "general" violates check constraint "general_birth_years_check"', '23514')::error
  );
END
$BODY$
LANGUAGE plpgsql;

