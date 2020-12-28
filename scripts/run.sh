#!/usr/bin/env bash

set -e
set -o pipefail

docker run \
  --rm \
  -ti \
  -e SALSA17_MYSQL_HOST \
  -e SALSA17_MYSQL_USER \
  -e SALSA17_MYSQL_PASS \
  -e SALSA17_MYSQL_DATABASE \
  -v /data/17salsa.com/home/data:/www/home/data \
  -v /data/17salsa.com/home/attachment:/www/home/attachment \
  -v /data/17salsa.com/center/data:/www/center/data \
  -p 8080:80 \
  --entrypoint bash \
  17salsa.com

  #ghcr.io/zixia/17salsa.com
