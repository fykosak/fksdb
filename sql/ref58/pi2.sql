ALTER TABLE `login`
DROP `email`,
DROP `fb_id`,
DROP `linkedin_id`,
COMMENT=''
REMOVE PARTITIONING;
