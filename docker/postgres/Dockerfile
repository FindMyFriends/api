FROM postgres:latest

RUN echo 'alias db_export="PGPASSWORD=postgres;pg_dump -s -h localhost -U postgres find_my_friends > /var/www/FindMyFriends/fixtures/schema.sql"' >> ~/.bashrc
RUN echo 'alias db_import="PGPASSWORD=postgres;psql -h localhost -U postgres find_my_friends < /var/www/FindMyFriends/fixtures/schema.sql"' >> ~/.bashrc
RUN echo 'alias db_test_import="PGPASSWORD=postgres;psql -h localhost -U postgres find_my_friends_test < /var/www/FindMyFriends/fixtures/schema.sql"' >> ~/.bashrc