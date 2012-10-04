-- db

SET storage_engine=InnoDB;

DROP DATABASE IF EXISTS fksdb;
CREATE DATABASE fksdb CHARACTER SET 'utf8';

USE fksdb;

-- kontakty

CREATE TABLE person (
	person_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	first_name VARCHAR(255),
	last_name VARCHAR(255),
	gender ENUM('M', 'F')
);

CREATE TABLE country (		
	country_iso CHAR(2) NOT NULL PRIMARY KEY COMMENT 'dvojznakový ISO kód země',
	name_cs VARCHAR(255) NOT NULL,
	name_en VARCHAR(255) NOT NULL
)
	COMMENT = 'Ciselnik států';

CREATE TABLE region (		
	region_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	country_iso CHAR(2),
	name VARCHAR(255),
	FOREIGN KEY (country_iso) REFERENCES country(country_iso)
)
	COMMENT = 'Ciselnik regionu pro vyber skoly v registraci';

CREATE TABLE address (		
	address_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	country_iso CHAR(2),
	street VARCHAR(255),
	house_nr VARCHAR(255),
	city VARCHAR(255),
	postal_code CHAR(5) COMMENT 'PSČ',
	region_id INT, -- PSČ --> kraj není normální, ale pohodlné 
	FOREIGN KEY (region_id) REFERENCES region(region_id)
)
	COMMENT = 'Adresa jako hodnotový objekt'; -- i jako immutable? TODO rozmyslet změnu adresy/stěhování

CREATE TABLE school (
	school_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255),
	abbrev VARCHAR(32)		COMMENT 'Zkratka pouzivana napr. ve vysledkove listine',
	address_id INT NOT NULL,
	email VARCHAR(255)		COMMENT 'Kontaktní e-mail',
	izo VARCHAR(255)		COMMENT 'IZO kód', -- TODO
	active BOOL			COMMENT 'Platný záznam školy',
	FOREIGN KEY (address_id) REFERENCES address(address_id)
);

CREATE TABLE person_info (
	person_id INT NOT NULL PRIMARY KEY,
	born DATE NULL			COMMENT 'datum narození',
	id_number VARCHAR(32) NULL	COMMENT 'číslo OP či ekvivalent',
	born_id VARCHAR(32) NULL	COMMENT 'rodné číslo nebo ekvivalent',
	phone VARCHAR(32) NULL		COMMENT 'tel. číslo',
	im VARCHAR(32) NULL		COMMENT 'ICQ, XMPP, etc.',
	note TEXT NULL			COMMENT 'ostatní/poznámka',
	uk_login VARCHAR(8) NULL	COMMENT 'CAS login, pro orgy',
	account VARCHAR(32) NULL	COMMENT 'bankovní účet jako text',
	agreed DATETIME NULL		COMMENT 'čas posledního souhlasu ze zprac. os. ú. nebo null',
	FOREIGN KEY (person_id) REFERENCES person(person_id)
)
	COMMENT = 'Podrobné informace o osobě, zde jsou všechny osobní údaje (tm)';

CREATE TABLE post_contact (
	post_contact_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	person_id INT NOT NULL,
	address_id INT NOT NULL,
	type ENUM('P', 'D') NOT NULL	COMMENT 'doručovací (Delivery), trvalá (Permanent)', -- TODO a ještě školní?
	FOREIGN KEY (person_id) REFERENCES person(person_id), -- kvůli poštovnímu spamu odkaz na person, nikoli person_info
	FOREIGN KEY (address_id) REFERENCES address(address_id)
)
	COMMENT = 'Přiřazení adres lidem vztahem M:N';


-- auth

CREATE TABLE login (
	person_id INT NOT NULL PRIMARY KEY,
	login VARCHAR(255) NOT NULL	COMMENT 'Login name',
	email VARCHAR(255) NOT NULL,
	hash VARCHAR(255) NOT NULL	COMMENT 'SHA1 hash hesla',
	fb_id VARCHAR(255) NULL		COMMENT 'ID pro přihlášení přes FB',
	created DATETIME,
	last_login DATETIME,
	active BOOL,
	UNIQUE (login),
	UNIQUE (email),
	FOREIGN KEY (person_id) REFERENCES person(person_id)
);

CREATE TABLE `right` (
	right_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`right` VARCHAR(255) NOT NULL
);

CREATE TABLE permission (
	person_id INT NOT NULL,
	right_id INT,
	FOREIGN KEY (person_id) REFERENCES login(person_id),
	FOREIGN KEY (right_id) REFERENCES `right`(right_id),
	UNIQUE (person_id, right_id)
);

CREATE TABLE auth_token (
	person_id INT NOT NULL,
	token VARCHAR(255),
	type VARCHAR(31),
	since DATETIME,
	until DATETIME,
	FOREIGN KEY (person_id) REFERENCES login(person_id)
);

