<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=4 fdm=marker: */
/* mi: charset=Shift_JIS */

/* ImageCache2 - �摜�̃����N��ύX���� */

// {{{ p2��{�ݒ�ǂݍ���&�F��

require_once 'conf/conf.inc.php';

$_login->authorize();

if (!$_conf['expack.ic2.enabled']) {
    exit('<html><body><p>ImageCache2�͖����ł��B<br>conf/conf_admin_ex.inc.php �̐ݒ��ς��Ă��������B</p></body></html>');
}

// }}}
// {{{ HTTP�w�b�_��XML�錾

P2Util::header_nocache();
header('Content-Type: text/html; charset=Shift_JIS');

// }}}
// {{{ ������

// �p�����[�^������
if (!isset($_GET['id']) || !isset($_GET['rank'])) {
    echo '-1';
    exit;
}
$id = (int) $_GET['id'];
$rank = (int) $_GET['rank'];
if ($id == 0 || $rank > 5 || $rank < -1) {
    echo '0';
    exit;
}

// ���C�u�����ǂݍ���
require_once 'PEAR.php';
require_once 'DB.php';
require_once 'DB/DataObject.php';
require_once P2EX_LIBRARY_DIR . '/ic2/loadconfig.inc.php';
require_once P2EX_LIBRARY_DIR . '/ic2/database.class.php';
require_once P2EX_LIBRARY_DIR . '/ic2/db_images.class.php';

// }}}
// {{{ execute

$finder = &new IC2DB_Images;
$finder->whereAdd(sprintf('id = %d', $id));
if ($finder->find(1)) {
    $setter = &new IC2DB_Images;
    $setter->rank = $rank;
    $setter->whereAddQuoted('size', '=', $finder->size);
    $setter->whereAddQuoted('md5',  '=', $finder->md5);
    $setter->whereAddQuoted('mime', '=', $finder->mime);
    if ($setter->update()) {
        echo '1';
    } else {
        echo '0';
    }
}
exit;

// }}}

?>
