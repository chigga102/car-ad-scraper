# Create tables for application

drop table if exists `ad`;
drop table if exists `fuel`;
drop table if exists `location`;
drop table if exists `transmission`;
drop table if exists `bodyType`;
drop table if exists `model`;
drop table if exists `make`;


create table `make` (
  idMake tinyint unsigned not null auto_increment,
  `name` varchar(50) not null,
  primary key (idMake)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;


create table `model` (
  idModel smallint unsigned not null auto_increment,
  idMake tinyint unsigned not null,
  `name` varchar(50) not null,
  primary key (idModel),
  foreign key (idMake) references make(idMake) on update cascade on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


create table `bodyType` (
  idBodyType tinyint unsigned not null auto_increment,
  `name` varchar(50) not null,
  primary key (idBodyType)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


create table `transmission` (
  idTransmission tinyint unsigned not null auto_increment,
  `name` varchar(50) not null,
  primary key (idTransmission)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


create table `location` (
  idLocation tinyint unsigned not null auto_increment,
  `name` varchar(50) not null,
  primary key (idLocation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


create table `fuel` (
  idFuel tinyint unsigned not null auto_increment,
  `name` varchar(10) not null,
  primary key (idFuel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


create table `ad` (
  idAd int unsigned not null auto_increment,
  idModel smallint unsigned not null,
  idBodyType tinyint unsigned ,
  doors tinyint unsigned ,

  `year` smallint unsigned not null,
  idFuel tinyint unsigned not null,
  engineSize decimal(2,1) unsigned not null,
  mileage mediumint unsigned not null,
  price mediumint unsigned not null,

  idTransmission tinyint unsigned ,
  idLocation tinyint unsigned,
  owners tinyint unsigned,

  `source` varchar(20) not null, 
  idSource varchar(25) not null,

  `captured` timestamp DEFAULT CURRENT_TIMESTAMP,

  primary key (idAd),
  foreign key (idModel) references model(idModel) on update cascade on delete cascade,
  foreign key (idBodyType) references bodyType(idBodyType) on update cascade on delete cascade,
  foreign key (idFuel) references fuel(idFuel) on update cascade on delete cascade,
  foreign key (idTransmission) references transmission(idTransmission) on update cascade on delete cascade,
  foreign key (idLocation) references location(idLocation) on update cascade on delete cascade,
  unique key (idSource, `source`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;