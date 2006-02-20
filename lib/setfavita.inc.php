<?php
// p2 -  ���C�ɔ̏���

require_once (P2_LIBRARY_DIR . '/filectl.class.php');

/**
 * ���C�ɔ��Z�b�g����
 *
 * $set �́A0(����), 1(�ǉ�), top, up, down, bottom
 */
function setFavIta()
{
    global $_conf, $_info_msg_ht;

    if (isset($_GET['setfavita'])) {
        $setfavita = $_GET['setfavita'];
    } elseif (isset($_POST['setfavita'])) {
        $setfavita = $_POST['setfavita'];
    }

    $host = isset($_GET['host']) ? $_GET['host'] : NULL;
    $bbs = isset($_GET['bbs']) ? $_GET['bbs'] : NULL;

    if ($_POST['url']) {
        if (preg_match("/http:\/\/(.+)\/([^\/]+)\/([^\/]+\.html?)?/", $_POST['url'], $matches)) {
            $host = $matches[1];
            $host = preg_replace('{/test/read\.cgi$}', '', $host);
            $bbs = $matches[2];
        } else {
            $_info_msg_ht .= "<p>p2 info: �u{$_POST['url']}�v�͔�URL�Ƃ��Ė����ł��B</p>";
        }
    }
    
    $list = $_POST['list'];
    
    if (!$host && !$bbs and (!($setfavita == 'submit' && $list))) {
        $_info_msg_ht .= "<p>p2 info: �̎w�肪�ςł�</p>";
        return false;
    }

    if (isset($_POST['itaj'])) {
        $itaj = $_POST['itaj'];
    }
    if (!isset($itaj) && isset($_GET['itaj_en'])) {
        $itaj = base64_decode($_GET['itaj_en']);
    } 
    if (empty($itaj)) { $itaj = $bbs; }

    //================================================
    // �ǂݍ���
    //================================================
    //favita_path�t�@�C�����Ȃ���ΐ���
    FileCtl::make_datafile($_conf['favita_path'], $_conf['favita_perm']);

    //favita_path�ǂݍ���;
    $lines = @file($_conf['favita_path']);

    //================================================
    // ����
    //================================================
    $neolines = array();
    $before_line_num = 0;
    
    // �ŏ��ɏd���v�f������
    if (!empty($lines)) {
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
    if ($setfavita == "submit" && $list) {
        $rec_lines = array();
        foreach (explode(',', $list) as $aList) {
            list($host, $bbs, $itaj_en) = explode('@', $aList);
            $rec_lines[] = "\t{$host}\t{$bbs}\t" . base64_decode($itaj_en);
        }
        
    } elseif ($setfavita and $host && $bbs && $itaj) {
        $newdata = "\t{$host}\t{$bbs}\t{$itaj}";
        include_once (P2_LIBRARY_DIR . '/getsetposlines.inc.php');
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
    if (FileCtl::file_write_contents($_conf['favita_path'], $cont) === false) {
        die('Error: cannot write file.');
    }
    
    return true;
}
?>
