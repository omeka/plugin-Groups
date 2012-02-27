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
        }
    }

    public function editAction()
    {

    }
}