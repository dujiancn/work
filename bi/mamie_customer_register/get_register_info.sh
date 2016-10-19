#!/bin/sh

file='./order_id.txt'
while read line
do
    sql="select o.order_id, ci.created as register_date from \`order\` as o left join customer_info as ci on o.customer_id=ci.customer_id where o.order_id=$line"
done < $file
