<?php 
require_once 'livespace.php';


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
        'lastname' => 'Do kontaktu',

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
?>
