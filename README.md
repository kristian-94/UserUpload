# UserUpload

## Overview
This php script user_upload.php inserts rows from a csv file into an existing database.
The script creates the table users to accept the csv data.
The csv file consists of rows of name, surname, email. These are read, treated, and inserted into the table.
Outputs a list of invalid and non unique emails if any were found at the end of the program.

## Requirements
The csv file must have a header on the first row consisting of 'name, surname, email', with all subsequent rows following this format.

## Assumptions
- The database exists already at the host server, and is input with the -d directive.
- The script will always create the table 'users' if the table doesn't exist once connected to the database.
- The help section will be output whenever program terminates early, as well as when the --help directive is specified.
- Always displays server information once connected.
- If dry_run and --create_table are specified, will not create the table. dry_run takes priority.
- If no --file is specified will create table and end program.
- Removes all non-alpha numeric characters except ```'-.``` from the names.
- Collect and output all emails that were invalid.
- Collect and output emails that were not unique.
- Collect and output rows with wrong number of columns.


## Directives
This help section tells you all of the command line directives and what they do:
- --file [csv file name] ---> this is the name of the CSV to be parsed. eg. --file users.csv
- --create_table ---> this will cause the MySQL users table to be built and no further action will be taken.
- --dry_run ---> used with the --file directive. Runs the script and executes all functions but does not update the database.
- -u ---> MySQL username. eg. -u root
- -p ---> MySQL password eg. -p mypassword
- -h ---> MySQL host eg. -h localhost
- -d ---> MySQL database name eg. -d usersDatabase
- --help ---> Outputs this help screen, a list of all directives and their details.


# Example inputs

Directives are specified by writing the input tag, space, and the name for that directive:

```php user_upload.php -h localhost -d myDatabase -u root -p mypassword --file users.csv --dry_run```

Single dash directives can be input together, the order must follow the order of each tag:

```php user_upload.php -hudp localhost root myDatabase myPassword --file users.csv```



