二进制日志
---

#### 简介
包含数据库的更新记录，以及可能发生了潜在更新的记录（例如一个删除语句没有匹配的行），除非使用了基于行的记录。同时还包含了每条语句的执行时长信息。

#### 目的
1、应用在主从复制，把主库的二进制日志传送到从库完成数据同步。
2、应用在数据恢复。

#### 性能
开启二进制日志会轻微损耗性能，但与它的作用相比不算什么。

#### 保存
1、默认保存位置为data目录,
2、mysql会为二进制日志文件追加数字扩展，形成有序的一系列文件，因此自定义扩展不会生效
3、服务器会在每次启动或刷新日志的时候新建日志文件，也会在日志大小大于max_binlog_size时新建文件，一个事务不能分成两部分存在两个日志文件。
4、mysqld会创建二进制日志索引问及那，记录所有已生成的日志文件。

#### 记录格式
1、row_based logging
2、statement-based logging
3、mixed-base logging

日记记录会在一个语句或事务完成后立刻执行完，并会在任何锁释放前或任何commit前完成。这保证了日志被记录


#### 问题
In earlier MySQL releases, there was a chance of inconsistency between the table content and binary log content if a crash occurred, even with sync_binlog set to 1. For example, if you are using InnoDB tables and the MySQL server processes a COMMIT statement, it writes many prepared transactions to the binary log in sequence, synchronizes the binary log, and then commits the transaction into InnoDB. If the server crashed between those two operations, the transaction would be rolled back by InnoDB at restart but still exist in the binary log. Such an issue was resolved in previous releases by enabling InnoDB support for two-phase commit in XA transactions. In 5.8.0 and higher, the InnoDB support for two-phase commit in XA transactions is always enabled.
InnoDB support for two-phase commit in XA transactions ensures that the binary log and InnoDB data files are synchronized. However, the MySQL server should also be configured to synchronize the binary log and the InnoDB logs to disk before committing the transaction. The InnoDB logs are synchronized by default, and sync_binlog=1 ensures the binary log is synchronized. The effect of implicit InnoDB support for two-phase commit in XA transactions and sync_binlog=1 is that at restart after a crash, after doing a rollback of transactions, the MySQL server scans the latest binary log file to collect transaction xid values and calculate the last valid position in the binary log file. The MySQL server then tells InnoDB to complete any prepared transactions that were successfully written to the to the binary log, and truncates the binary log to the last valid position. This ensures that the binary log reflects the exact data of InnoDB tables, and therefore the slave remains in synchrony with the master because it does not receive a statement which has been rolled back.
If the MySQL server discovers at crash recovery that the binary log is shorter than it should have been, it lacks at least one successfully committed InnoDB transaction. This should not happen if sync_binlog=1 and the disk/file system do an actual sync when they are requested to (some do not), so the server prints an error message The binary log file_name is shorter than its expected size. In this case, this binary log is not correct and replication should be restarted from a fresh snapshot of the master's data