<?php 
/**
 */
return [
    'module_init'=> [
        'fi\\home\\behavior\\InitConfig'
    ],
    'action_begin'=> [
        'fi\\home\\behavior\\ListenProtectedUrl'
    ]
]
?>