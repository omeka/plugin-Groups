<?php


class Groups_GroupController extends Omeka_Controller_Action
{

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

    public function joinAction()
    {
        $user =  current_user();
        $group = $this->findById();
        $group->addMember($user);
        $response = json_encode('ok');
        $this->_helper->json($response);
    }


    public function quitAction()
    {
        $user =  current_user();
        $group = $this->findById();
        $group->removeMember($user);
        $response = json_encode('ok');
        $this->_helper->json($response);
    }

    public function requestAction()
    {
        $user =  current_user();
        $group = $this->findById();
        $group->addPendingMember($user);
        $response = json_encode('ok');
        $this->_helper->json($response);

    }

    public function joinOthersAction($user)
    {
        $group= $this->findById();
        $group->addMember($user);
        $response = json_encode('ok');
        $this->_helper->json($response);
    }


    public function approveRequestAction()
    {
        $userId = $_POST['userId'];
        $groupId = $_POST['groupId'];
        $user = $this->getTable('User')->find($userId);
        $group = $this->getTable()->find($groupId);
        $group->approveMember($user);
        $response = json_encode('ok');
        $this->_helper->json($response);

    }


    public function removeMemberAction()
    {
        $group= $this->findById();
        $group->removeMember($user);
        $response = json_encode('ok');
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

        $form->setDefaults($group->toArray());
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