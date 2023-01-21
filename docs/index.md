# Web administration for Elasticsearch (2.x to 8.x)

# Table of contents

- [Disclaimer](#disclaimer)
- [Features](#features)
- [Screenshots](#screenshots)
    - [Selection](#selection)
    - [All screenshots](#all-screenshots)
- [Installation](#installation)
    - [Running with Docker](#running-with-docker)
        - [Requirements](#requirements)
        - [Steps](#steps)
    - [Source installation](#source-installation)
        - [Requirements](#requirements-1)
        - [Web server](#web-server)
            - [Apache](#apache)
            - [nginx](#nginx)
        - [Steps](#steps-1)
    - [Launch](#launch)
- [Other tools](#other-tools)
- [License](#license)
- [Privacy](#privacy)
- [Development](#development)
    - [Unit tests](#unit-tests)

# Disclaimer

[(Back to table of contents)](#table-of-contents)

The application named elasticsearch-admin is **NOT** affiliated in any way with Elasticsearch BV.

Elasticsearch is a trademark of Elasticsearch BV, registered in the U.S. and in other countries.

# Features

[(Back to table of contents)](#table-of-contents)

- Supported Elasticsearch versions: 2.x, 5.x, 6.x, 7.x, 8.x
- Connection to Elasticsearch: server-side (no CORS issue), private or public, local or remote, http or https, authorization with user/password or API key
- App users: register, login, logout, list, create, read, update, delete
- App roles: list, create, read, update (permissions), delete
- Notifications (Push API, Email, Slack Incoming Webhook, Microsoft Teams Incoming Webhook) about cluster health, node up, node down, disk threshold, license expiration, ES version
- Cluster: basic metrics, audit, disk thresholds, allocation explain [5.0], settings [5.0] (list, update)
- Nodes: list, stats, reload secure settings [6.4], read, settings, usage [6.0], plugins
- Indices: list, stats, reindex, create, read, update, lifecycle [6.6] (explain, remove policy), delete, close / open, freeze / unfreeze [6.6], force merge [2.1], clear cache, flush, refresh, empty [5.0], search by query, export (CSV, TSV, ODS, XLSX, GEOJSON), import from file (ODS, XLSX), import from database (MySQL, PostgreSQL), aliases (list, create, delete)
- Legacy index templates: list, create, read, update, delete, copy
- Composable index templates [7.8]: list, create, read, update, delete, simulate, copy
- Component templates [7.8]: list, create, read, update, delete, copy
- Index lifecycle management policies [6.6]: list, status, start, stop, create, read, update, delete, copy
- Shards: list, stats, cluster reroute (move, allocate replica, cancel allocation)
- Repositories: list, create (fs, s3, gcs, azure), read, update, delete, cleanup, verify
- Snapshot lifecycle management policies [7.4]: list, status, start, stop, create, read, update, delete, execute, history, stats, copy
- Snapshots: list, stats, create, read, delete, failures, restore, clone [7.10]
- Elasticsearch users (native realm): list, create, read, update, delete, enable, disable
- Elasticsearch roles: list, create, read, update, delete, copy
- Tasks [2.3]: list
- Remote clusters [5.4]: list
- Enrich policies [7.5]: list, stats, create (match, geo_match, range [7.16]), read, delete, execute, copy
- Pipelines [5.0]: list, create, read, update, delete, copy
- Compact and aligned text (CAT) APIs: list, export (CSV, TSV, ODS, XLSX)
- Console
- SQL access [6.3]: query, translate to DSL
- Deprecations info [5.6]
- License [5.0]: read, status / start trial / revert to basic [6.6], features
- Index graveyard [5.0]: list
- Dangling indices [7.9]: list, import, delete
- Data streams [7.9]: list, create, read, delete, stats

# Screenshots

[(Back to table of contents)](#table-of-contents)

## Selection

[(Back to screenshots)](#screenshots)

[![elasticsearch-admin - Cluster summary](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/8/resized/resized-cluster.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/8/original/original-cluster.png)

[![elasticsearch-admin - Cluster audit](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/8/resized/resized-cluster-audit.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/8/original/original-cluster-audit.png)

[![elasticsearch-admin - Cluster allocation explain](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/8/resized/resized-cluster-allocation-explain.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/8/original/original-cluster-allocation-explain.png)

[![elasticsearch-admin - Nodes list](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/8/resized/resized-nodes.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/8/original/original-nodes.png)

[![elasticsearch-admin - Indices list](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/8/resized/resized-indices.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/8/original/original-indices.png)

[![elasticsearch-admin - Shards list](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/8/resized/resized-shards.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/8/original/original-shards.png)

[![elasticsearch-admin - Composable index templates](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/8/resized/resized-index-templates.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/8/original/original-index-templates.png)

[![elasticsearch-admin - Snapshots list](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/8/resized/resized-snapshots.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/8/original/original-snapshots.png)

## All screenshots

[(Back to screenshots)](#screenshots)

- [2.x](https://github.com/stephanediondev/elasticsearch-admin/tree/main/screenshots/2)
- [5.x](https://github.com/stephanediondev/elasticsearch-admin/tree/main/screenshots/5)
- [6.x](https://github.com/stephanediondev/elasticsearch-admin/tree/main/screenshots/6)
- [7.x](https://github.com/stephanediondev/elasticsearch-admin/tree/main/screenshots/7)
- [8.x](https://github.com/stephanediondev/elasticsearch-admin/tree/main/screenshots/8)

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

docker run -e "ELASTICSEARCH_URL=http://x.x.x.x:9200" -e "SECRET_REGISTER=xxxxx" -p 80:8080 -p 443:4443 -d --name elasticsearch-admin stephanediondev/elasticsearch-admin
```

Edit ```ELASTICSEARCH_URL``` and ```SECRET_REGISTER``` (random string to secure registration)

If Elasticsearch security features are enabled, add ```-e "ELASTICSEARCH_USERNAME=xxxxx" -e "ELASTICSEARCH_PASSWORD=xxxxx"``` or ```-e "ELASTICSEARCH_API_KEY=xxxxx"```

If you have SSL certificates, add ```-v /path/privkey.pem:/etc/nginx/privkey.pem -v /path/fullchain.pem:/etc/nginx/fullchain.pem```

## Source installation

[(Back to installation)](#installation)

### Requirements

[(Back to source installation)](#source-installation)

- Web server
- PHP 8.2: [Visit](https://symfony.com/doc/current/setup/web_server_configuration.html)
- Composer: [Visit](https://getcomposer.org/download/)
- npm: [Visit](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm)

### Web server

[(Back to source installation)](#source-installation)

Configure a vhost with the document root set to the ```public``` folder (ie /var/www/elasticsearch-admin/public)

#### Apache

[(Back to web server)](#web-server)

Add the vhost below

```
<VirtualHost *:80>
    DocumentRoot "/var/www/elasticsearch-admin/public"
    ServerName your-domain-or-sub-domain
    ErrorLog ${APACHE_LOG_DIR}/elasticsearch-admin-error.log
    CustomLog ${APACHE_LOG_DIR}/elasticsearch-admin-access.log combined

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
</VirtualHost>
```

If you can't edit a vhost, add the Apache pack to get the ```.htaccess``` file in the ```public``` folder

```
composer require symfony/apache-pack
```

#### nginx

Add the server definition below

[(Back to web server)](#web-server)

```
server {
    listen [::]:8080 default_server;
    listen 8080 default_server;
    server_name your-domain-or-sub-domain;

    sendfile off;

    root /var/www/elasticsearch-admin/public;
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

Launch the following commands to install

```
cd /var/www/elasticsearch-admin/

composer install

npm install
npm run build

cp .env.dist .env
```

In the ```.env``` file edit ```ELASTICSEARCH_URL``` and ```SECRET_REGISTER``` (random string to secure registration)

If Elasticsearch security features are enabled, edit ```ELASTICSEARCH_USERNAME``` and ```ELASTICSEARCH_PASSWORD``` or ```ELASTICSEARCH_API_KEY```

You can also edit ```VAPID_PUBLIC_KEY``` and ```VAPID_PRIVATE_KEY``` to use push notifications (only in HTTPS)

You can generate values with the command below

```
bin/console app:generate-vapid
```

Add a cron command to send notifications every 5 minutes

```
crontab -e
# m h dom mon dow command
*/5 * * * * cd /var/www/elasticsearch-admin && bin/console app:send-notifications
```

## Launch

[(Back to installation)](#installation)

Once the installation is finished, you can access the application with one of the URL below (depending on your installation):

- http://docker-ip
- https://docker-ip (you need to approve the default localhost certificate)
- http://your-domain-or-sub-domain
- https://your-domain-or-sub-domain (if you have added certificates)

You will see a register page to create the first user (you need the secret registration key) [View](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/original-register.png)

After registration you can login [View](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/original-login.png)

# Other tools

[(Back to table of contents)](#table-of-contents)

| Name | Visit | Main language | Latest release* |
| --- | --- | --- | --- |
| Kaizen | [www.elastic-kaizen.com](https://www.elastic-kaizen.com/) | Java | 13 May 2022 |
| Elasticvue | [cars10/elasticvue](https://github.com/cars10/elasticvue) | JavaScript | 2 May 2022 |
| Dejavu | [appbaseio/dejavu](https://github.com/appbaseio/dejavu) | JavaScript | 14 Mar. 2022 |
| cerebro | [lmenezes/cerebro](https://github.com/lmenezes/cerebro) | Scala | 10 Apr. 2021 |
| Elasticsearch Comrade | [moshe/elasticsearch-comrade](https://github.com/moshe/elasticsearch-comrade) | Python | 30 May 2020 |
| ElasticHQ | [ElasticHQ/elasticsearch-HQ](https://github.com/ElasticHQ/elasticsearch-HQ) | Python | 15 Nov. 2019 |
| elasticsearch-head | [mobz/elasticsearch-head](https://github.com/mobz/elasticsearch-head) | JavaScript | 10 Apr. 2018 |

Checked on 13 May 2022 *

# License

[(Back to table of contents)](#table-of-contents)

[MIT License](https://github.com/stephanediondev/elasticsearch-admin/blob/main/LICENSE)

# Privacy

[(Back to table of contents)](#table-of-contents)

This application does **NOT** collect and send any user data.

# Development

[(Back to table of contents)](#table-of-contents)

You can run the application from a folder in an existing vhost (ie http://localhost/elasticsearch-admin/public)

## Unit tests

[(Back to development)](#development)

```
vendor/bin/phpstan analyse -l 6 src
vendor/bin/phpstan analyse -l 6 tests
bin/console app:phpunit
bin/phpunit
```
