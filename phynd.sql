-- MySQL dump 8.21
--
-- Host: localhost    Database: phynd
---------------------------------------------------------
-- Server version	3.23.49-log

--
-- Current Database: phynd
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ phynd;

USE phynd;

--
-- Table structure for table 'ACCESSexcludeip'
--

CREATE TABLE ACCESSexcludeip (
  ip varchar(20) NOT NULL default ''
) TYPE=MyISAM;

--
-- Table structure for table 'ACCESSincludeip'
--

CREATE TABLE ACCESSincludeip (
  ip varchar(20) NOT NULL default ''
) TYPE=MyISAM;

--
-- Table structure for table 'ACCESSiplist'
--

CREATE TABLE ACCESSiplist (
  ip varchar(20) NOT NULL default ''
) TYPE=MyISAM;

--
-- Table structure for table 'ACCESSnetblocks'
--

CREATE TABLE ACCESSnetblocks (
  start varchar(20) NOT NULL default '',
  end varchar(20) NOT NULL default ''
) TYPE=MyISAM;

--
-- Table structure for table 'CATALOGcategories'
--

CREATE TABLE CATALOGcategories (
  name varchar(20) NOT NULL default '',
  extensions varchar(254) NOT NULL default ''
) TYPE=MyISAM;

INSERT INTO `CATALOGcategories` VALUES ('Audio Files', 'mp3:ogg:shn:wav');
INSERT INTO `CATALOGcategories` VALUES ('Video Files', 'mov:mpg:avi:mpeg:asx:asf:wmv:rm');
INSERT INTO `CATALOGcategories` VALUES ('Compressed Files', 'zip:arj:rar:ace:gz:tar');
INSERT INTO `CATALOGcategories` VALUES ('Documents', 'doc:pdf:html:htm:txt:rtf');
INSERT INTO `CATALOGcategories` VALUES ('Image Files', 'jpg:gif:bmp:png:pcx');

--
-- Table structure for table 'CATALOGexcludeip'
--

CREATE TABLE CATALOGexcludeip (
  ip varchar(20) NOT NULL default ''
) TYPE=MyISAM;

--
-- Table structure for table 'CATALOGincludeip'
--

CREATE TABLE CATALOGincludeip (
  ip varchar(20) NOT NULL default ''
) TYPE=MyISAM;

--
-- Table structure for table 'CATALOGiplist'
--

CREATE TABLE CATALOGiplist (
  ip varchar(20) NOT NULL default ''
) TYPE=MyISAM;

--
-- Table structure for table 'CATALOGnetblocks'
--

CREATE TABLE CATALOGnetblocks (
  start varchar(20) NOT NULL default '',
  end varchar(20) NOT NULL default ''
) TYPE=MyISAM;

--
-- Table structure for table 'CATALOGrestrictwords'
--

CREATE TABLE CATALOGrestrictwords (
  word varchar(254) NOT NULL default ''
) TYPE=MyISAM;

--
-- Table structure for table 'CATALOGstats'
--

CREATE TABLE CATALOGstats (
  statistic varchar(254) NOT NULL default '',
  value bigint(20) NOT NULL default '0'
) TYPE=MyISAM;

--
-- Table structure for table 'LOGdenied'
--

CREATE TABLE LOGdenied (
  ip varchar(20) NOT NULL default '',
  time datetime NOT NULL default '0000-00-00 00:00:00',
  searchfor varchar(254) NOT NULL default ''
) TYPE=MyISAM;

--
-- Table structure for table 'SearchIndex'
--

CREATE TABLE SearchIndex (
  computer varchar(40) NOT NULL default '',
  directory varchar(255) NOT NULL default '',
  filename varchar(255) NOT NULL default '',
  size int(10) unsigned NOT NULL default '0',
  workgroup varchar(40) NOT NULL default '',
  KEY directory (directory),
  KEY filename (filename)
) TYPE=MyISAM;

--
-- Table structure for table 'temp'
--

CREATE TABLE temp (
  computer varchar(40) NOT NULL default '',
  directory varchar(255) NOT NULL default '',
  filename varchar(255) NOT NULL default '',
  size int(10) unsigned NOT NULL default '0',
  workgroup varchar(40) NOT NULL default '',
  KEY directory (directory),
  KEY filename (filename)
) TYPE=MyISAM;

































