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
        parent::browseAction();
        $tags = get_db()->getTable('Tag')->findBy(array('type'=>'Group'));
        $this->view->tags = $tags;
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
        $group->addMember($user);
        $responseArray = array('status'=>'ok');
        $response = json_encode($responseArray);
        $this->_helper->json($response);
    }


    public function quitAction()
    {
        $user =  current_user();
        $group = $this->findById();
        $group->removeMember($user);
        $responseArray = array('status'=>'ok');
        $response = json_encode($responseArray);
        $this->_helper->json($response);
    }

    public function requestAction()
    {
        $user =  current_user();
        $group = $this->findById();
        try {
            $group->addPendingMember($user);
            $responseArray = array('status'=>'ok');
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
        $user = $this->getTable('User')->find($userId);        
        $group = $this->getTable()->find($groupId);        
        $group->approveMember($user);
        $responseArray = array('status'=>'ok');
        $response = json_encode($responseArray);
        $this->_helper->json($response);
    }


    public function removeMemberAction()
    {
        $userId = $this->getRequest()->getParam('user');
        $user = get_db()->getTable('User')->find($userId);
        $group= $this->findById();
        $group->removeMember($user);
        $response = array('status'=>'ok');
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
            $group->addMember($currUser);
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
        $params = array(
            'user' => current_user()
        );
        $groups = $this->getTable()->findBy($params);
        $this->view->groups = $groups;
    }

    public function addItemAction()
    {
        $responseJson = array();
        $itemId = $_POST['itemId'];
        $groupId = $_POST['groupId'];
        $group = $this->getTable()->find($groupId);
        if($group->addItem($itemId)) {
            $responseJson['itemId'] = $itemId;
            $responseJson['groupId'] = $groupId;
        } else {
            $responseJson['status'] = 'fail';
        }
        $response = json_encode($responseJson);
        $this->_helper->json($response);
    }

}