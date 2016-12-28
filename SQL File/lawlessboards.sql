/*
Navicat MySQL Data Transfer

Source Server         : Local
Source Server Version : 50516
Source Host           : localhost:3306
Source Database       : lawlessboards

Target Server Type    : MYSQL
Target Server Version : 50516
File Encoding         : 65001

Date: 2013-10-20 11:00:13
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for accounts
-- ----------------------------
DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(130) DEFAULT NULL,
  `displayname` varchar(50) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `birthday` varchar(50) DEFAULT NULL,
  `country` varchar(75) DEFAULT NULL,
  `timezone` float(12,1) DEFAULT NULL,
  `key` varchar(100) DEFAULT NULL,
  `verified` int(12) DEFAULT NULL,
  `usertitle` varchar(1000) DEFAULT NULL,
  `ip` varchar(16) DEFAULT NULL,
  `lastactivity` int(12) DEFAULT NULL,
  `avatar` varchar(200) DEFAULT 'images/defaultavatar.jpg',
  `hidden` int(12) DEFAULT NULL,
  `signature` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for attachments
-- ----------------------------
DROP TABLE IF EXISTS `attachments`;
CREATE TABLE `attachments` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `thread` int(12) DEFAULT NULL,
  `post` int(12) DEFAULT NULL,
  `path` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for categories
-- ----------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `order` int(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for comments
-- ----------------------------
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `thread` int(12) DEFAULT NULL,
  `poster` int(12) DEFAULT NULL,
  `date` int(12) DEFAULT NULL,
  `comment` varchar(10000) DEFAULT NULL,
  `hidden` int(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1

-- ----------------------------
-- Table structure for likes
-- ----------------------------
DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `thread` int(12) DEFAULT NULL,
  `post` int(12) DEFAULT NULL,
  `like` int(12) DEFAULT NULL,
  `user` int(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for navigation
-- ----------------------------
DROP TABLE IF EXISTS `navigation`;
CREATE TABLE `navigation` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `text` varchar(100) CHARACTER SET utf32 DEFAULT NULL,
  `link` varchar(300) DEFAULT NULL,
  `hidden` int(12) DEFAULT NULL,
  `loginonly` int(12) DEFAULT NULL,
  `logoutonly` int(12) DEFAULT NULL,
  `order` int(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of navigation
-- ----------------------------
INSERT INTO `navigation` VALUES ('1', '&#xf015; Home', 'index', null, null, null, '1');
INSERT INTO `navigation` VALUES ('2', '&#xf007; Register', 'register', null, null, '1', '2');
INSERT INTO `navigation` VALUES ('3', '&#xf023; Login', 'login', null, null, '1', '3');
INSERT INTO `navigation` VALUES ('4', '&#xf08b; Log Out', 'logout', null, '1', null, '99999');
INSERT INTO `navigation` VALUES ('5', '&#xf002; Search', 'search', null, null, null, '4');
INSERT INTO `navigation` VALUES ('6', '&#xf007; My Profile', 'user?id=me', null, '1', null, '5');
INSERT INTO `navigation` VALUES ('7', '&#xf013; Settings', 'settings?view=profile', null, '1', null, '6');

-- ----------------------------
-- Table structure for polloptions
-- ----------------------------
DROP TABLE IF EXISTS `polloptions`;
CREATE TABLE `polloptions` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `poll` int(12) DEFAULT NULL,
  `text` varchar(200) DEFAULT NULL,
  `votes` int(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for polls
-- ----------------------------
DROP TABLE IF EXISTS `polls`;
CREATE TABLE `polls` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `thread` int(12) DEFAULT NULL,
  `text` varchar(200) DEFAULT NULL,
  `voters` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for privatemessages
-- ----------------------------
DROP TABLE IF EXISTS `privatemessages`;
CREATE TABLE `privatemessages` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `from` int(12) DEFAULT NULL,
  `to` int(12) DEFAULT NULL,
  `date` int(12) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `message` varchar(15000) DEFAULT NULL,
  `read` int(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for profilemessages
-- ----------------------------
DROP TABLE IF EXISTS `profilemessages`;
CREATE TABLE `profilemessages` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `user` int(12) DEFAULT NULL,
  `poster` int(12) DEFAULT NULL,
  `date` int(12) DEFAULT NULL,
  `message` varchar(5000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for rating
-- ----------------------------
DROP TABLE IF EXISTS `rating`;
CREATE TABLE `rating` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `thread` int(12) DEFAULT NULL,
  `rating` int(12) DEFAULT NULL,
  `user` int(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for read
-- ----------------------------
DROP TABLE IF EXISTS `read`;
CREATE TABLE `read` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `thread` int(12) DEFAULT NULL,
  `user` int(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for sections
-- ----------------------------
DROP TABLE IF EXISTS `sections`;
CREATE TABLE `sections` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `category` int(12) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `order` int(12) DEFAULT NULL,
  `parent` int(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for staffpermissions
-- ----------------------------
DROP TABLE IF EXISTS `staffpermissions`;
CREATE TABLE `staffpermissions` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(5000) DEFAULT NULL,
  `deleteattachment` int(12) DEFAULT NULL,
  `editpost` int(12) DEFAULT NULL,
  `hidepost` int(12) DEFAULT NULL,
  `deletepost` int(12) DEFAULT NULL,
  `editsettings` int(12) DEFAULT NULL,
  `changetemplate` int(12) DEFAULT NULL,
  `sectioncategory` int(12) DEFAULT NULL,
  `prefixes` int(12) DEFAULT NULL,
  `users` int(12) DEFAULT NULL,
  `usergroups` int(12) DEFAULT NULL,
  `infractions` int(12) DEFAULT NULL,
  `lockthreads` int(12) DEFAULT NULL,
  `movethreads` int(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for threads
-- ----------------------------
DROP TABLE IF EXISTS `threads`;
CREATE TABLE `threads` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `section` int(12) DEFAULT NULL,
  `poster` int(12) DEFAULT NULL,
  `date` int(12) DEFAULT NULL,
  `lastpost` int(12) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `body` varchar(20000) DEFAULT NULL,
  `locked` int(12) DEFAULT NULL,
  `views` int(12) NOT NULL,
  `hidden` int(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for usergroups
-- ----------------------------
DROP TABLE IF EXISTS `usergroups`;
CREATE TABLE `usergroups` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(5000) DEFAULT NULL,
  `nametag` varchar(1000) DEFAULT NULL,
  `nametagclose` varchar(1000) DEFAULT NULL,
  `title` varchar(1000) DEFAULT NULL,
  `passwordchange` int(12) DEFAULT NULL,
  `passwordhistory` int(12) DEFAULT NULL,
  `apply` int(12) DEFAULT NULL,
  `viewforum` int(12) DEFAULT NULL,
  `viewotherthreads` int(12) DEFAULT NULL,
  `downloadattachments` int(12) DEFAULT NULL,
  `viewreadthread` int(12) DEFAULT NULL,
  `search` int(12) DEFAULT NULL,
  `postthreads` int(12) DEFAULT NULL,
  `postowncomments` int(12) DEFAULT NULL,
  `postcomments` int(12) DEFAULT NULL,
  `editownposts` int(12) DEFAULT NULL,
  `hideownposts` int(12) DEFAULT NULL,
  `hideownthreads` int(12) DEFAULT NULL,
  `lockownthreads` int(12) DEFAULT NULL,
  `moveownthreads` int(12) DEFAULT NULL,
  `ratethreads` int(12) DEFAULT NULL,
  `mentionusers` int(12) DEFAULT NULL,
  `uploadattachments` int(12) DEFAULT NULL,
  `attachmentspacelimit` int(12) NOT NULL,
  `postpolls` int(12) DEFAULT NULL,
  `votepolls` int(12) DEFAULT NULL,
  `viewcalender` int(12) DEFAULT NULL,
  `viewonline` int(12) DEFAULT NULL,
  `viewip` int(12) DEFAULT NULL,
  `staff` int(12) DEFAULT NULL,
  `viewprofile` int(12) DEFAULT NULL,
  `hiddenmode` int(12) DEFAULT NULL,
  `viewhidden` int(12) DEFAULT NULL,
  `setowntitle` int(12) DEFAULT NULL,
  `viewotheravatar` int(12) DEFAULT NULL,
  `showpostedit` int(12) DEFAULT NULL,
  `uploadavatar` int(12) DEFAULT NULL,
  `animatedavatar` int(12) DEFAULT NULL,
  `maxavatarwidth` int(12) DEFAULT NULL,
  `maxavatarheight` int(12) DEFAULT NULL,
  `maxavatarsize` int(12) DEFAULT NULL,
  `signatureimage` int(12) DEFAULT NULL,
  `signatureanimatedimage` int(12) DEFAULT NULL,
  `allowsignature` int(12) DEFAULT NULL,
  `maxsignature` int(12) DEFAULT NULL,
  `maxsignaturelines` int(12) DEFAULT NULL,
  `signaturebbcode` int(12) DEFAULT NULL,
  `like` int(12) DEFAULT NULL,
  `dislike` int(12) DEFAULT NULL,
  `postownprofilemessage` int(12) DEFAULT NULL,
  `postprofilemessage` int(12) DEFAULT NULL,
  `editownprofilemessage` int(12) DEFAULT NULL,
  `deleteownprofilemessage` int(12) DEFAULT NULL,
  `postshadow` varchar(12) DEFAULT NULL,
  `deleteownposts` int(12) DEFAULT NULL,
  `deleteownthreads` int(12) DEFAULT NULL,
  `viewhiddencomments` int(12) DEFAULT NULL,
  `viewownhiddencomments` int(12) DEFAULT NULL,
  `viewhiddenthreads` int(12) DEFAULT NULL,
  `sendprivatemessage` int(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for usergroup_tracker
-- ----------------------------
DROP TABLE IF EXISTS `usergroup_tracker`;
CREATE TABLE `usergroup_tracker` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `user` int(12) DEFAULT NULL,
  `usergroup` int(12) DEFAULT NULL,
  `primary` int(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
