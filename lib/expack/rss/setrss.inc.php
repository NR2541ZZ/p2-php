<?php
/**
 * rep2expack - RSS���X�g�̏���
 */

require_once P2_LIBRARY_DIR . '/filectl.class.php';

// {{{ �ϐ�

// ���N�G�X�g�ǂݍ���
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['submit_setrss'])) {
        FileCtl::make_datafile($_conf['expack.rss.setting_path'], $_conf['expack.rss.setting_perm']);
        $fp = @fopen($_conf['expack.rss.setting_path'], 'wb');
        if (!$fp) {
            die("Error: {$_conf['expack.rss.setting_path']} ���X�V�ł��܂���ł���");
        }
        if (isset($_POST['list'])) {
            fputs($fp, $_POST['list']);
        }
        fclose($fp);
        P2Util::pushInfoHtml("<script type=\"text/javascript\">if (parent.menu) { parent.menu.location.href='{$_conf['menu_php']}?nr=1'; }</script>");
        return;
    }
    $setrss  = trim($_POST['setrss']);
    $xml     = trim($_POST['xml']);
    $site    = trim($_POST['site']);
    $site_en = trim($_POST['site_en']);
    $atom    = empty($_POST['atom']) ? 0 : 1;
} else {
    $setrss  = trim($_GET['setrss']);
    $xml     = trim($_GET['xml']);
    $site    = trim($_GET['site']);
    $site_en = trim($_GET['site_en']);
    $atom    = empty($_GET['atom']) ? 0 : 1;
}
// RSS�̃^�C�g���ݒ�
if ($site === '') {
    if ($site_en !== '') {
        $site = base64_decode($site_en);
    } else {
        $site = basename($xml);
    }
}
// ���O�ɋL�^����ϐ����Œ���̃T�j�^�C�Y
$xml = preg_replace_callback('/\s/', 'rawurlencode', $xml);
$site = preg_replace('/\s/', ' ', $site);
$site = htmlspecialchars($site, ENT_QUOTES);

// }}}
// {{{ �ǂݍ���

// rss_path�t�@�C�����Ȃ���ΐ���
FileCtl::make_datafile($_conf['expack.rss.setting_path'], $_conf['expack.rss.setting_perm']);

// rss_path�ǂݍ���;
$lines = @file($_conf['expack.rss.setting_path']);

// }}}
// {{{ ����

// �ŏ��ɏd���v�f������
if ($lines) {
    $i = -1;
    unset($neolines);
    foreach ($lines as $l) {
        $i++;

        $l = rtrim($l);
        $lar = explode("\t", $l);

        if ($lar[1] == $xml) { // �d�����
            $before_line_num = $i;
            continue;
        } elseif (strlen($lar[1]) == 0) { // URL�Ȃ����A�E�g
            continue;
        } else {
            $neolines[] = $l;
        }
    }
}

// �V�K�f�[�^�ݒ�
if ($setrss) {
    if ($xml && $site) {
        if ($atom == 1 || $setrss == 'atom') {
            $newdata = implode("\t", array($site, $xml, '1'));
        } else {
            $newdata = implode("\t", array($site, $xml, '0'));
        }
    }
    switch ($setrss) {
        case '0':
            $after_line_num = -1;
        case '1':
        case 'top':
            $after_line_num = 0;
            break;
        case 'up':
            $after_line_num = $before_line_num -1 ;
            if ($after_line_num < 0) {
                $after_line_num = 0;
            }
            break;
        case 'down':
            $after_line_num = $before_line_num + 1;
            if ($after_line_num >= count($neolines)) {
                $after_line_num = 'bottom';
            }
            break;
        case 'bottom';
            $after_line_num = 'bottom';
            break;
        default:
            $after_line_num = $before_line_num;
            if ($after_line_num >= count($neolines)) {
                $after_line_num = 'bottom';
            }
    }
}

// }}}
// {{{ ��������

$fp = @fopen($_conf['expack.rss.setting_path'], 'wb');
if (!$fp) {
    die("Error: {$_conf['expack.rss.setting_path']} ���X�V�ł��܂���ł���");
}
if ($neolines) {
    $i = 0;
    foreach ($neolines as $l) {
        if ($i === $after_line_num) {
            fputs($fp, $newdata."\n");
        }
        fputs($fp, $l."\n");
        $i++;
    }
    if ($after_line_num === 'bottom') {
        fputs($fp, $newdata."\n");
    }
    //�u$after_line_num == 'bottom'�v���ƌ듮�삷��B
} else {
    fputs($fp, $newdata);
}
fclose($fp);

// }}}

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
