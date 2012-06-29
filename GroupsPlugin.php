<?php

class GroupsPlugin extends Omeka_Plugin_Abstract
{
    protected $_hooks = array(
        'install',
        'uninstall',
        'define_acl',
        'define_routes',
        'public_theme_header',
        'commenting_append_to_form',
        'after_save_comment',
        'comment_browse_sql'
    );

    protected $_filters = array(
        'define_action_contexts',
        'guest_user_widgets'
    );

    public function setUp()
    {
        if(plugin_is_active('Commenting')) {
            $this->_hooks[] = 'after_save_comment';
            $this->_hooks[] = 'comment_browse_sql';
            $this->_hooks[] = 'commenting_append_to_form';
            $this->_filters[] = 'commenting_append_to_comment';
            $this->_filters[] = 'commenting_prepend_to_comments';
        }
        parent::setUp();
    }

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
        
        $sql = "
                CREATE TABLE IF NOT EXISTS `$db->GroupMembership` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `group_id` int(10) unsigned NOT NULL,
                  `user_id` int(10) unsigned NOT NULL,
                  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
                  `is_owner` tinyint(1) NOT NULL DEFAULT '0',
                  `is_pending` tinyint(1) NOT NULL DEFAULT '0',
                  `notify_member_joined` tinyint(1) NOT NULL DEFAULT '1',
                  `notify_member_left` tinyint(1) NOT NULL DEFAULT '1',
                  `notify_item_new` tinyint(1) NOT NULL DEFAULT '1',
                  `notify_item_deleted` int(11) NOT NULL DEFAULT '1',
                  PRIMARY KEY (`id`),
                  KEY `group_id` (`group_id`),
                  KEY `user_id` (`user_id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;                        
                ";
        $db->query($sql);
        
        $sql = "
                CREATE TABLE IF NOT EXISTS `$db->GroupConfirmation` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `group_id` int(10) unsigned NOT NULL,
                  `membership_id` int(10) unsigned NOT NULL,
                  `type` text COLLATE utf8_unicode_ci,
                  PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
                        ";
        $db->query($sql);
        
        
        $sql = "
        CREATE TABLE IF NOT EXISTS `$db->GroupInvitation` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `group_id` int(10) unsigned NOT NULL,
            `user_id` int(10) unsigned NOT NULL,
            `sender_id` int(10) unsigned NOT NULL,
            `message` text COLLATE utf8_unicode_ci,
            `created` timestamp,
            PRIMARY KEY (`id`)
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
        //@TODO: make this into the OMEKA namespace
        $commonsProps = array(
              array(
                    'name' => 'Commons',
                    'description' => 'Commons relations',
                    'namespace_prefix' => 'commons',
                    'namespace_uri' => 'http://ns.omeka-commons.org/',
                    'properties' => array(
                        array(
                            'local_part' => 'ownsComment',
                            'label' => 'Owns Comment',
                            'description' => 'The object Comment is associated with the subject Group'
                        ),                    )
                )
          );

        record_relations_install_properties($commonsProps);

    }

    public function hookUninstall()
    {
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `$db->Group`;";
        $db->query($sql);

        $sql = "DROP TABLE IF EXISTS `$db->GroupInvitation`;";
        $db->query($sql);        
        
        $sql = "DROP TABLE IF EXISTS `$db->GroupConfirmation`;";
        $db->query($sql);

        $sql = "DROP TABLE IF EXISTS `$db->GroupMembership`;";
        $db->query($sql);
                
        $blocksArray = array(
            'GroupsItemBlock',
            'GroupsAddItemBlock',
            'GroupsManageBlock',
            'GroupsMyGroupsBlock',
            'GroupsMembersBlock'
        );
        blocks_unregister_blocks($blocksArray);
        record_relations_delete_relations(array('subject_record_type'=>'Group'));
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

        $roles = array( 'researcher', 'contributor', 'admin', 'super');

        if($acl->hasRole('guest')) {
            $roles[] = 'guest';
        }


        $acl->allow(null, 'Groups_Group', array('browse', 'index', 'show'));
        $acl->allow($roles, 'Groups_Group', array('add', 'editSelf') );
        $acl->allow($roles, 'Groups_Group', 'edit', new Omeka_Acl_Assert_Ownership);

        $privileges = array('add-item',
                            'remove-item',
                            'items',
                            'join',
                            'join-others',
                            'remove-member',
                            'request',
                            'my-groups',
                            'administration',
                            'invitations',
                            'approve-request',
                            'quit'
                            );
        
        $acl->allow($roles, 'Groups_Group', $privileges, new GroupsAclAssertion);
    }

    public function hookDefineRoutes($router)
    {      

        $router->addRoute(
            'group-show',
            new Zend_Controller_Router_Route(
                'groups/:action/:id',
                array(
                        'module'        => 'groups',
                        'controller'    => 'group',
                        'action'		=> 'show',
                        'id'			=> ''
                )
            )
        );
                         
        $router->addRoute(
            'group-browse',
            new Zend_Controller_Router_Route(
                'groups/browse/:page',
                array(
                        'module'        => 'groups',
                        'controller'    => 'group',
                        'action'		=> 'browse',
                        'page' => '1'
                )
            )
        ); 
    }    
    
    public function hookAfterSaveComment($comment)
    {
        //build relations between comment and groups

        //unfortunate process of going through all the keys looking for 'groups_{id}'
        //Zend doesn't like form element names with []
        $groupIds = array();
        $public = false;
        foreach($_POST as $key=>$value) {
            $splitKey = explode('_', $key);
            if ( ($splitKey[0] == 'groups') && $value == 1) {
                if($splitKey[1] == 'public') {
                    $public = true;
                } else {
                    $groupIds[] = $splitKey[1];
                }
            }
        }

        $ownsComment = get_db()->getTable('RecordRelationsProperty')->findByVocabAndPropertyName('http://ns.omeka-commons.org/', 'ownsComment');
        $options = array(
            'subject_record_type' => 'Group',
//        	'subject_id' => this changes around in the loop below
            'object_record_type' => 'Comment',
            'object_id' => $comment->id,
            'public' => $public,
            'property_id' => $ownsComment->id

        );
        foreach($groupIds as $id) {
              $options['subject_id'] = $id;
              $rel = new RecordRelationsRelation;
              $rel->setProps($options);
              $rel->save();
        }
    }

    /**
     * Filter the comment select to only return comment if:
     * 1) the comment is not owned by any group
     * 2) if it is owned by a group, the current user is a member
     * 3) it has been marked as public, but also part of a group
     * This allows for filtering the conversation to what is part of a group
     */

    public function hookCommentBrowseSql($select, $params)
    {

        $user = current_user();
        if($user) {
            $userId = $user->id;
        } else {
            $userId = 0;
        }

        $filter = true;
        if(has_permission('Commenting_Comment', 'updateapproved') || has_permission('Commenting_Comment', 'updatespam')) {
            $filter = false;
        }

        if($user->id == 1) {
            $filter = false;
        }

        if($filter) {
            $db = get_db();
            $select->distinct();

            //first, just get a connection to the relation
            $db = get_db();
            $select->joinLeft(array('rr'=>$db->RecordRelationsRelation),
                            "rr.object_id = comments.id AND rr.object_record_type = 'Comment' ", array()
                            );
            //$select->where("rr.object_record_type = 'Comment'");

            $has_member = $db->getTable('RecordRelationsProperty')->findByVocabAndPropertyName(SIOC, 'has_member');
            $select->join(array('record_relations_relations'=>$db->RecordRelationsRelation),
                            ' (  rr.subject_id = record_relations_relations.subject_id ' .
                            'AND record_relations_relations.subject_record_type = "Group" ' .
                            'AND record_relations_relations.property_id = ' . $has_member->id .' ' .
                            'AND record_relations_relations.object_record_type = "User" ' .
                            'AND ( ( rr.public = 1 ) OR  ( record_relations_relations.object_id = ' . $userId  . ') ) ) ' .
                            'OR ( rr.id IS NULL)',
                            array()
                            );

        }
    }

    public function hookCommentingAppendToForm($form)
    {
        $user = current_user();
        if($user) {
            $groups = get_db()->getTable('Group')->findBy(array('user' => $user));
            $elements = array();
            foreach($groups as $group) {
                $name = 'groups_' . $group->id;
                $label = $group->title;
                $form->addElement('checkbox', $name, array('label'=>$label));
                $elements[] = $name;
            }
            if(!empty($elements)) {
                $form->addDisplayGroup($elements, 'groups', array('legend'=>"Add to your groups' discussions'"));
                $form->addElement('checkbox', 'groups_public', array(
                    'label' => 'Also make the comment public?',
                    'description' => "If unchecked, comment will only be visible to the selected groups. Otherwise, it will also be visible to anyone. A link to the group will appear next to it."
                    ));
            }
        }
    }

    public function filterCommentingAppendToComment($html, $comment)
    {
        $groups = groups_groups_for_comment($comment);
        $html .= "<div class='groups-comment-groups'><h3>Groups</h3><ul> ";
        foreach($groups as $group) {
            $html .= "<li class='groups-comment-group' id='groups-comment-group-{$group->id}'>" . $group->title .  "</li>";
        }
        $html .= "</ul></div>";
        return $html;
    }

    public function filterCommentingPrependToComments($html, $comments)
    {
        $user = current_user();
        if($user && ( count($comments) > 1 )) {
            $groups = array();
            foreach($comments as $comment) {
                $commentGroups = groups_groups_for_comment($comment);
                foreach($commentGroups as $g) {
                    if(!isset($groups[$g->id])) {
                        $groups[$g->id] = $g;
                    }
                }
            }
            $html .= "<div id='groups-comment-filter'>";
            $html .= "<p>Filter comments by groups</p>";
            $html .= "<ul id='groups-group-list'>";
            foreach($groups as $group) {
                $html .= "<li class='groups-group' id='groups-group-filter-{$group->id}'>" . $group->title . "</li>";
            }
            $html .= "</ul>";
            $html .= "</div>";
            return $html;
        }
    }

    public function filterGuestUserWidgets($widgets)
    {
        $user = current_user();
        $groups = get_db()->getTable('Group')->findBy(array('user'=>$user));
        $widget = array('label' => 'Groups');
        $widget['content'] = "<p><a href='" . uri('groups/add') . "'>Add a group</a></p>";
        $widget['content'] .= "<p><a href='" . uri('groups/my-groups') . "'>Manage your groups</a></p>";
        foreach($groups as $group) {
            $widget['content'] .= "<h3>";
            $widget['content'] .= groups_link_to_group($group);
            $widget['content'] .= "</h3>";
        }
        $widgets[] = $widget;
        return $widgets;
    }

    public function filterDefineActionContexts($contexts)
    {
        $contexts['show'] = array('rss2', 'atom');
        return $contexts;
    }
}