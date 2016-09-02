CREATE TABLE `env` (
  `env_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `env_variable_dict_id` int(10) unsigned NOT NULL,
  `domain_name` varchar(128) NOT NULL,
  `ip` varchar(16) NOT NULL,
  `host` varchar(32) NOT NULL,
  `platform` enum('dev','qa','production') NOT NULL DEFAULT 'dev',
  `created_by` varchar(64) NOT NULL,
  `updated_by` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`env_id`),
  UNIQUE KEY `platform_domain_name` (`platform`,`domain_name`),
  FOREIGN KEY (`env_variable_dict_id`) REFERENCES `env_variable_dict` (`env_variable_dict_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

