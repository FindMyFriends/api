INSERT INTO seekers (email, password) VALUES ('me@fmf.com', '251d541f1195f4b4f76ff37d71dd97d797694054c9b9f90602b717fd9e4d47a5f18eeaa099380790abc03093d0f22bb24e1b7a90145c3f4dde96206ffc8559b4ac4b88324f8bf35cb2ab37a620a0ade6');
SELECT created_base_evolution(1, 'man', 1::smallint, 1996, 'Dominik', 'Klapuch');
UPDATE verification_codes SET used_at = NOW() WHERE seeker_id = 1;
