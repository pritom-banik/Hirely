<?php
include 'db_connection.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'users') {
    header("Location: index.php");
    exit();
}

//================
//fetch user data
$id = $_SESSION['user_id'];
$string = $connection->prepare("SELECT * FROM users WHERE u_id=?");
$string->bind_param("i", $id);
$string->execute();
$result = $string->get_result();
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $u_id = $row['u_id'];
    $name = $row['name'];
    $summery = $row['summary'];
    $email = $row['email'];
}
?>

<?php
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $summery = $_POST['summery'];
    $update_stmt = $connection->prepare("UPDATE users SET name=?, summary=? WHERE u_id=?");
    $update_stmt->bind_param("ssi", $name, $summery, $id);
    if ($update_stmt->execute()) {
        echo "Profile updated successfully.";
        // Optionally, refresh the page to show updated info
        header("Location: users.php");
        exit();
    } else {
        echo "Error updating profile: " . $connection->error;
    }
}
?>

<?php
//adding skills
if (isset($_POST['add-skills'])) {
    $skill = $_POST['skills'];
    $update_skills = $connection->prepare("INSERT INTO skill(u_id,name) VALUES(?,?)");
    $update_skills->bind_param("is", $id, $skill);
    if ($update_skills->execute()) {
        echo "Skill added successfully.";
        header("Location: users.php");
        exit();
    } else {
        echo "Error adding skill: " . $connection->error;
    }
}




//removing skills

if (isset($_POST['remove-skills'])) {
    $skill_id = $_POST['skill_id'];
    $stmt = $connection->prepare("DELETE FROM skill WHERE s_id = ?");
    $stmt->bind_param("i", $skill_id);
    $stmt->execute();
    header("Location: users.php");
    exit;
}
?>

<?php
//add exp==============
if (isset($_POST['add_experience'])) {
    $company_id = $_POST['experience'];
    $position = $_POST['role'];
    $add_experience_stmt = $connection->prepare("INSERT INTO experience (u_id, c_id, role) VALUES (?, ?, ?)");
    $add_experience_stmt->bind_param("iis", $id, $company_id, $position);
    if ($add_experience_stmt->execute()) {
        echo "Experience added successfully.";
        header("Location: users.php");
        exit();
    } else {
        echo "Error adding experience: " . $connection->error;
    }
}

//removing experience
if (isset($_POST['remove-exp'])) {
    $e_id = $_POST['e_id'];
    $stmt = $connection->prepare("DELETE FROM experience WHERE e_id = ?");
    $stmt->bind_param("i", $e_id);
    $stmt->execute();
    header("Location: users.php");
    exit;
}
?>

<?php
if (isset($_POST['apply'])) {
    $j_id = $_POST['j_id'];
    $apply = $connection->prepare("INSERT INTO application(j_id,u_id) VALUES(?,?)");
    $apply->bind_param("ii", $j_id, $id);
    if ($apply->execute()) {
        echo "applied successfully";
        header("Location: users.php");
        exit();
    } else {
        echo "Error to apply: " . $connection->error;
    }

}


