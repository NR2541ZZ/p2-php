<?php
/*
    p2 - ���C�ɃX���֌W�̏����X�N���v�g

    ���C�ɃX���̒ǉ��폜��A�����ύX�ŌĂ΂��

    2005/03/10 �ȑO
    �X����idx�ł̂��C�ɓ���t���O�́A���݂͎g�p�i�@�\�j���Ă��Ȃ��B
    ���C�ɃX�����́Afavlist.idx�ł܂Ƃ߂Ď󂯎��B
    ��
    2005/03/10
    �X���b�h�\�����̕��׌y����ړI�Ƃ��āA�X���b�h.idx�ł����C�ɃX�����������ƂƂ���B
    subject�ł��C�ɃX���ꗗ�\�� �� favlist.idx ���Q��
    �X���b�h�\�����̂��C�ɃX���\�� �� �X���b�h.idx ���Q��
*/

require_once P2_LIBRARY_DIR . '/filectl.class.php';

/**
 * ���C�ɃX�����Z�b�g����֐�
 *
 * $set �́A0(����), 1(�ǉ�), top, up, down, bottom
 *
 * @access  public
 * @return  boolean  ���s����
 */
function setFav($host, $bbs, $key, $setfav, $setnum = null)
{
    global $_conf, $__conf;

    //==================================================================
    // key.idx
    //==================================================================
    // idxfile�̃p�X�����߂�
    $idx_host_dir = P2Util::idxDirOfHost($host);
    $idxfile = $idx_host_dir . '/' . $bbs . '/'.$key . '.idx';

    // �f�B���N�g����������΍��
    // FileCtl::mkdir_for($idxfile);

    // ����idx�f�[�^������Ȃ�ǂݍ���
    if (file_exists($idxfile) and $lines = file($idxfile)) {
        $l = rtrim($lines[0]);
        $data = explode('<>', $l);
    }

    /*
    // readnum
    if (!isset($data[4])) {
        $data[4] = 0;
    }
    if (!isset($data[9])) {
        $data[9] = $data[4] + 1; // $newline�͔p�~�\�肾���A���݊��p�ɔO�̂���
    }
    */

    // �Z�b�g�ԍ�������
    if (!is_null($setnum) && $_conf['expack.favset.enabled'] && $_conf['favlist_set_num'] > 0) {
        $setnum = (int)$setnum;
        if ($setnum < 0 || $_conf['favlist_set_num'] < $setnum) {
            return false;
        }
    } else {
        $setnum = 0;
    }

    // {{{ �X���b�h.idx �L�^

    if ($setfav == '0' || $setfav == '1') {
        $favflag = ((int)$data[6] & PHP_INT_MAX);
        if ($setfav == '0') {
            $favflag &= ~(1 << $setnum);
        } else {
            $favflag |= (1 << $setnum);
        }
        $data[6] = (string)$favflag;
        // ���C�ɃX������O�������ʁAidx�̈Ӗ����Ȃ��Ȃ�΍폜����
        if ($favflag == 0 and (!$data[3] && !$data[4] && $data[9] <= 1)) {
            @unlink($idxfile);
        } else {
            P2Util::recKeyIdx($idxfile, $data);
        }
    }

    // }}}

    //================================================
    // ����
    //================================================
    $neolines = array();
    $before_line_num = 0;

    if ($setnum == 0) {
        $favlist_file = $__conf['favlist_file'];
    } else {
        $favlist_file = $_conf['pref_dir'] . sprintf('/p2_favlist%d.idx', $setnum);
    }

    // favlist�t�@�C�����Ȃ���ΐ���
    FileCtl::make_datafile($favlist_file, $_conf['favlist_perm']);

    // favlist�ǂݍ���
    $favlines = file($favlist_file);
    if ($favlines === false) {
        return false;
    }

    // �ŏ��ɏd���v�f���폜���Ă���
    if (!empty($favlines)) {
        $i = -1;
        foreach ($favlines as $line) {
            $i++;
            $line = rtrim($line);
            $lar = explode('<>', $line);
            // �d�����
            if ($lar[1] == $key && $lar[11] == $bbs) {
                $before_line_num = $i; // �ړ��O�̍s�ԍ����Z�b�g
                continue;
            // key�̂Ȃ����͕̂s���f�[�^�Ȃ̂ŃX�L�b�v
            } elseif (!$lar[1]) {
                continue;
            } else {
                $neolines[] = $line;
            }
        }
    }

    // �L�^�f�[�^�ݒ�
    if ($setfav) {
        $newdata = "$data[0]<>{$key}<>$data[2]<>$data[3]<>$data[4]<>$data[5]<>1<>$data[7]<>$data[8]<>$data[9]<>{$host}<>{$bbs}";
        include_once P2_LIBRARY_DIR . '/getsetposlines.inc.php';
        $rec_lines = getSetPosLines($neolines, $newdata, $before_line_num, $setfav);
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
    if (file_put_contents($_conf['favlist_file'], $cont, LOCK_EX) === false) {
        trigger_error("file_put_contents(" . $_conf['favlist_file'] . ")", E_USER_WARNING);
        die('Error: cannot write file.');
        return false;
    }


    // ���C�ɃX�����L
    if ($_conf['join_favrank'] && $_conf['favlist_file'] == $__conf['favlist_file']) {
        $act = '';
        if ($setfav == "0") {
            $act = "out";
        } elseif ($setfav == "1") {
            $act = "add";
        }
        if ($act) {
            $itaj = P2Util::getItaName($host, $bbs);
            $post = array("host" => $host, "bbs" => $bbs, "key" => $key, "ttitle" => $data[0], "ita" => $itaj, "act" => $act);
            postFavRank($post);
        }
    }

    return true;
}

/**
 * ���C�ɃX�����L�Ń|�X�g����֐�
 *
 * @return  boolean  ���s����
 */
function postFavRank($post)
{
    global $_conf;

    $method = "POST";
    $httpua_fmt = "Monazilla/1.00 (%s/%s; expack-%s)";
    $httpua = sprintf($httpua_fmt, $_conf['p2name'], $_conf['p2version'], $_conf['p2expack']);

    $URL = parse_url($_conf['favrank_url']);
    if (isset($URL['query'])) {
        $URL['query'] = "?" . $URL['query'];
    } else {
        $URL['query'] = '';
    }

    // �v���L�V
    if ($_conf['proxy_use']) {
        $send_host = $_conf['proxy_host'];
        $send_port = $_conf['proxy_port'];
        $send_path = $url;
    } else {
        $send_host = $URL['host'];
        $send_port = $URL['port'];
        $send_path = $URL['path'].$URL['query'];
    }

    if (!$send_port) {$send_port = 80;}

    $request = $method . " " . $send_path . " HTTP/1.0\r\n";
    $request .= "Host: " . $URL['host'] . "\r\n";
    $request .= "User-Agent: " . $httpua . "\r\n";
    $request .= "Connection: Close\r\n";

    // POST�̎��̓w�b�_��ǉ����Ė�����URL�G���R�[�h�����f�[�^��Y�t
    if (strtoupper($method) == "POST") {
        while (list($name, $value) = each($post)) {
            $POST[] = $name . "=" . urlencode($value);
        }
        $postdata = implode("&", $POST);
        $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $request .= "Content-Length: " . strlen($postdata) . "\r\n";
        $request .= "\r\n";
        $request .= $postdata;
    } else {
        $request .= "\r\n";
    }

    // WEB�T�[�o�֐ڑ�
    $fp = fsockopen($send_host, $send_port, $errno, $errstr, 3);
    if (!$fp) {
        //echo "�T�[�o�ڑ��G���[: $errstr ($errno)<br>\n";
        //echo "p2 info: {$_conf['favrank_url']} �ɐڑ��ł��܂���ł����B<br>";
        return false;
    }

    fputs($fp, $request);
    fclose($fp);

    return true;
    //return $body;
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
