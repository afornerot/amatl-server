FROM reg.cadoles.com/envole/nineapache:8.2

USER root
COPY ./misc/docker/apache.conf /etc/apache2/conf.d/nine/site.conf

WORKDIR /app
USER apache
COPY --chown=apache:apache . .
RUN composer install --no-interaction
RUN mkdir -p /app/uploads
RUN mkdir -p /app/public/uploads
RUN mkdir -p /app/public/uploads/avatar
RUN mkdir -p /app/public/uploads/logo
RUN cp -rf /app/public/medias/logo /app/public/uploads/logo
RUN cp -rf /app/public/medias/avatar /app/public/uploads/avatar

CMD  ["/app/misc/script/reconfigure.sh","/etc/apache2/apache2.sh"]
