FROM reg.cadoles.com/envole/nineapache:8.2

USER root

RUN apk add --no-cache \
    chromium \
    nss \
    freetype \
    harfbuzz \
    ca-certificates \
    ttf-freefont

# 2. Création du wrapper à l'emplacement attendu (/usr/bin/chromium)
# On pointe vers le vrai binaire Alpine (/usr/bin/chromium-browser)
RUN printf '#!/bin/sh\nexec /usr/bin/chromium-browser --headless --no-sandbox --disable-setuid-sandbox --disable-dev-shm-usage "$@"' > /usr/bin/chromium && \
    chmod +x /usr/bin/chromium

# 3. Optionnel : Lien symbolique pour google-chrome
RUN ln -s /usr/bin/chromium /usr/bin/google-chrome

COPY ./misc/docker/apache.conf /etc/apache2/conf.d/nine/site.conf
COPY . /app/
RUN chown apache:apache /app -R

WORKDIR /app
USER apache

RUN composer install --no-interaction
RUN mkdir -p /app/uploads
RUN mkdir -p /app/public/uploads
RUN mkdir -p /app/public/uploads/avatar
RUN mkdir -p /app/public/uploads/logo
RUN mkdir -p /app/vendor
RUN cp -rf /app/public/medias/logo /app/public/uploads/logo
RUN cp -rf /app/public/medias/avatar /app/public/uploads/avatar

CMD  ["/app/misc/script/reconfigure.sh","/etc/apache2/apache2.sh"]
