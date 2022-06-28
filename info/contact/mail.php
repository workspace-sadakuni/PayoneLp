<?php header("Content-Type:text/html;charset=utf-8"); ?>
<?php //error_reporting(E_ALL | E_STRICT);
##---------------------------------------------------------------------##
if (version_compare(PHP_VERSION, '5.1.0', '>=')) {
	date_default_timezone_set('Asia/Tokyo');
}
//設定-----------------------------------------------------------------------------
//サイトのトップページのURL
$site_top = "./";
// 管理者メールアドレス ※「,」でメールアドレス複数指定可能 例 $to = "aa@aa.aa,bb@bb.bb";
$to = "info@pay-one.jp"; //メールアドレス
$Email = "メールアドレス";
//リファラチェック する=1, しない=0
$Referer_check = 0;
//リファラチェックを「する」場合のドメイン
$Referer_check_domain = ""; //サイトのドメイン
// 管理者宛のメールで差出人 する=1, しない=0
$userMail = 1;
// Bcc 例 $BccMail = "aa@aa.aa,bb@bb.bb";)
$BccMail = "";
// 管理者宛に送信されるメール件名
$subject = "ページにお問い合わせがありました";
// 送信確認画面 する=1, しない=0
$confirmDsp = 1;
// 送信完了後にサンクスページに移動　する=1, しない=0
$jumpPage = 1;
// 送信完了後に表示するページURL（上記で1を設定した場合のみ）※httpから始まるURLで指定ください。
$thanksPage = "./thanks.html";
// 必須入力 する=1, しない=0
$requireCheck = 1;
// 必須入力項目
$require = array('ご担当者名','メールアドレス','電話番号','カテゴリ','お問い合わせ内容');

// 自動返信メール-------------------------------------------------------------------
// 自動返信メール 送る=1, 送らない=0
$remail = 1;
//自動返信メールの送信者名
$refrom_name = "株式会社TAS Port（ペイワン）";
// 差出人に送信確認メールの件名
$re_subject = "お問い合わせいただきありがとうございます";
//自動返信メールの「○○様」
$dsp_name = 'お名前';
//自動返信メールの文言
$remail_text = <<< TEXT
お問い合わせいただき誠にありがとうございます。

下記の内容を確認させて頂いた後、
折り返し担当よりご連絡をさせていただきます。
宜しくお願いします。
TEXT;
$remail_text_bottom = <<< TEXTBOTTOM
尚、3日経ってもご連絡がない場合、
何かの問題でメールが届いていない可能性があります。
大変恐縮ですが、その際は下記メールアドレスまで
ご連絡をいただけますと幸いです。
【　info@tas-port.co.jp　】				

※本メールは、プログラムから自動で送信しています。
心当たりの無い方は、お手数ですが削除してください。
もしくは、そのまま送信元に返信していただければと思います。

TEXTBOTTOM;
//自動返信メールに署名（フッター）する=1, しない=0
$mailFooterDsp = 1;
//フッターテキスト
$mailSignature = <<< FOOTER
===============================================
株式会社TAS Port
〒107-0052
東京都港区赤坂2丁目8番11号 葵ビル302
Email : info@tas-port.co.jp
URL : https://www.tas-port.co.jp
===============================================

FOOTER;

//その他設定------------------------------------------------------------------------
//メールアドレスの形式チェック する=1, しない=0
$mail_check = 1;
//全角英数字→半角変換 する=1, しない=0
$hankaku = 1;
//全角英数字→半角変換を行う項目
$hankaku_array = array('電話番号','メールアドレス','郵便番号','ホームページURL');

