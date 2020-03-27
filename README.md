## Installation

```
bin/console security:encode-password
#encode a password

cp .env.dist .env
#edit ELASTICSEARCH_URL, ELASTICSEARCH_USERNAME, ELASTICSEARCH_PASSWORD, EMAIL and ENCODED_PASSWORD

composer install

yarn install
yarn encore production
```

### Features

- [x] Cluster: basic metrics
- [x] Nodes: list, read
- [x] Indices: list, read
- [x] Indices: create, delete, close, open, force merge, clear cache, flush, refresh, reindex
- [x] Documents (by index): list
- [x] Aliases (by index): list, create, delete
- [x] Index templates: list, read
- [x] Index templates: create, update, delete
- [x] Shards: list
- [x] Repositories: list, read
- [x] Repositories: create (fs), delete, cleanup, verify
- [x] Snapshots: list, read
- [x] Snapshots: create, delete, failures
- [x] Snapshot lifecycle management policies: list, read
- [x] Snapshot lifecycle management policies: create, update, execute, history, stats
- [x] Tasks: list
- [x] Cat APIs: list

### Todo

- [ ] Authentication
- [ ] Snapshots: restore
- [ ] Indices: update mappings
- [ ] Documents (by index): filter, delete
- [ ] Cluster: stats, reroute
- [ ] Repositories: create (url, source, s3, hdfs, azure, gcs)

## Screenshots

![Cluster](assets/images/cluster.png)
![Nodes](assets/images/nodes.png)
![Indices](assets/images/indices.png)
![Index](assets/images/index.png)
![Index shards](assets/images/index_shards.png)
![Index documents](assets/images/index_documents.png)
![Tasks](assets/images/tasks.png)

