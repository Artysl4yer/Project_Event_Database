<?php
session_start();
if (!isset($_SESSION['email']) && !isset($_SESSION['client_id'])) {
    header('Location: 1_Login.php');
    exit();
}

// Check if user is logged in and is a student
if (!isset($_SESSION['email'], $_SESSION['student_id'], $_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../pages/1_Login.php");
    exit();
}

include '../php/conn.php';

$student_id = $_SESSION['student_id'];
$event_id = isset($_GET['event']) ? $_GET['event'] : null;

// Get event details
$event_details = null;
if ($event_id) {
    $stmt = $conn->prepare("SELECT * FROM event_table WHERE number = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event_details = $result->fetch_assoc();

    // Check if survey already submitted
    $stmt = $conn->prepare("SELECT * FROM event_surveys WHERE event_number = ? AND student_id = ?");
    $stmt->bind_param("is", $event_id, $student_id);
    $stmt->execute();
    $survey_result = $stmt->get_result();
    
    if ($survey_result->num_rows > 0) {
        $survey_submitted = true;
        $survey_data = $survey_result->fetch_assoc();
    } else {
        $survey_submitted = false;
        $survey_data = null;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Event Survey</title>
    <link rel="stylesheet" href="../styles/style1.css">
    <link rel="stylesheet" href="../styles/style6.css">
    <link rel="stylesheet" href="../styles/survey.css">
    <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="title-container">
        <img src="../images-icon/plplogo.png" alt="PLP Logo">
        <h1>Pamantasan ng Lungsod ng Pasig</h1>
    </div>
    
    <div class="tab-container">
        <div class="menu-items">
            <a href="student-profile.php"><i class="fa-regular fa-circle-user"></i><span class="label">Profile</span></a>
            <a href="student-home.php"><i class="fa-solid fa-home"></i><span class="label">Home</span></a>
            <a href="student-attendance.php"><i class="fa-solid fa-qrcode"></i><span class="label">Scan QR</span></a>
            <a href="5_About.php"><i class="fa-solid fa-circle-info"></i><span class="label">About</span></a>
        </div>
        <div class="logout">
            <a href="../php/1logout.php" onclick="return confirm('Are you sure you want to logout?');"><i class="fa-solid fa-right-from-bracket"></i><span class="label">Logout</span></a>
        </div>
    </div>

    <div class="main-container">
        <?php if ($event_details): ?>
        <div class="survey-container">
            <div class="event-info">
                <h2><?php echo htmlspecialchars($event_details['event_title']); ?></h2>
                <p class="event-description"><?php echo htmlspecialchars($event_details['event_description']); ?></p>
            </div>

            <?php if ($survey_submitted): ?>
            <div class="survey-submitted">
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <h3>Thank You for Your Feedback!</h3>
                    <p>You have already submitted your survey for this event.</p>
                </div>
                <div class="previous-responses">
                    <h4>Your Responses</h4>
                    <div class="rating-summary">
                        <div class="rating-item">
                            <label>Overall Rating:</label>
                            <div class="stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $survey_data['overall_rating'] ? 'active' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="rating-item">
                            <label>Content Rating:</label>
                            <div class="stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $survey_data['content_rating'] ? 'active' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="rating-item">
                            <label>Speaker Rating:</label>
                            <div class="stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $survey_data['speaker_rating'] ? 'active' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="rating-item">
                            <label>Venue Rating:</label>
                            <div class="stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $survey_data['venue_rating'] ? 'active' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    <div class="feedback-text">
                        <h4>Your Feedback:</h4>
                        <p><?php echo htmlspecialchars($survey_data['feedback']); ?></p>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <form id="surveyForm" class="survey-form" action="../php/process_survey.php" method="POST">
                <input type="hidden" name="event_number" value="<?php echo $event_id; ?>">
                <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                
                <div class="rating-group">
                    <div class="rating-item">
                        <label>Overall Event Rating</label>
                        <div class="star-rating" data-rating="overall_rating">
                            <?php for($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="overall-star<?php echo $i; ?>" name="overall_rating" value="<?php echo $i; ?>" />
                            <label for="overall-star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="rating-item">
                        <label>Content Quality</label>
                        <div class="star-rating" data-rating="content_rating">
                            <?php for($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="content-star<?php echo $i; ?>" name="content_rating" value="<?php echo $i; ?>" />
                            <label for="content-star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="rating-item">
                        <label>Speaker/Presenter Quality</label>
                        <div class="star-rating" data-rating="speaker_rating">
                            <?php for($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="speaker-star<?php echo $i; ?>" name="speaker_rating" value="<?php echo $i; ?>" />
                            <label for="speaker-star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="rating-item">
                        <label>Venue & Organization</label>
                        <div class="star-rating" data-rating="venue_rating">
                            <?php for($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="venue-star<?php echo $i; ?>" name="venue_rating" value="<?php echo $i; ?>" />
                            <label for="venue-star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <div class="feedback-group">
                    <label for="feedback">Additional Feedback</label>
                    <textarea id="feedback" name="feedback" rows="5" placeholder="Please share your thoughts about the event..."></textarea>
                </div>

                <button type="submit" class="submit-btn">Submit Survey</button>
            </form>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="error-message">
            <p>No event selected or event not found.</p>
            <a href="student-home.php" class="back-btn">Back to Events</a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const starRatings = document.querySelectorAll('.star-rating');
            
            starRatings.forEach(container => {
                const stars = container.querySelectorAll('label');
                
                stars.forEach((star, index) => {
                    star.addEventListener('mouseover', () => {
                        stars.forEach((s, i) => {
                            if (i <= index) {
                                s.classList.add('hover');
                            } else {
                                s.classList.remove('hover');
                            }
                        });
                    });

                    star.addEventListener('mouseout', () => {
                        stars.forEach(s => s.classList.remove('hover'));
                    });
                });
            });

            const form = document.getElementById('surveyForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Validate all ratings are selected
                    const ratings = ['overall_rating', 'content_rating', 'speaker_rating', 'venue_rating'];
                    let valid = true;
                    
                    ratings.forEach(rating => {
                        if (!form.querySelector(`input[name="${rating}"]:checked`)) {
                            valid = false;
                            const container = form.querySelector(`[data-rating="${rating}"]`).parentNode;
                            container.classList.add('error');
                        }
                    });

                    if (!valid) {
                        alert('Please provide all ratings before submitting.');
                        return;
                    }

                    // Submit the form if all validations pass
                    form.submit();
                });
            }
        });
    </script>
</body>
</html> 