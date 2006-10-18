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
    global $_conf, $_info_msg_ht;

    // {{{ �p�����[�^�̐ݒ�
    
    if (isset($_GET['setfavita'])) {
        $setfavita = $_GET['setfavita'];
    } elseif (isset($_POST['setfavita'])) {
        $setfavita = $_POST['setfavita'];
    }

    $host = isset($_GET['host']) ? $_GET['host'] : NULL;
    $bbs  = isset($_GET['bbs'])  ? $_GET['bbs']  : NULL;

    if (!empty($_POST['url'])) {
        if (preg_match("/http:\/\/(.+)\/([^\/]+)\/([^\/]+\.html?)?/", $_POST['url'], $matches)) {
            $host = preg_replace('{/test/read\.cgi$}', '', $matches[1]);
            $bbs = $matches[2];
        } else {
            $_info_msg_ht .= "<p>p2 info: �u{$_POST['url']}�v�͔�URL�Ƃ��Ė����ł��B</p>";
        }
    }
    
    $list = isset($_POST['list']) ? $_POST['list'] : '';
    
    if ((!$host || !$bbs) and (empty($_POST['submit_setfavita']) || $list)) {
        $_info_msg_ht .= "<p>p2 info: �̎w�肪�ςł�</p>";
        return false;
    }

    $itaj = $_POST['itaj'] ? isset($_POST['itaj']) : '';
    
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
                $l = "\t" . $l;
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
    if (!empty($_POST['submit_setfavita']) && $list) {
        $rec_lines = array();
        foreach (explode(',', $list) as $aList) {
            list($host, $bbs, $itaj_en) = explode('@', $aList);
            $rec_lines[] = "\t{$host}\t{$bbs}\t" . base64_decode($itaj_en);
        }
        $_info_msg_ht .= "<script language=\"javascript\">
            if (parent.menu) { parent.menu.location.href='{$_conf['menu_php']}?nr=1'; }</script>";
    
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

?>