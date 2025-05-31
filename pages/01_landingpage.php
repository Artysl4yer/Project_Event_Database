<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fix the path to config.php
$config_path = __DIR__ . '/../config.php';
if (!file_exists($config_path)) {
    die("Configuration file not found at: " . $config_path);
}
include $config_path;

// Test database connection
if ($conn === false) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Test if we can query the event table
$test_query = "SELECT COUNT(*) as count FROM event_table";
$result = $conn->query($test_query);
if (!$result) {
    die("Error accessing event table: " . $conn->error);
}

// Debug information
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get events based on filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$department_filter = isset($_GET['department']) ? $_GET['department'] : 'all';
$search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$query = "SELECT *, 
          CASE 
              WHEN event_image IS NOT NULL AND event_image != '' 
              THEN CONCAT('../uploads/events/', event_image)
              ELSE '../images-icon/plm_courtyard.png'
          END as image_path 
          FROM event_table WHERE 1=1";

if ($status_filter !== 'all') {
    // Map the filter status to database status
    $status_mapping = [
        'Ongoing' => 'ongoing',
        'Upcoming' => 'scheduled',
        'Finished' => 'completed'
    ];
    
    if (isset($status_mapping[$status_filter])) {
        $query .= " AND event_status = '" . $conn->real_escape_string($status_mapping[$status_filter]) . "'";
    }
}

if ($department_filter !== 'all') {
    $query .= " AND organization = '" . $conn->real_escape_string($department_filter) . "'";
}

if (!empty($search_query)) {
    $query .= " AND (event_title LIKE '%$search_query%' 
                OR event_description LIKE '%$search_query%' 
                OR event_location LIKE '%$search_query%' 
                OR organization LIKE '%$search_query%')";
}

