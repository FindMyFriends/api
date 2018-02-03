FROM findmyfriends/docker:php-fpm

COPY sendmail /usr/sbin/fake-sendmail
COPY php.ini /usr/local/etc/php/php.ini

COPY .bash_aliases /root/.bash_aliases
RUN echo '. /root/.bash_aliases' >> ~/.bashrc