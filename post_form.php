<?php
/*
	p2 - ���X�������݃t�H�[��
*/

include_once './conf/conf.inc.php';  // ��{�ݒ�t�@�C���Ǎ�
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
$ttitle_hd = htmlspecialchars($ttitle);


// ��key.idx���疼�O�ƃ��[����Ǎ���
$datdir_host = P2Util::datdirOfHost($host);
$key_idx = $datdir_host."/".$bbs."/".$key.".idx";
if ($lines = @file($key_idx)) {
	$line = explode('<>', rtrim($lines[0]));
	$hd['FROM'] = htmlspecialchars($line[7], ENT_QUOTES);
	$hd['mail'] = htmlspecialchars($line[8], ENT_QUOTES);
}

// �O���POST���s������ΌĂяo��
$failed_post_file = P2Util::getFailedPostFilePath($host, $bbs, $key);
if ($cont_srd = DataPhp::getDataPhpCont($failed_post_file)) {
	$last_posted = unserialize($cont_srd);
	
	// �܂Ƃ߂ăT�j�^�C�Y
	$last_posted = array_map(create_function('$n', 'return htmlspecialchars($n, ENT_QUOTES);'), $last_posted);
	//$addslashesS = create_function('$str', 'return str_replace("\'", "\\\'", $str);');
	//$last_posted = array_map($addslashesS, $last_posted);

	$hd['FROM'] = $last_posted['FROM'];
	$hd['mail'] = $last_posted['mail'];
	$hd['MESSAGE'] = $last_posted['MESSAGE'];
	$hd['subject'] = $last_posted['subject'];

	/*
	$htm['load_last_posted'] = <<<EOP
[<a href="javascript:void(0);" onClick="return loadLastPosted('{$last_posted['FROM']}', '{$last_posted['mail']}', '{$last_posted['MESSAGE']}');" title="�vJavaScript">�O�񓊍e���s�������e��ǂݍ���</a>]<br>
EOP;
	*/
}

// �󔒂̓��[�U�ݒ�l�ɕϊ�
$hd['FROM'] = ($hd['FROM'] == '') ? htmlspecialchars($_conf['my_FROM'], ENT_QUOTES) : $hd['FROM'];
$hd['mail'] = ($hd['mail'] == '') ? htmlspecialchars($_conf['my_mail'], ENT_QUOTES) : $hd['mail'];
	
// P2NULL�͋󔒂ɕϊ�
$hd['FROM'] = ($hd['FROM'] == 'P2NULL') ? '' : $hd['FROM'];
$hd['mail'] = ($hd['mail'] == 'P2NULL') ? '' : $hd['mail'];

// 2ch����������
if (P2Util::isHost2chs($host) and file_exists($_conf['sid2ch_php'])) {
	$isMaruChar = '��';
} else {
	$isMaruChar = '';
}

if (!$_conf['ktai']) {
	$class_ttitle = ' class="thre_title"';
	$target_read = ' target="read"';
	$sub_size_at = ' size="40"';
	$name_size_at = ' size="19"';
	$mail_size_at = ' size="19"';
	$msg_cols_at = ' cols="'.$STYLE['post_msg_cols'].'"';
} else {
	$STYLE['post_msg_rows'] = 3;
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
	$htm['subject'] = <<<EOP
<b><span{$class_ttitle}>�^�C�g��</span></b>�F<input type="text" name="subject"{$sub_size_at} value="{$hd['subject']}"><br>
EOP;
	if ($_conf['ktai']) {
		$htm['subject'] = "<a href=\"{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}{$_conf['k_at_a']}\">{$itaj}</a><br>".$subject_ht;
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
	$htm['resform_ttitle'] = <<<EOP
<p><b><a{$class_ttitle} href="{$_conf['read_php']}?host={$host}&amp;bbs={$bbs}&amp;key={$key}{$_conf['k_at_a']}"{$target_read}>{$ttitle_hd}</a></b></p>
EOP;
	$newthread_hidden_ht = '';
}

$readnew_hidden_ht = !empty($_GET['from_read_new']) ? '<input type="hidden" name="from_read_new" value="1">' : '';

// Be.2ch
if (P2Util::isHost2chs($host) and $_conf['be_2ch_code'] && $_conf['be_2ch_mail']) {
	/*
	$checked = '';
	if (P2Util::isHostBe2chNet($host)) {
		$checked = ' checked';
	}
	*/
	$htm['be2ch'] = '<input type="submit" name="submit_beres" value="BE�ŏ�������">';
	// $htm['be2ch'] = '<input type="checkbox" id="post_be2ch" name="post_be2ch" value="1"'.$checked.'><label for="post_be2ch">Be.2ch�̃R�[�h�𑗐M</label><br>'."\n";
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

P2Util::header_content_type();
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
<body{$body_on_load}>\n
EOP;

echo $_info_msg_ht;
$_info_msg_ht = '';

// �����R�[�h����p�������擪�Ɏd���ނ��Ƃ�mb_convert_variables()�̎��������������
echo <<<EOP
{$htm['resform_ttitle']}
<form method="POST" action="./post.php" accept-charset="{$_conf['accept_charset']}">
	<input type="hidden" name="detect_hint" value="����">
	{$htm['subject']}
	{$isMaruChar}���O�F <input id="FROM" name="FROM" type="text" value="{$hd['FROM']}"{$name_size_at}> 
	 E-mail : <input id="mail" name="mail" type="text" value="{$hd['mail']}"{$mail_size_at}{$on_check_sage}>
	{$sage_cb_ht}
	<textarea id="MESSAGE" name="MESSAGE" rows="{$STYLE['post_msg_rows']}"{$msg_cols_at} wrap="off">{$hd['MESSAGE']}</textarea>
	<input type="submit" name="submit" value="{$submit_value}">
	{$htm['be2ch']}
	<br>

	<input type="hidden" name="bbs" value="{$bbs}">
	<input type="hidden" name="key" value="{$key}">
	<input type="hidden" name="time" value="{$time}">
	
	<input type="hidden" name="host" value="{$host}">
	<input type="hidden" name="popup" value="{$popup}">
	<input type="hidden" name="rescount" value="{$rescount}">
	<input type="hidden" name="ttitle_en" value="{$ttitle_en}">
	{$newthread_hidden_ht}{$readnew_hidden_ht}
	{$_conf['k_input_ht']}
</form>\n
EOP;

echo '</body></html>';

?>