## Source installation

### Requirements

- Web server
- PHP 7.2.5 or higher (7.4 recommended): https://symfony.com/doc/current/setup/web_server_configuration.html
- Composer: https://getcomposer.org/download/
- npm: https://docs.npmjs.com/downloading-and-installing-node-js-and-npm

### Web server

Configure a vhost with the document root set to the ```public``` folder (ie /var/www/elasticsearch-admin/public)

On Apache, add in your vhost the rules below

```
DirectoryIndex index.php

<IfModule mod_negotiation.c>
    Options -MultiViews
</IfModule>

<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteCond %{REQUEST_URI}::$0 ^(/.+)/(.*)::\2$
    RewriteRule .* - [E=BASE:%1]

    RewriteCond %{HTTP:Authorization} .+
    RewriteRule ^ - [E=HTTP_AUTHORIZATION:%0]

    RewriteCond %{ENV:REDIRECT_STATUS} =""
    RewriteRule ^index\.php(?:/(.*)|$) %{ENV:BASE}/$1 [R=301,L]

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ %{ENV:BASE}/index.php [L]
</IfModule>

<IfModule !mod_rewrite.c>
    <IfModule mod_alias.c>
        RedirectMatch 307 ^/$ /index.php/
    </IfModule>
</IfModule>
```

If you can't edit a vhost, add the Apache pack to get the ```.htaccess``` file in the ```public``` folder

```
composer require symfony/apache-pack
```

On nginx, see the server definition used for the Docker image in [nginx.conf](https://github.com/stephanediondev/elasticsearch-admin/blob/master/docker/nginx.conf)

### Steps

Download or clone the repository from GitHub https://github.com/stephanediondev/elasticsearch-admin

If you don't have PHP 7.4, remove ```composer.lock``` or you will have the error below

```
Fatal Error: composer.lock was created for PHP version 7.4 or higher but the current PHP version is ...
```

Launch the following commands to install

```
cd /var/www/elasticsearch-admin/

composer install

npm install
npm run build

cp .env.dist .env
```

In the ```.env``` file edit ```ELASTICSEARCH_URL``` and ```SECRET_REGISTER``` (random string to secure registration)

If Elasticsearch security features are enabled, edit ```ELASTICSEARCH_USERNAME``` and ```ELASTICSEARCH_PASSWORD```
