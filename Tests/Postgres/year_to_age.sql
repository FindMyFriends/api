CREATE FUNCTION tests.timestamp_to_age_range() RETURNS void
AS $$
BEGIN
  PERFORM assert.same(
    year_to_age(int4range(1993, 2000), tstzrange('2015-01-01', NOW())),
    int4range(15, 22)
  );
  PERFORM assert.same(
    year_to_age(int4range(1993, 2000), '2015-01-01'::timestamp),
    int4range(15, 22)
  );
END
$$
LANGUAGE plpgsql;