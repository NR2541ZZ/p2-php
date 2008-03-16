<?php
/*
    p2 - �X���b�h�f�[�^�ADAT���폜���邽�߂̊֐��S
*/

require_once P2_LIBRARY_DIR . '/filectl.class.php';
require_once P2_LIBRARY_DIR . '/setfav.inc.php';
require_once P2_LIBRARY_DIR . '/setpalace.inc.php';

/**
 * �w�肵���z��keys�̃��O�iidx, (dat, srd)�j���폜���āA
 * ���łɗ���������O���B���C�ɃX���A�a��������O���B
 *
 * ���[�U�����O���폜���鎞�́A�ʏ킱�̊֐����Ă΂��
 *
 * @access  public
 * @param   array  $keys  �폜�Ώۂ�key���i�[�����z��
 * @return  integer|false   �폜�ł�����1, �폜�Ώۂ��Ȃ����2��Ԃ��B���s�������false�B
 */
function deleteLogs($host, $bbs, $keys)
{
    // �w��key�̃��O���폜�i�Ώۂ���̎��j
    if (is_string($keys)) {
        $akey = $keys;
        offRecent($host, $bbs, $akey);
        offResHist($host, $bbs, $akey);
        setFav($host, $bbs, $akey, 0);
        setPal($host, $bbs, $akey, 0);
        $r = deleteThisKey($host, $bbs, $akey);

    // �w��key�z��̃��O���폜
    } elseif (is_array($keys)) {
        $rs = array();
        foreach ($keys as $akey) {
            offRecent($host, $bbs, $akey);
            offResHist($host, $bbs, $akey);
            setFav($host, $bbs, $akey, 0);
            setPal($host, $bbs, $akey, 0);
            $rs[] = deleteThisKey($host, $bbs, $akey);
        }
        if (array_search(1, $rs) !== false) {
            $r = 1;
        } elseif (array_search(2, $rs) !== false) {
            $r = 2;
        } else {
            $r = false;
        }
    }
    return $r;
}

/**
 * �w�肵���L�[�̃X���b�h���O�iidx (,dat)�j���폜����
 *
 * �ʏ�́A���̊֐��𒼐ڌĂяo�����Ƃ͂Ȃ��BdeleteLogs() ����Ăяo�����B
 *
 * @see deleteLogs()
 * @return  integer|false  �폜�ł�����1, �폜�Ώۂ��Ȃ����2��Ԃ��B���s�������false�B
 */
function deleteThisKey($host, $bbs, $key)
{
    global $_conf;

    $dat_host_dir = P2Util::datDirOfHost($host);
    $idx_host_dir = P2Util::idxDirOfHost($host);

    $anidx = $idx_host_dir . '/' . $bbs . '/' . $key . '.idx';
    $adat  = $dat_host_dir . '/' . $bbs . '/' . $key . '.dat';

    // File�̍폜����
    // idx�i�l�p�ݒ�j
    if (file_exists($anidx)) {
        if (unlink($anidx)) {
            $deleted_flag = true;
        } else {
            $failed_flag = true;
        }
    }

    // dat�̍폜����
    if (file_exists($adat)) {
        if (unlink($adat)) {
            $deleted_flag = true;
        } else {
            $failed_flag = true;
        }
    }

    // ���s�������
    if (!empty($failed_flag)) {
        return false;
    // �폜�ł�����
    } elseif (!empty($deleted_flag)) {
        return 1;
    // �폜�Ώۂ��Ȃ����
    } else {
        return 2;
    }
}


/**
 * �w�肵���L�[���ŋߓǂ񂾃X���ɓ����Ă邩�ǂ������`�F�b�N����
 *
 * @access  public
 * @return  boolean  �����Ă�����true
 */
function checkRecent($host, $bbs, $key)
{
    global $_conf;

    if (!file_exists($_conf['rct_file'])) {
        return false;
    }

    $lines = file($_conf['rct_file']);
    // �����true
    if (is_array($lines)) {
        foreach ($lines as $l) {
            $l = rtrim($l);
            $lar = explode('<>', $l);
            // ��������
            if ($lar[1] == $key && $lar[10] == $host && $lar[11] == $bbs) {
                return true;
            }
        }
    }
    return false;
}

