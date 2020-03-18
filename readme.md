# WP-China-Yes

## 介绍

因为WordPress官方的服务器都在国外，所以中国大陆的用户在访问由WordPress官方提供的服务（插件、主题商城，WP程序版本更新等）时总是很缓慢。

近期又因为被攻击的原因，WordPress的CDN提供商屏蔽了中国大陆的流量，导致大陆用户访问插件主题商城等服务时报429错误。

为解决上述问题，我在大陆境内架设了基于反向代理的缓存加速节点，用以加快WordPress官方服务在中国大陆的访问速度，并规避429报错问题。

此加速节点是直接为你的站点与WordPress总部服务器的一切通信做加速，加速范围包括但不限于：插件、主题商城的资源下载、作品图片、作者头像、主题预览等……

为使更多的使用WordPress的同学能够用上大陆加速节点，我开发了WP-China-Yes插件，以求帮助大家方便简洁的替换官方服务链接为大陆节点。

这个是一个公益项目，我始终都不会以任何借口对插件、加速节点的使用权等进行收费。

## 使用方法

Master上的是开发中的版本，可能存在BUG。推荐安装最新稳定版：[点击下载](https://github.com/sunxiyuan/wp-china-yes/releases/download/v2.0.1/wp-china-yes.zip)

下载并安装插件后直接启用即可，该插件会自动接管所有WP访问境外服务器的流量。

### 特别说明！
无论你使用何种方法安装插件，请一定保证插件的目录名是`wp-china-yes`，否则插件将无法正常工作。

## 开发计划

### v2.2.0
 - [ ] 贡献者列表
 - [ ] 捐助者列表
 
### v2.1.0
 这个版本主要想将用户平均分布在各个优质源上，同时帮助社区源赞助企业获得更多的品牌曝光机会

 - [ ] 节点使用数量统计
 - [ ] 社区源测速排序，并根据节点使用人数智能推荐负载较小的节点
 - [ ] 定时提醒排序、启动时提醒排序
 - [ ] 社区源宕机切换提示
 
### v2.0.2
 - [ ] 修复API接口编写不规范的问题
 
### v2.0.1
 - [x] 修复插件无法运行在使用子目录部署的WordPress上的问题
 
### v2.0.0
 - [x] 社区源列表
 - [x] 自定义源支持
 
### v1.0.0
 - [x] 基本功能