-- mysql 存储 请使用该Sql生成表
CREATE TABLE my_table (
     `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
     `q_sign` char(32) CHARACTER SET latin1 NOT NULL,
     `q_name` varchar(100) CHARACTER SET latin1 NOT NULL,
     `q_args` text CHARACTER SET latin1 NOT NULL COMMENT '//{}',
     `q_exec_time` int(11) unsigned NOT NULL,
     PRIMARY KEY (`id`),
     KEY `q_exec_time` (`q_exec_time`),
     KEY `q_sign` (`q_sign`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=135 DEFAULT CHARSET=utf8;

-- sqlite 存储 请使用该Sql生成表 或者直接使用 examile 中的sqlite.db
create table async_queue
(
    id integer
        constraint table_pk
            primary key autoincrement,
    q_sign varchar(32),
    q_name varchar(100),
    q_args text,
    q_exec_time int
);
