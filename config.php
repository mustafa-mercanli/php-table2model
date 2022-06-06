<?php
    $debug = True;

    $databases = array("default"=>
                    array("dbtype"=>"mysql",
                          "dbname"=>"testo",
                          "host"=>"localhost",
                          "username"=>"mumercan",
                          "password"=>"QmxlYWNoMTMu")
                );
    
    $databases = json_decode(json_encode($databases),false);

    return (object)array("databases"=>$databases,"debug"=>$debug);

?>