//logout==========
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hirely - User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-fixed"
    style="background-image: url('images/bg.jpg'); background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="w-full max-w-4xl p-6">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-white mb-1">Welcome to Hirely</h1>
            <p class="text-white">User Dashboard — Manage your profile and applications</p>
        </div>

        <div class="mx-auto bg-[#fef3c7]/90 p-8 rounded-lg shadow-md">
            <!-- User Profile -->
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">Hello, <?php echo htmlspecialchars($name); ?>!</h2>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($summery); ?></p>
            </div>

            <!-- Profile Update Form -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-3">Update Profile</h3>
                <h5 class="bg-green-400 border p-2">SELECT * FROM users WHERE u_id=?</h5>
                <form action="users.php" method="POST" class="space-y-4">
                    <div>
                        <label for="name" class="block text-gray-700 mb-1">Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required
                            class="w-full px-4 py-2 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a16207]">
                    </div>
                    <div>
                        <label for="summery" class="block text-gray-700 mb-1">Profile Summary:</label>
                        <input type="text" id="summery" name="summery" value="<?php echo htmlspecialchars($summery); ?>"
                            class="w-full px-4 py-2 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a16207]">
                    </div>
                    <h5 class="bg-green-400 border p-2">UPDATE users SET name=?, summary=? WHERE u_id=?</h5>
                    <button type="submit" name="update_profile"
                        class="w-full bg-[#a16207] text-white py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                        Update Profile
                    </button>
                </form>
            </div>

            <!-- Skills Section -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-3">Skills</h3>
                <h5 class="bg-green-400 border p-2">SELECT * FROM skill WHERE u_id = ?</h5>
                <h5 class="bg-green-400 border p-2">DELETE FROM skill WHERE s_id = ?</h5>
                <table class="w-full table-auto border-collapse mb-4">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 text-left">Skill Name</th>
                            <th class="px-4 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $skill = $connection->prepare("SELECT * FROM skill WHERE u_id = ?");
                        $skill->bind_param("i", $id);
                        $skill->execute();
                        $skill_result = $skill->get_result();

                        while ($skill_row = $skill_result->fetch_assoc()) {
                            ?>
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($skill_row['name']); ?></td>
                                <td class="px-4 py-2 text-right">
                                    <form action="users.php" method="POST" class="inline">
                                        <input type="hidden" name="skill_id" value="<?php echo $skill_row['s_id']; ?>">
                                        <button type="submit" name="remove-skills"
                                            class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition duration-200">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <h5 class="bg-green-400 border p-2">INSERT INTO skill(u_id,name) VALUES(?,?)</h5>
                <form action="users.php" method="POST" class="flex gap-2">
                    <input type="text" id="skills" name="skills" placeholder="Enter new skill"
                        class="flex-1 px-4 py-2 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a16207]">
                    <button type="submit" name="add-skills"
                        class="bg-[#a16207] text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                        Add Skill
                    </button>
                </form>
            </div>

            <!-- Experience Section -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-3">Experience</h3>
                <h5 class="bg-green-400 border p-2">SELECT experience.e_id as e_id, company.name as name,
                    experience.role as role FROM experience JOIN company ON experience.c_id = company.c_id WHERE
                    experience.u_id = ?</h5>
                <table class="w-full table-auto border-collapse mb-4">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 text-left">Company Name</th>
                            <th class="px-4 py-2 text-left">Job Position</th>
                            <th class="px-4 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $find_exp = $connection->prepare("SELECT experience.e_id as e_id, company.name as name, experience.role as role FROM experience JOIN company ON experience.c_id = company.c_id WHERE experience.u_id = ?");
                        $find_exp->bind_param("i", $id);
                        $find_exp->execute();
                        $exp_result = $find_exp->get_result();
                        while ($exp_row = $exp_result->fetch_assoc()) {
                            ?>
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($exp_row['name']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($exp_row['role']); ?></td>
                                <td class="px-4 py-2 text-right">
                                    <form action="users.php" method="POST" class="inline">
                                        <input type="hidden" name="e_id" value="<?php echo $exp_row['e_id']; ?>">
                                        <button type="submit" name="remove-exp"
                                            class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition duration-200">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <form action="users.php" method="POST" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="experience" class="block text-gray-700 mb-1">Past Company:</label>
                            <select id="experience" name="experience" required
                                class="w-full px-4 py-2 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a16207]">
                                <?php
                                $company = $connection->prepare("SELECT * FROM company");
                                $company->execute();
                                $result_company = $company->get_result();
                                while ($c_row = mysqli_fetch_assoc($result_company)) {
                                    ?>
                                    <option value="<?php echo $c_row['c_id'] ?>"><?php echo $c_row['name'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div>
                            <label for="role" class="block text-gray-700 mb-1">Job Position:</label>
                            <input type="text" id="role" name="role" required
                                class="w-full px-4 py-2 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a16207]">
                        </div>
                    </div>
                    <button type="submit" name="add_experience"
                        class="w-full bg-[#a16207] text-white py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                        Add Experience
                    </button>
                </form>
            </div>

            <!-- Available Jobs Section -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-3">Available Jobs</h3>
                <h5 class="bg-green-400 border p-2">SELECT j.j_id as j_id, j.title as title, j.description as
                    description, j.deadline as deadline, c.name as c_name, c.c_id as c_id, c.location as location FROM
                    job_circular j JOIN company c where j.c_id=c.c_id</h5>
                <table class="w-full table-auto border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 text-left">Job Title</th>
                            <th class="px-4 py-2 text-left">Company</th>
                            <th class="px-4 py-2 text-left">Description</th>
                            <th class="px-4 py-2 text-left">Location</th>
                            <th class="px-4 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $job_list = $connection->prepare('SELECT j.j_id as j_id, j.title as title, j.description as description, j.deadline as deadline, c.name as c_name, c.c_id as c_id, c.location as location FROM job_circular j JOIN company c where j.c_id=c.c_id');
                        $job_list->execute();
                        $job = $job_list->get_result();
                        while ($row_job = mysqli_fetch_assoc($job)) {
                            ?>
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row_job['title']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row_job['c_name']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row_job['description']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row_job['location']); ?></td>
                                <td class="px-4 py-2 text-right">
                                    <form action="users.php" method="POST" class="inline">
                                        <input type="hidden" name="j_id" value="<?php echo $row_job['j_id']; ?>">
                                        <button type="submit" name="apply"
                                            class="bg-[#a16207] text-white px-4 py-1 rounded hover:bg-gray-700 transition duration-200">
                                            Apply Now
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>




            <!-- Job Search Section -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-3">Search Jobs</h3>
                <h5 class="bg-green-400 border p-2">
                    SELECT j.j_id, j.title, j.description, j.deadline, c.name as c_name,
                    c.location
                    FROM job_circular j
                    INNER JOIN company c ON j.c_id = c.c_id
                    WHERE company=? AND location=? AND title LIKE ?
                    GROUP BY j.j_id
                    HAVING COUNT(a.a_id) > 0
                    ORDER BY j.deadline DESC</h5>
                <form action="users.php" method="POST" class="space-y-4">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label for="company" class="block text-gray-700 mb-1">Company:</label>
                            <select id="company" name="company"
                                class="w-full px-4 py-2 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a16207]">
                                <option value="">All Companies</option>
                                <?php
                                $company_query = "SELECT DISTINCT c.name, c.c_id FROM company c 
                                    INNER JOIN job_circular j ON c.c_id = j.c_id";
                                $companies = $connection->query($company_query);
                                while ($company = $companies->fetch_assoc()) {
                                    echo "<option value='" . $company['c_id'] . "'>" . htmlspecialchars($company['name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="location" class="block text-gray-700 mb-1">Location:</label>
                            <select id="location" name="location"
                                class="w-full px-4 py-2 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a16207]">
                                <option value="">All Locations</option>
                                <?php
                                $location_query = "SELECT DISTINCT location FROM company WHERE location IS NOT NULL";
                                $locations = $connection->query($location_query);
                                while ($location = $locations->fetch_assoc()) {
                                    echo "<option value='" . $location['location'] . "'>" . htmlspecialchars($location['location']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="job_title" class="block text-gray-700 mb-1">Job Title:</label>
                            <input type="text" id="job_title" name="job_title" placeholder="Search by title"
                                class="w-full px-4 py-2 bg-green-100 border border-green-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a16207]">
                        </div>
                    </div>
                    <button type="submit" name="search"
                        class="w-full bg-[#a16207] text-white py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                        Search Jobs
                    </button>
                </form>

                <?php
                if (isset($_POST['search'])) {
                    $conditions = array();
                    $params = array();
                    $types = "";

                    if (!empty($_POST['company'])) {
                        $conditions[] = "c.c_id = ?";
                        $params[] = $_POST['company'];
                        $types .= "i";
                    }
                    if (!empty($_POST['location'])) {
                        $conditions[] = "c.location = ?";
                        $params[] = $_POST['location'];
                        $types .= "s";
                    }
                    if (!empty($_POST['job_title'])) {
                        $conditions[] = "j.title LIKE ?";
                        $params[] = "%" . $_POST['job_title'] . "%";
                        $types .= "s";
                    }

                    $sql = "SELECT j.j_id, j.title, j.description, j.deadline, c.name as c_name, c.location 
                FROM job_circular j 
                INNER JOIN company c ON j.c_id = c.c_id";

                    if (!empty($conditions)) {
                        $sql .= " WHERE " . implode(" AND ", $conditions);
                    }

                    $stmt = $connection->prepare($sql);
                    if (!empty($params)) {
                        $stmt->bind_param($types, ...$params);
                    }
                    $stmt->execute();
                    $search_results = $stmt->get_result();
                    ?>

                    <div class="mt-4">
                        <table class="w-full table-auto border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-2 text-left">Job Title</th>
                                    <th class="px-4 py-2 text-left">Company</th>
                                    <th class="px-4 py-2 text-left">Location</th>
                                    <th class="px-4 py-2 text-left">Deadline</th>
                                    <th class="px-4 py-2 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($search_results->num_rows > 0) {
                                    while ($job = $search_results->fetch_assoc()) {
                                        echo "<tr class='odd:bg-white even:bg-gray-50'>";
                                        echo "<td class='px-4 py-2'>" . htmlspecialchars($job['title']) . "</td>";
                                        echo "<td class='px-4 py-2'>" . htmlspecialchars($job['c_name']) . "</td>";
                                        echo "<td class='px-4 py-2'>" . htmlspecialchars($job['location']) . "</td>";
                                        echo "<td class='px-4 py-2'>" . htmlspecialchars($job['deadline']) . "</td>";
                                        echo "<td class='px-4 py-2 text-right'>";
                                        echo "<form action='users.php' method='POST' class='inline'>";
                                        echo "<input type='hidden' name='j_id' value='" . $job['j_id'] . "'>";
                                        echo "<button type='submit' name='apply' 
                                  class='bg-[#a16207] text-white px-4 py-1 rounded hover:bg-gray-700 transition duration-200'>
                                  Apply Now</button>";
                                        echo "</form>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='px-4 py-2 text-center text-gray-500'>No jobs found matching your criteria</td></tr>";
                                }
                                ?>
                            </tbody>
                            <div class="mt-4">
                                <h4 class="text-lg font-medium text-gray-800 mb-2">
                                    Search Results
                                    <span class="text-[#a16207]">(<?php echo $search_results->num_rows; ?> jobs
                                        found)</span>
                                </h4>
                                <h5 class="bg-green-400 border p-2 mb-4">
                                    SELECT COUNT(*) as total FROM (<?php echo $sql; ?>) as job_count
                                </h5>

                                <table class="w-full table-auto border-collapse">

                                </table>
                            </div>
                        <?php } ?>
                </div>









                <!-- Logout -->
                <div class="mt-4">
                    <form action="users.php" method="POST">
                        <button type="submit" name="logout"
                            class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition duration-200">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
</body>

</html>