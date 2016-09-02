CREATE TABLE `env_variable_dict` (
    `env_variable_dict_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `env_variable_name` varchar(32) NOT NULL,
    `env_variable_note` varchar(128) NOT NULL,
    `env_variable_type` ENUM ('env','variable') NOT NULL,
    `platform` ENUM ('dev','qa','production') NOT NULL DEFAULT 'dev',
    `created_by` varchar(64) NOT NULL,
    `updated_by` varchar(64) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT NOW(),
    `updated_at` timestamp NOT NULL DEFAULT NOW(),
    PRIMARY KEY (`env_variable_dict_id`),
    UNIQUE INDEX `platform_env_variable_name`(`platform`,`env_variable_name`)
)ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
