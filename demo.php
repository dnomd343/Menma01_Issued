<?php

$mysqlServer = 'localhost';
$mysqlUser = 'chaoxing';
$mysqlPasswd = 'chaoxing_passwd';
$databaseName = 'chaoxing';

function getStatus() {
    global $mysqlServer, $mysqlUser, $mysqlPasswd, $databaseName;
    $conn = new mysqli($mysqlServer, $mysqlUser, $mysqlPasswd, $databaseName);
    $raw = $conn->query('SELECT * FROM main')->fetch_assoc(); 
    $status['passwd'] = $raw['passwd'];
    $status['validity'] = $raw['validity'];
    return $status;
}

function setStatus($passwd, $validity) {
    global $mysqlServer, $mysqlUser, $mysqlPasswd, $databaseName;
    $conn = new mysqli($mysqlServer, $mysqlUser, $mysqlPasswd, $databaseName);
    $conn->query('DELETE FROM main');
    $raw = $conn->query('INSERT INTO main (passwd,validity) VALUES ("' . $passwd . '","' . $validity . '")');
}

function checkStatus() {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(getStatus());
}

function newPasswd($length){
    $passwd = '';
    for ($i = 0; $i < $length; $i++) {
        $rand = mt_rand(0, 61);
        if ($rand >= 0 && $rand <=9) {
            $passwd .= chr($rand + 48);
        } else if ($rand >= 10 && $rand <=35) {
            $passwd .= chr($rand + 55);
        } else if ($rand >=36 && $rand <= 61) {
            $passwd .= chr($rand + 61);
        }
    }
    return $passwd;
}

function newStatus() {
    $passwd = newPasswd(16);
    $validity = (int)time() + 300;
    setStatus($passwd, $validity);
}

function isExpired() {
    if ((int)getStatus()['validity'] < (int)time()) {
        return true;
    } else {
        return false;
    }
}

function isAuth() {
    if ($_GET['key'] != getStatus()['passwd']) {
        return false;
    } else {
        return true;
    }
}

function checkKey() {
    header('Content-Type: application/json; charset=utf-8');
    if (isAuth()) {
        echo '{"status":"T"}';
    } else {
        echo '{"status":"F"}';
    }
}

function outputFile($fileName) {
    $data = file_get_contents($fileName);
    echo $data;
}

function queryScript($type) {
    if (isAuth()) {
        if ($type == 1) {
            header('Content-Type: application/x-javascript; charset=utf-8');
            outputFile('script_1.user.js');
        } else if ($type == 2) {
            header('Content-Type: application/x-javascript; charset=utf-8');
            outputFile('script_2.user.js');
        } else {
            header('Content-Type: application/x-javascript; charset=utf-8');
            outputFile('script_3.user.js');
        }
    } else {
        header('Content-Type: text/html; charset=utf-8');
        echo '密钥已失效';
    }
}

function route() {
    $urlRaw = $_SERVER['DOCUMENT_URI'];
    if ($urlRaw == '/js/admin') {
        header('Content-Type: text/html; charset=utf-8');
        outputFile('admin.html');
    } else if ($urlRaw == '/js/pw/new') {
        newStatus();
        checkStatus();
    } else if ($urlRaw == '/js/pw/status') {
        checkStatus();
    } else if ($urlRaw == '/js') {
        header('Content-Type: text/html; charset=utf-8');
        outputFile('user.html');
    } else if ($urlRaw == '/js/check') {
        checkKey();
    } else if ($urlRaw == '/js/query/script_1.user.js') {
        queryScript('1');
    } else if ($urlRaw == '/js/query/script_2.user.js') {
        queryScript('2');
    } else if ($urlRaw == '/js/query/script_3.user.js') {
        queryScript('3');
    } else {
        echo '未授权操作';
    }
}

function main() {
    if (isExpired()) {
        newStatus();
    }
    route();
}

main();

?>
