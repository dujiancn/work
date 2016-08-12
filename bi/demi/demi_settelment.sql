SELECT
    o.order_id,
    osi.settlement_date,
    o.bu,
    o.customer_payment_region,
    osi.order_value,
    osi.order_payment_method,
    o.phonebooking,
    osi.original_id,
    osi.reference_comments
FROM
    order_settlement_information osi
LEFT JOIN `order` o ON o.order_id = osi.order_id
WHERE osi.settlement_date >= '2015-01-01 00:00:00' AND osi.settlement_date<'2016-08-02 00:00:00';
