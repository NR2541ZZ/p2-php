<?php
// p2 - �`���b�g�����˂� cha2.net �̊֐�

/**
 * �`���b�g�����˂� cha2.net �� dat ��ǂ�ŕۑ�����֐�
 * �i�����擾�ɂ͖��Ή��j
 * ��2ch�݊��Ƃ��ēǂݍ���ł���̂Ō��݂��̊֐��͗��p�͂��Ă��Ȃ��B@see TreadRead->downloadDat()
 *
 * @access  public
 * @return  boolean
 */
function downloadDatCha2(&$ThreadRead)
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

    // �`���b�g�����˂�
    $cha2url = "http://{$ThreadRead->host}/cgi-bin/{$ThreadRead->bbs}/dat/{$ThreadRead->key}.dat";
    
    $datfile = $ThreadRead->keydat;
    
    FileCtl::mkdirFor($datfile);
    $cha2url_res = P2Util::fileDownload($cha2url, $datfile);
    
    if (!$cha2url_res or !$cha2url_res->is_success()) {
        $ThreadRead->diedat = true;
        return false;
    }
    
    $ThreadRead->isonline = true;
    
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
