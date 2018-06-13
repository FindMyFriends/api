CREATE FUNCTION tests.null_on_no_similar() RETURNS void
AS $$
DECLARE
  v_color_id smallint;
BEGIN
  INSERT INTO colors (name, hex) VALUES ('foo', '#000000')
  RETURNING id
  INTO v_color_id;
  PERFORM assert.same(similar_colors(v_color_id), NULL);
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.one_side_similar() RETURNS void
AS $$
DECLARE
  v_color_id integer;
  v_similar_color_id smallint;
BEGIN
  INSERT INTO colors (name, hex) VALUES ('foo', '#000000')
  RETURNING id
  INTO v_color_id;
  INSERT INTO colors (name, hex) VALUES ('bar', '#000001')
  RETURNING id
  INTO v_similar_color_id;
  INSERT INTO similar_colors (color_id, similar_color_id) VALUES (v_color_id, v_similar_color_id);
  PERFORM assert.same(similar_colors(v_color_id), ARRAY[v_similar_color_id]);
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.two_side_similar() RETURNS void
AS $$
DECLARE
  v_color_id integer;
  v_similar_color_id smallint;
  v_similar_color_id2 smallint;
BEGIN
  INSERT INTO colors (name, hex) VALUES ('foo', '#000000')
  RETURNING id
  INTO v_color_id;
  INSERT INTO colors (name, hex) VALUES ('bar', '#000001')
  RETURNING id
  INTO v_similar_color_id;
  INSERT INTO colors (name, hex) VALUES ('baz', '#000002')
  RETURNING id
  INTO v_similar_color_id2;
  INSERT INTO similar_colors (color_id, similar_color_id) VALUES
    (v_color_id, v_similar_color_id),
    (v_similar_color_id2, v_color_id);
  PERFORM assert.same(similar_colors(v_color_id), ARRAY[v_similar_color_id, v_similar_color_id2]);
END
$$
LANGUAGE plpgsql;

CREATE FUNCTION tests.null_on_no_id() RETURNS void
AS $$
BEGIN
  PERFORM assert.same(similar_colors( NULL ), NULL );
END
$$
LANGUAGE plpgsql;