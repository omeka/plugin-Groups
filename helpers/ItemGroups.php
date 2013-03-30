<?php
class Group_View_Helper_ItemGroups extends Zend_View_Helper_Abstract
{
    
    public function itemGroups($item)
    {
        $groups = $this->_getItems($item);
        $html = "<div class='groups-group-list'>";
        $html .= "<h3>" . __('Groups with ' . metadata($item, array('Dublin Core', 'Title'))) . "</h3>";
        $html .= "<ul class='groups-group-list'>";
        foreach($groups as $group) {
            $html .= "<div class='groups-group'>";
            $html .= "<h2>" . link_to($group, 'show', $group->title) . "</h2>";
            $html .= "<p>" . $group->description . "</p>";
            $html .= "</div>";
        }
        $html .= "</ul></div>";
        return $html;        
    }
    
    protected function _getItems($item)
    {
        $db = get_db();
        $params = array(
                'hasItem'=>$item
        );
        $groups = $db->getTable('Group')->findBy($params);
        
        //need to filter out permissions to view items in the group
        //if you can't see the items in the group, you shouldn't see a link to the group from an item
        //can't see a way to do it via filters on the sql
        $currentUser = current_user();
        $acl = Omeka_Context::getInstance()->acl;
        $assertion = new GroupsAclAssertion;
        foreach($groups as $index=>$group) {
        
            if(! $assertion->assert($acl, $currentUser, $group, 'items')) {
                unset($groups[$index]);
            }
        }
        return $groups;
        
    }
}