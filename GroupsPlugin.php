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
        'comment_browse_sql',

    );

    protected $_filters = array(
        'define_action_contexts',
        'commenting_append_to_comment',
        'commenting_prepend_to_comments'
    );
/*
    public function setUp()
    {
        parent::setUp();
        if(plugin_is_active('Commenting')) {
            $this->_hooks[] = 'after_save_comment';
        }
    }
*/

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

        $roles = array('researcher', 'contributor', 'admin', 'super');

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
                            'approve-request',
                            'quit'
                            );

        $acl->allow($roles, 'Groups_Group', $privileges, new GroupsAclAssertion);


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
            'property_id' => $ownsComment->id // need a property: commons:hasComment? or something from sioc?

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
        $select->distinct();
        $ownsComment = get_db()->getTable('RecordRelationsProperty')->findByVocabAndPropertyName('http://ns.omeka-commons.org/', 'ownsComment');
        //first, just get a connection to the relation and attach the subject_id (group_id)
        $db = get_db();
        $select->join(array('rr'=>$db->RecordRelationsRelation),
                        'rr.object_id = ct.id AND rr.object_record_type = "Comment" AND rr.subject_record_type = "Group"',
                        'rr.subject_id'
                        );

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
            $filter = true;
        }

        if($filter) {
            $has_member = get_db()->getTable('RecordRelationsProperty')->findByVocabAndPropertyName(SIOC, 'has_member');

            $select->join(array('rrr'=>$db->RecordRelationsRelation),
                            'rr.subject_id = rrr.subject_id AND rrr.subject_record_type = "Group" AND rrr.property_id = ' . $has_member->id .' AND rrr.object_record_type = "User"',
                            array()
                            );
            $select->where('rr.public = 1');
            $select->orWhere('rrr.object_id = ' . $userId);

        }
_log($select);
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
        $group = get_db()->getTable('Group')->find($comment->subject_id);
        $html .= "<p>" . $group->title .  "</p>";
        return $html;
    }

    public function filterCommentingPrependToComments($html, $comments)
    {
        $user = current_user();
        if($user && ( count($comments) != 0 )) {
            $groups = get_db()->getTable('Group')->findBy(array('user' => $user));
            $html = "<div id='groups-comment-filter'>";
            $html .= "<p>Filter comments by groups</p>";
            $html .= "<ul id='groups-group-list'>";
            foreach($groups as $group) {
                $html .= "<li class='groups-group'>" . $group->title . "</li>";
            }
            $html .= "</ul>";
            $html .= "</div>";
            return $html;
        }

    }

    public function filterDefineActionContexts($contexts)
    {
        $contexts['show'] = array('rss2', 'atom');
        return $contexts;
    }
}