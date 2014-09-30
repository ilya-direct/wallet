<?php
session_start();
if (optional_param('login',false) && optional_param('password',false) && optional_param('submit',false)){
    $USER=$DB->get_record_select('user',array('login'=>$login,'password'=>$password));
    if ($USER  == false){
        $login_error=1;
    }
}