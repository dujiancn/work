#!/bin/sh
file_name="./product_id.txt"
res_file_name="./product_sell_num.txt"

sql="select p.product_id,pd.name,p.provider_id from product as p
left join provider as pv on p.provider_id = pv.provider_id
left join product_description as pd on p.product_id=pd.product_id
where pd.language_id=3
and p.product_entity_type=0 
and p.is_tff=1 
and p.active=1
and p.region_id!=6
and p.product_type_id!=10023
and p.product_type_id!=10026
and pv.currency_id in (1,2,3,7,11,12)
and p.product_id not in (select product_id from product_category where category_id=1143);"

res=`-e "$sql"`
echo "$res" | grep -v 'product_id' > $file_name

while read line
do
    product_id=`echo "$line" | awk -F "\t" '{print $1}'`
    product_name=`echo "$line" | awk -F "\t" '{print $2}'`
    provider_id=`echo "$line" | awk -F "\t" '{print $3}'`
    sql="select $product_id as product_id, \"$product_name\" as product_name, $provider_id as provider_id, count(*) as num from order_product where product_id=$product_id and order_item_purchase_date>\"2016-09-10\""
    res=`-e "$sql"`
    echo "$res" | grep -v 'product_id' >> $res_file_name
done < $file_name
