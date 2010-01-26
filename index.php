<?php
// p2 -  �C���f�b�N�X�y�[�W

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

// �A�N�Z�X���ۗp��.htaccess���f�[�^�f�B���N�g���Ɏ����쐬����
_makeDenyHtaccess($_conf['pref_dir']);
_makeDenyHtaccess($_conf['dat_dir']);
_makeDenyHtaccess($_conf['idx_dir']);


if (UA::isK() || UA::isIPhoneGroup()) {

    // GET�N�G���[��url�̎w�肪����΁A���̂܂܃X���b�h�ǂ݂֔�΂�
    _locationReadPhpIfGetUrl(); // void|exit
    
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
P2View::printExtraHeadersHtml($frameset = true);
?>
	<title><?php eh($ptitle); ?></title>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
	<?php if (!$sidebar) { ?>
	<frameset cols="
		<?php if (!$_conf['pane_mode']) { ?><?php eh($_conf['frame_menu_width']); ?>,*
		<?php } else { ?><?php eh($_conf['frame_menu_width']); ?>,<?php eh($_conf['frame_subject_width']); ?>,<?php eh($_conf['frame_read_width']); ?><?php } ?>
	" frameborder="1" border="1">
		<frame src="<?php eh($_conf['menu_php']); ?>" name="menu" scrolling="auto">
	<?php } ?>

		<?php if (!$_conf['pane_mode']) { ?>
		<frameset id="fsright" name="fsright" rows="<?php eh($_conf['frame_subject_width']); ?>,<?php eh($_conf['frame_read_width']); ?>" frameborder="1" border="2">
		<?php } ?>
			<frame id="subject" name="subject" src="<?php eh($title_page); ?>" scrolling="auto">
			<frame id="read" name="read" src="<?php eh($read_page); ?>" scrolling="auto">
		<?php if (!$_conf['pane_mode']) { ?>
		</frameset>
		<?php } ?>
	
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
 * GET�N�G���[��url�̎w�肪����΁A���̂܂܃X���b�h�ǂ݂֔�΂�
 *
 * @return  void|exit
 */
function _locationReadPhpIfGetUrl()
{
    global $_conf;

    if (!empty($_GET['url']) || !empty($_GET['nama_url'])) {
        header(sprintf('Location: %s/%s?%s',
            dirname(UriUtil::getMyUri()), $_conf['read_php'], $_SERVER['QUERY_STRING'])
        );
        exit;
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
