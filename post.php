<?php
// p2 - ���X��������

require_once("./conf.php");  //��{�ݒ�t�@�C���Ǎ�
require_once './p2util.class.php';	// p2�p�̃��[�e�B���e�B�N���X
require_once './filectl.class.php';
require_once("./datactl.inc");

authorize(); //���[�U�F��

//================================================================
// �ϐ�
//================================================================
$_info_msg_ht = "";
$newtime = date("gis"); 

$FROM = $_POST['FROM'];
$mail = $_POST['mail'];
$MESSAGE = $_POST['MESSAGE'];

$bbs = $_POST['bbs'];
$key = $_POST['key'];
$time = $_POST['time'];

$host = $_POST['host'];
$popup = $_POST['popup'];
$rescount = $_POST['rescount'];

$subject = $_POST['subject'];
$submit = $_POST['submit'];

$sub = $_POST['sub'];

$_POST['ttitle_en'] && $ttitle_en = $_POST['ttitle_en'];
if (!$ttitle) {
	if ($ttitle_en) {
		$ttitle = base64_decode($ttitle_en);
	} elseif ($subject) {
		$ttitle = $subject;
	}
}

// magic_quates ����
if (get_magic_quotes_gpc()) {
	$FROM = stripslashes($FROM);
	$mail = stripslashes($mail);
	$MESSAGE = stripslashes($MESSAGE);
	$subject = stripslashes($subject);
	$submit = stripslashes($submit);
}

// �����e�[�u�����g���������R�[�h����
if (isset($_POST['binyu']) && extension_loaded('mbstring')) {
	$encoding = mb_detect_encoding($_POST['binyu'], 'JIS,UTF-8,EUC-JP,SJIS');
	if ($encoding != 'SJIS') {
		$FROM = mb_convert_encoding($FROM, 'SJIS-win', $encoding);
		$mail = mb_convert_encoding($mail, 'SJIS-win', $encoding);
		$MESSAGE = mb_convert_encoding($MESSAGE, 'SJIS-win', $encoding);
		$subject = mb_convert_encoding($subject, 'SJIS-win', $encoding);
		$submit = mb_convert_encoding($submit, 'SJIS-win', $encoding);
	}
}

// Safari���`���_�ƃo�b�N�X���b�V����S�p�ɕϊ�����̂��C��
if (P2Util::isBrowserSafariGroup()) {
	if (isset($_POST['fix_tilde'])  && $_POST['fix_tilde']  == "1") {
		$MESSAGE = str_replace('�`', '~', $MESSAGE);
	}
	if (isset($_POST['fix_bslash']) && $_POST['fix_bslash'] == "1") {
		$MESSAGE = str_replace('�_', '\\', $MESSAGE);
	}
}

/*
// 2004/12/13 ���ɕK�v�Ȃ����ȂƂ������ƂŃR�����g�A�E�g

// ���b�Z�[�W�ɘA���������p�X�y�[�X������΁A&nbsp;�ɕϊ�����
$MESSAGE = preg_replace_callback(
	'/ {2,}/',
    create_function(
	   '$matches',
	   'return str_replace(" ", "&nbsp;", $matches[0]);'
	),
	$MESSAGE
);
*/

// p2_cookie.txt �ǂݍ��� ===================================
$cookie_file = $prefdir."/p2_cookie/{$_POST['host']}/p2_cookie.txt";
$cookie_cont = FileCtl::get_file_contents($cookie_file);
if ($cookie_cont) {
	$p2cookies = unserialize($cookie_cont);
	if ($p2cookies['expires']) {
		if (time() > strtotime($p2cookies['expires'])) { // �����؂�Ȃ�j��
			// echo "<p>�����؂�̃N�b�L�[���폜���܂���</p>";
			unlink($cookie_file);
			unset($cookie_cont, $p2cookies);
		}
	}
}

// ������΂�livedoor�ړ]�ɑΉ��Bpost���livedoor�Ƃ���B
$host = P2Util::adjustHostJbbs($host);

