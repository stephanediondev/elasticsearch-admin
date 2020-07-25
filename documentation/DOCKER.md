## Docker

### Commands to run Elasticsearch for each version

```
docker run --name elasticsearch1 -p 100:9200 -p 1100:9300 -e "cluster.name=elasticsearch" -e "node.name=docker" -e "network.host=127.0.0.1" -e "http.host=0.0.0.0" -e "ES_JAVA_OPTS=-Xms512m -Xmx512m" -e "bootstrap.memory_lock=true" elasticsearch:1.6.0
docker run --name elasticsearch2 -p 200:9200 -p 1200:9300 -e "cluster.name=elasticsearch" -e "node.name=docker" -e "network.host=127.0.0.1" -e "http.host=0.0.0.0" -e "ES_JAVA_OPTS=-Xms512m -Xmx512m" -e "bootstrap.memory_lock=true" elasticsearch:2.0.0
docker run --name elasticsearch5 -p 500:9200 -p 1500:9300 -e "cluster.name=elasticsearch" -e "node.name=docker" -e "xpack.security.enabled=true" -e "xpack.monitoring.enabled=false" -e "network.host=127.0.0.1" -e "http.host=0.0.0.0" -e "ES_JAVA_OPTS=-Xms512m -Xmx512m" -e "bootstrap.memory_lock=true" docker.elastic.co/elasticsearch/elasticsearch:5.0.1
docker run --name elasticsearch6 -p 600:9200 -p 1600:9300 -e "cluster.name=elasticsearch" -e "node.name=docker" -e "xpack.security.enabled=true" -e "xpack.monitoring.enabled=false" -e "discovery.type=single-node" docker.elastic.co/elasticsearch/elasticsearch:6.0.0
docker run --name elasticsearch7 -p 700:9200 -p 1700:9300 -e "cluster.name=elasticsearch" -e "node.name=docker" -e "xpack.security.enabled=true" -e "xpack.monitoring.enabled=false" -e "discovery.type=single-node" docker.elastic.co/elasticsearch/elasticsearch:7.0.0
docker run --name elasticsearch8 -p 800:9200 -p 1800:9300 -e "cluster.name=elasticsearch" -e "node.name=docker" -e "xpack.security.enabled=true" -e "xpack.monitoring.enabled=false" -e "discovery.type=single-node" docker.elastic.co/elasticsearch/elasticsearch:8.0.0-SNAPSHOT
```

Enable cross-origin resource sharing (not needed for elasticsearch-admin)

```
-e "http.cors.enabled=true" -e "http.cors.allow-origin=*" -e "http.cors.allow-headers=X-Requested-With,Content-Type,Content-Length,Authorization" -e "http.cors.allow-credentials=true"
```

### Setup passwords

The default password is ```changeme```

#### Elasticsearch 5

```
PUT _xpack/security/user/elastic/_password
{
    "password": "xxx"
}
```

#### Elasticsearch 6

```
docker exec -it elasticsearch6 bash
bin/x-pack/setup-passwords auto
```

#### Elasticsearch 7

```
docker exec -it elasticsearch7 bash
bin/elasticsearch-setup-passwords auto
```

#### Elasticsearch 8

```
docker exec -it elasticsearch8 bash
bin/elasticsearch-setup-passwords auto
```

### Run elasticsearch-admin with Docker Hub

```
docker pull stephanediondev/elasticsearch-admin

docker run -e "ELASTICSEARCH_URL=http://x.x.x.x:9200" -e "SECRET_REGISTER=xxxxx" -p 80:8080 -d --name elasticsearch-admin stephanediondev/elasticsearch-admin

# Edit ELASTICSEARCH_URL and SECRET_REGISTER (random string to secure registration)
# If Elasticsearch security features are enabled, add -e "ELASTICSEARCH_USERNAME=xxxxx" -e "ELASTICSEARCH_PASSWORD=xxxxx"
```

### Build and run elasticsearch-admin with source installation

````
cd /var/www/elasticsearch-admin/
docker stop elasticsearch-admin && docker rm elasticsearch-admin && docker rmi elasticsearch-admin
docker build --force-rm --tag elasticsearch-admin .

docker run -e "ELASTICSEARCH_URL=http://x.x.x.x:9200" -e "SECRET_REGISTER=xxxxx" -p 80:8080 -d --name elasticsearch-admin elasticsearch-admin

# Edit ELASTICSEARCH_URL and SECRET_REGISTER (random string to secure registration)
# If Elasticsearch security features are enabled, add -e "ELASTICSEARCH_USERNAME=xxxxx" -e "ELASTICSEARCH_PASSWORD=xxxxx"
````
