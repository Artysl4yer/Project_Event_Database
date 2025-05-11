<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../styles/style1.css">
    <link rel="stylesheet" href="../styles/style2.css">
    <link rel="stylesheet" href="../styles/style3.css">
    <link rel="stylesheet" href="../styles/style5.css">
    <link rel="stylesheet" href="../styles/style6.css">
    <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
    <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
</head>
    <body>
        <div class="title-container">
            <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
            <div class="tab-container">
                <div class="menu-items">
                    <a href="4_Event.php" class="active"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
                    <a href="6_NewEvent.php" class="active"> <i class="fa-solid fa-calendar"></i> <span class="label">Events </span> </a>
                    <a href="" class="active"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Admins </span> </a>
                    <a href="7_StudentTable.php" class="active"> <i class="fa-solid fa-address-card"></i> <span class="label"> Participants </span> </a>
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
            <!-- The Event List. The compilation of events, sort to newest to latest -->
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
                    <h2>Participants</h2>
                    <div class="add-button">
                        <button class="btn-import" id="openModal" onclick="openModal()">  <span>  ADD <i class="fa-solid fa-plus"></i>  </span></button>
                    </div>
                    <table class="event-display-table">
                        <tr>
                            <th>Number</th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Location</th>
                            <th>Gender</th>
                            <th>Age</th>   
                            <th>Year</th>
                            <th>Dept</th>
                        </tr>
                        <?php

                        include '../php/conn.php';
                        $sql = "SELECT * FROM participants_table ORDER BY number DESC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                                
                        ?>
                        <tr>
                            <td><?= $row['number'] ?></td>
                            <td><?= $row['ID'] ?></td>
                            <td><?= htmlspecialchars($row['Name']) ?></td>
                            <td><?= htmlspecialchars($row['Course'])?></td>
                            <td><?= htmlspecialchars($row['Section']) ?></td>
                            <td><?= htmlspecialchars($row['Gender']) ?></td>
                            <td><?= $row['Age'] ?></td>
                            <td><?= htmlspecialchars($row['Year']) ?></td>
                            <td><?= htmlspecialchars($row['Dept']) ?></td>
                            <td><a class="edit-btn" href="#" onclick="openEditModal(<?= $row['number'] ?>)">Edit</a></td>
                            <td>
                                <form method="POST" action="../php/delete_event.php" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                    <input type="hidden" name="delete_id" value="<?= $row['number'] ?>">
                                    <button type="submit" name="delete" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                            else:
                        ?>
                        <tr><td colspan="8">No events found.</td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>
