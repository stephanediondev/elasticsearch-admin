[![GitHub Action](https://github.com/stephanediondev/elasticsearch-admin/workflows/build/badge.svg)](https://github.com/stephanediondev/elasticsearch-admin/actions) [![Travis CI](https://travis-ci.org/stephanediondev/elasticsearch-admin.svg?branch=master)](https://travis-ci.org/stephanediondev/elasticsearch-admin) [![SymfonyInsight](https://insight.symfony.com/projects/9eefdae6-9dfc-452e-856e-716f94e08ffa/mini.svg)](https://insight.symfony.com/projects/9eefdae6-9dfc-452e-856e-716f94e08ffa)

# Table of contents

- [Disclaimer](#disclaimer)
- [Product pages](#product-pages)
- [Features](#features)
- [Screenshots](#screenshots)
- [Installation](#installation)
    - [Running with Docker](#running-with-docker)
    - [Source installation](#source-installation)
- [Other tools](#other-tools)
- [License](#license)
- [Privacy](#privacy)
- [Development](#development)
    - [Unit tests](#unit-tests)

# Disclaimer

[(Back to table of contents)](#table-of-contents)

The application named elasticsearch-admin is **NOT** affiliated in any way with Elasticsearch BV.

Elasticsearch is a trademark of Elasticsearch BV, registered in the U.S. and in other countries.

# Product pages

[(Back to table of contents)](#table-of-contents)

- Product Hunt [Visit](https://www.producthunt.com/posts/elasticsearch-admin)
- Slant [Visit](https://www.slant.co/topics/11537/viewpoints/12/~elasticsearch-gui-clients~elasticsearch-admin)

# Features

[(Back to table of contents)](#table-of-contents)

- Supported Elasticsearch versions: 2.x, 5.x, 6.x, 7.x, 8.x (snapshot)
- Connection to Elasticsearch: server-side (no CORS issue), private or public, local or remote, http or https, credentials or not
- App users: register, login, logout, list, create, read, update, delete
- App roles: list, create, read, update (permissions), delete
- Cluster: basic metrics, audit, allocation explain [5.0], settings [5.0] (list, update)
- Nodes: list, read, usage [6.0], plugins, reload secure settings [6.4]
- Indices: list, stats, reindex, create, read, update (mappings), lifecycle [6.6] (explain, remove policy), delete, close / open, freeze / unfreeze [6.6], force merge [2.1], clear cache, flush, refresh, empty [5.0], search by query, export (CSV, TSV, ODS, XLSX, GEOJSON), import from file (ODS, XLSX), import from database (MySQL, PostgreSQL), aliases (list, create, delete)
- Legacy index templates: list, create, read, update, delete, copy
- Composable index templates [7.8]: list, create, read, update, delete, simulate, copy
- Component templates [7.8]: list, create, read, update, delete, copy
- Index lifecycle management policies [6.6]: list, status, start, stop, create, read, update, delete, copy
- Shards: list, stats, cluster reroute (move, allocate replica, cancel allocation)
- Repositories: list, create (fs, s3, gcs, azure), read, update, delete, cleanup, verify
- Snapshot lifecycle management policies [7.4]: list, status, start, stop, create, read, update, delete, execute, history, stats, copy
- Snapshots: list, stats, create, read, delete, failures, restore
- Elasticsearch users (native realm): list, create, read, update, delete, enable, disable
- Elasticsearch roles: list, create, read, update, delete, copy
- Tasks [2.3]: list
- Remote clusters [5.4]: list
- Enrich policies [7.5]: list, stats, create, read, delete, execute, copy
- Pipelines [5.0]: list, create, read, update, delete, copy
- Cat APIs: list, export (CSV, TSV, ODS, XLSX)
- Console
- SQL access [6.3]: query, translate to DSL
- Deprecations info [5.6]
- License [5.0]: read, status / start trial / revert to basic [6.6], features
- Index graveyard [5.0]: list
- Dangling indices [7.9]: list, import, delete

# Screenshots

[(Back to table of contents)](#table-of-contents)

[See all screenshots](https://github.com/stephanediondev/elasticsearch-admin/tree/master/screenshots/7.9.0)

[![Cluster](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-cluster.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-cluster.png)

[![Cluster audit](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-cluster-audit.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-cluster-audit.png)

[![Cluster allocation explain](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-cluster-allocation-explain.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-cluster-allocation-explain.png)

[![Nodes](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-nodes.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-nodes.png)

[![Indices](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-indices.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-indices.png)

[![Index import from database](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-index-database-import.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-index-database-import.png)

[![Shards](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-shards.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-shards.png)

[![Snaphosts](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-snapshots.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-snapshots.png)

# Installation

[(Back to table of contents)](#table-of-contents)

## Running with Docker

[(Back to installation)](#installation)

### Requirements

[(Back to running with Docker)](#running-with-docker)

- Docker: [Visit](https://www.docker.com/get-started)

### Steps

[(Back to running with Docker)](#running-with-docker)

The Docker image is hosted on Docker Hub [Visit](https://hub.docker.com/r/stephanediondev/elasticsearch-admin)

```
docker pull stephanediondev/elasticsearch-admin

docker run -e "ELASTICSEARCH_URL=http://x.x.x.x:9200" -e "SECRET_REGISTER=xxxxx" -p 80:8080 -d --name elasticsearch-admin stephanediondev/elasticsearch-admin
```

Edit ```ELASTICSEARCH_URL``` and ```SECRET_REGISTER``` (random string to secure registration)

If Elasticsearch security features are enabled, add ```-e "ELASTICSEARCH_USERNAME=xxxxx" -e "ELASTICSEARCH_PASSWORD=xxxxx"```

## Source installation

[(Back to installation)](#installation)

### Requirements

[(Back to source installation)](#source-installation)

- Web server
- PHP 7.2.5 or higher (7.4 recommended): [Visit](https://symfony.com/doc/current/setup/web_server_configuration.html)
- Composer: [Visit](https://getcomposer.org/download/)
- npm: [Visit](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm)

### Web server

[(Back to source installation)](#source-installation)

Configure a vhost with the document root set to the ```public``` folder (ie /var/www/elasticsearch-admin/public)

On **Apache**, add in your vhost the rules below

```
<Directory /var/www/elasticsearch-admin/public>
    AllowOverride None

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
</Directory>
```

If you can't edit a vhost, add the Apache pack to get the ```.htaccess``` file in the ```public``` folder

```
composer require symfony/apache-pack
```

On **nginx**, add the server definition below

```
server {
    listen [::]:8080 default_server;
    listen 8080 default_server;
    server_name _;

    sendfile off;

    root /var/www/html/public;
    index index.php index.html;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;

        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }
}
```

### Steps

[(Back to source installation)](#source-installation)

Download or clone the repository from GitHub [Visit](https://github.com/stephanediondev/elasticsearch-admin)

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

# Other tools

[(Back to table of contents)](#table-of-contents)

| Name | Website | GitHub | Main language |
| --- | --- | --- | --- |
| cerebro | | [lmenezes/cerebro](https://github.com/lmenezes/cerebro) | Scala |
| Dejavu | [Visit](https://opensource.appbase.io/dejavu/) | [appbaseio/dejavu](https://github.com/appbaseio/dejavu) | JavaScript |
| ElasticHQ | [Visit](http://www.elastichq.org/) | [ElasticHQ/elasticsearch-HQ](https://github.com/ElasticHQ/elasticsearch-HQ) | Python |
| Elasticsearch Comrade | | [moshe/elasticsearch-comrade](https://github.com/moshe/elasticsearch-comrade) | Python |
| elasticsearch-head | [Visit](http://mobz.github.io/elasticsearch-head/) | [mobz/elasticsearch-head](https://github.com/mobz/elasticsearch-head) | JavaScript |
| Elasticvue | [Visit](https://elasticvue.com/) | [cars10/elasticvue](https://github.com/cars10/elasticvue) | JavaScript |
| ELKman | [Visit](https://www.elkman.io/) | | PHP |
| Kaizen | [Visit](https://www.elastic-kaizen.com/) | | JavaFX |

# License
[(Back to table of contents)](#table-of-contents)

[MIT License](https://github.com/stephanediondev/elasticsearch-admin/blob/master/LICENSE)

# Privacy
[(Back to table of contents)](#table-of-contents)

This application does **NOT** collect and send any user data.

# Development
[(Back to table of contents)](#table-of-contents)

## Unit tests

[(Back to table of contents)](#table-of-contents)

```
bin/console app:phpunit && bin/phpunit
```
