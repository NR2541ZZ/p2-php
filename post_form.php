<?php
/*
	p2 - ���X�������݃t�H�[��
*/

include_once './conf.inc.php';  // ��{�ݒ�t�@�C���Ǎ�
require_once './p2util.class.php';	// p2�p�̃��[�e�B���e�B�N���X
require_once './dataphp.class.php';

authorize(); //���[�U�F��

//==================================================
// �ϐ�
//==================================================
$_info_msg_ht = '';

$htm = array();

$fake_time = -10; // time ��10���O�ɋU��
$time = time() - 9*60*60;
$time = $time + $fake_time * 60;

$bbs = isset($_GET['bbs']) ? $_GET['bbs'] : '';
$key = isset($_GET['key']) ? $_GET['key'] : '';
$host = isset($_GET['host']) ? $_GET['host'] : '';

$rescount = isset($_GET['rc']) ? $_GET['rc'] : 1;
$popup = isset($_GET['popup']) ? $_GET['popup'] : 0;

$itaj = P2Util::getItaName($host, $bbs);
if (!$itaj) { $itaj = $bbs; }

$ttitle_en = isset($_GET['ttitle_en']) ? $_GET['ttitle_en'] : '';
$ttitle = (strlen($ttitle_en) > 0) ? base64_decode($ttitle_en) : '';



// ��key.idx���疼�O�ƃ��[����Ǎ���
$datdir_host = P2Util::datdirOfHost($host);
$key_idx = $datdir_host."/".$bbs."/".$key.".idx";
if ($lines = @file($key_idx)) {
	$line = explode('<>', rtrim($lines[0]));
	$line = array_map(create_function('$n', 'return htmlspecialchars($n, ENT_QUOTES);'), $line);
	$FROM = $line[7];
	$mail = $line[8];
}

// �O���POST���s������ΌĂяo��
$failed_post_file = P2Util::getFailedPostFilePath($host, $bbs, $key);
if ($cont_srd = DataPhp::getDataPhpCont($failed_post_file)) {
	$last_posted = unserialize($cont_srd);
	$last_posted = array_map('htmlspecialchars', $last_posted);
	//$addslashesS = create_function('$str', 'return str_replace("\'", "\\\'", $str);');
	//$last_posted = array_map($addslashesS, $last_posted);

	$htm['FROM'] = $last_posted['FROM'];
	$htm['mail'] = $last_posted['mail'];
	$htm['MESSAGE'] = $last_posted['MESSAGE'];
	$htm['subject'] = $last_posted['subject'];

	/*
	$htm['load_last_posted'] = <<<EOP
[<a href="javascript:void(0);" onClick="return loadLastPosted('{$last_posted['FROM']}', '{$last_posted['mail']}', '{$last_posted['MESSAGE']}');" title="�vJavaScript">�O�񓊍e���s�������e��ǂݍ���</a>]<br>
EOP;
	*/
}

// 2ch����������
if (P2Util::isHost2chs($host) and file_exists($_conf['sid2ch_php'])) {
	$isMaruChar = "��";
} else {
	$isMaruChar = "";
}

if (!$_conf['ktai']) {
	$class_ttitle = ' class="thre_title"';
	$target_read = ' target="read"';
	$sub_size_at = ' size="40"';
	$name_size_at = ' size="19"';
	$mail_size_at = ' size="19"';
	$msg_cols_at = ' cols="'.$STYLE['post_msg_cols'].'"';
} else {
	$STYLE['post_msg_rows'] = 2;
}

