<?php 


// Get arguments from command line into an array. 
//$argv = array("--file", "users.csv", "--create_table", "--dry_run", "-u", "root", "-h", "userinfo", "--help");





if (in_array("--file", $argv)){
	for ($i=0; $i<sizeof($argv); $i++){
		if ($argv[$i]=="--file"){
			$filename=$argv[$i+1];
		}
	}
}

$username = "root";
if (in_array("-u", $argv)){
	for ($i=0; $i<sizeof($argv); $i++){
		if ($argv[$i]=="-u"){
			$username=$argv[$i+1];
			break;
		}
	}
}

$password = "";
if (in_array("-p", $argv)){
	for ($i=0; $i<sizeof($argv); $i++){
		if ($argv[$i]=="-p"){
			$password=$argv[$i+1];
			break;
		}
	}
}

$createTableOnly = false;
if (in_array("--create_table", $argv)){
	$createTableOnly=true;
}


$dryRun=false;
if (in_array("--dry_run", $argv)){
	$dryRun=true;
}
$printHelp = false;
if (in_array("--help", $argv)){
	$printHelp=true;
}
		
		
		

	
	
	










$servername = "localhost";


$databaseName = "userinfo";

// Create connection to mysql database
$conn = mysqli_connect($servername, $username, $password, $databaseName);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} else {echo "it connected to the 'userinfo' database \n";}

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
        echo "table users already exists. \n";
    }




// Table is now ready for data. Open file in read only mode
$file = fopen($filename,"r");



$allEmails=array();

// Read all current emails in 'users' table and store in $allemails, so duplicates aren't added.

$query = "SELECT email FROM users";
$result = mysqli_query($conn, $query);
    
    if (!$result){
        die ('Query FAILED, could not read.');
        
    } 
while ($row = mysqli_fetch_assoc($result)) {
            array_push($allEmails, $row["email"]);
        }

// Handy function from http://www.med   ia-division.com/correct-name-capitalization-in-php/
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


$header = true;

// while not at end of file keep going through each line.
    while(!feof($file)){
        

        //cycle through and place parts of csv in different variables.
        $row = fgetcsv($file);
      
        if ($header == true){
            
            // Remove all white space from header and check if formatted correctly.
            if (preg_replace('/\s+/', '', $row[0]) == "name" && preg_replace('/\s+/', '', $row[1]) == "surname" && preg_replace('/\s+/', '', $row[2]) == "email"){
                
                echo "Your header is correct. Will now import data to database. \n";
                $header = false;
                
            } else {
            
                die("Your header is incorrectly formatted. Must be 'name, surname, email'. \n");
            
            }
            
            $header = false;
            continue;
        }
        
        
        
        
        $fixedFirstName = titleCase($row[0]);
        $finalName= mysqli_real_escape_string($conn, $fixedFirstName);
        
        $fixedSurname = titleCase($row[1]);
        $finalSurname= mysqli_real_escape_string($conn, $fixedSurname);
        
        $lowerEmail = filter_var(strtolower($row[2]), FILTER_SANITIZE_EMAIL);

        
        
        //check if email is unique or has been inserted before.
                
                if (in_array($lowerEmail, $allEmails)){

                    echo "ERROR This email address is not unique:  " . $lowerEmail . "\n";
                    
                }   else if (!filter_var($lowerEmail, FILTER_VALIDATE_EMAIL)) {

                                
                    echo "ERROR This is an invalid email format: " . $lowerEmail . "\n"; 
                    }
        
                else {
                    $finalEmail = mysqli_real_escape_string($conn, $lowerEmail);
                    $query = "INSERT INTO users(name, surname, email)";
                    $query .= " VALUES ('$finalName', '$finalSurname', '$finalEmail')";

                    $result = mysqli_query($conn, $query);

                        if (!$result){
                           // die ("Query FAILED, because: " . mysqli_error($conn));
                        echo "error sending query: ".  $query . mysqli_error($conn) . "\n";
                        

                        }
                        else {
                                echo "users table updated. \n";
                                array_push($allEmails, $finalEmail);
                            }
                }
        

    }


if ($printHelp){
	echo " --file [csv file name] – this is the name of the CSV to be parsed\n
• --create_table – this will cause the MySQL users table to be built (and no further action will be taken)\n
• --dry_run – this will be used with the --file directive in the instance that we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered.\n
• -u – MySQL username\n
• -p – MySQL password\n
• -h – MySQL host\n
• --help – which will output the above list of directives with details. \n";
	
}









?>