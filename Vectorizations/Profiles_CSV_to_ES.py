#!/usr/bin/env python
# coding: utf-8

# In[7]:


import os
import csv
import json
from elasticsearch import Elasticsearch


# In[8]:


csv_path="/var/www/html/Salla/customer_transcations_raw.csv"
client = Elasticsearch("http://3.125.9.240:9221/")
data = []
counter = 0


# In[10]:


with open(csv_path) as csv_file:
    reader = csv.DictReader(csv_file)
    for row in reader:
        counter = counter + 1
        data.append({"index": {"_index": "profiles22"}})
        data.append(row)
        if counter%500==0:
            resp = client.bulk(body=data)
            data = []
        


# In[ ]:




