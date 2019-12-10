<html>
<body>

Welcome <br>
Email is registed in emlaucher as : <?php echo $_POST["email"]; ?>

</body>
</html>

<?php
$email = $_POST["email"];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "emlauncher";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "INSERT INTO user_pass (mail) VALUES ('$email')";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>