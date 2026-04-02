-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: mybook_db
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
-- Table structure for table `content_i_follow`
--

DROP TABLE IF EXISTS `content_i_follow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `content_i_follow` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `userid` bigint(20) NOT NULL,
  `contentid` bigint(20) NOT NULL,
  `content_type` varchar(10) NOT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `contentid` (`contentid`),
  KEY `disabled` (`disabled`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content_i_follow`
--

LOCK TABLES `content_i_follow` WRITE;
/*!40000 ALTER TABLE `content_i_follow` DISABLE KEYS */;
INSERT INTO `content_i_follow` VALUES (2,45780258653,80037172373257309,'post',0,'2021-01-25 09:30:19'),(3,204306973626090829,80037172373257309,'post',0,'2021-01-25 09:37:50'),(4,204306973626090829,80037172373257309,'post',0,'2021-01-25 10:02:12'),(5,204306973626090829,1770298928112,'post',0,'2021-01-30 10:46:07'),(6,74891567,998913867690,'post',0,'2025-09-16 07:03:16');
/*!40000 ALTER TABLE `content_i_follow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_invitations`
--

DROP TABLE IF EXISTS `group_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_invitations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inviteid` bigint(20) unsigned NOT NULL,
  `groupid` bigint(20) unsigned NOT NULL,
  `invited_by` bigint(20) unsigned NOT NULL,
  `invited_user` bigint(20) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `message` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `responded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invite_unique` (`groupid`,`invited_user`),
  KEY `groupid_idx` (`groupid`),
  KEY `invited_user_idx` (`invited_user`),
  KEY `status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_invitations`
--

LOCK TABLES `group_invitations` WRITE;
/*!40000 ALTER TABLE `group_invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `likes`
--

DROP TABLE IF EXISTS `likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `likes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL,
  `likes` text NOT NULL,
  `contentid` bigint(20) NOT NULL,
  `following` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `contentid` (`contentid`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `likes`
--

LOCK TABLES `likes` WRITE;
/*!40000 ALTER TABLE `likes` DISABLE KEYS */;
INSERT INTO `likes` VALUES (1,'user','[{\"userid\":\"43452924198233985\",\"date\":\"2021-01-14 13:41:33\"},{\"userid\":\"45780258653\",\"date\":\"2021-01-25 09:18:17\"},{\"userid\":\"60231042455\",\"date\":\"2025-09-10 09:45:41\"},{\"userid\":\"2398855150546025\",\"date\":\"2025-09-10 09:55:21\"}]',204306973626090829,'[{\"userid\":\"43452924198233985\",\"date\":\"2021-01-19 23:47:38\"}]'),(2,'user','[{\"userid\":\"204306973626090829\",\"date\":\"2021-01-19 23:47:38\"},{\"userid\":\"45780258653\",\"date\":\"2021-01-25 09:18:25\"}]',43452924198233985,'[{\"userid\":\"204306973626090829\",\"date\":\"2021-01-14 13:41:33\"}]'),(3,'post','[{\"userid\":\"43452924198233985\",\"date\":\"2021-01-14 17:33:32\"}]',850055142927,''),(4,'post','[{\"userid\":\"43452924198233985\",\"date\":\"2021-01-19 22:49:51\"},{\"userid\":\"204306973626090829\",\"date\":\"2021-01-19 23:32:17\"}]',43109449895,''),(5,'post','[{\"userid\":\"43452924198233985\",\"date\":\"2021-01-19 00:01:26\"}]',762319448360468565,''),(6,'post','[{\"userid\":\"204306973626090829\",\"date\":\"2021-01-19 23:17:45\"}]',2714,''),(7,'post','[{\"userid\":\"204306973626090829\",\"date\":\"2021-01-19 23:32:00\"}]',805836070763966993,''),(8,'post','[{\"userid\":\"43452924198233985\",\"date\":\"2021-01-20 00:01:59\"}]',4747,''),(9,'post','[{\"userid\":\"43452924198233985\",\"date\":\"2021-01-22 00:25:46\"}]',5783740831,''),(10,'user','',45780258653,'[{\"userid\":\"204306973626090829\",\"date\":\"2021-01-25 09:18:17\"},{\"userid\":\"43452924198233985\",\"date\":\"2021-01-25 09:18:26\"}]'),(11,'user','[{\"userid\":\"60231042455\",\"date\":\"2025-09-10 09:45:30\"},{\"userid\":\"524499258041\",\"date\":\"2025-09-10 15:45:52\"},{\"userid\":\"6602115230037\",\"date\":\"2025-09-16 04:49:48\"},{\"userid\":\"6188790089780162757\",\"date\":\"2025-09-16 05:24:56\"},{\"userid\":\"69787\",\"date\":\"2025-10-20 20:33:37\"},{\"userid\":\"8481550\",\"date\":\"2026-03-31 13:19:06\"}]',60231042455,'{\"1\":{\"userid\":\"2398855150546025\",\"date\":\"2025-09-10 09:36:09\"},\"2\":{\"userid\":\"204306973626090829\",\"date\":\"2025-09-10 09:45:41\"}}'),(12,'user','[{\"userid\":\"60231042455\",\"date\":\"2025-09-10 09:36:09\"},{\"userid\":\"6602115230037\",\"date\":\"2025-09-16 04:50:52\"}]',2398855150546025,'[{\"userid\":\"60231042455\",\"date\":\"2025-09-10 09:55:08\"},{\"userid\":\"204306973626090829\",\"date\":\"2025-09-10 09:55:21\"}]'),(13,'post','[{\"userid\":\"6602115230037\",\"date\":\"2025-09-16 05:23:24\"}]',979,''),(14,'post','[{\"userid\":\"60231042455\",\"date\":\"2025-09-10 09:35:51\"}]',6693,''),(15,'user','',524499258041,'[{\"userid\":\"60231042455\",\"date\":\"2025-09-10 15:45:52\"}]'),(16,'post','[{\"userid\":\"60231042455\",\"date\":\"2025-09-10 17:43:35\"},{\"userid\":\"6188790089780162757\",\"date\":\"2025-09-16 05:25:09\"},{\"userid\":\"74891567\",\"date\":\"2025-09-16 07:03:04\"}]',998913867690,''),(17,'user','[]',6602115230037,'[{\"userid\":\"60231042455\",\"date\":\"2025-09-16 04:49:48\"}]'),(18,'post','[{\"userid\":\"6602115230037\",\"date\":\"2025-09-16 05:23:08\"},{\"userid\":\"6188790089780162757\",\"date\":\"2025-09-16 05:25:56\"}]',32231653606,''),(19,'user','',6188790089780162757,'[{\"userid\":\"60231042455\",\"date\":\"2025-09-16 05:24:56\"}]'),(20,'user','[{\"userid\":\"69787\",\"date\":\"2025-10-20 20:33:27\"},{\"userid\":\"1225621126478570\",\"date\":\"2025-10-20 22:41:36\"}]',69787,'[{\"userid\":\"60231042455\",\"date\":\"2025-10-20 20:33:37\"},{\"userid\":\"1225621126478570\",\"date\":\"2025-10-20 22:43:06\"}]'),(21,'post','[{\"userid\":\"69787\",\"date\":\"2025-10-20 20:46:37\"}]',58668534335929743,''),(22,'post','[]',726,''),(23,'user','[{\"userid\":\"1225621126478570\",\"date\":\"2025-10-20 22:41:48\"},{\"userid\":\"69787\",\"date\":\"2025-10-20 22:43:06\"}]',1225621126478570,'[{\"userid\":\"69787\",\"date\":\"2025-10-20 22:41:36\"}]'),(24,'user','',8481550,'[{\"userid\":\"60231042455\",\"date\":\"2026-03-31 13:19:06\"}]');
/*!40000 ALTER TABLE `likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_group_members`
--

DROP TABLE IF EXISTS `message_group_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_group_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `groupid` bigint(20) unsigned NOT NULL,
  `userid` bigint(20) unsigned NOT NULL,
  `joined_date` datetime NOT NULL DEFAULT current_timestamp(),
  `role` varchar(20) NOT NULL DEFAULT 'member',
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_user_unique` (`groupid`,`userid`),
  KEY `groupid_idx` (`groupid`),
  KEY `userid_idx` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_group_members`
--

LOCK TABLES `message_group_members` WRITE;
/*!40000 ALTER TABLE `message_group_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_group_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_groups`
--

DROP TABLE IF EXISTS `message_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `groupid` bigint(20) unsigned NOT NULL,
  `group_name` varchar(120) NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `group_profile` varchar(255) NOT NULL DEFAULT '',
  `group_description` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupid_unique` (`groupid`),
  KEY `created_by_idx` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_groups`
--

LOCK TABLES `message_groups` WRITE;
/*!40000 ALTER TABLE `message_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `messageid` bigint(20) unsigned NOT NULL,
  `sender` bigint(20) unsigned NOT NULL,
  `receiver` bigint(20) unsigned NOT NULL,
  `message` text NOT NULL,
  `seen` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_sender` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_receiver` tinyint(1) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `group_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `message_type` varchar(20) NOT NULL DEFAULT 'text',
  `file_path` varchar(255) NOT NULL DEFAULT '',
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `mime_type` varchar(120) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `messageid_unique` (`messageid`),
  KEY `sender_idx` (`sender`),
  KEY `receiver_idx` (`receiver`),
  KEY `seen_idx` (`seen`),
  KEY `date_idx` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1,18446744073709551615,8481550,667704455020509864,'hi',0,0,0,'2026-03-31 18:42:02',0,'text','','','');
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_seen`
--

DROP TABLE IF EXISTS `notification_seen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_seen` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `userid` bigint(20) NOT NULL,
  `notification_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `notification_id` (`notification_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_seen`
--

LOCK TABLES `notification_seen` WRITE;
/*!40000 ALTER TABLE `notification_seen` DISABLE KEYS */;
INSERT INTO `notification_seen` VALUES (1,204306973626090829,14),(6,204306973626090829,2),(8,45780258653,17),(9,204306973626090829,19),(10,43452924198233985,20),(11,43452924198233985,21),(12,45780258653,25),(13,2398855150546025,34),(14,69787,60);
/*!40000 ALTER TABLE `notification_seen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `userid` bigint(20) NOT NULL,
  `activity` varchar(10) NOT NULL,
  `contentid` bigint(20) NOT NULL,
  `content_owner` bigint(20) NOT NULL,
  `content_type` varchar(10) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `contentid` (`contentid`),
  KEY `content_owner` (`content_owner`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,43452924198233985,'like',43109449895,204306973626090829,'post','2021-01-19 00:05:47'),(2,43452924198233985,'like',43109449895,204306973626090829,'post','2021-01-19 22:49:50'),(3,43452924198233985,'like',43109449895,204306973626090829,'post','2021-01-19 22:49:51'),(4,204306973626090829,'like',2714,204306973626090829,'post','2021-01-19 23:17:45'),(5,204306973626090829,'like',805836070763966993,43452924198233985,'post','2021-01-19 23:32:01'),(6,204306973626090829,'like',43109449895,204306973626090829,'post','2021-01-19 23:32:17'),(12,204306973626090829,'follow',43452924198233985,43452924198233985,'profile','2021-01-19 23:47:38'),(13,43452924198233985,'like',4747,43452924198233985,'post','2021-01-20 00:01:59'),(14,43452924198233985,'like',5783740831,204306973626090829,'comment','2021-01-22 00:25:47'),(15,45780258653,'follow',204306973626090829,204306973626090829,'profile','2021-01-25 09:18:17'),(16,45780258653,'follow',43452924198233985,43452924198233985,'profile','2021-01-25 09:18:26'),(17,204306973626090829,'comment',80037172373257309,43452924198233985,'post','2021-01-25 09:37:51'),(18,204306973626090829,'comment',80037172373257309,43452924198233985,'post','2021-01-25 10:02:12'),(19,43452924198233985,'tag',1770298928112,204306973626090829,'post','2021-01-30 10:38:56'),(20,204306973626090829,'comment',1770298928112,43452924198233985,'post','2021-01-30 10:46:07'),(21,204306973626090829,'tag',854556,43452924198233985,'comment','2021-01-30 10:46:08'),(22,43452924198233985,'tag',1770298928112,204306973626090829,'post','2021-01-30 11:10:24'),(23,43452924198233985,'tag',1770298928112,204306973626090829,'post','2021-01-30 11:10:55'),(24,43452924198233985,'tag',1770298928112,45780258653,'post','2021-01-30 11:16:11'),(25,43452924198233985,'tag',1770298928112,45780258653,'post','2021-01-30 11:16:45'),(26,212208,'tag',33503,212208,'post','2021-01-30 11:38:59'),(27,43452924198233985,'tag',2206778395,212208,'post','2021-01-30 11:39:31'),(28,60231042455,'follow',204306973626090829,204306973626090829,'profile','2025-09-09 11:52:25'),(29,60231042455,'follow',2398855150546025,2398855150546025,'profile','2025-09-09 12:00:49'),(30,60231042455,'follow',2398855150546025,2398855150546025,'profile','2025-09-09 12:11:58'),(31,60231042455,'like',979,60231042455,'post','2025-09-10 09:35:18'),(32,60231042455,'like',6693,60231042455,'post','2025-09-10 09:35:23'),(33,60231042455,'like',6693,60231042455,'post','2025-09-10 09:35:51'),(34,60231042455,'follow',2398855150546025,2398855150546025,'profile','2025-09-10 09:36:09'),(35,60231042455,'follow',204306973626090829,204306973626090829,'profile','2025-09-10 09:45:41'),(36,2398855150546025,'follow',60231042455,60231042455,'profile','2025-09-10 09:55:08'),(37,2398855150546025,'follow',204306973626090829,204306973626090829,'profile','2025-09-10 09:55:21'),(38,524499258041,'follow',60231042455,60231042455,'profile','2025-09-10 15:45:52'),(39,60231042455,'like',998913867690,60231042455,'post','2025-09-10 17:43:35'),(40,6602115230037,'follow',2398855150546025,2398855150546025,'profile','2025-09-16 04:48:16'),(41,6602115230037,'follow',2398855150546025,2398855150546025,'profile','2025-09-16 04:48:24'),(42,6602115230037,'follow',2398855150546025,2398855150546025,'profile','2025-09-16 04:48:28'),(43,6602115230037,'follow',2398855150546025,2398855150546025,'profile','2025-09-16 04:48:37'),(44,6602115230037,'follow',60231042455,60231042455,'profile','2025-09-16 04:49:48'),(45,6602115230037,'follow',2398855150546025,2398855150546025,'profile','2025-09-16 04:49:56'),(46,6602115230037,'follow',2398855150546025,2398855150546025,'profile','2025-09-16 04:50:51'),(47,6602115230037,'like',32231653606,6602115230037,'post','2025-09-16 05:23:08'),(48,6602115230037,'like',979,60231042455,'post','2025-09-16 05:23:24'),(49,6188790089780162757,'follow',60231042455,60231042455,'profile','2025-09-16 05:24:56'),(50,6188790089780162757,'like',998913867690,60231042455,'post','2025-09-16 05:25:09'),(51,6188790089780162757,'like',32231653606,6602115230037,'post','2025-09-16 05:25:56'),(52,74891567,'like',998913867690,60231042455,'post','2025-09-16 07:03:04'),(53,74891567,'comment',998913867690,60231042455,'post','2025-09-16 07:03:16'),(54,69787,'follow',60231042455,60231042455,'profile','2025-10-20 20:33:37'),(55,69787,'like',58668534335929743,69787,'post','2025-10-20 20:46:37'),(56,69787,'like',726,69787,'post','2025-10-20 21:16:23'),(57,69787,'tag',4749,69787,'post','2025-10-20 21:28:12'),(58,69787,'tag',28833525693656952,60231042455,'post','2025-10-20 21:29:00'),(59,69787,'follow',43452924198233985,43452924198233985,'profile','2025-10-20 22:22:32'),(60,1225621126478570,'follow',69787,69787,'profile','2025-10-20 22:41:36'),(61,69787,'follow',1225621126478570,1225621126478570,'profile','2025-10-20 22:43:06'),(62,8481550,'follow',60231042455,60231042455,'profile','2026-03-31 13:19:06');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `postid` bigint(20) NOT NULL,
  `post` text NOT NULL,
  `image` varchar(500) NOT NULL,
  `has_image` tinyint(1) NOT NULL,
  `is_profile_image` tinyint(1) NOT NULL,
  `is_cover_image` tinyint(1) NOT NULL,
  `parent` bigint(20) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `userid` bigint(20) NOT NULL,
  `likes` int(11) NOT NULL,
  `comments` int(11) NOT NULL,
  `tags` varchar(2048) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `postid` (`postid`),
  KEY `date` (`date`),
  KEY `parent` (`parent`),
  KEY `userid` (`userid`),
  KEY `likes` (`likes`),
  KEY `comments` (`comments`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,4747,'hey hey there','',0,0,0,0,'2021-01-12 21:04:52',43452924198233985,2,1,''),(2,23456160699398337,'a comment','',0,0,0,4747,'2021-01-12 21:25:19',43452924198233985,0,0,''),(3,805836070763966993,'','uploads/43452924198233985/cqKak3euuT8F8cl.jpg',1,0,1,0,'2021-01-14 04:42:37',43452924198233985,1,0,''),(5,67812054557977806,'','uploads/43452924198233985/bBYxZ2IEtOOQKSc.jpg',1,1,0,0,'2021-01-14 04:43:00',43452924198233985,0,0,''),(6,80037172373257309,'','uploads/43452924198233985/7RaXHpdjK2d6Abs.jpg',1,1,0,0,'2021-01-14 04:43:25',43452924198233985,0,7,''),(7,2714,'','uploads/204306973626090829/y68VT8ZQ17K3pUc.jpg',1,1,0,0,'2021-01-14 04:46:50',204306973626090829,1,0,''),(8,43109449895,'','uploads/204306973626090829/53Djo3ipQL2QofM.jpg',1,0,1,0,'2021-01-14 04:47:01',204306973626090829,2,0,''),(9,762319448360468565,'nice profile image','',0,0,0,80037172373257309,'2021-01-14 04:47:47',204306973626090829,1,0,''),(10,5783740831,'a second comment','',0,0,0,80037172373257309,'2021-01-14 05:03:13',204306973626090829,1,0,''),(11,6598910106126378,'commenting on my own post','',0,0,0,80037172373257309,'2021-01-14 05:11:04',43452924198233985,0,0,''),(12,850055142927,'a picture post','uploads/43452924198233985/3K078PtAVpmDn52.jpg',1,0,0,0,'2021-01-14 06:54:28',43452924198233985,1,0,''),(13,222104001751,'this is a third person comment','',0,0,0,80037172373257309,'2021-01-25 10:26:07',45780258653,0,0,''),(14,288955543664288,'another comment','',0,0,0,80037172373257309,'2021-01-25 10:30:19',45780258653,0,0,''),(15,31137199,'a comment from mary','',0,0,0,80037172373257309,'2021-01-25 10:37:51',204306973626090829,0,0,''),(16,99058875,'mary comment','',0,0,0,80037172373257309,'2021-01-25 11:02:13',204306973626090829,0,0,''),(19,46467689140,'','uploads/43452924198233985/p0dvNVKxAGXCqEx.jpg',1,0,0,0,'2021-01-25 13:35:07',43452924198233985,0,0,''),(20,78094,'am tagging @john in this post','',0,0,0,0,'2021-01-29 13:44:12',43452924198233985,0,0,''),(21,682147,'@mary, how are you?','',0,0,0,0,'2021-01-29 13:49:02',43452924198233985,0,0,''),(22,1648938600,'@mary am tagging you','',0,0,0,0,'2021-01-30 11:33:59',43452924198233985,0,0,'[\"@mary\"]'),(23,421555710,'@mary am tagging you again','',0,0,0,0,'2021-01-30 11:37:53',43452924198233985,0,0,'[]'),(24,1770298928112,'@mary @john again again','',0,0,0,0,'2021-01-30 11:38:56',43452924198233985,0,1,'[\"mary\"]'),(25,854556,'@thorne i\'ve seen your tag','',0,0,0,1770298928112,'2021-01-30 11:46:08',204306973626090829,0,0,'[\"thorne\"]'),(26,33503,'@peter you have tagged yourself','',0,0,0,0,'2021-01-30 12:38:58',212208,0,0,'[\"peter\"]'),(27,2206778395,'hey @peter','',0,0,0,0,'2021-01-30 12:39:30',43452924198233985,0,0,'[\"peter\"]'),(28,4854098,'hiiii','',0,0,0,0,'2025-09-09 16:53:09',60231042455,0,0,'[]'),(29,711376955046950,'are u here\r\n','',0,0,0,0,'2025-09-09 16:59:08',2398855150546025,0,0,'[]'),(30,6693,'','uploads/60231042455/F6nHP2F4TOXpDtS.jpg',1,0,0,0,'2025-09-10 14:29:13',60231042455,1,1,'[]'),(31,979,'','uploads/60231042455/wb3LttbSMDxD20M.jpg',1,1,0,0,'2025-09-10 14:29:44',60231042455,1,0,'[]'),(32,78741806701240,'nice picture','',0,0,0,6693,'2025-09-10 14:35:39',60231042455,0,0,'[]'),(33,998913867690,'','',0,0,1,0,'2025-09-10 16:48:39',60231042455,3,1,'[]'),(34,226193074788088558,'','',0,1,0,0,'2025-09-10 20:45:05',524499258041,0,0,'[]'),(35,99049,'','',0,0,1,0,'2025-09-10 20:45:31',524499258041,0,0,'[]'),(36,132603768898,'','uploads/524499258041/wGOXVDzq2hp9Jy5.jpg',1,0,0,0,'2025-09-10 20:47:15',524499258041,0,0,'[]'),(37,493355684639,'hello im doing okey\r\n','',0,0,0,0,'2025-09-10 20:47:27',524499258041,0,0,'[]'),(38,14002,'','',0,1,0,0,'2025-09-10 22:41:57',667704455020509864,0,0,'[]'),(39,32231653606,'','uploads/6602115230037/NrxBJYAuICIHNaY.jpg',1,0,0,0,'2025-09-16 10:23:02',6602115230037,2,0,'[]'),(40,54,'where r u?','',0,0,0,998913867690,'2025-09-16 12:03:16',74891567,0,0,'[]'),(41,58668534335929743,'the earth is flat my guy ...','',0,0,0,0,'2025-10-21 01:46:30',69787,1,0,'[]'),(46,726,'the phone and the hat arent mine ... the glasses too eheheh','uploads/69787/gkjry75f127kW1B.jpg',1,0,0,0,'2025-10-21 01:49:47',69787,0,0,'[]'),(49,28833525693656952,'@hermannnzi wtf u doin?','',0,0,0,0,'2025-10-21 02:29:00',69787,0,0,'[\"hermannnzi\"]'),(50,874818161773142397,'','uploads/8481550/dvPimA1CmBNiDHZ.jpg',1,1,0,0,'2026-03-31 18:23:44',8481550,0,0,'[]');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `userid` bigint(20) NOT NULL,
  `first_name` varchar(20) NOT NULL,
  `last_name` varchar(20) NOT NULL,
  `gender` varchar(6) NOT NULL,
  `profile_image` varchar(500) NOT NULL,
  `cover_image` varchar(500) NOT NULL,
  `date` year(4) NOT NULL,
  `online` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(64) NOT NULL,
  `url_address` varchar(100) NOT NULL,
  `likes` int(11) NOT NULL,
  `about` text NOT NULL,
  `tag_name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `date` (`date`),
  KEY `online` (`online`),
  KEY `email` (`email`),
  KEY `url_address` (`url_address`),
  KEY `likes` (`likes`),
  KEY `tag_name` (`tag_name`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,43452924198233985,'Eathorne','Choongo','Male','uploads/43452924198233985/7RaXHpdjK2d6Abs.jpg','uploads/43452924198233985/cqKak3euuT8F8cl.jpg',0000,0,'eathorne@yahoo.com','5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8','eathorne.choongo',2,'','thorne'),(2,204306973626090829,'Mary','Phiri','Female','uploads/204306973626090829/y68VT8ZQ17K3pUc.jpg','uploads/204306973626090829/53Djo3ipQL2QofM.jpg',0000,0,'maryphiri@yahoo.com','5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8','mary.phiri',4,'','mary'),(3,45780258653,'John','Captain','Male','','',0000,0,'john@yahoo.com','5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8','john.captain',0,'','john'),(5,212208,'Peter','Man','Male','','',0000,0,'peter@yahoo.com','5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8','peter.man',0,'','peter'),(8,524499258041,'Tom','Clay','Male','uploads/524499258041/DwaHcceq4CziYPu.jpg','uploads/524499258041/mSb21oIuPgIs46I.jpg',0000,0,'clay@gmail.com','3163d7f0efe6a79d936d26785f938443facd7940','tom.clay',0,'','tomclay'),(10,6602115230037,'Zeko','Khalid','Male','','',0000,0,'zeko@gmail.com','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','zeko.khalid',0,'','zekokhalid'),(12,74891567,'Sneha','Ppp','Female','','',0000,0,'sp@au.edu','40bd001563085fc35165329ea1ff5c5ecbdbbeef','sneha.ppp',0,'','snehappp'),(14,1225621126478570,'Ash','Ash','Female','','',0000,1760992908,'ashash@gmail.com','7110eda4d09e062aa5e4a390b0a572ac0d2c0220','ash.ash',2,'','ashash'),(16,2922253697632836125,'Hermann','N\'zi','Male','','',0000,1774995902,'u6411076@au.edu','da9d108421837fa215aa967a5c994f53c12560be','hermann.nzi',0,'','hermannnzi');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-01  5:25:19
