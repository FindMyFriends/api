FROM postgres:alpine

ENV PGPASSWORD postgres;

COPY .bash_aliases /root/.bash_aliases
COPY .psqlrc /root/.psqlrc

RUN echo '. /root/.bash_aliases' >> ~/.bashrc

COPY disconnect /usr/bin/disconnect
COPY import /usr/bin/import
COPY test_import /usr/bin/test_import

RUN chmod +x /usr/bin/disconnect
RUN chmod +x /usr/bin/import
RUN chmod +x /usr/bin/test_import