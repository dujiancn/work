select po.customer_name,po.customer_phone,po.customer_email,po.customer_number,po.city,pa.name as agency_name,po.order_id,po.departure_date,concat_ws(" ",u.first_name,u.last_name) as creator,po.contact_type,po.tour_type,pot.retail 
from pt_order as po 
left join user as u on u.user_id=po.created_by 
left join pt_agency  as pa on po.pt_agency_id=pa.pt_agency_id 
left join pt_order_total as pot on po.pt_order_id=pot.pt_order_id 
where po.created between "2016-01-01" and "2016-08-25"; 
