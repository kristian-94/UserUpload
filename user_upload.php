<?php 


// Get arguments from command line into an array. 

/* Different command line inputs to be tested:


php user_upload.php --file users.csv --dry_run -h localhost -u root
php user_upload.php --file users.csv --create_table -h localhost -u root -d use
php user_upload.php -h localhost -u root


Assume:
1. Script will always create the table if it doesn't exist once connected to the database. 


*/


// assign default values. Table is always going to be called users.
$password = "";
$createTableOnly = false;
$dryRun=false;
$printHelp = false;
$tableName = "users";
$databaseName = "";
$filename = "";

function printHelp(){
	global $printHelp;
	if ($printHelp===true){
		echo "\nHELP SECTION\n\nThis php script user_upload.php inserts rows from a csv file into an existing database.\nThe script will always create the table 'users' if the table doesn't exist once connected to the database.\nThe csv file must have a header on the first row consisting of 'name, surname, email', with all subsequent rows following this format.\n
		
		\n
			This help section tells you all of the command line inputs and what they do: \n
			• --file [csv file name] ---> this is the name of the CSV to be parsed. eg. --file users.csv\n
			• --create_table ---> this will cause the MySQL users table to be built and no further action will be taken. \n
			• --dry_run ---> used with the --file directive. Runs the script and executes all functions but does not update the database.\n
			• -u ---> MySQL username. eg. -u root \n
			• -p ---> MySQL password eg. -p mypassword\n
			• -h ---> MySQL host eg. -h localhost\n
			• -d ---> MySQL database name eg. -d usersDatabase \n
			• --help ---> Outputs this help screen, a list of all directives and their details.  \n";

}
}

// Go through input array and assign parameters to their variables.
for ($i=0; $i<sizeof($argv); $i++){
		if ($argv[$i]==="--file"){
			$filename=$argv[$i+1];
		}
		if ($argv[$i]==="-u"){
			$username=$argv[$i+1];
			}
		if ($argv[$i]==="-p"){
			$password=$argv[$i+1];
		}
		if ($argv[$i]==="--create_table"){
			$createTableOnly=true;
		}
		if ($argv[$i]==="--dry_run"){
			$dryRun=true;
			echo ("Commencing dry run. \n");
		}
		if ($argv[$i]==="--help"){
			$printHelp=true;
		}
		if ($argv[$i]==="-h"){
			$servername=$argv[$i+1];
		}
		if ($argv[$i]==="-d"){
			$databaseName=$argv[$i+1];
		}
}

//First see if we can connect to mysql server with servername, username and password.
$serverConnect = mysqli_connect($servername, $username, $password);
if (!$serverConnect) {
    die("Connection to server failed: " . mysqli_connect_error());
}

if ($databaseName){
	// Now check if we can connect to the mysql database, and establish connection.
	$conn = mysqli_connect($servername, $username, $password, $databaseName);
	if (!$conn) {
		die("\n Connection to database failed: " . mysqli_connect_error() . "\n --help for more information.\n");
	}
}
else { printHelp();
	die("You connected to the server. Please specify the database name with the -d input. --help for more information.\n");
	 }


// check if the table users has already been created.
$query = "SELECT * FROM $tableName";

// Send query to the database. $conn specifies which database. In the $query it specifies which table (users).
$result = mysqli_query($conn, $query);


if (!$result && $dryRun===false){

        // Create the table users in this database 'userinfo' if doesn't exist.
        $query = "CREATE TABLE $tableName(id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY, name VARCHAR(32) NOT NULL, surname VARCHAR(32) NOT NULL , email VARCHAR(50) NOT NULL UNIQUE)";

        $createQuery = mysqli_query($conn, $query);

        // Check if it worked to send the query
        if (!$createQuery){
            die("creating table did not work. " . mysqli_connect_error());   
        }
        else {
			echo "Table " . $tableName. " created.\n";
        }
    }

// Table did exist
    else if ($dryRun===false){
        echo "Attempted to create table " . $tableName. " but it already exists. \n";
    }


if ($dryRun===false){
	if ($createTableOnly===true){
		printHelp();
		die("Created table only and stopped program. ");
	}
}


// Check if a filename has been input, otherwise end program. 
if (!$filename){
	printHelp();
	die("You have not specified a file. Will now end program. \n Type --help for more information.\n ");
}

// Table is now ready for data. Open file in read only mode
if (file_exists($filename)){

		if (fopen($filename, "r")){
		$file = fopen($filename,"r");
		}else {die("Could not open file. Check spelling and directory. ");}

} else {die($filename . " file does not exist. Check spelling and directory. ");}

$allEmails=array();

// Read all current emails in 'users' table and store in $allemails, so duplicates aren't added.

$query = "SELECT email FROM $tableName";
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


$header=true;
$notUniqueEmails=array();
$invalidEmails=array();
// while not at end of file keep going through each line.
while(!feof($file)){
        

        
        $row = fgetcsv($file);
      
	
	//The first row should be a header. This if statement checks the header to see its formatted correctly, and if it is the if statement is skipped on all subsequent loops.
        if ($header === true){
            
            // Remove all white space from header and check if formatted correctly.
            if (preg_replace('/\s+/', '', $row[0]) == "name" && preg_replace('/\s+/', '', $row[1]) == "surname" && preg_replace('/\s+/', '', $row[2]) == "email"){
                
                $header = false;
                
            } else {
            
                die("Your header is incorrectly formatted. Must be 'name, surname, email'. \n");
            
            }
            
            $header = false;
            continue;
        }
        
        
        
        //cycle through and place parts of csv in different variables.
        $fixedFirstName = titleCase($row[0]);
        $finalName= mysqli_real_escape_string($conn, $fixedFirstName);
        
        $fixedSurname = titleCase($row[1]);
        $finalSurname= mysqli_real_escape_string($conn, $fixedSurname);
        
        $lowerEmail = filter_var(strtolower($row[2]), FILTER_SANITIZE_EMAIL);

        
        
        //check if email is unique or has been inserted before.
                
                if (in_array($lowerEmail, $allEmails)){
                    array_push($notUniqueEmails, $lowerEmail);
                    
                }   else if (!filter_var($lowerEmail, FILTER_VALIDATE_EMAIL)) {
					array_push($invalidEmails, $lowerEmail);
                    }
        
                else {
                    $finalEmail = mysqli_real_escape_string($conn, $lowerEmail);
                    $query = "INSERT INTO $tableName(name, surname, email)";
                    $query .= " VALUES ('$finalName', '$finalSurname', '$finalEmail')";
					
					if ($dryRun===false){
                    	$result = mysqli_query($conn, $query);
							if (!$result){
							echo "error inserting row into database: ".  $query . mysqli_error($conn) . "\n";
							}
							else {
								array_push($allEmails, $finalEmail);
							 }
					}
                }
        

    }

// If there are any emails that aren't unique echo them out. 
if ($notUniqueEmails){
	echo "\n\nThe following emails are not unique (already exist in table):\n";
	for ($i=0; $i<sizeof($notUniqueEmails); $i++){
		echo $notUniqueEmails[$i] . "\n";
	}
}

// If there are any emails that are invalid echo them out. 
if ($invalidEmails){
	echo "\nThe following emails are invalid:\n";
	for ($i=0; $i<sizeof($invalidEmails); $i++){
		echo $invalidEmails[$i] . "\n";
	}
}

if ($dryRun===true){
	echo "Dry run completed. Database was not altered. \n";
}
printHelp();








?>