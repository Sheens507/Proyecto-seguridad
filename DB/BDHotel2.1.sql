-- Unificación de Scripts SQL
CREATE DATABASE IF NOT EXISTS BDHotel1;
USE BDHotel1;

-- Creación de tablas con especificaciones unificadas
CREATE TABLE IF NOT EXISTS Cliente (
    ID_Cliente INT PRIMARY KEY AUTO_INCREMENT,
    Nombre_cli VARCHAR(50) NOT NULL,
    Apellido_cli VARCHAR(50) NOT NULL,
    Pais_cli VARCHAR(50) NOT NULL,
    Tel_cli VARCHAR(20) NOT NULL,
    Email_cli VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS Tipo_habitacion (
    ID_tipo INT PRIMARY KEY AUTO_INCREMENT,
    Tipo_hab VARCHAR(50) NOT NULL,
    Precio_hab DECIMAL(10,2) NOT NULL,
    Capacidad_hab_a INT NOT NULL,
    Capacidad_hab_n INT NOT NULL,
    Descripcion VARCHAR(150) NOT NULL
);

CREATE TABLE IF NOT EXISTS Habitacion (
    ID_hab INT PRIMARY KEY AUTO_INCREMENT,
    Num_hab INT NOT NULL,
    ID_tipo_hab INT NOT NULL,
    FOREIGN KEY (ID_tipo_hab) REFERENCES Tipo_habitacion(ID_tipo)
);

CREATE TABLE IF NOT EXISTS Reservacion (
    ID_reserv INT PRIMARY KEY AUTO_INCREMENT,
    Num_reserv INT NOT NULL,
    Fecha_ent DATE NOT NULL,
    Fecha_sal DATE NOT NULL,
    Estado ENUM('Cancelado', 'Pagada') NOT NULL,
    Num_adult INT NOT NULL,
    Num_ninos INT NOT NULL,
    Comentarios VARCHAR(100),
    Precio DECIMAL(10,2) NOT NULL,
    ID_cliente_FK INT NOT NULL,
    FOREIGN KEY (ID_cliente_FK) REFERENCES Cliente(ID_Cliente)
);

CREATE TABLE IF NOT EXISTS Asignacion (
    ID_asig_PK INT PRIMARY KEY AUTO_INCREMENT,
    ID_reserv_FK INT NOT NULL,
    ID_hab_FK INT NOT NULL,
    FOREIGN KEY (ID_reserv_FK) REFERENCES Reservacion(ID_reserv),
    FOREIGN KEY (ID_hab_FK) REFERENCES Habitacion(ID_hab)
);

CREATE TABLE IF NOT EXISTS Pago (
    ID_pago INT PRIMARY KEY AUTO_INCREMENT,
    Titular_tar VARCHAR(100) NOT NULL,
    Num_tar VARCHAR(20) NOT NULL,
    Mes_ven INT NOT NULL,
    Ano_ven INT NOT NULL,
    ID_reserva_FK INT NOT NULL,
    FOREIGN KEY (ID_reserva_FK) REFERENCES Reservacion(ID_reserv)
);

CREATE TABLE IF NOT EXISTS Usuario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usua_nombre TEXT NOT NULL,
    usua_correo VARCHAR(50) NOT NULL,
    usua_password VARCHAR(100) NOT NULL,
    usua_roll INT(1) NOT NULL,
    usua_estado VARCHAR(10) NOT NULL
);

CREATE TABLE IF NOT EXISTS Usuarios_token (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    estado VARCHAR(50) NOT NULL,
    fecha DATETIME NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuario(id)
);

-- Procedimientos almacenados
DELIMITER $$
CREATE PROCEDURE Asignar (IN ID_reserva INT, IN ID_hab INT)
BEGIN
    INSERT INTO Asignacion (ID_reserv_FK, ID_hab_FK)
    VALUES (ID_reserva, ID_hab);
    COMMIT;
END$$