// machibbs�AJBBS@������� �Ȃ�
if (P2Util::isHostMachiBbs($host) or P2Util::isHostJbbsShitaraba($host)) {
	$bbs_cgi = "/bbs/write.cgi";
	
	// JBBS@������� �Ȃ�
	if (P2Util::isHostJbbsShitaraba($host)) {	
		$bbs_cgi = "../../bbs/write.cgi";
		preg_match("/(\w+)$/", $host, $ar);
		$dir = $ar[1];
		$dir_k = "DIR";
	}
	
	$submit_k = "submit";
	$bbs_k = "BBS";
	$key_k = "KEY";
	$time_k = "TIME";
	$FROM_k = "NAME";
	$mail_k = "MAIL";
	$MESSAGE_k = "MESSAGE";
	$subject_k = "SUBJECT";
	
// 2ch
} else { 
	if ($sub) {
		$bbs_cgi = "/test/{$sub}bbs.cgi";
	} else {
		$bbs_cgi = "/test/bbs.cgi";
	}
	$submit_k = "submit";
	$bbs_k = "bbs";
	$key_k = "key";
	$time_k = "time";
	$FROM_k = "FROM";
	$mail_k = "mail";
	$MESSAGE_k = "MESSAGE";
	$subject_k = "subject";

}

if($_POST['newthread']){
	$post = array($submit_k=>$submit, $bbs_k=>$bbs, $subject_k=>$subject, $time_k=>$time, $FROM_k=>$FROM, $mail_k=>$mail, $MESSAGE_k=>$MESSAGE);
	if( P2Util::isHostJbbsShitaraba($host) ){
		$post[$dir_k] = $dir;
	}
	$location_ht = "{$subject_php}?host={$host}&amp;bbs={$bbs}{$k_at_a}";
}else{
	$post = array($submit_k=>$submit, $bbs_k=>$bbs, $key_k=>$key, $time_k=>$time, $FROM_k=>$FROM, $mail_k=>$mail, $MESSAGE_k=>$MESSAGE);
	if( P2Util::isHostJbbsShitaraba($host) ){
		$post[$dir_k] = $dir;
	}
	$location_ht = "{$_conf['read_php']}?host={$host}&amp;bbs={$bbs}&amp;key={$key}&amp;ls={$rescount}-&amp;nt={$newtime}{$k_at_a}#r{$rescount}";
}
//JavaScript�Ɋ܂܂��鎞��url�G���R�[�h���Ă͋t�Ƀ_���Ɓi&amp;�j

//2ch�Ł����O�C�����Ȃ�sid�ǉ�
if( P2Util::isHost2chs($host) and file_exists($sid2ch_php) ){
	$isMaruChar="��";
	
	if( file_exists($idpw2ch_php) and @filemtime($sid2ch_php) < time() - 60*60*24){	//���O�C����A24���Ԉȏ�o�߂��Ă����玩���ă��O�C��
		include_once("./login2ch.inc");
		login2ch();
	}
	
	include($sid2ch_php);
	$post['sid'] = $SID2ch;
}else{
	$isMaruChar="";
}

if($_POST['newthread']){
	$ptitle="p2 - �V�K�X���b�h�쐬";
}else{
	$ptitle="p2 - ���X��������";
}

//================================================================
// �������ݏ���
//================================================================

//=============================================
// �|�X�g���s 
//=============================================
$posted = postIt($URL, $request);

//=============================================
// cookie �ۑ�
//=============================================
FileCtl::make_datafile($cookie_file, $p2_perm); //�Ȃ���ΐ���
if ($p2cookies) {$cookie_cont=serialize($p2cookies);}
if ($cookie_cont) {
	$fp = @fopen($cookie_file, "wb") or die("Error: $cookie_file ���X�V�ł��܂���ł���");
	fputs($fp, $cookie_cont);
	fclose($fp);
}

//=============================================
// �X�����Đ����Ȃ�Asubject����key���擾
//=============================================
if ($_POST['newthread'] && $posted) {
	sleep(1);
	$key = getKeyInSubject();
}

