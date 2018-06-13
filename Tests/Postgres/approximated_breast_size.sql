CREATE FUNCTION tests.making_range_by_one() RETURNS void
AS $$
BEGIN
  PERFORM assert.same(
    approximated_breast_size('C'::breast_sizes),
    int4range(2, 4, '[]')
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.not_overstepping_breast_min() RETURNS void
AS $$
BEGIN
  PERFORM assert.same(
    approximated_breast_size('A'::breast_sizes),
    int4range(1, 2, '[]')
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.not_overstepping_breast_max() RETURNS void
AS $$
BEGIN
  PERFORM assert.same(
    approximated_breast_size('D'::breast_sizes),
    int4range(3, 4, '[]')
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.keeping_breast_null() RETURNS void
AS $$
BEGIN
  PERFORM assert.same(approximated_breast_size(NULL), NULL);
END
$$
LANGUAGE plpgsql;