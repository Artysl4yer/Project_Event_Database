<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style2.css">
        <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
        <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
    </head>
    <body>
        <div class="title-container">
            Pamantasan ng Lungsod ng Pasig
            <div class="tab-container">
                <div class="menu-items">
                    <a href="" class="active"> <i class="fa-solid fa-calendar"></i> <span class="label">Events </span> </a>
                    <a href="" class="active"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Admins </span> </a>
                    <a href="" class="active"> <i class="fa-solid fa-address-card"></i> <span class="label"> Register </span> </a>
                    <a href="#About" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
                    <a href="" class="active"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
                </div>
                <div class="logout">
                    <a href=""> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>
                </div>
            </div>
        </div>
        <div class="event-details">
            <div class="event-attendance-top">
                <p> Event List </p>
                <button class="btn-import" id="openModal"> Import Event</button>
                <div class="search-container">
                    <form class="example" actiion="action_page.php">
                        <label for="search"> </label>
                        <input type="text" id="search" name="fname" placeholder="Search...">
                        <button type="submit"><i class="fa fa-search"></i></button>
                    </form>
                </div>
            </div>
          
            <div class="event-list">
                <table>
                    <tr class="event-list-sets">
                        <th> Events: </th>
                    </tr>
                </table>
            </div>
        </div>

        <div id="importModal" class="modal">
            <div class="modal-content">
                <div class="header">
                    <h3> Create New Event </h3>
                    <p> Fill out the information below to get started </p>
                </div> 
                <form action="../php/event-sub.php" method="GET">
                    <div class="input-box">
                        <label for="event-title"> Event Title: </label>
                        <input type="text" name="event-title" required> 
                    </div>
                    <div class="input-box">
                        <label for="event-date"> Location: </label>
                        <input type="text" name="event-location" required> 
                    </div>
                    <div class="input-box">
                        <label for="event-orgs"> Organization: </label>
                        <input type="text" name="event-orgs" required> 
                    </div>
                    
                    <div class="input-box">
                        <label for="event-description"> Decription: </label>
                        <textarea id="description" name="event-description"></textarea>
                    </div>
                    <div class="input-box">
                        <label for="option"> 
                            <input type="checkbox" name="option"> 
                        </label>
                        <label for="option"> 
                            <input type="checkbox" name="option"> 
                        </label>
                        <label for="option"> 
                            <input type="checkbox" name="option"> 
                        </label>
                    </div>
                    <div class="controls">
                        <button class="btn-submit" type="submit">Submit</button>
                        <button class="btn-close"> Close </button>
                    </div>
                </form>
            </div>
        </div>
        <script src="../Javascript/popup.js"></script>
    </body>
</html>