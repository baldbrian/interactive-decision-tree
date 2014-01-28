<?php
session_start();
if (!$_SESSION['isLoggedIn']){
    header('Location: login.php');
}

require('../_CONFIG.php');

if (!isset($_SERVER["HTTP_HOST"])) {
  parse_str($argv[1], $_GET);
  parse_str($argv[1], $_POST);
}
try {
        $dbh = new PDO("mysql:host=" . DBHOST . ";dbname=" . DATABASE_NAME , DBUSERNAME, DBPASSWD);
    }
catch(PDOException $e)
    {
        echo $e->getMessage();
    }

function bindPostVals($query_string) {
    $cols = '';
    $upd = '';
    $vals = '';
    $data = array();
    unset($query_string['action']);
    foreach ($query_string as $key => $value) {
        {
            $key_name = ":" . $key;
            $upd .= "`$key` = " . "$key_name,";
            $cols .= "`$key`,";
            $vals .= ":$key,";
            $data[$key] = trim($value);
        }
    }

    $update = rtrim($upd,',');
    $columns = rtrim($cols,',');
    $values = rtrim($vals, ',');
    return array('columns'=>$columns, 'values' => $values, 'data' => $data, 'update' => $update);
}

switch($_POST['action']){

    case 'create':
    $d = bindPostVals($_POST);
    $q = $dbh->prepare("INSERT INTO referrals (" . $d['columns'] .") VALUES (" . $d['values'] . ")");
    $q->execute($d['data']);
    $e = $q->errorInfo();
    if(!$e[1]){
        $last_id = $dbh->lastInsertId();
        echo json_encode(array('status'=>'OK', 'message'=>'Added successfully','last_id' => $last_id));
    } else {
        echo json_encode(array('status'=>'ERROR', 'message'=>'Error Adding: ' . $e[1]));
    }
    break;

    case 'read':
    $q = $dbh->prepare('SELECT * from referrals where id = ?');
    $q->bindParam(1, $_POST['id']);
    $q->execute();
    $result = $q->fetch(PDO::FETCH_ASSOC);
    echo json_encode($result);
    break;

    case 'update':
    $d = bindPostVals($_POST);
    $q = $dbh->prepare("UPDATE referrals SET" .  $d['update'] ." WHERE id = :id ");
    $q->execute($d['data']);
    if(!$e[1]){
        echo json_encode(array('status'=>'OK', 'message'=>'Edited successfully','last_id' => $_POST['id']));
    } else {
        echo json_encode(array('status'=>'ERROR', 'message'=>'Error Editing: ' . $e[1]));
    }

    break;

    case 'delete':
    $d = bindPostVals($_POST);
    $q = $dbh->prepare("DELETE FROM referrals  WHERE id = :id ");
    $q->execute($d['data']);
    if(!$e[1]){
        echo json_encode(array('status'=>'OK', 'message'=>'Deleted successfully','last_id' => $_POST['id']));
    } else {
        echo json_encode(array('status'=>'ERROR', 'message'=>'Error Deleting: ' . $e[1]));
    }

    break;
}

