<?php
include 'db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hirely-adminDashboard</title>
</head>
<body>
    <h1>HEllo admin</h1>
    <form action="admin.php" method="POST">
        <label for="name" >Company Name:</label>
        <input type="text" id="name" name="name" required>
        <label for="description">Description</label>
        <input type="text" id="description" name="description" required>
        <label for="location">Location</label>
        <input type="text" id="location" name="location" required>
        <button type="submit" name="create_company">Create Company/Edit Company</button>
    </form>
</body>
</html>