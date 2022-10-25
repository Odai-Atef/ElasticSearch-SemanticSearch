import tensorflow_hub as hub
import numpy as np
import tensorflow_text
import os
import json
import io
from io import StringIO
from html.parser import HTMLParser
from elasticsearch import Elasticsearch

client = Elasticsearch(
    "http://3.125.9.240:9221/",
)

class MLStripper(HTMLParser):
    def __init__(self):
        super().__init__()
        self.reset()
        self.strict = False
        self.convert_charrefs= True
        self.text = StringIO()
    def handle_data(self, d):
        self.text.write(d)
    def get_data(self):
        return self.text.getvalue()

def strip_tags(html):
    s = MLStripper()
    s.feed(html)
    return s.get_data()
    
embed = hub.load("https://tfhub.dev/google/universal-sentence-encoder-multilingual-large/3")
# Compute embeddings.
dir_path=r'/var/www/html/Salla/Products/'
dir_path_vector=r'/var/www/html/Salla/vectors/'
products_data=[]
for path in os.listdir(dir_path):
    if os.path.isfile(os.path.join(dir_path, path)):
        try:
            file = open(os.path.join(dir_path, path),'r')
            content = json.load(file)
            for product in content['products']:
                product_name=product['name']
                product_name_vector=embed([product_name])[0].numpy().tolist()
                product_description=strip_tags(str(product['description']))
                product_description_vector=embed([product_description])[0].numpy().tolist()
                product_id=product['id']
                # print(os.path.join(dir_path_vector, str(product_id) +".json"))
                data={'product_name':product_name,'product_name_vector':product_name_vector,'product_description':product_description,'product_description_vector':product_description_vector,'product_id':product_id}

                products_data.append({"index": {"_index": "products", "_id": product_id}})
                products_data.append(data)
                if len(products_data)>=500:
                    resp = client.bulk(body=products_data)
                    products_data=[]
                    # print(resp)
                # with io.open(os.path.join(dir_path_vector, str(product_id) +".json"), 'w', encoding='utf-8') as f:
                #     data={'product_name':product_name,'product_name_vector':product_name_vector,'product_description':product_description,'product_description_vector':product_description_vector,'product_id':product_id}
                #     print(data)
                #     f.write(json.dumps(data, ensure_ascii=False))
                
        except:
            print("Error Parsing")