<?php
    function smarty_function_get_tag_summary($params, $smarty)
    {
        $db = Zend_Registry::get('db');
        $user_id = (int) $params['user_id'];

        $summary = Default_Model_DbTable_BlogPost::GetTagSummary($db, $user_id);

        if (isset($params['assign']) && strlen($params['assign']) > 0){
            $smarty->assign($params['assign'], $summary);
        }
    }
?>