//=============================================
// key.idx �ۑ�
//=============================================
if ($host && $bbs && $key) {
	$datdir_host = datdirOfHost($host);
	$keyidx = $datdir_host."/".$bbs."/".$key.".idx";
	
	// �ǂݍ���
	if ($keylines = @file($keyidx)) {
		$akeyline = explode('<>', rtrim($keylines[0]));
	}
	$s = "$akeyline[0]<>$akeyline[1]<>$akeyline[2]<>$akeyline[3]<>$akeyline[4]<>$akeyline[5]<>$akeyline[6]<>$FROM<>$mail<>$akeyline[9]";
	setKeyIdx($keyidx, $s); // key.idx�ɋL�^
}

//=============================================
// �������ݗ���
//=============================================
if (!$posted) { return; }

if ($host && $bbs && $key) {
	
	$rh_idx = $prefdir."/p2_res_hist.idx";
	FileCtl::make_datafile($rh_idx, $res_write_perm); //�Ȃ���ΐ���
	
	//�ǂݍ���;
	$lines = @file($rh_idx);
	
	//�ŏ��ɏd���v�f���폜
	if ($lines) {
		foreach ($lines as $line) {
			$line = rtrim($line);
			$lar = explode('<>', $line);
			if ($lar[1] == $key) { continue; } // �d�����
			if (!$lar[1]) { continue; } // key�̂Ȃ����͕̂s���f�[�^
			$neolines[] = $line;
		}
	}
	
	// �V�K�f�[�^�ǉ�
	$newdata = "$ttitle<>$key<><><><><><>$FROM<>$mail<><>$host<>$bbs";
	$neolines ? array_unshift($neolines, $newdata) : $neolines = array($newdata);
	while (sizeof($neolines) > $res_hist_rec_num) {
		array_pop($neolines);
	}
	
	// ��������
	$fp = @fopen($rh_idx, "wb") or die("Error: {$rh_idx} ���X�V�ł��܂���ł���");
	if ($neolines) {
		foreach ($neolines as $l) {
			fputs($fp, $l."\n");
		}
	}
	fclose($fp);

}

//=============================================
// �������݃��O
//=============================================
if ($res_write_rec) {

	// ���`���̏������ݗ�����V�`���ɕϊ�����
	P2Util::transResHistLog();

	$date_and_id = date("y/m/d H:i");
	$message = htmlspecialchars($MESSAGE, ENT_NOQUOTES);
	$message = preg_replace("/\r?\n/", "<br>", $message);

	$p2_res_hist_dat_php = $prefdir."/p2_res_hist.dat.php"; 
	FileCtl::make_datafile($p2_res_hist_dat_php, $res_write_perm); // �Ȃ���ΐ���

	// �ǂݍ����
	if (!$lines = P2Util::fileDataPhp($p2_res_hist_dat_php)) {
		$lines = array();
	}

	// �V�K�f�[�^
	$newdata = "$FROM<>$mail<>$date_and_id<>$message<>$ttitle<>$host<>$bbs<>$key";

	// �܂��^�u��S�ĊO���āi2ch�̏������݂ł̓^�u�͍폜����� 2004/12/13�j
	$newdata = str_replace("\t", "", $newdata);
	// <>���^�u�ɕϊ�����
	$newdata = str_replace("<>", "\t", $newdata);
			
	// &<>/ �� &xxx; �ɃG�X�P�[�v����
	$newdata = P2Util::escapeDataPhp($newdata);

	// �V�����f�[�^��ǉ�
	@array_push($lines, $newdata);
	// �擪����ǉ�
	@array_unshift($lines, '<?php /*');
	// ������ǉ�
	@array_push($lines, '*/ ?>');

	
	// ��������
	$fp = @fopen($p2_res_hist_dat_php, "wb") or die("Error: {$p2_res_hist_dat_php} ���X�V�ł��܂���ł���");
	flock($fp, LOCK_EX);
	foreach ($lines as $l) {
		fputs($fp, $l."\n");
	}
	flock($fp, LOCK_UN);
	fclose($fp);
}

//===========================================================
// �֐�
//===========================================================

/**
 * ���X�������݊֐�
 */
