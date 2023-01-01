from Connection_info import *
import clickhouse_connect
import pandas as pd
import numpy as np


client = clickhouse_connect.get_client(host=host,
                                       port=http_port,
                                       username=username,
                                       password=password,
                                       query_limit=0)

def customers (customer_id):
    customer = '''select  id , city , country 
                from salla_reports_exposed.store_customers''' \
               f" where id IN ({customer_id})"

    customer = client.query (customer)
    customer_info = customer.result_set
    df_customer_info = pd.DataFrame (customer_info)

    df_customer_info.rename (columns={0: 'customer_id',
                                      1: 'customer_city',
                                      2: 'customer_country'
                                      }, inplace=True)

    return df_customer_info


def stores ():
    store = ''' select store_id  ,store_country_name , store_city_name 
                    from salla_reports_exposed.stores
                    where store_plan!='basic'
                    and last_order_date is not null
                    and DATEDIFF(month, last_order_date, today()) <=6
                    '''

    store = client.query (store)
    store_info = store.result_set
    df_store_info = pd.DataFrame (store_info)

    df_store_info.rename (columns={0: 'store_id',
                                   1: 'store_country_name',
                                   2: 'store_city_name'

                                   }, inplace=True)

    return df_store_info


def carts (store_id):
    cart = ''' 
            with cart as (SELECT id , store_id ,customer_id , status ,created_at
                         FROM salla_reports_exposed.cart
                         WHERE salla_reports_exposed.cart.customer_id !=0
                         AND salla_reports_exposed.cart.store_id !=0
                         AND salla_reports_exposed.cart.status in ('active','pending', 'purchased')''' \
           f"AND salla_reports_exposed.cart.store_id IN ({store_id})" \
           ''' AND salla_reports_exposed.cart.created_at between '2022-10-01' and '2022-11-01'),

  cart_items as (SELECT cart_id , product_id ,quantity ,product_price , currency
                  FROM salla_reports_exposed.cart_items
                  WHERE salla_reports_exposed.cart_items.deleted_at IS NULL
                  AND salla_reports_exposed.cart_items.`type` ='product'
  )

  SELECT cart_id , product_id ,quantity, created_at ,cart.customer_id , cart.store_id ,cart.status , product_price 
  FROM cart_items
  JOIN cart ON cart.id = cart_items.cart_id
  '''

    cart = client.query (cart)
    cart_info = cart.result_set
    df_cart_info2 = pd.DataFrame (cart_info)

    df_cart_info2.rename (columns={0: 'cart_id',
                                   1: 'product_id',
                                   2: 'quantity',
                                   3: 'Event_Date',
                                   4: 'customer_id',
                                   5: 'store_id',
                                   6: 'Event',
                                   7: 'product_price'
                                   }, inplace=True)


    if df_cart_info2.empty:
        pass
    else:
        df_cart_info2['Event'].replace (['active', 'pending'], 'cart', inplace=True)
        df_cart_info2['parent_category'] = 'NAN'
        df_cart_info2['sun_category'] = 'NAN'
        df_cart_info2['child_category'] = 'NAN'

    return df_cart_info2


def wishlist (customer_ids):
    wish = "SELECT product_id, customer_id , product.store_id" \
           " FROM salla_reports_exposed.customer_wishlist" \
           " LEFT JOIN (SELECT id, store_id FROM AI_dept.products)  product" \
           " ON product.id = salla_reports_exposed.customer_wishlist.product_id " \
           f" WHERE salla_reports_exposed.customer_wishlist.customer_id IN ({customer_ids})"

    wishlist = client.query (wish)
    wishlist = wishlist.result_set
    df_wishlist = pd.DataFrame (wishlist)

    df_wishlist.rename (columns={0: 'product_id',
                                 1: 'customer_id',
                                 2: 'store_id'
                                 }, inplace=True)

    df_wishlist['Event'] = 'wishlist'
    df_wishlist['Event_Date'] = 'NA'
    df_wishlist['parent_category'] = 'NAN'
    df_wishlist['sun_category'] = 'NAN'
    df_wishlist['child_category'] = 'NAN'
    df_wishlist['cart_id'] = np.nan
    df_wishlist['quantity'] = np.nan

    return df_wishlist


def feedback ():
    rating = ''' with rating as (SELECT store_id , customer_id, product_id,
              salla_reports_exposed.store_feedback.`type` , created_at
              FROM salla_reports_exposed.store_feedback
              WHERE salla_reports_exposed.store_feedback.customer_id !=0 
              AND salla_reports_exposed.store_feedback.`type`='rating'
              AND salla_reports_exposed.store_feedback.created_at between '2022-10-01' and '2022-11-01'
              AND salla_reports_exposed.store_feedback.store_id IN (select store_id
                                                                    from salla_reports_exposed.stores
                                                                    where store_plan!='basic'
                                                                    and last_order_date is not null
                                                                    and DATEDIFF(month, last_order_date, today()) <=6)),
             product as ( SELECT id , name , description, price, currency
                FROM AI_dept.products)


            select rating.store_id , rating.customer_id, rating.product_id,rating.`type` , rating.created_at
            from product
            join rating on rating.product_id=product.id
    '''
    rating = client.query (rating)
    rating = rating.result_set
    df_rating = pd.DataFrame (rating)

    df_rating.rename (columns={0: 'store_id',
                               1: 'customer_id',
                               2: 'product_id',
                               3: 'Event',
                               4: 'Event_Date',
                               5: 'product_name',
                               6: 'product_description',
                               7: 'product_price',
                               8: 'product_currency'
                               }, inplace=True)
    df_rating['parent_category'] = 'NAN'
    df_rating['sun_category'] = 'NAN'
    df_rating['child_category'] = 'NAN'
    df_rating['cart_id'] = np.nan
    df_rating['quantity'] = np.nan

    return df_rating


def products (product_ids):
    product = ''' SELECT id , name , description ,price , currency
                FROM AI_dept.products
                WHERE AI_dept.products.status='sale'
                AND LENGTH(AI_dept.products.name) >= 4 
                AND AI_dept.products.name !='...' ''' \
              f'AND AI_dept.products.id IN {product_ids}'

    prosuct = client.query (product)
    prosuct = prosuct.result_set
    df_prosuct = pd.DataFrame (prosuct)

    df_prosuct.rename (columns={0: 'product_id',
                                1: 'product_name',
                                2: 'product_description',
                                3: 'product_price',
                                4: 'product_currency'
                                }, inplace=True)

    return df_prosuct