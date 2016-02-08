<?php
    function smarty_function_get_monthly_blog_summary($params, $smarty)
    {
        $options = array();

        if (isset($params['user_id']))
            $options['user_id'] = (int) $params['user_id'];

        if (isset($params['liveOnly']) && $params['liveOnly'])
            $options['status'] = Default_Model_DbTable_BlogPost::STATUS_LIVE;

        if (isset($params['public_only']))
            $options['public_only'] = (int) $params['public_only'];

        if (isset($params['actuals']))
            $options['actuals'] = array(1);

        $db = Zend_Registry::get('db');

        $summary = Default_Model_DbTable_BlogPost::GetMonthlySummary($db, $options);

        if (isset($params['assign']) && strlen($params['assign']) > 0)
            $smarty->assign($params['assign'], $summary);
    }
?>