//----------------------------------------------------------------------
$encode = "UTF-8";
if(isset($_GET)) $_GET = sanitize($_GET);
if(isset($_POST)) $_POST = sanitize($_POST);
if(isset($_COOKIE)) $_COOKIE = sanitize($_COOKIE);
if($encode == 'SJIS') $_POST = sjisReplace($_POST,$encode);
$funcRefererCheck = refererCheck($Referer_check,$Referer_check_domain);
$sendmail = 0;
$empty_flag = 0;
$post_mail = '';
$errm ='';
$header ='';
if($requireCheck == 1) {
	$requireResArray = requireCheck($require);
	$errm = $requireResArray['errm'];
	$empty_flag = $requireResArray['empty_flag'];
}
if(empty($errm)){
	foreach($_POST as $key=>$val) {
		if($val == "confirm_submit") $sendmail = 1;
		if($key == $Email) $post_mail = h($val);
		if($key == $Email && $mail_check == 1 && !empty($val)){
			if(!checkMail($val)){
				$errm .= "<p class=\"error_messe\">【".$key."】はメールアドレスの形式が正しくありません。</p>\n";
				$empty_flag = 1;
			}
		}
	}
}
if(($confirmDsp == 0 || $sendmail == 1) && $empty_flag != 1){
	if($remail == 1) {
		$userBody = mailToUser($_POST,$dsp_name,$remail_text,$remail_text_bottom,$mailFooterDsp,$mailSignature,$encode);
		$reheader = userHeader($refrom_name,$to,$encode);
		$re_subject = "=?iso-2022-jp?B?".base64_encode(mb_convert_encoding($re_subject,"JIS",$encode))."?=";
	}
	$adminBody = mailToAdmin($_POST,$subject,$mailFooterDsp,$mailSignature,$encode,$confirmDsp);
	$header = adminHeader($userMail,$post_mail,$BccMail,$to);
	$subject = "=?iso-2022-jp?B?".base64_encode(mb_convert_encoding($subject,"JIS",$encode))."?=";
	
	mail($to,$subject,$adminBody,$header);
	if($remail == 1 && !empty($post_mail)) mail($post_mail,$re_subject,$userBody,$reheader);
}
else if($confirmDsp == 1){ 
/*　==============******************===============送信確認画面===========　*/
?>
<!doctype html>
<html lang="ja">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<meta name="format-detection" content="telephone=no">

		<title>モバイルオーダー payone（ペイワン）</title>
		<meta name="keywords" content="payone,ペイワン,モバイルオーダー,決済,株式会社TAS Port"/>
		<meta name="description" content="スマートフォンで簡単に商品がご注文できるキャッシュレス決済サービスです。"/>
			
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo $document_root; ?>/assets/css/common.css"/>
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo $document_root; ?>/assets/css/style.css"/>
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo $document_root; ?>/assets/css/contact.css"/>
		<link href="https://fonts.googleapis.com/css?family=Noto+Sans+JP:200,400,500,600,700|Roboto&display=swap" rel="stylesheet">
		<link rel="shortcut icon" href="<?php echo $site_url ?>/favicon.ico"/><link rel="apple-touch-icon-precomposed" href="img/apple_touch_icon.png"/>

		<meta property="og:url" content="https://info.pay-one.jp"/>
		<meta property="og:title" content="モバイルオーダー payone（ペイワン）"/>
		<meta property="og:type" content="website">
		<meta property="og:description" content="スマートフォンで簡単に商品がご注文できるキャッシュレス決済サービスです。"/>
		<meta property="og:image" content="https://info.pay-one.jp/assets/img/common/ogp.png"/>
		<meta property="og:site_name" content="モバイルオーダー payone（ペイワン）"/>
		<meta property="og:locale" content="ja_JP"/>
        <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-W293G5T');</script>
    <!-- End Google Tag Manager -->
    
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-188871438-2"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'UA-188871438-2');
    </script>
	</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NJ3BXFV"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<body id="top">
      <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W293G5T"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
		<!--header-->
		<header id="header" class="l-header">
			<h1 class="l-header_logo"><a href="<?php echo $document_root;?>/"><img src="<?php echo $document_root; ?>/assets/img/common/Payone.svg" alt="モバイルオーダー Payone（ペイワン）"></a></h1>
			<!--hamburger-->
			<button class="c-hamburger js-hamburger" type="button">
				<span class="c-hamburger_line js-hamburger_line"></span>
				<span class="c-hamburger_line js-hamburger_line"></span>
				<span class="c-hamburger_line js-hamburger_line"></span>
			</button>
			<!--nav-->
			<nav class="l-nav js-nav">
				<ul class="l-nav_list">
					<li class="l-nav_item js-nav-item"><a href="<?php echo $document_root;?>/#top">TOP</a></li>
					<li class="l-nav_item js-nav-item"><a href="<?php echo $document_root;?>/#about">Payoneとは</a></li>
					<li class="l-nav_item js-nav-item"><a href="<?php echo $document_root;?>/#flow">ご利用の流れ</a></li>
					<li class="l-nav_item js-nav-item"><a href="<?php echo $document_root;?>/#faq">よくあるご質問</a></li>
					<li class="l-nav_item js-nav-item"><a href="<?php echo $document_root;?>/contact/">お問い合わせ</a></li>
					<li class="l-nav_item js-nav-item"><a href="<?php echo $document_root;?>/company/">運営会社</a></li>
				</ul>
			</nav>
		</header>

		<!--main-->
		<main class="l-main">
      <!--form-->
			<section id="contact" class="l-section p-contact">
				<div class="l-section_inner">
					<h2 class="c-heading">
						<span class="c-heading_en">Confirmation</span>
						<span class="c-heading_ja">確認画面</span>
					</h2>
					<div class="wmain">
                    <!-- フォーム -->
                    <div id="formWrap">
                        <div class="inwrap">
                            <?php if($empty_flag == 1){ ?>
                            <p class="error c-text">入力にエラーがあります。下記をご確認の上「戻る」ボタンにて修正をお願い致します。</p>
                            <div class="formwrap">
                                <div align="center">
                                    <?php echo $errm; ?>
                                </div>
                            </div>
                            <div class="text-center mb-3">
                                <input type="button" value="戻る" class="back-button" onclick="history.back()" >
                            </div> 
                            <?php }else{ ?>
                                <p class="c-text">以下の内容で間違いがなければ、「送信する」ボタンを押してください。</p>
                                <form action="<?php echo h($_SERVER['SCRIPT_NAME']); ?>" method="POST">
                                    <table class="p-contact_form">
                                        <?php echo confirmOutput($_POST);//入力内容を表示?>
                                    </table>
                                    <p align="center"><input type="hidden" name="mail_set" value="confirm_submit"></p>
                                    <div class="mail-btn mt-3">
                                        <div><input type="hidden" name="httpReferer" value="<?php echo h($_SERVER['HTTP_REFERER']);?>"></div>
                                        <div class="mb-5">
                                            <input type="submit" value="送信する" class="c-button">
                                        </div>
                                        <div>
                                            <input type="button" value="戻る" class="back-button" onclick="history.back()" >
                                        </div>
                                    </div>
                                </form>
                                <?php } ?>
                        </div>
                    </div><!-- /formWrap -->
                </div>
				</div>
			</section>
    </main>

		<!--to top-->
		<a href="#top" class="c-to-top js-to-top"></a>

		<!--footer-->
		<footer id="footer" class="l-footer js-hidearea">
			<div class="l-footer_wrap">
				<img class="l-footer_logo" src="<?php echo $document_root;?>/assets/img/common/tasport.png" alt="タスポート">

				<ul class="l-footer_list">
					<li class="l-footer_item"><a class="l-footer_link" href="<?php echo $document_root;?>/company/">運営会社</a></li>
					<li class="l-footer_item"><a class="l-footer_link" href="<?php echo $document_root;?>/privacy/">プライバシーポリシー</a></li>
					<li class="l-footer_item"><a class="l-footer_link" href="<?php echo $document_root;?>/terms/">利用規約</a></li>
					<li class="l-footer_item"><a class="l-footer_link" href="<?php echo $document_root;?>/">ホーム</a></li>
				</ul>
			</div>

			<div class="l-footer_sns">
				<a href="https://twitter.com/payone_official?s=21&t=K4V4UDhuaY2Z7UtRlrXHWA" class="l-footer_sns_link"><img class="l-footer_sns_icon" src="<?php echo $document_root;?>/assets/img/common/twitter.png" alt="ツイッター"></a>
				<a href="https://instagram.com/payone_mobile?igshid=YmMyMTA2M2Y=" class="l-footer_sns_link"><img class="l-footer_sns_icon" src="<?php echo $document_root;?>/assets/img/common/instagram.png" alt="インスタグラム"></a>
			</div>

			<small class="l-footer_copyright">Copyright©2022. TAS Port Corporation. All Rights Reserved.</small>
		</footer>

		<!--js-->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
		<script src="<?php echo $document_root;?>/assets/js/script.js"></script>
  </body>
</html>
<?php
/*　==============******************===========////送信確認画面===========　*/
}

