#!/usr/bin/env bash

set -e
set -o pipefail

cat /www/VERSION

apachectl stop > /dev/null 2>&1
apachectl -D FOREGROUND