CREATE PROCEDURE Disponibilidad (
    IN Busqueda_entrada DATE, 
    IN Busqueda_salida DATE, 
    IN Busqueda_adultos INT, 
    IN Busqueda_ninos INT
)
BEGIN
    SELECT H.Num_hab AS 'N° Habitación', 
           T.Tipo_hab AS 'Tipo', 
           T.Precio_hab AS 'Precio', 
           T.Capacidad_hab_a AS 'Adultos', 
           T.Capacidad_hab_n AS 'Niños'
    FROM Habitacion H
    INNER JOIN Tipo_habitacion T ON H.ID_tipo_hab = T.ID_tipo
    LEFT JOIN Asignacion A ON H.ID_hab = A.ID_hab_FK
    LEFT JOIN Reservacion R ON A.ID_reserv_FK = R.ID_reserv
    WHERE H.ID_hab NOT IN (
        SELECT A2.ID_hab_FK
        FROM Asignacion A2
        JOIN Reservacion R2 ON A2.ID_reserv_FK = R2.ID_reserv
        WHERE (R2.Estado = 'Pagada') AND 
              ((Busqueda_entrada BETWEEN R2.Fecha_ent AND R2.Fecha_sal) OR 
               (Busqueda_salida BETWEEN R2.Fecha_ent AND R2.Fecha_sal) OR 
               (R2.Fecha_ent BETWEEN Busqueda_entrada AND Busqueda_salida))
    ) AND T.Capacidad_hab_a >= Busqueda_adultos
      AND T.Capacidad_hab_n >= Busqueda_ninos
    GROUP BY H.ID_hab;
END$$
CALL Disponibilidad ('2025-01-22', '2025-01-25', 3, 2);


CREATE PROCEDURE Habitaciones (IN Numero INT, IN Tipo INT)
BEGIN
    INSERT INTO Habitacion (Num_hab, ID_tipo_hab)
    VALUES (Numero, Tipo);
    COMMIT;
END$$

/*Llamada del procedimiento Habitaciones*/
CALL Habitaciones('1', '1');
CALL Habitaciones('2', '1');
CALL Habitaciones('3', '1');
CALL Habitaciones('4', '1');
CALL Habitaciones('5', '1');
CALL Habitaciones('6', '2');
CALL Habitaciones('7', '2');
CALL Habitaciones('8', '2');
CALL Habitaciones('9', '2');
CALL Habitaciones('10', '2');
CALL Habitaciones('11', '3');
CALL Habitaciones('12', '3');
CALL Habitaciones('13', '3');
CALL Habitaciones('14', '3');
CALL Habitaciones('15', '3');
CALL Habitaciones('16', '4');
CALL Habitaciones('17', '4');
CALL Habitaciones('18', '4');
CALL Habitaciones('19', '4');
CALL Habitaciones('20', '4');

CREATE PROCEDURE Insertar_cliente (
    IN Nombre VARCHAR(50), 
    IN Apellido VARCHAR(50), 
    IN Pais VARCHAR(50), 
    IN Telefono INT, 
    IN Email VARCHAR(100)
)
BEGIN
    INSERT INTO Cliente (Nombre_cli, Apellido_cli, Pais_cli, Tel_cli, Email_cli)
    VALUES (Nombre, Apellido, Pais, Telefono, Email);
    SELECT LAST_INSERT_ID() AS id_cliente;
    COMMIT;
END$$

CALL Insertar_cliente ('Gabriela', 'Albaez', 'Panama', '65986598', 'correo@correo.com');

CREATE PROCEDURE Insertar_hab (
    IN Tipo VARCHAR(50), 
    IN Precio DECIMAL(10,2), 
    IN Capacidad_a INT, 
    IN Capacidad_n INT, 
    IN Descrip VARCHAR(150)
)
BEGIN
    INSERT INTO Tipo_habitacion (Tipo_hab, Precio_hab, Capacidad_hab_a, Capacidad_hab_n, Descripcion)
    VALUES (Tipo, Precio, Capacidad_a, Capacidad_n, Descrip);
    COMMIT;
END$$

