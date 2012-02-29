<?php

class GroupsPlugin extends Omeka_Plugin_Abstract
{
    protected $_hooks = array(
        'install',
        'uninstall',
        'define_acl',
        'define_routes',
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

            //check if item is already in the Group.
            //$itemId = Omeka_Context::getInstance()->getRequest()->getParam('id');
            $item = get_current_item();
            if(!$group->hasItem($item)) {
                $html .= "<li id='groups-id-{$group->id}' class='groups-item-add'>{$group->title}</li>";
            }
        }
        $html .= "</ul></div>";
        echo $html;
    }

    public function hookDefineAcl($acl)
    {
        require_once GROUPS_PLUGIN_DIR . '/GroupsAclAssertion.php';
        $acl->addResource('Groups_Group');

        $acl->allow(array('researcher', 'contributor'), 'Groups_Group', array('add', 'editSelf') );
        $acl->allow(array('researcher', 'contributor'), 'Groups_Group', 'edit', new Omeka_Acl_Assert_Ownership);

        $privileges = array('addItem',
                            'removeItem',
                            'items',
                            'join',
                            'joinOthers'
                            );
        $acl->allow(array('researcher', 'contributor'), 'Groups_Group', $privileges, new GroupsAclAssertion);

        $acl->allow(null, 'Groups_Group', array('browse', 'index', 'show'));

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
                    'action'        => 'browse'
                    )
            )
        );
    }

}