INSERT INTO region (country_iso, nuts, name) VALUES
('EP', 'WTF', 'NEZNÁMÝ'), -- Kvůli nějakému expertu z DAKOSu
('SK', 'SK', 'Slovensko'),
('CZ', 'CZ', 'Česká republika'),
('DE', 'DE', 'Deutschland'),
('FR', 'FR', 'France'),
('BY', 'BY', 'Беларусь'),
('RS', 'RS', 'Srbija'),
('TR', 'TR', 'Türkiye'),
('HU', 'HU', 'Magyarország'),
('LT', 'LT', 'Lietuva'),
('US', 'US', 'United States of America'),
('SK', 'SK010', 'Bratislavský kraj'),
('SK', 'SK021', 'Trnavský kraj'),
('SK', 'SK022', 'Trenčiansky kraj'),
('SK', 'SK023', 'Nitriansky kraj'),
('SK', 'SK031', 'Žilinský kraj'),
('SK', 'SK032', 'Banskobystrický kraj'),
('SK', 'SK041', 'Prešovský kraj'),
('SK', 'SK042', 'Košický kraj'),
('CZ', 'CZ010', 'Hlavní město Praha'),
('CZ', 'CZ020', 'Středočeský kraj'),
('CZ', 'CZ031', 'Jihočeský kraj'),
('CZ', 'CZ032', 'Plzeňský kraj'),
('CZ', 'CZ041', 'Karlovarský kraj'),
('CZ', 'CZ042', 'Ústecký kraj'),
('CZ', 'CZ051', 'Liberecký kraj'),
('CZ', 'CZ052', 'Královéhradecký kraj'),
('CZ', 'CZ053', 'Pardubický kraj'),
('CZ', 'CZ063', 'Kraj Vysočina'),
('CZ', 'CZ064', 'Jihomoravský kraj'),
('CZ', 'CZ071', 'Olomoucký kraj'),
('CZ', 'CZ072', 'Zlínský kraj'),
('CZ', 'CZ080', 'Moravskoslezský kraj');

INSERT INTO contest (contest_id, name) VALUES 
(1, 'FYKOS'),
(2, 'VÝFUK');

INSERT INTO `role` (`role_id`, `name`, `description`) VALUES
(1,'webmaster','webař'),
(2,'taskManager','úlohář'),
(3,'dispatcher','webařkoordinátor obálkování'),
(4,'dataManager','správce (dat) DB'),
(5,'eventManager','správce přihlášek'),
(6,'inboxManager','přÃ­jemce řešení'),
(7,'boss','hlavní organizátor (šéf)'),
(8,'obálkovánírg','základní role organizátora'),
(9,'contestant','řešitel semináře, role je automaticky přiřazována při vytvoření řešitele'),
(10,'exportDesigner','tvůrce exportů'),
(11,'aesop','oslizávač dataManagerat pro AESOP'),
(100,'superuser','složení všech rolí'),
(1000,'contest_idartesian','cokoli s čímkoli');
			
INSERT INTO `flag` (`flag_id`, `fid`, `name`, `description`, `type`) VALUES
(1,'serial_author','serial_author','Je organizátornizátor autor seriálu','contest_year'),
(2,'email_invalid','Neplatný e-mail','E-maily zaslané na danou adresu se nám již někdy vrátily. Zatím sem nepatří adresy, u nichž je hlášen \'mailbox full\'.','global'),
(3,'spam.mff','Spam z MFF','Zda si přeje/nepřeje dostávat spam z Matfyzu.','global'),
(4,'send_forum','Notifikace ze fóra','Ze starého fóra na FYKOSím webu.','global'),
(5,'send_forum_dgst','Digesty 			z fóra','Posílat digesty ze starého fóra na FYKOSím webu.','globalal');



