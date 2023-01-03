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
from flask import Flask
app = Flask(__name__)


model_name= 'aubmindlab/bert-base-arabertv02'  
tokenizer =AutoTokenizer.from_pretrained(model_name) 
model=AutoModel.from_pretrained(model_name,output_hidden_states=True)

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
    return outputs['last_hidden_state'].detach().numpy()[0][0].tolist()
    # return string,text,outputs['last_hidden_state'].detach().numpy()[0][0].tolist()
    
# tokens =text_to_tokens(remove_stop_words(text)) 
@app.route("/vector/<text>")
def calc_vector(text):
    text=text.replace("+"," ")
    print(text)
    return embedding(text)

# In[ ]:

