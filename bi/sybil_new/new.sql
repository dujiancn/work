select op.product_id, sum(op.final_price) as total_retail, sum(ope.guest_number) as people_num, count(distinct(op.order_id)) as order_num 
from order_product as op 
left join order_product_eticket as ope on 
op.order_product_id=ope.order_product_id 
where op.product_id in('100678','106083','107082','107487','108726','110121','110871') 
and op.order_item_purchase_date>='2016-10-01' 
and op.ordder_item_purchase_date<'2016-11-01' 
group by op.product_id;
