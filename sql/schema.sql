-- db

SET storage_engine=InnoDB;

DROP DATABASE IF EXISTS fksdb;
CREATE DATABASE fksdb CHARACTER SET 'utf8';

USE fksdb;

-- kontakty

CREATE TABLE person (
	person_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	family_name VARCHAR(255) NOT NULL COMMENT 'Příjmení (nebo více příjmení oddělených jednou mezerou)',
	other_name VARCHAR(255) NOT NULL COMMENT 'Křestní jména, von, de atd., oddělená jednou mezerou',
	display_name VARCHAR(511) NULL COMMENT 'zobrazované jméno, liší-li se od <other_name> <family_name>',
	gender ENUM('M', 'F') NOT NULL
)
	COMMENT = 'řazení: <family_name><other_name>, zobrazení <other_name> <family_name>';

CREATE TABLE country (		
	country_iso CHAR(2) NOT NULL PRIMARY KEY COMMENT 'dvojznakový kód země dle ISO 3166-1',
	name_cs VARCHAR(255) NOT NULL,
	name_en VARCHAR(255) NOT NULL
)
	COMMENT = 'Ciselnik států';

CREATE TABLE region (		
	region_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	country_iso CHAR(2) NOT NULL,
	nuts VARCHAR(5) UNIQUE,
	name VARCHAR(255) NOT NULL,
	FOREIGN KEY (country_iso) REFERENCES country(country_iso)
)
	COMMENT = 'Ciselnik regionu pro vyber skoly v registraci';

CREATE TABLE psc_region (
	psc CHAR(5) NOT NULL PRIMARY KEY,
	region_id INT NOT NULL,
	FOREIGN KEY (region_id) REFERENCES region(region_id)
)
	COMMENT = 'mapování český a slovenckých PSČ na evidovaný region';

CREATE TABLE address (		
	address_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	first_row VARCHAR(255) NULL COMMENT 'doplňkový řádek adresy (např. bytem u X Y)',
	second_row VARCHAR(255) NULL COMMENT 'ještě doplňkovější řádek adresy (nikdo neví)',
	target VARCHAR(255) NOT NULL COMMENT 'ulice č.p./or., vesnice č.p./or., poštovní přihrádka atd.',
	city VARCHAR(255) NOT NULL COMMENT 'město doručovací pošty',
	postal_code CHAR(5) COMMENT 'PSČ (pro ČR a SR)',
--  zdánlivě nenormální (PSČ->region), ale počítáme i s adresami s neCZ neSK PSČ
	region_id INT NOT NULL COMMENT 'detekce státu && formátovacích zvyklostí',
	FOREIGN KEY (region_id) REFERENCES region(region_id)
)
	COMMENT = 'adresa jako poštovní nikoli územní identifikátor, immutable.';
	-- nerozlišujeme změnu adresy (např. přejmenování ulice) od stěhování,
	-- jelikož stejně nevevidujeme historii

CREATE TABLE check_address (
	address_id INT NOT NULL PRIMARY KEY,
	FOREIGN KEY (address_id) REFERENCES address(address_id)
);

CREATE TABLE school (
	school_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, -- Klíčem není IČ/IZO, neboť zahraniční školy
	name_full VARCHAR(255)	COMMENT 'plný název školy',
	name VARCHAR(255) NOT NULL COMMENT 'zkrácený název školy (na obálku)',
	name_abbrev VARCHAR(32) NOT NULL	COMMENT 'Zkratka pouzivana napr. ve vysledkove listine',
	address_id INT NOT NULL,
	email VARCHAR(255)		COMMENT 'Kontaktní e-mail',
	ic CHAR(8)				COMMENT 'IČ (osm číslic)',
	izo VARCHAR(32)		COMMENT 'IZO kód (norma?)',
	active BOOL			COMMENT 'Platný záznam školy',
	note VARCHAR(255),
	UNIQUE(ic),
	UNIQUE(izo), -- právnické osoby, nebo školy (druhá možnost je problematická)?
	FOREIGN KEY (address_id) REFERENCES address(address_id)
);

CREATE TABLE check_school (
	school_id INT NOT NULL PRIMARY KEY,
	FOREIGN KEY (school_id) REFERENCES school(school_id)
);

