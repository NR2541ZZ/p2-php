<?php
/*
	p2 -  �ݒ�ҏW
*/

include_once './conf.inc.php';  //��{�ݒ�
require_once './filectl.class.php';
require_once './p2util.class.php';

authorize(); //���[�U�F��

$_info_msg_ht = "";

// �z�X�g�̓����p�ݒ�
if (!isset($rh_idx))     { $rh_idx     = $_conf['pref_dir'] . '/p2_res_hist.idx'; }
if (!isset($palace_idx)) { $palace_idx = $_conf['pref_dir'] . '/p2_palace.idx'; }

$synctitle = array(
	$_conf['favita_path'] => '���C�ɔ�',
	$_conf['favlist_file'] => '���C�ɃX��',
	$_conf['rct_file']     => '�ŋߓǂ񂾃X��',
	$rh_idx      => '�������ݗ���',
	$palace_idx  => '�X���̓a��',
);

if (isset($_POST['sync'])) {
	$syncfile = $_POST['sync'];
	if ($syncfile == $_conf['favita_path']) {
		include_once './syncfavita.inc';
	} elseif (in_array($syncfile, array($_conf['favlist_file'], $_conf['rct_file'], $rh_idx, $palace_idx))) {
		include_once './syncindex.inc';
	}
	if ($sync_ok) {
		$_info_msg_ht .= "<p>{$synctitle[$syncfile]}�𓯊����܂����B</p>";
	} else {
		$_info_msg_ht .= "<p>{$synctitle[$syncfile]}�͕ύX����܂���ł����B</p>";
	}
}

// �����o���p�ϐ�========================================
$ptitle = "�ݒ�t�@�C���ҏW";

if ($_conf['ktai']) {
	$status_st = '�ð��';
	$autho_user_st = '�F��հ��';
	$client_host_st = '�[��ν�';
	$client_ip_st = '�[��IP���ڽ';
	$browser_ua_st = '��׳��UA';
	$p2error_st = 'p2 �װ';
} else {
	$status_st = '�X�e�[�^�X';
	$autho_user_st = '�F�؃��[�U';
	$client_host_st = '�[���z�X�g';
	$client_ip_st = '�[��IP�A�h���X';
	$browser_ua_st = '�u���E�UUA';
	$p2error_st = 'p2 �G���[';
}

$autho_user_ht = "";

//=========================================================
// HTML�v�����g
//=========================================================
P2Util::header_nocache();
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html>
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<title>{$ptitle}</title>
EOP;
if(!$_conf['ktai']){
	@include("./style/style_css.inc");
	@include("./style/editpref_css.inc");
}
echo <<<EOP
</head>
<body>
EOP;

if (!$_conf['ktai']) {
	echo <<<EOP
<p id="pan_menu"><a href="setting.php">�ݒ�</a> &gt; {$ptitle}</p>
EOP;
}


echo $_info_msg_ht;
$_info_msg_ht = "";

//�ݒ�v�����g=====================
$aborn_name_txt = $_conf['pref_dir']."/p2_aborn_name.txt";
$aborn_mail_txt = $_conf['pref_dir']."/p2_aborn_mail.txt";
$aborn_msg_txt = $_conf['pref_dir']."/p2_aborn_msg.txt";
$aborn_id_txt = $_conf['pref_dir']."/p2_aborn_id.txt";
$ng_name_txt = $_conf['pref_dir']."/p2_ng_name.txt";
$ng_mail_txt = $_conf['pref_dir']."/p2_ng_mail.txt";
$ng_msg_txt = $_conf['pref_dir']."/p2_ng_msg.txt";
$ng_id_txt = $_conf['pref_dir']."/p2_ng_id.txt";

