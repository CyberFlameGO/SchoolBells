<?php

$db = new PDO("sqlite:./bells.db");
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$dateq = $db->quote(date('Y-m-d'));
$timeq = $db->quote(date('H:i'));

$data = $db->query("SELECT * FROM bells WHERE date=$dateq AND time=$timeq")->fetchAll(PDO::FETCH_ASSOC);

if(count($data)>0){
    shell_exec("./on.sh");
    sleep(2);
    shell_exec("./off.sh");
}

?>