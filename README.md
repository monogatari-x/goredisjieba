### version 0.0.5

### config.xml
```
<?xml version="1.0" encoding="UTF-8" ?>
<config>
    <address>0.0.0.0:6380</address>
    <db>0</db>
    <dict>/data/dict</dict>
</config>
```
* 其中db为dict定义目录下的一个子目录，所有字典存在该子目录下,当使用select db时可切换分词字典，使用refresh db时可以重新加载分词字典

### usage

1. 先安装配置好go环境

```
export GO111MODULE=on //开启go Module
export GOPROXY=https://goproxy.cn //设置go proxy
```

2. 然后安装相关扩展,如果xqb没在GOROOT下，拷贝过去即可/或者更改package的路径
```
go mod init goRedisJieba
go mod tidy
```

3. 然后go build编译redis

```
make linux //即GOOS=linux GOARCH=amd64 go build -o ./bin/goRedisJieba_linux ./src
./bin/goRedisJieba_linux --config=config.xml //运行该实例,可nohup &放到后台
```

### download
* https://github.com/jonnywang/goredisjieba/releases

### command
```
redis-cli --raw
127.0.0.1:6379> tag 我来到北京清华大学
我/r
来到/v
北京/ns
清华大学/nt
127.0.0.1:6379> cut 我来到北京清华大学 0
我
来到
北京
清华大学
127.0.0.1:6379> cut 我来到北京清华大学 1
我
来到
北京
清华大学
127.0.0.1:6379> cutAll 我来到北京清华大学
我
来到
北京
清华
清华大学
华大
大学
127.0.0.1:6379> cutforsearch 我来到北京清华大学 1
我
来到
北京
清华
华大
大学
清华大学
127.0.0.1:6379> cutforsearch 我来到北京清华大学 0
我
来到
北京
清华
华大
大学
清华大学
127.0.0.1:6379> extract 我来到北京清华大学  20
清华大学
来到
北京
127.0.0.1:6479> cutforsearch 天生愚钝所以努力学习块区链技术 0
天生
愚钝
所以
努力
力学
学习
努力学习
块
区
链
技术
127.0.0.1:6479> addword 块区
OK
127.0.0.1:6479> cutforsearch 天生愚钝所以努力学习块区链技术 0
天生
愚钝
所以
努力
力学
学习
努力学习
块区
链
技术
127.0.0.1:6479>
```
* “区块链”已经存在于字典，为了模拟addword命令动态添加词语， 我们这里使用“块区”
* 已实现command包含了redis的select,ping,version以及gojieba分词的cutall,cut,cutforsearch,tag,addword
* refresh db 刷新db目录分词字典

### php
```
<?php

$redis = new Redis();
$redis->connect('127.0.0.1', 6380, 10); //端口需要与config.xml配置保持一致
$redis->select(0);

$result = $redis->rawCommand('cutforsearch', '我来到北京清华大学', 1);
print_r($result);
```
