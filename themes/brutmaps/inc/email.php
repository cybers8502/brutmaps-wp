<?php

add_action('wp_ajax_send_support','send_support');

add_action('wp_ajax_nopriv_send_support', 'send_support');

//Send support Mail
function send_support() {

    $email = $_POST['s-email'];
    $name = $_POST['s-name'];
    $text = $_POST['s-message'];

    $message =  'E-mail: '.$email."\r\n".
        'Имя: '.$name."\r\n"."\r\n".
        $text;

    $to      =  'max.tkh.ua@gmail.com';
    $subject = 'Support';

    $headers = 'From: '.$email.'' . "\r\n" .
        'Reply-To: '.$email.'' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    if (@mail($to, $subject, $message, $headers)) {
        echo 'success';
    } else {
        echo 'error';
    }

    exit;

}