#get order_id
select order_id from `order` where customer_email="alitrip@toursforfun.cn"  and created>'2016-01-01' and created<'2016-10-01';

#get comments
select order_id,order_status_id,comments from order_status_history where order_id in (select order_id from `order` where customer_email="alitrip@toursforfun.cn"  and created>'2016-01-01' and created<'2016-10-01') and order_status_id in (1,200016);

#get retail
select order_id,value as retail from order_total where order_id in (select order_id from `order` where customer_email="alitrip@toursforfun.cn"  and created>'2016-01-01' and created<'2016-10-01') and class='ot_total';

#get capture
select order_id,order_value as capture from order_settlement_information where order_id in (select order_id from `order` where customer_email="alitrip@toursforfun.cn"  and created>'2016-01-01' and created<'2016-10-01');

#get retail adjustment
select o.order_id, op.product_id, opcra.amount 
from `order` as o left join order_product as op on o.order_id=op.order_id 
left join order_product_cost_retail_adjustment as opcra on op.order_product_id=opcra.order_product_id 
where o.customer_email="alitrip@toursforfun.cn"
and o.created>'2016-01-01'
and o.created<'2016-10-01'
and opcra.type='retail';

#get product departure date
select order_id,product_id,product_name,product_departure_date 
from order_product where order_id in
(select order_id from `order` where customer_email="alitrip@toursforfun.cn"  and created>'2016-01-01' and created<'2016-10-01');

#get reference comment
select order_id,reference_comments 
from order_settlement_information where order_id in
(select order_id from `order` where customer_email="alitrip@toursforfun.cn"  and created>'2016-01-01' and created<'2016-10-01');
