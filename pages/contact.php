<?php
session_start();
if (!isset($_SESSION['email']) && !isset($_SESSION['client_id'])) {
    header('Location: 1_Login.php');
    exit();
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';



if (isset($_POST['name']) &&
    isset($_POST['email']) &&
    isset($_POST['course']) &&
    isset($_POST['surveymsg']))
{       
        $subject = "Feedback Form";
        $name = $_POST['name'];
        $email = $_POST['email'];
        $course = $_POST['course'];
        $surveymsg = $_POST['surveymsg'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $em = "Invalid email Format";
    header("Location: survey.php?error=$em");
    exit();
}

    
    if (empty($name) || empty($course) || empty($surveymsg)) {
        $em = "Fill in all required fields";
        header("Location: survey.php?error=$em");
        exit();
    }

//Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'darryljohn016@gmail.com';                     //SMTP username
        $mail->Password   = 'ryukkjtvszndjoxr';                               //SMTP password
        $mail->SMTPSecure = "ssl";            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom($email, $name);
        $mail->addAddress($email);               //Name is optional
        $mail->addReplyTo('info@example.com', 'Information');
        $mail->addCC('cc@example.com');
        $mail->addBCC('bcc@example.com');

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = '$subject';
        $mail->Body    = "
                <h2>Feedback Form</h2>
                <p>Name: $name</p>
                <p>Email: $email</p>
                <p>Course: $course</p>
                <p>Message: $surveymsg</p>";

        $mail->send();
        $sm = 'Message has been sent';
        $em = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        header("Location: survey.php?success=$sm");
    } catch (Exception $e) {
        $em = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        header("Location: survey.php?error=$em");
    }
}