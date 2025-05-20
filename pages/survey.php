<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="styles/1_survey.css">
    </head>
    <body>
    <div class="survey-container">
        <h1>Feedback Form</h1>

    <?php 
        if (isset($_GET['error'])) {
            echo '<p class="error">' . htmlspecialchars($_GET['error']) . '</p>';
    }
    ?>

    <?php 
        if (isset($_GET['success'])) {
            echo '<p class="success">' . htmlspecialchars($_GET['success']) . '</p>';
    }
    ?>

        <p class="success">success message</p>
        <form action="contact.php" method="post">
            <input type="text" id="name" name="name" placeholder="Full Name" required>
            <input type="text" id="email" name="email" placeholder="Email" required>
            <select name="course" id="course" required>
                <option value="">Select an organization</option>
                <option value="CCS">College of Computer Studies</option>
                <option value="CBA">College of Business and Accountancy</option>
                <option value="CON">College of Nursing</option>
                <option value="COE">College of Education</option>
                <option value="CIHM">College of International Hospitality Management</option>
                <option value="COA">College of Arts</option>
            </select>
            <textarea name="surveymsg" id="surveymsg" placeholder="Feedback Here"></textarea>
            <button type="submit">Send</button>
        </form>

    </div> 
    </body>
</html>