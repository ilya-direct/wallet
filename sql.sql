select rec.time,rec.sum,i.name from record rec left join item i on i.id=rec.itemid;
delete  from  record where id>=1;

select * from  record where  signid=2
						and sum=108 and itemid=26 and time='2014.11.01';
show fields from record;

select * from record limit 118,14;
select * from record ;


insert into record(signid,sum,time,itemid) values(1,546,'2014:12:12',24);
insert into record(signid,sum,time,itemid) values(1,546,'2014.12.5',24);
insert into record(signid,sum,time,itemid) values(1,546,'2:23:2014',24);

select * from record where itemid=23;
select * from item where name='Корректировка';
show fields from item;
describe item;
select * from item where id=19;
delete from record;
delete from item;


ALTER TABLE item auto_increment=1;
ALTER TABLE record auto_increment=1;
ALTER TABLE transaction_category auto_increment=18;
select * from transaction_category;
delete  from record;
delete  from item;
delete from transaction_category;

select * from record; -- where itemid=19;
select * from item;
select * from transaction_category;
select r.*,i.name from record r inner join item i on i.id=r.itemid where i.name='Корректировка';

CREATE TABLE transaction_category (
	`id` int PRIMARY KEY NOT NULL auto_increment,
    `name` varchar(15) NOT NULL unique
)engine='InnoDB';

desc transaction_category;
desc record;
alter table record add column tcategory int ;

select * from record;

select * from  transaction_category;
select `id` from `transaction_category` where  `name`='p_mompm';

select * from record;
select * from  item;


select * from  transaction_category where  name='m_spend_multiple';
select * from  transaction_category where  name='m_spend_multiple';
insert into transaction_category (name) value('m_spend_multiple');

select * from transaction_category;
desc transaction_category;

-- alter table transaction_category modify name varchar(30);


delete from record;
delete from item;
desc record;
select * from record;
alter table record modify tcategory int not null;


select distinct id  from transaction_category tc join record r on r. ;
select distinct tcategory from record r join item i on r.itemid=i.id 
		join transaction_category tc on tc.id=r.tcategory
where year(r.time)='2014' and month(r.time)='01' and (tc.name like "p_%" or  tc.name like "m_%");
select tc.name as 'tcategory',group_concat(i.name  SEPARATOR '|') as 'desc', group_concat(r.sum SEPARATOR '|') as 'sum'
	from record r join item i on r.itemid=i.id 
     join transaction_category tc on tc.id=r.tcategory 
    where year(r.time)='2014' and month(r.time)='01'and day(r.time)='23' group by tcategory;
select * from item;
select * from transaction_category;
select * from record;
alter table `transaction_category` add column `value` varchar(30);
-- alter table record drop column category;


select sum(r.sum) from record r
join transaction_category tc on tc.id=r.tcategory 
    where year(r.time)='2014' and month(r.time)='01' and tc.name='p_mom_multiple';

select * from record r
join transaction_category tc on tc.id=r.tcategory 
    where year(r.time)=2014 and month(r.time)=1 and day(r.time)='23';
    
select distinct tc.name
	from record r join item i on r.itemid=i.id
		join transaction_category tc on tc.id=r.tcategory
	where year(r.time)='2014' and
			month(r.time)='01' and
			(tc.name like 'p_%' or  tc.name like 'm_%');
            
select sum(r.sum)
				from record r join transaction_category tc on tc.id=r.tcategory
    			where year(r.time)=2014 and month(r.time)=1 and 
    				day(r.time)=3 and tc.name='p_mompm';
select group_concat(i.name  SEPARATOR '|') as 'desc',group_concat(r.sum SEPARATOR '|') as 'sum'
			from record r join item i on r.itemid=i.id
    			join transaction_category tc on tc.id=r.tcategory
    		where year(r.time)=2014 and month(r.time)=1 and day(r.time)=23 and tc.name='p_mom_multiple';
            
select name from item1  where id<2 limit 5;

desc transaction_category;
-- alter table transaction_category modify column `name` varchar(30) not null;
-- alter table transaction_category drop index name;
select * from transaction_category;

select column_name from (SHOW KEYS FROM transaction_category  WHERE Key_name = 'PRIMARY');
SHOW KEYS FROM transaction_category; -- WHERE Key_name = 'PRIMARY';
SELECT column_name
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'wallet'
   AND TABLE_NAME = 'transaction_category'
   AND COLUMN_KEY = 'PRI';
select * from transaction_category;
desc transaction_category;
update transaction_category set `sort`=1, `name`='p_mompm' where id=1; 

delete from transaction_category;
select sum(sum)/12 from record where tcategory=10;
select * from item;
delete  from record;
-- alter table transaction_category add column deleted tinyint not null default 0;

select distinct tc.name
	from record r join item i on r.itemid=i.id
		join transaction_category tc on tc.id=r.tcategory
	where year(r.time)=2014 and
			month(r.time)=04 and
			(tc.name like 'p_%' or  tc.name like 'm_%') order by tc.sort;
select `value` from `transaction_category` where  `name`='date' and `deleted`=0;

select * from record where sum<0;

select   * from record r
	join transaction_category tc on r.tcategory=tc.id
    where tc.name like 'm_%';
update  record r
	join transaction_category tc on r.tcategory=tc.id
    set r.sum=-abs(r.sum)
    where tc.name like 'm_%';
update  record r
	join transaction_category tc on r.tcategory=tc.id
    set r.sum=abs(r.sum)
    where tc.name like 'p_%';
select * from  transaction_category 
    where name like 'm_%';   
    
