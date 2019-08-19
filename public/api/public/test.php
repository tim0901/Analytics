<?php

mysqli_report(MYSQLI_REPORT_STRICT);

$servername = "mysql";
$username = "Alex";
$pass ="password";
$databasename ="my_database";

//Create a connection
$connection = new mysqli($servername,$username,$pass,$databasename);
if($connection->connect_error){
    die("Connection failed: " . $connection->connect_error);
}

//Create a database
$sql = "CREATE DATABASE my_database";
if($connection->query($sql) === TRUE){
    echo "Database created successfully"."<br>";
}
else{
    echo ("Error creating database: " . $connection->error."<br>");
}


//Create a table
/*$sql = "CREATE TABLE MyGuests (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
firstname VARCHAR(30) NOT NULL,
lastname VARCHAR(30) NOT NULL,
email VARCHAR(50),
reg_date TIMESTAMP
)";*/
/*
if($connection->query($sql) === true){
    echo "Table MyGuests created successfully!"."<br>";
}
else{
    echo "Error creating table: ". $connection->error."<br>";
}
*/
//Prepare a Statement: can be executed multiple times with high efficiency
$statement = $connection->prepare("INSERT INTO MyGuests(firstname, lastname, email) VALUES (?,?,?)");
$statement->bind_param("sss",$firstname, $lastname, $email);


//Fill the table
$firstname = "John";
$lastname = "Smith";
$email = "JSmith@example.com";
$statement->execute();

$firstname = "Alex";
$lastname = "Richardson";
$email = "ap16135@qmul.ac.uk";
$statement->execute();

$firstname = "Peter";
$lastname = "Parker";
$email = "spider.man@hotmail.com";
$statement->execute();

$firstname = "Harry";
$lastname = "Potter";
$email = "thechosenone@hogwarts.net";
$statement->execute();

$statement->close();

//Output id of last inserted user
$last_id = $connection->insert_id;
echo $last_id." entries successfully entered. "."<br>";

//Select and display table on page
$sql = "SELECT id, firstname, lastname, email FROM MyGuests";
$result = $connection->query($sql);

if($result->num_rows > 0){
    //Output the data for each row
    while($row = $result->fetch_assoc()){
        echo "id: ".$row["id"]. " - Name: ".$row["firstname"]." ".$row["lastname"]." - Email: ".$row["email"]."<br>";
    }
}
else{
    echo "0 results.";
}
/*
//Delete table
$sql = "DROP TABLE MyGuests";
if($connection->query($sql) === true){
    echo "Table MyGuests deleted successfully!"."<br>";
}
else{
    echo "Error deleting table: ". $connection->error."<br>";
}
*/
$connection->close();