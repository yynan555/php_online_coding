<?php
namespace App\Core;

use App\Core\CommonFun;
use App\Core\Config;
use App\Core\UserAuth;
use App\Lib\PHPMailer\PHPMailer;
// 发送消息类

class Message
{
	// toUsers 用户名称
	public static function sendMessage($toUsers, $title, $content)
	{
		// 邮箱发送
		if( Config::get('app.email_host','') && Config::get('app.email_username') && Config::get('app.email_password')){
			$user_emails = [];
			foreach ((array)$toUsers as $user) {
				$userinfo = UserAuth::getUserConfig($user);
				if( isset($userinfo['email']) && !empty($userinfo) ){
					$user_emails[] = $userinfo['email'];
				}
			}
			$user_emails = array_unique($user_emails);
			self::sendMail($user_emails, $title, $content);
		}
	}
	/**
	 * 发送邮件
	 */
	public static function sendMail($toEmails, $title, $content)
	{
		$mail = new PHPMailer();

		// $mail->SMTPDebug = 1;

		$mail->isSMTP();
		$mail->SMTPAuth=true;
		$mail->SMTPSecure = 'ssl';
		$mail->Port = 465;
		$mail->CharSet = 'UTF-8';

		$mail->Hostname = CommonFun::url();
		$mail->FromName = CommonFun::url();

		$mail->Host = Config::get('app.email_host');
		$mail->Username = Config::get('app.email_username');
		$mail->From = Config::get('app.email_username');
		$mail->Password = Config::get('app.email_password');

		$mail->isHTML(true);

		foreach ((array)$toEmails as $email) {
			$mail->addAddress($email,$email);
		}

		$mail->Subject = $title;
		$mail->Body = $content;
		 
		$status = $mail->send();
		
		if($status) {
		 	return true;
		}else{
		 	// echo '发送邮件失败，错误信息为：'.$mail->ErrorInfo;
		 	return false;
		}
	}
}