FROM bylexus/apache-php56
MAINTAINER Huan <zixia@zixia.net>

COPY conf/000-default.conf /etc/apache2/sites-available/

RUN a2enmod rewrite
COPY www /var/www
COPY VERSION /var/www

CMD ["apachectl", "-D", "FOREGROUND"]

EXPOSE 80/tcp

VOLUME [\
  "/var/www/admin/UploadFiles/" \
]

LABEL maintainer="Huan LI <zixia@zixia.net>"
LABEL org.opencontainers.image.source="https://github.com/zixia/17salsa.com"