if(($jumpPage == 0 && $sendmail == 1) || ($jumpPage == 0 && ($confirmDsp == 0 && $sendmail == 0))) { 

/* ▼▼▼送信完了画面▼▼▼　*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>完了画面</title>
</head>
<body>
<div align="center">
<?php if($empty_flag == 1){ ?>
<h4>入力にエラーがあります。下記をご確認の上「戻る」ボタンにて修正をお願い致します。</h4>
<div style="color:red"><?php echo $errm; ?></div>
<br /><br /><input type="button" value=" 戻る " onClick="history.back()">
</div>
</body>
</html>
<?php }else{ ?>
送信ありがとうございました。<br />
送信は正常に完了しました。<br /><br />
<a href="<?php echo $site_top ;?>">トップページへ戻る&raquo;</a>
<?php copyright(); ?>
<!--  CV率を計測する場合ここにAnalyticsコードを貼り付け -->
</body>
</html>
<?php 
/* ▲▲▲送信完了画面▲▲▲　*/
  }
}
//確認画面無しの場合の表示、指定のページに移動する設定の場合、エラーチェックで問題が無ければ指定ページヘリダイレクト
else if(($jumpPage == 1 && $sendmail == 1) || $confirmDsp == 0) { 
	if($empty_flag == 1){ ?>
<div align="center"><h4>入力にエラーがあります。下記をご確認の上「戻る」ボタンにて修正をお願い致します。</h4><div style="color:red"><?php echo $errm; ?></div><br /><br /><input type="button" value=" 戻る " onClick="history.back()"></div>
<?php 
	}else{ header("Location: ".$thanksPage); }
}

