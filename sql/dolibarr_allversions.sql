--
-- Script run when an upgrade of Dolibarr is done. Whatever is the Dolibarr version.
--

CREATE TABLE `ddsu_adherent_assiduity` (
  `rowid` integer AUTO_INCREMENT PRIMARY KEY,
  `entity` integer NOT NULL DEFAULT 1,
  `fk_object` integer DEFAULT NULL,
  `event_id` mediumtext DEFAULT NULL,
  `datestamp` integer NOT NULL,
  `assiduity` mediumtext DEFAULT NULL
) ENGINE=InnoDB
