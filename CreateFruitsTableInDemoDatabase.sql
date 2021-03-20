drop database if exists `demo_database`;
create database if not exists `demo_database`;
show databases;
use `demo_database`;
drop table if exists `fruits`;
create table if not exists `fruits` (
	`index`		int				primary key auto_increment,
    `name`		varchar(40)		not null unique,
    `weight`	decimal(5,2)	not null,
    `price`		decimal(5,2)	not null
);
insert into `fruits` (`name`, `weight`, `price`) values ('apple', 200.03, 40.05) , ('banana', 400.03, 80.05), ('orange', 193.57, 45.75), ('grape', 393.43, 100.28);
select * from `fruits`;
