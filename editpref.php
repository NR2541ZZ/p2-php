<?php
/*
	p2 -  �ݒ�Ǘ�
*/

include_once './conf/conf.inc.php';  //��{�ݒ�
require_once './filectl.class.php';
require_once './p2util.class.php';

authorize(); //���[�U�F��

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
		include_once './syncfavita.inc.php';
	} elseif (in_array($syncfile, array($_conf['favlist_file'], $_conf['rct_file'], $rh_idx, $palace_idx))) {
		include_once './syncindex.inc.php';
	}
	if ($sync_ok) {
		$_info_msg_ht .= "<p>{$synctitle[$syncfile]}�𓯊����܂����B</p>";
	} else {
		$_info_msg_ht .= "<p>{$synctitle[$syncfile]}�͕ύX����܂���ł����B</p>";
	}
}

// �����o���p�ϐ�========================================
$ptitle = "�ݒ�Ǘ�";

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

if (empty($_conf['ktai'])) {
//<p id="pan_menu"><a href="setting.php">�ݒ�</a> &gt; {$ptitle}</p>
	echo <<<EOP
<p id="pan_menu">{$ptitle}</p>
EOP;
}


echo $_info_msg_ht;
$_info_msg_ht = "";

// �ݒ�v�����g =====================
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

	if (is_writable("conf/conf_user.inc.php") || is_writable("conf/conf_user_style.inc.php") || is_writable("conf/conf.inc.php")) {
		echo <<<EOP
<fieldset>
<legend>���̑�</legend>
<table><tr>
<td>
EOP;
		if (is_writable("conf/conf_user.inc.php")) {
			printEditFileForm("conf/conf_user.inc.php", '���[�U�ݒ�');
		}
		echo "</td><td>";
		if (is_writable("conf/conf_user_style.inc.php")) {
			printEditFileForm("conf/conf_user_style.inc.php", '�f�U�C���ݒ�');
		}
		echo "</td><td>";
		if (is_writable("conf/conf.inc.php")) {
			printEditFileForm("conf/conf.inc.php", '��{�ݒ�');
		}
		echo <<<EOP
</td>
</tr></table>
</fieldset>
EOP;
	}

	echo <<<EOP
</td></tr>
<tr><td colspan="2">\n
EOP;

	// �z�X�g�̓��� HTML�̃Z�b�g
	$htm['sync'] = <<<EOP
<fieldset>
<legend>�z�X�g�̓����i2ch�̔ړ]�ɑΉ����܂��j</legend>
<table><tr>
EOP;
	$exist_sync_flag = false;
	foreach ($synctitle as $syncpath => $syncname) {
		if (is_writable($syncpath)) {
			$exist_sync_flag = true;
			$htm['sync'] .= '<td>';
			$htm['sync'] .= getSyncFavoritesFormHt($syncpath, $syncname);
			$htm['sync'] .= '</td>';
		}
	}
	$htm['sync'] .= <<<EOP
</tr></table>
</fieldset>\n
EOP;

	if ($exist_sync_flag) {
		echo $htm['sync'];
	} else {
		echo "&nbsp;";
		// echo "<p>�z�X�g�̓����͕K�v����܂���</p>";
	}

	echo <<<EOP
</td></tr></table>\n
EOP;
}

// �g�їp�\��
if ($_conf['ktai']) {
	$htm['sync'] .= "<p>νĂ̓����i2ch�̔ړ]�ɑΉ����܂��j</p>\n";
	$exist_sync_flag = false;
	foreach ($synctitle as $syncpath => $syncname) {
		if (is_writable($syncpath)) {
			$exist_sync_flag = true;
			$htm['sync'] .= getSyncFavoritesFormHt($syncpath, $syncname);
		}
	}	
	if ($exist_sync_flag) {
		echo $htm['sync'];
	} else {
		// echo "<p>νĂ̓����͕K�v����܂���</p>";
	}
}

// {{{ �V���܂Ƃߓǂ݂̃L���b�V���\��
$max = $_conf['matome_cache_max'];
for ($i = 0; $i <= $max; $i++) {
	$dnum = ($i) ? '.'.$i : '';
	$ai = '&amp;cnum='.$i;
	$file = $_conf['matome_cache_path'].$dnum.$_conf['matome_cache_ext'];
	//echo '<!-- '.$file.' -->';
	if (file_exists($file)) {
		$date = date('Y/m/d G:i:s', filemtime($file));
		$b = filesize($file)/1024;
		$kb = round($b, 0);
		$url = 'read_new.php?cview=1'.$ai;
		if ($i == 0) {
			$links[] = '<a href="'.$url.'" target="read">'.$date.'</a> '.$kb.'KB';
		} else {
			$links[] = '<a href="'.$url.'" target="read">'.$date.'</a> '.$kb.'KB';
		}
	}
}
if (!empty($links)) {
	if ($_conf['ktai']) {
		echo '<hr>'."\n";
	}
	echo $htm['matome'] = '<p>�V���܂Ƃߓǂ݂̑O��L���b�V����\��<br>' . implode('<br>', $links) . '</p>';
}
// }}}

// �g�їp�t�b�^
if ($_conf['ktai']) {
	echo "<hr>\n";
	echo $_conf['k_to_index_ht']."\n";
}

echo '</body></html>';

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
 * �z�X�g�̓����p�t�H�[����HTML���擾����
 */
function getSyncFavoritesFormHt($path_value, $submit_value)
{
	global $_conf;
	
	$ht = <<<EOFORM
<form action="editpref.php" method="POST" target="_self">
	{$_conf['k_input_ht']}
	<input type="hidden" name="sync" value="{$path_value}">
	<input type="submit" value="{$submit_value}">
</form>\n
EOFORM;

	return $ht;
}

?>