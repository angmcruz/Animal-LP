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

ALTER TABLE animales
  ADD COLUMN lat DECIMAL(10,8) NULL,
  ADD COLUMN lng DECIMAL(11,8) NULL;

INSERT INTO animales (nombre, tipo, ecosistema, ubicacion, foto, descripcion, lat, lng)
VALUES
('Cóndor Andino', 'ave', 'sierra', 'Parque Nacional Cotopaxi',
 'condor.jpg',
 'Ave emblemática de los Andes, símbolo nacional del Ecuador. Habita en los páramos y montañas de la Sierra.',
 -0.680000, -78.450000);
INSERT INTO animales (nombre, tipo, ecosistema, ubicacion, foto, descripcion, lat, lng)
VALUES
('Iguana', 'reptil', 'playa', 'Guayaquil, Malecón',
 'iguana.jpg',
 'Iguana verde común en áreas urbanas y playas del litoral ecuatoriano',
 -2.196160, -79.886207);