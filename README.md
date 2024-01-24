# Eclectic
Webbapp för att administrera en Running Eclectic i golf. Den är skriven för Burviks golfklubb i Uppland, men kan med lätthet anpassas till vilken klubb som helst.
Databasen är MariaDB med anslutningsuppgifter såsom angetts i filen base.php.
Databasen skall ha två tabeller med struktur enligt nedan:

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
CREATE TABLE `ecl_players` (
  `id` int(11) NOT NULL COMMENT 'Spelarens ID',
  `player` varchar(50) NOT NULL DEFAULT '' COMMENT 'Namn',
  `hcp` tinyint(4) NOT NULL DEFAULT 18 COMMENT 'spelHcp',
  `female` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Flagga om kvinna',
  `tee` varchar(10) NOT NULL DEFAULT 'yellow' COMMENT 'Normaltee',
  `pw` varchar(50) NOT NULL DEFAULT 'pw' COMMENT 'Lösenord'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci COMMENT='Spelartabell';
ALTER TABLE `ecl_players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `player` (`player`);
ALTER TABLE `ecl_players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Spelarens ID';
COMMIT;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
CREATE TABLE `ecl_scores` (
  `player` int(11) NOT NULL DEFAULT 0 COMMENT 'Spelarens ID',
  `hole` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Hålet som avses',
  `score` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Antal slag på hålet',
  `hcp` tinyint(4) NOT NULL DEFAULT 18 COMMENT 'Spelarens hcp vid speltillfället',
  `season` int(11) NOT NULL COMMENT 'Aktuell år',
  `play_date` date NOT NULL COMMENT 'Dag då hålet spelades'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Slagresultaten';
ALTER TABLE `ecl_scores`
  ADD PRIMARY KEY (`player`,`hole`,`season`);
COMMIT;

