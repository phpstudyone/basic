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

爬虫采集数据，命令行下执行
**window系统**
``` sh
 C:/wamp/www/basic> yii hello/pachong
```
**linux系统**
``` sh
root@root:/var/www/html/basic$ ./yii hello/pachon
```

**获取视频的下载地址**
在imooc网站加载的js中，有一个 [video.js](http://www.imooc.com/static/page/course/video.js?v=2016010602358:formatted) 文件。该文件中有代码:
``` javascript
    function c(a) {
        $.getJSON("/course/ajaxmediainfo/?mid=" + pageInfo.mid + "&mode=flash", function(c) {
            P = c.data.result,
            a && a()
        })
    }
```

imooc的视频下载地址，可以请求[获取视频地址](http://www.imooc.com/course/ajaxmediainfo/?mid=372&mode=falsh)来获取，得到的json数据如下：
``` json
{
    "result": 0, 
    "data": {
        "result": {
            "mid": 372, 
            "mpath": [
                "http://v2.mukewang.com/736e6338-39b7-468c-9c92-e3ca06187de3/L.mp4?auth_key=1483869714-0-0-4002056eeca4a50189b5787117cd86bc", 
                "http://v2.mukewang.com/736e6338-39b7-468c-9c92-e3ca06187de3/M.mp4?auth_key=1483869714-0-0-daed2cdfa62e662b391b4c2730bcba79", 
                "http://v2.mukewang.com/736e6338-39b7-468c-9c92-e3ca06187de3/H.mp4?auth_key=1483869714-0-0-afd07ee1f892cc5109c45a3b628779ab"
            ], 
            "cpid": "147", 
            "name": "商品的全选功能", 
            "time": "239", 
            //以下字段在以前老视频为空
            "practise": [
                {
                    "id": 1, 
                    "type": "1", 
                    "timepoint": 445, 
                    "status": "1", 
                    "eid": "78", 
                    "skip": 0, 
                    "content": {
                        "name": "1. 假设有一个勾选框元素el,如何在js中将其修改为选中状态？", 
                        "options": [
                            {
                                "id": "303", 
                                "name": "el.onclick(); ", 
                                "tip": "", 
                                "is_answer": "0"
                            }, 
                            {
                                "id": "304", 
                                "name": "el.onchange();", 
                                "tip": "", 
                                "is_answer": "0"
                            }, 
                            {
                                "id": "305", 
                                "name": "el.checked = ture;", 
                                "tip": "", 
                                "is_answer": "1"
                            }, 
                            {
                                "id": "306", 
                                "name": "el.checked();", 
                                "tip": "", 
                                "is_answer": "0"
                            }
                        ]
                    }
                }, 
                {
                    "id": 2, 
                    "type": "1", 
                    "timepoint": 446, 
                    "status": "1", 
                    "eid": "80", 
                    "skip": 0, 
                    "content": {
                        "name": "2. 取得tr元素下面所有td标签，下面选项错误的是？", 
                        "options": [
                            {
                                "id": "311", 
                                "name": "tr.cells", 
                                "tip": "", 
                                "is_answer": "0"
                            }, 
                            {
                                "id": "312", 
                                "name": "tr.children  ", 
                                "tip": "", 
                                "is_answer": "0"
                            }, 
                            {
                                "id": "313", 
                                "name": "tr.getElementsByTagName(‘td’);", 
                                "tip": "", 
                                "is_answer": "0"
                            }, 
                            {
                                "id": "314", 
                                "name": "tr.rows", 
                                "tip": "", 
                                "is_answer": "1"
                            }
                        ]
                    }
                }
            ]
        }
    }, 
    "msg": "成功"
}
```
我们只需要解析出mpath数据就行，每条视屏有三条url，分别为 “普清”，“高清”，“超清”链接