$query .= " ORDER BY date_start ASC, event_start ASC";
$result = $conn->query($query);
$events = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Verify if the image file exists
        if ($row['event_image'] && file_exists(__DIR__ . '/../uploads/events/' . $row['event_image'])) {
            $row['image_path'] = '../uploads/events/' . $row['event_image'];
        } else {
            $row['image_path'] = '../images-icon/plm_courtyard.png';
        }
        $events[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style2.css">
        <link rel="stylesheet" href="../styles/style3.css">
        <link rel="stylesheet" href="../styles/style11.css">
        <link rel="stylesheet" href="../styles/landingpage.css">
        <title>PLP: Events</title>
        <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
        <style>
            .carousel-container {
                position: relative;
                max-width: 1200px;
                margin: 40px auto;
                overflow: hidden;
                padding: 0 20px;
                z-index: 1;
            }
            
            .carousel-track {
                padding-top: 40px;
                padding-bottom: 15px;
                display: flex;
                transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
                will-change: transform;
                gap: 30px;
                touch-action: pan-y pinch-zoom;
            }
            
            .carousel-slide {
                flex: 0 0 calc(100% - 60px);
                padding: 0;
                box-sizing: border-box;
            }
            
            .event-card {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                overflow: hidden;
                height: 600px;
                display: flex;
                flex-direction: column;
                cursor: pointer;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                position: relative;
                transform-origin: center;
            }
            
            .event-card:hover {
                transform: translateY(-10px) scale(1.02);
                box-shadow: 0 12px 30px rgba(0,0,0,0.2);
            }
            
            .event-image-container {
                position: relative;
                width: 100%;
                height: 400px;
                overflow: hidden;
                border-radius: 12px 12px 0 0;
                background: #000;
            }
            
            .event-image-container::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(to bottom, 
                    rgba(0,0,0,0) 0%,
                    rgba(0,0,0,0.2) 50%,
                    rgba(0,0,0,0.8) 100%);
                z-index: 1;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            .event-card:hover .event-image-container::after {
                opacity: 1;
            }
            
            .event-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
                display: block;
                transform-origin: center;
            }
            
            .event-card:hover .event-image {
                transform: scale(1.1);
            }
            
            .event-content {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                padding: 25px;
                background: linear-gradient(to top, 
                    rgba(0,0,0,0.9) 0%,
                    rgba(0,0,0,0.7) 50%,
                    rgba(0,0,0,0) 100%);
                color: white;
                z-index: 2;
                transform: translateY(0);
                transition: transform 0.3s ease;
            }
            
            .event-card:hover .event-content {
                transform: translateY(-10px);
            }
            
            .event-title {
                font-size: 1.8em;
                margin: 0 0 15px 0;
                color: white;
                line-height: 1.3;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            }
            
            .event-date {
                color: rgba(255,255,255,0.9);
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 0.95em;
                margin-bottom: 10px;
            }
            
            .event-status {
                display: inline-block;
                padding: 8px 15px;
                border-radius: 20px;
                font-size: 0.9em;
                font-weight: 500;
                margin: 5px 0;
                backdrop-filter: blur(5px);
                background: rgba(255,255,255,0.2);
                color: white;
                border: 1px solid rgba(255,255,255,0.3);
            }
            
            .status-ongoing {
                background: rgba(33, 150, 243, 0.3);
                border-color: rgba(33, 150, 243, 0.5);
            }
            
            .status-upcoming {
                background: rgba(46, 125, 50, 0.3);
                border-color: rgba(46, 125, 50, 0.5);
            }
            
            .status-finished {
                background: rgba(158, 158, 158, 0.3);
                border-color: rgba(158, 158, 158, 0.5);
            }
            
            .event-description {
                color: rgba(255,255,255,0.9);
                font-size: 0.95em;
                line-height: 1.5;
                margin: 10px 0;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
                text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
            }
            
            .event-details {
                display: flex;
                flex-direction: column;
                gap: 8px;
                margin-top: 15px;
                font-size: 0.9em;
                color: rgba(255,255,255,0.8);
            }
            
            .event-details p {
                display: flex;
                align-items: center;
                gap: 8px;
                margin: 0;
            }
            
            .event-details i {
                width: 16px;
                text-align: center;
            }
            
            .carousel-nav {
                display: none;
            }
            
            .carousel-dots {
                display: flex;
                justify-content: center;
                margin-top: 25px;
                gap: 12px;
            }
            
            .carousel-dot {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: #ddd;
                cursor: pointer;
                transition: all 0.3s ease;
                border: 2px solid transparent;
            }
            
            .carousel-dot:hover {
                background: #218838;
                transform: scale(1.2);
            }
            
            .carousel-dot.active {
                background: #218838;
                border-color: #fff;
                box-shadow: 0 0 0 2px #218838;
            }
            
            .carousel-progress {
                position: absolute;
                bottom: -5px;
                left: 0;
                width: 100%;
                height: 3px;
                background: #eee;
            }
            
            .carousel-progress-bar {
                height: 100%;
                background: #218838;
                width: 0%;
                transition: width 0.1s linear;
            }
            
            .event-filters {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                gap: 15px;
                margin: 20px auto;
                padding: 20px;
                max-width: 1000px;
                background: rgba(255, 255, 255, 0.95);
                border-radius: 30px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                position: relative;
                z-index: 100;
            }
            
            .filter-btn {
                padding: 12px 25px;
                border: 2px solid #218838;
                border-radius: 25px;
                background: transparent;
                color: #218838;
                cursor: pointer;
                transition: all 0.3s ease;
                font-weight: 600;
                font-size: 0.95em;
                min-width: 160px;
                text-align: center;
                white-space: nowrap;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }
            
            .filter-btn i {
                font-size: 1.1em;
            }
            
            .filter-btn:hover {
                background: rgba(33, 136, 56, 0.1);
                transform: translateY(-2px);
            }
            
            .filter-btn.active {
                background: #218838;
                color: white;
                box-shadow: 0 4px 15px rgba(33, 136, 56, 0.3);
            }
            
            .event-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.8);
                z-index: 1000;
                overflow-y: auto;
            }
            
            .event-modal-content {
                position: relative;
                background: white;
                width: 90%;
                max-width: 800px;
                margin: 50px auto;
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            }
            
            .close-modal {
                position: absolute;
                right: 20px;
                top: 20px;
                font-size: 24px;
                cursor: pointer;
                color: #666;
            }
            
            .event-modal-image-container {
                position: relative;
                width: 100%;
                height: 400px;
                overflow: hidden;
                border-radius: 12px;
                margin-bottom: 20px;
            }
            
            .event-modal-image {
                width: 100%;
                height: 400px;
                object-fit: cover;
                border-radius: 12px;
                margin-bottom: 20px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            
            .event-modal-title {
                font-size: 2em;
                margin-bottom: 15px;
                color: #333;
            }
            
            .event-modal-info {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 20px;
            }
            
            .event-modal-info-item {
                display: flex;
                align-items: center;
                gap: 10px;
                color: #666;
            }
            
            .event-modal-description {
                color: #444;
                line-height: 1.6;
                margin-bottom: 20px;
            }
            
            .event-modal-status {
                display: inline-block;
                padding: 8px 15px;
                border-radius: 20px;
                font-weight: 500;
                margin-bottom: 20px;
            }
            
            @media (max-width: 768px) {
                .event-filters {
                    padding: 15px;
                    gap: 10px;
                }
                
                .filter-btn {
                    min-width: 140px;
                    padding: 10px 20px;
                    font-size: 0.9em;
                }
                
                .carousel-container {
                    padding: 0 50px;
                }
                
                .carousel-nav {
                    width: 40px;
                    height: 40px;
                }
                
                .carousel-prev {
                    left: 5px;
                }
                
                .carousel-next {
                    right: 5px;
                }
                
                .event-image-container {
                    height: 300px;
                }
                
                .event-card {
                    height: 500px;
                }
                
                .event-title {
                    font-size: 1.5em;
                }
                
                .event-content {
                    padding: 20px;
                }
            }
            
            @media (max-width: 480px) {
                .event-filters {
                    flex-direction: column;
                    align-items: stretch;
                }
                
                .filter-btn {
                    width: 100%;
                }
                
                .carousel-container {
                    padding: 0 40px;
                }
                
                .carousel-nav {
                    width: 35px;
                    height: 35px;
                }
                
                .event-image-container {
                    height: 250px;
                }
                
                .event-card {
                    height: 450px;
                }
                
                .event-title {
                    font-size: 1.3em;
                }
                
                .event-content {
                    padding: 15px;
                }
            }
            
            /* Add loading state for images */
            .event-image-container::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, 
                    #1a1a1a 0%, 
                    #2a2a2a 50%, 
                    #1a1a1a 100%);
                background-size: 200% 100%;
                animation: loading 1.5s infinite;
                z-index: 1;
            }
            
            .event-image-container.loaded::before {
                display: none;
            }
            
            @keyframes loading {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }

            /* Add styles for search and filter container */
            .search-filter-container {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 20px;
                margin: 20px auto;
                padding: 20px;
                max-width: 1000px;
                background: rgba(255, 255, 255, 0.95);
                border-radius: 30px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                position: relative;
                z-index: 100;
                flex-wrap: wrap;
            }

            .search-form {
                display: flex;
                gap: 10px;
                flex: 1;
                min-width: 300px;
                max-width: 500px;
            }

            .search-input {
                flex: 1;
                padding: 12px 20px;
                border: 2px solid #218838;
                border-radius: 25px;
                font-size: 1em;
                outline: none;
                transition: all 0.3s ease;
            }

            .search-input:focus {
                box-shadow: 0 0 0 3px rgba(33, 136, 56, 0.2);
            }

            .search-btn {
                padding: 12px 25px;
                background: #218838;
                color: white;
                border: none;
                border-radius: 25px;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .search-btn:hover {
                background: #17692d;
                transform: translateY(-2px);
            }

            .department-filter {
                min-width: 250px;
            }

            .department-select {
                width: 100%;
                padding: 12px 20px;
                border: 2px solid #218838;
                border-radius: 25px;
                font-size: 1em;
                outline: none;
                cursor: pointer;
                background: white;
                transition: all 0.3s ease;
            }

            .department-select:focus {
                box-shadow: 0 0 0 3px rgba(33, 136, 56, 0.2);
            }

            @media (max-width: 768px) {
                .search-filter-container {
                    flex-direction: column;
                    padding: 15px;
                }

                .search-form {
                    width: 100%;
                    max-width: none;
                }

                .department-filter {
                    width: 100%;
                }

                .search-input,
                .department-select {
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <div class="title-container">
            <img src="../images-icon/plplogo.png"> <h1>Pamantasan ng Lungsod ng Pasig</h1>
        </div>
    
        <div class="image-background">
            <div class="image-background-dim"></div>
            <div class="image-content" id="banner">
                <h1>PLP EVENTS</h1>
                <div class="image-description">
                    <p>Welcome to Pamantasan ng Lungsod ng Pasig Updates</p>
                    <p>Get Up to date with the latest upcoming Events</p>
                </div>
            </div>
        </div>

        <!-- Add search and filter section before event filters -->
        <div class="search-filter-container">
            <form class="search-form" method="GET" action="">
                <input type="text" 
                       name="search" 
                       placeholder="Search events..." 
                       value="<?= htmlspecialchars($search_query) ?>"
                       class="search-input">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            
            <div class="department-filter">
                <select name="department" class="department-select" onchange="this.form.submit()">
                    <option value="all" <?= $department_filter === 'all' ? 'selected' : '' ?>>All Departments</option>
                    <option value="CCS" <?= $department_filter === 'CCS' ? 'selected' : '' ?>>College of Computer Studies</option>
                    <option value="CBA" <?= $department_filter === 'CBA' ? 'selected' : '' ?>>College of Business Administration</option>
                    <option value="CAS" <?= $department_filter === 'CAS' ? 'selected' : '' ?>>College of Arts and Sciences</option>
                    <option value="COE" <?= $department_filter === 'COE' ? 'selected' : '' ?>>College of Engineering</option>
                </select>
            </div>
        </div>

        <!-- Event Filters -->
        <div class="event-filters">
            <button class="filter-btn <?= $status_filter === 'all' ? 'active' : '' ?>" onclick="filterEvents('all')">
                <i class="fas fa-calendar-alt"></i>
                <span>All Events</span>
            </button>
            <button class="filter-btn <?= $status_filter === 'Ongoing' ? 'active' : '' ?>" onclick="filterEvents('Ongoing')">
                <i class="fas fa-play-circle"></i>
                <span>Active Events</span>
            </button>
            <button class="filter-btn <?= $status_filter === 'Upcoming' ? 'active' : '' ?>" onclick="filterEvents('Upcoming')">
                <i class="fas fa-clock"></i>
                <span>Upcoming Events</span>
            </button>
            <button class="filter-btn <?= $status_filter === 'Finished' ? 'active' : '' ?>" onclick="filterEvents('Finished')">
                <i class="fas fa-check-circle"></i>
                <span>Finished Events</span>
            </button>
        </div>

        <!-- Event Carousel -->
        <div class="carousel-container">
            <div class="carousel-track">
                <?php foreach ($events as $event): 
                    $dateOnly = (new DateTime($event['date_start']))->format('M d, Y');
                    $dateTimeStart = (new DateTime($event['event_start']))->format('h:i A');
                    
                    // Map database status to display status
                    $status_mapping = [
                        'ongoing' => 'Ongoing',
                        'scheduled' => 'Upcoming',
                        'completed' => 'Finished'
                    ];
                    $display_status = isset($status_mapping[$event['event_status']]) ? 
                                    $status_mapping[$event['event_status']] : 
                                    ucfirst($event['event_status']);
                    $statusClass = strtolower($display_status);
                ?>
                <div class="carousel-slide">
                    <div class="event-card" onclick="showEventDetails(<?= htmlspecialchars(json_encode($event)) ?>)">
                        <div class="event-image-container">
                            <img src="<?= htmlspecialchars($event['image_path']) ?>" 
                                 alt="<?= htmlspecialchars($event['event_title']) ?>" 
                                 class="event-image"
                                 loading="lazy"
                                 onload="this.parentElement.classList.add('loaded')"
                                 onerror="this.src='../images-icon/plm_courtyard.png'; this.parentElement.classList.add('loaded')">
                        </div>
                        <div class="event-content">
                            <h3 class="event-title"><?= htmlspecialchars($event['event_title']) ?></h3>
                            <div class="event-date">
                                <i class="far fa-calendar"></i> <?= $dateOnly ?> at <?= $dateTimeStart ?>
                            </div>
                            <div class="event-status <?= $statusClass ?>">
                                <?= htmlspecialchars($display_status) ?>
                            </div>
                            <p class="event-description"><?= htmlspecialchars(substr($event['event_description'], 0, 150)) ?>...</p>
                            <div class="event-details">
                                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['event_location']) ?></p>
                                <p><i class="fas fa-building"></i> <?= htmlspecialchars($event['organization']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="carousel-progress">
                <div class="carousel-progress-bar" id="carouselProgress"></div>
            </div>
            
            <div class="carousel-dots">
                <?php for ($i = 0; $i < count($events); $i++): ?>
                <div class="carousel-dot <?= $i === 0 ? 'active' : '' ?>" onclick="goToSlide(<?= $i ?>)" aria-label="Go to slide <?= $i + 1 ?>"></div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Event Details Modal -->
        <div id="eventModal" class="event-modal">
            <div class="event-modal-content">
                <span class="close-modal" onclick="closeEventModal()">&times;</span>
                <div class="event-modal-image-container">
                    <img id="modalEventImage" src="" alt="Event Image" class="event-modal-image">
                </div>
                <h2 id="modalEventTitle" class="event-modal-title"></h2>
                <div class="event-modal-info">
                    <div class="event-modal-info-item">
                        <i class="far fa-calendar"></i>
                        <span id="modalEventDate"></span>
                    </div>
                    <div class="event-modal-info-item">
                        <i class="far fa-clock"></i>
                        <span id="modalEventTime"></span>
                    </div>
                    <div class="event-modal-info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span id="modalEventLocation"></span>
                    </div>
                    <div class="event-modal-info-item">
                        <i class="fas fa-building"></i>
                        <span id="modalEventOrg"></span>
                    </div>
                </div>
                <div id="modalEventStatus" class="event-modal-status"></div>
                <p id="modalEventDescription" class="event-modal-description"></p>
            </div>
        </div>

        <script>
        let currentSlide = 0;
        let slideInterval;
        let progressInterval;
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.carousel-dot');
        const track = document.querySelector('.carousel-track');
        const progressBar = document.getElementById('carouselProgress');
        const SLIDE_DURATION = 5000; // 5 seconds
        const PROGRESS_UPDATE_INTERVAL = 50; // Update progress bar every 50ms

        function startSlideInterval() {
            clearInterval(slideInterval);
            clearInterval(progressInterval);
            progressBar.style.width = '0%';
            
            slideInterval = setInterval(nextSlide, SLIDE_DURATION);
            
            let progress = 0;
            progressInterval = setInterval(() => {
                progress += (100 / (SLIDE_DURATION / PROGRESS_UPDATE_INTERVAL));
                if (progress > 100) progress = 0;
                progressBar.style.width = progress + '%';
            }, PROGRESS_UPDATE_INTERVAL);
        }

        function updateCarousel() {
            const slideWidth = document.querySelector('.carousel-slide').offsetWidth;
            track.style.transform = `translateX(-${currentSlide * (slideWidth + 30)}px)`;
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
            progressBar.style.width = '0%';
            startSlideInterval();
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            updateCarousel();
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            updateCarousel();
        }

        function goToSlide(index) {
            currentSlide = index;
            updateCarousel();
        }

        // Touch events for mobile
        let touchStartX = 0;
        let touchEndX = 0;

        document.querySelector('.carousel-track').addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
            clearInterval(slideInterval);
            clearInterval(progressInterval);
        });

        document.querySelector('.carousel-track').addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
            startSlideInterval();
        });

        function handleSwipe() {
            const swipeThreshold = 50;
            if (touchEndX < touchStartX - swipeThreshold) {
                nextSlide();
            } else if (touchEndX > touchStartX + swipeThreshold) {
                prevSlide();
            }
        }

        // Initialize carousel
        updateCarousel();
        startSlideInterval();

        function filterEvents(status) {
            window.location.href = '?status=' + status;
        }

        function showEventDetails(event) {
            const modal = document.getElementById('eventModal');
            const modalImage = document.getElementById('modalEventImage');
            const modalTitle = document.getElementById('modalEventTitle');
            const modalDate = document.getElementById('modalEventDate');
            const modalTime = document.getElementById('modalEventTime');
            const modalLocation = document.getElementById('modalEventLocation');
            const modalOrg = document.getElementById('modalEventOrg');
            const modalStatus = document.getElementById('modalEventStatus');
            const modalDescription = document.getElementById('modalEventDescription');

            // Format date and time
            const dateOnly = new Date(event.date_start).toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
            const timeOnly = new Date(event.event_start).toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit'
            });

            // Set modal content
            modalImage.src = event.image_path;
            modalImage.onerror = function() {
                this.src = '../images-icon/plm_courtyard.png';
            };
            modalTitle.textContent = event.event_title;
            modalDate.textContent = dateOnly;
            modalTime.textContent = timeOnly;
            modalLocation.textContent = event.event_location;
            modalOrg.textContent = event.organization;
            modalStatus.textContent = event.event_status;
            modalStatus.className = 'event-modal-status status-' + event.event_status.toLowerCase();
            modalDescription.textContent = event.event_description;

            // Show modal
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeEventModal() {
            const modal = document.getElementById('eventModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('eventModal');
            if (event.target === modal) {
                closeEventModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeEventModal();
            }
        });

        // Add image loading handling
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.event-image');
            images.forEach(img => {
                if (img.complete) {
                    img.parentElement.classList.add('loaded');
                }
            });
        });

        // Add function to handle search and filter form submission
        function handleSearchFilter() {
            const searchForm = document.querySelector('.search-form');
            const departmentSelect = document.querySelector('.department-select');
            
            // Preserve existing status filter when submitting search
            const currentStatus = new URLSearchParams(window.location.search).get('status') || 'all';
            
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const searchValue = this.querySelector('input[name="search"]').value;
                const departmentValue = departmentSelect.value;
                
                let url = new URL(window.location.href);
                url.searchParams.set('search', searchValue);
                url.searchParams.set('department', departmentValue);
                url.searchParams.set('status', currentStatus);
                
                window.location.href = url.toString();
            });
            
            departmentSelect.addEventListener('change', function() {
                const searchValue = searchForm.querySelector('input[name="search"]').value;
                
                let url = new URL(window.location.href);
                url.searchParams.set('search', searchValue);
                url.searchParams.set('department', this.value);
                url.searchParams.set('status', currentStatus);
                
                window.location.href = url.toString();
            });
        }

        // Initialize search and filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            handleSearchFilter();
            // ... rest of the existing initialization code ...
        });
        </script>

        <?php
        // Debug output at the top of the page
        if (isset($_GET['debug'])) {
            echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px;'>";
            echo "<h3>Debug Information:</h3>";
            echo "Database connection: " . ($conn ? "OK" : "Failed") . "<br>";
            echo "Event table count: " . $result->fetch_assoc()['count'] . "<br>";
            echo "Session status: " . session_status() . "<br>";
            echo "Config path: " . $config_path . "<br>";
            echo "</div>";
        }
        ?>
    </body>
</html>