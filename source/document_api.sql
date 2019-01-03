CREATE TABLE `document_api` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL COMMENT '接口路由',
  `response` text NOT NULL COMMENT '返回示例',
  `desc` text NOT NULL COMMENT '接口描述',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