/*Insertando datos en tabla Tipo_habitacion*/
CALL Insertar_hab('Vista al jardin', '104.00', '3', '1', 'Estas cómodas habitaciones con una cama king 
o dos camas dobles están ubicadas cerca del lobby y cerca de la piscina con toboganes.'); 

CALL Insertar_hab('Suite de lujo', '104.00', '4', '2', 'Estas cómodas habitaciones con una cama king o 
dos camas queen cuentan con un balcón privado con vista a la piscina de agua salada');

CALL Insertar_hab('Vista la piscina', '111.00', '3', '2', 'Modernas habitaciones con una cama king o 2 
camas queen, disfrutan de vista a la alberca y toboganes gigantes');

CALL Insertar_hab('Habitación Familiar', '158.00', '5', '3', 'Estas confortables habitaciones con 3 
camas matrimoniales están ubicadas cerca de restaurantes, Alberca y bares');


CREATE PROCEDURE Reservar (
    IN Num_reserva INT, 
    IN Fecha_entrada DATE, 
    IN Fecha_salida DATE, 
    IN Estado VARCHAR(50), 
    IN Num_adult INT, 
    IN Num_ninos INT, 
    IN Precio DECIMAL(10,2), 
    IN ID_cliente INT
)
BEGIN
    INSERT INTO Reservacion (Num_reserv, Fecha_ent, Fecha_sal, Estado, Num_adult, Num_ninos, Precio, ID_cliente_FK)
    VALUES (Num_reserva, Fecha_entrada, Fecha_salida, Estado, Num_adult, Num_ninos, Precio, ID_cliente);
    SELECT LAST_INSERT_ID() AS ID_reserva;
    COMMIT;
END$$

CREATE PROCEDURE Tarjeta (
    IN Titular VARCHAR(100), 
    IN Numero_tar VARCHAR(20), 
    IN Mes INT, 
    IN Ano INT, 
    IN ID_reserva INT
)
BEGIN
    INSERT INTO Pago (Titular_tar, Num_tar, Mes_ven, Ano_ven, ID_reserva_FK)
    VALUES (Titular, Numero_tar, Mes, Ano, ID_reserva);
    COMMIT;
END$$
DELIMITER ;

-- Vista para los Administradores
CREATE OR REPLACE VIEW Vista_reservaciones AS
SELECT 
    C.Nombre_cli AS 'Nombre_del_cliente', 
    C.Apellido_cli AS 'Apellido_del_cliente',
    R.Num_reserv AS 'Numero_reservacion', 
    R.Estado AS 'Estado',
    R.Fecha_ent AS 'Fecha_llegada', 
    R.Fecha_sal AS 'Fecha_salida',
    T.Tipo_hab AS 'Tipo_habitacion', 
    H.Num_hab AS 'Numero_habitacion'
FROM Cliente C
JOIN Reservacion R ON C.ID_Cliente = R.ID_cliente_FK
JOIN Asignacion A ON A.ID_reserv_FK = R.ID_reserv
JOIN Habitacion H ON H.ID_hab = A.ID_hab_FK
JOIN Tipo_habitacion T ON T.ID_tipo = H.ID_tipo_hab
ORDER BY C.Nombre_cli;

-- Tabla para auditoría --
CREATE TABLE Auditoria (
	ID_audit INT PRIMARY KEY AUTO_INCREMENT,
	accion VARCHAR(10),
	tabla VARCHAR(50),
	ID INT,
	fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	usua VARCHAR(50)
	);
	

-- Triggers para auditoría --
DELIMITER $$
CREATE TRIGGER auditoria_tabla_cliente
AFTER INSERT ON Cliente
FOR EACH ROW
BEGIN
	INSERT INTO Auditoria (accion, tabla, ID, fecha, usua)
	VALUES ('INSERT', 'Cliente', NEW.ID_Cliente, NOW(), USER());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER auditoria_tabla_reserva
AFTER INSERT ON Reservacion
FOR EACH ROW
BEGIN
	INSERT INTO Auditoria (accion, tabla, ID, fecha, usua)
	VALUES ('INSERT', 'Reservacion',  NEW.ID_reserv, NOW(), USER());
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER auditoria_tabla_pago 
AFTER INSERT ON pago 
FOR EACH ROW BEGIN 
	INSERT INTO auditoria (accion, tabla, ID, fecha, usua)
    VALUES ('INSERT', 'Pago', New.ID_pago, NOW(), USER());
END
$$
DELIMITER ;