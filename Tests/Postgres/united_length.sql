CREATE FUNCTION tests.keeping_cm() RETURNS void
AS $$
BEGIN
  PERFORM assert.same(united_length(ROW(160, 'cm')), ROW(160, 'cm'):: LENGTH);
  PERFORM assert.same(united_length(ROW(16, 'cm')), ROW(16, 'cm'):: LENGTH);
  PERFORM assert.same(united_length(ROW(1, 'cm')), ROW(1, 'cm'):: LENGTH);
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.keeping_mm() RETURNS void
AS $$
BEGIN
  PERFORM assert.same(united_length(ROW(1, 'mm')), ROW(1, 'mm'):: LENGTH);
  PERFORM assert.same(united_length(ROW(9, 'mm')), ROW(9, 'mm'):: LENGTH);
  PERFORM assert.same(united_length(ROW(12, 'mm')), ROW(12, 'mm'):: LENGTH);
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.mm_to_cm_if_long() RETURNS void
AS $$
BEGIN
  PERFORM assert.same(united_length(ROW(10, 'mm')), ROW(1, 'cm'):: LENGTH);
  PERFORM assert.same(united_length(ROW(100, 'mm')), ROW(10, 'cm'):: LENGTH);
END
$$
LANGUAGE plpgsql;