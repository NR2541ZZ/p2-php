<?php
/*
	p2 - txt �� �\��
*/

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

// �����G���[
if (!isset($_GET['file'])) {
    p2die('file ���w�肳��Ă��܂���');
}

//=========================================================
// �ϐ�
//=========================================================
$file = isset($_GET['file']) ? $_GET['file'] : NULL;
$encode = "Shift_JIS";

//=========================================================
// �O����
//=========================================================
// �ǂݍ��߂�t�@�C�������肷��
$readable_files = array("doc/README.txt", "doc/ChangeLog.txt");

if ($readable_files && $file and (!in_array($file, $readable_files))) {
	$i = 0;
	foreach ($readable_files as $afile) {
		if ($i != 0) {
			$files_st .= "��";
		}
		$files_st .= "�u" . $afile . "�v";
		$i++;
	}
    
    p2die(
        '�t�@�C���̎w�肪����������܂���',
        hs(sprintf(
            '%s �搶�̓ǂ߂�t�@�C���́A%s�����I',
            basename($_SERVER['SCRIPT_NAME']), $files_st
        ))
    );
}

//=========================================================
// HTML�v�����g
//=========================================================
// �ǂݍ��ރt�@�C���͊g���q.txt����
if (preg_match("/\.txt$/i", $file)) {
	viewTxtFile($file, $encode);
} else {
    p2die('cannot view - "' . hs($file) . '"');
}

//===================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//===================================================================
/**
 * �t�@�C�����e��ǂݍ���ŕ\������֐�
 *
 * @return  void
 */
function viewTxtFile($file, $encode)
{
	global $_info_msg_ht;
	
	if ($file == '') {
		die('Error: file ���w�肳��Ă��܂���');
	}
	
	$filename = basename($file);
	$ptitle = $filename;
	
	$cont = file_get_contents($file);
	
	if ($encode == "EUC-JP") {
		$cont = mb_convert_encoding($cont, 'SJIS-win', 'eucJP-win');
	}
	
	$cont_area = htmlspecialchars($cont, ENT_QUOTES);

	// HTML�v�����g
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
<?php
P2View::printExtraHeadersHtml();
?>
	<title><?php eh($ptitle) ?></title>
</head>
<body onLoad="top.document.title=self.document.title;">
<?php
	P2Util::printInfoHtml();
?>
<pre>
<?php
	echo $cont_area;
?>
</pre>
</body></html>
<?php
}

