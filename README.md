The application named elasticsearch-admin is NOT affiliated in any way with Elasticsearch BV.

Elasticsearch is a trademark of Elasticsearch BV, registered in the U.S. and in other countries.

- [Running with Docker](#running-with-docker)
- [Source installation](#source-installation)
- [Features](#features)
- [Todo](#todo)
- [Screenshots](#screenshots)
- [Other tools](#other-tools)
- [Unit tests](#unit-tests)

## Running with Docker

- Repository: https://hub.docker.com/r/stephanediondev/elasticsearch-admin

```
docker run -e "ELASTICSEARCH_URL=http://x.x.x.x:9200" -e "SECRET_REGISTER=xxxxx" -p 80:8080 -d --name elasticsearch-admin stephanediondev/elasticsearch-admin

# Edit ELASTICSEARCH_URL and SECRET_REGISTER (random string to secure registration)
# If Elasticsearch security features are enabled, add -e "ELASTICSEARCH_USERNAME=xxxxx" -e "ELASTICSEARCH_PASSWORD=xxxxx"
```

## Source installation

- Web server with rewrite module enabled
- PHP 7.2.5 or higher: https://symfony.com/doc/current/setup/web_server_configuration.html
- Composer: https://getcomposer.org/download/
- npm: https://docs.npmjs.com/downloading-and-installing-node-js-and-npm

Download or clone the repository

Configure a vhost with the document root set to the "public" folder (ie /var/www/elasticsearch-admin/public)

```
cd /var/www/elasticsearch-admin/

composer install

npm install
npm run build

cp .env.dist .env

# Edit ELASTICSEARCH_URL and SECRET_REGISTER (random string to secure registration)
# If Elasticsearch security features are enabled, edit ELASTICSEARCH_USERNAME and ELASTICSEARCH_PASSWORD
```

## Features

- [x] Supported Elasticsearch versions: 2.x, 5.x, 6.x, 7.x, 8.x (snapshot)
- [x] Connection to Elasticsearch: server-side (no CORS issue), private or public, local or remote, http or https, credentials or not
- [x] App users: register, login, logout, list, create, read, update, delete
- [x] App roles: list, create, read, update (permissions), delete
- [x] Cluster: basic metrics, allocation explain [5.0], settings [5.0] (list, update)
- [x] Nodes: list, read, usage [6.0], plugins, reload secure settings [6.4]
- [x] Indices: list, reindex, create, read, update (mappings), lifecycle [6.6] (explain, remove policy), delete, close / open, freeze / unfreeze [6.6], force merge [2.1], clear cache, flush, refresh, empty [5.0], search by query, export (CSV, TSV, ODS, XLSX, GEOJSON), import from file (ODS, XLSX), import from database (PDO drivers), aliases (list, create, delete)
- [x] Legacy templates: list, create, read, update, delete, copy
- [x] Composable templates [7.8]: list, create, read, update, delete, copy
- [x] Component templates [7.8]: list, create, read, update, delete, copy
- [x] Index lifecycle management policies [6.6]: list, status, start, stop, create, read, update, delete, copy
- [x] Shards: list, cluster reroute (move, allocate replica, cancel allocation)
- [x] Repositories: list, create (fs, s3, gcs, azure), read, update, delete, cleanup, verify
- [x] Snapshot lifecycle management policies [7.4]: list, status, start, stop, create, read, update, delete, execute, history, stats, copy
- [x] Snapshots: list, create, read, delete, failures, restore
- [x] Elasticsearch users (native realm): list, create, read, update, delete, enable, disable
- [x] Elasticsearch roles: list, create, read, update, delete, copy
- [x] Tasks [2.3]: list
- [x] Remote clusters [5.4]: list
- [x] Enrich policies [7.5]: list, stats, create, read, delete, execute, copy
- [x] Pipelines [5.0]: list, create, read, update, delete, copy
- [x] Cat APIs: list, export (CSV, TSV, ODS, XLSX)
- [x] Console
- [x] SQL access [6.3]: query, translate to DSL
- [x] Deprecations info [5.6]
- [x] License [5.0]: read, status / start trial / revert to basic [6.6], features
- [x] Index graveyard [5.0]: list

## Todo

- [ ] SQL access: export
- [ ] Indices: shrink, split, clone
- [ ] Repositories: create (url, source, hdfs)
- [ ] Remote clusters: create, update, delete
- [ ] License: update

## Screenshots

[![Cluster](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-cluster.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-cluster.png)

[![Cluster settings](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-cluster-settings.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-cluster-settings.png)

[![Cluster allocation explain](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-cluster-allocation-explain.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-cluster-allocation-explain.png)

[![Nodes](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-nodes.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-nodes.png)

[![Node](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-node.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-node.png)

[![Indices](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-indices.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-indices.png)

[![Indices stats](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-indices-stats.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-indices-stats.png)

[![Index](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-index.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-index.png)

[![Index search](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-index-search.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-index-search.png)

[![Index import from file](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-index-file-import.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-index-file-import.png)

[![Index import from database](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-index-database-import.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-index-database-import.png)

[![Legacy templates](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-index-templates-legacy.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-index-templates-legacy.png)

[![Shards](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-shards.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-shards.png)

[![Create AWS S3 repository](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-repository-create-s3.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-repository-create-s3.png)

[![Create SLM policy](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-slm-policy-create.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-slm-policy-create.png)

[![Snaphosts](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-snapshots.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-snapshots.png)

[![Create snapshot](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-snapshot-create.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-snapshot-create.png)

[![License](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/resized/resized-license.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.8.0/original/original-license.png)

## Other tools

- http://mobz.github.io/elasticsearch-head/
- http://www.elastichq.org/
- https://elasticvue.com/
- https://github.com/lmenezes/cerebro
- https://github.com/moshe/elasticsearch-comrade
- https://opensource.appbase.io/dejavu/
- https://www.elastic-kaizen.com/

## Unit tests

```
bin/console app:phpunit && bin/phpunit
```