// 以下の変更は知識のある方のみ自己責任でお願いします。

//----------------------------------------------------------------------
//  関数定義(START)
//----------------------------------------------------------------------
function checkMail($str){
	$mailaddress_array = explode('@',$str);
	if(preg_match("/^[\.!#%&\-_0-9a-zA-Z\?\/\+]+\@[!#%&\-_0-9a-z]+(\.[!#%&\-_0-9a-z]+)+$/", "$str") && count($mailaddress_array) ==2){
		return true;
	}else{
		return false;
	}
}
function h($string) {
	global $encode;
	return htmlspecialchars($string, ENT_QUOTES,$encode);
}
function sanitize($arr){
	if(is_array($arr)){
		return array_map('sanitize',$arr);
	}
	return str_replace("\0","",$arr);
}
//Shift-JISの場合に誤変換文字の置換関数
function sjisReplace($arr,$encode){
	foreach($arr as $key => $val){
		$key = str_replace('＼','ー',$key);
		$resArray[$key] = $val;
	}
	return $resArray;
}
//送信メールにPOSTデータをセットする関数
function postToMail($arr){
	global $hankaku,$hankaku_array;
	$resArray = '';
	foreach($arr as $key => $val) {
		$out = '';
		if(is_array($val)){
			foreach($val as $key02 => $item){ 
				//連結項目の処理
				if(is_array($item)){
					$out .= connect2val($item);
				}else{
					$out .= $item . ', ';
				}
			}
			$out = rtrim($out,', ');
			
		}else{ $out = $val; }//チェックボックス（配列）追記ここまで
		if(get_magic_quotes_gpc()) { $out = stripslashes($out); }
		
		//全角→半角変換
		if($hankaku == 1){
			$out = zenkaku2hankaku($key,$out,$hankaku_array);
		}
		if($out != "confirm_submit" && $key != "httpReferer") {
			$resArray .= "【 ".h($key)." 】 ".h($out)."\n";
		}
	}
	return $resArray;
}
//確認画面の入力内容出力用関数
function confirmOutput($arr){
	global $hankaku,$hankaku_array;
	$html = '';
	foreach($arr as $key => $val) {
		$out = '';
		if(is_array($val)){
			foreach($val as $key02 => $item){ 
				//連結項目の処理
				if(is_array($item)){
					$out .= connect2val($item);
				}else{
					$out .= $item . ', ';
				}
			}
			$out = rtrim($out,', ');
			
		}else{ $out = $val; }//チェックボックス（配列）追記ここまで
		if(get_magic_quotes_gpc()) { $out = stripslashes($out); }
		$out = nl2br(h($out));//※追記 改行コードを<br>タグに変換
		$key = h($key);
		//機種依存文字変換
$before = array('①','②','③','④','⑤','⑥','⑦','⑧','⑨','⑩','№','㈲','㈱','髙');//変換前の文字
$after = array('(1)','(2)','(3)','(4)','(5)','(6)','(7)','(8)','(9)','(10)','No.','（有）','（株）','高');//変換後の文字
$out = str_replace($before, $after, $out);

		//全角→半角変換
		if($hankaku == 1){
			$out = zenkaku2hankaku($key,$out,$hankaku_array);
		}
		
		$html .= "<tr><th>".$key."</th><td>".$out;
		$html .= '<input type="hidden" name="'.$key.'" value="'.str_replace(array("<br />","<br>"),"",$out).'" />';
		$html .= "</td></tr>\n";
	}
	return $html;
}

