<?php


class Groups_GroupController extends Omeka_Controller_AbstractActionController
{

    public function init()
    {
        if(is_admin_theme()) {
            $this->_browseRecordsPerPage = get_option('per_page_public');
        } else {
            $this->_browseRecordsPerPage = get_option('per_page_admin');
        }

        $this->_helper->db->setDefaultModelName('Group');
//@TODO: check if I really need to muck about with the contexts
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('join', 'json')
                    ->initContext();
    }

    public function indexAction()
    {
        $this->_helper->redirector->goto('groups/browse');
    }

    public function browseAction()
    {
        $this->view->addHelperPath(GROUPS_PLUGIN_DIR . '/helpers', 'Group_View_Helper_');
        if(get_option('groups_taggable')) {
            $tags = get_db()->getTable('Tag')->findBy(array('type'=>'Group'));
            //$view->addHelperPath(USER_PROFILES_DIR . '/helpers', 'UserProfiles_View_Helper_');
            $this->view->tags = $tags;
        }
        $this->view->user = current_user();
        parent::browseAction();
    }

    public function addAction()
    {
        require_once GROUPS_PLUGIN_DIR . '/forms/group.php';
        $form = new GroupForm();
        $this->view->form = $form;

        // Check if the form was submitted.
        if ($this->getRequest()->isPost()) {
            $group = new Group();
            $group->setPostData($_POST);
            $currentUser = current_user();
            $group->owner_id = $currentUser->id;

            // Save the record. Passing false prevents thrown exceptions.
            if ($group->save(false)) {
                $group->addMember($currentUser, false, 'is_owner');
                $successMessage = $this->_getEditSuccessMessage($group);
                if ($successMessage) {
                    $this->_helper->flashMessenger($successMessage, 'success');
                }
                $this->_redirectAfterEdit($group);
                // Flash an error if the record does not validate.
            } else {
                $this->_helper->flashMessenger($group->getErrors());
            }
        }
    }

    public function editAction()
    {
        require_once GROUPS_PLUGIN_DIR . '/forms/group.php';
        $form = new GroupForm();
        $group = $this->_helper->db->findById();
        $defaults = $group->toArray();
        if(get_option('groups_taggable')) {
            $defaults['tags'] = groups_tags_string_for_group($group, false);
        }
        $form->setDefaults($defaults);
        $this->view->form = $form;
        $this->view->group = $group;
        if($this->getRequest()->isPost()) {
            $group->setPostData($_POST);
            $group->save();
            $this->_helper->redirector->gotoUrl('/groups/show/' . $group->id );
        }
    }

    public function showAction()
    {
        $record = $this->_helper->db->findById();

        $this->view->group = $record;
        parent::showAction();
        //stuff the items in so they are available to output formats
        if(is_allowed($this->view->group, 'items')) {
            $items = $this->view->group->getItems();
            $this->view->assign(array('items'=>$items));
        }
    }

    public function manageAction()
    {
        $group = $this->_helper->db->findById();

        if(!empty($_POST)) {
            if(!empty($_POST['emails'])) {
                $this->handleInvitations();
            }

            if(empty($_POST['groups'])) {
                //all the notifications have been unchecked
                $membership = groups_get_membership($group);
                $membership->notify_member_joined = 0;
                $membership->notify_item_new = 0;
                $membership->notify_member_left = 0;
                $membership->notify_item_deleted = 0;
                $membership->save();
            }
            $this->_handleMembershipStatus();
            $this->_handleAdministration();
            $this->_handleUnblocks();
            $this->_helper->redirector->gotoUrl('groups/show/' . $group->id);
        }
        $this->view->blocked_users = $this->_helper->db->getTable('GroupBlock')->findBy(array('blocker_id'=>$group->id, 'blocker_type'=>'Group'));
        $this->view->group = $group;
        $this->view->user_membership = $group->getMembership(array('user_id'=>current_user()->id));
    }

