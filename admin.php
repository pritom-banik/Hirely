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


<?php
// Keep all backend logic unchanged
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

if (isset($_POST['job-post'])) {
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

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hirely - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-fixed"
    style="background-image: url('images/bg.jpg'); background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="w-full max-w-4xl p-6">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-white mb-1">Welcome to Hirely</h1>
            <p class="text-white">Admin dashboard — manage your company and job circulars</p>
        </div>

        <div class="mx-auto bg-[#fef3c7]/90 p-8 rounded-lg shadow-md">
            <!-- Admin header -->
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">Hello,
                    <?php echo htmlspecialchars($name ?? 'Admin'); ?>
                </h2>
                <p class="text-sm text-gray-600">Company ID: <?php echo htmlspecialchars($c_id ?? 'Not linked'); ?></p>
            </div>

            <!-- Company info table -->
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
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-800 mb-2">Company Info</h3>
                <h5 class="bg-green-400 border p-2">SELECT * FROM company_admin WHERE admin_id = ?</h5>
                <table class="w-full table-auto border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Description</th>
                            <th class="px-4 py-2 text-left">Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="odd:bg-white even:bg-gray-50">
                            <td class="px-4 py-2"><?php echo htmlspecialchars($c_name); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($c_description); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($c_location); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>


            <!-- Company create/edit form -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-800 mb-3">
                    <?php echo ($c_id === null) ? 'Add Company' : 'Edit Company'; ?>
                </h3>
                <h5 class="bg-green-400 border p-2">INSERT INTO company (name, description, location) VALUES (?, ?, ?)
                </h5>
                <h5 class="bg-green-400 border p-2">UPDATE company SET name = ?, description = ?, location = ? WHERE
                    c_id = ?</h5>
                <form action="admin.php" method="POST" class="space-y-4">
                    <div>
                        <label for="name" class="block text-gray-700 mb-1">Company Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo $c_name ?>" required
                            class="w-full px-4 py-2 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a16207]" />
                    </div>
                    <div>
                        <label for="description" class="block text-gray-700 mb-1">Description</label>
                        <input type="text" id="description" name="description" value="<?php echo $c_description ?>"
                            required
                            class="w-full px-4 py-2 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a16207]" />
                    </div>
                    <div>
                        <label for="location" class="block text-gray-700 mb-1">Location</label>
                        <input type="text" id="location" name="location" value="<?php echo $c_location ?>" required
                            class="w-full px-4 py-2 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a16207]" />
                    </div>
                    <div>
                        <button type="submit" name="create_company"
                            class="w-full bg-[#a16207] text-white py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                            <?php if ($c_id === null) {
                                echo "Add company";
                            } else {
                                echo "Edit company";
                            } ?>
                        </button>
                    </div>
                </form>
            </div>


            <!-- Job circulars list -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-800 mb-2">Job Circulars</h3>
                <table class="w-full table-auto border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 text-left">Title</th>
                            <th class="px-4 py-2 text-left">Description</th>
                            <th class="px-4 py-2 text-left">Deadline</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        //displaying job circulars
                        $job_circulars = "SELECT * FROM job_circular WHERE c_id = $c_id";
                        $result = $connection->query($job_circulars);

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr class='odd:bg-white even:bg-gray-50'>";
                                echo "<td class='px-4 py-2'>" . htmlspecialchars($row['title']) . "</td>";
                                echo "<td class='px-4 py-2'>" . htmlspecialchars($row['description']) . "</td>";
                                echo "<td class='px-4 py-2'>" . htmlspecialchars($row['deadline']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td class='px-4 py-2' colspan='3'>No job circulars found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Job circular post form -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-800 mb-3">Post Job Circular</h3>
                <h5 class="bg-green-400 border p-2">INSERT INTO job_circular (c_id, title, description, deadline) VALUES
                    (?, ?, ?, ?)</h5>
                <form action="admin.php" method="POST" class="space-y-4">
                    <div>
                        <label for="title" class="block text-gray-700 mb-1">Circular Title:</label>
                        <input type="text" id="title" name="title" required
                            class="w-full px-4 py-2 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a16207]" />
                    </div>
                    <div>
                        <label for="description" class="block text-gray-700 mb-1">Description</label>
                        <input type="text" id="description" name="description" required
                            class="w-full px-4 py-2 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a16207]" />
                    </div>
                    <div>
                        <label for="deadline" class="block text-gray-700 mb-1">Deadline</label>
                        <input type="date" id="deadline" name="deadline" required
                            class="w-full px-4 py-2 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a16207]" />
                    </div>
                    <div>
                        <button type="submit" name="job-post"
                            class="w-full bg-[#a16207] text-white py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                            Post Circular
                        </button>
                    </div>
                </form>
            </div>


            <!-- Job Applications Section -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-800 mb-3">All Job Applications</h3>
                <h5 class="bg-green-400 border p-2">
                    SELECT
                    u.u_id,
                    u.email AS email,
                    u.name AS name,
                    u.summary AS summery,
                    GROUP_CONCAT(DISTINCT s.name) AS skills,
                    GROUP_CONCAT(DISTINCT c2.name) AS p_company
                    FROM users u
                    <span class="font-bold">LEFT JOIN</span> skill s ON u.u_id = s.u_id
                    <span class="font-bold">LEFT JOIN</span> experience e ON u.u_id = e.u_id
                    <span class="font-bold">LEFT JOIN</span> company c2 ON e.c_id = c2.c_id
                    WHERE u.u_id IN <span class="font-medium"> (
                    SELECT DISTINCT a.u_id
                    FROM application a
                    INNER JOIN job_circular j ON a.j_id = j.j_id
                    WHERE j.c_id = ?
                    )
                    </span>
                    GROUP BY u.u_id, u.name, u.summary;
                </h5>

                <table class="w-full table-auto border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 text-left">Applicant Name</th>
                            <th class="px-4 py-2 text-left">Skills</th>
                            <th class="px-4 py-2 text-left">Experience</th>
                            <th class="px-4 py-2 text-left">Summary</th>
                            <th class="px-4 py-2 text-left">Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $application = $connection->prepare("
                SELECT 
                    u.u_id,
                    u.email AS email,
                    u.name AS name,
                    u.summary AS summery,
                    GROUP_CONCAT(DISTINCT s.name) AS skills,
                    GROUP_CONCAT(DISTINCT c2.name) AS p_company
                FROM users u
                LEFT JOIN skill s ON u.u_id = s.u_id
                LEFT JOIN experience e ON u.u_id = e.u_id
                LEFT JOIN company c2 ON e.c_id = c2.c_id
                WHERE u.u_id IN (
                    SELECT DISTINCT a.u_id
                    FROM application a
                    INNER JOIN job_circular j ON a.j_id = j.j_id
                    WHERE j.c_id = ?
                )
                GROUP BY u.u_id, u.name, u.summary;
            ");

                        $application->bind_param('i', $c_id);
                        $application->execute();
                        $result_a = $application->get_result();

                        if ($result_a->num_rows > 0) {
                            while ($a_row = $result_a->fetch_assoc()) {
                                echo "<tr class='odd:bg-white even:bg-gray-50'>";
                                echo "<td class='px-4 py-2'>" . htmlspecialchars($a_row['name']) . "</td>";
                                echo "<td class='px-4 py-2'>" . htmlspecialchars($a_row['skills']) . "</td>";
                                echo "<td class='px-4 py-2'>" . htmlspecialchars($a_row['p_company']) . "</td>";
                                echo "<td class='px-4 py-2'>" . htmlspecialchars($a_row['summery']) . "</td>";
                                echo "<td class='px-4 py-2'>" . htmlspecialchars($a_row['email']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='px-4 py-2 text-center text-gray-500'>No applications found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="overflow-x-auto mt-6">
    <?php
    // Fetch all applicants grouped by job circular (for this company)
    $application = $connection->prepare("
        SELECT 
            j.j_id,
            j.title AS job_title,
            u.u_id,
            u.email AS email,
            u.name AS name,
            u.summary AS summery,
            GROUP_CONCAT(DISTINCT s.name) AS skills,
            GROUP_CONCAT(DISTINCT c2.name) AS p_company
        FROM job_circular j
        JOIN application a ON j.j_id = a.j_id
        JOIN users u ON a.u_id = u.u_id
        LEFT JOIN skill s ON u.u_id = s.u_id
        LEFT JOIN experience e ON u.u_id = e.u_id
        LEFT JOIN company c2 ON e.c_id = c2.c_id
        WHERE j.c_id = ?
        GROUP BY j.j_id, u.u_id, j.title, u.name, u.summary, u.email
        ORDER BY j.j_id;
    ");

    $application->bind_param('i', $c_id);
    $application->execute();
    $result_a = $application->get_result();

    $current_job = null;

    if ($result_a->num_rows > 0) {
        while ($a_row = $result_a->fetch_assoc()) {
            // When a new job circular starts, print its header and table head
            if ($current_job !== $a_row['j_id']) {
                // Close previous table if not the first one
                if ($current_job !== null) {
                    echo "</tbody></table><br>";
                }

                // New job circular title section
                echo "
                <h2 class='text-xl font-semibold text-gray-800 mb-2 mt-4'>
                    Job Circular: " . htmlspecialchars($a_row['job_title']) . "
                </h2>
                <table class='w-full table-auto border-collapse mb-4'>
                    <thead>
                        <tr class='bg-gray-100'>
                            <th class='px-4 py-2 text-left'>Applicant Name</th>
                            <th class='px-4 py-2 text-left'>Skills</th>
                            <th class='px-4 py-2 text-left'>Experience</th>
                            <th class='px-4 py-2 text-left'>Summary</th>
                            <th class='px-4 py-2 text-left'>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                ";
                $current_job = $a_row['j_id'];
            }

            // Applicant rows
            echo "<tr class='odd:bg-white even:bg-gray-50'>";
            echo "<td class='px-4 py-2'>" . htmlspecialchars($a_row['name']) . "</td>";
            echo "<td class='px-4 py-2'>" . htmlspecialchars($a_row['skills'] ?? '—') . "</td>";
            echo "<td class='px-4 py-2'>" . htmlspecialchars($a_row['p_company'] ?? '—') . "</td>";
            echo "<td class='px-4 py-2'>" . htmlspecialchars($a_row['summery'] ?? '—') . "</td>";
            echo "<td class='px-4 py-2'>" . htmlspecialchars($a_row['email']) . "</td>";
            echo "</tr>";
        }

        // Close last table
        echo "</tbody></table>";
    } else {
        echo "<p class='text-gray-500 text-center'>No applications found for any job circular.</p>";
    }
    ?>
</div>



            <!-- Logout -->
            <div class="mt-4">
                <form action="admin.php" method="POST">
                    <button type="submit" name="logout"
                        class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition duration-200">Logout</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>