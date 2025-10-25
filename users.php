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
if(isset($_POST['apply'])){
    $j_id=$_POST['j_id'];
    $apply=$connection->prepare("INSERT INTO application(j_id,u_id) VALUES(?,?)");
    $apply->bind_param("ii",$j_id,$id);
    if($apply->execute()){
        echo "applied successfully";
        header("Location: users.php");
        exit();
    }else{
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
    <title>Document</title>
</head>

<body>
    <h1>Hello, <?php echo $name ?>!</h1>
    <h2><?php echo $summery ?></h2>

    <form action="users.php" method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo $name ?>" required><br><br>
        <label for="summery">Profile summery</label>
        <input type="text" id="summery" name="summery" value="<?php echo $summery ?>"><br><br>
        <button type="submit" name="update_profile">update_profile</button>
    </form>


    <table>
        <tr>
            <th>Skills</th>
        </tr>
        <?php
        $skill = $connection->prepare("SELECT * FROM skill WHERE u_id = ?");
        $skill->bind_param("i", $id);
        $skill->execute();
        $skill_result = $skill->get_result();

        while ($skill_row = $skill_result->fetch_assoc()) {
            ?>
            <tr>
                <td>
                    <?php echo htmlspecialchars($skill_row['name']); ?>
                </td>
                <td>
                    <form action="users.php" method="POST">
                        <input type="hidden" name="skill_id" value="<?php echo $skill_row['s_id']; ?>">
                        <button type="submit" name="remove-skills">Remove</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>
    <form action="users.php" method="POST">
        <label for="skills">Skills</label>
        <input type="text" id="skills" name="skills">
        <button type="submit" name="add-skills">Add Skill</button>
    </form>

    <table>
        <tr>
            <th>Experience</th>
        </tr>
        <tr>
            <th>company name</th>
            <th>Job Position</th>
        </tr>
        <?php
        $find_exp = $connection->prepare("SELECT experience.e_id as e_id,company.name as name, experience.role as role FROM experience JOIN company ON experience.c_id = company.c_id WHERE experience.u_id = ?");
        $find_exp->bind_param("i", $id);
        $find_exp->execute();
        $exp_result = $find_exp->get_result();
        while ($exp_row = $exp_result->fetch_assoc()) {
            ?>
            <tr>
                <td><?php echo htmlspecialchars($exp_row['name']); ?></td>
                <td><?php echo htmlspecialchars($exp_row['role']); ?></td>
                <td>
                    <form action="users.php" method="POST">
                        <input type="hidden" name="e_id" value="<?php echo $exp_row['e_id']; ?>">
                        <button type="submit" name="remove-exp">Remove</button>
                    </form>
                </td>
            </tr>
            ?>
        <?php } ?>
    </table>


    <?php
    $company = $connection->prepare("SELECT * FROM company");
    $company->execute();
    $result_company = $company->get_result();
    ?>
    <form action="users.php" method="POST">
        <h1>Add Experience</h1>
        <label for="experience">Past company</label>
        <select id="experience" name="experience">
            <?php
            while ($c_row = mysqli_fetch_assoc($result_company)) {
                // You can use $c_row to display company options here
                ?>
                <option value="<?php echo $c_row['c_id'] ?>"><?php echo $c_row['name'] ?></option>
            <?php } ?>
        </select>
        <label for="role">Job Position</label>
        <input type="text" id="role" name="role">
        <button type="submit" name="add_experience">add one more experience</button>
    </form>

    

    <table>
        <tr>
            <th>Job Title</th>
            <th>Company</th>
            <th>Description</th>
            <th>Location</th>
            <th>Apply</th>
        </tr>
        <?php
        $job_list=$connection->prepare('SELECT j.j_id as j_id,j.title as title,j.description as description,j.deadline as deadline,c.name as c_name,c.c_id as c_id,c.location as location FROM job_circular j JOIN company c where j.c_id=c.c_id');
        $job_list->execute();
        $job=$job_list->get_result();
        while($row_job=mysqli_fetch_assoc($job)){
        ?>
        <tr>
            <td><?php echo $row_job['title']?></td>
            <td><?php echo $row_job['c_name']?></td>
            <td><?php echo $row_job['description']?></td>
            <td><?php echo $row_job['location']?></td>
            <td><form action="users.php" method="POST">
                <input type="hidden" name="j_id" value="<?php echo $row_job['j_id']; ?>">
                <button type="submit" name="apply">Apply Now</button>
            </form>
            </td>
        </tr>
        <?php } ?>
    </table>
            <form action="admin.php" method="POST">
                    <button type="submit" name="logout">Logout</button>
                </form>
</body>

</html>