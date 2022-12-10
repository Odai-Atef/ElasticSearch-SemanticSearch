#!/usr/bin/env python
# coding: utf-8

# In[ ]:


from transformers import AutoTokenizer, AutoModel
from arabert.preprocess import ArabertPreprocessor
from farasa.segmenter import FarasaSegmenter
import torch
import arabicstopwords.arabicstopwords as ast
import string
from html.parser import HTMLParser
import os
import json
from elasticsearch import Elasticsearch
from io import StringIO

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

# In[ ]:


model_name= 'aubmindlab/bert-base-arabertv02'  
tokenizer =AutoTokenizer.from_pretrained(model_name) 
model=AutoModel.from_pretrained(model_name,output_hidden_states=True)

client = Elasticsearch(
    "http://3.125.9.240:9221/",
)

# In[ ]:


def remove_punctuation(text):
    return text.translate(str.maketrans('', '', string.punctuation))

def remove_stop_words(text):
    stop_words = ast.stopwords_list()
    
    return ' '.join(word for word in text.split() if word not in stop_words)
def strip_tags(html):
    html=str(html)
    s = MLStripper()
    s.feed(html)
    return s.get_data()

def text_to_tokens(text):
    return tokenizer.tokenize(text)

def embedding(text):
    text = remove_stop_words(remove_punctuation(text))
    inputs = tokenizer.encode_plus(text,return_tensors='pt')
    outputs = model(inputs['input_ids'],inputs['attention_mask'],inputs['token_type_ids'])
    print(outputs['last_hidden_state'].shape)
    return outputs['last_hidden_state'].detach().numpy()[0][0].tolist()
    # return string,text,outputs['last_hidden_state'].detach().numpy()[0][0].tolist()
    
# tokens =text_to_tokens(remove_stop_words(text)) 


# In[ ]:


#reading json products
dir_path=r'/var/www/html/Salla/New/'
products_data=[]
for path in os.listdir(dir_path):
    if os.path.isfile(os.path.join(dir_path, path)):
        try:
            file = open(os.path.join(dir_path, path),'r')
            content = json.load(file)
            key_string=list(content.keys())[0]
            for product in content[key_string]:
                data=product
                data['product_description']=strip_tags(data['description'])
                data['product_description_vector'] = embedding(data['product_description'])
                data['product_name_vector'] = embedding(data['name'])
                exit(1)
                product_id=product['product_id']
                products_data.append({"index": {"_index": "products_semantic22", "_id": product_id}})
                products_data.append(data)
                if len(products_data)>=100:
                    resp = client.bulk(body=products_data)
                    products_data=[]
                    print("1000 Indexed")
                
        except:
            print("Error Parsing")

resp = client.bulk(body=products_data)


# import numpy as np
# from numpy.linalg import norm
 
# # define two lists or array
# nan, nan, A = embedding(text1)
# nan, nan, B  = embedding(text2)


# # compute cosine similarity
# cosine = np.dot(A,B)/(norm(A)*norm(B))
# print("Cosine Similarity:", cosine)


# # In[ ]:




