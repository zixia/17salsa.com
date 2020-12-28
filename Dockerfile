FROM bylexus/apache-php56
MAINTAINER Huan <zixia@zixia.net>

RUN apt update \
  && apt install -y \
    vim \
  && apt-get autoremove -y \
  && apt-get clean \
  && rm -fr /tmp/*

COPY conf/000-default.conf /etc/apache2/sites-available/

RUN a2enmod rewrite
COPY www /www
COPY VERSION /www

CMD ["apachectl", "-D", "FOREGROUND"]

EXPOSE 80/tcp

# VOLUME [\
#   "/www/admin/UploadFiles/" \
# ]

LABEL maintainer="Huan LI <zixia@zixia.net>"
LABEL org.opencontainers.image.source="https://github.com/zixia/17salsa.com"
