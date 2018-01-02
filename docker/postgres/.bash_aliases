function export() {
    pg_dump -s -U postgres find_my_friends > /var/www/FindMyFriends/fixtures/schema.sql;
}

alias connect="psql -U postgres -h localhost -d find_my_friends"