    public function joinAction()
    {
        $user =  current_user();
        $group = $this->_helper->db->findById();
        $responseArray = array('status'=>'ok');
        $response = json_encode($responseArray);
        $to = $group->getMembersForNotification('notify_member_joined');
        try {
            $group->sendNewMemberEmail($user, $to);
        } catch (Exception $e) {
            $responseArray = array('status'=>'error');
        }
        $group->addMember($user);
        $this->_helper->json($response);
    }

    public function quitAction()
    {
        $user =  current_user();
        $group = $this->_helper->db->findById();
        $group->removeMember($user);
        $responseArray = array('status'=>'ok');
        $response = json_encode($responseArray);

        $to = $group->getMembersForNotification('notify_member_left');
        try {
            $group->sendMemberLeftEmail($user, $to);
        } catch (Exception $e) {
            $responseArray = array('status'=>'error');
        }

        $this->_helper->json($response);
    }

    public function requestAction()
    {
        $user =  current_user();
        $group = $this->_helper->db->findById();
        try {
            $group->addMember($user, 1);
            $responseArray = array('status'=>'ok');
        } catch (Exception $e) {
            $responseArray = array('status'=>'error');
        }
        $to = $group->getMembers(array('is_admin'=>true));
        try {
            $group->sendPendingMemberEmail($user, $to);
        } catch (Exception $e) {
            $responseArray = array('status'=>'error');
        }
        $response = json_encode($responseArray);
        $this->_helper->json($response);
    }

    public function joinOthersAction($user)
    {
        $group= $this->_helper->db->findById();
        $group->addMember($user);
        $response = array('status'=>'ok');
        $this->_helper->json($response);
    }

    public function approveRequestAction()
    {
        $userId = $_POST['userId'];
        $groupId = $_POST['groupId'];
        $user = $this->_helper->db->getTable('User')->find($userId);
        $group = $this->_helper->db->getTable()->find($groupId);
        $group->approveMember($user);
        $responseArray = array('status'=>'ok');
        $response = json_encode($responseArray);

        $group->sendMemberApprovedEmail($user);
        $this->_helper->json($response);
    }

    public function removeMemberAction()
    {
        $userId = $this->getRequest()->getParam('user');
        $user = get_db()->getTable('User')->find($userId);
        $group= $this->_helper->db->findById();
        $group->removeMember($user);
        $response = array('status'=>'ok');
        $to = $group->getMembersForNotification('notify_member_left');
        $group->sendMemberLeftEmail($user, $to);
        $this->_helper->json($response);
    }

    public function removeCommentAction()
    {
        $commentId = $this->getRequest()->getParam('comment');
        $group = $this->_helper->db->findById();
        $group->removeComment($commentId);
        $this->_helper->redirector->gotoUrl('groups/show/' . $group->id);
    }

    public function removeItemAction()
    {
        $responseJson = array();
        $itemId = $this->getRequest()->getParam('itemId');
        $groupId = $this->getRequest()->getParam('groupId');
        $group = $this->_helper->db->getTable()->find($groupId);
        $item = $this->_helper->db->getTable('Item')->find($itemId);
        $responseJson['itemId'] = $itemId;
        $responseJson['groupId'] = $groupId;
        $group->removeItem($item);
        $this->_helper->json($responseJson);
    }

    public function myGroupsAction()
    {
        $user = current_user();
        $params = array(
                'user' => $user
        );

        if(!empty($_POST['blocks'])) {
            $groupInvitationTable = $this->_helper->db->getTable('GroupInvitation');
            foreach($_POST['blocks'] as $id=>$values) {

                $invitation = $groupInvitationTable->find($id);
                foreach($values as $value) {
                    switch($value) {
                        case 'block-user':
                            $block = new GroupBlock();
                            $block->blocked_id = $invitation->sender_id;
                            $block->blocker_id = $user->id;
                            $block->blocked_type = 'User';
                            $block->blocker_type = 'User';
                            $block->save();
                            break;

                        case 'block-group':
                            $block = new GroupBlock();
                            $block->blocked_id = $invitation->group_id;
                            $block->blocker_id = $user->id;
                            $block->blocked_type = 'Group';
                            $block->blocker_type = 'User';
                            $block->save();
                            break;
                    }
                }
            }
        }

        if(!empty($_POST['invitations'])) {
            $groupInvitationTable = $this->_helper->db->getTable('GroupInvitation');
            foreach($_POST['invitations'] as $id=>$value) {

                $invitation = $groupInvitationTable->find($id);
                switch($value) {
                    case 'join':
                        $invitation->Group->addMember($user);
                        $to = $invitation->Group->getMembersForNotification('notify_member_joined');
                        $invitation->Group->sendNewMemberEmail($user, $to);
                        break;

                    case 'decline':
                        $to = $this->getTable('User')->find($invitation->sender_id);
                        $invitation->Group->sendInvitationDeclinedEmail($user, $to);
                        break;

                    case 'request':
                        $invitation->Group->addMember($user, 1);
                        break;
                }
                $invitation->delete();
            }
        }

        $this->_handleMembershipStatus();

        $groups = $this->_helper->db->getTable('Group')->findBy($params);
        $invitations = $this->_helper->db->getTable('GroupInvitation')->findBy(array('user_id'=>$user->id));
        $this->view->groups = $groups;
        $this->view->invitations = $invitations;
    }

