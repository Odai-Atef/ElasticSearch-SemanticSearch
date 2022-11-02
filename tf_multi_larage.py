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
dir_path=r'/var/www/html/Salla/New/'
dir_path_vector=r'/var/www/html/Salla/vectors/'
products_data=[]
for path in os.listdir(dir_path):
    if os.path.isfile(os.path.join(dir_path, path)):
        try:
            file = open(os.path.join(dir_path, path),'r')
            content = json.load(file)
            for product in content['SELECT salla_reports.stg_mysql_salla__products.name  as product_name,\r\ncategory_id,\r\nsalla_reports.stg_mysql_salla__products.description as product_description,\r\nsalla_reports.stg_mysql_salla__product_options.description as option_description,\r\nsalla_reports.stg_mysql_salla__brands.name as brand_name,\r\nsalla_reports.stg_mysql_salla__product_options.id as product_id  from salla_reports.stg_mysql_salla__products \r\njoin salla_reports.stg_mysql_salla__product_categories on salla_reports.stg_mysql_salla__product_categories.product_id = salla_reports.stg_mysql_salla__products.id\r\njoin salla_reports.stg_mysql_salla__brands on salla_reports.stg_mysql_salla__products.brand_id = salla_reports.stg_mysql_salla__brands.id \r\njoin salla_reports.stg_mysql_salla__product_options smspo  on  salla_reports.stg_mysql_salla__product_options.product_id  = salla_reports.stg_mysql_salla__products.id ']:
                data=product
                data['product_description']=strip_tags(data['product_description'])
                product_id=product['product_id']
                products_data.append({"index": {"_index": "products2", "_id": product_id}})
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