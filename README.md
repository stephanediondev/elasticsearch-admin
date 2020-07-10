## Requirements

- Elasticsearch 6.x or 7.x: https://www.elastic.co/guide/en/elasticsearch/reference/current/install-elasticsearch.htm
- Web server with rewrite module enabled
- PHP 7.2.5 or higher: https://symfony.com/doc/current/setup/web_server_configuration.html
- Composer: https://getcomposer.org/download/
- npm: https://docs.npmjs.com/downloading-and-installing-node-js-and-npm

## Installation

### Docker

Repository: https://hub.docker.com/r/stephanediondev/elasticsearch-admin

```
docker run --publish 80:8080 -e "ELASTICSEARCH_URL=http://x.x.x.x:9200" -e "EMAIL=example@example.com" -e "ENCODED_PASSWORD=\$argon2id\$v=19\$m=65536,t=4,p=1\$Hx5YWkNlKMb6xkAumzAMYg\$wAtGPNTQoHoo+AyQphqu+WYqhL+BJlWgQqv71+MExw8" --detach --name elasticsearch-admin stephanediondev/elasticsearch-admin

#password = example
```

### Classic

Download or clone the repository

Configure a vhost with the document root set to "public" folder (ie /var/www/elasticsearch-admin/public)

```
cd /var/www/elasticsearch-admin/

composer install

npm install
npm run build

bin/console security:encode-password
# Encode a password

cp .env.dist .env
# Edit ELASTICSEARCH_URL, EMAIL and ENCODED_PASSWORD
# If Elasticsearch security features are enabled, edit ELASTICSEARCH_USERNAME and ELASTICSEARCH_PASSWORD
```

## Features

- [x] Connection to Elasticsearch: server-side (no CORS config), local (private) or remote server, http or https, with credentials or not
- [x] Login: user managed by Symfony, not related to Elasticsearch
- [x] Cluster: basic metrics, allocation explain, list settings, update settings (transient or persistent)
- [x] Nodes: list, read, usage, plugins, reload secure settings [6.4]
- [x] Indices: list, reindex, create, read, update (mappings), lifecycle [6.6] (explain, remove policy), delete, close / open, freeze / unfreeze [6.6], force merge, clear cache, flush, refresh, empty, import (ODS, XLSX) / export (CSV, TSV, ODS, XLSX, GEOJSON), aliases (list, create, delete)
- [x] Index templates (legacy): list, create, read, update, delete, copy
- [x] Index templates [7.8]: list, create, read, update, delete, copy
- [x] Component templates [7.8]: list, create, read, update, delete, copy
- [x] Index lifecycle management policies [6.6]: list, status, start, stop, create, read, update, delete, copy
- [x] Shards: list
- [x] Repositories: list, create (fs, s3, gcs), read, update, delete, cleanup, verify
- [x] Snapshot lifecycle management policies [7.4]: list, status, start, stop, create, read, update, delete, execute, history, stats, copy
- [x] Snapshots: list, create, read, delete, failures, restore
- [x] Users (native realm): list, create, read, update, delete, enable, disable
- [x] Roles: list, create, read, update, delete, copy
- [x] Tasks: list
- [x] Remote clusters: list
- [x] Enrich policies [7.5]: list, stats, create, read, delete, execute, copy
- [x] Pipelines: list, create, read, update, delete, copy
- [x] Cat APIs: list, export (CSV, TSV, ODS, XLSX)
- [x] Console
- [x] Deprecations info
- [x] License: read, status / start trial / revert to basic [6.6], features

## Todo

- [ ] Web installer: form to define parameters
- [ ] Users management with roles and permissions, not related to Elasticsearch
- [ ] Indices: update (dynamic index settings), shrink, split, import from database
- [ ] Index templates (legacy): convert to new version
- [ ] Repositories: create (url, source, hdfs, azure)
- [ ] Remote clusters: create, update, delete
- [ ] License: update

## Unit tests

```
bin/console app:phpunit && bin/phpunit
```

## Screenshots

[![Login](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-login.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-login.png)

[![Cluster](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-cluster.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-cluster.png)

[![Cluster settings](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-cluster-settings.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-cluster-settings.png)

[![Cluster allocation explain](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-cluster-allocation-explain.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-cluster-allocation-explain.png)

[![Nodes](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-nodes.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-nodes.png)

[![Node](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-node.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-node.png)

[![Indices](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-indices.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-indices.png)

[![Indices stats](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-indices-stats.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-indices-stats.png)

[![Index](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-index.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-index.png)

[![Create index](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-index-create.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-index-create.png)

[![Index templates](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-index-templates.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-index-templates.png)

[![Create index template](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-index-template-create.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-index-template-create.png)

[![Shards](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-shards.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-shards.png)

[![Create AWS S3 repository](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-repository-create-s3.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-repository-create-s3.png)

[![Create SLM policy](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-slm-policy-create.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-slm-policy-create.png)

[![Snaphosts](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-snapshots.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-snapshots.png)

[![Create snapshot](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-snapshot-create.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-snapshot-create.png)

[![Create enrich policy](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-enrich-create.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-enrich-create.png)

[![License](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/resized-license.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/assets/images/original-license.png)
