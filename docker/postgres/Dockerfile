FROM postgres:alpine

ENV PGPASSWORD postgres;

COPY .bash_aliases /root/.bash_aliases
COPY .psqlrc /root/.psqlrc

RUN echo '. /root/.bash_aliases' >> ~/.bashrc