CREATE TABLE `variable` (
    `variable_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `env_variable_dict_id` int(10) unsigned NOT NULL,
    `value` varchar(256) NOT NULL,
    `platform` ENUM ('dev','qa','production') NOT NULL DEFAULT 'dev',
    `created_by` varchar(64) NOT NULL,
    `updated_by` varchar(64) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT NOW(),
    `updated_at` timestamp NOT NULL DEFAULT NOW(),
    PRIMARY KEY (`variable_id`),
    UNIQUE INDEX `platform_key`(`platform`,`env_variable_dict_id`),
    FOREIGN KEY (`env_variable_dict_id`) REFERENCES `env_variable_dict` (`env_variable_dict_id`)
)ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
