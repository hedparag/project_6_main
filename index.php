<?php echo "Initial page" ?>
<h2>abcd</h2>



Error fetching employee: SQLSTATE[22P02]: Invalid text representation: 7 ERROR: invalid input syntax for type bigint: "22'" CONTEXT: unnamed portal parameter $1 = '...'

Fatal error: Uncaught PDOException: SQLSTATE[22P02]: Invalid text representation: 7 ERROR: invalid input syntax for type bigint: "22'" CONTEXT: unnamed portal parameter $1 = '...' in C:\xampp\htdocs\project_6_main\approve_employee.php:17 Stack trace: #0 C:\xampp\htdocs\project_6_main\approve_employee.php(17): PDOStatement->execute(Array) #1 {main} thrown in C:\xampp\htdocs\project_6_main\approve_employee.php on line 17


Fatal error: Uncaught PDOException: SQLSTATE[22P02]: Invalid text representation: 7 ERROR: invalid input syntax for type bigint: "22'" CONTEXT: unnamed portal parameter $1 = '...' in C:\xampp\htdocs\project_6_main\unapprove_employee.php:17 Stack trace: #0 C:\xampp\htdocs\project_6_main\unapprove_employee.php(17): PDOStatement->execute(Array) #1 {main} thrown in C:\xampp\htdocs\project_6_main\unapprove_employee.php on line 1

