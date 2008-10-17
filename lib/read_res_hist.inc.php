<?php
// p2 - �������ݗ��� �̂��߂̊֐��Q�B�i�N���X�ɂ������Ƃ���A���邢��read_res_hist.funcs.php�ɉ����������j

/**
 * �������ݗ����̃��O���폜����
 *
 * @access  public
 * @return  boolean
 */
function deleteResHistDat()
{
    global $_conf;
    
    if (!file_exists($_conf['p2_res_hist_dat'])) {
        return true;
    }
    
    /*
    $bak_file = $_conf['p2_res_hist_dat'] . '.bak';
    if (strstr(PHP_OS, 'WIN') and file_exists($bak_file)) {
        unlink($bak_file);
    }
    rename($_conf['p2_res_hist_dat'], $bak_file);
    */
    
    return unlink($_conf['p2_res_hist_dat']);
}

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
        p2die(sprintf('%s ���J���܂���ł���', $_conf['p2_res_hist_dat']));
        return false;
    }
    $reslines = array_map('rtrim', $reslines);
    
    // �t�@�C���̉��ɋL�^����Ă�����̂��V�����̂ŋt���ɂ���
    $reslines = array_reverse($reslines);
    
    $neolines = array();
    
    // �`�F�b�N���Đ�����
    if ($reslines) {
        $rmnums = getRmNums($checked_hists, $reslines);
        $neolines = rmLine($rmnums, $reslines);
        
        P2Util::pushInfoHtml("<p>p2 info: " . count($rmnums) . "���̃��X�L�����폜���܂���</p>");
    }
    
    if (is_array($neolines)) {
        // �s����߂�
        $neolines = array_reverse($neolines);
        
        $cont = "";
        if ($neolines) {
            $cont = implode("\n", $neolines) . "\n";
        }
        
        // �������ݏ���
        if (false === FileCtl::filePutRename($_conf['p2_res_hist_dat'], $cont)) {
            $errmsg = sprintf('p2 error: %s(), FileCtl::filePutRename() failed.', __FUNCTION__);
            trigger_error($errmsg, E_USER_WARNING);
            return false;
        }
    }
    return true;
}

/**
 * �폜�Ώۂ̔ԍ���z��Ŏ擾����
 *
 * @return  array
 */
function getRmNums($checked_hists, $reslines)
{
    $order = 1;
    $rmnums = array();
    foreach ($reslines as $ares) {
        $rar = explode("<>", $ares);
        
        // �ԍ��Ɠ��t����v���邩���`�F�b�N����
        if (checkMsgID($checked_hists, $order, $rar[2])) {
            $rmnums[] = $order; // �폜����ԍ���o�^
        }
        // �S�����������甲����
        if (count($checked_hists) == count($rmnums)) {
            break;
        }
        $order++;
    }
    return $rmnums;
}

/**
 * �ԍ��Ɠ��t����v���邩���`�F�b�N����
 *
 * @param   array  $checked_hists
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
 * �w�肵���s�ԍ��i�z��Ɋi�[�j���s���X�g����폜����
 *
 * @param   array  $rmnums  �w��ԍ����i�[�����z��
 * @return  array|false  �폜�������ʂ̍s���X�g��Ԃ�
 */
function rmLine($rmnums, $lines)
{
    if ($lines) {
        $neolines = array();
        $order = 0;
        foreach ($lines as $l) {
            $order++; // �擪�s��1
            if (in_array($order, $rmnums)) {
                continue; // �폜����
            }
            $neolines[] = $l;
        }
        return $neolines;
    }
    return false;
}
