
<?php
// $host = 'localhost'; 
// $dbname = 'my_db'; 
// $user = 'postgres'; 
// $password = '1234'; 

// $conn_string = "host=$host dbname=$dbname user=$user password=$password";
// $dbconn = pg_connect($conn_string);
?>


<?php
$host = "localhost";
$dbname = "my_db";
$user = "postgres";
$password = "1234";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
