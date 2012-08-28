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
        'comment_browse_sql'
        
    );

    protected $_filters = array(
        'define_action_contexts',
        'guest_user_widgets',
        'blocks_notifications'
    );

    public function setUp()
    {
        if(plugin_is_active('Commenting')) {
            $this->_hooks[] = 'before_save_form_comment';
            $this->_hooks[] = 'comment_browse_sql';
            $this->_hooks[] = 'commenting_append_to_form';
            $this->_filters[] = 'commenting_append_to_comment';
            //$this->_filters[] = 'commenting_prepend_to_comments';
        }
        
        if(!class_exists('Ownable')) {
            include(GROUPS_PLUGIN_DIR . '/Ownable.php');
            include(GROUPS_PLUGIN_DIR . '/Ownership.php');
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
                  FULLTEXT KEY `title` (`title`),
                  FULLTEXT KEY `description` (`description`),
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
            
            CREATE TABLE IF NOT EXISTS `$db->GroupBlock` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `blocked_id` int(11) NOT NULL,
              `blocker_id` int(11) NOT NULL,
              `blocked_type` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
              `blocker_type` tinytext NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;
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
        queue_js('tiny_mce', 'javascripts/tiny_mce');
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
                            'change-status',
                            'make-owner',
                            'quit',
                            'manage',
                            'edit',
                            'block',
                            'unblock',
                            'remove-comment'
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
    
    public function hookBeforeSaveFormComment()
    {
        //build relations between comment and groups
        //first save the actual comment 
        $form = new Commenting_CommentForm();
        $data = $_POST;
        $valid = $form->isValid($_POST);
        $gbody = $form->getElement('groups_commenting_body')->getValue();
        $gbodyEmpty = false;
        if(trim(strip_tags($gbody)) == '' ) {
            $gbodyEmpty = true;
        }
        $body = $form->getElement('commenting_body')->getValue();
        $bodyEmpty = false;
        if(trim(strip_tags($body)) == '' ) {
            $bodyEmpty = true;
        }        
        if($bodyEmpty && $gbodyEmpty) {
            return;
        }        
        
        $data['body'] = $gbody;
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $data['approved'] = has_permission('Commenting_Comment', 'noappcomment');
        $data['flagged'] = 0;        
        
        if($form->isValid($data)) {
            $comment = new Comment();
            //commenting uses saveForm, so don't use that here to avoid triggering this hook again            
            $comment->setArray($data);
            $comment->save();
            $groupIds = array();
            foreach($_POST as $key=>$value) {
                $splitKey = explode('_', $key);
                if ( ($splitKey[0] == 'groups') && $value == 1) {
                    $groupIds[] = $splitKey[1];
                }
            }
            
            if(!empty($groupIds)) {
                $groupTable = get_db()->getTable('Group');
                $ownsComment = get_db()->getTable('RecordRelationsProperty')->findByVocabAndPropertyName('http://ns.omeka-commons.org/', 'ownsComment');
                $options = array(
                    'subject_record_type' => 'Group',
                    //'subject_id' => this changes around in the loop below
                    'object_record_type' => 'Comment',
                    'object_id' => $comment->id,
                    'public' => true,
                    'property_id' => $ownsComment->id
        
                );
                
                foreach($groupIds as $id) {
                    $options['subject_id'] = $id;
                    $rel = new RecordRelationsRelation;
                    $rel->setProps($options);
                    $rel->save();
                    
                    //make sure that, if record for comment is an Item, it is also in the group.
                    if($comment->record_type = 'Item') {                        
                        $group = $groupTable->find($id);
                        //addItem does it's own checking for duplicates
                        $group->addItem($comment->record_id);
                    }                    
                }
            }            
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

        $filter = true;
        //skip this hook under certain conditions from groups, like when getting all the comments for a single group
        if(isset($params['groups_skip_hook'])) {
            $filter = false;
        }
        
        $user = current_user();
        if($user) {
            $userId = $user->id;
        } else {
            $userId = 0;
        }
        
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
            
            $rrTable = $db->getTable('RecordRelationsRelation');
            //there are a lot of crazy joins by the end of some queries, so
            //the 'a' is there to prevent assigning the same alias twice
            //gotta be a better way?
            $rrAlias = $rrTable->getTableAlias() . 'a'; 
            $select->joinLeft(array('rr'=>$db->RecordRelationsRelation),
                            "rr.object_id = comments.id AND rr.object_record_type = 'Comment' ", array()
                            );

            if(isset($params['item_id'])) {
                
            }
            $has_member = $db->getTable('RecordRelationsProperty')->findByVocabAndPropertyName(SIOC, 'has_member');
            $select->join(array($rrAlias=>$db->RecordRelationsRelation),
                            ' (  rr.subject_id = ' . $rrAlias . '.subject_id ' .
                            'AND ' . $rrAlias . '.subject_record_type = "Group" ' .
                            'AND ' . $rrAlias . '.property_id = ' . $has_member->id .' ' .
                            'AND ' . $rrAlias . '.object_record_type = "User" ' .
                            'AND ( ( rr.public = 1 ) OR  ( ' . $rrAlias . '.object_id = ' . $userId  . ') ) ) ' .
                            'OR ( rr.id IS NULL)',
                            array()
                            );

        }
    }

    /**
     * Create a duplicate commenting form, just for group-specific comments
     * @param unknown_type $form
     */
    
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
                $form->addElement('textarea', 'groups_commenting_body',
                    array('required'=>false,
                          'rows'=>5,                          
                          'filters'=> array(
                              array('StripTags', array('allowTags' => array('p', 'em', 'strong', 'a'))),
                            ),
                        )
                    );
                $elements[] = 'groups_commenting_body';
                $form->addDisplayGroup($elements, 'groups_commenting', 
                       array(
                       'id'=>'groups_comment_form',
                       'description'=>"<label>Group-specific comment</label><p>Comments made here will only appear in the selected groups. <span id='groups-commenting-copy'>Copy comment</span></p>"
                        
                      )
                );
                $displayGroup = $form->getDisplayGroup('groups_commenting');
                $decOptions = array('escape'=>false, 'placement'=>'prepend', 'tag'=>'div', 'class'=>'groups-commenting', 'id'=>'groups-commenting');
                $dec = $displayGroup->addDecorator('description', $decOptions);
                $dec = $displayGroup->getDecorator('description');
            }
        }
    }
    
    public function filterBlocksNotifications($notifications)
    {
        $notification = array('title'=>'Groups Notifications');
        $invitations = groups_invitations_for_user();
        $html = '';
        if(!empty($invitations)) {
            $html .= "<p><a href='" . public_uri('groups/my-groups') . "'>Manage Invitations</a></p>";
            $html .= "<ul>";
            foreach($invitations as $invitation) {
                $html .= "<li>{$invitation->Sender->name} has invited you to join 
                    <a href='" . record_uri($invitation->Group, 'show') . "'>{$invitation->Group->title}</a>";
            }
            $html .= "</ul>";            
        }
        $confirmations = groups_confirmations_for_user();
        if(!empty($confirmations)) {
            $html .= "<ul>";
            foreach($confirmations as $confirmation) {
                if($confirmation->type != 'make_admin') {
                    $group = $confirmation->Group;
                    $type = substr($confirmation->type, 3);
                    $html .= "<li>You have been asked to be a $type of <a href='" . record_uri($group, 'manage') . "'>{$group->title}</a></li>";                    
                }
            }
            
            $html .= "</ul>";
            
        }
        
        $membershipsTable = get_db()->getTable('GroupMemberships');
        $groups = groups_groups_for_user(current_user(), true);
        $html .= "<ul>";
        foreach($groups as $group) {
            $requests = $group->memberRequests();
            if(!empty($requests)) {
                $html .= "<li>" . count($requests) . " pending membership request(s) to <a href='" . record_uri($group, 'manage') . "'>{$group->title}</a></li>";
            }
        }
        $html .= "</ul>";
        
        $notification['html'] = $html;
        $notifications[] = $notification;
        return $notifications;
    }

    public function filterCommentingAppendToComment($html, $comment)
    {
        $groups = groups_groups_for_comment($comment);
        $request = Zend_Controller_Front::getInstance()->getRequest();
                
        $class = Inflector::classify($request->getControllerName());
        $id = $request->getParam('id');
        if(! ($class == $comment->record_type && $id = $comment->record_id)) {
            $html .= "<div class='groups-original-item'>";
            $html .= "<a href='" . WEB_ROOT . "{$comment->path}'>Source</a>";
            $html .= "</div>";            
        }

        $group = groups_get_current_group();
        if($group && has_permission($group, 'remove-comment')) {
            $html .= "<div id='groups-comment-administration'>";
            $html .= "<p><a href='" . WEB_ROOT . "/groups/group/remove-comment/id/{$group->id}/comment/{$comment->id}' class='groups-remove-comment' id='groups-remove-comment-{$comment->id}'>Remove from this group</a></p>";            
            $html .= "</div>";            
        }

        return $html;
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