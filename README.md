# PHP-table2Model
This is a a parent class that manages a db table depends on some meta data (ex:table name) defined on its child. It is easy to use.

```php
$conn = Conn::generate("default"); //default is a db connection name set in config.php

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
// Filtered user list
$user_list = (new User($conn))->filter_json("some_key='some_value'");

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

```