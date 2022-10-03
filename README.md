# ElasticSearch-SemanticSearch
Install and run the model
```
pip install requirements.txt
python tf_tweets.py
```
Install and run docker for Elasticsearch
```
docker pull docker.elastic.co/elasticsearch/elasticsearch:8.4.2
docker network create elastic
docker run --name es01 --net elastic -p 9200:9200 -p 9300:9300 -it docker.elastic.co/elasticsearch/elasticsearch:8.4.2
```
Generate Vector for seach keyword
```
python tf_text.py
```
ElasticSearch Schema
```
{
  "settings": {
    "number_of_shards": 2,
    "number_of_replicas": 1
  },
  "mappings": {
    "dynamic": "true",
    "_source": {
      "enabled": "true"
    },
    "properties": {
      "Document_name": {
        "type": "text"
      },
      "Doc_vector": {
        "type": "dense_vector",
        "dims": 512
      }
    }
  }
}
```
Query ElasticSearch use query.json with post request 
