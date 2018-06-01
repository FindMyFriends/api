FROM nginx:alpine

COPY nginx.conf /etc/nginx/nginx.conf

COPY fmf.conf /etc/nginx/sites-available/fmf.conf
COPY fmf.conf /etc/nginx/sites-enabled/fmf.conf

COPY ssl/fmf.localhost.crt /etc/ssl/certs/fmf.localhost.crt
COPY ssl/fmf.localhost.key /etc/ssl/private/fmf.localhost.key