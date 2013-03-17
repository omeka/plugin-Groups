<?php

class Group_View_Helper_GroupSearchFilters extends Zend_View_Helper_Abstract
{
    public function groupSearchFilters(array $params = null)
    {
        if ($params === null) {
            $request = Zend_Controller_Front::getInstance()->getRequest();
            $requestArray = $request->getParams();
        } else {
            $requestArray = $params;
        }
        
        $db = get_db();
        $displayArray = array();
        foreach ($requestArray as $key => $value) {
            $filter = $key;
            if($value != null) {
                $displayValue = null;
                switch ($key) {
                    
                    case 'tag':
                    case 'tags':
                        $displayValue = $value;
                        break;
                }
                if ($displayValue) {
                    $displayArray[$filter] = $displayValue;
                }                   
            }
        }
        
        $html = '';
        if (!empty($displayArray)) {
            $html .= '<div id="groups-filters">';
            $html .= '<ul>';
            foreach($displayArray as $name => $query) {
                $html .= '<li class="' . $name . '">' . ucfirst($name) . ': ' . $query . '</li>';
            }
            if(!empty($advancedArray)) {
                foreach($advancedArray as $j => $advanced) {
                    $html .= '<li class="advanced">' . $advanced . '</li>';
                }
            }
            $html .= '</ul>';
            $html .= '</div>';
        }
        return $html;        
    }    
}