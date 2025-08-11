create database AnimalesLP;
use AnimalesLP;



create table animales (
	idAnimal int primary key AUTO_INCREMENT,
	nombre varchar(250) not null,
    tipo varchar(250) not null,
    ecosistema varchar(250) not null,
    ubicacion varchar(250) not null,
    foto varchar(250) not null,
    descripcion varchar (500)
);

