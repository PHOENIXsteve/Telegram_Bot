<?php
  $host = 'localhost';
  $username = 'u6790_phoenix';
  $password = "52520077_shsh";
  $dbname = "u6790_phoenix";

  $conn = mysqli_connect($host,$username,$password,$dbname);
  if (!$conn) {
    echo "MYSQLI_ERRORnn" . mysqli_error($conn);
  }
  function rStr($text){
        global $conn;
        $res = mysqli_real_escape_string($conn,$text);
        return $res;
    }
  $admin = "1915213082";
?>