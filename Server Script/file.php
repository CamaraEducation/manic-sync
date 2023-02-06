
<?php
$server_time = $_SERVER['REQUEST_TIME'];
if (isset($_FILES['file1']) && isset($_FILES['file2']) && isset($_FILES['file3'])) {
    // File 1
    $file1 = $_FILES['file1'];
    $file1_name = $file1['name'];
    $file1_tmp_name = $file1['tmp_name'];

    // File 2
    $file2 = $_FILES['file2'];
    $file2_name = $file2['name'];
    $file2_tmp_name = $file2['tmp_name'];

    // File 3
    $file3 = $_FILES['file3'];
    $file3_name = $file3['name'];
    $file3_tmp_name = $file3['tmp_name'];

    // move the files to the desired location
    move_uploaded_file($file1_tmp_name, 'data/files/'.$server_time."-$file1_name");
    move_uploaded_file($file2_tmp_name, 'data/files/'.$server_time."-$file2_name");
    move_uploaded_file($file3_tmp_name, 'data/files/'.$server_time."-$file3_name");

    $data = <<<DATA
    {
        "usage" : "$server_time-$file1_name",
        "apps"  : "$server_time-$file2_name",
        "docs"  : "$server_time-$file3_name"
    }

    DATA;

    file_put_contents('data/jobs/'.$server_time.'-data.json', $data);

    echo 'ok';
}


