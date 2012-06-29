<?php


class Groups_GroupController extends Omeka_Controller_Action
{

    protected $_browseRecordsPerPage = 10;

    public function init()
    {
        if (version_compare(OMEKA_VERSION, '2.0-dev', '>=')) {
            $this->_helper->db->setDefaultModelName('Group');
        } else {
            $this->_modelClass = 'Group';
        }
//@TODO: check if I really need to muck about with the contexts
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('join', 'json')
                    ->initContext();
    }

    public function indexAction()
    {
        $this->redirect->goto('groups/browse');
    }

    public function browseAction()
    {        
        $tags = get_db()->getTable('Tag')->findBy(array('type'=>'Group'));
        $this->view->tags = $tags;
        parent::browseAction();
    }


    public function showAction()
    {
        //unfortunate duplication of parent because I want the group record later
        $varName = strtolower($this->_helper->db->getDefaultModelName());

        $record = $this->findById();

        Zend_Registry::set($varName, $record);

        fire_plugin_hook('show_' . strtolower(get_class($record)), $record);

        $this->view->assign(array($varName => $record));
        //end unfortunate duplication
        //now do something useful with it

        //stuff the items in so they are available to output formats
        if(has_permission($record, 'items')) {
            $items = groups_items_for_group();
            $this->view->assign(array('items'=>$items));
        }
    }

    public function joinAction()
    {
        $user =  current_user();
        $group = $this->findById();        
        $responseArray = array('status'=>'ok');
        $response = json_encode($responseArray);
        $to = $group->findMembersForNotification('notify_member_joined');        
        try {
            $group->sendNewMemberEmail($to);
        } catch (Exception $e) {            
            $responseArray = array('status'=>'error');
        }
        $group->addMember($user);
        $this->_helper->json($response);
    }


    public function quitAction()
    {
        $user =  current_user();
        $group = $this->findById();
        $group->removeMember($user);        
        $responseArray = array('status'=>'ok');
        $response = json_encode($responseArray);

        $to = $group->findMembersForNotification('notify_member_left');
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
        $group = $this->findById();
        try {
            $group->addMember($user, 1);
            $responseArray = array('status'=>'ok');
        } catch (Exception $e) {
            $responseArray = array('status'=>'error');
        }
        $to = $group->findAdmins();
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
        $group= $this->findById();
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
        $group= $this->findById();
        $group->removeMember($user);
        $response = array('status'=>'ok');
        
        $to = $group->findMembersForNotification('notify_member_left');
        $group->sendMemberLeftEmail($user, $to);
        $this->_helper->json($response);

    }

    public function addAction()
    {
        require_once GROUPS_PLUGIN_DIR . '/forms/group.php';
        $form = new GroupForm();
        $this->view->form = $form;

        if(!empty($_POST)) {
            $group = new Group();
            $currUser = current_user();
            $_POST['owner_id'] = $currUser->id;
            $group->saveForm($_POST);
            $group->addMember($currUser, 0, 'is_owner');
            $this->redirect->gotoUrl('/groups/show/' . $group->id );
        }

    }

    public function editAction()
    {
        require_once GROUPS_PLUGIN_DIR . '/forms/group.php';
        $form = new GroupForm();
        $this->view->form = $form;
        $group = $this->findById();
        $defaults = $group->toArray();
        $defaults['tags'] = groups_tags_string_for_group($group);
        $form->setDefaults($defaults);
        $this->view->form = $form;

        if(!empty($_POST)) {
            $currUser = current_user();
            $group->saveForm($_POST);
            $this->redirect->gotoUrl('/groups/show/' . $group->id );
        }
    }

    public function myGroupsAction()
    {

        $user = current_user();
        $params = array(
                'user' => $user
        );     
        if(!empty($_POST['invitations'])) {      
            foreach($_POST['invitations'] as $id=>$options) {
                $invitation = $this->findById($id, 'GroupInvitation');
                foreach($options as $option=>$value) {
                    if($option == 'join') {
                        $invitation->Group->addMember($user);
                        $to = $invitation->Group->findMembersForNotification('notify_member_joined');
                        try {
                            $invitation->Group->sendNewMemberEmail($to);
                        } catch(Exception $e) {
                            _log($e);
                        }
                        $invitation->delete();
                    }
                }
            }
        }
        if(!empty($_POST['groups'])) {
  
            foreach($_POST['groups'] as $id=>$options) {
                $group = $this->findById($id);
                $membership = groups_get_membership($group);
                $membership->unsetOptions();
                foreach($options as $option=>$value) {
            
                    switch($option) {
            
                        case "quit":
                            $membership->delete();            
                            break;
            
                        case "submitted":
                            //do nothing, just here to make the $_POST arrive when nothing is checked
                            break;
            
                        default:
                            $membership->$option = 1;
                            if($confirmation = $membership->getConfirmation($option)) {
                                $confirmation->delete();
                            }
                            break;
                    }
                }
                if($membership->exists()) {
                    $membership->save();
                }
                
            }                
        }

        

        $groups = $this->_helper->db->getTable()->findBy($params);
        $invitations = $this->_helper->db->getTable('GroupInvitation')->findBy(array('user_id'=>$user->id));        
        $this->view->groups = $groups;
        $this->view->invitations = $invitations;
    }
    
