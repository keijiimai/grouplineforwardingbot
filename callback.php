<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Composerでインストールしたライブラリを一括読み込み
require_once __DIR__ . '/vendor/autoload.php';

//エラーメッセージ用日本語言語ファイルを読み込む
require 'vendor/phpmailer/phpmailer/language/phpmailer.lang-ja.php';

//環境変数の読み込み
$CHANNEL_ACCESS_TOKEN=getenv('CHANNEL_ACCESS_TOKEN');
$CHANNEL_SECRET=getenv('CHANNEL_SECRET');
$FROM_ADRESS=getenv('FROM_ADRESS');
//$TO_ADRESS=getenv('TO_ADRESS');
$BCC_ADRESS=getenv('BCC_ADRESS');//BCC要らない場合は消す
$APP_PASSWORD=getenv('APP_PASSWORD');

$contents = file_get_contents('php://input'); //POSTの生データを読み込む
$json = json_decode($contents); //生のJSONデータをデコードして変数jsonに格納
$event = $json->events[0]; //変数jsonの中のevent配列の0番目を取り出し、変数eventに格納
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($CHANNEL_ACCESS_TOKEN); // アクセストークンを使いCurlHTTPClientをインスタンス化
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $CHANNEL_SECRET]);    //CurlHTTPClientとシークレットを使いLINEBotをインスタンス化

$responseProfile = $bot->getProfile($event->source->userId);
if ($responseProfile->isSucceeded()) {
$profile = $responseProfile->getJSONDecodedBody();
      $user_display_name = $profile['displayName'];

}else{
  error_log("fail to get profile");
}

//タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

//mailを送っていく
//mbstring の日本語設定
mb_language("Japanese"); 
mb_internal_encoding("UTF-8");

// インスタンスを生成（引数に true を指定して例外 Exception を有効に）
$mail = new PHPMailer(true);

$mail->CharSet = "iso-2022-jp";
$mail->Encoding = "7bit";

//エラーメッセージ用言語ファイルを使用する
$mail->setLanguage('ja', 'vendor/phpmailer/phpmailer/language/');

try{
  //サーバの設定
  $mail->SMTPDebug = SMTP::DEBUG_SERVER;  // デバグの出力を有効に（テスト環境での検証用）
  $mail->isSMTP();   // SMTP を使用
  $mail->Host       = 'smtp.gmail.com';  // ★★★ Gmail SMTP サーバーを指定
  $mail->SMTPAuth   = true;   // SMTP authentication を有効に
  $mail->Username   = $FROM_ADRESS;  // ★★★ Gmail ユーザ名
  $mail->Password   = $APP_PASSWORD;  // ★★★ Gmail パスワード ←アプリパスワード
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // ★★★ 暗号化（TLS)を有効に 
  $mail->Port = 587;  //★★★ ポートは 587 

  //差出人アドレス, 差出人名 
  $FROM_NAME = "今井慶治";
  $mail->setFrom($FROM_ADRESS, mb_encode_mimeheader($FROM_NAME)); 

  // 受信者アドレス, 受信者名（受信者名はオプション）
  $TO_NAME="";

  
  $TO_ADRESS = explode(',',$TO_ADRESS);
  
  for ($i = 0; $i < count($TO_ADRESS); $i++) {
    $mail->addBCC($TO_ADRESS[$i]);
  }


  //$mail->addAddress($TO_ADRESS, mb_encode_mimeheader($TO_NAME));  
  //$mail->addBCC($BCC_ADRESS);//BCC要らない場合は消してください

  //コンテンツ設定
  $mail->isHTML(true);   // HTML形式を指定

  //タイムスタンプのミリ秒を時間に変換。　
  //$SendTime = date("H:i:s",($event->timestamp)/1000);

  //現在の日付
  //$nowdate =  date("m/d");

switch($event->message->type) {
case 'text':
  //メール表題（タイトル）
  $mail->Subject = mb_encode_mimeheader("土田グループのお知らせ");//mb_encode_mimeheader($nowdate);

  //本文（HTML用）
  $bodytext = $event->message->text;
  $mail->Body  = mb_convert_encoding($bodytext,"JIS","UTF-8"); 

if ($user_display_name = "けいじ") {

   $mail->send();  //送信
}
  break;

case 'image':
  


  /*
  
  //メール表題（タイトル）
  $mail->Subject = mb_encode_mimeheader($nowdate);

  //本文（HTML用）
  $bodytext = $SendTime.'//'.$user_display_name.'//'."Image";
  $mail->Body  = mb_convert_encoding($bodytext,"JIS","UTF-8"); 

  $response = $bot->getMessageContent($event->message->id);

  
  if ($response->isSucceeded()) {
   //バイナリデータをjpgに変換
  $im=imagecreatefromstring($response->getRawBody());
  $filename = date("Ymd-His") . '-' . mt_rand() . '.jpg';
  imagejpeg($im, $filename,10);//10はクオリティ低
  }else {
    error_log($response->getHTTPStatus() . ' ' . $response->getRawBody());
  }

  //画像添付
  $mail->addAttachment($filename);
*/


  //$mail->send();  //送信
break;

case 'video':
//処理をここに書く
break;

case 'audio':
//処理をここに書く
break;

case 'file':
  //$mail->addAttachment($filename);



  $mail->send();  //送信
break; 

case 'sticker': 
//処理をここに書く
break;

default:
}  

} catch (Exception $e) {
  error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
}
?>