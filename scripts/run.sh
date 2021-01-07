#!/usr/bin/env bash

set -e
set -o pipefail

docker run \
  --rm \
  -ti \
  -e SALSA17_MYSQL_HOST \
  -e SALSA17_MYSQL_USER \
  -e SALSA17_MYSQL_PASS \
  -v /data/17salsa.com/home/data:/var/www/home/data \
  -v /data/17salsa.com/home/attachment:/var/www/home/attachment \
  -v /data/17salsa.com/center/data:/var/www/center/data \
  -v /data/17salsa.com/wiki/data:/var/www/wiki/data \
  -v /data/17salsa.com/wiki/uploads:/var/www/wiki/uploads \
  -p 8080:80 \
  -e VIRTUAL_HOST=17salsa.com,*.17salsa.com,17salsa.net,*.17salsa.net,17salsa.org,*.17salsa.org \
  -e LETSENCRYPT_HOST=17salsa.com,www.17salsa.com,cdn.17salsa.com,abu.17salsa.com,17salsa.net,www.17salsa.net,17salsa.org,www.17salsa.org \
  -e HTTPS_METHOD=noredirect \
  --entrypoint bash \
  17salsa.com

  #ghcr.io/zixia/17salsa.com
