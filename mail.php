<?php 

require_once('phpmailer/PHPMailerAutoload.php');
$mail = new PHPMailer;
$mail->CharSet = 'utf-8';

$name = $_POST['user_name'];
$phone = $_POST['user_phone'];
$email = $_POST['user_email'];
$text = $_POST['user_text'];

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.mail.ru';  																							// Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'humanitgroup@mail.ru'; // login
$mail->Password = 'h5Hj4DHNzG'; // password our mail
$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 465; // TCP port to connect to / this port change for each mail provider 

$mail->setFrom('humanitgroup@mail.ru'); // mail send
$mail->addAddress('taras.kram@humanit.group');     // mail get
//$mail->addAddress('ellen@example.com');               // Name is optional
//$mail->addReplyTo('info@example.com', 'Information');
//$mail->addCC('cc@example.com');
//$mail->addBCC('bcc@example.com');
//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = 'Zgloszenie od klienta';
$mail->Body    = '' .$name . ' zostawil zgloszenie, numer tefonu: ' .$phone. '<br>adres mailowy: ' .$email. ' tekst zgloszenia: <br>' .$text;
$mail->AltBody = '';

if(!$mail->send()) {
    echo 'Error';
} else {
    echo
    '<script>
function myFunction() {
	window.open("https://solutions4ad.com/partner/scripts/l2d1sz5?AccountId=2361fbf0&TotalCost=0&OrderID= ' .$phone. ' &ActionCode=Humanit_CPS&CampaignID=e95d00cb");
       }
   myFunction();

	</script>';
header("refresh: 1; url=thanku.html");
    //header('location: thanku.html');
    //header('location: https://solutions4ad.com/partner/scripts/l2d1sz5?AccountId=2361fbf0&TotalCost=0&OrderID= ' .$phone. ' &ActionCode=Humanit_CPS&CampaignID=e95d00cb');
    //header("refresh: 1; url=thanku.html");
    //header('location: thanku.html');
}
?>