//全角→半角変換
function zenkaku2hankaku($key,$out,$hankaku_array){
	global $encode;
	if(is_array($hankaku_array) && function_exists('mb_convert_kana')){
		foreach($hankaku_array as $hankaku_array_val){
			if($key == $hankaku_array_val){
				$out = mb_convert_kana($out,'a',$encode);
			}
		}
	}
	return $out;
}
//配列連結の処理
function connect2val($arr){
	$out = '';
	foreach($arr as $key => $val){
		if($key === 0 || $val == ''){//配列が未記入（0）、または内容が空のの場合には連結文字を付加しない（型まで調べる必要あり）
			$key = '';
		}elseif(strpos($key,"円") !== false && $val != '' && preg_match("/^[0-9]+$/",$val)){
			$val = number_format($val);//金額の場合には3桁ごとにカンマを追加
		}
		$out .= $val . $key;
	}
	return $out;
}

//管理者宛送信メールヘッダ
function adminHeader($userMail,$post_mail,$BccMail,$to){
	$header = '';
	if($userMail == 1 && !empty($post_mail)) {
		$header="From: $post_mail\n";
		if($BccMail != '') {
		  $header.="Bcc: $BccMail\n";
		}
		$header.="Reply-To: ".$post_mail."\n";
	}else {
		if($BccMail != '') {
		  $header="Bcc: $BccMail\n";
		}
		$header.="Reply-To: ".$to."\n";
	}
		$header.="Content-Type:text/plain;charset=iso-2022-jp\nX-Mailer: PHP/".phpversion();
		return $header;
}
//管理者宛送信メールボディ
function mailToAdmin($arr,$subject,$mailFooterDsp,$mailSignature,$encode,$confirmDsp){
	$adminBody="ホームページ\n「 https://info.pay-one.jp/ 」に\n";
	$adminBody .="お客様からお問い合わせがありました。\n";
	$adminBody .="※本メールは、プログラムから自動で送信しています。\n\n";
	$adminBody .="以下お客様情報です。\n";
	$adminBody.="\n−−−−−−−−−−−−−−−−−−−−−−−−−−\n";
	$adminBody.= postToMail($arr);//POSTデータを関数からセット
	$adminBody.="\n−−−−−−−−−−−−−−−−−−−−−−−−−−\n\n";
	$adminBody.="お客様への折り返しのご連絡を宜しくお願い致します。\n\n";
	$adminBody.="\n\n送信された日時：".date( "Y/m/d (D) H:i:s", time() )."\n";
	//$adminBody.="送信者のIPアドレス：".@$_SERVER["REMOTE_ADDR"]."\n";
	//$adminBody.="送信者のホスト名：".getHostByAddr(getenv('REMOTE_ADDR'))."\n";
	if($confirmDsp != 0){
		//$adminBody.="問い合わせのページURL：".@$_SERVER['HTTP_REFERER']."\n";
	}else{
		//$adminBody.="問い合わせのページURL：".@$arr['httpReferer']."\n";
	}
	if($mailFooterDsp == 0) $adminBody.= $mailSignature;
	return mb_convert_encoding($adminBody,"JIS",$encode);
}

