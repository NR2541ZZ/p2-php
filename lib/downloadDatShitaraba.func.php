<?php
// p2 - �������JBBS�ijbbs.livedoor.jp�j�̊֐�

require_once P2_LIB_DIR . '/FileCtl.php';

/**
 * �������JBBS�� rawmode.cgi ��ǂ�ŁAdat�ɕۑ�����i2ch���ɐ��`�j
 * @see http://blog.livedoor.jp/bbsnews/archives/50283526.html
 *
 * @access  public
 * @return  boolean
 */
function downloadDatShitaraba(&$ThreadRead)
{
    // {{{ ����dat�̎擾���X�����K�����ǂ�����O�̂��߃`�F�b�N
    
    if (file_exists($ThreadRead->keydat)) {
        $dls = file($ThreadRead->keydat);
        if (sizeof($dls) != $ThreadRead->gotnum) {
            // echo 'bad size!<br>';
            unlink($ThreadRead->keydat);
            $ThreadRead->gotnum = 0;
        }
    } else {
        $ThreadRead->gotnum = 0;
    }
    
    // }}}
    
    if ($ThreadRead->gotnum == 0) {
        $file_append = false;
        $START = 1;
    } else {
        $file_append = true;
        $START = $ThreadRead->gotnum + 1;
    }

    // JBBS@�������
    if (P2Util::isHostJbbsShitaraba($ThreadRead->host)) {
        // ������΂�livedoor�ړ]�ɑΉ��B�Ǎ����livedoor�Ƃ���B
        $host = P2Util::adjustHostJbbsShitaraba($ThreadRead->host);
        list($host, $category, ) = explode('/', $host);
        $machiurl = "http://{$host}/bbs/rawmode.cgi/{$category}/{$ThreadRead->bbs}/{$ThreadRead->key}/{$START}-";
    }

    $tempfile = $ThreadRead->keydat . '.dat.temp'; // dat��2�d�ɂȂ��Ă邯�ǂ�����
    
    FileCtl::mkdirFor($tempfile);
    $machiurl_res = P2Util::fileDownload($machiurl, $tempfile);
    
    if (!$machiurl_res or !$machiurl_res->is_success()) {
        $ThreadRead->diedat = true;
        return false;
    }
    
    // ������΂Ȃ�EUC��SJIS�ɕϊ�
    if (P2Util::isHostJbbsShitaraba($ThreadRead->host)) {
        $temp_data = file_get_contents($tempfile);
        $temp_data = mb_convert_encoding($temp_data, 'SJIS-win', 'eucJP-win');
        if (false === FileCtl::filePutRename($tempfile, $temp_data)) {
            die('Error: cannot write file.');
        }
    }
    
    $mlines = file($tempfile);
    
    // �ꎞ�t�@�C�����폜����
    unlink($tempfile);

    // ��rawmode.cgi�ł͂���͏o�Ȃ����낤
    /*
    // �iJBBS�jERROR!: �X���b�h������܂���B�ߋ����O�q�ɂɂ�����܂���B
    if (preg_match("/^ERROR.*$/i", $mlines[0], $matches)) {
        $ThreadRead->pushDownloadDatErrorMsgHtml($matches[0]);
        $ThreadRead->diedat = true;
        return false;
    }
    */

    // {{{ DAT����������
    
    $latest_num = 0;
    if ($mdatlines = _shitarabaDatTo2chDatLines($mlines, $latest_num)) {

        $cont = '';
        for ($i = $START; $i <= $latest_num; $i++) {
            if ($mdatlines[$i]) {
                $cont .= $mdatlines[$i];
            } else {
                $cont .= "���ځ[��<>���ځ[��<>���ځ[��<>���ځ[��<>\n";
            }
        }
        
        $done = false;
        if ($fp = fopen($ThreadRead->keydat, 'ab+')) {
            flock($fp, LOCK_EX);
            if (false !== fwrite($fp, $cont)) {
                $done = true;
            }
            flock($fp, LOCK_UN);
            fclose($fp);
        }
        if (!$done) {
            trigger_error('cannot write file (' . $ThreadRead->keydat . ')', E_USER_WARNING);
            die('Error: cannot write file.');
        }
    }
    
    // }}}
    
    $ThreadRead->isonline = true;
    
    return true;
}


/**
 * �������BBS�� rawmode.cgi �œǂݍ���DAT��2ch��dat�ɕϊ�����
 *
 * @access  private
 * @return  array|false
 */
function _shitarabaDatTo2chDatLines($mlines, &$latest_num)
{
    if (!$mlines) {
        return false;
    }
    
    $mdatlines = array();
    
    foreach ($mlines as $ml) {
        $ml = rtrim($ml);

        // 1<><font color=#FF0000>�Ǘ��l</font><>sage<>2005/04/06(��) 21:44:54<>Pandemonium�����X���b�h�ł��B���X���́@<a href="/bbs/read.cgi/game/10109/1112791494/950" target="_blank">&gt;&gt;950</a> �����ӂ������Đ\�����鎖�B<br><br>��5W1H�̖@���𖳎��������̂͑S�ĕ��u�ł��肢���܂��B<br>���S���E���R�S���N���E����E�x��E�`�`�͕��u�ŁB�����ł��Ȃ��l�͓��ނƂ݂Ȃ���܂��B<br>���E�l�ɑ΂���S���s�ׁE�e�W���u�̒@���Ȃǐ��̃X���ł��肢���܂��B<br>�������s�ׂ̕��R�e�͊��S���u�ŁB���X�Ƃ����a��^���Ȃ��悤�ɂ��܂��傤�B<br>���ȏ�𓥂܂��Ĉ��Y�̓x���߂���ꍇ�͍폜�˗��X���ɂ��肢���܂��B<br><br>[�O�X��]�y�t�[���|�b�v�zPandemonium(20)Part.41�y���ꂷ�珬���z<br>http://jbbs.livedoor.jp/bbs/read.cgi/game/10109/1109905935/<>�y�����́zPandemonium(20)Part.42�y�ʂ̔���|�z<>EM04DJXI

        $data = explode('<>', $ml);
        
        $order = $data[0];
        $name  = $data[1];
        $mail  = $data[2];
        $date  = $data[3];
        $body  = $data[4];
        if ($order == 1) {
            $mtitle = $data[5];
        }
        if ($data[6]) {
            $date .= " ID:".$data[6];
        }

        /* rawmode.cgi �ł͂���͂Ȃ�
        // �������JBBS jbbs.livedoor.com ��link.cgi������
        // <a href="http://jbbs.livedoor.jp/bbs/link.cgi?url=http://dempa.2ch.net/gazo/free/img-box/img20030424164949.gif" target="_blank">http://dempa.2ch.net/gazo/free/img-box/img20030424164949.gif</a>
        $body = preg_replace('{<a href="(?:http://jbbs\.(?:shitaraba\.com|livedoor\.(?:com|jp)))?/bbs/link\.cgi\?url=([^"]+)" target="_blank">([^><]+)</a>}i', '$1', $body);
        */

        // �����N�O��
        $body = preg_replace('{<a href="(https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+\$,%#]+)" target="_blank">(https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+\$,%#]+)</a>}i', '$1', $body);
        
        $b = "\n";
        $s = '<>';
        if ($order == 1) {
            $datline = implode($s, array($name, $mail, $date, $body, $mtitle)) . $b;
        } else {
            $datline = implode($s, array($name, $mail, $date, $body, '')) . $b;
        }
        $mdatlines[$order] = $datline;
        if ($order > $latest_num) {
            $latest_num = $order;
        }
    }
    
    return $mdatlines;
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
