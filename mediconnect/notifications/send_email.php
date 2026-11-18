<?php
function send_notification_email(array $config, string $to, string $subject, string $message): void
{
    $autoload = dirname(__DIR__) . '/vendor/autoload.php';
    if (!is_file($autoload)) {
        throw new RuntimeException('PHPMailer is missing. Run: composer require phpmailer/phpmailer');
    }
    require_once $autoload;

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->Port = (int)$config['port'];
    $mail->SMTPSecure = ($config['encryption'] ?? 'tls') === 'ssl'
        ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($config['from_email'], $config['from_name'] ?? 'MediConnect');
    $mail->addAddress($to);
    $mail->isHTML(false);
    $mail->Subject = $subject;
    $mail->Body = $message . "\n\nPlease sign in to your MediConnect account for secure details.";
    $mail->send();
}
