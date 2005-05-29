<?php
// p2 - �������ݗ��� �̂��߂̊֐��Q

require_once (P2_LIBRARY_DIR . '/dataphp.class.php');

//======================================================================
// �֐�
//======================================================================
/**
 * �`�F�b�N�����������݋L�����폜����
 */
function deleMsg($checked_hists)
{
    global $_conf;

    // �ǂݍ����
    if (!$reslines = file($_conf['p2_res_hist_dat'])) {
        die("p2 Error: {$_conf['p2_res_hist_dat']} ���J���܂���ł���");
    }
    $reslines = array_map('rtrim', $reslines);
    
    // �t�@�C���̉��ɋL�^����Ă�����̂��V�����̂ŋt���ɂ���
    $reslines = array_reverse($reslines);
    
    // �`�F�b�N���Đ�����
    if ($reslines) {
        $n = 1;
        foreach ($reslines as $ares) {
            $rar = explode("<>", $ares);
            
            // �ԍ��Ɠ��t����v���邩���`�F�b�N����
            if (checkMsgID($checked_hists, $n, $rar[2])) {
                $rmnums[] = $n; // �폜����ԍ���o�^
            }
            
            $n++;
        }
        $neolines = rmLine($rmnums, $reslines);
    }
    
    if (is_array($neolines)) {
        // �s����߂�
        $neolines = array_reverse($neolines);
        
        $cont = "";
        if ($neolines) {
            $cont = implode("\n", $neolines) . "\n";
        }
        // �������ݏ���
        FileCtl::file_write_contents($_conf['p2_res_hist_dat'], $cont);
    }
}

/**
 * �ԍ��Ɠ��t����v���邩���`�F�b�N����
 */
function checkMsgID($checked_hists, $order, $date)
{
    if ($checked_hists) {
        foreach ($checked_hists as $v) {
            $vary = explode(",,,,", $v);    // ",,,," �͊O�����痈��ϐ��ŁA����ȃf���~�^
            if (($vary[0] == $order) and ($vary[1] == $date)) {
                return true;
            }
        }
    }
    return false;
}

/**
 * �w�肵���ԍ��i�z��w��j���s���X�g����폜����
 */
function rmLine($order_list, $lines)
{
    if ($lines) {
        $neolines = array();
        $i = 0;
        foreach ($lines as $l) {
            $i++;
            if (checkOrder($order_list, $i)) { continue; } // �폜����
            $neolines[] = $l;
        }
        return $neolines;
    }
    return false;
}

/**
 * �ԍ��Ɣz����r
 */
function checkOrder($order_list, $order)
{
    if ($order_list) {
        foreach ($order_list as $n) {
            if ($n == $order) {
                return true;
            }
        }
    }
    return false;
}

?>