select * from transaction_category;
select * from item where id=45;
alter table transaction_category add column sign tinyint(1) default 0;

select * from record;
select * from card;
drop table card;
alter table record drop column cardid;


SELECT *
FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = 'wallet'
   AND TABLE_NAME = 'record';
drop table card;
select * from record;
alter table cardcheck drop foreign key fk_cardid_cardcheck;
   
show tables from INFORMATION_SCHEMA;
alter table card   auto_increment=1;
alter table user drop PRIMARY KEY;
desc user;
select * from record;
alter table user change column `login_1` `login_11` varchar(15);
alter table user change column `id` `idid` int;
show tables;

alter table record drop column userid;
alter table record drop column cardid;
alter table record drop column signid;

alter table record add constraint  fk_tcategory_record  foreign key (tcategory) references transaction_category(id); 
alter table record modify column `time` date;
alter table record change column `time` `date` date;

create table `balance_check` (
	`id` int PRIMARY KEY not null auto_increment,
    `date` date,
	`consider` int not null,
	`real` int not null,
	`diff` int not null
) engine=InnoDB collate='utf8_general_ci';
desc balance_check;

desc record;
select * from transaction_category;
select * from balance_check;
delete  from balance_check;
insert into balance_check (`date`,consider,`real`,diff) values('2014.01.31',18728,12605,-6123);
alter table balance_check change `real` `realmoney` int;

select r.sum from record r left join transaction_category tc on tc.id=r.tcategory 
	where tc.name='correcting' and year(r.`date`)=2014 and month(r.date)=1;
select sum(sum) from record where date>"2013-12-31" and date<="2013-12-31";
alter table balance_check modify column `date` date unique;
desc balance_check;
select * from record;
delete from record;
create table dbx_download (
	id int not null PRIMARY KEY AUTO_INCREMENT,
    fname varchar(6) unique,
    downloadtime datetime,
    in_db tinyint DEFAULT 0
);
desc dbx_download;
alter table dbx_download modify column fname varchar(7) not null;
select * from dbx_download;

show tables;
drop table record;
drop database test;
create database test;
use test;
show tables;
show grants;

select * from dbx_finance;
delete from dbx_finance;
select * from dbx_finance where  `year`=2014 and `month`=1;
drop table dbx_finance;
select * from category;
desc dbx_download;
create table `dbx_finance` (
	`id` int primary key auto_increment,
	`month` tinyint(2) zerofill not null,
	`year` year not null,
    `file_name` varchar(20) default null,
	`download_time` datetime default '0000-00-00 00:00:00',
    `exists` tinyint not null default 0,
	`csv_converted`  tinyint not null default 0,
	`in_db` tinyint not null default 0,
	UNIQUE KEY (`month`, `year`)
);
delete from record;

select * from record ;
select * from item where id in (417,419);
select * from transaction_category;
alter table transaction_category drop column sign;
alter table transaction_category change column sign2 sign char(1);
update transaction_category set sign2='-' where sign=2;
update  dbx_finance set `exists`=0;
select * from item;
select * from balance_check;
alter table record auto_increment=1;
select * from dbx_finance;
select * from transaction_category where id in (2,3,4,5,6,7,8,10,11,12,13,14);
select distinct tcategory from record;
delete from record where tcategory=1;

select * from record;
select * from  cardcheck;
desc cardcheck;
drop table category;
SELECT *
FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = 'wallet'
   AND TABLE_NAME = 'item';
alter table item drop foreign key fk_categoryid_item;
alter table item drop column categoryid;
delete from record;
delete from item;
delete from balance_check;
delete from dbx_finance;
delete from transaction_category;

select * from record;
select * from transaction_category;
	select r.* from record r left join transaction_category tc on tc.id=r.tcategory
			where tc.name='correcting';
            
select * from record where itemid=877; -- id=2701
select * from record where date='2015-01-25'; -- id=2701
select * from item where name like 'подарок мам%';
select * from dbx_finance;
select * from transaction_category;
select * from record;
select * from balance_check where year(date)=2013;


drop database wall;

select * from record r left join item i on i.id=r.itemid where year(r.date)='2015' and month(r.date)='2' and day(r.date)=3 order by r.date;
delete from record where id=1511;
delete from record where id=1431;

delete from record where year(date)='2015' and month(date)='2' and sum is null;

drop database walletl;
create database wallet;

delete from record where sum is null;
select distinct tcategory from record where sum is null;

select * from transaction_category;


select sum(r.sum) from record r left join transaction_category tc on tc.id=r.tcategory 
	where year(r.date)=2014 and tc.name in ('p_mompm','p_mom_multiple'); 
select sum(sum)/14 from record where sum>0;
select count(*) from record where year(date)=2014;


select count(*) from record;

select * from balance_check 
	where year(date)=2015 
		and month(date)=2;
select * from record 
	where year(date)=2015 
		and month(date)=2;
select r.sum from record r left join transaction_category tc on tc.id=r.tcategory
			where tc.name='correcting' and year(r.`date`)=2015 and month(r.date)=2;
select * from record;

alter table item change `correct_name_id` `correct_item_name_id` int default null;

select * from item ;

create table correct_item_name (
	`id` int not null primary key auto_increment,
    `name` varchar(30)
);
desc correct_item_name;
alter table correct_item_name add constraint `ux_cor_name` unique (`name`);
select * from correct_item_name;
select * from item;
select * from item where correct_item_name_id is null order by name limit 1;
desc correct_item_name;
select * from correct_item_name where  `name`='2 тетрадки';
ALTER TABLE `correct_item_name` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
-- ALTER TABLE `correct_item_name` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;