--订单数量,匿名订单数量,订单中email和客户email不一致，订单中phone和客户phone不一致，订单email和客户email不一致且订单中phone和客户中phone不一致的数量
select date_format(o.created,"%Y-%m") as date,count(o.order_id) as total_count,
sum(if((o.purchased_without_account>0),1,0)) as anonymous_count,
sum(if((o.customer_email!=c.email and o.purchased_without_account=0),1,0)) as email_num, 
sum(if((o.customer_phone!=c.phone and o.purchased_without_account=0),1,0)) as phone_num,
sum(if((o.customer_phone!=c.phone and o.customer_email!=c.email and o.purchased_without_account=0),1,0)) as emmail_phone_num
from `order` as o 
join `customer` as c on o.customer_id=c.customer_id
where o.created like "2016%" group by date;

--anonymous order
select order_id from `order`
where purchased_without_account>0
and created like "2016%";

--customer_email数据
select o.order_id,o.customer_email as order_email, c.email as customer_email
from `order` as o
join `customer` as c on o.customer_id=c.customer_id
where o.created like "2016%"
and o.purchased_without_account=0
and o.customer_email!=c.email;

--customer_phone数据
select o.order_id,o.customer_phone as order_phone, c.phone as customer_phone
from `order` as o
join `customer` as c on o.customer_id=c.customer_id
where o.created like "2016%"
and o.purchased_without_account=0
and o.customer_phone!=c.phone;

--customer_email_phone数据
select o.order_id,o.customer_email as order_email, c.email as customer_email,o.customer_phone as order_phone, c.phone as customer_phone
from `order` as o
join `customer` as c on o.customer_id=c.customer_id
where o.created like "2016%"
and o.purchased_without_account=0
and o.customer_phone!=c.phone
and o.customer_email!=c.email;

