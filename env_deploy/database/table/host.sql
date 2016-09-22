CREATE TABLE `host` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(64) NOT NULL,
    `ip` varchar(16) NOT NULL,
    `parent_name` varchar(64),
    `layer` tinyint(1) unsigned NOT NULL DEFAULT '0' comment '顶级host为0，其他依次继承',
    `platform` ENUM ('dev','qa','production') NOT NULL DEFAULT 'dev',
    `created_by` varchar(64) NOT NULL,
    `updated_by` varchar(64) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT NOW(),
    `updated_at` timestamp NOT NULL DEFAULT NOW(),
    PRIMARY KEY (`id`),
    UNIQUE INDEX `name`(`name`)
)ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
