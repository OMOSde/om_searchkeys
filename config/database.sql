-- ********************************************************
-- *                                                      *
-- * IMPORTANT NOTE                                       *
-- *                                                      *
-- * Do not import this file manually but use the Contao  *
-- * install tool to create and maintain database tables! *
-- *                                                      *
-- ********************************************************

-- 
-- Table `tl_om_searchkeys`
--

CREATE TABLE `tl_om_searchkeys` (
    `id` int(11) NOT NULL auto_increment,
    `tstamp` int(10) unsigned NOT NULL default '0',
    `searchkey` varchar(255) NOT NULL default '',
    `member` int(11) NOT NULL default '0',
    `results` int(11) NOT NULL default '0',
    `relevance` int(11) NOT NULL default '0',
    `rootPage` int(11) NOT NULL default '0',
    `referer` varchar(255) NOT NULL default '',
    `user` int(10) unsigned NOT NULL default '0',
    PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table `tl_om_searchkeys_counts`
--
CREATE TABLE `tl_om_searchkeys_counts` (
    `id` int(11) NOT NULL auto_increment,
    `tstamp` int(10) unsigned NOT NULL default '0',
    `keywords` int(10) unsigned NOT NULL default '0',
    `counter` int(10) unsigned NOT NULL default '0',
    PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;