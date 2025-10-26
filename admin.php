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
                        );
                    </span>
                    <!-- GROUP BY u.u_id, u.name, u.summary; -->
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







            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-800 mb-3">User Grouped by Skills</h3>
                <h5 class="bg-green-400 border p-2">
                    SELECT s.name AS skill_name,
                    COUNT(DISTINCT s.u_id) AS applicant_count,
                    GROUP_CONCAT(DISTINCT u.name SEPARATOR ', ') AS applicants
                    FROM skill s
                    JOIN users u ON s.u_id = u.u_id
                    GROUP BY s.name
                    ORDER BY applicant_count DESC, s.name</h5>
                <h5 class="bg-green-400 border p-2">
                    SELECT u.u_id, u.name, u.email, u.summary,
                    GROUP_CONCAT(DISTINCT s2.name SEPARATOR ', ') AS all_skills
                    FROM users u
                    LEFT JOIN skill s2 ON u.u_id = s2.u_id
                    WHERE EXISTS (
                    SELECT 1 FROM skill s3 WHERE s3.u_id = u.u_id AND s3.name = ?
                    )
                    GROUP BY u.u_id
                    ORDER BY u.name
                </h5>
                <table class="w-full table-auto border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 text-left">Skill</th>
                            <th class="px-4 py-2 text-left">Applicants</th>
                            <th class="px-4 py-2 text-left">Applicant Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $skills_query = "
                SELECT 
                    s.name AS skill_name,
                    COUNT(DISTINCT s.u_id) AS applicant_count,
                    GROUP_CONCAT(DISTINCT u.name SEPARATOR ', ') AS applicants
                FROM skill s
                JOIN users u ON s.u_id = u.u_id
                GROUP BY s.name
                ORDER BY applicant_count DESC, s.name
            ";
                        $skills_result = $connection->query($skills_query);

                        if ($skills_result && $skills_result->num_rows > 0) {
                            // prepared statement to fetch user details for a given skill
                            $user_stmt = $connection->prepare("
                    SELECT u.u_id, u.name, u.email, u.summary,
                           GROUP_CONCAT(DISTINCT s2.name SEPARATOR ', ') AS all_skills
                    FROM users u
                    LEFT JOIN skill s2 ON u.u_id = s2.u_id
                    WHERE EXISTS (
                        SELECT 1 FROM skill s3 WHERE s3.u_id = u.u_id AND s3.name = ?
                    )
                    GROUP BY u.u_id
                    ORDER BY u.name
                ");

                            while ($skill_row = $skills_result->fetch_assoc()) {
                                $skill_name = $skill_row['skill_name'];
                                echo "<tr class='odd:bg-white even:bg-gray-50'>";
                                echo "<td class='px-4 py-2'>" . htmlspecialchars($skill_name) . "</td>";
                                echo "<td class='px-4 py-2'>" . intval($skill_row['applicant_count']) . "</td>";

                                $details_html = "";
                                if ($user_stmt) {
                                    $user_stmt->bind_param("s", $skill_name);
                                    $user_stmt->execute();
                                    $user_res = $user_stmt->get_result();
                                    if ($user_res && $user_res->num_rows > 0) {
                                        $details_html .= "<div class='space-y-2'>";
                                        while ($u = $user_res->fetch_assoc()) {
                                            $details_html .= "<div class='p-2 bg-white rounded border'>";
                                            $details_html .= "<div class='font-semibold text-gray-800'>" . htmlspecialchars($u['name']) . " <span class='text-sm text-gray-500'>(" . htmlspecialchars($u['email']) . ")</span></div>";
                                            $details_html .= "<div class='text-sm text-gray-600'>Summary: " . htmlspecialchars($u['summary']) . "</div>";
                                            $details_html .= "<div class='text-sm text-gray-600'>Skills: " . htmlspecialchars($u['all_skills']) . "</div>";
                                            $details_html .= "</div>";
                                        }
                                        $details_html .= "</div>";
                                    } else {
                                        $details_html = "<div class='text-sm text-gray-500'>No users found</div>";
                                    }
                                    $user_res->free_result();
                                } else {
                                    $details_html = "<div class='text-sm text-gray-500'>Query error</div>";
                                }

                                echo "<td class='px-4 py-2'>" . $details_html . "</td>";
                                echo "</tr>";
                            }

                            if ($user_stmt)
                                $user_stmt->close();
                        } else {
                            echo "<tr><td colspan='3' class='px-4 py-2 text-center text-gray-500'>No skills found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
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