    public function administrationAction()
    {
        $user = current_user();
        $this->_handleUnblocks();
        $this->_handleAdministration();
        $groups = $this->_helper->db->getTable('GroupMembership')->findGroupsBy(array('user_id'=>$user->id, 'is_pending'=>0, 'admin_or_owner'=>true));
        $this->view->groups = $groups;
    }

    public function invitationsAction()
    {
        $sender = current_user();
        $groups = $this->_helper->db->getTable('GroupMembership')->findGroupsBy(array('user_id'=>$sender->id, 'is_pending'=>0));
        foreach($groups as $key=>$group) {
            if(!is_allowed($group, 'invitations')) {
                unset($groups[$key]);
            }
        }
        $this->_handleInvitations();
        $this->view->groups = $groups;
    }

    public function addItemAction()
    {
        $responseJson = array();
        $itemId = $_POST['itemId'];
        $groupId = $_POST['groupId'];
        $group = $this->_helper->db->getTable()->find($groupId);
        $item = $this->_helper->db->getTable('Item')->find($itemId);
        $responseJson['itemId'] = $itemId;
        $responseJson['groupId'] = $groupId;
        $group->addItem($item);

        $to = $group->getMembersForNotification('notify_item_new');
        $group->sendNewItemEmail($item, $to, current_user());
        $this->_helper->json($responseJson);
    }

