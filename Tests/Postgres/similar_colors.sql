CREATE FUNCTION unit_tests.null_on_no_similar() RETURNS TEST_RESULT AS $$
DECLARE
  v_color_id smallint;
BEGIN
  INSERT INTO colors (name, hex) VALUES ('foo', '#000000')
  RETURNING id INTO v_color_id;
  RETURN message FROM assert.is_equal(
    similar_colors(v_color_id),
    NULL
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.one_side_similar() RETURNS TEST_RESULT AS $$
DECLARE
  v_color_id integer;
  v_similar_color_id integer;
BEGIN
  INSERT INTO colors (name, hex) VALUES ('foo', '#000000')
  RETURNING id INTO v_color_id;
  INSERT INTO colors (name, hex) VALUES ('bar', '#000001')
  RETURNING id INTO v_similar_color_id;
  INSERT INTO similar_colors (color_id, similar_color_id) VALUES (v_color_id, v_similar_color_id);
  RETURN message FROM assert.is_equal(
    similar_colors(v_color_id),
    array[v_similar_color_id]
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.two_side_similar() RETURNS TEST_RESULT AS $$
DECLARE
  v_color_id integer;
  v_similar_color_id integer;
  v_similar_color_id2 integer;
BEGIN
  INSERT INTO colors (name, hex) VALUES ('foo', '#000000')
  RETURNING id INTO v_color_id;
  INSERT INTO colors (name, hex) VALUES ('bar', '#000001')
  RETURNING id INTO v_similar_color_id;
  INSERT INTO colors (name, hex) VALUES ('baz', '#000002')
  RETURNING id INTO v_similar_color_id2;
  INSERT INTO similar_colors (color_id, similar_color_id) VALUES
    (v_color_id, v_similar_color_id),
    (v_similar_color_id2, v_color_id);
  RETURN message FROM assert.is_equal(
    similar_colors(v_color_id),
    array[v_similar_color_id, v_similar_color_id2]
  );
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION unit_tests.null_on_no_id() RETURNS TEST_RESULT AS $$
BEGIN
  RETURN message FROM assert.is_equal(similar_colors(NULL), NULL);
END
$$
LANGUAGE plpgsql;