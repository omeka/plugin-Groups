<?php

class GroupsPlugin extends Omeka_Plugin_Abstract
{
    protected $_hooks = array(
        'install',
        'uninstall',
        'define_acl',
        'public_append_to_items_show',
        'public_theme_header'
    );


    public function hookInstall()
    {
        $db = get_db();
        $sql = "
                CREATE TABLE IF NOT EXISTS `$db->Group` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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

    public function hookPublicThemeHeader()
    {
        queue_js('groups');
    }

    public function hookPublicAppendToItemsShow()
    {
        $html = "<div id='groups-item-add'><p>Add to group(s)</p><ul>";
        $groups = groups_groups_for_user();
        foreach($groups as $group) {
            $html .= "<li id='groups-id-{$group->id}' class='groups-item-add'>{$group->title}</li>";
        }

        $html .= "</ul></div>";
        echo $html;
    }

    public function hookDefineAcl($acl)
    {
        $priviledges = array('add', 'browse', 'editSelf', 'index', 'show');
        if (version_compare(OMEKA_VERSION, '2.0-dev', '>=')) {
            $acl->addResource('Groups_Group');
            $acl->allow(array('researcher', 'contributor', 'admin', 'super'), 'Groups_Group', $priviledges );
        } else {
            $acl->loadResourceList(
                array('Groups_Group' => $priviledges)
            );
        }
    }
}