CREATE TABLE dakos_school (
	dakos_SKOLA_Id INT NOT NULL PRIMARY KEY,
	school_id INT NOT NULL,
	FOREIGN KEY (school_id) REFERENCES school(school_id)
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

CREATE TABLE dakos_person (
	dakos_id INT NOT NULL PRIMARY KEY COMMENT 'Id účastníka z dakosího exportu',
	person_id INT NOT NULL,
	FOREIGN KEY (person_id) REFERENCES person(person_id)
)
	COMMENT = 'Identifikace osoby z DaKoSu';

CREATE TABLE olddb_person (
	olddb_uid INT NOT NULL PRIMARY KEY COMMENT 'users.id ze staré DB',
	person_id INT NOT NULL,
	olddb_redundant BOOL NOT NULL COMMENT 'Tato data se nezkopírovala',
	FOREIGN KEY (person_id) REFERENCES person(person_id)
);

CREATE TABLE post_contact (
	post_contact_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	person_id INT NOT NULL,
	address_id INT NOT NULL,
	type ENUM('P', 'D') NOT NULL	COMMENT 'doručovací (Delivery), trvalá (Permanent)', -- pokud trvalá == doručovací, uvede se jako trvalá
	FOREIGN KEY (person_id) REFERENCES person(person_id), -- kvůli poštovnímu spamu odkaz na person, nikoli person_info
	FOREIGN KEY (address_id) REFERENCES address(address_id)
)
	COMMENT = 'Přiřazení adres lidem vztahem M:N';


-- auth

CREATE TABLE login (
	person_id INT NOT NULL PRIMARY KEY,
	login VARCHAR(255) NULL	COMMENT 'Login name',
	email VARCHAR(255) NOT NULL,
	hash CHAR(40) NULL 		COMMENT 'sha1(person_id . md5(password)) as hexadecimal', -- kvůli přenosu starých MD5 hashů a rainbow tables
	fb_id VARCHAR(255) NULL		COMMENT 'ID pro přihlášení přes FB',
	created DATETIME NOT NULL,
	last_login DATETIME NULL,
	active BOOL NOT NULL,
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
	right_id INT NOT NULL,
	FOREIGN KEY (person_id) REFERENCES login(person_id),
	FOREIGN KEY (right_id) REFERENCES `right`(right_id),
	UNIQUE (person_id, right_id)
);

CREATE TABLE auth_token (
	person_id INT NOT NULL,
	token VARCHAR(255) NOT NULL,
	type VARCHAR(31),
	since DATETIME NOT NULL,
	until DATETIME,
	FOREIGN KEY (person_id) REFERENCES login(person_id)
);

-- seminář: ucastnici, ulohy a orgové

CREATE TABLE contest (
	contest_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255) NOT NULL
)
	COMMENT = '(sub)semináře'
;
CREATE TABLE contestant (		
	ct_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	contest_id INT NOT NULL		COMMENT 'seminář',
	year TINYINT NOT NULL		COMMENT 'Rocnik semináře',
	person_id INT NOT NULL,
	school_id INT,
	class VARCHAR(16) COMMENT 'třída, do níž chodí, př. IV.B',
	study_year TINYINT COMMENT 'ročník, který studuje 6--9 nebo 1--4', 
	FOREIGN KEY (person_id) REFERENCES person(person_id),
	FOREIGN KEY (school_id) REFERENCES school(school_id),
	FOREIGN KEY (contest_id) REFERENCES contest(contest_id),
	UNIQUE(contest_id, year, person_id)
)
	COMMENT = 'Instance ucastnika (v konkretnim rocniku a semináři)';



