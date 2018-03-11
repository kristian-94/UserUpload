<?php

/* Get arguments from command line into an array. 

Assumptions:
1. Database is already created in the server. Input with -d directive.
2. Script will always create the table if it doesn't exist once connected to the database.

*/

// Assign default values. Table is always going to be called users.
$password = "";
$createTableOnly = false;
$dryRun=false;
$printHelp = false;
$tableName = "users";
$databaseName = "";
$filename = "";
$servername = "";
$username = "";

// This function prints the help section if the --help directive was specified.
function printHelp(){
	global $printHelp;
	if ($printHelp===true){
		echo "\nHELP SECTION\n\nThis php script user_upload.php inserts rows from a csv file into an existing database.\nThe script will always create the table 'users' if the table doesn't exist once connected to the database.\nThe csv file must have a header on the first row consisting of 'name, surname, email', with all subsequent rows following this format.\n\nThis help section tells you all of the command line inputs and what they do:
	• --file [csv file name] ---> this is the name of the CSV to be parsed. eg. --file users.csv
	• --create_table ---> this will cause the MySQL users table to be built and no further action will be taken. 
	• --dry_run ---> used with the --file directive. Runs the script and executes all functions but does not update the database.
	• -u ---> MySQL username. eg. -u root
	• -p ---> MySQL password eg. -p mypassword
	• -h ---> MySQL host eg. -h localhost
	• -d ---> MySQL database name eg. -d usersDatabase
	• --help ---> Outputs this help screen, a list of all directives and their details.\n
	An example input: php user_upload.php -h localhost -u root -p mypassword --file users.csv --dry_run";
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

//First see if we can connect to mysql server with servername, username and password. Not worrying about connecting to the database yet.
$serverConnect = @mysqli_connect($servername, $username, $password);
if (!$serverConnect) {
    die("\n\nConnection to server failed: " . mysqli_connect_error() . "\n\n Please specify the username, password, and host name.\n Type --help for more information. ");
}

// If a database name was given from the -d parameter.
if ($databaseName){
	
	// First check if database can be found. 
	$dbCheck = mysqli_select_db($serverConnect, $databaseName);
	if (!$dbCheck){ 
		printHelp();
		die("\n\nDatabase " . $databaseName . " cannot be found at this host. \nPlease check you have input a valid username, password and host name\n --help for more information.\n");
	} else {
		
		// Now we attempt to connect to the database.
		$conn = @mysqli_connect($servername, $username, $password, $databaseName);
		if (!$conn) {
			printHelp();
			die("\n\n Connection to database failed: " . mysqli_connect_error() . "\n --help for more information.\n");
		}
	}
}
// If no database was given with the -d parameter. 
else { 
	printHelp();
	die("You connected to the server. Please specify the database name with the -d input. --help for more information.\n");
	 }


// Create query that selects the table users, to check if it has already been created. 
$query = "SELECT * FROM $tableName";

// Send query to the database. $conn specifies which database. In the $query it specifies which table (users).
$result = mysqli_query($conn, $query);

// If the table does not exist and dry run is not active, create the table users.
if (!$result && $dryRun===false){

        // Create the table users. Email is unique and has id as the primary key.
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

// Table did exist, prints a message saying so.
    else if ($dryRun===false){
        echo "Attempted to create table " . $tableName. " but it already exists. \n";
    }

// In a dry run the database should not be updated.
// --dry_run conflicts with create_table
// In this script the table will not be created if --dry_run and --create_table are active.
if ($dryRun===false){
	if ($createTableOnly===true){
		printHelp();
		die("Created table " . $tableName. " only and stopped program. ");
	}
}


// Check if a filename has been input, otherwise end program. 
if (!$filename){
	printHelp();
	die("You have not specified a file with the --file parameter. Will now end program. \n Type --help for more information.\n ");
}

// Table is now ready for data. Open file in read only mode if it exists.
if (file_exists($filename)){

	if (fopen($filename, "r")){
		$file = fopen($filename,"r");
		}
	else {
		die("Could not open file. Check spelling and directory. ");
	}

} else {
	die($filename . " file does not exist. Check spelling and directory. ");
}

$allEmails=array();

// Read all current emails in 'users' table and store in $allemails, so duplicates aren't added.
$query = "SELECT email FROM $tableName";
$result = mysqli_query($conn, $query);

if (!$result){
    die ('Query FAILED, could not read any emails from table.');
}

// Add all emails from the table into $allEmails array.
while ($row = mysqli_fetch_assoc($result)) {
	array_push($allEmails, $row["email"]);
}

// Handy function from http://www.media-division.com/correct-name-capitalization-in-php/
// Does correct capitalisation on various names.
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

// While not at end of file. This loop adds each row of the csv to the table one at a time, collecting the invalid entries to be printed after.
while(!feof($file)){
	
	// $row is an array of each row in the csv file.
	$row = fgetcsv($file);

	//The first row should be a header. 
	//This if statement checks the header to see its formatted correctly, and if it is, this if statement is skipped on all subsequent loops.
    if ($header === true){
            
            // Remove all white space from header and check if formatted correctly.
            if (preg_replace('/\s+/', '', $row[0]) === "name" && preg_replace('/\s+/', '', $row[1]) === "surname" && preg_replace('/\s+/', '', $row[2]) === "email"){
                $header = false;
            } else {
                die("Your header in " . $filename . " is incorrectly formatted. Must be 'name, surname, email'. \n");
            }
            
            $header = false;
            continue;
        }
        
	//Cycle through csv and place names, surnames, and emails in different variables.
	// The names are changed to the correct format and a function is used so they can't affect the database in the query sent.
	$fixedFirstName = titleCase($row[0]);
	$finalName= mysqli_real_escape_string($conn, $fixedFirstName);

	$fixedSurname = titleCase($row[1]);
	$finalSurname= mysqli_real_escape_string($conn, $fixedSurname);

	// Emails are sanitized here, all illegal characters are taken out of the string.
	$lowerEmail = filter_var(strtolower($row[2]), FILTER_SANITIZE_EMAIL);

	// Check if email is unique or has been inserted before.
	if (in_array($lowerEmail, $allEmails)){

	// Add incorrect emails to another array to print them later all at once after 1 error message.
		array_push($notUniqueEmails, $lowerEmail);
	} 

	// Validate email before inserting, if not valid add to $invalidEmails array.
	else if (!filter_var($lowerEmail, FILTER_VALIDATE_EMAIL)) {
		array_push($invalidEmails, $lowerEmail);
	}
	// Names, surnames and emails have been validated and format corrected. Now insert them into the databse.
	else {
		$finalEmail = mysqli_real_escape_string($conn, $lowerEmail);
		$query = "INSERT INTO $tableName(name, surname, email)";
		$query .= " VALUES ('$finalName', '$finalSurname', '$finalEmail')";

		if ($dryRun===false){
			$result = mysqli_query($conn, $query);
				if (!$result){
				echo "Error inserting row into database: ".  $query . mysqli_error($conn) . "\n";
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