INSERT INTO seekers (email, password) VALUES ('me@fmf.com', '251d541f1195f4b4f76ff37d71dd97d797694054c9b9f90602b717fd9e4d47a5f18eeaa099380790abc03093d0f22bb24e1b7a90145c3f4dde96206ffc8559b4ac4b88324f8bf35cb2ab37a620a0ade6');
INSERT INTO seekers (email, password) VALUES ('you@fmf.com', '251d541f1195f4b4f76ff37d71dd97d797694054c9b9f90602b717fd9e4d47a5f18eeaa099380790abc03093d0f22bb24e1b7a90145c3f4dde96206ffc8559b4ac4b88324f8bf35cb2ab37a620a0ade6');
INSERT INTO seeker_contacts (seeker_id, facebook) VALUES (1, 'klapuchdominik');
INSERT INTO seeker_contacts (seeker_id, facebook) VALUES (2, 'someone');
SELECT created_base_evolution(1, 'man', 1::smallint, 1996::smallint, 'Dominik', 'Klapuch');
SELECT created_base_evolution(2, 'woman', 1::smallint, 1995::smallint, 'Jane', 'Unknown');
UPDATE verification_codes SET used_at = NOW() WHERE seeker_id = 1;
UPDATE verification_codes SET used_at = NOW() WHERE seeker_id = 2;


WITH general AS (
INSERT INTO general (sex, ethnic_group_id, birth_year_range, firstname) VALUES (
  'woman',
  1,
  int4range(1993, 1998),
  'Jane'
)
RETURNING id
),
body AS (INSERT INTO bodies DEFAULT VALUES RETURNING id),
face AS (INSERT INTO faces DEFAULT VALUES RETURNING id),
nail AS (INSERT INTO nails DEFAULT VALUES RETURNING id),
hand AS (
INSERT INTO hands (nail_id, care, visible_veins) VALUES (
  (SELECT id FROM nail),
  DEFAULT,
  DEFAULT
)
RETURNING id
),
hair AS (INSERT INTO hair DEFAULT VALUES RETURNING id),
beard AS (INSERT INTO beards DEFAULT VALUES RETURNING id),
eyebrow AS (INSERT INTO eyebrows DEFAULT VALUES RETURNING id),
tooth AS (INSERT INTO teeth DEFAULT VALUES RETURNING id),
left_eye AS (INSERT INTO eyes DEFAULT VALUES RETURNING id),
right_eye AS (INSERT INTO eyes DEFAULT VALUES RETURNING id),
description AS (
INSERT INTO descriptions (general_id, body_id, face_id, hand_id, hair_id, beard_id, eyebrow_id, tooth_id, left_eye_id, right_eye_id) VALUES (
  (SELECT id FROM general),
  (SELECT id FROM body),
  (SELECT id FROM face),
  (SELECT id FROM hand),
  (SELECT id FROM hair),
  (SELECT id FROM beard),
  (SELECT id FROM eyebrow),
  (SELECT id FROM tooth),
  (SELECT id FROM left_eye),
  (SELECT id FROM right_eye)
)
RETURNING id
)
INSERT INTO demands (seeker_id, description_id, created_at) VALUES (
  1,
  (SELECT id FROM description),
  now()
);

INSERT INTO soulmate_requests (demand_id, searched_at, self_id, status) VALUES (1, now(), NULL, 'processing');
INSERT INTO soulmate_requests (demand_id, searched_at, self_id, status) VALUES (1, now(), 1, 'succeed');

INSERT INTO soulmates (demand_id, evolution_id, score) VALUES (1, 2, 10);