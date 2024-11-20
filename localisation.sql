CREATE TABLE IF NOT EXISTS utilisateurs(
    id INT NOT NULL AUTO_INCREMENT,
    prenom VARCHAR(20) NOT NULL,
    nom    VARCHAR(20) NOT NULL,
    email VARCHAR(20) NOT NULL,
    motDePass VARCHAR(50) NOT NULL,
    motDePassConfirm VARCHAR(50) NOT NULL,
    PRIMARY KEY (id)

);

CREATE TABLE IF NOT EXISTS enfant(
idEnfant INT NOT NULL AUTO_INCREMENT,
nom VARCHAR(20) NOT NULL,
postNom VARCHAR(20) NOT NULL,
prenom VARCHAR(20) NOT NULL,
age INT(3) NOT NULL,
classe VARCHAR(20) NOT NULL,
photo longblob,
PRIMARY KEY (idEnfant)
);

CREATE TABLE IF NOT EXISTS position (
id INT NOT NULL AUTO_INCREMENT,
idEnfant INT NOT NULL,
latitude FLOAT NOT NULL,
longitude FLOAT NOT NULL,

etat TINYINT(1) NOT NULL,
PRIMARY KEY(id),
FOREIGN KEY (idEnfant) REFERENCES enfant(idEnfant)
);