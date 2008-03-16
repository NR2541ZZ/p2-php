<?php
/*
    expack - �X���b�h���c���[�\������
    �c���[�\���ȊO�̃��[�`����read.php����q��
*/

require_once 'conf/conf.inc.php';
require_once P2_LIBRARY_DIR . '/thread.class.php';    //�X���b�h�N���X�Ǎ�
require_once P2_LIBRARY_DIR . '/threadread.class.php';    //�X���b�h���[�h�N���X�Ǎ�
require_once P2_LIBRARY_DIR . '/filectl.class.php';
require_once P2_LIBRARY_DIR . '/ngabornctl.class.php';
require_once P2_LIBRARY_DIR . '/showthread.class.php';    //HTML�\���N���X
require_once P2_LIBRARY_DIR . '/showthreadpc.class.php';  //HTML�\���N���X
//require_once P2_LIBRARY_DIR . '/showthreadtree.class.php'; // �c���[�\���N���X

$_login->authorize(); // ���[�U�F��

//================================================================
// �ϐ�
//================================================================

$newtime = date('gis'); // ���������N���N���b�N���Ă��ēǍ����Ȃ��d�l�ɑ΍R����_�~�[�N�G���[
//$_today = date('y/m/d');

if (empty($_GET['host']) || empty($_GET['bbs']) || empty($_GET['key']) || empty($_GET['ls'])) {
    P2Util::printSimpleHtml('p2 - read_async.php: ���X�̎w�肪�ςł��B');
    die('');
}

$host = $_GET['host'];
$bbs  = $_GET['bbs'];
$key  = $_GET['key'];
$mode = isset($_GET['q']) ? (int)$_GET['q'] : 0;

$_conf['ktai'] = false;

//==================================================================
// ���C��
//==================================================================
$aThread = &new ThreadRead;


//==========================================================
// idx�̓ǂݍ���
//==========================================================

// host�𕪉�����idx�t�@�C���̃p�X�����߂�
if (!isset($aThread->keyidx)) {
    $aThread->setThreadPathInfo($host, $bbs, $key);
}

// �f�B���N�g����������΍��
//FileCtl::mkdir_for($aThread->keyidx);

$aThread->itaj = P2Util::getItaName($host, $bbs);
if (!$aThread->itaj) {
    $aThread->itaj = $aThread->bbs;
}

// idx�t�@�C��������Γǂݍ���
if (is_readable($aThread->keyidx)) {
    $lines = @file($aThread->keyidx);
    $l = rtrim($lines[0]);
    $data = explode('<>', $l);
} else {
    $data = array_fill(0, 10, '');
}
$aThread->getThreadInfoFromIdx();


//===========================================================
// DAT�̃_�E�����[�h
//===========================================================
if (empty($_GET['offline'])) {
    $aThread->downloadDat();
}

// DAT��ǂݍ���
$aThread->readDat();

// �I�t���C���w��ł����O���Ȃ���΁A���߂ċ����ǂݍ���
if (empty($aThread->datlines) && !empty($_GET['offline'])) {
    $aThread->downloadDat();
    $aThread->readDat();
}


$aThread->setTitleFromLocal(); // �^�C�g�����擾���Đݒ�


//===========================================================
// �\�����X�Ԃ͈̔͂�ݒ�
//===========================================================
$aThread->ls = $_GET['ls'];
$rn = (int)$aThread->ls; // string "256n" => integer 256
$rp = $rn - 1;
$aThread->lsToPoint();


//===============================================================
// �v�����g
//===============================================================
$ptitle_ht = htmlspecialchars($aThread->itaj, ENT_QUOTES).' / '.$aThread->ttitle_hd;

// {{{ HTTP�w�b�_��XML�錾

P2Util::header_nocache();
header('Content-Type: text/html; charset=Shift_JIS');

// }}}
// {{{ �{�̐���

$node = '�Ȃ��ہB';

if ($aThread->rescount) {

    //$aShowThread = &new ShowThreadTree($aThread);
    $aShowThread = &new ShowThreadPc($aThread);

    if (isset($aShowThread->thread->datlines[$rp])) {
        $ares = $aShowThread->thread->datlines[$rp];
        $part = $aShowThread->thread->explodeDatLine($ares);
        switch ($mode) {
            // ���X�|�b�v�A�b�v
            case 1:
                $node = $aShowThread->qRes($ares, $rn);
                break;
            // �R�s�y
            case 2:
                $node = $rn;
                $node .= ' �F' . strip_tags($part[0]);
                $node .= ' �F' . strip_tags($part[1]);
                $node .= ' �F' . strip_tags($part[2]) . "\n";
                $node .= trim(preg_replace('/ *<br.*?> */i', "\n", strip_tags($part[3], '<br>')));
                 break;
            default:
                $node = $aShowThread->transMsg($part[3], $rn);
        }
    }

}

// }}}
// {{{ �{�̏o��

if (P2Util::isBrowserSafariGroup()) {
    $node = P2Util::encodeResponseTextForSafari($node);
}
echo $node;

// }}}

// idx�E����ݒ�t���O���Ȃ���ΏI��
if (empty($_GET['rec'])) {
    exit;
}


// �e���r�ԑg����2ch�Ȃǂ̓��O�Eidx�E������ۑ����Ȃ�
if (P2Util::isHostNoCacheData($aThread->host)) {
    //@unlink($aThread->keydat); // ThreadRead::readDat()�ō폜����
    exit;
}


//===========================================================
// idx�̒l��ݒ�A�L�^
//===========================================================
if ($aThread->rescount) {
    $aThread->readnum = min($aThread->rescount, max(0, $data[5], $aThread->resrange['to']));

    $newline = $aThread->readnum + 1;   // $newline�͔p�~�\�肾���A���݊��p�ɔO�̂���

    $sar = array($aThread->ttitle, $aThread->key, $data[2], $aThread->rescount, $aThread->modified,
                 $aThread->readnum, $data[6], $data[7], $data[8], $newline,
                 $data[10], $data[11], $aThread->datochiok);
    P2Util::recKeyIdx($aThread->keyidx, $sar); // key.idx�ɋL�^
}

//===========================================================
// �������L�^
//===========================================================
$newdata_ar = array($aThread->ttitle, $aThread->key, $data[2], '', '', $aThread->readnum,
                    $data[6], $data[7], $data[8], $newline, $aThread->host, $aThread->bbs);
$newdata = implode('<>', $newdata_ar);
P2Util::recRecent($newdata);

// NG���ځ[����L�^
NgAbornCtl::saveNgAborns();

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