//ユーザ宛送信メールヘッダ
function userHeader($refrom_name,$to,$encode){
	$reheader = "From: ";
	if(!empty($refrom_name)){
		$default_internal_encode = mb_internal_encoding();
		if($default_internal_encode != $encode){
			mb_internal_encoding($encode);
		}
		$reheader .= mb_encode_mimeheader($refrom_name)." <".$to.">\nReply-To: ".$to;
	}else{
		$reheader .= "$to\nReply-To: ".$to;
	}
	$reheader .= "\nContent-Type: text/plain;charset=iso-2022-jp\nX-Mailer: PHP/".phpversion();
	return $reheader;
}
//ユーザ宛送信メールボディ
function mailToUser($arr,$dsp_name,$remail_text,$remail_text_bottom,$mailFooterDsp,$mailSignature,$encode){
	$userBody = '';
	if(isset($arr[$dsp_name])) $userBody = h($arr[$dsp_name]). " 様\n\n";
	$userBody.="この度は【　株式会社TAS Port（ペイワン）　】の\n";
	$userBody.="【　https://info.pay-one.jp/　】より\n";
	$userBody.= $remail_text;
	$userBody.="\n−−−−−−−−−−−−−−−−−−−−−−−−−−\n\n";
	$userBody.= postToMail($arr);//POSTデータを関数からセット
	$userBody.="\n−−−−−−−−−−−−−−−−−−−−−−−−−−\n\n";
	$userBody.= $remail_text_bottom;
	//$userBody.="送信日時：".date( "Y/m/d (D) H:i:s", time() )."\n";
	if($mailFooterDsp == 1) $userBody.= $mailSignature;
	return mb_convert_encoding($userBody,"JIS",$encode);
}
//必須チェック関数
function requireCheck($require){
	$res['errm'] = '';
	$res['empty_flag'] = 0;
	foreach($require as $requireVal){
		$existsFalg = '';
		foreach($_POST as $key => $val) {
			if($key == $requireVal) {
				
				//連結指定の項目（配列）のための必須チェック
				if(is_array($val)){
					$connectEmpty = 0;
					foreach($val as $kk => $vv){
						if(is_array($vv)){
							foreach($vv as $kk02 => $vv02){
								if($vv02 == ''){
									$connectEmpty++;
								}
							}
						}
						
					}
					if($connectEmpty > 0){
						$res['errm'] .= "<p class=\"error_messe\">【".h($key)."】は必須項目です。</p>\n";
						$res['empty_flag'] = 1;
					}
				}
				//デフォルト必須チェック
				elseif($val == ''){
					$res['errm'] .= "<p class=\"error_messe\">【".h($key)."】は必須項目です。</p>\n";
					$res['empty_flag'] = 1;
				}
				
				$existsFalg = 1;
				break;
			}
			
		}
		if($existsFalg != 1){
				$res['errm'] .= "<p class=\"error_messe\">【".$requireVal."】が未選択です。</p>\n";
				$res['empty_flag'] = 1;
		}
	}
	
	return $res;
}
//リファラチェック
function refererCheck($Referer_check,$Referer_check_domain){
	if($Referer_check == 1 && !empty($Referer_check_domain)){
		if(strpos($_SERVER['HTTP_REFERER'],$Referer_check_domain) === false){
			return exit('<p align="center">リファラチェックエラー。フォームページのドメインとこのファイルのドメインが一致しません</p>');
		}
	}
}
function copyright(){
	echo '';
}
//----------------------------------------------------------------------
//  関数定義(END)
//----------------------------------------------------------------------
?>