/**
 * �w�肵���L�[���������ݗ����ɓ����Ă邩�ǂ������`�F�b�N����
 *
 * @access  public
 * @return  boolean  �����Ă�����true
 */
function checkResHist($host, $bbs, $key)
{
    global $_conf;

    $rh_idx = $_conf['pref_dir'] . "/p2_res_hist.idx";

    if (!file_exists($rh_idx)) {
        return false;
    }

    $lines = file($rh_idx);
    // �����true
    if (is_array($lines)) {
        foreach ($lines as $l) {
            $l = rtrim($l);
            $lar = explode('<>', $l);
            // ��������
            if ($lar[1] == $key && $lar[10] == $host && $lar[11] == $bbs) {
                return true;
            }
        }
    }
    return false;
}

/**
 * �w�肵���L�[�̗����i�ŋߓǂ񂾃X���j���폜����
 *
 * @access  public
 * @return  integer|false  �폜�����Ȃ�1, �폜�Ώۂ��Ȃ����2�B���s��false
 */
function offRecent($host, $bbs, $key)
{
    global $_conf;

    if (!file_exists($_conf['rct_file'])) {
        return 2;
    }

    $lines = file($_conf['rct_file']);
    if ($lines === false) {
        return false;
    }

    $neolines = array();

    // {{{ ����΍폜

    if (is_array($lines)) {
        foreach ($lines as $line) {
            $line = rtrim($line);
            $lar = explode('<>', $line);
            // �폜�i�X�L�b�v�j
            if ($lar[1] == $key && $lar[10] == $host && $lar[11] == $bbs) {
                $done = true;
                continue;
            }
            $neolines[] = $line;
        }
    }

    // }}}
    // {{{ ��������

    if (is_array($neolines)) {
        $cont = '';
        foreach ($neolines as $l) {
            $cont .= $l . "\n";
        }

        if (FileCtl::filePutRename($_conf['rct_file'], $cont) === false) {
            $errmsg = sprintf('p2 error: %s(), FileCtl::filePutRename() failed.', __FUNCTION__);
            trigger_error($errmsg, E_USER_WARNING);
            return false;
        }

    }

    // }}}

    if (!empty($done)) {
        return 1;
    } else {
        return 2;
    }
}

/**
 * �w�肵���L�[�̏������ݗ������폜����
 *
 * @access  public
 * @return  integer|false  �폜�����Ȃ�1, �폜�Ώۂ��Ȃ����2�B���s��false
 */
function offResHist($host, $bbs, $key)
{
    global $_conf;

    $rh_idx = $_conf['pref_dir'] . '/p2_res_hist.idx';

    if (!file_exists($rh_idx)) {
        return 2;
    }

    $lines = file($rh_idx);
    if ($lines === false) {
        return false;
    }

    $neolines = array();

    // {{{ ����΍폜

    if (is_array($lines)) {
        foreach ($lines as $l) {
            $l = rtrim($l);
            $lar = explode('<>', $l);
            // �폜�i�X�L�b�v�j
            if ($lar[1] == $key && $lar[10] == $host && $lar[11] == $bbs) {
                $done = true;
                continue;
            }
            $neolines[] = $l;
        }
    }

    // }}}
    // {{{ ��������

    if (is_array($neolines)) {
        $cont = '';
        foreach ($neolines as $l) {
            $cont .= $l . "\n";
        }

        if (FileCtl::filePutRename($rh_idx, $cont) === false) {
            $errmsg = sprintf('p2 error: %s(), FileCtl::filePutRename() failed.', __FUNCTION__);
            trigger_error($errmsg, E_USER_WARNING);
            return false;
        }

    }

    // }}}

    if (!empty($done)) {
        return 1;
    } else {
        return 2;
    }
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
