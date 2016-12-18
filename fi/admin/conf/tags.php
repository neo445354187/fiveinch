<?php 
/**
 */
return [
    'module_init'=> [
        'fi\\admin\\behavior\\InitConfig'
    ],
    'action_begin'=> [
        'fi\\admin\\behavior\\ListenLoginStatus',
        'fi\\admin\\behavior\\ListenPrivilege',
        'fi\\admin\\behavior\\ListenOperate'
    ]
]
?>