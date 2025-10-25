<?php
include 'db_connection.php';

//==============================
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

//==============================
//getting admin info
$admin_id = $_SESSION['admin_id'];
$stmt = $connection->prepare("SELECT * FROM company_admin WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $c_id = $row['c_id'] ?? null;
    $name = $row['name'];
    $email = $row['email'];
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hirely-adminDashboard</title>
</head>

<body>
    <h1>HEllo <?php echo $name ?></h1>
    <h3><?php echo $c_id ?></h3>
    <table>
        <th>
        <td>Name</td>
        <td>Descripton</td>
        <td>Location</td>
        </th>

        <?php
        //showing company info
        $c_name = $c_description = $c_location = 'Not found any';
        if ($c_id !== null) {
            $company_info = "SELECT * FROM company WHERE c_id = $c_id";
            $result = $connection->query($company_info);

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $c_name = $row['name'] ?? '';
                $c_description = $row['description'] ?? '';
                $c_location = $row['location'] ?? '';
            }
        }
        ?>
        <tr>
            <td><?php echo $c_name ?></td>
            <td><?php echo $c_description ?></td>
            <td><?php echo $c_location ?></td>
        </tr>
    </table>
    <form action="admin.php" method="POST">
        <label for="name">Company Name:</label>
        <input type="text" id="name" name="name" required>
        <label for="description">Description</label>
        <input type="text" id="description" name="description" required>
        <label for="location">Location</label>
        <input type="text" id="location" name="location" required>
        <button type="submit" name="create_company"><?php if ($c_id === null) {
            echo "Add company";
        } else {
            echo "Edit company";
        }
        ; ?></button>
    </form>
    <?php
    if (isset($_POST['create_company'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $location = $_POST['location'];

        if ($c_id === null) {
            // Insert new company
            $sql = "INSERT INTO company (name, description, location) VALUES (?, ?, ?)";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("sss", $name, $description, $location);

            if ($stmt->execute()) {
                // Get the new ==== company ID
                $new_c_id = $stmt->insert_id;

                echo "Company added successfully.";

                // Update company_admin table to link this admin to the new company
                $sql_admin = "UPDATE company_admin SET c_id = ? WHERE admin_id = ?";
                $stmt_admin = $connection->prepare($sql_admin);
                $stmt_admin->bind_param("ii", $new_c_id, $admin_id);

                if ($stmt_admin->execute()) {
                    echo " Admin updated with new company.";
                } else {
                    echo " Error updating admin: " . $stmt_admin->error;
                }

                $stmt_admin->close();

            } else {
                echo "Error adding company: " . $stmt->error;
            }

            $stmt->close();
        } else {
            // Update company
            $sql = "UPDATE company SET name = ?, description = ?, location = ? WHERE c_id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("sssi", $name, $description, $location, $c_id);

            if ($stmt->execute()) {
                echo "Company updated successfully.";
            } else {
                echo "Error updating company: " . $stmt->error;
            }

            $stmt->close();
        }
        header("Location: admin.php");
        exit();

    }
    ?>

    <table>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Deadline</th>
        </tr>
        <?php
        //displaying job circulars
        $job_circulars = "SELECT * FROM job_circular WHERE c_id = $c_id";
        $result = $connection->query($job_circulars);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                echo "<td>" . htmlspecialchars($row['deadline']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No job circulars found.</td></tr>";
        }
        
        ?>
    </table>

    <!-- job circular post form -->

    <form action="admin.php" method="POST">
        <label for="title">Circular Title:</label>
        <input type="text" id="title" name="title" required>
        <label for="description">Description</label>
        <input type="text" id="description" name="description" required>
        <label for="deadline">Deadline</label>
        <input type="date" id="deadline" name="deadline" required>
        <button type="submit" name="job-post">post circular</button>
    </form>
    <?php
    if(isset($_POST['job-post'])){
        $title = $_POST['title'];
        $description = $_POST['description'];
        $deadline = $_POST['deadline'];

        $sql = "INSERT INTO job_circular (c_id, title, description, deadline) VALUES (?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("isss", $c_id, $title, $description, $deadline);

        if ($stmt->execute()) {
            echo "Job circular posted successfully.";
        } else {
            echo "Error posting job circular: " . $stmt->error;
        }

        $stmt->close();
        header("Location: admin.php");
        exit();
    }
    ?>



    <!-- logout form -->
    <form action="admin.php" method="POST">
        <button type="submit" name="logout">Logout</button>
    </form>

</body>

</html>


<?php
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>