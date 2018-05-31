FROM nginx:alpine

COPY site.conf /etc/nginx/conf.d/default.conf
COPY ssl/fmf.localhost.crt /etc/ssl/certs/fmf.localhost.crt
COPY ssl/fmf.localhost.key /etc/ssl/private/fmf.localhost.key