--统计发帖数量
select date_format(created,"%Y-%m") as date,count(*) from travel_companion where created>="2015-01" AND created<="2016-07" group by date;
--统计订单数量
select date_format(created,"%Y-%m") as date,count(*) from `order` as o where o.created>="2015-01" AND o.created<"2016-07" AND o.order_id in (select distinct(order_id) from order_travel_companion) group by date;
--抽取所有订单
select o.order_id,o.created,o.status,ot.value as retail from `order` as o left join order_total as ot on ot.order_id=o.order_id where o.created>="2015-01" AND o.created<"2016-07" AND o.order_id in (select distinct(order_id) from order_travel_companion) AND ot.class="ot_total";
---支付订单统计
select date_format(created,"%Y-%m") as date,count(*) from `order` as o where o.created>="2015-01" AND o.created<"2016-07" and o.order_id in(select distinct(order_id) from order_status_history where order_id in (select order_id from order_travel_companion) and order_status_id in (100005,100006,100023)) group by date;
---执行订单统计
select date_format(created,"%Y-%m") as date,count(*) from `order` as o where o.created>="2015-01" AND o.created<"2016-07" and o.order_id in(select distinct(order_id) from order_travel_companion) and status in (100005,100006) group by date; 
