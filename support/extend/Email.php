<?php

namespace support\extend;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Email
{
    /**
     * 发送邮件
     *
     * @Author    HSK
     * @DateTime  2020-10-16 13:54:22
     *
     * @param [type] $to_mail
     * @param string $subject
     * @param string $body
     * @param [type] $attachment
     * @param bool $is_html
     *
     * @return void
     */
    public static function send($to_mail, $subject = '', $body = '', $attachment = null, $is_html = true)
    {
        $config = config('email');

        try {
            $mail = new PHPMailer();

            // 服务器设置
            $mail->CharSet    = "UTF-8";
            $mail->SMTPDebug  = 0;
            $mail->isSMTP();
            $mail->Host       = $config['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['SMTP_USER'];
            $mail->Password   = $config['SMTP_PASS'];
            $mail->SMTPSecure = $config['SMTP_SECURE'];
            $mail->Port       = $config['SMTP_PORT'];
            // 发件人
            $mail->setFrom($config['FROM_EMAIL'], $config['FROM_NAME']);
            // 收件人
            if (is_array($to_mail)) {
                foreach ($to_mail as $v) {
                    $mail->addAddress($v);
                }
            } else {
                $mail->addAddress($to_mail);
            }
            // 回复邮箱
            $mail->addReplyTo($config['REPLY_EMAIL'], $config['REPLY_NAME']);
            // 附件
            if (!empty($attachment)) {
                if (is_array($attachment)) {
                    foreach ($attachment as $v) {
                        $mail->addAttachment($v);
                    }
                } else {
                    $mail->addAttachment($attachment);
                }
            }
            // 内容
            $mail->isHTML($is_html);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $body;

            return $mail->send() ? true : $mail->ErrorInfo;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
