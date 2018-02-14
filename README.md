# playPony

## QQ空间宠物自动化脚本

### 版本说明

根据自己的需求选择

v1.php：每次运行，自动偷糖果、喂食（包括好友）、捡大便（包括好友）

v2.php：在v1的基础上修正（仍然自动偷糖果）+只喂食暮光闪闪（专门用来刷谐律精华，集M6）

v3.php：在v1的基础上修正（仍然自动偷糖果）+只操作自己的全部宠物（治病+喂食+生成大便+捡大便）

### 安装

clone本项目：

	cd /path/to/
	git clone https://github.com/juzeon/playPony.git
	cd playPony/

新建`config.php`文件，填入：

```
<?php
define('QQNUM', 'QQ号');
define('SKEY', 'QQ空间skey');
define('PSKEY', 'QQ空间p_skey');
define('FOOD_LIMIT',0);//每次运行喂食次数限制，0为不喂食。v3无此项
```

设置文件权限：

	chmod -R +x /path/to/playPony/

将`v1.php`或`v2.php`加入cron，每五分钟运行一次，输出日志到log.txt：

	*/5 * * * * php /path/to/playPony/v2.php >> /path/to/playPony/log.txt 2>&1
	

## LICENSE

GPLv3