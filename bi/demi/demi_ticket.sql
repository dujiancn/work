SELECT
op.order_id,
op.product_departure_date,
op.product_id,
op.product_name,
op.final_price_cost,
op.final_price
FROM
order_product AS op LEFT JOIN `order` as o ON o.order_id = op.order_id
WHERE
(op.product_id IN (
        SELECT
        pti.product_id
        FROM
        product_ticket_info AS pti
        LEFT JOIN `ticket_info` ti ON ti.`ticket_info_id` = pti.`ticket_info_id`
        LEFT JOIN `ticket_info_description` AS tid ON tid.ticket_info_id = pti.ticket_info_id
        AND tid.language_id = 3
        WHERE
        ti.`sub_type` = 'is_ticket'
        AND ti.active = 1
    ) or op.product_id in (71706)) AND o.created BETWEEN '2015-01-01' AND '2016-08-31';
