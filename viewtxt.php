<?php
/*
	p2 - txt �� �\��

	�ŐV�X�V��: 2004/10/24
*/

include("./conf.php");   //��{�ݒ�t�@�C���Ǎ�
require_once("./filectl_class.inc");

authorize(); //���[�U�F��

//=========================================================
// �ϐ�
//=========================================================
$_info_msg_ht = "";

$file = $_GET['file'];
$encode = "Shift_JIS";

//=========================================================
// �O����
//=========================================================
// �ǂݍ��߂�t�@�C�������肷��
$readable_files = array("doc/README.txt", "doc/ChangeLog.txt");

if ($readable_files and (!in_array($file, $readable_files))) {
	$i = 0;
	foreach ($readable_files as $afile) {
		if ($i != 0) {
			$files_st .= "��";
		}
		$files_st .= "�u".$afile."�v";
		$i++;
	}
	die("Error: ".basename($_SERVER['PHP_SELF'])." �搶�̓ǂ߂�t�@�C���́A".$files_st."�����I");
}

//=========================================================
// HTML�v�����g
//=========================================================
// �ǂݍ��ރt�@�C���͊g���q.txt����
if (preg_match("/\.txt$/i", $file)) {
	viewTxtFile($file, $encode);
} else {
	die("error: cannot view \"$file\"");
}

/**
 * �t�@�C�����e��ǂݍ���ŕ\������֐�
 */
function viewTxtFile($file, $encode)
{
	global $_info_msg_ht;

	$filename = basename($file);
	$ptitle = $filename;
	
	//�t�@�C�����e�ǂݍ���
	$cont = FileCtl::get_file_contents($file);
	
	if ($encode == "EUC-JP") {
		include_once("./strctl_class.inc");
		$cont = StrCtl::p2EUCtoSJIS($cont);
	}
	
	$cont_area = htmlspecialchars($cont);

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

echo $_info_msg_ht;
echo "<pre>";
echo $cont_area;
echo "</pre>";
echo <<<EOFOOTER
</body>
</html>
EOFOOTER;

}

?>