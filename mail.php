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

    $mail = new PHPMailer(); // create a new object
    $mail->IsSMTP(); // enable SMTP
    $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
    $mail->SMTPAuth = true; // authentication enabled
    $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 465; // or 587
    $mail->IsHTML(true);
    $mail->Username = "automatwspolpraca@gmail.com";
    $mail->Password = "Legnicka#^^@$@#3662423";
    $mail->SetFrom("automatwspolpraca@gmail.com");
    $mail->AddAddress("taras.kram@humanit.group");    // Set email format to HTML

    $mail->Subject = 'Zgloszenie od klienta';
    $mail->Body    = '' .$name. ' zostawil zgloszenie, numer tefonu: ' .$phone. '<br>adres mailowy: ' .$email. ' tekst zgloszenia: <br>' .$text;
    $mail->AltBody = '';

    $mail->send();
?>
