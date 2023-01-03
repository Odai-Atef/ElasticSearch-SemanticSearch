from query import *
from elasticsearch import Elasticsearch

es_client = Elasticsearch (f"http://{ELASTIC_SEARCH_HOST}:{ELASTIC_SEARCH_PORT}")
def es_indexing (df):
    dfjson = df.to_json (orient='records', lines=True).splitlines ()
    counter = 0
    data = []
    for row in dfjson:
        counter = counter + 1
        data.append ({"index": {"_index": ELASTIC_SEARCH_INDEX}})
        data.append (row)
        if counter % 100 == 0:
            resp = es_client.bulk (body=data)
            data = []

    es_client.bulk (body=data)

# stores_df
# ----------------
stores_df = stores ()
stores_id = stores_df.store_id.to_list ()


# cart_df
# ----------------
stop = len (stores_id)
step = 500
c_i = []

for cart_index in range(0,stop , step):
    cart_info = carts (stores_id[cart_index:(step+cart_index)])
    c_i.append (cart_info)

cart_items_df = pd.concat (c_i, axis=0, ignore_index=True)

# rating_df
# ----------------
rating_df = feedback ()



# wishlist_df
# ----------------
customer_id = cart_items_df.customer_id.to_list ()
stop0 = len (customer_id)
step0 = 20000
w_i = []

for wishlist_index in range (0, stop0, step0):
    cart_info = wishlist (customer_id[wishlist_index:(step0 + wishlist_index)])
    w_i.append (cart_info)

wishlist_df = pd.concat (w_i, axis=0, ignore_index=True)



# product_df
# ----------------
data1 = pd.concat ([cart_items_df, rating_df , wishlist_df], ignore_index=True, sort=False)
data2 = pd.merge (data1, stores_df, on='store_id', how='left')

product_list = list (set (data2.product_id))
stop1 = len (product_list)
step1 = 1000
p_i = []
for product in range(0, len(product_list) , step1):
    product = products (product_list[product:(product+step1)])
    p_i.append (product)

products_df = pd.concat (p_i, axis=0, ignore_index=True)

data3 = pd.merge (data2, products_df, on='product_id', how='left')
cart_items_df_ids = data3.customer_id.to_list ()

# ------------------------------------------------
customer_idss = list(set(data3.customer_id))
stop1 = len (customer_idss)
step1 = 10000
cu_i = []
for cu_index in range(0, len(customer_idss) , step1):
    cu_info = customers (customer_idss[cu_index:(cu_index+step1)])
    cu_i.append (cu_info)

customer_df = pd.concat (cu_i, axis=0, ignore_index=True)


data5 = pd.merge (data3, customer_df, on='customer_id', how='left')
data6=data5[~data5['product_name'].isna()]



# index data to ES
s=100000

for i in range (0 , len(data6), s):
    es_indexing(data6[i+1:s+i])