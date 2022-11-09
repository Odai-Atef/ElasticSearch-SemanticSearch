# import tensorflow_hub as hub
import numpy as np
# import tensorflow_text
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
    html=str(html)
    s = MLStripper()
    s.feed(html)
    return s.get_data()
    
# embed = hub.load("https://tfhub.dev/google/universal-sentence-encoder-multilingual-large/3")
# Compute embeddings.
dir_path=r'/var/www/html/Salla/New/'
dir_path_vector=r'/var/www/html/Salla/vectors/'
products_data=[]
for path in os.listdir(dir_path):
    if os.path.isfile(os.path.join(dir_path, path)):
        try:
            file = open(os.path.join(dir_path, path),'r')
            content = json.load(file)
            for product in content['SELECT smsp.id as product_id, smsp.name as product_name,smsc.id as category_id ,\r\nsmsc.name as category_name,smsp.description as product_description, smsb.name as brand_name\r\nFROM salla_reports.stg_mysql_salla__products smsp \r\nJOIN stg_mysql_salla__product_categories smspc  on stg_mysql_salla__product_categories.product_id = smsp.id \r\nJOIN stg_mysql_salla__categories smsc on stg_mysql_salla__product_categories.category_id = stg_mysql_salla__categories.id \r\nJOIN stg_mysql_salla__brands smsb on smsb.id = smsp.brand_id ']:
                data=product
                data['product_description']=strip_tags(data['product_description'])
                product_id=product['product_id']
                products_data.append({"index": {"_index": "products3", "_id": product_id}})
                products_data.append(data)
                if len(products_data)>=1000:
                    resp = client.bulk(body=products_data)
                    products_data=[]
                    # print(resp)
                # with io.open(os.path.join(dir_path_vector, str(product_id) +".json"), 'w', encoding='utf-8') as f:
                #     data={'product_name':product_name,'product_name_vector':product_name_vector,'product_description':product_description,'product_description_vector':product_description_vector,'product_id':product_id}
                #     print(data)
                #     f.write(json.dumps(data, ensure_ascii=False))
            # os.remove(os.path.join(dir_path, path))
                
        except:
            print("Error Parsing")