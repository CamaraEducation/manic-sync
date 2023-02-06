<?php

define('SERVER', 'http://camara.local:83/file.php');
define('MANICDB', 'C:\Users\camaraadmin\AppData\Local\Finkit\ManicTime\ManicTimeReports.db');

// retrieve the last recorded date
function getLastDate(){
    $db = new SQLite3('ManicTimeLastExport.db');
    $res = $db->query("SELECT last_date from export_date where id=1");
    return $res->fetchArray()[0];
}

// function to export Manic Files
function manicData($file, $query){
    $date = getLastDate();

    // prepare the database
    $manicdb = new SQLite3(MANICDB);

    $result = $manicdb->query($query);
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $data[] = $row;
    } $manicdb->close();

    isset($data) ? $jsonData = json_encode($data) : $jsonData ='';
    
    file_put_contents($file, $jsonData, JSON_PRETTY_PRINT);
}

function updateLastDate(){
    $last = date('Y-m-d h:i:s');
    $db = new SQLite3('ManicTimeLastExport.db');
    $query = "UPDATE export_date SET last_date='$last' WHERE id=1";
    $db->exec($query);
    $db->close();
}

function init(){
    config();
    $lastDate = getLastDate();

    // computer usage data
    manicData(
        "computer.json",
        "select c.DeviceName ,a.Name, a.StartLocalTime, a.EndLocalTime, ROUND((JULIANDAY(a.EndLocalTime) - JULIANDAY(a.StartLocalTime)) * 86400) / 60 AS Duration from Ar_Activity a JOIN Ar_Timeline b on a.ReportId = b.ReportId JOIN Ar_Environment c on b.EnvironmentId = c.EnvironmentId where a.ReportId = 2 and StartLocalTime > '$lastDate'"
    );

    // application usage data
    manicData(
        "application.json",
        "select c.DeviceName ,cg.Name, a.StartLocalTime, a.EndLocalTime, ROUND((JULIANDAY(a.EndLocalTime) - JULIANDAY(a.StartLocalTime)) * 86400) / 60 AS Duration from Ar_Activity a JOIN Ar_CommonGroup cg on a.CommonGroupId = cg.CommonId JOIN Ar_Timeline b on a.ReportId = b.ReportId JOIN Ar_Environment c on b.EnvironmentId = c.EnvironmentId WHERE a.ReportId = 3 and StartLocalTime > '$lastDate'"
    );

    // documets usage data
    manicData(
        "document.json",
        "select c.DeviceName ,a.Name, a.StartLocalTime, a.EndLocalTime, ROUND((JULIANDAY(a.EndLocalTime) - JULIANDAY(a.StartLocalTime)) * 86400) / 60 AS Duration from Ar_Activity a JOIN Ar_Timeline b on a.ReportId = b.ReportId JOIN Ar_Environment c on b.EnvironmentId = c.EnvironmentId where a.ReportId = 4 and StartLocalTime > '$lastDate'"
    );

    streamData();
}

// data streaming function
function streamData(){

    $client_name = file_get_contents('config');
    $url = 'http://camara.local:83/file.php';
    $file1 = new CURLFile('application.json');
    $file2 = new CURLFile('computer.json');
    $file3 = new CURLFile('document.json');
    $data = array(
        'file1' => $file1,
        'file2' => $file2,
        'file3' => $file3,
        "client" => "$client_name",
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    $output = trim($output);


    // 2023-01-05 09:32:51
    if ($output == 'ok') {
        updateLastDate();
    }
}

function config(){

    $url = 'http://camara.local:83/config.php';

    // file config.json does not exist send get request to url
    if (!file_exists('config')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        file_put_contents('config', $output);
    }
    
}

init();