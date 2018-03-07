<?php 

$servername = "localhost";
$username = "root";
$password = "";
$databaseName = "userinfo";

// Create connection to mysql database
$conn = mysqli_connect($servername, $username, $password, $databaseName);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} else {echo "it connected to the 'userinfo' database <br>";}

// check if the table users has already been created.
$query = "SELECT * FROM users";

// Send query to the database. $conn specifies which database. In the $query it specifies which table (users).
$result = mysqli_query($conn, $query);


// table did not exist. Create it.
    if (!$result){

        // Create the table users in this database 'userinfo' if doesn't exist.
        $query = "CREATE TABLE users(id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY, name VARCHAR(32) NOT NULL, surname VARCHAR(32) NOT NULL , email VARCHAR(50) NOT NULL UNIQUE)";

        $createQuery = mysqli_query($conn, $query);

        // Check if it worked to send the query
        if (!$createQuery){
            die("creating table did not work. " . mysqli_connect_error());   
        }
        else {
            echo "table users created.";} 
    }

// Table did exist
    else {
        echo "table users already exists. <br>";
    }





// open file in read only mode
$file = fopen("users.csv","r");
// while not at end of file keep going thru each line
    while(!feof($file))
      {
        $ArrayOneLine = fgetcsv($file);
        
        $lowerName = strtolower($ArrayOneLine[0]);
        $finalName= mysqli_real_escape_string($conn, ucwords($lowerName));
        
        $lowerSurname = strtolower($ArrayOneLine[1]);
        $finalSurname= mysqli_real_escape_string($conn, ucwords($lowerSurname));
        
        $lowerEmail = strtolower($ArrayOneLine[2]);
        $finalEmail = mysqli_real_escape_string($conn, $lowerEmail);
        
        
        $query = "INSERT INTO users(name, surname, email)";
        $query .= "VALUES ('$finalName', '$finalSurname', '$finalEmail')";
        
        $result = mysqli_query($conn, $query);
        
        if (!$result){
            die ("Query FAILED" . mysqli_error($conn));
        
        }
            else {echo "users table updated. <br>";} 
        
    }
$query = "SELECT * FROM users";
$result = mysqli_query($conn, $query);
    
    if (!$result){
        die ('Query FAILED, could not read.');
        
    } else {echo "table is able to be read.<br>"; }

  while ($row = mysqli_fetch_assoc($result)) {
            print_r($row);
            echo "we are here.";
        }



?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
    
</body>
</html>