    public function administrationAction()
    {
        $user = current_user();
        if(!empty($_POST)) {
            foreach($_POST['membership'] as $groupId=>$memberships) {
                $group = $this->_helper->db->getTable('Group')->find($groupId);
                foreach($memberships as $membershipId=>$action) {
                    $membership = $this->_helper->db->getTable('GroupMembership')->find($membershipId);
                    switch($action) {
                        case 'remove':
                            $group->removeMember($membership->User);
                            $to = $group->findMembersForNotification('notify_member_left');
                            $group->sendMemberLeftEmail($to);                            
                        break;
                        
                        case 'deny':
                            $group->denyMembership($membership);
                        break;
                        
                        case 'approve':
                            $group->approveMember($membership->User);
                            $to = $group->findMembersForNotification('notify_member_new');
                            $group->sendNewMemberEmail($to);
                        break;
                    }
                }
            }
            
            foreach($_POST['status'] as $groupId=>$memberships) {
                foreach($memberships as $membershipId=>$action) {
                    $membership = $this->_helper->db->getTable('GroupMembership')->find($membershipId);
                    switch($action) {
                        case 'member':
                            $membership->is_admin = 0;
                            $membership->is_owner = 0;
                        break;
                        
                        case 'admin':
                            if(!$membership->is_admin) {
                                $confirmation = new GroupConfirmation;
                                $confirmation->group_id = $groupId;
                                $confirmation->membership_id = $membershipId;
                                $confirmation->type = 'is_admin';
                            }
                            $membership->is_owner = 0;
                        break;
                        
                        case 'owner':
                            if(!$membership->is_owner) {
                                $confirmation = new GroupConfirmation;
                                $confirmation->group_id = $groupId;
                                $confirmation->membership_id = $membershipId;
                                $confirmation->type = 'is_owner';
                            }
                        break;
                    }
                    $membership->save();
                }
            }
        }
        $groups = $this->_helper->db->getTable('GroupMembership')->findGroupsBy(array('user_id'=>$user->id, 'is_pending'=>0, 'admin_or_owner'=>true));
        $this->view->groups = $groups;        
    }

    public function invitationsAction()
    {
        $sender = current_user();
        $groups = $this->_helper->db->getTable('GroupMembership')->findGroupsBy(array('user_id'=>$sender->id, 'is_pending'=>0));
        foreach($groups as $key=>$group) {
            if(!has_permission($group, 'invitations')) {
                unset($groups[$key]);
            }
        }
        if(isset($_POST['emails'])) {
            $emails = explode(',', $_POST['emails']);
            $message = $_POST['message'];
            $userTable = $this->_helper->db->getTable('User');
            
            $nonUserEmails = array();
            foreach($_POST['groups'] as $groupId) {
                $group = $this->getTable()->find($groupId);
                foreach($emails as $index=>$email) {      
                    $email = trim($email);
                    $invitation = new GroupInvitation;
                    $user = $userTable->findByEmail(trim($email));
                    if($user) {
                        if($group->hasMember($user)) {                            
                            unset($emails[$index]);
                        } else {              
                            $invitation->user_id = $user->id;
                            $invitation->sender_id = $sender->id;
                            $invitation->message = $message;
                            $invitation->group_id = $groupId;
                            $invitation->save();                            
                        }
                                                
                    } else {
                        $nonUserEmails[] = $email;
                        unset($emails[$index]);
                    }                    
                }
                try {
                    $group->sendInvitationEmail($emails, $message, $sender);
                    $this->flashSuccess('Invitations successfully sent');
                } catch(Exception $e) {
                    $this->flashError("Couldn't send email");
                }
                                
            } 
        }
        $this->view->groups = $groups;        
    }
    
    public function addItemAction()
    {
        $responseJson = array();
        $itemId = $_POST['itemId'];
        $groupId = $_POST['groupId'];
        $group = $this->_helper->db->getTable()->find($groupId);
        $item = $this->_helper->db->getTable('Item')->find($itemId);
        if($group->addItem($itemId)) {
            $responseJson['itemId'] = $itemId;
            $responseJson['groupId'] = $groupId;
        } else {
            $responseJson['status'] = 'fail';
        }
        $response = json_encode($responseJson);
        
        $to = $group->findMembersForNotification('notify_item_new');  
        $group->sendNewItemEmail($item, $to);        
        $this->_helper->json($response);
    }

}