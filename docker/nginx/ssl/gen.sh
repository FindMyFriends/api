#!/bin/bash


### Thanks to https://www.zdrojak.cz/clanky/vytvoreni-vlastni-certifikacni-autority-tvorba-vlastnich-self-signed-certifikatu/

###
# How to generate certificate authority:
#  -> without password:
#    sudo openssl genrsa -out /etc/ssl/private/rootCA-Development.key 2048
#  -> with password:
#    sudo openssl genrsa -des3 -out /etc/ssl/private/rootCA-Development.key 2048
#
#    sudo chmod 600 /etc/ssl/private/rootCA-Development.key 2048
#    sudo openssl req -x509 -new -nodes -key /etc/ssl/private/rootCA-Development.key -sha256 -days 3650 -subj "/C=CZ/ST=Prague/L=Prague/O=Development" -out /etc/ssl/certs/rootCA-Development.pem
###

if [ "$EUID" -ne 0 ]; then
  echo "Please run as root"
  exit 1
fi

if [ $# -eq 0 ]; then
  echo "Please specify domain(s)."
  echo "Usage: $0 <main domain> [another domain] [another domain] ..."
  exit 2
fi

ROOTCA="Development"

# -> for certificate authority with password uncomment the line below:
#read -p "Enter rootCA-${ROOTCA}.key password: " PASSWORD

DOMAIN=$1
CONF="/tmp/$DOMAIN.openssl.cnf"
PRIVATE_KEY="/etc/ssl/private/$DOMAIN.key"
CERTIFICATE_REQUEST="/etc/ssl/certs/$DOMAIN.crt.req"
CERTIFICATE="/etc/ssl/certs/$DOMAIN.crt"
ANOTHER_DOMAINS=""
ANOTHER_DOMAINS_INFO=""

ANOTHER_DOMAIN_INDEX=3
for ANOTHER_DOMAIN in ${@:2}
do
  ANOTHER_DOMAINS="${ANOTHER_DOMAINS}DNS.${ANOTHER_DOMAIN_INDEX} = ${ANOTHER_DOMAIN}"$'\n'
  ANOTHER_DOMAIN_INDEX=$((ANOTHER_DOMAIN_INDEX + 1))
  ANOTHER_DOMAINS="${ANOTHER_DOMAINS}DNS.${ANOTHER_DOMAIN_INDEX} = *.${ANOTHER_DOMAIN}"$'\n'
  ANOTHER_DOMAIN_INDEX=$((ANOTHER_DOMAIN_INDEX + 1))

  ANOTHER_DOMAINS_INFO="-> ${ANOTHER_DOMAIN}"$'\n'
done

cat > $CONF <<-EOF
[req]
default_bits = 2048
prompt = no
x509_extensions = v3_req
distinguished_name = dn

[dn]
C = CZ
ST = Prague
L = Prague
O = $ROOTCA
CN = *.$DOMAIN

[v3_req]
subjectAltName = @alt_names

[alt_names]
DNS.1 = $DOMAIN
DNS.2 = *.$DOMAIN
$ANOTHER_DOMAINS
EOF

openssl genrsa -out $PRIVATE_KEY 2048
chmod 600 $PRIVATE_KEY
openssl req -new -config $CONF -key $PRIVATE_KEY -out $CERTIFICATE_REQUEST

openssl x509 -req -in $CERTIFICATE_REQUEST -CA /etc/ssl/certs/rootCA-${ROOTCA}.pem -CAkey /etc/ssl/private/rootCA-${ROOTCA}.key \
  -CAcreateserial -out $CERTIFICATE -days 3650 -sha256 -extfile $CONF -extensions 'v3_req'
# -> for certificate authority with password uncomment the line below and remove this line and the one up
#  -CAcreateserial -out $CERTIFICATE -days 3650 -sha256 -extfile $CONF -extensions 'v3_req' -passin pass:$PASSWORD

rm $CERTIFICATE_REQUEST
rm $CONF

chmod 600 $PRIVATE_KEY

echo "Self signed certificate ${CERTIFICATE} and private key ${PRIVATE_KEY} for main domain ${DOMAIN} were generated with 10 years expiration time"

if [ -z "$ANOTHER_DOMAINS_INFO" ]; then
  echo "Another domains:"
  echo ${ANOTHER_DOMAINS_INFO}
fi