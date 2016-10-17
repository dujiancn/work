#!/bin/sh

file='./order_id.txt'
passwd='hslxk96rvardr[idLvjZ'
while read line
do
    sql="select o.order_id, ci.created as register_date from \`order\` as o left join customer_info as ci on o.customer_id=ci.customer_id where o.order_id=$line"
    mysql -htoursforfunread.mysql.db.ctripcorp.com -P55944 -uuws_tours4fun_r --default-character-set=utf8 -p$passwd -A tffdb -e $sql
done < $file