-- seminář: ucastnici, ulohy a orgové

CREATE TABLE contest (
	contest_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255)
)
	COMMENT = '(sub)semináře'
;
CREATE TABLE contestant (		
	ct_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	contest_id INT NOT NULL		COMMENT 'seminář',
	year TINYINT			COMMENT 'Rocnik semináře',
	person_id INT NOT NULL,
	school_id INT,
	class VARCHAR(16) COMMENT 'třída, do níž chodí, př. IV.B',
	study_year TINYINT	COMMENT 'ročník, který studuje 6--9 nebo 1--4 ', 
	FOREIGN KEY (person_id) REFERENCES person(person_id),
	FOREIGN KEY (school_id) REFERENCES school(school_id),
	FOREIGN KEY (contest_id) REFERENCES contest(contest_id)
)
	COMMENT = 'Instance ucastnika (v konkretnim rocniku a semináři)';

CREATE TABLE org (
	person_id INT NOT NULL PRIMARY KEY,
	contest_id INT NOT NULL,
	since TINYINT NOT NULL		COMMENT 'od kterého ročníku orguje',
	until TINYINT NULL		COMMENT 'v kterém rončíku skončil',
	role VARCHAR(32)		COMMENT 'hlavní org, úlohář, etc.',
	note TEXT,
	`order` TINYINT NOT NULL	COMMENT 'pořadí pro řazení ve výpisech',
	tex_signature VARCHAR(32)	COMMENT 'zkratka používaná v TeXových vzorácích',
	FOREIGN KEY (person_id) REFERENCES person(person_id),
	FOREIGN KEY (contest_id) REFERENCES contest(contest_id)
);

CREATE TABLE task (
	task_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	label VARCHAR(16) NOT NULL	COMMENT 'Oznaceni ulohy, treba "23-4-5"',
	name VARCHAR(255)		COMMENT 'Jmeno ulohy',
	contest_id INT NOT NULL		COMMENT 'seminář',
	year TINYINT NOT NULL		COMMENT 'Rocnik seminare',
	series TINYINT NOT NULL		COMMENT 'Serie',
	tasknr TINYINT			COMMENT 'Uloha',
	points TINYINT			COMMENT 'Maximalni pocet bodu',
	submit_mode CHAR		COMMENT 'Zpusob odevzdavani: "S" (submitovatko), "C" (codex), "P" (premie)',
	submit_start DATETIME		COMMENT 'Od kdy se smi submitovat',
	submit_deadline DATETIME	COMMENT 'Do kdy',
	correction_mode CHAR		COMMENT 'Zpusob opravovani: "P" (papirove), "E" (elektronicky), jinak NULL',
	FOREIGN KEY (contest_id) REFERENCES contest(contest_id)
)
	COMMENT = 'Premie z historickych rocniku jsou ulozeny jako ulohy "8-1-P", submit_mode="P", tasknr=0';

CREATE TABLE submit (
	ct_id INT NOT NULL		COMMENT 'Contestant',
	task_id INT NOT NULL		COMMENT 'Task',
	submitted_on DATETIME,
	source enum('post', 'upload')	COMMENT 'odkud přišlo řešení',
	note VARCHAR(255)		COMMENT 'Pocet stranek a jine poznamky',
	raw_points DECIMAL(4,2)	COMMENT 'Pred prepoctem',
	calc_points DECIMAL(4,2)	COMMENT 'Po prepoctu (NULL pokud se v tomto rocniku neprepocitava)',
	FOREIGN KEY (ct_id) REFERENCES contestant(ct_id),
	FOREIGN KEY (task_id) REFERENCES task(task_id),
	UNIQUE (ct_id,task_id)
);

-- Soustredeni a jiné akce
CREATE TABLE `action` (
	action_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	contest_id INT NOT NULL		COMMENT 'seminář',
	year TINYINT NOT NULL		COMMENT 'ročník',
	begin DATE NOT NULL		COMMENT 'první den akce',
	`end` DATE NOT NULL		COMMENT 'poslední den akce, u jednodenní akce shodný s begin',
	applications enum('internal', 'web')	COMMENT 'způsob přihlašování na akci',
	registration_begin DATE NULL	COMMENT 'případný počátek webové registrace',
	registration_end DATE NULL	COMMENT 'případný konec webové registrace',
	name VARCHAR(255) NOT NULL	COMMENT 'název akce',
	collection_id INT NULL		COMMENT 'spamovací sběr z této akce, je-li webově přihlašovaná',
	FOREIGN KEY (contest_id) REFERENCES contest(contest_id),
	FOREIGN KEY (collection_id) REFERENCES si_collection(collection_id)
);

