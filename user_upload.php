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


    if (!$result){

        // Create the table users in this database 'userinfo' if doesn't exist.
        $query = "CREATE TABLE users(id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY, name VARCHAR(32) NOT NULL, surname VARCHAR(32) NOT NULL , email VARCHAR(50) NOT NULL UNIQUE)";

        $createQuery = mysqli_query($conn, $query);

        // Check if it worked to send the query
        if (!$createQuery){
            die("creating table did not work. " . mysqli_connect_error());   
        }
        else {
            echo "table users created.";
        } 
    }

// Table did exist
    else {
        echo "table users already exists. <br>";
    }





// open file in read only mode
$file = fopen("users.csv","r");



$allEmails=array();

// should read table first then insert all current emails into $allEmails before trying to add new ones.

$query = "SELECT email FROM users";
$result = mysqli_query($conn, $query);
    
    if (!$result){
        die ('Query FAILED, could not read.');
        
    } 
while ($row = mysqli_fetch_assoc($result)) {
            array_push($allEmails, $row["email"]);
        }

// Handy function from http://www.media-division.com/correct-name-capitalization-in-php/
// Get correct capitalisation on various names.
function titleCase($string) 
{
	$word_splitters = array(' ', '-', "O'", "L'", "D'", 'St.', 'Mc');
	$lowercase_exceptions = array('the', 'van', 'den', 'von', 'und', 'der', 'de', 'da', 'of', 'and', "l'", "d'");
	$uppercase_exceptions = array('III', 'IV', 'VI', 'VII', 'VIII', 'IX');
 
	$string = strtolower($string);
	foreach ($word_splitters as $delimiter)
	{ 
		$words = explode($delimiter, $string); 
		$newwords = array(); 
		foreach ($words as $word)
		{ 
			if (in_array(strtoupper($word), $uppercase_exceptions))
				$word = strtoupper($word);
			else
			if (!in_array($word, $lowercase_exceptions))
				$word = ucfirst($word); 
 
			$newwords[] = $word;
		}
 
		if (in_array(strtolower($delimiter), $lowercase_exceptions))
			$delimiter = strtolower($delimiter);
 
		$string = join($delimiter, $newwords); 
	} 
	return $string; 
}




// while not at end of file keep going thru each line
    while(!feof($file)){
        
        //cycle through and place parts of csv in different variables.
        $row = fgetcsv($file);
        
        $fixedFirstName = titleCase($row[0]);
        $finalName= mysqli_real_escape_string($conn, $fixedFirstName);
        
        $fixedSurname = titleCase($row[1]);
        $finalSurname= mysqli_real_escape_string($conn, $fixedSurname);
        
        $lowerEmail = filter_var(strtolower($row[2]), FILTER_SANITIZE_EMAIL);
        //$finalEmail = mysqli_real_escape_string($conn, $lowerEmail);
        $finalEmail = $lowerEmail;

        
        
        //check if email is unique or has been inserted before.
                
                if (in_array($finalEmail, $allEmails)){

                    //echo "ERROR adding " . $finalEmail . ": email address must be unique.<br>";
                }   else if (!filter_var($finalEmail, FILTER_VALIDATE_EMAIL)) {
                                
                    echo "ERROR adding email, invalid email format: " . $finalEmail . "<br>"; 
                    }
        
                else {
                    $query = "INSERT INTO users(name, surname, email)";
                    $query .= "VALUES ('$finalName', '$finalSurname', '$finalEmail')";

                    $result = mysqli_query($conn, $query);

                        if (!$result){
                            //die ("Query FAILED, because: " . mysqli_error($conn));

                        }
                        else {
                                echo "users table updated. <br>";
                                array_push($allEmails, $finalEmail);
                            }
                }
        

    }



$query = "SELECT * FROM users";
$result = mysqli_query($conn, $query);
    
    if (!$result){
        die ('Query FAILED, could not read.');
        
    } else {echo "table is able to be read.<br>"; }

  while ($row = mysqli_fetch_assoc($result)) {
            print_r($row);
            echo "<br>";
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