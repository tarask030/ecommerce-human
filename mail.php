<?php 
require_once 'livespace.php';
require_once 'phpmailer/PHPMailerAutoload.php';

$name = $_POST['user_name'];
$email = $_POST['user_email'];
$phone = $_POST['user_phone'];
$text = $_POST['user_text'];

    print_r($_POST);
    echo ('result1');
    $ls = new LiveSpace(array(
        'api_url' => 'https://humanit.livespace.io',
        'api_key' => '5724zompu99t01z5wbjczb1jgee2zj',
        'api_secret' => 'i4rl8raug44py4r'
       ));
    echo ('result2');

$contactData = array(

    'contact' => array(
        'firstname' => $_POST['user_name'],
        'lastname' => 'ecommerce.humanit.group',

        'phones' => array( // telefony
            0 => array(
                'phone_no' => $_POST['user_phone'], // numer telefonu
                'type' => 1, // typ telefonu: 
            ),
            1 => array(
                'phone_no' => '0', // numer telefonu
                'type' => 2, // typ telefonu: 1 - telefon,
                'is_default' => 1
            )
         ),

        'emails' => array( // adresy email
            0 => array(
                'email' =>  $_POST['user_email']// adres email
            ),
            1 => array(
                'email' => $_POST['user_email'], // adres email
                'is_default' => 1
            )
        ),
        'note' => $_POST['user_text'],
    )
);
    echo ('result3');
    $result = $ls->call('Contact/addContact', $contactData);
    if ($result->getStatus()) {
        '<iframe src="https://solutions4ad.com/partner/scripts/l2d1sz5?AccountId=2361fbf0&TotalCost=0&OrderID= ' .$phone. ' &ActionCode=Humanit_CPS&CampaignID=e95d00cb"></iframe>';
            header("refresh: 1; url=thanku.html");
       } else {
    echo 'Wystąpił błąd #' . $result->getResult() . ":\r\n";
    print_r($result->getError());
    };

    $mail = new PHPMailer;
    $mail->CharSet = 'utf-8';
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp.mail.ru';  																							// Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'humanitgroup@mail.ru'; // login
    $mail->Password = 'IyPTT41uusu:'; // password our mail
    $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, 
    $mail->Port = 465; 

    $mail->setFrom('humanitgroup@mail.ru'); // mail send
    $mail->addAddress('taras.kram@humanit.group');     // mail get
    $mail->isHTML(true);                                  // Set email format to HTML

    $mail->Subject = 'Zgloszenie od klienta';
    $mail->Body    = '' .$name. ' zostawil zgloszenie, numer tefonu: ' .$phone. '<br>adres mailowy: ' .$email. ' tekst zgloszenia: <br>' .$text;
    $mail->AltBody = '';

    $mail->send();
?>
