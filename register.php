<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hirely</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Welcome to Hirely</h1>
            <p class="text-gray-600">Your go-to platform for hiring top talent.</p>
        </div>

        <?php if (!empty($message)) echo $message; ?>
        
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">Register</h2>
        <form action="register.php" method="post" class="space-y-4">
            <div>
                <label for="email" class="block text-gray-700 mb-2">Email:</label>
                <input type="email" id="email" name="email" required 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            
            <div>
                <label for="username" class="block text-gray-700 mb-2">Username:</label>
                <input type="text" id="username" name="username" required 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            
            <div>
                <label for="type" class="block text-gray-700 mb-2">Join as:</label>
                <select id="type" name="type" required 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500 bg-white">
                    <option value="company_admin">Company Admin</option>
                    <option value="users">Job Seeker</option>
                </select>
            </div>
            
            <div>
                <label for="password" class="block text-gray-700 mb-2">Password:</label>
                <input type="password" id="password" name="password" required 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            
            <button type="submit" 
                class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                Register
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <span class="text-gray-600">Already have an account? </span>
            <a href="index.php" class="text-blue-500 hover:text-blue-600">Login here</a>
        </div>
    </div>
</body>
</html>


<?php
include 'db_connection.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['username'];
    $type = $_POST['type'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    
        $stmt = "INSERT INTO $type (name, email, password) VALUES ($name, $email, $password)";

        $inserted = mysqli_query($connection, $stmt);
        if($inserted){
            $message = "<p class='text-green-500 text-center'>✅ Registration successful! You can now <a href='index.php' class='text-blue-500 underline'>log in</a>.</p>";
        } else {
            $message = "<p class='text-red-500 text-center'>❌ Error: " . htmlspecialchars($stmt->error) . "</p>";
        }
}
?>
