#!/usr/bin/env bash

set -e
set -o pipefail

cat /www/VERSION

rm -f /var/run/apache2/apache2.pid
exec apachectl -DFOREGROUND "$@"
