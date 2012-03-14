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

    protected $_filters = array(
        'define_action_contexts'
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
                  KEY `owner_id` (`owner_id`),
                  FULLTEXT KEY (`title`,`description`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
                ";
        $db->query($sql);

        $blocksArray = array(
            'GroupsItemBlock',
            'GroupsAddItemBlock',
            'GroupsManageBlock',
            'GroupsMyGroupsBlock',
            'GroupsMembersBlock'
        );
        blocks_register_blocks($blocksArray);

        $omekaMemberProps = array(
            array(
                'name' => 'Omeka',
                'namespace_prefix' => 'omeka',
                'namespace_uri' => OMEKA,
                'properties' => array(
                    array(
                        'local_part' => 'has_pending_member',
                        'label' => 'Membership pending',
                        'description' => 'A user has requested membership to a group'
                    ),
                    array(
                        'local_part' => 'has_invited_member',
                        'label' => 'Has invited member',
                        'description' => 'The group owner has invited someone to join'
                    )
                )
            )
        );

        record_relations_install_properties($omekaMemberProps);
    }

    public function hookUninstall()
    {
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `$db->Group`;";
        $db->query($sql);

        $blocksArray = array(
            'GroupsItemBlock',
            'GroupsAddItemBlock',
            'GroupsManageBlock',
            'GroupsMyGroupsBlock',
            'GroupsMembersBlock'
        );
        blocks_unregister_blocks($blocksArray);
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
        $acl->allow(array('researcher', 'contributor', 'admin', 'super'), 'Groups_Group', array('add', 'editSelf') );
        $acl->allow(array('researcher', 'contributor', 'admin', 'super'), 'Groups_Group', 'edit', new Omeka_Acl_Assert_Ownership);

        $privileges = array('add-item',
                            'remove-item',
                            'items',
                            'join',
                            'join-others',
                            'remove-member',
                            'approve-request',
                            'quit'
                            );

        $acl->allow(array('researcher', 'contributor', 'admin', 'super'), 'Groups_Group', $privileges, new GroupsAclAssertion);

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

    public function filterDefineActionContexts($contexts)
    {
        $contexts['show'] = array('rss2', 'atom');
        return $contexts;
    }
}