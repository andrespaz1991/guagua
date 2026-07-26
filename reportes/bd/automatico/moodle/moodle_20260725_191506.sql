-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: moodle
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `mdl_adminpresets`
--

DROP TABLE IF EXISTS `mdl_adminpresets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_adminpresets` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `comments` longtext DEFAULT NULL,
  `site` varchar(255) NOT NULL DEFAULT '',
  `author` varchar(255) DEFAULT NULL,
  `moodleversion` varchar(20) NOT NULL DEFAULT '',
  `moodlerelease` varchar(255) NOT NULL DEFAULT '',
  `iscore` tinyint(1) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timeimported` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Table to store presets data';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_adminpresets`
--

LOCK TABLES `mdl_adminpresets` WRITE;
/*!40000 ALTER TABLE `mdl_adminpresets` DISABLE KEYS */;
INSERT INTO `mdl_adminpresets` VALUES (1,0,'Starter (lanzador)','Moodle con todas las funciones m??s populares, incluidas asignaciones, comentarios, foros, H5P, cuestionarios y seguimiento de finalizaci??n.','http://localhost/moodle','','','',1,1756251970,0),(2,0,'Completo','Todas las caracter??sticas de Starter m??s la herramienta externa (LTI), SCORM, Workshop, Analytics, Badges, Competencias, planes de aprendizaje y mucho m??s.','http://localhost/moodle','','','',2,1756251982,0);
/*!40000 ALTER TABLE `mdl_adminpresets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_adminpresets_app`
--

DROP TABLE IF EXISTS `mdl_adminpresets_app`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_adminpresets_app` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `adminpresetid` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `time` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_admiapp_adm_ix` (`adminpresetid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Applied presets';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_adminpresets_app`
--

LOCK TABLES `mdl_adminpresets_app` WRITE;
/*!40000 ALTER TABLE `mdl_adminpresets_app` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_adminpresets_app` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_adminpresets_app_it`
--

DROP TABLE IF EXISTS `mdl_adminpresets_app_it`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_adminpresets_app_it` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `adminpresetapplyid` bigint(10) NOT NULL,
  `configlogid` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_admiappit_con_ix` (`configlogid`),
  KEY `mdl_admiappit_adm_ix` (`adminpresetapplyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Admin presets applied items. To maintain the relation with c';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_adminpresets_app_it`
--

LOCK TABLES `mdl_adminpresets_app_it` WRITE;
/*!40000 ALTER TABLE `mdl_adminpresets_app_it` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_adminpresets_app_it` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_adminpresets_app_it_a`
--

DROP TABLE IF EXISTS `mdl_adminpresets_app_it_a`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_adminpresets_app_it_a` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `adminpresetapplyid` bigint(10) NOT NULL,
  `configlogid` bigint(10) NOT NULL,
  `itemname` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_admiappita_con_ix` (`configlogid`),
  KEY `mdl_admiappita_adm_ix` (`adminpresetapplyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Attributes of the applied items';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_adminpresets_app_it_a`
--

LOCK TABLES `mdl_adminpresets_app_it_a` WRITE;
/*!40000 ALTER TABLE `mdl_adminpresets_app_it_a` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_adminpresets_app_it_a` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_adminpresets_app_plug`
--

DROP TABLE IF EXISTS `mdl_adminpresets_app_plug`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_adminpresets_app_plug` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `adminpresetapplyid` bigint(10) NOT NULL,
  `plugin` varchar(100) DEFAULT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` smallint(4) NOT NULL DEFAULT 0,
  `oldvalue` smallint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_admiappplug_adm_ix` (`adminpresetapplyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Admin presets plugins applied';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_adminpresets_app_plug`
--

LOCK TABLES `mdl_adminpresets_app_plug` WRITE;
/*!40000 ALTER TABLE `mdl_adminpresets_app_plug` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_adminpresets_app_plug` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_adminpresets_it`
--

DROP TABLE IF EXISTS `mdl_adminpresets_it`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_adminpresets_it` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `adminpresetid` bigint(10) NOT NULL,
  `plugin` varchar(100) DEFAULT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_admiit_adm_ix` (`adminpresetid`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Table to store settings';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_adminpresets_it`
--

LOCK TABLES `mdl_adminpresets_it` WRITE;
/*!40000 ALTER TABLE `mdl_adminpresets_it` DISABLE KEYS */;
INSERT INTO `mdl_adminpresets_it` VALUES (1,1,'none','usecomments','0'),(2,1,'none','usetags','0'),(3,1,'none','enablenotes','0'),(4,1,'none','enableblogs','0'),(5,1,'none','enablebadges','0'),(6,1,'none','enableanalytics','0'),(7,1,'core_competency','enabled','0'),(8,1,'core_competency','pushcourseratingstouserplans','0'),(9,1,'tool_dataprivacy','showdataretentionsummary','0'),(10,1,'none','forum_maxattachments','3'),(11,1,'none','guestloginbutton','0'),(12,1,'none','activitychoosertabmode','1'),(13,2,'none','usecomments','1'),(14,2,'none','usetags','1'),(15,2,'none','enablenotes','1'),(16,2,'none','enableblogs','1'),(17,2,'none','enablebadges','1'),(18,2,'none','enableanalytics','1'),(19,2,'core_competency','enabled','1'),(20,2,'core_competency','pushcourseratingstouserplans','1'),(21,2,'tool_dataprivacy','showdataretentionsummary','1'),(22,2,'none','forum_maxattachments','9'),(23,2,'none','guestloginbutton','1'),(24,2,'none','activitychoosertabmode','0');
/*!40000 ALTER TABLE `mdl_adminpresets_it` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_adminpresets_it_a`
--

DROP TABLE IF EXISTS `mdl_adminpresets_it_a`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_adminpresets_it_a` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `itemid` bigint(10) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_admiita_ite_ix` (`itemid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Admin presets items attributes. For settings with attributes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_adminpresets_it_a`
--

LOCK TABLES `mdl_adminpresets_it_a` WRITE;
/*!40000 ALTER TABLE `mdl_adminpresets_it_a` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_adminpresets_it_a` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_adminpresets_plug`
--

DROP TABLE IF EXISTS `mdl_adminpresets_plug`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_adminpresets_plug` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `adminpresetid` bigint(10) NOT NULL,
  `plugin` varchar(100) DEFAULT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `enabled` smallint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_admiplug_adm_ix` (`adminpresetid`)
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Admin presets plugins status, to store information about whe';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_adminpresets_plug`
--

LOCK TABLES `mdl_adminpresets_plug` WRITE;
/*!40000 ALTER TABLE `mdl_adminpresets_plug` DISABLE KEYS */;
INSERT INTO `mdl_adminpresets_plug` VALUES (1,1,'mod','chat',0),(2,1,'mod','data',0),(3,1,'mod','lti',0),(4,1,'mod','imscp',0),(5,1,'mod','lesson',0),(6,1,'mod','scorm',0),(7,1,'mod','survey',0),(8,1,'mod','wiki',0),(9,1,'mod','workshop',0),(10,1,'availability','grouping',0),(11,1,'availability','profile',0),(12,1,'block','activity_modules',0),(13,1,'block','blog_menu',0),(14,1,'block','blog_tags',0),(15,1,'block','comments',0),(16,1,'block','completionstatus',0),(17,1,'block','course_summary',0),(18,1,'block','course_list',0),(19,1,'block','tag_flickr',0),(20,1,'block','globalsearch',0),(21,1,'block','badges',0),(22,1,'block','lp',0),(23,1,'block','myprofile',0),(24,1,'block','login',0),(25,1,'block','site_main_menu',0),(26,1,'block','mentees',0),(27,1,'block','mnet_hosts',0),(28,1,'block','private_files',0),(29,1,'block','blog_recent',0),(30,1,'block','rss_client',0),(31,1,'block','search_forums',0),(32,1,'block','section_links',0),(33,1,'block','selfcompletion',0),(34,1,'block','social_activities',0),(35,1,'block','tags',0),(36,1,'block','tag_youtube',0),(37,1,'block','feedback',0),(38,1,'block','online_users',0),(39,1,'block','recentlyaccessedcourses',0),(40,1,'block','starredcourses',0),(41,1,'format','social',0),(42,1,'dataformat','json',0),(43,1,'enrol','cohort',0),(44,1,'enrol','guest',0),(45,1,'filter','mathjaxloader',-9999),(46,1,'filter','activitynames',-9999),(47,1,'qbehaviour','adaptivenopenalty',0),(48,1,'qbehaviour','deferredcbm',0),(49,1,'qbehaviour','immediatecbm',0),(50,1,'qtype','calculated',0),(51,1,'qtype','calculatedmulti',0),(52,1,'qtype','calculatedsimple',0),(53,1,'qtype','ddmarker',0),(54,1,'qtype','ddimageortext',0),(55,1,'qtype','multianswer',0),(56,1,'qtype','numerical',0),(57,1,'qtype','randomsamatch',0),(58,1,'repository','local',0),(59,1,'repository','url',0),(60,1,'repository','wikimedia',0),(61,1,'editor','tinymce',0),(62,2,'mod','chat',1),(63,2,'mod','data',1),(64,2,'mod','lti',1),(65,2,'mod','imscp',1),(66,2,'mod','lesson',1),(67,2,'mod','scorm',1),(68,2,'mod','survey',1),(69,2,'mod','wiki',1),(70,2,'mod','workshop',1),(71,2,'availability','grouping',1),(72,2,'availability','profile',1),(73,2,'block','activity_modules',1),(74,2,'block','blog_menu',1),(75,2,'block','blog_tags',1),(76,2,'block','comments',1),(77,2,'block','completionstatus',1),(78,2,'block','course_list',1),(79,2,'block','tag_flickr',1),(80,2,'block','globalsearch',1),(81,2,'block','badges',1),(82,2,'block','lp',1),(83,2,'block','myprofile',1),(84,2,'block','login',1),(85,2,'block','site_main_menu',1),(86,2,'block','mentees',1),(87,2,'block','mnet_hosts',1),(88,2,'block','private_files',1),(89,2,'block','blog_recent',1),(90,2,'block','search_forums',1),(91,2,'block','section_links',1),(92,2,'block','social_activities',1),(93,2,'block','tags',1),(94,2,'block','online_users',1),(95,2,'block','recentlyaccessedcourses',1),(96,2,'block','starredcourses',1),(97,2,'format','social',1),(98,2,'dataformat','json',1),(99,2,'enrol','cohort',1),(100,2,'enrol','guest',1),(101,2,'filter','mathjaxloader',1),(102,2,'filter','activitynames',1),(103,2,'qbehaviour','adaptivenopenalty',1),(104,2,'qbehaviour','deferredcbm',1),(105,2,'qbehaviour','immediatecbm',1),(106,2,'qtype','calculated',1),(107,2,'qtype','calculatedmulti',1),(108,2,'qtype','calculatedsimple',1),(109,2,'qtype','ddmarker',1),(110,2,'qtype','ddimageortext',1),(111,2,'qtype','multianswer',1),(112,2,'qtype','numerical',1),(113,2,'qtype','randomsamatch',1),(114,2,'repository','local',1),(115,2,'repository','url',1),(116,2,'repository','wikimedia',1),(117,2,'editor','tinymce',1);
/*!40000 ALTER TABLE `mdl_adminpresets_plug` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_analytics_indicator_calc`
--

DROP TABLE IF EXISTS `mdl_analytics_indicator_calc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_analytics_indicator_calc` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `starttime` bigint(10) NOT NULL,
  `endtime` bigint(10) NOT NULL,
  `contextid` bigint(10) NOT NULL,
  `sampleorigin` varchar(255) NOT NULL DEFAULT '',
  `sampleid` bigint(10) NOT NULL,
  `indicator` varchar(255) NOT NULL DEFAULT '',
  `value` decimal(10,2) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_analindicalc_staendcon_ix` (`starttime`,`endtime`,`contextid`),
  KEY `mdl_analindicalc_con_ix` (`contextid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stored indicator calculations';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_analytics_indicator_calc`
--

LOCK TABLES `mdl_analytics_indicator_calc` WRITE;
/*!40000 ALTER TABLE `mdl_analytics_indicator_calc` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_analytics_indicator_calc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_analytics_models`
--

DROP TABLE IF EXISTS `mdl_analytics_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_analytics_models` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `trained` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(1333) DEFAULT NULL,
  `target` varchar(255) NOT NULL DEFAULT '',
  `indicators` longtext NOT NULL,
  `timesplitting` varchar(255) DEFAULT NULL,
  `predictionsprocessor` varchar(255) DEFAULT NULL,
  `version` bigint(10) NOT NULL,
  `contextids` longtext DEFAULT NULL,
  `timecreated` bigint(10) DEFAULT NULL,
  `timemodified` bigint(10) NOT NULL,
  `usermodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_analmode_enatra_ix` (`enabled`,`trained`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Analytic models.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_analytics_models`
--

LOCK TABLES `mdl_analytics_models` WRITE;
/*!40000 ALTER TABLE `mdl_analytics_models` DISABLE KEYS */;
INSERT INTO `mdl_analytics_models` VALUES (1,0,0,NULL,'\\core_course\\analytics\\target\\course_dropout','[\"\\\\core\\\\analytics\\\\indicator\\\\any_access_after_end\",\"\\\\core\\\\analytics\\\\indicator\\\\any_access_before_start\",\"\\\\core\\\\analytics\\\\indicator\\\\any_write_action_in_course\",\"\\\\core\\\\analytics\\\\indicator\\\\read_actions\",\"\\\\core_course\\\\analytics\\\\indicator\\\\completion_enabled\",\"\\\\core_course\\\\analytics\\\\indicator\\\\potential_cognitive_depth\",\"\\\\core_course\\\\analytics\\\\indicator\\\\potential_social_breadth\",\"\\\\mod_assign\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_assign\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_book\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_book\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_chat\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_chat\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_choice\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_choice\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_data\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_data\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_feedback\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_feedback\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_folder\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_folder\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_forum\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_forum\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_glossary\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_glossary\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_imscp\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_imscp\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_label\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_label\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_lesson\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_lesson\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_lti\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_lti\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_page\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_page\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_quiz\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_quiz\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_resource\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_resource\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_scorm\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_scorm\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_survey\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_survey\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_url\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_url\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_wiki\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_wiki\\\\analytics\\\\indicator\\\\social_breadth\",\"\\\\mod_workshop\\\\analytics\\\\indicator\\\\cognitive_depth\",\"\\\\mod_workshop\\\\analytics\\\\indicator\\\\social_breadth\"]',NULL,NULL,1756252056,NULL,1756252056,1756252056,0),(2,1,1,NULL,'\\core_course\\analytics\\target\\no_teaching','[\"\\\\core_course\\\\analytics\\\\indicator\\\\no_teacher\",\"\\\\core_course\\\\analytics\\\\indicator\\\\no_student\"]','\\core\\analytics\\time_splitting\\single_range',NULL,1756252056,NULL,1756252056,1756252056,0),(3,1,1,NULL,'\\core_user\\analytics\\target\\upcoming_activities_due','[\"\\\\core_course\\\\analytics\\\\indicator\\\\activities_due\"]','\\core\\analytics\\time_splitting\\upcoming_week',NULL,1756252056,NULL,1756252056,1756252057,0),(4,1,1,NULL,'\\core_course\\analytics\\target\\no_access_since_course_start','[\"\\\\core\\\\analytics\\\\indicator\\\\any_course_access\"]','\\core\\analytics\\time_splitting\\one_month_after_start',NULL,1756252057,NULL,1756252057,1756252057,0),(5,1,1,NULL,'\\core_course\\analytics\\target\\no_recent_accesses','[\"\\\\core\\\\analytics\\\\indicator\\\\any_course_access\"]','\\core\\analytics\\time_splitting\\past_month',NULL,1756252057,NULL,1756252057,1756252057,0);
/*!40000 ALTER TABLE `mdl_analytics_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_analytics_models_log`
--

DROP TABLE IF EXISTS `mdl_analytics_models_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_analytics_models_log` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `modelid` bigint(10) NOT NULL,
  `version` bigint(10) NOT NULL,
  `evaluationmode` varchar(50) NOT NULL DEFAULT '',
  `target` varchar(255) NOT NULL DEFAULT '',
  `indicators` longtext NOT NULL,
  `timesplitting` varchar(255) DEFAULT NULL,
  `score` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `info` longtext DEFAULT NULL,
  `dir` longtext NOT NULL,
  `timecreated` bigint(10) NOT NULL,
  `usermodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_analmodelog_mod_ix` (`modelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Analytic models changes during evaluation.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_analytics_models_log`
--

LOCK TABLES `mdl_analytics_models_log` WRITE;
/*!40000 ALTER TABLE `mdl_analytics_models_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_analytics_models_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_analytics_predict_samples`
--

DROP TABLE IF EXISTS `mdl_analytics_predict_samples`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_analytics_predict_samples` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `modelid` bigint(10) NOT NULL,
  `analysableid` bigint(10) NOT NULL,
  `timesplitting` varchar(255) NOT NULL DEFAULT '',
  `rangeindex` bigint(10) NOT NULL,
  `sampleids` longtext NOT NULL,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_analpredsamp_modanatimr_ix` (`modelid`,`analysableid`,`timesplitting`,`rangeindex`),
  KEY `mdl_analpredsamp_mod_ix` (`modelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Samples already used for predictions.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_analytics_predict_samples`
--

LOCK TABLES `mdl_analytics_predict_samples` WRITE;
/*!40000 ALTER TABLE `mdl_analytics_predict_samples` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_analytics_predict_samples` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_analytics_prediction_actions`
--

DROP TABLE IF EXISTS `mdl_analytics_prediction_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_analytics_prediction_actions` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `predictionid` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `actionname` varchar(255) NOT NULL DEFAULT '',
  `timecreated` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_analpredacti_preuseact_ix` (`predictionid`,`userid`,`actionname`),
  KEY `mdl_analpredacti_pre_ix` (`predictionid`),
  KEY `mdl_analpredacti_use_ix` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Register of user actions over predictions.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_analytics_prediction_actions`
--

LOCK TABLES `mdl_analytics_prediction_actions` WRITE;
/*!40000 ALTER TABLE `mdl_analytics_prediction_actions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_analytics_prediction_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_analytics_predictions`
--

DROP TABLE IF EXISTS `mdl_analytics_predictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_analytics_predictions` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `modelid` bigint(10) NOT NULL,
  `contextid` bigint(10) NOT NULL,
  `sampleid` bigint(10) NOT NULL,
  `rangeindex` mediumint(5) NOT NULL,
  `prediction` decimal(10,2) NOT NULL,
  `predictionscore` decimal(10,5) NOT NULL,
  `calculations` longtext NOT NULL,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timestart` bigint(10) DEFAULT NULL,
  `timeend` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_analpred_modcon_ix` (`modelid`,`contextid`),
  KEY `mdl_analpred_mod_ix` (`modelid`),
  KEY `mdl_analpred_con_ix` (`contextid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Predictions';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_analytics_predictions`
--

LOCK TABLES `mdl_analytics_predictions` WRITE;
/*!40000 ALTER TABLE `mdl_analytics_predictions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_analytics_predictions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_analytics_train_samples`
--

DROP TABLE IF EXISTS `mdl_analytics_train_samples`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_analytics_train_samples` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `modelid` bigint(10) NOT NULL,
  `analysableid` bigint(10) NOT NULL,
  `timesplitting` varchar(255) NOT NULL DEFAULT '',
  `sampleids` longtext NOT NULL,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_analtraisamp_modanatim_ix` (`modelid`,`analysableid`,`timesplitting`),
  KEY `mdl_analtraisamp_mod_ix` (`modelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Samples used for training';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_analytics_train_samples`
--

LOCK TABLES `mdl_analytics_train_samples` WRITE;
/*!40000 ALTER TABLE `mdl_analytics_train_samples` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_analytics_train_samples` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_analytics_used_analysables`
--

DROP TABLE IF EXISTS `mdl_analytics_used_analysables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_analytics_used_analysables` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `modelid` bigint(10) NOT NULL,
  `action` varchar(50) NOT NULL DEFAULT '',
  `analysableid` bigint(10) NOT NULL,
  `firstanalysis` bigint(10) NOT NULL,
  `timeanalysed` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_analusedanal_modact_ix` (`modelid`,`action`),
  KEY `mdl_analusedanal_ana_ix` (`analysableid`),
  KEY `mdl_analusedanal_mod_ix` (`modelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='List of analysables used by each model';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_analytics_used_analysables`
--

LOCK TABLES `mdl_analytics_used_analysables` WRITE;
/*!40000 ALTER TABLE `mdl_analytics_used_analysables` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_analytics_used_analysables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_analytics_used_files`
--

DROP TABLE IF EXISTS `mdl_analytics_used_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_analytics_used_files` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `modelid` bigint(10) NOT NULL DEFAULT 0,
  `fileid` bigint(10) NOT NULL DEFAULT 0,
  `action` varchar(50) NOT NULL DEFAULT '',
  `time` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_analusedfile_modactfil_ix` (`modelid`,`action`,`fileid`),
  KEY `mdl_analusedfile_mod_ix` (`modelid`),
  KEY `mdl_analusedfile_fil_ix` (`fileid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Files that have already been used for training and predictio';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_analytics_used_files`
--

LOCK TABLES `mdl_analytics_used_files` WRITE;
/*!40000 ALTER TABLE `mdl_analytics_used_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_analytics_used_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assign`
--

DROP TABLE IF EXISTS `mdl_assign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assign` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `intro` longtext NOT NULL,
  `introformat` smallint(4) NOT NULL DEFAULT 0,
  `alwaysshowdescription` tinyint(2) NOT NULL DEFAULT 0,
  `nosubmissions` tinyint(2) NOT NULL DEFAULT 0,
  `submissiondrafts` tinyint(2) NOT NULL DEFAULT 0,
  `sendnotifications` tinyint(2) NOT NULL DEFAULT 0,
  `sendlatenotifications` tinyint(2) NOT NULL DEFAULT 0,
  `duedate` bigint(10) NOT NULL DEFAULT 0,
  `allowsubmissionsfromdate` bigint(10) NOT NULL DEFAULT 0,
  `grade` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `requiresubmissionstatement` tinyint(2) NOT NULL DEFAULT 0,
  `completionsubmit` tinyint(2) NOT NULL DEFAULT 0,
  `cutoffdate` bigint(10) NOT NULL DEFAULT 0,
  `gradingduedate` bigint(10) NOT NULL DEFAULT 0,
  `teamsubmission` tinyint(2) NOT NULL DEFAULT 0,
  `requireallteammemberssubmit` tinyint(2) NOT NULL DEFAULT 0,
  `teamsubmissiongroupingid` bigint(10) NOT NULL DEFAULT 0,
  `blindmarking` tinyint(2) NOT NULL DEFAULT 0,
  `hidegrader` tinyint(2) NOT NULL DEFAULT 0,
  `revealidentities` tinyint(2) NOT NULL DEFAULT 0,
  `attemptreopenmethod` varchar(10) NOT NULL DEFAULT 'none',
  `maxattempts` mediumint(6) NOT NULL DEFAULT -1,
  `markingworkflow` tinyint(2) NOT NULL DEFAULT 0,
  `markingallocation` tinyint(2) NOT NULL DEFAULT 0,
  `sendstudentnotifications` tinyint(2) NOT NULL DEFAULT 1,
  `preventsubmissionnotingroup` tinyint(2) NOT NULL DEFAULT 0,
  `activity` longtext DEFAULT NULL,
  `activityformat` smallint(4) NOT NULL DEFAULT 0,
  `timelimit` bigint(10) NOT NULL DEFAULT 0,
  `submissionattachments` tinyint(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_assi_cou_ix` (`course`),
  KEY `mdl_assi_tea_ix` (`teamsubmissiongroupingid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='This table saves information about an instance of mod_assign';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assign`
--

LOCK TABLES `mdl_assign` WRITE;
/*!40000 ALTER TABLE `mdl_assign` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assign_grades`
--

DROP TABLE IF EXISTS `mdl_assign_grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assign_grades` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `assignment` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `grader` bigint(10) NOT NULL DEFAULT 0,
  `grade` decimal(10,5) DEFAULT 0.00000,
  `attemptnumber` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_assigrad_assuseatt_uix` (`assignment`,`userid`,`attemptnumber`),
  KEY `mdl_assigrad_use_ix` (`userid`),
  KEY `mdl_assigrad_att_ix` (`attemptnumber`),
  KEY `mdl_assigrad_ass_ix` (`assignment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Grading information about a single assignment submission.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assign_grades`
--

LOCK TABLES `mdl_assign_grades` WRITE;
/*!40000 ALTER TABLE `mdl_assign_grades` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assign_grades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assign_overrides`
--

DROP TABLE IF EXISTS `mdl_assign_overrides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assign_overrides` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `assignid` bigint(10) NOT NULL DEFAULT 0,
  `groupid` bigint(10) DEFAULT NULL,
  `userid` bigint(10) DEFAULT NULL,
  `sortorder` bigint(10) DEFAULT NULL,
  `allowsubmissionsfromdate` bigint(10) DEFAULT NULL,
  `duedate` bigint(10) DEFAULT NULL,
  `cutoffdate` bigint(10) DEFAULT NULL,
  `timelimit` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_assiover_ass_ix` (`assignid`),
  KEY `mdl_assiover_gro_ix` (`groupid`),
  KEY `mdl_assiover_use_ix` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='The overrides to assign settings.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assign_overrides`
--

LOCK TABLES `mdl_assign_overrides` WRITE;
/*!40000 ALTER TABLE `mdl_assign_overrides` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assign_overrides` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assign_plugin_config`
--

DROP TABLE IF EXISTS `mdl_assign_plugin_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assign_plugin_config` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `assignment` bigint(10) NOT NULL DEFAULT 0,
  `plugin` varchar(28) NOT NULL DEFAULT '',
  `subtype` varchar(28) NOT NULL DEFAULT '',
  `name` varchar(28) NOT NULL DEFAULT '',
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_assiplugconf_plu_ix` (`plugin`),
  KEY `mdl_assiplugconf_sub_ix` (`subtype`),
  KEY `mdl_assiplugconf_nam_ix` (`name`),
  KEY `mdl_assiplugconf_ass_ix` (`assignment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Config data for an instance of a plugin in an assignment.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assign_plugin_config`
--

LOCK TABLES `mdl_assign_plugin_config` WRITE;
/*!40000 ALTER TABLE `mdl_assign_plugin_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assign_plugin_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assign_submission`
--

DROP TABLE IF EXISTS `mdl_assign_submission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assign_submission` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `assignment` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `timestarted` bigint(10) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `groupid` bigint(10) NOT NULL DEFAULT 0,
  `attemptnumber` bigint(10) NOT NULL DEFAULT 0,
  `latest` tinyint(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_assisubm_assusegroatt_uix` (`assignment`,`userid`,`groupid`,`attemptnumber`),
  KEY `mdl_assisubm_use_ix` (`userid`),
  KEY `mdl_assisubm_att_ix` (`attemptnumber`),
  KEY `mdl_assisubm_assusegrolat_ix` (`assignment`,`userid`,`groupid`,`latest`),
  KEY `mdl_assisubm_ass_ix` (`assignment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='This table keeps information about student interactions with';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assign_submission`
--

LOCK TABLES `mdl_assign_submission` WRITE;
/*!40000 ALTER TABLE `mdl_assign_submission` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assign_submission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assign_user_flags`
--

DROP TABLE IF EXISTS `mdl_assign_user_flags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assign_user_flags` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `assignment` bigint(10) NOT NULL DEFAULT 0,
  `locked` bigint(10) NOT NULL DEFAULT 0,
  `mailed` smallint(4) NOT NULL DEFAULT 0,
  `extensionduedate` bigint(10) NOT NULL DEFAULT 0,
  `workflowstate` varchar(20) DEFAULT NULL,
  `allocatedmarker` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_assiuserflag_mai_ix` (`mailed`),
  KEY `mdl_assiuserflag_use_ix` (`userid`),
  KEY `mdl_assiuserflag_ass_ix` (`assignment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='List of flags that can be set for a single user in a single ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assign_user_flags`
--

LOCK TABLES `mdl_assign_user_flags` WRITE;
/*!40000 ALTER TABLE `mdl_assign_user_flags` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assign_user_flags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assign_user_mapping`
--

DROP TABLE IF EXISTS `mdl_assign_user_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assign_user_mapping` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `assignment` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_assiusermapp_ass_ix` (`assignment`),
  KEY `mdl_assiusermapp_use_ix` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Map an assignment specific id number to a user';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assign_user_mapping`
--

LOCK TABLES `mdl_assign_user_mapping` WRITE;
/*!40000 ALTER TABLE `mdl_assign_user_mapping` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assign_user_mapping` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assignfeedback_comments`
--

DROP TABLE IF EXISTS `mdl_assignfeedback_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assignfeedback_comments` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `assignment` bigint(10) NOT NULL DEFAULT 0,
  `grade` bigint(10) NOT NULL DEFAULT 0,
  `commenttext` longtext DEFAULT NULL,
  `commentformat` smallint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_assicomm_ass_ix` (`assignment`),
  KEY `mdl_assicomm_gra_ix` (`grade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Text feedback for submitted assignments';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assignfeedback_comments`
--

LOCK TABLES `mdl_assignfeedback_comments` WRITE;
/*!40000 ALTER TABLE `mdl_assignfeedback_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assignfeedback_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assignfeedback_editpdf_annot`
--

DROP TABLE IF EXISTS `mdl_assignfeedback_editpdf_annot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assignfeedback_editpdf_annot` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `gradeid` bigint(10) NOT NULL DEFAULT 0,
  `pageno` bigint(10) NOT NULL DEFAULT 0,
  `x` bigint(10) DEFAULT 0,
  `y` bigint(10) DEFAULT 0,
  `endx` bigint(10) DEFAULT 0,
  `endy` bigint(10) DEFAULT 0,
  `path` longtext DEFAULT NULL,
  `type` varchar(10) DEFAULT 'line',
  `colour` varchar(10) DEFAULT 'black',
  `draft` tinyint(2) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `mdl_assieditanno_grapag_ix` (`gradeid`,`pageno`),
  KEY `mdl_assieditanno_gra_ix` (`gradeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='stores annotations added to pdfs submitted by students';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assignfeedback_editpdf_annot`
--

LOCK TABLES `mdl_assignfeedback_editpdf_annot` WRITE;
/*!40000 ALTER TABLE `mdl_assignfeedback_editpdf_annot` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assignfeedback_editpdf_annot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assignfeedback_editpdf_cmnt`
--

DROP TABLE IF EXISTS `mdl_assignfeedback_editpdf_cmnt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assignfeedback_editpdf_cmnt` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `gradeid` bigint(10) NOT NULL DEFAULT 0,
  `x` bigint(10) DEFAULT 0,
  `y` bigint(10) DEFAULT 0,
  `width` bigint(10) DEFAULT 120,
  `rawtext` longtext DEFAULT NULL,
  `pageno` bigint(10) NOT NULL DEFAULT 0,
  `colour` varchar(10) DEFAULT 'black',
  `draft` tinyint(2) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `mdl_assieditcmnt_grapag_ix` (`gradeid`,`pageno`),
  KEY `mdl_assieditcmnt_gra_ix` (`gradeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores comments added to pdfs';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assignfeedback_editpdf_cmnt`
--

LOCK TABLES `mdl_assignfeedback_editpdf_cmnt` WRITE;
/*!40000 ALTER TABLE `mdl_assignfeedback_editpdf_cmnt` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assignfeedback_editpdf_cmnt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assignfeedback_editpdf_queue`
--

DROP TABLE IF EXISTS `mdl_assignfeedback_editpdf_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assignfeedback_editpdf_queue` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `submissionid` bigint(10) NOT NULL,
  `submissionattempt` bigint(10) NOT NULL,
  `attemptedconversions` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_assieditqueu_subsub_uix` (`submissionid`,`submissionattempt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Queue for processing.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assignfeedback_editpdf_queue`
--

LOCK TABLES `mdl_assignfeedback_editpdf_queue` WRITE;
/*!40000 ALTER TABLE `mdl_assignfeedback_editpdf_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assignfeedback_editpdf_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assignfeedback_editpdf_quick`
--

DROP TABLE IF EXISTS `mdl_assignfeedback_editpdf_quick`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assignfeedback_editpdf_quick` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `rawtext` longtext NOT NULL,
  `width` bigint(10) NOT NULL DEFAULT 120,
  `colour` varchar(10) DEFAULT 'yellow',
  PRIMARY KEY (`id`),
  KEY `mdl_assieditquic_use_ix` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores teacher specified quicklist comments';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assignfeedback_editpdf_quick`
--

LOCK TABLES `mdl_assignfeedback_editpdf_quick` WRITE;
/*!40000 ALTER TABLE `mdl_assignfeedback_editpdf_quick` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assignfeedback_editpdf_quick` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assignfeedback_editpdf_rot`
--

DROP TABLE IF EXISTS `mdl_assignfeedback_editpdf_rot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assignfeedback_editpdf_rot` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `gradeid` bigint(10) NOT NULL DEFAULT 0,
  `pageno` bigint(10) NOT NULL DEFAULT 0,
  `pathnamehash` longtext NOT NULL,
  `isrotated` tinyint(1) NOT NULL DEFAULT 0,
  `degree` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_assieditrot_grapag_uix` (`gradeid`,`pageno`),
  KEY `mdl_assieditrot_gra_ix` (`gradeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores rotation information of a page.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assignfeedback_editpdf_rot`
--

LOCK TABLES `mdl_assignfeedback_editpdf_rot` WRITE;
/*!40000 ALTER TABLE `mdl_assignfeedback_editpdf_rot` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assignfeedback_editpdf_rot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assignfeedback_file`
--

DROP TABLE IF EXISTS `mdl_assignfeedback_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assignfeedback_file` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `assignment` bigint(10) NOT NULL DEFAULT 0,
  `grade` bigint(10) NOT NULL DEFAULT 0,
  `numfiles` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_assifile_ass2_ix` (`assignment`),
  KEY `mdl_assifile_gra_ix` (`grade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores info about the number of files submitted by a student';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assignfeedback_file`
--

LOCK TABLES `mdl_assignfeedback_file` WRITE;
/*!40000 ALTER TABLE `mdl_assignfeedback_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assignfeedback_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assignment`
--

DROP TABLE IF EXISTS `mdl_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assignment` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `intro` longtext NOT NULL,
  `introformat` smallint(4) NOT NULL DEFAULT 0,
  `assignmenttype` varchar(50) NOT NULL DEFAULT '',
  `resubmit` tinyint(2) NOT NULL DEFAULT 0,
  `preventlate` tinyint(2) NOT NULL DEFAULT 0,
  `emailteachers` tinyint(2) NOT NULL DEFAULT 0,
  `var1` bigint(10) DEFAULT 0,
  `var2` bigint(10) DEFAULT 0,
  `var3` bigint(10) DEFAULT 0,
  `var4` bigint(10) DEFAULT 0,
  `var5` bigint(10) DEFAULT 0,
  `maxbytes` bigint(10) NOT NULL DEFAULT 100000,
  `timedue` bigint(10) NOT NULL DEFAULT 0,
  `timeavailable` bigint(10) NOT NULL DEFAULT 0,
  `grade` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_assi_cou2_ix` (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Defines assignments';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assignment`
--

LOCK TABLES `mdl_assignment` WRITE;
/*!40000 ALTER TABLE `mdl_assignment` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assignment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assignment_submissions`
--

DROP TABLE IF EXISTS `mdl_assignment_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assignment_submissions` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `assignment` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `numfiles` bigint(10) NOT NULL DEFAULT 0,
  `data1` longtext DEFAULT NULL,
  `data2` longtext DEFAULT NULL,
  `grade` bigint(11) NOT NULL DEFAULT 0,
  `submissioncomment` longtext NOT NULL,
  `format` smallint(4) NOT NULL DEFAULT 0,
  `teacher` bigint(10) NOT NULL DEFAULT 0,
  `timemarked` bigint(10) NOT NULL DEFAULT 0,
  `mailed` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_assisubm_use2_ix` (`userid`),
  KEY `mdl_assisubm_mai_ix` (`mailed`),
  KEY `mdl_assisubm_tim_ix` (`timemarked`),
  KEY `mdl_assisubm_ass2_ix` (`assignment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Info about submitted assignments';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assignment_submissions`
--

LOCK TABLES `mdl_assignment_submissions` WRITE;
/*!40000 ALTER TABLE `mdl_assignment_submissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assignment_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assignment_upgrade`
--

DROP TABLE IF EXISTS `mdl_assignment_upgrade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assignment_upgrade` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `oldcmid` bigint(10) NOT NULL DEFAULT 0,
  `oldinstance` bigint(10) NOT NULL DEFAULT 0,
  `newcmid` bigint(10) NOT NULL DEFAULT 0,
  `newinstance` bigint(10) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_assiupgr_old_ix` (`oldcmid`),
  KEY `mdl_assiupgr_old2_ix` (`oldinstance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Info about upgraded assignments';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assignment_upgrade`
--

LOCK TABLES `mdl_assignment_upgrade` WRITE;
/*!40000 ALTER TABLE `mdl_assignment_upgrade` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assignment_upgrade` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assignsubmission_file`
--

DROP TABLE IF EXISTS `mdl_assignsubmission_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assignsubmission_file` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `assignment` bigint(10) NOT NULL DEFAULT 0,
  `submission` bigint(10) NOT NULL DEFAULT 0,
  `numfiles` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_assifile_ass_ix` (`assignment`),
  KEY `mdl_assifile_sub_ix` (`submission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Info about file submissions for assignments';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assignsubmission_file`
--

LOCK TABLES `mdl_assignsubmission_file` WRITE;
/*!40000 ALTER TABLE `mdl_assignsubmission_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assignsubmission_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_assignsubmission_onlinetext`
--

DROP TABLE IF EXISTS `mdl_assignsubmission_onlinetext`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_assignsubmission_onlinetext` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `assignment` bigint(10) NOT NULL DEFAULT 0,
  `submission` bigint(10) NOT NULL DEFAULT 0,
  `onlinetext` longtext DEFAULT NULL,
  `onlineformat` smallint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_assionli_ass_ix` (`assignment`),
  KEY `mdl_assionli_sub_ix` (`submission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Info about onlinetext submission';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_assignsubmission_onlinetext`
--

LOCK TABLES `mdl_assignsubmission_onlinetext` WRITE;
/*!40000 ALTER TABLE `mdl_assignsubmission_onlinetext` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_assignsubmission_onlinetext` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_attendance`
--

DROP TABLE IF EXISTS `mdl_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_attendance` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) DEFAULT NULL,
  `grade` bigint(10) NOT NULL DEFAULT 100,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `intro` longtext DEFAULT NULL,
  `introformat` smallint(4) NOT NULL DEFAULT 0,
  `subnet` varchar(255) DEFAULT NULL,
  `sessiondetailspos` varchar(5) NOT NULL DEFAULT 'left',
  `showsessiondetails` tinyint(1) NOT NULL DEFAULT 1,
  `showextrauserdetails` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `mdl_atte_cou_ix` (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Attendance module table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_attendance`
--

LOCK TABLES `mdl_attendance` WRITE;
/*!40000 ALTER TABLE `mdl_attendance` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_attendance_log`
--

DROP TABLE IF EXISTS `mdl_attendance_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_attendance_log` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `sessionid` bigint(10) NOT NULL DEFAULT 0,
  `studentid` bigint(10) NOT NULL DEFAULT 0,
  `statusid` bigint(10) NOT NULL DEFAULT 0,
  `statusset` varchar(1333) DEFAULT NULL,
  `timetaken` bigint(10) NOT NULL DEFAULT 0,
  `takenby` bigint(10) NOT NULL DEFAULT 0,
  `remarks` varchar(1333) DEFAULT NULL,
  `ipaddress` varchar(45) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `mdl_attelog_ses_ix` (`sessionid`),
  KEY `mdl_attelog_stu_ix` (`studentid`),
  KEY `mdl_attelog_sta_ix` (`statusid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='attendance_log table retrofitted from MySQL';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_attendance_log`
--

LOCK TABLES `mdl_attendance_log` WRITE;
/*!40000 ALTER TABLE `mdl_attendance_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_attendance_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_attendance_rotate_passwords`
--

DROP TABLE IF EXISTS `mdl_attendance_rotate_passwords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_attendance_rotate_passwords` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `attendanceid` bigint(10) NOT NULL,
  `password` varchar(20) NOT NULL DEFAULT '',
  `expirytime` bigint(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Table to hold temporary passwords for rotate QR code feature';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_attendance_rotate_passwords`
--

LOCK TABLES `mdl_attendance_rotate_passwords` WRITE;
/*!40000 ALTER TABLE `mdl_attendance_rotate_passwords` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_attendance_rotate_passwords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_attendance_sessions`
--

DROP TABLE IF EXISTS `mdl_attendance_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_attendance_sessions` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `attendanceid` bigint(10) NOT NULL DEFAULT 0,
  `groupid` bigint(10) NOT NULL DEFAULT 0,
  `sessdate` bigint(10) NOT NULL DEFAULT 0,
  `duration` bigint(10) NOT NULL DEFAULT 0,
  `lasttaken` bigint(10) DEFAULT NULL,
  `lasttakenby` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) DEFAULT NULL,
  `description` longtext NOT NULL,
  `descriptionformat` tinyint(2) NOT NULL DEFAULT 0,
  `studentscanmark` tinyint(1) NOT NULL DEFAULT 0,
  `studentsearlyopentime` bigint(10) NOT NULL DEFAULT 0,
  `autoassignstatus` tinyint(1) NOT NULL DEFAULT 0,
  `studentpassword` varchar(50) DEFAULT '',
  `subnet` varchar(255) DEFAULT NULL,
  `automark` tinyint(1) NOT NULL DEFAULT 0,
  `automarkcompleted` tinyint(1) NOT NULL DEFAULT 0,
  `statusset` mediumint(5) NOT NULL DEFAULT 0,
  `absenteereport` tinyint(1) NOT NULL DEFAULT 1,
  `preventsharedip` tinyint(1) NOT NULL DEFAULT 0,
  `preventsharediptime` bigint(10) DEFAULT NULL,
  `caleventid` bigint(10) NOT NULL DEFAULT 0,
  `calendarevent` tinyint(1) NOT NULL DEFAULT 1,
  `includeqrcode` tinyint(1) NOT NULL DEFAULT 0,
  `rotateqrcode` tinyint(1) NOT NULL DEFAULT 0,
  `rotateqrcodesecret` varchar(10) DEFAULT NULL,
  `automarkcmid` bigint(10) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_attesess_att_ix` (`attendanceid`),
  KEY `mdl_attesess_gro_ix` (`groupid`),
  KEY `mdl_attesess_ses_ix` (`sessdate`),
  KEY `mdl_attesess_cal_ix` (`caleventid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='attendance_sessions table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_attendance_sessions`
--

LOCK TABLES `mdl_attendance_sessions` WRITE;
/*!40000 ALTER TABLE `mdl_attendance_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_attendance_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_attendance_statuses`
--

DROP TABLE IF EXISTS `mdl_attendance_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_attendance_statuses` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `attendanceid` bigint(10) NOT NULL DEFAULT 0,
  `acronym` varchar(2) NOT NULL DEFAULT '',
  `description` varchar(30) NOT NULL DEFAULT '',
  `grade` decimal(5,2) NOT NULL DEFAULT 0.00,
  `studentavailability` bigint(10) DEFAULT NULL,
  `setunmarked` tinyint(2) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `setnumber` mediumint(5) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_attestat_att_ix` (`attendanceid`),
  KEY `mdl_attestat_vis_ix` (`visible`),
  KEY `mdl_attestat_del_ix` (`deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='attendance_statuses table retrofitted from MySQL';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_attendance_statuses`
--

LOCK TABLES `mdl_attendance_statuses` WRITE;
/*!40000 ALTER TABLE `mdl_attendance_statuses` DISABLE KEYS */;
INSERT INTO `mdl_attendance_statuses` VALUES (1,0,'P','Presente',2.00,NULL,NULL,1,0,0),(2,0,'FI','Falta injustificada',0.00,NULL,NULL,1,0,0),(3,0,'R','Retraso',1.00,NULL,NULL,1,0,0),(4,0,'FJ','Falta justificada',1.00,NULL,NULL,1,0,0);
/*!40000 ALTER TABLE `mdl_attendance_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_attendance_tempusers`
--

DROP TABLE IF EXISTS `mdl_attendance_tempusers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_attendance_tempusers` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `studentid` bigint(10) DEFAULT NULL,
  `courseid` bigint(10) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_attetemp_stu_uix` (`studentid`),
  KEY `mdl_attetemp_cou_ix` (`courseid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores temporary users details';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_attendance_tempusers`
--

LOCK TABLES `mdl_attendance_tempusers` WRITE;
/*!40000 ALTER TABLE `mdl_attendance_tempusers` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_attendance_tempusers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_attendance_warning`
--

DROP TABLE IF EXISTS `mdl_attendance_warning`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_attendance_warning` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `idnumber` bigint(10) NOT NULL,
  `warningpercent` bigint(10) NOT NULL,
  `warnafter` bigint(10) NOT NULL,
  `maxwarn` bigint(10) NOT NULL DEFAULT 1,
  `emailuser` smallint(4) NOT NULL,
  `emailsubject` varchar(255) NOT NULL DEFAULT '',
  `emailcontent` longtext NOT NULL,
  `emailcontentformat` smallint(4) NOT NULL,
  `thirdpartyemails` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_attewarn_idnwarwar_uix` (`idnumber`,`warningpercent`,`warnafter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Warning configuration';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_attendance_warning`
--

LOCK TABLES `mdl_attendance_warning` WRITE;
/*!40000 ALTER TABLE `mdl_attendance_warning` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_attendance_warning` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_attendance_warning_done`
--

DROP TABLE IF EXISTS `mdl_attendance_warning_done`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_attendance_warning_done` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `notifyid` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `timesent` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_attewarndone_notuse_ix` (`notifyid`,`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Warnings processed';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_attendance_warning_done`
--

LOCK TABLES `mdl_attendance_warning_done` WRITE;
/*!40000 ALTER TABLE `mdl_attendance_warning_done` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_attendance_warning_done` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_auth_lti_linked_login`
--

DROP TABLE IF EXISTS `mdl_auth_lti_linked_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_auth_lti_linked_login` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL,
  `issuer` longtext NOT NULL,
  `issuer256` varchar(64) NOT NULL DEFAULT '',
  `sub` varchar(255) NOT NULL DEFAULT '',
  `sub256` varchar(64) NOT NULL DEFAULT '',
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_authltilinklogi_useiss_uix` (`userid`,`issuer256`,`sub256`),
  KEY `mdl_authltilinklogi_use_ix` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Accounts linked to a users Moodle account.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_auth_lti_linked_login`
--

LOCK TABLES `mdl_auth_lti_linked_login` WRITE;
/*!40000 ALTER TABLE `mdl_auth_lti_linked_login` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_auth_lti_linked_login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_auth_oauth2_linked_login`
--

DROP TABLE IF EXISTS `mdl_auth_oauth2_linked_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_auth_oauth2_linked_login` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `usermodified` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `issuerid` bigint(10) NOT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `email` longtext NOT NULL,
  `confirmtoken` varchar(64) NOT NULL DEFAULT '',
  `confirmtokenexpires` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_authoautlinklogi_useis_uix` (`userid`,`issuerid`,`username`),
  KEY `mdl_authoautlinklogi_issuse_ix` (`issuerid`,`username`),
  KEY `mdl_authoautlinklogi_use_ix` (`usermodified`),
  KEY `mdl_authoautlinklogi_use2_ix` (`userid`),
  KEY `mdl_authoautlinklogi_iss_ix` (`issuerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Accounts linked to a users Moodle account.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_auth_oauth2_linked_login`
--

LOCK TABLES `mdl_auth_oauth2_linked_login` WRITE;
/*!40000 ALTER TABLE `mdl_auth_oauth2_linked_login` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_auth_oauth2_linked_login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_backup_controllers`
--

DROP TABLE IF EXISTS `mdl_backup_controllers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_backup_controllers` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `backupid` varchar(32) NOT NULL DEFAULT '',
  `operation` varchar(20) NOT NULL DEFAULT 'backup',
  `type` varchar(10) NOT NULL DEFAULT '',
  `itemid` bigint(10) NOT NULL,
  `format` varchar(20) NOT NULL DEFAULT '',
  `interactive` smallint(4) NOT NULL,
  `purpose` smallint(4) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `status` smallint(4) NOT NULL,
  `execution` smallint(4) NOT NULL,
  `executiontime` bigint(10) NOT NULL,
  `checksum` varchar(32) NOT NULL DEFAULT '',
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `progress` decimal(15,14) NOT NULL DEFAULT 0.00000000000000,
  `controller` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_backcont_bac_uix` (`backupid`),
  KEY `mdl_backcont_typite_ix` (`type`,`itemid`),
  KEY `mdl_backcont_useite_ix` (`userid`,`itemid`),
  KEY `mdl_backcont_use_ix` (`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='To store the backup_controllers as they are used';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_backup_controllers`
--

LOCK TABLES `mdl_backup_controllers` WRITE;
/*!40000 ALTER TABLE `mdl_backup_controllers` DISABLE KEYS */;
INSERT INTO `mdl_backup_controllers` VALUES (1,'5bb48fff8b41cf5f9b91b1080bb5091a','backup','course',2,'moodle2',0,50,2,1000,1,0,'06688f2749c08f3f8902fff585f5e960',1756513236,1756513245,0.00000000000000,''),(2,'ab665784f7e98a9f679ab363574b149e','backup','course',44,'moodle2',0,50,2,1000,1,0,'4f6f9637af0f62d7bfe7dfdf651f9f8f',1756527067,1756527071,0.00000000000000,''),(3,'870342241d0a1153075d6aba3b55b8f0','backup','course',43,'moodle2',0,50,2,1000,1,0,'d276a1d560db4510f6f0ee1614df5197',1756527080,1756527084,0.00000000000000,''),(4,'c3f9d048b6168d6c5069b67c0e21c70d','backup','course',42,'moodle2',0,50,2,1000,1,0,'0bb1a21bc95b99e11a9942825b92029a',1756527092,1756527095,0.00000000000000,''),(5,'036d94f93b5c5f6cf79067b85cfb7980','backup','course',41,'moodle2',0,50,2,1000,1,0,'67763420fca74f0b85c70ec5114ca4a3',1756527101,1756527106,0.00000000000000,''),(6,'b15be9fb7b21db1368016654c494ccac','backup','course',40,'moodle2',0,50,2,1000,1,0,'17436cb66f49df031ae8328f2efef9c7',1756527112,1756527116,0.00000000000000,''),(7,'14342253c6a09d3c9b66f366ec2f1788','backup','course',39,'moodle2',0,50,2,1000,1,0,'010ea160c8581907eef23882342dee38',1756527125,1756527129,0.00000000000000,''),(8,'1879c8bd47505d294ade3b5fe7a2ea42','backup','course',38,'moodle2',0,50,2,1000,1,0,'f1e94232fa3c058728070c738e9be197',1756527136,1756527139,0.00000000000000,''),(9,'511d96c2ee799179342fb88a076868cc','backup','course',37,'moodle2',0,50,2,1000,1,0,'574941b6ac573c92e9fd436347c387fb',1756527147,1756527151,0.00000000000000,''),(10,'0b13fa6bb94bf95e1e5602ebfa181b65','backup','course',36,'moodle2',0,50,2,1000,1,0,'dd2db1bad522d731bc172a31f6acd35e',1756527159,1756527162,0.00000000000000,''),(11,'2db7c19978ecf5ca7b9e9853e3c27af2','backup','course',35,'moodle2',0,50,2,1000,1,0,'c2dcf8d68cabd9ada95ec18d1efd81eb',1756527168,1756527171,0.00000000000000,''),(12,'396b60c608c23437c431cf1c3b6674e5','backup','course',34,'moodle2',0,50,2,1000,1,0,'86a3a777892efbca2bc5f8d7a130e13a',1756527179,1756527182,0.00000000000000,''),(13,'5519c7da2052c8696a8db5927446baf1','backup','course',33,'moodle2',0,50,2,1000,1,0,'dd35768628cdc01462e1b1582b38983c',1756527192,1756527195,0.00000000000000,''),(14,'39c2faccb4d91af6ea559ec71ef87c18','backup','course',32,'moodle2',0,50,2,1000,1,0,'a2466b8071049290c8ac9cc939945237',1756527203,1756527207,0.00000000000000,''),(15,'ff8177ee295531094a5cd45b2bb73b24','backup','course',31,'moodle2',0,50,2,1000,1,0,'0fcc261061b0f013be1b203ce2fde103',1756527213,1756527217,0.00000000000000,''),(16,'81f853cf6021255959b45fd4a8e0190c','backup','course',30,'moodle2',0,50,2,1000,1,0,'220706e2abc628b07c99dc4af88448d1',1756527224,1756527227,0.00000000000000,''),(17,'fff3ef84423cc937903591317d82de9e','backup','course',29,'moodle2',0,50,2,1000,1,0,'37c19ed26016d9f212d3755af7559e87',1756527235,1756527238,0.00000000000000,''),(18,'1d08c7094e44c16311f8ca117b938bc1','backup','course',28,'moodle2',0,50,2,1000,1,0,'96d8dfab3095cd9dc9cc7f566a349ad1',1756527248,1756527252,0.00000000000000,''),(19,'b7714983f4075c873db957474edd87bb','backup','course',27,'moodle2',0,50,2,1000,1,0,'9ccb024ee0d5e81f2a5fb3a51389d951',1756527259,1756527262,0.00000000000000,''),(20,'b0828e76bd35af3a53a9674dbb488c78','backup','course',26,'moodle2',0,50,2,1000,1,0,'87546b15d362aeee167a149a1ce15f7c',1756527270,1756527273,0.00000000000000,''),(21,'fe0f0bc56e37b321c4ba888c9151ea97','backup','course',25,'moodle2',0,50,2,1000,1,0,'e8ada2d1b1ccbde4319987399981d161',1756527281,1756527284,0.00000000000000,''),(22,'c9f120b7c0f4f0089cf01bb1744455fb','backup','course',24,'moodle2',0,50,2,1000,1,0,'55d557b8b77b725270fdf87180b7c21e',1756527292,1756527296,0.00000000000000,''),(23,'9ab6931fb19ca3ed621bbb6f420466d5','backup','course',23,'moodle2',0,50,2,1000,1,0,'3b742bcd5c43c5a102a36f551017d296',1756527303,1756527309,0.00000000000000,''),(24,'41befd9cc67491852f8695b796c8bbfe','backup','course',22,'moodle2',0,50,2,1000,1,0,'56b5b358a6a655fda6b1de4f89dc5443',1756527320,1756527323,0.00000000000000,''),(25,'9cdd67e1f9e0b71fb0d3fa57760c6f3c','backup','course',21,'moodle2',0,50,2,1000,1,0,'347dc2ce22587bddff05d40c05baa770',1756527332,1756527335,0.00000000000000,''),(26,'35ad68588815be31309a136a0b53196a','backup','course',20,'moodle2',0,50,2,1000,1,0,'e5b9c8ba2edc674e35ceedd89b6631c7',1756527342,1756527345,0.00000000000000,''),(27,'3adda2aebfb69f0437eaf4c091de81e3','backup','course',19,'moodle2',0,50,2,1000,1,0,'082fa2efe1e945e2378a1cf7f732d98e',1756527352,1756527355,0.00000000000000,''),(28,'38826c8944cfe6e830818725fa075d33','backup','course',18,'moodle2',0,50,2,1000,1,0,'619234612a483b6a84daad0f5a47e01a',1756527363,1756527366,0.00000000000000,''),(29,'e8726916560360bf2cd8f38465755b5f','backup','course',17,'moodle2',0,50,2,1000,1,0,'587bdaa0487e6f025c548ad17bef1627',1756527375,1756527379,0.00000000000000,''),(30,'fe92f11cabc94f5a0f326b6bb594e194','backup','course',16,'moodle2',0,50,2,1000,1,0,'bf72393118c6e27792d3af25edd0d788',1756527389,1756527392,0.00000000000000,''),(31,'2db2de4d8d5a43f6235cf48ef2409690','backup','course',15,'moodle2',0,50,2,1000,1,0,'ef3771338866a5c595f4ba83116e05ad',1756527399,1756527402,0.00000000000000,''),(32,'12176d56d53e516d4b65b5a67611c45d','backup','course',14,'moodle2',0,50,2,1000,1,0,'2835117f122e48049ec11af82cc4de85',1756527409,1756527413,0.00000000000000,''),(33,'9d9b883177a4aca8befe143fd4294473','backup','course',13,'moodle2',0,50,2,1000,1,0,'64cabe2a219063249066bd6cb45dc5af',1756527421,1756527424,0.00000000000000,''),(34,'e0ecb832028f2bd8e7e702c04919525a','backup','course',12,'moodle2',0,50,2,1000,1,0,'685101dac2db44714d631c21751647e3',1756527430,1756527434,0.00000000000000,''),(35,'c9c2dea70e4291788c0bfc5840b6973e','backup','course',11,'moodle2',0,50,2,1000,1,0,'1dafa5db0a079b3761bd47d0ca35dd29',1756527444,1756527448,0.00000000000000,''),(36,'20c32c4b7e833b0a06aa3e7ae47e3652','backup','course',10,'moodle2',0,50,2,1000,1,0,'78e0d6f55c442ac3d0f0aff8fdfd7c7e',1756527456,1756527459,0.00000000000000,''),(37,'b6462e602e634f7f8ce39c12fd1c530f','backup','course',9,'moodle2',0,50,2,1000,1,0,'146947936ee8108ca6f6f101f4ba3cae',1756527468,1756527471,0.00000000000000,''),(38,'367f32ec2bffbcbc5691602e2bbff177','backup','course',8,'moodle2',0,50,2,1000,1,0,'5488ab9e4acf4088a9587289a1134536',1756527478,1756527481,0.00000000000000,''),(39,'b99e1cd0650f3aa94ea93117b2d8ecee','backup','course',7,'moodle2',0,50,2,1000,1,0,'18bc24568a9af0ec70c1deb5ded92c3b',1756527488,1756527492,0.00000000000000,''),(40,'b30fa418f2e6b1541370331e50491da9','backup','course',6,'moodle2',0,50,2,1000,1,0,'6b48a0acbe70db1587ba1a693e6f8dd3',1756527500,1756527502,0.00000000000000,''),(41,'5d4527d75c641807bed7e17897562f80','backup','course',5,'moodle2',0,50,2,1000,1,0,'3ceff6356517813b42669ddecbf1fe1b',1756527510,1756527513,0.00000000000000,''),(42,'ff49a90ea293cc27f8e2f87a2d319454','backup','course',4,'moodle2',0,50,2,1000,1,0,'c51d3da490e09907577e257c06dfbddf',1756527520,1756527525,0.00000000000000,''),(43,'22c23018399182f0f38e37bbe36bd5d3','backup','course',3,'moodle2',0,50,2,1000,1,0,'d55d5fd8fc7ac601c754679a37f76fee',1756527533,1756527537,0.00000000000000,''),(44,'bdb7767f78805cebea44a8fa514588fa','backup','course',86,'moodle2',0,50,2,1000,1,0,'b2bd8e2e40e9e43cd85367463821e8b6',1756528517,1756528520,0.00000000000000,''),(45,'14f5508076a081ca0440c5bd20f6a065','backup','course',85,'moodle2',0,50,2,1000,1,0,'2938e88894be472d772ed0ee4bcfd811',1756528529,1756528533,0.00000000000000,''),(46,'0df277867ba529a6e23e87b27085f449','backup','course',84,'moodle2',0,50,2,1000,1,0,'f24692c46337c1957469d27ca2e3c9fc',1756528542,1756528545,0.00000000000000,''),(47,'fff26e9e8fb55d92fa61541b5003af98','backup','course',83,'moodle2',0,50,2,1000,1,0,'8e7b7943b86d87424fe3836ff2dd7998',1756528552,1756528556,0.00000000000000,''),(48,'560cbff00b17ad57340f77dcf9825ac6','backup','course',82,'moodle2',0,50,2,1000,1,0,'eaa6a933fff01cecf7b42693e50b2ff8',1756528563,1756528567,0.00000000000000,''),(49,'a6b75db50edf4ad70dda2ddfb91e2464','backup','course',81,'moodle2',0,50,2,1000,1,0,'766de837edb60f25413dc8566c90564f',1756528576,1756528580,0.00000000000000,''),(50,'8b689af917c52aa971603254e8f4e6cf','backup','course',80,'moodle2',0,50,2,1000,1,0,'6712ec76498e909fce9be487c0937a47',1756528589,1756528593,0.00000000000000,''),(51,'8b7fe8e785a56f5453d01c1cbd558ce4','backup','course',79,'moodle2',0,50,2,1000,1,0,'a0ff4db419691a060bfc4f2b555ff1a1',1756528600,1756528604,0.00000000000000,''),(52,'3d9bd88c3dda1d5fd15766f28962cb4a','backup','course',78,'moodle2',0,50,2,1000,1,0,'7aa4cccb6fb296cea0ad01ab6b47a3a3',1756528613,1756528615,0.00000000000000,''),(53,'e5256fe9ff53ace7aa4e722340c7c4e1','backup','course',77,'moodle2',0,50,2,1000,1,0,'6adf1500863fe5eb9d7405f7df738150',1756528624,1756528627,0.00000000000000,''),(54,'3128b17cbd312abf2fa13d937b2a68c7','backup','course',76,'moodle2',0,50,2,1000,1,0,'538aa0f213873eb0d633bcd6e2fc9820',1756528635,1756528638,0.00000000000000,''),(55,'7a1ab3eb10420bb09e0bf5535354d03d','backup','course',75,'moodle2',0,50,2,1000,1,0,'e91dce6fa5f066d68d7e8e4372f1b606',1756528645,1756528649,0.00000000000000,''),(56,'e60f16b758f455c3aa10d8ca473b78d9','backup','course',74,'moodle2',0,50,2,1000,1,0,'0e1c35d67d70439a77f95c5034cfe5e1',1756528657,1756528660,0.00000000000000,''),(57,'2b20fc871ff50a1f4e739fe7d6f7fe5f','backup','course',73,'moodle2',0,50,2,1000,1,0,'7039a7edc1e70b7c74344f370b3db9b7',1756528668,1756528671,0.00000000000000,''),(58,'2209888b84a285fce85e4f4b5e264e42','backup','course',72,'moodle2',0,50,2,1000,1,0,'1402a5c42ff01eefb333aa3127dbdbfd',1756528679,1756528682,0.00000000000000,''),(59,'5e3908c633785db232cf8fcfd073769d','backup','course',71,'moodle2',0,50,2,1000,1,0,'2d2efa668b63ed7364430942ca084dff',1756528691,1756528694,0.00000000000000,''),(60,'ef5cac9825e2eb8bffb690c47850b1c7','backup','course',70,'moodle2',0,50,2,1000,1,0,'c36c8c80367c9bc6ae82bdba0aefc2f1',1756528702,1756528705,0.00000000000000,''),(61,'b1bd81d804e687eec52bd19e17030b9c','backup','course',69,'moodle2',0,50,2,1000,1,0,'7fb9fac96df3b943d92b82e1046f8fe4',1756528713,1756528716,0.00000000000000,''),(62,'e19b22da18afe9cc30895fa9f4fc209e','backup','course',68,'moodle2',0,50,2,1000,1,0,'d7e387597d7fa75699784c1b72c913b0',1756528724,1756528727,0.00000000000000,''),(63,'28020a46e25696f45abe400f41649da2','backup','course',67,'moodle2',0,50,2,1000,1,0,'fe956074e39b5120ff4726d9f97d92e0',1756528735,1756528739,0.00000000000000,''),(64,'3c6d7e85d6220e07938871585faaaf74','backup','course',66,'moodle2',0,50,2,1000,1,0,'34de6e4e52a610db8ea5d07b121df7d3',1756528745,1756528748,0.00000000000000,''),(65,'61b89762321c7bd811109d3973a12b74','backup','course',65,'moodle2',0,50,2,1000,1,0,'b6297ee64170c3721202d2060afa5fb4',1756528755,1756528758,0.00000000000000,''),(66,'f6d5fbb3e84071f35f5a39e547b5e016','backup','course',64,'moodle2',0,50,2,1000,1,0,'750e1966197694521a92ce959681a1cb',1756528767,1756528770,0.00000000000000,''),(67,'8b65bc30317a0bec972f02a5eae376ea','backup','course',63,'moodle2',0,50,2,1000,1,0,'a188a82cf2214fa1e6b67fda32ce15f9',1756528779,1756528782,0.00000000000000,''),(68,'12bad76dc47d88eee40490b8a7242aaa','backup','course',62,'moodle2',0,50,2,1000,1,0,'07f3bcaac56cefd3b09f5fcae54160fc',1756528790,1756528793,0.00000000000000,''),(69,'0b0290c95b82f3840bcca7206f58b43f','backup','course',61,'moodle2',0,50,2,1000,1,0,'ef40a47a86e5b44d1356adfac3cd2154',1756528801,1756528804,0.00000000000000,''),(70,'1438a57cdf7d0b3ddfe9d709fab74864','backup','course',60,'moodle2',0,50,2,1000,1,0,'0eb87f9becd5d9112c717c824cfc1d20',1756528813,1756528817,0.00000000000000,''),(71,'e18decdea8691f013f91a7e106c4fd5e','backup','course',59,'moodle2',0,50,2,1000,1,0,'4321e4121dd7ea01e0238b297249bf69',1756528824,1756528827,0.00000000000000,''),(72,'369774960bcbcf5ab12d044301d7fe71','backup','course',58,'moodle2',0,50,2,1000,1,0,'6a8f10aa5f496fd9f172ffcb7193ea22',1756528837,1756528840,0.00000000000000,''),(73,'644760215273a24b3abe4bec1b1165c6','backup','course',57,'moodle2',0,50,2,1000,1,0,'f27d6b96246839e2d6f955b80fff9fa5',1756528848,1756528851,0.00000000000000,''),(74,'a2db9401c2d9c9fbce8de0dd9c16e92a','backup','course',56,'moodle2',0,50,2,1000,1,0,'990b58162405f2b50a6ba24eddacd1aa',1756528858,1756528862,0.00000000000000,''),(75,'e03d6f2a454b6de4fdf849e3a14fcc05','backup','course',55,'moodle2',0,50,2,1000,1,0,'8b1c86b7443de8fef19e0fca553865af',1756528870,1756528873,0.00000000000000,''),(76,'fb1c68e22e63beff9cae428f851a953d','backup','course',54,'moodle2',0,50,2,1000,1,0,'1c016df9a29bda595698e1beebb6f0a1',1756528880,1756528884,0.00000000000000,''),(77,'8381855b318e6cda894f46b2c3d55cab','backup','course',53,'moodle2',0,50,2,1000,1,0,'d2228eb52ee7e781c084709655643d0d',1756528892,1756528896,0.00000000000000,''),(78,'74e00668cdb4843bb3124e2da3879edc','backup','course',52,'moodle2',0,50,2,1000,1,0,'c686c2d9d99165f6c318f49919cd73ae',1756528902,1756528905,0.00000000000000,''),(79,'307df5088b31034282e26db2e415a08d','backup','course',51,'moodle2',0,50,2,1000,1,0,'b143e526d9cd20f33fd3a67d0581aabb',1756528912,1756528916,0.00000000000000,''),(80,'d31c7e402462d35d61ae0d935226e215','backup','course',50,'moodle2',0,50,2,1000,1,0,'350e22b02fd3a456938c77ab75f02797',1756528923,1756528926,0.00000000000000,''),(81,'133b4c15336772f674d34d1719deda8e','backup','course',49,'moodle2',0,50,2,1000,1,0,'72839d7f87987474cb1b45a9ce50d6f2',1756528935,1756528938,0.00000000000000,''),(82,'fff43afa7b9de0d3fc901a85b39bd052','backup','course',48,'moodle2',0,50,2,1000,1,0,'13cb17196c2ab286c7e58220e9011519',1756528947,1756528951,0.00000000000000,''),(83,'56be863de57be35464cb3729abdd8231','backup','course',47,'moodle2',0,50,2,1000,1,0,'6287a49580c7220e042c1a85250322b1',1756528960,1756528963,0.00000000000000,''),(84,'a13fcf10ba830f83309ee967076d71cb','backup','course',46,'moodle2',0,50,2,1000,1,0,'e4014cc39fb0c9a70ee0714203c300e8',1756528969,1756528972,0.00000000000000,''),(85,'692ef6123373b896a22f549955347602','backup','course',45,'moodle2',0,50,2,1000,1,0,'7824aa89b00e0f954ee87d30de47b3f9',1756528980,1756528982,0.00000000000000,''),(86,'6768eda5d8b5aadd7ad01e38f602e465','backup','course',107,'moodle2',0,50,2,1000,1,0,'4b2eafcc14a88049837dcb174d174f52',1756554957,1756554961,0.00000000000000,''),(87,'cf0ac3d1b66bc8b6d661dd7c7e126f56','backup','course',106,'moodle2',0,50,2,1000,1,0,'e1b20abaa3b0d6f91dece105f74a3e37',1756555011,1756555014,0.00000000000000,''),(88,'8061cecfcec7e463acfac82865ad843e','backup','course',105,'moodle2',0,50,2,1000,1,0,'86c745235193e9abe7927f99217e3e0e',1756555041,1756555044,0.00000000000000,''),(89,'a001949799f3a3872ca2515931e3ed11','backup','course',104,'moodle2',0,50,2,1000,1,0,'37d87d21b8e28390ab61e78951eebe00',1756555077,1756555080,0.00000000000000,''),(90,'30a6407c5ca1965dee0519c2ac3508c4','backup','course',103,'moodle2',0,50,2,1000,1,0,'767baedf4c99e9941f4c5f4cfdfc8a11',1756555144,1756555146,0.00000000000000,''),(91,'e20f3fd89765ebef1e3096493f2516c1','backup','course',102,'moodle2',0,50,2,1000,1,0,'6fe7a1746cc22627b2f3ce2e6e43f34d',1756555209,1756555214,0.00000000000000,''),(92,'ad06498b838bded2c881bd6028f534a4','backup','course',101,'moodle2',0,50,2,1000,1,0,'bdced7dcee23219fb166ea92d0103239',1756555272,1756555275,0.00000000000000,'');
/*!40000 ALTER TABLE `mdl_backup_controllers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_backup_courses`
--

DROP TABLE IF EXISTS `mdl_backup_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_backup_courses` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `courseid` bigint(10) NOT NULL DEFAULT 0,
  `laststarttime` bigint(10) NOT NULL DEFAULT 0,
  `lastendtime` bigint(10) NOT NULL DEFAULT 0,
  `laststatus` varchar(1) NOT NULL DEFAULT '5',
  `nextstarttime` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_backcour_cou_uix` (`courseid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='To store every course backup status';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_backup_courses`
--

LOCK TABLES `mdl_backup_courses` WRITE;
/*!40000 ALTER TABLE `mdl_backup_courses` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_backup_courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_backup_logs`
--

DROP TABLE IF EXISTS `mdl_backup_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_backup_logs` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `backupid` varchar(32) NOT NULL DEFAULT '',
  `loglevel` smallint(4) NOT NULL,
  `message` longtext NOT NULL,
  `timecreated` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_backlogs_bacid_uix` (`backupid`,`id`),
  KEY `mdl_backlogs_bac_ix` (`backupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='To store all the logs from backup and restore operations (by';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_backup_logs`
--

LOCK TABLES `mdl_backup_logs` WRITE;
/*!40000 ALTER TABLE `mdl_backup_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_backup_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_badge`
--

DROP TABLE IF EXISTS `mdl_badge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_badge` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` longtext DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `usercreated` bigint(10) NOT NULL,
  `usermodified` bigint(10) NOT NULL,
  `issuername` varchar(255) NOT NULL DEFAULT '',
  `issuerurl` varchar(255) NOT NULL DEFAULT '',
  `issuercontact` varchar(255) DEFAULT NULL,
  `expiredate` bigint(10) DEFAULT NULL,
  `expireperiod` bigint(10) DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 1,
  `courseid` bigint(10) DEFAULT NULL,
  `message` longtext NOT NULL,
  `messagesubject` longtext NOT NULL,
  `attachment` tinyint(1) NOT NULL DEFAULT 1,
  `notification` tinyint(1) NOT NULL DEFAULT 1,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `nextcron` bigint(10) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `language` varchar(255) DEFAULT NULL,
  `imageauthorname` varchar(255) DEFAULT NULL,
  `imageauthoremail` varchar(255) DEFAULT NULL,
  `imageauthorurl` varchar(255) DEFAULT NULL,
  `imagecaption` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_badg_typ_ix` (`type`),
  KEY `mdl_badg_cou_ix` (`courseid`),
  KEY `mdl_badg_use_ix` (`usermodified`),
  KEY `mdl_badg_use2_ix` (`usercreated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Defines badge';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_badge`
--

LOCK TABLES `mdl_badge` WRITE;
/*!40000 ALTER TABLE `mdl_badge` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_badge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_badge_alignment`
--

DROP TABLE IF EXISTS `mdl_badge_alignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_badge_alignment` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `badgeid` bigint(10) NOT NULL DEFAULT 0,
  `targetname` varchar(255) NOT NULL DEFAULT '',
  `targeturl` varchar(255) NOT NULL DEFAULT '',
  `targetdescription` longtext DEFAULT NULL,
  `targetframework` varchar(255) DEFAULT NULL,
  `targetcode` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_badgalig_bad_ix` (`badgeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Defines alignment for badges';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_badge_alignment`
--

LOCK TABLES `mdl_badge_alignment` WRITE;
/*!40000 ALTER TABLE `mdl_badge_alignment` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_badge_alignment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_badge_backpack`
--

DROP TABLE IF EXISTS `mdl_badge_backpack`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_badge_backpack` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `email` varchar(100) NOT NULL DEFAULT '',
  `backpackuid` bigint(10) NOT NULL,
  `autosync` tinyint(1) NOT NULL DEFAULT 0,
  `password` varchar(50) DEFAULT NULL,
  `externalbackpackid` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_badgback_useext_uix` (`userid`,`externalbackpackid`),
  KEY `mdl_badgback_use_ix` (`userid`),
  KEY `mdl_badgback_ext_ix` (`externalbackpackid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Defines settings for connecting external backpack';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_badge_backpack`
--

LOCK TABLES `mdl_badge_backpack` WRITE;
/*!40000 ALTER TABLE `mdl_badge_backpack` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_badge_backpack` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_badge_backpack_oauth2`
--

DROP TABLE IF EXISTS `mdl_badge_backpack_oauth2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_badge_backpack_oauth2` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `usermodified` bigint(10) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL,
  `issuerid` bigint(10) NOT NULL,
  `externalbackpackid` bigint(10) NOT NULL,
  `token` longtext NOT NULL,
  `refreshtoken` longtext NOT NULL,
  `expires` bigint(10) DEFAULT NULL,
  `scope` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_badgbackoaut_use_ix` (`usermodified`),
  KEY `mdl_badgbackoaut_use2_ix` (`userid`),
  KEY `mdl_badgbackoaut_iss_ix` (`issuerid`),
  KEY `mdl_badgbackoaut_ext_ix` (`externalbackpackid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Default comment for the table, please edit me';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_badge_backpack_oauth2`
--

LOCK TABLES `mdl_badge_backpack_oauth2` WRITE;
/*!40000 ALTER TABLE `mdl_badge_backpack_oauth2` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_badge_backpack_oauth2` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_badge_criteria`
--

DROP TABLE IF EXISTS `mdl_badge_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_badge_criteria` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `badgeid` bigint(10) NOT NULL DEFAULT 0,
  `criteriatype` bigint(10) DEFAULT NULL,
  `method` tinyint(1) NOT NULL DEFAULT 1,
  `description` longtext DEFAULT NULL,
  `descriptionformat` tinyint(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_badgcrit_badcri_uix` (`badgeid`,`criteriatype`),
  KEY `mdl_badgcrit_cri_ix` (`criteriatype`),
  KEY `mdl_badgcrit_bad_ix` (`badgeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Defines criteria for issuing badges';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_badge_criteria`
--

LOCK TABLES `mdl_badge_criteria` WRITE;
/*!40000 ALTER TABLE `mdl_badge_criteria` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_badge_criteria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_badge_criteria_met`
--

DROP TABLE IF EXISTS `mdl_badge_criteria_met`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_badge_criteria_met` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `issuedid` bigint(10) DEFAULT NULL,
  `critid` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `datemet` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_badgcritmet_cri_ix` (`critid`),
  KEY `mdl_badgcritmet_use_ix` (`userid`),
  KEY `mdl_badgcritmet_iss_ix` (`issuedid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Defines criteria that were met for an issued badge';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_badge_criteria_met`
--

LOCK TABLES `mdl_badge_criteria_met` WRITE;
/*!40000 ALTER TABLE `mdl_badge_criteria_met` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_badge_criteria_met` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_badge_criteria_param`
--

DROP TABLE IF EXISTS `mdl_badge_criteria_param`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_badge_criteria_param` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `critid` bigint(10) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_badgcritpara_cri_ix` (`critid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Defines parameters for badges criteria';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_badge_criteria_param`
--

LOCK TABLES `mdl_badge_criteria_param` WRITE;
/*!40000 ALTER TABLE `mdl_badge_criteria_param` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_badge_criteria_param` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_badge_endorsement`
--

DROP TABLE IF EXISTS `mdl_badge_endorsement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_badge_endorsement` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `badgeid` bigint(10) NOT NULL DEFAULT 0,
  `issuername` varchar(255) NOT NULL DEFAULT '',
  `issuerurl` varchar(255) NOT NULL DEFAULT '',
  `issueremail` varchar(255) NOT NULL DEFAULT '',
  `claimid` varchar(255) DEFAULT NULL,
  `claimcomment` longtext DEFAULT NULL,
  `dateissued` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_badgendo_bad_ix` (`badgeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Defines endorsement for badge';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_badge_endorsement`
--

LOCK TABLES `mdl_badge_endorsement` WRITE;
/*!40000 ALTER TABLE `mdl_badge_endorsement` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_badge_endorsement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_badge_external`
--

DROP TABLE IF EXISTS `mdl_badge_external`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_badge_external` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `backpackid` bigint(10) NOT NULL,
  `collectionid` bigint(10) NOT NULL,
  `entityid` varchar(255) DEFAULT NULL,
  `assertion` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_badgexte_bac_ix` (`backpackid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Setting for external badges display';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_badge_external`
--

LOCK TABLES `mdl_badge_external` WRITE;
/*!40000 ALTER TABLE `mdl_badge_external` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_badge_external` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_badge_external_backpack`
--

DROP TABLE IF EXISTS `mdl_badge_external_backpack`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_badge_external_backpack` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `backpackapiurl` varchar(255) NOT NULL DEFAULT '',
  `backpackweburl` varchar(255) NOT NULL DEFAULT '',
  `apiversion` varchar(12) NOT NULL DEFAULT '1.0',
  `sortorder` bigint(10) NOT NULL DEFAULT 0,
  `oauth2_issuerid` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_badgexteback_bac_uix` (`backpackapiurl`),
  UNIQUE KEY `mdl_badgexteback_bac2_uix` (`backpackweburl`),
  KEY `mdl_badgexteback_oau_ix` (`oauth2_issuerid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Defines settings for site level backpacks that a user can co';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_badge_external_backpack`
--

LOCK TABLES `mdl_badge_external_backpack` WRITE;
/*!40000 ALTER TABLE `mdl_badge_external_backpack` DISABLE KEYS */;
INSERT INTO `mdl_badge_external_backpack` VALUES (1,'https://api.badgr.io/v2','https://badgr.io','2',1,NULL);
/*!40000 ALTER TABLE `mdl_badge_external_backpack` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_badge_external_identifier`
--

DROP TABLE IF EXISTS `mdl_badge_external_identifier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_badge_external_identifier` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `sitebackpackid` bigint(10) NOT NULL,
  `internalid` varchar(128) NOT NULL DEFAULT '',
  `externalid` varchar(128) NOT NULL DEFAULT '',
  `type` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_badgexteiden_sitintext_uix` (`sitebackpackid`,`internalid`,`externalid`,`type`),
  KEY `mdl_badgexteiden_sit_ix` (`sitebackpackid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Setting for external badges mappings';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_badge_external_identifier`
--

LOCK TABLES `mdl_badge_external_identifier` WRITE;
/*!40000 ALTER TABLE `mdl_badge_external_identifier` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_badge_external_identifier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_badge_issued`
--

DROP TABLE IF EXISTS `mdl_badge_issued`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_badge_issued` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `badgeid` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `uniquehash` longtext NOT NULL,
  `dateissued` bigint(10) NOT NULL DEFAULT 0,
  `dateexpire` bigint(10) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 0,
  `issuernotified` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_badgissu_baduse_uix` (`badgeid`,`userid`),
  KEY `mdl_badgissu_bad_ix` (`badgeid`),
  KEY `mdl_badgissu_use_ix` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Defines issued badges';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_badge_issued`
--

LOCK TABLES `mdl_badge_issued` WRITE;
/*!40000 ALTER TABLE `mdl_badge_issued` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_badge_issued` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_badge_manual_award`
--

DROP TABLE IF EXISTS `mdl_badge_manual_award`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_badge_manual_award` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `badgeid` bigint(10) NOT NULL,
  `recipientid` bigint(10) NOT NULL,
  `issuerid` bigint(10) NOT NULL,
  `issuerrole` bigint(10) NOT NULL,
  `datemet` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_badgmanuawar_bad_ix` (`badgeid`),
  KEY `mdl_badgmanuawar_rec_ix` (`recipientid`),
  KEY `mdl_badgmanuawar_iss_ix` (`issuerid`),
  KEY `mdl_badgmanuawar_iss2_ix` (`issuerrole`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Track manual award criteria for badges';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_badge_manual_award`
--

LOCK TABLES `mdl_badge_manual_award` WRITE;
/*!40000 ALTER TABLE `mdl_badge_manual_award` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_badge_manual_award` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_badge_related`
--

DROP TABLE IF EXISTS `mdl_badge_related`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_badge_related` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `badgeid` bigint(10) NOT NULL DEFAULT 0,
  `relatedbadgeid` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_badgrela_badrel_uix` (`badgeid`,`relatedbadgeid`),
  KEY `mdl_badgrela_bad_ix` (`badgeid`),
  KEY `mdl_badgrela_rel_ix` (`relatedbadgeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Defines badge related for badges';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_badge_related`
--

LOCK TABLES `mdl_badge_related` WRITE;
/*!40000 ALTER TABLE `mdl_badge_related` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_badge_related` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_bigbluebuttonbn`
--

DROP TABLE IF EXISTS `mdl_bigbluebuttonbn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_bigbluebuttonbn` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `type` tinyint(2) NOT NULL DEFAULT 0,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `intro` longtext DEFAULT NULL,
  `introformat` smallint(4) NOT NULL DEFAULT 1,
  `meetingid` varchar(255) NOT NULL DEFAULT '',
  `moderatorpass` varchar(255) NOT NULL DEFAULT '',
  `viewerpass` varchar(255) NOT NULL DEFAULT '',
  `wait` tinyint(1) NOT NULL DEFAULT 0,
  `record` tinyint(1) NOT NULL DEFAULT 0,
  `recordallfromstart` tinyint(1) NOT NULL DEFAULT 0,
  `recordhidebutton` tinyint(1) NOT NULL DEFAULT 0,
  `welcome` longtext DEFAULT NULL,
  `voicebridge` mediumint(5) NOT NULL DEFAULT 0,
  `openingtime` bigint(10) NOT NULL DEFAULT 0,
  `closingtime` bigint(10) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `presentation` longtext DEFAULT NULL,
  `participants` longtext DEFAULT NULL,
  `userlimit` smallint(3) NOT NULL DEFAULT 0,
  `recordings_html` tinyint(1) NOT NULL DEFAULT 0,
  `recordings_deleted` tinyint(1) NOT NULL DEFAULT 1,
  `recordings_imported` tinyint(1) NOT NULL DEFAULT 0,
  `recordings_preview` tinyint(1) NOT NULL DEFAULT 0,
  `clienttype` tinyint(1) NOT NULL DEFAULT 0,
  `muteonstart` tinyint(1) NOT NULL DEFAULT 0,
  `disablecam` tinyint(1) NOT NULL DEFAULT 0,
  `disablemic` tinyint(1) NOT NULL DEFAULT 0,
  `disableprivatechat` tinyint(1) NOT NULL DEFAULT 0,
  `disablepublicchat` tinyint(1) NOT NULL DEFAULT 0,
  `disablenote` tinyint(1) NOT NULL DEFAULT 0,
  `hideuserlist` tinyint(1) NOT NULL DEFAULT 0,
  `lockedlayout` tinyint(1) NOT NULL DEFAULT 0,
  `lockonjoin` tinyint(1) NOT NULL DEFAULT 0,
  `lockonjoinconfigurable` tinyint(1) NOT NULL DEFAULT 0,
  `completionattendance` int(9) NOT NULL DEFAULT 0,
  `completionengagementchats` int(9) NOT NULL DEFAULT 0,
  `completionengagementtalks` int(9) NOT NULL DEFAULT 0,
  `completionengagementraisehand` int(9) NOT NULL DEFAULT 0,
  `completionengagementpollvotes` int(9) NOT NULL DEFAULT 0,
  `completionengagementemojis` int(9) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='The bigbluebuttonbn table to store information about a meeti';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_bigbluebuttonbn`
--

LOCK TABLES `mdl_bigbluebuttonbn` WRITE;
/*!40000 ALTER TABLE `mdl_bigbluebuttonbn` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_bigbluebuttonbn` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_bigbluebuttonbn_logs`
--

DROP TABLE IF EXISTS `mdl_bigbluebuttonbn_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_bigbluebuttonbn_logs` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `courseid` bigint(10) NOT NULL,
  `bigbluebuttonbnid` bigint(10) NOT NULL,
  `userid` bigint(10) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `meetingid` varchar(256) NOT NULL DEFAULT '',
  `log` varchar(32) NOT NULL DEFAULT '',
  `meta` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_bigblogs_cou_ix` (`courseid`),
  KEY `mdl_bigblogs_log_ix` (`log`),
  KEY `mdl_bigblogs_coubiguselog_ix` (`courseid`,`bigbluebuttonbnid`,`userid`,`log`),
  KEY `mdl_bigblogs_uselog_ix` (`userid`,`log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='The bigbluebuttonbn table to store meeting activity events';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_bigbluebuttonbn_logs`
--

LOCK TABLES `mdl_bigbluebuttonbn_logs` WRITE;
/*!40000 ALTER TABLE `mdl_bigbluebuttonbn_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_bigbluebuttonbn_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_bigbluebuttonbn_recordings`
--

DROP TABLE IF EXISTS `mdl_bigbluebuttonbn_recordings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_bigbluebuttonbn_recordings` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `courseid` bigint(10) NOT NULL,
  `bigbluebuttonbnid` bigint(10) NOT NULL,
  `groupid` bigint(10) DEFAULT NULL,
  `recordingid` varchar(64) NOT NULL DEFAULT '',
  `headless` tinyint(1) NOT NULL DEFAULT 0,
  `imported` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `importeddata` longtext DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `usermodified` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_bigbreco_cou_ix` (`courseid`),
  KEY `mdl_bigbreco_rec_ix` (`recordingid`),
  KEY `mdl_bigbreco_big_ix` (`bigbluebuttonbnid`),
  KEY `mdl_bigbreco_use_ix` (`usermodified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='The bigbluebuttonbn table to store references to recordings';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_bigbluebuttonbn_recordings`
--

LOCK TABLES `mdl_bigbluebuttonbn_recordings` WRITE;
/*!40000 ALTER TABLE `mdl_bigbluebuttonbn_recordings` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_bigbluebuttonbn_recordings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_block`
--

DROP TABLE IF EXISTS `mdl_block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_block` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL DEFAULT '',
  `cron` bigint(10) NOT NULL DEFAULT 0,
  `lastcron` bigint(10) NOT NULL DEFAULT 0,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_bloc_nam_uix` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='contains all installed blocks';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_block`
--

LOCK TABLES `mdl_block` WRITE;
/*!40000 ALTER TABLE `mdl_block` DISABLE KEYS */;
INSERT INTO `mdl_block` VALUES (1,'accessreview',0,0,1),(2,'activity_modules',0,0,1),(3,'activity_results',0,0,1),(4,'admin_bookmarks',0,0,1),(5,'badges',0,0,1),(6,'blog_menu',0,0,1),(7,'blog_recent',0,0,1),(8,'blog_tags',0,0,1),(9,'calendar_month',0,0,1),(10,'calendar_upcoming',0,0,1),(11,'comments',0,0,1),(12,'completionstatus',0,0,1),(13,'course_list',0,0,1),(14,'course_summary',0,0,0),(15,'feedback',0,0,0),(16,'globalsearch',0,0,1),(17,'glossary_random',0,0,1),(18,'html',0,0,1),(19,'login',0,0,1),(20,'lp',0,0,1),(21,'mentees',0,0,1),(22,'mnet_hosts',0,0,1),(23,'myoverview',0,0,1),(24,'myprofile',0,0,1),(25,'navigation',0,0,1),(26,'news_items',0,0,1),(27,'online_users',0,0,1),(28,'private_files',0,0,1),(29,'recent_activity',0,0,1),(30,'recentlyaccessedcourses',0,0,1),(31,'recentlyaccesseditems',0,0,1),(32,'rss_client',0,0,0),(33,'search_forums',0,0,1),(34,'section_links',0,0,1),(35,'selfcompletion',0,0,0),(36,'settings',0,0,1),(37,'site_main_menu',0,0,1),(38,'social_activities',0,0,1),(39,'starredcourses',0,0,1),(40,'tag_flickr',0,0,1),(41,'tag_youtube',0,0,0),(42,'tags',0,0,1),(43,'timeline',0,0,1),(44,'completion_progress',0,0,1);
/*!40000 ALTER TABLE `mdl_block` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_block_instances`
--

DROP TABLE IF EXISTS `mdl_block_instances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_block_instances` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `blockname` varchar(40) NOT NULL DEFAULT '',
  `parentcontextid` bigint(10) NOT NULL,
  `showinsubcontexts` smallint(4) NOT NULL,
  `requiredbytheme` smallint(4) NOT NULL DEFAULT 0,
  `pagetypepattern` varchar(64) NOT NULL DEFAULT '',
  `subpagepattern` varchar(16) DEFAULT NULL,
  `defaultregion` varchar(16) NOT NULL DEFAULT '',
  `defaultweight` bigint(10) NOT NULL,
  `configdata` longtext DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_blocinst_parshopagsub_ix` (`parentcontextid`,`showinsubcontexts`,`pagetypepattern`,`subpagepattern`),
  KEY `mdl_blocinst_tim_ix` (`timemodified`),
  KEY `mdl_blocinst_par_ix` (`parentcontextid`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='This table stores block instances. The type of block this is';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_block_instances`
--

LOCK TABLES `mdl_block_instances` WRITE;
/*!40000 ALTER TABLE `mdl_block_instances` DISABLE KEYS */;
INSERT INTO `mdl_block_instances` VALUES (1,'admin_bookmarks',1,0,0,'admin-*',NULL,'side-pre',2,'',1756253204,1756253204),(2,'recentlyaccesseditems',1,0,0,'my-index','2','side-post',0,'',1756253205,1756253205),(3,'timeline',1,0,0,'my-index','2','content',0,'',1756253206,1756253206),(4,'calendar_month',1,0,0,'my-index','2','content',1,'',1756253207,1756253207),(5,'myoverview',1,0,0,'my-index','3','content',0,'',1756253207,1756253207),(6,'recentlyaccesseditems',5,0,0,'my-index','4','side-post',0,'',1756520656,1756520656),(7,'timeline',5,0,0,'my-index','4','content',0,'',1756520656,1756520656),(8,'calendar_month',5,0,0,'my-index','4','content',1,'',1756520657,1756520657),(9,'course_list',5,0,0,'my-index','4','content',3,'',1756525406,1756525417),(10,'completionstatus',214,1,0,'*',NULL,'content',0,'Tzo4OiJzdGRDbGFzcyI6MDp7fQ==',1756558108,1756560825),(11,'completion_progress',214,0,0,'course-view-*',NULL,'content',0,'Tzo4OiJzdGRDbGFzcyI6NTp7czo3OiJvcmRlcmJ5IjtzOjExOiJvcmRlcmJ5dGltZSI7czo4OiJsb25nYmFycyI7czo0OiJ3cmFwIjtzOjE0OiJzaG93cGVyY2VudGFnZSI7czoxOiIxIjtzOjEzOiJwcm9ncmVzc1RpdGxlIjtzOjg6IlByb2dyZXNvIjtzOjE4OiJhY3Rpdml0aWVzaW5jbHVkZWQiO3M6MTg6ImFjdGl2aXR5Y29tcGxldGlvbiI7fQ==',1756559903,1756560879);
/*!40000 ALTER TABLE `mdl_block_instances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_block_positions`
--

DROP TABLE IF EXISTS `mdl_block_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_block_positions` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `blockinstanceid` bigint(10) NOT NULL,
  `contextid` bigint(10) NOT NULL,
  `pagetype` varchar(64) NOT NULL DEFAULT '',
  `subpage` varchar(16) NOT NULL DEFAULT '',
  `visible` smallint(4) NOT NULL,
  `region` varchar(16) NOT NULL DEFAULT '',
  `weight` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_blocposi_bloconpagsub_uix` (`blockinstanceid`,`contextid`,`pagetype`,`subpage`),
  KEY `mdl_blocposi_blo_ix` (`blockinstanceid`),
  KEY `mdl_blocposi_con_ix` (`contextid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores the position of a sticky block_instance on a another ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_block_positions`
--

LOCK TABLES `mdl_block_positions` WRITE;
/*!40000 ALTER TABLE `mdl_block_positions` DISABLE KEYS */;
INSERT INTO `mdl_block_positions` VALUES (1,11,214,'course-view-topics','',1,'side-pre',0);
/*!40000 ALTER TABLE `mdl_block_positions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_block_recent_activity`
--

DROP TABLE IF EXISTS `mdl_block_recent_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_block_recent_activity` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `courseid` bigint(10) NOT NULL,
  `cmid` bigint(10) NOT NULL,
  `timecreated` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `action` tinyint(1) NOT NULL,
  `modname` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_blocreceacti_coutim_ix` (`courseid`,`timecreated`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Recent activity block';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_block_recent_activity`
--

LOCK TABLES `mdl_block_recent_activity` WRITE;
/*!40000 ALTER TABLE `mdl_block_recent_activity` DISABLE KEYS */;
INSERT INTO `mdl_block_recent_activity` VALUES (1,2,2,1756264617,2,0,NULL),(2,88,138,1756561330,2,0,NULL);
/*!40000 ALTER TABLE `mdl_block_recent_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_block_recentlyaccesseditems`
--

DROP TABLE IF EXISTS `mdl_block_recentlyaccesseditems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_block_recentlyaccesseditems` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `courseid` bigint(10) NOT NULL,
  `cmid` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `timeaccess` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_blocrece_usecoucmi_uix` (`userid`,`courseid`,`cmid`),
  KEY `mdl_blocrece_use_ix` (`userid`),
  KEY `mdl_blocrece_cou_ix` (`courseid`),
  KEY `mdl_blocrece_cmi_ix` (`cmid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Most recently accessed items accessed by a user';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_block_recentlyaccesseditems`
--

LOCK TABLES `mdl_block_recentlyaccesseditems` WRITE;
/*!40000 ALTER TABLE `mdl_block_recentlyaccesseditems` DISABLE KEYS */;
INSERT INTO `mdl_block_recentlyaccesseditems` VALUES (2,88,138,2,1756563204);
/*!40000 ALTER TABLE `mdl_block_recentlyaccesseditems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_block_rss_client`
--

DROP TABLE IF EXISTS `mdl_block_rss_client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_block_rss_client` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `title` longtext NOT NULL,
  `preferredtitle` varchar(64) NOT NULL DEFAULT '',
  `description` longtext NOT NULL,
  `shared` tinyint(2) NOT NULL DEFAULT 0,
  `url` varchar(255) NOT NULL DEFAULT '',
  `skiptime` bigint(10) NOT NULL DEFAULT 0,
  `skipuntil` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Remote news feed information. Contains the news feed id, the';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_block_rss_client`
--

LOCK TABLES `mdl_block_rss_client` WRITE;
/*!40000 ALTER TABLE `mdl_block_rss_client` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_block_rss_client` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_blog_association`
--

DROP TABLE IF EXISTS `mdl_blog_association`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_blog_association` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `contextid` bigint(10) NOT NULL,
  `blogid` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_blogasso_con_ix` (`contextid`),
  KEY `mdl_blogasso_blo_ix` (`blogid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Associations of blog entries with courses and module instanc';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_blog_association`
--

LOCK TABLES `mdl_blog_association` WRITE;
/*!40000 ALTER TABLE `mdl_blog_association` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_blog_association` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_blog_external`
--

DROP TABLE IF EXISTS `mdl_blog_external`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_blog_external` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` longtext DEFAULT NULL,
  `url` longtext NOT NULL,
  `filtertags` varchar(255) DEFAULT NULL,
  `failedlastsync` tinyint(1) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) DEFAULT NULL,
  `timefetched` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_blogexte_use_ix` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='External blog links used for RSS copying of blog entries to ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_blog_external`
--

LOCK TABLES `mdl_blog_external` WRITE;
/*!40000 ALTER TABLE `mdl_blog_external` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_blog_external` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_book`
--

DROP TABLE IF EXISTS `mdl_book`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_book` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `intro` longtext DEFAULT NULL,
  `introformat` smallint(4) NOT NULL DEFAULT 0,
  `numbering` smallint(4) NOT NULL DEFAULT 0,
  `navstyle` smallint(4) NOT NULL DEFAULT 1,
  `customtitles` tinyint(2) NOT NULL DEFAULT 0,
  `revision` bigint(10) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Defines book';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_book`
--

LOCK TABLES `mdl_book` WRITE;
/*!40000 ALTER TABLE `mdl_book` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_book` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_book_chapters`
--

DROP TABLE IF EXISTS `mdl_book_chapters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_book_chapters` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `bookid` bigint(10) NOT NULL DEFAULT 0,
  `pagenum` bigint(10) NOT NULL DEFAULT 0,
  `subchapter` bigint(10) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` longtext NOT NULL,
  `contentformat` smallint(4) NOT NULL DEFAULT 0,
  `hidden` tinyint(2) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `importsrc` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `mdl_bookchap_boo_ix` (`bookid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Defines book_chapters';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_book_chapters`
--

LOCK TABLES `mdl_book_chapters` WRITE;
/*!40000 ALTER TABLE `mdl_book_chapters` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_book_chapters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_cache_filters`
--

DROP TABLE IF EXISTS `mdl_cache_filters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_cache_filters` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `filter` varchar(32) NOT NULL DEFAULT '',
  `version` bigint(10) NOT NULL DEFAULT 0,
  `md5key` varchar(32) NOT NULL DEFAULT '',
  `rawtext` longtext NOT NULL,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_cachfilt_filmd5_ix` (`filter`,`md5key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='For keeping information about cached data';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_cache_filters`
--

LOCK TABLES `mdl_cache_filters` WRITE;
/*!40000 ALTER TABLE `mdl_cache_filters` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_cache_filters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_cache_flags`
--

DROP TABLE IF EXISTS `mdl_cache_flags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_cache_flags` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `flagtype` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `value` longtext NOT NULL,
  `expiry` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_cachflag_fla_ix` (`flagtype`),
  KEY `mdl_cachflag_nam_ix` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Cache of time-sensitive flags';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_cache_flags`
--

LOCK TABLES `mdl_cache_flags` WRITE;
/*!40000 ALTER TABLE `mdl_cache_flags` DISABLE KEYS */;
INSERT INTO `mdl_cache_flags` VALUES (1,'userpreferenceschanged','2',1756559754,'1',1756588554),(2,'accesslib/dirtyusers','2',1756527542,'1',1756556342),(3,'accesslib/dirtycontexts','/1/3/11',1756513258,'1',1756542058),(4,'accesslib/dirtycontexts','/1/3/21',1756527545,'1',1756556345),(5,'accesslib/dirtycontexts','/1/3/22',1756527531,'1',1756556331),(6,'accesslib/dirtycontexts','/1/3/23',1756527518,'1',1756556318),(7,'accesslib/dirtycontexts','/1/3/24',1756527508,'1',1756556308),(8,'accesslib/dirtycontexts','/1/3/25',1756527498,'1',1756556298),(9,'accesslib/dirtycontexts','/1/3/26',1756527486,'1',1756556286),(10,'accesslib/dirtycontexts','/1/3/27',1756527476,'1',1756556276),(11,'accesslib/dirtycontexts','/1/3/28',1756527465,'1',1756556265),(12,'accesslib/dirtycontexts','/1/3/29',1756527454,'1',1756556254),(13,'accesslib/dirtycontexts','/1/3/30',1756527441,'1',1756556241),(14,'accesslib/dirtycontexts','/1/3/31',1756527429,'1',1756556229),(15,'accesslib/dirtycontexts','/1/3/32',1756527417,'1',1756556217),(16,'accesslib/dirtycontexts','/1/3/33',1756527407,'1',1756556207),(17,'accesslib/dirtycontexts','/1/3/34',1756527397,'1',1756556197),(18,'accesslib/dirtycontexts','/1/3/35',1756527386,'1',1756556186),(19,'accesslib/dirtycontexts','/1/3/36',1756527371,'1',1756556171),(20,'accesslib/dirtycontexts','/1/3/37',1756527361,'1',1756556161),(21,'accesslib/dirtycontexts','/1/3/38',1756527350,'1',1756556150),(22,'accesslib/dirtycontexts','/1/3/39',1756527340,'1',1756556140),(23,'accesslib/dirtycontexts','/1/3/40',1756527329,'1',1756556129),(24,'accesslib/dirtycontexts','/1/3/41',1756527318,'1',1756556118),(25,'accesslib/dirtycontexts','/1/3/42',1756527301,'1',1756556101),(26,'accesslib/dirtycontexts','/1/3/43',1756527289,'1',1756556089),(27,'accesslib/dirtycontexts','/1/3/44',1756527278,'1',1756556078),(28,'accesslib/dirtycontexts','/1/3/45',1756527268,'1',1756556068),(29,'accesslib/dirtycontexts','/1/3/46',1756527256,'1',1756556056),(30,'accesslib/dirtycontexts','/1/3/47',1756527243,'1',1756556043),(31,'accesslib/dirtycontexts','/1/3/48',1756527233,'1',1756556033),(32,'accesslib/dirtycontexts','/1/3/49',1756527221,'1',1756556021),(33,'accesslib/dirtycontexts','/1/3/50',1756527211,'1',1756556011),(34,'accesslib/dirtycontexts','/1/3/51',1756527201,'1',1756556001),(35,'accesslib/dirtycontexts','/1/3/52',1756527189,'1',1756555989),(36,'accesslib/dirtycontexts','/1/3/53',1756527176,'1',1756555976),(37,'accesslib/dirtycontexts','/1/3/54',1756527167,'1',1756555967),(38,'accesslib/dirtycontexts','/1/3/55',1756527157,'1',1756555957),(39,'accesslib/dirtycontexts','/1/3/56',1756527145,'1',1756555945),(40,'accesslib/dirtycontexts','/1/3/57',1756527135,'1',1756555935),(41,'accesslib/dirtycontexts','/1/3/58',1756527123,'1',1756555923),(42,'accesslib/dirtycontexts','/1/3/59',1756527110,'1',1756555910),(43,'accesslib/dirtycontexts','/1/3/60',1756527099,'1',1756555899),(44,'accesslib/dirtycontexts','/1/3/61',1756527090,'1',1756555890),(45,'accesslib/dirtycontexts','/1/3/62',1756527078,'1',1756555878),(46,'accesslib/dirtycontexts','/1',1756524665,'1',1756553465),(47,'accesslib/dirtyusers','3',1756527543,'1',1756556343),(48,'accesslib/dirtyusers','4',1756527544,'1',1756556344),(49,'accesslib/dirtyusers','5',1756527544,'1',1756556344),(50,'accesslib/dirtyusers','6',1756527544,'1',1756556344),(51,'accesslib/dirtyusers','7',1756527544,'1',1756556344),(52,'accesslib/dirtyusers','8',1756527545,'1',1756556345),(53,'accesslib/dirtycontexts','/1/115/123',1756528989,'1',1756557789),(54,'accesslib/dirtycontexts','/1/115/124',1756528977,'1',1756557777),(55,'accesslib/dirtycontexts','/1/115/125',1756528967,'1',1756557767),(56,'accesslib/dirtycontexts','/1/115/126',1756528958,'1',1756557758),(57,'accesslib/dirtycontexts','/1/115/127',1756528945,'1',1756557745),(58,'accesslib/dirtycontexts','/1/115/128',1756528933,'1',1756557733),(59,'accesslib/dirtycontexts','/1/115/129',1756528922,'1',1756557722),(60,'accesslib/dirtycontexts','/1/115/130',1756528911,'1',1756557711),(61,'accesslib/dirtycontexts','/1/115/131',1756528900,'1',1756557700),(62,'accesslib/dirtycontexts','/1/115/132',1756528890,'1',1756557690),(63,'accesslib/dirtycontexts','/1/115/133',1756528878,'1',1756557678),(64,'accesslib/dirtycontexts','/1/115/134',1756528867,'1',1756557667),(65,'accesslib/dirtycontexts','/1/115/135',1756528856,'1',1756557656),(66,'accesslib/dirtycontexts','/1/115/136',1756528846,'1',1756557646),(67,'accesslib/dirtycontexts','/1/115/137',1756528835,'1',1756557635),(68,'accesslib/dirtycontexts','/1/115/138',1756528822,'1',1756557622),(69,'accesslib/dirtycontexts','/1/115/139',1756528811,'1',1756557611),(70,'accesslib/dirtycontexts','/1/115/140',1756528799,'1',1756557599),(71,'accesslib/dirtycontexts','/1/115/141',1756528788,'1',1756557588),(72,'accesslib/dirtycontexts','/1/115/142',1756528776,'1',1756557576),(73,'accesslib/dirtycontexts','/1/115/143',1756528763,'1',1756557563),(74,'accesslib/dirtycontexts','/1/115/144',1756528753,'1',1756557553),(75,'accesslib/dirtycontexts','/1/115/145',1756528743,'1',1756557543),(76,'accesslib/dirtycontexts','/1/115/146',1756528732,'1',1756557532),(77,'accesslib/dirtycontexts','/1/115/147',1756528721,'1',1756557521),(78,'accesslib/dirtycontexts','/1/115/148',1756528711,'1',1756557511),(79,'accesslib/dirtycontexts','/1/115/149',1756528699,'1',1756557499),(80,'accesslib/dirtycontexts','/1/115/150',1756528689,'1',1756557489),(81,'accesslib/dirtycontexts','/1/115/151',1756528677,'1',1756557477),(82,'accesslib/dirtycontexts','/1/115/152',1756528665,'1',1756557465),(83,'accesslib/dirtycontexts','/1/115/153',1756528654,'1',1756557454),(84,'accesslib/dirtycontexts','/1/115/154',1756528644,'1',1756557444),(85,'accesslib/dirtycontexts','/1/115/155',1756528632,'1',1756557432),(86,'accesslib/dirtycontexts','/1/115/156',1756528620,'1',1756557420),(87,'accesslib/dirtycontexts','/1/115/157',1756528610,'1',1756557410),(88,'accesslib/dirtycontexts','/1/115/158',1756528598,'1',1756557398),(89,'accesslib/dirtycontexts','/1/115/159',1756528587,'1',1756557387),(90,'accesslib/dirtycontexts','/1/115/160',1756528572,'1',1756557372),(91,'accesslib/dirtycontexts','/1/115/161',1756528561,'1',1756557361),(92,'accesslib/dirtycontexts','/1/115/162',1756528550,'1',1756557350),(93,'accesslib/dirtycontexts','/1/115/163',1756528538,'1',1756557338),(94,'accesslib/dirtycontexts','/1/115/164',1756528527,'1',1756557327),(95,'accesslib/dirtycontexts','/1/207/213',1756529937,'1',1756558737),(96,'accesslib/dirtycontexts','/1/207/214',1756529942,'1',1756558742),(97,'accesslib/dirtycontexts','/1/207/215',1756529944,'1',1756558744),(98,'accesslib/dirtycontexts','/1/207/216',1756529950,'1',1756558750),(99,'accesslib/dirtycontexts','/1/207/217',1756529954,'1',1756558754),(100,'accesslib/dirtycontexts','/1/207/218',1756529959,'1',1756558759),(101,'accesslib/dirtycontexts','/1/207/219',1756529963,'1',1756558763),(102,'accesslib/dirtycontexts','/1/208/220',1756554548,'1',1756583348),(103,'accesslib/dirtycontexts','/1/208/221',1756554551,'1',1756583351),(104,'accesslib/dirtycontexts','/1/208/222',1756554555,'1',1756583355),(105,'accesslib/dirtycontexts','/1/208/223',1756554560,'1',1756583360),(106,'accesslib/dirtycontexts','/1/208/224',1756554564,'1',1756583364),(107,'accesslib/dirtycontexts','/1/208/225',1756554568,'1',1756583368),(108,'accesslib/dirtycontexts','/1/208/226',1756554572,'1',1756583372),(109,'accesslib/dirtycontexts','/1/207/227',1756555280,'1',1756584080),(110,'accesslib/dirtycontexts','/1/207/228',1756555220,'1',1756584020),(111,'accesslib/dirtycontexts','/1/207/229',1756555151,'1',1756583951),(112,'accesslib/dirtycontexts','/1/207/230',1756555085,'1',1756583885),(113,'accesslib/dirtycontexts','/1/207/231',1756555049,'1',1756583849),(114,'accesslib/dirtycontexts','/1/207/232',1756555019,'1',1756583819),(115,'accesslib/dirtycontexts','/1/207/233',1756554968,'1',1756583768),(116,'accesslib/dirtycontexts','/1/209/241',1756555435,'1',1756584235),(117,'accesslib/dirtycontexts','/1/209/242',1756555439,'1',1756584239),(118,'accesslib/dirtycontexts','/1/209/243',1756555443,'1',1756584243),(119,'accesslib/dirtycontexts','/1/209/244',1756555448,'1',1756584248),(120,'accesslib/dirtycontexts','/1/209/245',1756555452,'1',1756584252),(121,'accesslib/dirtycontexts','/1/209/246',1756555456,'1',1756584256),(122,'accesslib/dirtycontexts','/1/209/247',1756555461,'1',1756584261),(123,'accesslib/dirtycontexts','/1/210/248',1756555633,'1',1756584433),(124,'accesslib/dirtycontexts','/1/210/249',1756555635,'1',1756584435),(125,'accesslib/dirtycontexts','/1/210/250',1756555639,'1',1756584439),(126,'accesslib/dirtycontexts','/1/210/251',1756555642,'1',1756584442),(127,'accesslib/dirtycontexts','/1/210/252',1756555647,'1',1756584447),(128,'accesslib/dirtycontexts','/1/210/253',1756555650,'1',1756584450),(129,'accesslib/dirtycontexts','/1/210/254',1756555656,'1',1756584456),(130,'accesslib/dirtycontexts','/1/211/255',1756555897,'1',1756584697),(131,'accesslib/dirtycontexts','/1/211/256',1756555902,'1',1756584702),(132,'accesslib/dirtycontexts','/1/211/257',1756555905,'1',1756584705),(133,'accesslib/dirtycontexts','/1/211/258',1756555910,'1',1756584710),(134,'accesslib/dirtycontexts','/1/211/259',1756555914,'1',1756584714),(135,'accesslib/dirtycontexts','/1/211/260',1756555917,'1',1756584717),(136,'accesslib/dirtycontexts','/1/211/261',1756555923,'1',1756584723),(137,'accesslib/dirtycontexts','/1/211/262',1756555926,'1',1756584726),(138,'accesslib/dirtycontexts','/1/212/263',1756556260,'1',1756585060),(139,'accesslib/dirtycontexts','/1/212/264',1756556265,'1',1756585065),(140,'accesslib/dirtycontexts','/1/212/265',1756556268,'1',1756585068),(141,'accesslib/dirtycontexts','/1/212/266',1756556274,'1',1756585074),(142,'accesslib/dirtycontexts','/1/212/267',1756556278,'1',1756585078),(143,'accesslib/dirtycontexts','/1/212/268',1756556285,'1',1756585085),(144,'accesslib/dirtycontexts','/1/212/269',1756556290,'1',1756585090),(145,'accesslib/dirtycontexts','/1/212/270',1756556294,'1',1756585094);
/*!40000 ALTER TABLE `mdl_cache_flags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_capabilities`
--

DROP TABLE IF EXISTS `mdl_capabilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_capabilities` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `captype` varchar(50) NOT NULL DEFAULT '',
  `contextlevel` bigint(10) NOT NULL DEFAULT 0,
  `component` varchar(100) NOT NULL DEFAULT '',
  `riskbitmask` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_capa_nam_uix` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=729 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='this defines all capabilities';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_capabilities`
--

LOCK TABLES `mdl_capabilities` WRITE;
/*!40000 ALTER TABLE `mdl_capabilities` DISABLE KEYS */;
INSERT INTO `mdl_capabilities` VALUES (1,'moodle/site:config','write',10,'moodle',62),(2,'moodle/site:configview','read',10,'moodle',0),(3,'moodle/site:readallmessages','read',10,'moodle',8),(4,'moodle/site:manageallmessaging','write',10,'moodle',8),(5,'moodle/site:deleteanymessage','write',10,'moodle',32),(6,'moodle/site:sendmessage','write',10,'moodle',16),(7,'moodle/site:senderrormessage','write',10,'moodle',16),(8,'moodle/site:deleteownmessage','write',10,'moodle',0),(9,'moodle/site:approvecourse','write',40,'moodle',4),(10,'moodle/backup:backupcourse','write',50,'moodle',28),(11,'moodle/backup:backupsection','write',50,'moodle',28),(12,'moodle/backup:backupactivity','write',70,'moodle',28),(13,'moodle/backup:backuptargetimport','read',50,'moodle',28),(14,'moodle/backup:downloadfile','write',50,'moodle',28),(15,'moodle/backup:configure','write',50,'moodle',28),(16,'moodle/backup:userinfo','read',50,'moodle',8),(17,'moodle/backup:anonymise','read',50,'moodle',8),(18,'moodle/restore:restorecourse','write',50,'moodle',28),(19,'moodle/restore:restoresection','write',50,'moodle',28),(20,'moodle/restore:restoreactivity','write',50,'moodle',28),(21,'moodle/restore:viewautomatedfilearea','write',50,'moodle',28),(22,'moodle/restore:restoretargetimport','write',50,'moodle',28),(23,'moodle/restore:uploadfile','write',50,'moodle',28),(24,'moodle/restore:configure','write',50,'moodle',28),(25,'moodle/restore:rolldates','write',50,'moodle',0),(26,'moodle/restore:userinfo','write',50,'moodle',30),(27,'moodle/restore:createuser','write',10,'moodle',24),(28,'moodle/site:manageblocks','write',80,'moodle',20),(29,'moodle/site:accessallgroups','read',70,'moodle',0),(30,'moodle/site:viewanonymousevents','read',70,'moodle',8),(31,'moodle/site:viewfullnames','read',70,'moodle',0),(32,'moodle/site:viewuseridentity','read',70,'moodle',0),(33,'moodle/site:viewreports','read',50,'moodle',8),(34,'moodle/site:trustcontent','write',70,'moodle',4),(35,'moodle/site:uploadusers','write',10,'moodle',24),(36,'moodle/filter:manage','write',50,'moodle',0),(37,'moodle/user:create','write',10,'moodle',24),(38,'moodle/user:delete','write',10,'moodle',40),(39,'moodle/user:update','write',10,'moodle',24),(40,'moodle/user:viewdetails','read',50,'moodle',0),(41,'moodle/user:viewalldetails','read',30,'moodle',8),(42,'moodle/user:viewlastip','read',30,'moodle',8),(43,'moodle/user:viewhiddendetails','read',50,'moodle',8),(44,'moodle/user:loginas','write',50,'moodle',30),(45,'moodle/user:managesyspages','write',10,'moodle',0),(46,'moodle/user:manageblocks','write',30,'moodle',0),(47,'moodle/user:manageownblocks','write',10,'moodle',0),(48,'moodle/user:manageownfiles','write',10,'moodle',0),(49,'moodle/user:ignoreuserquota','write',10,'moodle',0),(50,'moodle/my:configsyspages','write',10,'moodle',0),(51,'moodle/role:assign','write',50,'moodle',28),(52,'moodle/role:review','read',50,'moodle',8),(53,'moodle/role:override','write',50,'moodle',28),(54,'moodle/role:safeoverride','write',50,'moodle',16),(55,'moodle/role:manage','write',10,'moodle',28),(56,'moodle/role:switchroles','read',50,'moodle',12),(57,'moodle/category:manage','write',40,'moodle',4),(58,'moodle/category:viewcourselist','read',40,'moodle',0),(59,'moodle/category:viewhiddencategories','read',40,'moodle',0),(60,'moodle/cohort:manage','write',40,'moodle',0),(61,'moodle/cohort:assign','write',40,'moodle',0),(62,'moodle/cohort:view','read',50,'moodle',0),(63,'moodle/course:create','write',40,'moodle',4),(64,'moodle/course:creategroupconversations','write',50,'moodle',4),(65,'moodle/course:request','write',40,'moodle',0),(66,'moodle/course:delete','write',50,'moodle',32),(67,'moodle/course:update','write',50,'moodle',4),(68,'moodle/course:view','read',50,'moodle',0),(69,'moodle/course:enrolreview','read',50,'moodle',8),(70,'moodle/course:enrolconfig','write',50,'moodle',8),(71,'moodle/course:reviewotherusers','read',50,'moodle',0),(72,'moodle/course:bulkmessaging','write',50,'moodle',16),(73,'moodle/course:viewhiddenuserfields','read',50,'moodle',8),(74,'moodle/course:viewhiddencourses','read',50,'moodle',0),(75,'moodle/course:visibility','write',50,'moodle',0),(76,'moodle/course:managefiles','write',50,'moodle',4),(77,'moodle/course:ignoreavailabilityrestrictions','read',70,'moodle',0),(78,'moodle/course:ignorefilesizelimits','write',50,'moodle',0),(79,'moodle/course:manageactivities','write',70,'moodle',4),(80,'moodle/course:activityvisibility','write',70,'moodle',0),(81,'moodle/course:viewhiddenactivities','read',70,'moodle',0),(82,'moodle/course:viewparticipants','read',50,'moodle',0),(83,'moodle/course:changefullname','write',50,'moodle',4),(84,'moodle/course:changeshortname','write',50,'moodle',4),(85,'moodle/course:changelockedcustomfields','write',50,'moodle',16),(86,'moodle/course:configurecustomfields','write',10,'moodle',16),(87,'moodle/course:renameroles','write',50,'moodle',0),(88,'moodle/course:changeidnumber','write',50,'moodle',4),(89,'moodle/course:changecategory','write',50,'moodle',4),(90,'moodle/course:changesummary','write',50,'moodle',4),(91,'moodle/course:setforcedlanguage','write',50,'moodle',0),(92,'moodle/site:viewparticipants','read',10,'moodle',0),(93,'moodle/course:isincompletionreports','read',50,'moodle',0),(94,'moodle/course:viewscales','read',50,'moodle',0),(95,'moodle/course:managescales','write',50,'moodle',0),(96,'moodle/course:managegroups','write',50,'moodle',4),(97,'moodle/course:reset','write',50,'moodle',32),(98,'moodle/course:viewsuspendedusers','read',50,'moodle',0),(99,'moodle/course:tag','write',50,'moodle',16),(100,'moodle/blog:view','read',10,'moodle',0),(101,'moodle/blog:search','read',10,'moodle',0),(102,'moodle/blog:viewdrafts','read',10,'moodle',8),(103,'moodle/blog:create','write',10,'moodle',16),(104,'moodle/blog:manageentries','write',10,'moodle',16),(105,'moodle/blog:manageexternal','write',10,'moodle',16),(106,'moodle/calendar:manageownentries','write',50,'moodle',16),(107,'moodle/calendar:managegroupentries','write',50,'moodle',16),(108,'moodle/calendar:manageentries','write',50,'moodle',16),(109,'moodle/user:editprofile','write',30,'moodle',24),(110,'moodle/user:editownprofile','write',10,'moodle',16),(111,'moodle/user:changeownpassword','write',10,'moodle',0),(112,'moodle/user:readuserposts','read',30,'moodle',0),(113,'moodle/user:readuserblogs','read',30,'moodle',0),(114,'moodle/user:viewuseractivitiesreport','read',30,'moodle',8),(115,'moodle/user:editmessageprofile','write',30,'moodle',16),(116,'moodle/user:editownmessageprofile','write',10,'moodle',0),(117,'moodle/question:managecategory','write',50,'moodle',20),(118,'moodle/question:add','write',50,'moodle',20),(119,'moodle/question:editmine','write',50,'moodle',20),(120,'moodle/question:editall','write',50,'moodle',20),(121,'moodle/question:viewmine','read',50,'moodle',0),(122,'moodle/question:viewall','read',50,'moodle',0),(123,'moodle/question:usemine','read',50,'moodle',0),(124,'moodle/question:useall','read',50,'moodle',0),(125,'moodle/question:movemine','write',50,'moodle',0),(126,'moodle/question:moveall','write',50,'moodle',0),(127,'moodle/question:config','write',10,'moodle',2),(128,'moodle/question:flag','write',50,'moodle',0),(129,'moodle/question:tagmine','write',50,'moodle',0),(130,'moodle/question:tagall','write',50,'moodle',0),(131,'moodle/site:doclinks','read',10,'moodle',0),(132,'moodle/course:sectionvisibility','write',50,'moodle',0),(133,'moodle/course:useremail','write',50,'moodle',0),(134,'moodle/course:viewhiddensections','read',50,'moodle',0),(135,'moodle/course:setcurrentsection','write',50,'moodle',0),(136,'moodle/course:movesections','write',50,'moodle',0),(137,'moodle/site:mnetlogintoremote','read',10,'moodle',0),(138,'moodle/grade:viewall','read',50,'moodle',8),(139,'moodle/grade:view','read',50,'moodle',0),(140,'moodle/grade:viewhidden','read',50,'moodle',8),(141,'moodle/grade:import','write',50,'moodle',12),(142,'moodle/grade:export','read',50,'moodle',8),(143,'moodle/grade:manage','write',50,'moodle',12),(144,'moodle/grade:edit','write',50,'moodle',12),(145,'moodle/grade:managegradingforms','write',50,'moodle',12),(146,'moodle/grade:sharegradingforms','write',10,'moodle',4),(147,'moodle/grade:managesharedforms','write',10,'moodle',4),(148,'moodle/grade:manageoutcomes','write',50,'moodle',0),(149,'moodle/grade:manageletters','write',50,'moodle',0),(150,'moodle/grade:hide','write',50,'moodle',0),(151,'moodle/grade:lock','write',50,'moodle',0),(152,'moodle/grade:unlock','write',50,'moodle',0),(153,'moodle/my:manageblocks','write',10,'moodle',0),(154,'moodle/notes:view','read',50,'moodle',0),(155,'moodle/notes:manage','write',50,'moodle',16),(156,'moodle/tag:manage','write',10,'moodle',16),(157,'moodle/tag:edit','write',10,'moodle',16),(158,'moodle/tag:flag','write',10,'moodle',16),(159,'moodle/tag:editblocks','write',10,'moodle',0),(160,'moodle/block:view','read',80,'moodle',0),(161,'moodle/block:edit','write',80,'moodle',20),(162,'moodle/portfolio:export','read',10,'moodle',0),(163,'moodle/comment:view','read',50,'moodle',0),(164,'moodle/comment:post','write',50,'moodle',24),(165,'moodle/comment:delete','write',50,'moodle',32),(166,'moodle/webservice:createtoken','write',10,'moodle',62),(167,'moodle/webservice:managealltokens','write',10,'moodle',42),(168,'moodle/webservice:createmobiletoken','write',10,'moodle',24),(169,'moodle/rating:view','read',50,'moodle',0),(170,'moodle/rating:viewany','read',50,'moodle',8),(171,'moodle/rating:viewall','read',50,'moodle',8),(172,'moodle/rating:rate','write',50,'moodle',0),(173,'moodle/course:markcomplete','write',50,'moodle',0),(174,'moodle/course:overridecompletion','write',50,'moodle',0),(175,'moodle/badges:manageglobalsettings','write',10,'moodle',34),(176,'moodle/badges:viewbadges','read',50,'moodle',0),(177,'moodle/badges:manageownbadges','write',30,'moodle',0),(178,'moodle/badges:viewotherbadges','read',30,'moodle',0),(179,'moodle/badges:earnbadge','write',50,'moodle',0),(180,'moodle/badges:createbadge','write',50,'moodle',16),(181,'moodle/badges:deletebadge','write',50,'moodle',32),(182,'moodle/badges:configuredetails','write',50,'moodle',16),(183,'moodle/badges:configurecriteria','write',50,'moodle',4),(184,'moodle/badges:configuremessages','write',50,'moodle',16),(185,'moodle/badges:awardbadge','write',50,'moodle',16),(186,'moodle/badges:revokebadge','write',50,'moodle',16),(187,'moodle/badges:viewawarded','read',50,'moodle',8),(188,'moodle/site:forcelanguage','read',10,'moodle',0),(189,'moodle/search:query','read',10,'moodle',0),(190,'moodle/competency:competencymanage','write',40,'moodle',0),(191,'moodle/competency:competencyview','read',40,'moodle',0),(192,'moodle/competency:competencygrade','write',50,'moodle',0),(193,'moodle/competency:coursecompetencymanage','write',50,'moodle',0),(194,'moodle/competency:coursecompetencyconfigure','write',70,'moodle',0),(195,'moodle/competency:coursecompetencygradable','read',50,'moodle',0),(196,'moodle/competency:coursecompetencyview','read',50,'moodle',0),(197,'moodle/competency:evidencedelete','write',30,'moodle',0),(198,'moodle/competency:planmanage','write',30,'moodle',0),(199,'moodle/competency:planmanagedraft','write',30,'moodle',0),(200,'moodle/competency:planmanageown','write',30,'moodle',0),(201,'moodle/competency:planmanageowndraft','write',30,'moodle',0),(202,'moodle/competency:planview','read',30,'moodle',0),(203,'moodle/competency:planviewdraft','read',30,'moodle',0),(204,'moodle/competency:planviewown','read',30,'moodle',0),(205,'moodle/competency:planviewowndraft','read',30,'moodle',0),(206,'moodle/competency:planrequestreview','write',30,'moodle',0),(207,'moodle/competency:planrequestreviewown','write',30,'moodle',0),(208,'moodle/competency:planreview','write',30,'moodle',0),(209,'moodle/competency:plancomment','write',30,'moodle',0),(210,'moodle/competency:plancommentown','write',30,'moodle',0),(211,'moodle/competency:usercompetencyview','read',30,'moodle',0),(212,'moodle/competency:usercompetencyrequestreview','write',30,'moodle',0),(213,'moodle/competency:usercompetencyrequestreviewown','write',30,'moodle',0),(214,'moodle/competency:usercompetencyreview','write',30,'moodle',0),(215,'moodle/competency:usercompetencycomment','write',30,'moodle',0),(216,'moodle/competency:usercompetencycommentown','write',30,'moodle',0),(217,'moodle/competency:templatemanage','write',40,'moodle',0),(218,'moodle/analytics:listinsights','read',50,'moodle',8),(219,'moodle/analytics:managemodels','write',10,'moodle',2),(220,'moodle/competency:templateview','read',40,'moodle',0),(221,'moodle/competency:userevidencemanage','write',30,'moodle',0),(222,'moodle/competency:userevidencemanageown','write',30,'moodle',0),(223,'moodle/competency:userevidenceview','read',30,'moodle',0),(224,'moodle/site:maintenanceaccess','write',10,'moodle',0),(225,'moodle/site:messageanyuser','write',10,'moodle',16),(226,'moodle/site:managecontextlocks','write',70,'moodle',0),(227,'moodle/course:togglecompletion','write',70,'moodle',0),(228,'moodle/analytics:listowninsights','read',10,'moodle',0),(229,'moodle/h5p:setdisplayoptions','write',70,'moodle',0),(230,'moodle/h5p:deploy','write',70,'moodle',4),(231,'moodle/h5p:updatelibraries','write',70,'moodle',4),(232,'moodle/course:recommendactivity','write',10,'moodle',0),(233,'moodle/contentbank:access','read',50,'moodle',0),(234,'moodle/contentbank:upload','write',50,'moodle',16),(235,'moodle/contentbank:deleteanycontent','write',50,'moodle',32),(236,'moodle/contentbank:deleteowncontent','write',50,'moodle',0),(237,'moodle/contentbank:manageanycontent','write',50,'moodle',32),(238,'moodle/contentbank:manageowncontent','write',50,'moodle',0),(239,'moodle/contentbank:useeditor','write',50,'moodle',16),(240,'moodle/contentbank:downloadcontent','read',50,'moodle',0),(241,'moodle/course:downloadcoursecontent','read',50,'moodle',0),(242,'moodle/course:configuredownloadcontent','write',50,'moodle',0),(243,'moodle/payment:manageaccounts','write',50,'moodle',42),(244,'moodle/payment:viewpayments','read',50,'moodle',8),(245,'moodle/contentbank:viewunlistedcontent','read',50,'moodle',0),(246,'moodle/reportbuilder:view','read',10,'moodle',0),(247,'moodle/reportbuilder:edit','write',10,'moodle',0),(248,'moodle/reportbuilder:editall','write',10,'moodle',0),(249,'moodle/reportbuilder:scheduleviewas','read',10,'moodle',0),(250,'mod/assign:view','read',70,'mod_assign',0),(251,'mod/assign:submit','write',70,'mod_assign',0),(252,'mod/assign:grade','write',70,'mod_assign',4),(253,'mod/assign:exportownsubmission','read',70,'mod_assign',0),(254,'mod/assign:addinstance','write',50,'mod_assign',4),(255,'mod/assign:editothersubmission','write',70,'mod_assign',41),(256,'mod/assign:grantextension','write',70,'mod_assign',0),(257,'mod/assign:revealidentities','write',70,'mod_assign',0),(258,'mod/assign:reviewgrades','write',70,'mod_assign',0),(259,'mod/assign:releasegrades','write',70,'mod_assign',0),(260,'mod/assign:managegrades','write',70,'mod_assign',0),(261,'mod/assign:manageallocations','write',70,'mod_assign',0),(262,'mod/assign:viewgrades','read',70,'mod_assign',0),(263,'mod/assign:viewblinddetails','write',70,'mod_assign',8),(264,'mod/assign:receivegradernotifications','read',70,'mod_assign',0),(265,'mod/assign:manageoverrides','write',70,'mod_assign',0),(266,'mod/assign:showhiddengrader','read',70,'mod_assign',0),(267,'mod/assign:viewownsubmissionsummary','read',70,'mod_assign',0),(268,'mod/assignment:view','read',70,'mod_assignment',0),(269,'mod/assignment:addinstance','write',50,'mod_assignment',4),(270,'mod/assignment:submit','write',70,'mod_assignment',0),(271,'mod/assignment:grade','write',70,'mod_assignment',4),(272,'mod/assignment:exportownsubmission','read',70,'mod_assignment',0),(273,'mod/bigbluebuttonbn:addinstance','write',50,'mod_bigbluebuttonbn',4),(274,'mod/bigbluebuttonbn:addinstancewithmeeting','write',70,'mod_bigbluebuttonbn',0),(275,'mod/bigbluebuttonbn:addinstancewithrecording','write',70,'mod_bigbluebuttonbn',0),(276,'mod/bigbluebuttonbn:join','read',70,'mod_bigbluebuttonbn',0),(277,'mod/bigbluebuttonbn:view','read',70,'mod_bigbluebuttonbn',0),(278,'mod/bigbluebuttonbn:managerecordings','write',70,'mod_bigbluebuttonbn',0),(279,'mod/bigbluebuttonbn:publishrecordings','write',70,'mod_bigbluebuttonbn',0),(280,'mod/bigbluebuttonbn:unpublishrecordings','write',70,'mod_bigbluebuttonbn',0),(281,'mod/bigbluebuttonbn:protectrecordings','write',70,'mod_bigbluebuttonbn',0),(282,'mod/bigbluebuttonbn:unprotectrecordings','write',70,'mod_bigbluebuttonbn',0),(283,'mod/bigbluebuttonbn:deleterecordings','write',70,'mod_bigbluebuttonbn',0),(284,'mod/bigbluebuttonbn:importrecordings','write',70,'mod_bigbluebuttonbn',0),(285,'mod/book:addinstance','write',50,'mod_book',4),(286,'mod/book:read','read',70,'mod_book',0),(287,'mod/book:viewhiddenchapters','read',70,'mod_book',0),(288,'mod/book:edit','write',70,'mod_book',4),(289,'mod/chat:addinstance','write',50,'mod_chat',4),(290,'mod/chat:chat','write',70,'mod_chat',16),(291,'mod/chat:readlog','read',70,'mod_chat',0),(292,'mod/chat:deletelog','write',70,'mod_chat',0),(293,'mod/chat:exportparticipatedsession','read',70,'mod_chat',8),(294,'mod/chat:exportsession','read',70,'mod_chat',8),(295,'mod/chat:view','read',70,'mod_chat',0),(296,'mod/choice:addinstance','write',50,'mod_choice',4),(297,'mod/choice:choose','write',70,'mod_choice',0),(298,'mod/choice:readresponses','read',70,'mod_choice',0),(299,'mod/choice:deleteresponses','write',70,'mod_choice',0),(300,'mod/choice:downloadresponses','read',70,'mod_choice',0),(301,'mod/choice:view','read',70,'mod_choice',0),(302,'mod/data:addinstance','write',50,'mod_data',4),(303,'mod/data:viewentry','read',70,'mod_data',0),(304,'mod/data:writeentry','write',70,'mod_data',16),(305,'mod/data:comment','write',70,'mod_data',16),(306,'mod/data:rate','write',70,'mod_data',0),(307,'mod/data:viewrating','read',70,'mod_data',0),(308,'mod/data:viewanyrating','read',70,'mod_data',8),(309,'mod/data:viewallratings','read',70,'mod_data',8),(310,'mod/data:approve','write',70,'mod_data',16),(311,'mod/data:manageentries','write',70,'mod_data',16),(312,'mod/data:managecomments','write',70,'mod_data',16),(313,'mod/data:managetemplates','write',70,'mod_data',20),(314,'mod/data:viewalluserpresets','read',70,'mod_data',0),(315,'mod/data:manageuserpresets','write',70,'mod_data',20),(316,'mod/data:exportentry','read',70,'mod_data',8),(317,'mod/data:exportownentry','read',70,'mod_data',0),(318,'mod/data:exportallentries','read',70,'mod_data',8),(319,'mod/data:exportuserinfo','read',70,'mod_data',8),(320,'mod/data:view','read',70,'mod_data',0),(321,'mod/feedback:addinstance','write',50,'mod_feedback',4),(322,'mod/feedback:view','read',70,'mod_feedback',0),(323,'mod/feedback:complete','write',70,'mod_feedback',16),(324,'mod/feedback:viewanalysepage','read',70,'mod_feedback',8),(325,'mod/feedback:deletesubmissions','write',70,'mod_feedback',0),(326,'mod/feedback:mapcourse','write',70,'mod_feedback',0),(327,'mod/feedback:edititems','write',70,'mod_feedback',20),(328,'mod/feedback:createprivatetemplate','write',70,'mod_feedback',16),(329,'mod/feedback:createpublictemplate','write',70,'mod_feedback',16),(330,'mod/feedback:deletetemplate','write',70,'mod_feedback',0),(331,'mod/feedback:viewreports','read',70,'mod_feedback',8),(332,'mod/feedback:receivemail','read',70,'mod_feedback',8),(333,'mod/folder:addinstance','write',50,'mod_folder',4),(334,'mod/folder:view','read',70,'mod_folder',0),(335,'mod/folder:managefiles','write',70,'mod_folder',20),(336,'mod/forum:addinstance','write',50,'mod_forum',4),(337,'mod/forum:viewdiscussion','read',70,'mod_forum',0),(338,'mod/forum:viewhiddentimedposts','read',70,'mod_forum',0),(339,'mod/forum:startdiscussion','write',70,'mod_forum',16),(340,'mod/forum:replypost','write',70,'mod_forum',16),(341,'mod/forum:addnews','write',70,'mod_forum',16),(342,'mod/forum:replynews','write',70,'mod_forum',16),(343,'mod/forum:viewrating','read',70,'mod_forum',0),(344,'mod/forum:viewanyrating','read',70,'mod_forum',8),(345,'mod/forum:viewallratings','read',70,'mod_forum',8),(346,'mod/forum:rate','write',70,'mod_forum',0),(347,'mod/forum:postprivatereply','write',70,'mod_forum',0),(348,'mod/forum:readprivatereplies','read',70,'mod_forum',0),(349,'mod/forum:createattachment','write',70,'mod_forum',16),(350,'mod/forum:deleteownpost','write',70,'mod_forum',0),(351,'mod/forum:deleteanypost','write',70,'mod_forum',0),(352,'mod/forum:splitdiscussions','write',70,'mod_forum',0),(353,'mod/forum:movediscussions','write',70,'mod_forum',0),(354,'mod/forum:pindiscussions','write',70,'mod_forum',0),(355,'mod/forum:editanypost','write',70,'mod_forum',16),(356,'mod/forum:viewqandawithoutposting','read',70,'mod_forum',0),(357,'mod/forum:viewsubscribers','read',70,'mod_forum',0),(358,'mod/forum:managesubscriptions','write',70,'mod_forum',16),(359,'mod/forum:postwithoutthrottling','write',70,'mod_forum',16),(360,'mod/forum:exportdiscussion','read',70,'mod_forum',8),(361,'mod/forum:exportforum','read',70,'mod_forum',8),(362,'mod/forum:exportpost','read',70,'mod_forum',8),(363,'mod/forum:exportownpost','read',70,'mod_forum',8),(364,'mod/forum:addquestion','write',70,'mod_forum',16),(365,'mod/forum:allowforcesubscribe','read',70,'mod_forum',0),(366,'mod/forum:canposttomygroups','write',70,'mod_forum',0),(367,'mod/forum:canoverridediscussionlock','write',70,'mod_forum',0),(368,'mod/forum:canoverridecutoff','write',70,'mod_forum',0),(369,'mod/forum:cantogglefavourite','write',70,'mod_forum',0),(370,'mod/forum:grade','write',70,'mod_forum',0),(371,'mod/glossary:addinstance','write',50,'mod_glossary',4),(372,'mod/glossary:view','read',70,'mod_glossary',0),(373,'mod/glossary:write','write',70,'mod_glossary',16),(374,'mod/glossary:manageentries','write',70,'mod_glossary',16),(375,'mod/glossary:managecategories','write',70,'mod_glossary',16),(376,'mod/glossary:comment','write',70,'mod_glossary',16),(377,'mod/glossary:managecomments','write',70,'mod_glossary',16),(378,'mod/glossary:import','write',70,'mod_glossary',16),(379,'mod/glossary:export','read',70,'mod_glossary',0),(380,'mod/glossary:approve','write',70,'mod_glossary',16),(381,'mod/glossary:rate','write',70,'mod_glossary',0),(382,'mod/glossary:viewrating','read',70,'mod_glossary',0),(383,'mod/glossary:viewanyrating','read',70,'mod_glossary',8),(384,'mod/glossary:viewallratings','read',70,'mod_glossary',8),(385,'mod/glossary:exportentry','read',70,'mod_glossary',8),(386,'mod/glossary:exportownentry','read',70,'mod_glossary',0),(387,'mod/h5pactivity:view','read',70,'mod_h5pactivity',0),(388,'mod/h5pactivity:addinstance','write',50,'mod_h5pactivity',0),(389,'mod/h5pactivity:submit','write',70,'mod_h5pactivity',0),(390,'mod/h5pactivity:reviewattempts','read',70,'mod_h5pactivity',0),(391,'mod/imscp:view','read',70,'mod_imscp',0),(392,'mod/imscp:addinstance','write',50,'mod_imscp',4),(393,'mod/label:addinstance','write',50,'mod_label',4),(394,'mod/label:view','read',70,'mod_label',0),(395,'mod/lesson:addinstance','write',50,'mod_lesson',4),(396,'mod/lesson:edit','write',70,'mod_lesson',4),(397,'mod/lesson:grade','write',70,'mod_lesson',20),(398,'mod/lesson:viewreports','read',70,'mod_lesson',8),(399,'mod/lesson:manage','write',70,'mod_lesson',0),(400,'mod/lesson:manageoverrides','write',70,'mod_lesson',0),(401,'mod/lesson:view','read',70,'mod_lesson',0),(402,'mod/lti:view','read',70,'mod_lti',0),(403,'mod/lti:addinstance','write',50,'mod_lti',4),(404,'mod/lti:manage','write',70,'mod_lti',8),(405,'mod/lti:admin','write',70,'mod_lti',8),(406,'mod/lti:addcoursetool','write',50,'mod_lti',0),(407,'mod/lti:addpreconfiguredinstance','write',50,'mod_lti',0),(408,'mod/lti:addmanualinstance','write',50,'mod_lti',0),(409,'mod/lti:requesttooladd','write',50,'mod_lti',0),(410,'mod/page:view','read',70,'mod_page',0),(411,'mod/page:addinstance','write',50,'mod_page',4),(412,'mod/quiz:view','read',70,'mod_quiz',0),(413,'mod/quiz:addinstance','write',50,'mod_quiz',4),(414,'mod/quiz:attempt','write',70,'mod_quiz',16),(415,'mod/quiz:reviewmyattempts','read',70,'mod_quiz',0),(416,'mod/quiz:manage','write',70,'mod_quiz',16),(417,'mod/quiz:manageoverrides','write',70,'mod_quiz',0),(418,'mod/quiz:viewoverrides','read',70,'mod_quiz',0),(419,'mod/quiz:preview','write',70,'mod_quiz',0),(420,'mod/quiz:grade','write',70,'mod_quiz',20),(421,'mod/quiz:regrade','write',70,'mod_quiz',16),(422,'mod/quiz:viewreports','read',70,'mod_quiz',8),(423,'mod/quiz:deleteattempts','write',70,'mod_quiz',32),(424,'mod/quiz:ignoretimelimits','read',70,'mod_quiz',0),(425,'mod/quiz:emailconfirmsubmission','read',70,'mod_quiz',0),(426,'mod/quiz:emailnotifysubmission','read',70,'mod_quiz',0),(427,'mod/quiz:emailwarnoverdue','read',70,'mod_quiz',0),(428,'mod/quiz:emailnotifyattemptgraded','read',70,'mod_quiz',0),(429,'mod/resource:view','read',70,'mod_resource',0),(430,'mod/resource:addinstance','write',50,'mod_resource',4),(431,'mod/scorm:addinstance','write',50,'mod_scorm',4),(432,'mod/scorm:viewreport','read',70,'mod_scorm',0),(433,'mod/scorm:skipview','read',70,'mod_scorm',0),(434,'mod/scorm:savetrack','write',70,'mod_scorm',0),(435,'mod/scorm:viewscores','read',70,'mod_scorm',0),(436,'mod/scorm:deleteresponses','write',70,'mod_scorm',0),(437,'mod/scorm:deleteownresponses','write',70,'mod_scorm',0),(438,'mod/survey:addinstance','write',50,'mod_survey',4),(439,'mod/survey:participate','read',70,'mod_survey',0),(440,'mod/survey:readresponses','read',70,'mod_survey',0),(441,'mod/survey:download','read',70,'mod_survey',0),(442,'mod/url:view','read',70,'mod_url',0),(443,'mod/url:addinstance','write',50,'mod_url',4),(444,'mod/wiki:addinstance','write',50,'mod_wiki',4),(445,'mod/wiki:viewpage','read',70,'mod_wiki',0),(446,'mod/wiki:editpage','write',70,'mod_wiki',16),(447,'mod/wiki:createpage','write',70,'mod_wiki',16),(448,'mod/wiki:viewcomment','read',70,'mod_wiki',0),(449,'mod/wiki:editcomment','write',70,'mod_wiki',16),(450,'mod/wiki:managecomment','write',70,'mod_wiki',0),(451,'mod/wiki:managefiles','write',70,'mod_wiki',0),(452,'mod/wiki:overridelock','write',70,'mod_wiki',0),(453,'mod/wiki:managewiki','write',70,'mod_wiki',0),(454,'mod/workshop:view','read',70,'mod_workshop',0),(455,'mod/workshop:addinstance','write',50,'mod_workshop',4),(456,'mod/workshop:switchphase','write',70,'mod_workshop',0),(457,'mod/workshop:editdimensions','write',70,'mod_workshop',4),(458,'mod/workshop:submit','write',70,'mod_workshop',0),(459,'mod/workshop:peerassess','write',70,'mod_workshop',0),(460,'mod/workshop:manageexamples','write',70,'mod_workshop',0),(461,'mod/workshop:allocate','write',70,'mod_workshop',0),(462,'mod/workshop:publishsubmissions','write',70,'mod_workshop',0),(463,'mod/workshop:viewauthornames','read',70,'mod_workshop',0),(464,'mod/workshop:viewreviewernames','read',70,'mod_workshop',0),(465,'mod/workshop:viewallsubmissions','read',70,'mod_workshop',0),(466,'mod/workshop:viewpublishedsubmissions','read',70,'mod_workshop',0),(467,'mod/workshop:viewauthorpublished','read',70,'mod_workshop',0),(468,'mod/workshop:viewallassessments','read',70,'mod_workshop',0),(469,'mod/workshop:overridegrades','write',70,'mod_workshop',0),(470,'mod/workshop:ignoredeadlines','write',70,'mod_workshop',0),(471,'mod/workshop:deletesubmissions','write',70,'mod_workshop',0),(472,'mod/workshop:exportsubmissions','read',70,'mod_workshop',0),(473,'auth/oauth2:managelinkedlogins','write',30,'auth_oauth2',0),(474,'enrol/category:synchronised','write',10,'enrol_category',0),(475,'enrol/category:config','write',50,'enrol_category',0),(476,'enrol/cohort:config','write',50,'enrol_cohort',0),(477,'enrol/cohort:unenrol','write',50,'enrol_cohort',0),(478,'enrol/database:unenrol','write',50,'enrol_database',0),(479,'enrol/database:config','write',50,'enrol_database',0),(480,'enrol/fee:config','write',50,'enrol_fee',0),(481,'enrol/fee:manage','write',50,'enrol_fee',0),(482,'enrol/fee:unenrol','write',50,'enrol_fee',0),(483,'enrol/fee:unenrolself','write',50,'enrol_fee',0),(484,'enrol/flatfile:manage','write',50,'enrol_flatfile',0),(485,'enrol/flatfile:unenrol','write',50,'enrol_flatfile',0),(486,'enrol/guest:config','write',50,'enrol_guest',0),(487,'enrol/imsenterprise:config','write',50,'enrol_imsenterprise',0),(488,'enrol/ldap:manage','write',50,'enrol_ldap',0),(489,'enrol/lti:config','write',50,'enrol_lti',0),(490,'enrol/lti:unenrol','write',50,'enrol_lti',0),(491,'enrol/manual:config','write',50,'enrol_manual',0),(492,'enrol/manual:enrol','write',50,'enrol_manual',0),(493,'enrol/manual:manage','write',50,'enrol_manual',0),(494,'enrol/manual:unenrol','write',50,'enrol_manual',0),(495,'enrol/manual:unenrolself','write',50,'enrol_manual',0),(496,'enrol/meta:config','write',50,'enrol_meta',0),(497,'enrol/meta:selectaslinked','read',50,'enrol_meta',0),(498,'enrol/meta:unenrol','write',50,'enrol_meta',0),(499,'enrol/mnet:config','write',50,'enrol_mnet',0),(500,'enrol/paypal:config','write',50,'enrol_paypal',0),(501,'enrol/paypal:manage','write',50,'enrol_paypal',0),(502,'enrol/paypal:unenrol','write',50,'enrol_paypal',0),(503,'enrol/paypal:unenrolself','write',50,'enrol_paypal',0),(504,'enrol/self:config','write',50,'enrol_self',0),(505,'enrol/self:manage','write',50,'enrol_self',0),(506,'enrol/self:holdkey','write',50,'enrol_self',0),(507,'enrol/self:unenrolself','write',50,'enrol_self',0),(508,'enrol/self:unenrol','write',50,'enrol_self',0),(509,'enrol/self:enrolself','write',50,'enrol_self',0),(510,'message/airnotifier:managedevice','write',10,'message_airnotifier',0),(511,'block/accessreview:addinstance','write',80,'block_accessreview',0),(512,'block/accessreview:view','read',80,'block_accessreview',0),(513,'block/activity_modules:addinstance','write',80,'block_activity_modules',20),(514,'block/activity_results:addinstance','write',80,'block_activity_results',20),(515,'block/admin_bookmarks:myaddinstance','write',10,'block_admin_bookmarks',0),(516,'block/admin_bookmarks:addinstance','write',80,'block_admin_bookmarks',20),(517,'block/badges:addinstance','read',80,'block_badges',0),(518,'block/badges:myaddinstance','read',10,'block_badges',8),(519,'block/blog_menu:addinstance','write',80,'block_blog_menu',20),(520,'block/blog_recent:addinstance','write',80,'block_blog_recent',20),(521,'block/blog_tags:addinstance','write',80,'block_blog_tags',20),(522,'block/calendar_month:myaddinstance','write',10,'block_calendar_month',0),(523,'block/calendar_month:addinstance','write',80,'block_calendar_month',20),(524,'block/calendar_upcoming:myaddinstance','write',10,'block_calendar_upcoming',0),(525,'block/calendar_upcoming:addinstance','write',80,'block_calendar_upcoming',20),(526,'block/comments:myaddinstance','write',10,'block_comments',0),(527,'block/comments:addinstance','write',80,'block_comments',20),(528,'block/completionstatus:addinstance','write',80,'block_completionstatus',20),(529,'block/course_list:myaddinstance','write',10,'block_course_list',0),(530,'block/course_list:addinstance','write',80,'block_course_list',20),(531,'block/course_summary:addinstance','write',80,'block_course_summary',20),(532,'block/feedback:addinstance','write',80,'block_feedback',20),(533,'block/globalsearch:myaddinstance','write',10,'block_globalsearch',0),(534,'block/globalsearch:addinstance','write',80,'block_globalsearch',0),(535,'block/glossary_random:myaddinstance','write',10,'block_glossary_random',0),(536,'block/glossary_random:addinstance','write',80,'block_glossary_random',20),(537,'block/html:myaddinstance','write',10,'block_html',0),(538,'block/html:addinstance','write',80,'block_html',20),(539,'block/login:addinstance','write',80,'block_login',20),(540,'block/lp:addinstance','write',10,'block_lp',0),(541,'block/lp:myaddinstance','write',10,'block_lp',0),(542,'block/mentees:myaddinstance','write',10,'block_mentees',0),(543,'block/mentees:addinstance','write',80,'block_mentees',20),(544,'block/mnet_hosts:myaddinstance','write',10,'block_mnet_hosts',0),(545,'block/mnet_hosts:addinstance','write',80,'block_mnet_hosts',20),(546,'block/myoverview:myaddinstance','write',10,'block_myoverview',0),(547,'block/myprofile:myaddinstance','write',10,'block_myprofile',0),(548,'block/myprofile:addinstance','write',80,'block_myprofile',20),(549,'block/navigation:myaddinstance','write',10,'block_navigation',0),(550,'block/navigation:addinstance','write',80,'block_navigation',20),(551,'block/news_items:myaddinstance','write',10,'block_news_items',0),(552,'block/news_items:addinstance','write',80,'block_news_items',20),(553,'block/online_users:myaddinstance','write',10,'block_online_users',0),(554,'block/online_users:addinstance','write',80,'block_online_users',20),(555,'block/online_users:viewlist','read',80,'block_online_users',0),(556,'block/private_files:myaddinstance','write',10,'block_private_files',0),(557,'block/private_files:addinstance','write',80,'block_private_files',20),(558,'block/recent_activity:addinstance','write',80,'block_recent_activity',20),(559,'block/recent_activity:viewaddupdatemodule','read',50,'block_recent_activity',0),(560,'block/recent_activity:viewdeletemodule','read',50,'block_recent_activity',0),(561,'block/recentlyaccessedcourses:myaddinstance','write',10,'block_recentlyaccessedcourses',0),(562,'block/recentlyaccesseditems:myaddinstance','write',10,'block_recentlyaccesseditems',0),(563,'block/rss_client:myaddinstance','write',10,'block_rss_client',0),(564,'block/rss_client:addinstance','write',80,'block_rss_client',20),(565,'block/rss_client:manageownfeeds','write',80,'block_rss_client',0),(566,'block/rss_client:manageanyfeeds','write',80,'block_rss_client',16),(567,'block/search_forums:addinstance','write',80,'block_search_forums',20),(568,'block/section_links:addinstance','write',80,'block_section_links',20),(569,'block/selfcompletion:addinstance','write',80,'block_selfcompletion',20),(570,'block/settings:myaddinstance','write',10,'block_settings',0),(571,'block/settings:addinstance','write',80,'block_settings',20),(572,'block/site_main_menu:addinstance','write',80,'block_site_main_menu',20),(573,'block/social_activities:addinstance','write',80,'block_social_activities',20),(574,'block/starredcourses:myaddinstance','write',10,'block_starredcourses',0),(575,'block/tag_flickr:addinstance','write',80,'block_tag_flickr',20),(576,'block/tag_youtube:addinstance','write',80,'block_tag_youtube',20),(577,'block/tags:myaddinstance','write',10,'block_tags',0),(578,'block/tags:addinstance','write',80,'block_tags',20),(579,'block/timeline:myaddinstance','write',10,'block_timeline',0),(580,'report/completion:view','read',50,'report_completion',8),(581,'report/courseoverview:view','read',10,'report_courseoverview',8),(582,'report/log:view','read',50,'report_log',8),(583,'report/log:viewtoday','read',50,'report_log',8),(584,'report/loglive:view','read',50,'report_loglive',8),(585,'report/outline:view','read',50,'report_outline',8),(586,'report/outline:viewuserreport','read',50,'report_outline',8),(587,'report/participation:view','read',50,'report_participation',8),(588,'report/performance:view','read',10,'report_performance',2),(589,'report/progress:view','read',50,'report_progress',8),(590,'report/questioninstances:view','read',10,'report_questioninstances',0),(591,'report/security:view','read',10,'report_security',2),(592,'report/stats:view','read',50,'report_stats',8),(593,'report/status:view','read',10,'report_status',2),(594,'report/usersessions:manageownsessions','write',30,'report_usersessions',0),(595,'gradeexport/ods:view','read',50,'gradeexport_ods',8),(596,'gradeexport/ods:publish','read',50,'gradeexport_ods',8),(597,'gradeexport/txt:view','read',50,'gradeexport_txt',8),(598,'gradeexport/txt:publish','read',50,'gradeexport_txt',8),(599,'gradeexport/xls:view','read',50,'gradeexport_xls',8),(600,'gradeexport/xls:publish','read',50,'gradeexport_xls',8),(601,'gradeexport/xml:view','read',50,'gradeexport_xml',8),(602,'gradeexport/xml:publish','read',50,'gradeexport_xml',8),(603,'gradeimport/csv:view','write',50,'gradeimport_csv',0),(604,'gradeimport/direct:view','write',50,'gradeimport_direct',0),(605,'gradeimport/xml:view','write',50,'gradeimport_xml',0),(606,'gradeimport/xml:publish','write',50,'gradeimport_xml',0),(607,'gradereport/grader:view','read',50,'gradereport_grader',8),(608,'gradereport/history:view','read',50,'gradereport_history',8),(609,'gradereport/outcomes:view','read',50,'gradereport_outcomes',8),(610,'gradereport/overview:view','read',50,'gradereport_overview',8),(611,'gradereport/singleview:view','read',50,'gradereport_singleview',8),(612,'gradereport/user:view','read',50,'gradereport_user',8),(613,'webservice/rest:use','read',50,'webservice_rest',0),(614,'webservice/soap:use','read',50,'webservice_soap',0),(615,'webservice/xmlrpc:use','read',50,'webservice_xmlrpc',0),(616,'repository/areafiles:view','read',70,'repository_areafiles',0),(617,'repository/contentbank:view','read',70,'repository_contentbank',0),(618,'repository/contentbank:accesscoursecontent','read',50,'repository_contentbank',0),(619,'repository/contentbank:accesscoursecategorycontent','read',40,'repository_contentbank',0),(620,'repository/contentbank:accessgeneralcontent','read',40,'repository_contentbank',0),(621,'repository/coursefiles:view','read',70,'repository_coursefiles',0),(622,'repository/dropbox:view','read',70,'repository_dropbox',0),(623,'repository/equella:view','read',70,'repository_equella',0),(624,'repository/filesystem:view','read',70,'repository_filesystem',0),(625,'repository/flickr:view','read',70,'repository_flickr',0),(626,'repository/flickr_public:view','read',70,'repository_flickr_public',0),(627,'repository/googledocs:view','read',70,'repository_googledocs',0),(628,'repository/local:view','read',70,'repository_local',0),(629,'repository/merlot:view','read',70,'repository_merlot',0),(630,'repository/nextcloud:view','read',70,'repository_nextcloud',0),(631,'repository/onedrive:view','read',70,'repository_onedrive',0),(632,'repository/recent:view','read',70,'repository_recent',0),(633,'repository/s3:view','read',70,'repository_s3',0),(634,'repository/upload:view','read',70,'repository_upload',0),(635,'repository/url:view','read',70,'repository_url',0),(636,'repository/user:view','read',70,'repository_user',0),(637,'repository/webdav:view','read',70,'repository_webdav',0),(638,'repository/wikimedia:view','read',70,'repository_wikimedia',0),(639,'repository/youtube:view','read',70,'repository_youtube',0),(640,'moodle/question:commentmine','write',50,'qbank_comment',0),(641,'moodle/question:commentall','write',50,'qbank_comment',0),(642,'qbank/customfields:changelockedcustomfields','write',50,'qbank_customfields',16),(643,'qbank/customfields:configurecustomfields','write',10,'qbank_customfields',16),(644,'qbank/customfields:viewhiddencustomfields','read',50,'qbank_customfields',0),(645,'tool/brickfield:viewcoursetools','read',50,'tool_brickfield',8),(646,'tool/brickfield:viewsystemtools','read',10,'tool_brickfield',2),(647,'tool/customlang:view','read',10,'tool_customlang',2),(648,'tool/customlang:edit','write',10,'tool_customlang',6),(649,'tool/customlang:export','read',10,'tool_customlang',2),(650,'tool/dataprivacy:managedatarequests','write',10,'tool_dataprivacy',60),(651,'tool/dataprivacy:requestdeleteforotheruser','write',10,'tool_dataprivacy',60),(652,'tool/dataprivacy:managedataregistry','write',10,'tool_dataprivacy',60),(653,'tool/dataprivacy:makedatarequestsforchildren','write',30,'tool_dataprivacy',24),(654,'tool/dataprivacy:makedatadeletionrequestsforchildren','write',30,'tool_dataprivacy',24),(655,'tool/dataprivacy:downloadownrequest','read',30,'tool_dataprivacy',0),(656,'tool/dataprivacy:downloadallrequests','read',30,'tool_dataprivacy',8),(657,'tool/dataprivacy:requestdelete','write',30,'tool_dataprivacy',32),(658,'tool/lpmigrate:frameworksmigrate','write',10,'tool_lpmigrate',0),(659,'tool/monitor:subscribe','read',50,'tool_monitor',8),(660,'tool/monitor:managerules','write',50,'tool_monitor',4),(661,'tool/monitor:managetool','write',10,'tool_monitor',4),(662,'tool/policy:accept','write',10,'tool_policy',0),(663,'tool/policy:acceptbehalf','write',30,'tool_policy',8),(664,'tool/policy:managedocs','write',10,'tool_policy',0),(665,'tool/policy:viewacceptances','read',10,'tool_policy',0),(666,'tool/recyclebin:deleteitems','write',50,'tool_recyclebin',32),(667,'tool/recyclebin:restoreitems','write',50,'tool_recyclebin',0),(668,'tool/recyclebin:viewitems','read',50,'tool_recyclebin',0),(669,'tool/uploaduser:uploaduserpictures','write',10,'tool_uploaduser',16),(670,'tool/usertours:managetours','write',10,'tool_usertours',4),(671,'contenttype/h5p:access','read',50,'contenttype_h5p',0),(672,'contenttype/h5p:upload','write',50,'contenttype_h5p',16),(673,'contenttype/h5p:useeditor','write',50,'contenttype_h5p',16),(674,'booktool/exportimscp:export','read',70,'booktool_exportimscp',0),(675,'booktool/importhtml:import','write',70,'booktool_importhtml',4),(676,'booktool/print:print','read',70,'booktool_print',0),(677,'forumreport/summary:view','read',70,'forumreport_summary',0),(678,'forumreport/summary:viewall','read',70,'forumreport_summary',8),(679,'quiz/grading:viewstudentnames','read',70,'quiz_grading',0),(680,'quiz/grading:viewidnumber','read',70,'quiz_grading',0),(681,'quiz/statistics:view','read',70,'quiz_statistics',0),(682,'quizaccess/seb:managetemplates','write',10,'quizaccess_seb',0),(683,'quizaccess/seb:bypassseb','read',70,'quizaccess_seb',0),(684,'quizaccess/seb:manage_seb_requiresafeexambrowser','write',70,'quizaccess_seb',0),(685,'quizaccess/seb:manage_seb_templateid','read',70,'quizaccess_seb',0),(686,'quizaccess/seb:manage_filemanager_sebconfigfile','write',70,'quizaccess_seb',0),(687,'quizaccess/seb:manage_seb_showsebdownloadlink','write',70,'quizaccess_seb',0),(688,'quizaccess/seb:manage_seb_allowedbrowserexamkeys','write',70,'quizaccess_seb',0),(689,'quizaccess/seb:manage_seb_linkquitseb','write',70,'quizaccess_seb',0),(690,'quizaccess/seb:manage_seb_userconfirmquit','write',70,'quizaccess_seb',0),(691,'quizaccess/seb:manage_seb_allowuserquitseb','write',70,'quizaccess_seb',0),(692,'quizaccess/seb:manage_seb_quitpassword','write',70,'quizaccess_seb',0),(693,'quizaccess/seb:manage_seb_allowreloadinexam','write',70,'quizaccess_seb',0),(694,'quizaccess/seb:manage_seb_showsebtaskbar','write',70,'quizaccess_seb',0),(695,'quizaccess/seb:manage_seb_showreloadbutton','write',70,'quizaccess_seb',0),(696,'quizaccess/seb:manage_seb_showtime','write',70,'quizaccess_seb',0),(697,'quizaccess/seb:manage_seb_showkeyboardlayout','write',70,'quizaccess_seb',0),(698,'quizaccess/seb:manage_seb_showwificontrol','write',70,'quizaccess_seb',0),(699,'quizaccess/seb:manage_seb_enableaudiocontrol','write',70,'quizaccess_seb',0),(700,'quizaccess/seb:manage_seb_muteonstartup','write',70,'quizaccess_seb',0),(701,'quizaccess/seb:manage_seb_allowspellchecking','write',70,'quizaccess_seb',0),(702,'quizaccess/seb:manage_seb_activateurlfiltering','write',70,'quizaccess_seb',0),(703,'quizaccess/seb:manage_seb_filterembeddedcontent','write',70,'quizaccess_seb',0),(704,'quizaccess/seb:manage_seb_expressionsallowed','write',70,'quizaccess_seb',0),(705,'quizaccess/seb:manage_seb_regexallowed','write',70,'quizaccess_seb',0),(706,'quizaccess/seb:manage_seb_expressionsblocked','write',70,'quizaccess_seb',0),(707,'quizaccess/seb:manage_seb_regexblocked','write',70,'quizaccess_seb',0),(708,'atto/h5p:addembed','write',70,'atto_h5p',0),(709,'atto/recordrtc:recordaudio','write',70,'atto_recordrtc',0),(710,'atto/recordrtc:recordvideo','write',70,'atto_recordrtc',0),(711,'mod/attendance:view','read',70,'mod_attendance',0),(712,'mod/attendance:addinstance','write',50,'mod_attendance',4),(713,'mod/attendance:viewreports','read',70,'mod_attendance',8),(714,'mod/attendance:takeattendances','write',70,'mod_attendance',32),(715,'mod/attendance:changeattendances','write',70,'mod_attendance',32),(716,'mod/attendance:manageattendances','write',70,'mod_attendance',2),(717,'mod/attendance:changepreferences','write',70,'mod_attendance',2),(718,'mod/attendance:import','write',70,'mod_attendance',8),(719,'mod/attendance:export','read',70,'mod_attendance',8),(720,'mod/attendance:canbelisted','read',70,'mod_attendance',8),(721,'mod/attendance:managetemporaryusers','write',70,'mod_attendance',32),(722,'mod/attendance:viewsummaryreports','read',40,'mod_attendance',8),(723,'mod/attendance:warningemails','write',70,'mod_attendance',32),(724,'mod/attendance:manualautomark','write',50,'mod_attendance',4),(725,'block/completion_progress:overview','read',80,'block_completion_progress',8),(726,'block/completion_progress:showbar','read',80,'block_completion_progress',0),(727,'block/completion_progress:addinstance','write',80,'block_completion_progress',8),(728,'block/completion_progress:myaddinstance','read',10,'block_completion_progress',8);
/*!40000 ALTER TABLE `mdl_capabilities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_chat`
--

DROP TABLE IF EXISTS `mdl_chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_chat` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `intro` longtext NOT NULL,
  `introformat` smallint(4) NOT NULL DEFAULT 0,
  `keepdays` bigint(11) NOT NULL DEFAULT 0,
  `studentlogs` smallint(4) NOT NULL DEFAULT 0,
  `chattime` bigint(10) NOT NULL DEFAULT 0,
  `schedule` smallint(4) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_chat_cou_ix` (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Each of these is a chat room';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_chat`
--

LOCK TABLES `mdl_chat` WRITE;
/*!40000 ALTER TABLE `mdl_chat` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_chat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_chat_messages`
--

DROP TABLE IF EXISTS `mdl_chat_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_chat_messages` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `chatid` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `groupid` bigint(10) NOT NULL DEFAULT 0,
  `issystem` tinyint(1) NOT NULL DEFAULT 0,
  `message` longtext NOT NULL,
  `timestamp` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_chatmess_use_ix` (`userid`),
  KEY `mdl_chatmess_gro_ix` (`groupid`),
  KEY `mdl_chatmess_timcha_ix` (`timestamp`,`chatid`),
  KEY `mdl_chatmess_cha_ix` (`chatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores all the actual chat messages';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_chat_messages`
--

LOCK TABLES `mdl_chat_messages` WRITE;
/*!40000 ALTER TABLE `mdl_chat_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_chat_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_chat_messages_current`
--

DROP TABLE IF EXISTS `mdl_chat_messages_current`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_chat_messages_current` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `chatid` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `groupid` bigint(10) NOT NULL DEFAULT 0,
  `issystem` tinyint(1) NOT NULL DEFAULT 0,
  `message` longtext NOT NULL,
  `timestamp` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_chatmesscurr_use_ix` (`userid`),
  KEY `mdl_chatmesscurr_gro_ix` (`groupid`),
  KEY `mdl_chatmesscurr_timcha_ix` (`timestamp`,`chatid`),
  KEY `mdl_chatmesscurr_cha_ix` (`chatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores current session';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_chat_messages_current`
--

LOCK TABLES `mdl_chat_messages_current` WRITE;
/*!40000 ALTER TABLE `mdl_chat_messages_current` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_chat_messages_current` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_chat_users`
--

DROP TABLE IF EXISTS `mdl_chat_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_chat_users` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `chatid` bigint(11) NOT NULL DEFAULT 0,
  `userid` bigint(11) NOT NULL DEFAULT 0,
  `groupid` bigint(11) NOT NULL DEFAULT 0,
  `version` varchar(16) NOT NULL DEFAULT '',
  `ip` varchar(45) NOT NULL DEFAULT '',
  `firstping` bigint(10) NOT NULL DEFAULT 0,
  `lastping` bigint(10) NOT NULL DEFAULT 0,
  `lastmessageping` bigint(10) NOT NULL DEFAULT 0,
  `sid` varchar(32) NOT NULL DEFAULT '',
  `course` bigint(10) NOT NULL DEFAULT 0,
  `lang` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `mdl_chatuser_use_ix` (`userid`),
  KEY `mdl_chatuser_las_ix` (`lastping`),
  KEY `mdl_chatuser_gro_ix` (`groupid`),
  KEY `mdl_chatuser_cha_ix` (`chatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Keeps track of which users are in which chat rooms';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_chat_users`
--

LOCK TABLES `mdl_chat_users` WRITE;
/*!40000 ALTER TABLE `mdl_chat_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_chat_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_choice`
--

DROP TABLE IF EXISTS `mdl_choice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_choice` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `intro` longtext NOT NULL,
  `introformat` smallint(4) NOT NULL DEFAULT 0,
  `publish` tinyint(2) NOT NULL DEFAULT 0,
  `showresults` tinyint(2) NOT NULL DEFAULT 0,
  `display` smallint(4) NOT NULL DEFAULT 0,
  `allowupdate` tinyint(2) NOT NULL DEFAULT 0,
  `allowmultiple` tinyint(2) NOT NULL DEFAULT 0,
  `showunanswered` tinyint(2) NOT NULL DEFAULT 0,
  `includeinactive` tinyint(2) NOT NULL DEFAULT 1,
  `limitanswers` tinyint(2) NOT NULL DEFAULT 0,
  `timeopen` bigint(10) NOT NULL DEFAULT 0,
  `timeclose` bigint(10) NOT NULL DEFAULT 0,
  `showpreview` tinyint(2) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `completionsubmit` tinyint(1) NOT NULL DEFAULT 0,
  `showavailable` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_choi_cou_ix` (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Available choices are stored here';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_choice`
--

LOCK TABLES `mdl_choice` WRITE;
/*!40000 ALTER TABLE `mdl_choice` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_choice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_choice_answers`
--

DROP TABLE IF EXISTS `mdl_choice_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_choice_answers` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `choiceid` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `optionid` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_choiansw_use_ix` (`userid`),
  KEY `mdl_choiansw_cho_ix` (`choiceid`),
  KEY `mdl_choiansw_opt_ix` (`optionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='choices performed by users';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_choice_answers`
--

LOCK TABLES `mdl_choice_answers` WRITE;
/*!40000 ALTER TABLE `mdl_choice_answers` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_choice_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_choice_options`
--

DROP TABLE IF EXISTS `mdl_choice_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_choice_options` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `choiceid` bigint(10) NOT NULL DEFAULT 0,
  `text` longtext DEFAULT NULL,
  `maxanswers` bigint(10) DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_choiopti_cho_ix` (`choiceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='available options to choice';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_choice_options`
--

LOCK TABLES `mdl_choice_options` WRITE;
/*!40000 ALTER TABLE `mdl_choice_options` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_choice_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_cohort`
--

DROP TABLE IF EXISTS `mdl_cohort`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_cohort` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `contextid` bigint(10) NOT NULL,
  `name` varchar(254) NOT NULL DEFAULT '',
  `idnumber` varchar(100) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `descriptionformat` tinyint(2) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `component` varchar(100) NOT NULL DEFAULT '',
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `theme` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_coho_con_ix` (`contextid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Each record represents one cohort (aka site-wide group).';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_cohort`
--

LOCK TABLES `mdl_cohort` WRITE;
/*!40000 ALTER TABLE `mdl_cohort` DISABLE KEYS */;
INSERT INTO `mdl_cohort` VALUES (2,1,'6','6','<p dir=\"ltr\" style=\"text-align:left;\">6</p>',1,1,'',1756523535,1756524308,''),(3,1,'6','61','<p dir=\"ltr\" style=\"text-align: left;\">6</p>',1,1,'',1756525343,1756525343,'moove'),(4,208,'7','7','<p dir=\"ltr\" style=\"text-align: left;\">7</p>',1,1,'',1756554376,1756554376,'moove');
/*!40000 ALTER TABLE `mdl_cohort` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_cohort_members`
--

DROP TABLE IF EXISTS `mdl_cohort_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_cohort_members` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `cohortid` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `timeadded` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_cohomemb_cohuse_uix` (`cohortid`,`userid`),
  KEY `mdl_cohomemb_coh_ix` (`cohortid`),
  KEY `mdl_cohomemb_use_ix` (`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Link a user to a cohort.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_cohort_members`
--

LOCK TABLES `mdl_cohort_members` WRITE;
/*!40000 ALTER TABLE `mdl_cohort_members` DISABLE KEYS */;
INSERT INTO `mdl_cohort_members` VALUES (8,2,3,1756523550),(9,2,4,1756523550),(10,2,5,1756523550),(11,2,6,1756523550),(12,2,7,1756523550),(13,2,2,1756523551),(14,2,8,1756523551),(15,3,3,1756525356),(16,3,4,1756525356),(17,3,5,1756525356),(18,3,6,1756525356),(19,3,7,1756525356),(20,3,2,1756525356),(21,3,8,1756525356);
/*!40000 ALTER TABLE `mdl_cohort_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_comments`
--

DROP TABLE IF EXISTS `mdl_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_comments` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `contextid` bigint(10) NOT NULL,
  `component` varchar(255) DEFAULT NULL,
  `commentarea` varchar(255) NOT NULL DEFAULT '',
  `itemid` bigint(10) NOT NULL,
  `content` longtext NOT NULL,
  `format` tinyint(2) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL,
  `timecreated` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_comm_concomite_ix` (`contextid`,`commentarea`,`itemid`),
  KEY `mdl_comm_use_ix` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='moodle comments module';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_comments`
--

LOCK TABLES `mdl_comments` WRITE;
/*!40000 ALTER TABLE `mdl_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency`
--

DROP TABLE IF EXISTS `mdl_competency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `shortname` varchar(100) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `descriptionformat` smallint(4) NOT NULL DEFAULT 0,
  `idnumber` varchar(100) DEFAULT NULL,
  `competencyframeworkid` bigint(10) NOT NULL,
  `parentid` bigint(10) NOT NULL DEFAULT 0,
  `path` varchar(255) NOT NULL DEFAULT '',
  `sortorder` bigint(10) NOT NULL,
  `ruletype` varchar(100) DEFAULT NULL,
  `ruleoutcome` tinyint(2) NOT NULL DEFAULT 0,
  `ruleconfig` longtext DEFAULT NULL,
  `scaleid` bigint(10) DEFAULT NULL,
  `scaleconfiguration` longtext DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `usermodified` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_comp_comidn_uix` (`competencyframeworkid`,`idnumber`),
  KEY `mdl_comp_rul_ix` (`ruleoutcome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='This table contains the master record of each competency in ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency`
--

LOCK TABLES `mdl_competency` WRITE;
/*!40000 ALTER TABLE `mdl_competency` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency_coursecomp`
--

DROP TABLE IF EXISTS `mdl_competency_coursecomp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency_coursecomp` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `courseid` bigint(10) NOT NULL,
  `competencyid` bigint(10) NOT NULL,
  `ruleoutcome` tinyint(2) NOT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `usermodified` bigint(10) NOT NULL,
  `sortorder` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_compcour_coucom_uix` (`courseid`,`competencyid`),
  KEY `mdl_compcour_courul_ix` (`courseid`,`ruleoutcome`),
  KEY `mdl_compcour_cou2_ix` (`courseid`),
  KEY `mdl_compcour_com_ix` (`competencyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Link a competency to a course.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency_coursecomp`
--

LOCK TABLES `mdl_competency_coursecomp` WRITE;
/*!40000 ALTER TABLE `mdl_competency_coursecomp` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency_coursecomp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency_coursecompsetting`
--

DROP TABLE IF EXISTS `mdl_competency_coursecompsetting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency_coursecompsetting` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `courseid` bigint(10) NOT NULL,
  `pushratingstouserplans` tinyint(2) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `usermodified` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_compcour_cou_uix` (`courseid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='This table contains the course specific settings for compete';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency_coursecompsetting`
--

LOCK TABLES `mdl_competency_coursecompsetting` WRITE;
/*!40000 ALTER TABLE `mdl_competency_coursecompsetting` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency_coursecompsetting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency_evidence`
--

DROP TABLE IF EXISTS `mdl_competency_evidence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency_evidence` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `usercompetencyid` bigint(10) NOT NULL,
  `contextid` bigint(10) NOT NULL,
  `action` tinyint(2) NOT NULL,
  `actionuserid` bigint(10) DEFAULT NULL,
  `descidentifier` varchar(255) NOT NULL DEFAULT '',
  `desccomponent` varchar(255) NOT NULL DEFAULT '',
  `desca` longtext DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `grade` bigint(10) DEFAULT NULL,
  `note` longtext DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `usermodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_compevid_use_ix` (`usercompetencyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='The evidence linked to a user competency';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency_evidence`
--

LOCK TABLES `mdl_competency_evidence` WRITE;
/*!40000 ALTER TABLE `mdl_competency_evidence` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency_evidence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency_framework`
--

DROP TABLE IF EXISTS `mdl_competency_framework`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency_framework` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `shortname` varchar(100) DEFAULT NULL,
  `contextid` bigint(10) NOT NULL,
  `idnumber` varchar(100) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `descriptionformat` smallint(4) NOT NULL DEFAULT 0,
  `scaleid` bigint(11) DEFAULT NULL,
  `scaleconfiguration` longtext NOT NULL,
  `visible` tinyint(2) NOT NULL DEFAULT 1,
  `taxonomies` varchar(255) NOT NULL DEFAULT '',
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `usermodified` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_compfram_idn_uix` (`idnumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='List of competency frameworks.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency_framework`
--

LOCK TABLES `mdl_competency_framework` WRITE;
/*!40000 ALTER TABLE `mdl_competency_framework` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency_framework` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency_modulecomp`
--

DROP TABLE IF EXISTS `mdl_competency_modulecomp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency_modulecomp` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `cmid` bigint(10) NOT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `usermodified` bigint(10) NOT NULL,
  `sortorder` bigint(10) NOT NULL,
  `competencyid` bigint(10) NOT NULL,
  `ruleoutcome` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_compmodu_cmicom_uix` (`cmid`,`competencyid`),
  KEY `mdl_compmodu_cmirul_ix` (`cmid`,`ruleoutcome`),
  KEY `mdl_compmodu_cmi_ix` (`cmid`),
  KEY `mdl_compmodu_com_ix` (`competencyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Link a competency to a module.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency_modulecomp`
--

LOCK TABLES `mdl_competency_modulecomp` WRITE;
/*!40000 ALTER TABLE `mdl_competency_modulecomp` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency_modulecomp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency_plan`
--

DROP TABLE IF EXISTS `mdl_competency_plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency_plan` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` longtext DEFAULT NULL,
  `descriptionformat` smallint(4) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL,
  `templateid` bigint(10) DEFAULT NULL,
  `origtemplateid` bigint(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `duedate` bigint(10) DEFAULT 0,
  `reviewerid` bigint(10) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `usermodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_compplan_usesta_ix` (`userid`,`status`),
  KEY `mdl_compplan_tem_ix` (`templateid`),
  KEY `mdl_compplan_stadue_ix` (`status`,`duedate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Learning plans';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency_plan`
--

LOCK TABLES `mdl_competency_plan` WRITE;
/*!40000 ALTER TABLE `mdl_competency_plan` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency_plan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency_plancomp`
--

DROP TABLE IF EXISTS `mdl_competency_plancomp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency_plancomp` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `planid` bigint(10) NOT NULL,
  `competencyid` bigint(10) NOT NULL,
  `sortorder` bigint(10) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) DEFAULT NULL,
  `usermodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_compplan_placom_uix` (`planid`,`competencyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Plan competencies';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency_plancomp`
--

LOCK TABLES `mdl_competency_plancomp` WRITE;
/*!40000 ALTER TABLE `mdl_competency_plancomp` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency_plancomp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency_relatedcomp`
--

DROP TABLE IF EXISTS `mdl_competency_relatedcomp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency_relatedcomp` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `competencyid` bigint(10) NOT NULL,
  `relatedcompetencyid` bigint(10) NOT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) DEFAULT NULL,
  `usermodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Related competencies';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency_relatedcomp`
--

LOCK TABLES `mdl_competency_relatedcomp` WRITE;
/*!40000 ALTER TABLE `mdl_competency_relatedcomp` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency_relatedcomp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency_template`
--

DROP TABLE IF EXISTS `mdl_competency_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency_template` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `shortname` varchar(100) DEFAULT NULL,
  `contextid` bigint(10) NOT NULL,
  `description` longtext DEFAULT NULL,
  `descriptionformat` smallint(4) NOT NULL DEFAULT 0,
  `visible` tinyint(2) NOT NULL DEFAULT 1,
  `duedate` bigint(10) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `usermodified` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Learning plan templates.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency_template`
--

LOCK TABLES `mdl_competency_template` WRITE;
/*!40000 ALTER TABLE `mdl_competency_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency_templatecohort`
--

DROP TABLE IF EXISTS `mdl_competency_templatecohort`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency_templatecohort` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `templateid` bigint(10) NOT NULL,
  `cohortid` bigint(10) NOT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `usermodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_comptemp_temcoh_uix` (`templateid`,`cohortid`),
  KEY `mdl_comptemp_tem2_ix` (`templateid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Default comment for the table, please edit me';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency_templatecohort`
--

LOCK TABLES `mdl_competency_templatecohort` WRITE;
/*!40000 ALTER TABLE `mdl_competency_templatecohort` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency_templatecohort` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency_templatecomp`
--

DROP TABLE IF EXISTS `mdl_competency_templatecomp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency_templatecomp` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `templateid` bigint(10) NOT NULL,
  `competencyid` bigint(10) NOT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `usermodified` bigint(10) NOT NULL,
  `sortorder` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_comptemp_tem_ix` (`templateid`),
  KEY `mdl_comptemp_com_ix` (`competencyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Link a competency to a learning plan template.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency_templatecomp`
--

LOCK TABLES `mdl_competency_templatecomp` WRITE;
/*!40000 ALTER TABLE `mdl_competency_templatecomp` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency_templatecomp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency_usercomp`
--

DROP TABLE IF EXISTS `mdl_competency_usercomp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency_usercomp` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL,
  `competencyid` bigint(10) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT 0,
  `reviewerid` bigint(10) DEFAULT NULL,
  `proficiency` tinyint(2) DEFAULT NULL,
  `grade` bigint(10) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) DEFAULT NULL,
  `usermodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_compuser_usecom_uix` (`userid`,`competencyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='User competencies';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency_usercomp`
--

LOCK TABLES `mdl_competency_usercomp` WRITE;
/*!40000 ALTER TABLE `mdl_competency_usercomp` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency_usercomp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency_usercompcourse`
--

DROP TABLE IF EXISTS `mdl_competency_usercompcourse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency_usercompcourse` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL,
  `courseid` bigint(10) NOT NULL,
  `competencyid` bigint(10) NOT NULL,
  `proficiency` tinyint(2) DEFAULT NULL,
  `grade` bigint(10) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) DEFAULT NULL,
  `usermodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_compuser_usecoucom_uix` (`userid`,`courseid`,`competencyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='User competencies in a course';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency_usercompcourse`
--

LOCK TABLES `mdl_competency_usercompcourse` WRITE;
/*!40000 ALTER TABLE `mdl_competency_usercompcourse` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency_usercompcourse` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency_usercompplan`
--

DROP TABLE IF EXISTS `mdl_competency_usercompplan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency_usercompplan` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL,
  `competencyid` bigint(10) NOT NULL,
  `planid` bigint(10) NOT NULL,
  `proficiency` tinyint(2) DEFAULT NULL,
  `grade` bigint(10) DEFAULT NULL,
  `sortorder` bigint(10) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) DEFAULT NULL,
  `usermodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_compuser_usecompla_uix` (`userid`,`competencyid`,`planid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='User competencies plans';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency_usercompplan`
--

LOCK TABLES `mdl_competency_usercompplan` WRITE;
/*!40000 ALTER TABLE `mdl_competency_usercompplan` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency_usercompplan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency_userevidence`
--

DROP TABLE IF EXISTS `mdl_competency_userevidence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency_userevidence` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` longtext NOT NULL,
  `descriptionformat` tinyint(1) NOT NULL,
  `url` longtext NOT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `usermodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_compuser_use_ix` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='The evidence of prior learning';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency_userevidence`
--

LOCK TABLES `mdl_competency_userevidence` WRITE;
/*!40000 ALTER TABLE `mdl_competency_userevidence` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency_userevidence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency_userevidencecomp`
--

DROP TABLE IF EXISTS `mdl_competency_userevidencecomp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_competency_userevidencecomp` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userevidenceid` bigint(10) NOT NULL,
  `competencyid` bigint(10) NOT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `usermodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_compuser_usecom2_uix` (`userevidenceid`,`competencyid`),
  KEY `mdl_compuser_use2_ix` (`userevidenceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Relationship between user evidence and competencies';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency_userevidencecomp`
--

LOCK TABLES `mdl_competency_userevidencecomp` WRITE;
/*!40000 ALTER TABLE `mdl_competency_userevidencecomp` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_competency_userevidencecomp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_config`
--

DROP TABLE IF EXISTS `mdl_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_config` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_conf_nam_uix` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=581 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Moodle configuration variables';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_config`
--

LOCK TABLES `mdl_config` WRITE;
/*!40000 ALTER TABLE `mdl_config` DISABLE KEYS */;
INSERT INTO `mdl_config` VALUES (2,'rolesactive','1'),(3,'auth','email'),(4,'enrol_plugins_enabled','cohort,manual,guest,self'),(5,'theme','moove'),(6,'filter_multilang_converted','1'),(7,'siteidentifier','7tyMKyE5AwVw6znSUXrQsNJVcbhUSuNalocalhost'),(8,'backup_version','2022041900'),(9,'backup_release','4.0'),(10,'mnet_dispatcher_mode','off'),(11,'sessiontimeout','28800'),(12,'stringfilters',''),(13,'filterall','0'),(14,'texteditors','atto,tinymce,textarea'),(15,'antiviruses',''),(16,'media_plugins_sortorder','videojs,youtube'),(17,'upgrade_extracreditweightsstepignored','1'),(18,'upgrade_calculatedgradeitemsignored','1'),(19,'upgrade_letterboundarycourses','1'),(20,'mnet_localhost_id','1'),(21,'mnet_all_hosts_id','2'),(22,'siteguest','1'),(23,'siteadmins','2'),(24,'themerev','1756559476'),(25,'jsrev','1756559476'),(26,'templaterev','1756559476'),(27,'gdversion','2'),(28,'licenses','unknown,allrightsreserved,public,cc,cc-nd,cc-nc-nd,cc-nc,cc-nc-sa,cc-sa'),(29,'sitedefaultlicense','unknown'),(30,'version','2022041912'),(31,'enableuserfeedback','0'),(32,'userfeedback_nextreminder','1'),(33,'userfeedback_remindafter','90'),(34,'enableoutcomes','0'),(35,'usecomments','1'),(36,'usetags','1'),(37,'enablenotes','1'),(38,'enableportfolios','0'),(39,'enablewebservices','0'),(40,'enablestats','0'),(41,'enablerssfeeds','0'),(42,'enableblogs','1'),(43,'enablecompletion','1'),(44,'completiondefault','1'),(45,'enableavailability','1'),(46,'enableplagiarism','0'),(47,'enablebadges','1'),(48,'enableglobalsearch','0'),(49,'allowstealth','0'),(50,'enableanalytics','1'),(51,'messaging','1'),(52,'enablecustomreports','1'),(53,'allowemojipicker','1'),(54,'userfiltersdefault','realname'),(55,'defaultpreference_maildisplay','2'),(56,'defaultpreference_mailformat','1'),(57,'defaultpreference_maildigest','0'),(58,'defaultpreference_autosubscribe','1'),(59,'defaultpreference_trackforums','0'),(60,'defaultpreference_core_contentbank_visibility','1'),(61,'enroladminnewcourse','1'),(62,'autologinguests','0'),(63,'hiddenuserfields',''),(64,'showuseridentity','email'),(65,'fullnamedisplay','language'),(66,'alternativefullnameformat','language'),(67,'maxusersperpage','100'),(68,'enablegravatar','0'),(69,'gravatardefaulturl','mm'),(70,'agedigitalconsentverification','0'),(71,'agedigitalconsentmap','*, 16\r\nAT, 14\r\nBE, 13\r\nBG, 14\r\nCY, 14\r\nCZ, 15\r\nDK, 13\r\nEE, 13\r\nES, 14\r\nFI, 13\r\nFR, 15\r\nGB, 13\r\nGR, 15\r\nIT, 14\r\nLT, 14\r\nLV, 13\r\nMT, 13\r\nNO, 13\r\nPT, 13\r\nSE, 13\r\nUS, 13'),(72,'sitepolicy',''),(73,'sitepolicyguest',''),(74,'downloadcoursecontentallowed','0'),(75,'maxsizeperdownloadcoursefile','52428800'),(76,'enablecourserequests','1'),(78,'lockrequestcategory','0'),(79,'courserequestnotify',''),(80,'activitychoosertabmode','0'),(81,'activitychooseractivefooter','hidden'),(82,'enableasyncbackup','0'),(83,'grade_profilereport','user'),(84,'grade_aggregationposition','1'),(85,'grade_includescalesinaggregation','1'),(86,'grade_hiddenasdate','0'),(87,'gradepublishing','0'),(88,'grade_export_exportfeedback','0'),(89,'grade_export_displaytype','1'),(90,'grade_export_decimalpoints','2'),(91,'grade_export_userprofilefields','firstname,lastname,idnumber,institution,department,email'),(92,'grade_export_customprofilefields',''),(93,'recovergradesdefault','0'),(94,'gradeexport',''),(95,'unlimitedgrades','0'),(96,'grade_report_showmin','1'),(97,'gradepointmax','100'),(98,'gradepointdefault','100'),(99,'grade_minmaxtouse','1'),(100,'grade_mygrades_report','overview'),(101,'gradereport_mygradeurl',''),(102,'grade_hideforcedsettings','1'),(103,'grade_aggregation','13'),(104,'grade_aggregation_flag','0'),(105,'grade_aggregations_visible','13'),(106,'grade_aggregateonlygraded','1'),(107,'grade_aggregateonlygraded_flag','2'),(108,'grade_aggregateoutcomes','0'),(109,'grade_aggregateoutcomes_flag','2'),(110,'grade_keephigh','0'),(111,'grade_keephigh_flag','3'),(112,'grade_droplow','0'),(113,'grade_droplow_flag','2'),(114,'grade_overridecat','1'),(115,'grade_displaytype','1'),(116,'grade_decimalpoints','2'),(117,'grade_item_advanced','iteminfo,idnumber,gradepass,plusfactor,multfactor,display,decimals,hiddenuntil,locktime'),(118,'grade_report_studentsperpage','100'),(119,'grade_report_showonlyactiveenrol','1'),(120,'grade_report_quickgrading','1'),(121,'grade_report_showquickfeedback','0'),(122,'grade_report_meanselection','1'),(123,'grade_report_enableajax','0'),(124,'grade_report_showcalculations','1'),(125,'grade_report_showeyecons','0'),(126,'grade_report_showaverages','1'),(127,'grade_report_showlocks','0'),(128,'grade_report_showranges','0'),(129,'grade_report_showanalysisicon','1'),(130,'grade_report_showuserimage','1'),(131,'grade_report_showactivityicons','1'),(132,'grade_report_shownumberofgrades','0'),(133,'grade_report_averagesdisplaytype','inherit'),(134,'grade_report_rangesdisplaytype','inherit'),(135,'grade_report_averagesdecimalpoints','inherit'),(136,'grade_report_rangesdecimalpoints','inherit'),(137,'grade_report_historyperpage','50'),(138,'grade_report_overview_showrank','0'),(139,'grade_report_overview_showtotalsifcontainhidden','0'),(140,'grade_report_user_showrank','0'),(141,'grade_report_user_showpercentage','1'),(142,'grade_report_user_showgrade','1'),(143,'grade_report_user_showfeedback','1'),(144,'grade_report_user_showrange','1'),(145,'grade_report_user_showweight','1'),(146,'grade_report_user_showaverage','0'),(147,'grade_report_user_showlettergrade','0'),(148,'grade_report_user_rangedecimals','0'),(149,'grade_report_user_showhiddenitems','1'),(150,'grade_report_user_showtotalsifcontainhidden','0'),(151,'grade_report_user_showcontributiontocoursetotal','1'),(152,'badges_defaultissuername',''),(153,'badges_defaultissuercontact',''),(154,'badges_badgesalt','badges1756251815'),(155,'badges_allowcoursebadges','1'),(156,'badges_allowexternalbackpack','1'),(157,'rememberuserlicensepref','1'),(159,'forcetimezone','99'),(160,'country','0'),(161,'defaultcity',''),(162,'geoip2file','D:\\xampp\\moodledata/geoip/GeoLite2-City.mmdb'),(163,'googlemapkey3',''),(164,'allcountrycodes',''),(165,'autolang','1'),(166,'lang','es_co'),(167,'autolangusercreation','1'),(168,'langmenu','1'),(169,'langlist',''),(170,'langrev','1756559476'),(171,'langcache','1'),(172,'langstringcache','1'),(173,'locale',''),(174,'latinexcelexport','0'),(175,'messagingallusers','0'),(176,'messagingdefaultpressenter','1'),(177,'messagingdeletereadnotificationsdelay','604800'),(178,'messagingdeleteallnotificationsdelay','2620800'),(179,'messagingallowemailoverride','0'),(181,'authloginviaemail','0'),(182,'allowaccountssameemail','0'),(183,'authpreventaccountcreation','0'),(184,'loginpageautofocus','0'),(185,'guestloginbutton','1'),(186,'limitconcurrentlogins','0'),(187,'alternateloginurl',''),(188,'forgottenpasswordurl',''),(189,'auth_instructions',''),(190,'allowemailaddresses',''),(191,'denyemailaddresses',''),(192,'verifychangedemail','1'),(193,'recaptchapublickey',''),(194,'recaptchaprivatekey',''),(195,'searchengine','simpledb'),(196,'searchindexwhendisabled','0'),(197,'searchindextime','600'),(198,'searchallavailablecourses','0'),(199,'searchincludeallcourses','0'),(200,'searchenablecategories','0'),(201,'searchdefaultcategory','core-all'),(202,'searchhideallcategory','0'),(203,'searchmaxtopresults','3'),(204,'searchteacherroles',''),(205,'searchenginequeryonly',''),(206,'searchbannerenable','0'),(207,'searchbanner',''),(208,'filteruploadedfiles','0'),(209,'filtermatchoneperpage','0'),(210,'filtermatchonepertext','0'),(211,'filternavigationwithsystemcontext','0'),(212,'requiremodintro','0'),(213,'portfolio_moderate_filesize_threshold','1048576'),(214,'portfolio_high_filesize_threshold','5242880'),(215,'portfolio_moderate_db_threshold','20'),(216,'portfolio_high_db_threshold','50'),(217,'repositorycacheexpire','120'),(218,'repositorygetfiletimeout','30'),(219,'repositorysyncfiletimeout','1'),(220,'repositorysyncimagetimeout','3'),(221,'repositoryallowexternallinks','1'),(222,'legacyfilesinnewcourses','0'),(223,'legacyfilesaddallowed','1'),(224,'media_default_width','640'),(225,'media_default_height','360'),(226,'allowbeforeblock','0'),(227,'allowedip',''),(228,'blockedip',''),(229,'protectusernames','1'),(230,'forcelogin','0'),(231,'forceloginforprofiles','1'),(232,'forceloginforprofileimage','0'),(233,'opentowebcrawlers','0'),(234,'allowindexing','0'),(235,'maxbytes','0'),(236,'userquota','104857600'),(237,'allowobjectembed','0'),(238,'enabletrusttext','0'),(239,'maxeditingtime','1800'),(240,'extendedusernamechars','0'),(241,'keeptagnamecase','1'),(242,'profilesforenrolledusersonly','1'),(243,'cronclionly','1'),(244,'cronremotepassword',''),(245,'lockoutthreshold','0'),(246,'lockoutwindow','1800'),(247,'lockoutduration','1800'),(248,'passwordpolicy','1'),(249,'minpasswordlength','8'),(250,'minpassworddigits','1'),(251,'minpasswordlower','1'),(252,'minpasswordupper','1'),(253,'minpasswordnonalphanum','1'),(254,'maxconsecutiveidentchars','0'),(255,'passwordpolicycheckonlogin','0'),(256,'passwordreuselimit','0'),(257,'pwresettime','1800'),(258,'passwordchangelogout','0'),(259,'passwordchangetokendeletion','0'),(260,'tokenduration','7257600'),(261,'groupenrolmentkeypolicy','1'),(262,'disableuserimages','0'),(263,'emailchangeconfirmation','1'),(264,'rememberusername','2'),(265,'strictformsrequired','0'),(266,'cookiesecure','1'),(267,'cookiehttponly','0'),(268,'allowframembedding','0'),(269,'curlsecurityblockedhosts','127.0.0.1\r\n192.168.0.0/16\r\n10.0.0.0/8\r\n172.16.0.0/12\r\n0.0.0.0\r\nlocalhost\r\n169.254.169.254\r\n0000::1'),(270,'curlsecurityallowedport','443\r\n80'),(271,'referrerpolicy','default'),(272,'displayloginfailures','0'),(273,'notifyloginfailures',''),(274,'notifyloginthreshold','10'),(275,'themelist',''),(276,'themedesignermode','0'),(277,'allowuserthemes','0'),(278,'allowcoursethemes','0'),(279,'allowcategorythemes','0'),(280,'allowcohortthemes','1'),(281,'allowthemechangeonurl','0'),(282,'allowuserblockhiding','1'),(283,'langmenuinsecurelayout','0'),(284,'logininfoinsecurelayout','0'),(285,'custommenuitems',''),(286,'customusermenuitems','profile,moodle|/user/profile.php\ngrades,grades|/grade/report/mygrades.php\ncalendar,core_calendar|/calendar/view.php?view=month\nprivatefiles,moodle|/user/files.php\nreports,core_reportbuilder|/reportbuilder/index.php'),(287,'enabledevicedetection','1'),(288,'devicedetectregex','[]'),(289,'calendartype','gregorian'),(290,'calendar_adminseesall','0'),(291,'calendar_site_timeformat','0'),(292,'calendar_startwday','1'),(293,'calendar_weekend','65'),(294,'calendar_lookahead','21'),(295,'calendar_maxevents','10'),(296,'enablecalendarexport','1'),(297,'calendar_customexport','1'),(298,'calendar_exportlookahead','365'),(299,'calendar_exportlookback','5'),(300,'calendar_exportsalt','wNxfpNYr7T21T1SzkDAYGGhKP7AnMF9KEJigCAYAhBP3JMDAMHGXdt8Fr61V'),(301,'calendar_showicalsource','1'),(302,'useblogassociations','1'),(303,'bloglevel','4'),(304,'useexternalblogs','1'),(305,'externalblogcrontime','86400'),(306,'maxexternalblogsperuser','1'),(307,'blogusecomments','1'),(308,'blogshowcommentscount','1'),(309,'enabledashboard','1'),(310,'defaulthomepage','3'),(311,'navshowfullcoursenames','0'),(312,'navshowcategories','1'),(313,'navshowmycoursecategories','0'),(314,'navshowallcourses','0'),(315,'navsortmycoursessort','sortorder'),(316,'navsortmycourseshiddenlast','1'),(317,'navcourselimit','10'),(318,'usesitenameforsitepages','0'),(319,'linkadmincategories','1'),(320,'linkcoursesections','1'),(321,'navshowfrontpagemods','1'),(322,'navadduserpostslinks','1'),(323,'formatstringstriptags','1'),(324,'emoticons','[{\"text\":\":-)\",\"imagename\":\"s\\/smiley\",\"imagecomponent\":\"core\",\"altidentifier\":\"smiley\",\"altcomponent\":\"core_pix\"},{\"text\":\":)\",\"imagename\":\"s\\/smiley\",\"imagecomponent\":\"core\",\"altidentifier\":\"smiley\",\"altcomponent\":\"core_pix\"},{\"text\":\":-D\",\"imagename\":\"s\\/biggrin\",\"imagecomponent\":\"core\",\"altidentifier\":\"biggrin\",\"altcomponent\":\"core_pix\"},{\"text\":\";-)\",\"imagename\":\"s\\/wink\",\"imagecomponent\":\"core\",\"altidentifier\":\"wink\",\"altcomponent\":\"core_pix\"},{\"text\":\":-\\/\",\"imagename\":\"s\\/mixed\",\"imagecomponent\":\"core\",\"altidentifier\":\"mixed\",\"altcomponent\":\"core_pix\"},{\"text\":\"V-.\",\"imagename\":\"s\\/thoughtful\",\"imagecomponent\":\"core\",\"altidentifier\":\"thoughtful\",\"altcomponent\":\"core_pix\"},{\"text\":\":-P\",\"imagename\":\"s\\/tongueout\",\"imagecomponent\":\"core\",\"altidentifier\":\"tongueout\",\"altcomponent\":\"core_pix\"},{\"text\":\":-p\",\"imagename\":\"s\\/tongueout\",\"imagecomponent\":\"core\",\"altidentifier\":\"tongueout\",\"altcomponent\":\"core_pix\"},{\"text\":\"B-)\",\"imagename\":\"s\\/cool\",\"imagecomponent\":\"core\",\"altidentifier\":\"cool\",\"altcomponent\":\"core_pix\"},{\"text\":\"^-)\",\"imagename\":\"s\\/approve\",\"imagecomponent\":\"core\",\"altidentifier\":\"approve\",\"altcomponent\":\"core_pix\"},{\"text\":\"8-)\",\"imagename\":\"s\\/wideeyes\",\"imagecomponent\":\"core\",\"altidentifier\":\"wideeyes\",\"altcomponent\":\"core_pix\"},{\"text\":\":o)\",\"imagename\":\"s\\/clown\",\"imagecomponent\":\"core\",\"altidentifier\":\"clown\",\"altcomponent\":\"core_pix\"},{\"text\":\":-(\",\"imagename\":\"s\\/sad\",\"imagecomponent\":\"core\",\"altidentifier\":\"sad\",\"altcomponent\":\"core_pix\"},{\"text\":\":(\",\"imagename\":\"s\\/sad\",\"imagecomponent\":\"core\",\"altidentifier\":\"sad\",\"altcomponent\":\"core_pix\"},{\"text\":\"8-.\",\"imagename\":\"s\\/shy\",\"imagecomponent\":\"core\",\"altidentifier\":\"shy\",\"altcomponent\":\"core_pix\"},{\"text\":\":-I\",\"imagename\":\"s\\/blush\",\"imagecomponent\":\"core\",\"altidentifier\":\"blush\",\"altcomponent\":\"core_pix\"},{\"text\":\":-X\",\"imagename\":\"s\\/kiss\",\"imagecomponent\":\"core\",\"altidentifier\":\"kiss\",\"altcomponent\":\"core_pix\"},{\"text\":\"8-o\",\"imagename\":\"s\\/surprise\",\"imagecomponent\":\"core\",\"altidentifier\":\"surprise\",\"altcomponent\":\"core_pix\"},{\"text\":\"P-|\",\"imagename\":\"s\\/blackeye\",\"imagecomponent\":\"core\",\"altidentifier\":\"blackeye\",\"altcomponent\":\"core_pix\"},{\"text\":\"8-[\",\"imagename\":\"s\\/angry\",\"imagecomponent\":\"core\",\"altidentifier\":\"angry\",\"altcomponent\":\"core_pix\"},{\"text\":\"(grr)\",\"imagename\":\"s\\/angry\",\"imagecomponent\":\"core\",\"altidentifier\":\"angry\",\"altcomponent\":\"core_pix\"},{\"text\":\"xx-P\",\"imagename\":\"s\\/dead\",\"imagecomponent\":\"core\",\"altidentifier\":\"dead\",\"altcomponent\":\"core_pix\"},{\"text\":\"|-.\",\"imagename\":\"s\\/sleepy\",\"imagecomponent\":\"core\",\"altidentifier\":\"sleepy\",\"altcomponent\":\"core_pix\"},{\"text\":\"}-]\",\"imagename\":\"s\\/evil\",\"imagecomponent\":\"core\",\"altidentifier\":\"evil\",\"altcomponent\":\"core_pix\"},{\"text\":\"(h)\",\"imagename\":\"s\\/heart\",\"imagecomponent\":\"core\",\"altidentifier\":\"heart\",\"altcomponent\":\"core_pix\"},{\"text\":\"(heart)\",\"imagename\":\"s\\/heart\",\"imagecomponent\":\"core\",\"altidentifier\":\"heart\",\"altcomponent\":\"core_pix\"},{\"text\":\"(y)\",\"imagename\":\"s\\/yes\",\"imagecomponent\":\"core\",\"altidentifier\":\"yes\",\"altcomponent\":\"core\"},{\"text\":\"(n)\",\"imagename\":\"s\\/no\",\"imagecomponent\":\"core\",\"altidentifier\":\"no\",\"altcomponent\":\"core\"},{\"text\":\"(martin)\",\"imagename\":\"s\\/martin\",\"imagecomponent\":\"core\",\"altidentifier\":\"martin\",\"altcomponent\":\"core_pix\"},{\"text\":\"( )\",\"imagename\":\"s\\/egg\",\"imagecomponent\":\"core\",\"altidentifier\":\"egg\",\"altcomponent\":\"core_pix\"}]'),(325,'docroot','https://docs.moodle.org'),(326,'doclang',''),(327,'doctonewwindow','0'),(328,'coursecontactduplicates','0'),(329,'courselistshortnames','0'),(330,'coursesperpage','20'),(331,'courseswithsummarieslimit','10'),(332,'courseoverviewfileslimit','1'),(333,'courseoverviewfilesext','web_image'),(334,'coursegraceperiodbefore','0'),(335,'coursegraceperiodafter','0'),(336,'useexternalyui','1'),(337,'yuicomboloading','1'),(338,'cachejs','1'),(339,'additionalhtmlhead',''),(340,'additionalhtmltopofbody',''),(341,'additionalhtmlfooter',''),(342,'cachetemplates','1'),(343,'pathtophp',''),(344,'pathtodu',''),(345,'aspellpath',''),(346,'pathtodot',''),(347,'pathtogs','/usr/bin/gs'),(348,'pathtopdftoppm',''),(349,'pathtopython',''),(350,'supportname','Administrador Usuario'),(351,'supportpage',''),(352,'dbsessions','0'),(353,'sessiontimeoutwarning','1200'),(354,'sessioncookie',''),(355,'sessioncookiepath',''),(356,'sessioncookiedomain',''),(357,'statsfirstrun','none'),(358,'statsmaxruntime','0'),(359,'statsruntimedays','31'),(360,'statsuserthreshold','0'),(361,'slasharguments','1'),(362,'getremoteaddrconf','3'),(363,'reverseproxyignore',''),(364,'proxyhost',''),(365,'proxyport','0'),(366,'proxytype','HTTP'),(367,'proxyuser',''),(368,'proxypassword',''),(369,'proxybypass','localhost, 127.0.0.1'),(370,'maintenance_enabled','0'),(371,'maintenance_message',''),(372,'deleteunconfirmed','168'),(373,'deleteincompleteusers','0'),(374,'disablegradehistory','0'),(375,'gradehistorylifetime','0'),(376,'tempdatafoldercleanup','168'),(377,'filescleanupperiod','86400'),(378,'extramemorylimit','512M'),(379,'maxtimelimit','0'),(380,'curlcache','120'),(381,'curltimeoutkbitrate','56'),(382,'cron_enabled','1'),(383,'task_scheduled_concurrency_limit','3'),(384,'task_scheduled_max_runtime','1800'),(385,'task_adhoc_concurrency_limit','3'),(386,'task_adhoc_max_runtime','1800'),(387,'task_logmode','1'),(388,'task_logtostdout','1'),(389,'task_logretention','2419200'),(390,'task_logretainruns','20'),(391,'smtphosts',''),(392,'smtpsecure',''),(393,'smtpauthtype','LOGIN'),(394,'smtpuser',''),(395,'smtppass',''),(396,'smtpmaxbulk','1'),(397,'allowedemaildomains',''),(398,'divertallemailsto',''),(399,'divertallemailsexcept',''),(400,'emaildkimselector',''),(401,'sitemailcharset','0'),(402,'allowusermailcharset','0'),(403,'allowattachments','1'),(404,'mailnewline','LF'),(405,'emailfromvia','1'),(406,'emailsubjectprefix',''),(407,'emailheaders',''),(408,'updateautocheck','1'),(409,'updateminmaturity','200'),(410,'updatenotifybuilds','0'),(411,'enablewsdocumentation','0'),(412,'dndallowtextandlinks','0'),(413,'pathtosassc',''),(414,'contextlocking','0'),(415,'contextlockappliestoadmin','1'),(416,'forceclean','0'),(417,'enablecourserelativedates','0'),(418,'debug','0'),(419,'debugdisplay','0'),(420,'perfdebug','7'),(421,'debugstringids','0'),(422,'debugsqltrace','0'),(423,'debugvalidators','0'),(424,'debugpageinfo','0'),(425,'profilingenabled','0'),(426,'profilingincluded',''),(427,'profilingexcluded',''),(428,'profilingautofrec','0'),(429,'profilingallowme','0'),(430,'profilingallowall','0'),(431,'profilingslow','0'),(432,'profilinglifetime','1440'),(433,'profilingimportprefix','(I)'),(434,'release','4.0.12 (Build: 20231211)'),(435,'branch','400'),(436,'localcachedirpurged','1756559477'),(437,'scheduledtaskreset','1756559477'),(439,'paygw_plugins_sortorder','paypal'),(440,'allversionshash','4eb2bc4bac9e258206b8c96d7f311e0890dfff46'),(442,'registrationpending','0'),(443,'enableaccessibilitytools','1'),(444,'notloggedinroleid','6'),(445,'guestroleid','6'),(446,'defaultuserroleid','7'),(447,'creatornewroleid','3'),(448,'restorernewroleid','3'),(449,'sitepolicyhandler',''),(450,'gradebookroles','5'),(451,'h5plibraryhandler','h5plib_v124'),(452,'airnotifierurl','https://messages.moodle.net'),(453,'airnotifierport','443'),(454,'airnotifiermobileappname','com.moodle.moodlemobile'),(455,'airnotifierappname','commoodlemoodlemobile'),(456,'airnotifieraccesskey',''),(457,'block_rss_client_num_entries','5'),(458,'block_rss_client_timeout','30'),(459,'block_course_list_adminview','all'),(460,'block_course_list_hideallcourseslink','0'),(461,'block_html_allowcssclasses','0'),(462,'block_online_users_timetosee','5'),(463,'block_online_users_onlinestatushiding','1'),(464,'enablemobilewebservice','0'),(465,'timezone','Europe/Berlin'),(466,'registerauth',''),(467,'pathtounoconv','/usr/bin/unoconv'),(468,'filter_multilang_force_old','0'),(469,'logguests','1'),(470,'loglifetime','0'),(471,'data_enablerssfeeds','0'),(472,'bigbluebuttonbn_default_dpa_accepted','0'),(473,'bigbluebuttonbn_server_url','https://test-moodle.blindsidenetworks.com/bigbluebutton/'),(474,'bigbluebuttonbn_shared_secret','0b21fcaf34673a8c3ec8ed877d76ae34'),(475,'bigbluebuttonbn_welcome_default',''),(476,'bigbluebuttonbn_welcome_editable','1'),(477,'bigbluebuttonbn_recording_default','1'),(478,'bigbluebuttonbn_recording_refresh_period','300'),(479,'bigbluebuttonbn_recording_editable','1'),(480,'bigbluebuttonbn_recording_all_from_start_default','0'),(481,'bigbluebuttonbn_recording_all_from_start_editable','0'),(482,'bigbluebuttonbn_recording_hide_button_default','0'),(483,'bigbluebuttonbn_recording_hide_button_editable','0'),(484,'bigbluebuttonbn_importrecordings_enabled','0'),(485,'bigbluebuttonbn_importrecordings_from_deleted_enabled','0'),(486,'bigbluebuttonbn_recordings_deleted_default','1'),(487,'bigbluebuttonbn_recordings_deleted_editable','0'),(488,'bigbluebuttonbn_recordings_imported_default','0'),(489,'bigbluebuttonbn_recordings_imported_editable','1'),(490,'bigbluebuttonbn_recordings_preview_default','1'),(491,'bigbluebuttonbn_recordings_preview_editable','0'),(492,'bigbluebuttonbn_recordings_asc_sort','0'),(493,'bigbluebuttonbn_recording_protect_editable','1'),(494,'bigbluebuttonbn_waitformoderator_default','0'),(495,'bigbluebuttonbn_waitformoderator_editable','1'),(496,'bigbluebuttonbn_waitformoderator_ping_interval','10'),(497,'bigbluebuttonbn_waitformoderator_cache_ttl','60'),(498,'bigbluebuttonbn_voicebridge_editable','0'),(499,'bigbluebuttonbn_preuploadpresentation_editable','0'),(500,'bigbluebuttonbn_userlimit_default','0'),(501,'bigbluebuttonbn_userlimit_editable','0'),(502,'bigbluebuttonbn_participant_moderator_default','0'),(503,'bigbluebuttonbn_muteonstart_default','0'),(504,'bigbluebuttonbn_muteonstart_editable','0'),(505,'bigbluebuttonbn_disablecam_default','0'),(506,'bigbluebuttonbn_disablecam_editable','1'),(507,'bigbluebuttonbn_disablemic_default','0'),(508,'bigbluebuttonbn_disablemic_editable','1'),(509,'bigbluebuttonbn_disableprivatechat_default','0'),(510,'bigbluebuttonbn_disableprivatechat_editable','1'),(511,'bigbluebuttonbn_disablepublicchat_default','0'),(512,'bigbluebuttonbn_disablepublicchat_editable','1'),(513,'bigbluebuttonbn_disablenote_default','0'),(514,'bigbluebuttonbn_disablenote_editable','1'),(515,'bigbluebuttonbn_hideuserlist_default','0'),(516,'bigbluebuttonbn_hideuserlist_editable','1'),(517,'bigbluebuttonbn_lockonjoin_default','1'),(518,'bigbluebuttonbn_lockonjoin_editable','0'),(519,'bigbluebuttonbn_recordingready_enabled','0'),(520,'bigbluebuttonbn_meetingevents_enabled','0'),(521,'chat_method','ajax'),(522,'chat_refresh_userlist','10'),(523,'chat_old_ping','35'),(524,'chat_refresh_room','5'),(525,'chat_normal_updatemode','jsupdate'),(526,'chat_serverhost','localhost'),(527,'chat_serverip','127.0.0.1'),(528,'chat_serverport','9111'),(529,'chat_servermax','100'),(530,'forum_displaymode','3'),(531,'forum_shortpost','300'),(532,'forum_longpost','600'),(533,'forum_manydiscussions','100'),(534,'forum_maxbytes','512000'),(535,'forum_maxattachments','9'),(536,'forum_subscription','0'),(537,'forum_trackingtype','1'),(538,'forum_trackreadposts','1'),(539,'forum_allowforcedreadtracking','0'),(540,'forum_oldpostdays','14'),(541,'forum_usermarksread','0'),(542,'forum_cleanreadtime','2'),(543,'digestmailtime','17'),(544,'forum_enablerssfeeds','0'),(545,'forum_enabletimedposts','1'),(546,'glossary_entbypage','10'),(547,'glossary_dupentries','0'),(548,'glossary_allowcomments','0'),(549,'glossary_linkbydefault','1'),(550,'glossary_defaultapproval','1'),(551,'glossary_enablerssfeeds','0'),(552,'glossary_linkentries','0'),(553,'glossary_casesensitive','0'),(554,'glossary_fullmatch','0'),(555,'feedback_allowfullanonymous','0'),(556,'profileroles','3,4,5'),(557,'allowguestmymoodle','1'),(558,'coursecontact','3'),(559,'frontpage','6'),(560,'frontpageloggedin','7,2,5'),(561,'maxcategorydepth','2'),(562,'frontpagecourselimit','200'),(563,'commentsperpage','15'),(564,'defaultfrontpageroleid','8'),(565,'messageinbound_enabled','0'),(566,'messageinbound_mailbox',''),(567,'messageinbound_domain',''),(568,'messageinbound_host',''),(569,'messageinbound_hostssl','ssl'),(570,'messageinbound_hostuser',''),(571,'messageinbound_hostpass',''),(572,'mobilecssurl',''),(573,'supportemail','andres.paz1991@gmail.com'),(574,'noreplyaddress','andres.paz1991@gmail.com'),(577,'format_plugins_sortorder','topics,singleactivity,social,weeks'),(579,'defaultrequestcategory','17');
/*!40000 ALTER TABLE `mdl_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_config_log`
--

DROP TABLE IF EXISTS `mdl_config_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_config_log` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `plugin` varchar(100) DEFAULT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` longtext DEFAULT NULL,
  `oldvalue` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_conflog_tim_ix` (`timemodified`),
  KEY `mdl_conflog_use_ix` (`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=1836 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Changes done in server configuration through admin UI';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_config_log`
--

LOCK TABLES `mdl_config_log` WRITE;
/*!40000 ALTER TABLE `mdl_config_log` DISABLE KEYS */;
INSERT INTO `mdl_config_log` VALUES (1,0,1756252075,NULL,'enableuserfeedback','0',NULL),(2,0,1756252075,NULL,'userfeedback_nextreminder','1',NULL),(3,0,1756252076,NULL,'userfeedback_remindafter','90',NULL),(4,0,1756252076,NULL,'enableoutcomes','0',NULL),(5,0,1756252076,NULL,'usecomments','1',NULL),(6,0,1756252077,NULL,'usetags','1',NULL),(7,0,1756252077,NULL,'enablenotes','1',NULL),(8,0,1756252077,NULL,'enableportfolios','0',NULL),(9,0,1756252077,NULL,'enablewebservices','0',NULL),(10,0,1756252078,NULL,'enablestats','0',NULL),(11,0,1756252078,NULL,'enablerssfeeds','0',NULL),(12,0,1756252079,NULL,'enableblogs','1',NULL),(13,0,1756252079,NULL,'enablecompletion','1',NULL),(14,0,1756252079,NULL,'completiondefault','1',NULL),(15,0,1756252080,NULL,'enableavailability','1',NULL),(16,0,1756252080,NULL,'enableplagiarism','0',NULL),(17,0,1756252080,NULL,'enablebadges','1',NULL),(18,0,1756252080,NULL,'enableglobalsearch','0',NULL),(19,0,1756252080,NULL,'allowstealth','0',NULL),(20,0,1756252081,NULL,'enableanalytics','1',NULL),(21,0,1756252081,'core_competency','enabled','1',NULL),(22,0,1756252081,NULL,'messaging','1',NULL),(23,0,1756252082,NULL,'enablecustomreports','1',NULL),(24,0,1756252082,NULL,'allowemojipicker','1',NULL),(25,0,1756252082,NULL,'userfiltersdefault','realname',NULL),(26,0,1756252083,NULL,'defaultpreference_maildisplay','2',NULL),(27,0,1756252083,NULL,'defaultpreference_mailformat','1',NULL),(28,0,1756252083,NULL,'defaultpreference_maildigest','0',NULL),(29,0,1756252083,NULL,'defaultpreference_autosubscribe','1',NULL),(30,0,1756252084,NULL,'defaultpreference_trackforums','0',NULL),(31,0,1756252084,NULL,'defaultpreference_core_contentbank_visibility','1',NULL),(32,0,1756252084,NULL,'enroladminnewcourse','1',NULL),(33,0,1756252084,NULL,'autologinguests','0',NULL),(34,0,1756252085,NULL,'hiddenuserfields','',NULL),(35,0,1756252086,NULL,'showuseridentity','email',NULL),(36,0,1756252086,NULL,'fullnamedisplay','language',NULL),(37,0,1756252086,NULL,'alternativefullnameformat','language',NULL),(38,0,1756252087,NULL,'maxusersperpage','100',NULL),(39,0,1756252087,NULL,'enablegravatar','0',NULL),(40,0,1756252087,NULL,'gravatardefaulturl','mm',NULL),(41,0,1756252087,NULL,'agedigitalconsentverification','0',NULL),(42,0,1756252088,NULL,'agedigitalconsentmap','*, 16\r\nAT, 14\r\nBE, 13\r\nBG, 14\r\nCY, 14\r\nCZ, 15\r\nDK, 13\r\nEE, 13\r\nES, 14\r\nFI, 13\r\nFR, 15\r\nGB, 13\r\nGR, 15\r\nIT, 14\r\nLT, 14\r\nLV, 13\r\nMT, 13\r\nNO, 13\r\nPT, 13\r\nSE, 13\r\nUS, 13',NULL),(43,0,1756252088,NULL,'sitepolicy','',NULL),(44,0,1756252088,NULL,'sitepolicyguest','',NULL),(45,0,1756252089,'moodlecourse','visible','1',NULL),(46,0,1756252089,'moodlecourse','downloadcontentsitedefault','0',NULL),(47,0,1756252089,'moodlecourse','participantsperpage','20',NULL),(48,0,1756252090,'moodlecourse','format','topics',NULL),(49,0,1756252090,'moodlecourse','maxsections','52',NULL),(50,0,1756252090,'moodlecourse','numsections','4',NULL),(51,0,1756252090,'moodlecourse','hiddensections','1',NULL),(52,0,1756252091,'moodlecourse','coursedisplay','0',NULL),(53,0,1756252091,'moodlecourse','courseenddateenabled','1',NULL),(54,0,1756252091,'moodlecourse','courseduration','31536000',NULL),(55,0,1756252092,'moodlecourse','lang','',NULL),(56,0,1756252092,'moodlecourse','newsitems','5',NULL),(57,0,1756252092,'moodlecourse','showgrades','1',NULL),(58,0,1756252093,'moodlecourse','showreports','0',NULL),(59,0,1756252093,'moodlecourse','showactivitydates','1',NULL),(60,0,1756252093,'moodlecourse','maxbytes','0',NULL),(61,0,1756252093,'moodlecourse','enablecompletion','1',NULL),(62,0,1756252093,'moodlecourse','showcompletionconditions','1',NULL),(63,0,1756252094,'moodlecourse','groupmode','0',NULL),(64,0,1756252094,'moodlecourse','groupmodeforce','0',NULL),(65,0,1756252094,NULL,'downloadcoursecontentallowed','0',NULL),(66,0,1756252095,NULL,'maxsizeperdownloadcoursefile','52428800',NULL),(67,0,1756252095,NULL,'enablecourserequests','1',NULL),(68,0,1756252096,NULL,'defaultrequestcategory','1',NULL),(69,0,1756252096,NULL,'lockrequestcategory','0',NULL),(70,0,1756252096,NULL,'courserequestnotify','',NULL),(71,0,1756252097,NULL,'activitychoosertabmode','0',NULL),(72,0,1756252097,NULL,'activitychooseractivefooter','hidden',NULL),(73,0,1756252097,'backup','loglifetime','30',NULL),(74,0,1756252098,'backup','backup_general_users','1',NULL),(75,0,1756252098,'backup','backup_general_users_locked','',NULL),(76,0,1756252098,'backup','backup_general_anonymize','0',NULL),(77,0,1756252099,'backup','backup_general_anonymize_locked','',NULL),(78,0,1756252099,'backup','backup_general_role_assignments','1',NULL),(79,0,1756252099,'backup','backup_general_role_assignments_locked','',NULL),(80,0,1756252099,'backup','backup_general_activities','1',NULL),(81,0,1756252100,'backup','backup_general_activities_locked','',NULL),(82,0,1756252100,'backup','backup_general_blocks','1',NULL),(83,0,1756252100,'backup','backup_general_blocks_locked','',NULL),(84,0,1756252100,'backup','backup_general_files','1',NULL),(85,0,1756252101,'backup','backup_general_files_locked','',NULL),(86,0,1756252101,'backup','backup_general_filters','1',NULL),(87,0,1756252102,'backup','backup_general_filters_locked','',NULL),(88,0,1756252102,'backup','backup_general_comments','1',NULL),(89,0,1756252102,'backup','backup_general_comments_locked','',NULL),(90,0,1756252102,'backup','backup_general_badges','1',NULL),(91,0,1756252103,'backup','backup_general_badges_locked','',NULL),(92,0,1756252103,'backup','backup_general_calendarevents','1',NULL),(93,0,1756252103,'backup','backup_general_calendarevents_locked','',NULL),(94,0,1756252103,'backup','backup_general_userscompletion','1',NULL),(95,0,1756252104,'backup','backup_general_userscompletion_locked','',NULL),(96,0,1756252104,'backup','backup_general_logs','0',NULL),(97,0,1756252104,'backup','backup_general_logs_locked','',NULL),(98,0,1756252104,'backup','backup_general_histories','0',NULL),(99,0,1756252105,'backup','backup_general_histories_locked','',NULL),(100,0,1756252105,'backup','backup_general_questionbank','1',NULL),(101,0,1756252105,'backup','backup_general_questionbank_locked','',NULL),(102,0,1756252105,'backup','backup_general_groups','1',NULL),(103,0,1756252105,'backup','backup_general_groups_locked','',NULL),(104,0,1756252106,'backup','backup_general_competencies','1',NULL),(105,0,1756252106,'backup','backup_general_competencies_locked','',NULL),(106,0,1756252106,'backup','backup_general_contentbankcontent','1',NULL),(107,0,1756252107,'backup','backup_general_contentbankcontent_locked','',NULL),(108,0,1756252107,'backup','backup_general_legacyfiles','1',NULL),(109,0,1756252107,'backup','backup_general_legacyfiles_locked','',NULL),(110,0,1756252107,'backup','import_general_maxresults','10',NULL),(111,0,1756252107,'backup','import_general_duplicate_admin_allowed','0',NULL),(112,0,1756252108,'backup','backup_import_permissions','0',NULL),(113,0,1756252108,'backup','backup_import_permissions_locked','',NULL),(114,0,1756252108,'backup','backup_import_activities','1',NULL),(115,0,1756252108,'backup','backup_import_activities_locked','',NULL),(116,0,1756252109,'backup','backup_import_blocks','1',NULL),(117,0,1756252109,'backup','backup_import_blocks_locked','',NULL),(118,0,1756252109,'backup','backup_import_filters','1',NULL),(119,0,1756252109,'backup','backup_import_filters_locked','',NULL),(120,0,1756252109,'backup','backup_import_calendarevents','1',NULL),(121,0,1756252110,'backup','backup_import_calendarevents_locked','',NULL),(122,0,1756252110,'backup','backup_import_questionbank','1',NULL),(123,0,1756252110,'backup','backup_import_questionbank_locked','',NULL),(124,0,1756252110,'backup','backup_import_groups','1',NULL),(125,0,1756252111,'backup','backup_import_groups_locked','',NULL),(126,0,1756252111,'backup','backup_import_competencies','1',NULL),(127,0,1756252111,'backup','backup_import_competencies_locked','',NULL),(128,0,1756252111,'backup','backup_import_contentbankcontent','1',NULL),(129,0,1756252112,'backup','backup_import_contentbankcontent_locked','',NULL),(130,0,1756252112,'backup','backup_import_legacyfiles','1',NULL),(131,0,1756252112,'backup','backup_import_legacyfiles_locked','',NULL),(132,0,1756252113,'backup','backup_auto_active','0',NULL),(133,0,1756252113,'backup','backup_auto_weekdays','0000000',NULL),(134,0,1756252113,'backup','backup_auto_hour','0',NULL),(135,0,1756252115,'backup','backup_auto_minute','0',NULL),(136,0,1756252115,'backup','backup_auto_storage','0',NULL),(137,0,1756252115,'backup','backup_auto_destination','',NULL),(138,0,1756252115,'backup','backup_auto_max_kept','1',NULL),(139,0,1756252115,'backup','backup_auto_delete_days','0',NULL),(140,0,1756252116,'backup','backup_auto_min_kept','0',NULL),(141,0,1756252116,'backup','backup_shortname','0',NULL),(142,0,1756252116,'backup','backup_auto_skip_hidden','1',NULL),(143,0,1756252116,'backup','backup_auto_skip_modif_days','30',NULL),(144,0,1756252117,'backup','backup_auto_skip_modif_prev','0',NULL),(145,0,1756252117,'backup','backup_auto_users','1',NULL),(146,0,1756252117,'backup','backup_auto_role_assignments','1',NULL),(147,0,1756252118,'backup','backup_auto_activities','1',NULL),(148,0,1756252118,'backup','backup_auto_blocks','1',NULL),(149,0,1756252118,'backup','backup_auto_files','1',NULL),(150,0,1756252119,'backup','backup_auto_filters','1',NULL),(151,0,1756252119,'backup','backup_auto_comments','1',NULL),(152,0,1756252119,'backup','backup_auto_badges','1',NULL),(153,0,1756252119,'backup','backup_auto_calendarevents','1',NULL),(154,0,1756252119,'backup','backup_auto_userscompletion','1',NULL),(155,0,1756252120,'backup','backup_auto_logs','0',NULL),(156,0,1756252120,'backup','backup_auto_histories','0',NULL),(157,0,1756252120,'backup','backup_auto_questionbank','1',NULL),(158,0,1756252120,'backup','backup_auto_groups','1',NULL),(159,0,1756252121,'backup','backup_auto_competencies','1',NULL),(160,0,1756252121,'backup','backup_auto_contentbankcontent','1',NULL),(161,0,1756252121,'backup','backup_auto_legacyfiles','1',NULL),(162,0,1756252121,'restore','restore_general_users','1',NULL),(163,0,1756252122,'restore','restore_general_users_locked','',NULL),(164,0,1756252122,'restore','restore_general_enrolments','1',NULL),(165,0,1756252122,'restore','restore_general_enrolments_locked','',NULL),(166,0,1756252122,'restore','restore_general_role_assignments','1',NULL),(167,0,1756252123,'restore','restore_general_role_assignments_locked','',NULL),(168,0,1756252123,'restore','restore_general_permissions','1',NULL),(169,0,1756252123,'restore','restore_general_permissions_locked','',NULL),(170,0,1756252123,'restore','restore_general_activities','1',NULL),(171,0,1756252124,'restore','restore_general_activities_locked','',NULL),(172,0,1756252124,'restore','restore_general_blocks','1',NULL),(173,0,1756252124,'restore','restore_general_blocks_locked','',NULL),(174,0,1756252125,'restore','restore_general_filters','1',NULL),(175,0,1756252125,'restore','restore_general_filters_locked','',NULL),(176,0,1756252125,'restore','restore_general_comments','1',NULL),(177,0,1756252126,'restore','restore_general_comments_locked','',NULL),(178,0,1756252126,'restore','restore_general_badges','1',NULL),(179,0,1756252126,'restore','restore_general_badges_locked','',NULL),(180,0,1756252126,'restore','restore_general_calendarevents','1',NULL),(181,0,1756252127,'restore','restore_general_calendarevents_locked','',NULL),(182,0,1756252127,'restore','restore_general_userscompletion','1',NULL),(183,0,1756252127,'restore','restore_general_userscompletion_locked','',NULL),(184,0,1756252127,'restore','restore_general_logs','1',NULL),(185,0,1756252128,'restore','restore_general_logs_locked','',NULL),(186,0,1756252128,'restore','restore_general_histories','1',NULL),(187,0,1756252128,'restore','restore_general_histories_locked','',NULL),(188,0,1756252128,'restore','restore_general_groups','1',NULL),(189,0,1756252129,'restore','restore_general_groups_locked','',NULL),(190,0,1756252129,'restore','restore_general_competencies','1',NULL),(191,0,1756252129,'restore','restore_general_competencies_locked','',NULL),(192,0,1756252129,'restore','restore_general_contentbankcontent','1',NULL),(193,0,1756252130,'restore','restore_general_contentbankcontent_locked','',NULL),(194,0,1756252130,'restore','restore_general_legacyfiles','1',NULL),(195,0,1756252131,'restore','restore_general_legacyfiles_locked','',NULL),(196,0,1756252131,'restore','restore_merge_overwrite_conf','0',NULL),(197,0,1756252131,'restore','restore_merge_overwrite_conf_locked','',NULL),(198,0,1756252131,'restore','restore_merge_course_fullname','1',NULL),(199,0,1756252132,'restore','restore_merge_course_fullname_locked','',NULL),(200,0,1756252132,'restore','restore_merge_course_shortname','1',NULL),(201,0,1756252132,'restore','restore_merge_course_shortname_locked','',NULL),(202,0,1756252132,'restore','restore_merge_course_startdate','1',NULL),(203,0,1756252133,'restore','restore_merge_course_startdate_locked','',NULL),(204,0,1756252133,'restore','restore_replace_overwrite_conf','0',NULL),(205,0,1756252133,'restore','restore_replace_overwrite_conf_locked','',NULL),(206,0,1756252133,'restore','restore_replace_course_fullname','1',NULL),(207,0,1756252134,'restore','restore_replace_course_fullname_locked','',NULL),(208,0,1756252134,'restore','restore_replace_course_shortname','1',NULL),(209,0,1756252134,'restore','restore_replace_course_shortname_locked','',NULL),(210,0,1756252134,'restore','restore_replace_course_startdate','1',NULL),(211,0,1756252135,'restore','restore_replace_course_startdate_locked','',NULL),(212,0,1756252135,'restore','restore_replace_keep_roles_and_enrolments','0',NULL),(213,0,1756252135,'restore','restore_replace_keep_roles_and_enrolments_locked','',NULL),(214,0,1756252135,'restore','restore_replace_keep_groups_and_groupings','0',NULL),(215,0,1756252136,'restore','restore_replace_keep_groups_and_groupings_locked','',NULL),(216,0,1756252136,NULL,'enableasyncbackup','0',NULL),(217,0,1756252137,'backup','backup_async_message_users','0',NULL),(218,0,1756252137,'backup','backup_async_message_subject','{operation} de Moodle se complet?? exitosamente',NULL),(219,0,1756252137,'backup','backup_async_message','Hola {user_firstname} {user_lastname}, <br/> ??Su {operation} (ID: {backupid}) ha sido completado exitosamente <br/><br/>Puede verla aqu??: <a href=\"{link}\">{link}</a>.',NULL),(220,0,1756252138,NULL,'grade_profilereport','user',NULL),(221,0,1756252138,NULL,'grade_aggregationposition','1',NULL),(222,0,1756252138,NULL,'grade_includescalesinaggregation','1',NULL),(223,0,1756252138,NULL,'grade_hiddenasdate','0',NULL),(224,0,1756252139,NULL,'gradepublishing','0',NULL),(225,0,1756252139,NULL,'grade_export_exportfeedback','0',NULL),(226,0,1756252139,NULL,'grade_export_displaytype','1',NULL),(227,0,1756252140,NULL,'grade_export_decimalpoints','2',NULL),(228,0,1756252140,NULL,'grade_export_userprofilefields','firstname,lastname,idnumber,institution,department,email',NULL),(229,0,1756252140,NULL,'grade_export_customprofilefields','',NULL),(230,0,1756252141,NULL,'recovergradesdefault','0',NULL),(231,0,1756252141,NULL,'gradeexport','',NULL),(232,0,1756252141,NULL,'unlimitedgrades','0',NULL),(233,0,1756252141,NULL,'grade_report_showmin','1',NULL),(234,0,1756252142,NULL,'gradepointmax','100',NULL),(235,0,1756252142,NULL,'gradepointdefault','100',NULL),(236,0,1756252142,NULL,'grade_minmaxtouse','1',NULL),(237,0,1756252143,NULL,'grade_mygrades_report','overview',NULL),(238,0,1756252143,NULL,'gradereport_mygradeurl','',NULL),(239,0,1756252143,NULL,'grade_hideforcedsettings','1',NULL),(240,0,1756252143,NULL,'grade_aggregation','13',NULL),(241,0,1756252144,NULL,'grade_aggregation_flag','0',NULL),(242,0,1756252144,NULL,'grade_aggregations_visible','13',NULL),(243,0,1756252144,NULL,'grade_aggregateonlygraded','1',NULL),(244,0,1756252144,NULL,'grade_aggregateonlygraded_flag','2',NULL),(245,0,1756252145,NULL,'grade_aggregateoutcomes','0',NULL),(246,0,1756252145,NULL,'grade_aggregateoutcomes_flag','2',NULL),(247,0,1756252145,NULL,'grade_keephigh','0',NULL),(248,0,1756252145,NULL,'grade_keephigh_flag','3',NULL),(249,0,1756252146,NULL,'grade_droplow','0',NULL),(250,0,1756252146,NULL,'grade_droplow_flag','2',NULL),(251,0,1756252146,NULL,'grade_overridecat','1',NULL),(252,0,1756252147,NULL,'grade_displaytype','1',NULL),(253,0,1756252147,NULL,'grade_decimalpoints','2',NULL),(254,0,1756252147,NULL,'grade_item_advanced','iteminfo,idnumber,gradepass,plusfactor,multfactor,display,decimals,hiddenuntil,locktime',NULL),(255,0,1756252147,NULL,'grade_report_studentsperpage','100',NULL),(256,0,1756252148,NULL,'grade_report_showonlyactiveenrol','1',NULL),(257,0,1756252148,NULL,'grade_report_quickgrading','1',NULL),(258,0,1756252148,NULL,'grade_report_showquickfeedback','0',NULL),(259,0,1756252149,NULL,'grade_report_meanselection','1',NULL),(260,0,1756252149,NULL,'grade_report_enableajax','0',NULL),(261,0,1756252149,NULL,'grade_report_showcalculations','1',NULL),(262,0,1756252149,NULL,'grade_report_showeyecons','0',NULL),(263,0,1756252150,NULL,'grade_report_showaverages','1',NULL),(264,0,1756252150,NULL,'grade_report_showlocks','0',NULL),(265,0,1756252150,NULL,'grade_report_showranges','0',NULL),(266,0,1756252151,NULL,'grade_report_showanalysisicon','1',NULL),(267,0,1756252151,NULL,'grade_report_showuserimage','1',NULL),(268,0,1756252151,NULL,'grade_report_showactivityicons','1',NULL),(269,0,1756252151,NULL,'grade_report_shownumberofgrades','0',NULL),(270,0,1756252151,NULL,'grade_report_averagesdisplaytype','inherit',NULL),(271,0,1756252152,NULL,'grade_report_rangesdisplaytype','inherit',NULL),(272,0,1756252152,NULL,'grade_report_averagesdecimalpoints','inherit',NULL),(273,0,1756252153,NULL,'grade_report_rangesdecimalpoints','inherit',NULL),(274,0,1756252153,NULL,'grade_report_historyperpage','50',NULL),(275,0,1756252153,NULL,'grade_report_overview_showrank','0',NULL),(276,0,1756252154,NULL,'grade_report_overview_showtotalsifcontainhidden','0',NULL),(277,0,1756252154,NULL,'grade_report_user_showrank','0',NULL),(278,0,1756252154,NULL,'grade_report_user_showpercentage','1',NULL),(279,0,1756252154,NULL,'grade_report_user_showgrade','1',NULL),(280,0,1756252155,NULL,'grade_report_user_showfeedback','1',NULL),(281,0,1756252155,NULL,'grade_report_user_showrange','1',NULL),(282,0,1756252155,NULL,'grade_report_user_showweight','1',NULL),(283,0,1756252156,NULL,'grade_report_user_showaverage','0',NULL),(284,0,1756252156,NULL,'grade_report_user_showlettergrade','0',NULL),(285,0,1756252156,NULL,'grade_report_user_rangedecimals','0',NULL),(286,0,1756252156,NULL,'grade_report_user_showhiddenitems','1',NULL),(287,0,1756252157,NULL,'grade_report_user_showtotalsifcontainhidden','0',NULL),(288,0,1756252157,NULL,'grade_report_user_showcontributiontocoursetotal','1',NULL),(289,0,1756252157,'analytics','modeinstruction','',NULL),(290,0,1756252157,'analytics','percentonline','0',NULL),(291,0,1756252157,'analytics','typeinstitution','',NULL),(292,0,1756252158,'analytics','levelinstitution','',NULL),(293,0,1756252158,'analytics','predictionsprocessor','\\mlbackend_php\\processor',NULL),(294,0,1756252158,'analytics','defaulttimesplittingsevaluation','\\core\\analytics\\time_splitting\\quarters_accum,\\core\\analytics\\time_splitting\\quarters,\\core\\analytics\\time_splitting\\single_range',NULL),(295,0,1756252159,'analytics','modeloutputdir','',NULL),(296,0,1756252159,'analytics','onlycli','1',NULL),(297,0,1756252159,'analytics','modeltimelimit','1200',NULL),(298,0,1756252159,'analytics','calclifetime','35',NULL),(299,0,1756252160,NULL,'badges_defaultissuername','',NULL),(300,0,1756252160,NULL,'badges_defaultissuercontact','',NULL),(301,0,1756252160,NULL,'badges_badgesalt','badges1756251815',NULL),(302,0,1756252161,NULL,'badges_allowcoursebadges','1',NULL),(303,0,1756252161,NULL,'badges_allowexternalbackpack','1',NULL),(304,0,1756252161,NULL,'rememberuserlicensepref','1',NULL),(305,0,1756252166,NULL,'timezone','Europe/Berlin',NULL),(306,0,1756252171,NULL,'forcetimezone','99',NULL),(307,0,1756252171,NULL,'country','0',NULL),(308,0,1756252172,NULL,'defaultcity','',NULL),(309,0,1756252172,NULL,'geoip2file','D:\\xampp\\moodledata/geoip/GeoLite2-City.mmdb',NULL),(310,0,1756252172,NULL,'googlemapkey3','',NULL),(311,0,1756252172,NULL,'allcountrycodes','',NULL),(312,0,1756252173,NULL,'autolang','1',NULL),(313,0,1756252173,NULL,'lang','es_co',NULL),(314,0,1756252173,NULL,'autolangusercreation','1',NULL),(315,0,1756252174,NULL,'langmenu','1',NULL),(316,0,1756252174,NULL,'langlist','',NULL),(317,0,1756252174,NULL,'langcache','1',NULL),(318,0,1756252174,NULL,'langstringcache','1',NULL),(319,0,1756252175,NULL,'locale','',NULL),(320,0,1756252176,NULL,'latinexcelexport','0',NULL),(321,0,1756252176,NULL,'messagingallusers','0',NULL),(322,0,1756252176,NULL,'messagingdefaultpressenter','1',NULL),(323,0,1756252177,NULL,'messagingdeletereadnotificationsdelay','604800',NULL),(324,0,1756252177,NULL,'messagingdeleteallnotificationsdelay','2620800',NULL),(325,0,1756252177,NULL,'messagingallowemailoverride','0',NULL),(326,0,1756252178,NULL,'registerauth','',NULL),(327,0,1756252178,NULL,'authloginviaemail','0',NULL),(328,0,1756252179,NULL,'allowaccountssameemail','0',NULL),(329,0,1756252179,NULL,'authpreventaccountcreation','0',NULL),(330,0,1756252179,NULL,'loginpageautofocus','0',NULL),(331,0,1756252180,NULL,'guestloginbutton','1',NULL),(332,0,1756252180,NULL,'limitconcurrentlogins','0',NULL),(333,0,1756252180,NULL,'alternateloginurl','',NULL),(334,0,1756252180,NULL,'forgottenpasswordurl','',NULL),(335,0,1756252181,NULL,'auth_instructions','',NULL),(336,0,1756252181,NULL,'allowemailaddresses','',NULL),(337,0,1756252181,NULL,'denyemailaddresses','',NULL),(338,0,1756252182,NULL,'verifychangedemail','1',NULL),(339,0,1756252182,NULL,'recaptchapublickey','',NULL),(340,0,1756252182,NULL,'recaptchaprivatekey','',NULL),(341,0,1756252183,NULL,'searchengine','simpledb',NULL),(342,0,1756252183,NULL,'searchindexwhendisabled','0',NULL),(343,0,1756252184,NULL,'searchindextime','600',NULL),(344,0,1756252184,NULL,'searchallavailablecourses','0',NULL),(345,0,1756252184,NULL,'searchincludeallcourses','0',NULL),(346,0,1756252184,NULL,'searchenablecategories','0',NULL),(347,0,1756252185,NULL,'searchdefaultcategory','core-all',NULL),(348,0,1756252185,NULL,'searchhideallcategory','0',NULL),(349,0,1756252185,NULL,'searchmaxtopresults','3',NULL),(350,0,1756252185,NULL,'searchteacherroles','',NULL),(351,0,1756252186,NULL,'searchenginequeryonly','',NULL),(352,0,1756252186,NULL,'searchbannerenable','0',NULL),(353,0,1756252186,NULL,'searchbanner','',NULL),(354,0,1756252187,'cachestore_apcu','testperformance','0',NULL),(355,0,1756252187,'cachestore_memcached','testservers','',NULL),(356,0,1756252187,'cachestore_mongodb','testserver','',NULL),(357,0,1756252188,'cachestore_redis','test_server','',NULL),(358,0,1756252188,'cachestore_redis','test_password','',NULL),(359,0,1756252188,'cachestore_redis','test_ttl','0',NULL),(360,0,1756252188,'antivirus','notifyemail','',NULL),(361,0,1756252189,'antivirus','notifylevel','2',NULL),(362,0,1756252189,'antivirus','threshold','1200',NULL),(363,0,1756252190,'antivirus','enablequarantine','0',NULL),(364,0,1756252190,'antivirus','quarantinetime','2419200',NULL),(365,0,1756252190,NULL,'filteruploadedfiles','0',NULL),(366,0,1756252191,NULL,'filtermatchoneperpage','0',NULL),(367,0,1756252191,NULL,'filtermatchonepertext','0',NULL),(368,0,1756252191,NULL,'filternavigationwithsystemcontext','0',NULL),(369,0,1756252191,NULL,'requiremodintro','0',NULL),(370,0,1756252192,NULL,'portfolio_moderate_filesize_threshold','1048576',NULL),(371,0,1756252192,NULL,'portfolio_high_filesize_threshold','5242880',NULL),(372,0,1756252192,NULL,'portfolio_moderate_db_threshold','20',NULL),(373,0,1756252193,NULL,'portfolio_high_db_threshold','50',NULL),(374,0,1756252193,NULL,'repositorycacheexpire','120',NULL),(375,0,1756252193,NULL,'repositorygetfiletimeout','30',NULL),(376,0,1756252194,NULL,'repositorysyncfiletimeout','1',NULL),(377,0,1756252194,NULL,'repositorysyncimagetimeout','3',NULL),(378,0,1756252194,NULL,'repositoryallowexternallinks','1',NULL),(379,0,1756252195,NULL,'legacyfilesinnewcourses','0',NULL),(380,0,1756252195,NULL,'legacyfilesaddallowed','1',NULL),(381,0,1756252195,NULL,'media_default_width','640',NULL),(382,0,1756252196,NULL,'media_default_height','360',NULL),(383,0,1756252196,'question_preview','behaviour','deferredfeedback',NULL),(384,0,1756252196,'question_preview','correctness','1',NULL),(385,0,1756252196,'question_preview','marks','2',NULL),(386,0,1756252197,'question_preview','markdp','2',NULL),(387,0,1756252197,'question_preview','feedback','1',NULL),(388,0,1756252197,'question_preview','generalfeedback','1',NULL),(389,0,1756252198,'question_preview','rightanswer','1',NULL),(390,0,1756252198,'question_preview','history','0',NULL),(391,0,1756252198,NULL,'allowbeforeblock','0',NULL),(392,0,1756252198,NULL,'allowedip','',NULL),(393,0,1756252199,NULL,'blockedip','',NULL),(394,0,1756252199,NULL,'protectusernames','1',NULL),(395,0,1756252200,NULL,'forcelogin','0',NULL),(396,0,1756252200,NULL,'forceloginforprofiles','1',NULL),(397,0,1756252200,NULL,'forceloginforprofileimage','0',NULL),(398,0,1756252201,NULL,'opentowebcrawlers','0',NULL),(399,0,1756252201,NULL,'allowindexing','0',NULL),(400,0,1756252201,NULL,'maxbytes','0',NULL),(401,0,1756252201,NULL,'userquota','104857600',NULL),(402,0,1756252201,NULL,'allowobjectembed','0',NULL),(403,0,1756252202,NULL,'enabletrusttext','0',NULL),(404,0,1756252202,NULL,'maxeditingtime','1800',NULL),(405,0,1756252202,NULL,'extendedusernamechars','0',NULL),(406,0,1756252203,NULL,'keeptagnamecase','1',NULL),(407,0,1756252203,NULL,'profilesforenrolledusersonly','1',NULL),(408,0,1756252203,NULL,'cronclionly','1',NULL),(409,0,1756252203,NULL,'cronremotepassword','',NULL),(410,0,1756252204,'tool_task','enablerunnow','1',NULL),(411,0,1756252204,NULL,'lockoutthreshold','0',NULL),(412,0,1756252205,NULL,'lockoutwindow','1800',NULL),(413,0,1756252205,NULL,'lockoutduration','1800',NULL),(414,0,1756252205,NULL,'passwordpolicy','1',NULL),(415,0,1756252205,NULL,'minpasswordlength','8',NULL),(416,0,1756252206,NULL,'minpassworddigits','1',NULL),(417,0,1756252206,NULL,'minpasswordlower','1',NULL),(418,0,1756252206,NULL,'minpasswordupper','1',NULL),(419,0,1756252206,NULL,'minpasswordnonalphanum','1',NULL),(420,0,1756252207,NULL,'maxconsecutiveidentchars','0',NULL),(421,0,1756252207,NULL,'passwordpolicycheckonlogin','0',NULL),(422,0,1756252207,NULL,'passwordreuselimit','0',NULL),(423,0,1756252208,NULL,'pwresettime','1800',NULL),(424,0,1756252208,NULL,'passwordchangelogout','0',NULL),(425,0,1756252208,NULL,'passwordchangetokendeletion','0',NULL),(426,0,1756252208,NULL,'tokenduration','7257600',NULL),(427,0,1756252209,NULL,'groupenrolmentkeypolicy','1',NULL),(428,0,1756252209,NULL,'disableuserimages','0',NULL),(429,0,1756252209,NULL,'emailchangeconfirmation','1',NULL),(430,0,1756252209,NULL,'rememberusername','2',NULL),(431,0,1756252210,NULL,'strictformsrequired','0',NULL),(432,0,1756252210,'adminpresets','sensiblesettings','recaptchapublickey@@none, recaptchaprivatekey@@none, googlemapkey3@@none, secretphrase@@url, cronremotepassword@@none, smtpuser@@none, smtppass@none, proxypassword@@none, quizpassword@@quiz, allowedip@@none, blockedip@@none, dbpass@@logstore_database, messageinbound_hostpass@@none, bind_pw@@auth_cas, pass@@auth_db, bind_pw@@auth_ldap, dbpass@@enrol_database, bind_pw@@enrol_ldap, server_password@@search_solr, ssl_keypassword@@search_solr, alternateserver_password@@search_solr, alternatessl_keypassword@@search_solr, test_password@@cachestore_redis, password@@mlbackend_python, badges_badgesalt@@none, calendar_exportsalt@@none',NULL),(433,0,1756252210,NULL,'cookiesecure','1',NULL),(434,0,1756252211,NULL,'cookiehttponly','0',NULL),(435,0,1756252211,NULL,'allowframembedding','0',NULL),(436,0,1756252211,NULL,'curlsecurityblockedhosts','127.0.0.1\r\n192.168.0.0/16\r\n10.0.0.0/8\r\n172.16.0.0/12\r\n0.0.0.0\r\nlocalhost\r\n169.254.169.254\r\n0000::1',NULL),(437,0,1756252212,NULL,'curlsecurityallowedport','443\r\n80',NULL),(438,0,1756252212,NULL,'referrerpolicy','default',NULL),(439,0,1756252213,NULL,'displayloginfailures','0',NULL),(440,0,1756252213,NULL,'notifyloginfailures','',NULL),(441,0,1756252213,NULL,'notifyloginthreshold','10',NULL),(442,0,1756252214,NULL,'themelist','',NULL),(443,0,1756252214,NULL,'themedesignermode','0',NULL),(444,0,1756252214,NULL,'allowuserthemes','0',NULL),(445,0,1756252214,NULL,'allowcoursethemes','0',NULL),(446,0,1756252215,NULL,'allowcategorythemes','0',NULL),(447,0,1756252215,NULL,'allowcohortthemes','0',NULL),(448,0,1756252215,NULL,'allowthemechangeonurl','0',NULL),(449,0,1756252215,NULL,'allowuserblockhiding','1',NULL),(450,0,1756252216,NULL,'langmenuinsecurelayout','0',NULL),(451,0,1756252216,NULL,'logininfoinsecurelayout','0',NULL),(452,0,1756252216,NULL,'custommenuitems','',NULL),(453,0,1756252217,NULL,'customusermenuitems','profile,moodle|/user/profile.php\ngrades,grades|/grade/report/mygrades.php\ncalendar,core_calendar|/calendar/view.php?view=month\nprivatefiles,moodle|/user/files.php\nreports,core_reportbuilder|/reportbuilder/index.php',NULL),(454,0,1756252217,NULL,'enabledevicedetection','1',NULL),(455,0,1756252217,NULL,'devicedetectregex','[]',NULL),(456,0,1756252218,'theme_boost','unaddableblocks','navigation,settings,course_list,section_links',NULL),(457,0,1756252218,'theme_boost','preset','default.scss',NULL),(458,0,1756252218,'theme_boost','presetfiles','',NULL),(459,0,1756252219,'theme_boost','backgroundimage','',NULL),(460,0,1756252219,'theme_boost','loginbackgroundimage','',NULL),(461,0,1756252220,'theme_boost','brandcolor','',NULL),(462,0,1756252220,'theme_boost','scsspre','',NULL),(463,0,1756252220,'theme_boost','scss','',NULL),(464,0,1756252220,'theme_classic','navbardark','0',NULL),(465,0,1756252221,'theme_classic','unaddableblocks','',NULL),(466,0,1756252221,'theme_classic','preset','default.scss',NULL),(467,0,1756252221,'theme_classic','presetfiles','',NULL),(468,0,1756252222,'theme_classic','backgroundimage','',NULL),(469,0,1756252222,'theme_classic','loginbackgroundimage','',NULL),(470,0,1756252222,'theme_classic','brandcolor','',NULL),(471,0,1756252223,'theme_classic','scsspre','',NULL),(472,0,1756252223,'theme_classic','scss','',NULL),(473,0,1756252223,'core_admin','logo','',NULL),(474,0,1756252223,'core_admin','logocompact','',NULL),(475,0,1756252224,'core_admin','coursecolor1','#81ecec',NULL),(476,0,1756252224,'core_admin','coursecolor2','#74b9ff',NULL),(477,0,1756252224,'core_admin','coursecolor3','#a29bfe',NULL),(478,0,1756252225,'core_admin','coursecolor4','#dfe6e9',NULL),(479,0,1756252225,'core_admin','coursecolor5','#00b894',NULL),(480,0,1756252226,'core_admin','coursecolor6','#0984e3',NULL),(481,0,1756252226,'core_admin','coursecolor7','#b2bec3',NULL),(482,0,1756252226,'core_admin','coursecolor8','#fdcb6e',NULL),(483,0,1756252227,'core_admin','coursecolor9','#fd79a8',NULL),(484,0,1756252227,'core_admin','coursecolor10','#6c5ce7',NULL),(485,0,1756252227,NULL,'calendartype','gregorian',NULL),(486,0,1756252228,NULL,'calendar_adminseesall','0',NULL),(487,0,1756252228,NULL,'calendar_site_timeformat','0',NULL),(488,0,1756252228,NULL,'calendar_startwday','1',NULL),(489,0,1756252229,NULL,'calendar_weekend','65',NULL),(490,0,1756252229,NULL,'calendar_lookahead','21',NULL),(491,0,1756252229,NULL,'calendar_maxevents','10',NULL),(492,0,1756252229,NULL,'enablecalendarexport','1',NULL),(493,0,1756252230,NULL,'calendar_customexport','1',NULL),(494,0,1756252230,NULL,'calendar_exportlookahead','365',NULL),(495,0,1756252230,NULL,'calendar_exportlookback','5',NULL),(496,0,1756252231,NULL,'calendar_exportsalt','wNxfpNYr7T21T1SzkDAYGGhKP7AnMF9KEJigCAYAhBP3JMDAMHGXdt8Fr61V',NULL),(497,0,1756252231,NULL,'calendar_showicalsource','1',NULL),(498,0,1756252231,NULL,'useblogassociations','1',NULL),(499,0,1756252232,NULL,'bloglevel','4',NULL),(500,0,1756252232,NULL,'useexternalblogs','1',NULL),(501,0,1756252232,NULL,'externalblogcrontime','86400',NULL),(502,0,1756252232,NULL,'maxexternalblogsperuser','1',NULL),(503,0,1756252232,NULL,'blogusecomments','1',NULL),(504,0,1756252233,NULL,'blogshowcommentscount','1',NULL),(505,0,1756252233,NULL,'enabledashboard','1',NULL),(506,0,1756252233,NULL,'defaulthomepage','3',NULL),(507,0,1756252234,NULL,'navshowfullcoursenames','0',NULL),(508,0,1756252234,NULL,'navshowcategories','1',NULL),(509,0,1756252234,NULL,'navshowmycoursecategories','0',NULL),(510,0,1756252235,NULL,'navshowallcourses','0',NULL),(511,0,1756252235,NULL,'navsortmycoursessort','sortorder',NULL),(512,0,1756252235,NULL,'navsortmycourseshiddenlast','1',NULL),(513,0,1756252236,NULL,'navcourselimit','10',NULL),(514,0,1756252236,NULL,'usesitenameforsitepages','0',NULL),(515,0,1756252236,NULL,'linkadmincategories','1',NULL),(516,0,1756252237,NULL,'linkcoursesections','1',NULL),(517,0,1756252237,NULL,'navshowfrontpagemods','1',NULL),(518,0,1756252237,NULL,'navadduserpostslinks','1',NULL),(519,0,1756252238,NULL,'formatstringstriptags','1',NULL),(520,0,1756252238,NULL,'emoticons','[{\"text\":\":-)\",\"imagename\":\"s\\/smiley\",\"imagecomponent\":\"core\",\"altidentifier\":\"smiley\",\"altcomponent\":\"core_pix\"},{\"text\":\":)\",\"imagename\":\"s\\/smiley\",\"imagecomponent\":\"core\",\"altidentifier\":\"smiley\",\"altcomponent\":\"core_pix\"},{\"text\":\":-D\",\"imagename\":\"s\\/biggrin\",\"imagecomponent\":\"core\",\"altidentifier\":\"biggrin\",\"altcomponent\":\"core_pix\"},{\"text\":\";-)\",\"imagename\":\"s\\/wink\",\"imagecomponent\":\"core\",\"altidentifier\":\"wink\",\"altcomponent\":\"core_pix\"},{\"text\":\":-\\/\",\"imagename\":\"s\\/mixed\",\"imagecomponent\":\"core\",\"altidentifier\":\"mixed\",\"altcomponent\":\"core_pix\"},{\"text\":\"V-.\",\"imagename\":\"s\\/thoughtful\",\"imagecomponent\":\"core\",\"altidentifier\":\"thoughtful\",\"altcomponent\":\"core_pix\"},{\"text\":\":-P\",\"imagename\":\"s\\/tongueout\",\"imagecomponent\":\"core\",\"altidentifier\":\"tongueout\",\"altcomponent\":\"core_pix\"},{\"text\":\":-p\",\"imagename\":\"s\\/tongueout\",\"imagecomponent\":\"core\",\"altidentifier\":\"tongueout\",\"altcomponent\":\"core_pix\"},{\"text\":\"B-)\",\"imagename\":\"s\\/cool\",\"imagecomponent\":\"core\",\"altidentifier\":\"cool\",\"altcomponent\":\"core_pix\"},{\"text\":\"^-)\",\"imagename\":\"s\\/approve\",\"imagecomponent\":\"core\",\"altidentifier\":\"approve\",\"altcomponent\":\"core_pix\"},{\"text\":\"8-)\",\"imagename\":\"s\\/wideeyes\",\"imagecomponent\":\"core\",\"altidentifier\":\"wideeyes\",\"altcomponent\":\"core_pix\"},{\"text\":\":o)\",\"imagename\":\"s\\/clown\",\"imagecomponent\":\"core\",\"altidentifier\":\"clown\",\"altcomponent\":\"core_pix\"},{\"text\":\":-(\",\"imagename\":\"s\\/sad\",\"imagecomponent\":\"core\",\"altidentifier\":\"sad\",\"altcomponent\":\"core_pix\"},{\"text\":\":(\",\"imagename\":\"s\\/sad\",\"imagecomponent\":\"core\",\"altidentifier\":\"sad\",\"altcomponent\":\"core_pix\"},{\"text\":\"8-.\",\"imagename\":\"s\\/shy\",\"imagecomponent\":\"core\",\"altidentifier\":\"shy\",\"altcomponent\":\"core_pix\"},{\"text\":\":-I\",\"imagename\":\"s\\/blush\",\"imagecomponent\":\"core\",\"altidentifier\":\"blush\",\"altcomponent\":\"core_pix\"},{\"text\":\":-X\",\"imagename\":\"s\\/kiss\",\"imagecomponent\":\"core\",\"altidentifier\":\"kiss\",\"altcomponent\":\"core_pix\"},{\"text\":\"8-o\",\"imagename\":\"s\\/surprise\",\"imagecomponent\":\"core\",\"altidentifier\":\"surprise\",\"altcomponent\":\"core_pix\"},{\"text\":\"P-|\",\"imagename\":\"s\\/blackeye\",\"imagecomponent\":\"core\",\"altidentifier\":\"blackeye\",\"altcomponent\":\"core_pix\"},{\"text\":\"8-[\",\"imagename\":\"s\\/angry\",\"imagecomponent\":\"core\",\"altidentifier\":\"angry\",\"altcomponent\":\"core_pix\"},{\"text\":\"(grr)\",\"imagename\":\"s\\/angry\",\"imagecomponent\":\"core\",\"altidentifier\":\"angry\",\"altcomponent\":\"core_pix\"},{\"text\":\"xx-P\",\"imagename\":\"s\\/dead\",\"imagecomponent\":\"core\",\"altidentifier\":\"dead\",\"altcomponent\":\"core_pix\"},{\"text\":\"|-.\",\"imagename\":\"s\\/sleepy\",\"imagecomponent\":\"core\",\"altidentifier\":\"sleepy\",\"altcomponent\":\"core_pix\"},{\"text\":\"}-]\",\"imagename\":\"s\\/evil\",\"imagecomponent\":\"core\",\"altidentifier\":\"evil\",\"altcomponent\":\"core_pix\"},{\"text\":\"(h)\",\"imagename\":\"s\\/heart\",\"imagecomponent\":\"core\",\"altidentifier\":\"heart\",\"altcomponent\":\"core_pix\"},{\"text\":\"(heart)\",\"imagename\":\"s\\/heart\",\"imagecomponent\":\"core\",\"altidentifier\":\"heart\",\"altcomponent\":\"core_pix\"},{\"text\":\"(y)\",\"imagename\":\"s\\/yes\",\"imagecomponent\":\"core\",\"altidentifier\":\"yes\",\"altcomponent\":\"core\"},{\"text\":\"(n)\",\"imagename\":\"s\\/no\",\"imagecomponent\":\"core\",\"altidentifier\":\"no\",\"altcomponent\":\"core\"},{\"text\":\"(martin)\",\"imagename\":\"s\\/martin\",\"imagecomponent\":\"core\",\"altidentifier\":\"martin\",\"altcomponent\":\"core_pix\"},{\"text\":\"( )\",\"imagename\":\"s\\/egg\",\"imagecomponent\":\"core\",\"altidentifier\":\"egg\",\"altcomponent\":\"core_pix\"}]',NULL),(521,0,1756252238,NULL,'docroot','https://docs.moodle.org',NULL),(522,0,1756252239,NULL,'doclang','',NULL),(523,0,1756252239,NULL,'doctonewwindow','0',NULL),(524,0,1756252239,NULL,'coursecontactduplicates','0',NULL),(525,0,1756252240,NULL,'courselistshortnames','0',NULL),(526,0,1756252240,NULL,'coursesperpage','20',NULL),(527,0,1756252240,NULL,'courseswithsummarieslimit','10',NULL),(528,0,1756252241,NULL,'courseoverviewfileslimit','1',NULL),(529,0,1756252241,NULL,'courseoverviewfilesext','web_image',NULL),(530,0,1756252241,NULL,'coursegraceperiodbefore','0',NULL),(531,0,1756252241,NULL,'coursegraceperiodafter','0',NULL),(532,0,1756252242,NULL,'useexternalyui','0',NULL),(533,0,1756252242,NULL,'yuicomboloading','1',NULL),(534,0,1756252242,NULL,'cachejs','1',NULL),(535,0,1756252243,NULL,'additionalhtmlhead','',NULL),(536,0,1756252243,NULL,'additionalhtmltopofbody','',NULL),(537,0,1756252243,NULL,'additionalhtmlfooter','',NULL),(538,0,1756252244,NULL,'cachetemplates','1',NULL),(539,0,1756252244,NULL,'pathtophp','',NULL),(540,0,1756252244,NULL,'pathtodu','',NULL),(541,0,1756252245,NULL,'aspellpath','',NULL),(542,0,1756252245,NULL,'pathtodot','',NULL),(543,0,1756252245,NULL,'pathtogs','/usr/bin/gs',NULL),(544,0,1756252245,NULL,'pathtopdftoppm','',NULL),(545,0,1756252246,NULL,'pathtopython','',NULL),(546,0,1756252246,NULL,'supportname','Administrador Usuario',NULL),(547,0,1756252247,NULL,'supportpage','',NULL),(548,0,1756252247,NULL,'dbsessions','0',NULL),(549,0,1756252248,NULL,'sessiontimeoutwarning','1200',NULL),(550,0,1756252248,NULL,'sessioncookie','',NULL),(551,0,1756252248,NULL,'sessioncookiepath','',NULL),(552,0,1756252248,NULL,'sessioncookiedomain','',NULL),(553,0,1756252249,NULL,'statsfirstrun','none',NULL),(554,0,1756252249,NULL,'statsmaxruntime','0',NULL),(555,0,1756252249,NULL,'statsruntimedays','31',NULL),(556,0,1756252250,NULL,'statsuserthreshold','0',NULL),(557,0,1756252250,NULL,'slasharguments','1',NULL),(558,0,1756252250,NULL,'getremoteaddrconf','3',NULL),(559,0,1756252251,NULL,'reverseproxyignore','',NULL),(560,0,1756252251,NULL,'proxyhost','',NULL),(561,0,1756252251,NULL,'proxyport','0',NULL),(562,0,1756252252,NULL,'proxytype','HTTP',NULL),(563,0,1756252252,NULL,'proxyuser','',NULL),(564,0,1756252252,NULL,'proxypassword','',NULL),(565,0,1756252253,NULL,'proxybypass','localhost, 127.0.0.1',NULL),(566,0,1756252253,NULL,'maintenance_enabled','0',NULL),(567,0,1756252254,NULL,'maintenance_message','',NULL),(568,0,1756252254,NULL,'deleteunconfirmed','168',NULL),(569,0,1756252254,NULL,'deleteincompleteusers','0',NULL),(570,0,1756252254,NULL,'disablegradehistory','0',NULL),(571,0,1756252255,NULL,'gradehistorylifetime','0',NULL),(572,0,1756252255,NULL,'tempdatafoldercleanup','168',NULL),(573,0,1756252255,NULL,'filescleanupperiod','86400',NULL),(574,0,1756252255,NULL,'extramemorylimit','512M',NULL),(575,0,1756252256,NULL,'maxtimelimit','0',NULL),(576,0,1756252256,NULL,'curlcache','120',NULL),(577,0,1756252256,NULL,'curltimeoutkbitrate','56',NULL),(578,0,1756252257,NULL,'cron_enabled','1',NULL),(579,0,1756252257,NULL,'task_scheduled_concurrency_limit','3',NULL),(580,0,1756252257,NULL,'task_scheduled_max_runtime','1800',NULL),(581,0,1756252257,NULL,'task_adhoc_concurrency_limit','3',NULL),(582,0,1756252258,NULL,'task_adhoc_max_runtime','1800',NULL),(583,0,1756252258,NULL,'task_logmode','1',NULL),(584,0,1756252258,NULL,'task_logtostdout','1',NULL),(585,0,1756252259,NULL,'task_logretention','2419200',NULL),(586,0,1756252259,NULL,'task_logretainruns','20',NULL),(587,0,1756252259,NULL,'smtphosts','',NULL),(588,0,1756252260,NULL,'smtpsecure','',NULL),(589,0,1756252261,NULL,'smtpauthtype','LOGIN',NULL),(590,0,1756252261,NULL,'smtpuser','',NULL),(591,0,1756252261,NULL,'smtppass','',NULL),(592,0,1756252261,NULL,'smtpmaxbulk','1',NULL),(593,0,1756252262,NULL,'allowedemaildomains','',NULL),(594,0,1756252262,NULL,'divertallemailsto','',NULL),(595,0,1756252262,NULL,'divertallemailsexcept','',NULL),(596,0,1756252263,NULL,'emaildkimselector','',NULL),(597,0,1756252263,NULL,'sitemailcharset','0',NULL),(598,0,1756252263,NULL,'allowusermailcharset','0',NULL),(599,0,1756252264,NULL,'allowattachments','1',NULL),(600,0,1756252264,NULL,'mailnewline','LF',NULL),(601,0,1756252264,NULL,'emailfromvia','1',NULL),(602,0,1756252264,NULL,'emailsubjectprefix','',NULL),(603,0,1756252265,NULL,'emailheaders','',NULL),(604,0,1756252265,NULL,'updateautocheck','1',NULL),(605,0,1756252265,NULL,'updateminmaturity','200',NULL),(606,0,1756252265,NULL,'updatenotifybuilds','0',NULL),(607,0,1756252266,NULL,'enablewsdocumentation','0',NULL),(608,0,1756252266,NULL,'dndallowtextandlinks','0',NULL),(609,0,1756252266,NULL,'pathtosassc','',NULL),(610,0,1756252267,NULL,'contextlocking','0',NULL),(611,0,1756252267,NULL,'contextlockappliestoadmin','1',NULL),(612,0,1756252267,NULL,'forceclean','0',NULL),(613,0,1756252268,NULL,'enablecourserelativedates','0',NULL),(614,0,1756252268,NULL,'debug','0',NULL),(615,0,1756252268,NULL,'debugdisplay','0',NULL),(616,0,1756252269,NULL,'perfdebug','7',NULL),(617,0,1756252269,NULL,'debugstringids','0',NULL),(618,0,1756252269,NULL,'debugsqltrace','0',NULL),(619,0,1756252269,NULL,'debugvalidators','0',NULL),(620,0,1756252270,NULL,'debugpageinfo','0',NULL),(621,0,1756252270,NULL,'profilingenabled','0',NULL),(622,0,1756252270,NULL,'profilingincluded','',NULL),(623,0,1756252270,NULL,'profilingexcluded','',NULL),(624,0,1756252271,NULL,'profilingautofrec','0',NULL),(625,0,1756252271,NULL,'profilingallowme','0',NULL),(626,0,1756252271,NULL,'profilingallowall','0',NULL),(627,0,1756252271,NULL,'profilingslow','0',NULL),(628,0,1756252272,NULL,'profilinglifetime','1440',NULL),(629,0,1756252272,NULL,'profilingimportprefix','(I)',NULL),(630,0,1756252286,'core_competency','pushcourseratingstouserplans','1',NULL),(631,0,1756253213,'activitynames','filter_active','1',''),(632,0,1756253213,'core_filter','order','activitynames','activitynames'),(633,0,1756253217,'displayh5p','filter_active','1',''),(634,0,1756253217,'core_filter','order','displayh5p, activitynames','activitynames, displayh5p'),(635,0,1756253220,'emoticon','filter_active','1',''),(636,0,1756253223,'mathjaxloader','filter_active','1',''),(637,0,1756253223,'core_filter','order','displayh5p, activitynames, mathjaxloader, emoticon','displayh5p, activitynames, emoticon, mathjaxloader'),(638,0,1756253225,'mediaplugin','filter_active','1',''),(639,0,1756253230,'urltolink','filter_active','1',''),(640,0,1756253230,'core_filter','order','displayh5p, activitynames, mathjaxloader, emoticon, urltolink, mediaplugin','displayh5p, activitynames, mathjaxloader, emoticon, mediaplugin, urltolink'),(641,2,1756260651,NULL,'enableaccessibilitytools','1',NULL),(642,2,1756260652,'tool_moodlenet','enablemoodlenet','1',NULL),(643,2,1756260652,NULL,'notloggedinroleid','6',NULL),(644,2,1756260652,NULL,'guestroleid','6',NULL),(645,2,1756260652,NULL,'defaultuserroleid','7',NULL),(646,2,1756260652,NULL,'creatornewroleid','3',NULL),(647,2,1756260653,NULL,'restorernewroleid','3',NULL),(648,2,1756260653,'tool_log','exportlog','1',NULL),(649,2,1756260653,'tool_dataprivacy','contactdataprotectionofficer','0',NULL),(650,2,1756260653,'tool_dataprivacy','automaticdataexportapproval','0',NULL),(651,2,1756260654,'tool_dataprivacy','automaticdatadeletionapproval','0',NULL),(652,2,1756260654,'tool_dataprivacy','automaticdeletionrequests','1',NULL),(653,2,1756260654,'tool_dataprivacy','privacyrequestexpiry','604800',NULL),(654,2,1756260654,'tool_dataprivacy','requireallenddatesforuserdeletion','1',NULL),(655,2,1756260654,'tool_dataprivacy','showdataretentionsummary','1',NULL),(656,2,1756260655,NULL,'sitepolicyhandler','',NULL),(657,2,1756260655,NULL,'gradebookroles','5',NULL),(658,2,1756260655,'analytics','logstore','logstore_standard',NULL),(659,2,1756260656,NULL,'h5plibraryhandler','h5plib_v124',NULL),(660,2,1756260656,NULL,'airnotifierurl','https://messages.moodle.net',NULL),(661,2,1756260656,NULL,'airnotifierport','443',NULL),(662,2,1756260656,NULL,'airnotifiermobileappname','com.moodle.moodlemobile',NULL),(663,2,1756260656,NULL,'airnotifierappname','commoodlemoodlemobile',NULL),(664,2,1756260657,NULL,'airnotifieraccesskey','',NULL),(665,2,1756260657,'auth_manual','expiration','0',NULL),(666,2,1756260657,'auth_manual','expirationtime','30',NULL),(667,2,1756260657,'auth_manual','expiration_warning','0',NULL),(668,2,1756260657,'auth_manual','field_lock_firstname','unlocked',NULL),(669,2,1756260657,'auth_manual','field_lock_lastname','unlocked',NULL),(670,2,1756260658,'auth_manual','field_lock_email','unlocked',NULL),(671,2,1756260658,'auth_manual','field_lock_city','unlocked',NULL),(672,2,1756260658,'auth_manual','field_lock_country','unlocked',NULL),(673,2,1756260658,'auth_manual','field_lock_lang','unlocked',NULL),(674,2,1756260658,'auth_manual','field_lock_description','unlocked',NULL),(675,2,1756260658,'auth_manual','field_lock_idnumber','unlocked',NULL),(676,2,1756260658,'auth_manual','field_lock_institution','unlocked',NULL),(677,2,1756260659,'auth_manual','field_lock_department','unlocked',NULL),(678,2,1756260659,'auth_manual','field_lock_phone1','unlocked',NULL),(679,2,1756260659,'auth_manual','field_lock_phone2','unlocked',NULL),(680,2,1756260659,'auth_manual','field_lock_address','unlocked',NULL),(681,2,1756260659,'auth_manual','field_lock_firstnamephonetic','unlocked',NULL),(682,2,1756260660,'auth_manual','field_lock_lastnamephonetic','unlocked',NULL),(683,2,1756260660,'auth_manual','field_lock_middlename','unlocked',NULL),(684,2,1756260660,'auth_manual','field_lock_alternatename','unlocked',NULL),(685,2,1756260660,'auth_email','recaptcha','0',NULL),(686,2,1756260661,'auth_email','field_lock_firstname','unlocked',NULL),(687,2,1756260661,'auth_email','field_lock_lastname','unlocked',NULL),(688,2,1756260661,'auth_email','field_lock_email','unlocked',NULL),(689,2,1756260662,'auth_email','field_lock_city','unlocked',NULL),(690,2,1756260663,'auth_email','field_lock_country','unlocked',NULL),(691,2,1756260663,'auth_email','field_lock_lang','unlocked',NULL),(692,2,1756260663,'auth_email','field_lock_description','unlocked',NULL),(693,2,1756260663,'auth_email','field_lock_idnumber','unlocked',NULL),(694,2,1756260663,'auth_email','field_lock_institution','unlocked',NULL),(695,2,1756260663,'auth_email','field_lock_department','unlocked',NULL),(696,2,1756260664,'auth_email','field_lock_phone1','unlocked',NULL),(697,2,1756260664,'auth_email','field_lock_phone2','unlocked',NULL),(698,2,1756260664,'auth_email','field_lock_address','unlocked',NULL),(699,2,1756260664,'auth_email','field_lock_firstnamephonetic','unlocked',NULL),(700,2,1756260664,'auth_email','field_lock_lastnamephonetic','unlocked',NULL),(701,2,1756260664,'auth_email','field_lock_middlename','unlocked',NULL),(702,2,1756260664,'auth_email','field_lock_alternatename','unlocked',NULL),(703,2,1756260664,'auth_mnet','rpc_negotiation_timeout','30',NULL),(704,2,1756260665,'auth_oauth2','field_lock_firstname','unlocked',NULL),(705,2,1756260665,'auth_oauth2','field_lock_lastname','unlocked',NULL),(706,2,1756260665,'auth_oauth2','field_lock_email','unlocked',NULL),(707,2,1756260665,'auth_oauth2','field_lock_city','unlocked',NULL),(708,2,1756260665,'auth_oauth2','field_lock_country','unlocked',NULL),(709,2,1756260665,'auth_oauth2','field_lock_lang','unlocked',NULL),(710,2,1756260666,'auth_oauth2','field_lock_description','unlocked',NULL),(711,2,1756260666,'auth_oauth2','field_lock_idnumber','unlocked',NULL),(712,2,1756260666,'auth_oauth2','field_lock_institution','unlocked',NULL),(713,2,1756260667,'auth_oauth2','field_lock_department','unlocked',NULL),(714,2,1756260667,'auth_oauth2','field_lock_phone1','unlocked',NULL),(715,2,1756260667,'auth_oauth2','field_lock_phone2','unlocked',NULL),(716,2,1756260667,'auth_oauth2','field_lock_address','unlocked',NULL),(717,2,1756260667,'auth_oauth2','field_lock_firstnamephonetic','unlocked',NULL),(718,2,1756260667,'auth_oauth2','field_lock_lastnamephonetic','unlocked',NULL),(719,2,1756260668,'auth_oauth2','field_lock_middlename','unlocked',NULL),(720,2,1756260668,'auth_oauth2','field_lock_alternatename','unlocked',NULL),(721,2,1756260668,'auth_shibboleth','user_attribute','',NULL),(722,2,1756260669,'auth_shibboleth','convert_data','',NULL),(723,2,1756260669,'auth_shibboleth','alt_login','off',NULL),(724,2,1756260669,'auth_shibboleth','organization_selection','urn:mace:organization1:providerID, Example Organization 1\n        https://another.idp-id.com/shibboleth, Other Example Organization, /Shibboleth.sso/DS/SWITCHaai\n        urn:mace:organization2:providerID, Example Organization 2, /Shibboleth.sso/WAYF/SWITCHaai',NULL),(725,2,1756260669,'auth_shibboleth','logout_handler','',NULL),(726,2,1756260669,'auth_shibboleth','logout_return_url','',NULL),(727,2,1756260669,'auth_shibboleth','login_name','Shibboleth Login',NULL),(728,2,1756260669,'auth_shibboleth','auth_logo','',NULL),(729,2,1756260670,'auth_shibboleth','auth_instructions','Utilice el <a href=\"http://localhost/moodle/auth/shibboleth/index.php\">inicio de sesi??n de Shibboleth</a> para obtener acceso a trav??s de Shibboleth, si su instituci??n lo admite. De lo contrario, utilice el formulario de inicio de sesi??n normal que se muestra aqu??.',NULL),(730,2,1756260670,'auth_shibboleth','changepasswordurl','',NULL),(731,2,1756260670,'auth_shibboleth','field_map_firstname','',NULL),(732,2,1756260670,'auth_shibboleth','field_updatelocal_firstname','oncreate',NULL),(733,2,1756260670,'auth_shibboleth','field_lock_firstname','unlocked',NULL),(734,2,1756260670,'auth_shibboleth','field_map_lastname','',NULL),(735,2,1756260671,'auth_shibboleth','field_updatelocal_lastname','oncreate',NULL),(736,2,1756260671,'auth_shibboleth','field_lock_lastname','unlocked',NULL),(737,2,1756260671,'auth_shibboleth','field_map_email','',NULL),(738,2,1756260671,'auth_shibboleth','field_updatelocal_email','oncreate',NULL),(739,2,1756260672,'auth_shibboleth','field_lock_email','unlocked',NULL),(740,2,1756260672,'auth_shibboleth','field_map_city','',NULL),(741,2,1756260673,'auth_shibboleth','field_updatelocal_city','oncreate',NULL),(742,2,1756260673,'auth_shibboleth','field_lock_city','unlocked',NULL),(743,2,1756260673,'auth_shibboleth','field_map_country','',NULL),(744,2,1756260673,'auth_shibboleth','field_updatelocal_country','oncreate',NULL),(745,2,1756260673,'auth_shibboleth','field_lock_country','unlocked',NULL),(746,2,1756260674,'auth_shibboleth','field_map_lang','',NULL),(747,2,1756260674,'auth_shibboleth','field_updatelocal_lang','oncreate',NULL),(748,2,1756260674,'auth_shibboleth','field_lock_lang','unlocked',NULL),(749,2,1756260674,'auth_shibboleth','field_map_description','',NULL),(750,2,1756260674,'auth_shibboleth','field_updatelocal_description','oncreate',NULL),(751,2,1756260675,'auth_shibboleth','field_lock_description','unlocked',NULL),(752,2,1756260675,'auth_shibboleth','field_map_idnumber','',NULL),(753,2,1756260675,'auth_shibboleth','field_updatelocal_idnumber','oncreate',NULL),(754,2,1756260676,'auth_shibboleth','field_lock_idnumber','unlocked',NULL),(755,2,1756260676,'auth_shibboleth','field_map_institution','',NULL),(756,2,1756260676,'auth_shibboleth','field_updatelocal_institution','oncreate',NULL),(757,2,1756260676,'auth_shibboleth','field_lock_institution','unlocked',NULL),(758,2,1756260677,'auth_shibboleth','field_map_department','',NULL),(759,2,1756260677,'auth_shibboleth','field_updatelocal_department','oncreate',NULL),(760,2,1756260677,'auth_shibboleth','field_lock_department','unlocked',NULL),(761,2,1756260677,'auth_shibboleth','field_map_phone1','',NULL),(762,2,1756260677,'auth_shibboleth','field_updatelocal_phone1','oncreate',NULL),(763,2,1756260677,'auth_shibboleth','field_lock_phone1','unlocked',NULL),(764,2,1756260677,'auth_shibboleth','field_map_phone2','',NULL),(765,2,1756260678,'auth_shibboleth','field_updatelocal_phone2','oncreate',NULL),(766,2,1756260678,'auth_shibboleth','field_lock_phone2','unlocked',NULL),(767,2,1756260678,'auth_shibboleth','field_map_address','',NULL),(768,2,1756260678,'auth_shibboleth','field_updatelocal_address','oncreate',NULL),(769,2,1756260679,'auth_shibboleth','field_lock_address','unlocked',NULL),(770,2,1756260679,'auth_shibboleth','field_map_firstnamephonetic','',NULL),(771,2,1756260679,'auth_shibboleth','field_updatelocal_firstnamephonetic','oncreate',NULL),(772,2,1756260679,'auth_shibboleth','field_lock_firstnamephonetic','unlocked',NULL),(773,2,1756260679,'auth_shibboleth','field_map_lastnamephonetic','',NULL),(774,2,1756260679,'auth_shibboleth','field_updatelocal_lastnamephonetic','oncreate',NULL),(775,2,1756260680,'auth_shibboleth','field_lock_lastnamephonetic','unlocked',NULL),(776,2,1756260680,'auth_shibboleth','field_map_middlename','',NULL),(777,2,1756260680,'auth_shibboleth','field_updatelocal_middlename','oncreate',NULL),(778,2,1756260681,'auth_shibboleth','field_lock_middlename','unlocked',NULL),(779,2,1756260681,'auth_shibboleth','field_map_alternatename','',NULL),(780,2,1756260681,'auth_shibboleth','field_updatelocal_alternatename','oncreate',NULL),(781,2,1756260681,'auth_shibboleth','field_lock_alternatename','unlocked',NULL),(782,2,1756260682,'auth_none','field_lock_firstname','unlocked',NULL),(783,2,1756260682,'auth_none','field_lock_lastname','unlocked',NULL),(784,2,1756260682,'auth_none','field_lock_email','unlocked',NULL),(785,2,1756260682,'auth_none','field_lock_city','unlocked',NULL),(786,2,1756260682,'auth_none','field_lock_country','unlocked',NULL),(787,2,1756260683,'auth_none','field_lock_lang','unlocked',NULL),(788,2,1756260683,'auth_none','field_lock_description','unlocked',NULL),(789,2,1756260685,'auth_none','field_lock_idnumber','unlocked',NULL),(790,2,1756260685,'auth_none','field_lock_institution','unlocked',NULL),(791,2,1756260685,'auth_none','field_lock_department','unlocked',NULL),(792,2,1756260686,'auth_none','field_lock_phone1','unlocked',NULL),(793,2,1756260686,'auth_none','field_lock_phone2','unlocked',NULL),(794,2,1756260687,'auth_none','field_lock_address','unlocked',NULL),(795,2,1756260687,'auth_none','field_lock_firstnamephonetic','unlocked',NULL),(796,2,1756260687,'auth_none','field_lock_lastnamephonetic','unlocked',NULL),(797,2,1756260687,'auth_none','field_lock_middlename','unlocked',NULL),(798,2,1756260688,'auth_none','field_lock_alternatename','unlocked',NULL),(799,2,1756260688,'auth_cas','field_map_firstname','',NULL),(800,2,1756260688,'auth_cas','field_updatelocal_firstname','oncreate',NULL),(801,2,1756260688,'auth_cas','field_updateremote_firstname','0',NULL),(802,2,1756260689,'auth_cas','field_lock_firstname','unlocked',NULL),(803,2,1756260689,'auth_cas','field_map_lastname','',NULL),(804,2,1756260689,'auth_cas','field_updatelocal_lastname','oncreate',NULL),(805,2,1756260689,'auth_cas','field_updateremote_lastname','0',NULL),(806,2,1756260690,'auth_cas','field_lock_lastname','unlocked',NULL),(807,2,1756260690,'auth_cas','field_map_email','',NULL),(808,2,1756260690,'auth_cas','field_updatelocal_email','oncreate',NULL),(809,2,1756260690,'auth_cas','field_updateremote_email','0',NULL),(810,2,1756260690,'auth_cas','field_lock_email','unlocked',NULL),(811,2,1756260692,'auth_cas','field_map_city','',NULL),(812,2,1756260692,'auth_cas','field_updatelocal_city','oncreate',NULL),(813,2,1756260693,'auth_cas','field_updateremote_city','0',NULL),(814,2,1756260693,'auth_cas','field_lock_city','unlocked',NULL),(815,2,1756260693,'auth_cas','field_map_country','',NULL),(816,2,1756260694,'auth_cas','field_updatelocal_country','oncreate',NULL),(817,2,1756260694,'auth_cas','field_updateremote_country','0',NULL),(818,2,1756260694,'auth_cas','field_lock_country','unlocked',NULL),(819,2,1756260694,'auth_cas','field_map_lang','',NULL),(820,2,1756260695,'auth_cas','field_updatelocal_lang','oncreate',NULL),(821,2,1756260695,'auth_cas','field_updateremote_lang','0',NULL),(822,2,1756260695,'auth_cas','field_lock_lang','unlocked',NULL),(823,2,1756260696,'auth_cas','field_map_description','',NULL),(824,2,1756260696,'auth_cas','field_updatelocal_description','oncreate',NULL),(825,2,1756260697,'auth_cas','field_updateremote_description','0',NULL),(826,2,1756260697,'auth_cas','field_lock_description','unlocked',NULL),(827,2,1756260697,'auth_cas','field_map_idnumber','',NULL),(828,2,1756260697,'auth_cas','field_updatelocal_idnumber','oncreate',NULL),(829,2,1756260698,'auth_cas','field_updateremote_idnumber','0',NULL),(830,2,1756260698,'auth_cas','field_lock_idnumber','unlocked',NULL),(831,2,1756260698,'auth_cas','field_map_institution','',NULL),(832,2,1756260699,'auth_cas','field_updatelocal_institution','oncreate',NULL),(833,2,1756260699,'auth_cas','field_updateremote_institution','0',NULL),(834,2,1756260699,'auth_cas','field_lock_institution','unlocked',NULL),(835,2,1756260699,'auth_cas','field_map_department','',NULL),(836,2,1756260699,'auth_cas','field_updatelocal_department','oncreate',NULL),(837,2,1756260700,'auth_cas','field_updateremote_department','0',NULL),(838,2,1756260700,'auth_cas','field_lock_department','unlocked',NULL),(839,2,1756260701,'auth_cas','field_map_phone1','',NULL),(840,2,1756260702,'auth_cas','field_updatelocal_phone1','oncreate',NULL),(841,2,1756260702,'auth_cas','field_updateremote_phone1','0',NULL),(842,2,1756260702,'auth_cas','field_lock_phone1','unlocked',NULL),(843,2,1756260702,'auth_cas','field_map_phone2','',NULL),(844,2,1756260702,'auth_cas','field_updatelocal_phone2','oncreate',NULL),(845,2,1756260702,'auth_cas','field_updateremote_phone2','0',NULL),(846,2,1756260703,'auth_cas','field_lock_phone2','unlocked',NULL),(847,2,1756260703,'auth_cas','field_map_address','',NULL),(848,2,1756260703,'auth_cas','field_updatelocal_address','oncreate',NULL),(849,2,1756260703,'auth_cas','field_updateremote_address','0',NULL),(850,2,1756260703,'auth_cas','field_lock_address','unlocked',NULL),(851,2,1756260703,'auth_cas','field_map_firstnamephonetic','',NULL),(852,2,1756260704,'auth_cas','field_updatelocal_firstnamephonetic','oncreate',NULL),(853,2,1756260704,'auth_cas','field_updateremote_firstnamephonetic','0',NULL),(854,2,1756260704,'auth_cas','field_lock_firstnamephonetic','unlocked',NULL),(855,2,1756260704,'auth_cas','field_map_lastnamephonetic','',NULL),(856,2,1756260704,'auth_cas','field_updatelocal_lastnamephonetic','oncreate',NULL),(857,2,1756260704,'auth_cas','field_updateremote_lastnamephonetic','0',NULL),(858,2,1756260704,'auth_cas','field_lock_lastnamephonetic','unlocked',NULL),(859,2,1756260705,'auth_cas','field_map_middlename','',NULL),(860,2,1756260705,'auth_cas','field_updatelocal_middlename','oncreate',NULL),(861,2,1756260705,'auth_cas','field_updateremote_middlename','0',NULL),(862,2,1756260705,'auth_cas','field_lock_middlename','unlocked',NULL),(863,2,1756260705,'auth_cas','field_map_alternatename','',NULL),(864,2,1756260705,'auth_cas','field_updatelocal_alternatename','oncreate',NULL),(865,2,1756260705,'auth_cas','field_updateremote_alternatename','0',NULL),(866,2,1756260706,'auth_cas','field_lock_alternatename','unlocked',NULL),(867,2,1756260706,'auth_ldap','field_map_firstname','',NULL),(868,2,1756260706,'auth_ldap','field_updatelocal_firstname','oncreate',NULL),(869,2,1756260707,'auth_ldap','field_updateremote_firstname','0',NULL),(870,2,1756260707,'auth_ldap','field_lock_firstname','unlocked',NULL),(871,2,1756260707,'auth_ldap','field_map_lastname','',NULL),(872,2,1756260707,'auth_ldap','field_updatelocal_lastname','oncreate',NULL),(873,2,1756260707,'auth_ldap','field_updateremote_lastname','0',NULL),(874,2,1756260708,'auth_ldap','field_lock_lastname','unlocked',NULL),(875,2,1756260708,'auth_ldap','field_map_email','',NULL),(876,2,1756260708,'auth_ldap','field_updatelocal_email','oncreate',NULL),(877,2,1756260708,'auth_ldap','field_updateremote_email','0',NULL),(878,2,1756260708,'auth_ldap','field_lock_email','unlocked',NULL),(879,2,1756260708,'auth_ldap','field_map_city','',NULL),(880,2,1756260709,'auth_ldap','field_updatelocal_city','oncreate',NULL),(881,2,1756260709,'auth_ldap','field_updateremote_city','0',NULL),(882,2,1756260709,'auth_ldap','field_lock_city','unlocked',NULL),(883,2,1756260709,'auth_ldap','field_map_country','',NULL),(884,2,1756260710,'auth_ldap','field_updatelocal_country','oncreate',NULL),(885,2,1756260710,'auth_ldap','field_updateremote_country','0',NULL),(886,2,1756260710,'auth_ldap','field_lock_country','unlocked',NULL),(887,2,1756260710,'auth_ldap','field_map_lang','',NULL),(888,2,1756260711,'auth_ldap','field_updatelocal_lang','oncreate',NULL),(889,2,1756260712,'auth_ldap','field_updateremote_lang','0',NULL),(890,2,1756260713,'auth_ldap','field_lock_lang','unlocked',NULL),(891,2,1756260713,'auth_ldap','field_map_description','',NULL),(892,2,1756260713,'auth_ldap','field_updatelocal_description','oncreate',NULL),(893,2,1756260713,'auth_ldap','field_updateremote_description','0',NULL),(894,2,1756260713,'auth_ldap','field_lock_description','unlocked',NULL),(895,2,1756260713,'auth_ldap','field_map_idnumber','',NULL),(896,2,1756260714,'auth_ldap','field_updatelocal_idnumber','oncreate',NULL),(897,2,1756260714,'auth_ldap','field_updateremote_idnumber','0',NULL),(898,2,1756260714,'auth_ldap','field_lock_idnumber','unlocked',NULL),(899,2,1756260714,'auth_ldap','field_map_institution','',NULL),(900,2,1756260714,'auth_ldap','field_updatelocal_institution','oncreate',NULL),(901,2,1756260714,'auth_ldap','field_updateremote_institution','0',NULL),(902,2,1756260714,'auth_ldap','field_lock_institution','unlocked',NULL),(903,2,1756260714,'auth_ldap','field_map_department','',NULL),(904,2,1756260715,'auth_ldap','field_updatelocal_department','oncreate',NULL),(905,2,1756260715,'auth_ldap','field_updateremote_department','0',NULL),(906,2,1756260715,'auth_ldap','field_lock_department','unlocked',NULL),(907,2,1756260715,'auth_ldap','field_map_phone1','',NULL),(908,2,1756260715,'auth_ldap','field_updatelocal_phone1','oncreate',NULL),(909,2,1756260715,'auth_ldap','field_updateremote_phone1','0',NULL),(910,2,1756260716,'auth_ldap','field_lock_phone1','unlocked',NULL),(911,2,1756260716,'auth_ldap','field_map_phone2','',NULL),(912,2,1756260716,'auth_ldap','field_updatelocal_phone2','oncreate',NULL),(913,2,1756260716,'auth_ldap','field_updateremote_phone2','0',NULL),(914,2,1756260716,'auth_ldap','field_lock_phone2','unlocked',NULL),(915,2,1756260716,'auth_ldap','field_map_address','',NULL),(916,2,1756260716,'auth_ldap','field_updatelocal_address','oncreate',NULL),(917,2,1756260717,'auth_ldap','field_updateremote_address','0',NULL),(918,2,1756260717,'auth_ldap','field_lock_address','unlocked',NULL),(919,2,1756260717,'auth_ldap','field_map_firstnamephonetic','',NULL),(920,2,1756260717,'auth_ldap','field_updatelocal_firstnamephonetic','oncreate',NULL),(921,2,1756260718,'auth_ldap','field_updateremote_firstnamephonetic','0',NULL),(922,2,1756260718,'auth_ldap','field_lock_firstnamephonetic','unlocked',NULL),(923,2,1756260718,'auth_ldap','field_map_lastnamephonetic','',NULL),(924,2,1756260718,'auth_ldap','field_updatelocal_lastnamephonetic','oncreate',NULL),(925,2,1756260718,'auth_ldap','field_updateremote_lastnamephonetic','0',NULL),(926,2,1756260718,'auth_ldap','field_lock_lastnamephonetic','unlocked',NULL),(927,2,1756260719,'auth_ldap','field_map_middlename','',NULL),(928,2,1756260719,'auth_ldap','field_updatelocal_middlename','oncreate',NULL),(929,2,1756260719,'auth_ldap','field_updateremote_middlename','0',NULL),(930,2,1756260719,'auth_ldap','field_lock_middlename','unlocked',NULL),(931,2,1756260719,'auth_ldap','field_map_alternatename','',NULL),(932,2,1756260719,'auth_ldap','field_updatelocal_alternatename','oncreate',NULL),(933,2,1756260719,'auth_ldap','field_updateremote_alternatename','0',NULL),(934,2,1756260720,'auth_ldap','field_lock_alternatename','unlocked',NULL),(935,2,1756260720,'auth_db','host','127.0.0.1',NULL),(936,2,1756260721,'auth_db','type','mysqli',NULL),(937,2,1756260721,'auth_db','sybasequoting','0',NULL),(938,2,1756260721,'auth_db','name','',NULL),(939,2,1756260723,'auth_db','user','',NULL),(940,2,1756260723,'auth_db','pass','',NULL),(941,2,1756260723,'auth_db','table','',NULL),(942,2,1756260723,'auth_db','fielduser','',NULL),(943,2,1756260723,'auth_db','fieldpass','',NULL),(944,2,1756260723,'auth_db','passtype','plaintext',NULL),(945,2,1756260724,'auth_db','extencoding','utf-8',NULL),(946,2,1756260724,'auth_db','setupsql','',NULL),(947,2,1756260724,'auth_db','debugauthdb','0',NULL),(948,2,1756260724,'auth_db','changepasswordurl','',NULL),(949,2,1756260725,'auth_db','removeuser','0',NULL),(950,2,1756260725,'auth_db','updateusers','0',NULL),(951,2,1756260725,'auth_db','field_map_firstname','',NULL),(952,2,1756260725,'auth_db','field_updatelocal_firstname','oncreate',NULL),(953,2,1756260725,'auth_db','field_updateremote_firstname','0',NULL),(954,2,1756260725,'auth_db','field_lock_firstname','unlocked',NULL),(955,2,1756260726,'auth_db','field_map_lastname','',NULL),(956,2,1756260726,'auth_db','field_updatelocal_lastname','oncreate',NULL),(957,2,1756260726,'auth_db','field_updateremote_lastname','0',NULL),(958,2,1756260726,'auth_db','field_lock_lastname','unlocked',NULL),(959,2,1756260726,'auth_db','field_map_email','',NULL),(960,2,1756260727,'auth_db','field_updatelocal_email','oncreate',NULL),(961,2,1756260727,'auth_db','field_updateremote_email','0',NULL),(962,2,1756260727,'auth_db','field_lock_email','unlocked',NULL),(963,2,1756260727,'auth_db','field_map_city','',NULL),(964,2,1756260727,'auth_db','field_updatelocal_city','oncreate',NULL),(965,2,1756260728,'auth_db','field_updateremote_city','0',NULL),(966,2,1756260728,'auth_db','field_lock_city','unlocked',NULL),(967,2,1756260728,'auth_db','field_map_country','',NULL),(968,2,1756260728,'auth_db','field_updatelocal_country','oncreate',NULL),(969,2,1756260728,'auth_db','field_updateremote_country','0',NULL),(970,2,1756260728,'auth_db','field_lock_country','unlocked',NULL),(971,2,1756260729,'auth_db','field_map_lang','',NULL),(972,2,1756260729,'auth_db','field_updatelocal_lang','oncreate',NULL),(973,2,1756260729,'auth_db','field_updateremote_lang','0',NULL),(974,2,1756260729,'auth_db','field_lock_lang','unlocked',NULL),(975,2,1756260729,'auth_db','field_map_description','',NULL),(976,2,1756260729,'auth_db','field_updatelocal_description','oncreate',NULL),(977,2,1756260730,'auth_db','field_updateremote_description','0',NULL),(978,2,1756260730,'auth_db','field_lock_description','unlocked',NULL),(979,2,1756260730,'auth_db','field_map_idnumber','',NULL),(980,2,1756260730,'auth_db','field_updatelocal_idnumber','oncreate',NULL),(981,2,1756260731,'auth_db','field_updateremote_idnumber','0',NULL),(982,2,1756260731,'auth_db','field_lock_idnumber','unlocked',NULL),(983,2,1756260731,'auth_db','field_map_institution','',NULL),(984,2,1756260731,'auth_db','field_updatelocal_institution','oncreate',NULL),(985,2,1756260731,'auth_db','field_updateremote_institution','0',NULL),(986,2,1756260732,'auth_db','field_lock_institution','unlocked',NULL),(987,2,1756260732,'auth_db','field_map_department','',NULL),(988,2,1756260732,'auth_db','field_updatelocal_department','oncreate',NULL),(989,2,1756260733,'auth_db','field_updateremote_department','0',NULL),(990,2,1756260734,'auth_db','field_lock_department','unlocked',NULL),(991,2,1756260734,'auth_db','field_map_phone1','',NULL),(992,2,1756260734,'auth_db','field_updatelocal_phone1','oncreate',NULL),(993,2,1756260734,'auth_db','field_updateremote_phone1','0',NULL),(994,2,1756260734,'auth_db','field_lock_phone1','unlocked',NULL),(995,2,1756260734,'auth_db','field_map_phone2','',NULL),(996,2,1756260735,'auth_db','field_updatelocal_phone2','oncreate',NULL),(997,2,1756260735,'auth_db','field_updateremote_phone2','0',NULL),(998,2,1756260735,'auth_db','field_lock_phone2','unlocked',NULL),(999,2,1756260735,'auth_db','field_map_address','',NULL),(1000,2,1756260735,'auth_db','field_updatelocal_address','oncreate',NULL),(1001,2,1756260735,'auth_db','field_updateremote_address','0',NULL),(1002,2,1756260735,'auth_db','field_lock_address','unlocked',NULL),(1003,2,1756260736,'auth_db','field_map_firstnamephonetic','',NULL),(1004,2,1756260736,'auth_db','field_updatelocal_firstnamephonetic','oncreate',NULL),(1005,2,1756260736,'auth_db','field_updateremote_firstnamephonetic','0',NULL),(1006,2,1756260737,'auth_db','field_lock_firstnamephonetic','unlocked',NULL),(1007,2,1756260737,'auth_db','field_map_lastnamephonetic','',NULL),(1008,2,1756260737,'auth_db','field_updatelocal_lastnamephonetic','oncreate',NULL),(1009,2,1756260737,'auth_db','field_updateremote_lastnamephonetic','0',NULL),(1010,2,1756260737,'auth_db','field_lock_lastnamephonetic','unlocked',NULL),(1011,2,1756260737,'auth_db','field_map_middlename','',NULL),(1012,2,1756260738,'auth_db','field_updatelocal_middlename','oncreate',NULL),(1013,2,1756260738,'auth_db','field_updateremote_middlename','0',NULL),(1014,2,1756260738,'auth_db','field_lock_middlename','unlocked',NULL),(1015,2,1756260738,'auth_db','field_map_alternatename','',NULL),(1016,2,1756260738,'auth_db','field_updatelocal_alternatename','oncreate',NULL),(1017,2,1756260738,'auth_db','field_updateremote_alternatename','0',NULL),(1018,2,1756260739,'auth_db','field_lock_alternatename','unlocked',NULL),(1019,2,1756260739,NULL,'block_rss_client_num_entries','5',NULL),(1020,2,1756260739,NULL,'block_rss_client_timeout','30',NULL),(1021,2,1756260739,NULL,'block_course_list_adminview','all',NULL),(1022,2,1756260739,NULL,'block_course_list_hideallcourseslink','0',NULL),(1023,2,1756260740,'block_recentlyaccessedcourses','displaycategories','1',NULL),(1024,2,1756260740,'block_starredcourses','displaycategories','1',NULL),(1025,2,1756260740,'block_section_links','numsections1','22',NULL),(1026,2,1756260740,'block_section_links','incby1','2',NULL),(1027,2,1756260740,'block_section_links','numsections2','40',NULL),(1028,2,1756260741,'block_section_links','incby2','5',NULL),(1029,2,1756260741,'block_section_links','showsectionname','0',NULL),(1030,2,1756260741,'block_activity_results','config_showbest','3',NULL),(1031,2,1756260741,'block_activity_results','config_showbest_locked','',NULL),(1032,2,1756260741,'block_activity_results','config_showworst','0',NULL),(1033,2,1756260742,'block_activity_results','config_showworst_locked','',NULL),(1034,2,1756260742,'block_activity_results','config_usegroups','0',NULL),(1035,2,1756260742,'block_activity_results','config_usegroups_locked','',NULL),(1036,2,1756260742,'block_activity_results','config_nameformat','1',NULL),(1037,2,1756260742,'block_activity_results','config_nameformat_locked','',NULL),(1038,2,1756260742,'block_activity_results','config_gradeformat','1',NULL),(1039,2,1756260744,'block_activity_results','config_gradeformat_locked','',NULL),(1040,2,1756260744,'block_activity_results','config_decimalpoints','2',NULL),(1041,2,1756260744,'block_activity_results','config_decimalpoints_locked','',NULL),(1042,2,1756260744,'block_accessreview','whattoshow','showboth',NULL),(1043,2,1756260745,'block_accessreview','errordisplay','showint',NULL),(1044,2,1756260745,'block_accessreview','toolpage','errors',NULL),(1045,2,1756260745,NULL,'block_html_allowcssclasses','0',NULL),(1046,2,1756260745,NULL,'block_online_users_timetosee','5',NULL),(1047,2,1756260745,NULL,'block_online_users_onlinestatushiding','1',NULL),(1048,2,1756260746,'block_myoverview','displaycategories','1',NULL),(1049,2,1756260746,'block_myoverview','layouts','card,list,summary',NULL),(1050,2,1756260746,'block_myoverview','displaygroupingallincludinghidden','0',NULL),(1051,2,1756260746,'block_myoverview','displaygroupingall','1',NULL),(1052,2,1756260746,'block_myoverview','displaygroupinginprogress','1',NULL),(1053,2,1756260746,'block_myoverview','displaygroupingpast','1',NULL),(1054,2,1756260747,'block_myoverview','displaygroupingfuture','1',NULL),(1055,2,1756260747,'block_myoverview','displaygroupingcustomfield','0',NULL),(1056,2,1756260747,'block_myoverview','customfiltergrouping','',NULL),(1057,2,1756264273,NULL,'enablemobilewebservice','0',NULL),(1058,2,1756264273,NULL,'timezone','Europe/Berlin',NULL),(1059,2,1756264273,NULL,'registerauth','',NULL),(1060,2,1756264273,'block_myoverview','displaygroupinghidden','1',NULL),(1061,2,1756264274,'block_tag_youtube','apikey','',NULL),(1062,2,1756264274,'mlbackend_python','useserver','0',NULL),(1063,2,1756264274,'mlbackend_python','host','',NULL),(1064,2,1756264274,'mlbackend_python','port','0',NULL),(1065,2,1756264274,'mlbackend_python','secure','0',NULL),(1066,2,1756264274,'mlbackend_python','username','default',NULL),(1067,2,1756264274,'mlbackend_python','password','',NULL),(1068,2,1756264275,'fileconverter_googledrive','issuerid','',NULL),(1069,2,1756264275,NULL,'pathtounoconv','/usr/bin/unoconv',NULL),(1070,2,1756264275,'editor_atto','toolbar','collapse = collapse\r\nstyle1 = title, bold, italic\r\nlist = unorderedlist, orderedlist, indent\r\nlinks = link\r\nfiles = emojipicker, image, media, recordrtc, managefiles, h5p\r\naccessibility = accessibilitychecker, accessibilityhelper\r\nstyle2 = underline, strike, subscript, superscript\r\nalign = align\r\ninsert = equation, charmap, table, clear\r\nundo = undo\r\nother = html',NULL),(1071,2,1756264276,'editor_atto','autosavefrequency','60',NULL),(1072,2,1756264276,'atto_collapse','showgroups','6',NULL),(1073,2,1756264276,'atto_equation','librarygroup1','\\cdot\r\n\\times\r\n\\ast\r\n\\div\r\n\\diamond\r\n\\pm\r\n\\mp\r\n\\oplus\r\n\\ominus\r\n\\otimes\r\n\\oslash\r\n\\odot\r\n\\circ\r\n\\bullet\r\n\\asymp\r\n\\equiv\r\n\\subseteq\r\n\\supseteq\r\n\\leq\r\n\\geq\r\n\\preceq\r\n\\succeq\r\n\\sim\r\n\\simeq\r\n\\approx\r\n\\subset\r\n\\supset\r\n\\ll\r\n\\gg\r\n\\prec\r\n\\succ\r\n\\infty\r\n\\in\r\n\\ni\r\n\\forall\r\n\\exists\r\n\\neq\r\n',NULL),(1074,2,1756264276,'atto_equation','librarygroup2','\\leftarrow\r\n\\rightarrow\r\n\\uparrow\r\n\\downarrow\r\n\\leftrightarrow\r\n\\nearrow\r\n\\searrow\r\n\\swarrow\r\n\\nwarrow\r\n\\Leftarrow\r\n\\Rightarrow\r\n\\Uparrow\r\n\\Downarrow\r\n\\Leftrightarrow\r\n',NULL),(1075,2,1756264276,'atto_equation','librarygroup3','\\alpha\r\n\\beta\r\n\\gamma\r\n\\delta\r\n\\epsilon\r\n\\zeta\r\n\\eta\r\n\\theta\r\n\\iota\r\n\\kappa\r\n\\lambda\r\n\\mu\r\n\\nu\r\n\\xi\r\n\\pi\r\n\\rho\r\n\\sigma\r\n\\tau\r\n\\upsilon\r\n\\phi\r\n\\chi\r\n\\psi\r\n\\omega\r\n\\Gamma\r\n\\Delta\r\n\\Theta\r\n\\Lambda\r\n\\Xi\r\n\\Pi\r\n\\Sigma\r\n\\Upsilon\r\n\\Phi\r\n\\Psi\r\n\\Omega\r\n',NULL),(1076,2,1756264276,'atto_equation','librarygroup4','\\sum{a,b}\r\n\\sqrt[a]{b+c}\r\n\\int_{a}^{b}{c}\r\n\\iint_{a}^{b}{c}\r\n\\iiint_{a}^{b}{c}\r\n\\oint{a}\r\n(a)\r\n[a]\r\n\\lbrace{a}\\rbrace\r\n\\left| \\begin{matrix} a_1 & a_2 \\\\ a_3 & a_4 \\end{matrix} \\right|\r\n\\frac{a}{b+c}\r\n\\vec{a}\r\n\\binom {a} {b}\r\n{a \\brack b}\r\n{a \\brace b}\r\n',NULL),(1077,2,1756264277,'atto_recordrtc','allowedtypes','both',NULL),(1078,2,1756264277,'atto_recordrtc','audiobitrate','128000',NULL),(1079,2,1756264277,'atto_recordrtc','videobitrate','2500000',NULL),(1080,2,1756264277,'atto_recordrtc','audiotimelimit','120',NULL),(1081,2,1756264277,'atto_recordrtc','videotimelimit','120',NULL),(1082,2,1756264277,'atto_table','allowborders','0',NULL),(1083,2,1756264278,'atto_table','allowbackgroundcolour','0',NULL),(1084,2,1756264278,'atto_table','allowwidth','0',NULL),(1085,2,1756264278,'editor_tinymce','customtoolbar','wrap,formatselect,wrap,bold,italic,wrap,bullist,numlist,wrap,link,unlink,wrap,image\r\n\r\nundo,redo,wrap,underline,strikethrough,sub,sup,wrap,justifyleft,justifycenter,justifyright,wrap,outdent,indent,wrap,forecolor,backcolor,wrap,ltr,rtl\r\n\r\nfontselect,fontsizeselect,wrap,code,search,replace,wrap,nonbreaking,charmap,table,wrap,cleanup,removeformat,pastetext,pasteword,wrap,fullscreen',NULL),(1086,2,1756264278,'editor_tinymce','fontselectlist','Trebuchet=Trebuchet MS,Verdana,Arial,Helvetica,sans-serif;Arial=arial,helvetica,sans-serif;Courier New=courier new,courier,monospace;Georgia=georgia,times new roman,times,serif;Tahoma=tahoma,arial,helvetica,sans-serif;Times New Roman=times new roman,times,serif;Verdana=verdana,arial,helvetica,sans-serif;Impact=impact;Wingdings=wingdings',NULL),(1087,2,1756264278,'editor_tinymce','customconfig','',NULL),(1088,2,1756264278,'tinymce_moodleemoticon','requireemoticon','1',NULL),(1089,2,1756264278,'tinymce_spellchecker','spellengine','',NULL),(1090,2,1756264278,'tinymce_spellchecker','spelllanguagelist','+English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr,German=de,Italian=it,Polish=pl,Portuguese=pt,Spanish=es,Swedish=sv',NULL),(1091,2,1756264279,'antivirus_clamav','runningmethod','commandline',NULL),(1092,2,1756264279,'antivirus_clamav','pathtoclam','',NULL),(1093,2,1756264279,'antivirus_clamav','pathtounixsocket','',NULL),(1094,2,1756264279,'antivirus_clamav','tcpsockethost','',NULL),(1095,2,1756264279,'antivirus_clamav','tcpsocketport','3310',NULL),(1096,2,1756264279,'antivirus_clamav','clamfailureonupload','tryagain',NULL),(1097,2,1756264279,'antivirus_clamav','tries','1',NULL),(1098,2,1756264280,NULL,'filter_multilang_force_old','0',NULL),(1099,2,1756264280,'filter_urltolink','formats','0,1,4',NULL),(1100,2,1756264280,'filter_urltolink','embedimages','1',NULL),(1101,2,1756264280,'filter_mathjaxloader','httpsurl','https://cdn.jsdelivr.net/npm/mathjax@2.7.9/MathJax.js',NULL),(1102,2,1756264280,'filter_mathjaxloader','texfiltercompatibility','0',NULL),(1103,2,1756264281,'filter_mathjaxloader','mathjaxconfig','MathJax.Hub.Config({\r\n    config: [\"Accessible.js\", \"Safe.js\"],\r\n    errorSettings: { message: [\"!\"] },\r\n    skipStartupTypeset: true,\r\n    messageStyle: \"none\"\r\n});\r\n',NULL),(1104,2,1756264281,'filter_mathjaxloader','additionaldelimiters','',NULL),(1105,2,1756264281,'filter_emoticon','formats','0,1,4',NULL),(1106,2,1756264281,'filter_displayh5p','allowedsources','',NULL),(1107,2,1756264282,'filter_tex','latexpreamble','\\usepackage[latin1]{inputenc}\r\n\\usepackage{amsmath}\r\n\\usepackage{amsfonts}\r\n\\RequirePackage{amsmath,amssymb,latexsym}\r\n',NULL),(1108,2,1756264282,'filter_tex','latexbackground','#FFFFFF',NULL),(1109,2,1756264283,'filter_tex','density','120',NULL),(1110,2,1756264284,'filter_tex','pathlatex','c:\\texmf\\miktex\\bin\\latex.exe',NULL),(1111,2,1756264284,'filter_tex','pathdvips','c:\\texmf\\miktex\\bin\\dvips.exe',NULL),(1112,2,1756264284,'filter_tex','pathconvert','c:\\imagemagick\\convert.exe',NULL),(1113,2,1756264284,'filter_tex','pathdvisvgm','c:\\texmf\\miktex\\bin\\dvisvgm.exe',NULL),(1114,2,1756264284,'filter_tex','pathmimetex','',NULL),(1115,2,1756264285,'format_singleactivity','activitytype','forum',NULL),(1116,2,1756264285,'format_topics','indentation','1',NULL),(1117,2,1756264285,'format_weeks','indentation','1',NULL),(1118,2,1756264285,'tool_brickfield','analysistype','0',NULL),(1119,2,1756264285,'tool_brickfield','deletehistoricaldata','1',NULL),(1120,2,1756264286,'tool_brickfield','batch','1000',NULL),(1121,2,1756264286,'tool_brickfield','perpage','50',NULL),(1122,2,1756264286,'tool_recyclebin','coursebinenable','1',NULL),(1123,2,1756264287,'tool_recyclebin','coursebinexpiry','604800',NULL),(1124,2,1756264287,'tool_recyclebin','categorybinenable','1',NULL),(1125,2,1756264287,'tool_recyclebin','categorybinexpiry','604800',NULL),(1126,2,1756264287,'tool_recyclebin','autohide','1',NULL),(1127,2,1756264287,'logstore_database','dbdriver','',NULL),(1128,2,1756264288,'logstore_database','dbhost','',NULL),(1129,2,1756264288,'logstore_database','dbuser','',NULL),(1130,2,1756264288,'logstore_database','dbpass','',NULL),(1131,2,1756264288,'logstore_database','dbname','',NULL),(1132,2,1756264288,'logstore_database','dbtable','',NULL),(1133,2,1756264288,'logstore_database','dbpersist','0',NULL),(1134,2,1756264289,'logstore_database','dbsocket','',NULL),(1135,2,1756264289,'logstore_database','dbport','',NULL),(1136,2,1756264289,'logstore_database','dbschema','',NULL),(1137,2,1756264289,'logstore_database','dbcollation','',NULL),(1138,2,1756264289,'logstore_database','dbhandlesoptions','0',NULL),(1139,2,1756264290,'logstore_database','buffersize','50',NULL),(1140,2,1756264290,'logstore_database','jsonformat','1',NULL),(1141,2,1756264290,'logstore_database','logguests','0',NULL),(1142,2,1756264290,'logstore_database','includelevels','1,2,0',NULL),(1143,2,1756264290,'logstore_database','includeactions','c,r,u,d',NULL),(1144,2,1756264290,'logstore_legacy','loglegacy','0',NULL),(1145,2,1756264291,NULL,'logguests','1',NULL),(1146,2,1756264291,NULL,'loglifetime','0',NULL),(1147,2,1756264291,'logstore_standard','logguests','1',NULL),(1148,2,1756264291,'logstore_standard','jsonformat','1',NULL),(1149,2,1756264291,'logstore_standard','loglifetime','0',NULL),(1150,2,1756264292,'logstore_standard','buffersize','50',NULL),(1151,2,1756264292,'enrol_guest','requirepassword','0',NULL),(1152,2,1756264292,'enrol_guest','usepasswordpolicy','0',NULL),(1153,2,1756264292,'enrol_guest','showhint','0',NULL),(1154,2,1756264292,'enrol_guest','defaultenrol','1',NULL),(1155,2,1756264292,'enrol_guest','status','1',NULL),(1156,2,1756264293,'enrol_guest','status_adv','',NULL),(1157,2,1756264294,'enrol_imsenterprise','imsfilelocation','',NULL),(1158,2,1756264295,'enrol_imsenterprise','logtolocation','',NULL),(1159,2,1756264295,'enrol_imsenterprise','mailadmins','0',NULL),(1160,2,1756264295,'enrol_imsenterprise','createnewusers','0',NULL),(1161,2,1756264295,'enrol_imsenterprise','imsupdateusers','0',NULL),(1162,2,1756264295,'enrol_imsenterprise','imsdeleteusers','0',NULL),(1163,2,1756264295,'enrol_imsenterprise','fixcaseusernames','0',NULL),(1164,2,1756264295,'enrol_imsenterprise','fixcasepersonalnames','0',NULL),(1165,2,1756264295,'enrol_imsenterprise','imssourcedidfallback','0',NULL),(1166,2,1756264296,'enrol_imsenterprise','imsrolemap01','5',NULL),(1167,2,1756264296,'enrol_imsenterprise','imsrolemap02','3',NULL),(1168,2,1756264296,'enrol_imsenterprise','imsrolemap03','3',NULL),(1169,2,1756264296,'enrol_imsenterprise','imsrolemap04','5',NULL),(1170,2,1756264296,'enrol_imsenterprise','imsrolemap05','0',NULL),(1171,2,1756264296,'enrol_imsenterprise','imsrolemap06','4',NULL),(1172,2,1756264297,'enrol_imsenterprise','imsrolemap07','0',NULL),(1173,2,1756264297,'enrol_imsenterprise','imsrolemap08','4',NULL),(1174,2,1756264297,'enrol_imsenterprise','truncatecoursecodes','0',NULL),(1175,2,1756264297,'enrol_imsenterprise','createnewcourses','0',NULL),(1176,2,1756264297,'enrol_imsenterprise','updatecourses','0',NULL),(1177,2,1756264298,'enrol_imsenterprise','createnewcategories','0',NULL),(1178,2,1756264298,'enrol_imsenterprise','nestedcategories','0',NULL),(1179,2,1756264298,'enrol_imsenterprise','categoryidnumber','0',NULL),(1180,2,1756264298,'enrol_imsenterprise','categoryseparator','',NULL),(1181,2,1756264298,'enrol_imsenterprise','imsunenrol','0',NULL),(1182,2,1756264298,'enrol_imsenterprise','imscoursemapshortname','coursecode',NULL),(1183,2,1756264299,'enrol_imsenterprise','imscoursemapfullname','short',NULL),(1184,2,1756264299,'enrol_imsenterprise','imscoursemapsummary','ignore',NULL),(1185,2,1756264299,'enrol_imsenterprise','imsrestricttarget','',NULL),(1186,2,1756264299,'enrol_imsenterprise','imscapitafix','0',NULL),(1187,2,1756264299,'enrol_flatfile','location','',NULL),(1188,2,1756264299,'enrol_flatfile','encoding','UTF-8',NULL),(1189,2,1756264300,'enrol_flatfile','mailstudents','0',NULL),(1190,2,1756264300,'enrol_flatfile','mailteachers','0',NULL),(1191,2,1756264300,'enrol_flatfile','mailadmins','0',NULL),(1192,2,1756264300,'enrol_flatfile','unenrolaction','3',NULL),(1193,2,1756264301,'enrol_flatfile','expiredaction','3',NULL),(1194,2,1756264301,'enrol_self','requirepassword','0',NULL),(1195,2,1756264301,'enrol_self','usepasswordpolicy','0',NULL),(1196,2,1756264301,'enrol_self','showhint','0',NULL),(1197,2,1756264301,'enrol_self','expiredaction','1',NULL),(1198,2,1756264301,'enrol_self','expirynotifyhour','6',NULL),(1199,2,1756264301,'enrol_self','defaultenrol','1',NULL),(1200,2,1756264302,'enrol_self','status','1',NULL),(1201,2,1756264302,'enrol_self','newenrols','1',NULL),(1202,2,1756264302,'enrol_self','groupkey','0',NULL),(1203,2,1756264302,'enrol_self','roleid','5',NULL),(1204,2,1756264302,'enrol_self','enrolperiod','0',NULL),(1205,2,1756264302,'enrol_self','expirynotify','0',NULL),(1206,2,1756264302,'enrol_self','expirythreshold','86400',NULL),(1207,2,1756264304,'enrol_self','longtimenosee','0',NULL),(1208,2,1756264304,'enrol_self','maxenrolled','0',NULL),(1209,2,1756264305,'enrol_self','sendcoursewelcomemessage','1',NULL),(1210,2,1756264305,'enrol_database','dbtype','',NULL),(1211,2,1756264305,'enrol_database','dbhost','localhost',NULL),(1212,2,1756264305,'enrol_database','dbuser','',NULL),(1213,2,1756264305,'enrol_database','dbpass','',NULL),(1214,2,1756264305,'enrol_database','dbname','',NULL),(1215,2,1756264305,'enrol_database','dbencoding','utf-8',NULL),(1216,2,1756264305,'enrol_database','dbsetupsql','',NULL),(1217,2,1756264306,'enrol_database','dbsybasequoting','0',NULL),(1218,2,1756264306,'enrol_database','debugdb','0',NULL),(1219,2,1756264306,'enrol_database','localcoursefield','idnumber',NULL),(1220,2,1756264306,'enrol_database','localuserfield','idnumber',NULL),(1221,2,1756264306,'enrol_database','localrolefield','shortname',NULL),(1222,2,1756264306,'enrol_database','localcategoryfield','id',NULL),(1223,2,1756264306,'enrol_database','remoteenroltable','',NULL),(1224,2,1756264307,'enrol_database','remotecoursefield','',NULL),(1225,2,1756264307,'enrol_database','remoteuserfield','',NULL),(1226,2,1756264307,'enrol_database','remoterolefield','',NULL),(1227,2,1756264308,'enrol_database','remoteotheruserfield','',NULL),(1228,2,1756264308,'enrol_database','defaultrole','5',NULL),(1229,2,1756264308,'enrol_database','ignorehiddencourses','0',NULL),(1230,2,1756264308,'enrol_database','unenrolaction','0',NULL),(1231,2,1756264309,'enrol_database','newcoursetable','',NULL),(1232,2,1756264309,'enrol_database','newcoursefullname','fullname',NULL),(1233,2,1756264309,'enrol_database','newcourseshortname','shortname',NULL),(1234,2,1756264309,'enrol_database','newcourseidnumber','idnumber',NULL),(1235,2,1756264309,'enrol_database','newcoursecategory','',NULL),(1236,2,1756264309,'enrol_database','defaultcategory','1',NULL),(1237,2,1756264310,'enrol_database','templatecourse','',NULL),(1238,2,1756264310,'enrol_fee','expiredaction','3',NULL),(1239,2,1756264310,'enrol_fee','status','1',NULL),(1240,2,1756264310,'enrol_fee','cost','0',NULL),(1241,2,1756264310,'enrol_fee','currency','USD',NULL),(1242,2,1756264310,'enrol_fee','roleid','5',NULL),(1243,2,1756264311,'enrol_fee','enrolperiod','0',NULL),(1244,2,1756264311,'enrol_manual','expiredaction','1',NULL),(1245,2,1756264311,'enrol_manual','expirynotifyhour','6',NULL),(1246,2,1756264311,'enrol_manual','defaultenrol','1',NULL),(1247,2,1756264311,'enrol_manual','status','0',NULL),(1248,2,1756264311,'enrol_manual','roleid','5',NULL),(1249,2,1756264312,'enrol_manual','enrolstart','4',NULL),(1250,2,1756264312,'enrol_manual','enrolperiod','0',NULL),(1251,2,1756264312,'enrol_manual','expirynotify','0',NULL),(1252,2,1756264312,'enrol_manual','expirythreshold','86400',NULL),(1253,2,1756264312,'enrol_mnet','roleid','5',NULL),(1254,2,1756264313,'enrol_mnet','roleid_adv','1',NULL),(1255,2,1756264313,'enrol_meta','nosyncroleids','',NULL),(1256,2,1756264313,'enrol_meta','syncall','1',NULL),(1257,2,1756264315,'enrol_meta','unenrolaction','3',NULL),(1258,2,1756264315,'enrol_meta','coursesort','sortorder',NULL),(1259,2,1756264316,'enrol_paypal','paypalbusiness','',NULL),(1260,2,1756264316,'enrol_paypal','mailstudents','0',NULL),(1261,2,1756264316,'enrol_paypal','mailteachers','0',NULL),(1262,2,1756264316,'enrol_paypal','mailadmins','0',NULL),(1263,2,1756264316,'enrol_paypal','expiredaction','3',NULL),(1264,2,1756264316,'enrol_paypal','status','1',NULL),(1265,2,1756264316,'enrol_paypal','cost','0',NULL),(1266,2,1756264317,'enrol_paypal','currency','USD',NULL),(1267,2,1756264317,'enrol_paypal','roleid','5',NULL),(1268,2,1756264317,'enrol_paypal','enrolperiod','0',NULL),(1269,2,1756264318,'enrol_lti','emaildisplay','2',NULL),(1270,2,1756264318,'enrol_lti','city','',NULL),(1271,2,1756264318,'enrol_lti','country','',NULL),(1272,2,1756264319,'enrol_lti','timezone','99',NULL),(1273,2,1756264319,'enrol_lti','lang','es_co',NULL),(1274,2,1756264320,'enrol_lti','institution','',NULL),(1275,2,1756264320,'enrol_cohort','roleid','5',NULL),(1276,2,1756264320,'enrol_cohort','unenrolaction','0',NULL),(1277,2,1756264320,NULL,'data_enablerssfeeds','0',NULL),(1278,2,1756264320,NULL,'bigbluebuttonbn_default_dpa_accepted','0',NULL),(1279,2,1756264320,NULL,'bigbluebuttonbn_server_url','https://test-moodle.blindsidenetworks.com/bigbluebutton/',NULL),(1280,2,1756264321,NULL,'bigbluebuttonbn_shared_secret','********',NULL),(1281,2,1756264321,NULL,'bigbluebuttonbn_welcome_default','',NULL),(1282,2,1756264322,NULL,'bigbluebuttonbn_welcome_editable','1',NULL),(1283,2,1756264322,NULL,'bigbluebuttonbn_recording_default','1',NULL),(1284,2,1756264322,NULL,'bigbluebuttonbn_recording_refresh_period','300',NULL),(1285,2,1756264323,NULL,'bigbluebuttonbn_recording_editable','1',NULL),(1286,2,1756264323,NULL,'bigbluebuttonbn_recording_all_from_start_default','0',NULL),(1287,2,1756264323,NULL,'bigbluebuttonbn_recording_all_from_start_editable','0',NULL),(1288,2,1756264323,NULL,'bigbluebuttonbn_recording_hide_button_default','0',NULL),(1289,2,1756264323,NULL,'bigbluebuttonbn_recording_hide_button_editable','0',NULL),(1290,2,1756264324,NULL,'bigbluebuttonbn_importrecordings_enabled','0',NULL),(1291,2,1756264324,NULL,'bigbluebuttonbn_importrecordings_from_deleted_enabled','0',NULL),(1292,2,1756264324,NULL,'bigbluebuttonbn_recordings_deleted_default','1',NULL),(1293,2,1756264324,NULL,'bigbluebuttonbn_recordings_deleted_editable','0',NULL),(1294,2,1756264324,NULL,'bigbluebuttonbn_recordings_imported_default','0',NULL),(1295,2,1756264325,NULL,'bigbluebuttonbn_recordings_imported_editable','1',NULL),(1296,2,1756264325,NULL,'bigbluebuttonbn_recordings_preview_default','1',NULL),(1297,2,1756264325,NULL,'bigbluebuttonbn_recordings_preview_editable','0',NULL),(1298,2,1756264325,NULL,'bigbluebuttonbn_recordings_asc_sort','0',NULL),(1299,2,1756264326,NULL,'bigbluebuttonbn_recording_protect_editable','1',NULL),(1300,2,1756264326,NULL,'bigbluebuttonbn_waitformoderator_default','0',NULL),(1301,2,1756264326,NULL,'bigbluebuttonbn_waitformoderator_editable','1',NULL),(1302,2,1756264326,NULL,'bigbluebuttonbn_waitformoderator_ping_interval','10',NULL),(1303,2,1756264326,NULL,'bigbluebuttonbn_waitformoderator_cache_ttl','60',NULL),(1304,2,1756264326,NULL,'bigbluebuttonbn_voicebridge_editable','0',NULL),(1305,2,1756264327,NULL,'bigbluebuttonbn_preuploadpresentation_editable','0',NULL),(1306,2,1756264327,'mod_bigbluebuttonbn','presentationdefault','',NULL),(1307,2,1756264328,NULL,'bigbluebuttonbn_userlimit_default','0',NULL),(1308,2,1756264329,NULL,'bigbluebuttonbn_userlimit_editable','0',NULL),(1309,2,1756264329,NULL,'bigbluebuttonbn_participant_moderator_default','0',NULL),(1310,2,1756264329,NULL,'bigbluebuttonbn_muteonstart_default','0',NULL),(1311,2,1756264329,NULL,'bigbluebuttonbn_muteonstart_editable','0',NULL),(1312,2,1756264329,NULL,'bigbluebuttonbn_disablecam_default','0',NULL),(1313,2,1756264329,NULL,'bigbluebuttonbn_disablecam_editable','1',NULL),(1314,2,1756264330,NULL,'bigbluebuttonbn_disablemic_default','0',NULL),(1315,2,1756264330,NULL,'bigbluebuttonbn_disablemic_editable','1',NULL),(1316,2,1756264330,NULL,'bigbluebuttonbn_disableprivatechat_default','0',NULL),(1317,2,1756264330,NULL,'bigbluebuttonbn_disableprivatechat_editable','1',NULL),(1318,2,1756264330,NULL,'bigbluebuttonbn_disablepublicchat_default','0',NULL),(1319,2,1756264331,NULL,'bigbluebuttonbn_disablepublicchat_editable','1',NULL),(1320,2,1756264331,NULL,'bigbluebuttonbn_disablenote_default','0',NULL),(1321,2,1756264331,NULL,'bigbluebuttonbn_disablenote_editable','1',NULL),(1322,2,1756264331,NULL,'bigbluebuttonbn_hideuserlist_default','0',NULL),(1323,2,1756264331,NULL,'bigbluebuttonbn_hideuserlist_editable','1',NULL),(1324,2,1756264331,NULL,'bigbluebuttonbn_lockonjoin_default','1',NULL),(1325,2,1756264332,NULL,'bigbluebuttonbn_lockonjoin_editable','0',NULL),(1326,2,1756264332,NULL,'bigbluebuttonbn_recordingready_enabled','0',NULL),(1327,2,1756264332,NULL,'bigbluebuttonbn_meetingevents_enabled','0',NULL),(1328,2,1756264332,'folder','showexpanded','1',NULL),(1329,2,1756264333,'folder','maxsizetodownload','0',NULL),(1330,2,1756264333,NULL,'chat_method','ajax',NULL),(1331,2,1756264333,NULL,'chat_refresh_userlist','10',NULL),(1332,2,1756264333,NULL,'chat_old_ping','35',NULL),(1333,2,1756264334,NULL,'chat_refresh_room','5',NULL),(1334,2,1756264334,NULL,'chat_normal_updatemode','jsupdate',NULL),(1335,2,1756264334,NULL,'chat_serverhost','localhost',NULL),(1336,2,1756264334,NULL,'chat_serverip','127.0.0.1',NULL),(1337,2,1756264334,NULL,'chat_serverport','9111',NULL),(1338,2,1756264335,NULL,'chat_servermax','100',NULL),(1339,2,1756264335,'quiz','timelimit','0',NULL),(1340,2,1756264335,'quiz','timelimit_adv','',NULL),(1341,2,1756264335,'quiz','timelimit_locked','',NULL),(1342,2,1756264336,'quiz','notifyattemptgradeddelay','18000',NULL),(1343,2,1756264336,'quiz','overduehandling','autosubmit',NULL),(1344,2,1756264336,'quiz','overduehandling_adv','',NULL),(1345,2,1756264336,'quiz','overduehandling_locked','',NULL),(1346,2,1756264336,'quiz','graceperiod','86400',NULL),(1347,2,1756264336,'quiz','graceperiod_adv','',NULL),(1348,2,1756264337,'quiz','graceperiod_locked','',NULL),(1349,2,1756264337,'quiz','graceperiodmin','60',NULL),(1350,2,1756264337,'quiz','attempts','0',NULL),(1351,2,1756264337,'quiz','attempts_adv','',NULL),(1352,2,1756264337,'quiz','attempts_locked','',NULL),(1353,2,1756264337,'quiz','grademethod','1',NULL),(1354,2,1756264337,'quiz','grademethod_adv','',NULL),(1355,2,1756264338,'quiz','grademethod_locked','',NULL),(1356,2,1756264338,'quiz','maximumgrade','10',NULL),(1357,2,1756264340,'quiz','maximumgrade_locked','',NULL),(1358,2,1756264340,'quiz','questionsperpage','1',NULL),(1359,2,1756264340,'quiz','questionsperpage_adv','',NULL),(1360,2,1756264340,'quiz','questionsperpage_locked','',NULL),(1361,2,1756264340,'quiz','navmethod','free',NULL),(1362,2,1756264340,'quiz','navmethod_adv','1',NULL),(1363,2,1756264341,'quiz','navmethod_locked','',NULL),(1364,2,1756264341,'quiz','shuffleanswers','1',NULL),(1365,2,1756264341,'quiz','shuffleanswers_adv','',NULL),(1366,2,1756264341,'quiz','shuffleanswers_locked','',NULL),(1367,2,1756264341,'quiz','preferredbehaviour','deferredfeedback',NULL),(1368,2,1756264342,'quiz','preferredbehaviour_locked','',NULL),(1369,2,1756264342,'quiz','canredoquestions','0',NULL),(1370,2,1756264343,'quiz','canredoquestions_adv','1',NULL),(1371,2,1756264343,'quiz','canredoquestions_locked','',NULL),(1372,2,1756264343,'quiz','attemptonlast','0',NULL),(1373,2,1756264343,'quiz','attemptonlast_adv','1',NULL),(1374,2,1756264343,'quiz','attemptonlast_locked','',NULL),(1375,2,1756264343,'quiz','reviewattempt','69904',NULL),(1376,2,1756264344,'quiz','reviewcorrectness','69904',NULL),(1377,2,1756264344,'quiz','reviewmarks','69904',NULL),(1378,2,1756264344,'quiz','reviewspecificfeedback','69904',NULL),(1379,2,1756264344,'quiz','reviewgeneralfeedback','69904',NULL),(1380,2,1756264344,'quiz','reviewrightanswer','69904',NULL),(1381,2,1756264344,'quiz','reviewoverallfeedback','4368',NULL),(1382,2,1756264344,'quiz','showuserpicture','0',NULL),(1383,2,1756264345,'quiz','showuserpicture_adv','',NULL),(1384,2,1756264345,'quiz','showuserpicture_locked','',NULL),(1385,2,1756264345,'quiz','decimalpoints','2',NULL),(1386,2,1756264345,'quiz','decimalpoints_adv','',NULL),(1387,2,1756264345,'quiz','decimalpoints_locked','',NULL),(1388,2,1756264346,'quiz','questiondecimalpoints','-1',NULL),(1389,2,1756264346,'quiz','questiondecimalpoints_adv','',NULL),(1390,2,1756264346,'quiz','questiondecimalpoints_locked','',NULL),(1391,2,1756264346,'quiz','showblocks','0',NULL),(1392,2,1756264346,'quiz','showblocks_adv','1',NULL),(1393,2,1756264346,'quiz','showblocks_locked','',NULL),(1394,2,1756264346,'quiz','quizpassword','',NULL),(1395,2,1756264347,'quiz','quizpassword_adv','',NULL),(1396,2,1756264347,'quiz','quizpassword_required','',NULL),(1397,2,1756264347,'quiz','quizpassword_locked','',NULL),(1398,2,1756264347,'quiz','subnet','',NULL),(1399,2,1756264347,'quiz','subnet_adv','1',NULL),(1400,2,1756264347,'quiz','subnet_locked','',NULL),(1401,2,1756264347,'quiz','delay1','0',NULL),(1402,2,1756264347,'quiz','delay1_adv','1',NULL),(1403,2,1756264348,'quiz','delay1_locked','',NULL),(1404,2,1756264348,'quiz','delay2','0',NULL),(1405,2,1756264348,'quiz','delay2_adv','1',NULL),(1406,2,1756264349,'quiz','delay2_locked','',NULL),(1407,2,1756264350,'quiz','browsersecurity','-',NULL),(1408,2,1756264350,'quiz','browsersecurity_adv','1',NULL),(1409,2,1756264351,'quiz','browsersecurity_locked','',NULL),(1410,2,1756264351,'quiz','initialnumfeedbacks','2',NULL),(1411,2,1756264351,'quiz','autosaveperiod','60',NULL),(1412,2,1756264351,'quizaccess_seb','autoreconfigureseb','1',NULL),(1413,2,1756264351,'quizaccess_seb','showseblinks','seb,http',NULL),(1414,2,1756264352,'quizaccess_seb','downloadlink','https://safeexambrowser.org/download_en.html',NULL),(1415,2,1756264352,'quizaccess_seb','quizpasswordrequired','0',NULL),(1416,2,1756264352,'quizaccess_seb','displayblocksbeforestart','0',NULL),(1417,2,1756264352,'quizaccess_seb','displayblockswhenfinished','1',NULL),(1418,2,1756264353,'label','dndmedia','1',NULL),(1419,2,1756264353,'label','dndresizewidth','400',NULL),(1420,2,1756264353,'label','dndresizeheight','400',NULL),(1421,2,1756264353,NULL,'forum_displaymode','3',NULL),(1422,2,1756264353,NULL,'forum_shortpost','300',NULL),(1423,2,1756264353,NULL,'forum_longpost','600',NULL),(1424,2,1756264354,NULL,'forum_manydiscussions','100',NULL),(1425,2,1756264354,NULL,'forum_maxbytes','512000',NULL),(1426,2,1756264354,NULL,'forum_maxattachments','9',NULL),(1427,2,1756264354,NULL,'forum_subscription','0',NULL),(1428,2,1756264355,NULL,'forum_trackingtype','1',NULL),(1429,2,1756264355,NULL,'forum_trackreadposts','1',NULL),(1430,2,1756264355,NULL,'forum_allowforcedreadtracking','0',NULL),(1431,2,1756264355,NULL,'forum_oldpostdays','14',NULL),(1432,2,1756264356,NULL,'forum_usermarksread','0',NULL),(1433,2,1756264356,NULL,'forum_cleanreadtime','2',NULL),(1434,2,1756264356,NULL,'digestmailtime','17',NULL),(1435,2,1756264356,NULL,'forum_enablerssfeeds','0',NULL),(1436,2,1756264356,NULL,'forum_enabletimedposts','1',NULL),(1437,2,1756264356,NULL,'glossary_entbypage','10',NULL),(1438,2,1756264356,NULL,'glossary_dupentries','0',NULL),(1439,2,1756264357,NULL,'glossary_allowcomments','0',NULL),(1440,2,1756264357,NULL,'glossary_linkbydefault','1',NULL),(1441,2,1756264357,NULL,'glossary_defaultapproval','1',NULL),(1442,2,1756264357,NULL,'glossary_enablerssfeeds','0',NULL),(1443,2,1756264358,NULL,'glossary_linkentries','0',NULL),(1444,2,1756264358,NULL,'glossary_casesensitive','0',NULL),(1445,2,1756264358,NULL,'glossary_fullmatch','0',NULL),(1446,2,1756264358,'mod_lesson','mediafile','',NULL),(1447,2,1756264358,'mod_lesson','mediafile_adv','1',NULL),(1448,2,1756264359,'mod_lesson','mediawidth','640',NULL),(1449,2,1756264359,'mod_lesson','mediaheight','480',NULL),(1450,2,1756264359,'mod_lesson','mediaclose','0',NULL),(1451,2,1756264359,'mod_lesson','progressbar','0',NULL),(1452,2,1756264359,'mod_lesson','progressbar_adv','',NULL),(1453,2,1756264360,'mod_lesson','ongoing','0',NULL),(1454,2,1756264360,'mod_lesson','ongoing_adv','1',NULL),(1455,2,1756264360,'mod_lesson','displayleftmenu','0',NULL),(1456,2,1756264360,'mod_lesson','displayleftmenu_adv','',NULL),(1457,2,1756264362,'mod_lesson','displayleftif','0',NULL),(1458,2,1756264363,'mod_lesson','displayleftif_adv','1',NULL),(1459,2,1756264363,'mod_lesson','slideshow','0',NULL),(1460,2,1756264363,'mod_lesson','slideshow_adv','1',NULL),(1461,2,1756264363,'mod_lesson','slideshowwidth','640',NULL),(1462,2,1756264364,'mod_lesson','slideshowheight','480',NULL),(1463,2,1756264364,'mod_lesson','slideshowbgcolor','#FFFFFF',NULL),(1464,2,1756264364,'mod_lesson','maxanswers','5',NULL),(1465,2,1756264364,'mod_lesson','maxanswers_adv','1',NULL),(1466,2,1756264364,'mod_lesson','defaultfeedback','0',NULL),(1467,2,1756264365,'mod_lesson','defaultfeedback_adv','1',NULL),(1468,2,1756264365,'mod_lesson','activitylink','',NULL),(1469,2,1756264365,'mod_lesson','activitylink_adv','1',NULL),(1470,2,1756264365,'mod_lesson','timelimit','0',NULL),(1471,2,1756264365,'mod_lesson','timelimit_adv','',NULL),(1472,2,1756264365,'mod_lesson','password','0',NULL),(1473,2,1756264366,'mod_lesson','password_adv','1',NULL),(1474,2,1756264366,'mod_lesson','modattempts','0',NULL),(1475,2,1756264366,'mod_lesson','modattempts_adv','',NULL),(1476,2,1756264366,'mod_lesson','displayreview','0',NULL),(1477,2,1756264366,'mod_lesson','displayreview_adv','',NULL),(1478,2,1756264367,'mod_lesson','maximumnumberofattempts','1',NULL),(1479,2,1756264367,'mod_lesson','maximumnumberofattempts_adv','',NULL),(1480,2,1756264367,'mod_lesson','defaultnextpage','0',NULL),(1481,2,1756264367,'mod_lesson','defaultnextpage_adv','1',NULL),(1482,2,1756264367,'mod_lesson','numberofpagestoshow','1',NULL),(1483,2,1756264367,'mod_lesson','numberofpagestoshow_adv','1',NULL),(1484,2,1756264368,'mod_lesson','practice','0',NULL),(1485,2,1756264368,'mod_lesson','practice_adv','',NULL),(1486,2,1756264368,'mod_lesson','customscoring','1',NULL),(1487,2,1756264369,'mod_lesson','customscoring_adv','1',NULL),(1488,2,1756264369,'mod_lesson','retakesallowed','0',NULL),(1489,2,1756264369,'mod_lesson','retakesallowed_adv','',NULL),(1490,2,1756264369,'mod_lesson','handlingofretakes','0',NULL),(1491,2,1756264369,'mod_lesson','handlingofretakes_adv','1',NULL),(1492,2,1756264370,'mod_lesson','minimumnumberofquestions','0',NULL),(1493,2,1756264370,'mod_lesson','minimumnumberofquestions_adv','1',NULL),(1494,2,1756264370,'book','numberingoptions','0,1,2,3',NULL),(1495,2,1756264370,'book','numbering','1',NULL),(1496,2,1756264371,NULL,'feedback_allowfullanonymous','0',NULL),(1497,2,1756264371,'page','displayoptions','5',NULL),(1498,2,1756264371,'page','printintro','0',NULL),(1499,2,1756264371,'page','printlastmodified','1',NULL),(1500,2,1756264371,'page','display','5',NULL),(1501,2,1756264371,'page','popupwidth','620',NULL),(1502,2,1756264372,'page','popupheight','450',NULL),(1503,2,1756264372,'imscp','keepold','1',NULL),(1504,2,1756264372,'imscp','keepold_adv','',NULL),(1505,2,1756264372,'scorm','displaycoursestructure','0',NULL),(1506,2,1756264373,'scorm','displaycoursestructure_adv','',NULL),(1507,2,1756264374,'scorm','popup','0',NULL),(1508,2,1756264374,'scorm','popup_adv','',NULL),(1509,2,1756264375,'scorm','framewidth','100',NULL),(1510,2,1756264375,'scorm','framewidth_adv','1',NULL),(1511,2,1756264375,'scorm','frameheight','500',NULL),(1512,2,1756264375,'scorm','frameheight_adv','1',NULL),(1513,2,1756264375,'scorm','winoptgrp_adv','1',NULL),(1514,2,1756264375,'scorm','scrollbars','0',NULL),(1515,2,1756264376,'scorm','directories','0',NULL),(1516,2,1756264376,'scorm','location','0',NULL),(1517,2,1756264376,'scorm','menubar','0',NULL),(1518,2,1756264376,'scorm','toolbar','0',NULL),(1519,2,1756264376,'scorm','status','0',NULL),(1520,2,1756264377,'scorm','skipview','0',NULL),(1521,2,1756264377,'scorm','skipview_adv','1',NULL),(1522,2,1756264377,'scorm','hidebrowse','0',NULL),(1523,2,1756264377,'scorm','hidebrowse_adv','1',NULL),(1524,2,1756264377,'scorm','hidetoc','0',NULL),(1525,2,1756264378,'scorm','hidetoc_adv','1',NULL),(1526,2,1756264378,'scorm','nav','1',NULL),(1527,2,1756264378,'scorm','nav_adv','1',NULL),(1528,2,1756264378,'scorm','navpositionleft','-100',NULL),(1529,2,1756264379,'scorm','navpositionleft_adv','1',NULL),(1530,2,1756264379,'scorm','navpositiontop','-100',NULL),(1531,2,1756264379,'scorm','navpositiontop_adv','1',NULL),(1532,2,1756264379,'scorm','collapsetocwinsize','767',NULL),(1533,2,1756264379,'scorm','collapsetocwinsize_adv','1',NULL),(1534,2,1756264379,'scorm','displayattemptstatus','1',NULL),(1535,2,1756264379,'scorm','displayattemptstatus_adv','',NULL),(1536,2,1756264380,'scorm','grademethod','1',NULL),(1537,2,1756264380,'scorm','maxgrade','100',NULL),(1538,2,1756264380,'scorm','maxattempt','0',NULL),(1539,2,1756264380,'scorm','whatgrade','0',NULL),(1540,2,1756264380,'scorm','forcecompleted','0',NULL),(1541,2,1756264381,'scorm','forcenewattempt','0',NULL),(1542,2,1756264381,'scorm','autocommit','0',NULL),(1543,2,1756264381,'scorm','masteryoverride','1',NULL),(1544,2,1756264381,'scorm','lastattemptlock','0',NULL),(1545,2,1756264382,'scorm','auto','0',NULL),(1546,2,1756264382,'scorm','updatefreq','0',NULL),(1547,2,1756264382,'scorm','scormstandard','0',NULL),(1548,2,1756264382,'scorm','allowtypeexternal','0',NULL),(1549,2,1756264382,'scorm','allowtypelocalsync','0',NULL),(1550,2,1756264382,'scorm','allowtypeexternalaicc','0',NULL),(1551,2,1756264383,'scorm','allowaicchacp','0',NULL),(1552,2,1756264383,'scorm','aicchacptimeout','30',NULL),(1553,2,1756264383,'scorm','aicchacpkeepsessiondata','1',NULL),(1554,2,1756264383,'scorm','aiccuserid','1',NULL),(1555,2,1756264383,'scorm','forcejavascript','1',NULL),(1556,2,1756264383,'scorm','allowapidebug','0',NULL),(1557,2,1756264386,'scorm','apidebugmask','.*',NULL),(1558,2,1756264386,'scorm','protectpackagedownloads','0',NULL),(1559,2,1756264386,'resource','framesize','130',NULL),(1560,2,1756264387,'resource','displayoptions','0,1,4,5,6',NULL),(1561,2,1756264387,'resource','printintro','1',NULL),(1562,2,1756264395,'resource','display','0',NULL),(1563,2,1756264395,'resource','showsize','0',NULL),(1564,2,1756264395,'resource','showtype','0',NULL),(1565,2,1756264396,'resource','showdate','0',NULL),(1566,2,1756264396,'resource','popupwidth','620',NULL),(1567,2,1756264396,'resource','popupheight','450',NULL),(1568,2,1756264396,'resource','filterfiles','0',NULL),(1569,2,1756264396,'workshop','grade','80',NULL),(1570,2,1756264396,'workshop','gradinggrade','20',NULL),(1571,2,1756264396,'workshop','gradedecimals','0',NULL),(1572,2,1756264397,'workshop','maxbytes','0',NULL),(1573,2,1756264397,'workshop','strategy','accumulative',NULL),(1574,2,1756264397,'workshop','examplesmode','0',NULL),(1575,2,1756264397,'workshopallocation_random','numofreviews','5',NULL),(1576,2,1756264397,'workshopform_numerrors','grade0','No',NULL),(1577,2,1756264397,'workshopform_numerrors','grade1','S??',NULL),(1578,2,1756264398,'workshopeval_best','comparison','5',NULL),(1579,2,1756264398,'assign','feedback_plugin_for_gradebook','assignfeedback_comments',NULL),(1580,2,1756264398,'assign','showrecentsubmissions','0',NULL),(1581,2,1756264398,'assign','submissionreceipts','1',NULL),(1582,2,1756264398,'assign','submissionstatement','Confirmo que este trabajo es de elaboraci??n propia, excepto aquellas partes en las que haya reconocido la autor??a de la obra o parte de ella a otras personas.',NULL),(1583,2,1756264399,'assign','submissionstatementteamsubmission','Confirmo que este env??o es trabajo de mi grupo, excepto aquellas partes en las que se haya reconocido la autor??a de la obra o parte de ella a otras personas.',NULL),(1584,2,1756264399,'assign','submissionstatementteamsubmissionallsubmit','Confirmo que este trabajo es de elaboraci??n propia como miembro del grupo, excepto aquellas partes en las que haya reconocido la autor??a de la obra o parte de ella a otras personas.',NULL),(1585,2,1756264399,'assign','maxperpage','-1',NULL),(1586,2,1756264399,'assign','alwaysshowdescription','1',NULL),(1587,2,1756264400,'assign','alwaysshowdescription_adv','',NULL),(1588,2,1756264400,'assign','alwaysshowdescription_locked','',NULL),(1589,2,1756264400,'assign','allowsubmissionsfromdate','0',NULL),(1590,2,1756264400,'assign','allowsubmissionsfromdate_enabled','1',NULL),(1591,2,1756264401,'assign','allowsubmissionsfromdate_adv','',NULL),(1592,2,1756264401,'assign','duedate','604800',NULL),(1593,2,1756264401,'assign','duedate_enabled','1',NULL),(1594,2,1756264401,'assign','duedate_adv','',NULL),(1595,2,1756264401,'assign','cutoffdate','1209600',NULL),(1596,2,1756264401,'assign','cutoffdate_enabled','',NULL),(1597,2,1756264402,'assign','cutoffdate_adv','',NULL),(1598,2,1756264402,'assign','enabletimelimit','0',NULL),(1599,2,1756264402,'assign','gradingduedate','1209600',NULL),(1600,2,1756264402,'assign','gradingduedate_enabled','1',NULL),(1601,2,1756264403,'assign','gradingduedate_adv','',NULL),(1602,2,1756264403,'assign','submissiondrafts','0',NULL),(1603,2,1756264403,'assign','submissiondrafts_adv','',NULL),(1604,2,1756264403,'assign','submissiondrafts_locked','',NULL),(1605,2,1756264403,'assign','requiresubmissionstatement','0',NULL),(1606,2,1756264404,'assign','requiresubmissionstatement_adv','',NULL),(1607,2,1756264404,'assign','requiresubmissionstatement_locked','',NULL),(1608,2,1756264404,'assign','attemptreopenmethod','none',NULL),(1609,2,1756264404,'assign','attemptreopenmethod_adv','',NULL),(1610,2,1756264404,'assign','attemptreopenmethod_locked','',NULL),(1611,2,1756264405,'assign','maxattempts','-1',NULL),(1612,2,1756264406,'assign','maxattempts_adv','',NULL),(1613,2,1756264406,'assign','maxattempts_locked','',NULL),(1614,2,1756264406,'assign','teamsubmission','0',NULL),(1615,2,1756264407,'assign','teamsubmission_adv','',NULL),(1616,2,1756264407,'assign','teamsubmission_locked','',NULL),(1617,2,1756264407,'assign','preventsubmissionnotingroup','0',NULL),(1618,2,1756264407,'assign','preventsubmissionnotingroup_adv','',NULL),(1619,2,1756264407,'assign','preventsubmissionnotingroup_locked','',NULL),(1620,2,1756264408,'assign','requireallteammemberssubmit','0',NULL),(1621,2,1756264408,'assign','requireallteammemberssubmit_adv','',NULL),(1622,2,1756264408,'assign','requireallteammemberssubmit_locked','',NULL),(1623,2,1756264408,'assign','teamsubmissiongroupingid','',NULL),(1624,2,1756264408,'assign','teamsubmissiongroupingid_adv','',NULL),(1625,2,1756264409,'assign','sendnotifications','0',NULL),(1626,2,1756264409,'assign','sendnotifications_adv','',NULL),(1627,2,1756264409,'assign','sendnotifications_locked','',NULL),(1628,2,1756264409,'assign','sendlatenotifications','0',NULL),(1629,2,1756264409,'assign','sendlatenotifications_adv','',NULL),(1630,2,1756264409,'assign','sendlatenotifications_locked','',NULL),(1631,2,1756264410,'assign','sendstudentnotifications','1',NULL),(1632,2,1756264410,'assign','sendstudentnotifications_adv','',NULL),(1633,2,1756264410,'assign','sendstudentnotifications_locked','',NULL),(1634,2,1756264410,'assign','blindmarking','0',NULL),(1635,2,1756264410,'assign','blindmarking_adv','',NULL),(1636,2,1756264411,'assign','blindmarking_locked','',NULL),(1637,2,1756264411,'assign','hidegrader','0',NULL),(1638,2,1756264411,'assign','hidegrader_adv','',NULL),(1639,2,1756264411,'assign','hidegrader_locked','',NULL),(1640,2,1756264411,'assign','markingworkflow','0',NULL),(1641,2,1756264412,'assign','markingworkflow_adv','',NULL),(1642,2,1756264412,'assign','markingworkflow_locked','',NULL),(1643,2,1756264412,'assign','markingallocation','0',NULL),(1644,2,1756264412,'assign','markingallocation_adv','',NULL),(1645,2,1756264412,'assign','markingallocation_locked','',NULL),(1646,2,1756264413,'assignsubmission_file','default','1',NULL),(1647,2,1756264413,'assignsubmission_file','maxfiles','20',NULL),(1648,2,1756264413,'assignsubmission_file','filetypes','',NULL),(1649,2,1756264413,'assignsubmission_file','maxbytes','0',NULL),(1650,2,1756264414,'assignsubmission_onlinetext','default','0',NULL),(1651,2,1756264414,'assignfeedback_comments','default','1',NULL),(1652,2,1756264414,'assignfeedback_comments','inline','0',NULL),(1653,2,1756264414,'assignfeedback_comments','inline_adv','',NULL),(1654,2,1756264414,'assignfeedback_comments','inline_locked','',NULL),(1655,2,1756264415,'assignfeedback_editpdf','default','1',NULL),(1656,2,1756264415,'assignfeedback_editpdf','stamps','/cross.png',NULL),(1657,2,1756264415,'assignfeedback_file','default','0',NULL),(1658,2,1756264415,'assignfeedback_offline','default','0',NULL),(1659,2,1756264415,'url','framesize','130',NULL),(1660,2,1756264416,'url','secretphrase','',NULL),(1661,2,1756264416,'url','rolesinparams','0',NULL),(1662,2,1756264417,'url','displayoptions','0,1,5,6',NULL),(1663,2,1756264417,'url','printintro','1',NULL),(1664,2,1756264418,'url','display','0',NULL),(1665,2,1756264418,'url','popupwidth','620',NULL),(1666,2,1756264418,'url','popupheight','450',NULL),(1667,2,1756264419,'paygw_paypal','surcharge','0',NULL),(1668,2,1756264419,'media_videojs','videoextensions','html_video,media_source,.f4v,.flv',NULL),(1669,2,1756264419,'media_videojs','audioextensions','html_audio',NULL),(1670,2,1756264419,'media_videojs','youtube','1',NULL),(1671,2,1756264420,'media_videojs','videocssclass','video-js',NULL),(1672,2,1756264420,'media_videojs','audiocssclass','video-js',NULL),(1673,2,1756264420,'media_videojs','limitsize','1',NULL),(1674,2,1756264420,'qtype_multichoice','answerhowmany','1',NULL),(1675,2,1756264420,'qtype_multichoice','shuffleanswers','1',NULL),(1676,2,1756264420,'qtype_multichoice','answernumbering','abc',NULL),(1677,2,1756264421,NULL,'profileroles','3,4,5',NULL),(1678,2,1756264421,NULL,'allowguestmymoodle','1',NULL),(1679,2,1756264421,NULL,'coursecontact','3',NULL),(1680,2,1756264422,NULL,'frontpage','6',NULL),(1681,2,1756264422,NULL,'frontpageloggedin','6',NULL),(1682,2,1756264423,NULL,'maxcategorydepth','2',NULL),(1683,2,1756264423,NULL,'frontpagecourselimit','200',NULL),(1684,2,1756264423,NULL,'commentsperpage','15',NULL),(1685,2,1756264423,NULL,'defaultfrontpageroleid','8',NULL),(1686,2,1756264423,NULL,'messageinbound_enabled','0',NULL),(1687,2,1756264424,NULL,'messageinbound_mailbox','',NULL),(1688,2,1756264424,NULL,'messageinbound_domain','',NULL),(1689,2,1756264424,NULL,'messageinbound_host','',NULL),(1690,2,1756264424,NULL,'messageinbound_hostssl','ssl',NULL),(1691,2,1756264425,NULL,'messageinbound_hostuser','',NULL),(1692,2,1756264425,NULL,'messageinbound_hostpass','',NULL),(1693,2,1756264425,'tool_mobile','apppolicy','',NULL),(1694,2,1756264425,'tool_mobile','typeoflogin','1',NULL),(1695,2,1756264425,'tool_mobile','qrcodetype','1',NULL),(1696,2,1756264426,'tool_mobile','qrkeyttl','600',NULL),(1697,2,1756264426,'tool_mobile','forcedurlscheme','moodlemobile',NULL),(1698,2,1756264426,'tool_mobile','minimumversion','',NULL),(1699,2,1756264426,'tool_mobile','autologinmintimebetweenreq','360',NULL),(1700,2,1756264426,NULL,'mobilecssurl','',NULL),(1701,2,1756264427,'tool_mobile','enablesmartappbanners','0',NULL),(1702,2,1756264427,'tool_mobile','iosappid','633359593',NULL),(1703,2,1756264427,'tool_mobile','androidappid','com.moodle.moodlemobile',NULL),(1704,2,1756264427,'tool_mobile','setuplink','https://download.moodle.org/mobile',NULL),(1705,2,1756264428,'tool_mobile','forcelogout','0',NULL),(1706,2,1756264428,'tool_mobile','disabledfeatures','',NULL),(1707,2,1756264429,'tool_mobile','custommenuitems','',NULL),(1708,2,1756264429,'tool_mobile','filetypeexclusionlist','',NULL),(1709,2,1756264429,'tool_mobile','customlangstrings','',NULL),(1710,2,1756264429,'tool_moodlenet','defaultmoodlenetname','Central MoodleNet',NULL),(1711,2,1756264429,'tool_moodlenet','defaultmoodlenet','https://moodle.net',NULL),(1712,2,1756264469,NULL,'supportemail','andres.paz1991@gmail.com',NULL),(1713,2,1756264469,NULL,'noreplyaddress','andres.paz1991@gmail.com',NULL),(1714,2,1756515146,'moodlecourse','numsections','10','4'),(1715,2,1756515291,'moodlecourse','newsitems','10','5'),(1716,2,1756515448,NULL,'frontpage','7,2,6','6'),(1717,2,1756515448,NULL,'frontpageloggedin','7,2,6','6'),(1718,2,1756516357,'theme_moove','logo','/logo_josefina.png',NULL),(1719,2,1756516358,'theme_moove','favicon','/favicon.ico',NULL),(1720,2,1756516358,'theme_moove','preset','default.scss',NULL),(1721,2,1756516358,'theme_moove','presetfiles','',NULL),(1722,2,1756516359,'theme_moove','loginbgimg','/fondo_Vallesol.jpg',NULL),(1723,2,1756516359,'theme_moove','brandcolor','#0f47ad',NULL),(1724,2,1756516359,'theme_moove','secondarymenucolor','#0f47ad',NULL),(1725,2,1756516359,'theme_moove','fontsite','Roboto',NULL),(1726,2,1756516359,'theme_moove','enablecourseindex','1',NULL),(1727,2,1756516360,'theme_moove','scsspre','',NULL),(1728,2,1756516360,'theme_moove','scss','',NULL),(1729,2,1756516360,'theme_moove','googleanalytics','',NULL),(1730,2,1756516360,'theme_moove','disableteacherspic','1',NULL),(1731,2,1756516360,'theme_moove','slidercount','0',NULL),(1732,2,1756516361,'theme_moove','displaymarketingbox','1',NULL),(1733,2,1756516361,'theme_moove','numbersfrontpage','1',NULL),(1734,2,1756516361,'theme_moove','faqcount','0',NULL),(1735,2,1756516361,'theme_moove','website','',NULL),(1736,2,1756516361,'theme_moove','mobile','',NULL),(1737,2,1756516361,'theme_moove','mail','iervallesol@gmail.com',NULL),(1738,2,1756516361,'theme_moove','facebook','',NULL),(1739,2,1756516362,'theme_moove','twitter','',NULL),(1740,2,1756516362,'theme_moove','linkedin','',NULL),(1741,2,1756516362,'theme_moove','youtube','',NULL),(1742,2,1756516362,'theme_moove','instagram','https://www.instagram.com/momentosvallesol/',NULL),(1743,2,1756516362,'theme_moove','whatsapp','',NULL),(1744,2,1756516362,'theme_moove','telegram','',NULL),(1745,2,1756516435,'theme_moove','marketingheading','Vallesol',NULL),(1746,2,1756516435,'theme_moove','marketingcontent','Plataforma de Vallesol',NULL),(1747,2,1756516435,'theme_moove','marketing1icon','',NULL),(1748,2,1756516435,'theme_moove','marketing1heading','Lorem',NULL),(1749,2,1756516436,'theme_moove','marketing1content','Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.',NULL),(1750,2,1756516436,'theme_moove','marketing2icon','',NULL),(1751,2,1756516436,'theme_moove','marketing2heading','Lorem',NULL),(1752,2,1756516436,'theme_moove','marketing2content','Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.',NULL),(1753,2,1756516436,'theme_moove','marketing3icon','',NULL),(1754,2,1756516436,'theme_moove','marketing3heading','Lorem',NULL),(1755,2,1756516436,'theme_moove','marketing3content','Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.',NULL),(1756,2,1756516437,'theme_moove','marketing4icon','',NULL),(1757,2,1756516437,'theme_moove','marketing4heading','Lorem',NULL),(1758,2,1756516437,'theme_moove','marketing4content','Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.',NULL),(1759,2,1756516437,'theme_moove','numbersfrontpagecontent','<h2>Con la confianza de m??s de 25,000 clientes satisfechos.</h2>\r\n                    <p>Con muchos bloques ??nicos usted puede f??cilmente construir <br class=\"d-none d-sm-block d-md-none d-xl-block\">\r\n                        una p??gina sin escribir c??digo. Construya su pr??ximo sitio web <br class=\"d-none d-sm-block d-md-none d-xl-block\">\r\n                        en pocos minutos.</p>',NULL),(1760,2,1756516913,NULL,'frontpage','7,2,6,4','7,2,6'),(1761,2,1756516913,NULL,'frontpageloggedin','7,2,6,4','7,2,6'),(1762,2,1756517431,'theme_moove','slidercount','5','0'),(1763,2,1756517431,'theme_moove','numbersfrontpage','0','1'),(1764,2,1756517432,'theme_moove','numbersfrontpagecontent','<h2>Espacio de Aprendizaje<br></h2>','<h2>Con la confianza de m??s de 25,000 clientes satisfechos.</h2>\r\n                    <p>Con muchos bloques ??nicos usted puede f??cilmente construir <br class=\"d-none d-sm-block d-md-none d-xl-block\">\r\n                        una p??gina sin escribir c??digo. Construya su pr??ximo sitio web <br class=\"d-none d-sm-block d-md-none d-xl-block\">\r\n                        en pocos minutos.</p>'),(1765,2,1756517567,'theme_moove','slidercount','1','5'),(1766,2,1756517568,'theme_moove','sliderimage1','/vallesol.jpg',NULL),(1767,2,1756517568,'theme_moove','slidertitle1','Vallesol',NULL),(1768,2,1756517568,'theme_moove','slidercap1','',NULL),(1769,2,1756517568,'theme_moove','sliderimage2','',NULL),(1770,2,1756517568,'theme_moove','slidertitle2','',NULL),(1771,2,1756517569,'theme_moove','slidercap2','',NULL),(1772,2,1756517569,'theme_moove','sliderimage3','',NULL),(1773,2,1756517569,'theme_moove','slidertitle3','',NULL),(1774,2,1756517569,'theme_moove','slidercap3','',NULL),(1775,2,1756517570,'theme_moove','sliderimage4','',NULL),(1776,2,1756517570,'theme_moove','slidertitle4','',NULL),(1777,2,1756517570,'theme_moove','slidercap4','',NULL),(1778,2,1756517570,'theme_moove','sliderimage5','',NULL),(1779,2,1756517570,'theme_moove','slidertitle5','',NULL),(1780,2,1756517570,'theme_moove','slidercap5','',NULL),(1781,2,1756517571,'theme_moove','displaymarketingbox','0','1'),(1782,2,1756518016,NULL,'useexternalyui','1','0'),(1783,2,1756520314,NULL,'frontpageloggedin','','7,2,6,4'),(1784,2,1756520426,NULL,'frontpage','','7,2,6,4'),(1785,2,1756520482,NULL,'frontpageloggedin','2',''),(1786,2,1756520721,NULL,'frontpageloggedin','7,2,5','2'),(1787,2,1756520923,NULL,'frontpage','6',''),(1788,2,1756522042,'attendance','resultsperpage','100',NULL),(1789,2,1756522042,'attendance','studentscanmark','1',NULL),(1790,2,1756522043,'attendance','rotateqrcodeinterval','15',NULL),(1791,2,1756522043,'attendance','rotateqrcodeexpirymargin','2',NULL),(1792,2,1756522043,'attendance','studentscanmarksessiontime','1',NULL),(1793,2,1756522043,'attendance','studentscanmarksessiontimeend','60',NULL),(1794,2,1756522044,'attendance','subnetactivitylevel','1',NULL),(1795,2,1756522044,'attendance','defaultview','2',NULL),(1796,2,1756522044,'attendance','multisessionexpanded','0',NULL),(1797,2,1756522044,'attendance','showsessiondescriptiononreport','0',NULL),(1798,2,1756522044,'attendance','studentrecordingexpanded','1',NULL),(1799,2,1756522044,'attendance','enablecalendar','1',NULL),(1800,2,1756522045,'attendance','enablewarnings','0',NULL),(1801,2,1756522045,'attendance','automark_useempty','1',NULL),(1802,2,1756522045,'attendance','customexportfields','id',NULL),(1803,2,1756522045,'attendance','mobilesessionfrom','21600',NULL),(1804,2,1756522045,'attendance','mobilesessionto','86400',NULL),(1805,2,1756522045,'attendance','subnet','',NULL),(1806,2,1756522046,'attendance','calendarevent_default','1',NULL),(1807,2,1756522046,'attendance','absenteereport_default','1',NULL),(1808,2,1756522046,'attendance','studentscanmark_default','0',NULL),(1809,2,1756522046,'attendance','automark_default','0',NULL),(1810,2,1756522046,'attendance','studentsearlyopentime','0',NULL),(1811,2,1756522046,'attendance','randompassword_default','0',NULL),(1812,2,1756522046,'attendance','includeqrcode_default','0',NULL),(1813,2,1756522046,'attendance','rotateqrcode_default','0',NULL),(1814,2,1756522047,'attendance','autoassignstatus','0',NULL),(1815,2,1756522047,'attendance','preventsharedip','0',NULL),(1816,2,1756522047,'attendance','preventsharediptime','',NULL),(1817,2,1756522047,'attendance','warningpercent','70',NULL),(1818,2,1756522047,'attendance','warnafter','5',NULL),(1819,2,1756522047,'attendance','maxwarn','1',NULL),(1820,2,1756522047,'attendance','emailuser','1',NULL),(1821,2,1756522048,'attendance','emailsubject','Advertencia de asistencia',NULL),(1822,2,1756522048,'attendance','emailcontent','Hola %userfirstname%,\r\nSu asistencia en %coursename% %attendancename% ha ca??do por debajo del %warningpercent% y actualmente es del %percent% - ??esperamos que est?? bien!\r\n\r\nPara aprovechar al m??ximo este curso, debe mejorar su asistencia, p??ngase en contacto si necesita m??s ayuda.',NULL),(1823,2,1756524650,'cohort','enrol_plugins_enabled','0','1'),(1824,2,1756524665,'cohort','enrol_plugins_enabled','1','0'),(1825,2,1756524875,NULL,'allowcohortthemes','1','0'),(1826,2,1756559714,'block_completion_progress','wrapafter','16',NULL),(1827,2,1756559714,'block_completion_progress','defaultlongbars','squeeze',NULL),(1828,2,1756559714,'block_completion_progress','coursenametoshow','shortname',NULL),(1829,2,1756559715,'block_completion_progress','completed_colour','#73A839',NULL),(1830,2,1756559715,'block_completion_progress','submittednotcomplete_colour','#FFCC00',NULL),(1831,2,1756559715,'block_completion_progress','notCompleted_colour','#C71C22',NULL),(1832,2,1756559715,'block_completion_progress','futureNotCompleted_colour','#025187',NULL),(1833,2,1756559716,'block_completion_progress','showinactive','0',NULL),(1834,2,1756559716,'block_completion_progress','showlastincourse','1',NULL),(1835,2,1756559716,'block_completion_progress','forceiconsinbar','1',NULL);
/*!40000 ALTER TABLE `mdl_config_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_config_plugins`
--

DROP TABLE IF EXISTS `mdl_config_plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_config_plugins` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `plugin` varchar(100) NOT NULL DEFAULT 'core',
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_confplug_plunam_uix` (`plugin`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2017 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Moodle modules and plugins configuration variables';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_config_plugins`
--

LOCK TABLES `mdl_config_plugins` WRITE;
/*!40000 ALTER TABLE `mdl_config_plugins` DISABLE KEYS */;
INSERT INTO `mdl_config_plugins` VALUES (1,'question','multichoice_sortorder','1'),(2,'question','truefalse_sortorder','2'),(3,'question','match_sortorder','3'),(4,'question','shortanswer_sortorder','4'),(5,'question','numerical_sortorder','5'),(6,'question','essay_sortorder','6'),(7,'core_competency','enabled','1'),(8,'moodlecourse','visible','1'),(9,'moodlecourse','downloadcontentsitedefault','0'),(10,'moodlecourse','participantsperpage','20'),(11,'moodlecourse','format','topics'),(12,'moodlecourse','maxsections','52'),(13,'moodlecourse','numsections','10'),(14,'moodlecourse','hiddensections','1'),(15,'moodlecourse','coursedisplay','0'),(16,'moodlecourse','courseenddateenabled','1'),(17,'moodlecourse','courseduration','31536000'),(18,'moodlecourse','lang',''),(19,'moodlecourse','newsitems','10'),(20,'moodlecourse','showgrades','1'),(21,'moodlecourse','showreports','0'),(22,'moodlecourse','showactivitydates','1'),(23,'moodlecourse','maxbytes','0'),(24,'moodlecourse','enablecompletion','1'),(25,'moodlecourse','showcompletionconditions','1'),(26,'moodlecourse','groupmode','0'),(27,'moodlecourse','groupmodeforce','0'),(28,'backup','loglifetime','30'),(29,'backup','backup_general_users','1'),(30,'backup','backup_general_users_locked',''),(31,'backup','backup_general_anonymize','0'),(32,'backup','backup_general_anonymize_locked',''),(33,'backup','backup_general_role_assignments','1'),(34,'backup','backup_general_role_assignments_locked',''),(35,'backup','backup_general_activities','1'),(36,'backup','backup_general_activities_locked',''),(37,'backup','backup_general_blocks','1'),(38,'backup','backup_general_blocks_locked',''),(39,'backup','backup_general_files','1'),(40,'backup','backup_general_files_locked',''),(41,'backup','backup_general_filters','1'),(42,'backup','backup_general_filters_locked',''),(43,'backup','backup_general_comments','1'),(44,'backup','backup_general_comments_locked',''),(45,'backup','backup_general_badges','1'),(46,'backup','backup_general_badges_locked',''),(47,'backup','backup_general_calendarevents','1'),(48,'backup','backup_general_calendarevents_locked',''),(49,'backup','backup_general_userscompletion','1'),(50,'backup','backup_general_userscompletion_locked',''),(51,'backup','backup_general_logs','0'),(52,'backup','backup_general_logs_locked',''),(53,'backup','backup_general_histories','0'),(54,'backup','backup_general_histories_locked',''),(55,'backup','backup_general_questionbank','1'),(56,'backup','backup_general_questionbank_locked',''),(57,'backup','backup_general_groups','1'),(58,'backup','backup_general_groups_locked',''),(59,'backup','backup_general_competencies','1'),(60,'backup','backup_general_competencies_locked',''),(61,'backup','backup_general_contentbankcontent','1'),(62,'backup','backup_general_contentbankcontent_locked',''),(63,'backup','backup_general_legacyfiles','1'),(64,'backup','backup_general_legacyfiles_locked',''),(65,'backup','import_general_maxresults','10'),(66,'backup','import_general_duplicate_admin_allowed','0'),(67,'backup','backup_import_permissions','0'),(68,'backup','backup_import_permissions_locked',''),(69,'backup','backup_import_activities','1'),(70,'backup','backup_import_activities_locked',''),(71,'backup','backup_import_blocks','1'),(72,'backup','backup_import_blocks_locked',''),(73,'backup','backup_import_filters','1'),(74,'backup','backup_import_filters_locked',''),(75,'backup','backup_import_calendarevents','1'),(76,'backup','backup_import_calendarevents_locked',''),(77,'backup','backup_import_questionbank','1'),(78,'backup','backup_import_questionbank_locked',''),(79,'backup','backup_import_groups','1'),(80,'backup','backup_import_groups_locked',''),(81,'backup','backup_import_competencies','1'),(82,'backup','backup_import_competencies_locked',''),(83,'backup','backup_import_contentbankcontent','1'),(84,'backup','backup_import_contentbankcontent_locked',''),(85,'backup','backup_import_legacyfiles','1'),(86,'backup','backup_import_legacyfiles_locked',''),(87,'backup','backup_auto_active','0'),(88,'backup','backup_auto_weekdays','0000000'),(89,'backup','backup_auto_hour','0'),(90,'backup','backup_auto_minute','0'),(91,'backup','backup_auto_storage','0'),(92,'backup','backup_auto_destination',''),(93,'backup','backup_auto_max_kept','1'),(94,'backup','backup_auto_delete_days','0'),(95,'backup','backup_auto_min_kept','0'),(96,'backup','backup_shortname','0'),(97,'backup','backup_auto_skip_hidden','1'),(98,'backup','backup_auto_skip_modif_days','30'),(99,'backup','backup_auto_skip_modif_prev','0'),(100,'backup','backup_auto_users','1'),(101,'backup','backup_auto_role_assignments','1'),(102,'backup','backup_auto_activities','1'),(103,'backup','backup_auto_blocks','1'),(104,'backup','backup_auto_files','1'),(105,'backup','backup_auto_filters','1'),(106,'backup','backup_auto_comments','1'),(107,'backup','backup_auto_badges','1'),(108,'backup','backup_auto_calendarevents','1'),(109,'backup','backup_auto_userscompletion','1'),(110,'backup','backup_auto_logs','0'),(111,'backup','backup_auto_histories','0'),(112,'backup','backup_auto_questionbank','1'),(113,'backup','backup_auto_groups','1'),(114,'backup','backup_auto_competencies','1'),(115,'backup','backup_auto_contentbankcontent','1'),(116,'backup','backup_auto_legacyfiles','1'),(117,'restore','restore_general_users','1'),(118,'restore','restore_general_users_locked',''),(119,'restore','restore_general_enrolments','1'),(120,'restore','restore_general_enrolments_locked',''),(121,'restore','restore_general_role_assignments','1'),(122,'restore','restore_general_role_assignments_locked',''),(123,'restore','restore_general_permissions','1'),(124,'restore','restore_general_permissions_locked',''),(125,'restore','restore_general_activities','1'),(126,'restore','restore_general_activities_locked',''),(127,'restore','restore_general_blocks','1'),(128,'restore','restore_general_blocks_locked',''),(129,'restore','restore_general_filters','1'),(130,'restore','restore_general_filters_locked',''),(131,'restore','restore_general_comments','1'),(132,'restore','restore_general_comments_locked',''),(133,'restore','restore_general_badges','1'),(134,'restore','restore_general_badges_locked',''),(135,'restore','restore_general_calendarevents','1'),(136,'restore','restore_general_calendarevents_locked',''),(137,'restore','restore_general_userscompletion','1'),(138,'restore','restore_general_userscompletion_locked',''),(139,'restore','restore_general_logs','1'),(140,'restore','restore_general_logs_locked',''),(141,'restore','restore_general_histories','1'),(142,'restore','restore_general_histories_locked',''),(143,'restore','restore_general_groups','1'),(144,'restore','restore_general_groups_locked',''),(145,'restore','restore_general_competencies','1'),(146,'restore','restore_general_competencies_locked',''),(147,'restore','restore_general_contentbankcontent','1'),(148,'restore','restore_general_contentbankcontent_locked',''),(149,'restore','restore_general_legacyfiles','1'),(150,'restore','restore_general_legacyfiles_locked',''),(151,'restore','restore_merge_overwrite_conf','0'),(152,'restore','restore_merge_overwrite_conf_locked',''),(153,'restore','restore_merge_course_fullname','1'),(154,'restore','restore_merge_course_fullname_locked',''),(155,'restore','restore_merge_course_shortname','1'),(156,'restore','restore_merge_course_shortname_locked',''),(157,'restore','restore_merge_course_startdate','1'),(158,'restore','restore_merge_course_startdate_locked',''),(159,'restore','restore_replace_overwrite_conf','0'),(160,'restore','restore_replace_overwrite_conf_locked',''),(161,'restore','restore_replace_course_fullname','1'),(162,'restore','restore_replace_course_fullname_locked',''),(163,'restore','restore_replace_course_shortname','1'),(164,'restore','restore_replace_course_shortname_locked',''),(165,'restore','restore_replace_course_startdate','1'),(166,'restore','restore_replace_course_startdate_locked',''),(167,'restore','restore_replace_keep_roles_and_enrolments','0'),(168,'restore','restore_replace_keep_roles_and_enrolments_locked',''),(169,'restore','restore_replace_keep_groups_and_groupings','0'),(170,'restore','restore_replace_keep_groups_and_groupings_locked',''),(171,'backup','backup_async_message_users','0'),(172,'backup','backup_async_message_subject','{operation} de Moodle se complet?? exitosamente'),(173,'backup','backup_async_message','Hola {user_firstname} {user_lastname}, <br/> ??Su {operation} (ID: {backupid}) ha sido completado exitosamente <br/><br/>Puede verla aqu??: <a href=\"{link}\">{link}</a>.'),(174,'analytics','modeinstruction',''),(175,'analytics','percentonline','0'),(176,'analytics','typeinstitution',''),(177,'analytics','levelinstitution',''),(178,'analytics','predictionsprocessor','\\mlbackend_php\\processor'),(179,'analytics','defaulttimesplittingsevaluation','\\core\\analytics\\time_splitting\\quarters_accum,\\core\\analytics\\time_splitting\\quarters,\\core\\analytics\\time_splitting\\single_range'),(180,'analytics','modeloutputdir',''),(181,'analytics','onlycli','1'),(182,'analytics','modeltimelimit','1200'),(183,'analytics','calclifetime','35'),(184,'cachestore_apcu','testperformance','0'),(185,'cachestore_memcached','testservers',''),(186,'cachestore_mongodb','testserver',''),(187,'cachestore_redis','test_server',''),(188,'cachestore_redis','test_password',''),(189,'cachestore_redis','test_ttl','0'),(190,'antivirus','notifyemail',''),(191,'antivirus','notifylevel','2'),(192,'antivirus','threshold','1200'),(193,'antivirus','enablequarantine','0'),(194,'antivirus','quarantinetime','2419200'),(195,'question_preview','behaviour','deferredfeedback'),(196,'question_preview','correctness','1'),(197,'question_preview','marks','2'),(198,'question_preview','markdp','2'),(199,'question_preview','feedback','1'),(200,'question_preview','generalfeedback','1'),(201,'question_preview','rightanswer','1'),(202,'question_preview','history','0'),(203,'tool_task','enablerunnow','1'),(204,'adminpresets','sensiblesettings','recaptchapublickey@@none, recaptchaprivatekey@@none, googlemapkey3@@none, secretphrase@@url, cronremotepassword@@none, smtpuser@@none, smtppass@none, proxypassword@@none, quizpassword@@quiz, allowedip@@none, blockedip@@none, dbpass@@logstore_database, messageinbound_hostpass@@none, bind_pw@@auth_cas, pass@@auth_db, bind_pw@@auth_ldap, dbpass@@enrol_database, bind_pw@@enrol_ldap, server_password@@search_solr, ssl_keypassword@@search_solr, alternateserver_password@@search_solr, alternatessl_keypassword@@search_solr, test_password@@cachestore_redis, password@@mlbackend_python, badges_badgesalt@@none, calendar_exportsalt@@none'),(205,'theme_boost','unaddableblocks','navigation,settings,course_list,section_links'),(206,'theme_boost','preset','default.scss'),(207,'theme_boost','presetfiles',''),(208,'theme_boost','backgroundimage',''),(209,'theme_boost','loginbackgroundimage',''),(210,'theme_boost','brandcolor',''),(211,'theme_boost','scsspre',''),(212,'theme_boost','scss',''),(213,'theme_classic','navbardark','0'),(214,'theme_classic','unaddableblocks',''),(215,'theme_classic','preset','default.scss'),(216,'theme_classic','presetfiles',''),(217,'theme_classic','backgroundimage',''),(218,'theme_classic','loginbackgroundimage',''),(219,'theme_classic','brandcolor',''),(220,'theme_classic','scsspre',''),(221,'theme_classic','scss',''),(222,'core_admin','logo',''),(223,'core_admin','logocompact',''),(224,'core_admin','coursecolor1','#81ecec'),(225,'core_admin','coursecolor2','#74b9ff'),(226,'core_admin','coursecolor3','#a29bfe'),(227,'core_admin','coursecolor4','#dfe6e9'),(228,'core_admin','coursecolor5','#00b894'),(229,'core_admin','coursecolor6','#0984e3'),(230,'core_admin','coursecolor7','#b2bec3'),(231,'core_admin','coursecolor8','#fdcb6e'),(232,'core_admin','coursecolor9','#fd79a8'),(233,'core_admin','coursecolor10','#6c5ce7'),(234,'core_competency','pushcourseratingstouserplans','1'),(235,'antivirus_clamav','version','2022041900'),(236,'availability_completion','version','2022041900'),(237,'availability_date','version','2022041900'),(238,'availability_grade','version','2022041900'),(239,'availability_group','version','2022041900'),(240,'availability_grouping','version','2022041900'),(241,'availability_profile','version','2022041900'),(242,'qtype_calculated','version','2022041900'),(243,'qtype_calculatedmulti','version','2022041900'),(244,'qtype_calculatedsimple','version','2022041900'),(245,'qtype_ddimageortext','version','2022041900'),(246,'qtype_ddmarker','version','2022041900'),(247,'qtype_ddwtos','version','2022041900'),(248,'qtype_description','version','2022041900'),(249,'qtype_essay','version','2022041900'),(250,'qtype_gapselect','version','2022041900'),(251,'qtype_match','version','2022041900'),(252,'qtype_missingtype','version','2022041900'),(253,'qtype_multianswer','version','2022041900'),(254,'qtype_multichoice','version','2022041900'),(255,'qtype_numerical','version','2022041900'),(256,'qtype_random','version','2022041900'),(257,'qtype_randomsamatch','version','2022041900'),(258,'qtype_shortanswer','version','2022041900'),(259,'qtype_truefalse','version','2022041900'),(260,'mod_assign','version','2022041902'),(261,'mod_assignment','version','2022041900'),(263,'mod_bigbluebuttonbn','version','2022041901'),(265,'mod_book','version','2022041900'),(266,'mod_chat','version','2022041900'),(267,'mod_choice','version','2022041900'),(268,'mod_data','version','2022041900'),(269,'mod_feedback','version','2022041900'),(271,'mod_folder','version','2022041900'),(273,'mod_forum','version','2022041901'),(274,'mod_glossary','version','2022041900'),(275,'mod_h5pactivity','version','2022041900'),(276,'mod_imscp','version','2022041900'),(278,'mod_label','version','2022041902'),(279,'mod_lesson','version','2022041900'),(280,'mod_lti','version','2022041900'),(282,'mod_lti','kid','cfc6dbf0afca5d9deb5f'),(283,'mod_page','version','2022041900'),(285,'mod_quiz','version','2022041900'),(286,'mod_resource','version','2022041900'),(287,'mod_scorm','version','2022041900'),(288,'mod_survey','version','2022041900'),(290,'mod_url','version','2022041900'),(292,'mod_wiki','version','2022041900'),(294,'mod_workshop','version','2022041900'),(295,'auth_cas','version','2022041900'),(297,'auth_db','version','2022041900'),(299,'auth_email','version','2022041900'),(300,'auth_ldap','version','2022041900'),(302,'auth_lti','version','2022041900'),(303,'auth_manual','version','2022041900'),(304,'auth_mnet','version','2022041900'),(306,'auth_nologin','version','2022041900'),(307,'auth_none','version','2022041900'),(308,'auth_oauth2','version','2022041900'),(309,'auth_shibboleth','version','2022041900'),(311,'auth_webservice','version','2022041900'),(312,'calendartype_gregorian','version','2022041900'),(313,'customfield_checkbox','version','2022041900'),(314,'customfield_date','version','2022041900'),(315,'customfield_select','version','2022041900'),(316,'customfield_text','version','2022041900'),(317,'customfield_textarea','version','2022041900'),(318,'enrol_category','version','2022041900'),(320,'enrol_cohort','version','2022041900'),(321,'enrol_database','version','2022041900'),(323,'enrol_fee','version','2022041900'),(324,'enrol_flatfile','version','2022041900'),(326,'enrol_flatfile','map_1','manager'),(327,'enrol_flatfile','map_2','coursecreator'),(328,'enrol_flatfile','map_3','editingteacher'),(329,'enrol_flatfile','map_4','teacher'),(330,'enrol_flatfile','map_5','student'),(331,'enrol_flatfile','map_6','guest'),(332,'enrol_flatfile','map_7','user'),(333,'enrol_flatfile','map_8','frontpage'),(334,'enrol_guest','version','2022041900'),(335,'enrol_imsenterprise','version','2022041900'),(337,'enrol_ldap','version','2022041900'),(339,'enrol_lti','version','2022041903'),(341,'enrol_lti','lti_13_kid','4a10dce9ee5189cc8469'),(342,'enrol_manual','version','2022041900'),(344,'enrol_meta','version','2022041900'),(346,'enrol_mnet','version','2022041900'),(347,'enrol_paypal','version','2022041900'),(348,'enrol_self','version','2022041900'),(350,'message_airnotifier','version','2022041900'),(352,'message','airnotifier_provider_enrol_flatfile_flatfile_enrolment_locked','0'),(353,'message','airnotifier_provider_enrol_imsenterprise_imsenterprise_enrolment_locked','0'),(354,'message','airnotifier_provider_enrol_manual_expiry_notification_locked','0'),(355,'message','airnotifier_provider_enrol_paypal_paypal_enrolment_locked','0'),(356,'message','airnotifier_provider_enrol_self_expiry_notification_locked','0'),(357,'message','airnotifier_provider_mod_assign_assign_notification_locked','0'),(358,'message','airnotifier_provider_mod_assignment_assignment_updates_locked','0'),(359,'message','airnotifier_provider_mod_bigbluebuttonbn_recording_ready_locked','0'),(360,'message','airnotifier_provider_mod_bigbluebuttonbn_instance_updated_locked','0'),(361,'message','airnotifier_provider_mod_feedback_submission_locked','0'),(362,'message','airnotifier_provider_mod_feedback_message_locked','0'),(363,'message','airnotifier_provider_mod_forum_posts_locked','0'),(364,'message','message_provider_mod_forum_posts_enabled','email,airnotifier'),(365,'message','airnotifier_provider_mod_forum_digests_locked','0'),(366,'message','airnotifier_provider_mod_lesson_graded_essay_locked','0'),(367,'message','message_provider_mod_lesson_graded_essay_enabled','email,airnotifier'),(368,'message','airnotifier_provider_mod_quiz_submission_locked','0'),(369,'message','airnotifier_provider_mod_quiz_confirmation_locked','0'),(370,'message','message_provider_mod_quiz_confirmation_enabled','email,airnotifier'),(371,'message','airnotifier_provider_mod_quiz_attempt_overdue_locked','0'),(372,'message','message_provider_mod_quiz_attempt_overdue_enabled','email,airnotifier'),(373,'message','airnotifier_provider_mod_quiz_attempt_grading_complete_locked','0'),(374,'message','message_provider_mod_quiz_attempt_grading_complete_enabled','email,airnotifier'),(375,'message','airnotifier_provider_moodle_newlogin_locked','0'),(376,'message','message_provider_moodle_newlogin_enabled','email,airnotifier'),(377,'message','airnotifier_provider_moodle_notices_locked','0'),(378,'message','airnotifier_provider_moodle_errors_locked','0'),(379,'message','airnotifier_provider_moodle_availableupdate_locked','0'),(380,'message','airnotifier_provider_moodle_instantmessage_locked','0'),(381,'message','airnotifier_provider_moodle_backup_locked','0'),(382,'message','airnotifier_provider_moodle_courserequested_locked','0'),(383,'message','airnotifier_provider_moodle_courserequestapproved_locked','0'),(384,'message','message_provider_moodle_courserequestapproved_enabled','email,airnotifier'),(385,'message','airnotifier_provider_moodle_courserequestrejected_locked','0'),(386,'message','message_provider_moodle_courserequestrejected_enabled','email,airnotifier'),(387,'message','airnotifier_provider_moodle_coursecompleted_locked','0'),(388,'message','message_provider_moodle_coursecompleted_enabled','email,airnotifier'),(389,'message','airnotifier_provider_moodle_coursecontentupdated_locked','0'),(390,'message','message_provider_moodle_coursecontentupdated_enabled','popup,email,airnotifier'),(391,'message','airnotifier_provider_moodle_badgerecipientnotice_locked','0'),(392,'message','message_provider_moodle_badgerecipientnotice_enabled','popup,email,airnotifier'),(393,'message','airnotifier_provider_moodle_badgecreatornotice_locked','0'),(394,'message','airnotifier_provider_moodle_competencyplancomment_locked','0'),(395,'message','airnotifier_provider_moodle_competencyusercompcomment_locked','0'),(396,'message','airnotifier_provider_moodle_insights_locked','0'),(397,'message','message_provider_moodle_insights_enabled','popup,email,airnotifier'),(398,'message','airnotifier_provider_moodle_messagecontactrequests_locked','0'),(399,'message','message_provider_moodle_messagecontactrequests_enabled','email,airnotifier'),(400,'message','airnotifier_provider_moodle_asyncbackupnotification_locked','0'),(401,'message','airnotifier_provider_moodle_gradenotifications_locked','0'),(402,'message','airnotifier_provider_moodle_infected_locked','0'),(403,'message','airnotifier_provider_moodle_reportbuilderschedule_locked','0'),(404,'message_email','version','2022041900'),(406,'message','email_provider_enrol_flatfile_flatfile_enrolment_locked','0'),(407,'message','message_provider_enrol_flatfile_flatfile_enrolment_enabled','email'),(408,'message','email_provider_enrol_imsenterprise_imsenterprise_enrolment_locked','0'),(409,'message','message_provider_enrol_imsenterprise_imsenterprise_enrolment_enabled','email'),(410,'message','email_provider_enrol_manual_expiry_notification_locked','0'),(411,'message','message_provider_enrol_manual_expiry_notification_enabled','email'),(412,'message','email_provider_enrol_paypal_paypal_enrolment_locked','0'),(413,'message','message_provider_enrol_paypal_paypal_enrolment_enabled','email'),(414,'message','email_provider_enrol_self_expiry_notification_locked','0'),(415,'message','message_provider_enrol_self_expiry_notification_enabled','email'),(416,'message','email_provider_mod_assign_assign_notification_locked','0'),(417,'message','message_provider_mod_assign_assign_notification_enabled','email'),(418,'message','email_provider_mod_assignment_assignment_updates_locked','0'),(419,'message','message_provider_mod_assignment_assignment_updates_enabled','email'),(420,'message','email_provider_mod_bigbluebuttonbn_recording_ready_locked','0'),(421,'message','message_provider_mod_bigbluebuttonbn_recording_ready_enabled','email'),(422,'message','email_provider_mod_bigbluebuttonbn_instance_updated_locked','0'),(423,'message','message_provider_mod_bigbluebuttonbn_instance_updated_enabled','email'),(424,'message','email_provider_mod_feedback_submission_locked','0'),(425,'message','message_provider_mod_feedback_submission_enabled','email'),(426,'message','email_provider_mod_feedback_message_locked','0'),(427,'message','message_provider_mod_feedback_message_enabled','email'),(428,'message','email_provider_mod_forum_posts_locked','0'),(429,'message','email_provider_mod_forum_digests_locked','0'),(430,'message','message_provider_mod_forum_digests_enabled','email'),(431,'message','email_provider_mod_lesson_graded_essay_locked','0'),(432,'message','email_provider_mod_quiz_submission_locked','0'),(433,'message','message_provider_mod_quiz_submission_enabled','email'),(434,'message','email_provider_mod_quiz_confirmation_locked','0'),(435,'message','email_provider_mod_quiz_attempt_overdue_locked','0'),(436,'message','email_provider_mod_quiz_attempt_grading_complete_locked','0'),(437,'message','email_provider_moodle_newlogin_locked','0'),(438,'message','email_provider_moodle_notices_locked','0'),(439,'message','message_provider_moodle_notices_enabled','email'),(440,'message','email_provider_moodle_errors_locked','0'),(441,'message','message_provider_moodle_errors_enabled','email'),(442,'message','email_provider_moodle_availableupdate_locked','0'),(443,'message','message_provider_moodle_availableupdate_enabled','email'),(444,'message','email_provider_moodle_instantmessage_locked','0'),(445,'message','message_provider_moodle_instantmessage_enabled','popup,email'),(446,'message','email_provider_moodle_backup_locked','0'),(447,'message','message_provider_moodle_backup_enabled','email'),(448,'message','email_provider_moodle_courserequested_locked','0'),(449,'message','message_provider_moodle_courserequested_enabled','email'),(450,'message','email_provider_moodle_courserequestapproved_locked','0'),(451,'message','email_provider_moodle_courserequestrejected_locked','0'),(452,'message','email_provider_moodle_coursecompleted_locked','0'),(453,'message','email_provider_moodle_coursecontentupdated_locked','0'),(454,'message','email_provider_moodle_badgerecipientnotice_locked','0'),(455,'message','email_provider_moodle_badgecreatornotice_locked','0'),(456,'message','message_provider_moodle_badgecreatornotice_enabled','email'),(457,'message','email_provider_moodle_competencyplancomment_locked','0'),(458,'message','message_provider_moodle_competencyplancomment_enabled','email'),(459,'message','email_provider_moodle_competencyusercompcomment_locked','0'),(460,'message','message_provider_moodle_competencyusercompcomment_enabled','email'),(461,'message','email_provider_moodle_insights_locked','0'),(462,'message','email_provider_moodle_messagecontactrequests_locked','0'),(463,'message','email_provider_moodle_asyncbackupnotification_locked','0'),(464,'message','message_provider_moodle_asyncbackupnotification_enabled','popup,email'),(465,'message','email_provider_moodle_gradenotifications_locked','0'),(466,'message','message_provider_moodle_gradenotifications_enabled','popup,email'),(467,'message','email_provider_moodle_infected_locked','0'),(468,'message','message_provider_moodle_infected_enabled','email'),(469,'message','email_provider_moodle_reportbuilderschedule_locked','1'),(470,'message','message_provider_moodle_reportbuilderschedule_enabled','email'),(471,'message_popup','version','2022041900'),(473,'message','popup_provider_enrol_flatfile_flatfile_enrolment_locked','0'),(474,'message','popup_provider_enrol_imsenterprise_imsenterprise_enrolment_locked','0'),(475,'message','popup_provider_enrol_manual_expiry_notification_locked','0'),(476,'message','popup_provider_enrol_paypal_paypal_enrolment_locked','0'),(477,'message','popup_provider_enrol_self_expiry_notification_locked','0'),(478,'message','popup_provider_mod_assign_assign_notification_locked','0'),(479,'message','popup_provider_mod_assignment_assignment_updates_locked','0'),(480,'message','popup_provider_mod_bigbluebuttonbn_recording_ready_locked','0'),(481,'message','popup_provider_mod_bigbluebuttonbn_instance_updated_locked','0'),(482,'message','popup_provider_mod_feedback_submission_locked','0'),(483,'message','popup_provider_mod_feedback_message_locked','0'),(484,'message','popup_provider_mod_forum_posts_locked','0'),(485,'message','popup_provider_mod_forum_digests_locked','0'),(486,'message','popup_provider_mod_lesson_graded_essay_locked','0'),(487,'message','popup_provider_mod_quiz_submission_locked','0'),(488,'message','popup_provider_mod_quiz_confirmation_locked','0'),(489,'message','popup_provider_mod_quiz_attempt_overdue_locked','0'),(490,'message','popup_provider_mod_quiz_attempt_grading_complete_locked','0'),(491,'message','popup_provider_moodle_newlogin_locked','0'),(492,'message','popup_provider_moodle_notices_locked','0'),(493,'message','popup_provider_moodle_errors_locked','0'),(494,'message','popup_provider_moodle_availableupdate_locked','0'),(495,'message','popup_provider_moodle_instantmessage_locked','0'),(496,'message','popup_provider_moodle_backup_locked','0'),(497,'message','popup_provider_moodle_courserequested_locked','0'),(498,'message','popup_provider_moodle_courserequestapproved_locked','0'),(499,'message','popup_provider_moodle_courserequestrejected_locked','0'),(500,'message','popup_provider_moodle_coursecompleted_locked','0'),(501,'message','popup_provider_moodle_coursecontentupdated_locked','0'),(502,'message','popup_provider_moodle_badgerecipientnotice_locked','0'),(503,'message','popup_provider_moodle_badgecreatornotice_locked','0'),(504,'message','popup_provider_moodle_competencyplancomment_locked','0'),(505,'message','popup_provider_moodle_competencyusercompcomment_locked','0'),(506,'message','popup_provider_moodle_insights_locked','0'),(507,'message','popup_provider_moodle_messagecontactrequests_locked','0'),(508,'message','popup_provider_moodle_asyncbackupnotification_locked','0'),(509,'message','popup_provider_moodle_gradenotifications_locked','0'),(510,'message','popup_provider_moodle_infected_locked','0'),(511,'message','popup_provider_moodle_reportbuilderschedule_locked','0'),(512,'block_accessreview','version','2022041900'),(513,'block_activity_modules','version','2022041900'),(514,'block_activity_results','version','2022041900'),(515,'block_admin_bookmarks','version','2022041900'),(516,'block_badges','version','2022041900'),(517,'block_blog_menu','version','2022041900'),(518,'block_blog_recent','version','2022041900'),(519,'block_blog_tags','version','2022041900'),(520,'block_calendar_month','version','2022041901'),(521,'block_calendar_upcoming','version','2022041900'),(522,'block_comments','version','2022041900'),(523,'block_completionstatus','version','2022041900'),(524,'block_course_list','version','2022041900'),(525,'block_course_summary','version','2022041900'),(527,'block_feedback','version','2022041900'),(529,'block_globalsearch','version','2022041900'),(530,'block_glossary_random','version','2022041900'),(531,'block_html','version','2022041900'),(532,'block_login','version','2022041900'),(533,'block_lp','version','2022041900'),(534,'block_mentees','version','2022041900'),(535,'block_mnet_hosts','version','2022041900'),(536,'block_myoverview','version','2022041901'),(537,'block_myprofile','version','2022041900'),(538,'block_navigation','version','2022041900'),(539,'block_news_items','version','2022041900'),(540,'block_online_users','version','2022041900'),(541,'block_private_files','version','2022041900'),(542,'block_recent_activity','version','2022041900'),(543,'block_recentlyaccessedcourses','version','2022041900'),(545,'block_recentlyaccesseditems','version','2022041901'),(546,'block_rss_client','version','2022041900'),(548,'block_search_forums','version','2022041900'),(549,'block_section_links','version','2022041900'),(550,'block_selfcompletion','version','2022041900'),(552,'block_settings','version','2022041900'),(553,'block_site_main_menu','version','2022041900'),(554,'block_social_activities','version','2022041900'),(555,'block_starredcourses','version','2022041900'),(556,'block_tag_flickr','version','2022041900'),(557,'block_tag_youtube','version','2022041900'),(559,'block_tags','version','2022041900'),(560,'block_timeline','version','2022041901'),(562,'media_html5audio','version','2022041900'),(563,'media_html5video','version','2022041900'),(564,'media_videojs','version','2022041900'),(565,'media_vimeo','version','2022041900'),(566,'media_youtube','version','2022041900'),(567,'filter_activitynames','version','2022041900'),(569,'filter_algebra','version','2022041900'),(570,'filter_data','version','2022041900'),(572,'filter_displayh5p','version','2022041900'),(574,'filter_emailprotect','version','2022041900'),(575,'filter_emoticon','version','2022041900'),(577,'filter_glossary','version','2022041900'),(579,'filter_mathjaxloader','version','2022041900'),(581,'filter_mediaplugin','version','2022041900'),(583,'filter_multilang','version','2022041900'),(584,'filter_tex','version','2022041900'),(586,'filter_tidy','version','2022041900'),(587,'filter_urltolink','version','2022041900'),(589,'editor_atto','version','2022041900'),(591,'editor_textarea','version','2022041900'),(592,'editor_tinymce','version','2022041900'),(593,'format_singleactivity','version','2022041900'),(594,'format_social','version','2022041900'),(595,'format_topics','version','2022041901'),(596,'format_weeks','version','2022041901'),(597,'dataformat_csv','version','2022041900'),(598,'dataformat_excel','version','2022041900'),(599,'dataformat_html','version','2022041900'),(600,'dataformat_json','version','2022041900'),(601,'dataformat_ods','version','2022041900'),(602,'dataformat_pdf','version','2022041900'),(603,'profilefield_checkbox','version','2022041900'),(604,'profilefield_datetime','version','2022041900'),(605,'profilefield_menu','version','2022041900'),(606,'profilefield_social','version','2022041900'),(607,'profilefield_text','version','2022041900'),(608,'profilefield_textarea','version','2022041900'),(609,'report_backups','version','2022041900'),(610,'report_competency','version','2022041900'),(611,'report_completion','version','2022041900'),(613,'report_configlog','version','2022041900'),(614,'report_courseoverview','version','2022041900'),(615,'report_eventlist','version','2022041900'),(616,'report_infectedfiles','version','2022041900'),(617,'report_insights','version','2022041900'),(618,'report_log','version','2022041900'),(620,'report_loglive','version','2022041900'),(621,'report_outline','version','2022041900'),(623,'report_participation','version','2022041900'),(625,'report_performance','version','2022041900'),(626,'report_progress','version','2022041900'),(628,'report_questioninstances','version','2022041900'),(629,'report_security','version','2022041900'),(630,'report_stats','version','2022041900'),(632,'report_status','version','2022041900'),(633,'report_usersessions','version','2022041900'),(634,'gradeexport_ods','version','2022041900'),(635,'gradeexport_txt','version','2022041900'),(636,'gradeexport_xls','version','2022041900'),(637,'gradeexport_xml','version','2022041900'),(638,'gradeimport_csv','version','2022041900'),(639,'gradeimport_direct','version','2022041900'),(640,'gradeimport_xml','version','2022041900'),(641,'gradereport_grader','version','2022041900'),(642,'gradereport_history','version','2022041900'),(643,'gradereport_outcomes','version','2022041900'),(644,'gradereport_overview','version','2022041900'),(645,'gradereport_singleview','version','2022041900'),(646,'gradereport_user','version','2022041900'),(647,'gradingform_guide','version','2022041900'),(648,'gradingform_rubric','version','2022041900'),(649,'mlbackend_php','version','2022041900'),(650,'mlbackend_python','version','2022041900'),(651,'mnetservice_enrol','version','2022041900'),(652,'webservice_rest','version','2022041900'),(653,'webservice_soap','version','2022041900'),(654,'webservice_xmlrpc','version','2022041900'),(655,'repository_areafiles','version','2022041900'),(657,'areafiles','enablecourseinstances','0'),(658,'areafiles','enableuserinstances','0'),(659,'repository_contentbank','version','2022041900'),(661,'contentbank','enablecourseinstances','0'),(662,'contentbank','enableuserinstances','0'),(663,'repository_coursefiles','version','2022041900'),(664,'repository_dropbox','version','2022041900'),(665,'repository_equella','version','2022041900'),(666,'repository_filesystem','version','2022041900'),(667,'repository_flickr','version','2022041900'),(668,'repository_flickr_public','version','2022041900'),(669,'repository_googledocs','version','2022041900'),(670,'repository_local','version','2022041900'),(672,'local','enablecourseinstances','0'),(673,'local','enableuserinstances','0'),(674,'repository_merlot','version','2022041900'),(675,'repository_nextcloud','version','2022041900'),(676,'repository_onedrive','version','2022041900'),(677,'repository_recent','version','2022041900'),(679,'recent','enablecourseinstances','0'),(680,'recent','enableuserinstances','0'),(681,'repository_s3','version','2022041900'),(682,'repository_upload','version','2022041900'),(684,'upload','enablecourseinstances','0'),(685,'upload','enableuserinstances','0'),(686,'repository_url','version','2022041900'),(688,'url','enablecourseinstances','0'),(689,'url','enableuserinstances','0'),(690,'repository_user','version','2022041900'),(692,'user','enablecourseinstances','0'),(693,'user','enableuserinstances','0'),(694,'repository_webdav','version','2022041900'),(695,'repository_wikimedia','version','2022041900'),(697,'wikimedia','enablecourseinstances','0'),(698,'wikimedia','enableuserinstances','0'),(699,'repository_youtube','version','2022041900'),(701,'portfolio_download','version','2022041900'),(702,'portfolio_flickr','version','2022041900'),(703,'portfolio_googledocs','version','2022041900'),(704,'portfolio_mahara','version','2022041900'),(705,'search_simpledb','version','2022041900'),(707,'search_solr','version','2022041900'),(708,'qbank_bulkmove','version','2022041900'),(709,'qbank_columnsortorder','version','2022041900'),(710,'qbank_comment','version','2022041900'),(711,'qbank_customfields','version','2022041900'),(712,'qbank_deletequestion','version','2022041900'),(713,'qbank_editquestion','version','2022041900'),(714,'qbank_exportquestions','version','2022041900'),(715,'qbank_exporttoxml','version','2022041900'),(716,'qbank_history','version','2022041900'),(717,'qbank_importquestions','version','2022041900'),(718,'qbank_managecategories','version','2022041900'),(719,'qbank_previewquestion','version','2022041900'),(720,'qbank_statistics','version','2022041900'),(721,'qbank_tagquestion','version','2022041900'),(722,'qbank_usage','version','2022041900'),(723,'qbank_viewcreator','version','2022041900'),(724,'qbank_viewquestionname','version','2022041900'),(725,'qbank_viewquestiontext','version','2022041900'),(726,'qbank_viewquestiontype','version','2022041900'),(727,'qbehaviour_adaptive','version','2022041900'),(728,'qbehaviour_adaptivenopenalty','version','2022041900'),(729,'qbehaviour_deferredcbm','version','2022041900'),(730,'qbehaviour_deferredfeedback','version','2022041900'),(731,'qbehaviour_immediatecbm','version','2022041900'),(732,'qbehaviour_immediatefeedback','version','2022041900'),(733,'qbehaviour_informationitem','version','2022041900'),(734,'qbehaviour_interactive','version','2022041900'),(735,'qbehaviour_interactivecountback','version','2022041900'),(736,'qbehaviour_manualgraded','version','2022041900'),(738,'question','disabledbehaviours','manualgraded'),(739,'qbehaviour_missing','version','2022041900'),(740,'qformat_aiken','version','2022041900'),(741,'qformat_blackboard_six','version','2022041900'),(742,'qformat_gift','version','2022041900'),(743,'qformat_missingword','version','2022041900'),(744,'qformat_multianswer','version','2022041900'),(745,'qformat_xhtml','version','2022041900'),(746,'qformat_xml','version','2022041900'),(747,'tool_admin_presets','version','2022041900'),(748,'tool_analytics','version','2022041900'),(749,'tool_availabilityconditions','version','2022041900'),(750,'tool_behat','version','2022041901'),(751,'tool_brickfield','version','2022041900'),(753,'tool_capability','version','2022041900'),(754,'tool_cohortroles','version','2022041901'),(755,'tool_componentlibrary','version','2022041900'),(756,'tool_customlang','version','2022041900'),(758,'tool_dataprivacy','version','2022041900'),(759,'message','airnotifier_provider_tool_dataprivacy_contactdataprotectionofficer_locked','0'),(760,'message','email_provider_tool_dataprivacy_contactdataprotectionofficer_locked','0'),(761,'message','popup_provider_tool_dataprivacy_contactdataprotectionofficer_locked','0'),(762,'message','message_provider_tool_dataprivacy_contactdataprotectionofficer_enabled','email,popup'),(763,'message','airnotifier_provider_tool_dataprivacy_datarequestprocessingresults_locked','0'),(764,'message','email_provider_tool_dataprivacy_datarequestprocessingresults_locked','0'),(765,'message','popup_provider_tool_dataprivacy_datarequestprocessingresults_locked','0'),(766,'message','message_provider_tool_dataprivacy_datarequestprocessingresults_enabled','email,popup'),(767,'message','airnotifier_provider_tool_dataprivacy_notifyexceptions_locked','0'),(768,'message','email_provider_tool_dataprivacy_notifyexceptions_locked','0'),(769,'message','popup_provider_tool_dataprivacy_notifyexceptions_locked','0'),(770,'message','message_provider_tool_dataprivacy_notifyexceptions_enabled','email'),(771,'tool_dbtransfer','version','2022041900'),(772,'tool_filetypes','version','2022041900'),(773,'tool_generator','version','2022041900'),(774,'tool_httpsreplace','version','2022041900'),(775,'tool_innodb','version','2022041900'),(776,'tool_installaddon','version','2022041900'),(777,'tool_langimport','version','2022041900'),(778,'tool_licensemanager','version','2022041900'),(779,'tool_log','version','2022041900'),(781,'tool_log','enabled_stores','logstore_standard'),(782,'tool_lp','version','2022041900'),(783,'tool_lpimportcsv','version','2022041900'),(784,'tool_lpmigrate','version','2022041900'),(785,'tool_messageinbound','version','2022041900'),(786,'message','airnotifier_provider_tool_messageinbound_invalidrecipienthandler_locked','0'),(787,'message','email_provider_tool_messageinbound_invalidrecipienthandler_locked','0'),(788,'message','popup_provider_tool_messageinbound_invalidrecipienthandler_locked','0'),(789,'message','message_provider_tool_messageinbound_invalidrecipienthandler_enabled','email'),(790,'message','airnotifier_provider_tool_messageinbound_messageprocessingerror_locked','0'),(791,'message','email_provider_tool_messageinbound_messageprocessingerror_locked','0'),(792,'message','popup_provider_tool_messageinbound_messageprocessingerror_locked','0'),(793,'message','message_provider_tool_messageinbound_messageprocessingerror_enabled','email'),(794,'message','airnotifier_provider_tool_messageinbound_messageprocessingsuccess_locked','0'),(795,'message','email_provider_tool_messageinbound_messageprocessingsuccess_locked','0'),(796,'message','popup_provider_tool_messageinbound_messageprocessingsuccess_locked','0'),(797,'message','message_provider_tool_messageinbound_messageprocessingsuccess_enabled','email'),(798,'tool_mobile','version','2022041900'),(799,'tool_monitor','version','2022041900'),(800,'message','airnotifier_provider_tool_monitor_notification_locked','0'),(801,'message','email_provider_tool_monitor_notification_locked','0'),(802,'message','popup_provider_tool_monitor_notification_locked','0'),(803,'message','message_provider_tool_monitor_notification_enabled','email'),(804,'tool_moodlenet','version','2022041900'),(806,'tool_multilangupgrade','version','2022041900'),(807,'tool_oauth2','version','2022041900'),(808,'tool_phpunit','version','2022041900'),(809,'tool_policy','version','2022041900'),(810,'tool_profiling','version','2022041900'),(811,'tool_recyclebin','version','2022041900'),(812,'tool_replace','version','2022041900'),(813,'tool_spamcleaner','version','2022041900'),(814,'tool_task','version','2022041900'),(815,'tool_templatelibrary','version','2022041900'),(816,'tool_unsuproles','version','2022041900'),(818,'tool_uploadcourse','version','2022041900'),(819,'tool_uploaduser','version','2022041900'),(820,'tool_usertours','version','2022041900'),(822,'tool_xmldb','version','2022041900'),(823,'cachestore_apcu','version','2022041900'),(824,'cachestore_file','version','2022041900'),(825,'cachestore_memcached','version','2022041900'),(826,'cachestore_mongodb','version','2022041900'),(827,'cachestore_redis','version','2022041900'),(828,'cachestore_session','version','2022041900'),(829,'cachestore_static','version','2022041900'),(830,'cachelock_file','version','2022041900'),(831,'fileconverter_googledrive','version','2022041900'),(832,'fileconverter_unoconv','version','2022041900'),(834,'contenttype_h5p','version','2022041900'),(835,'theme_boost','version','2022041900'),(836,'theme_classic','version','2022041900'),(837,'h5plib_v124','version','2022041900'),(838,'paygw_paypal','version','2022041900'),(840,'assignsubmission_comments','version','2022041900'),(842,'assignsubmission_file','sortorder','1'),(843,'assignsubmission_comments','sortorder','2'),(844,'assignsubmission_onlinetext','sortorder','0'),(845,'assignsubmission_file','version','2022041900'),(846,'assignsubmission_onlinetext','version','2022041900'),(848,'assignfeedback_comments','version','2022041900'),(850,'assignfeedback_comments','sortorder','0'),(851,'assignfeedback_editpdf','sortorder','1'),(852,'assignfeedback_file','sortorder','3'),(853,'assignfeedback_offline','sortorder','2'),(854,'assignfeedback_editpdf','version','2022041902'),(856,'assignfeedback_file','version','2022041900'),(858,'assignfeedback_offline','version','2022041900'),(859,'assignment_offline','version','2022041900'),(860,'assignment_online','version','2022041900'),(861,'assignment_upload','version','2022041900'),(862,'assignment_uploadsingle','version','2022041900'),(863,'booktool_exportimscp','version','2022041900'),(864,'booktool_importhtml','version','2022041900'),(865,'booktool_print','version','2022041900'),(866,'datafield_checkbox','version','2022041900'),(867,'datafield_date','version','2022041900'),(868,'datafield_file','version','2022041900'),(869,'datafield_latlong','version','2022041900'),(870,'datafield_menu','version','2022041900'),(871,'datafield_multimenu','version','2022041900'),(872,'datafield_number','version','2022041900'),(873,'datafield_picture','version','2022041900'),(874,'datafield_radiobutton','version','2022041900'),(875,'datafield_text','version','2022041900'),(876,'datafield_textarea','version','2022041900'),(877,'datafield_url','version','2022041900'),(878,'datapreset_imagegallery','version','2022041900'),(879,'forumreport_summary','version','2022041900'),(880,'ltiservice_basicoutcomes','version','2022041900'),(881,'ltiservice_gradebookservices','version','2022041900'),(882,'ltiservice_memberships','version','2022041900'),(883,'ltiservice_profile','version','2022041900'),(884,'ltiservice_toolproxy','version','2022041900'),(885,'ltiservice_toolsettings','version','2022041900'),(886,'quiz_grading','version','2022041900'),(888,'quiz_overview','version','2022041900'),(890,'quiz_responses','version','2022041900'),(892,'quiz_statistics','version','2022041901'),(894,'quizaccess_delaybetweenattempts','version','2022041900'),(895,'quizaccess_ipaddress','version','2022041900'),(896,'quizaccess_numattempts','version','2022041900'),(897,'quizaccess_offlineattempts','version','2022041900'),(898,'quizaccess_openclosedate','version','2022041900'),(899,'quizaccess_password','version','2022041900'),(900,'quizaccess_seb','version','2022041901'),(902,'quizaccess_securewindow','version','2022041900'),(903,'quizaccess_timelimit','version','2022041900'),(904,'scormreport_basic','version','2022041900'),(905,'scormreport_graphs','version','2022041900'),(906,'scormreport_interactions','version','2022041900'),(907,'scormreport_objectives','version','2022041900'),(908,'workshopform_accumulative','version','2022041900'),(910,'workshopform_comments','version','2022041900'),(912,'workshopform_numerrors','version','2022041900'),(914,'workshopform_rubric','version','2022041900'),(916,'workshopallocation_manual','version','2022041900'),(917,'workshopallocation_random','version','2022041900'),(918,'workshopallocation_scheduled','version','2022041900'),(919,'workshopeval_best','version','2022041900'),(920,'atto_accessibilitychecker','version','2022041900'),(921,'atto_accessibilityhelper','version','2022041900'),(922,'atto_align','version','2022041900'),(923,'atto_backcolor','version','2022041900'),(924,'atto_bold','version','2022041900'),(925,'atto_charmap','version','2022041900'),(926,'atto_clear','version','2022041900'),(927,'atto_collapse','version','2022041900'),(928,'atto_emojipicker','version','2022041900'),(929,'atto_emoticon','version','2022041900'),(930,'atto_equation','version','2022041901'),(931,'atto_fontcolor','version','2022041900'),(932,'atto_h5p','version','2022041900'),(933,'atto_html','version','2022041900'),(934,'atto_image','version','2022041900'),(935,'atto_indent','version','2022041900'),(936,'atto_italic','version','2022041900'),(937,'atto_link','version','2022041900'),(938,'atto_managefiles','version','2022041900'),(939,'atto_media','version','2022041900'),(940,'atto_noautolink','version','2022041900'),(941,'atto_orderedlist','version','2022041900'),(942,'atto_recordrtc','version','2022041900'),(943,'atto_rtl','version','2022041900'),(944,'atto_strike','version','2022041900'),(945,'atto_subscript','version','2022041900'),(946,'atto_superscript','version','2022041900'),(947,'atto_table','version','2022041900'),(948,'atto_title','version','2022041900'),(949,'atto_underline','version','2022041900'),(950,'atto_undo','version','2022041900'),(951,'atto_unorderedlist','version','2022041900'),(952,'tinymce_ctrlhelp','version','2022041900'),(953,'tinymce_managefiles','version','2022041900'),(954,'tinymce_moodleemoticon','version','2022041900'),(955,'tinymce_moodleimage','version','2022041900'),(956,'tinymce_moodlemedia','version','2022041900'),(957,'tinymce_moodlenolink','version','2022041900'),(958,'tinymce_pdw','version','2022041900'),(959,'tinymce_spellchecker','version','2022041900'),(961,'tinymce_wrap','version','2022041900'),(962,'logstore_database','version','2022041900'),(963,'logstore_legacy','version','2022041900'),(964,'logstore_standard','version','2022041900'),(965,'tool_moodlenet','enablemoodlenet','1'),(966,'tool_log','exportlog','1'),(967,'tool_dataprivacy','contactdataprotectionofficer','0'),(968,'tool_dataprivacy','automaticdataexportapproval','0'),(969,'tool_dataprivacy','automaticdatadeletionapproval','0'),(970,'tool_dataprivacy','automaticdeletionrequests','1'),(971,'tool_dataprivacy','privacyrequestexpiry','604800'),(972,'tool_dataprivacy','requireallenddatesforuserdeletion','1'),(973,'tool_dataprivacy','showdataretentionsummary','1'),(974,'analytics','logstore','logstore_standard'),(975,'auth_manual','expiration','0'),(976,'auth_manual','expirationtime','30'),(977,'auth_manual','expiration_warning','0'),(978,'auth_manual','field_lock_firstname','unlocked'),(979,'auth_manual','field_lock_lastname','unlocked'),(980,'auth_manual','field_lock_email','unlocked'),(981,'auth_manual','field_lock_city','unlocked'),(982,'auth_manual','field_lock_country','unlocked'),(983,'auth_manual','field_lock_lang','unlocked'),(984,'auth_manual','field_lock_description','unlocked'),(985,'auth_manual','field_lock_idnumber','unlocked'),(986,'auth_manual','field_lock_institution','unlocked'),(987,'auth_manual','field_lock_department','unlocked'),(988,'auth_manual','field_lock_phone1','unlocked'),(989,'auth_manual','field_lock_phone2','unlocked'),(990,'auth_manual','field_lock_address','unlocked'),(991,'auth_manual','field_lock_firstnamephonetic','unlocked'),(992,'auth_manual','field_lock_lastnamephonetic','unlocked'),(993,'auth_manual','field_lock_middlename','unlocked'),(994,'auth_manual','field_lock_alternatename','unlocked'),(995,'auth_email','recaptcha','0'),(996,'auth_email','field_lock_firstname','unlocked'),(997,'auth_email','field_lock_lastname','unlocked'),(998,'auth_email','field_lock_email','unlocked'),(999,'auth_email','field_lock_city','unlocked'),(1000,'auth_email','field_lock_country','unlocked'),(1001,'auth_email','field_lock_lang','unlocked'),(1002,'auth_email','field_lock_description','unlocked'),(1003,'auth_email','field_lock_idnumber','unlocked'),(1004,'auth_email','field_lock_institution','unlocked'),(1005,'auth_email','field_lock_department','unlocked'),(1006,'auth_email','field_lock_phone1','unlocked'),(1007,'auth_email','field_lock_phone2','unlocked'),(1008,'auth_email','field_lock_address','unlocked'),(1009,'auth_email','field_lock_firstnamephonetic','unlocked'),(1010,'auth_email','field_lock_lastnamephonetic','unlocked'),(1011,'auth_email','field_lock_middlename','unlocked'),(1012,'auth_email','field_lock_alternatename','unlocked'),(1013,'auth_mnet','rpc_negotiation_timeout','30'),(1014,'auth_oauth2','field_lock_firstname','unlocked'),(1015,'auth_oauth2','field_lock_lastname','unlocked'),(1016,'auth_oauth2','field_lock_email','unlocked'),(1017,'auth_oauth2','field_lock_city','unlocked'),(1018,'auth_oauth2','field_lock_country','unlocked'),(1019,'auth_oauth2','field_lock_lang','unlocked'),(1020,'auth_oauth2','field_lock_description','unlocked'),(1021,'auth_oauth2','field_lock_idnumber','unlocked'),(1022,'auth_oauth2','field_lock_institution','unlocked'),(1023,'auth_oauth2','field_lock_department','unlocked'),(1024,'auth_oauth2','field_lock_phone1','unlocked'),(1025,'auth_oauth2','field_lock_phone2','unlocked'),(1026,'auth_oauth2','field_lock_address','unlocked'),(1027,'auth_oauth2','field_lock_firstnamephonetic','unlocked'),(1028,'auth_oauth2','field_lock_lastnamephonetic','unlocked'),(1029,'auth_oauth2','field_lock_middlename','unlocked'),(1030,'auth_oauth2','field_lock_alternatename','unlocked'),(1031,'auth_shibboleth','user_attribute',''),(1032,'auth_shibboleth','convert_data',''),(1033,'auth_shibboleth','alt_login','off'),(1034,'auth_shibboleth','organization_selection','urn:mace:organization1:providerID, Example Organization 1\n        https://another.idp-id.com/shibboleth, Other Example Organization, /Shibboleth.sso/DS/SWITCHaai\n        urn:mace:organization2:providerID, Example Organization 2, /Shibboleth.sso/WAYF/SWITCHaai'),(1035,'auth_shibboleth','logout_handler',''),(1036,'auth_shibboleth','logout_return_url',''),(1037,'auth_shibboleth','login_name','Shibboleth Login'),(1038,'auth_shibboleth','auth_logo',''),(1039,'auth_shibboleth','auth_instructions','Utilice el <a href=\"http://localhost/moodle/auth/shibboleth/index.php\">inicio de sesi??n de Shibboleth</a> para obtener acceso a trav??s de Shibboleth, si su instituci??n lo admite. De lo contrario, utilice el formulario de inicio de sesi??n normal que se muestra aqu??.'),(1040,'auth_shibboleth','changepasswordurl',''),(1041,'auth_shibboleth','field_map_firstname',''),(1042,'auth_shibboleth','field_updatelocal_firstname','oncreate'),(1043,'auth_shibboleth','field_lock_firstname','unlocked'),(1044,'auth_shibboleth','field_map_lastname',''),(1045,'auth_shibboleth','field_updatelocal_lastname','oncreate'),(1046,'auth_shibboleth','field_lock_lastname','unlocked'),(1047,'auth_shibboleth','field_map_email',''),(1048,'auth_shibboleth','field_updatelocal_email','oncreate'),(1049,'auth_shibboleth','field_lock_email','unlocked'),(1050,'auth_shibboleth','field_map_city',''),(1051,'auth_shibboleth','field_updatelocal_city','oncreate'),(1052,'auth_shibboleth','field_lock_city','unlocked'),(1053,'auth_shibboleth','field_map_country',''),(1054,'auth_shibboleth','field_updatelocal_country','oncreate'),(1055,'auth_shibboleth','field_lock_country','unlocked'),(1056,'auth_shibboleth','field_map_lang',''),(1057,'auth_shibboleth','field_updatelocal_lang','oncreate'),(1058,'auth_shibboleth','field_lock_lang','unlocked'),(1059,'auth_shibboleth','field_map_description',''),(1060,'auth_shibboleth','field_updatelocal_description','oncreate'),(1061,'auth_shibboleth','field_lock_description','unlocked'),(1062,'auth_shibboleth','field_map_idnumber',''),(1063,'auth_shibboleth','field_updatelocal_idnumber','oncreate'),(1064,'auth_shibboleth','field_lock_idnumber','unlocked'),(1065,'auth_shibboleth','field_map_institution',''),(1066,'auth_shibboleth','field_updatelocal_institution','oncreate'),(1067,'auth_shibboleth','field_lock_institution','unlocked'),(1068,'auth_shibboleth','field_map_department',''),(1069,'auth_shibboleth','field_updatelocal_department','oncreate'),(1070,'auth_shibboleth','field_lock_department','unlocked'),(1071,'auth_shibboleth','field_map_phone1',''),(1072,'auth_shibboleth','field_updatelocal_phone1','oncreate'),(1073,'auth_shibboleth','field_lock_phone1','unlocked'),(1074,'auth_shibboleth','field_map_phone2',''),(1075,'auth_shibboleth','field_updatelocal_phone2','oncreate'),(1076,'auth_shibboleth','field_lock_phone2','unlocked'),(1077,'auth_shibboleth','field_map_address',''),(1078,'auth_shibboleth','field_updatelocal_address','oncreate'),(1079,'auth_shibboleth','field_lock_address','unlocked'),(1080,'auth_shibboleth','field_map_firstnamephonetic',''),(1081,'auth_shibboleth','field_updatelocal_firstnamephonetic','oncreate'),(1082,'auth_shibboleth','field_lock_firstnamephonetic','unlocked'),(1083,'auth_shibboleth','field_map_lastnamephonetic',''),(1084,'auth_shibboleth','field_updatelocal_lastnamephonetic','oncreate'),(1085,'auth_shibboleth','field_lock_lastnamephonetic','unlocked'),(1086,'auth_shibboleth','field_map_middlename',''),(1087,'auth_shibboleth','field_updatelocal_middlename','oncreate'),(1088,'auth_shibboleth','field_lock_middlename','unlocked'),(1089,'auth_shibboleth','field_map_alternatename',''),(1090,'auth_shibboleth','field_updatelocal_alternatename','oncreate'),(1091,'auth_shibboleth','field_lock_alternatename','unlocked'),(1092,'auth_none','field_lock_firstname','unlocked'),(1093,'auth_none','field_lock_lastname','unlocked'),(1094,'auth_none','field_lock_email','unlocked'),(1095,'auth_none','field_lock_city','unlocked'),(1096,'auth_none','field_lock_country','unlocked'),(1097,'auth_none','field_lock_lang','unlocked'),(1098,'auth_none','field_lock_description','unlocked'),(1099,'auth_none','field_lock_idnumber','unlocked'),(1100,'auth_none','field_lock_institution','unlocked'),(1101,'auth_none','field_lock_department','unlocked'),(1102,'auth_none','field_lock_phone1','unlocked'),(1103,'auth_none','field_lock_phone2','unlocked'),(1104,'auth_none','field_lock_address','unlocked'),(1105,'auth_none','field_lock_firstnamephonetic','unlocked'),(1106,'auth_none','field_lock_lastnamephonetic','unlocked'),(1107,'auth_none','field_lock_middlename','unlocked'),(1108,'auth_none','field_lock_alternatename','unlocked'),(1109,'auth_cas','field_map_firstname',''),(1110,'auth_cas','field_updatelocal_firstname','oncreate'),(1111,'auth_cas','field_updateremote_firstname','0'),(1112,'auth_cas','field_lock_firstname','unlocked'),(1113,'auth_cas','field_map_lastname',''),(1114,'auth_cas','field_updatelocal_lastname','oncreate'),(1115,'auth_cas','field_updateremote_lastname','0'),(1116,'auth_cas','field_lock_lastname','unlocked'),(1117,'auth_cas','field_map_email',''),(1118,'auth_cas','field_updatelocal_email','oncreate'),(1119,'auth_cas','field_updateremote_email','0'),(1120,'auth_cas','field_lock_email','unlocked'),(1121,'auth_cas','field_map_city',''),(1122,'auth_cas','field_updatelocal_city','oncreate'),(1123,'auth_cas','field_updateremote_city','0'),(1124,'auth_cas','field_lock_city','unlocked'),(1125,'auth_cas','field_map_country',''),(1126,'auth_cas','field_updatelocal_country','oncreate'),(1127,'auth_cas','field_updateremote_country','0'),(1128,'auth_cas','field_lock_country','unlocked'),(1129,'auth_cas','field_map_lang',''),(1130,'auth_cas','field_updatelocal_lang','oncreate'),(1131,'auth_cas','field_updateremote_lang','0'),(1132,'auth_cas','field_lock_lang','unlocked'),(1133,'auth_cas','field_map_description',''),(1134,'auth_cas','field_updatelocal_description','oncreate'),(1135,'auth_cas','field_updateremote_description','0'),(1136,'auth_cas','field_lock_description','unlocked'),(1137,'auth_cas','field_map_idnumber',''),(1138,'auth_cas','field_updatelocal_idnumber','oncreate'),(1139,'auth_cas','field_updateremote_idnumber','0'),(1140,'auth_cas','field_lock_idnumber','unlocked'),(1141,'auth_cas','field_map_institution',''),(1142,'auth_cas','field_updatelocal_institution','oncreate'),(1143,'auth_cas','field_updateremote_institution','0'),(1144,'auth_cas','field_lock_institution','unlocked'),(1145,'auth_cas','field_map_department',''),(1146,'auth_cas','field_updatelocal_department','oncreate'),(1147,'auth_cas','field_updateremote_department','0'),(1148,'auth_cas','field_lock_department','unlocked'),(1149,'auth_cas','field_map_phone1',''),(1150,'auth_cas','field_updatelocal_phone1','oncreate'),(1151,'auth_cas','field_updateremote_phone1','0'),(1152,'auth_cas','field_lock_phone1','unlocked'),(1153,'auth_cas','field_map_phone2',''),(1154,'auth_cas','field_updatelocal_phone2','oncreate'),(1155,'auth_cas','field_updateremote_phone2','0'),(1156,'auth_cas','field_lock_phone2','unlocked'),(1157,'auth_cas','field_map_address',''),(1158,'auth_cas','field_updatelocal_address','oncreate'),(1159,'auth_cas','field_updateremote_address','0'),(1160,'auth_cas','field_lock_address','unlocked'),(1161,'auth_cas','field_map_firstnamephonetic',''),(1162,'auth_cas','field_updatelocal_firstnamephonetic','oncreate'),(1163,'auth_cas','field_updateremote_firstnamephonetic','0'),(1164,'auth_cas','field_lock_firstnamephonetic','unlocked'),(1165,'auth_cas','field_map_lastnamephonetic',''),(1166,'auth_cas','field_updatelocal_lastnamephonetic','oncreate'),(1167,'auth_cas','field_updateremote_lastnamephonetic','0'),(1168,'auth_cas','field_lock_lastnamephonetic','unlocked'),(1169,'auth_cas','field_map_middlename',''),(1170,'auth_cas','field_updatelocal_middlename','oncreate'),(1171,'auth_cas','field_updateremote_middlename','0'),(1172,'auth_cas','field_lock_middlename','unlocked'),(1173,'auth_cas','field_map_alternatename',''),(1174,'auth_cas','field_updatelocal_alternatename','oncreate'),(1175,'auth_cas','field_updateremote_alternatename','0'),(1176,'auth_cas','field_lock_alternatename','unlocked'),(1177,'auth_ldap','field_map_firstname',''),(1178,'auth_ldap','field_updatelocal_firstname','oncreate'),(1179,'auth_ldap','field_updateremote_firstname','0'),(1180,'auth_ldap','field_lock_firstname','unlocked'),(1181,'auth_ldap','field_map_lastname',''),(1182,'auth_ldap','field_updatelocal_lastname','oncreate'),(1183,'auth_ldap','field_updateremote_lastname','0'),(1184,'auth_ldap','field_lock_lastname','unlocked'),(1185,'auth_ldap','field_map_email',''),(1186,'auth_ldap','field_updatelocal_email','oncreate'),(1187,'auth_ldap','field_updateremote_email','0'),(1188,'auth_ldap','field_lock_email','unlocked'),(1189,'auth_ldap','field_map_city',''),(1190,'auth_ldap','field_updatelocal_city','oncreate'),(1191,'auth_ldap','field_updateremote_city','0'),(1192,'auth_ldap','field_lock_city','unlocked'),(1193,'auth_ldap','field_map_country',''),(1194,'auth_ldap','field_updatelocal_country','oncreate'),(1195,'auth_ldap','field_updateremote_country','0'),(1196,'auth_ldap','field_lock_country','unlocked'),(1197,'auth_ldap','field_map_lang',''),(1198,'auth_ldap','field_updatelocal_lang','oncreate'),(1199,'auth_ldap','field_updateremote_lang','0'),(1200,'auth_ldap','field_lock_lang','unlocked'),(1201,'auth_ldap','field_map_description',''),(1202,'auth_ldap','field_updatelocal_description','oncreate'),(1203,'auth_ldap','field_updateremote_description','0'),(1204,'auth_ldap','field_lock_description','unlocked'),(1205,'auth_ldap','field_map_idnumber',''),(1206,'auth_ldap','field_updatelocal_idnumber','oncreate'),(1207,'auth_ldap','field_updateremote_idnumber','0'),(1208,'auth_ldap','field_lock_idnumber','unlocked'),(1209,'auth_ldap','field_map_institution',''),(1210,'auth_ldap','field_updatelocal_institution','oncreate'),(1211,'auth_ldap','field_updateremote_institution','0'),(1212,'auth_ldap','field_lock_institution','unlocked'),(1213,'auth_ldap','field_map_department',''),(1214,'auth_ldap','field_updatelocal_department','oncreate'),(1215,'auth_ldap','field_updateremote_department','0'),(1216,'auth_ldap','field_lock_department','unlocked'),(1217,'auth_ldap','field_map_phone1',''),(1218,'auth_ldap','field_updatelocal_phone1','oncreate'),(1219,'auth_ldap','field_updateremote_phone1','0'),(1220,'auth_ldap','field_lock_phone1','unlocked'),(1221,'auth_ldap','field_map_phone2',''),(1222,'auth_ldap','field_updatelocal_phone2','oncreate'),(1223,'auth_ldap','field_updateremote_phone2','0'),(1224,'auth_ldap','field_lock_phone2','unlocked'),(1225,'auth_ldap','field_map_address',''),(1226,'auth_ldap','field_updatelocal_address','oncreate'),(1227,'auth_ldap','field_updateremote_address','0'),(1228,'auth_ldap','field_lock_address','unlocked'),(1229,'auth_ldap','field_map_firstnamephonetic',''),(1230,'auth_ldap','field_updatelocal_firstnamephonetic','oncreate'),(1231,'auth_ldap','field_updateremote_firstnamephonetic','0'),(1232,'auth_ldap','field_lock_firstnamephonetic','unlocked'),(1233,'auth_ldap','field_map_lastnamephonetic',''),(1234,'auth_ldap','field_updatelocal_lastnamephonetic','oncreate'),(1235,'auth_ldap','field_updateremote_lastnamephonetic','0'),(1236,'auth_ldap','field_lock_lastnamephonetic','unlocked'),(1237,'auth_ldap','field_map_middlename',''),(1238,'auth_ldap','field_updatelocal_middlename','oncreate'),(1239,'auth_ldap','field_updateremote_middlename','0'),(1240,'auth_ldap','field_lock_middlename','unlocked'),(1241,'auth_ldap','field_map_alternatename',''),(1242,'auth_ldap','field_updatelocal_alternatename','oncreate'),(1243,'auth_ldap','field_updateremote_alternatename','0'),(1244,'auth_ldap','field_lock_alternatename','unlocked'),(1245,'auth_db','host','127.0.0.1'),(1246,'auth_db','type','mysqli'),(1247,'auth_db','sybasequoting','0'),(1248,'auth_db','name',''),(1249,'auth_db','user',''),(1250,'auth_db','pass',''),(1251,'auth_db','table',''),(1252,'auth_db','fielduser',''),(1253,'auth_db','fieldpass',''),(1254,'auth_db','passtype','plaintext'),(1255,'auth_db','extencoding','utf-8'),(1256,'auth_db','setupsql',''),(1257,'auth_db','debugauthdb','0'),(1258,'auth_db','changepasswordurl',''),(1259,'auth_db','removeuser','0'),(1260,'auth_db','updateusers','0'),(1261,'auth_db','field_map_firstname',''),(1262,'auth_db','field_updatelocal_firstname','oncreate'),(1263,'auth_db','field_updateremote_firstname','0'),(1264,'auth_db','field_lock_firstname','unlocked'),(1265,'auth_db','field_map_lastname',''),(1266,'auth_db','field_updatelocal_lastname','oncreate'),(1267,'auth_db','field_updateremote_lastname','0'),(1268,'auth_db','field_lock_lastname','unlocked'),(1269,'auth_db','field_map_email',''),(1270,'auth_db','field_updatelocal_email','oncreate'),(1271,'auth_db','field_updateremote_email','0'),(1272,'auth_db','field_lock_email','unlocked'),(1273,'auth_db','field_map_city',''),(1274,'auth_db','field_updatelocal_city','oncreate'),(1275,'auth_db','field_updateremote_city','0'),(1276,'auth_db','field_lock_city','unlocked'),(1277,'auth_db','field_map_country',''),(1278,'auth_db','field_updatelocal_country','oncreate'),(1279,'auth_db','field_updateremote_country','0'),(1280,'auth_db','field_lock_country','unlocked'),(1281,'auth_db','field_map_lang',''),(1282,'auth_db','field_updatelocal_lang','oncreate'),(1283,'auth_db','field_updateremote_lang','0'),(1284,'auth_db','field_lock_lang','unlocked'),(1285,'auth_db','field_map_description',''),(1286,'auth_db','field_updatelocal_description','oncreate'),(1287,'auth_db','field_updateremote_description','0'),(1288,'auth_db','field_lock_description','unlocked'),(1289,'auth_db','field_map_idnumber',''),(1290,'auth_db','field_updatelocal_idnumber','oncreate'),(1291,'auth_db','field_updateremote_idnumber','0'),(1292,'auth_db','field_lock_idnumber','unlocked'),(1293,'auth_db','field_map_institution',''),(1294,'auth_db','field_updatelocal_institution','oncreate'),(1295,'auth_db','field_updateremote_institution','0'),(1296,'auth_db','field_lock_institution','unlocked'),(1297,'auth_db','field_map_department',''),(1298,'auth_db','field_updatelocal_department','oncreate'),(1299,'auth_db','field_updateremote_department','0'),(1300,'auth_db','field_lock_department','unlocked'),(1301,'auth_db','field_map_phone1',''),(1302,'auth_db','field_updatelocal_phone1','oncreate'),(1303,'auth_db','field_updateremote_phone1','0'),(1304,'auth_db','field_lock_phone1','unlocked'),(1305,'auth_db','field_map_phone2',''),(1306,'auth_db','field_updatelocal_phone2','oncreate'),(1307,'auth_db','field_updateremote_phone2','0'),(1308,'auth_db','field_lock_phone2','unlocked'),(1309,'auth_db','field_map_address',''),(1310,'auth_db','field_updatelocal_address','oncreate'),(1311,'auth_db','field_updateremote_address','0'),(1312,'auth_db','field_lock_address','unlocked'),(1313,'auth_db','field_map_firstnamephonetic',''),(1314,'auth_db','field_updatelocal_firstnamephonetic','oncreate'),(1315,'auth_db','field_updateremote_firstnamephonetic','0'),(1316,'auth_db','field_lock_firstnamephonetic','unlocked'),(1317,'auth_db','field_map_lastnamephonetic',''),(1318,'auth_db','field_updatelocal_lastnamephonetic','oncreate'),(1319,'auth_db','field_updateremote_lastnamephonetic','0'),(1320,'auth_db','field_lock_lastnamephonetic','unlocked'),(1321,'auth_db','field_map_middlename',''),(1322,'auth_db','field_updatelocal_middlename','oncreate'),(1323,'auth_db','field_updateremote_middlename','0'),(1324,'auth_db','field_lock_middlename','unlocked'),(1325,'auth_db','field_map_alternatename',''),(1326,'auth_db','field_updatelocal_alternatename','oncreate'),(1327,'auth_db','field_updateremote_alternatename','0'),(1328,'auth_db','field_lock_alternatename','unlocked'),(1329,'block_recentlyaccessedcourses','displaycategories','1'),(1330,'block_starredcourses','displaycategories','1'),(1331,'block_section_links','numsections1','22'),(1332,'block_section_links','incby1','2'),(1333,'block_section_links','numsections2','40'),(1334,'block_section_links','incby2','5'),(1335,'block_section_links','showsectionname','0'),(1336,'block_activity_results','config_showbest','3'),(1337,'block_activity_results','config_showbest_locked',''),(1338,'block_activity_results','config_showworst','0'),(1339,'block_activity_results','config_showworst_locked',''),(1340,'block_activity_results','config_usegroups','0'),(1341,'block_activity_results','config_usegroups_locked',''),(1342,'block_activity_results','config_nameformat','1'),(1343,'block_activity_results','config_nameformat_locked',''),(1344,'block_activity_results','config_gradeformat','1'),(1345,'block_activity_results','config_gradeformat_locked',''),(1346,'block_activity_results','config_decimalpoints','2'),(1347,'block_activity_results','config_decimalpoints_locked',''),(1348,'block_accessreview','whattoshow','showboth'),(1349,'block_accessreview','errordisplay','showint'),(1350,'block_accessreview','toolpage','errors'),(1351,'block_myoverview','displaycategories','1'),(1352,'block_myoverview','layouts','card,list,summary'),(1353,'block_myoverview','displaygroupingallincludinghidden','0'),(1354,'block_myoverview','displaygroupingall','1'),(1355,'block_myoverview','displaygroupinginprogress','1'),(1356,'block_myoverview','displaygroupingpast','1'),(1357,'block_myoverview','displaygroupingfuture','1'),(1358,'block_myoverview','displaygroupingcustomfield','0'),(1359,'block_myoverview','customfiltergrouping',''),(1360,'block_myoverview','displaygroupingfavourites','1'),(1361,'block_myoverview','displaygroupinghidden','1'),(1362,'block_tag_youtube','apikey',''),(1363,'mlbackend_python','useserver','0'),(1364,'mlbackend_python','host',''),(1365,'mlbackend_python','port','0'),(1366,'mlbackend_python','secure','0'),(1367,'mlbackend_python','username','default'),(1368,'mlbackend_python','password',''),(1369,'fileconverter_googledrive','issuerid',''),(1370,'editor_atto','toolbar','collapse = collapse\r\nstyle1 = title, bold, italic\r\nlist = unorderedlist, orderedlist, indent\r\nlinks = link\r\nfiles = emojipicker, image, media, recordrtc, managefiles, h5p\r\naccessibility = accessibilitychecker, accessibilityhelper\r\nstyle2 = underline, strike, subscript, superscript\r\nalign = align\r\ninsert = equation, charmap, table, clear\r\nundo = undo\r\nother = html'),(1371,'editor_atto','autosavefrequency','60'),(1372,'atto_collapse','showgroups','6'),(1373,'atto_equation','librarygroup1','\\cdot\r\n\\times\r\n\\ast\r\n\\div\r\n\\diamond\r\n\\pm\r\n\\mp\r\n\\oplus\r\n\\ominus\r\n\\otimes\r\n\\oslash\r\n\\odot\r\n\\circ\r\n\\bullet\r\n\\asymp\r\n\\equiv\r\n\\subseteq\r\n\\supseteq\r\n\\leq\r\n\\geq\r\n\\preceq\r\n\\succeq\r\n\\sim\r\n\\simeq\r\n\\approx\r\n\\subset\r\n\\supset\r\n\\ll\r\n\\gg\r\n\\prec\r\n\\succ\r\n\\infty\r\n\\in\r\n\\ni\r\n\\forall\r\n\\exists\r\n\\neq\r\n'),(1374,'atto_equation','librarygroup2','\\leftarrow\r\n\\rightarrow\r\n\\uparrow\r\n\\downarrow\r\n\\leftrightarrow\r\n\\nearrow\r\n\\searrow\r\n\\swarrow\r\n\\nwarrow\r\n\\Leftarrow\r\n\\Rightarrow\r\n\\Uparrow\r\n\\Downarrow\r\n\\Leftrightarrow\r\n'),(1375,'atto_equation','librarygroup3','\\alpha\r\n\\beta\r\n\\gamma\r\n\\delta\r\n\\epsilon\r\n\\zeta\r\n\\eta\r\n\\theta\r\n\\iota\r\n\\kappa\r\n\\lambda\r\n\\mu\r\n\\nu\r\n\\xi\r\n\\pi\r\n\\rho\r\n\\sigma\r\n\\tau\r\n\\upsilon\r\n\\phi\r\n\\chi\r\n\\psi\r\n\\omega\r\n\\Gamma\r\n\\Delta\r\n\\Theta\r\n\\Lambda\r\n\\Xi\r\n\\Pi\r\n\\Sigma\r\n\\Upsilon\r\n\\Phi\r\n\\Psi\r\n\\Omega\r\n'),(1376,'atto_equation','librarygroup4','\\sum{a,b}\r\n\\sqrt[a]{b+c}\r\n\\int_{a}^{b}{c}\r\n\\iint_{a}^{b}{c}\r\n\\iiint_{a}^{b}{c}\r\n\\oint{a}\r\n(a)\r\n[a]\r\n\\lbrace{a}\\rbrace\r\n\\left| \\begin{matrix} a_1 & a_2 \\\\ a_3 & a_4 \\end{matrix} \\right|\r\n\\frac{a}{b+c}\r\n\\vec{a}\r\n\\binom {a} {b}\r\n{a \\brack b}\r\n{a \\brace b}\r\n'),(1377,'atto_recordrtc','allowedtypes','both'),(1378,'atto_recordrtc','audiobitrate','128000'),(1379,'atto_recordrtc','videobitrate','2500000'),(1380,'atto_recordrtc','audiotimelimit','120'),(1381,'atto_recordrtc','videotimelimit','120'),(1382,'atto_table','allowborders','0'),(1383,'atto_table','allowbackgroundcolour','0'),(1384,'atto_table','allowwidth','0'),(1385,'editor_tinymce','customtoolbar','wrap,formatselect,wrap,bold,italic,wrap,bullist,numlist,wrap,link,unlink,wrap,image\r\n\r\nundo,redo,wrap,underline,strikethrough,sub,sup,wrap,justifyleft,justifycenter,justifyright,wrap,outdent,indent,wrap,forecolor,backcolor,wrap,ltr,rtl\r\n\r\nfontselect,fontsizeselect,wrap,code,search,replace,wrap,nonbreaking,charmap,table,wrap,cleanup,removeformat,pastetext,pasteword,wrap,fullscreen'),(1386,'editor_tinymce','fontselectlist','Trebuchet=Trebuchet MS,Verdana,Arial,Helvetica,sans-serif;Arial=arial,helvetica,sans-serif;Courier New=courier new,courier,monospace;Georgia=georgia,times new roman,times,serif;Tahoma=tahoma,arial,helvetica,sans-serif;Times New Roman=times new roman,times,serif;Verdana=verdana,arial,helvetica,sans-serif;Impact=impact;Wingdings=wingdings'),(1387,'editor_tinymce','customconfig',''),(1388,'tinymce_moodleemoticon','requireemoticon','1'),(1389,'tinymce_spellchecker','spellengine',''),(1390,'tinymce_spellchecker','spelllanguagelist','+English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr,German=de,Italian=it,Polish=pl,Portuguese=pt,Spanish=es,Swedish=sv'),(1391,'antivirus_clamav','runningmethod','commandline'),(1392,'antivirus_clamav','pathtoclam',''),(1393,'antivirus_clamav','pathtounixsocket',''),(1394,'antivirus_clamav','tcpsockethost',''),(1395,'antivirus_clamav','tcpsocketport','3310'),(1396,'antivirus_clamav','clamfailureonupload','tryagain'),(1397,'antivirus_clamav','tries','1'),(1398,'filter_urltolink','formats','0,1,4'),(1399,'filter_urltolink','embedimages','1'),(1400,'filter_mathjaxloader','httpsurl','https://cdn.jsdelivr.net/npm/mathjax@2.7.9/MathJax.js'),(1401,'filter_mathjaxloader','texfiltercompatibility','0'),(1402,'filter_mathjaxloader','mathjaxconfig','MathJax.Hub.Config({\r\n    config: [\"Accessible.js\", \"Safe.js\"],\r\n    errorSettings: { message: [\"!\"] },\r\n    skipStartupTypeset: true,\r\n    messageStyle: \"none\"\r\n});\r\n'),(1403,'filter_mathjaxloader','additionaldelimiters',''),(1404,'filter_emoticon','formats','0,1,4'),(1405,'filter_displayh5p','allowedsources',''),(1406,'filter_tex','latexpreamble','\\usepackage[latin1]{inputenc}\r\n\\usepackage{amsmath}\r\n\\usepackage{amsfonts}\r\n\\RequirePackage{amsmath,amssymb,latexsym}\r\n'),(1407,'filter_tex','latexbackground','#FFFFFF'),(1408,'filter_tex','density','120'),(1409,'filter_tex','pathlatex','c:\\texmf\\miktex\\bin\\latex.exe'),(1410,'filter_tex','convertformat','gif'),(1411,'filter_tex','pathdvips','c:\\texmf\\miktex\\bin\\dvips.exe'),(1412,'filter_tex','pathconvert','c:\\imagemagick\\convert.exe'),(1413,'filter_tex','pathdvisvgm','c:\\texmf\\miktex\\bin\\dvisvgm.exe'),(1414,'filter_tex','pathmimetex',''),(1415,'format_singleactivity','activitytype','forum'),(1416,'format_topics','indentation','1'),(1417,'format_weeks','indentation','1'),(1418,'tool_brickfield','analysistype','0'),(1419,'tool_brickfield','deletehistoricaldata','1'),(1420,'tool_brickfield','batch','1000'),(1421,'tool_brickfield','perpage','50'),(1422,'tool_recyclebin','coursebinenable','1'),(1423,'tool_recyclebin','coursebinexpiry','604800'),(1424,'tool_recyclebin','categorybinenable','1'),(1425,'tool_recyclebin','categorybinexpiry','604800'),(1426,'tool_recyclebin','autohide','1'),(1427,'logstore_database','dbdriver',''),(1428,'logstore_database','dbhost',''),(1429,'logstore_database','dbuser',''),(1430,'logstore_database','dbpass',''),(1431,'logstore_database','dbname',''),(1432,'logstore_database','dbtable',''),(1433,'logstore_database','dbpersist','0'),(1434,'logstore_database','dbsocket',''),(1435,'logstore_database','dbport',''),(1436,'logstore_database','dbschema',''),(1437,'logstore_database','dbcollation',''),(1438,'logstore_database','dbhandlesoptions','0'),(1439,'logstore_database','buffersize','50'),(1440,'logstore_database','jsonformat','1'),(1441,'logstore_database','logguests','0'),(1442,'logstore_database','includelevels','1,2,0'),(1443,'logstore_database','includeactions','c,r,u,d'),(1444,'logstore_legacy','loglegacy','0'),(1445,'logstore_standard','logguests','1'),(1446,'logstore_standard','jsonformat','1'),(1447,'logstore_standard','loglifetime','0'),(1448,'logstore_standard','buffersize','50'),(1449,'enrol_guest','requirepassword','0'),(1450,'enrol_guest','usepasswordpolicy','0'),(1451,'enrol_guest','showhint','0'),(1452,'enrol_guest','defaultenrol','1'),(1453,'enrol_guest','status','1'),(1454,'enrol_guest','status_adv',''),(1455,'enrol_imsenterprise','imsfilelocation',''),(1456,'enrol_imsenterprise','logtolocation',''),(1457,'enrol_imsenterprise','mailadmins','0'),(1458,'enrol_imsenterprise','createnewusers','0'),(1459,'enrol_imsenterprise','imsupdateusers','0'),(1460,'enrol_imsenterprise','imsdeleteusers','0'),(1461,'enrol_imsenterprise','fixcaseusernames','0'),(1462,'enrol_imsenterprise','fixcasepersonalnames','0'),(1463,'enrol_imsenterprise','imssourcedidfallback','0'),(1464,'enrol_imsenterprise','imsrolemap01','5'),(1465,'enrol_imsenterprise','imsrolemap02','3'),(1466,'enrol_imsenterprise','imsrolemap03','3'),(1467,'enrol_imsenterprise','imsrolemap04','5'),(1468,'enrol_imsenterprise','imsrolemap05','0'),(1469,'enrol_imsenterprise','imsrolemap06','4'),(1470,'enrol_imsenterprise','imsrolemap07','0'),(1471,'enrol_imsenterprise','imsrolemap08','4'),(1472,'enrol_imsenterprise','truncatecoursecodes','0'),(1473,'enrol_imsenterprise','createnewcourses','0'),(1474,'enrol_imsenterprise','updatecourses','0'),(1475,'enrol_imsenterprise','createnewcategories','0'),(1476,'enrol_imsenterprise','nestedcategories','0'),(1477,'enrol_imsenterprise','categoryidnumber','0'),(1478,'enrol_imsenterprise','categoryseparator',''),(1479,'enrol_imsenterprise','imsunenrol','0'),(1480,'enrol_imsenterprise','imscoursemapshortname','coursecode'),(1481,'enrol_imsenterprise','imscoursemapfullname','short'),(1482,'enrol_imsenterprise','imscoursemapsummary','ignore'),(1483,'enrol_imsenterprise','imsrestricttarget',''),(1484,'enrol_imsenterprise','imscapitafix','0'),(1485,'enrol_flatfile','location',''),(1486,'enrol_flatfile','encoding','UTF-8'),(1487,'enrol_flatfile','mailstudents','0'),(1488,'enrol_flatfile','mailteachers','0'),(1489,'enrol_flatfile','mailadmins','0'),(1490,'enrol_flatfile','unenrolaction','3'),(1491,'enrol_flatfile','expiredaction','3'),(1492,'enrol_self','requirepassword','0'),(1493,'enrol_self','usepasswordpolicy','0'),(1494,'enrol_self','showhint','0'),(1495,'enrol_self','expiredaction','1'),(1496,'enrol_self','expirynotifyhour','6'),(1497,'enrol_self','defaultenrol','1'),(1498,'enrol_self','status','1'),(1499,'enrol_self','newenrols','1'),(1500,'enrol_self','groupkey','0'),(1501,'enrol_self','roleid','5'),(1502,'enrol_self','enrolperiod','0'),(1503,'enrol_self','expirynotify','0'),(1504,'enrol_self','expirythreshold','86400'),(1505,'enrol_self','longtimenosee','0'),(1506,'enrol_self','maxenrolled','0'),(1507,'enrol_self','sendcoursewelcomemessage','1'),(1508,'enrol_database','dbtype',''),(1509,'enrol_database','dbhost','localhost'),(1510,'enrol_database','dbuser',''),(1511,'enrol_database','dbpass',''),(1512,'enrol_database','dbname',''),(1513,'enrol_database','dbencoding','utf-8'),(1514,'enrol_database','dbsetupsql',''),(1515,'enrol_database','dbsybasequoting','0'),(1516,'enrol_database','debugdb','0'),(1517,'enrol_database','localcoursefield','idnumber'),(1518,'enrol_database','localuserfield','idnumber'),(1519,'enrol_database','localrolefield','shortname'),(1520,'enrol_database','localcategoryfield','id'),(1521,'enrol_database','remoteenroltable',''),(1522,'enrol_database','remotecoursefield',''),(1523,'enrol_database','remoteuserfield',''),(1524,'enrol_database','remoterolefield',''),(1525,'enrol_database','remoteotheruserfield',''),(1526,'enrol_database','defaultrole','5'),(1527,'enrol_database','ignorehiddencourses','0'),(1528,'enrol_database','unenrolaction','0'),(1529,'enrol_database','newcoursetable',''),(1530,'enrol_database','newcoursefullname','fullname'),(1531,'enrol_database','newcourseshortname','shortname'),(1532,'enrol_database','newcourseidnumber','idnumber'),(1533,'enrol_database','newcoursecategory',''),(1534,'enrol_database','defaultcategory','1'),(1535,'enrol_database','templatecourse',''),(1536,'enrol_fee','expiredaction','3'),(1537,'enrol_fee','status','1'),(1538,'enrol_fee','cost','0'),(1539,'enrol_fee','currency','USD'),(1540,'enrol_fee','roleid','5'),(1541,'enrol_fee','enrolperiod','0'),(1542,'enrol_manual','expiredaction','1'),(1543,'enrol_manual','expirynotifyhour','6'),(1544,'enrol_manual','defaultenrol','1'),(1545,'enrol_manual','status','0'),(1546,'enrol_manual','roleid','5'),(1547,'enrol_manual','enrolstart','4'),(1548,'enrol_manual','enrolperiod','0'),(1549,'enrol_manual','expirynotify','0'),(1550,'enrol_manual','expirythreshold','86400'),(1551,'enrol_mnet','roleid','5'),(1552,'enrol_mnet','roleid_adv','1'),(1553,'enrol_meta','nosyncroleids',''),(1554,'enrol_meta','syncall','1'),(1555,'enrol_meta','unenrolaction','3'),(1556,'enrol_meta','coursesort','sortorder'),(1557,'enrol_paypal','paypalbusiness',''),(1558,'enrol_paypal','mailstudents','0'),(1559,'enrol_paypal','mailteachers','0'),(1560,'enrol_paypal','mailadmins','0'),(1561,'enrol_paypal','expiredaction','3'),(1562,'enrol_paypal','status','1'),(1563,'enrol_paypal','cost','0'),(1564,'enrol_paypal','currency','USD'),(1565,'enrol_paypal','roleid','5'),(1566,'enrol_paypal','enrolperiod','0'),(1567,'enrol_lti','emaildisplay','2'),(1568,'enrol_lti','city',''),(1569,'enrol_lti','country',''),(1570,'enrol_lti','timezone','99'),(1571,'enrol_lti','lang','es_co'),(1572,'enrol_lti','institution',''),(1573,'enrol_cohort','roleid','5'),(1574,'enrol_cohort','unenrolaction','0'),(1575,'mod_bigbluebuttonbn','presentationdefault',''),(1576,'folder','showexpanded','1'),(1577,'folder','maxsizetodownload','0'),(1578,'quiz','timelimit','0'),(1579,'quiz','timelimit_adv',''),(1580,'quiz','timelimit_locked',''),(1581,'quiz','notifyattemptgradeddelay','18000'),(1582,'quiz','overduehandling','autosubmit'),(1583,'quiz','overduehandling_adv',''),(1584,'quiz','overduehandling_locked',''),(1585,'quiz','graceperiod','86400'),(1586,'quiz','graceperiod_adv',''),(1587,'quiz','graceperiod_locked',''),(1588,'quiz','graceperiodmin','60'),(1589,'quiz','attempts','0'),(1590,'quiz','attempts_adv',''),(1591,'quiz','attempts_locked',''),(1592,'quiz','grademethod','1'),(1593,'quiz','grademethod_adv',''),(1594,'quiz','grademethod_locked',''),(1595,'quiz','maximumgrade','10'),(1596,'quiz','maximumgrade_locked',''),(1597,'quiz','questionsperpage','1'),(1598,'quiz','questionsperpage_adv',''),(1599,'quiz','questionsperpage_locked',''),(1600,'quiz','navmethod','free'),(1601,'quiz','navmethod_adv','1'),(1602,'quiz','navmethod_locked',''),(1603,'quiz','shuffleanswers','1'),(1604,'quiz','shuffleanswers_adv',''),(1605,'quiz','shuffleanswers_locked',''),(1606,'quiz','preferredbehaviour','deferredfeedback'),(1607,'quiz','preferredbehaviour_locked',''),(1608,'quiz','canredoquestions','0'),(1609,'quiz','canredoquestions_adv','1'),(1610,'quiz','canredoquestions_locked',''),(1611,'quiz','attemptonlast','0'),(1612,'quiz','attemptonlast_adv','1'),(1613,'quiz','attemptonlast_locked',''),(1614,'quiz','reviewattempt','69904'),(1615,'quiz','reviewcorrectness','69904'),(1616,'quiz','reviewmarks','69904'),(1617,'quiz','reviewspecificfeedback','69904'),(1618,'quiz','reviewgeneralfeedback','69904'),(1619,'quiz','reviewrightanswer','69904'),(1620,'quiz','reviewoverallfeedback','4368'),(1621,'quiz','showuserpicture','0'),(1622,'quiz','showuserpicture_adv',''),(1623,'quiz','showuserpicture_locked',''),(1624,'quiz','decimalpoints','2'),(1625,'quiz','decimalpoints_adv',''),(1626,'quiz','decimalpoints_locked',''),(1627,'quiz','questiondecimalpoints','-1'),(1628,'quiz','questiondecimalpoints_adv',''),(1629,'quiz','questiondecimalpoints_locked',''),(1630,'quiz','showblocks','0'),(1631,'quiz','showblocks_adv','1'),(1632,'quiz','showblocks_locked',''),(1633,'quiz','quizpassword',''),(1634,'quiz','quizpassword_adv',''),(1635,'quiz','quizpassword_required',''),(1636,'quiz','quizpassword_locked',''),(1637,'quiz','subnet',''),(1638,'quiz','subnet_adv','1'),(1639,'quiz','subnet_locked',''),(1640,'quiz','delay1','0'),(1641,'quiz','delay1_adv','1'),(1642,'quiz','delay1_locked',''),(1643,'quiz','delay2','0'),(1644,'quiz','delay2_adv','1'),(1645,'quiz','delay2_locked',''),(1646,'quiz','browsersecurity','-'),(1647,'quiz','browsersecurity_adv','1'),(1648,'quiz','browsersecurity_locked',''),(1649,'quiz','initialnumfeedbacks','2'),(1650,'quiz','autosaveperiod','60'),(1651,'quizaccess_seb','autoreconfigureseb','1'),(1652,'quizaccess_seb','showseblinks','seb,http'),(1653,'quizaccess_seb','downloadlink','https://safeexambrowser.org/download_en.html'),(1654,'quizaccess_seb','quizpasswordrequired','0'),(1655,'quizaccess_seb','displayblocksbeforestart','0'),(1656,'quizaccess_seb','displayblockswhenfinished','1'),(1657,'label','dndmedia','1'),(1658,'label','dndresizewidth','400'),(1659,'label','dndresizeheight','400'),(1660,'mod_lesson','mediafile',''),(1661,'mod_lesson','mediafile_adv','1'),(1662,'mod_lesson','mediawidth','640'),(1663,'mod_lesson','mediaheight','480'),(1664,'mod_lesson','mediaclose','0'),(1665,'mod_lesson','progressbar','0'),(1666,'mod_lesson','progressbar_adv',''),(1667,'mod_lesson','ongoing','0'),(1668,'mod_lesson','ongoing_adv','1'),(1669,'mod_lesson','displayleftmenu','0'),(1670,'mod_lesson','displayleftmenu_adv',''),(1671,'mod_lesson','displayleftif','0'),(1672,'mod_lesson','displayleftif_adv','1'),(1673,'mod_lesson','slideshow','0'),(1674,'mod_lesson','slideshow_adv','1'),(1675,'mod_lesson','slideshowwidth','640'),(1676,'mod_lesson','slideshowheight','480'),(1677,'mod_lesson','slideshowbgcolor','#FFFFFF'),(1678,'mod_lesson','maxanswers','5'),(1679,'mod_lesson','maxanswers_adv','1'),(1680,'mod_lesson','defaultfeedback','0'),(1681,'mod_lesson','defaultfeedback_adv','1'),(1682,'mod_lesson','activitylink',''),(1683,'mod_lesson','activitylink_adv','1'),(1684,'mod_lesson','timelimit','0'),(1685,'mod_lesson','timelimit_adv',''),(1686,'mod_lesson','password','0'),(1687,'mod_lesson','password_adv','1'),(1688,'mod_lesson','modattempts','0'),(1689,'mod_lesson','modattempts_adv',''),(1690,'mod_lesson','displayreview','0'),(1691,'mod_lesson','displayreview_adv',''),(1692,'mod_lesson','maximumnumberofattempts','1'),(1693,'mod_lesson','maximumnumberofattempts_adv',''),(1694,'mod_lesson','defaultnextpage','0'),(1695,'mod_lesson','defaultnextpage_adv','1'),(1696,'mod_lesson','numberofpagestoshow','1'),(1697,'mod_lesson','numberofpagestoshow_adv','1'),(1698,'mod_lesson','practice','0'),(1699,'mod_lesson','practice_adv',''),(1700,'mod_lesson','customscoring','1'),(1701,'mod_lesson','customscoring_adv','1'),(1702,'mod_lesson','retakesallowed','0'),(1703,'mod_lesson','retakesallowed_adv',''),(1704,'mod_lesson','handlingofretakes','0'),(1705,'mod_lesson','handlingofretakes_adv','1'),(1706,'mod_lesson','minimumnumberofquestions','0'),(1707,'mod_lesson','minimumnumberofquestions_adv','1'),(1708,'book','numberingoptions','0,1,2,3'),(1709,'book','numbering','1'),(1710,'page','displayoptions','5'),(1711,'page','printintro','0'),(1712,'page','printlastmodified','1'),(1713,'page','display','5'),(1714,'page','popupwidth','620'),(1715,'page','popupheight','450'),(1716,'imscp','keepold','1'),(1717,'imscp','keepold_adv',''),(1718,'scorm','displaycoursestructure','0'),(1719,'scorm','displaycoursestructure_adv',''),(1720,'scorm','popup','0'),(1721,'scorm','popup_adv',''),(1722,'scorm','framewidth','100'),(1723,'scorm','framewidth_adv','1'),(1724,'scorm','frameheight','500'),(1725,'scorm','frameheight_adv','1'),(1726,'scorm','winoptgrp_adv','1'),(1727,'scorm','scrollbars','0'),(1728,'scorm','directories','0'),(1729,'scorm','location','0'),(1730,'scorm','menubar','0'),(1731,'scorm','toolbar','0'),(1732,'scorm','status','0'),(1733,'scorm','skipview','0'),(1734,'scorm','skipview_adv','1'),(1735,'scorm','hidebrowse','0'),(1736,'scorm','hidebrowse_adv','1'),(1737,'scorm','hidetoc','0'),(1738,'scorm','hidetoc_adv','1'),(1739,'scorm','nav','1'),(1740,'scorm','nav_adv','1'),(1741,'scorm','navpositionleft','-100'),(1742,'scorm','navpositionleft_adv','1'),(1743,'scorm','navpositiontop','-100'),(1744,'scorm','navpositiontop_adv','1'),(1745,'scorm','collapsetocwinsize','767'),(1746,'scorm','collapsetocwinsize_adv','1'),(1747,'scorm','displayattemptstatus','1'),(1748,'scorm','displayattemptstatus_adv',''),(1749,'scorm','grademethod','1'),(1750,'scorm','maxgrade','100'),(1751,'scorm','maxattempt','0'),(1752,'scorm','whatgrade','0'),(1753,'scorm','forcecompleted','0'),(1754,'scorm','forcenewattempt','0'),(1755,'scorm','autocommit','0'),(1756,'scorm','masteryoverride','1'),(1757,'scorm','lastattemptlock','0'),(1758,'scorm','auto','0'),(1759,'scorm','updatefreq','0'),(1760,'scorm','scormstandard','0'),(1761,'scorm','allowtypeexternal','0'),(1762,'scorm','allowtypelocalsync','0'),(1763,'scorm','allowtypeexternalaicc','0'),(1764,'scorm','allowaicchacp','0'),(1765,'scorm','aicchacptimeout','30'),(1766,'scorm','aicchacpkeepsessiondata','1'),(1767,'scorm','aiccuserid','1'),(1768,'scorm','forcejavascript','1'),(1769,'scorm','allowapidebug','0'),(1770,'scorm','apidebugmask','.*'),(1771,'scorm','protectpackagedownloads','0'),(1772,'resource','framesize','130'),(1773,'resource','displayoptions','0,1,4,5,6'),(1774,'resource','printintro','1'),(1775,'resource','display','0'),(1776,'resource','showsize','0'),(1777,'resource','showtype','0'),(1778,'resource','showdate','0'),(1779,'resource','popupwidth','620'),(1780,'resource','popupheight','450'),(1781,'resource','filterfiles','0'),(1782,'workshop','grade','80'),(1783,'workshop','gradinggrade','20'),(1784,'workshop','gradedecimals','0'),(1785,'workshop','maxbytes','0'),(1786,'workshop','strategy','accumulative'),(1787,'workshop','examplesmode','0'),(1788,'workshopallocation_random','numofreviews','5'),(1789,'workshopform_numerrors','grade0','No'),(1790,'workshopform_numerrors','grade1','S??'),(1791,'workshopeval_best','comparison','5'),(1792,'assign','feedback_plugin_for_gradebook','assignfeedback_comments'),(1793,'assign','showrecentsubmissions','0'),(1794,'assign','submissionreceipts','1'),(1795,'assign','submissionstatement','Confirmo que este trabajo es de elaboraci??n propia, excepto aquellas partes en las que haya reconocido la autor??a de la obra o parte de ella a otras personas.'),(1796,'assign','submissionstatementteamsubmission','Confirmo que este env??o es trabajo de mi grupo, excepto aquellas partes en las que se haya reconocido la autor??a de la obra o parte de ella a otras personas.'),(1797,'assign','submissionstatementteamsubmissionallsubmit','Confirmo que este trabajo es de elaboraci??n propia como miembro del grupo, excepto aquellas partes en las que haya reconocido la autor??a de la obra o parte de ella a otras personas.'),(1798,'assign','maxperpage','-1'),(1799,'assign','alwaysshowdescription','1'),(1800,'assign','alwaysshowdescription_adv',''),(1801,'assign','alwaysshowdescription_locked',''),(1802,'assign','allowsubmissionsfromdate','0'),(1803,'assign','allowsubmissionsfromdate_enabled','1'),(1804,'assign','allowsubmissionsfromdate_adv',''),(1805,'assign','duedate','604800'),(1806,'assign','duedate_enabled','1'),(1807,'assign','duedate_adv',''),(1808,'assign','cutoffdate','1209600'),(1809,'assign','cutoffdate_enabled',''),(1810,'assign','cutoffdate_adv',''),(1811,'assign','enabletimelimit','0'),(1812,'assign','gradingduedate','1209600'),(1813,'assign','gradingduedate_enabled','1'),(1814,'assign','gradingduedate_adv',''),(1815,'assign','submissiondrafts','0'),(1816,'assign','submissiondrafts_adv',''),(1817,'assign','submissiondrafts_locked',''),(1818,'assign','requiresubmissionstatement','0'),(1819,'assign','requiresubmissionstatement_adv',''),(1820,'assign','requiresubmissionstatement_locked',''),(1821,'assign','attemptreopenmethod','none'),(1822,'assign','attemptreopenmethod_adv',''),(1823,'assign','attemptreopenmethod_locked',''),(1824,'assign','maxattempts','-1'),(1825,'assign','maxattempts_adv',''),(1826,'assign','maxattempts_locked',''),(1827,'assign','teamsubmission','0'),(1828,'assign','teamsubmission_adv',''),(1829,'assign','teamsubmission_locked',''),(1830,'assign','preventsubmissionnotingroup','0'),(1831,'assign','preventsubmissionnotingroup_adv',''),(1832,'assign','preventsubmissionnotingroup_locked',''),(1833,'assign','requireallteammemberssubmit','0'),(1834,'assign','requireallteammemberssubmit_adv',''),(1835,'assign','requireallteammemberssubmit_locked',''),(1836,'assign','teamsubmissiongroupingid',''),(1837,'assign','teamsubmissiongroupingid_adv',''),(1838,'assign','sendnotifications','0'),(1839,'assign','sendnotifications_adv',''),(1840,'assign','sendnotifications_locked',''),(1841,'assign','sendlatenotifications','0'),(1842,'assign','sendlatenotifications_adv',''),(1843,'assign','sendlatenotifications_locked',''),(1844,'assign','sendstudentnotifications','1'),(1845,'assign','sendstudentnotifications_adv',''),(1846,'assign','sendstudentnotifications_locked',''),(1847,'assign','blindmarking','0'),(1848,'assign','blindmarking_adv',''),(1849,'assign','blindmarking_locked',''),(1850,'assign','hidegrader','0'),(1851,'assign','hidegrader_adv',''),(1852,'assign','hidegrader_locked',''),(1853,'assign','markingworkflow','0'),(1854,'assign','markingworkflow_adv',''),(1855,'assign','markingworkflow_locked',''),(1856,'assign','markingallocation','0'),(1857,'assign','markingallocation_adv',''),(1858,'assign','markingallocation_locked',''),(1859,'assignsubmission_file','default','1'),(1860,'assignsubmission_file','maxfiles','20'),(1861,'assignsubmission_file','filetypes',''),(1862,'assignsubmission_file','maxbytes','0'),(1863,'assignsubmission_onlinetext','default','0'),(1864,'assignfeedback_comments','default','1'),(1865,'assignfeedback_comments','inline','0'),(1866,'assignfeedback_comments','inline_adv',''),(1867,'assignfeedback_comments','inline_locked',''),(1868,'assignfeedback_editpdf','default','1'),(1869,'assignfeedback_editpdf','stamps','/cross.png'),(1870,'assignfeedback_file','default','0'),(1871,'assignfeedback_offline','default','0'),(1872,'url','framesize','130'),(1873,'url','secretphrase',''),(1874,'url','rolesinparams','0'),(1875,'url','displayoptions','0,1,5,6'),(1876,'url','printintro','1'),(1877,'url','display','0'),(1878,'url','popupwidth','620'),(1879,'url','popupheight','450'),(1880,'paygw_paypal','surcharge','0'),(1881,'media_videojs','videoextensions','html_video,media_source,.f4v,.flv'),(1882,'media_videojs','audioextensions','html_audio'),(1883,'media_videojs','youtube','1'),(1884,'media_videojs','videocssclass','video-js'),(1885,'media_videojs','audiocssclass','video-js'),(1886,'media_videojs','limitsize','1'),(1887,'qtype_multichoice','answerhowmany','1'),(1888,'qtype_multichoice','shuffleanswers','1'),(1889,'qtype_multichoice','answernumbering','abc'),(1890,'tool_mobile','apppolicy',''),(1891,'tool_mobile','typeoflogin','1'),(1892,'tool_mobile','qrcodetype','1'),(1893,'tool_mobile','qrkeyttl','600'),(1894,'tool_mobile','forcedurlscheme','moodlemobile'),(1895,'tool_mobile','minimumversion',''),(1896,'tool_mobile','autologinmintimebetweenreq','360'),(1897,'tool_mobile','enablesmartappbanners','0'),(1898,'tool_mobile','iosappid','633359593'),(1899,'tool_mobile','androidappid','com.moodle.moodlemobile'),(1900,'tool_mobile','setuplink','https://download.moodle.org/mobile'),(1901,'tool_mobile','forcelogout','0'),(1902,'tool_mobile','disabledfeatures',''),(1903,'tool_mobile','custommenuitems',''),(1904,'tool_mobile','filetypeexclusionlist',''),(1905,'tool_mobile','customlangstrings',''),(1906,'tool_moodlenet','defaultmoodlenetname','Central MoodleNet'),(1907,'tool_moodlenet','defaultmoodlenet','https://moodle.net'),(1908,'theme_moove','version','2022062007'),(1910,'theme_moove','logo','/logo_josefina.png'),(1911,'theme_moove','favicon','/favicon.ico'),(1912,'theme_moove','preset','default.scss'),(1913,'theme_moove','presetfiles',''),(1914,'theme_moove','loginbgimg','/fondo_Vallesol.jpg'),(1915,'theme_moove','brandcolor','#0f47ad'),(1916,'theme_moove','secondarymenucolor','#0f47ad'),(1917,'theme_moove','fontsite','Roboto'),(1918,'theme_moove','enablecourseindex','1'),(1919,'theme_moove','scsspre',''),(1920,'theme_moove','scss',''),(1921,'theme_moove','googleanalytics',''),(1922,'theme_moove','disableteacherspic','1'),(1923,'theme_moove','slidercount','1'),(1924,'theme_moove','displaymarketingbox','0'),(1925,'theme_moove','numbersfrontpage','0'),(1926,'theme_moove','faqcount','0'),(1927,'theme_moove','website',''),(1928,'theme_moove','mobile',''),(1929,'theme_moove','mail','iervallesol@gmail.com'),(1930,'theme_moove','facebook',''),(1931,'theme_moove','twitter',''),(1932,'theme_moove','linkedin',''),(1933,'theme_moove','youtube',''),(1934,'theme_moove','instagram','https://www.instagram.com/momentosvallesol/'),(1935,'theme_moove','whatsapp',''),(1936,'theme_moove','telegram',''),(1937,'theme_moove','marketingheading','Vallesol'),(1938,'theme_moove','marketingcontent','Plataforma de Vallesol'),(1939,'theme_moove','marketing1icon',''),(1940,'theme_moove','marketing1heading','Lorem'),(1941,'theme_moove','marketing1content','Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.'),(1942,'theme_moove','marketing2icon',''),(1943,'theme_moove','marketing2heading','Lorem'),(1944,'theme_moove','marketing2content','Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.'),(1945,'theme_moove','marketing3icon',''),(1946,'theme_moove','marketing3heading','Lorem'),(1947,'theme_moove','marketing3content','Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.'),(1948,'theme_moove','marketing4icon',''),(1949,'theme_moove','marketing4heading','Lorem'),(1950,'theme_moove','marketing4content','Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.'),(1951,'theme_moove','numbersfrontpagecontent','<h2>Espacio de Aprendizaje<br></h2>'),(1952,'theme_moove','sliderimage1','/vallesol.jpg'),(1953,'theme_moove','slidertitle1','Vallesol'),(1954,'theme_moove','slidercap1',''),(1955,'theme_moove','sliderimage2',''),(1956,'theme_moove','slidertitle2',''),(1957,'theme_moove','slidercap2',''),(1958,'theme_moove','sliderimage3',''),(1959,'theme_moove','slidertitle3',''),(1960,'theme_moove','slidercap3',''),(1961,'theme_moove','sliderimage4',''),(1962,'theme_moove','slidertitle4',''),(1963,'theme_moove','slidercap4',''),(1964,'theme_moove','sliderimage5',''),(1965,'theme_moove','slidertitle5',''),(1966,'theme_moove','slidercap5',''),(1967,'mod_attendance','version','2022083108'),(1969,'attendance','resultsperpage','100'),(1970,'attendance','studentscanmark','1'),(1971,'attendance','rotateqrcodeinterval','15'),(1972,'attendance','rotateqrcodeexpirymargin','2'),(1973,'attendance','studentscanmarksessiontime','1'),(1974,'attendance','studentscanmarksessiontimeend','60'),(1975,'attendance','subnetactivitylevel','1'),(1976,'attendance','defaultview','2'),(1977,'attendance','multisessionexpanded','0'),(1978,'attendance','showsessiondescriptiononreport','0'),(1979,'attendance','studentrecordingexpanded','1'),(1980,'attendance','enablecalendar','1'),(1981,'attendance','enablewarnings','0'),(1982,'attendance','automark_useempty','1'),(1983,'attendance','customexportfields','id'),(1984,'attendance','mobilesessionfrom','21600'),(1985,'attendance','mobilesessionto','86400'),(1986,'attendance','subnet',''),(1987,'attendance','calendarevent_default','1'),(1988,'attendance','absenteereport_default','1'),(1989,'attendance','studentscanmark_default','0'),(1990,'attendance','automark_default','0'),(1991,'attendance','studentsearlyopentime','0'),(1992,'attendance','randompassword_default','0'),(1993,'attendance','includeqrcode_default','0'),(1994,'attendance','rotateqrcode_default','0'),(1995,'attendance','autoassignstatus','0'),(1996,'attendance','preventsharedip','0'),(1997,'attendance','preventsharediptime',''),(1998,'attendance','warningpercent','70'),(1999,'attendance','warnafter','5'),(2000,'attendance','maxwarn','1'),(2001,'attendance','emailuser','1'),(2002,'attendance','emailsubject','Advertencia de asistencia'),(2003,'attendance','emailcontent','Hola %userfirstname%,\r\nSu asistencia en %coursename% %attendancename% ha ca??do por debajo del %warningpercent% y actualmente es del %percent% - ??esperamos que est?? bien!\r\n\r\nPara aprovechar al m??ximo este curso, debe mejorar su asistencia, p??ngase en contacto si necesita m??s ayuda.'),(2004,'enrol_ldap','objectclass','(objectClass=*)'),(2005,'block_completion_progress','version','2023063000'),(2006,'block_completion_progress','wrapafter','16'),(2007,'block_completion_progress','defaultlongbars','squeeze'),(2008,'block_completion_progress','coursenametoshow','shortname'),(2009,'block_completion_progress','completed_colour','#73A839'),(2010,'block_completion_progress','cachevalue','4'),(2011,'block_completion_progress','submittednotcomplete_colour','#FFCC00'),(2012,'block_completion_progress','notCompleted_colour','#C71C22'),(2013,'block_completion_progress','futureNotCompleted_colour','#025187'),(2014,'block_completion_progress','showinactive','0'),(2015,'block_completion_progress','showlastincourse','1'),(2016,'block_completion_progress','forceiconsinbar','1');
/*!40000 ALTER TABLE `mdl_config_plugins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_contentbank_content`
--

DROP TABLE IF EXISTS `mdl_contentbank_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_contentbank_content` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `contenttype` varchar(100) NOT NULL DEFAULT '',
  `contextid` bigint(10) NOT NULL,
  `visibility` tinyint(1) NOT NULL DEFAULT 1,
  `instanceid` bigint(10) DEFAULT NULL,
  `configdata` longtext DEFAULT NULL,
  `usercreated` bigint(10) NOT NULL,
  `usermodified` bigint(10) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_contcont_nam_ix` (`name`),
  KEY `mdl_contcont_conconins_ix` (`contextid`,`contenttype`,`instanceid`),
  KEY `mdl_contcont_con_ix` (`contextid`),
  KEY `mdl_contcont_use_ix` (`usermodified`),
  KEY `mdl_contcont_use2_ix` (`usercreated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='This table stores content data in the content bank.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_contentbank_content`
--

LOCK TABLES `mdl_contentbank_content` WRITE;
/*!40000 ALTER TABLE `mdl_contentbank_content` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_contentbank_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_context`
--

DROP TABLE IF EXISTS `mdl_context`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_context` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `contextlevel` bigint(10) NOT NULL DEFAULT 0,
  `instanceid` bigint(10) NOT NULL DEFAULT 0,
  `path` varchar(255) DEFAULT NULL,
  `depth` tinyint(2) NOT NULL DEFAULT 0,
  `locked` tinyint(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_cont_conins_uix` (`contextlevel`,`instanceid`),
  KEY `mdl_cont_ins_ix` (`instanceid`),
  KEY `mdl_cont_pat_ix` (`path`)
) ENGINE=InnoDB AUTO_INCREMENT=275 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='one of these must be set';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_context`
--

LOCK TABLES `mdl_context` WRITE;
/*!40000 ALTER TABLE `mdl_context` DISABLE KEYS */;
INSERT INTO `mdl_context` VALUES (1,10,0,'/1',1,0),(2,50,1,'/1/2',2,0),(4,30,1,'/1/4',2,0),(5,30,2,'/1/5',2,0),(6,80,1,'/1/6',2,0),(7,80,2,'/1/7',2,0),(8,80,3,'/1/8',2,0),(9,80,4,'/1/9',2,0),(10,80,5,'/1/10',2,0),(64,80,6,'/1/5/64',3,0),(65,80,7,'/1/5/65',3,0),(66,80,8,'/1/5/66',3,0),(67,30,3,'/1/67',2,0),(68,30,4,'/1/68',2,0),(69,30,5,'/1/69',2,0),(70,30,6,'/1/70',2,0),(71,30,7,'/1/71',2,0),(72,30,8,'/1/72',2,0),(74,80,9,'/1/5/74',3,0),(207,40,17,'/1/207',2,0),(208,40,18,'/1/208',2,0),(209,40,19,'/1/209',2,0),(210,40,20,'/1/210',2,0),(211,40,21,'/1/211',2,0),(212,40,22,'/1/212',2,0),(213,50,87,'/1/207/213',3,0),(214,50,88,'/1/207/214',3,0),(215,50,89,'/1/207/215',3,0),(216,50,90,'/1/207/216',3,0),(217,50,91,'/1/207/217',3,0),(218,50,92,'/1/207/218',3,0),(219,50,93,'/1/207/219',3,0),(220,50,94,'/1/208/220',3,0),(221,50,95,'/1/208/221',3,0),(222,50,96,'/1/208/222',3,0),(223,50,97,'/1/208/223',3,0),(224,50,98,'/1/208/224',3,0),(225,50,99,'/1/208/225',3,0),(226,50,100,'/1/208/226',3,0),(241,50,108,'/1/209/241',3,0),(242,50,109,'/1/209/242',3,0),(243,50,110,'/1/209/243',3,0),(244,50,111,'/1/209/244',3,0),(245,50,112,'/1/209/245',3,0),(246,50,113,'/1/209/246',3,0),(247,50,114,'/1/209/247',3,0),(248,50,115,'/1/210/248',3,0),(249,50,116,'/1/210/249',3,0),(250,50,117,'/1/210/250',3,0),(251,50,118,'/1/210/251',3,0),(252,50,119,'/1/210/252',3,0),(253,50,120,'/1/210/253',3,0),(254,50,121,'/1/210/254',3,0),(255,50,122,'/1/211/255',3,0),(256,50,123,'/1/211/256',3,0),(257,50,124,'/1/211/257',3,0),(258,50,125,'/1/211/258',3,0),(259,50,126,'/1/211/259',3,0),(260,50,127,'/1/211/260',3,0),(261,50,128,'/1/211/261',3,0),(262,50,129,'/1/211/262',3,0),(263,50,130,'/1/212/263',3,0),(264,50,131,'/1/212/264',3,0),(265,50,132,'/1/212/265',3,0),(266,50,133,'/1/212/266',3,0),(267,50,134,'/1/212/267',3,0),(268,50,135,'/1/212/268',3,0),(269,50,136,'/1/212/269',3,0),(270,50,137,'/1/212/270',3,0),(271,70,88,'/1/207/214/271',4,0),(272,80,10,'/1/207/214/272',4,0),(273,80,11,'/1/207/214/273',4,0),(274,70,138,'/1/207/214/274',4,0);
/*!40000 ALTER TABLE `mdl_context` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_context_temp`
--

DROP TABLE IF EXISTS `mdl_context_temp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_context_temp` (
  `id` bigint(10) NOT NULL,
  `path` varchar(255) NOT NULL DEFAULT '',
  `depth` tinyint(2) NOT NULL,
  `locked` tinyint(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Used by build_context_path() in upgrade and cron to keep con';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_context_temp`
--

LOCK TABLES `mdl_context_temp` WRITE;
/*!40000 ALTER TABLE `mdl_context_temp` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_context_temp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_course`
--

DROP TABLE IF EXISTS `mdl_course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_course` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `category` bigint(10) NOT NULL DEFAULT 0,
  `sortorder` bigint(10) NOT NULL DEFAULT 0,
  `fullname` varchar(254) NOT NULL DEFAULT '',
  `shortname` varchar(255) NOT NULL DEFAULT '',
  `idnumber` varchar(100) NOT NULL DEFAULT '',
  `summary` longtext DEFAULT NULL,
  `summaryformat` tinyint(2) NOT NULL DEFAULT 0,
  `format` varchar(21) NOT NULL DEFAULT 'topics',
  `showgrades` tinyint(2) NOT NULL DEFAULT 1,
  `newsitems` mediumint(5) NOT NULL DEFAULT 1,
  `startdate` bigint(10) NOT NULL DEFAULT 0,
  `enddate` bigint(10) NOT NULL DEFAULT 0,
  `relativedatesmode` tinyint(1) NOT NULL DEFAULT 0,
  `marker` bigint(10) NOT NULL DEFAULT 0,
  `maxbytes` bigint(10) NOT NULL DEFAULT 0,
  `legacyfiles` smallint(4) NOT NULL DEFAULT 0,
  `showreports` smallint(4) NOT NULL DEFAULT 0,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `visibleold` tinyint(1) NOT NULL DEFAULT 1,
  `downloadcontent` tinyint(1) DEFAULT NULL,
  `groupmode` smallint(4) NOT NULL DEFAULT 0,
  `groupmodeforce` smallint(4) NOT NULL DEFAULT 0,
  `defaultgroupingid` bigint(10) NOT NULL DEFAULT 0,
  `lang` varchar(30) NOT NULL DEFAULT '',
  `calendartype` varchar(30) NOT NULL DEFAULT '',
  `theme` varchar(50) NOT NULL DEFAULT '',
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `requested` tinyint(1) NOT NULL DEFAULT 0,
  `enablecompletion` tinyint(1) NOT NULL DEFAULT 0,
  `completionnotify` tinyint(1) NOT NULL DEFAULT 0,
  `cacherev` bigint(10) NOT NULL DEFAULT 0,
  `originalcourseid` bigint(10) DEFAULT NULL,
  `showactivitydates` tinyint(1) NOT NULL DEFAULT 0,
  `showcompletionconditions` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_cour_cat_ix` (`category`),
  KEY `mdl_cour_idn_ix` (`idnumber`),
  KEY `mdl_cour_sho_ix` (`shortname`),
  KEY `mdl_cour_sor_ix` (`sortorder`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Central course table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_course`
--

LOCK TABLES `mdl_course` WRITE;
/*!40000 ALTER TABLE `mdl_course` DISABLE KEYS */;
INSERT INTO `mdl_course` VALUES (1,0,1,'vallesol','valle','','',0,'site',1,3,0,0,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756251815,1756520923,0,0,0,1756559476,NULL,0,NULL),(87,17,10007,'Ciencias Sociales 6','CS6','','curso de Ciencias Sociales para grado 6',0,'topics',1,10,1756702680,1763359080,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756529933,1756529933,0,1,0,1756559476,NULL,0,1),(88,17,10006,'Matematicas 6','M6','','curso de Matematicas para grado 6',0,'topics',1,10,1756702680,1763359080,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756529938,1756560789,0,1,0,1756561332,NULL,0,1),(89,17,10005,'Emprendimiento 6','E6','','curso de Emprendimiento para grado 6',0,'topics',1,10,1756702680,1763359080,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756529942,1756529942,0,1,0,1756559476,NULL,0,1),(90,17,10004,'Educacin Fisica 6','EF6','','curso de Educacin Fisica para grado 6',0,'topics',1,10,1756702680,1763359080,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756529944,1756529944,0,1,0,1756559476,NULL,0,1),(91,17,10003,'Tecnologia 6','T6','','curso de Tecnologia para grado 6',0,'topics',1,10,1756702680,1763359080,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756529950,1756529950,0,1,0,1756559476,NULL,0,1),(92,17,10002,'Geometria 6','G6','','curso de Geometria para grado 6',0,'topics',1,10,1756702680,1763359080,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756529955,1756529955,0,1,0,1756559476,NULL,0,1),(93,17,10001,'Urbanidad 6','U6','','curso de Urbanidad para grado 6',0,'topics',1,10,1756702680,1763359080,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756529959,1756529959,0,1,0,1756559476,NULL,0,1),(94,18,20007,'Ciencias Sociales 7','CS7','','curso de Ciencias Sociales para grado 7',0,'topics',1,10,1756727220,1763383620,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756554544,1756554544,0,1,0,1756559476,NULL,0,1),(95,18,20006,'Matematicas 7','M7','','curso de Matematicas para grado 7',0,'topics',1,10,1756727220,1763383620,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756554548,1756554548,0,1,0,1756559476,NULL,0,1),(96,18,20005,'Emprendimiento 7','E7','','curso de Emprendimiento para grado 7',0,'topics',1,10,1756727220,1763383620,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756554552,1756554552,0,1,0,1756559476,NULL,0,1),(97,18,20004,'Educacin Fisica 7','EF7','','curso de Educacin Fisica para grado 7',0,'topics',1,10,1756727220,1763383620,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756554556,1756554556,0,1,0,1756559476,NULL,0,1),(98,18,20003,'Tecnologia 7','T7','','curso de Tecnologia para grado 7',0,'topics',1,10,1756727220,1763383620,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756554560,1756554560,0,1,0,1756559476,NULL,0,1),(99,18,20002,'Geometria 7','G7','','curso de Geometria para grado 7',0,'topics',1,10,1756727220,1763383620,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756554564,1756554564,0,1,0,1756559476,NULL,0,1),(100,18,20001,'Urbanidad 7','U7','','curso de Urbanidad para grado 7',0,'topics',1,10,1756727220,1763383620,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756554568,1756554568,0,1,0,1756559476,NULL,0,1),(108,19,30007,'Ciencias Sociales 8','CS8','','curso de Ciencias Sociales para grado 8',0,'topics',1,10,1757332980,1763384580,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555431,1756555431,0,1,0,1756559476,NULL,0,1),(109,19,30006,'Matematicas 8','M8','','curso de Matematicas para grado 8',0,'topics',1,10,1757332980,1763384580,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555435,1756555435,0,1,0,1756559476,NULL,0,1),(110,19,30005,'Emprendimiento 8','E8','','curso de Emprendimiento para grado 8',0,'topics',1,10,1757332980,1763384580,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555440,1756555440,0,1,0,1756559476,NULL,0,1),(111,19,30004,'Educacin Fisica 8','EF8','','curso de Educacin Fisica para grado 8',0,'topics',1,10,1757332980,1763384580,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555443,1756555443,0,1,0,1756559476,NULL,0,1),(112,19,30003,'Tecnologia 8','T8','','curso de Tecnologia para grado 8',0,'topics',1,10,1757332980,1763384580,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555448,1756555448,0,1,0,1756559476,NULL,0,1),(113,19,30002,'Geometria 8','G8','','curso de Geometria para grado 8',0,'topics',1,10,1757332980,1763384580,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555453,1756555453,0,1,0,1756559476,NULL,0,1),(114,19,30001,'Urbanidad 8','U8','','curso de Urbanidad para grado 8',0,'topics',1,10,1757332980,1763384580,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555456,1756555456,0,1,0,1756559476,NULL,0,1),(115,20,40007,'Ciencias Sociales 9','CS9','','curso de Ciencias Sociales para grado 9',0,'topics',1,10,1754654760,1763384760,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555628,1756555628,0,1,0,1756559476,NULL,0,1),(116,20,40006,'Matematicas 9','M9','','curso de Matematicas para grado 9',0,'topics',1,10,1754654760,1763384760,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555633,1756555633,0,1,0,1756559476,NULL,0,1),(117,20,40005,'Emprendimiento 9','E9','','curso de Emprendimiento para grado 9',0,'topics',1,10,1754654760,1763384760,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555635,1756555635,0,1,0,1756559476,NULL,0,1),(118,20,40004,'Educacin Fisica 9','EF9','','curso de Educacin Fisica para grado 9',0,'topics',1,10,1754654760,1763384760,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555639,1756555639,0,1,0,1756559476,NULL,0,1),(119,20,40003,'Tecnologia 9','T9','','curso de Tecnologia para grado 9',0,'topics',1,10,1754654760,1763384760,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555642,1756555642,0,1,0,1756559476,NULL,0,1),(120,20,40002,'Geometria 9','G9','','curso de Geometria para grado 9',0,'topics',1,10,1754654760,1763384760,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555647,1756555647,0,1,0,1756559476,NULL,0,1),(121,20,40001,'Urbanidad 9','U9','','curso de Urbanidad para grado 9',0,'topics',1,10,1754654760,1763384760,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555651,1756555651,0,1,0,1756559476,NULL,0,1),(122,21,50008,'Ciencias Economicas_Politicas10','CEconomicas_Politicas10','','curso de Ciencias para grado Economicas_Politicas10',0,'topics',1,10,1754655060,1794921060,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555894,1756555894,0,1,0,1756559476,NULL,0,1),(123,21,50007,'Matematicas 10','M10','','curso de Matematicas para grado 10',0,'topics',1,10,1754655060,1794921060,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555897,1756555897,0,1,0,1756559476,NULL,0,1),(124,21,50006,'Emprendimiento 10','E10','','curso de Emprendimiento para grado 10',0,'topics',1,10,1754655060,1794921060,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555902,1756555902,0,1,0,1756559476,NULL,0,1),(125,21,50005,'Educacin Fisica 10','EF10','','curso de Educacin Fisica para grado 10',0,'topics',1,10,1754655060,1794921060,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555905,1756555905,0,1,0,1756559476,NULL,0,1),(126,21,50004,'Tecnologia 10','T10','','curso de Tecnologia para grado 10',0,'topics',1,10,1754655060,1794921060,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555910,1756555910,0,1,0,1756559476,NULL,0,1),(127,21,50003,'Geometria 10','G10','','curso de Geometria para grado 10',0,'topics',1,10,1754655060,1794921060,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555915,1756555915,0,1,0,1756559476,NULL,0,1),(128,21,50002,'Urbanidad 10','U10','','curso de Urbanidad para grado 10',0,'topics',1,10,1754655060,1794921060,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555917,1756555917,0,1,0,1756559476,NULL,0,1),(129,21,50001,'Fisica 10','F10','','curso de Fisica para grado 10',0,'topics',1,10,1754655060,1794921060,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756555923,1756555923,0,1,0,1756559476,NULL,0,1),(130,22,60008,'Ciencias Economicas_Politicas11','CEconomicas_Politicas11','','curso de Ciencias para grado Economicas_Politicas11',0,'topics',1,10,1757333700,1763385300,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756556256,1756556256,0,1,0,1756559476,NULL,0,1),(131,22,60007,'Matematicas 11','M11','','curso de Matematicas para grado 11',0,'topics',1,10,1757333700,1763385300,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756556261,1756556261,0,1,0,1756559476,NULL,0,1),(132,22,60006,'Emprendimiento 11','E11','','curso de Emprendimiento para grado 11',0,'topics',1,10,1757333700,1763385300,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756556265,1756556265,0,1,0,1756559476,NULL,0,1),(133,22,60005,'Educacin Fisica 11','EF11','','curso de Educacin Fisica para grado 11',0,'topics',1,10,1757333700,1763385300,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756556269,1756556269,0,1,0,1756559476,NULL,0,1),(134,22,60004,'Tecnologia 11','T11','','curso de Tecnologia para grado 11',0,'topics',1,10,1757333700,1763385300,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756556274,1756556274,0,1,0,1756559476,NULL,0,1),(135,22,60003,'Geometria 11','G11','','curso de Geometria para grado 11',0,'topics',1,10,1757333700,1763385300,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756556278,1756556278,0,1,0,1756559476,NULL,0,1),(136,22,60002,'Urbanidad 11','U11','','curso de Urbanidad para grado 11',0,'topics',1,10,1757333700,1763385300,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756556285,1756556285,0,1,0,1756559476,NULL,0,1),(137,22,60001,'Fisica 11','F11','','curso de Fisica  para grado 11',0,'topics',1,10,1757333700,1763385300,0,0,0,0,0,1,1,NULL,0,0,0,'','','',1756556290,1756556290,0,1,0,1756559476,NULL,0,1);
/*!40000 ALTER TABLE `mdl_course` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_course_categories`
--

DROP TABLE IF EXISTS `mdl_course_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_course_categories` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `idnumber` varchar(100) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `descriptionformat` tinyint(2) NOT NULL DEFAULT 0,
  `parent` bigint(10) NOT NULL DEFAULT 0,
  `sortorder` bigint(10) NOT NULL DEFAULT 0,
  `coursecount` bigint(10) NOT NULL DEFAULT 0,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `visibleold` tinyint(1) NOT NULL DEFAULT 1,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `depth` bigint(10) NOT NULL DEFAULT 0,
  `path` varchar(255) NOT NULL DEFAULT '',
  `theme` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_courcate_par_ix` (`parent`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Course categories';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_course_categories`
--

LOCK TABLES `mdl_course_categories` WRITE;
/*!40000 ALTER TABLE `mdl_course_categories` DISABLE KEYS */;
INSERT INTO `mdl_course_categories` VALUES (17,'6','6','6',0,0,10000,7,1,1,1756529048,1,'/17',NULL),(18,'7','7','<p dir=\"ltr\" style=\"text-align: left;\">7</p>',1,0,20000,7,1,1,1756529125,1,'/18',NULL),(19,'8','8','<p dir=\"ltr\" style=\"text-align: left;\">8</p>',1,0,30000,7,1,1,1756529174,1,'/19',NULL),(20,'9','9','<p dir=\"ltr\" style=\"text-align: left;\">9</p>',1,0,40000,7,1,1,1756529208,1,'/20',NULL),(21,'10','10','<p dir=\"ltr\" style=\"text-align: left;\">10</p>',1,0,50000,8,1,1,1756529240,1,'/21',NULL),(22,'11','11','<p dir=\"ltr\" style=\"text-align: left;\">11</p>',1,0,60000,8,1,1,1756529267,1,'/22',NULL);
/*!40000 ALTER TABLE `mdl_course_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_course_completion_aggr_methd`
--

DROP TABLE IF EXISTS `mdl_course_completion_aggr_methd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_course_completion_aggr_methd` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `criteriatype` bigint(10) DEFAULT NULL,
  `method` tinyint(1) NOT NULL DEFAULT 0,
  `value` decimal(10,5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_courcompaggrmeth_coucr_uix` (`course`,`criteriatype`),
  KEY `mdl_courcompaggrmeth_cou_ix` (`course`),
  KEY `mdl_courcompaggrmeth_cri_ix` (`criteriatype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Course completion aggregation methods for criteria';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_course_completion_aggr_methd`
--

LOCK TABLES `mdl_course_completion_aggr_methd` WRITE;
/*!40000 ALTER TABLE `mdl_course_completion_aggr_methd` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_course_completion_aggr_methd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_course_completion_crit_compl`
--

DROP TABLE IF EXISTS `mdl_course_completion_crit_compl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_course_completion_crit_compl` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `criteriaid` bigint(10) NOT NULL DEFAULT 0,
  `gradefinal` decimal(10,5) DEFAULT NULL,
  `unenroled` bigint(10) DEFAULT NULL,
  `timecompleted` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_courcompcritcomp_useco_uix` (`userid`,`course`,`criteriaid`),
  KEY `mdl_courcompcritcomp_use_ix` (`userid`),
  KEY `mdl_courcompcritcomp_cou_ix` (`course`),
  KEY `mdl_courcompcritcomp_cri_ix` (`criteriaid`),
  KEY `mdl_courcompcritcomp_tim_ix` (`timecompleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Course completion user records';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_course_completion_crit_compl`
--

LOCK TABLES `mdl_course_completion_crit_compl` WRITE;
/*!40000 ALTER TABLE `mdl_course_completion_crit_compl` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_course_completion_crit_compl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_course_completion_criteria`
--

DROP TABLE IF EXISTS `mdl_course_completion_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_course_completion_criteria` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `criteriatype` bigint(10) NOT NULL DEFAULT 0,
  `module` varchar(100) DEFAULT NULL,
  `moduleinstance` bigint(10) DEFAULT NULL,
  `courseinstance` bigint(10) DEFAULT NULL,
  `enrolperiod` bigint(10) DEFAULT NULL,
  `timeend` bigint(10) DEFAULT NULL,
  `gradepass` decimal(10,5) DEFAULT NULL,
  `role` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_courcompcrit_cou_ix` (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Course completion criteria';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_course_completion_criteria`
--

LOCK TABLES `mdl_course_completion_criteria` WRITE;
/*!40000 ALTER TABLE `mdl_course_completion_criteria` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_course_completion_criteria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_course_completion_defaults`
--

DROP TABLE IF EXISTS `mdl_course_completion_defaults`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_course_completion_defaults` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL,
  `module` bigint(10) NOT NULL,
  `completion` tinyint(1) NOT NULL DEFAULT 0,
  `completionview` tinyint(1) NOT NULL DEFAULT 0,
  `completionusegrade` tinyint(1) NOT NULL DEFAULT 0,
  `completionpassgrade` tinyint(1) NOT NULL DEFAULT 0,
  `completionexpected` bigint(10) NOT NULL DEFAULT 0,
  `customrules` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_courcompdefa_coumod_uix` (`course`,`module`),
  KEY `mdl_courcompdefa_mod_ix` (`module`),
  KEY `mdl_courcompdefa_cou_ix` (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Default settings for activities completion';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_course_completion_defaults`
--

LOCK TABLES `mdl_course_completion_defaults` WRITE;
/*!40000 ALTER TABLE `mdl_course_completion_defaults` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_course_completion_defaults` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_course_completions`
--

DROP TABLE IF EXISTS `mdl_course_completions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_course_completions` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `timeenrolled` bigint(10) NOT NULL DEFAULT 0,
  `timestarted` bigint(10) NOT NULL DEFAULT 0,
  `timecompleted` bigint(10) DEFAULT NULL,
  `reaggregate` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_courcomp_usecou_uix` (`userid`,`course`),
  KEY `mdl_courcomp_use_ix` (`userid`),
  KEY `mdl_courcomp_cou_ix` (`course`),
  KEY `mdl_courcomp_tim_ix` (`timecompleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Course completion records';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_course_completions`
--

LOCK TABLES `mdl_course_completions` WRITE;
/*!40000 ALTER TABLE `mdl_course_completions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_course_completions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_course_format_options`
--

DROP TABLE IF EXISTS `mdl_course_format_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_course_format_options` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `courseid` bigint(10) NOT NULL,
  `format` varchar(21) NOT NULL DEFAULT '',
  `sectionid` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_courformopti_couforsec_uix` (`courseid`,`format`,`sectionid`,`name`),
  KEY `mdl_courformopti_cou_ix` (`courseid`)
) ENGINE=InnoDB AUTO_INCREMENT=316 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores format-specific options for the course or course sect';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_course_format_options`
--

LOCK TABLES `mdl_course_format_options` WRITE;
/*!40000 ALTER TABLE `mdl_course_format_options` DISABLE KEYS */;
INSERT INTO `mdl_course_format_options` VALUES (1,1,'site',0,'numsections','1'),(214,87,'topics',0,'hiddensections','1'),(215,87,'topics',0,'coursedisplay','0'),(216,88,'topics',0,'hiddensections','1'),(217,88,'topics',0,'coursedisplay','0'),(218,89,'topics',0,'hiddensections','1'),(219,89,'topics',0,'coursedisplay','0'),(220,90,'topics',0,'hiddensections','1'),(221,90,'topics',0,'coursedisplay','0'),(222,91,'topics',0,'hiddensections','1'),(223,91,'topics',0,'coursedisplay','0'),(224,92,'topics',0,'hiddensections','1'),(225,92,'topics',0,'coursedisplay','0'),(226,93,'topics',0,'hiddensections','1'),(227,93,'topics',0,'coursedisplay','0'),(228,94,'topics',0,'hiddensections','1'),(229,94,'topics',0,'coursedisplay','0'),(230,95,'topics',0,'hiddensections','1'),(231,95,'topics',0,'coursedisplay','0'),(232,96,'topics',0,'hiddensections','1'),(233,96,'topics',0,'coursedisplay','0'),(234,97,'topics',0,'hiddensections','1'),(235,97,'topics',0,'coursedisplay','0'),(236,98,'topics',0,'hiddensections','1'),(237,98,'topics',0,'coursedisplay','0'),(238,99,'topics',0,'hiddensections','1'),(239,99,'topics',0,'coursedisplay','0'),(240,100,'topics',0,'hiddensections','1'),(241,100,'topics',0,'coursedisplay','0'),(256,108,'topics',0,'hiddensections','1'),(257,108,'topics',0,'coursedisplay','0'),(258,109,'topics',0,'hiddensections','1'),(259,109,'topics',0,'coursedisplay','0'),(260,110,'topics',0,'hiddensections','1'),(261,110,'topics',0,'coursedisplay','0'),(262,111,'topics',0,'hiddensections','1'),(263,111,'topics',0,'coursedisplay','0'),(264,112,'topics',0,'hiddensections','1'),(265,112,'topics',0,'coursedisplay','0'),(266,113,'topics',0,'hiddensections','1'),(267,113,'topics',0,'coursedisplay','0'),(268,114,'topics',0,'hiddensections','1'),(269,114,'topics',0,'coursedisplay','0'),(270,115,'topics',0,'hiddensections','1'),(271,115,'topics',0,'coursedisplay','0'),(272,116,'topics',0,'hiddensections','1'),(273,116,'topics',0,'coursedisplay','0'),(274,117,'topics',0,'hiddensections','1'),(275,117,'topics',0,'coursedisplay','0'),(276,118,'topics',0,'hiddensections','1'),(277,118,'topics',0,'coursedisplay','0'),(278,119,'topics',0,'hiddensections','1'),(279,119,'topics',0,'coursedisplay','0'),(280,120,'topics',0,'hiddensections','1'),(281,120,'topics',0,'coursedisplay','0'),(282,121,'topics',0,'hiddensections','1'),(283,121,'topics',0,'coursedisplay','0'),(284,122,'topics',0,'hiddensections','1'),(285,122,'topics',0,'coursedisplay','0'),(286,123,'topics',0,'hiddensections','1'),(287,123,'topics',0,'coursedisplay','0'),(288,124,'topics',0,'hiddensections','1'),(289,124,'topics',0,'coursedisplay','0'),(290,125,'topics',0,'hiddensections','1'),(291,125,'topics',0,'coursedisplay','0'),(292,126,'topics',0,'hiddensections','1'),(293,126,'topics',0,'coursedisplay','0'),(294,127,'topics',0,'hiddensections','1'),(295,127,'topics',0,'coursedisplay','0'),(296,128,'topics',0,'hiddensections','1'),(297,128,'topics',0,'coursedisplay','0'),(298,129,'topics',0,'hiddensections','1'),(299,129,'topics',0,'coursedisplay','0'),(300,130,'topics',0,'hiddensections','1'),(301,130,'topics',0,'coursedisplay','0'),(302,131,'topics',0,'hiddensections','1'),(303,131,'topics',0,'coursedisplay','0'),(304,132,'topics',0,'hiddensections','1'),(305,132,'topics',0,'coursedisplay','0'),(306,133,'topics',0,'hiddensections','1'),(307,133,'topics',0,'coursedisplay','0'),(308,134,'topics',0,'hiddensections','1'),(309,134,'topics',0,'coursedisplay','0'),(310,135,'topics',0,'hiddensections','1'),(311,135,'topics',0,'coursedisplay','0'),(312,136,'topics',0,'hiddensections','1'),(313,136,'topics',0,'coursedisplay','0'),(314,137,'topics',0,'hiddensections','1'),(315,137,'topics',0,'coursedisplay','0');
/*!40000 ALTER TABLE `mdl_course_format_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_course_modules`
--

DROP TABLE IF EXISTS `mdl_course_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_course_modules` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `module` bigint(10) NOT NULL DEFAULT 0,
  `instance` bigint(10) NOT NULL DEFAULT 0,
  `section` bigint(10) NOT NULL DEFAULT 0,
  `idnumber` varchar(100) DEFAULT NULL,
  `added` bigint(10) NOT NULL DEFAULT 0,
  `score` smallint(4) NOT NULL DEFAULT 0,
  `indent` mediumint(5) NOT NULL DEFAULT 0,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `visibleoncoursepage` tinyint(1) NOT NULL DEFAULT 1,
  `visibleold` tinyint(1) NOT NULL DEFAULT 1,
  `groupmode` smallint(4) NOT NULL DEFAULT 0,
  `groupingid` bigint(10) NOT NULL DEFAULT 0,
  `completion` tinyint(1) NOT NULL DEFAULT 0,
  `completiongradeitemnumber` bigint(10) DEFAULT NULL,
  `completionview` tinyint(1) NOT NULL DEFAULT 0,
  `completionexpected` bigint(10) NOT NULL DEFAULT 0,
  `completionpassgrade` tinyint(1) NOT NULL DEFAULT 0,
  `showdescription` tinyint(1) NOT NULL DEFAULT 0,
  `availability` longtext DEFAULT NULL,
  `deletioninprogress` tinyint(1) NOT NULL DEFAULT 0,
  `downloadcontent` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `mdl_courmodu_vis_ix` (`visible`),
  KEY `mdl_courmodu_cou_ix` (`course`),
  KEY `mdl_courmodu_mod_ix` (`module`),
  KEY `mdl_courmodu_ins_ix` (`instance`),
  KEY `mdl_courmodu_idncou_ix` (`idnumber`,`course`),
  KEY `mdl_courmodu_gro_ix` (`groupingid`)
) ENGINE=InnoDB AUTO_INCREMENT=139 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='course_modules table retrofitted from MySQL';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_course_modules`
--

LOCK TABLES `mdl_course_modules` WRITE;
/*!40000 ALTER TABLE `mdl_course_modules` DISABLE KEYS */;
INSERT INTO `mdl_course_modules` VALUES (87,87,10,86,679,NULL,1756529935,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(88,88,10,87,690,NULL,1756529939,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(89,89,10,88,701,NULL,1756529943,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(90,90,10,89,712,NULL,1756529946,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(91,91,10,90,723,NULL,1756529951,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(92,92,10,91,734,NULL,1756529956,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(93,93,10,92,745,NULL,1756529959,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(94,94,10,93,756,NULL,1756554545,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(95,95,10,94,767,NULL,1756554549,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(96,96,10,95,778,NULL,1756554554,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(97,97,10,96,789,NULL,1756554556,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(98,98,10,97,800,NULL,1756554562,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(99,99,10,98,811,NULL,1756554565,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(100,100,10,99,822,NULL,1756554569,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(108,108,10,107,910,NULL,1756555433,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(109,109,10,108,921,NULL,1756555436,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(110,110,10,109,932,NULL,1756555440,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(111,111,10,110,943,NULL,1756555444,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(112,112,10,111,954,NULL,1756555449,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(113,113,10,112,965,NULL,1756555454,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(114,114,10,113,976,NULL,1756555456,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(115,115,10,114,987,NULL,1756555630,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(116,116,10,115,998,NULL,1756555633,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(117,117,10,116,1009,NULL,1756555636,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(118,118,10,117,1020,NULL,1756555639,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(119,119,10,118,1031,NULL,1756555643,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(120,120,10,119,1042,NULL,1756555648,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(121,121,10,120,1053,NULL,1756555652,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(122,122,10,121,1064,NULL,1756555895,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(123,123,10,122,1075,NULL,1756555899,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(124,124,10,123,1086,NULL,1756555903,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(125,125,10,124,1097,NULL,1756555906,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(126,126,10,125,1108,NULL,1756555911,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(127,127,10,126,1119,NULL,1756555915,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(128,128,10,127,1130,NULL,1756555918,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(129,129,10,128,1141,NULL,1756555924,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(130,130,10,129,1152,NULL,1756556257,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(131,131,10,130,1163,NULL,1756556262,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(132,132,10,131,1174,NULL,1756556266,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(133,133,10,132,1185,NULL,1756556269,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(134,134,10,133,1196,NULL,1756556275,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(135,135,10,134,1207,NULL,1756556281,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(136,136,10,135,1218,NULL,1756556286,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(137,137,10,136,1229,NULL,1756556291,0,0,1,1,1,0,0,0,NULL,0,0,0,0,NULL,0,1),(138,88,19,1,690,'',1756561329,0,0,1,1,1,0,0,1,NULL,0,0,0,0,NULL,0,1);
/*!40000 ALTER TABLE `mdl_course_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_course_modules_completion`
--

DROP TABLE IF EXISTS `mdl_course_modules_completion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_course_modules_completion` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `coursemoduleid` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `completionstate` tinyint(1) NOT NULL,
  `viewed` tinyint(1) DEFAULT NULL,
  `overrideby` bigint(10) DEFAULT NULL,
  `timemodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_courmoducomp_usecou_uix` (`userid`,`coursemoduleid`),
  KEY `mdl_courmoducomp_cou_ix` (`coursemoduleid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores the completion state (completed or not completed, etc';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_course_modules_completion`
--

LOCK TABLES `mdl_course_modules_completion` WRITE;
/*!40000 ALTER TABLE `mdl_course_modules_completion` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_course_modules_completion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_course_published`
--

DROP TABLE IF EXISTS `mdl_course_published`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_course_published` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `huburl` varchar(255) DEFAULT NULL,
  `courseid` bigint(10) NOT NULL,
  `timepublished` bigint(10) NOT NULL,
  `enrollable` tinyint(1) NOT NULL DEFAULT 1,
  `hubcourseid` bigint(10) NOT NULL,
  `status` tinyint(1) DEFAULT 0,
  `timechecked` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Information about how and when an local courses were publish';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_course_published`
--

LOCK TABLES `mdl_course_published` WRITE;
/*!40000 ALTER TABLE `mdl_course_published` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_course_published` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_course_request`
--

DROP TABLE IF EXISTS `mdl_course_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_course_request` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(254) NOT NULL DEFAULT '',
  `shortname` varchar(100) NOT NULL DEFAULT '',
  `summary` longtext NOT NULL,
  `summaryformat` tinyint(2) NOT NULL DEFAULT 0,
  `category` bigint(10) NOT NULL DEFAULT 0,
  `reason` longtext NOT NULL,
  `requester` bigint(10) NOT NULL DEFAULT 0,
  `password` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `mdl_courrequ_sho_ix` (`shortname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='course requests';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_course_request`
--

LOCK TABLES `mdl_course_request` WRITE;
/*!40000 ALTER TABLE `mdl_course_request` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_course_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_course_sections`
--

DROP TABLE IF EXISTS `mdl_course_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_course_sections` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `section` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) DEFAULT NULL,
  `summary` longtext DEFAULT NULL,
  `summaryformat` tinyint(2) NOT NULL DEFAULT 0,
  `sequence` longtext DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `availability` longtext DEFAULT NULL,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_coursect_cousec_uix` (`course`,`section`)
) ENGINE=InnoDB AUTO_INCREMENT=1240 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='to define the sections for each course';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_course_sections`
--

LOCK TABLES `mdl_course_sections` WRITE;
/*!40000 ALTER TABLE `mdl_course_sections` DISABLE KEYS */;
INSERT INTO `mdl_course_sections` VALUES (6,1,1,NULL,'',1,'',1,NULL,1756265022),(679,87,0,NULL,'',1,'87',1,NULL,1756529935),(680,87,1,NULL,'',1,'',1,NULL,1756529935),(681,87,2,NULL,'',1,'',1,NULL,1756529936),(682,87,3,NULL,'',1,'',1,NULL,1756529936),(683,87,4,NULL,'',1,'',1,NULL,1756529936),(684,87,5,NULL,'',1,'',1,NULL,1756529936),(685,87,6,NULL,'',1,'',1,NULL,1756529936),(686,87,7,NULL,'',1,'',1,NULL,1756529936),(687,87,8,NULL,'',1,'',1,NULL,1756529937),(688,87,9,NULL,'',1,'',1,NULL,1756529937),(689,87,10,NULL,'',1,'',1,NULL,1756529937),(690,88,0,NULL,'',1,'88,138',1,NULL,1756529939),(691,88,1,NULL,'',1,'',1,NULL,1756529939),(692,88,2,NULL,'',1,'',1,NULL,1756529939),(693,88,3,NULL,'',1,'',1,NULL,1756529939),(694,88,4,NULL,'',1,'',1,NULL,1756529940),(695,88,5,NULL,'',1,'',1,NULL,1756529940),(696,88,6,NULL,'',1,'',1,NULL,1756529941),(697,88,7,NULL,'',1,'',1,NULL,1756529941),(698,88,8,NULL,'',1,'',1,NULL,1756529941),(699,88,9,NULL,'',1,'',1,NULL,1756529941),(700,88,10,NULL,'',1,'',1,NULL,1756529942),(701,89,0,NULL,'',1,'89',1,NULL,1756529943),(702,89,1,NULL,'',1,'',1,NULL,1756529943),(703,89,2,NULL,'',1,'',1,NULL,1756529943),(704,89,3,NULL,'',1,'',1,NULL,1756529944),(705,89,4,NULL,'',1,'',1,NULL,1756529944),(706,89,5,NULL,'',1,'',1,NULL,1756529944),(707,89,6,NULL,'',1,'',1,NULL,1756529944),(708,89,7,NULL,'',1,'',1,NULL,1756529944),(709,89,8,NULL,'',1,'',1,NULL,1756529944),(710,89,9,NULL,'',1,'',1,NULL,1756529944),(711,89,10,NULL,'',1,'',1,NULL,1756529944),(712,90,0,NULL,'',1,'90',1,NULL,1756529946),(713,90,1,NULL,'',1,'',1,NULL,1756529946),(714,90,2,NULL,'',1,'',1,NULL,1756529947),(715,90,3,NULL,'',1,'',1,NULL,1756529947),(716,90,4,NULL,'',1,'',1,NULL,1756529949),(717,90,5,NULL,'',1,'',1,NULL,1756529949),(718,90,6,NULL,'',1,'',1,NULL,1756529949),(719,90,7,NULL,'',1,'',1,NULL,1756529949),(720,90,8,NULL,'',1,'',1,NULL,1756529949),(721,90,9,NULL,'',1,'',1,NULL,1756529949),(722,90,10,NULL,'',1,'',1,NULL,1756529950),(723,91,0,NULL,'',1,'91',1,NULL,1756529952),(724,91,1,NULL,'',1,'',1,NULL,1756529952),(725,91,2,NULL,'',1,'',1,NULL,1756529952),(726,91,3,NULL,'',1,'',1,NULL,1756529952),(727,91,4,NULL,'',1,'',1,NULL,1756529952),(728,91,5,NULL,'',1,'',1,NULL,1756529952),(729,91,6,NULL,'',1,'',1,NULL,1756529953),(730,91,7,NULL,'',1,'',1,NULL,1756529953),(731,91,8,NULL,'',1,'',1,NULL,1756529954),(732,91,9,NULL,'',1,'',1,NULL,1756529954),(733,91,10,NULL,'',1,'',1,NULL,1756529954),(734,92,0,NULL,'',1,'92',1,NULL,1756529956),(735,92,1,NULL,'',1,'',1,NULL,1756529957),(736,92,2,NULL,'',1,'',1,NULL,1756529957),(737,92,3,NULL,'',1,'',1,NULL,1756529957),(738,92,4,NULL,'',1,'',1,NULL,1756529957),(739,92,5,NULL,'',1,'',1,NULL,1756529958),(740,92,6,NULL,'',1,'',1,NULL,1756529958),(741,92,7,NULL,'',1,'',1,NULL,1756529958),(742,92,8,NULL,'',1,'',1,NULL,1756529958),(743,92,9,NULL,'',1,'',1,NULL,1756529958),(744,92,10,NULL,'',1,'',1,NULL,1756529958),(745,93,0,NULL,'',1,'93',1,NULL,1756529960),(746,93,1,NULL,'',1,'',1,NULL,1756529960),(747,93,2,NULL,'',1,'',1,NULL,1756529960),(748,93,3,NULL,'',1,'',1,NULL,1756529961),(749,93,4,NULL,'',1,'',1,NULL,1756529961),(750,93,5,NULL,'',1,'',1,NULL,1756529961),(751,93,6,NULL,'',1,'',1,NULL,1756529961),(752,93,7,NULL,'',1,'',1,NULL,1756529961),(753,93,8,NULL,'',1,'',1,NULL,1756529961),(754,93,9,NULL,'',1,'',1,NULL,1756529962),(755,93,10,NULL,'',1,'',1,NULL,1756529962),(756,94,0,NULL,'',1,'94',1,NULL,1756554546),(757,94,1,NULL,'',1,'',1,NULL,1756554546),(758,94,2,NULL,'',1,'',1,NULL,1756554546),(759,94,3,NULL,'',1,'',1,NULL,1756554546),(760,94,4,NULL,'',1,'',1,NULL,1756554546),(761,94,5,NULL,'',1,'',1,NULL,1756554547),(762,94,6,NULL,'',1,'',1,NULL,1756554547),(763,94,7,NULL,'',1,'',1,NULL,1756554547),(764,94,8,NULL,'',1,'',1,NULL,1756554547),(765,94,9,NULL,'',1,'',1,NULL,1756554547),(766,94,10,NULL,'',1,'',1,NULL,1756554547),(767,95,0,NULL,'',1,'95',1,NULL,1756554549),(768,95,1,NULL,'',1,'',1,NULL,1756554550),(769,95,2,NULL,'',1,'',1,NULL,1756554550),(770,95,3,NULL,'',1,'',1,NULL,1756554550),(771,95,4,NULL,'',1,'',1,NULL,1756554550),(772,95,5,NULL,'',1,'',1,NULL,1756554550),(773,95,6,NULL,'',1,'',1,NULL,1756554550),(774,95,7,NULL,'',1,'',1,NULL,1756554550),(775,95,8,NULL,'',1,'',1,NULL,1756554551),(776,95,9,NULL,'',1,'',1,NULL,1756554551),(777,95,10,NULL,'',1,'',1,NULL,1756554551),(778,96,0,NULL,'',1,'96',1,NULL,1756554554),(779,96,1,NULL,'',1,'',1,NULL,1756554554),(780,96,2,NULL,'',1,'',1,NULL,1756554554),(781,96,3,NULL,'',1,'',1,NULL,1756554554),(782,96,4,NULL,'',1,'',1,NULL,1756554555),(783,96,5,NULL,'',1,'',1,NULL,1756554555),(784,96,6,NULL,'',1,'',1,NULL,1756554555),(785,96,7,NULL,'',1,'',1,NULL,1756554555),(786,96,8,NULL,'',1,'',1,NULL,1756554555),(787,96,9,NULL,'',1,'',1,NULL,1756554555),(788,96,10,NULL,'',1,'',1,NULL,1756554555),(789,97,0,NULL,'',1,'97',1,NULL,1756554556),(790,97,1,NULL,'',1,'',1,NULL,1756554557),(791,97,2,NULL,'',1,'',1,NULL,1756554557),(792,97,3,NULL,'',1,'',1,NULL,1756554557),(793,97,4,NULL,'',1,'',1,NULL,1756554558),(794,97,5,NULL,'',1,'',1,NULL,1756554558),(795,97,6,NULL,'',1,'',1,NULL,1756554558),(796,97,7,NULL,'',1,'',1,NULL,1756554558),(797,97,8,NULL,'',1,'',1,NULL,1756554559),(798,97,9,NULL,'',1,'',1,NULL,1756554559),(799,97,10,NULL,'',1,'',1,NULL,1756554560),(800,98,0,NULL,'',1,'98',1,NULL,1756554562),(801,98,1,NULL,'',1,'',1,NULL,1756554562),(802,98,2,NULL,'',1,'',1,NULL,1756554563),(803,98,3,NULL,'',1,'',1,NULL,1756554563),(804,98,4,NULL,'',1,'',1,NULL,1756554563),(805,98,5,NULL,'',1,'',1,NULL,1756554563),(806,98,6,NULL,'',1,'',1,NULL,1756554563),(807,98,7,NULL,'',1,'',1,NULL,1756554563),(808,98,8,NULL,'',1,'',1,NULL,1756554563),(809,98,9,NULL,'',1,'',1,NULL,1756554563),(810,98,10,NULL,'',1,'',1,NULL,1756554563),(811,99,0,NULL,'',1,'99',1,NULL,1756554565),(812,99,1,NULL,'',1,'',1,NULL,1756554565),(813,99,2,NULL,'',1,'',1,NULL,1756554565),(814,99,3,NULL,'',1,'',1,NULL,1756554566),(815,99,4,NULL,'',1,'',1,NULL,1756554566),(816,99,5,NULL,'',1,'',1,NULL,1756554566),(817,99,6,NULL,'',1,'',1,NULL,1756554566),(818,99,7,NULL,'',1,'',1,NULL,1756554566),(819,99,8,NULL,'',1,'',1,NULL,1756554567),(820,99,9,NULL,'',1,'',1,NULL,1756554567),(821,99,10,NULL,'',1,'',1,NULL,1756554567),(822,100,0,NULL,'',1,'100',1,NULL,1756554569),(823,100,1,NULL,'',1,'',1,NULL,1756554570),(824,100,2,NULL,'',1,'',1,NULL,1756554570),(825,100,3,NULL,'',1,'',1,NULL,1756554570),(826,100,4,NULL,'',1,'',1,NULL,1756554570),(827,100,5,NULL,'',1,'',1,NULL,1756554570),(828,100,6,NULL,'',1,'',1,NULL,1756554570),(829,100,7,NULL,'',1,'',1,NULL,1756554570),(830,100,8,NULL,'',1,'',1,NULL,1756554570),(831,100,9,NULL,'',1,'',1,NULL,1756554571),(832,100,10,NULL,'',1,'',1,NULL,1756554571),(910,108,0,NULL,'',1,'108',1,NULL,1756555433),(911,108,1,NULL,'',1,'',1,NULL,1756555433),(912,108,2,NULL,'',1,'',1,NULL,1756555434),(913,108,3,NULL,'',1,'',1,NULL,1756555434),(914,108,4,NULL,'',1,'',1,NULL,1756555434),(915,108,5,NULL,'',1,'',1,NULL,1756555434),(916,108,6,NULL,'',1,'',1,NULL,1756555434),(917,108,7,NULL,'',1,'',1,NULL,1756555434),(918,108,8,NULL,'',1,'',1,NULL,1756555434),(919,108,9,NULL,'',1,'',1,NULL,1756555435),(920,108,10,NULL,'',1,'',1,NULL,1756555435),(921,109,0,NULL,'',1,'109',1,NULL,1756555436),(922,109,1,NULL,'',1,'',1,NULL,1756555437),(923,109,2,NULL,'',1,'',1,NULL,1756555437),(924,109,3,NULL,'',1,'',1,NULL,1756555437),(925,109,4,NULL,'',1,'',1,NULL,1756555437),(926,109,5,NULL,'',1,'',1,NULL,1756555438),(927,109,6,NULL,'',1,'',1,NULL,1756555438),(928,109,7,NULL,'',1,'',1,NULL,1756555438),(929,109,8,NULL,'',1,'',1,NULL,1756555438),(930,109,9,NULL,'',1,'',1,NULL,1756555438),(931,109,10,NULL,'',1,'',1,NULL,1756555439),(932,110,0,NULL,'',1,'110',1,NULL,1756555440),(933,110,1,NULL,'',1,'',1,NULL,1756555441),(934,110,2,NULL,'',1,'',1,NULL,1756555442),(935,110,3,NULL,'',1,'',1,NULL,1756555442),(936,110,4,NULL,'',1,'',1,NULL,1756555442),(937,110,5,NULL,'',1,'',1,NULL,1756555442),(938,110,6,NULL,'',1,'',1,NULL,1756555442),(939,110,7,NULL,'',1,'',1,NULL,1756555443),(940,110,8,NULL,'',1,'',1,NULL,1756555443),(941,110,9,NULL,'',1,'',1,NULL,1756555443),(942,110,10,NULL,'',1,'',1,NULL,1756555443),(943,111,0,NULL,'',1,'111',1,NULL,1756555444),(944,111,1,NULL,'',1,'',1,NULL,1756555445),(945,111,2,NULL,'',1,'',1,NULL,1756555445),(946,111,3,NULL,'',1,'',1,NULL,1756555445),(947,111,4,NULL,'',1,'',1,NULL,1756555447),(948,111,5,NULL,'',1,'',1,NULL,1756555447),(949,111,6,NULL,'',1,'',1,NULL,1756555447),(950,111,7,NULL,'',1,'',1,NULL,1756555447),(951,111,8,NULL,'',1,'',1,NULL,1756555447),(952,111,9,NULL,'',1,'',1,NULL,1756555448),(953,111,10,NULL,'',1,'',1,NULL,1756555448),(954,112,0,NULL,'',1,'112',1,NULL,1756555449),(955,112,1,NULL,'',1,'',1,NULL,1756555449),(956,112,2,NULL,'',1,'',1,NULL,1756555450),(957,112,3,NULL,'',1,'',1,NULL,1756555450),(958,112,4,NULL,'',1,'',1,NULL,1756555450),(959,112,5,NULL,'',1,'',1,NULL,1756555450),(960,112,6,NULL,'',1,'',1,NULL,1756555450),(961,112,7,NULL,'',1,'',1,NULL,1756555450),(962,112,8,NULL,'',1,'',1,NULL,1756555451),(963,112,9,NULL,'',1,'',1,NULL,1756555451),(964,112,10,NULL,'',1,'',1,NULL,1756555452),(965,113,0,NULL,'',1,'113',1,NULL,1756555454),(966,113,1,NULL,'',1,'',1,NULL,1756555454),(967,113,2,NULL,'',1,'',1,NULL,1756555454),(968,113,3,NULL,'',1,'',1,NULL,1756555455),(969,113,4,NULL,'',1,'',1,NULL,1756555455),(970,113,5,NULL,'',1,'',1,NULL,1756555455),(971,113,6,NULL,'',1,'',1,NULL,1756555455),(972,113,7,NULL,'',1,'',1,NULL,1756555455),(973,113,8,NULL,'',1,'',1,NULL,1756555455),(974,113,9,NULL,'',1,'',1,NULL,1756555455),(975,113,10,NULL,'',1,'',1,NULL,1756555455),(976,114,0,NULL,'',1,'114',1,NULL,1756555457),(977,114,1,NULL,'',1,'',1,NULL,1756555457),(978,114,2,NULL,'',1,'',1,NULL,1756555457),(979,114,3,NULL,'',1,'',1,NULL,1756555457),(980,114,4,NULL,'',1,'',1,NULL,1756555457),(981,114,5,NULL,'',1,'',1,NULL,1756555458),(982,114,6,NULL,'',1,'',1,NULL,1756555458),(983,114,7,NULL,'',1,'',1,NULL,1756555458),(984,114,8,NULL,'',1,'',1,NULL,1756555458),(985,114,9,NULL,'',1,'',1,NULL,1756555459),(986,114,10,NULL,'',1,'',1,NULL,1756555459),(987,115,0,NULL,'',1,'115',1,NULL,1756555631),(988,115,1,NULL,'',1,'',1,NULL,1756555631),(989,115,2,NULL,'',1,'',1,NULL,1756555631),(990,115,3,NULL,'',1,'',1,NULL,1756555631),(991,115,4,NULL,'',1,'',1,NULL,1756555632),(992,115,5,NULL,'',1,'',1,NULL,1756555632),(993,115,6,NULL,'',1,'',1,NULL,1756555632),(994,115,7,NULL,'',1,'',1,NULL,1756555632),(995,115,8,NULL,'',1,'',1,NULL,1756555632),(996,115,9,NULL,'',1,'',1,NULL,1756555632),(997,115,10,NULL,'',1,'',1,NULL,1756555632),(998,116,0,NULL,'',1,'116',1,NULL,1756555634),(999,116,1,NULL,'',1,'',1,NULL,1756555634),(1000,116,2,NULL,'',1,'',1,NULL,1756555634),(1001,116,3,NULL,'',1,'',1,NULL,1756555634),(1002,116,4,NULL,'',1,'',1,NULL,1756555634),(1003,116,5,NULL,'',1,'',1,NULL,1756555634),(1004,116,6,NULL,'',1,'',1,NULL,1756555635),(1005,116,7,NULL,'',1,'',1,NULL,1756555635),(1006,116,8,NULL,'',1,'',1,NULL,1756555635),(1007,116,9,NULL,'',1,'',1,NULL,1756555635),(1008,116,10,NULL,'',1,'',1,NULL,1756555635),(1009,117,0,NULL,'',1,'117',1,NULL,1756555638),(1010,117,1,NULL,'',1,'',1,NULL,1756555638),(1011,117,2,NULL,'',1,'',1,NULL,1756555638),(1012,117,3,NULL,'',1,'',1,NULL,1756555638),(1013,117,4,NULL,'',1,'',1,NULL,1756555638),(1014,117,5,NULL,'',1,'',1,NULL,1756555638),(1015,117,6,NULL,'',1,'',1,NULL,1756555638),(1016,117,7,NULL,'',1,'',1,NULL,1756555638),(1017,117,8,NULL,'',1,'',1,NULL,1756555638),(1018,117,9,NULL,'',1,'',1,NULL,1756555639),(1019,117,10,NULL,'',1,'',1,NULL,1756555639),(1020,118,0,NULL,'',1,'118',1,NULL,1756555640),(1021,118,1,NULL,'',1,'',1,NULL,1756555640),(1022,118,2,NULL,'',1,'',1,NULL,1756555640),(1023,118,3,NULL,'',1,'',1,NULL,1756555640),(1024,118,4,NULL,'',1,'',1,NULL,1756555641),(1025,118,5,NULL,'',1,'',1,NULL,1756555641),(1026,118,6,NULL,'',1,'',1,NULL,1756555641),(1027,118,7,NULL,'',1,'',1,NULL,1756555642),(1028,118,8,NULL,'',1,'',1,NULL,1756555642),(1029,118,9,NULL,'',1,'',1,NULL,1756555642),(1030,118,10,NULL,'',1,'',1,NULL,1756555642),(1031,119,0,NULL,'',1,'119',1,NULL,1756555643),(1032,119,1,NULL,'',1,'',1,NULL,1756555644),(1033,119,2,NULL,'',1,'',1,NULL,1756555644),(1034,119,3,NULL,'',1,'',1,NULL,1756555644),(1035,119,4,NULL,'',1,'',1,NULL,1756555644),(1036,119,5,NULL,'',1,'',1,NULL,1756555645),(1037,119,6,NULL,'',1,'',1,NULL,1756555645),(1038,119,7,NULL,'',1,'',1,NULL,1756555645),(1039,119,8,NULL,'',1,'',1,NULL,1756555645),(1040,119,9,NULL,'',1,'',1,NULL,1756555646),(1041,119,10,NULL,'',1,'',1,NULL,1756555646),(1042,120,0,NULL,'',1,'120',1,NULL,1756555648),(1043,120,1,NULL,'',1,'',1,NULL,1756555648),(1044,120,2,NULL,'',1,'',1,NULL,1756555648),(1045,120,3,NULL,'',1,'',1,NULL,1756555648),(1046,120,4,NULL,'',1,'',1,NULL,1756555649),(1047,120,5,NULL,'',1,'',1,NULL,1756555649),(1048,120,6,NULL,'',1,'',1,NULL,1756555649),(1049,120,7,NULL,'',1,'',1,NULL,1756555649),(1050,120,8,NULL,'',1,'',1,NULL,1756555649),(1051,120,9,NULL,'',1,'',1,NULL,1756555649),(1052,120,10,NULL,'',1,'',1,NULL,1756555650),(1053,121,0,NULL,'',1,'121',1,NULL,1756555652),(1054,121,1,NULL,'',1,'',1,NULL,1756555653),(1055,121,2,NULL,'',1,'',1,NULL,1756555653),(1056,121,3,NULL,'',1,'',1,NULL,1756555653),(1057,121,4,NULL,'',1,'',1,NULL,1756555653),(1058,121,5,NULL,'',1,'',1,NULL,1756555654),(1059,121,6,NULL,'',1,'',1,NULL,1756555654),(1060,121,7,NULL,'',1,'',1,NULL,1756555654),(1061,121,8,NULL,'',1,'',1,NULL,1756555654),(1062,121,9,NULL,'',1,'',1,NULL,1756555655),(1063,121,10,NULL,'',1,'',1,NULL,1756555655),(1064,122,0,NULL,'',1,'122',1,NULL,1756555895),(1065,122,1,NULL,'',1,'',1,NULL,1756555896),(1066,122,2,NULL,'',1,'',1,NULL,1756555896),(1067,122,3,NULL,'',1,'',1,NULL,1756555896),(1068,122,4,NULL,'',1,'',1,NULL,1756555896),(1069,122,5,NULL,'',1,'',1,NULL,1756555896),(1070,122,6,NULL,'',1,'',1,NULL,1756555896),(1071,122,7,NULL,'',1,'',1,NULL,1756555896),(1072,122,8,NULL,'',1,'',1,NULL,1756555896),(1073,122,9,NULL,'',1,'',1,NULL,1756555896),(1074,122,10,NULL,'',1,'',1,NULL,1756555897),(1075,123,0,NULL,'',1,'123',1,NULL,1756555899),(1076,123,1,NULL,'',1,'',1,NULL,1756555899),(1077,123,2,NULL,'',1,'',1,NULL,1756555900),(1078,123,3,NULL,'',1,'',1,NULL,1756555900),(1079,123,4,NULL,'',1,'',1,NULL,1756555900),(1080,123,5,NULL,'',1,'',1,NULL,1756555900),(1081,123,6,NULL,'',1,'',1,NULL,1756555900),(1082,123,7,NULL,'',1,'',1,NULL,1756555901),(1083,123,8,NULL,'',1,'',1,NULL,1756555901),(1084,123,9,NULL,'',1,'',1,NULL,1756555901),(1085,123,10,NULL,'',1,'',1,NULL,1756555901),(1086,124,0,NULL,'',1,'124',1,NULL,1756555903),(1087,124,1,NULL,'',1,'',1,NULL,1756555903),(1088,124,2,NULL,'',1,'',1,NULL,1756555903),(1089,124,3,NULL,'',1,'',1,NULL,1756555904),(1090,124,4,NULL,'',1,'',1,NULL,1756555904),(1091,124,5,NULL,'',1,'',1,NULL,1756555904),(1092,124,6,NULL,'',1,'',1,NULL,1756555904),(1093,124,7,NULL,'',1,'',1,NULL,1756555904),(1094,124,8,NULL,'',1,'',1,NULL,1756555904),(1095,124,9,NULL,'',1,'',1,NULL,1756555904),(1096,124,10,NULL,'',1,'',1,NULL,1756555905),(1097,125,0,NULL,'',1,'125',1,NULL,1756555906),(1098,125,1,NULL,'',1,'',1,NULL,1756555907),(1099,125,2,NULL,'',1,'',1,NULL,1756555907),(1100,125,3,NULL,'',1,'',1,NULL,1756555908),(1101,125,4,NULL,'',1,'',1,NULL,1756555909),(1102,125,5,NULL,'',1,'',1,NULL,1756555909),(1103,125,6,NULL,'',1,'',1,NULL,1756555909),(1104,125,7,NULL,'',1,'',1,NULL,1756555909),(1105,125,8,NULL,'',1,'',1,NULL,1756555909),(1106,125,9,NULL,'',1,'',1,NULL,1756555909),(1107,125,10,NULL,'',1,'',1,NULL,1756555909),(1108,126,0,NULL,'',1,'126',1,NULL,1756555911),(1109,126,1,NULL,'',1,'',1,NULL,1756555912),(1110,126,2,NULL,'',1,'',1,NULL,1756555912),(1111,126,3,NULL,'',1,'',1,NULL,1756555912),(1112,126,4,NULL,'',1,'',1,NULL,1756555912),(1113,126,5,NULL,'',1,'',1,NULL,1756555912),(1114,126,6,NULL,'',1,'',1,NULL,1756555912),(1115,126,7,NULL,'',1,'',1,NULL,1756555913),(1116,126,8,NULL,'',1,'',1,NULL,1756555913),(1117,126,9,NULL,'',1,'',1,NULL,1756555914),(1118,126,10,NULL,'',1,'',1,NULL,1756555914),(1119,127,0,NULL,'',1,'127',1,NULL,1756555915),(1120,127,1,NULL,'',1,'',1,NULL,1756555916),(1121,127,2,NULL,'',1,'',1,NULL,1756555916),(1122,127,3,NULL,'',1,'',1,NULL,1756555916),(1123,127,4,NULL,'',1,'',1,NULL,1756555916),(1124,127,5,NULL,'',1,'',1,NULL,1756555916),(1125,127,6,NULL,'',1,'',1,NULL,1756555917),(1126,127,7,NULL,'',1,'',1,NULL,1756555917),(1127,127,8,NULL,'',1,'',1,NULL,1756555917),(1128,127,9,NULL,'',1,'',1,NULL,1756555917),(1129,127,10,NULL,'',1,'',1,NULL,1756555917),(1130,128,0,NULL,'',1,'128',1,NULL,1756555919),(1131,128,1,NULL,'',1,'',1,NULL,1756555919),(1132,128,2,NULL,'',1,'',1,NULL,1756555919),(1133,128,3,NULL,'',1,'',1,NULL,1756555919),(1134,128,4,NULL,'',1,'',1,NULL,1756555920),(1135,128,5,NULL,'',1,'',1,NULL,1756555920),(1136,128,6,NULL,'',1,'',1,NULL,1756555920),(1137,128,7,NULL,'',1,'',1,NULL,1756555921),(1138,128,8,NULL,'',1,'',1,NULL,1756555921),(1139,128,9,NULL,'',1,'',1,NULL,1756555922),(1140,128,10,NULL,'',1,'',1,NULL,1756555923),(1141,129,0,NULL,'',1,'129',1,NULL,1756555924),(1142,129,1,NULL,'',1,'',1,NULL,1756555925),(1143,129,2,NULL,'',1,'',1,NULL,1756555925),(1144,129,3,NULL,'',1,'',1,NULL,1756555925),(1145,129,4,NULL,'',1,'',1,NULL,1756555925),(1146,129,5,NULL,'',1,'',1,NULL,1756555925),(1147,129,6,NULL,'',1,'',1,NULL,1756555925),(1148,129,7,NULL,'',1,'',1,NULL,1756555925),(1149,129,8,NULL,'',1,'',1,NULL,1756555925),(1150,129,9,NULL,'',1,'',1,NULL,1756555926),(1151,129,10,NULL,'',1,'',1,NULL,1756555926),(1152,130,0,NULL,'',1,'130',1,NULL,1756556258),(1153,130,1,NULL,'',1,'',1,NULL,1756556258),(1154,130,2,NULL,'',1,'',1,NULL,1756556259),(1155,130,3,NULL,'',1,'',1,NULL,1756556259),(1156,130,4,NULL,'',1,'',1,NULL,1756556259),(1157,130,5,NULL,'',1,'',1,NULL,1756556259),(1158,130,6,NULL,'',1,'',1,NULL,1756556259),(1159,130,7,NULL,'',1,'',1,NULL,1756556259),(1160,130,8,NULL,'',1,'',1,NULL,1756556259),(1161,130,9,NULL,'',1,'',1,NULL,1756556260),(1162,130,10,NULL,'',1,'',1,NULL,1756556260),(1163,131,0,NULL,'',1,'131',1,NULL,1756556262),(1164,131,1,NULL,'',1,'',1,NULL,1756556263),(1165,131,2,NULL,'',1,'',1,NULL,1756556263),(1166,131,3,NULL,'',1,'',1,NULL,1756556263),(1167,131,4,NULL,'',1,'',1,NULL,1756556263),(1168,131,5,NULL,'',1,'',1,NULL,1756556263),(1169,131,6,NULL,'',1,'',1,NULL,1756556263),(1170,131,7,NULL,'',1,'',1,NULL,1756556264),(1171,131,8,NULL,'',1,'',1,NULL,1756556264),(1172,131,9,NULL,'',1,'',1,NULL,1756556265),(1173,131,10,NULL,'',1,'',1,NULL,1756556265),(1174,132,0,NULL,'',1,'132',1,NULL,1756556266),(1175,132,1,NULL,'',1,'',1,NULL,1756556267),(1176,132,2,NULL,'',1,'',1,NULL,1756556267),(1177,132,3,NULL,'',1,'',1,NULL,1756556267),(1178,132,4,NULL,'',1,'',1,NULL,1756556267),(1179,132,5,NULL,'',1,'',1,NULL,1756556267),(1180,132,6,NULL,'',1,'',1,NULL,1756556267),(1181,132,7,NULL,'',1,'',1,NULL,1756556267),(1182,132,8,NULL,'',1,'',1,NULL,1756556268),(1183,132,9,NULL,'',1,'',1,NULL,1756556268),(1184,132,10,NULL,'',1,'',1,NULL,1756556268),(1185,133,0,NULL,'',1,'133',1,NULL,1756556270),(1186,133,1,NULL,'',1,'',1,NULL,1756556270),(1187,133,2,NULL,'',1,'',1,NULL,1756556270),(1188,133,3,NULL,'',1,'',1,NULL,1756556270),(1189,133,4,NULL,'',1,'',1,NULL,1756556272),(1190,133,5,NULL,'',1,'',1,NULL,1756556272),(1191,133,6,NULL,'',1,'',1,NULL,1756556273),(1192,133,7,NULL,'',1,'',1,NULL,1756556273),(1193,133,8,NULL,'',1,'',1,NULL,1756556273),(1194,133,9,NULL,'',1,'',1,NULL,1756556273),(1195,133,10,NULL,'',1,'',1,NULL,1756556273),(1196,134,0,NULL,'',1,'134',1,NULL,1756556275),(1197,134,1,NULL,'',1,'',1,NULL,1756556276),(1198,134,2,NULL,'',1,'',1,NULL,1756556276),(1199,134,3,NULL,'',1,'',1,NULL,1756556276),(1200,134,4,NULL,'',1,'',1,NULL,1756556276),(1201,134,5,NULL,'',1,'',1,NULL,1756556277),(1202,134,6,NULL,'',1,'',1,NULL,1756556277),(1203,134,7,NULL,'',1,'',1,NULL,1756556277),(1204,134,8,NULL,'',1,'',1,NULL,1756556277),(1205,134,9,NULL,'',1,'',1,NULL,1756556277),(1206,134,10,NULL,'',1,'',1,NULL,1756556277),(1207,135,0,NULL,'',1,'135',1,NULL,1756556281),(1208,135,1,NULL,'',1,'',1,NULL,1756556282),(1209,135,2,NULL,'',1,'',1,NULL,1756556282),(1210,135,3,NULL,'',1,'',1,NULL,1756556282),(1211,135,4,NULL,'',1,'',1,NULL,1756556282),(1212,135,5,NULL,'',1,'',1,NULL,1756556283),(1213,135,6,NULL,'',1,'',1,NULL,1756556283),(1214,135,7,NULL,'',1,'',1,NULL,1756556283),(1215,135,8,NULL,'',1,'',1,NULL,1756556283),(1216,135,9,NULL,'',1,'',1,NULL,1756556283),(1217,135,10,NULL,'',1,'',1,NULL,1756556284),(1218,136,0,NULL,'',1,'136',1,NULL,1756556287),(1219,136,1,NULL,'',1,'',1,NULL,1756556287),(1220,136,2,NULL,'',1,'',1,NULL,1756556287),(1221,136,3,NULL,'',1,'',1,NULL,1756556287),(1222,136,4,NULL,'',1,'',1,NULL,1756556287),(1223,136,5,NULL,'',1,'',1,NULL,1756556288),(1224,136,6,NULL,'',1,'',1,NULL,1756556288),(1225,136,7,NULL,'',1,'',1,NULL,1756556288),(1226,136,8,NULL,'',1,'',1,NULL,1756556288),(1227,136,9,NULL,'',1,'',1,NULL,1756556289),(1228,136,10,NULL,'',1,'',1,NULL,1756556289),(1229,137,0,NULL,'',1,'137',1,NULL,1756556291),(1230,137,1,NULL,'',1,'',1,NULL,1756556292),(1231,137,2,NULL,'',1,'',1,NULL,1756556292),(1232,137,3,NULL,'',1,'',1,NULL,1756556292),(1233,137,4,NULL,'',1,'',1,NULL,1756556293),(1234,137,5,NULL,'',1,'',1,NULL,1756556293),(1235,137,6,NULL,'',1,'',1,NULL,1756556293),(1236,137,7,NULL,'',1,'',1,NULL,1756556293),(1237,137,8,NULL,'',1,'',1,NULL,1756556293),(1238,137,9,NULL,'',1,'',1,NULL,1756556293),(1239,137,10,NULL,'',1,'',1,NULL,1756556294);
/*!40000 ALTER TABLE `mdl_course_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_customfield_category`
--

DROP TABLE IF EXISTS `mdl_customfield_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_customfield_category` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(400) NOT NULL DEFAULT '',
  `description` longtext DEFAULT NULL,
  `descriptionformat` bigint(10) DEFAULT NULL,
  `sortorder` bigint(10) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `component` varchar(100) NOT NULL DEFAULT '',
  `area` varchar(100) NOT NULL DEFAULT '',
  `itemid` bigint(10) NOT NULL DEFAULT 0,
  `contextid` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_custcate_comareitesor_ix` (`component`,`area`,`itemid`,`sortorder`),
  KEY `mdl_custcate_con_ix` (`contextid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='core_customfield category table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_customfield_category`
--

LOCK TABLES `mdl_customfield_category` WRITE;
/*!40000 ALTER TABLE `mdl_customfield_category` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_customfield_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_customfield_data`
--

DROP TABLE IF EXISTS `mdl_customfield_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_customfield_data` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `fieldid` bigint(10) NOT NULL,
  `instanceid` bigint(10) NOT NULL,
  `intvalue` bigint(10) DEFAULT NULL,
  `decvalue` decimal(10,5) DEFAULT NULL,
  `shortcharvalue` varchar(255) DEFAULT NULL,
  `charvalue` varchar(1333) DEFAULT NULL,
  `value` longtext NOT NULL,
  `valueformat` bigint(10) NOT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `contextid` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_custdata_insfie_uix` (`instanceid`,`fieldid`),
  KEY `mdl_custdata_fieint_ix` (`fieldid`,`intvalue`),
  KEY `mdl_custdata_fiesho_ix` (`fieldid`,`shortcharvalue`),
  KEY `mdl_custdata_fiedec_ix` (`fieldid`,`decvalue`),
  KEY `mdl_custdata_fie_ix` (`fieldid`),
  KEY `mdl_custdata_con_ix` (`contextid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='core_customfield data table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_customfield_data`
--

LOCK TABLES `mdl_customfield_data` WRITE;
/*!40000 ALTER TABLE `mdl_customfield_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_customfield_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_customfield_field`
--

DROP TABLE IF EXISTS `mdl_customfield_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_customfield_field` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `shortname` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(400) NOT NULL DEFAULT '',
  `type` varchar(100) NOT NULL DEFAULT '',
  `description` longtext DEFAULT NULL,
  `descriptionformat` bigint(10) DEFAULT NULL,
  `sortorder` bigint(10) DEFAULT NULL,
  `categoryid` bigint(10) DEFAULT NULL,
  `configdata` longtext DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_custfiel_catsor_ix` (`categoryid`,`sortorder`),
  KEY `mdl_custfiel_cat_ix` (`categoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='core_customfield field table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_customfield_field`
--

LOCK TABLES `mdl_customfield_field` WRITE;
/*!40000 ALTER TABLE `mdl_customfield_field` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_customfield_field` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_data`
--

DROP TABLE IF EXISTS `mdl_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_data` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `intro` longtext NOT NULL,
  `introformat` smallint(4) NOT NULL DEFAULT 0,
  `comments` smallint(4) NOT NULL DEFAULT 0,
  `timeavailablefrom` bigint(10) NOT NULL DEFAULT 0,
  `timeavailableto` bigint(10) NOT NULL DEFAULT 0,
  `timeviewfrom` bigint(10) NOT NULL DEFAULT 0,
  `timeviewto` bigint(10) NOT NULL DEFAULT 0,
  `requiredentries` int(8) NOT NULL DEFAULT 0,
  `requiredentriestoview` int(8) NOT NULL DEFAULT 0,
  `maxentries` int(8) NOT NULL DEFAULT 0,
  `rssarticles` smallint(4) NOT NULL DEFAULT 0,
  `singletemplate` longtext DEFAULT NULL,
  `listtemplate` longtext DEFAULT NULL,
  `listtemplateheader` longtext DEFAULT NULL,
  `listtemplatefooter` longtext DEFAULT NULL,
  `addtemplate` longtext DEFAULT NULL,
  `rsstemplate` longtext DEFAULT NULL,
  `rsstitletemplate` longtext DEFAULT NULL,
  `csstemplate` longtext DEFAULT NULL,
  `jstemplate` longtext DEFAULT NULL,
  `asearchtemplate` longtext DEFAULT NULL,
  `approval` smallint(4) NOT NULL DEFAULT 0,
  `manageapproved` smallint(4) NOT NULL DEFAULT 1,
  `scale` bigint(10) NOT NULL DEFAULT 0,
  `assessed` bigint(10) NOT NULL DEFAULT 0,
  `assesstimestart` bigint(10) NOT NULL DEFAULT 0,
  `assesstimefinish` bigint(10) NOT NULL DEFAULT 0,
  `defaultsort` bigint(10) NOT NULL DEFAULT 0,
  `defaultsortdir` smallint(4) NOT NULL DEFAULT 0,
  `editany` smallint(4) NOT NULL DEFAULT 0,
  `notification` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `config` longtext DEFAULT NULL,
  `completionentries` bigint(10) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_data_cou_ix` (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='all database activities';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_data`
--

LOCK TABLES `mdl_data` WRITE;
/*!40000 ALTER TABLE `mdl_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_data_content`
--

DROP TABLE IF EXISTS `mdl_data_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_data_content` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `fieldid` bigint(10) NOT NULL DEFAULT 0,
  `recordid` bigint(10) NOT NULL DEFAULT 0,
  `content` longtext DEFAULT NULL,
  `content1` longtext DEFAULT NULL,
  `content2` longtext DEFAULT NULL,
  `content3` longtext DEFAULT NULL,
  `content4` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_datacont_rec_ix` (`recordid`),
  KEY `mdl_datacont_fie_ix` (`fieldid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='the content introduced in each record/fields';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_data_content`
--

LOCK TABLES `mdl_data_content` WRITE;
/*!40000 ALTER TABLE `mdl_data_content` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_data_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_data_fields`
--

DROP TABLE IF EXISTS `mdl_data_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_data_fields` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `dataid` bigint(10) NOT NULL DEFAULT 0,
  `type` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` longtext NOT NULL,
  `required` tinyint(1) NOT NULL DEFAULT 0,
  `param1` longtext DEFAULT NULL,
  `param2` longtext DEFAULT NULL,
  `param3` longtext DEFAULT NULL,
  `param4` longtext DEFAULT NULL,
  `param5` longtext DEFAULT NULL,
  `param6` longtext DEFAULT NULL,
  `param7` longtext DEFAULT NULL,
  `param8` longtext DEFAULT NULL,
  `param9` longtext DEFAULT NULL,
  `param10` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_datafiel_typdat_ix` (`type`,`dataid`),
  KEY `mdl_datafiel_dat_ix` (`dataid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='every field available';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_data_fields`
--

LOCK TABLES `mdl_data_fields` WRITE;
/*!40000 ALTER TABLE `mdl_data_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_data_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_data_records`
--

DROP TABLE IF EXISTS `mdl_data_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_data_records` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `groupid` bigint(10) NOT NULL DEFAULT 0,
  `dataid` bigint(10) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `approved` smallint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_datareco_dat_ix` (`dataid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='every record introduced';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_data_records`
--

LOCK TABLES `mdl_data_records` WRITE;
/*!40000 ALTER TABLE `mdl_data_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_data_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_editor_atto_autosave`
--

DROP TABLE IF EXISTS `mdl_editor_atto_autosave`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_editor_atto_autosave` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `elementid` varchar(255) NOT NULL DEFAULT '',
  `contextid` bigint(10) NOT NULL,
  `pagehash` varchar(64) NOT NULL DEFAULT '',
  `userid` bigint(10) NOT NULL,
  `drafttext` longtext NOT NULL,
  `draftid` bigint(10) DEFAULT NULL,
  `pageinstance` varchar(64) NOT NULL DEFAULT '',
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_editattoauto_eleconuse_uix` (`elementid`,`contextid`,`userid`,`pagehash`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Draft text that is auto-saved every 5 seconds while an edito';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_editor_atto_autosave`
--

LOCK TABLES `mdl_editor_atto_autosave` WRITE;
/*!40000 ALTER TABLE `mdl_editor_atto_autosave` DISABLE KEYS */;
INSERT INTO `mdl_editor_atto_autosave` VALUES (4,'id_introeditor',11,'ad0dc8a6d48255ad4350df70c9c4dc6edeb3a8a0',2,'',626131268,'yui_3_17_2_1_1756264645182_59',1756264646),(9,'id_description_editor',15,'39752aa7d6fe615299f071acd19c9562f869f680',2,'',889871680,'yui_3_17_2_1_1756510666450_47',1756510667),(13,'id_description_editor',3,'d8cd6bd0c56caa62ebf631fa665405363a0b5fc3',2,'',880664072,'yui_3_17_2_1_1756510754133_47',1756510754),(17,'id_summary_editor',20,'08bb3538ad860a8ae9610a0fb2e1fa5774e489ad',2,'',225502183,'yui_3_17_2_1_1756513198546_210',1756513199),(18,'id_description_editor',3,'5744cde3cd9e5ed55db55d3fae407042ffba6d6c',2,'',509409154,'yui_3_17_2_1_1756515081593_47',1756515082),(28,'id_s_theme_moove_marketingcontent',1,'898b011dcd04234ce2f35e8798e89f2347aa9ffc',2,'Plataforma de Vallesol',NULL,'yui_3_17_2_1_1756516365385_52',1756516438),(29,'id_s_theme_moove_marketing1content',1,'8ca867f7ae870e8b238dce50ca68d4958908465e',2,'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.',NULL,'yui_3_17_2_1_1756516365385_345',1756516438),(30,'id_s_theme_moove_marketing2content',1,'8ca867f7ae870e8b238dce50ca68d4958908465e',2,'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.',NULL,'yui_3_17_2_1_1756516365385_627',1756516438),(31,'id_s_theme_moove_marketing3content',1,'8ca867f7ae870e8b238dce50ca68d4958908465e',2,'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.',NULL,'yui_3_17_2_1_1756516365385_909',1756516438),(32,'id_s_theme_moove_marketing4content',1,'8ca867f7ae870e8b238dce50ca68d4958908465e',2,'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.',NULL,'yui_3_17_2_1_1756516365385_1191',1756516438),(33,'id_s_theme_moove_numbersfrontpagecontent',1,'19bc0c0f2a7f74207c063bba5321f8cd1bb28541',2,'<h2>Con la confianza de m??s de 25,000 clientes satisfechos.</h2>\n                    <p>Con muchos bloques ??nicos usted puede f??cilmente construir <br class=\"d-none d-sm-block d-md-none d-xl-block\">\n                        una p??gina sin escribir c??digo. Construya su pr??ximo sitio web <br class=\"d-none d-sm-block d-md-none d-xl-block\">\n                        en pocos minutos.</p>',NULL,'yui_3_17_2_1_1756516365385_1473',1756516438),(57,'id_s_theme_moove_slidercap1',1,'2ef5a2eeb7ab04c3540a0b614dbc7a5240cf03ea',2,'',-1,'yui_3_17_2_1_1756526312425_80',1756526315),(63,'id_s__summary',1,'d698a94dd4342f2bd7def826b1ff26d912b2a68b',2,'',-1,'yui_3_17_2_1_1756523698613_47',1756523699),(64,'id_description',3,'ced68e54a7ab71c0e036a69c3c019b60a2e0e9b5',2,'',-1,'yui_3_17_2_1_1756521169780_47',1756521170),(66,'id_description_editor',3,'af6c8acdfa2e13ae999f42fd97d4f2f8b297da29',2,'',180925415,'yui_3_17_2_1_1756523443248_47',1756523443),(67,'id_description_editor',3,'54f5264ec62764b3d303ab3d97937bd9dd416956',2,'',267493176,'yui_3_17_2_1_1756523922389_48',1756523922),(72,'id_description_editor',1,'8bcb439222aa91e6047a2188228629a0922e98e8',2,'',271631729,'yui_3_17_2_1_1756524250453_48',1756524250),(74,'id_description_editor',14,'b5e1f44f2ada55184a1425da05f4f83f32d772fb',2,'',315778510,'yui_3_17_2_1_1756525848830_48',1756525850),(75,'id_summary_editor',2,'847712c3c8320d7e3809a60643abfc435e2901ac',2,'',604166486,'yui_3_17_2_1_1756524381061_58',1756524382),(76,'id_description_editor',18,'407f51268bfee8fbade000c44ed809066aba4ff9',2,'',677599815,'yui_3_17_2_1_1756526049932_48',1756526051),(77,'id_summary_editor',14,'13628eef43a0293574a83fd73e738e7065bc6534',2,'',439235491,'yui_3_17_2_1_1756526548607_212',1756526550),(86,'id_description_editor',208,'b5dba6f77578d52cbd1c9bc57c2c26a360109b08',2,'',274020558,'yui_3_17_2_1_1756529147246_48',1756529149),(91,'id_description_editor',207,'a686d49aca854afcf8f53c29b84d06dbda8a1d79',2,'',27181891,'yui_3_17_2_1_1756530021562_48',1756530022),(93,'id_description_editor',208,'20f6caa8dbec2f1a15293734f85fd3ab8141faf2',2,'',387269773,'yui_3_17_2_1_1756554391431_48',1756554391);
/*!40000 ALTER TABLE `mdl_editor_atto_autosave` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol`
--

DROP TABLE IF EXISTS `mdl_enrol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `enrol` varchar(20) NOT NULL DEFAULT '',
  `status` bigint(10) NOT NULL DEFAULT 0,
  `courseid` bigint(10) NOT NULL,
  `sortorder` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) DEFAULT NULL,
  `enrolperiod` bigint(10) DEFAULT 0,
  `enrolstartdate` bigint(10) DEFAULT 0,
  `enrolenddate` bigint(10) DEFAULT 0,
  `expirynotify` tinyint(1) DEFAULT 0,
  `expirythreshold` bigint(10) DEFAULT 0,
  `notifyall` tinyint(1) DEFAULT 0,
  `password` varchar(50) DEFAULT NULL,
  `cost` varchar(20) DEFAULT NULL,
  `currency` varchar(3) DEFAULT NULL,
  `roleid` bigint(10) DEFAULT 0,
  `customint1` bigint(10) DEFAULT NULL,
  `customint2` bigint(10) DEFAULT NULL,
  `customint3` bigint(10) DEFAULT NULL,
  `customint4` bigint(10) DEFAULT NULL,
  `customint5` bigint(10) DEFAULT NULL,
  `customint6` bigint(10) DEFAULT NULL,
  `customint7` bigint(10) DEFAULT NULL,
  `customint8` bigint(10) DEFAULT NULL,
  `customchar1` varchar(255) DEFAULT NULL,
  `customchar2` varchar(255) DEFAULT NULL,
  `customchar3` varchar(1333) DEFAULT NULL,
  `customdec1` decimal(12,7) DEFAULT NULL,
  `customdec2` decimal(12,7) DEFAULT NULL,
  `customtext1` longtext DEFAULT NULL,
  `customtext2` longtext DEFAULT NULL,
  `customtext3` longtext DEFAULT NULL,
  `customtext4` longtext DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_enro_enr_ix` (`enrol`),
  KEY `mdl_enro_cou_ix` (`courseid`)
) ENGINE=InnoDB AUTO_INCREMENT=409 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Instances of enrolment plugins used in courses, fields marke';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol`
--

LOCK TABLES `mdl_enrol` WRITE;
/*!40000 ALTER TABLE `mdl_enrol` DISABLE KEYS */;
INSERT INTO `mdl_enrol` VALUES (256,'manual',0,87,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529937,1756529937),(257,'guest',1,87,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529937,1756529937),(258,'self',1,87,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529937,1756529937),(259,'manual',0,88,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529942,1756529942),(260,'guest',1,88,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529942,1756529942),(261,'self',1,88,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529942,1756529942),(262,'manual',0,89,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529944,1756529944),(263,'guest',1,89,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529944,1756529944),(264,'self',1,89,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529944,1756529944),(265,'manual',0,90,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529950,1756529950),(266,'guest',1,90,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529950,1756529950),(267,'self',1,90,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529950,1756529950),(268,'manual',0,91,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529954,1756529954),(269,'guest',1,91,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529954,1756529954),(270,'self',1,91,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529954,1756529954),(271,'manual',0,92,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529959,1756529959),(272,'guest',1,92,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529959,1756529959),(273,'self',1,92,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529959,1756529959),(274,'manual',0,93,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529962,1756529962),(275,'guest',1,93,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529962,1756529962),(276,'self',1,93,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756529963,1756529963),(277,'manual',0,94,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554547,1756554547),(278,'guest',1,94,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554548,1756554548),(279,'self',1,94,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554548,1756554548),(280,'manual',0,95,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554551,1756554551),(281,'guest',1,95,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554551,1756554551),(282,'self',1,95,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554551,1756554551),(283,'manual',0,96,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554555,1756554555),(284,'guest',1,96,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554555,1756554555),(285,'self',1,96,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554555,1756554555),(286,'manual',0,97,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554560,1756554560),(287,'guest',1,97,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554560,1756554560),(288,'self',1,97,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554560,1756554560),(289,'manual',0,98,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554564,1756554564),(290,'guest',1,98,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554564,1756554564),(291,'self',1,98,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554564,1756554564),(292,'manual',0,99,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554567,1756554567),(293,'guest',1,99,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554567,1756554567),(294,'self',1,99,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554568,1756554568),(295,'manual',0,100,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554571,1756554571),(296,'guest',1,100,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554571,1756554571),(297,'self',1,100,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756554571,1756554571),(319,'manual',0,108,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555435,1756555435),(320,'guest',1,108,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555435,1756555435),(321,'self',1,108,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555435,1756555435),(322,'manual',0,109,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555439,1756555439),(323,'guest',1,109,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555439,1756555439),(324,'self',1,109,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555439,1756555439),(325,'manual',0,110,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555443,1756555443),(326,'guest',1,110,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555443,1756555443),(327,'self',1,110,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555443,1756555443),(328,'manual',0,111,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555448,1756555448),(329,'guest',1,111,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555448,1756555448),(330,'self',1,111,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555448,1756555448),(331,'manual',0,112,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555452,1756555452),(332,'guest',1,112,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555452,1756555452),(333,'self',1,112,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555452,1756555452),(334,'manual',0,113,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555455,1756555455),(335,'guest',1,113,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555455,1756555455),(336,'self',1,113,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555455,1756555455),(337,'manual',0,114,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555460,1756555460),(338,'guest',1,114,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555460,1756555460),(339,'self',1,114,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555460,1756555460),(340,'manual',0,115,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555632,1756555632),(341,'guest',1,115,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555632,1756555632),(342,'self',1,115,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555632,1756555632),(343,'manual',0,116,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555635,1756555635),(344,'guest',1,116,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555635,1756555635),(345,'self',1,116,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555635,1756555635),(346,'manual',0,117,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555639,1756555639),(347,'guest',1,117,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555639,1756555639),(348,'self',1,117,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555639,1756555639),(349,'manual',0,118,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555642,1756555642),(350,'guest',1,118,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555642,1756555642),(351,'self',1,118,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555642,1756555642),(352,'manual',0,119,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555646,1756555646),(353,'guest',1,119,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555647,1756555647),(354,'self',1,119,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555647,1756555647),(355,'manual',0,120,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555650,1756555650),(356,'guest',1,120,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555650,1756555650),(357,'self',1,120,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555650,1756555650),(358,'manual',0,121,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555655,1756555655),(359,'guest',1,121,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555655,1756555655),(360,'self',1,121,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555656,1756555656),(361,'manual',0,122,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555897,1756555897),(362,'guest',1,122,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555897,1756555897),(363,'self',1,122,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555897,1756555897),(364,'manual',0,123,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555901,1756555901),(365,'guest',1,123,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555901,1756555901),(366,'self',1,123,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555902,1756555902),(367,'manual',0,124,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555905,1756555905),(368,'guest',1,124,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555905,1756555905),(369,'self',1,124,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555905,1756555905),(370,'manual',0,125,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555909,1756555909),(371,'guest',1,125,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555909,1756555909),(372,'self',1,125,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555910,1756555910),(373,'manual',0,126,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555914,1756555914),(374,'guest',1,126,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555914,1756555914),(375,'self',1,126,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555914,1756555914),(376,'manual',0,127,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555917,1756555917),(377,'guest',1,127,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555917,1756555917),(378,'self',1,127,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555917,1756555917),(379,'manual',0,128,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555923,1756555923),(380,'guest',1,128,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555923,1756555923),(381,'self',1,128,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555923,1756555923),(382,'manual',0,129,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555926,1756555926),(383,'guest',1,129,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555926,1756555926),(384,'self',1,129,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756555926,1756555926),(385,'manual',0,130,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556260,1756556260),(386,'guest',1,130,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556260,1756556260),(387,'self',1,130,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556260,1756556260),(388,'manual',0,131,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556265,1756556265),(389,'guest',1,131,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556265,1756556265),(390,'self',1,131,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556265,1756556265),(391,'manual',0,132,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556268,1756556268),(392,'guest',1,132,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556268,1756556268),(393,'self',1,132,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556268,1756556268),(394,'manual',0,133,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556274,1756556274),(395,'guest',1,133,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556274,1756556274),(396,'self',1,133,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556274,1756556274),(397,'manual',0,134,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556278,1756556278),(398,'guest',1,134,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556278,1756556278),(399,'self',1,134,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556278,1756556278),(400,'manual',0,135,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556284,1756556284),(401,'guest',1,135,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556285,1756556285),(402,'self',1,135,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556285,1756556285),(403,'manual',0,136,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556290,1756556290),(404,'guest',1,136,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556290,1756556290),(405,'self',1,136,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556290,1756556290),(406,'manual',0,137,0,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556294,1756556294),(407,'guest',1,137,1,NULL,0,0,0,0,0,0,'',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556294,1756556294),(408,'self',1,137,2,NULL,0,0,0,0,86400,0,NULL,NULL,NULL,5,0,0,0,1,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1756556294,1756556294);
/*!40000 ALTER TABLE `mdl_enrol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_flatfile`
--

DROP TABLE IF EXISTS `mdl_enrol_flatfile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_flatfile` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `action` varchar(30) NOT NULL DEFAULT '',
  `roleid` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `courseid` bigint(10) NOT NULL,
  `timestart` bigint(10) NOT NULL DEFAULT 0,
  `timeend` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_enroflat_cou_ix` (`courseid`),
  KEY `mdl_enroflat_use_ix` (`userid`),
  KEY `mdl_enroflat_rol_ix` (`roleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='enrol_flatfile table retrofitted from MySQL';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_flatfile`
--

LOCK TABLES `mdl_enrol_flatfile` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_flatfile` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_flatfile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_lti_app_registration`
--

DROP TABLE IF EXISTS `mdl_enrol_lti_app_registration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_lti_app_registration` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `platformid` longtext DEFAULT NULL,
  `clientid` varchar(1333) DEFAULT NULL,
  `uniqueid` varchar(255) NOT NULL DEFAULT '',
  `platformclienthash` varchar(64) DEFAULT NULL,
  `platformuniqueidhash` varchar(64) DEFAULT NULL,
  `authenticationrequesturl` longtext DEFAULT NULL,
  `jwksurl` longtext DEFAULT NULL,
  `accesstokenurl` longtext DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_enroltiappregi_uni_uix` (`uniqueid`),
  UNIQUE KEY `mdl_enroltiappregi_pla_uix` (`platformclienthash`),
  UNIQUE KEY `mdl_enroltiappregi_pla2_uix` (`platformuniqueidhash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Details of each application that has been registered with th';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_lti_app_registration`
--

LOCK TABLES `mdl_enrol_lti_app_registration` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_lti_app_registration` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_lti_app_registration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_lti_context`
--

DROP TABLE IF EXISTS `mdl_enrol_lti_context`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_lti_context` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `contextid` varchar(255) NOT NULL DEFAULT '',
  `ltideploymentid` bigint(10) NOT NULL,
  `type` longtext DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_enrolticont_lticon_uix` (`ltideploymentid`,`contextid`),
  KEY `mdl_enrolticont_lti_ix` (`ltideploymentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Each row represents a context in the platform, where resourc';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_lti_context`
--

LOCK TABLES `mdl_enrol_lti_context` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_lti_context` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_lti_context` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_lti_deployment`
--

DROP TABLE IF EXISTS `mdl_enrol_lti_deployment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_lti_deployment` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `deploymentid` varchar(255) NOT NULL DEFAULT '',
  `platformid` bigint(10) NOT NULL,
  `legacyconsumerkey` varchar(255) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_enroltidepl_pladep_uix` (`platformid`,`deploymentid`),
  KEY `mdl_enroltidepl_pla_ix` (`platformid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Each row represents a deployment of a tool within a platform';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_lti_deployment`
--

LOCK TABLES `mdl_enrol_lti_deployment` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_lti_deployment` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_lti_deployment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_lti_lti2_consumer`
--

DROP TABLE IF EXISTS `mdl_enrol_lti_lti2_consumer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_lti_lti2_consumer` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `consumerkey256` varchar(255) NOT NULL DEFAULT '',
  `consumerkey` longtext DEFAULT NULL,
  `secret` varchar(1024) NOT NULL DEFAULT '',
  `ltiversion` varchar(10) DEFAULT NULL,
  `consumername` varchar(255) DEFAULT NULL,
  `consumerversion` varchar(255) DEFAULT NULL,
  `consumerguid` varchar(1024) DEFAULT NULL,
  `profile` longtext DEFAULT NULL,
  `toolproxy` longtext DEFAULT NULL,
  `settings` longtext DEFAULT NULL,
  `protected` tinyint(1) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `enablefrom` bigint(10) DEFAULT NULL,
  `enableuntil` bigint(10) DEFAULT NULL,
  `lastaccess` bigint(10) DEFAULT NULL,
  `created` bigint(10) NOT NULL,
  `updated` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_enroltilti2cons_con_uix` (`consumerkey256`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='LTI consumers interacting with moodle';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_lti_lti2_consumer`
--

LOCK TABLES `mdl_enrol_lti_lti2_consumer` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_lti_lti2_consumer` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_lti_lti2_consumer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_lti_lti2_context`
--

DROP TABLE IF EXISTS `mdl_enrol_lti_lti2_context`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_lti_lti2_context` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `consumerid` bigint(11) NOT NULL,
  `lticontextkey` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(100) DEFAULT NULL,
  `settings` longtext DEFAULT NULL,
  `created` bigint(10) NOT NULL,
  `updated` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_enroltilti2cont_con_ix` (`consumerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Information about a specific LTI contexts from the consumers';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_lti_lti2_context`
--

LOCK TABLES `mdl_enrol_lti_lti2_context` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_lti_lti2_context` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_lti_lti2_context` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_lti_lti2_nonce`
--

DROP TABLE IF EXISTS `mdl_enrol_lti_lti2_nonce`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_lti_lti2_nonce` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `consumerid` bigint(11) NOT NULL,
  `value` varchar(64) NOT NULL DEFAULT '',
  `expires` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_enroltilti2nonc_con_ix` (`consumerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Nonce used for authentication between moodle and a consumer';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_lti_lti2_nonce`
--

LOCK TABLES `mdl_enrol_lti_lti2_nonce` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_lti_lti2_nonce` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_lti_lti2_nonce` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_lti_lti2_resource_link`
--

DROP TABLE IF EXISTS `mdl_enrol_lti_lti2_resource_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_lti_lti2_resource_link` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `contextid` bigint(11) DEFAULT NULL,
  `consumerid` bigint(11) DEFAULT NULL,
  `ltiresourcelinkkey` varchar(255) NOT NULL DEFAULT '',
  `settings` longtext DEFAULT NULL,
  `primaryresourcelinkid` bigint(11) DEFAULT NULL,
  `shareapproved` tinyint(1) DEFAULT NULL,
  `created` bigint(10) NOT NULL,
  `updated` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_enroltilti2resolink_con_ix` (`contextid`),
  KEY `mdl_enroltilti2resolink_pri_ix` (`primaryresourcelinkid`),
  KEY `mdl_enroltilti2resolink_co2_ix` (`consumerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Link from the consumer to the tool';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_lti_lti2_resource_link`
--

LOCK TABLES `mdl_enrol_lti_lti2_resource_link` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_lti_lti2_resource_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_lti_lti2_resource_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_lti_lti2_share_key`
--

DROP TABLE IF EXISTS `mdl_enrol_lti_lti2_share_key`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_lti_lti2_share_key` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `sharekey` varchar(32) NOT NULL DEFAULT '',
  `resourcelinkid` bigint(11) NOT NULL,
  `autoapprove` tinyint(1) NOT NULL,
  `expires` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_enroltilti2sharkey_sha_uix` (`sharekey`),
  UNIQUE KEY `mdl_enroltilti2sharkey_res_uix` (`resourcelinkid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Resource link share key';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_lti_lti2_share_key`
--

LOCK TABLES `mdl_enrol_lti_lti2_share_key` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_lti_lti2_share_key` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_lti_lti2_share_key` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_lti_lti2_tool_proxy`
--

DROP TABLE IF EXISTS `mdl_enrol_lti_lti2_tool_proxy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_lti_lti2_tool_proxy` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `toolproxykey` varchar(32) NOT NULL DEFAULT '',
  `consumerid` bigint(11) NOT NULL,
  `toolproxy` longtext NOT NULL,
  `created` bigint(10) NOT NULL,
  `updated` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_enroltilti2toolprox_to_uix` (`toolproxykey`),
  KEY `mdl_enroltilti2toolprox_con_ix` (`consumerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='A tool proxy between moodle and a consumer';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_lti_lti2_tool_proxy`
--

LOCK TABLES `mdl_enrol_lti_lti2_tool_proxy` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_lti_lti2_tool_proxy` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_lti_lti2_tool_proxy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_lti_lti2_user_result`
--

DROP TABLE IF EXISTS `mdl_enrol_lti_lti2_user_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_lti_lti2_user_result` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `resourcelinkid` bigint(11) NOT NULL,
  `ltiuserkey` varchar(255) NOT NULL DEFAULT '',
  `ltiresultsourcedid` varchar(1024) NOT NULL DEFAULT '',
  `created` bigint(10) NOT NULL,
  `updated` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_enroltilti2userresu_res_ix` (`resourcelinkid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Results for each user for each resource link';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_lti_lti2_user_result`
--

LOCK TABLES `mdl_enrol_lti_lti2_user_result` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_lti_lti2_user_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_lti_lti2_user_result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_lti_resource_link`
--

DROP TABLE IF EXISTS `mdl_enrol_lti_resource_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_lti_resource_link` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `resourcelinkid` varchar(255) NOT NULL DEFAULT '',
  `ltideploymentid` bigint(10) NOT NULL,
  `resourceid` bigint(10) NOT NULL,
  `lticontextid` bigint(10) DEFAULT NULL,
  `lineitemsservice` varchar(1333) DEFAULT NULL,
  `lineitemservice` varchar(1333) DEFAULT NULL,
  `lineitemscope` varchar(255) DEFAULT NULL,
  `resultscope` varchar(255) DEFAULT NULL,
  `scorescope` varchar(255) DEFAULT NULL,
  `contextmembershipsurl` varchar(1333) DEFAULT NULL,
  `nrpsserviceversions` varchar(255) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_enroltiresolink_reslti_uix` (`resourcelinkid`,`ltideploymentid`),
  KEY `mdl_enroltiresolink_lti_ix` (`ltideploymentid`),
  KEY `mdl_enroltiresolink_lti2_ix` (`lticontextid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Each row represents a resource link for a platform and deplo';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_lti_resource_link`
--

LOCK TABLES `mdl_enrol_lti_resource_link` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_lti_resource_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_lti_resource_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_lti_tool_consumer_map`
--

DROP TABLE IF EXISTS `mdl_enrol_lti_tool_consumer_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_lti_tool_consumer_map` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `toolid` bigint(11) NOT NULL,
  `consumerid` bigint(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_enroltitoolconsmap_too_ix` (`toolid`),
  KEY `mdl_enroltitoolconsmap_con_ix` (`consumerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Table that maps the published tool to tool consumers.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_lti_tool_consumer_map`
--

LOCK TABLES `mdl_enrol_lti_tool_consumer_map` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_lti_tool_consumer_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_lti_tool_consumer_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_lti_tools`
--

DROP TABLE IF EXISTS `mdl_enrol_lti_tools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_lti_tools` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `enrolid` bigint(10) NOT NULL,
  `contextid` bigint(10) NOT NULL,
  `ltiversion` varchar(15) NOT NULL DEFAULT 'LTI-1p3',
  `institution` varchar(40) NOT NULL DEFAULT '',
  `lang` varchar(30) NOT NULL DEFAULT 'en',
  `timezone` varchar(100) NOT NULL DEFAULT '99',
  `maxenrolled` bigint(10) NOT NULL DEFAULT 0,
  `maildisplay` tinyint(2) NOT NULL DEFAULT 2,
  `city` varchar(120) NOT NULL DEFAULT '',
  `country` varchar(2) NOT NULL DEFAULT '',
  `gradesync` tinyint(1) NOT NULL DEFAULT 0,
  `gradesynccompletion` tinyint(1) NOT NULL DEFAULT 0,
  `membersync` tinyint(1) NOT NULL DEFAULT 0,
  `membersyncmode` tinyint(1) NOT NULL DEFAULT 0,
  `roleinstructor` bigint(10) NOT NULL,
  `rolelearner` bigint(10) NOT NULL,
  `secret` longtext DEFAULT NULL,
  `uuid` varchar(36) DEFAULT NULL,
  `provisioningmodelearner` tinyint(2) DEFAULT NULL,
  `provisioningmodeinstructor` tinyint(2) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_enroltitool_uui_uix` (`uuid`),
  KEY `mdl_enroltitool_enr_ix` (`enrolid`),
  KEY `mdl_enroltitool_con_ix` (`contextid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='List of tools provided to the remote system';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_lti_tools`
--

LOCK TABLES `mdl_enrol_lti_tools` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_lti_tools` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_lti_tools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_lti_user_resource_link`
--

DROP TABLE IF EXISTS `mdl_enrol_lti_user_resource_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_lti_user_resource_link` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `ltiuserid` bigint(10) NOT NULL,
  `resourcelinkid` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_enroltiuserresolink_lt_uix` (`ltiuserid`,`resourcelinkid`),
  KEY `mdl_enroltiuserresolink_lti_ix` (`ltiuserid`),
  KEY `mdl_enroltiuserresolink_res_ix` (`resourcelinkid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Join table mapping users to resource links as this is a many';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_lti_user_resource_link`
--

LOCK TABLES `mdl_enrol_lti_user_resource_link` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_lti_user_resource_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_lti_user_resource_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_lti_users`
--

DROP TABLE IF EXISTS `mdl_enrol_lti_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_lti_users` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL,
  `toolid` bigint(10) NOT NULL,
  `serviceurl` longtext DEFAULT NULL,
  `sourceid` longtext DEFAULT NULL,
  `ltideploymentid` bigint(10) DEFAULT NULL,
  `consumerkey` longtext DEFAULT NULL,
  `consumersecret` longtext DEFAULT NULL,
  `membershipsurl` longtext DEFAULT NULL,
  `membershipsid` longtext DEFAULT NULL,
  `lastgrade` decimal(10,5) DEFAULT NULL,
  `lastaccess` bigint(10) DEFAULT NULL,
  `timecreated` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_enroltiuser_use_ix` (`userid`),
  KEY `mdl_enroltiuser_too_ix` (`toolid`),
  KEY `mdl_enroltiuser_lti_ix` (`ltideploymentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='User access log and gradeback data';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_lti_users`
--

LOCK TABLES `mdl_enrol_lti_users` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_lti_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_lti_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_enrol_paypal`
--

DROP TABLE IF EXISTS `mdl_enrol_paypal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_enrol_paypal` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `business` varchar(255) NOT NULL DEFAULT '',
  `receiver_email` varchar(255) NOT NULL DEFAULT '',
  `receiver_id` varchar(255) NOT NULL DEFAULT '',
  `item_name` varchar(255) NOT NULL DEFAULT '',
  `courseid` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `instanceid` bigint(10) NOT NULL DEFAULT 0,
  `memo` varchar(255) NOT NULL DEFAULT '',
  `tax` varchar(255) NOT NULL DEFAULT '',
  `option_name1` varchar(255) NOT NULL DEFAULT '',
  `option_selection1_x` varchar(255) NOT NULL DEFAULT '',
  `option_name2` varchar(255) NOT NULL DEFAULT '',
  `option_selection2_x` varchar(255) NOT NULL DEFAULT '',
  `payment_status` varchar(255) NOT NULL DEFAULT '',
  `pending_reason` varchar(255) NOT NULL DEFAULT '',
  `reason_code` varchar(30) NOT NULL DEFAULT '',
  `txn_id` varchar(255) NOT NULL DEFAULT '',
  `parent_txn_id` varchar(255) NOT NULL DEFAULT '',
  `payment_type` varchar(30) NOT NULL DEFAULT '',
  `timeupdated` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_enropayp_bus_ix` (`business`),
  KEY `mdl_enropayp_rec_ix` (`receiver_email`),
  KEY `mdl_enropayp_cou_ix` (`courseid`),
  KEY `mdl_enropayp_use_ix` (`userid`),
  KEY `mdl_enropayp_ins_ix` (`instanceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Holds all known information about PayPal transactions';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_enrol_paypal`
--

LOCK TABLES `mdl_enrol_paypal` WRITE;
/*!40000 ALTER TABLE `mdl_enrol_paypal` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_enrol_paypal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_event`
--

DROP TABLE IF EXISTS `mdl_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_event` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` longtext NOT NULL,
  `description` longtext NOT NULL,
  `format` smallint(4) NOT NULL DEFAULT 0,
  `categoryid` bigint(10) NOT NULL DEFAULT 0,
  `courseid` bigint(10) NOT NULL DEFAULT 0,
  `groupid` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `repeatid` bigint(10) NOT NULL DEFAULT 0,
  `component` varchar(100) DEFAULT NULL,
  `modulename` varchar(20) NOT NULL DEFAULT '',
  `instance` bigint(10) NOT NULL DEFAULT 0,
  `type` smallint(4) NOT NULL DEFAULT 0,
  `eventtype` varchar(20) NOT NULL DEFAULT '',
  `timestart` bigint(10) NOT NULL DEFAULT 0,
  `timeduration` bigint(10) NOT NULL DEFAULT 0,
  `timesort` bigint(10) DEFAULT NULL,
  `visible` smallint(4) NOT NULL DEFAULT 1,
  `uuid` varchar(255) NOT NULL DEFAULT '',
  `sequence` bigint(10) NOT NULL DEFAULT 1,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `subscriptionid` bigint(10) DEFAULT NULL,
  `priority` bigint(10) DEFAULT NULL,
  `location` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_even_cou_ix` (`courseid`),
  KEY `mdl_even_use_ix` (`userid`),
  KEY `mdl_even_tim_ix` (`timestart`),
  KEY `mdl_even_tim2_ix` (`timeduration`),
  KEY `mdl_even_uui_ix` (`uuid`),
  KEY `mdl_even_typtim_ix` (`type`,`timesort`),
  KEY `mdl_even_grocoucatvisuse_ix` (`groupid`,`courseid`,`categoryid`,`visible`,`userid`),
  KEY `mdl_even_eve_ix` (`eventtype`),
  KEY `mdl_even_comeveins_ix` (`component`,`eventtype`,`instance`),
  KEY `mdl_even_modinseve_ix` (`modulename`,`instance`,`eventtype`),
  KEY `mdl_even_cat_ix` (`categoryid`),
  KEY `mdl_even_sub_ix` (`subscriptionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='For everything with a time associated to it';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_event`
--

LOCK TABLES `mdl_event` WRITE;
/*!40000 ALTER TABLE `mdl_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_event_subscriptions`
--

DROP TABLE IF EXISTS `mdl_event_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_event_subscriptions` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL DEFAULT '',
  `categoryid` bigint(10) NOT NULL DEFAULT 0,
  `courseid` bigint(10) NOT NULL DEFAULT 0,
  `groupid` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `eventtype` varchar(20) NOT NULL DEFAULT '',
  `pollinterval` bigint(10) NOT NULL DEFAULT 0,
  `lastupdated` bigint(10) DEFAULT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Tracks subscriptions to remote calendars.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_event_subscriptions`
--

LOCK TABLES `mdl_event_subscriptions` WRITE;
/*!40000 ALTER TABLE `mdl_event_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_event_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_events_handlers`
--

DROP TABLE IF EXISTS `mdl_events_handlers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_events_handlers` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `eventname` varchar(166) NOT NULL DEFAULT '',
  `component` varchar(166) NOT NULL DEFAULT '',
  `handlerfile` varchar(255) NOT NULL DEFAULT '',
  `handlerfunction` longtext DEFAULT NULL,
  `schedule` varchar(255) DEFAULT NULL,
  `status` bigint(10) NOT NULL DEFAULT 0,
  `internal` tinyint(2) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_evenhand_evecom_uix` (`eventname`,`component`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='This table is for storing which components requests what typ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_events_handlers`
--

LOCK TABLES `mdl_events_handlers` WRITE;
/*!40000 ALTER TABLE `mdl_events_handlers` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_events_handlers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_events_queue`
--

DROP TABLE IF EXISTS `mdl_events_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_events_queue` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `eventdata` longtext NOT NULL,
  `stackdump` longtext DEFAULT NULL,
  `userid` bigint(10) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_evenqueu_use_ix` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='This table is for storing queued events. It stores only one ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_events_queue`
--

LOCK TABLES `mdl_events_queue` WRITE;
/*!40000 ALTER TABLE `mdl_events_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_events_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_events_queue_handlers`
--

DROP TABLE IF EXISTS `mdl_events_queue_handlers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_events_queue_handlers` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `queuedeventid` bigint(10) NOT NULL,
  `handlerid` bigint(10) NOT NULL,
  `status` bigint(10) DEFAULT NULL,
  `errormessage` longtext DEFAULT NULL,
  `timemodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_evenqueuhand_que_ix` (`queuedeventid`),
  KEY `mdl_evenqueuhand_han_ix` (`handlerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='This is the list of queued handlers for processing. The even';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_events_queue_handlers`
--

LOCK TABLES `mdl_events_queue_handlers` WRITE;
/*!40000 ALTER TABLE `mdl_events_queue_handlers` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_events_queue_handlers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_external_functions`
--

DROP TABLE IF EXISTS `mdl_external_functions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_external_functions` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `classname` varchar(100) NOT NULL DEFAULT '',
  `methodname` varchar(100) NOT NULL DEFAULT '',
  `classpath` varchar(255) DEFAULT NULL,
  `component` varchar(100) NOT NULL DEFAULT '',
  `capabilities` varchar(255) DEFAULT NULL,
  `services` varchar(1333) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_extefunc_nam_uix` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=675 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='list of all external functions';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_external_functions`
--

LOCK TABLES `mdl_external_functions` WRITE;
/*!40000 ALTER TABLE `mdl_external_functions` DISABLE KEYS */;
INSERT INTO `mdl_external_functions` VALUES (1,'core_auth_confirm_user','core_auth_external','confirm_user',NULL,'moodle','',NULL),(2,'core_auth_request_password_reset','core_auth_external','request_password_reset',NULL,'moodle','',NULL),(3,'core_auth_is_minor','core_auth_external','is_minor',NULL,'moodle','',NULL),(4,'core_auth_is_age_digital_consent_verification_enabled','core_auth_external','is_age_digital_consent_verification_enabled',NULL,'moodle','',NULL),(5,'core_auth_resend_confirmation_email','core_auth_external','resend_confirmation_email',NULL,'moodle','',NULL),(6,'core_backup_get_async_backup_progress','core_backup_external','get_async_backup_progress','backup/externallib.php','moodle','',NULL),(7,'core_backup_get_async_backup_links_backup','core_backup_external','get_async_backup_links_backup','backup/externallib.php','moodle','',NULL),(8,'core_backup_get_async_backup_links_restore','core_backup_external','get_async_backup_links_restore','backup/externallib.php','moodle','',NULL),(9,'core_backup_get_copy_progress','core_backup_external','get_copy_progress','backup/externallib.php','moodle','',NULL),(10,'core_backup_submit_copy_form','core_backup_external','submit_copy_form','backup/externallib.php','moodle','',NULL),(11,'core_badges_get_user_badges','core_badges_external','get_user_badges',NULL,'moodle','moodle/badges:viewotherbadges','moodle_mobile_app'),(12,'core_blog_get_entries','core_blog\\external','get_entries',NULL,'moodle','','moodle_mobile_app'),(13,'core_blog_view_entries','core_blog\\external','view_entries',NULL,'moodle','','moodle_mobile_app'),(14,'core_calendar_get_calendar_monthly_view','core_calendar_external','get_calendar_monthly_view','calendar/externallib.php','moodle','','moodle_mobile_app'),(15,'core_calendar_get_calendar_day_view','core_calendar_external','get_calendar_day_view','calendar/externallib.php','moodle','','moodle_mobile_app'),(16,'core_calendar_get_calendar_upcoming_view','core_calendar_external','get_calendar_upcoming_view','calendar/externallib.php','moodle','','moodle_mobile_app'),(17,'core_calendar_update_event_start_day','core_calendar_external','update_event_start_day','calendar/externallib.php','moodle','moodle/calendar:manageentries, moodle/calendar:manageownentries, moodle/calendar:managegroupentries','moodle_mobile_app'),(18,'core_calendar_create_calendar_events','core_calendar_external','create_calendar_events','calendar/externallib.php','moodle','moodle/calendar:manageentries, moodle/calendar:manageownentries, moodle/calendar:managegroupentries','moodle_mobile_app'),(19,'core_calendar_delete_calendar_events','core_calendar_external','delete_calendar_events','calendar/externallib.php','moodle','moodle/calendar:manageentries, moodle/calendar:manageownentries, moodle/calendar:managegroupentries','moodle_mobile_app'),(20,'core_calendar_get_calendar_events','core_calendar_external','get_calendar_events','calendar/externallib.php','moodle','moodle/calendar:manageentries, moodle/calendar:manageownentries, moodle/calendar:managegroupentries','moodle_mobile_app'),(21,'core_calendar_get_action_events_by_timesort','core_calendar_external','get_calendar_action_events_by_timesort','calendar/externallib.php','moodle','moodle/calendar:manageentries, moodle/calendar:manageownentries, moodle/calendar:managegroupentries','moodle_mobile_app'),(22,'core_calendar_get_action_events_by_course','core_calendar_external','get_calendar_action_events_by_course','calendar/externallib.php','moodle','moodle/calendar:manageentries, moodle/calendar:manageownentries, moodle/calendar:managegroupentries','moodle_mobile_app'),(23,'core_calendar_get_action_events_by_courses','core_calendar_external','get_calendar_action_events_by_courses','calendar/externallib.php','moodle','moodle/calendar:manageentries, moodle/calendar:manageownentries, moodle/calendar:managegroupentries','moodle_mobile_app'),(24,'core_calendar_get_calendar_event_by_id','core_calendar_external','get_calendar_event_by_id','calendar/externallib.php','moodle','moodle/calendar:manageentries, moodle/calendar:manageownentries, moodle/calendar:managegroupentries','moodle_mobile_app'),(25,'core_calendar_submit_create_update_form','core_calendar_external','submit_create_update_form','calendar/externallib.php','moodle','moodle/calendar:manageentries, moodle/calendar:manageownentries, moodle/calendar:managegroupentries','moodle_mobile_app'),(26,'core_calendar_get_calendar_access_information','core_calendar_external','get_calendar_access_information','calendar/externallib.php','moodle','','moodle_mobile_app'),(27,'core_calendar_get_allowed_event_types','core_calendar_external','get_allowed_event_types','calendar/externallib.php','moodle','','moodle_mobile_app'),(28,'core_calendar_get_timestamps','core_calendar_external','get_timestamps','calendar/externallib.php','moodle','',NULL),(29,'core_calendar_get_calendar_export_token','core_calendar\\external\\export\\token','execute',NULL,'moodle','','moodle_mobile_app'),(30,'core_calendar_delete_subscription','core_calendar\\external\\subscription\\delete','execute',NULL,'moodle','',NULL),(31,'core_cohort_add_cohort_members','core_cohort_external','add_cohort_members','cohort/externallib.php','moodle','moodle/cohort:assign',NULL),(32,'core_cohort_create_cohorts','core_cohort_external','create_cohorts','cohort/externallib.php','moodle','moodle/cohort:manage',NULL),(33,'core_cohort_delete_cohort_members','core_cohort_external','delete_cohort_members','cohort/externallib.php','moodle','moodle/cohort:assign',NULL),(34,'core_cohort_delete_cohorts','core_cohort_external','delete_cohorts','cohort/externallib.php','moodle','moodle/cohort:manage',NULL),(35,'core_cohort_get_cohort_members','core_cohort_external','get_cohort_members','cohort/externallib.php','moodle','moodle/cohort:view',NULL),(36,'core_cohort_search_cohorts','core_cohort_external','search_cohorts','cohort/externallib.php','moodle','moodle/cohort:view',NULL),(37,'core_cohort_get_cohorts','core_cohort_external','get_cohorts','cohort/externallib.php','moodle','moodle/cohort:view',NULL),(38,'core_cohort_update_cohorts','core_cohort_external','update_cohorts','cohort/externallib.php','moodle','moodle/cohort:manage',NULL),(39,'core_comment_get_comments','core_comment_external','get_comments',NULL,'moodle','moodle/comment:view','moodle_mobile_app'),(40,'core_comment_add_comments','core_comment_external','add_comments',NULL,'moodle','','moodle_mobile_app'),(41,'core_comment_delete_comments','core_comment_external','delete_comments',NULL,'moodle','','moodle_mobile_app'),(42,'core_completion_get_activities_completion_status','core_completion_external','get_activities_completion_status',NULL,'moodle','','moodle_mobile_app'),(43,'core_completion_get_course_completion_status','core_completion_external','get_course_completion_status',NULL,'moodle','report/completion:view','moodle_mobile_app'),(44,'core_completion_mark_course_self_completed','core_completion_external','mark_course_self_completed',NULL,'moodle','','moodle_mobile_app'),(45,'core_completion_update_activity_completion_status_manually','core_completion_external','update_activity_completion_status_manually',NULL,'moodle','','moodle_mobile_app'),(46,'core_completion_override_activity_completion_status','core_completion_external','override_activity_completion_status',NULL,'moodle','moodle/course:overridecompletion',NULL),(47,'core_course_create_categories','core_course_external','create_categories','course/externallib.php','moodle','moodle/category:manage',NULL),(48,'core_course_create_courses','core_course_external','create_courses','course/externallib.php','moodle','moodle/course:create, moodle/course:visibility',NULL),(49,'core_course_delete_categories','core_course_external','delete_categories','course/externallib.php','moodle','moodle/category:manage',NULL),(50,'core_course_delete_courses','core_course_external','delete_courses','course/externallib.php','moodle','moodle/course:delete',NULL),(51,'core_course_delete_modules','core_course_external','delete_modules','course/externallib.php','moodle','moodle/course:manageactivities',NULL),(52,'core_course_duplicate_course','core_course_external','duplicate_course','course/externallib.php','moodle','moodle/backup:backupcourse, moodle/restore:restorecourse, moodle/course:create',NULL),(53,'core_course_get_categories','core_course_external','get_categories','course/externallib.php','moodle','moodle/category:viewhiddencategories','moodle_mobile_app'),(54,'core_course_get_contents','core_course_external','get_course_contents','course/externallib.php','moodle','moodle/course:update, moodle/course:viewhiddencourses','moodle_mobile_app'),(55,'core_course_get_course_module','core_course_external','get_course_module','course/externallib.php','moodle','','moodle_mobile_app'),(56,'core_course_get_course_module_by_instance','core_course_external','get_course_module_by_instance','course/externallib.php','moodle','','moodle_mobile_app'),(57,'core_course_get_module','core_course_external','get_module','course/externallib.php','moodle','',NULL),(58,'core_courseformat_get_state','core_courseformat\\external\\get_state','execute',NULL,'moodle','',NULL),(59,'core_courseformat_update_course','core_courseformat\\external\\update_course','execute',NULL,'moodle','moodle/course:sectionvisibility, moodle/course:activityvisibility',NULL),(60,'core_course_edit_module','core_course_external','edit_module','course/externallib.php','moodle','',NULL),(61,'core_course_edit_section','core_course_external','edit_section','course/externallib.php','moodle','',NULL),(62,'core_course_get_courses','core_course_external','get_courses','course/externallib.php','moodle','moodle/course:view, moodle/course:update, moodle/course:viewhiddencourses','moodle_mobile_app'),(63,'core_course_import_course','core_course_external','import_course','course/externallib.php','moodle','moodle/backup:backuptargetimport, moodle/restore:restoretargetimport',NULL),(64,'core_course_search_courses','core_course_external','search_courses','course/externallib.php','moodle','','moodle_mobile_app'),(65,'core_course_update_categories','core_course_external','update_categories','course/externallib.php','moodle','moodle/category:manage',NULL),(66,'core_course_update_courses','core_course_external','update_courses','course/externallib.php','moodle','moodle/course:update, moodle/course:changecategory, moodle/course:changefullname, moodle/course:changeshortname, moodle/course:changeidnumber, moodle/course:changesummary, moodle/course:visibility',NULL),(67,'core_course_view_course','core_course_external','view_course','course/externallib.php','moodle','','moodle_mobile_app'),(68,'core_course_get_user_navigation_options','core_course_external','get_user_navigation_options','course/externallib.php','moodle','','moodle_mobile_app'),(69,'core_course_get_user_administration_options','core_course_external','get_user_administration_options','course/externallib.php','moodle','','moodle_mobile_app'),(70,'core_course_get_courses_by_field','core_course_external','get_courses_by_field','course/externallib.php','moodle','','moodle_mobile_app'),(71,'core_course_check_updates','core_course_external','check_updates','course/externallib.php','moodle','','moodle_mobile_app'),(72,'core_course_get_updates_since','core_course_external','get_updates_since','course/externallib.php','moodle','','moodle_mobile_app'),(73,'core_course_get_enrolled_courses_by_timeline_classification','core_course_external','get_enrolled_courses_by_timeline_classification','course/externallib.php','moodle','','moodle_mobile_app'),(74,'core_course_get_enrolled_courses_with_action_events_by_timeline_classification','\\core_course\\external\\get_enrolled_courses_with_action_events_by_timeline_classification','execute',NULL,'moodle','','moodle_mobile_app'),(75,'core_course_get_recent_courses','core_course_external','get_recent_courses','course/externallib.php','moodle','','moodle_mobile_app'),(76,'core_course_set_favourite_courses','core_course_external','set_favourite_courses','course/externallib.php','moodle','','moodle_mobile_app'),(77,'core_course_get_enrolled_users_by_cmid','core_course_external','get_enrolled_users_by_cmid','course/externallib.php','moodle','',NULL),(78,'core_course_add_content_item_to_user_favourites','core_course_external','add_content_item_to_user_favourites','course/externallib.php','moodle','',NULL),(79,'core_course_remove_content_item_from_user_favourites','core_course_external','remove_content_item_from_user_favourites','course/externallib.php','moodle','',NULL),(80,'core_course_get_course_content_items','core_course_external','get_course_content_items','course/externallib.php','moodle','',NULL),(81,'core_course_get_activity_chooser_footer','core_course_external','get_activity_chooser_footer','course/externallib.php','moodle','',NULL),(82,'core_course_toggle_activity_recommendation','core_course_external','toggle_activity_recommendation','course/externallib.php','moodle','',NULL),(83,'core_enrol_get_course_enrolment_methods','core_enrol_external','get_course_enrolment_methods','enrol/externallib.php','moodle','','moodle_mobile_app'),(84,'core_enrol_get_enrolled_users','core_enrol_external','get_enrolled_users','enrol/externallib.php','moodle','moodle/user:viewdetails, moodle/user:viewhiddendetails, moodle/course:useremail, moodle/user:update, moodle/site:accessallgroups','moodle_mobile_app'),(85,'core_enrol_get_enrolled_users_with_capability','core_enrol_external','get_enrolled_users_with_capability','enrol/externallib.php','moodle','',NULL),(86,'core_enrol_get_potential_users','core_enrol_external','get_potential_users','enrol/externallib.php','moodle','moodle/course:enrolreview',NULL),(87,'core_enrol_search_users','core_enrol_external','search_users','enrol/externallib.php','moodle','moodle/course:viewparticipants','moodle_mobile_app'),(88,'core_enrol_get_users_courses','core_enrol_external','get_users_courses','enrol/externallib.php','moodle','moodle/course:viewparticipants','moodle_mobile_app'),(89,'core_enrol_submit_user_enrolment_form','core_enrol_external','submit_user_enrolment_form','enrol/externallib.php','moodle','',NULL),(90,'core_enrol_unenrol_user_enrolment','core_enrol_external','unenrol_user_enrolment','enrol/externallib.php','moodle','',NULL),(91,'core_fetch_notifications','core_external','fetch_notifications','lib/external/externallib.php','moodle','',NULL),(92,'core_session_touch','core\\session\\external','touch_session',NULL,'moodle','',NULL),(93,'core_session_time_remaining','core\\session\\external','time_remaining',NULL,'moodle','',NULL),(94,'core_files_get_files','core_files_external','get_files','files/externallib.php','moodle','','moodle_mobile_app'),(95,'core_files_upload','core_files_external','upload','files/externallib.php','moodle','',NULL),(96,'core_files_delete_draft_files','core_files\\external\\delete\\draft','execute',NULL,'moodle','','moodle_mobile_app'),(97,'core_files_get_unused_draft_itemid','core_files\\external\\get\\unused_draft','execute',NULL,'moodle','','moodle_mobile_app'),(98,'core_form_get_filetypes_browser_data','core_form\\external','get_filetypes_browser_data',NULL,'moodle','',NULL),(99,'core_form_dynamic_form','core_form\\external\\dynamic_form','execute',NULL,'moodle','',NULL),(100,'core_get_component_strings','core_external','get_component_strings','lib/external/externallib.php','moodle','','moodle_mobile_app'),(101,'core_get_fragment','core_external','get_fragment','lib/external/externallib.php','moodle','',NULL),(102,'core_get_string','core_external','get_string','lib/external/externallib.php','moodle','',NULL),(103,'core_get_strings','core_external','get_strings','lib/external/externallib.php','moodle','',NULL),(104,'core_get_user_dates','core_external','get_user_dates','lib/external/externallib.php','moodle','',NULL),(105,'core_grades_update_grades','core_grades_external','update_grades',NULL,'moodle','',NULL),(106,'core_grades_grader_gradingpanel_point_fetch','core_grades\\grades\\grader\\gradingpanel\\point\\external\\fetch','execute',NULL,'moodle','','moodle_mobile_app'),(107,'core_grades_grader_gradingpanel_point_store','core_grades\\grades\\grader\\gradingpanel\\point\\external\\store','execute',NULL,'moodle','','moodle_mobile_app'),(108,'core_grades_grader_gradingpanel_scale_fetch','core_grades\\grades\\grader\\gradingpanel\\scale\\external\\fetch','execute',NULL,'moodle','','moodle_mobile_app'),(109,'core_grades_grader_gradingpanel_scale_store','core_grades\\grades\\grader\\gradingpanel\\scale\\external\\store','execute',NULL,'moodle','','moodle_mobile_app'),(110,'core_grades_create_gradecategory','core_grades_external','create_gradecategory',NULL,'moodle','moodle/grade:manage',NULL),(111,'core_grades_create_gradecategories','core_grades\\external\\create_gradecategories','execute',NULL,'moodle','moodle/grade:manage',NULL),(112,'core_grading_get_definitions','core_grading_external','get_definitions',NULL,'moodle','',NULL),(113,'core_grading_get_gradingform_instances','core_grading_external','get_gradingform_instances',NULL,'moodle','',NULL),(114,'core_grading_save_definitions','core_grading_external','save_definitions',NULL,'moodle','',NULL),(115,'core_group_add_group_members','core_group_external','add_group_members','group/externallib.php','moodle','moodle/course:managegroups',NULL),(116,'core_group_assign_grouping','core_group_external','assign_grouping','group/externallib.php','moodle','',NULL),(117,'core_group_create_groupings','core_group_external','create_groupings','group/externallib.php','moodle','',NULL),(118,'core_group_create_groups','core_group_external','create_groups','group/externallib.php','moodle','moodle/course:managegroups',NULL),(119,'core_group_delete_group_members','core_group_external','delete_group_members','group/externallib.php','moodle','moodle/course:managegroups',NULL),(120,'core_group_delete_groupings','core_group_external','delete_groupings','group/externallib.php','moodle','',NULL),(121,'core_group_delete_groups','core_group_external','delete_groups','group/externallib.php','moodle','moodle/course:managegroups',NULL),(122,'core_group_get_activity_allowed_groups','core_group_external','get_activity_allowed_groups','group/externallib.php','moodle','','moodle_mobile_app'),(123,'core_group_get_activity_groupmode','core_group_external','get_activity_groupmode','group/externallib.php','moodle','','moodle_mobile_app'),(124,'core_group_get_course_groupings','core_group_external','get_course_groupings','group/externallib.php','moodle','','moodle_mobile_app'),(125,'core_group_get_course_groups','core_group_external','get_course_groups','group/externallib.php','moodle','moodle/course:managegroups','moodle_mobile_app'),(126,'core_group_get_course_user_groups','core_group_external','get_course_user_groups','group/externallib.php','moodle','moodle/course:managegroups','moodle_mobile_app'),(127,'core_group_get_group_members','core_group_external','get_group_members','group/externallib.php','moodle','moodle/course:managegroups',NULL),(128,'core_group_get_groupings','core_group_external','get_groupings','group/externallib.php','moodle','',NULL),(129,'core_group_get_groups','core_group_external','get_groups','group/externallib.php','moodle','moodle/course:managegroups',NULL),(130,'core_group_unassign_grouping','core_group_external','unassign_grouping','group/externallib.php','moodle','',NULL),(131,'core_group_update_groupings','core_group_external','update_groupings','group/externallib.php','moodle','',NULL),(132,'core_group_update_groups','core_group_external','update_groups','group/externallib.php','moodle','moodle/course:managegroups',NULL),(133,'core_message_mute_conversations','core_message_external','mute_conversations','message/externallib.php','moodle','','moodle_mobile_app'),(134,'core_message_unmute_conversations','core_message_external','unmute_conversations','message/externallib.php','moodle','','moodle_mobile_app'),(135,'core_message_block_user','core_message_external','block_user','message/externallib.php','moodle','','moodle_mobile_app'),(136,'core_message_get_contact_requests','core_message_external','get_contact_requests','message/externallib.php','moodle','','moodle_mobile_app'),(137,'core_message_create_contact_request','core_message_external','create_contact_request','message/externallib.php','moodle','','moodle_mobile_app'),(138,'core_message_confirm_contact_request','core_message_external','confirm_contact_request','message/externallib.php','moodle','','moodle_mobile_app'),(139,'core_message_decline_contact_request','core_message_external','decline_contact_request','message/externallib.php','moodle','','moodle_mobile_app'),(140,'core_message_get_received_contact_requests_count','core_message_external','get_received_contact_requests_count','message/externallib.php','moodle','','moodle_mobile_app'),(141,'core_message_delete_contacts','core_message_external','delete_contacts','message/externallib.php','moodle','','moodle_mobile_app'),(142,'core_message_delete_conversations_by_id','core_message_external','delete_conversations_by_id','message/externallib.php','moodle','moodle/site:deleteownmessage','moodle_mobile_app'),(143,'core_message_delete_message','core_message_external','delete_message','message/externallib.php','moodle','moodle/site:deleteownmessage','moodle_mobile_app'),(144,'core_message_get_blocked_users','core_message_external','get_blocked_users','message/externallib.php','moodle','','moodle_mobile_app'),(145,'core_message_data_for_messagearea_search_messages','core_message_external','data_for_messagearea_search_messages','message/externallib.php','moodle','','moodle_mobile_app'),(146,'core_message_message_search_users','core_message_external','message_search_users','message/externallib.php','moodle','','moodle_mobile_app'),(147,'core_message_get_user_contacts','core_message_external','get_user_contacts','message/externallib.php','moodle','','moodle_mobile_app'),(148,'core_message_get_conversations','core_message_external','get_conversations','message/externallib.php','moodle','','moodle_mobile_app'),(149,'core_message_get_conversation','core_message_external','get_conversation','message/externallib.php','moodle','','moodle_mobile_app'),(150,'core_message_get_conversation_between_users','core_message_external','get_conversation_between_users','message/externallib.php','moodle','','moodle_mobile_app'),(151,'core_message_get_self_conversation','core_message_external','get_self_conversation','message/externallib.php','moodle','','moodle_mobile_app'),(152,'core_message_get_messages','core_message_external','get_messages','message/externallib.php','moodle','','moodle_mobile_app'),(153,'core_message_get_conversation_counts','core_message_external','get_conversation_counts','message/externallib.php','moodle','','moodle_mobile_app'),(154,'core_message_get_unread_conversation_counts','core_message_external','get_unread_conversation_counts','message/externallib.php','moodle','','moodle_mobile_app'),(155,'core_message_get_conversation_members','core_message_external','get_conversation_members','message/externallib.php','moodle','','moodle_mobile_app'),(156,'core_message_get_member_info','core_message_external','get_member_info','message/externallib.php','moodle','','moodle_mobile_app'),(157,'core_message_get_unread_conversations_count','core_message_external','get_unread_conversations_count','message/externallib.php','moodle','','moodle_mobile_app'),(158,'core_message_mark_all_notifications_as_read','core_message_external','mark_all_notifications_as_read','message/externallib.php','moodle','','moodle_mobile_app'),(159,'core_message_mark_all_conversation_messages_as_read','core_message_external','mark_all_conversation_messages_as_read','message/externallib.php','moodle','','moodle_mobile_app'),(160,'core_message_mark_message_read','core_message_external','mark_message_read','message/externallib.php','moodle','','moodle_mobile_app'),(161,'core_message_mark_notification_read','core_message_external','mark_notification_read','message/externallib.php','moodle','','moodle_mobile_app'),(162,'core_message_message_processor_config_form','core_message_external','message_processor_config_form','message/externallib.php','moodle','','moodle_mobile_app'),(163,'core_message_get_message_processor','core_message_external','get_message_processor','message/externallib.php','moodle','',NULL),(164,'core_message_search_contacts','core_message_external','search_contacts','message/externallib.php','moodle','','moodle_mobile_app'),(165,'core_message_send_instant_messages','core_message_external','send_instant_messages','message/externallib.php','moodle','moodle/site:sendmessage','moodle_mobile_app'),(166,'core_message_send_messages_to_conversation','core_message_external','send_messages_to_conversation','message/externallib.php','moodle','moodle/site:sendmessage','moodle_mobile_app'),(167,'core_message_get_conversation_messages','core_message_external','get_conversation_messages','message/externallib.php','moodle','','moodle_mobile_app'),(168,'core_message_unblock_user','core_message_external','unblock_user','message/externallib.php','moodle','','moodle_mobile_app'),(169,'core_message_get_user_notification_preferences','core_message_external','get_user_notification_preferences','message/externallib.php','moodle','moodle/user:editownmessageprofile','moodle_mobile_app'),(170,'core_message_get_user_message_preferences','core_message_external','get_user_message_preferences','message/externallib.php','moodle','moodle/user:editownmessageprofile','moodle_mobile_app'),(171,'core_message_set_favourite_conversations','core_message_external','set_favourite_conversations','message/externallib.php','moodle','','moodle_mobile_app'),(172,'core_message_unset_favourite_conversations','core_message_external','unset_favourite_conversations','message/externallib.php','moodle','','moodle_mobile_app'),(173,'core_message_delete_message_for_all_users','core_message_external','delete_message_for_all_users','message/externallib.php','moodle','moodle/site:deleteanymessage','moodle_mobile_app'),(174,'core_message_get_unread_notification_count','\\core_message\\external\\get_unread_notification_count','execute',NULL,'moodle','','moodle_mobile_app'),(175,'core_notes_create_notes','core_notes_external','create_notes','notes/externallib.php','moodle','moodle/notes:manage','moodle_mobile_app'),(176,'core_notes_delete_notes','core_notes_external','delete_notes','notes/externallib.php','moodle','moodle/notes:manage','moodle_mobile_app'),(177,'core_notes_get_course_notes','core_notes_external','get_course_notes','notes/externallib.php','moodle','moodle/notes:view','moodle_mobile_app'),(178,'core_notes_get_notes','core_notes_external','get_notes','notes/externallib.php','moodle','moodle/notes:view',NULL),(179,'core_notes_update_notes','core_notes_external','update_notes','notes/externallib.php','moodle','moodle/notes:manage',NULL),(180,'core_notes_view_notes','core_notes_external','view_notes','notes/externallib.php','moodle','moodle/notes:view','moodle_mobile_app'),(181,'core_output_load_template','core\\output\\external','load_template',NULL,'moodle','',NULL),(182,'core_output_load_template_with_dependencies','core\\output\\external','load_template_with_dependencies',NULL,'moodle','',NULL),(183,'core_output_load_fontawesome_icon_map','core\\output\\external','load_fontawesome_icon_map',NULL,'moodle','',NULL),(184,'core_output_load_fontawesome_icon_system_map','core\\external\\output\\icon_system\\load_fontawesome_map','execute',NULL,'moodle','',NULL),(185,'core_question_update_flag','core_question_external','update_flag',NULL,'moodle','moodle/question:flag','moodle_mobile_app'),(186,'core_question_submit_tags_form','core_question_external','submit_tags_form',NULL,'moodle','',NULL),(187,'core_question_get_random_question_summaries','core_question_external','get_random_question_summaries',NULL,'moodle','',NULL),(188,'core_rating_get_item_ratings','core_rating_external','get_item_ratings',NULL,'moodle','moodle/rating:view','moodle_mobile_app'),(189,'core_rating_add_rating','core_rating_external','add_rating',NULL,'moodle','moodle/rating:rate','moodle_mobile_app'),(190,'core_role_assign_roles','core_role_external','assign_roles','enrol/externallib.php','moodle','moodle/role:assign',NULL),(191,'core_role_unassign_roles','core_role_external','unassign_roles','enrol/externallib.php','moodle','moodle/role:assign',NULL),(192,'core_search_get_relevant_users','\\core_search\\external','get_relevant_users',NULL,'moodle','',NULL),(193,'core_tag_get_tagindex','core_tag_external','get_tagindex',NULL,'moodle','','moodle_mobile_app'),(194,'core_tag_get_tags','core_tag_external','get_tags',NULL,'moodle','',NULL),(195,'core_tag_update_tags','core_tag_external','update_tags',NULL,'moodle','',NULL),(196,'core_tag_get_tagindex_per_area','core_tag_external','get_tagindex_per_area',NULL,'moodle','','moodle_mobile_app'),(197,'core_tag_get_tag_areas','core_tag_external','get_tag_areas',NULL,'moodle','','moodle_mobile_app'),(198,'core_tag_get_tag_collections','core_tag_external','get_tag_collections',NULL,'moodle','','moodle_mobile_app'),(199,'core_tag_get_tag_cloud','core_tag_external','get_tag_cloud',NULL,'moodle','','moodle_mobile_app'),(200,'core_update_inplace_editable','core_external','update_inplace_editable','lib/external/externallib.php','moodle','',NULL),(201,'core_user_add_user_device','core_user_external','add_user_device','user/externallib.php','moodle','','moodle_mobile_app'),(202,'core_user_add_user_private_files','core_user_external','add_user_private_files','user/externallib.php','moodle','moodle/user:manageownfiles','moodle_mobile_app'),(203,'core_user_create_users','core_user_external','create_users','user/externallib.php','moodle','moodle/user:create',NULL),(204,'core_user_delete_users','core_user_external','delete_users','user/externallib.php','moodle','moodle/user:delete',NULL),(205,'core_user_get_course_user_profiles','core_user_external','get_course_user_profiles','user/externallib.php','moodle','moodle/user:viewdetails, moodle/user:viewhiddendetails, moodle/course:useremail, moodle/user:update, moodle/site:accessallgroups','moodle_mobile_app'),(206,'core_user_get_users','core_user_external','get_users','user/externallib.php','moodle','moodle/user:viewdetails, moodle/user:viewhiddendetails, moodle/course:useremail, moodle/user:update',NULL),(207,'core_user_get_users_by_field','core_user_external','get_users_by_field','user/externallib.php','moodle','moodle/user:viewdetails, moodle/user:viewhiddendetails, moodle/course:useremail, moodle/user:update','moodle_mobile_app'),(208,'core_user_search_identity','\\core_user\\external\\search_identity','execute',NULL,'moodle','moodle/user:viewalldetails',NULL),(209,'core_user_remove_user_device','core_user_external','remove_user_device','user/externallib.php','moodle','','moodle_mobile_app'),(210,'core_user_update_users','core_user_external','update_users','user/externallib.php','moodle','moodle/user:update',NULL),(211,'core_user_update_user_preferences','core_user_external','update_user_preferences','user/externallib.php','moodle','moodle/user:editownmessageprofile, moodle/user:editmessageprofile','moodle_mobile_app'),(212,'core_user_view_user_list','core_user_external','view_user_list','user/externallib.php','moodle','moodle/course:viewparticipants','moodle_mobile_app'),(213,'core_user_view_user_profile','core_user_external','view_user_profile','user/externallib.php','moodle','moodle/user:viewdetails','moodle_mobile_app'),(214,'core_user_get_user_preferences','core_user_external','get_user_preferences','user/externallib.php','moodle','','moodle_mobile_app'),(215,'core_user_update_picture','core_user_external','update_picture','user/externallib.php','moodle','moodle/user:editownprofile, moodle/user:editprofile','moodle_mobile_app'),(216,'core_user_set_user_preferences','core_user_external','set_user_preferences','user/externallib.php','moodle','moodle/site:config','moodle_mobile_app'),(217,'core_user_agree_site_policy','core_user_external','agree_site_policy','user/externallib.php','moodle','','moodle_mobile_app'),(218,'core_user_get_private_files_info','core_user_external','get_private_files_info','user/externallib.php','moodle','moodle/user:manageownfiles','moodle_mobile_app'),(219,'core_competency_create_competency_framework','core_competency\\external','create_competency_framework',NULL,'moodle','moodle/competency:competencymanage',NULL),(220,'core_competency_read_competency_framework','core_competency\\external','read_competency_framework',NULL,'moodle','moodle/competency:competencyview',NULL),(221,'core_competency_duplicate_competency_framework','core_competency\\external','duplicate_competency_framework',NULL,'moodle','moodle/competency:competencymanage',NULL),(222,'core_competency_delete_competency_framework','core_competency\\external','delete_competency_framework',NULL,'moodle','moodle/competency:competencymanage',NULL),(223,'core_competency_update_competency_framework','core_competency\\external','update_competency_framework',NULL,'moodle','moodle/competency:competencymanage',NULL),(224,'core_competency_list_competency_frameworks','core_competency\\external','list_competency_frameworks',NULL,'moodle','moodle/competency:competencyview',NULL),(225,'core_competency_count_competency_frameworks','core_competency\\external','count_competency_frameworks',NULL,'moodle','moodle/competency:competencyview',NULL),(226,'core_competency_competency_framework_viewed','core_competency\\external','competency_framework_viewed',NULL,'moodle','moodle/competency:competencyview',NULL),(227,'core_competency_create_competency','core_competency\\external','create_competency',NULL,'moodle','moodle/competency:competencymanage',NULL),(228,'core_competency_read_competency','core_competency\\external','read_competency',NULL,'moodle','moodle/competency:competencyview',NULL),(229,'core_competency_competency_viewed','core_competency\\external','competency_viewed',NULL,'moodle','moodle/competency:competencyview','moodle_mobile_app'),(230,'core_competency_delete_competency','core_competency\\external','delete_competency',NULL,'moodle','moodle/competency:competencymanage',NULL),(231,'core_competency_update_competency','core_competency\\external','update_competency',NULL,'moodle','moodle/competency:competencymanage',NULL),(232,'core_competency_list_competencies','core_competency\\external','list_competencies',NULL,'moodle','moodle/competency:competencyview',NULL),(233,'core_competency_list_competencies_in_template','core_competency\\external','list_competencies_in_template',NULL,'moodle','moodle/competency:competencyview',NULL),(234,'core_competency_count_competencies','core_competency\\external','count_competencies',NULL,'moodle','moodle/competency:competencyview',NULL),(235,'core_competency_count_competencies_in_template','core_competency\\external','count_competencies_in_template',NULL,'moodle','moodle/competency:competencyview',NULL),(236,'core_competency_search_competencies','core_competency\\external','search_competencies',NULL,'moodle','moodle/competency:competencyview',NULL),(237,'core_competency_set_parent_competency','core_competency\\external','set_parent_competency',NULL,'moodle','moodle/competency:competencymanage',NULL),(238,'core_competency_move_up_competency','core_competency\\external','move_up_competency',NULL,'moodle','moodle/competency:competencymanage',NULL),(239,'core_competency_move_down_competency','core_competency\\external','move_down_competency',NULL,'moodle','moodle/competency:competencymanage',NULL),(240,'core_competency_list_course_module_competencies','core_competency\\external','list_course_module_competencies',NULL,'moodle','moodle/competency:coursecompetencyview',NULL),(241,'core_competency_count_course_module_competencies','core_competency\\external','count_course_module_competencies',NULL,'moodle','moodle/competency:coursecompetencyview',NULL),(242,'core_competency_list_course_competencies','core_competency\\external','list_course_competencies',NULL,'moodle','moodle/competency:coursecompetencyview','moodle_mobile_app'),(243,'core_competency_count_competencies_in_course','core_competency\\external','count_competencies_in_course',NULL,'moodle','moodle/competency:coursecompetencyview',NULL),(244,'core_competency_count_courses_using_competency','core_competency\\external','count_courses_using_competency',NULL,'moodle','moodle/competency:coursecompetencyview',NULL),(245,'core_competency_add_competency_to_course','core_competency\\external','add_competency_to_course',NULL,'moodle','moodle/competency:coursecompetencymanage',NULL),(246,'core_competency_add_competency_to_template','core_competency\\external','add_competency_to_template',NULL,'moodle','moodle/competency:templatemanage',NULL),(247,'core_competency_remove_competency_from_course','core_competency\\external','remove_competency_from_course',NULL,'moodle','moodle/competency:coursecompetencymanage',NULL),(248,'core_competency_set_course_competency_ruleoutcome','core_competency\\external','set_course_competency_ruleoutcome',NULL,'moodle','moodle/competency:coursecompetencymanage',NULL),(249,'core_competency_remove_competency_from_template','core_competency\\external','remove_competency_from_template',NULL,'moodle','moodle/competency:templatemanage',NULL),(250,'core_competency_reorder_course_competency','core_competency\\external','reorder_course_competency',NULL,'moodle','moodle/competency:coursecompetencymanage',NULL),(251,'core_competency_reorder_template_competency','core_competency\\external','reorder_template_competency',NULL,'moodle','moodle/competency:templatemanage',NULL),(252,'core_competency_create_template','core_competency\\external','create_template',NULL,'moodle','moodle/competency:templatemanage',NULL),(253,'core_competency_duplicate_template','core_competency\\external','duplicate_template',NULL,'moodle','moodle/competency:templatemanage',NULL),(254,'core_competency_read_template','core_competency\\external','read_template',NULL,'moodle','moodle/competency:templateview',NULL),(255,'core_competency_delete_template','core_competency\\external','delete_template',NULL,'moodle','moodle/competency:templatemanage',NULL),(256,'core_competency_update_template','core_competency\\external','update_template',NULL,'moodle','moodle/competency:templatemanage',NULL),(257,'core_competency_list_templates','core_competency\\external','list_templates',NULL,'moodle','moodle/competency:templateview',NULL),(258,'core_competency_list_templates_using_competency','core_competency\\external','list_templates_using_competency',NULL,'moodle','moodle/competency:templateview',NULL),(259,'core_competency_count_templates','core_competency\\external','count_templates',NULL,'moodle','moodle/competency:templateview',NULL),(260,'core_competency_count_templates_using_competency','core_competency\\external','count_templates_using_competency',NULL,'moodle','moodle/competency:templateview',NULL),(261,'core_competency_create_plan','core_competency\\external','create_plan',NULL,'moodle','moodle/competency:planmanage',NULL),(262,'core_competency_update_plan','core_competency\\external','update_plan',NULL,'moodle','moodle/competency:planmanage',NULL),(263,'core_competency_complete_plan','core_competency\\external','complete_plan',NULL,'moodle','moodle/competency:planmanage',NULL),(264,'core_competency_reopen_plan','core_competency\\external','reopen_plan',NULL,'moodle','moodle/competency:planmanage',NULL),(265,'core_competency_read_plan','core_competency\\external','read_plan',NULL,'moodle','moodle/competency:planviewown',NULL),(266,'core_competency_delete_plan','core_competency\\external','delete_plan',NULL,'moodle','moodle/competency:planmanage',NULL),(267,'core_competency_list_user_plans','core_competency\\external','list_user_plans',NULL,'moodle','moodle/competency:planviewown',NULL),(268,'core_competency_list_plan_competencies','core_competency\\external','list_plan_competencies',NULL,'moodle','moodle/competency:planviewown',NULL),(269,'core_competency_add_competency_to_plan','core_competency\\external','add_competency_to_plan',NULL,'moodle','moodle/competency:planmanage',NULL),(270,'core_competency_remove_competency_from_plan','core_competency\\external','remove_competency_from_plan',NULL,'moodle','moodle/competency:planmanage',NULL),(271,'core_competency_reorder_plan_competency','core_competency\\external','reorder_plan_competency',NULL,'moodle','moodle/competency:planmanage',NULL),(272,'core_competency_plan_request_review','core_competency\\external','plan_request_review',NULL,'moodle','moodle/competency:planmanagedraft',NULL),(273,'core_competency_plan_start_review','core_competency\\external','plan_start_review',NULL,'moodle','moodle/competency:planmanage',NULL),(274,'core_competency_plan_stop_review','core_competency\\external','plan_stop_review',NULL,'moodle','moodle/competency:planmanage',NULL),(275,'core_competency_plan_cancel_review_request','core_competency\\external','plan_cancel_review_request',NULL,'moodle','moodle/competency:planmanagedraft',NULL),(276,'core_competency_approve_plan','core_competency\\external','approve_plan',NULL,'moodle','moodle/competency:planmanage',NULL),(277,'core_competency_unapprove_plan','core_competency\\external','unapprove_plan',NULL,'moodle','moodle/competency:planmanage',NULL),(278,'core_competency_template_has_related_data','core_competency\\external','template_has_related_data',NULL,'moodle','moodle/competency:templateview',NULL),(279,'core_competency_get_scale_values','core_competency\\external','get_scale_values',NULL,'moodle','moodle/competency:competencymanage','moodle_mobile_app'),(280,'core_competency_add_related_competency','core_competency\\external','add_related_competency',NULL,'moodle','moodle/competency:competencymanage',NULL),(281,'core_competency_remove_related_competency','core_competency\\external','remove_related_competency',NULL,'moodle','moodle/competency:competencymanage',NULL),(282,'core_competency_read_user_evidence','core_competency\\external','read_user_evidence',NULL,'moodle','moodle/competency:userevidenceview',NULL),(283,'core_competency_delete_user_evidence','core_competency\\external','delete_user_evidence',NULL,'moodle','moodle/competency:userevidencemanageown',NULL),(284,'core_competency_create_user_evidence_competency','core_competency\\external','create_user_evidence_competency',NULL,'moodle','moodle/competency:userevidencemanageown, moodle/competency:competencyview',NULL),(285,'core_competency_delete_user_evidence_competency','core_competency\\external','delete_user_evidence_competency',NULL,'moodle','moodle/competency:userevidencemanageown',NULL),(286,'core_competency_user_competency_cancel_review_request','core_competency\\external','user_competency_cancel_review_request',NULL,'moodle','moodle/competency:userevidencemanageown',NULL),(287,'core_competency_user_competency_request_review','core_competency\\external','user_competency_request_review',NULL,'moodle','moodle/competency:userevidencemanageown',NULL),(288,'core_competency_user_competency_start_review','core_competency\\external','user_competency_start_review',NULL,'moodle','moodle/competency:competencygrade',NULL),(289,'core_competency_user_competency_stop_review','core_competency\\external','user_competency_stop_review',NULL,'moodle','moodle/competency:competencygrade',NULL),(290,'core_competency_user_competency_viewed','core_competency\\external','user_competency_viewed',NULL,'moodle','moodle/competency:usercompetencyview','moodle_mobile_app'),(291,'core_competency_user_competency_viewed_in_plan','core_competency\\external','user_competency_viewed_in_plan',NULL,'moodle','moodle/competency:usercompetencyview','moodle_mobile_app'),(292,'core_competency_user_competency_viewed_in_course','core_competency\\external','user_competency_viewed_in_course',NULL,'moodle','moodle/competency:usercompetencyview','moodle_mobile_app'),(293,'core_competency_user_competency_plan_viewed','core_competency\\external','user_competency_plan_viewed',NULL,'moodle','moodle/competency:usercompetencyview','moodle_mobile_app'),(294,'core_competency_grade_competency','core_competency\\external','grade_competency',NULL,'moodle','moodle/competency:competencygrade',NULL),(295,'core_competency_grade_competency_in_plan','core_competency\\external','grade_competency_in_plan',NULL,'moodle','moodle/competency:competencygrade',NULL),(296,'core_competency_grade_competency_in_course','core_competency\\external','grade_competency_in_course',NULL,'moodle','moodle/competency:competencygrade','moodle_mobile_app'),(297,'core_competency_unlink_plan_from_template','core_competency\\external','unlink_plan_from_template',NULL,'moodle','moodle/competency:planmanage',NULL),(298,'core_competency_template_viewed','core_competency\\external','template_viewed',NULL,'moodle','moodle/competency:templateview',NULL),(299,'core_competency_request_review_of_user_evidence_linked_competencies','core_competency\\external','request_review_of_user_evidence_linked_competencies',NULL,'moodle','moodle/competency:userevidencemanageown',NULL),(300,'core_competency_update_course_competency_settings','core_competency\\external','update_course_competency_settings',NULL,'moodle','moodle/competency:coursecompetencyconfigure',NULL),(301,'core_competency_delete_evidence','core_competency\\external','delete_evidence',NULL,'moodle','moodle/competency:evidencedelete','moodle_mobile_app'),(302,'core_webservice_get_site_info','core_webservice_external','get_site_info','webservice/externallib.php','moodle','','moodle_mobile_app'),(303,'core_block_get_course_blocks','core_block_external','get_course_blocks',NULL,'moodle','','moodle_mobile_app'),(304,'core_block_get_dashboard_blocks','core_block_external','get_dashboard_blocks',NULL,'moodle','','moodle_mobile_app'),(305,'core_block_fetch_addable_blocks','core_block\\external\\fetch_addable_blocks','execute',NULL,'moodle','moodle/site:manageblocks','moodle_mobile_app'),(306,'core_filters_get_available_in_context','core_filters\\external','get_available_in_context',NULL,'moodle','','moodle_mobile_app'),(307,'core_customfield_delete_field','core_customfield_external','delete_field','customfield/externallib.php','moodle','',NULL),(308,'core_customfield_reload_template','core_customfield_external','reload_template','customfield/externallib.php','moodle','',NULL),(309,'core_customfield_create_category','core_customfield_external','create_category','customfield/externallib.php','moodle','',NULL),(310,'core_customfield_delete_category','core_customfield_external','delete_category','customfield/externallib.php','moodle','',NULL),(311,'core_customfield_move_field','core_customfield_external','move_field','customfield/externallib.php','moodle','',NULL),(312,'core_customfield_move_category','core_customfield_external','move_category','customfield/externallib.php','moodle','',NULL),(313,'core_h5p_get_trusted_h5p_file','core_h5p\\external','get_trusted_h5p_file',NULL,'moodle','','moodle_mobile_app'),(314,'core_table_get_dynamic_table_content','core_table\\external\\dynamic\\get','execute',NULL,'moodle','','moodle_mobile_app'),(315,'core_xapi_statement_post','core_xapi\\external\\post_statement','execute',NULL,'moodle','','moodle_mobile_app'),(316,'core_contentbank_delete_content','core_contentbank\\external\\delete_content','execute',NULL,'moodle','moodle/contentbank:deleteanycontent',NULL),(317,'core_contentbank_rename_content','core_contentbank\\external\\rename_content','execute',NULL,'moodle','moodle/contentbank:manageowncontent',NULL),(318,'core_contentbank_set_content_visibility','core_contentbank\\external\\set_content_visibility','execute',NULL,'moodle','moodle/contentbank:manageowncontent',NULL),(319,'core_create_userfeedback_action_record','core\\external\\record_userfeedback_action','execute',NULL,'moodle','',NULL),(320,'core_payment_get_available_gateways','core_payment\\external\\get_available_gateways','execute',NULL,'moodle','',NULL),(321,'core_reportbuilder_filters_reset','core_reportbuilder\\external\\filters\\reset','execute',NULL,'moodle','',NULL),(322,'core_dynamic_tabs_get_content','core\\external\\dynamic_tabs_get_content','execute',NULL,'moodle','',NULL),(323,'core_change_editmode','core\\external\\editmode','change_editmode',NULL,'moodle','',NULL),(324,'core_reportbuilder_reports_delete','core_reportbuilder\\external\\reports\\delete','execute',NULL,'moodle','',NULL),(325,'core_reportbuilder_reports_get','core_reportbuilder\\external\\reports\\get','execute',NULL,'moodle','',NULL),(326,'core_reportbuilder_columns_add','core_reportbuilder\\external\\columns\\add','execute',NULL,'moodle','',NULL),(327,'core_reportbuilder_columns_delete','core_reportbuilder\\external\\columns\\delete','execute',NULL,'moodle','',NULL),(328,'core_reportbuilder_columns_reorder','core_reportbuilder\\external\\columns\\reorder','execute',NULL,'moodle','',NULL),(329,'core_reportbuilder_columns_sort_get','core_reportbuilder\\external\\columns\\sort\\get','execute',NULL,'moodle','',NULL),(330,'core_reportbuilder_columns_sort_reorder','core_reportbuilder\\external\\columns\\sort\\reorder','execute',NULL,'moodle','',NULL),(331,'core_reportbuilder_columns_sort_toggle','core_reportbuilder\\external\\columns\\sort\\toggle','execute',NULL,'moodle','',NULL),(332,'core_reportbuilder_conditions_add','core_reportbuilder\\external\\conditions\\add','execute',NULL,'moodle','',NULL),(333,'core_reportbuilder_conditions_delete','core_reportbuilder\\external\\conditions\\delete','execute',NULL,'moodle','',NULL),(334,'core_reportbuilder_conditions_reorder','core_reportbuilder\\external\\conditions\\reorder','execute',NULL,'moodle','',NULL),(335,'core_reportbuilder_conditions_reset','core_reportbuilder\\external\\conditions\\reset','execute',NULL,'moodle','',NULL),(336,'core_reportbuilder_filters_add','core_reportbuilder\\external\\filters\\add','execute',NULL,'moodle','',NULL),(337,'core_reportbuilder_filters_delete','core_reportbuilder\\external\\filters\\delete','execute',NULL,'moodle','',NULL),(338,'core_reportbuilder_filters_reorder','core_reportbuilder\\external\\filters\\reorder','execute',NULL,'moodle','',NULL),(339,'core_reportbuilder_audiences_delete','core_reportbuilder\\external\\audiences\\delete','execute',NULL,'moodle','',NULL),(340,'core_reportbuilder_schedules_delete','core_reportbuilder\\external\\schedules\\delete','execute',NULL,'moodle','',NULL),(341,'core_reportbuilder_schedules_send','core_reportbuilder\\external\\schedules\\send','execute',NULL,'moodle','',NULL),(342,'core_reportbuilder_schedules_toggle','core_reportbuilder\\external\\schedules\\toggle','execute',NULL,'moodle','',NULL),(343,'mod_assign_copy_previous_attempt','mod_assign_external','copy_previous_attempt','mod/assign/externallib.php','mod_assign','mod/assign:view, mod/assign:submit',NULL),(344,'mod_assign_get_grades','mod_assign_external','get_grades','mod/assign/externallib.php','mod_assign','','moodle_mobile_app'),(345,'mod_assign_get_assignments','mod_assign_external','get_assignments','mod/assign/externallib.php','mod_assign','','moodle_mobile_app'),(346,'mod_assign_get_submissions','mod_assign_external','get_submissions','mod/assign/externallib.php','mod_assign','','moodle_mobile_app'),(347,'mod_assign_get_user_flags','mod_assign_external','get_user_flags','mod/assign/externallib.php','mod_assign','','moodle_mobile_app'),(348,'mod_assign_set_user_flags','mod_assign_external','set_user_flags','mod/assign/externallib.php','mod_assign','mod/assign:grade','moodle_mobile_app'),(349,'mod_assign_get_user_mappings','mod_assign_external','get_user_mappings','mod/assign/externallib.php','mod_assign','','moodle_mobile_app'),(350,'mod_assign_revert_submissions_to_draft','mod_assign_external','revert_submissions_to_draft','mod/assign/externallib.php','mod_assign','','moodle_mobile_app'),(351,'mod_assign_lock_submissions','mod_assign_external','lock_submissions','mod/assign/externallib.php','mod_assign','','moodle_mobile_app'),(352,'mod_assign_unlock_submissions','mod_assign_external','unlock_submissions','mod/assign/externallib.php','mod_assign','','moodle_mobile_app'),(353,'mod_assign_save_submission','mod_assign_external','save_submission','mod/assign/externallib.php','mod_assign','','moodle_mobile_app'),(354,'mod_assign_submit_for_grading','mod_assign_external','submit_for_grading','mod/assign/externallib.php','mod_assign','','moodle_mobile_app'),(355,'mod_assign_save_grade','mod_assign_external','save_grade','mod/assign/externallib.php','mod_assign','','moodle_mobile_app'),(356,'mod_assign_save_grades','mod_assign_external','save_grades','mod/assign/externallib.php','mod_assign','','moodle_mobile_app'),(357,'mod_assign_save_user_extensions','mod_assign_external','save_user_extensions','mod/assign/externallib.php','mod_assign','','moodle_mobile_app'),(358,'mod_assign_reveal_identities','mod_assign_external','reveal_identities','mod/assign/externallib.php','mod_assign','','moodle_mobile_app'),(359,'mod_assign_view_grading_table','mod_assign_external','view_grading_table','mod/assign/externallib.php','mod_assign','mod/assign:view, mod/assign:viewgrades','moodle_mobile_app'),(360,'mod_assign_view_submission_status','mod_assign_external','view_submission_status','mod/assign/externallib.php','mod_assign','mod/assign:view','moodle_mobile_app'),(361,'mod_assign_get_submission_status','mod_assign_external','get_submission_status','mod/assign/externallib.php','mod_assign','mod/assign:view','moodle_mobile_app'),(362,'mod_assign_list_participants','mod_assign_external','list_participants','mod/assign/externallib.php','mod_assign','mod/assign:view, mod/assign:viewgrades','moodle_mobile_app'),(363,'mod_assign_submit_grading_form','mod_assign_external','submit_grading_form','mod/assign/externallib.php','mod_assign','mod/assign:grade','moodle_mobile_app'),(364,'mod_assign_get_participant','mod_assign_external','get_participant','mod/assign/externallib.php','mod_assign','mod/assign:view, mod/assign:viewgrades','moodle_mobile_app'),(365,'mod_assign_view_assign','mod_assign_external','view_assign','mod/assign/externallib.php','mod_assign','mod/assign:view','moodle_mobile_app'),(366,'mod_assign_start_submission','mod_assign\\external\\start_submission','execute',NULL,'mod_assign','mod/assign:view','moodle_mobile_app'),(367,'mod_bigbluebuttonbn_can_join','mod_bigbluebuttonbn\\external\\can_join','execute',NULL,'mod_bigbluebuttonbn','mod/bigbluebuttonbn:view','moodle_mobile_app'),(368,'mod_bigbluebuttonbn_get_recordings','mod_bigbluebuttonbn\\external\\get_recordings','execute',NULL,'mod_bigbluebuttonbn','mod/bigbluebuttonbn:view','moodle_mobile_app'),(369,'mod_bigbluebuttonbn_get_recordings_to_import','mod_bigbluebuttonbn\\external\\get_recordings_to_import','execute',NULL,'mod_bigbluebuttonbn','mod/bigbluebuttonbn:importrecordings','moodle_mobile_app'),(370,'mod_bigbluebuttonbn_update_recording','mod_bigbluebuttonbn\\external\\update_recording','execute',NULL,'mod_bigbluebuttonbn','mod/bigbluebuttonbn:managerecordings','moodle_mobile_app'),(371,'mod_bigbluebuttonbn_end_meeting','mod_bigbluebuttonbn\\external\\end_meeting','execute',NULL,'mod_bigbluebuttonbn','mod/bigbluebuttonbn:join','moodle_mobile_app'),(372,'mod_bigbluebuttonbn_completion_validate','mod_bigbluebuttonbn\\external\\completion_validate','execute',NULL,'mod_bigbluebuttonbn','mod/bigbluebuttonbn:view','moodle_mobile_app'),(373,'mod_bigbluebuttonbn_meeting_info','mod_bigbluebuttonbn\\external\\meeting_info','execute',NULL,'mod_bigbluebuttonbn','mod/bigbluebuttonbn:view','moodle_mobile_app'),(374,'mod_bigbluebuttonbn_get_bigbluebuttonbns_by_courses','mod_bigbluebuttonbn\\external\\get_bigbluebuttonbns_by_courses','execute',NULL,'mod_bigbluebuttonbn','mod/bigbluebuttonbn:view','moodle_mobile_app'),(375,'mod_bigbluebuttonbn_view_bigbluebuttonbn','mod_bigbluebuttonbn\\external\\view_bigbluebuttonbn','execute',NULL,'mod_bigbluebuttonbn','mod/bigbluebuttonbn:view','moodle_mobile_app'),(376,'mod_bigbluebuttonbn_get_join_url','mod_bigbluebuttonbn\\external\\get_join_url','execute',NULL,'mod_bigbluebuttonbn','mod/bigbluebuttonbn:join','moodle_mobile_app'),(377,'mod_book_view_book','mod_book_external','view_book',NULL,'mod_book','mod/book:read','moodle_mobile_app'),(378,'mod_book_get_books_by_courses','mod_book_external','get_books_by_courses',NULL,'mod_book','','moodle_mobile_app'),(379,'mod_chat_login_user','mod_chat_external','login_user',NULL,'mod_chat','mod/chat:chat','moodle_mobile_app'),(380,'mod_chat_get_chat_users','mod_chat_external','get_chat_users',NULL,'mod_chat','mod/chat:chat','moodle_mobile_app'),(381,'mod_chat_send_chat_message','mod_chat_external','send_chat_message',NULL,'mod_chat','mod/chat:chat','moodle_mobile_app'),(382,'mod_chat_get_chat_latest_messages','mod_chat_external','get_chat_latest_messages',NULL,'mod_chat','mod/chat:chat','moodle_mobile_app'),(383,'mod_chat_view_chat','mod_chat_external','view_chat',NULL,'mod_chat','mod/chat:chat','moodle_mobile_app'),(384,'mod_chat_get_chats_by_courses','mod_chat_external','get_chats_by_courses',NULL,'mod_chat','','moodle_mobile_app'),(385,'mod_chat_get_sessions','mod_chat_external','get_sessions',NULL,'mod_chat','','moodle_mobile_app'),(386,'mod_chat_get_session_messages','mod_chat_external','get_session_messages',NULL,'mod_chat','','moodle_mobile_app'),(387,'mod_choice_get_choice_results','mod_choice_external','get_choice_results',NULL,'mod_choice','','moodle_mobile_app'),(388,'mod_choice_get_choice_options','mod_choice_external','get_choice_options',NULL,'mod_choice','mod/choice:choose','moodle_mobile_app'),(389,'mod_choice_submit_choice_response','mod_choice_external','submit_choice_response',NULL,'mod_choice','mod/choice:choose','moodle_mobile_app'),(390,'mod_choice_view_choice','mod_choice_external','view_choice',NULL,'mod_choice','','moodle_mobile_app'),(391,'mod_choice_get_choices_by_courses','mod_choice_external','get_choices_by_courses',NULL,'mod_choice','','moodle_mobile_app'),(392,'mod_choice_delete_choice_responses','mod_choice_external','delete_choice_responses',NULL,'mod_choice','mod/choice:choose','moodle_mobile_app'),(393,'mod_data_get_databases_by_courses','mod_data_external','get_databases_by_courses',NULL,'mod_data','mod/data:viewentry','moodle_mobile_app'),(394,'mod_data_view_database','mod_data_external','view_database',NULL,'mod_data','mod/data:viewentry','moodle_mobile_app'),(395,'mod_data_get_data_access_information','mod_data_external','get_data_access_information',NULL,'mod_data','mod/data:viewentry','moodle_mobile_app'),(396,'mod_data_get_entries','mod_data_external','get_entries',NULL,'mod_data','mod/data:viewentry','moodle_mobile_app'),(397,'mod_data_get_entry','mod_data_external','get_entry',NULL,'mod_data','mod/data:viewentry','moodle_mobile_app'),(398,'mod_data_get_fields','mod_data_external','get_fields',NULL,'mod_data','mod/data:viewentry','moodle_mobile_app'),(399,'mod_data_search_entries','mod_data_external','search_entries',NULL,'mod_data','mod/data:viewentry','moodle_mobile_app'),(400,'mod_data_approve_entry','mod_data_external','approve_entry',NULL,'mod_data','mod/data:approve','moodle_mobile_app'),(401,'mod_data_delete_entry','mod_data_external','delete_entry',NULL,'mod_data','mod/data:manageentries','moodle_mobile_app'),(402,'mod_data_add_entry','mod_data_external','add_entry',NULL,'mod_data','mod/data:writeentry','moodle_mobile_app'),(403,'mod_data_update_entry','mod_data_external','update_entry',NULL,'mod_data','mod/data:writeentry','moodle_mobile_app'),(404,'mod_feedback_get_feedbacks_by_courses','mod_feedback_external','get_feedbacks_by_courses',NULL,'mod_feedback','mod/feedback:view','moodle_mobile_app'),(405,'mod_feedback_get_feedback_access_information','mod_feedback_external','get_feedback_access_information',NULL,'mod_feedback','mod/feedback:view','moodle_mobile_app'),(406,'mod_feedback_view_feedback','mod_feedback_external','view_feedback',NULL,'mod_feedback','mod/feedback:view','moodle_mobile_app'),(407,'mod_feedback_get_current_completed_tmp','mod_feedback_external','get_current_completed_tmp',NULL,'mod_feedback','mod/feedback:view','moodle_mobile_app'),(408,'mod_feedback_get_items','mod_feedback_external','get_items',NULL,'mod_feedback','mod/feedback:view','moodle_mobile_app'),(409,'mod_feedback_launch_feedback','mod_feedback_external','launch_feedback',NULL,'mod_feedback','mod/feedback:complete','moodle_mobile_app'),(410,'mod_feedback_get_page_items','mod_feedback_external','get_page_items',NULL,'mod_feedback','mod/feedback:complete','moodle_mobile_app'),(411,'mod_feedback_process_page','mod_feedback_external','process_page',NULL,'mod_feedback','mod/feedback:complete','moodle_mobile_app'),(412,'mod_feedback_get_analysis','mod_feedback_external','get_analysis',NULL,'mod_feedback','mod/feedback:viewanalysepage','moodle_mobile_app'),(413,'mod_feedback_get_unfinished_responses','mod_feedback_external','get_unfinished_responses',NULL,'mod_feedback','mod/feedback:view','moodle_mobile_app'),(414,'mod_feedback_get_finished_responses','mod_feedback_external','get_finished_responses',NULL,'mod_feedback','mod/feedback:view','moodle_mobile_app'),(415,'mod_feedback_get_non_respondents','mod_feedback_external','get_non_respondents',NULL,'mod_feedback','mod/feedback:viewreports','moodle_mobile_app'),(416,'mod_feedback_get_responses_analysis','mod_feedback_external','get_responses_analysis',NULL,'mod_feedback','mod/feedback:viewreports','moodle_mobile_app'),(417,'mod_feedback_get_last_completed','mod_feedback_external','get_last_completed',NULL,'mod_feedback','mod/feedback:view','moodle_mobile_app'),(418,'mod_folder_view_folder','mod_folder_external','view_folder',NULL,'mod_folder','mod/folder:view','moodle_mobile_app'),(419,'mod_folder_get_folders_by_courses','mod_folder_external','get_folders_by_courses',NULL,'mod_folder','mod/folder:view','moodle_mobile_app'),(420,'mod_forum_get_forums_by_courses','mod_forum_external','get_forums_by_courses','mod/forum/externallib.php','mod_forum','mod/forum:viewdiscussion','moodle_mobile_app'),(421,'mod_forum_get_discussion_posts','mod_forum_external','get_discussion_posts','mod/forum/externallib.php','mod_forum','mod/forum:viewdiscussion, mod/forum:viewqandawithoutposting','moodle_mobile_app'),(422,'mod_forum_get_forum_discussions_paginated','mod_forum_external','get_forum_discussions_paginated','mod/forum/externallib.php','mod_forum','mod/forum:viewdiscussion, mod/forum:viewqandawithoutposting','moodle_mobile_app'),(423,'mod_forum_get_forum_discussions','mod_forum_external','get_forum_discussions','mod/forum/externallib.php','mod_forum','mod/forum:viewdiscussion, mod/forum:viewqandawithoutposting','moodle_mobile_app'),(424,'mod_forum_view_forum','mod_forum_external','view_forum','mod/forum/externallib.php','mod_forum','mod/forum:viewdiscussion','moodle_mobile_app'),(425,'mod_forum_view_forum_discussion','mod_forum_external','view_forum_discussion','mod/forum/externallib.php','mod_forum','mod/forum:viewdiscussion','moodle_mobile_app'),(426,'mod_forum_add_discussion_post','mod_forum_external','add_discussion_post','mod/forum/externallib.php','mod_forum','mod/forum:replypost','moodle_mobile_app'),(427,'mod_forum_add_discussion','mod_forum_external','add_discussion','mod/forum/externallib.php','mod_forum','mod/forum:startdiscussion','moodle_mobile_app'),(428,'mod_forum_can_add_discussion','mod_forum_external','can_add_discussion','mod/forum/externallib.php','mod_forum','','moodle_mobile_app'),(429,'mod_forum_get_forum_access_information','mod_forum_external','get_forum_access_information',NULL,'mod_forum','','moodle_mobile_app'),(430,'mod_forum_set_subscription_state','mod_forum_external','set_subscription_state','mod/forum/externallib.php','mod_forum','','moodle_mobile_app'),(431,'mod_forum_set_lock_state','mod_forum_external','set_lock_state','mod/forum/externallib.php','mod_forum','moodle/course:manageactivities','moodle_mobile_app'),(432,'mod_forum_toggle_favourite_state','mod_forum_external','toggle_favourite_state','mod/forum/externallib.php','mod_forum','','moodle_mobile_app'),(433,'mod_forum_set_pin_state','mod_forum_external','set_pin_state','mod/forum/externallib.php','mod_forum','','moodle_mobile_app'),(434,'mod_forum_delete_post','mod_forum_external','delete_post','mod/forum/externallib.php','mod_forum','','moodle_mobile_app'),(435,'mod_forum_get_discussion_posts_by_userid','mod_forum_external','get_discussion_posts_by_userid','mod/forum/externallib.php','mod_forum','mod/forum:viewdiscussion, mod/forum:viewqandawithoutposting',NULL),(436,'mod_forum_get_discussion_post','mod_forum_external','get_discussion_post','mod/forum/externallib.php','mod_forum','','moodle_mobile_app'),(437,'mod_forum_prepare_draft_area_for_post','mod_forum_external','prepare_draft_area_for_post','mod/forum/externallib.php','mod_forum','','moodle_mobile_app'),(438,'mod_forum_update_discussion_post','mod_forum_external','update_discussion_post','mod/forum/externallib.php','mod_forum','','moodle_mobile_app'),(439,'mod_glossary_get_glossaries_by_courses','mod_glossary_external','get_glossaries_by_courses',NULL,'mod_glossary','mod/glossary:view','moodle_mobile_app'),(440,'mod_glossary_view_glossary','mod_glossary_external','view_glossary',NULL,'mod_glossary','mod/glossary:view','moodle_mobile_app'),(441,'mod_glossary_view_entry','mod_glossary_external','view_entry',NULL,'mod_glossary','mod/glossary:view','moodle_mobile_app'),(442,'mod_glossary_get_entries_by_letter','mod_glossary_external','get_entries_by_letter',NULL,'mod_glossary','mod/glossary:view','moodle_mobile_app'),(443,'mod_glossary_get_entries_by_date','mod_glossary_external','get_entries_by_date',NULL,'mod_glossary','mod/glossary:view','moodle_mobile_app'),(444,'mod_glossary_get_categories','mod_glossary_external','get_categories',NULL,'mod_glossary','mod/glossary:view','moodle_mobile_app'),(445,'mod_glossary_get_entries_by_category','mod_glossary_external','get_entries_by_category',NULL,'mod_glossary','mod/glossary:view','moodle_mobile_app'),(446,'mod_glossary_get_authors','mod_glossary_external','get_authors',NULL,'mod_glossary','mod/glossary:view','moodle_mobile_app'),(447,'mod_glossary_get_entries_by_author','mod_glossary_external','get_entries_by_author',NULL,'mod_glossary','mod/glossary:view','moodle_mobile_app'),(448,'mod_glossary_get_entries_by_author_id','mod_glossary_external','get_entries_by_author_id',NULL,'mod_glossary','mod/glossary:view','moodle_mobile_app'),(449,'mod_glossary_get_entries_by_search','mod_glossary_external','get_entries_by_search',NULL,'mod_glossary','mod/glossary:view','moodle_mobile_app'),(450,'mod_glossary_get_entries_by_term','mod_glossary_external','get_entries_by_term',NULL,'mod_glossary','mod/glossary:view','moodle_mobile_app'),(451,'mod_glossary_get_entries_to_approve','mod_glossary_external','get_entries_to_approve',NULL,'mod_glossary','mod/glossary:approve','moodle_mobile_app'),(452,'mod_glossary_get_entry_by_id','mod_glossary_external','get_entry_by_id',NULL,'mod_glossary','mod/glossary:view','moodle_mobile_app'),(453,'mod_glossary_add_entry','mod_glossary_external','add_entry',NULL,'mod_glossary','mod/glossary:write','moodle_mobile_app'),(454,'mod_glossary_delete_entry','mod_glossary\\external\\delete_entry','execute',NULL,'mod_glossary','','moodle_mobile_app'),(455,'mod_glossary_update_entry','mod_glossary\\external\\update_entry','execute',NULL,'mod_glossary','','moodle_mobile_app'),(456,'mod_glossary_prepare_entry_for_edition','mod_glossary\\external\\prepare_entry','execute',NULL,'mod_glossary','','moodle_mobile_app'),(457,'mod_h5pactivity_get_h5pactivity_access_information','mod_h5pactivity\\external\\get_h5pactivity_access_information','execute',NULL,'mod_h5pactivity','mod/h5pactivity:view','moodle_mobile_app'),(458,'mod_h5pactivity_view_h5pactivity','mod_h5pactivity\\external\\view_h5pactivity','execute',NULL,'mod_h5pactivity','mod/h5pactivity:view','moodle_mobile_app'),(459,'mod_h5pactivity_get_attempts','mod_h5pactivity\\external\\get_attempts','execute',NULL,'mod_h5pactivity','mod/h5pactivity:view','moodle_mobile_app'),(460,'mod_h5pactivity_get_results','mod_h5pactivity\\external\\get_results','execute',NULL,'mod_h5pactivity','mod/h5pactivity:view','moodle_mobile_app'),(461,'mod_h5pactivity_get_h5pactivities_by_courses','mod_h5pactivity\\external\\get_h5pactivities_by_courses','execute',NULL,'mod_h5pactivity','mod/h5pactivity:view','moodle_mobile_app'),(462,'mod_h5pactivity_log_report_viewed','mod_h5pactivity\\external\\log_report_viewed','execute',NULL,'mod_h5pactivity','','moodle_mobile_app'),(463,'mod_h5pactivity_get_user_attempts','mod_h5pactivity\\external\\get_user_attempts','execute',NULL,'mod_h5pactivity','mod/h5pactivity:reviewattempts','moodle_mobile_app'),(464,'mod_imscp_view_imscp','mod_imscp_external','view_imscp',NULL,'mod_imscp','mod/imscp:view','moodle_mobile_app'),(465,'mod_imscp_get_imscps_by_courses','mod_imscp_external','get_imscps_by_courses',NULL,'mod_imscp','mod/imscp:view','moodle_mobile_app'),(466,'mod_label_get_labels_by_courses','mod_label_external','get_labels_by_courses',NULL,'mod_label','mod/label:view','moodle_mobile_app'),(467,'mod_lesson_get_lessons_by_courses','mod_lesson_external','get_lessons_by_courses',NULL,'mod_lesson','mod/lesson:view','moodle_mobile_app'),(468,'mod_lesson_get_lesson_access_information','mod_lesson_external','get_lesson_access_information',NULL,'mod_lesson','mod/lesson:view','moodle_mobile_app'),(469,'mod_lesson_view_lesson','mod_lesson_external','view_lesson',NULL,'mod_lesson','mod/lesson:view','moodle_mobile_app'),(470,'mod_lesson_get_questions_attempts','mod_lesson_external','get_questions_attempts',NULL,'mod_lesson','mod/lesson:view','moodle_mobile_app'),(471,'mod_lesson_get_user_grade','mod_lesson_external','get_user_grade',NULL,'mod_lesson','mod/lesson:view','moodle_mobile_app'),(472,'mod_lesson_get_user_attempt_grade','mod_lesson_external','get_user_attempt_grade',NULL,'mod_lesson','mod/lesson:view','moodle_mobile_app'),(473,'mod_lesson_get_content_pages_viewed','mod_lesson_external','get_content_pages_viewed',NULL,'mod_lesson','mod/lesson:view','moodle_mobile_app'),(474,'mod_lesson_get_user_timers','mod_lesson_external','get_user_timers',NULL,'mod_lesson','mod/lesson:view','moodle_mobile_app'),(475,'mod_lesson_get_pages','mod_lesson_external','get_pages',NULL,'mod_lesson','mod/lesson:view','moodle_mobile_app'),(476,'mod_lesson_launch_attempt','mod_lesson_external','launch_attempt',NULL,'mod_lesson','mod/lesson:view','moodle_mobile_app'),(477,'mod_lesson_get_page_data','mod_lesson_external','get_page_data',NULL,'mod_lesson','mod/lesson:view','moodle_mobile_app'),(478,'mod_lesson_process_page','mod_lesson_external','process_page',NULL,'mod_lesson','mod/lesson:view','moodle_mobile_app'),(479,'mod_lesson_finish_attempt','mod_lesson_external','finish_attempt',NULL,'mod_lesson','mod/lesson:view','moodle_mobile_app'),(480,'mod_lesson_get_attempts_overview','mod_lesson_external','get_attempts_overview',NULL,'mod_lesson','mod/lesson:viewreports','moodle_mobile_app'),(481,'mod_lesson_get_user_attempt','mod_lesson_external','get_user_attempt',NULL,'mod_lesson','mod/lesson:viewreports','moodle_mobile_app'),(482,'mod_lesson_get_pages_possible_jumps','mod_lesson_external','get_pages_possible_jumps',NULL,'mod_lesson','mod/lesson:view','moodle_mobile_app'),(483,'mod_lesson_get_lesson','mod_lesson_external','get_lesson',NULL,'mod_lesson','mod/lesson:view','moodle_mobile_app'),(484,'mod_lti_get_tool_launch_data','mod_lti_external','get_tool_launch_data',NULL,'mod_lti','mod/lti:view','moodle_mobile_app'),(485,'mod_lti_get_ltis_by_courses','mod_lti_external','get_ltis_by_courses',NULL,'mod_lti','mod/lti:view','moodle_mobile_app'),(486,'mod_lti_view_lti','mod_lti_external','view_lti',NULL,'mod_lti','mod/lti:view','moodle_mobile_app'),(487,'mod_lti_get_tool_proxies','mod_lti_external','get_tool_proxies',NULL,'mod_lti','moodle/site:config',NULL),(488,'mod_lti_create_tool_proxy','mod_lti_external','create_tool_proxy',NULL,'mod_lti','moodle/site:config',NULL),(489,'mod_lti_delete_tool_proxy','mod_lti_external','delete_tool_proxy',NULL,'mod_lti','moodle/site:config',NULL),(490,'mod_lti_get_tool_proxy_registration_request','mod_lti_external','get_tool_proxy_registration_request',NULL,'mod_lti','moodle/site:config',NULL),(491,'mod_lti_get_tool_types','mod_lti_external','get_tool_types',NULL,'mod_lti','moodle/site:config',NULL),(492,'mod_lti_get_tool_types_and_proxies','mod_lti\\external\\get_tool_types_and_proxies','execute',NULL,'mod_lti','moodle/site:config',NULL),(493,'mod_lti_get_tool_types_and_proxies_count','mod_lti\\external\\get_tool_types_and_proxies_count','execute',NULL,'mod_lti','moodle/site:config',NULL),(494,'mod_lti_create_tool_type','mod_lti_external','create_tool_type',NULL,'mod_lti','moodle/site:config',NULL),(495,'mod_lti_update_tool_type','mod_lti_external','update_tool_type',NULL,'mod_lti','moodle/site:config',NULL),(496,'mod_lti_delete_tool_type','mod_lti_external','delete_tool_type',NULL,'mod_lti','moodle/site:config',NULL),(497,'mod_lti_is_cartridge','mod_lti_external','is_cartridge',NULL,'mod_lti','moodle/site:config',NULL),(498,'mod_page_view_page','mod_page_external','view_page',NULL,'mod_page','mod/page:view','moodle_mobile_app'),(499,'mod_page_get_pages_by_courses','mod_page_external','get_pages_by_courses',NULL,'mod_page','mod/page:view','moodle_mobile_app'),(500,'mod_quiz_get_quizzes_by_courses','mod_quiz_external','get_quizzes_by_courses',NULL,'mod_quiz','mod/quiz:view','moodle_mobile_app'),(501,'mod_quiz_view_quiz','mod_quiz_external','view_quiz',NULL,'mod_quiz','mod/quiz:view','moodle_mobile_app'),(502,'mod_quiz_get_user_attempts','mod_quiz_external','get_user_attempts',NULL,'mod_quiz','mod/quiz:view','moodle_mobile_app'),(503,'mod_quiz_get_user_best_grade','mod_quiz_external','get_user_best_grade',NULL,'mod_quiz','mod/quiz:view','moodle_mobile_app'),(504,'mod_quiz_get_combined_review_options','mod_quiz_external','get_combined_review_options',NULL,'mod_quiz','mod/quiz:view','moodle_mobile_app'),(505,'mod_quiz_start_attempt','mod_quiz_external','start_attempt',NULL,'mod_quiz','mod/quiz:attempt','moodle_mobile_app'),(506,'mod_quiz_get_attempt_data','mod_quiz_external','get_attempt_data',NULL,'mod_quiz','mod/quiz:attempt','moodle_mobile_app'),(507,'mod_quiz_get_attempt_summary','mod_quiz_external','get_attempt_summary',NULL,'mod_quiz','mod/quiz:attempt','moodle_mobile_app'),(508,'mod_quiz_save_attempt','mod_quiz_external','save_attempt',NULL,'mod_quiz','mod/quiz:attempt','moodle_mobile_app'),(509,'mod_quiz_process_attempt','mod_quiz_external','process_attempt',NULL,'mod_quiz','mod/quiz:attempt','moodle_mobile_app'),(510,'mod_quiz_get_attempt_review','mod_quiz_external','get_attempt_review',NULL,'mod_quiz','mod/quiz:reviewmyattempts','moodle_mobile_app'),(511,'mod_quiz_view_attempt','mod_quiz_external','view_attempt',NULL,'mod_quiz','mod/quiz:attempt','moodle_mobile_app'),(512,'mod_quiz_view_attempt_summary','mod_quiz_external','view_attempt_summary',NULL,'mod_quiz','mod/quiz:attempt','moodle_mobile_app'),(513,'mod_quiz_view_attempt_review','mod_quiz_external','view_attempt_review',NULL,'mod_quiz','mod/quiz:reviewmyattempts','moodle_mobile_app'),(514,'mod_quiz_get_quiz_feedback_for_grade','mod_quiz_external','get_quiz_feedback_for_grade',NULL,'mod_quiz','mod/quiz:view','moodle_mobile_app'),(515,'mod_quiz_get_quiz_access_information','mod_quiz_external','get_quiz_access_information',NULL,'mod_quiz','mod/quiz:view','moodle_mobile_app'),(516,'mod_quiz_get_attempt_access_information','mod_quiz_external','get_attempt_access_information',NULL,'mod_quiz','mod/quiz:view','moodle_mobile_app'),(517,'mod_quiz_get_quiz_required_qtypes','mod_quiz_external','get_quiz_required_qtypes',NULL,'mod_quiz','mod/quiz:view','moodle_mobile_app'),(518,'mod_quiz_set_question_version','mod_quiz\\external\\submit_question_version','execute',NULL,'mod_quiz','mod/quiz:view',NULL),(519,'mod_resource_view_resource','mod_resource_external','view_resource',NULL,'mod_resource','mod/resource:view','moodle_mobile_app'),(520,'mod_resource_get_resources_by_courses','mod_resource_external','get_resources_by_courses',NULL,'mod_resource','mod/resource:view','moodle_mobile_app'),(521,'mod_scorm_view_scorm','mod_scorm_external','view_scorm',NULL,'mod_scorm','','moodle_mobile_app'),(522,'mod_scorm_get_scorm_attempt_count','mod_scorm_external','get_scorm_attempt_count',NULL,'mod_scorm','','moodle_mobile_app'),(523,'mod_scorm_get_scorm_scoes','mod_scorm_external','get_scorm_scoes',NULL,'mod_scorm','','moodle_mobile_app'),(524,'mod_scorm_get_scorm_user_data','mod_scorm_external','get_scorm_user_data',NULL,'mod_scorm','','moodle_mobile_app'),(525,'mod_scorm_insert_scorm_tracks','mod_scorm_external','insert_scorm_tracks',NULL,'mod_scorm','mod/scorm:savetrack','moodle_mobile_app'),(526,'mod_scorm_get_scorm_sco_tracks','mod_scorm_external','get_scorm_sco_tracks',NULL,'mod_scorm','','moodle_mobile_app'),(527,'mod_scorm_get_scorms_by_courses','mod_scorm_external','get_scorms_by_courses',NULL,'mod_scorm','','moodle_mobile_app'),(528,'mod_scorm_launch_sco','mod_scorm_external','launch_sco',NULL,'mod_scorm','','moodle_mobile_app'),(529,'mod_scorm_get_scorm_access_information','mod_scorm_external','get_scorm_access_information',NULL,'mod_scorm','','moodle_mobile_app'),(530,'mod_survey_get_surveys_by_courses','mod_survey_external','get_surveys_by_courses',NULL,'mod_survey','','moodle_mobile_app'),(531,'mod_survey_view_survey','mod_survey_external','view_survey',NULL,'mod_survey','mod/survey:participate','moodle_mobile_app'),(532,'mod_survey_get_questions','mod_survey_external','get_questions',NULL,'mod_survey','mod/survey:participate','moodle_mobile_app'),(533,'mod_survey_submit_answers','mod_survey_external','submit_answers',NULL,'mod_survey','mod/survey:participate','moodle_mobile_app'),(534,'mod_url_view_url','mod_url_external','view_url',NULL,'mod_url','mod/url:view','moodle_mobile_app'),(535,'mod_url_get_urls_by_courses','mod_url_external','get_urls_by_courses',NULL,'mod_url','mod/url:view','moodle_mobile_app'),(536,'mod_wiki_get_wikis_by_courses','mod_wiki_external','get_wikis_by_courses',NULL,'mod_wiki','mod/wiki:viewpage','moodle_mobile_app'),(537,'mod_wiki_view_wiki','mod_wiki_external','view_wiki',NULL,'mod_wiki','mod/wiki:viewpage','moodle_mobile_app'),(538,'mod_wiki_view_page','mod_wiki_external','view_page',NULL,'mod_wiki','mod/wiki:viewpage','moodle_mobile_app'),(539,'mod_wiki_get_subwikis','mod_wiki_external','get_subwikis',NULL,'mod_wiki','mod/wiki:viewpage','moodle_mobile_app'),(540,'mod_wiki_get_subwiki_pages','mod_wiki_external','get_subwiki_pages',NULL,'mod_wiki','mod/wiki:viewpage','moodle_mobile_app'),(541,'mod_wiki_get_subwiki_files','mod_wiki_external','get_subwiki_files',NULL,'mod_wiki','mod/wiki:viewpage','moodle_mobile_app'),(542,'mod_wiki_get_page_contents','mod_wiki_external','get_page_contents',NULL,'mod_wiki','mod/wiki:viewpage','moodle_mobile_app'),(543,'mod_wiki_get_page_for_editing','mod_wiki_external','get_page_for_editing',NULL,'mod_wiki','mod/wiki:editpage','moodle_mobile_app'),(544,'mod_wiki_new_page','mod_wiki_external','new_page',NULL,'mod_wiki','mod/wiki:editpage','moodle_mobile_app'),(545,'mod_wiki_edit_page','mod_wiki_external','edit_page',NULL,'mod_wiki','mod/wiki:editpage','moodle_mobile_app'),(546,'mod_workshop_get_workshops_by_courses','mod_workshop_external','get_workshops_by_courses',NULL,'mod_workshop','mod/workshop:view','moodle_mobile_app'),(547,'mod_workshop_get_workshop_access_information','mod_workshop_external','get_workshop_access_information',NULL,'mod_workshop','mod/workshop:view','moodle_mobile_app'),(548,'mod_workshop_get_user_plan','mod_workshop_external','get_user_plan',NULL,'mod_workshop','mod/workshop:view','moodle_mobile_app'),(549,'mod_workshop_view_workshop','mod_workshop_external','view_workshop',NULL,'mod_workshop','mod/workshop:view','moodle_mobile_app'),(550,'mod_workshop_add_submission','mod_workshop_external','add_submission',NULL,'mod_workshop','mod/workshop:submit','moodle_mobile_app'),(551,'mod_workshop_update_submission','mod_workshop_external','update_submission',NULL,'mod_workshop','mod/workshop:submit','moodle_mobile_app'),(552,'mod_workshop_delete_submission','mod_workshop_external','delete_submission',NULL,'mod_workshop','mod/workshop:submit','moodle_mobile_app'),(553,'mod_workshop_get_submissions','mod_workshop_external','get_submissions',NULL,'mod_workshop','','moodle_mobile_app'),(554,'mod_workshop_get_submission','mod_workshop_external','get_submission',NULL,'mod_workshop','','moodle_mobile_app'),(555,'mod_workshop_get_submission_assessments','mod_workshop_external','get_submission_assessments',NULL,'mod_workshop','','moodle_mobile_app'),(556,'mod_workshop_get_assessment','mod_workshop_external','get_assessment',NULL,'mod_workshop','','moodle_mobile_app'),(557,'mod_workshop_get_assessment_form_definition','mod_workshop_external','get_assessment_form_definition',NULL,'mod_workshop','','moodle_mobile_app'),(558,'mod_workshop_get_reviewer_assessments','mod_workshop_external','get_reviewer_assessments',NULL,'mod_workshop','','moodle_mobile_app'),(559,'mod_workshop_update_assessment','mod_workshop_external','update_assessment',NULL,'mod_workshop','','moodle_mobile_app'),(560,'mod_workshop_get_grades','mod_workshop_external','get_grades',NULL,'mod_workshop','','moodle_mobile_app'),(561,'mod_workshop_evaluate_assessment','mod_workshop_external','evaluate_assessment',NULL,'mod_workshop','','moodle_mobile_app'),(562,'mod_workshop_get_grades_report','mod_workshop_external','get_grades_report',NULL,'mod_workshop','','moodle_mobile_app'),(563,'mod_workshop_view_submission','mod_workshop_external','view_submission',NULL,'mod_workshop','mod/workshop:view','moodle_mobile_app'),(564,'mod_workshop_evaluate_submission','mod_workshop_external','evaluate_submission',NULL,'mod_workshop','','moodle_mobile_app'),(565,'auth_email_get_signup_settings','auth_email_external','get_signup_settings',NULL,'auth_email','',NULL),(566,'auth_email_signup_user','auth_email_external','signup_user',NULL,'auth_email','',NULL),(567,'enrol_guest_get_instance_info','enrol_guest_external','get_instance_info',NULL,'enrol_guest','','moodle_mobile_app'),(568,'enrol_manual_enrol_users','enrol_manual_external','enrol_users','enrol/manual/externallib.php','enrol_manual','enrol/manual:enrol',NULL),(569,'enrol_manual_unenrol_users','enrol_manual_external','unenrol_users','enrol/manual/externallib.php','enrol_manual','enrol/manual:unenrol',NULL),(570,'enrol_meta_add_instances','enrol_meta\\external\\add_instances','execute',NULL,'enrol_meta','enrol/meta:config',NULL),(571,'enrol_meta_delete_instances','enrol_meta\\external\\delete_instances','execute',NULL,'enrol_meta','enrol/meta:config',NULL),(572,'enrol_self_get_instance_info','enrol_self_external','get_instance_info','enrol/self/externallib.php','enrol_self','','moodle_mobile_app'),(573,'enrol_self_enrol_user','enrol_self_external','enrol_user','enrol/self/externallib.php','enrol_self','','moodle_mobile_app'),(574,'message_airnotifier_is_system_configured','message_airnotifier_external','is_system_configured','message/output/airnotifier/externallib.php','message_airnotifier','','moodle_mobile_app'),(575,'message_airnotifier_are_notification_preferences_configured','message_airnotifier_external','are_notification_preferences_configured','message/output/airnotifier/externallib.php','message_airnotifier','','moodle_mobile_app'),(576,'message_airnotifier_get_user_devices','message_airnotifier_external','get_user_devices','message/output/airnotifier/externallib.php','message_airnotifier','','moodle_mobile_app'),(577,'message_airnotifier_enable_device','message_airnotifier_external','enable_device','message/output/airnotifier/externallib.php','message_airnotifier','message/airnotifier:managedevice','moodle_mobile_app'),(578,'message_popup_get_popup_notifications','message_popup_external','get_popup_notifications','message/output/popup/externallib.php','message_popup','','moodle_mobile_app'),(579,'message_popup_get_unread_popup_notification_count','message_popup_external','get_unread_popup_notification_count','message/output/popup/externallib.php','message_popup','','moodle_mobile_app'),(580,'block_accessreview_get_module_data','block_accessreview\\external\\get_module_data','execute',NULL,'block_accessreview','block/accessreview:view',NULL),(581,'block_accessreview_get_section_data','block_accessreview\\external\\get_section_data','execute',NULL,'block_accessreview','block/accessreview:view',NULL),(582,'block_recentlyaccesseditems_get_recent_items','block_recentlyaccesseditems\\external','get_recent_items',NULL,'block_recentlyaccesseditems','','moodle_mobile_app'),(583,'block_starredcourses_get_starred_courses','block_starredcourses_external','get_starred_courses','block/starredcourses/classes/external.php','block_starredcourses','','moodle_mobile_app'),(584,'media_videojs_get_language','media_videojs\\external\\get_language','execute',NULL,'media_videojs','',NULL),(585,'report_competency_data_for_report','report_competency\\external','data_for_report',NULL,'report_competency','moodle/competency:coursecompetencyview',NULL),(586,'report_insights_set_notuseful_prediction','report_insights\\external','set_notuseful_prediction',NULL,'report_insights','','moodle_mobile_app'),(587,'report_insights_set_fixed_prediction','report_insights\\external','set_fixed_prediction',NULL,'report_insights','','moodle_mobile_app'),(588,'report_insights_action_executed','report_insights\\external','action_executed',NULL,'report_insights','','moodle_mobile_app'),(589,'gradereport_overview_get_course_grades','gradereport_overview_external','get_course_grades',NULL,'gradereport_overview','','moodle_mobile_app'),(590,'gradereport_overview_view_grade_report','gradereport_overview_external','view_grade_report',NULL,'gradereport_overview','gradereport/overview:view','moodle_mobile_app'),(591,'gradereport_user_get_grades_table','gradereport_user_external','get_grades_table','grade/report/user/externallib.php','gradereport_user','gradereport/user:view','moodle_mobile_app'),(592,'gradereport_user_view_grade_report','gradereport_user_external','view_grade_report','grade/report/user/externallib.php','gradereport_user','gradereport/user:view','moodle_mobile_app'),(593,'gradereport_user_get_grade_items','gradereport_user_external','get_grade_items','grade/report/user/externallib.php','gradereport_user','gradereport/user:view','moodle_mobile_app'),(594,'gradingform_guide_grader_gradingpanel_fetch','gradingform_guide\\grades\\grader\\gradingpanel\\external\\fetch','execute',NULL,'gradingform_guide','',NULL),(595,'gradingform_guide_grader_gradingpanel_store','gradingform_guide\\grades\\grader\\gradingpanel\\external\\store','execute',NULL,'gradingform_guide','',NULL),(596,'gradingform_rubric_grader_gradingpanel_fetch','gradingform_rubric\\grades\\grader\\gradingpanel\\external\\fetch','execute',NULL,'gradingform_rubric','',NULL),(597,'gradingform_rubric_grader_gradingpanel_store','gradingform_rubric\\grades\\grader\\gradingpanel\\external\\store','execute',NULL,'gradingform_rubric','',NULL),(598,'qbank_columnsortorder_set_columnbank_order','qbank_columnsortorder\\external\\set_columnbank_order','execute',NULL,'qbank_columnsortorder','',NULL),(599,'qbank_editquestion_set_status','qbank_editquestion\\external\\update_question_version_status','execute',NULL,'qbank_editquestion','',NULL),(600,'qbank_tagquestion_submit_tags_form','qbank_tagquestion\\external\\submit_tags','execute',NULL,'qbank_tagquestion','',NULL),(601,'tool_analytics_potential_contexts','tool_analytics\\external','potential_contexts',NULL,'tool_analytics','','moodle_mobile_app'),(602,'tool_behat_get_entity_generator','tool_behat\\external\\get_entity_generator','execute',NULL,'tool_behat','moodle/site:config',NULL),(603,'tool_dataprivacy_cancel_data_request','tool_dataprivacy\\external','cancel_data_request',NULL,'tool_dataprivacy','',NULL),(604,'tool_dataprivacy_contact_dpo','tool_dataprivacy\\external','contact_dpo',NULL,'tool_dataprivacy','',NULL),(605,'tool_dataprivacy_mark_complete','tool_dataprivacy\\external','mark_complete',NULL,'tool_dataprivacy','tool/dataprivacy:managedatarequests',NULL),(606,'tool_dataprivacy_get_data_request','tool_dataprivacy\\external','get_data_request',NULL,'tool_dataprivacy','tool/dataprivacy:managedatarequests',NULL),(607,'tool_dataprivacy_approve_data_request','tool_dataprivacy\\external','approve_data_request',NULL,'tool_dataprivacy','tool/dataprivacy:managedatarequests',NULL),(608,'tool_dataprivacy_bulk_approve_data_requests','tool_dataprivacy\\external','bulk_approve_data_requests',NULL,'tool_dataprivacy','tool/dataprivacy:managedatarequests',NULL),(609,'tool_dataprivacy_deny_data_request','tool_dataprivacy\\external','deny_data_request',NULL,'tool_dataprivacy','tool/dataprivacy:managedatarequests',NULL),(610,'tool_dataprivacy_bulk_deny_data_requests','tool_dataprivacy\\external','bulk_deny_data_requests',NULL,'tool_dataprivacy','tool/dataprivacy:managedatarequests',NULL),(611,'tool_dataprivacy_get_users','tool_dataprivacy\\external','get_users',NULL,'tool_dataprivacy','tool/dataprivacy:managedatarequests',NULL),(612,'tool_dataprivacy_create_purpose_form','tool_dataprivacy\\external','create_purpose_form',NULL,'tool_dataprivacy','',NULL),(613,'tool_dataprivacy_create_category_form','tool_dataprivacy\\external','create_category_form',NULL,'tool_dataprivacy','',NULL),(614,'tool_dataprivacy_delete_purpose','tool_dataprivacy\\external','delete_purpose',NULL,'tool_dataprivacy','',NULL),(615,'tool_dataprivacy_delete_category','tool_dataprivacy\\external','delete_category',NULL,'tool_dataprivacy','',NULL),(616,'tool_dataprivacy_set_contextlevel_form','tool_dataprivacy\\external','set_contextlevel_form',NULL,'tool_dataprivacy','',NULL),(617,'tool_dataprivacy_set_context_form','tool_dataprivacy\\external','set_context_form',NULL,'tool_dataprivacy','',NULL),(618,'tool_dataprivacy_tree_extra_branches','tool_dataprivacy\\external','tree_extra_branches',NULL,'tool_dataprivacy','',NULL),(619,'tool_dataprivacy_confirm_contexts_for_deletion','tool_dataprivacy\\external','confirm_contexts_for_deletion',NULL,'tool_dataprivacy','',NULL),(620,'tool_dataprivacy_set_context_defaults','tool_dataprivacy\\external','set_context_defaults',NULL,'tool_dataprivacy','tool/dataprivacy:managedataregistry',NULL),(621,'tool_dataprivacy_get_category_options','tool_dataprivacy\\external','get_category_options',NULL,'tool_dataprivacy','tool/dataprivacy:managedataregistry',NULL),(622,'tool_dataprivacy_get_purpose_options','tool_dataprivacy\\external','get_purpose_options',NULL,'tool_dataprivacy','tool/dataprivacy:managedataregistry',NULL),(623,'tool_dataprivacy_get_activity_options','tool_dataprivacy\\external','get_activity_options',NULL,'tool_dataprivacy','tool/dataprivacy:managedataregistry',NULL),(624,'tool_lp_data_for_competency_frameworks_manage_page','tool_lp\\external','data_for_competency_frameworks_manage_page',NULL,'tool_lp','moodle/competency:competencyview',NULL),(625,'tool_lp_data_for_competency_summary','tool_lp\\external','data_for_competency_summary',NULL,'tool_lp','moodle/competency:competencyview',NULL),(626,'tool_lp_data_for_competencies_manage_page','tool_lp\\external','data_for_competencies_manage_page',NULL,'tool_lp','moodle/competency:competencyview',NULL),(627,'tool_lp_list_courses_using_competency','tool_lp\\external','list_courses_using_competency',NULL,'tool_lp','moodle/competency:coursecompetencyview',NULL),(628,'tool_lp_data_for_course_competencies_page','tool_lp\\external','data_for_course_competencies_page',NULL,'tool_lp','moodle/competency:coursecompetencyview','moodle_mobile_app'),(629,'tool_lp_data_for_template_competencies_page','tool_lp\\external','data_for_template_competencies_page',NULL,'tool_lp','moodle/competency:templateview',NULL),(630,'tool_lp_data_for_templates_manage_page','tool_lp\\external','data_for_templates_manage_page',NULL,'tool_lp','moodle/competency:templateview',NULL),(631,'tool_lp_data_for_plans_page','tool_lp\\external','data_for_plans_page',NULL,'tool_lp','moodle/competency:planviewown','moodle_mobile_app'),(632,'tool_lp_data_for_plan_page','tool_lp\\external','data_for_plan_page',NULL,'tool_lp','moodle/competency:planview','moodle_mobile_app'),(633,'tool_lp_data_for_related_competencies_section','tool_lp\\external','data_for_related_competencies_section',NULL,'tool_lp','moodle/competency:competencyview',NULL),(634,'tool_lp_search_users','tool_lp\\external','search_users',NULL,'tool_lp','',NULL),(635,'tool_lp_search_cohorts','core_cohort_external','search_cohorts','cohort/externallib.php','tool_lp','moodle/cohort:view',NULL),(636,'tool_lp_data_for_user_evidence_list_page','tool_lp\\external','data_for_user_evidence_list_page',NULL,'tool_lp','moodle/competency:userevidenceview','moodle_mobile_app'),(637,'tool_lp_data_for_user_evidence_page','tool_lp\\external','data_for_user_evidence_page',NULL,'tool_lp','moodle/competency:userevidenceview','moodle_mobile_app'),(638,'tool_lp_data_for_user_competency_summary','tool_lp\\external','data_for_user_competency_summary',NULL,'tool_lp','moodle/competency:planview','moodle_mobile_app'),(639,'tool_lp_data_for_user_competency_summary_in_plan','tool_lp\\external','data_for_user_competency_summary_in_plan',NULL,'tool_lp','moodle/competency:planview','moodle_mobile_app'),(640,'tool_lp_data_for_user_competency_summary_in_course','tool_lp\\external','data_for_user_competency_summary_in_course',NULL,'tool_lp','moodle/competency:coursecompetencyview','moodle_mobile_app'),(641,'tool_mobile_get_plugins_supporting_mobile','tool_mobile\\external','get_plugins_supporting_mobile',NULL,'tool_mobile','','moodle_mobile_app'),(642,'tool_mobile_get_public_config','tool_mobile\\external','get_public_config',NULL,'tool_mobile','','moodle_mobile_app'),(643,'tool_mobile_get_config','tool_mobile\\external','get_config',NULL,'tool_mobile','','moodle_mobile_app'),(644,'tool_mobile_get_autologin_key','tool_mobile\\external','get_autologin_key',NULL,'tool_mobile','','moodle_mobile_app'),(645,'tool_mobile_get_content','tool_mobile\\external','get_content',NULL,'tool_mobile','','moodle_mobile_app'),(646,'tool_mobile_call_external_functions','tool_mobile\\external','call_external_functions',NULL,'tool_mobile','','moodle_mobile_app'),(647,'tool_mobile_validate_subscription_key','tool_mobile\\external','validate_subscription_key',NULL,'tool_mobile','','moodle_mobile_app'),(648,'tool_mobile_get_tokens_for_qr_login','tool_mobile\\external','get_tokens_for_qr_login',NULL,'tool_mobile','','moodle_mobile_app'),(649,'tool_moodlenet_verify_webfinger','tool_moodlenet\\external','verify_webfinger',NULL,'tool_moodlenet','','moodle_mobile_app'),(650,'tool_moodlenet_search_courses','tool_moodlenet\\external','search_courses',NULL,'tool_moodlenet','','moodle_mobile_app'),(651,'tool_policy_get_policy_version','tool_policy\\external','get_policy_version',NULL,'tool_policy','',NULL),(652,'tool_policy_submit_accept_on_behalf','tool_policy\\external','submit_accept_on_behalf',NULL,'tool_policy','',NULL),(653,'tool_templatelibrary_list_templates','tool_templatelibrary\\external','list_templates',NULL,'tool_templatelibrary','',NULL),(654,'tool_templatelibrary_load_canonical_template','tool_templatelibrary\\external','load_canonical_template',NULL,'tool_templatelibrary','',NULL),(655,'tool_usertours_fetch_and_start_tour','tool_usertours\\external\\tour','fetch_and_start_tour',NULL,'tool_usertours','',NULL),(656,'tool_usertours_step_shown','tool_usertours\\external\\tour','step_shown',NULL,'tool_usertours','',NULL),(657,'tool_usertours_complete_tour','tool_usertours\\external\\tour','complete_tour',NULL,'tool_usertours','',NULL),(658,'tool_usertours_reset_tour','tool_usertours\\external\\tour','reset_tour',NULL,'tool_usertours','',NULL),(659,'tool_xmldb_invoke_move_action','tool_xmldb_external','invoke_move_action',NULL,'tool_xmldb','',NULL),(660,'paygw_paypal_get_config_for_js','paygw_paypal\\external\\get_config_for_js','execute',NULL,'paygw_paypal','',NULL),(661,'paygw_paypal_create_transaction_complete','paygw_paypal\\external\\transaction_complete','execute',NULL,'paygw_paypal','',NULL),(662,'quizaccess_seb_validate_quiz_keys','quizaccess_seb\\external\\validate_quiz_keys','execute',NULL,'quizaccess_seb','',NULL),(663,'theme_moove_fontsize','theme_moove\\api\\accessibility','fontsize','theme_moove/classes/api/accessibility.php','theme_moove','',NULL),(664,'theme_moove_sitecolor','theme_moove\\api\\accessibility','sitecolor',NULL,'theme_moove','',NULL),(665,'theme_moove_savethemesettings','theme_moove\\api\\accessibility','savethemesettings',NULL,'theme_moove','',NULL),(666,'theme_moove_getthemesettings','theme_moove\\api\\accessibility','getthemesettings',NULL,'theme_moove','',NULL),(667,'mod_attendance_add_attendance','mod_attendance_external','add_attendance','mod/attendance/externallib.php','mod_attendance','',NULL),(668,'mod_attendance_remove_attendance','mod_attendance_external','remove_attendance','mod/attendance/externallib.php','mod_attendance','',NULL),(669,'mod_attendance_add_session','mod_attendance_external','add_session','mod/attendance/externallib.php','mod_attendance','',NULL),(670,'mod_attendance_remove_session','mod_attendance_external','remove_session','mod/attendance/externallib.php','mod_attendance','',NULL),(671,'mod_attendance_get_courses_with_today_sessions','mod_attendance_external','get_courses_with_today_sessions','mod/attendance/externallib.php','mod_attendance','',NULL),(672,'mod_attendance_get_session','mod_attendance_external','get_session','mod/attendance/externallib.php','mod_attendance','',NULL),(673,'mod_attendance_update_user_status','mod_attendance_external','update_user_status','mod/attendance/externallib.php','mod_attendance','',NULL),(674,'mod_attendance_get_sessions','mod_attendance_external','get_sessions','mod/attendance/externallib.php','mod_attendance','',NULL);
/*!40000 ALTER TABLE `mdl_external_functions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_external_services`
--

DROP TABLE IF EXISTS `mdl_external_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_external_services` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL,
  `requiredcapability` varchar(150) DEFAULT NULL,
  `restrictedusers` tinyint(1) NOT NULL,
  `component` varchar(100) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) DEFAULT NULL,
  `shortname` varchar(255) DEFAULT NULL,
  `downloadfiles` tinyint(1) NOT NULL DEFAULT 0,
  `uploadfiles` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_exteserv_nam_uix` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='built in and custom external services';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_external_services`
--

LOCK TABLES `mdl_external_services` WRITE;
/*!40000 ALTER TABLE `mdl_external_services` DISABLE KEYS */;
INSERT INTO `mdl_external_services` VALUES (1,'Moodle mobile web service',0,NULL,0,'moodle',1756252045,1756264394,'moodle_mobile_app',1,1),(2,'Attendance',1,NULL,0,'mod_attendance',1756521931,NULL,'mod_attendance',0,0);
/*!40000 ALTER TABLE `mdl_external_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_external_services_functions`
--

DROP TABLE IF EXISTS `mdl_external_services_functions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_external_services_functions` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `externalserviceid` bigint(10) NOT NULL,
  `functionname` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `mdl_exteservfunc_ext_ix` (`externalserviceid`)
) ENGINE=InnoDB AUTO_INCREMENT=396 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='lists functions available in each service group';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_external_services_functions`
--

LOCK TABLES `mdl_external_services_functions` WRITE;
/*!40000 ALTER TABLE `mdl_external_services_functions` DISABLE KEYS */;
INSERT INTO `mdl_external_services_functions` VALUES (1,1,'core_badges_get_user_badges'),(2,1,'core_blog_get_entries'),(3,1,'core_blog_view_entries'),(4,1,'core_calendar_get_calendar_monthly_view'),(5,1,'core_calendar_get_calendar_day_view'),(6,1,'core_calendar_get_calendar_upcoming_view'),(7,1,'core_calendar_update_event_start_day'),(8,1,'core_calendar_create_calendar_events'),(9,1,'core_calendar_delete_calendar_events'),(10,1,'core_calendar_get_calendar_events'),(11,1,'core_calendar_get_action_events_by_timesort'),(12,1,'core_calendar_get_action_events_by_course'),(13,1,'core_calendar_get_action_events_by_courses'),(14,1,'core_calendar_get_calendar_event_by_id'),(15,1,'core_calendar_submit_create_update_form'),(16,1,'core_calendar_get_calendar_access_information'),(17,1,'core_calendar_get_allowed_event_types'),(18,1,'core_calendar_get_calendar_export_token'),(19,1,'core_comment_get_comments'),(20,1,'core_comment_add_comments'),(21,1,'core_comment_delete_comments'),(22,1,'core_completion_get_activities_completion_status'),(23,1,'core_completion_get_course_completion_status'),(24,1,'core_completion_mark_course_self_completed'),(25,1,'core_completion_update_activity_completion_status_manually'),(26,1,'core_course_get_categories'),(27,1,'core_course_get_contents'),(28,1,'core_course_get_course_module'),(29,1,'core_course_get_course_module_by_instance'),(30,1,'core_course_get_courses'),(31,1,'core_course_search_courses'),(32,1,'core_course_view_course'),(33,1,'core_course_get_user_navigation_options'),(34,1,'core_course_get_user_administration_options'),(35,1,'core_course_get_courses_by_field'),(36,1,'core_course_check_updates'),(37,1,'core_course_get_updates_since'),(38,1,'core_course_get_enrolled_courses_by_timeline_classification'),(39,1,'core_course_get_enrolled_courses_with_action_events_by_timeline_classification'),(40,1,'core_course_get_recent_courses'),(41,1,'core_course_set_favourite_courses'),(42,1,'core_enrol_get_course_enrolment_methods'),(43,1,'core_enrol_get_enrolled_users'),(44,1,'core_enrol_search_users'),(45,1,'core_enrol_get_users_courses'),(46,1,'core_files_get_files'),(47,1,'core_files_delete_draft_files'),(48,1,'core_files_get_unused_draft_itemid'),(49,1,'core_get_component_strings'),(50,1,'core_grades_grader_gradingpanel_point_fetch'),(51,1,'core_grades_grader_gradingpanel_point_store'),(52,1,'core_grades_grader_gradingpanel_scale_fetch'),(53,1,'core_grades_grader_gradingpanel_scale_store'),(54,1,'core_group_get_activity_allowed_groups'),(55,1,'core_group_get_activity_groupmode'),(56,1,'core_group_get_course_groupings'),(57,1,'core_group_get_course_groups'),(58,1,'core_group_get_course_user_groups'),(59,1,'core_message_mute_conversations'),(60,1,'core_message_unmute_conversations'),(61,1,'core_message_block_user'),(62,1,'core_message_get_contact_requests'),(63,1,'core_message_create_contact_request'),(64,1,'core_message_confirm_contact_request'),(65,1,'core_message_decline_contact_request'),(66,1,'core_message_get_received_contact_requests_count'),(67,1,'core_message_delete_contacts'),(68,1,'core_message_delete_conversations_by_id'),(69,1,'core_message_delete_message'),(70,1,'core_message_get_blocked_users'),(71,1,'core_message_data_for_messagearea_search_messages'),(72,1,'core_message_message_search_users'),(73,1,'core_message_get_user_contacts'),(74,1,'core_message_get_conversations'),(75,1,'core_message_get_conversation'),(76,1,'core_message_get_conversation_between_users'),(77,1,'core_message_get_self_conversation'),(78,1,'core_message_get_messages'),(79,1,'core_message_get_conversation_counts'),(80,1,'core_message_get_unread_conversation_counts'),(81,1,'core_message_get_conversation_members'),(82,1,'core_message_get_member_info'),(83,1,'core_message_get_unread_conversations_count'),(84,1,'core_message_mark_all_notifications_as_read'),(85,1,'core_message_mark_all_conversation_messages_as_read'),(86,1,'core_message_mark_message_read'),(87,1,'core_message_mark_notification_read'),(88,1,'core_message_message_processor_config_form'),(89,1,'core_message_search_contacts'),(90,1,'core_message_send_instant_messages'),(91,1,'core_message_send_messages_to_conversation'),(92,1,'core_message_get_conversation_messages'),(93,1,'core_message_unblock_user'),(94,1,'core_message_get_user_notification_preferences'),(95,1,'core_message_get_user_message_preferences'),(96,1,'core_message_set_favourite_conversations'),(97,1,'core_message_unset_favourite_conversations'),(98,1,'core_message_delete_message_for_all_users'),(99,1,'core_message_get_unread_notification_count'),(100,1,'core_notes_create_notes'),(101,1,'core_notes_delete_notes'),(102,1,'core_notes_get_course_notes'),(103,1,'core_notes_view_notes'),(104,1,'core_question_update_flag'),(105,1,'core_rating_get_item_ratings'),(106,1,'core_rating_add_rating'),(107,1,'core_tag_get_tagindex'),(108,1,'core_tag_get_tagindex_per_area'),(109,1,'core_tag_get_tag_areas'),(110,1,'core_tag_get_tag_collections'),(111,1,'core_tag_get_tag_cloud'),(112,1,'core_user_add_user_device'),(113,1,'core_user_add_user_private_files'),(114,1,'core_user_get_course_user_profiles'),(115,1,'core_user_get_users_by_field'),(116,1,'core_user_remove_user_device'),(117,1,'core_user_update_user_preferences'),(118,1,'core_user_view_user_list'),(119,1,'core_user_view_user_profile'),(120,1,'core_user_get_user_preferences'),(121,1,'core_user_update_picture'),(122,1,'core_user_set_user_preferences'),(123,1,'core_user_agree_site_policy'),(124,1,'core_user_get_private_files_info'),(125,1,'core_competency_competency_viewed'),(126,1,'core_competency_list_course_competencies'),(127,1,'core_competency_get_scale_values'),(128,1,'core_competency_user_competency_viewed'),(129,1,'core_competency_user_competency_viewed_in_plan'),(130,1,'core_competency_user_competency_viewed_in_course'),(131,1,'core_competency_user_competency_plan_viewed'),(132,1,'core_competency_grade_competency_in_course'),(133,1,'core_competency_delete_evidence'),(134,1,'core_webservice_get_site_info'),(135,1,'core_block_get_course_blocks'),(136,1,'core_block_get_dashboard_blocks'),(137,1,'core_block_fetch_addable_blocks'),(138,1,'core_filters_get_available_in_context'),(139,1,'core_h5p_get_trusted_h5p_file'),(140,1,'core_table_get_dynamic_table_content'),(141,1,'core_xapi_statement_post'),(142,1,'mod_assign_get_grades'),(143,1,'mod_assign_get_assignments'),(144,1,'mod_assign_get_submissions'),(145,1,'mod_assign_get_user_flags'),(146,1,'mod_assign_set_user_flags'),(147,1,'mod_assign_get_user_mappings'),(148,1,'mod_assign_revert_submissions_to_draft'),(149,1,'mod_assign_lock_submissions'),(150,1,'mod_assign_unlock_submissions'),(151,1,'mod_assign_save_submission'),(152,1,'mod_assign_submit_for_grading'),(153,1,'mod_assign_save_grade'),(154,1,'mod_assign_save_grades'),(155,1,'mod_assign_save_user_extensions'),(156,1,'mod_assign_reveal_identities'),(157,1,'mod_assign_view_grading_table'),(158,1,'mod_assign_view_submission_status'),(159,1,'mod_assign_get_submission_status'),(160,1,'mod_assign_list_participants'),(161,1,'mod_assign_submit_grading_form'),(162,1,'mod_assign_get_participant'),(163,1,'mod_assign_view_assign'),(164,1,'mod_assign_start_submission'),(165,1,'mod_bigbluebuttonbn_can_join'),(166,1,'mod_bigbluebuttonbn_get_recordings'),(167,1,'mod_bigbluebuttonbn_get_recordings_to_import'),(168,1,'mod_bigbluebuttonbn_update_recording'),(169,1,'mod_bigbluebuttonbn_end_meeting'),(170,1,'mod_bigbluebuttonbn_completion_validate'),(171,1,'mod_bigbluebuttonbn_meeting_info'),(172,1,'mod_bigbluebuttonbn_get_bigbluebuttonbns_by_courses'),(173,1,'mod_bigbluebuttonbn_view_bigbluebuttonbn'),(174,1,'mod_bigbluebuttonbn_get_join_url'),(175,1,'mod_book_view_book'),(176,1,'mod_book_get_books_by_courses'),(177,1,'mod_chat_login_user'),(178,1,'mod_chat_get_chat_users'),(179,1,'mod_chat_send_chat_message'),(180,1,'mod_chat_get_chat_latest_messages'),(181,1,'mod_chat_view_chat'),(182,1,'mod_chat_get_chats_by_courses'),(183,1,'mod_chat_get_sessions'),(184,1,'mod_chat_get_session_messages'),(185,1,'mod_choice_get_choice_results'),(186,1,'mod_choice_get_choice_options'),(187,1,'mod_choice_submit_choice_response'),(188,1,'mod_choice_view_choice'),(189,1,'mod_choice_get_choices_by_courses'),(190,1,'mod_choice_delete_choice_responses'),(191,1,'mod_data_get_databases_by_courses'),(192,1,'mod_data_view_database'),(193,1,'mod_data_get_data_access_information'),(194,1,'mod_data_get_entries'),(195,1,'mod_data_get_entry'),(196,1,'mod_data_get_fields'),(197,1,'mod_data_search_entries'),(198,1,'mod_data_approve_entry'),(199,1,'mod_data_delete_entry'),(200,1,'mod_data_add_entry'),(201,1,'mod_data_update_entry'),(202,1,'mod_feedback_get_feedbacks_by_courses'),(203,1,'mod_feedback_get_feedback_access_information'),(204,1,'mod_feedback_view_feedback'),(205,1,'mod_feedback_get_current_completed_tmp'),(206,1,'mod_feedback_get_items'),(207,1,'mod_feedback_launch_feedback'),(208,1,'mod_feedback_get_page_items'),(209,1,'mod_feedback_process_page'),(210,1,'mod_feedback_get_analysis'),(211,1,'mod_feedback_get_unfinished_responses'),(212,1,'mod_feedback_get_finished_responses'),(213,1,'mod_feedback_get_non_respondents'),(214,1,'mod_feedback_get_responses_analysis'),(215,1,'mod_feedback_get_last_completed'),(216,1,'mod_folder_view_folder'),(217,1,'mod_folder_get_folders_by_courses'),(218,1,'mod_forum_get_forums_by_courses'),(219,1,'mod_forum_get_discussion_posts'),(220,1,'mod_forum_get_forum_discussions_paginated'),(221,1,'mod_forum_get_forum_discussions'),(222,1,'mod_forum_view_forum'),(223,1,'mod_forum_view_forum_discussion'),(224,1,'mod_forum_add_discussion_post'),(225,1,'mod_forum_add_discussion'),(226,1,'mod_forum_can_add_discussion'),(227,1,'mod_forum_get_forum_access_information'),(228,1,'mod_forum_set_subscription_state'),(229,1,'mod_forum_set_lock_state'),(230,1,'mod_forum_toggle_favourite_state'),(231,1,'mod_forum_set_pin_state'),(232,1,'mod_forum_delete_post'),(233,1,'mod_forum_get_discussion_post'),(234,1,'mod_forum_prepare_draft_area_for_post'),(235,1,'mod_forum_update_discussion_post'),(236,1,'mod_glossary_get_glossaries_by_courses'),(237,1,'mod_glossary_view_glossary'),(238,1,'mod_glossary_view_entry'),(239,1,'mod_glossary_get_entries_by_letter'),(240,1,'mod_glossary_get_entries_by_date'),(241,1,'mod_glossary_get_categories'),(242,1,'mod_glossary_get_entries_by_category'),(243,1,'mod_glossary_get_authors'),(244,1,'mod_glossary_get_entries_by_author'),(245,1,'mod_glossary_get_entries_by_author_id'),(246,1,'mod_glossary_get_entries_by_search'),(247,1,'mod_glossary_get_entries_by_term'),(248,1,'mod_glossary_get_entries_to_approve'),(249,1,'mod_glossary_get_entry_by_id'),(250,1,'mod_glossary_add_entry'),(251,1,'mod_glossary_delete_entry'),(252,1,'mod_glossary_update_entry'),(253,1,'mod_glossary_prepare_entry_for_edition'),(254,1,'mod_h5pactivity_get_h5pactivity_access_information'),(255,1,'mod_h5pactivity_view_h5pactivity'),(256,1,'mod_h5pactivity_get_attempts'),(257,1,'mod_h5pactivity_get_results'),(258,1,'mod_h5pactivity_get_h5pactivities_by_courses'),(259,1,'mod_h5pactivity_log_report_viewed'),(260,1,'mod_h5pactivity_get_user_attempts'),(261,1,'mod_imscp_view_imscp'),(262,1,'mod_imscp_get_imscps_by_courses'),(263,1,'mod_label_get_labels_by_courses'),(264,1,'mod_lesson_get_lessons_by_courses'),(265,1,'mod_lesson_get_lesson_access_information'),(266,1,'mod_lesson_view_lesson'),(267,1,'mod_lesson_get_questions_attempts'),(268,1,'mod_lesson_get_user_grade'),(269,1,'mod_lesson_get_user_attempt_grade'),(270,1,'mod_lesson_get_content_pages_viewed'),(271,1,'mod_lesson_get_user_timers'),(272,1,'mod_lesson_get_pages'),(273,1,'mod_lesson_launch_attempt'),(274,1,'mod_lesson_get_page_data'),(275,1,'mod_lesson_process_page'),(276,1,'mod_lesson_finish_attempt'),(277,1,'mod_lesson_get_attempts_overview'),(278,1,'mod_lesson_get_user_attempt'),(279,1,'mod_lesson_get_pages_possible_jumps'),(280,1,'mod_lesson_get_lesson'),(281,1,'mod_lti_get_tool_launch_data'),(282,1,'mod_lti_get_ltis_by_courses'),(283,1,'mod_lti_view_lti'),(284,1,'mod_page_view_page'),(285,1,'mod_page_get_pages_by_courses'),(286,1,'mod_quiz_get_quizzes_by_courses'),(287,1,'mod_quiz_view_quiz'),(288,1,'mod_quiz_get_user_attempts'),(289,1,'mod_quiz_get_user_best_grade'),(290,1,'mod_quiz_get_combined_review_options'),(291,1,'mod_quiz_start_attempt'),(292,1,'mod_quiz_get_attempt_data'),(293,1,'mod_quiz_get_attempt_summary'),(294,1,'mod_quiz_save_attempt'),(295,1,'mod_quiz_process_attempt'),(296,1,'mod_quiz_get_attempt_review'),(297,1,'mod_quiz_view_attempt'),(298,1,'mod_quiz_view_attempt_summary'),(299,1,'mod_quiz_view_attempt_review'),(300,1,'mod_quiz_get_quiz_feedback_for_grade'),(301,1,'mod_quiz_get_quiz_access_information'),(302,1,'mod_quiz_get_attempt_access_information'),(303,1,'mod_quiz_get_quiz_required_qtypes'),(304,1,'mod_resource_view_resource'),(305,1,'mod_resource_get_resources_by_courses'),(306,1,'mod_scorm_view_scorm'),(307,1,'mod_scorm_get_scorm_attempt_count'),(308,1,'mod_scorm_get_scorm_scoes'),(309,1,'mod_scorm_get_scorm_user_data'),(310,1,'mod_scorm_insert_scorm_tracks'),(311,1,'mod_scorm_get_scorm_sco_tracks'),(312,1,'mod_scorm_get_scorms_by_courses'),(313,1,'mod_scorm_launch_sco'),(314,1,'mod_scorm_get_scorm_access_information'),(315,1,'mod_survey_get_surveys_by_courses'),(316,1,'mod_survey_view_survey'),(317,1,'mod_survey_get_questions'),(318,1,'mod_survey_submit_answers'),(319,1,'mod_url_view_url'),(320,1,'mod_url_get_urls_by_courses'),(321,1,'mod_wiki_get_wikis_by_courses'),(322,1,'mod_wiki_view_wiki'),(323,1,'mod_wiki_view_page'),(324,1,'mod_wiki_get_subwikis'),(325,1,'mod_wiki_get_subwiki_pages'),(326,1,'mod_wiki_get_subwiki_files'),(327,1,'mod_wiki_get_page_contents'),(328,1,'mod_wiki_get_page_for_editing'),(329,1,'mod_wiki_new_page'),(330,1,'mod_wiki_edit_page'),(331,1,'mod_workshop_get_workshops_by_courses'),(332,1,'mod_workshop_get_workshop_access_information'),(333,1,'mod_workshop_get_user_plan'),(334,1,'mod_workshop_view_workshop'),(335,1,'mod_workshop_add_submission'),(336,1,'mod_workshop_update_submission'),(337,1,'mod_workshop_delete_submission'),(338,1,'mod_workshop_get_submissions'),(339,1,'mod_workshop_get_submission'),(340,1,'mod_workshop_get_submission_assessments'),(341,1,'mod_workshop_get_assessment'),(342,1,'mod_workshop_get_assessment_form_definition'),(343,1,'mod_workshop_get_reviewer_assessments'),(344,1,'mod_workshop_update_assessment'),(345,1,'mod_workshop_get_grades'),(346,1,'mod_workshop_evaluate_assessment'),(347,1,'mod_workshop_get_grades_report'),(348,1,'mod_workshop_view_submission'),(349,1,'mod_workshop_evaluate_submission'),(350,1,'enrol_guest_get_instance_info'),(351,1,'enrol_self_get_instance_info'),(352,1,'enrol_self_enrol_user'),(353,1,'message_airnotifier_is_system_configured'),(354,1,'message_airnotifier_are_notification_preferences_configured'),(355,1,'message_airnotifier_get_user_devices'),(356,1,'message_airnotifier_enable_device'),(357,1,'message_popup_get_popup_notifications'),(358,1,'message_popup_get_unread_popup_notification_count'),(359,1,'block_recentlyaccesseditems_get_recent_items'),(360,1,'block_starredcourses_get_starred_courses'),(361,1,'report_insights_set_notuseful_prediction'),(362,1,'report_insights_set_fixed_prediction'),(363,1,'report_insights_action_executed'),(364,1,'gradereport_overview_get_course_grades'),(365,1,'gradereport_overview_view_grade_report'),(366,1,'gradereport_user_get_grades_table'),(367,1,'gradereport_user_view_grade_report'),(368,1,'gradereport_user_get_grade_items'),(369,1,'tool_analytics_potential_contexts'),(370,1,'tool_lp_data_for_course_competencies_page'),(371,1,'tool_lp_data_for_plans_page'),(372,1,'tool_lp_data_for_plan_page'),(373,1,'tool_lp_data_for_user_evidence_list_page'),(374,1,'tool_lp_data_for_user_evidence_page'),(375,1,'tool_lp_data_for_user_competency_summary'),(376,1,'tool_lp_data_for_user_competency_summary_in_plan'),(377,1,'tool_lp_data_for_user_competency_summary_in_course'),(378,1,'tool_mobile_get_plugins_supporting_mobile'),(379,1,'tool_mobile_get_public_config'),(380,1,'tool_mobile_get_config'),(381,1,'tool_mobile_get_autologin_key'),(382,1,'tool_mobile_get_content'),(383,1,'tool_mobile_call_external_functions'),(384,1,'tool_mobile_validate_subscription_key'),(385,1,'tool_mobile_get_tokens_for_qr_login'),(386,1,'tool_moodlenet_verify_webfinger'),(387,1,'tool_moodlenet_search_courses'),(388,2,'mod_attendance_add_attendance'),(389,2,'mod_attendance_remove_attendance'),(390,2,'mod_attendance_add_session'),(391,2,'mod_attendance_remove_session'),(392,2,'mod_attendance_get_courses_with_today_sessions'),(393,2,'mod_attendance_get_session'),(394,2,'mod_attendance_update_user_status'),(395,2,'mod_attendance_get_sessions');
/*!40000 ALTER TABLE `mdl_external_services_functions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_external_services_users`
--

DROP TABLE IF EXISTS `mdl_external_services_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_external_services_users` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `externalserviceid` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `iprestriction` varchar(255) DEFAULT NULL,
  `validuntil` bigint(10) DEFAULT NULL,
  `timecreated` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_exteservuser_ext_ix` (`externalserviceid`),
  KEY `mdl_exteservuser_use_ix` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='users allowed to use services with restricted users flag';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_external_services_users`
--

LOCK TABLES `mdl_external_services_users` WRITE;
/*!40000 ALTER TABLE `mdl_external_services_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_external_services_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_external_tokens`
--

DROP TABLE IF EXISTS `mdl_external_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_external_tokens` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `token` varchar(128) NOT NULL DEFAULT '',
  `privatetoken` varchar(64) DEFAULT NULL,
  `tokentype` smallint(4) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `externalserviceid` bigint(10) NOT NULL,
  `sid` varchar(128) DEFAULT NULL,
  `contextid` bigint(10) NOT NULL,
  `creatorid` bigint(10) NOT NULL DEFAULT 1,
  `iprestriction` varchar(255) DEFAULT NULL,
  `validuntil` bigint(10) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `lastaccess` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_extetoke_tok_ix` (`token`),
  KEY `mdl_extetoke_use_ix` (`userid`),
  KEY `mdl_extetoke_ext_ix` (`externalserviceid`),
  KEY `mdl_extetoke_con_ix` (`contextid`),
  KEY `mdl_extetoke_cre_ix` (`creatorid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Security tokens for accessing of external services';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_external_tokens`
--

LOCK TABLES `mdl_external_tokens` WRITE;
/*!40000 ALTER TABLE `mdl_external_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_external_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_favourite`
--

DROP TABLE IF EXISTS `mdl_favourite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_favourite` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `component` varchar(100) NOT NULL DEFAULT '',
  `itemtype` varchar(100) NOT NULL DEFAULT '',
  `itemid` bigint(10) NOT NULL,
  `contextid` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `ordering` bigint(10) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_favo_comiteiteconuse_uix` (`component`,`itemtype`,`itemid`,`contextid`,`userid`),
  KEY `mdl_favo_con_ix` (`contextid`),
  KEY `mdl_favo_use_ix` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores the relationship between an arbitrary item (itemtype,';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_favourite`
--

LOCK TABLES `mdl_favourite` WRITE;
/*!40000 ALTER TABLE `mdl_favourite` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_favourite` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_feedback`
--

DROP TABLE IF EXISTS `mdl_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_feedback` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `intro` longtext NOT NULL,
  `introformat` smallint(4) NOT NULL DEFAULT 0,
  `anonymous` tinyint(1) NOT NULL DEFAULT 1,
  `email_notification` tinyint(1) NOT NULL DEFAULT 1,
  `multiple_submit` tinyint(1) NOT NULL DEFAULT 1,
  `autonumbering` tinyint(1) NOT NULL DEFAULT 1,
  `site_after_submit` varchar(255) NOT NULL DEFAULT '',
  `page_after_submit` longtext NOT NULL,
  `page_after_submitformat` tinyint(2) NOT NULL DEFAULT 0,
  `publish_stats` tinyint(1) NOT NULL DEFAULT 0,
  `timeopen` bigint(10) NOT NULL DEFAULT 0,
  `timeclose` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `completionsubmit` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_feed_cou_ix` (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='all feedbacks';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_feedback`
--

LOCK TABLES `mdl_feedback` WRITE;
/*!40000 ALTER TABLE `mdl_feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_feedback_completed`
--

DROP TABLE IF EXISTS `mdl_feedback_completed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_feedback_completed` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `feedback` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `random_response` bigint(10) NOT NULL DEFAULT 0,
  `anonymous_response` tinyint(1) NOT NULL DEFAULT 0,
  `courseid` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_feedcomp_use_ix` (`userid`),
  KEY `mdl_feedcomp_fee_ix` (`feedback`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='filled out feedback';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_feedback_completed`
--

LOCK TABLES `mdl_feedback_completed` WRITE;
/*!40000 ALTER TABLE `mdl_feedback_completed` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_feedback_completed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_feedback_completedtmp`
--

DROP TABLE IF EXISTS `mdl_feedback_completedtmp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_feedback_completedtmp` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `feedback` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `guestid` varchar(255) NOT NULL DEFAULT '',
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `random_response` bigint(10) NOT NULL DEFAULT 0,
  `anonymous_response` tinyint(1) NOT NULL DEFAULT 0,
  `courseid` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_feedcomp_use2_ix` (`userid`),
  KEY `mdl_feedcomp_fee2_ix` (`feedback`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='filled out feedback';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_feedback_completedtmp`
--

LOCK TABLES `mdl_feedback_completedtmp` WRITE;
/*!40000 ALTER TABLE `mdl_feedback_completedtmp` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_feedback_completedtmp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_feedback_item`
--

DROP TABLE IF EXISTS `mdl_feedback_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_feedback_item` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `feedback` bigint(10) NOT NULL DEFAULT 0,
  `template` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) NOT NULL DEFAULT '',
  `presentation` longtext NOT NULL,
  `typ` varchar(255) NOT NULL DEFAULT '',
  `hasvalue` tinyint(1) NOT NULL DEFAULT 0,
  `position` smallint(3) NOT NULL DEFAULT 0,
  `required` tinyint(1) NOT NULL DEFAULT 0,
  `dependitem` bigint(10) NOT NULL DEFAULT 0,
  `dependvalue` varchar(255) NOT NULL DEFAULT '',
  `options` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `mdl_feeditem_fee_ix` (`feedback`),
  KEY `mdl_feeditem_tem_ix` (`template`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='feedback_items';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_feedback_item`
--

LOCK TABLES `mdl_feedback_item` WRITE;
/*!40000 ALTER TABLE `mdl_feedback_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_feedback_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_feedback_sitecourse_map`
--

DROP TABLE IF EXISTS `mdl_feedback_sitecourse_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_feedback_sitecourse_map` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `feedbackid` bigint(10) NOT NULL DEFAULT 0,
  `courseid` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_feedsitemap_cou_ix` (`courseid`),
  KEY `mdl_feedsitemap_fee_ix` (`feedbackid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='feedback sitecourse map';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_feedback_sitecourse_map`
--

LOCK TABLES `mdl_feedback_sitecourse_map` WRITE;
/*!40000 ALTER TABLE `mdl_feedback_sitecourse_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_feedback_sitecourse_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_feedback_template`
--

DROP TABLE IF EXISTS `mdl_feedback_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_feedback_template` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `ispublic` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `mdl_feedtemp_cou_ix` (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='templates of feedbackstructures';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_feedback_template`
--

LOCK TABLES `mdl_feedback_template` WRITE;
/*!40000 ALTER TABLE `mdl_feedback_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_feedback_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_feedback_value`
--

DROP TABLE IF EXISTS `mdl_feedback_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_feedback_value` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course_id` bigint(10) NOT NULL DEFAULT 0,
  `item` bigint(10) NOT NULL DEFAULT 0,
  `completed` bigint(10) NOT NULL DEFAULT 0,
  `tmp_completed` bigint(10) NOT NULL DEFAULT 0,
  `value` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_feedvalu_comitecou_uix` (`completed`,`item`,`course_id`),
  KEY `mdl_feedvalu_cou_ix` (`course_id`),
  KEY `mdl_feedvalu_ite_ix` (`item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='values of the completeds';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_feedback_value`
--

LOCK TABLES `mdl_feedback_value` WRITE;
/*!40000 ALTER TABLE `mdl_feedback_value` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_feedback_value` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_feedback_valuetmp`
--

DROP TABLE IF EXISTS `mdl_feedback_valuetmp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_feedback_valuetmp` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course_id` bigint(10) NOT NULL DEFAULT 0,
  `item` bigint(10) NOT NULL DEFAULT 0,
  `completed` bigint(10) NOT NULL DEFAULT 0,
  `tmp_completed` bigint(10) NOT NULL DEFAULT 0,
  `value` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_feedvalu_comitecou2_uix` (`completed`,`item`,`course_id`),
  KEY `mdl_feedvalu_cou2_ix` (`course_id`),
  KEY `mdl_feedvalu_ite2_ix` (`item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='values of the completedstmp';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_feedback_valuetmp`
--

LOCK TABLES `mdl_feedback_valuetmp` WRITE;
/*!40000 ALTER TABLE `mdl_feedback_valuetmp` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_feedback_valuetmp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_file_conversion`
--

DROP TABLE IF EXISTS `mdl_file_conversion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_file_conversion` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `usermodified` bigint(10) NOT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `sourcefileid` bigint(10) NOT NULL,
  `targetformat` varchar(100) NOT NULL DEFAULT '',
  `status` bigint(10) DEFAULT 0,
  `statusmessage` longtext DEFAULT NULL,
  `converter` varchar(255) DEFAULT NULL,
  `destfileid` bigint(10) DEFAULT NULL,
  `data` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_fileconv_sou_ix` (`sourcefileid`),
  KEY `mdl_fileconv_des_ix` (`destfileid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Table to track file conversions.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_file_conversion`
--

LOCK TABLES `mdl_file_conversion` WRITE;
/*!40000 ALTER TABLE `mdl_file_conversion` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_file_conversion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_files`
--

DROP TABLE IF EXISTS `mdl_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_files` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `contenthash` varchar(40) NOT NULL DEFAULT '',
  `pathnamehash` varchar(40) NOT NULL DEFAULT '',
  `contextid` bigint(10) NOT NULL,
  `component` varchar(100) NOT NULL DEFAULT '',
  `filearea` varchar(50) NOT NULL DEFAULT '',
  `itemid` bigint(10) NOT NULL,
  `filepath` varchar(255) NOT NULL DEFAULT '',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `userid` bigint(10) DEFAULT NULL,
  `filesize` bigint(10) NOT NULL,
  `mimetype` varchar(100) DEFAULT NULL,
  `status` bigint(10) NOT NULL DEFAULT 0,
  `source` longtext DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `license` varchar(255) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `sortorder` bigint(10) NOT NULL DEFAULT 0,
  `referencefileid` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_file_pat_uix` (`pathnamehash`),
  KEY `mdl_file_comfilconite_ix` (`component`,`filearea`,`contextid`,`itemid`),
  KEY `mdl_file_con_ix` (`contenthash`),
  KEY `mdl_file_lic_ix` (`license`),
  KEY `mdl_file_con2_ix` (`contextid`),
  KEY `mdl_file_use_ix` (`userid`),
  KEY `mdl_file_ref_ix` (`referencefileid`)
) ENGINE=InnoDB AUTO_INCREMENT=578 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='description of files, content is stored in sha1 file pool';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_files`
--

LOCK TABLES `mdl_files` WRITE;
/*!40000 ALTER TABLE `mdl_files` DISABLE KEYS */;
INSERT INTO `mdl_files` VALUES (1,'5f8e911d0da441e36f47c5c46f4393269211ca56','508e674d49c30d4fde325fe6c7f6fd3d56b247e1',1,'assignfeedback_editpdf','stamps',0,'/','smile.png',2,1085,'image/png',0,NULL,NULL,NULL,1756253575,1756253575,0,NULL),(2,'da39a3ee5e6b4b0d3255bfef95601890afd80709','70b7cdade7b4e27d4e83f0cdaad10d6a3c0cccb5',1,'assignfeedback_editpdf','stamps',0,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756253575,1756264221,0,NULL),(3,'75c101cb8cb34ea573cd25ac38f8157b1de901b8','68317eab56c67d32aeaee5acf509a0c4aa828b6b',1,'assignfeedback_editpdf','stamps',0,'/','sad.png',2,966,'image/png',0,NULL,NULL,NULL,1756253576,1756253576,0,NULL),(4,'0c5190a24c3943966541401c883eacaa20ca20cb','695a55ff780e61c9e59428aa425430b0d6bde53b',1,'assignfeedback_editpdf','stamps',0,'/','tick.png',2,1039,'image/png',0,NULL,NULL,NULL,1756253577,1756253577,0,NULL),(5,'8c96a486d5801e0f4ab8c411f561f1c687e1f865','373e63af262a9b8466ba8632551520be793c37ff',1,'assignfeedback_editpdf','stamps',0,'/','cross.png',2,861,'image/png',0,NULL,NULL,NULL,1756253577,1756253577,0,NULL),(6,'8c96a486d5801e0f4ab8c411f561f1c687e1f865','7e0d26b6543afd879d292c18cd84a0b8ce8638dd',5,'user','draft',945374478,'/','cross.png',2,861,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";N;s:8:\"original\";s:228:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjIyOiJhc3NpZ25mZWVkYmFja19lZGl0cGRmIjtzOjY6Iml0ZW1pZCI7aTowO3M6ODoiZmlsZWFyZWEiO3M6Njoic3RhbXBzIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6OToiY3Jvc3MucG5nIjt9\";}',NULL,NULL,1756253577,1756253577,0,NULL),(7,'da39a3ee5e6b4b0d3255bfef95601890afd80709','cfee8fe97119fd9ee3540f07c1d39f69ee078ebe',5,'user','draft',945374478,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756264221,1756264221,0,NULL),(8,'75c101cb8cb34ea573cd25ac38f8157b1de901b8','a64a2b458e0a713b4ca70f6200ce704a160a8cd1',5,'user','draft',945374478,'/','sad.png',2,966,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";N;s:8:\"original\";s:228:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjIyOiJhc3NpZ25mZWVkYmFja19lZGl0cGRmIjtzOjY6Iml0ZW1pZCI7aTowO3M6ODoiZmlsZWFyZWEiO3M6Njoic3RhbXBzIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6Nzoic2FkLnBuZyI7fQ==\";}',NULL,NULL,1756253576,1756253576,0,NULL),(9,'5f8e911d0da441e36f47c5c46f4393269211ca56','d0d3acbc379472b69bbe6331be42835f04c7db7f',5,'user','draft',945374478,'/','smile.png',2,1085,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";N;s:8:\"original\";s:228:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjIyOiJhc3NpZ25mZWVkYmFja19lZGl0cGRmIjtzOjY6Iml0ZW1pZCI7aTowO3M6ODoiZmlsZWFyZWEiO3M6Njoic3RhbXBzIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6OToic21pbGUucG5nIjt9\";}',NULL,NULL,1756253575,1756253575,0,NULL),(10,'0c5190a24c3943966541401c883eacaa20ca20cb','f9805390f871998dec0b47ba2e11e42e3891131f',5,'user','draft',945374478,'/','tick.png',2,1039,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";N;s:8:\"original\";s:228:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjIyOiJhc3NpZ25mZWVkYmFja19lZGl0cGRmIjtzOjY6Iml0ZW1pZCI7aTowO3M6ODoiZmlsZWFyZWEiO3M6Njoic3RhbXBzIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6ODoidGljay5wbmciO30=\";}',NULL,NULL,1756253577,1756253577,0,NULL),(11,'78ee3fba86046eeefa8b23077043181a98af2166','bcae3d9f37febef33f307b81adafa12ba5e6f528',1,'core','preview',0,'/thumb/','75c101cb8cb34ea573cd25ac38f8157b1de901b8',NULL,2030,'image/png',0,NULL,NULL,NULL,1756264229,1756264229,0,NULL),(12,'77ef02027f515bbb27ad8386c6d65562a1851eaf','9094b6c8a969b9a77a93a182e81d9dc9e7c7eed3',1,'core','preview',0,'/thumb/','5f8e911d0da441e36f47c5c46f4393269211ca56',NULL,2041,'image/png',0,NULL,NULL,NULL,1756264229,1756264229,0,NULL),(13,'8ae3b3e54a6c9d56ce555341466dffff706c14c5','22bc79f83252d47527b38cf899f12fb18814bf1e',1,'core','preview',0,'/thumb/','8c96a486d5801e0f4ab8c411f561f1c687e1f865',NULL,1482,'image/png',0,NULL,NULL,NULL,1756264229,1756264229,0,NULL),(14,'da39a3ee5e6b4b0d3255bfef95601890afd80709','74c104d54c05b5f8c633a36da516d37e6c5279e4',1,'core','preview',0,'/thumb/','.',NULL,0,NULL,0,NULL,NULL,NULL,1756264229,1756264229,0,NULL),(15,'da39a3ee5e6b4b0d3255bfef95601890afd80709','884555719c50529b9df662a38619d04b5b11e25c',1,'core','preview',0,'/','.',NULL,0,NULL,0,NULL,NULL,NULL,1756264229,1756264229,0,NULL),(16,'0117d9ba985437c4361927a151236bd91d77ddb0','90599c0692a05d92f276c92ee12c060680122fbd',1,'core','preview',0,'/thumb/','0c5190a24c3943966541401c883eacaa20ca20cb',NULL,1471,'image/png',0,NULL,NULL,NULL,1756264229,1756264229,0,NULL),(17,'8c96a486d5801e0f4ab8c411f561f1c687e1f865','30cbf781384dceb9808ab49abdb733a727e477bc',5,'user','draft',384855540,'/','cross.png',2,861,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";N;s:8:\"original\";s:228:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjIyOiJhc3NpZ25mZWVkYmFja19lZGl0cGRmIjtzOjY6Iml0ZW1pZCI7aTowO3M6ODoiZmlsZWFyZWEiO3M6Njoic3RhbXBzIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6OToiY3Jvc3MucG5nIjt9\";}',NULL,NULL,1756253577,1756253577,0,NULL),(18,'da39a3ee5e6b4b0d3255bfef95601890afd80709','d959185569e786a77da02785b1a36acb062dc976',5,'user','draft',384855540,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756264235,1756264235,0,NULL),(19,'75c101cb8cb34ea573cd25ac38f8157b1de901b8','b0aa4126f1baf6b143e6efdd870c1488d0542222',5,'user','draft',384855540,'/','sad.png',2,966,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";N;s:8:\"original\";s:228:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjIyOiJhc3NpZ25mZWVkYmFja19lZGl0cGRmIjtzOjY6Iml0ZW1pZCI7aTowO3M6ODoiZmlsZWFyZWEiO3M6Njoic3RhbXBzIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6Nzoic2FkLnBuZyI7fQ==\";}',NULL,NULL,1756253576,1756253576,0,NULL),(20,'5f8e911d0da441e36f47c5c46f4393269211ca56','0c0d2d9cbc590d82a317c16c2a11064ea0ffd7d6',5,'user','draft',384855540,'/','smile.png',2,1085,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";N;s:8:\"original\";s:228:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjIyOiJhc3NpZ25mZWVkYmFja19lZGl0cGRmIjtzOjY6Iml0ZW1pZCI7aTowO3M6ODoiZmlsZWFyZWEiO3M6Njoic3RhbXBzIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6OToic21pbGUucG5nIjt9\";}',NULL,NULL,1756253575,1756253575,0,NULL),(21,'0c5190a24c3943966541401c883eacaa20ca20cb','05376e51410499691ac7c6b3df371fece7b4de2e',5,'user','draft',384855540,'/','tick.png',2,1039,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";N;s:8:\"original\";s:228:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjIyOiJhc3NpZ25mZWVkYmFja19lZGl0cGRmIjtzOjY6Iml0ZW1pZCI7aTowO3M6ODoiZmlsZWFyZWEiO3M6Njoic3RhbXBzIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6ODoidGljay5wbmciO30=\";}',NULL,NULL,1756253577,1756253577,0,NULL),(22,'3fe51246c7f0d368a14099dc8e44cc9eeef62952','6304ea894fb2a60e08ee23997313b11f17e245cf',5,'user','draft',645676717,'/','ExampleSCORMen.zip',2,2238787,'application/zip',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:18:\"ExampleSCORMen.zip\";}','Administrador Usuario','unknown',1756264601,1756264601,0,NULL),(23,'da39a3ee5e6b4b0d3255bfef95601890afd80709','d919e327584b27cf9765110fc0d4097bb07c3132',5,'user','draft',645676717,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756264601,1756264601,0,NULL),(86,'fd66c238697aa124ff3dfb9c994bd038924a0f3c','57a5feb5d25ab707e397066b994ff867c5195113',5,'user','draft',474682830,'/','cursos.csv',2,2658,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:9:\"reina.csv\";}','Administrador Usuario','unknown',1756513414,1756513414,0,NULL),(87,'da39a3ee5e6b4b0d3255bfef95601890afd80709','8dfa17fd9e1c08276dd97a8a8ab8574f1370a5bf',5,'user','draft',474682830,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756513414,1756513414,0,NULL),(88,'dc7bf23440e66c2a5bf3670830d349b0c0f3c2e7','6ae3add793aba4f03dd5535f1af990b382dc25cb',5,'user','draft',367197955,'/','theme_moove_moodle40_2022062007.zip',2,2986704,'application/zip',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:35:\"theme_moove_moodle40_2022062007.zip\";}','Administrador Usuario','unknown',1756515774,1756515774,0,NULL),(89,'da39a3ee5e6b4b0d3255bfef95601890afd80709','52a22e873538d17e2e782c75b98655c004c4565b',5,'user','draft',367197955,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756515774,1756515774,0,NULL),(90,'93b423f4116ed0c57e4336bcb76fe21768bd3406','8a775dcd9d5c9cdb1082afc258148268aab2e3f7',5,'user','draft',96840744,'/','logo_josefina.png',2,1148709,'image/png',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:68:\"LOGO_LA_JOSEFINA-removebg-preview_upscayl_5x_upscayl-standard-4x.png\";}','Administrador Usuario','unknown',1756515996,1756515996,0,NULL),(91,'da39a3ee5e6b4b0d3255bfef95601890afd80709','10d7d5888cfd4a60aff0ceb332468d13b9071b9f',5,'user','draft',96840744,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756515997,1756515997,0,NULL),(92,'9e2cf2773071cbdc795fcbc6beefb0a9bffa7096','cf5c798fbe243c657deccb9958f8861e885e507b',1,'core','preview',0,'/thumb/','93b423f4116ed0c57e4336bcb76fe21768bd3406',NULL,14710,'image/png',0,NULL,NULL,NULL,1756515999,1756515999,0,NULL),(93,'c7c266b74de3c23d0d923e46c5deb97926cba323','6a69072f1cc81ebb5abbee2709615deb497d0a40',5,'user','draft',175489045,'/','favicon.ico',2,1150,'image/vnd.microsoft.icon',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:11:\"favicon.ico\";}','Administrador Usuario','unknown',1756516145,1756516145,0,NULL),(94,'da39a3ee5e6b4b0d3255bfef95601890afd80709','fe4657db5d4ddc8eaaff2c8a9649d54d21a8d40d',5,'user','draft',175489045,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756516145,1756516145,0,NULL),(95,'34726d7a55ea8c2f1c241301da59995cf6855fb2','41cdf83257203bdb0c3563c1d0b4089eb159c7cb',5,'user','draft',866714765,'/','fondo_Vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:12:\"vallesol.jpg\";}','Administrador Usuario','unknown',1756516285,1756516285,0,NULL),(96,'da39a3ee5e6b4b0d3255bfef95601890afd80709','599a8680b3fd1dd2099b905c3905cf8fce1464ad',5,'user','draft',866714765,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756516285,1756516285,0,NULL),(97,'76264ef77efd518c8e083954a8dcfcbc5f5d8588','aaec590de469db80b73c1237c2dae89f4f810b8e',1,'core','preview',0,'/thumb/','34726d7a55ea8c2f1c241301da59995cf6855fb2',NULL,16339,'image/png',0,NULL,NULL,NULL,1756516288,1756516288,0,NULL),(98,'93b423f4116ed0c57e4336bcb76fe21768bd3406','59cd43c345f64ca61bdd111bf671b3070d1ac210',1,'theme_moove','logo',0,'/','logo_josefina.png',2,1148709,'image/png',0,'LOGO_LA_JOSEFINA-removebg-preview_upscayl_5x_upscayl-standard-4x.png','Administrador Usuario','unknown',1756515996,1756516356,0,NULL),(99,'da39a3ee5e6b4b0d3255bfef95601890afd80709','7d2d3bd28ea1be627ad966d2fc0a8bc480355609',1,'theme_moove','logo',0,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756515997,1756517437,0,NULL),(100,'c7c266b74de3c23d0d923e46c5deb97926cba323','dbb5ee1384219ef9fcbd0e55380fccee274b4214',1,'theme_moove','favicon',0,'/','favicon.ico',2,1150,'image/vnd.microsoft.icon',0,'favicon.ico','Administrador Usuario','unknown',1756516145,1756516357,0,NULL),(101,'da39a3ee5e6b4b0d3255bfef95601890afd80709','b8a9db73fbfd9447288cdeed4cf892ef804907d1',1,'theme_moove','favicon',0,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756516145,1756517437,0,NULL),(102,'34726d7a55ea8c2f1c241301da59995cf6855fb2','ab91bf7a0a682ce5cdfdd92face8bc84bc42bd19',1,'theme_moove','loginbgimg',0,'/','fondo_Vallesol.jpg',2,2001896,'image/jpeg',0,'vallesol.jpg','Administrador Usuario','unknown',1756516285,1756516358,0,NULL),(103,'da39a3ee5e6b4b0d3255bfef95601890afd80709','253db534e9a1a7082782e0cda8a86406a72a4f53',1,'theme_moove','loginbgimg',0,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756516285,1756517438,0,NULL),(104,'93b423f4116ed0c57e4336bcb76fe21768bd3406','e72d208eedbd5c392e7fc6a3c81e1c295cd367ef',5,'user','draft',229709085,'/','logo_josefina.png',2,1148709,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:68:\"LOGO_LA_JOSEFINA-removebg-preview_upscayl_5x_upscayl-standard-4x.png\";s:8:\"original\";s:224:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjQ6ImxvZ28iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxNzoibG9nb19qb3NlZmluYS5wbmciO30=\";}','Administrador Usuario','unknown',1756515996,1756516356,0,NULL),(105,'da39a3ee5e6b4b0d3255bfef95601890afd80709','b4ae2763551de84fbf9867c383fd62f6e5aecba9',5,'user','draft',229709085,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756516568,1756516568,0,NULL),(106,'c7c266b74de3c23d0d923e46c5deb97926cba323','ee90fb16aed7a954dff49261d73dd7eae2582102',5,'user','draft',871293789,'/','favicon.ico',2,1150,'image/vnd.microsoft.icon',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:11:\"favicon.ico\";s:8:\"original\";s:220:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjc6ImZhdmljb24iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxMToiZmF2aWNvbi5pY28iO30=\";}','Administrador Usuario','unknown',1756516145,1756516357,0,NULL),(107,'da39a3ee5e6b4b0d3255bfef95601890afd80709','88209b3631263ec83bb70ca525d6db7a59c58f52',5,'user','draft',871293789,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756516569,1756516569,0,NULL),(108,'34726d7a55ea8c2f1c241301da59995cf6855fb2','234979fad9943f7a54603548a5115eba12e6e498',5,'user','draft',805161398,'/','fondo_Vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:12:\"vallesol.jpg\";s:8:\"original\";s:236:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjEwOiJsb2dpbmJnaW1nIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6MTg6ImZvbmRvX1ZhbGxlc29sLmpwZyI7fQ==\";}','Administrador Usuario','unknown',1756516285,1756516358,0,NULL),(109,'da39a3ee5e6b4b0d3255bfef95601890afd80709','2e64d4598b14d21958805e62da6507d493447b08',5,'user','draft',805161398,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756516569,1756516569,0,NULL),(110,'93b423f4116ed0c57e4336bcb76fe21768bd3406','c0de0e2d9f620c54b6b3ade282b2400d0b575ac3',5,'user','draft',929396422,'/','logo_josefina.png',2,1148709,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:68:\"LOGO_LA_JOSEFINA-removebg-preview_upscayl_5x_upscayl-standard-4x.png\";s:8:\"original\";s:224:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjQ6ImxvZ28iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxNzoibG9nb19qb3NlZmluYS5wbmciO30=\";}','Administrador Usuario','unknown',1756515996,1756516356,0,NULL),(111,'da39a3ee5e6b4b0d3255bfef95601890afd80709','2e39bb2717a29daf2a5704bed839107cb747c0b7',5,'user','draft',929396422,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756516623,1756516623,0,NULL),(112,'c7c266b74de3c23d0d923e46c5deb97926cba323','c781fa70a5517fb54b4c40e0b0614d6337c52928',5,'user','draft',556043933,'/','favicon.ico',2,1150,'image/vnd.microsoft.icon',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:11:\"favicon.ico\";s:8:\"original\";s:220:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjc6ImZhdmljb24iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxMToiZmF2aWNvbi5pY28iO30=\";}','Administrador Usuario','unknown',1756516145,1756516357,0,NULL),(113,'da39a3ee5e6b4b0d3255bfef95601890afd80709','560083a8455260438680ab123e79be98f50eff9b',5,'user','draft',556043933,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756516624,1756516624,0,NULL),(114,'34726d7a55ea8c2f1c241301da59995cf6855fb2','e4f481a5c98286b708404f75e73da73d3648e627',5,'user','draft',502443168,'/','fondo_Vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:12:\"vallesol.jpg\";s:8:\"original\";s:236:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjEwOiJsb2dpbmJnaW1nIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6MTg6ImZvbmRvX1ZhbGxlc29sLmpwZyI7fQ==\";}','Administrador Usuario','unknown',1756516285,1756516358,0,NULL),(115,'da39a3ee5e6b4b0d3255bfef95601890afd80709','ac7344153467426ff54c9274b0aaee8b822a2251',5,'user','draft',502443168,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756516624,1756516624,0,NULL),(116,'93b423f4116ed0c57e4336bcb76fe21768bd3406','52dd10775eb1bdd2fce030b34d12d1028f1b21ad',5,'user','draft',140820862,'/','logo_josefina.png',2,1148709,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:68:\"LOGO_LA_JOSEFINA-removebg-preview_upscayl_5x_upscayl-standard-4x.png\";s:8:\"original\";s:224:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjQ6ImxvZ28iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxNzoibG9nb19qb3NlZmluYS5wbmciO30=\";}','Administrador Usuario','unknown',1756515996,1756516356,0,NULL),(117,'da39a3ee5e6b4b0d3255bfef95601890afd80709','d8ee2b921932b197bfa229213f3370fbd016768f',5,'user','draft',140820862,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517072,1756517072,0,NULL),(118,'c7c266b74de3c23d0d923e46c5deb97926cba323','617b9e20cdca000f0999e9d5fae3ce9d5c22eead',5,'user','draft',829434048,'/','favicon.ico',2,1150,'image/vnd.microsoft.icon',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:11:\"favicon.ico\";s:8:\"original\";s:220:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjc6ImZhdmljb24iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxMToiZmF2aWNvbi5pY28iO30=\";}','Administrador Usuario','unknown',1756516145,1756516357,0,NULL),(119,'da39a3ee5e6b4b0d3255bfef95601890afd80709','f163ceb88bbcbb6637a293bf8cf5afee667ebfa4',5,'user','draft',829434048,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517073,1756517073,0,NULL),(120,'34726d7a55ea8c2f1c241301da59995cf6855fb2','d9a26c1f915c01a53461d62fec617945f9520063',5,'user','draft',646239003,'/','fondo_Vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:12:\"vallesol.jpg\";s:8:\"original\";s:236:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjEwOiJsb2dpbmJnaW1nIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6MTg6ImZvbmRvX1ZhbGxlc29sLmpwZyI7fQ==\";}','Administrador Usuario','unknown',1756516285,1756516358,0,NULL),(121,'da39a3ee5e6b4b0d3255bfef95601890afd80709','d59a198cd0f2c68700d4428387219a4543217daf',5,'user','draft',646239003,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517073,1756517073,0,NULL),(122,'93b423f4116ed0c57e4336bcb76fe21768bd3406','08be1b869e207e86e849c2744d530fbb7917cd31',5,'user','draft',130402466,'/','logo_josefina.png',2,1148709,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:68:\"LOGO_LA_JOSEFINA-removebg-preview_upscayl_5x_upscayl-standard-4x.png\";s:8:\"original\";s:224:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjQ6ImxvZ28iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxNzoibG9nb19qb3NlZmluYS5wbmciO30=\";}','Administrador Usuario','unknown',1756515996,1756516356,0,NULL),(123,'da39a3ee5e6b4b0d3255bfef95601890afd80709','212a5eccebac78caded1cf65d5b397d848cd2372',5,'user','draft',130402466,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517350,1756517350,0,NULL),(124,'c7c266b74de3c23d0d923e46c5deb97926cba323','9da4a699567696eda338789a497c571d182e382e',5,'user','draft',857710990,'/','favicon.ico',2,1150,'image/vnd.microsoft.icon',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:11:\"favicon.ico\";s:8:\"original\";s:220:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjc6ImZhdmljb24iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxMToiZmF2aWNvbi5pY28iO30=\";}','Administrador Usuario','unknown',1756516145,1756516357,0,NULL),(125,'da39a3ee5e6b4b0d3255bfef95601890afd80709','d82b95467cb3fa7e368b6b75f41469ba8e54068f',5,'user','draft',857710990,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517350,1756517350,0,NULL),(126,'34726d7a55ea8c2f1c241301da59995cf6855fb2','2de2b61e88e268cf077997eac4bd991233d1b3e8',5,'user','draft',974418688,'/','fondo_Vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:12:\"vallesol.jpg\";s:8:\"original\";s:236:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjEwOiJsb2dpbmJnaW1nIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6MTg6ImZvbmRvX1ZhbGxlc29sLmpwZyI7fQ==\";}','Administrador Usuario','unknown',1756516285,1756516358,0,NULL),(127,'da39a3ee5e6b4b0d3255bfef95601890afd80709','c3903f3181c2bf5980dab3784c17fe16c06aaa78',5,'user','draft',974418688,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517351,1756517351,0,NULL),(128,'93b423f4116ed0c57e4336bcb76fe21768bd3406','dd9b4948b3508eeace2b7c29514b93f54e5d524e',5,'user','draft',531344336,'/','logo_josefina.png',2,1148709,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:68:\"LOGO_LA_JOSEFINA-removebg-preview_upscayl_5x_upscayl-standard-4x.png\";s:8:\"original\";s:224:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjQ6ImxvZ28iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxNzoibG9nb19qb3NlZmluYS5wbmciO30=\";}','Administrador Usuario','unknown',1756515996,1756516356,0,NULL),(129,'da39a3ee5e6b4b0d3255bfef95601890afd80709','574f5be17e14826c179af4c1e5f558fc4718d9b9',5,'user','draft',531344336,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517437,1756517437,0,NULL),(130,'c7c266b74de3c23d0d923e46c5deb97926cba323','0eb1e5f49e9534d3b0ec694295cf0e10fad5a6a8',5,'user','draft',595412729,'/','favicon.ico',2,1150,'image/vnd.microsoft.icon',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:11:\"favicon.ico\";s:8:\"original\";s:220:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjc6ImZhdmljb24iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxMToiZmF2aWNvbi5pY28iO30=\";}','Administrador Usuario','unknown',1756516145,1756516357,0,NULL),(131,'da39a3ee5e6b4b0d3255bfef95601890afd80709','0b3468670c39bdbc1c771e60e9f7cca7b1e1d32d',5,'user','draft',595412729,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517437,1756517437,0,NULL),(132,'34726d7a55ea8c2f1c241301da59995cf6855fb2','37685dfbe306ebd1121dbb56b08ef0fe8759ee00',5,'user','draft',667065977,'/','fondo_Vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:12:\"vallesol.jpg\";s:8:\"original\";s:236:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjEwOiJsb2dpbmJnaW1nIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6MTg6ImZvbmRvX1ZhbGxlc29sLmpwZyI7fQ==\";}','Administrador Usuario','unknown',1756516285,1756516358,0,NULL),(133,'da39a3ee5e6b4b0d3255bfef95601890afd80709','b45bced4a678cf9247148e26b2f3da30c44c48d2',5,'user','draft',667065977,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517438,1756517438,0,NULL),(134,'34726d7a55ea8c2f1c241301da59995cf6855fb2','0a7b3af926f3b5b5b77f7616a3dff19063e78158',5,'user','draft',645301338,'/','vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:12:\"vallesol.jpg\";}','Administrador Usuario','unknown',1756517525,1756517525,0,NULL),(135,'da39a3ee5e6b4b0d3255bfef95601890afd80709','f42168146a50af061ca477cbd4e0e97b04b9a092',5,'user','draft',645301338,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517525,1756517525,0,NULL),(137,'da39a3ee5e6b4b0d3255bfef95601890afd80709','bb9b866e96fab33829a1353d285960b30add11f3',1,'theme_moove','sliderimage1',0,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517525,1756517525,0,NULL),(138,'34726d7a55ea8c2f1c241301da59995cf6855fb2','87d68cc045ea9c5fb08754fde58f4269d2d2ec79',1,'theme_moove','sliderimage1',0,'/','vallesol.jpg',2,2001896,'image/jpeg',0,'vallesol.jpg','Administrador Usuario','unknown',1756517525,1756517574,0,NULL),(139,'93b423f4116ed0c57e4336bcb76fe21768bd3406','9620874a86954806bf4d5d77cd6e24da5e1adc76',5,'user','draft',47201444,'/','logo_josefina.png',2,1148709,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:68:\"LOGO_LA_JOSEFINA-removebg-preview_upscayl_5x_upscayl-standard-4x.png\";s:8:\"original\";s:224:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjQ6ImxvZ28iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxNzoibG9nb19qb3NlZmluYS5wbmciO30=\";}','Administrador Usuario','unknown',1756515996,1756516356,0,NULL),(140,'da39a3ee5e6b4b0d3255bfef95601890afd80709','bd9e34887448783d6ef4c3552a61ce366d5a15fe',5,'user','draft',47201444,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517579,1756517579,0,NULL),(141,'c7c266b74de3c23d0d923e46c5deb97926cba323','5629c38a75ff04573188d0b5185dbefe85540e2f',5,'user','draft',938787774,'/','favicon.ico',2,1150,'image/vnd.microsoft.icon',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:11:\"favicon.ico\";s:8:\"original\";s:220:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjc6ImZhdmljb24iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxMToiZmF2aWNvbi5pY28iO30=\";}','Administrador Usuario','unknown',1756516145,1756516357,0,NULL),(142,'da39a3ee5e6b4b0d3255bfef95601890afd80709','68d93bb8e23bc0e4cc4ea8951a67b017b8298345',5,'user','draft',938787774,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517579,1756517579,0,NULL),(143,'34726d7a55ea8c2f1c241301da59995cf6855fb2','ca10480035c2ef5ae6f7f39c6e7726c568c6b639',5,'user','draft',374040695,'/','fondo_Vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:12:\"vallesol.jpg\";s:8:\"original\";s:236:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjEwOiJsb2dpbmJnaW1nIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6MTg6ImZvbmRvX1ZhbGxlc29sLmpwZyI7fQ==\";}','Administrador Usuario','unknown',1756516285,1756516358,0,NULL),(144,'da39a3ee5e6b4b0d3255bfef95601890afd80709','2589cac308192eb5c14e7e1964a29953498ce0ea',5,'user','draft',374040695,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517580,1756517580,0,NULL),(145,'34726d7a55ea8c2f1c241301da59995cf6855fb2','eb663c9bf1825099c7661847db22bf3b078eac87',5,'user','draft',276808170,'/','vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:12:\"vallesol.jpg\";s:8:\"original\";s:228:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjEyOiJzbGlkZXJpbWFnZTEiO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxMjoidmFsbGVzb2wuanBnIjt9\";}','Administrador Usuario','unknown',1756517525,1756517574,0,NULL),(146,'da39a3ee5e6b4b0d3255bfef95601890afd80709','3c994f66e0ff29e6d46a6b24c5912ea0cad2c82f',5,'user','draft',276808170,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517580,1756517580,0,NULL),(147,'93b423f4116ed0c57e4336bcb76fe21768bd3406','47f37b36efcdce0e53450eaae5803d615616d7e6',5,'user','draft',466699921,'/','logo_josefina.png',2,1148709,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:68:\"LOGO_LA_JOSEFINA-removebg-preview_upscayl_5x_upscayl-standard-4x.png\";s:8:\"original\";s:224:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjQ6ImxvZ28iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxNzoibG9nb19qb3NlZmluYS5wbmciO30=\";}','Administrador Usuario','unknown',1756515996,1756516356,0,NULL),(148,'da39a3ee5e6b4b0d3255bfef95601890afd80709','057add38c479c19dee53aa93218e77870770fc51',5,'user','draft',466699921,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517841,1756517841,0,NULL),(149,'c7c266b74de3c23d0d923e46c5deb97926cba323','8082c274439635608b8316d9a6b67da0dbe0bd97',5,'user','draft',833232690,'/','favicon.ico',2,1150,'image/vnd.microsoft.icon',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:11:\"favicon.ico\";s:8:\"original\";s:220:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjc6ImZhdmljb24iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxMToiZmF2aWNvbi5pY28iO30=\";}','Administrador Usuario','unknown',1756516145,1756516357,0,NULL),(150,'da39a3ee5e6b4b0d3255bfef95601890afd80709','cc92f23c5c100fe3c87bde5ce50333780c908c49',5,'user','draft',833232690,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517842,1756517842,0,NULL),(151,'34726d7a55ea8c2f1c241301da59995cf6855fb2','ff66386c5cf1861d475ee059502d88e5eb663f9e',5,'user','draft',640590066,'/','fondo_Vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:12:\"vallesol.jpg\";s:8:\"original\";s:236:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjEwOiJsb2dpbmJnaW1nIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6MTg6ImZvbmRvX1ZhbGxlc29sLmpwZyI7fQ==\";}','Administrador Usuario','unknown',1756516285,1756516358,0,NULL),(152,'da39a3ee5e6b4b0d3255bfef95601890afd80709','6fe255869544ea4b4434a538bb002381992fed6a',5,'user','draft',640590066,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517842,1756517842,0,NULL),(153,'34726d7a55ea8c2f1c241301da59995cf6855fb2','3c1b7695e2df79c60e0e2e5f8f4b1666838528a2',5,'user','draft',227686812,'/','vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:12:\"vallesol.jpg\";s:8:\"original\";s:228:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjEyOiJzbGlkZXJpbWFnZTEiO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxMjoidmFsbGVzb2wuanBnIjt9\";}','Administrador Usuario','unknown',1756517525,1756517574,0,NULL),(154,'da39a3ee5e6b4b0d3255bfef95601890afd80709','11dadd8a249c4060b4ecdd89b4239557d038f105',5,'user','draft',227686812,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517842,1756517842,0,NULL),(155,'93b423f4116ed0c57e4336bcb76fe21768bd3406','f8d7f1491cd4b706d85e5e8a95240260fffc35c5',5,'user','draft',815503717,'/','logo_josefina.png',2,1148709,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:68:\"LOGO_LA_JOSEFINA-removebg-preview_upscayl_5x_upscayl-standard-4x.png\";s:8:\"original\";s:224:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjQ6ImxvZ28iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxNzoibG9nb19qb3NlZmluYS5wbmciO30=\";}','Administrador Usuario','unknown',1756515996,1756516356,0,NULL),(156,'da39a3ee5e6b4b0d3255bfef95601890afd80709','a9ae6de400d757579e7721ee7c2c69b16cd20e57',5,'user','draft',815503717,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517928,1756517928,0,NULL),(157,'c7c266b74de3c23d0d923e46c5deb97926cba323','9b41726854e17ca335190839bd0acd720e975ae2',5,'user','draft',707913399,'/','favicon.ico',2,1150,'image/vnd.microsoft.icon',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:11:\"favicon.ico\";s:8:\"original\";s:220:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjc6ImZhdmljb24iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxMToiZmF2aWNvbi5pY28iO30=\";}','Administrador Usuario','unknown',1756516145,1756516357,0,NULL),(158,'da39a3ee5e6b4b0d3255bfef95601890afd80709','bfc29a81b3e21b2601cc9247e36c2224308fd67e',5,'user','draft',707913399,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517928,1756517928,0,NULL),(159,'34726d7a55ea8c2f1c241301da59995cf6855fb2','1cf21c7af2ae97f10351f21010552397e8d22706',5,'user','draft',479160733,'/','fondo_Vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:12:\"vallesol.jpg\";s:8:\"original\";s:236:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjEwOiJsb2dpbmJnaW1nIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6MTg6ImZvbmRvX1ZhbGxlc29sLmpwZyI7fQ==\";}','Administrador Usuario','unknown',1756516285,1756516358,0,NULL),(160,'da39a3ee5e6b4b0d3255bfef95601890afd80709','5a8c68999de59d5268c1c3ef0fc4beeeac36d3f8',5,'user','draft',479160733,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517928,1756517928,0,NULL),(161,'34726d7a55ea8c2f1c241301da59995cf6855fb2','9dea8bfde66d5c584b46d4ae3b77f2ccbee9aefd',5,'user','draft',126490421,'/','vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:12:\"vallesol.jpg\";s:8:\"original\";s:228:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjEyOiJzbGlkZXJpbWFnZTEiO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxMjoidmFsbGVzb2wuanBnIjt9\";}','Administrador Usuario','unknown',1756517525,1756517574,0,NULL),(162,'da39a3ee5e6b4b0d3255bfef95601890afd80709','5db54276af7fe90a1d710ee5f44facfd1a3f8041',5,'user','draft',126490421,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756517929,1756517929,0,NULL),(163,'3544e46c2731627e959a5547747cf66ba9ab3a87','368f4063eb989efab3b9d578e2f5c5a1eef376d0',5,'user','draft',700024501,'/','6.csv',2,542,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:5:\"6.csv\";}','Administrador Usuario','unknown',1756522923,1756522923,0,NULL),(164,'da39a3ee5e6b4b0d3255bfef95601890afd80709','b987d083d3b80837773e2199c64bd1232dbb98a1',5,'user','draft',700024501,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756522924,1756522924,0,NULL),(165,'3544e46c2731627e959a5547747cf66ba9ab3a87','901bc88ba0a3dfe78b7ca51c52d61ca0235dfe74',5,'user','draft',962704688,'/','6.csv',2,542,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:5:\"6.csv\";}','Administrador Usuario','unknown',1756522970,1756522970,0,NULL),(166,'da39a3ee5e6b4b0d3255bfef95601890afd80709','278ca8cb218f9364d6a8d2dd55a77ee9c15c5a56',5,'user','draft',962704688,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756522970,1756522970,0,NULL),(167,'3544e46c2731627e959a5547747cf66ba9ab3a87','1b33464e72aec1f57927b9d24a47944cdbf02397',5,'user','draft',63108380,'/','6.csv',2,542,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:5:\"6.csv\";}','Administrador Usuario','unknown',1756525295,1756525295,0,NULL),(168,'da39a3ee5e6b4b0d3255bfef95601890afd80709','60ce232d20976c7f02d0a90a56be205e8ca5bdfb',5,'user','draft',63108380,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756525295,1756525295,0,NULL),(169,'93b423f4116ed0c57e4336bcb76fe21768bd3406','1403b1c3ff184ce99ec9a6da65391cb52fffc0c8',5,'user','draft',548168597,'/','logo_josefina.png',2,1148709,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:68:\"LOGO_LA_JOSEFINA-removebg-preview_upscayl_5x_upscayl-standard-4x.png\";s:8:\"original\";s:224:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjQ6ImxvZ28iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxNzoibG9nb19qb3NlZmluYS5wbmciO30=\";}','Administrador Usuario','unknown',1756515996,1756516356,0,NULL),(170,'da39a3ee5e6b4b0d3255bfef95601890afd80709','dfb6ec7aa4db43d0ac7bd8023f786a142a53f8b8',5,'user','draft',548168597,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756526178,1756526178,0,NULL),(171,'c7c266b74de3c23d0d923e46c5deb97926cba323','95c13b2123801e24c5ac3d7514085df8597eb417',5,'user','draft',534939440,'/','favicon.ico',2,1150,'image/vnd.microsoft.icon',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:11:\"favicon.ico\";s:8:\"original\";s:220:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjc6ImZhdmljb24iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxMToiZmF2aWNvbi5pY28iO30=\";}','Administrador Usuario','unknown',1756516145,1756516357,0,NULL),(172,'da39a3ee5e6b4b0d3255bfef95601890afd80709','455874edecfab426ed91da9232b7fc4f19822e6e',5,'user','draft',534939440,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756526181,1756526181,0,NULL),(173,'34726d7a55ea8c2f1c241301da59995cf6855fb2','f4faa53427617903ac33d0f5aaf8305097b02b79',5,'user','draft',114698938,'/','fondo_Vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:12:\"vallesol.jpg\";s:8:\"original\";s:236:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjEwOiJsb2dpbmJnaW1nIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6MTg6ImZvbmRvX1ZhbGxlc29sLmpwZyI7fQ==\";}','Administrador Usuario','unknown',1756516285,1756516358,0,NULL),(174,'da39a3ee5e6b4b0d3255bfef95601890afd80709','dc834143daa10b6cfe5ddf67f42e42505bde6d85',5,'user','draft',114698938,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756526181,1756526181,0,NULL),(175,'34726d7a55ea8c2f1c241301da59995cf6855fb2','4d63c895fbbe98565d0d8f8308626b5531d780a2',5,'user','draft',210514142,'/','vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:12:\"vallesol.jpg\";s:8:\"original\";s:228:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjEyOiJzbGlkZXJpbWFnZTEiO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxMjoidmFsbGVzb2wuanBnIjt9\";}','Administrador Usuario','unknown',1756517525,1756517574,0,NULL),(176,'da39a3ee5e6b4b0d3255bfef95601890afd80709','bae426062b6cf80eba80dd749acb4e0a0c9b54d0',5,'user','draft',210514142,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756526182,1756526182,0,NULL),(177,'93b423f4116ed0c57e4336bcb76fe21768bd3406','54c6c2379fe5b05533d10c12805cc2708b3725da',5,'user','draft',350473956,'/','logo_josefina.png',2,1148709,'image/png',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:68:\"LOGO_LA_JOSEFINA-removebg-preview_upscayl_5x_upscayl-standard-4x.png\";s:8:\"original\";s:224:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjQ6ImxvZ28iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxNzoibG9nb19qb3NlZmluYS5wbmciO30=\";}','Administrador Usuario','unknown',1756515996,1756516356,0,NULL),(178,'da39a3ee5e6b4b0d3255bfef95601890afd80709','6e69a65130a3df24596b1623ee7998a9e13146c0',5,'user','draft',350473956,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756526312,1756526312,0,NULL),(179,'c7c266b74de3c23d0d923e46c5deb97926cba323','a97bf757ebfedd66bc28b4b6b3e48d37de4d989f',5,'user','draft',636000137,'/','favicon.ico',2,1150,'image/vnd.microsoft.icon',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:11:\"favicon.ico\";s:8:\"original\";s:220:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjc6ImZhdmljb24iO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxMToiZmF2aWNvbi5pY28iO30=\";}','Administrador Usuario','unknown',1756516145,1756516357,0,NULL),(180,'da39a3ee5e6b4b0d3255bfef95601890afd80709','309e73eb59e228d35cfa504d158ca709d4689e56',5,'user','draft',636000137,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756526313,1756526313,0,NULL),(181,'34726d7a55ea8c2f1c241301da59995cf6855fb2','49fd9729b2e49e1c90ddc6f2fa1a1fc0f987e45a',5,'user','draft',648386166,'/','fondo_Vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:12:\"vallesol.jpg\";s:8:\"original\";s:236:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjEwOiJsb2dpbmJnaW1nIjtzOjg6ImZpbGVwYXRoIjtzOjE6Ii8iO3M6ODoiZmlsZW5hbWUiO3M6MTg6ImZvbmRvX1ZhbGxlc29sLmpwZyI7fQ==\";}','Administrador Usuario','unknown',1756516285,1756516358,0,NULL),(182,'da39a3ee5e6b4b0d3255bfef95601890afd80709','15873d46ae3832d0c7ec1e68d00f0d4faebe8d23',5,'user','draft',648386166,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756526313,1756526313,0,NULL),(183,'34726d7a55ea8c2f1c241301da59995cf6855fb2','2dca0e9545c0554b04625ee2e09ff509405680ff',5,'user','draft',750017681,'/','vallesol.jpg',2,2001896,'image/jpeg',0,'O:8:\"stdClass\":2:{s:6:\"source\";s:12:\"vallesol.jpg\";s:8:\"original\";s:228:\"YTo2OntzOjk6ImNvbnRleHRpZCI7aToxO3M6OToiY29tcG9uZW50IjtzOjExOiJ0aGVtZV9tb292ZSI7czo2OiJpdGVtaWQiO2k6MDtzOjg6ImZpbGVhcmVhIjtzOjEyOiJzbGlkZXJpbWFnZTEiO3M6ODoiZmlsZXBhdGgiO3M6MToiLyI7czo4OiJmaWxlbmFtZSI7czoxMjoidmFsbGVzb2wuanBnIjt9\";}','Administrador Usuario','unknown',1756517525,1756517574,0,NULL),(184,'da39a3ee5e6b4b0d3255bfef95601890afd80709','6d08525e4a3a8b253b270f494cc52c982d22fb1f',5,'user','draft',750017681,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756526313,1756526313,0,NULL),(353,'d67b785f0f6f646336dcac0a5866cd439a930ee1','4fc1893ae156c98707270e82d34e2a2adbf39459',5,'user','draft',781335811,'/','reina2.csv',2,2686,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:10:\"reina2.csv\";}','Administrador Usuario','unknown',1756528071,1756528071,0,NULL),(354,'da39a3ee5e6b4b0d3255bfef95601890afd80709','5e8f6b5baaee6ae6d29a408da65f4f4a08e5bc1e',5,'user','draft',781335811,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756528071,1756528071,0,NULL),(523,'8fa345acb77c59b6eeac4f0163bc30428db54c9b','be72e129e78045a95694a113e04db051be6ce023',5,'user','draft',993785328,'/','reina2.csv',2,2686,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:10:\"reina2.csv\";}','Administrador Usuario','unknown',1756529435,1756529435,0,NULL),(524,'da39a3ee5e6b4b0d3255bfef95601890afd80709','143dd1671d35b8c644e06e0f8bce6efc85415615',5,'user','draft',993785328,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756529435,1756529435,0,NULL),(525,'8fa345acb77c59b6eeac4f0163bc30428db54c9b','d726a2aebfee72364e4ba50b3286d7ab81d9df9e',5,'user','draft',892540714,'/','reina2.csv',2,2686,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:10:\"reina2.csv\";}','Administrador Usuario','unknown',1756529623,1756529623,0,NULL),(526,'da39a3ee5e6b4b0d3255bfef95601890afd80709','735c73ff77c7af11b48538c7f980ae7ffac07674',5,'user','draft',892540714,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756529623,1756529623,0,NULL),(527,'8fa345acb77c59b6eeac4f0163bc30428db54c9b','4a84c7199aca330dbc7d14a8fc4aec39135b3aad',5,'user','draft',721858849,'/','reina.csv',2,2686,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:10:\"reina2.csv\";}','Administrador Usuario','unknown',1756529713,1756529713,0,NULL),(528,'da39a3ee5e6b4b0d3255bfef95601890afd80709','3d01430bd701839cd0f259e15cd446876b0da58b',5,'user','draft',721858849,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756529713,1756529713,0,NULL),(529,'d67b785f0f6f646336dcac0a5866cd439a930ee1','245634eccac94e0dd6b46d86fb2fd800afbbb8da',5,'user','draft',517203485,'/','reina.csv',2,2686,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:9:\"reina.csv\";}','Administrador Usuario','unknown',1756529769,1756529769,0,NULL),(530,'da39a3ee5e6b4b0d3255bfef95601890afd80709','e89db7e53c1c32cd0e41947685f0e05d5e44a106',5,'user','draft',517203485,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756529770,1756529770,0,NULL),(531,'8fa345acb77c59b6eeac4f0163bc30428db54c9b','969e36c9b0d41b683b2d4104e24554a743ff8f01',5,'user','draft',678770601,'/','reina2.csv',2,2686,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:10:\"reina2.csv\";}','Administrador Usuario','unknown',1756529800,1756529800,0,NULL),(532,'da39a3ee5e6b4b0d3255bfef95601890afd80709','353d740b3f44364ee4b75b5cf15e9a4b2550f6cb',5,'user','draft',678770601,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756529800,1756529800,0,NULL),(533,'e776dcfb9dfb5d9266848287efa69b270dbb8fc8','8160d21883673d017a7e2828b72725815deda54e',5,'user','draft',650259729,'/','6.csv',2,467,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:5:\"6.csv\";}','Administrador Usuario','unknown',1756529879,1756529879,0,NULL),(534,'da39a3ee5e6b4b0d3255bfef95601890afd80709','21976d8323d04f5d72efae36ad235faae725f9a3',5,'user','draft',650259729,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756529879,1756529879,0,NULL),(536,'da39a3ee5e6b4b0d3255bfef95601890afd80709','7e2971df0233f80c234214b755d57cfafbd5d389',5,'user','draft',760211450,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756554433,1756554433,0,NULL),(537,'738627e975e79211fb38bd471621af1e2ffadec5','cba4155c1acd1f87410e53fa7de9f5899cdc4902',5,'user','draft',760211450,'/','8.csv',2,467,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:5:\"8.csv\";}','Administrador Usuario','unknown',1756554785,1756554785,0,NULL),(540,'871e5d11f756d6eea518a2a3d5a2a0377e8521c8','3e802d104073fe3471cecac02911a07c2d2b84ff',207,'tool_recyclebin','recyclebin_coursecat',86,'/','backup.mbz',2,5217,'application/vnd.moodle.backup',0,NULL,NULL,NULL,1756554960,1756554961,0,NULL),(541,'da39a3ee5e6b4b0d3255bfef95601890afd80709','7b1343cc136c9af7abdb0027fff716e7d0de6560',207,'tool_recyclebin','recyclebin_coursecat',86,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756554961,1756554961,0,NULL),(544,'69d9524fc1b4c6d1f1c9873fb9e8cb52114ec296','102b7fdb44b481f16f7de77fcb3b20dfd4a70970',207,'tool_recyclebin','recyclebin_coursecat',87,'/','backup.mbz',2,5200,'application/vnd.moodle.backup',0,NULL,NULL,NULL,1756555014,1756555014,0,NULL),(545,'da39a3ee5e6b4b0d3255bfef95601890afd80709','b569af2b4caacfacbeecd64c845a36e62d623724',207,'tool_recyclebin','recyclebin_coursecat',87,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756555014,1756555014,0,NULL),(548,'a5db8555330d02386d8d3b4a01d65ab3ca6e0324','a85ead01ff9c2979832073ac43af5599c4eedd48',207,'tool_recyclebin','recyclebin_coursecat',88,'/','backup.mbz',2,5217,'application/vnd.moodle.backup',0,NULL,NULL,NULL,1756555044,1756555044,0,NULL),(549,'da39a3ee5e6b4b0d3255bfef95601890afd80709','893dec528a23f6e0559b4d17977fd6bef2d28b4e',207,'tool_recyclebin','recyclebin_coursecat',88,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756555045,1756555045,0,NULL),(552,'60d9ca326c437edfb999284d4427dcf20d57d289','40cabe7cee92c8843d86a47d16c5faa7a1c06350',207,'tool_recyclebin','recyclebin_coursecat',89,'/','backup.mbz',2,5215,'application/vnd.moodle.backup',0,NULL,NULL,NULL,1756555079,1756555080,0,NULL),(553,'da39a3ee5e6b4b0d3255bfef95601890afd80709','68a2daca5ccaa9e72fe62ca9baf3ca50a5e2fa7a',207,'tool_recyclebin','recyclebin_coursecat',89,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756555081,1756555081,0,NULL),(556,'2e7dfd296698b460725399600980e30c4f838c4c','e4e86415ed071b9c0aa225a7e75061f8a016c059',207,'tool_recyclebin','recyclebin_coursecat',90,'/','backup.mbz',2,5214,'application/vnd.moodle.backup',0,NULL,NULL,NULL,1756555146,1756555147,0,NULL),(557,'da39a3ee5e6b4b0d3255bfef95601890afd80709','cbf70dc4972637970c6835e322404e6486408d3e',207,'tool_recyclebin','recyclebin_coursecat',90,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756555147,1756555147,0,NULL),(560,'8275a82bd886245a479784b2189b487a1b271c0a','388d67b955b6b363fc349b2bd77145ddaf3a14c4',207,'tool_recyclebin','recyclebin_coursecat',91,'/','backup.mbz',2,5206,'application/vnd.moodle.backup',0,NULL,NULL,NULL,1756555213,1756555214,0,NULL),(561,'da39a3ee5e6b4b0d3255bfef95601890afd80709','e5d33e37eddcaf3dd9a2a99469bd0a7db661b933',207,'tool_recyclebin','recyclebin_coursecat',91,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756555215,1756555215,0,NULL),(564,'39e8e41dc4df7ed24f720606394b75c539524374','df80a1d2a8d99cdf6fe1186ea967101006f284be',207,'tool_recyclebin','recyclebin_coursecat',92,'/','backup.mbz',2,5224,'application/vnd.moodle.backup',0,NULL,NULL,NULL,1756555275,1756555275,0,NULL),(565,'da39a3ee5e6b4b0d3255bfef95601890afd80709','1e5e1e209c2dd89c991b0adad801368adad701e3',207,'tool_recyclebin','recyclebin_coursecat',92,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756555275,1756555275,0,NULL),(566,'738627e975e79211fb38bd471621af1e2ffadec5','cdc1d8067a06d36fb39d3135ffcb17d0301dbf3f',5,'user','draft',337560519,'/','8.csv',2,467,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:5:\"8.csv\";}','Administrador Usuario','unknown',1756555371,1756555371,0,NULL),(567,'da39a3ee5e6b4b0d3255bfef95601890afd80709','d7e9ed4f810d20536f03e2b163e2588b4adbccf4',5,'user','draft',337560519,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756555371,1756555371,0,NULL),(568,'4ef113f5dcebf1fecc133987963f5699e4edd888','1f976d51c1656a530c852a23fca4236bc71aee72',5,'user','draft',828047057,'/','9.csv',2,467,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:5:\"9.csv\";}','Administrador Usuario','unknown',1756555556,1756555556,0,NULL),(569,'da39a3ee5e6b4b0d3255bfef95601890afd80709','bb6f88ef96cd0ad496deb1a6786c482658521104',5,'user','draft',828047057,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756555556,1756555556,0,NULL),(570,'91e71652dc4da6644f44102036054f5d23aa618e','ee81623f44e092e6299c828d571c5a5b1fdf9088',5,'user','draft',623552813,'/','10.csv',2,579,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:6:\"10.csv\";}','Administrador Usuario','unknown',1756555843,1756555843,0,NULL),(571,'da39a3ee5e6b4b0d3255bfef95601890afd80709','c6a38dbad11a66f6d24c8367312ff6f948ee999b',5,'user','draft',623552813,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756555843,1756555843,0,NULL),(572,'5a3ca874065a0d334fe2e3400e9a498e6866b87c','8bbd7947492f8324b3f3dab2a70d7842494cada6',5,'user','draft',24716819,'/','11.csv',2,580,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:6:\"11.csv\";}','Administrador Usuario','unknown',1756556127,1756556127,0,NULL),(573,'da39a3ee5e6b4b0d3255bfef95601890afd80709','96f7292ca4a051597007e64f2b0083233f529bda',5,'user','draft',24716819,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756556127,1756556127,0,NULL),(574,'e776dcfb9dfb5d9266848287efa69b270dbb8fc8','0e0d86d6b6ab5c50b9632b0d86135d96ff8c80c4',5,'user','draft',545497775,'/','6.csv',2,467,'text/csv',0,'O:8:\"stdClass\":1:{s:6:\"source\";s:5:\"6.csv\";}','Administrador Usuario','unknown',1756561157,1756561157,0,NULL),(575,'da39a3ee5e6b4b0d3255bfef95601890afd80709','90a89eaa56691f0f54913c397c93e2785193bbae',5,'user','draft',545497775,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756561160,1756561160,0,NULL),(576,'e776dcfb9dfb5d9266848287efa69b270dbb8fc8','094a2e665b8e203240889c10363b65d9f85942db',274,'mod_resource','content',0,'/','6.csv',2,467,'text/csv',0,'6.csv','Administrador Usuario','unknown',1756561157,1756561330,1,NULL),(577,'da39a3ee5e6b4b0d3255bfef95601890afd80709','996455f7a988fbb16f526e5dba96ae6db84f9798',274,'mod_resource','content',0,'/','.',2,0,NULL,0,NULL,NULL,NULL,1756561160,1756561330,0,NULL);
/*!40000 ALTER TABLE `mdl_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_files_reference`
--

DROP TABLE IF EXISTS `mdl_files_reference`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_files_reference` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `repositoryid` bigint(10) NOT NULL,
  `lastsync` bigint(10) DEFAULT NULL,
  `reference` longtext DEFAULT NULL,
  `referencehash` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_filerefe_refrep_uix` (`referencehash`,`repositoryid`),
  KEY `mdl_filerefe_rep_ix` (`repositoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Store files references';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_files_reference`
--

LOCK TABLES `mdl_files_reference` WRITE;
/*!40000 ALTER TABLE `mdl_files_reference` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_files_reference` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_filter_active`
--

DROP TABLE IF EXISTS `mdl_filter_active`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_filter_active` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `filter` varchar(32) NOT NULL DEFAULT '',
  `contextid` bigint(10) NOT NULL,
  `active` smallint(4) NOT NULL,
  `sortorder` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_filtacti_confil_uix` (`contextid`,`filter`),
  KEY `mdl_filtacti_con_ix` (`contextid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores information about which filters are active in which c';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_filter_active`
--

LOCK TABLES `mdl_filter_active` WRITE;
/*!40000 ALTER TABLE `mdl_filter_active` DISABLE KEYS */;
INSERT INTO `mdl_filter_active` VALUES (1,'activitynames',1,1,2),(2,'displayh5p',1,1,1),(3,'emoticon',1,1,4),(4,'mathjaxloader',1,1,3),(5,'mediaplugin',1,1,6),(6,'urltolink',1,1,5);
/*!40000 ALTER TABLE `mdl_filter_active` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_filter_config`
--

DROP TABLE IF EXISTS `mdl_filter_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_filter_config` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `filter` varchar(32) NOT NULL DEFAULT '',
  `contextid` bigint(10) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_filtconf_confilnam_uix` (`contextid`,`filter`,`name`),
  KEY `mdl_filtconf_con_ix` (`contextid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores per-context configuration settings for filters which ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_filter_config`
--

LOCK TABLES `mdl_filter_config` WRITE;
/*!40000 ALTER TABLE `mdl_filter_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_filter_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_folder`
--

DROP TABLE IF EXISTS `mdl_folder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_folder` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `intro` longtext DEFAULT NULL,
  `introformat` smallint(4) NOT NULL DEFAULT 0,
  `revision` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `display` smallint(4) NOT NULL DEFAULT 0,
  `showexpanded` tinyint(1) NOT NULL DEFAULT 1,
  `showdownloadfolder` tinyint(1) NOT NULL DEFAULT 1,
  `forcedownload` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `mdl_fold_cou_ix` (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='each record is one folder resource';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_folder`
--

LOCK TABLES `mdl_folder` WRITE;
/*!40000 ALTER TABLE `mdl_folder` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_folder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_forum`
--

DROP TABLE IF EXISTS `mdl_forum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_forum` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `type` varchar(20) NOT NULL DEFAULT 'general',
  `name` varchar(255) NOT NULL DEFAULT '',
  `intro` longtext NOT NULL,
  `introformat` smallint(4) NOT NULL DEFAULT 0,
  `duedate` bigint(10) NOT NULL DEFAULT 0,
  `cutoffdate` bigint(10) NOT NULL DEFAULT 0,
  `assessed` bigint(10) NOT NULL DEFAULT 0,
  `assesstimestart` bigint(10) NOT NULL DEFAULT 0,
  `assesstimefinish` bigint(10) NOT NULL DEFAULT 0,
  `scale` bigint(10) NOT NULL DEFAULT 0,
  `grade_forum` bigint(10) NOT NULL DEFAULT 0,
  `grade_forum_notify` smallint(4) NOT NULL DEFAULT 0,
  `maxbytes` bigint(10) NOT NULL DEFAULT 0,
  `maxattachments` bigint(10) NOT NULL DEFAULT 1,
  `forcesubscribe` tinyint(1) NOT NULL DEFAULT 0,
  `trackingtype` tinyint(2) NOT NULL DEFAULT 1,
  `rsstype` tinyint(2) NOT NULL DEFAULT 0,
  `rssarticles` tinyint(2) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `warnafter` bigint(10) NOT NULL DEFAULT 0,
  `blockafter` bigint(10) NOT NULL DEFAULT 0,
  `blockperiod` bigint(10) NOT NULL DEFAULT 0,
  `completiondiscussions` int(9) NOT NULL DEFAULT 0,
  `completionreplies` int(9) NOT NULL DEFAULT 0,
  `completionposts` int(9) NOT NULL DEFAULT 0,
  `displaywordcount` tinyint(1) NOT NULL DEFAULT 0,
  `lockdiscussionafter` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_foru_cou_ix` (`course`)
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Forums contain and structure discussion';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_forum`
--

LOCK TABLES `mdl_forum` WRITE;
/*!40000 ALTER TABLE `mdl_forum` DISABLE KEYS */;
INSERT INTO `mdl_forum` VALUES (86,87,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756529934,0,0,0,0,0,0,0,0),(87,88,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756529939,0,0,0,0,0,0,0,0),(88,89,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756529943,0,0,0,0,0,0,0,0),(89,90,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756529945,0,0,0,0,0,0,0,0),(90,91,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756529951,0,0,0,0,0,0,0,0),(91,92,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756529956,0,0,0,0,0,0,0,0),(92,93,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756529959,0,0,0,0,0,0,0,0),(93,94,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756554545,0,0,0,0,0,0,0,0),(94,95,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756554549,0,0,0,0,0,0,0,0),(95,96,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756554554,0,0,0,0,0,0,0,0),(96,97,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756554556,0,0,0,0,0,0,0,0),(97,98,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756554561,0,0,0,0,0,0,0,0),(98,99,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756554564,0,0,0,0,0,0,0,0),(99,100,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756554569,0,0,0,0,0,0,0,0),(107,108,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555433,0,0,0,0,0,0,0,0),(108,109,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555436,0,0,0,0,0,0,0,0),(109,110,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555440,0,0,0,0,0,0,0,0),(110,111,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555444,0,0,0,0,0,0,0,0),(111,112,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555449,0,0,0,0,0,0,0,0),(112,113,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555453,0,0,0,0,0,0,0,0),(113,114,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555456,0,0,0,0,0,0,0,0),(114,115,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555630,0,0,0,0,0,0,0,0),(115,116,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555633,0,0,0,0,0,0,0,0),(116,117,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555636,0,0,0,0,0,0,0,0),(117,118,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555639,0,0,0,0,0,0,0,0),(118,119,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555643,0,0,0,0,0,0,0,0),(119,120,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555648,0,0,0,0,0,0,0,0),(120,121,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555652,0,0,0,0,0,0,0,0),(121,122,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555895,0,0,0,0,0,0,0,0),(122,123,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555899,0,0,0,0,0,0,0,0),(123,124,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555903,0,0,0,0,0,0,0,0),(124,125,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555906,0,0,0,0,0,0,0,0),(125,126,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555911,0,0,0,0,0,0,0,0),(126,127,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555915,0,0,0,0,0,0,0,0),(127,128,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555918,0,0,0,0,0,0,0,0),(128,129,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756555924,0,0,0,0,0,0,0,0),(129,130,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756556257,0,0,0,0,0,0,0,0),(130,131,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756556262,0,0,0,0,0,0,0,0),(131,132,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756556266,0,0,0,0,0,0,0,0),(132,133,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756556269,0,0,0,0,0,0,0,0),(133,134,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756556275,0,0,0,0,0,0,0,0),(134,135,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756556281,0,0,0,0,0,0,0,0),(135,136,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756556286,0,0,0,0,0,0,0,0),(136,137,'news','Avisos','Avisos y novedades generales',1,0,0,0,0,0,0,0,0,0,1,1,1,0,0,1756556291,0,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `mdl_forum` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_forum_digests`
--

DROP TABLE IF EXISTS `mdl_forum_digests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_forum_digests` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL,
  `forum` bigint(10) NOT NULL,
  `maildigest` tinyint(1) NOT NULL DEFAULT -1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_forudige_forusemai_uix` (`forum`,`userid`,`maildigest`),
  KEY `mdl_forudige_use_ix` (`userid`),
  KEY `mdl_forudige_for_ix` (`forum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Keeps track of user mail delivery preferences for each forum';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_forum_digests`
--

LOCK TABLES `mdl_forum_digests` WRITE;
/*!40000 ALTER TABLE `mdl_forum_digests` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_forum_digests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_forum_discussion_subs`
--

DROP TABLE IF EXISTS `mdl_forum_discussion_subs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_forum_discussion_subs` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `forum` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `discussion` bigint(10) NOT NULL,
  `preference` bigint(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_forudiscsubs_usedis_uix` (`userid`,`discussion`),
  KEY `mdl_forudiscsubs_for_ix` (`forum`),
  KEY `mdl_forudiscsubs_use_ix` (`userid`),
  KEY `mdl_forudiscsubs_dis_ix` (`discussion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Users may choose to subscribe and unsubscribe from specific ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_forum_discussion_subs`
--

LOCK TABLES `mdl_forum_discussion_subs` WRITE;
/*!40000 ALTER TABLE `mdl_forum_discussion_subs` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_forum_discussion_subs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_forum_discussions`
--

DROP TABLE IF EXISTS `mdl_forum_discussions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_forum_discussions` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `forum` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `firstpost` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `groupid` bigint(10) NOT NULL DEFAULT -1,
  `assessed` tinyint(1) NOT NULL DEFAULT 1,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `usermodified` bigint(10) NOT NULL DEFAULT 0,
  `timestart` bigint(10) NOT NULL DEFAULT 0,
  `timeend` bigint(10) NOT NULL DEFAULT 0,
  `pinned` tinyint(1) NOT NULL DEFAULT 0,
  `timelocked` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_forudisc_use_ix` (`userid`),
  KEY `mdl_forudisc_cou_ix` (`course`),
  KEY `mdl_forudisc_for_ix` (`forum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Forums are composed of discussions';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_forum_discussions`
--

LOCK TABLES `mdl_forum_discussions` WRITE;
/*!40000 ALTER TABLE `mdl_forum_discussions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_forum_discussions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_forum_grades`
--

DROP TABLE IF EXISTS `mdl_forum_grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_forum_grades` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `forum` bigint(10) NOT NULL,
  `itemnumber` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `grade` decimal(10,5) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_forugrad_foriteuse_uix` (`forum`,`itemnumber`,`userid`),
  KEY `mdl_forugrad_use_ix` (`userid`),
  KEY `mdl_forugrad_for_ix` (`forum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Grading data for forum instances';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_forum_grades`
--

LOCK TABLES `mdl_forum_grades` WRITE;
/*!40000 ALTER TABLE `mdl_forum_grades` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_forum_grades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_forum_posts`
--

DROP TABLE IF EXISTS `mdl_forum_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_forum_posts` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `discussion` bigint(10) NOT NULL DEFAULT 0,
  `parent` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `created` bigint(10) NOT NULL DEFAULT 0,
  `modified` bigint(10) NOT NULL DEFAULT 0,
  `mailed` tinyint(2) NOT NULL DEFAULT 0,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `message` longtext NOT NULL,
  `messageformat` tinyint(2) NOT NULL DEFAULT 0,
  `messagetrust` tinyint(2) NOT NULL DEFAULT 0,
  `attachment` varchar(100) NOT NULL DEFAULT '',
  `totalscore` smallint(4) NOT NULL DEFAULT 0,
  `mailnow` bigint(10) NOT NULL DEFAULT 0,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `privatereplyto` bigint(10) NOT NULL DEFAULT 0,
  `wordcount` bigint(20) DEFAULT NULL,
  `charcount` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_forupost_use_ix` (`userid`),
  KEY `mdl_forupost_cre_ix` (`created`),
  KEY `mdl_forupost_mai_ix` (`mailed`),
  KEY `mdl_forupost_pri_ix` (`privatereplyto`),
  KEY `mdl_forupost_dis_ix` (`discussion`),
  KEY `mdl_forupost_par_ix` (`parent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='All posts are stored in this table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_forum_posts`
--

LOCK TABLES `mdl_forum_posts` WRITE;
/*!40000 ALTER TABLE `mdl_forum_posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_forum_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_forum_queue`
--

DROP TABLE IF EXISTS `mdl_forum_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_forum_queue` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `discussionid` bigint(10) NOT NULL DEFAULT 0,
  `postid` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_foruqueu_use_ix` (`userid`),
  KEY `mdl_foruqueu_dis_ix` (`discussionid`),
  KEY `mdl_foruqueu_pos_ix` (`postid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='For keeping track of posts that will be mailed in digest for';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_forum_queue`
--

LOCK TABLES `mdl_forum_queue` WRITE;
/*!40000 ALTER TABLE `mdl_forum_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_forum_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_forum_read`
--

DROP TABLE IF EXISTS `mdl_forum_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_forum_read` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `forumid` bigint(10) NOT NULL DEFAULT 0,
  `discussionid` bigint(10) NOT NULL DEFAULT 0,
  `postid` bigint(10) NOT NULL DEFAULT 0,
  `firstread` bigint(10) NOT NULL DEFAULT 0,
  `lastread` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_foruread_foruse_ix` (`forumid`,`userid`),
  KEY `mdl_foruread_disuse_ix` (`discussionid`,`userid`),
  KEY `mdl_foruread_posuse_ix` (`postid`,`userid`),
  KEY `mdl_foruread_use_ix` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Tracks each users read posts';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_forum_read`
--

LOCK TABLES `mdl_forum_read` WRITE;
/*!40000 ALTER TABLE `mdl_forum_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_forum_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_forum_subscriptions`
--

DROP TABLE IF EXISTS `mdl_forum_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_forum_subscriptions` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `forum` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_forusubs_usefor_uix` (`userid`,`forum`),
  KEY `mdl_forusubs_use_ix` (`userid`),
  KEY `mdl_forusubs_for_ix` (`forum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Keeps track of who is subscribed to what forum';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_forum_subscriptions`
--

LOCK TABLES `mdl_forum_subscriptions` WRITE;
/*!40000 ALTER TABLE `mdl_forum_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_forum_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_forum_track_prefs`
--

DROP TABLE IF EXISTS `mdl_forum_track_prefs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_forum_track_prefs` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `forumid` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_forutracpref_usefor_ix` (`userid`,`forumid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Tracks each users untracked forums';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_forum_track_prefs`
--

LOCK TABLES `mdl_forum_track_prefs` WRITE;
/*!40000 ALTER TABLE `mdl_forum_track_prefs` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_forum_track_prefs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_glossary`
--

DROP TABLE IF EXISTS `mdl_glossary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_glossary` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `intro` longtext NOT NULL,
  `introformat` smallint(4) NOT NULL DEFAULT 0,
  `allowduplicatedentries` tinyint(2) NOT NULL DEFAULT 0,
  `displayformat` varchar(50) NOT NULL DEFAULT 'dictionary',
  `mainglossary` tinyint(2) NOT NULL DEFAULT 0,
  `showspecial` tinyint(2) NOT NULL DEFAULT 1,
  `showalphabet` tinyint(2) NOT NULL DEFAULT 1,
  `showall` tinyint(2) NOT NULL DEFAULT 1,
  `allowcomments` tinyint(2) NOT NULL DEFAULT 0,
  `allowprintview` tinyint(2) NOT NULL DEFAULT 1,
  `usedynalink` tinyint(2) NOT NULL DEFAULT 1,
  `defaultapproval` tinyint(2) NOT NULL DEFAULT 1,
  `approvaldisplayformat` varchar(50) NOT NULL DEFAULT 'default',
  `globalglossary` tinyint(2) NOT NULL DEFAULT 0,
  `entbypage` smallint(3) NOT NULL DEFAULT 10,
  `editalways` tinyint(2) NOT NULL DEFAULT 0,
  `rsstype` tinyint(2) NOT NULL DEFAULT 0,
  `rssarticles` tinyint(2) NOT NULL DEFAULT 0,
  `assessed` bigint(10) NOT NULL DEFAULT 0,
  `assesstimestart` bigint(10) NOT NULL DEFAULT 0,
  `assesstimefinish` bigint(10) NOT NULL DEFAULT 0,
  `scale` bigint(10) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `completionentries` int(9) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_glos_cou_ix` (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='all glossaries';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_glossary`
--

LOCK TABLES `mdl_glossary` WRITE;
/*!40000 ALTER TABLE `mdl_glossary` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_glossary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_glossary_alias`
--

DROP TABLE IF EXISTS `mdl_glossary_alias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_glossary_alias` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `entryid` bigint(10) NOT NULL DEFAULT 0,
  `alias` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `mdl_glosalia_ent_ix` (`entryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='entries alias';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_glossary_alias`
--

LOCK TABLES `mdl_glossary_alias` WRITE;
/*!40000 ALTER TABLE `mdl_glossary_alias` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_glossary_alias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_glossary_categories`
--

DROP TABLE IF EXISTS `mdl_glossary_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_glossary_categories` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `glossaryid` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `usedynalink` tinyint(2) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `mdl_gloscate_glo_ix` (`glossaryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='all categories for glossary entries';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_glossary_categories`
--

LOCK TABLES `mdl_glossary_categories` WRITE;
/*!40000 ALTER TABLE `mdl_glossary_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_glossary_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_glossary_entries`
--

DROP TABLE IF EXISTS `mdl_glossary_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_glossary_entries` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `glossaryid` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `concept` varchar(255) NOT NULL DEFAULT '',
  `definition` longtext NOT NULL,
  `definitionformat` tinyint(2) NOT NULL DEFAULT 0,
  `definitiontrust` tinyint(2) NOT NULL DEFAULT 0,
  `attachment` varchar(100) NOT NULL DEFAULT '',
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  `teacherentry` tinyint(2) NOT NULL DEFAULT 0,
  `sourceglossaryid` bigint(10) NOT NULL DEFAULT 0,
  `usedynalink` tinyint(2) NOT NULL DEFAULT 1,
  `casesensitive` tinyint(2) NOT NULL DEFAULT 0,
  `fullmatch` tinyint(2) NOT NULL DEFAULT 1,
  `approved` tinyint(2) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `mdl_glosentr_use_ix` (`userid`),
  KEY `mdl_glosentr_con_ix` (`concept`),
  KEY `mdl_glosentr_glo_ix` (`glossaryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='all glossary entries';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_glossary_entries`
--

LOCK TABLES `mdl_glossary_entries` WRITE;
/*!40000 ALTER TABLE `mdl_glossary_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_glossary_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_glossary_entries_categories`
--

DROP TABLE IF EXISTS `mdl_glossary_entries_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_glossary_entries_categories` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `categoryid` bigint(10) NOT NULL DEFAULT 0,
  `entryid` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_glosentrcate_cat_ix` (`categoryid`),
  KEY `mdl_glosentrcate_ent_ix` (`entryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='categories of each glossary entry';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_glossary_entries_categories`
--

LOCK TABLES `mdl_glossary_entries_categories` WRITE;
/*!40000 ALTER TABLE `mdl_glossary_entries_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_glossary_entries_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_glossary_formats`
--

DROP TABLE IF EXISTS `mdl_glossary_formats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_glossary_formats` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `popupformatname` varchar(50) NOT NULL DEFAULT '',
  `visible` tinyint(2) NOT NULL DEFAULT 1,
  `showgroup` tinyint(2) NOT NULL DEFAULT 1,
  `showtabs` varchar(100) DEFAULT NULL,
  `defaultmode` varchar(50) NOT NULL DEFAULT '',
  `defaulthook` varchar(50) NOT NULL DEFAULT '',
  `sortkey` varchar(50) NOT NULL DEFAULT '',
  `sortorder` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Setting of the display formats';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_glossary_formats`
--

LOCK TABLES `mdl_glossary_formats` WRITE;
/*!40000 ALTER TABLE `mdl_glossary_formats` DISABLE KEYS */;
INSERT INTO `mdl_glossary_formats` VALUES (1,'continuous','continuous',1,1,'standard,category,date','','','',''),(2,'dictionary','dictionary',1,1,'standard','','','',''),(3,'encyclopedia','encyclopedia',1,1,'standard,category,date,author','','','',''),(4,'entrylist','entrylist',1,1,'standard,category,date,author','','','',''),(5,'faq','faq',1,1,'standard,category,date,author','','','',''),(6,'fullwithauthor','fullwithauthor',1,1,'standard,category,date,author','','','',''),(7,'fullwithoutauthor','fullwithoutauthor',1,1,'standard,category,date','','','','');
/*!40000 ALTER TABLE `mdl_glossary_formats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_grade_categories`
--

DROP TABLE IF EXISTS `mdl_grade_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_grade_categories` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `courseid` bigint(10) NOT NULL,
  `parent` bigint(10) DEFAULT NULL,
  `depth` bigint(10) NOT NULL DEFAULT 0,
  `path` varchar(255) DEFAULT NULL,
  `fullname` varchar(255) NOT NULL DEFAULT '',
  `aggregation` bigint(10) NOT NULL DEFAULT 0,
  `keephigh` bigint(10) NOT NULL DEFAULT 0,
  `droplow` bigint(10) NOT NULL DEFAULT 0,
  `aggregateonlygraded` tinyint(1) NOT NULL DEFAULT 0,
  `aggregateoutcomes` tinyint(1) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `hidden` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_gradcate_cou_ix` (`courseid`),
  KEY `mdl_gradcate_par_ix` (`parent`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='This table keeps information about categories, used for grou';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_grade_categories`
--

LOCK TABLES `mdl_grade_categories` WRITE;
/*!40000 ALTER TABLE `mdl_grade_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_grade_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_grade_categories_history`
--

DROP TABLE IF EXISTS `mdl_grade_categories_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_grade_categories_history` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `action` bigint(10) NOT NULL DEFAULT 0,
  `oldid` bigint(10) NOT NULL,
  `source` varchar(255) DEFAULT NULL,
  `timemodified` bigint(10) DEFAULT NULL,
  `loggeduser` bigint(10) DEFAULT NULL,
  `courseid` bigint(10) NOT NULL,
  `parent` bigint(10) DEFAULT NULL,
  `depth` bigint(10) NOT NULL DEFAULT 0,
  `path` varchar(255) DEFAULT NULL,
  `fullname` varchar(255) NOT NULL DEFAULT '',
  `aggregation` bigint(10) NOT NULL DEFAULT 0,
  `keephigh` bigint(10) NOT NULL DEFAULT 0,
  `droplow` bigint(10) NOT NULL DEFAULT 0,
  `aggregateonlygraded` tinyint(1) NOT NULL DEFAULT 0,
  `aggregateoutcomes` tinyint(1) NOT NULL DEFAULT 0,
  `aggregatesubcats` tinyint(1) NOT NULL DEFAULT 0,
  `hidden` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_gradcatehist_act_ix` (`action`),
  KEY `mdl_gradcatehist_tim_ix` (`timemodified`),
  KEY `mdl_gradcatehist_old_ix` (`oldid`),
  KEY `mdl_gradcatehist_cou_ix` (`courseid`),
  KEY `mdl_gradcatehist_par_ix` (`parent`),
  KEY `mdl_gradcatehist_log_ix` (`loggeduser`)
) ENGINE=InnoDB AUTO_INCREMENT=277 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='History of grade_categories';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_grade_categories_history`
--

LOCK TABLES `mdl_grade_categories_history` WRITE;
/*!40000 ALTER TABLE `mdl_grade_categories_history` DISABLE KEYS */;
INSERT INTO `mdl_grade_categories_history` VALUES (1,1,1,'system',1756264616,2,2,NULL,0,NULL,'?',13,0,0,1,0,0,0),(2,2,1,'system',1756264616,2,2,NULL,1,'/1/','?',13,0,0,1,0,0,0),(3,3,1,'coursedelete',1756513246,2,2,NULL,1,'/1/','?',13,0,0,1,0,0,0),(4,1,2,'system',1756527072,2,44,NULL,0,NULL,'?',13,0,0,1,0,0,0),(5,2,2,'system',1756527073,2,44,NULL,1,'/2/','?',13,0,0,1,0,0,0),(6,3,2,'coursedelete',1756527074,2,44,NULL,1,'/2/','?',13,0,0,1,0,0,0),(7,1,3,'system',1756527086,2,43,NULL,0,NULL,'?',13,0,0,1,0,0,0),(8,2,3,'system',1756527086,2,43,NULL,1,'/3/','?',13,0,0,1,0,0,0),(9,3,3,'coursedelete',1756527087,2,43,NULL,1,'/3/','?',13,0,0,1,0,0,0),(10,1,4,'system',1756527096,2,42,NULL,0,NULL,'?',13,0,0,1,0,0,0),(11,2,4,'system',1756527096,2,42,NULL,1,'/4/','?',13,0,0,1,0,0,0),(12,3,4,'coursedelete',1756527097,2,42,NULL,1,'/4/','?',13,0,0,1,0,0,0),(13,1,5,'system',1756527107,2,41,NULL,0,NULL,'?',13,0,0,1,0,0,0),(14,2,5,'system',1756527107,2,41,NULL,1,'/5/','?',13,0,0,1,0,0,0),(15,3,5,'coursedelete',1756527107,2,41,NULL,1,'/5/','?',13,0,0,1,0,0,0),(16,1,6,'system',1756527117,2,40,NULL,0,NULL,'?',13,0,0,1,0,0,0),(17,2,6,'system',1756527117,2,40,NULL,1,'/6/','?',13,0,0,1,0,0,0),(18,3,6,'coursedelete',1756527118,2,40,NULL,1,'/6/','?',13,0,0,1,0,0,0),(19,1,7,'system',1756527130,2,39,NULL,0,NULL,'?',13,0,0,1,0,0,0),(20,2,7,'system',1756527131,2,39,NULL,1,'/7/','?',13,0,0,1,0,0,0),(21,3,7,'coursedelete',1756527131,2,39,NULL,1,'/7/','?',13,0,0,1,0,0,0),(22,1,8,'system',1756527140,2,38,NULL,0,NULL,'?',13,0,0,1,0,0,0),(23,2,8,'system',1756527141,2,38,NULL,1,'/8/','?',13,0,0,1,0,0,0),(24,3,8,'coursedelete',1756527141,2,38,NULL,1,'/8/','?',13,0,0,1,0,0,0),(25,1,9,'system',1756527152,2,37,NULL,0,NULL,'?',13,0,0,1,0,0,0),(26,2,9,'system',1756527153,2,37,NULL,1,'/9/','?',13,0,0,1,0,0,0),(27,3,9,'coursedelete',1756527153,2,37,NULL,1,'/9/','?',13,0,0,1,0,0,0),(28,1,10,'system',1756527163,2,36,NULL,0,NULL,'?',13,0,0,1,0,0,0),(29,2,10,'system',1756527163,2,36,NULL,1,'/10/','?',13,0,0,1,0,0,0),(30,3,10,'coursedelete',1756527163,2,36,NULL,1,'/10/','?',13,0,0,1,0,0,0),(31,1,11,'system',1756527172,2,35,NULL,0,NULL,'?',13,0,0,1,0,0,0),(32,2,11,'system',1756527173,2,35,NULL,1,'/11/','?',13,0,0,1,0,0,0),(33,3,11,'coursedelete',1756527173,2,35,NULL,1,'/11/','?',13,0,0,1,0,0,0),(34,1,12,'system',1756527183,2,34,NULL,0,NULL,'?',13,0,0,1,0,0,0),(35,2,12,'system',1756527185,2,34,NULL,1,'/12/','?',13,0,0,1,0,0,0),(36,3,12,'coursedelete',1756527185,2,34,NULL,1,'/12/','?',13,0,0,1,0,0,0),(37,1,13,'system',1756527196,2,33,NULL,0,NULL,'?',13,0,0,1,0,0,0),(38,2,13,'system',1756527196,2,33,NULL,1,'/13/','?',13,0,0,1,0,0,0),(39,3,13,'coursedelete',1756527197,2,33,NULL,1,'/13/','?',13,0,0,1,0,0,0),(40,1,14,'system',1756527207,2,32,NULL,0,NULL,'?',13,0,0,1,0,0,0),(41,2,14,'system',1756527208,2,32,NULL,1,'/14/','?',13,0,0,1,0,0,0),(42,3,14,'coursedelete',1756527208,2,32,NULL,1,'/14/','?',13,0,0,1,0,0,0),(43,1,15,'system',1756527218,2,31,NULL,0,NULL,'?',13,0,0,1,0,0,0),(44,2,15,'system',1756527218,2,31,NULL,1,'/15/','?',13,0,0,1,0,0,0),(45,3,15,'coursedelete',1756527218,2,31,NULL,1,'/15/','?',13,0,0,1,0,0,0),(46,1,16,'system',1756527228,2,30,NULL,0,NULL,'?',13,0,0,1,0,0,0),(47,2,16,'system',1756527229,2,30,NULL,1,'/16/','?',13,0,0,1,0,0,0),(48,3,16,'coursedelete',1756527229,2,30,NULL,1,'/16/','?',13,0,0,1,0,0,0),(49,1,17,'system',1756527239,2,29,NULL,0,NULL,'?',13,0,0,1,0,0,0),(50,2,17,'system',1756527240,2,29,NULL,1,'/17/','?',13,0,0,1,0,0,0),(51,3,17,'coursedelete',1756527240,2,29,NULL,1,'/17/','?',13,0,0,1,0,0,0),(52,1,18,'system',1756527252,2,28,NULL,0,NULL,'?',13,0,0,1,0,0,0),(53,2,18,'system',1756527253,2,28,NULL,1,'/18/','?',13,0,0,1,0,0,0),(54,3,18,'coursedelete',1756527253,2,28,NULL,1,'/18/','?',13,0,0,1,0,0,0),(55,1,19,'system',1756527263,2,27,NULL,0,NULL,'?',13,0,0,1,0,0,0),(56,2,19,'system',1756527264,2,27,NULL,1,'/19/','?',13,0,0,1,0,0,0),(57,3,19,'coursedelete',1756527264,2,27,NULL,1,'/19/','?',13,0,0,1,0,0,0),(58,1,20,'system',1756527274,2,26,NULL,0,NULL,'?',13,0,0,1,0,0,0),(59,2,20,'system',1756527275,2,26,NULL,1,'/20/','?',13,0,0,1,0,0,0),(60,3,20,'coursedelete',1756527275,2,26,NULL,1,'/20/','?',13,0,0,1,0,0,0),(61,1,21,'system',1756527285,2,25,NULL,0,NULL,'?',13,0,0,1,0,0,0),(62,2,21,'system',1756527286,2,25,NULL,1,'/21/','?',13,0,0,1,0,0,0),(63,3,21,'coursedelete',1756527286,2,25,NULL,1,'/21/','?',13,0,0,1,0,0,0),(64,1,22,'system',1756527296,2,24,NULL,0,NULL,'?',13,0,0,1,0,0,0),(65,2,22,'system',1756527297,2,24,NULL,1,'/22/','?',13,0,0,1,0,0,0),(66,3,22,'coursedelete',1756527297,2,24,NULL,1,'/22/','?',13,0,0,1,0,0,0),(67,1,23,'system',1756527310,2,23,NULL,0,NULL,'?',13,0,0,1,0,0,0),(68,2,23,'system',1756527312,2,23,NULL,1,'/23/','?',13,0,0,1,0,0,0),(69,3,23,'coursedelete',1756527312,2,23,NULL,1,'/23/','?',13,0,0,1,0,0,0),(70,1,24,'system',1756527324,2,22,NULL,0,NULL,'?',13,0,0,1,0,0,0),(71,2,24,'system',1756527325,2,22,NULL,1,'/24/','?',13,0,0,1,0,0,0),(72,3,24,'coursedelete',1756527325,2,22,NULL,1,'/24/','?',13,0,0,1,0,0,0),(73,1,25,'system',1756527336,2,21,NULL,0,NULL,'?',13,0,0,1,0,0,0),(74,2,25,'system',1756527337,2,21,NULL,1,'/25/','?',13,0,0,1,0,0,0),(75,3,25,'coursedelete',1756527337,2,21,NULL,1,'/25/','?',13,0,0,1,0,0,0),(76,1,26,'system',1756527346,2,20,NULL,0,NULL,'?',13,0,0,1,0,0,0),(77,2,26,'system',1756527347,2,20,NULL,1,'/26/','?',13,0,0,1,0,0,0),(78,3,26,'coursedelete',1756527347,2,20,NULL,1,'/26/','?',13,0,0,1,0,0,0),(79,1,27,'system',1756527356,2,19,NULL,0,NULL,'?',13,0,0,1,0,0,0),(80,2,27,'system',1756527357,2,19,NULL,1,'/27/','?',13,0,0,1,0,0,0),(81,3,27,'coursedelete',1756527357,2,19,NULL,1,'/27/','?',13,0,0,1,0,0,0),(82,1,28,'system',1756527367,2,18,NULL,0,NULL,'?',13,0,0,1,0,0,0),(83,2,28,'system',1756527368,2,18,NULL,1,'/28/','?',13,0,0,1,0,0,0),(84,3,28,'coursedelete',1756527368,2,18,NULL,1,'/28/','?',13,0,0,1,0,0,0),(85,1,29,'system',1756527380,2,17,NULL,0,NULL,'?',13,0,0,1,0,0,0),(86,2,29,'system',1756527382,2,17,NULL,1,'/29/','?',13,0,0,1,0,0,0),(87,3,29,'coursedelete',1756527382,2,17,NULL,1,'/29/','?',13,0,0,1,0,0,0),(88,1,30,'system',1756527392,2,16,NULL,0,NULL,'?',13,0,0,1,0,0,0),(89,2,30,'system',1756527393,2,16,NULL,1,'/30/','?',13,0,0,1,0,0,0),(90,3,30,'coursedelete',1756527393,2,16,NULL,1,'/30/','?',13,0,0,1,0,0,0),(91,1,31,'system',1756527403,2,15,NULL,0,NULL,'?',13,0,0,1,0,0,0),(92,2,31,'system',1756527404,2,15,NULL,1,'/31/','?',13,0,0,1,0,0,0),(93,3,31,'coursedelete',1756527404,2,15,NULL,1,'/31/','?',13,0,0,1,0,0,0),(94,1,32,'system',1756527414,2,14,NULL,0,NULL,'?',13,0,0,1,0,0,0),(95,2,32,'system',1756527414,2,14,NULL,1,'/32/','?',13,0,0,1,0,0,0),(96,3,32,'coursedelete',1756527415,2,14,NULL,1,'/32/','?',13,0,0,1,0,0,0),(97,1,33,'system',1756527425,2,13,NULL,0,NULL,'?',13,0,0,1,0,0,0),(98,2,33,'system',1756527426,2,13,NULL,1,'/33/','?',13,0,0,1,0,0,0),(99,3,33,'coursedelete',1756527426,2,13,NULL,1,'/33/','?',13,0,0,1,0,0,0),(100,1,34,'system',1756527435,2,12,NULL,0,NULL,'?',13,0,0,1,0,0,0),(101,2,34,'system',1756527436,2,12,NULL,1,'/34/','?',13,0,0,1,0,0,0),(102,3,34,'coursedelete',1756527436,2,12,NULL,1,'/34/','?',13,0,0,1,0,0,0),(103,1,35,'system',1756527448,2,11,NULL,0,NULL,'?',13,0,0,1,0,0,0),(104,2,35,'system',1756527449,2,11,NULL,1,'/35/','?',13,0,0,1,0,0,0),(105,3,35,'coursedelete',1756527449,2,11,NULL,1,'/35/','?',13,0,0,1,0,0,0),(106,1,36,'system',1756527461,2,10,NULL,0,NULL,'?',13,0,0,1,0,0,0),(107,2,36,'system',1756527461,2,10,NULL,1,'/36/','?',13,0,0,1,0,0,0),(108,3,36,'coursedelete',1756527461,2,10,NULL,1,'/36/','?',13,0,0,1,0,0,0),(109,1,37,'system',1756527472,2,9,NULL,0,NULL,'?',13,0,0,1,0,0,0),(110,2,37,'system',1756527472,2,9,NULL,1,'/37/','?',13,0,0,1,0,0,0),(111,3,37,'coursedelete',1756527472,2,9,NULL,1,'/37/','?',13,0,0,1,0,0,0),(112,1,38,'system',1756527482,2,8,NULL,0,NULL,'?',13,0,0,1,0,0,0),(113,2,38,'system',1756527483,2,8,NULL,1,'/38/','?',13,0,0,1,0,0,0),(114,3,38,'coursedelete',1756527483,2,8,NULL,1,'/38/','?',13,0,0,1,0,0,0),(115,1,39,'system',1756527493,2,7,NULL,0,NULL,'?',13,0,0,1,0,0,0),(116,2,39,'system',1756527493,2,7,NULL,1,'/39/','?',13,0,0,1,0,0,0),(117,3,39,'coursedelete',1756527493,2,7,NULL,1,'/39/','?',13,0,0,1,0,0,0),(118,1,40,'system',1756527504,2,6,NULL,0,NULL,'?',13,0,0,1,0,0,0),(119,2,40,'system',1756527505,2,6,NULL,1,'/40/','?',13,0,0,1,0,0,0),(120,3,40,'coursedelete',1756527505,2,6,NULL,1,'/40/','?',13,0,0,1,0,0,0),(121,1,41,'system',1756527514,2,5,NULL,0,NULL,'?',13,0,0,1,0,0,0),(122,2,41,'system',1756527514,2,5,NULL,1,'/41/','?',13,0,0,1,0,0,0),(123,3,41,'coursedelete',1756527514,2,5,NULL,1,'/41/','?',13,0,0,1,0,0,0),(124,1,42,'system',1756527526,2,4,NULL,0,NULL,'?',13,0,0,1,0,0,0),(125,2,42,'system',1756527527,2,4,NULL,1,'/42/','?',13,0,0,1,0,0,0),(126,3,42,'coursedelete',1756527527,2,4,NULL,1,'/42/','?',13,0,0,1,0,0,0),(127,1,43,'system',1756527538,2,3,NULL,0,NULL,'?',13,0,0,1,0,0,0),(128,2,43,'system',1756527538,2,3,NULL,1,'/43/','?',13,0,0,1,0,0,0),(129,3,43,'coursedelete',1756527538,2,3,NULL,1,'/43/','?',13,0,0,1,0,0,0),(130,1,44,'system',1756528521,2,86,NULL,0,NULL,'?',13,0,0,1,0,0,0),(131,2,44,'system',1756528521,2,86,NULL,1,'/44/','?',13,0,0,1,0,0,0),(132,3,44,'coursedelete',1756528522,2,86,NULL,1,'/44/','?',13,0,0,1,0,0,0),(133,1,45,'system',1756528534,2,85,NULL,0,NULL,'?',13,0,0,1,0,0,0),(134,2,45,'system',1756528534,2,85,NULL,1,'/45/','?',13,0,0,1,0,0,0),(135,3,45,'coursedelete',1756528535,2,85,NULL,1,'/45/','?',13,0,0,1,0,0,0),(136,1,46,'system',1756528546,2,84,NULL,0,NULL,'?',13,0,0,1,0,0,0),(137,2,46,'system',1756528547,2,84,NULL,1,'/46/','?',13,0,0,1,0,0,0),(138,3,46,'coursedelete',1756528547,2,84,NULL,1,'/46/','?',13,0,0,1,0,0,0),(139,1,47,'system',1756528556,2,83,NULL,0,NULL,'?',13,0,0,1,0,0,0),(140,2,47,'system',1756528558,2,83,NULL,1,'/47/','?',13,0,0,1,0,0,0),(141,3,47,'coursedelete',1756528558,2,83,NULL,1,'/47/','?',13,0,0,1,0,0,0),(142,1,48,'system',1756528568,2,82,NULL,0,NULL,'?',13,0,0,1,0,0,0),(143,2,48,'system',1756528569,2,82,NULL,1,'/48/','?',13,0,0,1,0,0,0),(144,3,48,'coursedelete',1756528569,2,82,NULL,1,'/48/','?',13,0,0,1,0,0,0),(145,1,49,'system',1756528581,2,81,NULL,0,NULL,'?',13,0,0,1,0,0,0),(146,2,49,'system',1756528582,2,81,NULL,1,'/49/','?',13,0,0,1,0,0,0),(147,3,49,'coursedelete',1756528582,2,81,NULL,1,'/49/','?',13,0,0,1,0,0,0),(148,1,50,'system',1756528593,2,80,NULL,0,NULL,'?',13,0,0,1,0,0,0),(149,2,50,'system',1756528594,2,80,NULL,1,'/50/','?',13,0,0,1,0,0,0),(150,3,50,'coursedelete',1756528594,2,80,NULL,1,'/50/','?',13,0,0,1,0,0,0),(151,1,51,'system',1756528605,2,79,NULL,0,NULL,'?',13,0,0,1,0,0,0),(152,2,51,'system',1756528606,2,79,NULL,1,'/51/','?',13,0,0,1,0,0,0),(153,3,51,'coursedelete',1756528606,2,79,NULL,1,'/51/','?',13,0,0,1,0,0,0),(154,1,52,'system',1756528617,2,78,NULL,0,NULL,'?',13,0,0,1,0,0,0),(155,2,52,'system',1756528617,2,78,NULL,1,'/52/','?',13,0,0,1,0,0,0),(156,3,52,'coursedelete',1756528618,2,78,NULL,1,'/52/','?',13,0,0,1,0,0,0),(157,1,53,'system',1756528627,2,77,NULL,0,NULL,'?',13,0,0,1,0,0,0),(158,2,53,'system',1756528628,2,77,NULL,1,'/53/','?',13,0,0,1,0,0,0),(159,3,53,'coursedelete',1756528628,2,77,NULL,1,'/53/','?',13,0,0,1,0,0,0),(160,1,54,'system',1756528639,2,76,NULL,0,NULL,'?',13,0,0,1,0,0,0),(161,2,54,'system',1756528639,2,76,NULL,1,'/54/','?',13,0,0,1,0,0,0),(162,3,54,'coursedelete',1756528639,2,76,NULL,1,'/54/','?',13,0,0,1,0,0,0),(163,1,55,'system',1756528650,2,75,NULL,0,NULL,'?',13,0,0,1,0,0,0),(164,2,55,'system',1756528651,2,75,NULL,1,'/55/','?',13,0,0,1,0,0,0),(165,3,55,'coursedelete',1756528651,2,75,NULL,1,'/55/','?',13,0,0,1,0,0,0),(166,1,56,'system',1756528661,2,74,NULL,0,NULL,'?',13,0,0,1,0,0,0),(167,2,56,'system',1756528661,2,74,NULL,1,'/56/','?',13,0,0,1,0,0,0),(168,3,56,'coursedelete',1756528662,2,74,NULL,1,'/56/','?',13,0,0,1,0,0,0),(169,1,57,'system',1756528673,2,73,NULL,0,NULL,'?',13,0,0,1,0,0,0),(170,2,57,'system',1756528673,2,73,NULL,1,'/57/','?',13,0,0,1,0,0,0),(171,3,57,'coursedelete',1756528673,2,73,NULL,1,'/57/','?',13,0,0,1,0,0,0),(172,1,58,'system',1756528684,2,72,NULL,0,NULL,'?',13,0,0,1,0,0,0),(173,2,58,'system',1756528684,2,72,NULL,1,'/58/','?',13,0,0,1,0,0,0),(174,3,58,'coursedelete',1756528684,2,72,NULL,1,'/58/','?',13,0,0,1,0,0,0),(175,1,59,'system',1756528695,2,71,NULL,0,NULL,'?',13,0,0,1,0,0,0),(176,2,59,'system',1756528695,2,71,NULL,1,'/59/','?',13,0,0,1,0,0,0),(177,3,59,'coursedelete',1756528695,2,71,NULL,1,'/59/','?',13,0,0,1,0,0,0),(178,1,60,'system',1756528706,2,70,NULL,0,NULL,'?',13,0,0,1,0,0,0),(179,2,60,'system',1756528707,2,70,NULL,1,'/60/','?',13,0,0,1,0,0,0),(180,3,60,'coursedelete',1756528707,2,70,NULL,1,'/60/','?',13,0,0,1,0,0,0),(181,1,61,'system',1756528716,2,69,NULL,0,NULL,'?',13,0,0,1,0,0,0),(182,2,61,'system',1756528717,2,69,NULL,1,'/61/','?',13,0,0,1,0,0,0),(183,3,61,'coursedelete',1756528717,2,69,NULL,1,'/61/','?',13,0,0,1,0,0,0),(184,1,62,'system',1756528728,2,68,NULL,0,NULL,'?',13,0,0,1,0,0,0),(185,2,62,'system',1756528728,2,68,NULL,1,'/62/','?',13,0,0,1,0,0,0),(186,3,62,'coursedelete',1756528729,2,68,NULL,1,'/62/','?',13,0,0,1,0,0,0),(187,1,63,'system',1756528740,2,67,NULL,0,NULL,'?',13,0,0,1,0,0,0),(188,2,63,'system',1756528740,2,67,NULL,1,'/63/','?',13,0,0,1,0,0,0),(189,3,63,'coursedelete',1756528740,2,67,NULL,1,'/63/','?',13,0,0,1,0,0,0),(190,1,64,'system',1756528749,2,66,NULL,0,NULL,'?',13,0,0,1,0,0,0),(191,2,64,'system',1756528750,2,66,NULL,1,'/64/','?',13,0,0,1,0,0,0),(192,3,64,'coursedelete',1756528750,2,66,NULL,1,'/64/','?',13,0,0,1,0,0,0),(193,1,65,'system',1756528759,2,65,NULL,0,NULL,'?',13,0,0,1,0,0,0),(194,2,65,'system',1756528760,2,65,NULL,1,'/65/','?',13,0,0,1,0,0,0),(195,3,65,'coursedelete',1756528760,2,65,NULL,1,'/65/','?',13,0,0,1,0,0,0),(196,1,66,'system',1756528771,2,64,NULL,0,NULL,'?',13,0,0,1,0,0,0),(197,2,66,'system',1756528772,2,64,NULL,1,'/66/','?',13,0,0,1,0,0,0),(198,3,66,'coursedelete',1756528772,2,64,NULL,1,'/66/','?',13,0,0,1,0,0,0),(199,1,67,'system',1756528783,2,63,NULL,0,NULL,'?',13,0,0,1,0,0,0),(200,2,67,'system',1756528783,2,63,NULL,1,'/67/','?',13,0,0,1,0,0,0),(201,3,67,'coursedelete',1756528784,2,63,NULL,1,'/67/','?',13,0,0,1,0,0,0),(202,1,68,'system',1756528794,2,62,NULL,0,NULL,'?',13,0,0,1,0,0,0),(203,2,68,'system',1756528795,2,62,NULL,1,'/68/','?',13,0,0,1,0,0,0),(204,3,68,'coursedelete',1756528795,2,62,NULL,1,'/68/','?',13,0,0,1,0,0,0),(205,1,69,'system',1756528806,2,61,NULL,0,NULL,'?',13,0,0,1,0,0,0),(206,2,69,'system',1756528806,2,61,NULL,1,'/69/','?',13,0,0,1,0,0,0),(207,3,69,'coursedelete',1756528806,2,61,NULL,1,'/69/','?',13,0,0,1,0,0,0),(208,1,70,'system',1756528818,2,60,NULL,0,NULL,'?',13,0,0,1,0,0,0),(209,2,70,'system',1756528818,2,60,NULL,1,'/70/','?',13,0,0,1,0,0,0),(210,3,70,'coursedelete',1756528818,2,60,NULL,1,'/70/','?',13,0,0,1,0,0,0),(211,1,71,'system',1756528830,2,59,NULL,0,NULL,'?',13,0,0,1,0,0,0),(212,2,71,'system',1756528831,2,59,NULL,1,'/71/','?',13,0,0,1,0,0,0),(213,3,71,'coursedelete',1756528831,2,59,NULL,1,'/71/','?',13,0,0,1,0,0,0),(214,1,72,'system',1756528841,2,58,NULL,0,NULL,'?',13,0,0,1,0,0,0),(215,2,72,'system',1756528842,2,58,NULL,1,'/72/','?',13,0,0,1,0,0,0),(216,3,72,'coursedelete',1756528842,2,58,NULL,1,'/72/','?',13,0,0,1,0,0,0),(217,1,73,'system',1756528852,2,57,NULL,0,NULL,'?',13,0,0,1,0,0,0),(218,2,73,'system',1756528852,2,57,NULL,1,'/73/','?',13,0,0,1,0,0,0),(219,3,73,'coursedelete',1756528853,2,57,NULL,1,'/73/','?',13,0,0,1,0,0,0),(220,1,74,'system',1756528863,2,56,NULL,0,NULL,'?',13,0,0,1,0,0,0),(221,2,74,'system',1756528864,2,56,NULL,1,'/74/','?',13,0,0,1,0,0,0),(222,3,74,'coursedelete',1756528864,2,56,NULL,1,'/74/','?',13,0,0,1,0,0,0),(223,1,75,'system',1756528874,2,55,NULL,0,NULL,'?',13,0,0,1,0,0,0),(224,2,75,'system',1756528875,2,55,NULL,1,'/75/','?',13,0,0,1,0,0,0),(225,3,75,'coursedelete',1756528875,2,55,NULL,1,'/75/','?',13,0,0,1,0,0,0),(226,1,76,'system',1756528884,2,54,NULL,0,NULL,'?',13,0,0,1,0,0,0),(227,2,76,'system',1756528885,2,54,NULL,1,'/76/','?',13,0,0,1,0,0,0),(228,3,76,'coursedelete',1756528885,2,54,NULL,1,'/76/','?',13,0,0,1,0,0,0),(229,1,77,'system',1756528896,2,53,NULL,0,NULL,'?',13,0,0,1,0,0,0),(230,2,77,'system',1756528897,2,53,NULL,1,'/77/','?',13,0,0,1,0,0,0),(231,3,77,'coursedelete',1756528897,2,53,NULL,1,'/77/','?',13,0,0,1,0,0,0),(232,1,78,'system',1756528906,2,52,NULL,0,NULL,'?',13,0,0,1,0,0,0),(233,2,78,'system',1756528906,2,52,NULL,1,'/78/','?',13,0,0,1,0,0,0),(234,3,78,'coursedelete',1756528906,2,52,NULL,1,'/78/','?',13,0,0,1,0,0,0),(235,1,79,'system',1756528917,2,51,NULL,0,NULL,'?',13,0,0,1,0,0,0),(236,2,79,'system',1756528918,2,51,NULL,1,'/79/','?',13,0,0,1,0,0,0),(237,3,79,'coursedelete',1756528918,2,51,NULL,1,'/79/','?',13,0,0,1,0,0,0),(238,1,80,'system',1756528927,2,50,NULL,0,NULL,'?',13,0,0,1,0,0,0),(239,2,80,'system',1756528928,2,50,NULL,1,'/80/','?',13,0,0,1,0,0,0),(240,3,80,'coursedelete',1756528928,2,50,NULL,1,'/80/','?',13,0,0,1,0,0,0),(241,1,81,'system',1756528939,2,49,NULL,0,NULL,'?',13,0,0,1,0,0,0),(242,2,81,'system',1756528940,2,49,NULL,1,'/81/','?',13,0,0,1,0,0,0),(243,3,81,'coursedelete',1756528940,2,49,NULL,1,'/81/','?',13,0,0,1,0,0,0),(244,1,82,'system',1756528953,2,48,NULL,0,NULL,'?',13,0,0,1,0,0,0),(245,2,82,'system',1756528953,2,48,NULL,1,'/82/','?',13,0,0,1,0,0,0),(246,3,82,'coursedelete',1756528954,2,48,NULL,1,'/82/','?',13,0,0,1,0,0,0),(247,1,83,'system',1756528964,2,47,NULL,0,NULL,'?',13,0,0,1,0,0,0),(248,2,83,'system',1756528965,2,47,NULL,1,'/83/','?',13,0,0,1,0,0,0),(249,3,83,'coursedelete',1756528965,2,47,NULL,1,'/83/','?',13,0,0,1,0,0,0),(250,1,84,'system',1756528973,2,46,NULL,0,NULL,'?',13,0,0,1,0,0,0),(251,2,84,'system',1756528974,2,46,NULL,1,'/84/','?',13,0,0,1,0,0,0),(252,3,84,'coursedelete',1756528974,2,46,NULL,1,'/84/','?',13,0,0,1,0,0,0),(253,1,85,'system',1756528983,2,45,NULL,0,NULL,'?',13,0,0,1,0,0,0),(254,2,85,'system',1756528984,2,45,NULL,1,'/85/','?',13,0,0,1,0,0,0),(255,3,85,'coursedelete',1756528984,2,45,NULL,1,'/85/','?',13,0,0,1,0,0,0),(256,1,86,'system',1756554962,2,107,NULL,0,NULL,'?',13,0,0,1,0,0,0),(257,2,86,'system',1756554963,2,107,NULL,1,'/86/','?',13,0,0,1,0,0,0),(258,3,86,'coursedelete',1756554963,2,107,NULL,1,'/86/','?',13,0,0,1,0,0,0),(259,1,87,'system',1756555015,2,106,NULL,0,NULL,'?',13,0,0,1,0,0,0),(260,2,87,'system',1756555016,2,106,NULL,1,'/87/','?',13,0,0,1,0,0,0),(261,3,87,'coursedelete',1756555016,2,106,NULL,1,'/87/','?',13,0,0,1,0,0,0),(262,1,88,'system',1756555045,2,105,NULL,0,NULL,'?',13,0,0,1,0,0,0),(263,2,88,'system',1756555046,2,105,NULL,1,'/88/','?',13,0,0,1,0,0,0),(264,3,88,'coursedelete',1756555046,2,105,NULL,1,'/88/','?',13,0,0,1,0,0,0),(265,1,89,'system',1756555081,2,104,NULL,0,NULL,'?',13,0,0,1,0,0,0),(266,2,89,'system',1756555081,2,104,NULL,1,'/89/','?',13,0,0,1,0,0,0),(267,3,89,'coursedelete',1756555082,2,104,NULL,1,'/89/','?',13,0,0,1,0,0,0),(268,1,90,'system',1756555147,2,103,NULL,0,NULL,'?',13,0,0,1,0,0,0),(269,2,90,'system',1756555148,2,103,NULL,1,'/90/','?',13,0,0,1,0,0,0),(270,3,90,'coursedelete',1756555148,2,103,NULL,1,'/90/','?',13,0,0,1,0,0,0),(271,1,91,'system',1756555215,2,102,NULL,0,NULL,'?',13,0,0,1,0,0,0),(272,2,91,'system',1756555216,2,102,NULL,1,'/91/','?',13,0,0,1,0,0,0),(273,3,91,'coursedelete',1756555216,2,102,NULL,1,'/91/','?',13,0,0,1,0,0,0),(274,1,92,'system',1756555276,2,101,NULL,0,NULL,'?',13,0,0,1,0,0,0),(275,2,92,'system',1756555276,2,101,NULL,1,'/92/','?',13,0,0,1,0,0,0),(276,3,92,'coursedelete',1756555277,2,101,NULL,1,'/92/','?',13,0,0,1,0,0,0);
/*!40000 ALTER TABLE `mdl_grade_categories_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_grade_grades`
--

DROP TABLE IF EXISTS `mdl_grade_grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_grade_grades` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `itemid` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `rawgrade` decimal(10,5) DEFAULT NULL,
  `rawgrademax` decimal(10,5) NOT NULL DEFAULT 100.00000,
  `rawgrademin` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `rawscaleid` bigint(10) DEFAULT NULL,
  `usermodified` bigint(10) DEFAULT NULL,
  `finalgrade` decimal(10,5) DEFAULT NULL,
  `hidden` bigint(10) NOT NULL DEFAULT 0,
  `locked` bigint(10) NOT NULL DEFAULT 0,
  `locktime` bigint(10) NOT NULL DEFAULT 0,
  `exported` bigint(10) NOT NULL DEFAULT 0,
  `overridden` bigint(10) NOT NULL DEFAULT 0,
  `excluded` bigint(10) NOT NULL DEFAULT 0,
  `feedback` longtext DEFAULT NULL,
  `feedbackformat` bigint(10) NOT NULL DEFAULT 0,
  `information` longtext DEFAULT NULL,
  `informationformat` bigint(10) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) DEFAULT NULL,
  `timemodified` bigint(10) DEFAULT NULL,
  `aggregationstatus` varchar(10) NOT NULL DEFAULT 'unknown',
  `aggregationweight` decimal(10,5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_gradgrad_useite_uix` (`userid`,`itemid`),
  KEY `mdl_gradgrad_locloc_ix` (`locked`,`locktime`),
  KEY `mdl_gradgrad_ite_ix` (`itemid`),
  KEY `mdl_gradgrad_use_ix` (`userid`),
  KEY `mdl_gradgrad_raw_ix` (`rawscaleid`),
  KEY `mdl_gradgrad_use2_ix` (`usermodified`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='grade_grades  This table keeps individual grades for each us';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_grade_grades`
--

LOCK TABLES `mdl_grade_grades` WRITE;
/*!40000 ALTER TABLE `mdl_grade_grades` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_grade_grades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_grade_grades_history`
--

DROP TABLE IF EXISTS `mdl_grade_grades_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_grade_grades_history` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `action` bigint(10) NOT NULL DEFAULT 0,
  `oldid` bigint(10) NOT NULL,
  `source` varchar(255) DEFAULT NULL,
  `timemodified` bigint(10) DEFAULT NULL,
  `loggeduser` bigint(10) DEFAULT NULL,
  `itemid` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `rawgrade` decimal(10,5) DEFAULT NULL,
  `rawgrademax` decimal(10,5) NOT NULL DEFAULT 100.00000,
  `rawgrademin` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `rawscaleid` bigint(10) DEFAULT NULL,
  `usermodified` bigint(10) DEFAULT NULL,
  `finalgrade` decimal(10,5) DEFAULT NULL,
  `hidden` bigint(10) NOT NULL DEFAULT 0,
  `locked` bigint(10) NOT NULL DEFAULT 0,
  `locktime` bigint(10) NOT NULL DEFAULT 0,
  `exported` bigint(10) NOT NULL DEFAULT 0,
  `overridden` bigint(10) NOT NULL DEFAULT 0,
  `excluded` bigint(10) NOT NULL DEFAULT 0,
  `feedback` longtext DEFAULT NULL,
  `feedbackformat` bigint(10) NOT NULL DEFAULT 0,
  `information` longtext DEFAULT NULL,
  `informationformat` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_gradgradhist_act_ix` (`action`),
  KEY `mdl_gradgradhist_tim_ix` (`timemodified`),
  KEY `mdl_gradgradhist_useitetim_ix` (`userid`,`itemid`,`timemodified`),
  KEY `mdl_gradgradhist_old_ix` (`oldid`),
  KEY `mdl_gradgradhist_ite_ix` (`itemid`),
  KEY `mdl_gradgradhist_use_ix` (`userid`),
  KEY `mdl_gradgradhist_raw_ix` (`rawscaleid`),
  KEY `mdl_gradgradhist_use2_ix` (`usermodified`),
  KEY `mdl_gradgradhist_log_ix` (`loggeduser`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='History table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_grade_grades_history`
--

LOCK TABLES `mdl_grade_grades_history` WRITE;
/*!40000 ALTER TABLE `mdl_grade_grades_history` DISABLE KEYS */;
INSERT INTO `mdl_grade_grades_history` VALUES (1,1,1,'mod/scorm',1756264632,2,2,2,0.00000,100.00000,0.00000,NULL,2,0.00000,0,0,0,0,0,0,NULL,0,NULL,0),(2,1,2,'system',1756264632,2,1,2,NULL,100.00000,0.00000,NULL,NULL,NULL,0,0,0,0,0,0,NULL,0,NULL,0),(3,2,2,'aggregation',1756264633,2,1,2,NULL,100.00000,0.00000,NULL,NULL,0.00000,0,0,0,0,0,0,NULL,0,NULL,0),(4,3,1,'coursedelete',1756513246,2,2,2,0.00000,100.00000,0.00000,NULL,2,0.00000,0,0,0,0,0,0,NULL,0,NULL,0),(5,3,2,'coursedelete',1756513246,2,1,2,NULL,100.00000,0.00000,NULL,NULL,0.00000,0,0,0,0,0,0,NULL,0,NULL,0);
/*!40000 ALTER TABLE `mdl_grade_grades_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_grade_import_newitem`
--

DROP TABLE IF EXISTS `mdl_grade_import_newitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_grade_import_newitem` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `itemname` varchar(255) NOT NULL DEFAULT '',
  `importcode` bigint(10) NOT NULL,
  `importer` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_gradimponewi_imp_ix` (`importer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='temporary table for storing new grade_item names from grade ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_grade_import_newitem`
--

LOCK TABLES `mdl_grade_import_newitem` WRITE;
/*!40000 ALTER TABLE `mdl_grade_import_newitem` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_grade_import_newitem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_grade_import_values`
--

DROP TABLE IF EXISTS `mdl_grade_import_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_grade_import_values` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `itemid` bigint(10) DEFAULT NULL,
  `newgradeitem` bigint(10) DEFAULT NULL,
  `userid` bigint(10) NOT NULL,
  `finalgrade` decimal(10,5) DEFAULT NULL,
  `feedback` longtext DEFAULT NULL,
  `importcode` bigint(10) NOT NULL,
  `importer` bigint(10) DEFAULT NULL,
  `importonlyfeedback` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_gradimpovalu_ite_ix` (`itemid`),
  KEY `mdl_gradimpovalu_new_ix` (`newgradeitem`),
  KEY `mdl_gradimpovalu_imp_ix` (`importer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Temporary table for importing grades';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_grade_import_values`
--

LOCK TABLES `mdl_grade_import_values` WRITE;
/*!40000 ALTER TABLE `mdl_grade_import_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_grade_import_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_grade_items`
--

DROP TABLE IF EXISTS `mdl_grade_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_grade_items` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `courseid` bigint(10) DEFAULT NULL,
  `categoryid` bigint(10) DEFAULT NULL,
  `itemname` varchar(255) DEFAULT NULL,
  `itemtype` varchar(30) NOT NULL DEFAULT '',
  `itemmodule` varchar(30) DEFAULT NULL,
  `iteminstance` bigint(10) DEFAULT NULL,
  `itemnumber` bigint(10) DEFAULT NULL,
  `iteminfo` longtext DEFAULT NULL,
  `idnumber` varchar(255) DEFAULT NULL,
  `calculation` longtext DEFAULT NULL,
  `gradetype` smallint(4) NOT NULL DEFAULT 1,
  `grademax` decimal(10,5) NOT NULL DEFAULT 100.00000,
  `grademin` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `scaleid` bigint(10) DEFAULT NULL,
  `outcomeid` bigint(10) DEFAULT NULL,
  `gradepass` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `multfactor` decimal(10,5) NOT NULL DEFAULT 1.00000,
  `plusfactor` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `aggregationcoef` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `aggregationcoef2` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `sortorder` bigint(10) NOT NULL DEFAULT 0,
  `display` bigint(10) NOT NULL DEFAULT 0,
  `decimals` tinyint(1) DEFAULT NULL,
  `hidden` bigint(10) NOT NULL DEFAULT 0,
  `locked` bigint(10) NOT NULL DEFAULT 0,
  `locktime` bigint(10) NOT NULL DEFAULT 0,
  `needsupdate` bigint(10) NOT NULL DEFAULT 0,
  `weightoverride` tinyint(1) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) DEFAULT NULL,
  `timemodified` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_graditem_locloc_ix` (`locked`,`locktime`),
  KEY `mdl_graditem_itenee_ix` (`itemtype`,`needsupdate`),
  KEY `mdl_graditem_gra_ix` (`gradetype`),
  KEY `mdl_graditem_idncou_ix` (`idnumber`,`courseid`),
  KEY `mdl_graditem_iteiteitecou_ix` (`itemtype`,`itemmodule`,`iteminstance`,`courseid`),
  KEY `mdl_graditem_cou_ix` (`courseid`),
  KEY `mdl_graditem_cat_ix` (`categoryid`),
  KEY `mdl_graditem_sca_ix` (`scaleid`),
  KEY `mdl_graditem_out_ix` (`outcomeid`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='This table keeps information about gradeable items (ie colum';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_grade_items`
--

LOCK TABLES `mdl_grade_items` WRITE;
/*!40000 ALTER TABLE `mdl_grade_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_grade_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_grade_items_history`
--

DROP TABLE IF EXISTS `mdl_grade_items_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_grade_items_history` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `action` bigint(10) NOT NULL DEFAULT 0,
  `oldid` bigint(10) NOT NULL,
  `source` varchar(255) DEFAULT NULL,
  `timemodified` bigint(10) DEFAULT NULL,
  `loggeduser` bigint(10) DEFAULT NULL,
  `courseid` bigint(10) DEFAULT NULL,
  `categoryid` bigint(10) DEFAULT NULL,
  `itemname` varchar(255) DEFAULT NULL,
  `itemtype` varchar(30) NOT NULL DEFAULT '',
  `itemmodule` varchar(30) DEFAULT NULL,
  `iteminstance` bigint(10) DEFAULT NULL,
  `itemnumber` bigint(10) DEFAULT NULL,
  `iteminfo` longtext DEFAULT NULL,
  `idnumber` varchar(255) DEFAULT NULL,
  `calculation` longtext DEFAULT NULL,
  `gradetype` smallint(4) NOT NULL DEFAULT 1,
  `grademax` decimal(10,5) NOT NULL DEFAULT 100.00000,
  `grademin` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `scaleid` bigint(10) DEFAULT NULL,
  `outcomeid` bigint(10) DEFAULT NULL,
  `gradepass` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `multfactor` decimal(10,5) NOT NULL DEFAULT 1.00000,
  `plusfactor` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `aggregationcoef` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `aggregationcoef2` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `sortorder` bigint(10) NOT NULL DEFAULT 0,
  `hidden` bigint(10) NOT NULL DEFAULT 0,
  `locked` bigint(10) NOT NULL DEFAULT 0,
  `locktime` bigint(10) NOT NULL DEFAULT 0,
  `needsupdate` bigint(10) NOT NULL DEFAULT 0,
  `display` bigint(10) NOT NULL DEFAULT 0,
  `decimals` tinyint(1) DEFAULT NULL,
  `weightoverride` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_graditemhist_act_ix` (`action`),
  KEY `mdl_graditemhist_tim_ix` (`timemodified`),
  KEY `mdl_graditemhist_old_ix` (`oldid`),
  KEY `mdl_graditemhist_cou_ix` (`courseid`),
  KEY `mdl_graditemhist_cat_ix` (`categoryid`),
  KEY `mdl_graditemhist_sca_ix` (`scaleid`),
  KEY `mdl_graditemhist_out_ix` (`outcomeid`),
  KEY `mdl_graditemhist_log_ix` (`loggeduser`)
) ENGINE=InnoDB AUTO_INCREMENT=188 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='History of grade_items';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_grade_items_history`
--

LOCK TABLES `mdl_grade_items_history` WRITE;
/*!40000 ALTER TABLE `mdl_grade_items_history` DISABLE KEYS */;
INSERT INTO `mdl_grade_items_history` VALUES (1,1,1,'system',1756264616,2,2,NULL,NULL,'course',NULL,1,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(2,1,2,NULL,1756264616,2,2,1,'actividad','mod','scorm',1,0,NULL,'',NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,2,0,0,0,1,0,NULL,0),(3,2,2,NULL,1756264617,2,2,1,'actividad','mod','scorm',1,0,NULL,'',NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,1.00000,2,0,0,0,1,0,NULL,0),(4,3,2,'coursedelete',1756513246,2,2,1,'actividad','mod','scorm',1,0,NULL,'',NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,1.00000,2,0,0,0,1,0,NULL,0),(5,3,1,'coursedelete',1756513246,2,2,NULL,NULL,'course',NULL,1,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,0,0,NULL,0),(6,1,3,'system',1756527072,2,44,NULL,NULL,'course',NULL,2,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(7,3,3,'coursedelete',1756527073,2,44,NULL,NULL,'course',NULL,2,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(8,1,4,'system',1756527086,2,43,NULL,NULL,'course',NULL,3,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(9,3,4,'coursedelete',1756527087,2,43,NULL,NULL,'course',NULL,3,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(10,1,5,'system',1756527096,2,42,NULL,NULL,'course',NULL,4,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(11,3,5,'coursedelete',1756527097,2,42,NULL,NULL,'course',NULL,4,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(12,1,6,'system',1756527107,2,41,NULL,NULL,'course',NULL,5,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(13,3,6,'coursedelete',1756527107,2,41,NULL,NULL,'course',NULL,5,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(14,1,7,'system',1756527117,2,40,NULL,NULL,'course',NULL,6,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(15,3,7,'coursedelete',1756527118,2,40,NULL,NULL,'course',NULL,6,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(16,1,8,'system',1756527130,2,39,NULL,NULL,'course',NULL,7,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(17,3,8,'coursedelete',1756527131,2,39,NULL,NULL,'course',NULL,7,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(18,1,9,'system',1756527140,2,38,NULL,NULL,'course',NULL,8,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(19,3,9,'coursedelete',1756527141,2,38,NULL,NULL,'course',NULL,8,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(20,1,10,'system',1756527152,2,37,NULL,NULL,'course',NULL,9,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(21,3,10,'coursedelete',1756527153,2,37,NULL,NULL,'course',NULL,9,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(22,1,11,'system',1756527163,2,36,NULL,NULL,'course',NULL,10,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(23,3,11,'coursedelete',1756527163,2,36,NULL,NULL,'course',NULL,10,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(24,1,12,'system',1756527172,2,35,NULL,NULL,'course',NULL,11,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(25,3,12,'coursedelete',1756527173,2,35,NULL,NULL,'course',NULL,11,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(26,1,13,'system',1756527183,2,34,NULL,NULL,'course',NULL,12,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(27,3,13,'coursedelete',1756527185,2,34,NULL,NULL,'course',NULL,12,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(28,1,14,'system',1756527196,2,33,NULL,NULL,'course',NULL,13,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(29,3,14,'coursedelete',1756527197,2,33,NULL,NULL,'course',NULL,13,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(30,1,15,'system',1756527208,2,32,NULL,NULL,'course',NULL,14,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(31,3,15,'coursedelete',1756527208,2,32,NULL,NULL,'course',NULL,14,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(32,1,16,'system',1756527218,2,31,NULL,NULL,'course',NULL,15,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(33,3,16,'coursedelete',1756527218,2,31,NULL,NULL,'course',NULL,15,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(34,1,17,'system',1756527229,2,30,NULL,NULL,'course',NULL,16,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(35,3,17,'coursedelete',1756527229,2,30,NULL,NULL,'course',NULL,16,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(36,1,18,'system',1756527239,2,29,NULL,NULL,'course',NULL,17,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(37,3,18,'coursedelete',1756527240,2,29,NULL,NULL,'course',NULL,17,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(38,1,19,'system',1756527253,2,28,NULL,NULL,'course',NULL,18,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(39,3,19,'coursedelete',1756527253,2,28,NULL,NULL,'course',NULL,18,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(40,1,20,'system',1756527263,2,27,NULL,NULL,'course',NULL,19,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(41,3,20,'coursedelete',1756527264,2,27,NULL,NULL,'course',NULL,19,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(42,1,21,'system',1756527275,2,26,NULL,NULL,'course',NULL,20,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(43,3,21,'coursedelete',1756527275,2,26,NULL,NULL,'course',NULL,20,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(44,1,22,'system',1756527285,2,25,NULL,NULL,'course',NULL,21,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(45,3,22,'coursedelete',1756527286,2,25,NULL,NULL,'course',NULL,21,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(46,1,23,'system',1756527297,2,24,NULL,NULL,'course',NULL,22,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(47,3,23,'coursedelete',1756527297,2,24,NULL,NULL,'course',NULL,22,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(48,1,24,'system',1756527312,2,23,NULL,NULL,'course',NULL,23,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(49,3,24,'coursedelete',1756527312,2,23,NULL,NULL,'course',NULL,23,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(50,1,25,'system',1756527325,2,22,NULL,NULL,'course',NULL,24,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(51,3,25,'coursedelete',1756527325,2,22,NULL,NULL,'course',NULL,24,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(52,1,26,'system',1756527336,2,21,NULL,NULL,'course',NULL,25,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(53,3,26,'coursedelete',1756527337,2,21,NULL,NULL,'course',NULL,25,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(54,1,27,'system',1756527346,2,20,NULL,NULL,'course',NULL,26,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(55,3,27,'coursedelete',1756527347,2,20,NULL,NULL,'course',NULL,26,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(56,1,28,'system',1756527357,2,19,NULL,NULL,'course',NULL,27,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(57,3,28,'coursedelete',1756527357,2,19,NULL,NULL,'course',NULL,27,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(58,1,29,'system',1756527367,2,18,NULL,NULL,'course',NULL,28,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(59,3,29,'coursedelete',1756527368,2,18,NULL,NULL,'course',NULL,28,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(60,1,30,'system',1756527381,2,17,NULL,NULL,'course',NULL,29,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(61,3,30,'coursedelete',1756527382,2,17,NULL,NULL,'course',NULL,29,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(62,1,31,'system',1756527392,2,16,NULL,NULL,'course',NULL,30,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(63,3,31,'coursedelete',1756527393,2,16,NULL,NULL,'course',NULL,30,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(64,1,32,'system',1756527403,2,15,NULL,NULL,'course',NULL,31,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(65,3,32,'coursedelete',1756527404,2,15,NULL,NULL,'course',NULL,31,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(66,1,33,'system',1756527414,2,14,NULL,NULL,'course',NULL,32,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(67,3,33,'coursedelete',1756527415,2,14,NULL,NULL,'course',NULL,32,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(68,1,34,'system',1756527425,2,13,NULL,NULL,'course',NULL,33,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(69,3,34,'coursedelete',1756527426,2,13,NULL,NULL,'course',NULL,33,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(70,1,35,'system',1756527435,2,12,NULL,NULL,'course',NULL,34,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(71,3,35,'coursedelete',1756527436,2,12,NULL,NULL,'course',NULL,34,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(72,1,36,'system',1756527449,2,11,NULL,NULL,'course',NULL,35,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(73,3,36,'coursedelete',1756527449,2,11,NULL,NULL,'course',NULL,35,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(74,1,37,'system',1756527461,2,10,NULL,NULL,'course',NULL,36,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(75,3,37,'coursedelete',1756527461,2,10,NULL,NULL,'course',NULL,36,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(76,1,38,'system',1756527472,2,9,NULL,NULL,'course',NULL,37,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(77,3,38,'coursedelete',1756527472,2,9,NULL,NULL,'course',NULL,37,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(78,1,39,'system',1756527482,2,8,NULL,NULL,'course',NULL,38,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(79,3,39,'coursedelete',1756527483,2,8,NULL,NULL,'course',NULL,38,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(80,1,40,'system',1756527493,2,7,NULL,NULL,'course',NULL,39,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(81,3,40,'coursedelete',1756527493,2,7,NULL,NULL,'course',NULL,39,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(82,1,41,'system',1756527504,2,6,NULL,NULL,'course',NULL,40,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(83,3,41,'coursedelete',1756527505,2,6,NULL,NULL,'course',NULL,40,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(84,1,42,'system',1756527514,2,5,NULL,NULL,'course',NULL,41,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(85,3,42,'coursedelete',1756527514,2,5,NULL,NULL,'course',NULL,41,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(86,1,43,'system',1756527527,2,4,NULL,NULL,'course',NULL,42,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(87,3,43,'coursedelete',1756527527,2,4,NULL,NULL,'course',NULL,42,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(88,1,44,'system',1756527538,2,3,NULL,NULL,'course',NULL,43,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(89,3,44,'coursedelete',1756527538,2,3,NULL,NULL,'course',NULL,43,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(90,1,45,'system',1756528521,2,86,NULL,NULL,'course',NULL,44,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(91,3,45,'coursedelete',1756528522,2,86,NULL,NULL,'course',NULL,44,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(92,1,46,'system',1756528534,2,85,NULL,NULL,'course',NULL,45,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(93,3,46,'coursedelete',1756528535,2,85,NULL,NULL,'course',NULL,45,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(94,1,47,'system',1756528547,2,84,NULL,NULL,'course',NULL,46,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(95,3,47,'coursedelete',1756528547,2,84,NULL,NULL,'course',NULL,46,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(96,1,48,'system',1756528557,2,83,NULL,NULL,'course',NULL,47,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(97,3,48,'coursedelete',1756528558,2,83,NULL,NULL,'course',NULL,47,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(98,1,49,'system',1756528568,2,82,NULL,NULL,'course',NULL,48,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(99,3,49,'coursedelete',1756528569,2,82,NULL,NULL,'course',NULL,48,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(100,1,50,'system',1756528582,2,81,NULL,NULL,'course',NULL,49,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(101,3,50,'coursedelete',1756528582,2,81,NULL,NULL,'course',NULL,49,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(102,1,51,'system',1756528594,2,80,NULL,NULL,'course',NULL,50,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(103,3,51,'coursedelete',1756528594,2,80,NULL,NULL,'course',NULL,50,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(104,1,52,'system',1756528605,2,79,NULL,NULL,'course',NULL,51,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(105,3,52,'coursedelete',1756528606,2,79,NULL,NULL,'course',NULL,51,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(106,1,53,'system',1756528617,2,78,NULL,NULL,'course',NULL,52,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(107,3,53,'coursedelete',1756528618,2,78,NULL,NULL,'course',NULL,52,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(108,1,54,'system',1756528628,2,77,NULL,NULL,'course',NULL,53,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(109,3,54,'coursedelete',1756528628,2,77,NULL,NULL,'course',NULL,53,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(110,1,55,'system',1756528639,2,76,NULL,NULL,'course',NULL,54,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(111,3,55,'coursedelete',1756528639,2,76,NULL,NULL,'course',NULL,54,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(112,1,56,'system',1756528651,2,75,NULL,NULL,'course',NULL,55,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(113,3,56,'coursedelete',1756528651,2,75,NULL,NULL,'course',NULL,55,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(114,1,57,'system',1756528661,2,74,NULL,NULL,'course',NULL,56,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(115,3,57,'coursedelete',1756528662,2,74,NULL,NULL,'course',NULL,56,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(116,1,58,'system',1756528673,2,73,NULL,NULL,'course',NULL,57,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(117,3,58,'coursedelete',1756528673,2,73,NULL,NULL,'course',NULL,57,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(118,1,59,'system',1756528684,2,72,NULL,NULL,'course',NULL,58,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(119,3,59,'coursedelete',1756528684,2,72,NULL,NULL,'course',NULL,58,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(120,1,60,'system',1756528695,2,71,NULL,NULL,'course',NULL,59,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(121,3,60,'coursedelete',1756528695,2,71,NULL,NULL,'course',NULL,59,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(122,1,61,'system',1756528707,2,70,NULL,NULL,'course',NULL,60,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(123,3,61,'coursedelete',1756528707,2,70,NULL,NULL,'course',NULL,60,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(124,1,62,'system',1756528717,2,69,NULL,NULL,'course',NULL,61,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(125,3,62,'coursedelete',1756528717,2,69,NULL,NULL,'course',NULL,61,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(126,1,63,'system',1756528728,2,68,NULL,NULL,'course',NULL,62,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(127,3,63,'coursedelete',1756528729,2,68,NULL,NULL,'course',NULL,62,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(128,1,64,'system',1756528740,2,67,NULL,NULL,'course',NULL,63,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(129,3,64,'coursedelete',1756528740,2,67,NULL,NULL,'course',NULL,63,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(130,1,65,'system',1756528750,2,66,NULL,NULL,'course',NULL,64,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(131,3,65,'coursedelete',1756528750,2,66,NULL,NULL,'course',NULL,64,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(132,1,66,'system',1756528759,2,65,NULL,NULL,'course',NULL,65,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(133,3,66,'coursedelete',1756528760,2,65,NULL,NULL,'course',NULL,65,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(134,1,67,'system',1756528771,2,64,NULL,NULL,'course',NULL,66,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(135,3,67,'coursedelete',1756528772,2,64,NULL,NULL,'course',NULL,66,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(136,1,68,'system',1756528783,2,63,NULL,NULL,'course',NULL,67,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(137,3,68,'coursedelete',1756528783,2,63,NULL,NULL,'course',NULL,67,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(138,1,69,'system',1756528795,2,62,NULL,NULL,'course',NULL,68,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(139,3,69,'coursedelete',1756528795,2,62,NULL,NULL,'course',NULL,68,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(140,1,70,'system',1756528806,2,61,NULL,NULL,'course',NULL,69,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(141,3,70,'coursedelete',1756528806,2,61,NULL,NULL,'course',NULL,69,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(142,1,71,'system',1756528818,2,60,NULL,NULL,'course',NULL,70,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(143,3,71,'coursedelete',1756528818,2,60,NULL,NULL,'course',NULL,70,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(144,1,72,'system',1756528830,2,59,NULL,NULL,'course',NULL,71,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(145,3,72,'coursedelete',1756528831,2,59,NULL,NULL,'course',NULL,71,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(146,1,73,'system',1756528842,2,58,NULL,NULL,'course',NULL,72,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(147,3,73,'coursedelete',1756528842,2,58,NULL,NULL,'course',NULL,72,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(148,1,74,'system',1756528852,2,57,NULL,NULL,'course',NULL,73,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(149,3,74,'coursedelete',1756528853,2,57,NULL,NULL,'course',NULL,73,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(150,1,75,'system',1756528863,2,56,NULL,NULL,'course',NULL,74,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(151,3,75,'coursedelete',1756528864,2,56,NULL,NULL,'course',NULL,74,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(152,1,76,'system',1756528874,2,55,NULL,NULL,'course',NULL,75,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(153,3,76,'coursedelete',1756528875,2,55,NULL,NULL,'course',NULL,75,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(154,1,77,'system',1756528885,2,54,NULL,NULL,'course',NULL,76,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(155,3,77,'coursedelete',1756528885,2,54,NULL,NULL,'course',NULL,76,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(156,1,78,'system',1756528897,2,53,NULL,NULL,'course',NULL,77,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(157,3,78,'coursedelete',1756528897,2,53,NULL,NULL,'course',NULL,77,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(158,1,79,'system',1756528906,2,52,NULL,NULL,'course',NULL,78,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(159,3,79,'coursedelete',1756528906,2,52,NULL,NULL,'course',NULL,78,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(160,1,80,'system',1756528917,2,51,NULL,NULL,'course',NULL,79,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(161,3,80,'coursedelete',1756528918,2,51,NULL,NULL,'course',NULL,79,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(162,1,81,'system',1756528928,2,50,NULL,NULL,'course',NULL,80,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(163,3,81,'coursedelete',1756528928,2,50,NULL,NULL,'course',NULL,80,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(164,1,82,'system',1756528940,2,49,NULL,NULL,'course',NULL,81,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(165,3,82,'coursedelete',1756528940,2,49,NULL,NULL,'course',NULL,81,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(166,1,83,'system',1756528953,2,48,NULL,NULL,'course',NULL,82,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(167,3,83,'coursedelete',1756528954,2,48,NULL,NULL,'course',NULL,82,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(168,1,84,'system',1756528964,2,47,NULL,NULL,'course',NULL,83,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(169,3,84,'coursedelete',1756528965,2,47,NULL,NULL,'course',NULL,83,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(170,1,85,'system',1756528973,2,46,NULL,NULL,'course',NULL,84,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(171,3,85,'coursedelete',1756528974,2,46,NULL,NULL,'course',NULL,84,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(172,1,86,'system',1756528984,2,45,NULL,NULL,'course',NULL,85,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(173,3,86,'coursedelete',1756528984,2,45,NULL,NULL,'course',NULL,85,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(174,1,87,'system',1756554962,2,107,NULL,NULL,'course',NULL,86,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(175,3,87,'coursedelete',1756554963,2,107,NULL,NULL,'course',NULL,86,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(176,1,88,'system',1756555015,2,106,NULL,NULL,'course',NULL,87,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(177,3,88,'coursedelete',1756555016,2,106,NULL,NULL,'course',NULL,87,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(178,1,89,'system',1756555046,2,105,NULL,NULL,'course',NULL,88,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(179,3,89,'coursedelete',1756555046,2,105,NULL,NULL,'course',NULL,88,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(180,1,90,'system',1756555081,2,104,NULL,NULL,'course',NULL,89,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(181,3,90,'coursedelete',1756555082,2,104,NULL,NULL,'course',NULL,89,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(182,1,91,'system',1756555147,2,103,NULL,NULL,'course',NULL,90,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(183,3,91,'coursedelete',1756555148,2,103,NULL,NULL,'course',NULL,90,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(184,1,92,'system',1756555216,2,102,NULL,NULL,'course',NULL,91,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(185,3,92,'coursedelete',1756555216,2,102,NULL,NULL,'course',NULL,91,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(186,1,93,'system',1756555276,2,101,NULL,NULL,'course',NULL,92,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0),(187,3,93,'coursedelete',1756555276,2,101,NULL,NULL,'course',NULL,92,NULL,NULL,NULL,NULL,1,100.00000,0.00000,NULL,NULL,0.00000,1.00000,0.00000,0.00000,0.00000,1,0,0,0,1,0,NULL,0);
/*!40000 ALTER TABLE `mdl_grade_items_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_grade_letters`
--

DROP TABLE IF EXISTS `mdl_grade_letters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_grade_letters` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `contextid` bigint(10) NOT NULL,
  `lowerboundary` decimal(10,5) NOT NULL,
  `letter` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_gradlett_conlowlet_uix` (`contextid`,`lowerboundary`,`letter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Repository for grade letters, for courses and other moodle e';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_grade_letters`
--

LOCK TABLES `mdl_grade_letters` WRITE;
/*!40000 ALTER TABLE `mdl_grade_letters` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_grade_letters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_grade_outcomes`
--

DROP TABLE IF EXISTS `mdl_grade_outcomes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_grade_outcomes` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `courseid` bigint(10) DEFAULT NULL,
  `shortname` varchar(255) NOT NULL DEFAULT '',
  `fullname` longtext NOT NULL,
  `scaleid` bigint(10) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `descriptionformat` tinyint(2) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) DEFAULT NULL,
  `timemodified` bigint(10) DEFAULT NULL,
  `usermodified` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_gradoutc_cousho_uix` (`courseid`,`shortname`),
  KEY `mdl_gradoutc_cou_ix` (`courseid`),
  KEY `mdl_gradoutc_sca_ix` (`scaleid`),
  KEY `mdl_gradoutc_use_ix` (`usermodified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='This table describes the outcomes used in the system. An out';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_grade_outcomes`
--

LOCK TABLES `mdl_grade_outcomes` WRITE;
/*!40000 ALTER TABLE `mdl_grade_outcomes` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_grade_outcomes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_grade_outcomes_courses`
--

DROP TABLE IF EXISTS `mdl_grade_outcomes_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_grade_outcomes_courses` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `courseid` bigint(10) NOT NULL,
  `outcomeid` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_gradoutccour_couout_uix` (`courseid`,`outcomeid`),
  KEY `mdl_gradoutccour_cou_ix` (`courseid`),
  KEY `mdl_gradoutccour_out_ix` (`outcomeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='stores what outcomes are used in what courses.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_grade_outcomes_courses`
--

LOCK TABLES `mdl_grade_outcomes_courses` WRITE;
/*!40000 ALTER TABLE `mdl_grade_outcomes_courses` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_grade_outcomes_courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_grade_outcomes_history`
--

DROP TABLE IF EXISTS `mdl_grade_outcomes_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_grade_outcomes_history` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `action` bigint(10) NOT NULL DEFAULT 0,
  `oldid` bigint(10) NOT NULL,
  `source` varchar(255) DEFAULT NULL,
  `timemodified` bigint(10) DEFAULT NULL,
  `loggeduser` bigint(10) DEFAULT NULL,
  `courseid` bigint(10) DEFAULT NULL,
  `shortname` varchar(255) NOT NULL DEFAULT '',
  `fullname` longtext NOT NULL,
  `scaleid` bigint(10) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `descriptionformat` tinyint(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_gradoutchist_act_ix` (`action`),
  KEY `mdl_gradoutchist_tim_ix` (`timemodified`),
  KEY `mdl_gradoutchist_old_ix` (`oldid`),
  KEY `mdl_gradoutchist_cou_ix` (`courseid`),
  KEY `mdl_gradoutchist_sca_ix` (`scaleid`),
  KEY `mdl_gradoutchist_log_ix` (`loggeduser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='History table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_grade_outcomes_history`
--

LOCK TABLES `mdl_grade_outcomes_history` WRITE;
/*!40000 ALTER TABLE `mdl_grade_outcomes_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_grade_outcomes_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_grade_settings`
--

DROP TABLE IF EXISTS `mdl_grade_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_grade_settings` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `courseid` bigint(10) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_gradsett_counam_uix` (`courseid`,`name`),
  KEY `mdl_gradsett_cou_ix` (`courseid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='gradebook settings';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_grade_settings`
--

LOCK TABLES `mdl_grade_settings` WRITE;
/*!40000 ALTER TABLE `mdl_grade_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_grade_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_grading_areas`
--

DROP TABLE IF EXISTS `mdl_grading_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_grading_areas` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `contextid` bigint(10) NOT NULL,
  `component` varchar(100) NOT NULL DEFAULT '',
  `areaname` varchar(100) NOT NULL DEFAULT '',
  `activemethod` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_gradarea_concomare_uix` (`contextid`,`component`,`areaname`),
  KEY `mdl_gradarea_con_ix` (`contextid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Identifies gradable areas where advanced grading can happen.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_grading_areas`
--

LOCK TABLES `mdl_grading_areas` WRITE;
/*!40000 ALTER TABLE `mdl_grading_areas` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_grading_areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_grading_definitions`
--

DROP TABLE IF EXISTS `mdl_grading_definitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_grading_definitions` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `areaid` bigint(10) NOT NULL,
  `method` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` longtext DEFAULT NULL,
  `descriptionformat` tinyint(2) DEFAULT NULL,
  `status` bigint(10) NOT NULL DEFAULT 0,
  `copiedfromid` bigint(10) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `usercreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) NOT NULL,
  `usermodified` bigint(10) NOT NULL,
  `timecopied` bigint(10) DEFAULT 0,
  `options` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_graddefi_aremet_uix` (`areaid`,`method`),
  KEY `mdl_graddefi_are_ix` (`areaid`),
  KEY `mdl_graddefi_use_ix` (`usermodified`),
  KEY `mdl_graddefi_use2_ix` (`usercreated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Contains the basic information about an advanced grading for';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_grading_definitions`
--

LOCK TABLES `mdl_grading_definitions` WRITE;
/*!40000 ALTER TABLE `mdl_grading_definitions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_grading_definitions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_grading_instances`
--

DROP TABLE IF EXISTS `mdl_grading_instances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_grading_instances` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `definitionid` bigint(10) NOT NULL,
  `raterid` bigint(10) NOT NULL,
  `itemid` bigint(10) DEFAULT NULL,
  `rawgrade` decimal(10,5) DEFAULT NULL,
  `status` bigint(10) NOT NULL DEFAULT 0,
  `feedback` longtext DEFAULT NULL,
  `feedbackformat` tinyint(2) DEFAULT NULL,
  `timemodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_gradinst_def_ix` (`definitionid`),
  KEY `mdl_gradinst_rat_ix` (`raterid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Grading form instance is an assessment record for one gradab';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_grading_instances`
--

LOCK TABLES `mdl_grading_instances` WRITE;
/*!40000 ALTER TABLE `mdl_grading_instances` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_grading_instances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_gradingform_guide_comments`
--

DROP TABLE IF EXISTS `mdl_gradingform_guide_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_gradingform_guide_comments` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `definitionid` bigint(10) NOT NULL,
  `sortorder` bigint(10) NOT NULL,
  `description` longtext DEFAULT NULL,
  `descriptionformat` tinyint(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_gradguidcomm_def_ix` (`definitionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='frequently used comments used in marking guide';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_gradingform_guide_comments`
--

LOCK TABLES `mdl_gradingform_guide_comments` WRITE;
/*!40000 ALTER TABLE `mdl_gradingform_guide_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_gradingform_guide_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_gradingform_guide_criteria`
--

DROP TABLE IF EXISTS `mdl_gradingform_guide_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_gradingform_guide_criteria` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `definitionid` bigint(10) NOT NULL,
  `sortorder` bigint(10) NOT NULL,
  `shortname` varchar(255) NOT NULL DEFAULT '',
  `description` longtext DEFAULT NULL,
  `descriptionformat` tinyint(2) DEFAULT NULL,
  `descriptionmarkers` longtext DEFAULT NULL,
  `descriptionmarkersformat` tinyint(2) DEFAULT NULL,
  `maxscore` decimal(10,5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_gradguidcrit_def_ix` (`definitionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores the rows of the criteria grid.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_gradingform_guide_criteria`
--

LOCK TABLES `mdl_gradingform_guide_criteria` WRITE;
/*!40000 ALTER TABLE `mdl_gradingform_guide_criteria` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_gradingform_guide_criteria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_gradingform_guide_fillings`
--

DROP TABLE IF EXISTS `mdl_gradingform_guide_fillings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_gradingform_guide_fillings` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `instanceid` bigint(10) NOT NULL,
  `criterionid` bigint(10) NOT NULL,
  `remark` longtext DEFAULT NULL,
  `remarkformat` tinyint(2) DEFAULT NULL,
  `score` decimal(10,5) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_gradguidfill_inscri_uix` (`instanceid`,`criterionid`),
  KEY `mdl_gradguidfill_ins_ix` (`instanceid`),
  KEY `mdl_gradguidfill_cri_ix` (`criterionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores the data of how the guide is filled by a particular r';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_gradingform_guide_fillings`
--

LOCK TABLES `mdl_gradingform_guide_fillings` WRITE;
/*!40000 ALTER TABLE `mdl_gradingform_guide_fillings` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_gradingform_guide_fillings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_gradingform_rubric_criteria`
--

DROP TABLE IF EXISTS `mdl_gradingform_rubric_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_gradingform_rubric_criteria` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `definitionid` bigint(10) NOT NULL,
  `sortorder` bigint(10) NOT NULL,
  `description` longtext DEFAULT NULL,
  `descriptionformat` tinyint(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_gradrubrcrit_def_ix` (`definitionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores the rows of the rubric grid.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_gradingform_rubric_criteria`
--

LOCK TABLES `mdl_gradingform_rubric_criteria` WRITE;
/*!40000 ALTER TABLE `mdl_gradingform_rubric_criteria` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_gradingform_rubric_criteria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_gradingform_rubric_fillings`
--

DROP TABLE IF EXISTS `mdl_gradingform_rubric_fillings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_gradingform_rubric_fillings` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `instanceid` bigint(10) NOT NULL,
  `criterionid` bigint(10) NOT NULL,
  `levelid` bigint(10) DEFAULT NULL,
  `remark` longtext DEFAULT NULL,
  `remarkformat` tinyint(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_gradrubrfill_inscri_uix` (`instanceid`,`criterionid`),
  KEY `mdl_gradrubrfill_lev_ix` (`levelid`),
  KEY `mdl_gradrubrfill_ins_ix` (`instanceid`),
  KEY `mdl_gradrubrfill_cri_ix` (`criterionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores the data of how the rubric is filled by a particular ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_gradingform_rubric_fillings`
--

LOCK TABLES `mdl_gradingform_rubric_fillings` WRITE;
/*!40000 ALTER TABLE `mdl_gradingform_rubric_fillings` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_gradingform_rubric_fillings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_gradingform_rubric_levels`
--

DROP TABLE IF EXISTS `mdl_gradingform_rubric_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_gradingform_rubric_levels` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `criterionid` bigint(10) NOT NULL,
  `score` decimal(10,5) NOT NULL,
  `definition` longtext DEFAULT NULL,
  `definitionformat` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_gradrubrleve_cri_ix` (`criterionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores the columns of the rubric grid.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_gradingform_rubric_levels`
--

LOCK TABLES `mdl_gradingform_rubric_levels` WRITE;
/*!40000 ALTER TABLE `mdl_gradingform_rubric_levels` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_gradingform_rubric_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_groupings`
--

DROP TABLE IF EXISTS `mdl_groupings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_groupings` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `courseid` bigint(10) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `idnumber` varchar(100) NOT NULL DEFAULT '',
  `description` longtext DEFAULT NULL,
  `descriptionformat` tinyint(2) NOT NULL DEFAULT 0,
  `configdata` longtext DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_grou_idn2_ix` (`idnumber`),
  KEY `mdl_grou_cou2_ix` (`courseid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='A grouping is a collection of groups. WAS: groups_groupings';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_groupings`
--

LOCK TABLES `mdl_groupings` WRITE;
/*!40000 ALTER TABLE `mdl_groupings` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_groupings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_groupings_groups`
--

DROP TABLE IF EXISTS `mdl_groupings_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_groupings_groups` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `groupingid` bigint(10) NOT NULL DEFAULT 0,
  `groupid` bigint(10) NOT NULL DEFAULT 0,
  `timeadded` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_grougrou_gro_ix` (`groupingid`),
  KEY `mdl_grougrou_gro2_ix` (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Link a grouping to a group (note, groups can be in multiple ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_groupings_groups`
--

LOCK TABLES `mdl_groupings_groups` WRITE;
/*!40000 ALTER TABLE `mdl_groupings_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_groupings_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_groups`
--

DROP TABLE IF EXISTS `mdl_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_groups` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `courseid` bigint(10) NOT NULL,
  `idnumber` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(254) NOT NULL DEFAULT '',
  `description` longtext DEFAULT NULL,
  `descriptionformat` tinyint(2) NOT NULL DEFAULT 0,
  `enrolmentkey` varchar(50) DEFAULT NULL,
  `picture` bigint(10) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_grou_idn_ix` (`idnumber`),
  KEY `mdl_grou_cou_ix` (`courseid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Each record represents a group.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_groups`
--

LOCK TABLES `mdl_groups` WRITE;
/*!40000 ALTER TABLE `mdl_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_groups_members`
--

DROP TABLE IF EXISTS `mdl_groups_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_groups_members` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `groupid` bigint(10) NOT NULL DEFAULT 0,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `timeadded` bigint(10) NOT NULL DEFAULT 0,
  `component` varchar(100) NOT NULL DEFAULT '',
  `itemid` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_groumemb_usegro_uix` (`userid`,`groupid`),
  KEY `mdl_groumemb_gro_ix` (`groupid`),
  KEY `mdl_groumemb_use_ix` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Link a user to a group.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_groups_members`
--

LOCK TABLES `mdl_groups_members` WRITE;
/*!40000 ALTER TABLE `mdl_groups_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_groups_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_h5p`
--

DROP TABLE IF EXISTS `mdl_h5p`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_h5p` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `jsoncontent` longtext NOT NULL,
  `mainlibraryid` bigint(10) NOT NULL,
  `displayoptions` smallint(4) DEFAULT NULL,
  `pathnamehash` varchar(40) NOT NULL DEFAULT '',
  `contenthash` varchar(40) NOT NULL DEFAULT '',
  `filtered` longtext DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_h5p_pat_ix` (`pathnamehash`),
  KEY `mdl_h5p_mai_ix` (`mainlibraryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED COMMENT='Stores H5P content information';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_h5p`
--

LOCK TABLES `mdl_h5p` WRITE;
/*!40000 ALTER TABLE `mdl_h5p` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_h5p` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'moodle'
--

--
-- Dumping routines for database 'moodle'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-25 19:15:15
