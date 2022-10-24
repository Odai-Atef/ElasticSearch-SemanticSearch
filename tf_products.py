import tensorflow.compat.v1 as tf
import tensorflow_hub as hub
import os
import json
import io
from io import StringIO

from html.parser import HTMLParser
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
    
graph = tf.Graph()

with tf.Session(graph = graph) as session:
    print("Downloading pre-trained embeddings from tensorflow hub…")
    embed = hub.Module("https://tfhub.dev/google/universal-sentence-encoder/3")
    # text_ph = tf.placeholder(tf.string)
    # embeddings = embed(text_ph)
    # print("Done.")
    # print("Creating tensorflow session…")
    # session = tf.Session()
    # session.run(tf.global_variables_initializer())
    # session.run(tf.tables_initializer())
    # print("Done.")
    # def embed_text(text):
    #     vectors = session.run(embeddings, feed_dict={text_ph: text})
    #     return [vector.tolist() for vector in vectors]



dir_path=r'/var/www/html/Salla/Products/'
dir_path_vector=r'/var/www/html/Salla/vectors/'
for path in os.listdir(dir_path):
    print(path)
    if os.path.isfile(os.path.join(dir_path, path)):
        try:
            file = open(os.path.join(dir_path, path),'r')
            content = json.load(file)
            for product in content['products']:
                product_name=str(product['name'])
                product_name_vector=embed_text([product_name])[0]
                product_description=strip_tags(str(product['description']))
                product_description_vector=embed_text([product_description])[0]
                product_id=product['id']
                with io.open(os.path.join(dir_path_vector, str(product_id) +".json"), 'w', encoding='utf-8') as f:
                    data={'product_name':product_name,'product_name_vector':product_name_vector,'product_description':product_description,'product_description_vector':product_description_vector,'product_id':product_id}
                    f.write(json.dumps(data, ensure_ascii=False))
        except:
            print("Error Parsing")

# for path in os.listdir(dir_path):
#     if os.path.isfile(os.path.join(dir_path, path)):
#         file = open(os.path.join(dir_path, path),'r')
#         content =json.load(file)
#         text=content['text']
#         text_vector = embed_text([text])[0]
#         data={"Document_name":text,"Doc_vector":text_vector}
#         print("Text to be embedded: {}".format(text))
#         print("Embedding size: {}".format(len(text_vector)))
#         print("Obtained Embedding[{},…]\n".format(text_vector[:5]))
#         with io.open(os.path.join(dir_path_vector, path), 'w', encoding='utf-8') as f:
#             print(os.path.join(dir_path_vector, path))
#             f.write(json.dumps(data, ensure_ascii=False))


# text="salah"
# text_vector = embed_text([text])[0]
# print("Text to be embedded: {}".format(text))
# print("Obtained Embedding[{},…]\n".format(text_vector[:5]))
# with io.open("/home/odai/v.json", 'w', encoding='utf-8') as f:
#     f.write(json.dumps(text_vector, ensure_ascii=False))
