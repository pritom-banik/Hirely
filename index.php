<?php
include 'db_connection.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hirely</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex flex-col items-center justify-center"
    style="background-image: url('images/bg.jpg'); background-size: cover; background-position: center;background-attachment: fixed;">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">Welcome to Hirely</h1>
        <p class="text-white">Your go-to platform for hiring top talent.</p>
    </div>
    <div class="main-form bg-[#fef3c7]/90 p-8 rounded-lg shadow-md w-96">

        <h2 class="text-2xl font-semibold mb-6 text-gray-800">Login</h2>
        <form action="index.php" method="post" class="space-y-4">
            <div>
                <label for="email" class="block text-gray-700 mb-2">Email:</label>
                <input type="email" id="email" name="email" required
                    class="w-full px-4 py-2 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label for="type" class="block text-gray-700 mb-2">Who you are:</label>
                <select id="type" name="type" required
                    class="w-full px-4 py-2 bg-green-100 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:border-blue-500 bg-white">
                    <option value="company_admin">Company Admin</option>
                    <option value="users">Job Seeker</option>
                </select>
            </div>

            <div>
                <label for="password" class="block text-gray-700 mb-2">Password:</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-2 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:border-blue-500">
            </div>

            <button type="submit"
                class="w-full bg-[#a16207] text-white py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                Login
            </button>
        </form>

        <div class="mt-6 text-center">
            <span class="text-gray-600">Don't have an account? </span>
            <a href="register.php" class="text-blue-500 hover:text-blue-600">Sign up here</a>
        </div>
    </div>
</body>

</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $type = $_POST['type'];
    $str = "SELECT * FROM $type WHERE email='$email' and password='$password'";
    $result = mysqli_query($connection, $str);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        if ($type == "company_admin") {
            $_SESSION['admin_id'] = $row['admin_id'];
            $_SESSION['user_type'] = "admin";

            header("Location: admin.php");
            exit;
        } else {
            $_SESSION['user_id'] = $row['u_id'];
            $_SESSION['user_type'] = "users";

            header("Location: users.php");
            exit;
        }
    } else {
        echo "<script>alert('Invalid email or password');</script>";
    }
}
?>