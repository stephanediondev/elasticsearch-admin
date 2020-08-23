[![PHP Composer](https://github.com/stephanediondev/elasticsearch-admin/workflows/PHP%20Composer/badge.svg)](https://github.com/stephanediondev/elasticsearch-admin/actions) [![Travis CI](https://travis-ci.org/stephanediondev/elasticsearch-admin.svg?branch=master)](https://travis-ci.org/stephanediondev/elasticsearch-admin) [![SymfonyInsight](https://insight.symfony.com/projects/2b71459c-720a-46ef-a15b-a9ddd39f8739/mini.svg)](https://insight.symfony.com/projects/2b71459c-720a-46ef-a15b-a9ddd39f8739)

The application named elasticsearch-admin is NOT affiliated in any way with Elasticsearch BV.

Elasticsearch is a trademark of Elasticsearch BV, registered in the U.S. and in other countries.

- [Product pages](#product-pages)
- [Running with Docker](#running-with-docker)
- [Source installation](#source-installation)
- [Features](#features)
- [Screenshots](#screenshots)
- [Other tools](#other-tools)
- [Unit tests](#unit-tests)

## Product pages

- [Product Hunt](https://www.producthunt.com/posts/elasticsearch-admin)
- [Slant](https://www.slant.co/topics/11537/viewpoints/12/~elasticsearch-gui-clients~elasticsearch-admin)

## Running with Docker

[See detailed documentation](https://github.com/stephanediondev/elasticsearch-admin/blob/master/documentation/RUNNING_WITH_DOCKER.md)

## Source installation

[See detailed documentation](https://github.com/stephanediondev/elasticsearch-admin/blob/master/documentation/SOURCE_INSTALLATION.md)

## Features

- Supported Elasticsearch versions: 2.x, 5.x, 6.x, 7.x, 8.x (snapshot)
- Connection to Elasticsearch: server-side (no CORS issue), private or public, local or remote, http or https, credentials or not
- App users: register, login, logout, list, create, read, update, delete
- App roles: list, create, read, update (permissions), delete
- Cluster: basic metrics, audit, allocation explain [5.0], settings [5.0] (list, update)
- Nodes: list, read, usage [6.0], plugins, reload secure settings [6.4]
- Indices: list, stats, reindex, create, read, update (mappings), lifecycle [6.6] (explain, remove policy), delete, close / open, freeze / unfreeze [6.6], force merge [2.1], clear cache, flush, refresh, empty [5.0], search by query, export (CSV, TSV, ODS, XLSX, GEOJSON), import from file (ODS, XLSX), import from database (MySQL, PostgreSQL), aliases (list, create, delete)
- Legacy index templates: list, create, read, update, delete, copy
- Composable index templates [7.8]: list, create, read, update, delete, copy
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

## Screenshots

[See all available screenshots](https://github.com/stephanediondev/elasticsearch-admin/tree/master/screenshots/7.9.0)

[![Cluster](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-cluster.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-cluster.png)

[![Cluster audit](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-cluster-audit.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-cluster-audit.png)

[![Cluster settings](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-cluster-settings.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-cluster-settings.png)

[![Cluster allocation explain](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-cluster-allocation-explain.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-cluster-allocation-explain.png)

[![Nodes](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-nodes.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-nodes.png)

[![Node](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-node.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-node.png)

[![Indices](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-indices.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-indices.png)

[![Indices stats](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-indices-stats.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-indices-stats.png)

[![Index](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-index.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-index.png)

[![Index search](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-index-search.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-index-search.png)

[![Index import from file](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-index-file-import.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-index-file-import.png)

[![Index import from database](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-index-database-import.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-index-database-import.png)

[![Legacy index templates](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-index-templates-legacy.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-index-templates-legacy.png)

[![Shards](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-shards.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-shards.png)

[![Create AWS S3 repository](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-repository-create-s3.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-repository-create-s3.png)

[![Create SLM policy](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-slm-policy-create.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-slm-policy-create.png)

[![Snaphosts](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-snapshots.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-snapshots.png)

[![Create snapshot](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-snapshot-create.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-snapshot-create.png)

[![License](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/resized/resized-license.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/7.9.0/original/original-license.png)

## Other tools

| Name | Website | GitHub | Main language |
| --- | --- | --- | --- |
| cerebro | | https://github.com/lmenezes/cerebro | Scala |
| Dejavu | https://opensource.appbase.io/dejavu/ | https://github.com/appbaseio/dejavu | JavaScript |
| ElasticHQ | http://www.elastichq.org/ | https://github.com/ElasticHQ/elasticsearch-HQ | Python |
| Elasticsearch Comrade | | https://github.com/moshe/elasticsearch-comrade | Python |
| elasticsearch-head | http://mobz.github.io/elasticsearch-head/ | https://github.com/mobz/elasticsearch-head | JavaScript |
| Elasticvue | https://elasticvue.com/ | https://github.com/cars10/elasticvue | JavaScript |
| ELKman | https://www.elkman.io/ | | PHP |
| Kaizen | https://www.elastic-kaizen.com/ | | JavaFX |

## Unit tests

```
bin/console app:phpunit && bin/phpunit
```
