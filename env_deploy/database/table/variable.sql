CREATE TABLE `variable` (
    `variable_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `variable_name` varchar(64) NOT NULL,
    `value` varchar(256) NOT NULL,
    `host_name` varchar(64) NOT NULL,
    `created_by` varchar(64) NOT NULL,
    `updated_by` varchar(64) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT NOW(),
    `updated_at` timestamp NOT NULL DEFAULT NOW(),
    PRIMARY KEY (`variable_id`),
    FOREIGN KEY (`variable_name`) REFERENCES `variable_description` (`variable_name`),
    FOREIGN KEY (`host_name`) REFERENCES `host` (`name`)
)ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
