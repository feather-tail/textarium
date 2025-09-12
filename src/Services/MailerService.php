<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerService
{
  public function sendVerification(string $to, string $token): bool
  {
    $mail = new PHPMailer(true);

    try {
      $mail->isSMTP();
      $mail->Host = $_ENV["MAIL_HOST"];
      $mail->SMTPAuth = true;
      $mail->Username = $_ENV["MAIL_USER"];
      $mail->Password = $_ENV["MAIL_PASS"];
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      $mail->Port = 465;

      $mail->Timeout = 10;
      $mail->SMTPKeepAlive = false;

      if (!$mail->smtpConnect()) {
        file_put_contents(
          __DIR__ . "/../../debug.log",
          "[EMAIL ERROR] SMTP connect failed" . "\n",
          FILE_APPEND,
        );
        return false;
      }

      $mail->setFrom($_ENV["MAIL_USER"], "Библиотека");
      $mail->addReplyTo($_ENV["MAIL_USER"], "Библиотека");
      $mail->addAddress($to);

      $host = $_ENV["APP_DOMAIN"] ?? "";
      $sanitizedHost = filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
      if ($sanitizedHost === false) {
        file_put_contents(
          __DIR__ . "/../../debug.log",
          "[EMAIL ERROR] Invalid APP_DOMAIN value" . "\n",
          FILE_APPEND,
        );
        return false;
      }
      $verifyLink = sprintf("https://%s/verify?token=%s", $sanitizedHost, urlencode($token));
      $mail->CharSet = "UTF-8";
      $mail->isHTML(true);
      $mail->Subject = "Подтверждение регистрации";
      $mail->Body = "
                <p>Здравствуйте!</p>
                <p>Чтобы завершить регистрацию в «Библиотеке», перейдите по ссылке:</p>
                <p><a href='$verifyLink'>$verifyLink</a></p>
                <hr>
                <small>Если вы не регистрировались, проигнорируйте это письмо.</small>
            ";

      if (!$mail->send()) {
        file_put_contents(
          __DIR__ . "/../../debug.log",
          "[EMAIL ERROR] send() returned false: " . $mail->ErrorInfo . "\n",
          FILE_APPEND,
        );
        return false;
      }

      return true;
    } catch (Exception $e) {
      file_put_contents(
        __DIR__ . "/../../debug.log",
        "[EMAIL EXCEPTION] " . $e->getMessage() . "\n",
        FILE_APPEND,
      );
      error_log("[Email Error] " . $e->getMessage());
      return false;
    }
  }
}
