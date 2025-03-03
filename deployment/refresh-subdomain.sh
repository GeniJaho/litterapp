SubDomain=${1:-docs}

docker run --rm --name temp_certbot \
    -v /v1/letsencrypt/certbot/conf/:/etc/letsencrypt/:rw \
    -v /v1/letsencrypt/certbot/www/:/var/www/certbot/:rw \
    certbot/certbot:latest \
    certonly --webroot --agree-tos --renew-by-default \
    --preferred-challenges http-01 --server https://acme-v02.api.letsencrypt.org/directory \
    --text --email ${CORRECT_MAIL_ADDRES}@gmail.com \
    -w /var/www/certbot -d ${SubDomain}.litterapp.net

cp ./certbot/conf/live/${SubDomain}.litterapp.net/cert.pem /v1/littertagger/cert/server-${SubDomain}.crt
cp ./certbot/conf/live/${SubDomain}.litterapp.net/privkey.pem /v1/littertagger/cert/server-${SubDomain}.key
cat ./certbot/conf/live/${SubDomain}.litterapp.net/fullchain.pem >> /v1/littertagger/cert/server-${SubDomain}.crt