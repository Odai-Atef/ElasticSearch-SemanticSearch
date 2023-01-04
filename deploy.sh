
#!/bin/bash  
Green='\033[0;32m'
White='\033[0;37m'       

Elastic_HOST="http://3.125.9.240:9221/"
Product_Index="products"
Product_SCHEMA=`cat ./Docs/ProdSchema.json` 
Customer_SCHEMA="customers"
Recommendation_SCHEMA="recommendation"
# echo "Cloning Recommendation System \n"
# git clone git@github.com:SallaApp/Recommendation-Engine.git ../Recommendation-Engine
# echo "Run Docker compose  \n"
# docker-compose up -d
# echo "Check Elastic Search \n"
# curl $Elastic_HOST
echo "$Green Create Product Schema  $White \n"
curl -X PUT $Elastic_HOST$Product_Index -H 'Content-Type: application/json' -d @Docs/ProdSchema.json
echo "$Green Create Recommendation Schema $White \n"
curl -X PUT $Elastic_HOST$Recommendation_SCHEMA -H 'Content-Type: application/json' -d @Docs/
# echo "Create Customer Schema \n"
# curl -X PUT $Elastic_HOST+$Customer_SCHEMA -H 'Content-Type: application/json' -d''
# echo "Creat Mysql Database and import the data"

