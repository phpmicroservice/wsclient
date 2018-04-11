FROM  phpmicroservice/docker_php:cli71_swoole_phalcon

MAINTAINER      Dongasai "1514582970@qq.com"

RUN apt update;apt install -y vim
COPY . /var/www/html/
ENV APP_SECRET_KEY="123456"
ENV REGISTER_SECRET_KEY="123456"
ENV REGISTER_ADDRESS="123456"
ENV REGISTER_PORT="pms_config"
ENV CONFIG_SECRET_KEY="9310FBCjxfycXLVMzbKOAptEpTVuiOch"
ENV CONFIG_DATA_KEY="dhC0FUFYauHYYPcfnZJxXRkmsCqJEYNA"
ENV CONFIG_ADDRESS="pms_config"
ENV CONFIG_PORT=9502
EXPOSE 9502
WORKDIR /var/www/html/
RUN composer install
CMD php start/start.php

