select region_id from region where country_id in (select country_id from country where continent_id in (2,4,9));
