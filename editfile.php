<?php
/*
	�t�@�C�����u���E�U�ŕҏW����

	�ŐV�X�V��: 2004/10/24
*/

require_once("./conf.php"); // ��{�ݒ�Ǎ�
require_once("./filectl_class.inc");

authorize(); // ���[�U�F��

// �ϐ� ==================================
$path = $_POST['path'];
$modori_url = $_POST['modori_url'];
$encode = $_POST['encode'];

if (isset($_POST['rows'])) {
	$rows = $_POST['rows'];
} else {
	$rows = 36; // �f�t�H���g�l
}
if (isset($_POST['cols'])) {
	$cols = $_POST['cols'];
} else {
	$cols = 128; // �f�t�H���g�l
}


if (isset($_POST['filecont'])) {
	$filecont = $_POST['filecont'];
}

//magic_quates ����
if (get_magic_quotes_gpc()) {
	$path = stripslashes($path);
	$modori_url = stripslashes($modori_url);
	$encode = stripslashes($encode);
	if (isset($filecont)) {
		$filecont = stripslashes($filecont);
	}
}

$_info_msg_ht = "";


//=========================================================
// �O����
//=========================================================
// �������߂�t�@�C�������肷��
$writable_files = array(
						"conf.php", "conf_user.php", "conf_style.inc",
						"p2_aborn_name.txt", "p2_aborn_mail.txt", "p2_aborn_msg.txt", "p2_aborn_id.txt",
						"p2_ng_name.txt", "p2_ng_mail.txt", "p2_ng_msg.txt", "p2_ng_id.txt",
						"conf_user_ex.php", "conf_constant.inc", "p2_aborn_res.txt",
						"conf.inc.php", "conf_user.inc.php", "conf_user_style.inc.php", "conf_user_ex.inc.php", "conf_user_constant.inc.php"
					);

if ($writable_files and (!in_array(basename($path), $writable_files))) {
	$i = 0;
	foreach ($writable_files as $afile) {
		if ($i != 0) {
			$files_st .= "��";
		}
		$files_st .= "�u".$afile."�v";
		$i++;
	}
	die("Error: ".basename($_SERVER['PHP_SELF'])." �搶�̏������߂�t�@�C���́A".$files_st."�����I");
}

//=========================================================
// ���C�� 
//=========================================================
if (isset($filecont)) {
	if (setFile($path, $filecont, $encode)) {
		$_info_msg_ht .= "saved, OK.";
	}
}

editFile($path, $encode);


//=========================================================
// �֐�
//=========================================================

/**
 * �t�@�C���ɓ��e���Z�b�g����֐�
 */
function setFile($path, $cont, $encode)
{
	if ($encode=="EUC-JP") {
		include_once("./strctl_class.inc");
		$cont = StrCtl::p2EUCtoSJIS($cont);
	}
	//��������
	$fp = @fopen($path,"wb") or die("Error: cannot write. ( $path )");
	fputs($fp, $cont); 
	fclose($fp);
	return true;
}

/**
 * �t�@�C�����e��ǂݍ���ŕҏW����֐�
 */
function editFile($path, $encode)
{
	global $modori_url, $_info_msg_ht, $rows, $cols;
	
	$filename = basename($path);
	$ptitle = "Edit: ".$filename;
	
	//�t�@�C�����e�ǂݍ���
	FileCtl::make_datafile($path) or die("Error: cannot make file. ( $path )");
	$cont = FileCtl::get_file_contents($path);
	
	if ($encode=="EUC-JP") {
		include_once("./strctl_class.inc");
		$cont = StrCtl::p2EUCtoSJIS($cont);
	}
	
	$cont_area = htmlspecialchars($cont);
	
	if ($modori_url) {
		$modori_url_ht = "<p><a href=\"{$modori_url}\">Back</a></p>\n";
	}
	
	//�v�����g
	echo <<<EOHEADER
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
	<title>{$ptitle}</title>
</head>
<body onLoad="top.document.title=self.document.title;">
EOHEADER;

echo $modori_url_ht;

echo "Edit: ".$path;
echo <<<EOFORM
<form action="{$_SERVER['PHP_SELF']}" method="post" accept-charset="Shift_JIS">
	<input type="hidden" name="path" value="{$path}">
	<input type="hidden" name="modori_url" value="{$modori_url}">
	<input type="hidden" name="encode" value="{$encode}">
	<input type="hidden" name="rows" value="{$rows}">
	<input type="hidden" name="cols" value="{$cols}">
	<input type="submit" name="submit" value="Save"> $_info_msg_ht<br>
	<textarea style="font-size:9pt;" id="filecont" name="filecont" rows="{$rows}" cols="{$cols}" wrap="off">{$cont_area}</textarea>	
</form>
EOFORM;

echo <<<EOFOOTER
</body>
</html>
EOFOOTER;

}

?>
