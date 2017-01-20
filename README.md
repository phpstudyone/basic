说明
====
## 第一个分支 development ##  
**使用ssh2来pull or pull 文件**

    因为很多公司，线上或者测试环境的代码，不是直接svn下来的，而是靠程序猿使用ftp软件手工的更新代码，
    而且服务器是使用私钥登录，如果代码文件多，还在不同的路径，这个工作是非常繁杂的。所以写了这个小工具，
    只要把要更新的代码的路径复制提交就可以自动更新其他环境代码。（毕竟我大svn可以直接导出更新的代码path）

## 第二个分支datahand ## 
**复制数据库结构和数据的插入sql**

    有时想本地弄个数据库方便测试，但是用工具直接导的话，数据库如果比较大，则会非常耗时，
    目前还没找到一款工具支持每个表导出500条数据-----这个分支只会复制数据库结构和500插入sql。

## 第三个分支 pachong ##  
**自动下载慕课网上的视频**
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

自动下载视频，命令行下执行
**window系统**
``` sh
 C:/wamp/www/basic> yii hello/download
```
**linux系统**
``` sh
root@root:/var/www/html/basic$ ./yii hello/download
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

**特别声明：本人已将相关情况反馈给imooc，他们也联系了开发人员跟进，
后续他们会补上这个问题，那时候应该不能直接下载他们视频**
**未经他人同意直接从他人服务器上下载视频，是不道德的！！**

## 第四个分支 chinese_change_to_pinyin ##  
**汉字转拼音功能**

    一个工具包，将汉字转换为拼音。
    使用 Yii::$app->c2p->getPiny($chineseStr,$flag); 获取返回的拼音结果

``` php
    /**
     * 将中文编码成拼音
     * @param string $chineseStr utf8字符集数据
     * @param string $flag 返回格式 [head:首字母|all:全拼音]
     * @return string
     */
    Yii::$app->c2p->getPiny($chineseStr,$flag);
```

**应用场景：**

+ 某些需要传入拼音做参数的地方 ， 如 搜狗天气api，只能接受拼音。使用这个工具之后，可以输入城市名称查询
+ 搜索需要，如：输入 “棕子” ， 要求查询的商品包括 “粽子”。我们可以写个脚本，
把查询的数据表中的商品名，全部转为拼音。同时把用户输入的关键词也转为拼音，此时可以完成匹配。
+ other...
