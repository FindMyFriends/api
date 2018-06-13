CREATE FUNCTION tests.making_range_by_two() RETURNS void
AS $$
BEGIN
  PERFORM assert.same(approximated_rating(5), int4range(3, 7, '[]'));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.not_overstepping_min() RETURNS void
AS $$
BEGIN
  PERFORM assert.same(approximated_rating(1), int4range(1, 3, '[]'));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.not_overstepping_max() RETURNS void
AS $$
BEGIN
  PERFORM assert.same(approximated_rating(9), int4range(7, 10, '[]'));
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.keeping_null() RETURNS void
AS $$
BEGIN
  PERFORM assert.same( approximated_rating(NULL), NULL);
END
$$
LANGUAGE plpgsql;