// �X������
if ($_GET['newthread']) {
	$ptitle = "{$itaj} - �V�K�X���b�h�쐬";
	// machibbs�AJBBS@������� �Ȃ�
	if (P2Util::isHostMachiBbs($host) or P2Util::isHostJbbsShitaraba($host)) {
		$submit_value = "�V�K��������";
	// 2ch�Ȃ�
	} else {
		$submit_value = "�V�K�X���b�h�쐬";
	}
	$subject_ht = <<<EOP
<b><span{$class_ttitle}>�^�C�g��</span></b>�F<input type="text" name="subject"{$sub_size_at} value="{$_htm['subject']}"><br>
EOP;
	if ($_conf['ktai']) {
		$subject_ht = "<a href=\"{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}{$k_at_a}\">{$itaj}</a><br>".$subject_ht;
	}
	$newthread_hidden_ht = "<input type=\"hidden\" name=\"newthread\" value=\"1\">";

// ��������
} else {
	$ptitle = "{$itaj} - ���X��������";
	
	// machibbs�AJBBS@������� �Ȃ�
	if (P2Util::isHostMachiBbs($host) or P2Util::isHostJbbsShitaraba($host)) {
		$submit_value = "��������";
	// 2ch�Ȃ�
	} else {
		$submit_value = "��������";
	}
	$ttitle_ht = <<<EOP
<p><b><a{$class_ttitle} href="{$_conf['read_php']}?host={$host}&amp;bbs={$bbs}&amp;key={$key}{$k_at_a}"{$target_read}>{$ttitle}</a></b></p>
EOP;
}

// Be.2ch
if (P2Util::isHost2chs($host) and $_conf['be_2ch_code'] && $_conf['be_2ch_mail']) {
	$htm['be2ch'] = '<input type="checkbox" id="post_be2ch" name="post_be2ch" value="1"><label for="post_be2ch">Be.2ch�̃R�[�h�𑗐M</label>'."\n";
}

//==========================================================
// ��HTML�v�����g
//==========================================================
if (!$_conf['ktai']) {
	$body_on_load = <<<EOP
 onLoad="setFocus('MESSAGE'); checkSage();"
EOP;
	$on_check_sage = 'onChange="checkSage();"';
	$sage_cb_ht=<<<EOP
<input id="sage" type="checkbox" onClick="mailSage();"><label for="sage">sage</label><br>
EOP;
}

header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOHEADER
<html lang="ja">
<head>
	{$_conf['meta_charset_ht']}
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<title>{$ptitle}</title>\n
EOHEADER;
if(!$_conf['ktai']){
	@include("style/style_css.inc"); // �X�^�C���V�[�g
	@include("style/post_css.inc"); // �X�^�C���V�[�g
echo <<<EOSCRIPT
	<script type="text/javascript" src="js/basic.js"></script>
	<script type="text/javascript" src="js/post_form.js"></script>
EOSCRIPT;
}
echo <<<EOP
</head>
<body{$body_on_load}>
EOP;

echo $_info_msg_ht;
$_info_msg_ht = "";

// �����R�[�h����p�������擪�Ɏd���ނ��Ƃ�mb_convert_variables()�̎��������������
echo <<<EOP
{$ttitle_ht}
<form method="POST" action="./post.php" accept-charset="{$_conf['accept_charset']}">
	<input type="hidden" name="detect_hint" value="����">
	{$subject_ht}
	{$isMaruChar}���O�F <input name="FROM" type="text" value="{$htm['FROM']}"{$name_size_at}> 
	 E-mail : <input id="mail" name="mail" type="text" value="{$htm['mail']}"{$mail_size_at}{$on_check_sage}>
	{$sage_cb_ht}
	<textarea id="MESSAGE" name="MESSAGE" rows="{$STYLE['post_msg_rows']}"{$msg_cols_at} wrap="off">{$htm['MESSAGE']}</textarea>	
	<input type="submit" name="submit" value="{$submit_value}"><br>
	{$htm['be2ch']}

	<input type="hidden" name="bbs" value="{$bbs}">
	<input type="hidden" name="key" value="{$key}">
	<input type="hidden" name="time" value="{$time}">
	
	<input type="hidden" name="host" value="{$host}">
	<input type="hidden" name="popup" value="{$popup}">
	<input type="hidden" name="rescount" value="{$rescount}">
	<input type="hidden" name="ttitle_en" value="{$ttitle_en}">
	{$newthread_hidden_ht}
	{$k_input_ht}
</form>
</body>
</html>
EOP;

?>