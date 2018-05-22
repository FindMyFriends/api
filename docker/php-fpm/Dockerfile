FROM findmyfriends/docker:php-fpm

COPY sendmail /usr/sbin/fake-sendmail
COPY php.ini /usr/local/etc/php/php.ini

RUN apk add --update bash

COPY .bash_aliases /root/.bash_aliases
RUN echo '. /root/.bash_aliases' >> ~/.bashrc

COPY .bash_history /root/.bash_history

COPY sendmail /usr/sbin/fake-sendmail

RUN rm -rf /var/cache/apk/*