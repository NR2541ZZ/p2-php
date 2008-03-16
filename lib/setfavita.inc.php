<?php
require_once P2_LIBRARY_DIR . '/filectl.class.php';

/**
 * ���C�ɔ��Z�b�g����֐�
 *
 * $set �́A0(����), 1(�ǉ�), top, up, down, bottom
 *
 * @access  public
 * @return  boolean  ���s����
 */
function setFavIta()
{
    global $_conf;

    // {{{ �p�����[�^�̐ݒ�

    if (isset($_GET['setfavita'])) {
        $setfavita = $_GET['setfavita'];
    } elseif (isset($_POST['setfavita'])) {
        $setfavita = $_POST['setfavita'];
    }

    $host = isset($_GET['host']) ? $_GET['host'] : null;
    $bbs  = isset($_GET['bbs'])  ? $_GET['bbs']  : null;

    if (!empty($_POST['url'])) {
        if (preg_match("/http:\/\/(.+)\/([^\/]+)\/([^\/]+\.html?)?/", $_POST['url'], $matches)) {
            $host = preg_replace('{/test/read\.cgi$}', '', $matches[1]);
            $bbs = $matches[2];
        } else {
            P2Util::pushInfoHtml("<p>p2 info: �u{$_POST['url']}�v�͔�URL�Ƃ��Ė����ł��B</p>");
        }
    }

    $list = isset($_POST['list']) ? $_POST['list'] : '';

    // ���X�g�ŕ��ёւ�
    if (!empty($_POST['submit_listfavita'])) {
        if (!$list) {
            P2Util::pushInfoHtml("<p>p2 info: ���X�g�̎w�肪�ςł�</p>");
            return false;
        }

    // �V�K�ǉ� or ������ёւ�
    } elseif (!$host || !$bbs) {
        P2Util::pushInfoHtml("<p>p2 info: �̎w�肪�ςł�</p>");
        return false;
    }

    $itaj = isset($_POST['itaj']) ? $_POST['itaj'] : '';

    if (!$itaj && isset($_GET['itaj_en'])) {
        $itaj = base64_decode($_GET['itaj_en']);
    }
    !$itaj and $itaj = $bbs;

    // }}}

    //================================================
    // ����
    //================================================
    FileCtl::make_datafile($_conf['favita_path'], $_conf['favita_perm']);

    $lines = file($_conf['favita_path']);
    if ($lines === false) {
        return false;
    }

    $neolines = array();
    $before_line_num = 0;

    // �ŏ��ɏd���v�f���������Ă���
    if ($lines) {
        $i = -1;
        foreach ($lines as $l) {
            $i++;
            $l = rtrim($l);

            // {{{ ���f�[�^�iver0.6.0�ȉ��j�ڍs�[�u
            if (!preg_match("/^\t/", $l)) {
                $l = "\t".$l;
            }
            // }}}

            $lar = explode("\t", $l);

            if ($lar[1] == $host and $lar[2] == $bbs) { // �d�����
                $before_line_num = $i;
                continue;
            } elseif (!$lar[1] || !$lar[2]) { // �s���f�[�^�ihost, bbs�Ȃ��j���A�E�g
                continue;
            } else {
                $neolines[] = $l;
            }
        }
    }

    // �L�^�f�[�^�ݒ�

    // ���X�g�ۂ��ƃ|�X�g���Ďw��
    if (!empty($_POST['submit_listfavita']) && $list) {
        $rec_lines = array();
        foreach (explode(',', $list) as $aList) {
            list($host, $bbs, $itaj_en) = explode('@', $aList);
            $rec_lines[] = "\t{$host}\t{$bbs}\t" . base64_decode($itaj_en);
        }
        P2Util::pushInfoHtml('<script language="javascript">');
        P2Util::pushInfoHtml("if (parent.menu) { parent.menu.location.href='{$_conf['menu_php']}?nr=1'; }");
        P2Util::pushInfoHtml('</script>');

    // ��̃f�[�^���w�肵�đ���
    } elseif ($setfavita and $host && $bbs && $itaj) {
        $newdata = "\t{$host}\t{$bbs}\t{$itaj}";
        include_once P2_LIBRARY_DIR . '/getsetposlines.inc.php';
        $rec_lines = getSetPosLines($neolines, $newdata, $before_line_num, $setfavita);

    // ����
    } else {
        $rec_lines = $neolines;
    }

    $cont = '';
    if (!empty($rec_lines)) {
        foreach ($rec_lines as $l) {
            $cont .= $l . "\n";
        }
    }

    // ��������
    if (file_put_contents($_conf['favita_path'], $cont, LOCK_EX) === false) {
        trigger_error("file_put_contents(" . $_conf['favita_path'] . ")", E_USER_WARNING);
        die('Error: cannot write file.');
        return false;
    }

    return true;
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
