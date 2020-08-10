## Running with Docker

### Requirements

- Docker: https://www.docker.com/get-started

### Steps

The Docker image is hosted on Docker Hub (https://hub.docker.com/r/stephanediondev/elasticsearch-admin)

```
docker pull stephanediondev/elasticsearch-admin:latest

docker run -e "ELASTICSEARCH_URL=http://x.x.x.x:9200" -e "SECRET_REGISTER=xxxxx" -p 80:8080 -d --name elasticsearch-admin stephanediondev/elasticsearch-admin:latest
```

Edit ```ELASTICSEARCH_URL``` and ```SECRET_REGISTER``` (random string to secure registration)

If Elasticsearch security features are enabled, add ```-e "ELASTICSEARCH_USERNAME=xxxxx" -e "ELASTICSEARCH_PASSWORD=xxxxx"```
