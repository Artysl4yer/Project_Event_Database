<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - PLM Events</title>
    <link rel="stylesheet" href="../styles/style1.css">
    <link rel="stylesheet" href="../styles/style2.css">
    <link rel="stylesheet" href="../styles/style4.css">
    <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, rgba(26, 94, 34, 0.6), rgba(160, 131, 14, 0.3)),
                        url('/images-icon/plm_courtyard.png') no-repeat center center;
            background-size: cover;
        }

        .success-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 500px;
            width: 90%;
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            font-size: 64px;
            color: #104911;
            margin-bottom: 20px;
        }

        .success-title {
            color: #104911;
            font-size: 24px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .success-message {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .home-button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #104911;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .home-button:hover {
            background-color: #0d3a0d;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1 class="success-title">Registration Successful!</h1>
        <p class="success-message">
            Thank you for registering for the event. Your registration has been confirmed.<br>
            You will receive further details about the event via email.
        </p>
        <a href="../index.php" class="home-button">
            <i class="fas fa-home"></i> Return to Home
        </a>
    </div>
</body>
</html> 