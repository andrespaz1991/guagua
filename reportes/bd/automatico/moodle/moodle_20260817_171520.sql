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

-- Dump completed on 2026-08-17 17:15:26
