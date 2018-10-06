CREATE FUNCTION tests.ignoring_guest_seeker() RETURNS void
AS $$
BEGIN
  PERFORM globals_set_seeker(0);
  PERFORM assert.null(globals_get_seeker());
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.null_as_null() RETURNS void
AS $$
BEGIN
  PERFORM globals_set_seeker(NULL);
  PERFORM assert.null(globals_get_seeker());
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.real_set() RETURNS void
AS $$
BEGIN
  PERFORM globals_set_seeker(10);
  PERFORM assert.same(10, globals_get_seeker());
END
$$
LANGUAGE plpgsql;