FROM php:7.4-apache

# Instala extensões do PHP necessárias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# Copia os arquivos do projeto para o container
COPY . /var/www/html/

# Configura as permissões
RUN chown -R www-data:www-data /var/www/html

# Expõe a porta 80
EXPOSE 80

# Inicia o Apache
CMD ["apache2-foreground"]