function postIt($URL, $request)
{
	global $_conf, $post_result, $post_error2ch, $p2cookies, $bbs, $host, $popup, $rescount, $ttitle_en, $STYLE, $fsockopen_time_limit, $proxy;
	global $ktai, $bbs_cgi, $post;
	
	$method = "POST";
	$url = "http://" . $host.  $bbs_cgi;
	
	$URL = parse_url($url); // URL����
	if (isset($URL['query'])) { // �N�G���[
	    $URL['query'] = "?".$URL['query'];
	} else {
	    $URL['query'] = "";
	}

	// �v���L�V
	if ($proxy['use']) {
		$send_host = $proxy['host'];
		$send_port = $proxy['port'];
		$send_path = $url;
	} else {
		$send_host = $URL['host'];
		$send_port = $URL['port'];
		$send_path = $URL['path'].$URL['query'];
	}

	if (!$send_port) {$send_port = 80;}	// �f�t�H���g��80
		
	$request = $method." ".$send_path." HTTP/1.0\r\n";
	$request .= "Host: ".$URL['host']."\r\n";
	$request .= "User-Agent: Monazilla/1.00 (".$_conf['p2name']."/".$_conf['p2version'].")"."\r\n";
	$request .= "Referer: http://".$URL['host']."/\r\n";
	
	// �N���C�A���g��IP�𑗐M����p2�Ǝ��̃w�b�_
	$request .= "p2-Client-IP: ".$_SERVER['REMOTE_ADDR']."/\r\n";
	
	// �N�b�L�[
	$cookies_to_send = "";
	if ($p2cookies) {
		foreach ($p2cookies as $cname => $cvalue) {
			if ($cname != "expires") {
				$cookies_to_send .= " {$cname}={$cvalue};";
			}
		}
	}
	
	// be.2ch.net �F�؃N�b�L�[
	if (P2Util::isHostBe2chNet($host)) {
		$cookies_to_send .= ' MDMD='.$_conf['be_2ch_code'].';';	// be.2ch.net�̔F�؃R�[�h(�p�X���[�h�ł͂Ȃ�)
		$cookies_to_send .= ' DMDM='.$_conf['be_2ch_mail'].';';	// be.2ch.net�̓o�^���[���A�h���X
	}
	
	if (!$cookies_to_send) { $cookies_to_send = ' ;'; }
	$request .= 'Cookie:'.$cookies_to_send."\r\n";
	//$request .= 'Cookie: PON='.$SPID.'; NAME='.$FROM.'; MAIL='.$mail."\r\n";
	
	$request .= "Connection: Close\r\n";
	
	/* POST�̎��̓w�b�_��ǉ����Ė�����URL�G���R�[�h�����f�[�^��Y�t */
	if (strtoupper($method) == "POST") {
	    while (list($name, $value) = each($post)) {
		
			// ������� or be.2ch.net�Ȃ�AEUC�ɕϊ�
			if (P2Util::isHostJbbsShitaraba($host) || P2Util::isHostBe2chNet($host)) {
				include_once './strctl.class.php';
				$value = StrCtl::p2SJIStoEUC($value);
			}

	        $POST[] = $name."=".urlencode($value);
	    }
	    $postdata = implode("&", $POST);
	    $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
	    $request .= "Content-Length: ".strlen($postdata)."\r\n";
	    $request .= "\r\n";
	    $request .= $postdata;
	} else {
	    $request .= "\r\n";
	}

	/* WEB�T�[�o�֐ڑ� */
	$fp = fsockopen($send_host, $send_port, $errno, $errstr, $fsockopen_time_limit);
	if (!$fp) {
		showPostMsg(false, "�T�[�o�ڑ��G���[: $errstr ($errno)<br>p2 Error: �T�[�o�ւ̐ڑ��Ɏ��s���܂���", false);
		return false;
	}
	
	//echo '<h4>$request</h4><p>' . $request . "</p>"; //for debug
	fputs($fp, $request);
	
	while (!feof($fp)) {
	
		if ($start_here) {

			while (!feof($fp)) {
				$wr .= fread($fp, 164000);
			}
			$response = $wr;
			break;

		} else {
			$l = fgets($fp, 164000);
			//echo $l ."<br>"; //for debug
			$response_header_ht .= $l."<br>";
			// �N�b�L�[�L�^
			if (preg_match("/Set-Cookie: (.+?)\r\n/", $l, $matches)) {
				//echo "<p>".$matches[0]."</p>"; //
				$cgroups = explode(";", $matches[1]);
				if ($cgroups) {
					foreach ($cgroups as $v) {
						if (preg_match("/(.+)=(.*)/", $v, $m)) {
							$k = ltrim($m[1]);
							if ($k != "path") {
								$p2cookies[$k] = $m[2];
							}
						}
					}
				}
				if ($p2cookies) {
					unset($cookies_to_send);
					foreach ($p2cookies as $cname => $cvalue) {
						if ($cname != "expires") {
							$cookies_to_send .= " {$cname}={$cvalue};";
						}
					}
					$newcokkies = "Cookie:{$cookies_to_send}\r\n";
					
					$request = preg_replace("/Cookie: .*?\r\n/", $newcokkies, $request);
				}

			// �]���͏������ݐ����Ɣ��f
			} elseif (preg_match("/^Location: /", $l, $matches)){
				$post_seikou = true;
			}
			if ($l == "\r\n") {
				$start_here = true;
			}
		}
		
	}
	fclose($fp);
	
	// be.2ch.net �����R�[�h�ϊ� EUC��SJIS
	if (P2Util::isHostBe2chNet($host)) {
		$response = StrCtl::p2EUCtoSJIS($response);
		
		//<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
		$response = preg_replace("{(<head>.*<META http-equiv=\"Content-Type\" content=\"text/html; charset=)EUC-JP(\">.*</head>)}is", "$1Shift_JIS$2", $response);
	}

	$kakikonda_match = "/<title>.*(�������݂܂���|�� �������݂܂��� ��|�������ݏI�� - SubAll BBS).*<\/title>/";
	$cookie_kakunin_match = "/<!-- 2ch_X:cookie -->|<title>�� �������݊m�F ��<\/title>|>�������݊m�F�B</";
	
	if (eregi("(<.+>)", $response, $matches)) {
		$response = $matches[1];
	}
	
	// �J�L�R�~����
	if (preg_match($kakikonda_match, $response, $matches) or $post_seikou) {
		showPostMsg(true, "�������݂��I���܂����B", true);
		return true;
		//$response_ht = htmlspecialchars($response);
		//echo "<pre>{$response_ht}</pre>";
		
	// cookie�m�F
	} elseif (preg_match($cookie_kakunin_match, $response, $matches)) {

		if ($_POST['newthread']) {
			$newthread_hidden_ht = "<input type=\"hidden\" name=\"newthread\" value=\"1\">";
		}
		// mbstring�L�����ASafari/Konqueror��UTF-8�œ��e���邱�Ƃ� �o�b�N�X���b�V���ƃ`���_���S�p�ɂȂ�̂�h���y\~�_�`�z 
		$accept_charset_ht = '';
		if (extension_loaded('mbstring') && P2Util::isBrowserSafariGroup()) {
			$accept_charset_ht = ' accept-charset="UTF-8"';
		}
		$response = preg_replace("/<form method=\"?POST\"? action=\"?\.\.\/test\/(sub)?bbs\.cgi\"?>/i", "<form method=\"POST\" action=\"./post.php\"{$accept_charset_ht}><input type=\"hidden\" name=\"host\" value=\"{$host}\"><input type=\"hidden\" name=\"popup\" value=\"{$popup}\"><input type=\"hidden\" name=\"rescount\" value=\"{$rescount}\"><input type=\"hidden\" name=\"ttitle_en\" value=\"{$ttitle_en}\"><input type=\"hidden\" name=\"sub\" value=\"\\1\"><input type=\"hidden\" name=\"binyu\" value=\"����\">{$newthread_hidden_ht}", $response);
	
		$h_b = explode("</head>", $response);
		echo $h_b[0];
		if (!$ktai) {
			@include("style/style_css.inc"); //�X�^�C���V�[�g
			@include("style/post_css.inc"); //�X�^�C���V�[�g
		}
		if ($popup) {
			$mado_okisa = explode(",", $STYLE['post_pop_size']);
			$mado_okisa_x = $mado_okisa[0];
			$mado_okisa_y = $mado_okisa[1]+200;
			echo <<<EOSCRIPT
			<script language="JavaScript">
			<!--
				resizeTo({$mado_okisa_x},{$mado_okisa_y});
			// -->
			</script>
EOSCRIPT;
		}
		
		echo "</head>";
		echo $h_b[1];
		return false;

	// ���̑��̓��X�|���X�����̂܂ܕ\��
	} else {
		$response = ereg_replace('������Ń����[�h���Ă��������B<a href="\.\./[a-z]+/index\.html"> GO! </a><br>', "", $response);
		echo $response;
		return false;
	}
}

