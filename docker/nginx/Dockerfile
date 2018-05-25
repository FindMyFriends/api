FROM nginx:alpine

COPY site.conf /etc/nginx/conf.d/default.conf
COPY ssl/localhost.crt /etc/ssl/certs/localhost.crt
COPY ssl/localhost.key /etc/ssl/private/localhost.key