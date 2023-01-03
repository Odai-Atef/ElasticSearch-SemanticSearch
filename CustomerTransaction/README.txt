
1- Start with Connection_info.py to sit your access
2- run the main.py file to run all the queries ---> data3 is the final data set that you can index

3- the data is selected for  Mahally stores with this condition :
        * store plan != basic
        * last_order_date <=6 months , which means the store should be active and receiving orders

4- the stores ids used to select the cart and the items in the cart
    Note : count of carts are huge , so I am only using a sample of 10 carts / 10 stores
          you can select more data

5-I am using (now()- toIntervalMonth(2)) to select the (carts , feedback) for the past 2 months only ,
    so whenever you run the quire you get 2 months of data size

