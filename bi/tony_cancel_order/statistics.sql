#get total num
select date_format(created,'%Y-%m') as dates,count(*) as total_num
from `order` 
where created>'2015-01-01' group by dates;

#get cancel order num
select date_format(o.created,'%Y-%m') as dates, count(*) as cancel_num 
from `order` as o 
where o.order_id in 
(select distinct(osh.order_id) from order_status_history as osh where osh.order_id>=273183 and osh.order_status_id IN (100069, 100097, 6) ) 
group by dates;

#get cusomer request cancel order num
select date_format(o.created,'%Y-%m') as dates, count(*) as cancel_num 
from `order` as o 
where o.order_id in (select distinct(osh.order_id) from order_status_history as osh where osh.order_id>=273183 and osh.order_status_id IN (100069, 100097, 6) ) 
and o.cancellation_history LIKE "%Request by customer%"
group by dates;

#get reservation not available cancel order num
select date_format(o.created,'%Y-%m') as dates, count(*) as cancel_num 
from `order` as o 
where o.order_id in (select distinct(osh.order_id) from order_status_history as osh where osh.order_id>=273183 and osh.order_status_id IN (100069, 100097, 6) ) 
and o.cancellation_history LIKE "%reservation not available%"
group by dates;

#get Non Payment cancel order num
select date_format(o.created,'%Y-%m') as dates, count(*) as cancel_num 
from `order` as o 
where o.order_id in (select distinct(osh.order_id) from order_status_history as osh where osh.order_id>=273183 and osh.order_status_id IN (100069, 100097, 6) ) 
and o.cancellation_history LIKE "%Non Payment%"
group by dates;

#get  Place another order cancel order num
select date_format(o.created,'%Y-%m') as dates, count(*) as cancel_num 
from `order` as o 
where o.order_id in (select distinct(osh.order_id) from order_status_history as osh where osh.order_id>=273183 and osh.order_status_id IN (100069, 100097, 6) ) 
and o.cancellation_history LIKE "%Place another order%"
group by dates;

#get  other cancel order num
select date_format(o.created,'%Y-%m') as dates, count(*) as cancel_num 
from `order` as o 
where o.order_id in (select distinct(osh.order_id) from order_status_history as osh where osh.order_id>=273183 and osh.order_status_id IN (100069, 100097, 6) ) 
and o.cancellation_history NOT LIKE "%Request by customer%"
and o.cancellation_history NOT LIKE "%reservation not available%"
and o.cancellation_history NOT LIKE "%Non Payment%"
and o.cancellation_history NOT LIKE "%Place another order%"
group by dates;
