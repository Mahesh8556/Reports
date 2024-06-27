<?php
   $host        = "host = 127.0.0.1";
   $port        = "port = 5432";
   $dbname      = "dbname = bhairavnath";
   $credentials = "user = postgres password=vaibhavi@512";

   $conn = pg_connect( "$host $port $dbname $credentials"  );
   if(!$conn) {
      echo "Error : Unable to open database\n";
   } else {
      echo "Opened database successfully\n";
   }
?>