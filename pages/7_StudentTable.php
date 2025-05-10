<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../styles/style1.css">
    <link rel="stylesheet" href="../styles/style2.css">
    <link rel="stylesheet" href="../styles/style3.css">
    <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
    <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
</head>
    <body>
        <div class="title-container">
            <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
            <div class="tab-container">
                <div class="menu-items">
                    <a href="4_Event.php" class="active"> <i class="fa-solid fa-jome"></i> <span class="label"> Home </span> </a>
                    <a href="6_NewEvent.php" class="active"> <i class="fa-solid fa-calendar"></i> <span class="label">Events </span> </a>
                    <a href="" class="active"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Admins </span> </a>
                    <a href="" class="active"> <i class="fa-solid fa-address-card"></i> <span class="label"> Register </span> </a>
                    <a href="5_About.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
                    <a href="8_archive.php" class="active"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
                    <a href="1_Login.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> Login </span> </a>
                </div>
                <div class="logout">
                    <a href=""> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>
                </div>
            </div>
        </div>
        <div class="event-main">
             <div class="event-details">
                    <div class="event-top">
                        <p> Event List </p>
                        <div class="search-container">
                            <form class="example" action="action_page.php">
                                <label for="search"> </label>
                                <input type="text" id="search" name="fname" placeholder="Search...">
                                <button type="submit"><i class="fa fa-search"></i></button>
                            </form>
                        </div>
                       
                       
                        <div class="col-md-12" id="importFrm" style="display:block">
                            <form action="../php/importData.php" method="post" enctype="multipart/form-data">
                                <input type="file" name="file" />
                                <input type="submit" class="btn btn-primary" name="importSubmit" value="IMPORT">
                            </form>
                        </div>
                        
                    </div>  
            </div>
            <div class="event-table-section">
                <h2 style="margin-left: 20px;">Student Details</h2>
                    <?php
                        include '../php/conn.php';

                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
                            $id = intval($_POST['ID']);
                            $name = trim($_POST['Name']);
                            $course = trim($_POST['Course']);
                            $section = trim($_POST['Section']);
                            $gender = trim($_POST['Gender']);
                            $age = intval($_POST['Age']);
                            $year = trim($_POST['Year']);
                            $dept = trim($_POST['Dept']);

                            $stmt = $conn->prepare("UPDATE participants_table SET Name=?, Course=?, Section=?, Gender=?, Age=?, Year=?, Dept=? WHERE ID=?");
                            $stmt->bind_param("ssssissi", $name, $course, $section, $gender, $age, $year, $dept, $id);
                            $stmt->execute();
                            $stmt->close();
                        }

                        if (isset($_GET['delete_id'])) {
                            $delete_id = intval($_GET['delete_id']);
                            $stmt = $conn->prepare("DELETE FROM participants_table WHERE ID = ?");
                            $stmt->bind_param("i", $delete_id);
                            $stmt->execute();
                            $stmt->close();
                        }

                        $events = $conn->query("SELECT * FROM event_table");
                        while ($event = $events->fetch_assoc()) {
                            echo "<h2 style='margin-left: 20px;'>Event: " . htmlspecialchars($event['event_title']) . "</h2>";

                            $event_id = $event['number'];
                            $participants = $conn->query("SELECT * FROM participants_table WHERE event_id = $event_id");

                            if ($participants->num_rows > 0) {
                                echo "<table border='1' cellpadding='5' cellspacing='0' style='width: 100%;'>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Course</th>
                                        <th>Section</th>
                                        <th>Gender</th>
                                        <th>Age</th>
                                        <th>Year</th>
                                        <th>Department</th>
                                        <th>Actions</th>
                                    </tr>";
                                while ($row = $participants->fetch_assoc()) {
                                    echo "<tr>
                                            <form method='POST'>
                                                <input type='hidden' name='ID' value='" . $row['ID'] . "'>
                                                <td>" . $row['ID'] . "</td>
                                                <td><input type='text' name='Name' value='" . htmlspecialchars($row['Name']) . "'></td>
                                                <td><input type='text' name='Course' value='" . htmlspecialchars($row['Course']) . "'></td>
                                                <td><input type='text' name='Section' value='" . htmlspecialchars($row['Section']) . "'></td>
                                                <td><input type='text' name='Gender' value='" . htmlspecialchars($row['Gender']) . "'></td>
                                                <td><input type='number' name='Age' value='" . htmlspecialchars($row['Age']) . "'></td>
                                                <td><input type='text' name='Year' value='" . htmlspecialchars($row['Year']) . "'></td>
                                                <td><input type='text' name='Dept' value='" . htmlspecialchars($row['Dept']) . "'></td>
                                                <td>
                                                    <button type='submit' name='update'>Update</button>
                                                </td>
                                             </form>
                                            </tr>";
                                }
                                echo "</table><br>";
                            } else {
                                echo "<p style='margin-left: 20px;'>No participants registered for this event.</p><br>";
                            }
                        }
                        $conn->close();
                    ?>
            </div>
        </div>
    </body>
</html>
