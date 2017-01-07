说明
====
**本爬虫程序爬取imooc，获取imooc网站的视频链接，定时任务下载视频**
爬虫需要建立的数据表
1.采集数据表  **collect_data**
  该表用于存储采集的数据
``` sql
CREATE TABLE `collect_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `video_url` varchar(625) NOT NULL,
  `is_download` tinyint(2) unsigned NOT NULL,
  `video_path` varchar(625) NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  `download_begin_time` int(10) unsigned NOT NULL,
  `download_end_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='采集数据表';
```

2.采集的url表 **collect_url**
  该表存储采集的url
``` sql
CREATE TABLE `collect_url` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `url` varchar(625) NOT NULL COMMENT '采集的url',
  `is_collect` tinyint(2) unsigned NOT NULL COMMENT '是否已经采集',
  `create_time` int(10) unsigned NOT NULL COMMENT '生成时间',
  `collect_time` int(10) unsigned NOT NULL COMMENT '采集时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='采集的url';
```

