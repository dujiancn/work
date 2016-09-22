--小团零售价
SELECT pto.order_code, pto.order_id, pto.go_back_date, pot.retail as `retail($)` from pt_order as pto
join pt_order_total as pot on pto.pt_order_id=pot.pt_order_id
where pto.go_back_date>="2016-01-01" and pto.go_back_date<"2016-09-01";

--小团captured_amount
SELECT pto.order_code, pto.order_id, pto.go_back_date, osi.order_value as `captured_amount($)` from pt_order as pto
join order_settlement_information as osi on pto.order_id=osi.order_id
where pto.go_back_date>="2016-01-01" and pto.go_back_date<"2016-09-01";

--调价
SELECT pto.order_code, pto.order_id, poa.amount as `retail_adjust($)` FROM pt_order_adjustment as poa
join pt_order as pto on(pto.pt_order_id = poa.pt_order_id)
where poa.type = 'retail' and pto.go_back_date >= '2016-01-01' and pto.go_back_date < '2016-09-01';