if (!$_conf['ktai']) {

	echo <<<EOP
<table><tr><td>

<fieldset>
<legend><a href="http://akid.s17.xrea.com:8080/p2puki/pukiwiki.php?%5B%5BNG%A5%EF%A1%BC%A5%C9%A4%CE%C0%DF%C4%EA%CA%FD%CB%A1%5D%5D" target="read">NG���[�h</a>�ҏW�F</legend>
<table><tr><td>
EOP;
	printEditFileForm($ng_name_txt, "���O");
	echo "</td><td>";
	printEditFileForm($ng_mail_txt, "���[��");
	echo "</td><td>";
	printEditFileForm($ng_msg_txt, "���b�Z�[�W");
	echo "</td><td>";
	printEditFileForm($ng_id_txt, " I D ");
	echo <<<EOP
</td></tr></table>
</fieldset>

</td><td>

<fieldset>
<legend>���ځ[�񃏁[�h�ҏW</legend>
<table><tr><td>
EOP;
	printEditFileForm($aborn_name_txt, "���O");
	echo "</td><td>";
	printEditFileForm($aborn_mail_txt, "���[��");
	echo "</td><td>";
	printEditFileForm($aborn_msg_txt, "���b�Z�[�W");
	echo "</td><td>";
	printEditFileForm($aborn_id_txt, " I D ");
	echo <<<EOP
</td>
</tr></table>
</fieldset>

</td></tr><tr><td colspan="2">
EOP;

	if( is_writable("conf_user.inc.php") || is_writable("conf_user_style.inc.php") || is_writable("conf.inc.php")){
		echo <<<EOP
<fieldset>
<legend>���̑�</legend>
<table><tr>
<td>
EOP;
		if (is_writable("conf_user.inc.php")) {
			printEditFileForm("conf_user.inc.php", "conf_user.inc.php");
		}
		echo "</td><td>";
		if (is_writable("conf_user_style.inc.php")) {
			printEditFileForm("conf_user_style.inc.php", "conf_user_style.inc.php");
		}
		echo "</td><td>";
		if (is_writable("conf.inc.php")) {
			printEditFileForm("conf.inc.php", "conf.inc.php");
		}
		echo <<<EOP
</td>
</tr></table>
</fieldset>
EOP;
	}

	echo <<<EOP
</td></tr><tr><td colspan="2">

<fieldset>
<legend>�z�X�g�̓����i2ch�̔ړ]�ɑΉ����܂��j</legend>
<table><tr>
EOP;
	foreach ($synctitle as $syncpath => $syncname) {
		if (is_writable($syncpath)) {
			echo '<td>';
			printSyncFavoritesForm($syncpath, $syncname);
			echo '</td>';
		}
	}
	echo <<<EOP
</tr></table>
</fieldset>

</td></tr></table>

EOP;
}

//�t�b�^�v�����g===================
if ($_conf['ktai']) {
	echo "<p>νĂ̓����i2ch�̔ړ]�ɑΉ����܂��j</p>\n";
	foreach ($synctitle as $syncpath => $syncname) {
		if (is_writable($syncpath)) {
			printSyncFavoritesForm($syncpath, $syncname);
		}
	}
	echo "<hr>\n";
	echo $k_to_index_ht;
}

echo <<<EOP
</body>
</html>
EOP;

//=====================================================
// �֐�
//=====================================================
function printEditFileForm($path_value, $submit_value)
{
	global $_conf;
	
	$rows = 36; //18
	$cols = 92; //90
	echo <<<EOFORM
<form action="editfile.php" method="POST" target="editfile">
	{$_conf['k_input_ht']}
	<input type="hidden" name="path" value="{$path_value}">
	<input type="hidden" name="encode" value="Shift_JIS">
	<input type="hidden" name="rows" value="{$rows}">
	<input type="hidden" name="cols" value="{$cols}">
	<input type="submit" value="{$submit_value}">
</form>\n
EOFORM;
}

/**
 * �z�X�g�̓����p�t�H�[�����v�����g����
 */
function printSyncFavoritesForm($path_value, $submit_value){
	global $_conf;
	
	echo <<<EOFORM
<form action="editpref.php" method="POST" target="_self">
	{$_conf['k_input_ht']}
	<input type="hidden" name="sync" value="{$path_value}">
	<input type="submit" value="{$submit_value}">
</form>\n
EOFORM;
}


?>