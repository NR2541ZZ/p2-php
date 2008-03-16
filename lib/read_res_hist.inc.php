<?php
// p2 - �������ݗ��� �̂��߂̊֐��Q�B�i�N���X�ɂ������Ƃ���j

require_once P2_LIBRARY_DIR . '/dataphp.class.php';

/**
 * �`�F�b�N�����������݋L�����폜����
 *
 * @access  public
 * @return  boolean
 */
function deleMsg($checked_hists)
{
    global $_conf;

    if (!$reslines = file($_conf['p2_res_hist_dat'])) {
        P2Util::printSimpleHtml("p2 Error: {$_conf['p2_res_hist_dat']} ���J���܂���ł���");
        die('');
        return false;
    }
    $reslines = array_map('rtrim', $reslines);

    // �t�@�C���̉��ɋL�^����Ă�����̂��V�����̂ŋt���ɂ���
    $reslines = array_reverse($reslines);

    $neolines = array();

    // �`�F�b�N���Đ�����
    if ($reslines) {
        $n = 1;
        $rmnums = array();
        foreach ($reslines as $ares) {
            $rar = explode("<>", $ares);

            // �ԍ��Ɠ��t����v���邩���`�F�b�N����
            if (checkMsgID($checked_hists, $n, $rar[2])) {
                $rmnums[] = $n; // �폜����ԍ���o�^
            }

            $n++;
        }
        $neolines = rmLine($rmnums, $reslines);

        P2Util::pushInfoHtml("<p>p2 info: " . count($rmnums) . "���̃��X�L�����폜���܂���</p>");
    }

    if (is_array($neolines)) {
        // �s����߂�
        $neolines = array_reverse($neolines);

        $cont = '';
        if ($neolines) {
            $cont = implode("\n", $neolines) . "\n";
        }

        // �������ݏ���
        if (FileCtl::filePutRename($_conf['p2_res_hist_dat'], $cont) === false) {
            $errmsg = sprintf('p2 error: %s(), FileCtl::filePutRename() failed.', __FUNCTION__);
            trigger_error($errmsg, E_USER_WARNING);
            return false;
        }
    }
    return true;
}

/**
 * �ԍ��Ɠ��t����v���邩���`�F�b�N����
 *
 * @return  boolean  ��v������true
 */
function checkMsgID($checked_hists, $order, $date)
{
    if ($checked_hists) {
        foreach ($checked_hists as $v) {
            $vary = explode(",,,,", $v);    // ",,,," �͊O�����痈��ϐ��ŁA����ȕςȃf���~�^
            if (($vary[0] == $order) and ($vary[1] == $date)) {
                return true;
            }
        }
    }
    return false;
}

/**
 * �w�肵���ԍ��i�z��w��j���s���X�g����폜����
 *
 * @return  array|false  �폜�������ʂ̍s���X�g��Ԃ�
 */
function rmLine($rmnums, $lines)
{
    if ($lines) {
        $neolines = array();
        $i = 0;
        foreach ($lines as $l) {
            $i++;
            if (in_array($i, $rmnums)) {
                continue; // �폜����
            }
            $neolines[] = $l;
        }
        return $neolines;
    }
    return false;
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