    private function _handleAdministration()
    {
        $confirmationTable = $this->_helper->db->getTable('GroupConfirmation');
        $user = current_user();
        $blockTable = $this->_helper->db->getTable('GroupBlock');
        $groupMembershipTable = $this->_helper->db->getTable('GroupMembership');
        if(!empty($_POST)) {
            if(isset($_POST['block'])) {
                foreach($_POST['block'] as $groupId=>$memberships) {
                    foreach($memberships as $membershipId=>$action) {
                        $membership = $groupMembershipTable->find($membershipId);
                        switch($action) {
                            case 'block':
                                $block = new GroupBlock;
                                $block->blocked_id = $membership->User->id;
                                $block->blocked_type = 'User';
                                $block->blocker_id = $groupId;
                                $block->blocker_type = 'Group';
                                $block->save();
                                break;

                            case 'unblock':
                                $blocks = $blockTable->findBy(array('blocked_id'=>$user_id,
                                                          'blocked_type'=>'User',
                                                          'blocker_id'=>$groupId,
                                                          'blocker_type'=>'Group'
                                                          )
                                                    );
                                $block = $blocks[0];
                                $block->delete();
                                break;
                        }
                    }

                }
            }
            if(isset($_POST['membership'])) {
                foreach($_POST['membership'] as $groupId=>$memberships) {
                    $group = $this->_helper->db->findById($groupId);
                    foreach($memberships as $membershipId=>$action) {
                        $membership = $groupMembershipTable->find($membershipId);
                        switch($action) {
                            case 'remove':
                                $group->removeMember($membership);
                                $to = $group->getMembersForNotification('notify_member_left');
                                $group->sendMemberLeftEmail($membership->User, $to);
                                break;

                            case 'deny':
                                $group->denyMembership($membership);
                                $to = $membership->User;
                                $group->sendMemberDeniedEmail($to);
                                break;

                            case 'approve':
                                $group->approveMember($membership);
                                $to = $group->getMembersForNotification('notify_member_joined');
                                $group->sendNewMemberEmail($membership->User, $to);
                                break;
                        }
                    }
                }
            }

            if(isset($_POST['status'])) {
                foreach($_POST['status'] as $groupId=>$memberships) {
                    foreach($memberships as $membershipId=>$action) {
                        $membership = $this->_helper->db->getTable('GroupMembership')->find($membershipId);
                        if($membership) {

                            switch($action) {
                                case 'member':
                                    $membership->is_admin = 0;
                                    $membership->is_owner = 0;
                                    break;

                                case 'admin':
                                    if(!$membership->is_admin) {
                                        $confirmation = $confirmationTable->findOrNew(array('group_id'=>$groupId, 'membership_id'=>$membershipId));
                                        if($confirmation->exists()) {
                                            switch($confirmation->type) {
                                                case 'is_admin':
                                                    $this->_helper->flashMessenger("You have already asked {$membership->User->name} to become an administrator of {$membership->Group->title}");
                                                    break;
                                                    //make_admin is when the user requests being made an admin
                                                case 'make_admin':
                                                    $membership->is_admin = 1;
                                                    $confirmation->delete();
                                                    break;
                                            }


                                        } else {
                                            $confirmation->group_id = $groupId;
                                            $confirmation->membership_id = $membershipId;
                                            $confirmation->type = 'is_admin';
                                            $this->_helper->flashMessenger($membership->User->name . " must accept becoming an administrator for the changes to take effect.");
                                            $confirmation->save();
                                        }
                                    }
                                    break;

                                case 'owner':
                                    if(!$membership->is_owner) {
                                        $confirmation = $confirmationTable->findOrNew(array('group_id'=>$groupId, 'membership_id'=>$membershipId, 'type'=>'is_owner'));
                                        $confirmation->group_id = $groupId;
                                        $confirmation->membership_id = $membershipId;
                                        $confirmation->type = 'is_owner';
                                        if($confirmation->exists()) {
                                            $this->flash("You have already asked {$membership->User->name} to become the owner of {$membership->Group->title}");
                                        }
                                        $this->flash($membership->User->name . " must accept becoming the owner for the changes to take effect.");
                                        $confirmation->save();
                                    }
                                    break;
                            }
                            $membership->save();
                            if($confirmation && !$confirmation->exists()) {
                                $membership->Group->sendChangeStatusEmail($membership->User, $action);
                            }

                        }
                    }
                }
            }
        }
    }

