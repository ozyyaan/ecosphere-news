<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];

    $query = "INSERT INTO users (username, password, email, role) 
              VALUES ('$username', '$password', '$email', 'user')";
    
    if (mysqli_query($koneksi, $query)) {
        header('Location: login.php');
    } else {
        $error = "Registration failed";
    }
}
?>

<form method="POST">
    <input type="text" name="username" required>
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <button type="submit">Register</button>
</form>