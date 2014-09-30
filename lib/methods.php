<?php
function get_category_id($category_name){
    global $DB;
    $category_name=strtolower($category_name);
    $category_name=trim($category_name);
    if ($DB->record_exists('category',array('name' => $category_name)))
        return $DB->get_field('category','id',array('name' => $category_name));
    else
        return $DB->insert_record('category',array('name' => $category_name);
}

function get_item_id($item_name,$category_id){
    global $DB;
    $item_name=strtolower($item_name);
    $item_name=trim($item_name);
    if ($DB->record_exists('category',array('name' => $item_name)))
        return $DB->get_field('category','id',array('name' => $item_name));
    else
        return $DB->insert_record('category',array('name' => $item_name));
}

function login(){

}