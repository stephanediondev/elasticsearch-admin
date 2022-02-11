## Docker for development (not for production)

### Commands to run Elasticsearch for each version

```
docker run --name elasticsearch1 -v elasticsearch1:/usr/share/elasticsearch/data -p 100:9200 -p 1100:9300 -e "cluster.name=elasticsearch" -e "node.name=docker" -e "network.host=127.0.0.1" -e "http.host=0.0.0.0" -e "ES_JAVA_OPTS=-Xms512m -Xmx512m" -e "bootstrap.memory_lock=true" elasticsearch:1.6.0
docker run --name elasticsearch2 -v elasticsearch2:/usr/share/elasticsearch/data -p 200:9200 -p 1200:9300 -e "cluster.name=elasticsearch" -e "node.name=docker" -e "network.host=127.0.0.1" -e "http.host=0.0.0.0" -e "ES_JAVA_OPTS=-Xms512m -Xmx512m" -e "bootstrap.memory_lock=true" elasticsearch:2.0.0
docker run --name elasticsearch5 -v elasticsearch5:/usr/share/elasticsearch/data -p 500:9200 -p 1500:9300 -e "cluster.name=elasticsearch" -e "node.name=docker" -e "xpack.security.enabled=true" -e "xpack.monitoring.enabled=false" -e "network.host=127.0.0.1" -e "http.host=0.0.0.0" -e "ES_JAVA_OPTS=-Xms512m -Xmx512m" -e "bootstrap.memory_lock=true" docker.elastic.co/elasticsearch/elasticsearch:5.0.1
docker run --name elasticsearch6 -v elasticsearch6:/usr/share/elasticsearch/data -p 600:9200 -p 1600:9300 -e "cluster.name=elasticsearch" -e "node.name=docker" -e "xpack.security.enabled=true" -e "xpack.monitoring.enabled=false" -e "discovery.type=single-node" docker.elastic.co/elasticsearch/elasticsearch:6.0.0
docker run --name elasticsearch7 -v elasticsearch7:/usr/share/elasticsearch/data -p 700:9200 -p 1700:9300 -e "cluster.name=elasticsearch" -e "node.name=docker" -e "xpack.security.enabled=true" -e "xpack.monitoring.enabled=false" -e "discovery.type=single-node" docker.elastic.co/elasticsearch/elasticsearch:7.0.0
docker run --name elasticsearch8 -v elasticsearch8:/usr/share/elasticsearch/data -p 800:9200 -p 1800:9300 -e "cluster.name=elasticsearch" -e "node.name=docker" -e "xpack.security.enabled=true" -e "discovery.type=single-node" docker.elastic.co/elasticsearch/elasticsearch:8.0.0
```

Enable cross-origin resource sharing (not needed for elasticsearch-admin)

```
-e "http.cors.enabled=true" -e "http.cors.allow-origin=*" -e "http.cors.allow-headers=X-Requested-With,Content-Type,Content-Length,Authorization" -e "http.cors.allow-credentials=true"
```

### Commands to remove Elasticsearch for each version

```
docker stop elasticsearch1 && docker rm elasticsearch1 && docker volume rm elasticsearch1
docker stop elasticsearch2 && docker rm elasticsearch2 && docker volume rm elasticsearch2
docker stop elasticsearch5 && docker rm elasticsearch5 && docker volume rm elasticsearch5
docker stop elasticsearch6 && docker rm elasticsearch6 && docker volume rm elasticsearch6
docker stop elasticsearch7 && docker rm elasticsearch7 && docker volume rm elasticsearch7
docker stop elasticsearch8 && docker rm elasticsearch8 && docker volume rm elasticsearch8
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

### Create folder for fs repository

```
docker exec -it elasticsearchX bash
mkdir fs
chown -R elasticsearch:root fs
echo 'path.repo: ["/usr/share/elasticsearch/fs"]' >> config/elasticsearch.yml
```

### Run elasticsearch-admin with Docker Hub

```
docker pull stephanediondev/elasticsearch-admin
docker stop elasticsearch-admin && docker rm elasticsearch-admin && docker rmi elasticsearch-admin
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

### /etc/hosts

```
127.0.0.1   es1-docker
127.0.0.1   es2-docker
127.0.0.1   es5-docker
127.0.0.1   es6-docker
127.0.0.1   es7-docker
127.0.0.1   es8-docker
```

### Apache vhosts

```
<VirtualHost *:80>
    DocumentRoot "/var/www/elasticsearch-admin/public"
    ServerName es1-docker
    ErrorLog ${APACHE_LOG_DIR}/es1-docker-error.log
    CustomLog ${APACHE_LOG_DIR}/es1-docker-access.log combined

    SetEnv ELASTICSEARCH_URL http://x.x.x.x:100
</VirtualHost>

<VirtualHost *:80>
    DocumentRoot "/var/www/elasticsearch-admin/public"
    ServerName es2-docker
    ErrorLog ${APACHE_LOG_DIR}/es2-docker-error.log
    CustomLog ${APACHE_LOG_DIR}/es2-docker-access.log combined

    SetEnv ELASTICSEARCH_URL http://x.x.x.x:200
</VirtualHost>

<VirtualHost *:80>
    DocumentRoot "/var/www/elasticsearch-admin/public"
    ServerName es5-docker
    ErrorLog ${APACHE_LOG_DIR}/es5-docker-error.log
    CustomLog ${APACHE_LOG_DIR}/es5-docker-access.log combined

    SetEnv ELASTICSEARCH_URL http://x.x.x.x:500
    SetEnv ELASTICSEARCH_USERNAME elastic
    SetEnv ELASTICSEARCH_PASSWORD changeme
</VirtualHost>

<VirtualHost *:80>
    DocumentRoot "/var/www/elasticsearch-admin/public"
    ServerName es6-docker
    ErrorLog ${APACHE_LOG_DIR}/es6-docker-error.log
    CustomLog ${APACHE_LOG_DIR}/es6-docker-access.log combined

    SetEnv ELASTICSEARCH_URL http://x.x.x.x:600
    SetEnv ELASTICSEARCH_USERNAME elastic
    SetEnv ELASTICSEARCH_PASSWORD changeme
</VirtualHost>

<VirtualHost *:80>
    DocumentRoot "/var/www/elasticsearch-admin/public"
    ServerName es7-docker
    ErrorLog ${APACHE_LOG_DIR}/es7-docker-error.log
    CustomLog ${APACHE_LOG_DIR}/es7-docker-access.log combined

    SetEnv ELASTICSEARCH_URL http://x.x.x.x:700
    SetEnv ELASTICSEARCH_USERNAME elastic
    SetEnv ELASTICSEARCH_PASSWORD changeme
</VirtualHost>

<VirtualHost *:80>
    DocumentRoot "/var/www/elasticsearch-admin/public"
    ServerName es8-docker
    ErrorLog ${APACHE_LOG_DIR}/es8-docker-error.log
    CustomLog ${APACHE_LOG_DIR}/es8-docker-access.log combined

    SetEnv ELASTICSEARCH_URL http://x.x.x.x:800
    SetEnv ELASTICSEARCH_USERNAME elastic
    SetEnv ELASTICSEARCH_PASSWORD changeme
</VirtualHost>
```
