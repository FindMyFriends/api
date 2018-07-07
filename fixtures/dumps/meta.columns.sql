INSERT INTO meta.prioritized_columns (object, "column", priority) VALUES
  ('public.general'::regclass::oid, 'sex', 1),
  ('public.general'::regclass::oid, 'firstname', 1),
  ('public.general'::regclass::oid, 'lastname', 1);

INSERT INTO meta.prioritized_application_columns (object, "column", prioritized_column_id) VALUES
  ('public.collective_evolutions'::regclass::oid, 'general_sex', 1),
  ('public.collective_evolutions'::regclass::oid, 'general_firstname', 2),
  ('public.collective_evolutions'::regclass::oid, 'general_lastname', 3);