    private function _handleInvitations()
    {
        $sender = current_user();
        if(isset($_POST['emails'])) {
            $emails = explode(',', $_POST['emails']);
            $message = $_POST['message'];
            $userTable = $this->_helper->db->getTable('User');
            $invitationTable = $this->_helper->db->getTable('GroupInvitation');
            $blocksTable = $this->_helper->db->getTable('GroupBlock');
            $nonUserEmails = array();
            $alreadyMemberEmails = array();
            foreach($_POST['invite_groups'] as $groupId) {
                $group = $this->_helper->db->getTable()->find($groupId);
                $groupEmails = array();
                foreach($emails as $index=>$email) {
                    $email = trim($email);
                    $user = $userTable->findByEmail(trim($email));
                    if(!$user) {
                        //$email might actually be a username
                        $select = $userTable->getSelect();
                        $select->where("username = ?", $email);
                        $select->where("active = 1");
                        $select->limit(1);
                        $user = $userTable->fetchObject($select);
                    }
                    if($user) {
                        if($group->hasMember($user)) {
                            $this->_helper->flashMessenger("{$user->name} ({$user->username}) is already a member of {$group->title}.", 'error');
                        } else {
                            if($invitationTable->findInvitationToGroup($groupId, $user->id, $sender->id)) {
                                $this->_helper->flashMessenger("You have already invited {$user->name} ({$user->username}) to {$group->title}", 'error');
                            } else {
                                $userBlocks = $blocksTable->count(array('blocker_id'=>$user->id,
                                                                        'blocked_id'=>$sender->id,
                                                                        'blocked_type'=>'User',
                                                                        'blocker_type'=>'User'));
                                $groupBlocks = $blocksTable->count(array('blocker_id'=>$user->id,
                                                                         'blocked_id'=>$group->id,
                                                                         'blocked_type'=>'Group',
                                                                         'blocker_type'=>'User'));
                                if($userBlocks == 0 && $groupBlocks == 0) {
                                    $invitation = new GroupInvitation;
                                    $invitation->user_id = $user->id;
                                    $invitation->sender_id = $sender->id;
                                    $invitation->message = $message;
                                    $invitation->group_id = $groupId;
                                    $invitation->save();
                                    $groupEmails[] = $email;
                                }
                                if($userBlocks) {
                                    $this->_helper->flashMessenger("{$user->name} has blocked invitations from you", 'error');
                                }
                                if($groupBlocks) {
                                    $this->_helper->flashMessenger("{$user->name} has blocked invitations from {$group->title}", 'error');
                                }
                            }
                        }

                    } else {
                        $nonUserEmails[] = $email;
                        $this->_helper->flashMessenger($email . " is not a member of the Omeka Commons.", 'error');
                        unset($emails[$index]);
                    }
                }
                $groupEmailsCount = count($groupEmails);
                if($groupEmailsCount==0) {
                    $this->_helper->flashMessenger('No invitations sent to ' . $group->title, 'success');
                } else {
                    try {
                        $group->sendInvitationEmail($groupEmails, $message, $sender);
                        $this->_helper->flashMessenger($groupEmailsCount . " invitation(s) to {$group->title} sent", 'success');
                    } catch(Exception $e) {
                        _log($e);
                        $this->_helper->flashMessenger("Couldn't send email", 'error');
                    }
                }
            }
        }
    }

    private function _handleMembershipStatus()
    {
        if(!empty($_POST['groups'])) {
            foreach($_POST['groups'] as $id=>$options) {
                $group = $this->_helper->db->findById($id);
                $membership = $group->getMembership(array('user_id'=>current_user()->id));
                $membership->unsetOptions();
                foreach($options as $option=>$value) {
                    switch($option) {
                        case "status":
                            switch($value) {
                                case 'quit':
                                    $membership->delete();
                                    break;
                                case 'make_admin':
                                    $confirmation = new GroupConfirmation;
                                    $confirmation->group_id = $group->id;
                                    $confirmation->membership_id = $membership->id;
                                    $confirmation->type = 'make_admin';
                                    $confirmation->save();
                                    break;
                            }

                            break;

                        case "submitted":
                            //do nothing, just here to make the $_POST arrive when nothing is checked
                            break;


                        case "admin":
                        case "owner":
                            //allow admins to quit being an admin
                            if($value == 'decline' && $option == 'admin') {
                                $membership->is_admin = 0;
                            }

                            if($value != 'decline') {
                                //make the previous owner no longer the owner
                                if($value == 'is_owner') {
                                    $owner = $group->getOwnerMembership();
                                    $owner->is_owner = 0;
                                    $owner->is_admin = 1;
                                    $owner->save();
                                }
                                $membership->$value = 1;
                            }

                            if($confirmation = $membership->getConfirmation('is_' . $option)) {
                                $confirmation->delete();
                            }


                            break;

                        default:
                            $membership->$option = 1;
                            break;
                    }
                }
                if($membership->exists()) {
                    $membership->save();
                }

            }
        }
    }

    private function _handleUnblocks()
    {

        if(!empty($_POST['unblocks'])) {
            $blocksTable = $this->_helper->db->getTable('GroupBlock');
            foreach($_POST['unblocks'] as $groupId=>$userId) {
                $blocks = $blocksTable->findBy(array(
                                                    'blocked_id'=>$userId,
                                                    'blocked_type'=>'User',
                                                    'blocker_id'=>$groupId,
                                                    'blocker_type'=>'Group'
                                                    ));
                $block = $blocks[0];
                $block->delete();
            }
        }
    }
}