SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Table structure for table `groups`
CREATE TABLE IF NOT EXISTS `groups` (
    `groupID` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(64) NOT NULL,
    `description` text,
    `image` varchar(255) DEFAULT NULL,
    `color` varchar(6) DEFAULT NULL,
    `is_private` tinyint(1) NOT NULL,
    `is_closed` tinyint(1) NOT NULL,
    PRIMARY KEY (`groupID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- Table structure for table `groups_posts`
CREATE TABLE IF NOT EXISTS `groups_posts` (
    `groupID` int(11) NOT NULL,
    `postID` int(11) NOT NULL,
    `reposted_by_userID` int(11) NOT NULL,
    `score` int(11) NOT NULL,
    PRIMARY KEY (`groupID`,`postID`),
    KEY `postID` (`postID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Table structure for table `languages`
CREATE TABLE IF NOT EXISTS `languages` (
    `languageID` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `translated` tinyint(1) NOT NULL,
    PRIMARY KEY (`languageID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Table structure for table `posts`
CREATE TABLE IF NOT EXISTS `posts` (
    `postID` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) DEFAULT NULL,
    `markdown` text,
    `image` varchar(255) DEFAULT NULL,
    `video` varchar(255) DEFAULT NULL,
    `link` text,
    `caption` text,
    `in_reply_to_postID` int(11) DEFAULT NULL,
    PRIMARY KEY (`postID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- Table structure for table `users`
CREATE TABLE IF NOT EXISTS `users` (
    `userID` int(11) NOT NULL AUTO_INCREMENT,
    `handle` varchar(50) NOT NULL,
    `first_name` varchar(255) NOT NULL,
    `middle_name` varchar(255) DEFAULT NULL,
    `last_name` varchar(255) NOT NULL,
    `birthday` date DEFAULT NULL,
    `gender` enum('male','female','undefined') NOT NULL DEFAULT 'undefined',
    `location` varchar(255) DEFAULT NULL,
    `about` text,
    `display_languageID` varchar(10) NOT NULL DEFAULT 'en-us',
    `fb_shared_secret` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

-- Table structure for table `users_emails`
CREATE TABLE IF NOT EXISTS `users_emails` (
    `userID` int(11) NOT NULL,
    `email` varchar(255) NOT NULL,
    PRIMARY KEY (`userID`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Table structure for table `users_groups`
CREATE TABLE IF NOT EXISTS `users_groups` (
    `userID` int(11) NOT NULL,
    `groupID` int(11) NOT NULL,
    PRIMARY KEY (`userID`,`groupID`),
    KEY `groupID` (`groupID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Table structure for table `users_languages`
CREATE TABLE IF NOT EXISTS `users_languages` (
    `languageID` varchar(255) NOT NULL,
    `userID` int(11) NOT NULL,
    PRIMARY KEY (`languageID`,`userID`),
    KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Table structure for table `users_votes`
CREATE TABLE IF NOT EXISTS `users_votes` (
    `userID` int(11) NOT NULL,
    `groupID` int(11) NOT NULL,
    `postID` int(11) NOT NULL,
    `vote` tinyint(4) NOT NULL,
    `downvote_reason` varchar(255) NOT NULL,
    PRIMARY KEY (`userID`,`groupID`,`postID`),
    KEY `groupID` (`groupID`),
    KEY `postID` (`postID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- Constraints for table `groups_posts`
ALTER TABLE `groups_posts`
    ADD CONSTRAINT `groups_posts_ibfk_2` FOREIGN KEY (`postID`) REFERENCES `posts` (`postID`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `groups_posts_ibfk_1` FOREIGN KEY (`groupID`) REFERENCES `groups` (`groupID`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Constraints for table `users_emails`
ALTER TABLE `users_emails`
    ADD CONSTRAINT `users_emails_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Constraints for table `users_groups`
ALTER TABLE `users_groups`
    ADD CONSTRAINT `users_groups_ibfk_2` FOREIGN KEY (`groupID`) REFERENCES `groups` (`groupID`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `users_groups_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Constraints for table `users_languages`
ALTER TABLE `users_languages`
    ADD CONSTRAINT `users_languages_ibfk_2` FOREIGN KEY (`languageID`) REFERENCES `languages` (`languageID`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `users_languages_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Constraints for table `users_votes`
ALTER TABLE `users_votes`
    ADD CONSTRAINT `users_votes_ibfk_3` FOREIGN KEY (`postID`) REFERENCES `posts` (`postID`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `users_votes_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `users_votes_ibfk_2` FOREIGN KEY (`groupID`) REFERENCES `groups` (`groupID`) ON DELETE CASCADE ON UPDATE CASCADE;
