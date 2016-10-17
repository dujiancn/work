select distinct(p.product_id) from product as p
left join provider as pv on p.provider_id = pv.provider_id
where p.product_entity_type=0 
and p.is_tff=1 
and p.active=1
and p.region_id!=6
and p.product_type_id!=10023
and p.product_type_id!=10026
and pv.currency_id in (1,2,3,7,11,12)
and p.product_id not in (select product_id from product_category where category_id=1143);