/**
 * �������ݏ������ʂ�\������
 */
function showPostMsg($isDone, $result_msg, $reload)
{
	global $location_ht, $popup, $STYLE, $ttitle;
	global $ktai, $_info_msg_ht;
	
	//�v�����g�p�ϐ�===============
	if (!$ktai) {
		$class_ttitle = " class=\"thre_title\"";
	}
	$ttitle_ht = "<b{$class_ttitle}>{$ttitle}</b>";
	
	if($popup){
		$location_js = preg_replace("/&amp;/", "&", $location_ht);
		$popup_ht = <<<EOJS
<script language="JavaScript">
<!--
	opener.location.href="{$location_js}";
	var delay= 3*1000;
	setTimeout("window.close()", delay);
// -->
</script>
EOJS;

	} else {
		$meta_refresh_ht = <<<EOP
		<meta http-equiv="refresh" content="1;URL={$location_ht}">
EOP;
	}

	// �v�����g ==============
	header_content_type();
	if ($doctype) { echo $doctype; }
	echo <<<EOHEADER
<html>
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
{$meta_refresh_ht}
EOHEADER;

	if ($isDone) {
		echo "<title>p2 - �������݂܂����B</title>";
	} else {
		echo "<title>{$ptitle}</title>";
	}

	if (!$ktai) {
		@include("style/style_css.inc"); //�X�^�C���V�[�g
		@include("style/post_css.inc"); //�X�^�C���V�[�g
		if ($popup) {
			echo <<<EOSCRIPT
			<script language="JavaScript">
			<!--
				resizeTo({$STYLE['post_pop_size']});
			// -->
			</script>
EOSCRIPT;
		}
		if ($reload) {
			echo $popup_ht;
		}
	} else {
		$kakunin_ht = <<<EOP
<p><a href="{$location_ht}">�m�F</a></p>
EOP;
	}
	
	echo <<<EOP
</head>
<body>
EOP;

echo $_info_msg_ht;
$_info_msg_ht="";

echo <<<EOP
<p>{$ttitle_ht}</p>
<p>{$result_msg}</p>
{$kakunin_ht}
</body>
</html>
EOP;
}

/**
 * subject����key���擾����
 */
function getKeyInSubject()
{
	global $host, $bbs, $ttitle;

	$datdir_host = datdirOfHost($host);
	$subject_url = "http://{$host}/{$bbs}/subject.txt";
	$subjectfile = $datdir_host."/".$bbs."/subject.txt";
	FileCtl::mkdir_for($subjectfile); // �f�B���N�g����������΍��
	P2Util::subjectDownload($subject_url, $subjectfile);
	if (extension_loaded('zlib') and strstr($host, ".2ch.net")) {
		$subject_txt_lines = @gzfile($subjectfile);
	} else {
		$subject_txt_lines = @file($subjectfile);
	}
	foreach ($subject_txt_lines as $l) {
		if (strstr($l, $ttitle)) {
			if (preg_match("/^([0-9]+)\.(dat|cgi)(,|<>)(.+) ?(\(|�i)([0-9]+)(\)|�j)/", $l, $matches)) {
				return $key = $matches[1];
			}
		}
	}
	return false;
}

?>