-- CallMigrate Remote Agent Database

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


--
-- Database: `callmigrate`
--
CREATE DATABASE IF NOT EXISTS `callmigrate`;
USE `callmigrate`;


--
-- Table `settings`
--

CREATE TABLE `settings` (
  `pkid` int NOT NULL,
  `sitetitle` varchar(100) DEFAULT NULL,
  `regstatus` tinyint NOT NULL,
  `claimstatus` tinyint NOT NULL,
  `custname` varchar(100) DEFAULT NULL,
  `clientid` varchar(100) DEFAULT NULL,
  `clientsecret` varchar(100) DEFAULT NULL,
  `cmserver` varchar(50) DEFAULT NULL,
  `cmremuser` varchar(25) DEFAULT NULL
);

INSERT INTO `settings` (`pkid`, `sitetitle`, `regstatus`, `claimstatus`, `cmserver`) VALUES (0, 'CallMigrate Remote', 0, 0, 'callmigrate.click');

--
-- Table `tunnels`
--

CREATE TABLE `tunnels` (
  `pkid` int NOT NULL,
  `tunnelname` varchar(50) NOT NULL,
  `tunnelport` varchar(5) NOT NULL,
  `localproto` varchar(25) NOT NULL,
  `localhost` varchar(50) NOT NULL,
  `localport` varchar(5) NOT NULL
);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`pkid`);

--
-- Indexes for table `tunnels`
--
ALTER TABLE `tunnels`
  ADD PRIMARY KEY (`pkid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `pkid` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tunnels`
--
ALTER TABLE `tunnels`
  MODIFY `pkid` int NOT NULL AUTO_INCREMENT;
COMMIT;

--
-- ADD cmdbuser and GRANT 
--
CREATE USER 'cmdbuser'@'localhost' IDENTIFIED BY 'HPwvw_8I5MzxBLZk';
GRANT ALL PRIVILEGES ON callmigrate.* TO 'cmdbuser'@'localhost' WITH GRANT
 OPTION;
FLUSH PRIVILEGES;
