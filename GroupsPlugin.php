<?php


class GroupsPlugin extends Omeka_Plugin_Abstract
{
    protected $_hooks = array(
        'install',
        'uninstall'
    );


    public function hookInstall()
    {
        $db = get_db();
        $sql = "
                CREATE TABLE IF NOT EXISTS `$db->Group` (
                  `id` int(10) unsigned NOT NULL,
                  `title` text COLLATE utf8_unicode_ci NOT NULL,
                  `description` text COLLATE utf8_unicode_ci,
                  `visibility` tinytext COLLATE utf8_unicode_ci NOT NULL,
                  `owner_id` int(10) unsigned NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `owner_id` (`owner_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
                ";

        $db->exec($sql);
    }

    public function hookUninstall()
    {
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `$db->Group`;";
        $db->exec($sql);
    }
}