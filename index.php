<?php
// p2 -  �C���f�b�N�X�y�[�W

require_once './conf/conf.inc.php';

require_once P2_LIB_DIR . '/FileCtl.php';

$_login->authorize(); // ���[�U�F��

// �A�N�Z�X���ۗp��.htaccess���f�[�^�f�B���N�g���ɍ쐬����
_makeDenyHtaccess($_conf['pref_dir']);
_makeDenyHtaccess($_conf['dat_dir']);
_makeDenyHtaccess($_conf['idx_dir']);

// �ϐ��ݒ�
$me_url = P2Util::getMyUrl();
$me_dir_url = dirname($me_url);

if (UA::isK() || UA::isIPhoneGroup()) {

    // url�w�肪����΁A���̂܂܃X���b�h�ǂ݂֔�΂�
    if (!empty($_GET['url']) || !empty($_GET['nama_url'])) {
        header('Location: ' . $me_dir_url . '/' . $_conf['read_php'] . '?' . $_SERVER['QUERY_STRING']);
        exit;
    }
    if (UA::isIPhoneGroup()) {
        require_once P2_IPHONE_LIB_DIR . '/index_print_k.inc.php';
    } else {
        require_once P2_LIB_DIR . '/index_print_k.inc.php';
    }
    index_print_k();
    
} else {

    // {{{ PC�p �ϐ�

    $title_page = 'title.php';

    $read_page = _getReadPage();
    
    $sidebar = isset($_GET['sidebar']) ? $_GET['sidebar'] : null;
    
    $ptitle = "rep2";
    
    // }}}
    // {{{ PC�p HTML�v�����g

    P2Util::headerNoCache();
    ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
        "http://www.w3.org/TR/html4/frameset.dtd">
<html lang="ja">
<head>
<?php
P2View::printExtraHeadersHtml();
?>
	<title><?php eh($ptitle); ?></title>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
	<?php if (!$sidebar) { ?>
	<frameset cols="<?php eh($_conf['frame_menu_width']); ?>,*" frameborder="1" border="1">
		<frame src="<?php eh($_conf['menu_php']); ?>" name="menu" scrolling="auto">
	<?php } ?>

		<frameset id="fsright" name="fsright" rows="<?php eh($_conf['frame_subject_width']); ?>,<?php eh($_conf['frame_read_width']); ?>" frameborder="1" border="2">
			<frame id="subject" name="subject" src="<?php eh($title_page); ?>" scrolling="auto">
			<frame id="read" name="read" src="<?php eh($read_page); ?>" scrolling="auto">
		</frameset>
	
	<?php if (!$sidebar) { ?>
	</frameset>
	<?php } ?>
</html><?php

    // }}}
}

exit;


//============================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//============================================================================
/**
 * �f�B���N�g���Ɂi�A�N�Z�X���ۂ̂��߂́j .htaccess ���Ȃ���΁A�����Ő�������
 *
 * @return  void
 */
function _makeDenyHtaccess($dir)
{
    $hta = $dir . '/.htaccess';
    if (!file_exists($hta)) {
        $data = 'Order allow,deny' . "\n"
              . 'Deny from all' . "\n";
        file_put_contents($hta, $data, LOCK_EX);
    }
}

/**
 * read�i�E���jframe��src�p�y�[�WURL���擾����
 *
 * @return  string
 */
function _getReadPage()
{
    global $_conf;
    
    if (!empty($_GET['url']) || !empty($_GET['nama_url'])) {
        $read_page = $_conf['read_php'] . '?' . $_SERVER['QUERY_STRING'];
    } else {
        $read_page = $_conf['first_page'] ? $_conf['first_page'] : 'first_cont.php';
    }
    return $read_page;
}