CREATE TABLE org (
	org_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	person_id INT NOT NULL,
	contest_id INT NOT NULL,
	since TINYINT NOT NULL		COMMENT 'od kterého ročníku orguje',
	until TINYINT NULL		COMMENT 'v kterém rončíku skončil',
	role VARCHAR(32)		COMMENT 'hlavní org, úlohář, etc.',
	note TEXT,
	`order` TINYINT NOT NULL	COMMENT 'pořadí pro řazení ve výpisech',
	tex_signature VARCHAR(32)	COMMENT 'zkratka používaná v TeXových vzorácích',
	UNIQUE(contest_id, person_id),
	UNIQUE(contest_id, tex_signature),
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
	COMMENT = '';
-- TODO rozšířit pro ukládání textů úloh

CREATE TABLE submit (
--	submit_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, -- TODO K čemu je to dobré?
	ct_id INT NOT NULL		COMMENT 'Contestant',
	task_id INT NOT NULL		COMMENT 'Task',
	submitted_on DATETIME NOT NULL,
	source enum('post', 'upload') NOT NULL	COMMENT 'odkud přišlo řešení',
	note VARCHAR(255)		COMMENT 'Pocet stranek a jine poznamky',
	raw_points DECIMAL(4,2)	COMMENT 'Pred prepoctem',
	calc_points DECIMAL(4,2)	COMMENT 'Po prepoctu (NULL pokud se v tomto rocniku neprepocitava)',
	FOREIGN KEY (ct_id) REFERENCES contestant(ct_id),
	FOREIGN KEY (task_id) REFERENCES task(task_id),
	PRIMARY KEY (ct_id,task_id)
);


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

CREATE TABLE si_collection (
	collection_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	school_year SMALLINT NOT NULL	COMMENT 'první kalendářní rok školního roku, k němuž jsou data validní'	,
	collection_date DATE NOT NULL	COMMENT 'konec sběru dat (orientačně)',
	note TEXT NULL
)
	COMMENT = 'Záznam o sběru spamovacích dat. Klidně děleno na jednotlivé soutěže.';

CREATE TABLE si_spamee (
	spamee_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	person_id INT NOT NULL,
	collection_id INT NOT NULL	COMMENT 'při kterém sběru byla získán tento kontakt',
	school_id INT,
	class VARCHAR(16) 		COMMENT 'třída, do níž chodí, př. IV.B',
	study_year TINYINT		COMMENT 'ročník, který studuje 6--9 nebo 1--4 ', 
	note VARCHAR(255)		COMMENT 'poznámka, např. počet bodů, které měl při sběru',
	FOREIGN KEY (person_id) REFERENCES person(person_id),
	FOREIGN KEY (school_id) REFERENCES school(school_id),
	FOREIGN KEY (collection_id) REFERENCES si_collection(collection_id)	
);


-- Soustredeni a jiné akce
CREATE TABLE `action` (
	action_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	contest_id INT NOT NULL		COMMENT 'seminář',
	year TINYINT NOT NULL		COMMENT 'ročník',
	begin DATE NOT NULL		COMMENT 'první den akce',
	`end` DATE NOT NULL		COMMENT 'poslední den akce, u jednodenní akce shodný s begin',
	applications enum('internal', 'web') NOT NULL	COMMENT 'způsob přihlašování na akci',
	registration_begin DATE NULL	COMMENT 'případný počátek webové registrace',
	registration_end DATE NULL	COMMENT 'případný konec webové registrace',
	name VARCHAR(255) NOT NULL	COMMENT 'název akce',
	collection_id INT NULL		COMMENT 'spamovací sběr z této akce, je-li webově přihlašovaná',
	fb_album_id BIGINT NULL 	COMMENT 'id galerie na Facebooku',
	report TEXT NULL 			COMMENT '(HTML) zápis z proběhlé akce',
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


-- statusboard: udalosti a jejich terminy

-- CREATE TABLE status_board (
-- 	st_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
-- 	event_type VARCHAR(16) NOT NULL,
-- 	object VARCHAR(16) NOT NULL,
-- 	completed DATETIME,
-- 	deadline DATETIME,
-- 	comment VARCHAR(255) NOT NULL DEFAULT "",
-- 	event_category CHAR NOT NULL DEFAULT "T"	COMMENT 'kategorie -- D pro deadline-only, T pro TODO',
-- 	UNIQUE (event_type, object)
-- );
-- 
-- CREATE TABLE status_depends (
-- 	st_id INT NOT NULL,
-- 	depends_on INT NOT NULL,
-- 	FOREIGN KEY (st_id) REFERENCES status_board(st_id) ON DELETE CASCADE,
-- 	FOREIGN KEY (depends_on) REFERENCES status_board(st_id) ON DELETE CASCADE,
-- 	UNIQUE (st_id, depends_on)
-- );


-- grants

-- CREATE USER fksdb IDENTIFIED BY 'brumbrum';	COMMENT 'FIXME: password'
-- CREATE USER fksdbweb IDENTIFIED BY 'brum';	COMMENT 'FIXME: password'

-- GRANT ALL ON fksdb.* TO fksdb WITH GRANT OPTION;
-- GRANT SELECT, UPDATE, INSERT, DELETE ON fksdb.* TO fksdbweb;
