<?php
/*
    �t�@�C�����u���E�U�ŕҏW����
*/

include_once './conf/conf.inc.php'; // ��{�ݒ�
require_once P2_LIBRARY_DIR . '/filectl.class.php';

$_login->authorize(); // ���[�U�F��

// �����G���[
if (!isset($_REQUEST['path'])) {
    die('Error: path ���w�肳��Ă��܂���');
}

// �ϐ� ==================================
$path       = isset($_REQUEST['path'])       ? $_REQUEST['path']       : null;
$modori_url = isset($_REQUEST['modori_url']) ? $_REQUEST['modori_url'] : null;
$encode     = isset($_REQUEST['encode'])     ? $_REQUEST['encode']     : null;

$rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : (!empty($_conf['ktai']) ? 5 : 36);
$cols = isset($_REQUEST['cols']) ? intval($_REQUEST['cols']) : (!empty($_conf['ktai']) ? 0 : 128);

isset($_POST['filecont']) and $filecont = $_POST['filecont'];

//=========================================================
// �O����
//=========================================================
// �������߂�t�@�C�������肷��
$writable_files = array(
                        //"conf.inc.php", "conf_user_style.inc.php",
                        //"p2_aborn_name.txt", "p2_aborn_mail.txt", "p2_aborn_msg.txt", "p2_aborn_id.txt",
                        //"p2_ng_name.txt", "p2_ng_mail.txt", "p2_ng_msg.txt", "p2_ng_id.txt",
                        "p2_aborn_res.txt",
						//"p2_highlight_name.txt", "p2_highlight_mail.txt", "p2_highlight_msg.txt", "p2_highlight_id.txt",
                        //"conf_user_ex.php", "conf_constant.inc",
                        //"conf_user_ex.inc.php", "conf_user_constant.inc.php"
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
    die("Error: ".basename($_SERVER['SCRIPT_NAME'])." �搶�̏������߂�t�@�C���́A".$files_st."�����I");
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
    if ($path == '') {
        die('Error: path ���w�肳��Ă��܂���');
    }

    if ($encode == "EUC-JP") {
        $cont = mb_convert_encoding($cont, 'SJIS-win', 'eucJP-win');
    }
    // ��������
    $fp = @fopen($path, 'wb') or die("Error: cannot write. ( $path )");
    @flock($fp, LOCK_EX);
    fputs($fp, $cont);
    @flock($fp, LOCK_UN);
    fclose($fp);
    return true;
}

/**
 * �t�@�C�����e��ǂݍ���ŕҏW����֐�
 */
function editFile($path, $encode)
{
    global $_conf, $modori_url, $_info_msg_ht, $rows, $cols;

    if ($path == '') {
        die('Error: path ���w�肳��Ă��܂���');
    }

    $filename = basename($path);
    $ptitle = "Edit: ".$filename;

    //�t�@�C�����e�ǂݍ���
    FileCtl::make_datafile($path) or die("Error: cannot make file. ( $path )");
    $cont = @file_get_contents($path);

    if ($encode == "EUC-JP") {
        $cont = mb_convert_encoding($cont, 'SJIS-win', 'eucJP-win');
    }

    $cont_area = htmlspecialchars($cont, ENT_QUOTES);

    if ($modori_url) {
        $modori_url_ht = "<p><a href=\"{$modori_url}\">Back</a></p>\n";
    }

    $rows_at = ($rows > 0) ? sprintf(' rows="%d"', $rows) : '';
    $cols_at = ($cols > 0) ? sprintf(' cols="%d"', $cols) : '';

    // �v�����g
    echo <<<EOHEADER
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>{$ptitle}</title>
</head>
<body onLoad="top.document.title=self.document.title;">
EOHEADER;

    echo $modori_url_ht;

    echo "Edit: ".$path;
    echo <<<EOFORM
<form action="{$_SERVER['SCRIPT_NAME']}" method="post" accept-charset="{$_conf['accept_charset']}">
    <input type="hidden" name="detect_hint" value="�����@����">
    <input type="hidden" name="path" value="{$path}">
    <input type="hidden" name="modori_url" value="{$modori_url}">
    <input type="hidden" name="encode" value="{$encode}">
    <input type="hidden" name="rows" value="{$rows}">
    <input type="hidden" name="cols" value="{$cols}">
    <input type="submit" name="submit" value="Save"> $_info_msg_ht<br>
    <textarea style="font-size:9pt;" id="filecont" name="filecont" wrap="off"{$rows_at}{$cols_at}>{$cont_area}</textarea>
</form>
EOFORM;

    echo '</body></html>';

    return true;
}

?>
