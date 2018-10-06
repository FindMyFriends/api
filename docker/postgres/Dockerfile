FROM postgres:11-alpine

RUN apk add --no-cache supervisor
RUN mkdir /etc/supervisor.d
COPY cron.ini /etc/supervisor.d/cron.ini

ENV PGPASSWORD postgres

COPY postgresql.conf /etc/postgresql.conf

COPY .bash_aliases /root/.bash_aliases
COPY .psqlrc /root/.psqlrc

RUN echo '. /root/.bash_aliases' >> ~/.bashrc

COPY disconnect /usr/bin/disconnect
COPY import /usr/bin/import
COPY test_import /usr/bin/test_import
COPY remove_tests /usr/bin/remove_tests

RUN chmod +x /usr/bin/disconnect
RUN chmod +x /usr/bin/import
RUN chmod +x /usr/bin/test_import
RUN chmod +x /usr/bin/remove_tests

# CRON
ADD crontab /crontab
RUN /usr/bin/crontab /crontab

RUN rm -rf /var/cache/apk/*

ENTRYPOINT ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]