CREATE TABLE action_application (
	application_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	action_id INT NOT NULL,
	person_id INT NOT NULL,
	note TEXT NULL			COMMENT 'poznámka',
	status SMALLINT NOT NULL	COMMENT 'Status: 0=pozvany, 1=potvrzeno, 2=odřekl, 3=vybrán, 4=účastnil se, viz SQL comment',
	-- přihlášení přes web: 11=zaregistroval se, 12=odřekl, 13=vybrán, 14=účastnil se
	-- vlastní přihlašování: 20=pozván (200=pozván jako účastník, 201=pozván jako náhradník), 21=potvrdil, 22=odřekl, 23=vybrán, 24=účastnil se
	created DATETIME NOT NULL	COMMENT 'čas vytvoření přihlášky',
	FOREIGN KEY (action_id) REFERENCES `action`(action_id),
	FOREIGN KEY (person_id) REFERENCES person(person_id) -- aby se mohli evidovat "anonymní lidé"	
);

-- TODO
-- dořešit zvaní na akce, pro speciální typy akci (DSEF, Fyziklání) by se měla vytvořit
-- "poděděné" tabulky s pomocnými atributy pro jejich přihlášky

-- Spamovací infrastruktura1
--   emailová i poštovní

CREATE TABLE si_settings ( -- TODO podle vymyšlení
	person_id INT NOT NULL,
	tasks BOOL NOT NULL		COMMENT 'Nové zadání, řešení, výsledkovka.',
	timer3 BOOL NOT NULL		COMMENT 'Upozornění tři dny před uzávěrkou.',
	timer7 BOOL NOT NULL		COMMENT '… sedm dní.',
	correction BOOL NOT NULL	COMMENT 'Odeslat upozornění o opravě řešení.',
	sendPdf BOOL NOT NULL		COMMENT 'Přiložit k tomuto upozornění soubor s opravou.',
	otherSpam BOOL NOT NULL	COMMENT 'Další upozornění – jarní, kalíšek…',
	FOREIGN KEY (person_id) REFERENCES person(person_id)
);

CREATE TABLE si_log (
	person_id INT NOT NULL,
	type VARCHAR(64) NOT NULL	COMMENT '„tasks“, „timer3“, …',
	note VARCHAR(64)		COMMENT '„24-1“… ',
	`time` DATETIME NOT NULL,
	FOREIGN KEY (person_id) REFERENCES person(person_id)
)
	COMMENT = 'Skript si podle logu dává pozor, aby někoho nespamoval dvakrát ohledně toho samého.';

CREATE TABLE si_spamee (
	spamee_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	person_id INT NOT NULL,
	collection_id INT NOT NULL	COMMENT 'při kterém sběru byla získán tento kontakt',
	school_id INT,
	class VARCHAR(16) 		COMMENT 'třída, do níž chodí, př. IV.B',
	study_year TINYINT		COMMENT 'ročník, který studuje 6--9 nebo 1--4 ', 
	FOREIGN KEY (person_id) REFERENCES person(person_id),
	FOREIGN KEY (school_id) REFERENCES school(school_id),
	FOREIGN KEY (collection_id) REFERENCES si_collection(collection_id)	
);
-- TODO ukládat parametry zdroje spamu -- počet bodů v soutěži, kategorie v souteži apod. 
--      pro vyhodnocení spamovací účinnosti

CREATE TABLE si_collection (
	collection_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	school_year SMALLINT NOT NULL	COMMENT 'první kalendářní rok školního roku, k němuž jsou data validní'	,
	collection_date DATE NOT NULL	COMMENT 'konec sběru dat',
	note TEXT NULL
);

-- statusboard: udalosti a jejich terminy

CREATE TABLE status_board (
	st_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	event_type VARCHAR(16) NOT NULL,
	object VARCHAR(16) NOT NULL,
	completed DATETIME,
	deadline DATETIME,
	comment VARCHAR(255) NOT NULL DEFAULT "",
	event_category CHAR NOT NULL DEFAULT "T"	COMMENT 'kategorie -- D pro deadline-only, T pro TODO',
	UNIQUE (event_type, object)
);

CREATE TABLE status_depends (
	st_id INT NOT NULL,
	depends_on INT NOT NULL,
	FOREIGN KEY (st_id) REFERENCES status_board(st_id) ON DELETE CASCADE,
	FOREIGN KEY (depends_on) REFERENCES status_board(st_id) ON DELETE CASCADE,
	UNIQUE (st_id, depends_on)
);


-- grants

-- CREATE USER fksdb IDENTIFIED BY 'brumbrum';	COMMENT 'FIXME: password'
-- CREATE USER fksdbweb IDENTIFIED BY 'brum';	COMMENT 'FIXME: password'

GRANT ALL ON fksdb.* TO fksdb WITH GRANT OPTION;
GRANT SELECT, UPDATE, INSERT, DELETE ON fksdb.* TO fksdbweb;
