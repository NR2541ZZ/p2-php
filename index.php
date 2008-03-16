<?php
// rep2 -  �C���f�b�N�X�y�[�W

include_once './conf/conf.inc.php';
require_once P2_LIBRARY_DIR . '/filectl.class.php';

$_login->authorize(); // ���[�U�F��

//=============================================================
// �O����
//=============================================================
// �A�N�Z�X���ۗp��.htaccess���f�[�^�f�B���N�g���ɍ쐬����
makeDenyHtaccess($_conf['pref_dir']);
makeDenyHtaccess($_conf['dat_dir']);
makeDenyHtaccess($_conf['idx_dir']);
makeImageCacheDenyHtaccess($_conf['expack.ic2.General.cachedir']);

//=============================================================

$me_url = P2Util::getMyUrl();
$me_dir_url = dirname($me_url);

if ($_conf['ktai']) {

    //=========================================================
    // �g�їp �C���f�b�N�X
    //=========================================================
    // url�w�肪����΁A���̂܂܃X���b�h�ǂ݂֔�΂�
    if (!empty($_GET['url']) || !empty($_GET['nama_url'])) {
        header('Location: ' . $me_dir_url . '/read.php?' . $_SERVER['QUERY_STRING']);
        exit;
    }
    include_once P2_LIBRARY_DIR . '/index_print_k.inc.php';
    index_print_k();

} else {
    //=========================================
    // PC�p �ϐ�
    //=========================================
    $title_page = 'title.php';

    if (!empty($_GET['url']) || !empty($_GET['nama_url'])) {
        $read_page = "read.php?" . $_SERVER['QUERY_STRING'];
    } else {
        if (!empty($_conf['first_page'])) {
            $read_page = $_conf['first_page'];
        } else {
            $read_page = 'first_cont.php';
        }
    }

    $sidebar = $_GET['sidebar'];

    $ptitle = 'rep2';
    //======================================================
    // PC�p HTML�v�����g
    //======================================================
    P2Util::header_nocache();
     if ($_conf['doctype']) { 
        echo str_replace(
            array('Transitional', 'loose.dtd'),
            array('Frameset', 'frameset.dtd'),
            $_conf['doctype']);
    }
    echo <<<EOHEADER
<html lang="ja">
<head>
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>{$ptitle}</title>
    <link href="favicon.ico" type="image/x-icon" rel="shortcut icon">
</head>
EOHEADER;

    if (!$sidebar) {
?>
    <frameset cols="<?php echo htmlspecialchars($_conf['frame_menu_width']); ?>,*" frameborder="1" border="2">
        <frame id="menu" name="menu" src="<?php echo htmlspecialchars($_conf['menu_php']); ?>" scrolling="auto">
<?php
    }
?>
    <frameset id="fsright" name="fsright" rows="<?php echo htmlspecialchars($_conf['frame_subject_width']); ?>,<?php echo htmlspecialchars($_conf['frame_read_width']); ?>" frameborder="1" border="2">
        <frame id="subject" name="subject" src="<?php echo htmlspecialchars($title_page); ?>" scrolling="auto">
        <frame id="read" name="read" src="<?php echo htmlspecialchars($read_page); ?>" scrolling="auto">
    </frameset>
<?php
    if (!$sidebar) {
        echo '</frameset>' . "\n";
    }

    echo '</html>';

}

//============================================================================
// �֐�
//============================================================================
/**
 * �f�B���N�g���Ɂi�A�N�Z�X���ۂ̂��߂́j .htaccess ���Ȃ���΁A�����Ő�������
 *
 * @return  void
 */
function makeDenyHtaccess($dir)
{
    $hta = $dir . '/.htaccess';
    if (!file_exists($hta)) {
        $data = 'Order allow,deny' . "\n"
              . 'Deny from all' . "\n";
        FileCtl::file_write_contents($hta, $data);
    }
}
/**
 * �f�B���N�g���Ɂi�摜�ȊO�A�N�Z�X���ۂ̂��߂́j .htaccess ���Ȃ���΁A�����Ő�������
 */
function makeImageCacheDenyHtaccess($dir)
{
    $hta = $dir . '/.htaccess';
    $allow_pattern = '\.(gif|jpe?g|png)$';
    /*if (is_dir($dir) && !file_exists($hta)) {
        $data = <<<HTACCESS
Order allow,deny
<FilesMatch "{$allow_pattern}">
    Allow from all
</FilesMatch>
Deny from all\n
HTACCESS;
        FileCtl::file_write_contents($hta, $data);
    }*/
    // �������݌����̖����i�����[�U�������ō쐬�����j.htaccess �͏������Ȃ�
    if (is_dir($dir) && file_exists($hta) && is_writable($hta)) {
        unlink($hta);
    }
}

/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
