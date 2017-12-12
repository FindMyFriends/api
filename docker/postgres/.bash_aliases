function export() {
    pg_dump -s -U postgres find_my_friends > /var/www/FindMyFriends/fixtures/schema.sql;
}

function import() {
    psql -U postgres -c "DROP DATABASE find_my_friends";
    psql -U postgres -c "CREATE DATABASE find_my_friends";
    psql -U postgres find_my_friends < /var/www/FindMyFriends/fixtures/schema.sql;
    for f in /var/www/FindMyFriends/fixtures/dumps/*.sql; do psql -U postgres -d find_my_friends -f $f; done
}

function test_import() {
    psql -U postgres -c "DROP DATABASE find_my_friends_test";
    psql -U postgres -c "CREATE DATABASE find_my_friends_test";
    psql -U postgres find_my_friends_test < /var/www/FindMyFriends/fixtures/schema.sql;
    psql -U postgres find_my_friends_test < /var/www/FindMyFriends/Tests/fixtures/plpgunit.sql;
    psql -U postgres find_my_friends_test < /var/www/FindMyFriends/Tests/fixtures/test_utils.sql;
    psql -U postgres find_my_friends_test < /var/www/FindMyFriends/Tests/fixtures/samples.sql;
    for f in /var/www/FindMyFriends/fixtures/dumps/*.sql; do psql -U postgres -d find_my_friends_test -f $f; done
}

alias connect="psql -U postgres -h localhost -d find_my_friends"