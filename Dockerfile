FROM php:7.4-apache

# Instala extensões do PHP necessárias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# Define o diretório de trabalho
WORKDIR /var/www/html/

# Copia todos os arquivos do projeto
COPY . .

# Verifica se os arquivos foram copiados (para debug)
RUN ls -la

# Configura as permissões
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Expõe a porta 80
EXPOSE 80

# Inicia o Apache
CMD ["apache2-foreground"]
