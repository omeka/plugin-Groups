<?php

class GroupsPlugin extends Omeka_Plugin_Abstract
{
    protected $_hooks = array(
        'install',
        'uninstall',
        'define_acl',
        'define_routes',
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
        $db->query($sql);

        $blocks = unserialize(get_option('blocks'));
        $blocks[] = 'GroupsItemBlock';
        $blocks[] = 'GroupsAddItemBlock';
        set_option('blocks', serialize($blocks));
    }

    public function hookUninstall()
    {
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `$db->Group`;";
        $db->query($sql);
    }

    public function hookPublicThemeHeader()
    {
        queue_js('groups');
        queue_css('groups');
    }


    public function hookDefineAcl($acl)
    {
        require_once GROUPS_PLUGIN_DIR . '/GroupsAclAssertion.php';
        $acl->addResource('Groups_Group');
        $acl->allow(null, 'Groups_Group', array('browse', 'index', 'show'));
        $acl->allow(array('researcher', 'contributor'), 'Groups_Group', array('add', 'editSelf') );
        $acl->allow(array('researcher', 'contributor'), 'Groups_Group', 'edit', new Omeka_Acl_Assert_Ownership);

        $privileges = array('add-item',
                            'remove-item',
                            'items',
                            'join',
                            'join-others',
                            'remove-member',
                            'quit'
                            );
        $acl->allow(array('researcher', 'contributor'), 'Groups_Group', $privileges, new GroupsAclAssertion);
    }

    public function hookDefineRoutes($router)
    {
        $router->addRoute(
            'groups-group-route',
            new Zend_Controller_Router_Route(
                'groups/:action/:id',
                array(
                    'module'        => 'groups',
                    'controller'    => 'group',
                    'action'        => 'browse',
                    'id'			=> ''
                    )
            )
        );
    }

}