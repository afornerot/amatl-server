FROM reg.cadoles.com/envole/nineapache:8.2

USER root
COPY ./misc/docker/apache.conf /etc/apache2/conf.d/nine/site.conf

RUN echo "* * * * * /app/bin/console app:Cron --env=prod" >> /var/spool/cron/crontabs/root

WORKDIR /app
COPY . .

# Installation des dépendances composer
RUN composer install --no-interaction

RUN mkdir -p /app/public/uploads
RUN mkdir -p /app/public/uploads/avatar
RUN mkdir -p /app/public/uploads/logo

RUN chown apache /app/public/uploads -R
RUN chmod u+w /app/public/uploads -R
RUN cp -rf /app/public/medias/logo /app/public/uploads/logo
RUN cp -rf /app/public/medias/avatar /app/public/uploads/avatar


RUN mkdir -p /app/var
RUN chown apache /app/var -R
RUN chmod u+w /app/var -R

CMD  /app/misc/script/reconfigure.sh && /etc/apache2/apache2.sh
