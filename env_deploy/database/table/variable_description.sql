CREATE TABLE `variable_description` (
    `variable_description_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `variable_name` varchar(64) NOT NULL,
    `note` varchar(512) NOT NULL,
    `created_by` varchar(64) NOT NULL,
    `updated_by` varchar(64) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT NOW(),
    `updated_at` timestamp NOT NULL DEFAULT NOW(),
    PRIMARY KEY (`variable_description_id`),
    UNIQUE INDEX `variable_name`(`variable_name`)
)ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
