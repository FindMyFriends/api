FROM postgres:alpine

RUN echo 'alias db_export="PGPASSWORD=postgres;pg_dump -s -U postgres find_my_friends > /var/www/FindMyFriends/fixtures/schema.sql"' >> ~/.bashrc
RUN echo 'alias db_import="PGPASSWORD=postgres;psql -U postgres -c \"DROP DATABASE find_my_friends\";psql -U postgres -c \"CREATE DATABASE find_my_friends\";psql -U postgres find_my_friends < /var/www/FindMyFriends/fixtures/schema.sql"' >> ~/.bashrc
RUN echo 'alias db_test_import="PGPASSWORD=postgres;psql -U postgres -c \"DROP DATABASE find_my_friends_test\";psql -U postgres -c \"CREATE DATABASE find_my_friends_test\";psql -U postgres find_my_friends_test < /var/www/FindMyFriends/fixtures/schema.sql;psql -U postgres find_my_friends_test < /var/www/FindMyFriends/Tests/fixtures/plpgunit.sql;psql -U postgres find_my_friends_test < /var/www/FindMyFriends/Tests/fixtures/test_utils.sql;psql -U postgres find_my_friends_test < /var/www/FindMyFriends/Tests/fixtures/samples.sql;"' >> ~/.bashrc

COPY .psqlrc /root/.psqlrc