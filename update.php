<?php
  $server = "localhost";
  $user = "root";
  $pass = "";
  $dbname = "disgustingPizza";
  $conn = mysqli_connect($server, $user, $pass, $dbname);
  if (!$conn) {
    echo "Connection failed";
  } else {
    updateObject();
  }

  function updateObject() {
    
  }
?>