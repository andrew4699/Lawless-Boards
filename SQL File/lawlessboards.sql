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
-- Records of attachments
-- ----------------------------
INSERT INTO `attachments` VALUES ('33', '1', '19', 'C:/xampp/1/ds_digital.zip');
INSERT INTO `attachments` VALUES ('34', '1', '19', 'C:/xampp/1/FairPlay.inc');

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
-- Records of categories
-- ----------------------------
INSERT INTO `categories` VALUES ('1', 'Basic Category', 'This is the description', '1');
INSERT INTO `categories` VALUES ('2', 'Other Category', '', '2');

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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of comments
-- ----------------------------
INSERT INTO `comments` VALUES ('17', '8', '3', '1368371113', '[B]test[/B]\r\n\r\n[I]test[/I]\r\n[U]tefa[/U]\r\n[LEFT]is[/LEFT]\r\n[CENTER]awesome[/CENTER]\r\n[RIGHT]kosomoko[/RIGHT]\r\n[LIST]koloko[/LIST]\r\n[NLIST]wat is dat[/NLIST]\r\n[LI]hi[/LI]\r\n[EMAIL]jasonraymanz@gmail.com[/EMAIL]\r\n\r\n[QUOTE]test[/QUOTE]\r\n\r\n[FONT=Arial]Only Arial font?[/FONT]\r\n[SIZE=5]only size5 ?[/SIZE]\r\n[COLOR=RED]ONLY RED COLOR?[/COLOR]', null);
INSERT INTO `comments` VALUES ('18', '9', '1', '1368373381', 'this is my comment\r\n\r\n[NLIST][LI]list item 1[/LI][LI]list item 2[/LI][LI]number lists work[/LI][/NLIST]\r\n\r\n[QUOTE]quote: hi[/QUOTE]', null);
INSERT INTO `comments` VALUES ('19', '9', '1', '1368376800', '[URL=http://example.com]test link[/URL]', null);

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
-- Records of likes
-- ----------------------------
INSERT INTO `likes` VALUES ('10', '1', '18', '1', '1');
INSERT INTO `likes` VALUES ('11', '1', '18', '0', '6');

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
-- Records of polloptions
-- ----------------------------
INSERT INTO `polloptions` VALUES ('11', '3', 'Spongebob\r', '2');
INSERT INTO `polloptions` VALUES ('12', '3', 'Family Guy\r', '0');
INSERT INTO `polloptions` VALUES ('13', '3', 'American Dad\r', '0');
INSERT INTO `polloptions` VALUES ('14', '3', 'South Park', '0');

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
-- Records of polls
-- ----------------------------
INSERT INTO `polls` VALUES ('3', '18', 'Best cartoon?', '[1][6]');

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
-- Records of privatemessages
-- ----------------------------
INSERT INTO `privatemessages` VALUES ('1', '1', '1', null, 'hello', 'how are you doing', '1');
INSERT INTO `privatemessages` VALUES ('2', '1', '1', '1370920934', 'yoo', 'wattup', '1');
INSERT INTO `privatemessages` VALUES ('3', '1', '1', '1370921719', 'sdfadsf', 'gafsgdsf', '1');

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
-- Records of profilemessages
-- ----------------------------
INSERT INTO `profilemessages` VALUES ('6', '1', '1', '1370536730', 'hey [MENTION]Aaron[/MENTION], i deleted my old profile message after editing it huehuehue');
INSERT INTO `profilemessages` VALUES ('7', '1', '6', '1370536789', 'Test efrfddf');
INSERT INTO `profilemessages` VALUES ('8', '6', '1', '1370798262', 'stop being such a slut');

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
-- Records of rating
-- ----------------------------
INSERT INTO `rating` VALUES ('1', '18', '5', '1');
INSERT INTO `rating` VALUES ('2', '18', '1', '6');

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
-- Records of read
-- ----------------------------
INSERT INTO `read` VALUES ('3', '1', '1');
INSERT INTO `read` VALUES ('6', '16', '1');
INSERT INTO `read` VALUES ('7', '16', '6');
INSERT INTO `read` VALUES ('9', '18', '1');
INSERT INTO `read` VALUES ('10', '18', '2');
INSERT INTO `read` VALUES ('11', '18', '6');
INSERT INTO `read` VALUES ('12', '19', '1');
INSERT INTO `read` VALUES ('13', '9', '1');
INSERT INTO `read` VALUES ('14', '8', '1');

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
-- Records of sections
-- ----------------------------
INSERT INTO `sections` VALUES ('1', '1', 'Basic Section', 'This is the section', '1', null);
INSERT INTO `sections` VALUES ('2', '1', 'Other Section', '', '0', null);
INSERT INTO `sections` VALUES ('3', '1', 'Child Section', 'This is a child section', '1', '1');
INSERT INTO `sections` VALUES ('6', '1', 'test', 'it', '1', '3');
INSERT INTO `sections` VALUES ('7', '1', 'herp', 'derp', '1', '6');
INSERT INTO `sections` VALUES ('8', '1', 'test2', 'lol', '2', '3');

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
-- Records of staffpermissions
-- ----------------------------
INSERT INTO `staffpermissions` VALUES ('1', 'Administrator', 'These are the administrator permisions which include all permissions.', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1');

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
-- Records of threads
-- ----------------------------
INSERT INTO `threads` VALUES ('8', '7', '3', '1368369893', '1368371113', 'ja bd fda ge', 'okk', '0', '368', '0');
INSERT INTO `threads` VALUES ('9', '3', '1', '1368373321', '1368376800', 'thread', 'hi ppl', null, '237', null);
INSERT INTO `threads` VALUES ('16', '1', '1', '1370363011', '1370363011', 'attachments', 'testing attachments', null, '302', null);
INSERT INTO `threads` VALUES ('18', '1', '1', '1370467108', '1370467108', 'poll test', 'asdfadsf', '1', '420', null);
INSERT INTO `threads` VALUES ('19', '1', '1', '1379819001', '1379819001', 'new attachments', 'hello', null, '8', null);

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
-- Records of usergroups
-- ----------------------------
INSERT INTO `usergroups` VALUES ('2', 'Unregistered Users', 'Users who are not logged use this group\'s permissions.', '<span style=\'color: black;\'>', '</span>', null, null, null, null, '1', '1', null, null, '1', null, null, null, null, null, null, null, null, null, null, null, '0', null, null, null, '1', null, null, '1', null, null, null, '1', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, '0', '0', null, null, null, null);
INSERT INTO `usergroups` VALUES ('3', 'Registered Users', 'Users are put in this group automatically.', '<span style=\'color: black\'>', '</span>', null, null, null, null, '1', '1', '1', '1', '1', '1', null, '1', '1', null, null, null, null, '1', '1', '1', '2500000', '1', '1', '1', '1', null, null, '1', '1', null, null, '1', '1', '1', '1', '150', '150', '1000000', '1', '1', '1', '1000', '20', '1', '1', '1', '1', '1', '1', null, null, '0', '0', null, null, null, '1');
INSERT INTO `usergroups` VALUES ('4', 'Administrator', 'This is the administrator group with all permissions.', '<span style=\'color: black; font-style: italic;\'>', '</span>', 'Administrator', null, null, null, '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '2147483647', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '200', '200', '1500000', '1', '1', '1', '10000', '100', '1', '1', '1', '1', '1', '1', '1', '', '1', '1', '1', '1', '1', '1');

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

-- ----------------------------
-- Records of usergroup_tracker
-- ----------------------------
INSERT INTO `usergroup_tracker` VALUES ('2', '1', '3', '0');
INSERT INTO `usergroup_tracker` VALUES ('3', '2', '3', '0');
INSERT INTO `usergroup_tracker` VALUES ('4', '3', '3', '1');
INSERT INTO `usergroup_tracker` VALUES ('5', '4', '3', '1');
INSERT INTO `usergroup_tracker` VALUES ('6', '5', '3', '1');
INSERT INTO `usergroup_tracker` VALUES ('7', '6', '3', '0');
INSERT INTO `usergroup_tracker` VALUES ('8', '1', '4', '1');
INSERT INTO `usergroup_tracker` VALUES ('9', '2', '4', '1');
INSERT INTO `usergroup_tracker` VALUES ('10', '7', '3', '1');
INSERT INTO `usergroup_tracker` VALUES ('11', '6', '4', '1');
