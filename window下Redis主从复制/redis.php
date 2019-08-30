<?php

/*
Redis主从复制简单介绍
为了使得集群在一部分节点下线或者无法与集群的大多数节点进行通讯的情况下， 仍然可以正常运作， Redis 集群对节点使用了主从复制功能： 集群中的每个节点都有 1 个至 N 个复制品（replica）， 其中一个复制品为主节点（master）， 而其余的 N-1 个复制品为从节点（slave）。[ 摘自 Redis 集群中的主从复制 ]

那么上面是主从复制呢，简单的来说就是一个主节点master可以拥有一个甚至多个从节点的slave，而一个slave又可以拥有多个slave，如此下去，形成了强大的多级服务器集群架构。
Master-Slave主从

其中主节点以写为主(可写也可以读)，从节点只能读不可写入！【读写分离场景】
其中主节点写入的数据会同步（不是准实时的）到salve上，这样如果主节点出现故障，数据丢失，则可以通过salve进行恢复。【容灾恢复场景，注：因为数据不是实时同步的，可能会存在从salve恢复数据后有数据丢失问题】

综上：下面是关于redis主从复制的一些特点：
1.一个master可以有多个slave
2.除了多个slave连到相同的master外，slave也可以连接其他slave形成图状结构
3.主从复制不会阻塞master。也就是说当一个或多个slave与master进行初次同步数据时，master可以继续处理client发来的请求。相反slave在初次同步数据时则会阻塞不能处理client的请求。
4.主从复制可以用来提高系统的可伸缩性,我们可以用多个slave 专门用于client的读请求，比如sort操作可以使用slave来处理。也可以用来做简单的数据冗余
5.可以在master禁用数据持久化，只需要注释掉master 配置文件中的所有save配置，然后只在slave上配置数据持久化。
6.可以用于读写分离和容灾恢复。

步骤：
下载window版本
分别复制三分命名为redis-6379 redis-6380 redis-6381

6379文件夹下redis.windows.conf不做修改



6380文件夹下redis.windows.conf修改：
port 6380
# slaveof <masterip> <masterport>
slaveof 127.0.0.1 6379



6381文件夹下redis.windows.conf修改：
port 6381
# slaveof <masterip> <masterport>
slaveof 127.0.0.1 6379


首先启动mater主节点
进入redis-6379 执行 redis-server.exe redis redis.windows.conf

然后依次启动6380 6381节点
redis-server.exe redis redis.windows.conf

进入主节点客户端 redis-cli.exe -p 6379
测试设置 字符 set test "3";
进入从节点 redis-cli.exe -p 6380
也能get到 get test;

主节点能读写，从节点只能读取不能写入.


*/