<?php

header("Content-Type: application/json;");

require './model.php';

$conn = Conn::generate("default");

// CREATE A USER //
$user = new User($conn);
$user->name = "John";
$user->surname = "Doe";
$user->save();
$inserted = $user->json();

// UPDATING A USER //
$user->name = "Patrick";
$user->save();
$updated = $user->json();

// Whole user list
$user_list = (new User($conn))->filter_json();

// DELETE A USER //
$user->delete();

// GET A USER
$user = new User($conn);
try{
    $user->get("some_key='some_value'");
}
catch(RecordNotFoundErr $e){
    //Record Not Found
}

$data = array("inserted"=>$inserted,"updated"=>$updated,"user_list"=>$user_list);

$conn = null;

die(json_encode($data));

?>