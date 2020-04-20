INSERT INTO region (country_iso, nuts, name, phone_prefix, phone_nsn) VALUES
('EP', 'WTF', 'NEZNÁMÝ',null,null), -- Kvůli nějakému expertu z DAKOSu
('SK', 'SK', 'Slovensko','+421',9),
('CZ', 'CZ', 'Česká republika','+420',9),
('DE', 'DE', 'Deutschland',null,null),
('FR', 'FR', 'France',null,null),
('BY', 'BY', 'Беларусь',null,null),
('RS', 'RS', 'Srbija',null,null),
('TR', 'TR', 'Türkiye',null,null),
('HU', 'HU', 'Magyarország',null,null),
('LT', 'LT', 'Lietuva',null,null),
('US', 'US', 'United States of America',null,null),
('SK', 'SK010', 'Bratislavský kraj',null,null),
('SK', 'SK021', 'Trnavský kraj',null,null),
('SK', 'SK022', 'Trenčiansky kraj',null,null),
('SK', 'SK023', 'Nitriansky kraj',null,null),
('SK', 'SK031', 'Žilinský kraj',null,null),
('SK', 'SK032', 'Banskobystrický kraj',null,null),
('SK', 'SK041', 'Prešovský kraj',null,null),
('SK', 'SK042', 'Košický kraj',null,null),
('CZ', 'CZ010', 'Hlavní město Praha',null,null),
('CZ', 'CZ020', 'Středočeský kraj',null,null),
('CZ', 'CZ031', 'Jihočeský kraj',null,null),
('CZ', 'CZ032', 'Plzeňský kraj',null,null),
('CZ', 'CZ041', 'Karlovarský kraj',null,null),
('CZ', 'CZ042', 'Ústecký kraj',null,null),
('CZ', 'CZ051', 'Liberecký kraj',null,null),
('CZ', 'CZ052', 'Královéhradecký kraj',null,null),
('CZ', 'CZ053', 'Pardubický kraj',null,null),
('CZ', 'CZ063', 'Kraj Vysočina',null,null),
('CZ', 'CZ064', 'Jihomoravský kraj',null,null),
('CZ', 'CZ071', 'Olomoucký kraj',null,null),
('CZ', 'CZ072', 'Zlínský kraj',null,null),
('CZ', 'CZ080', 'Moravskoslezský kraj',null,null);

INSERT INTO contest (contest_id, name) VALUES
(1, 'FYKOS'),
(2, 'VÝFUK');

INSERT INTO study_year (study_year) VALUES
(1),
(2),
(3),
(4),
(6),
(7),
(8),
(9);

INSERT INTO `role` (`role_id`, `name`, `description`) VALUES
(1,'webmaster','webař'),
(2,'taskManager','úlohář'),
(3,'dispatcher','koordinátor obálkování'),
(4,'dataManager','správce (dat) DB'),
(5,'eventManager','správce přihlášek'),
(6,'inboxManager','příjemce řešení'),
(7,'boss','hlavní organizátor (šéf)'),
(8,'org','základní role organizátora'),
(9,'contestant','řešitel semináře, role je automaticky přiřazována při vytvoření řešitele'),
(10,'exportDesigner','tvůrce exportů'),
(11,'aesop','oslizávač dat pro AESOP'),
(12,'schoolManager','správce dat škol'),
(13,'web','Dokuwiki uživatel pro fksdbexport'),
(14,'wiki','Uživatel neveřejné Dokuwiki pro fksdbexport'),
(100,'superuser','složení všech rolí'),
(1000,'cartesian','cokoli s čímkoli');

INSERT INTO `flag` (`flag_id`, `fid`, `name`, `description`, `type`) VALUES
(1,'serial_author','serial_author','Je organizátornizátor autor seriálu','contest_year'),
(2,'email_invalid','Neplatný e-mail','E-maily zaslané na danou adresu se nám již nekdy vrátily. Zatím sem nepatri adresy, u nichž je hlášen \'mailbox full\'.','global'),
(3,'spam_mff','Spam z MFF','Zda si preje/nepreje dostávat spam z Matfyzu.','global'),
(4,'send_forum','Notifikace ze fóra','Ze starého fóra na FYKOSím webu.','global'),
(5,'send_forum_dgst','Digesty z fóra','Posílat digesty ze starého fóra na FYKOSím webu.','global');



