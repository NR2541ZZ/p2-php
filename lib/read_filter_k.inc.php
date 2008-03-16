<?php
/**
 * p2 - �g�тŃ��X�t�B���^�����O�����Ƃ��̃y�[�W�J�ڗp�p�����[�^��ݒ肷��
 */

/**
 * �y�[�W�J�ڗp�̊�{URL(�G�X�P�[�v�ς�)�𐶐�����
 *
 * @param   object Thread $aThread  �X���b�h�I�u�W�F�N�g
 * @param   array $res_filter       �t�B���^�����O�̃p�����[�^
 * @return  string  �y�[�W�J�ڗp�̊�{URL
 */
function setFilterQuery($aThread, $res_filter)
{
    global $filter_q;
    $filter_q = '?host=' . $aThread->host . $bbs_q . $key_q . $offline_q;
    $filter_q .= '&amp;word=' . rawurlencode($_GET['word']);
    foreach ($res_filter as $key => $value) {
        $filter_q .= '&amp;' . rawurlencode($key) . '= ' . rawurlencode($value);
    }
    $filter_q .= '&amp;ls=all&amp;filter_page=';
    return $filter_q;
}

// �����ݒ�
if (isset($aThread) && isset($res_filter)) {
    $GLOBALS['filter_q'] = setFilterQuery($aThread, $res_filter);
}

/**
 * �w�b�_�ɕ\������i�r�Q�[�V�����p�̕ϐ�������������
 *
 * @return  void
 */
function resetReadNaviHeaderK()
{
    $GLOBALS['prev_st'] = '�O*';
    $GLOBALS['next_st'] = '��*';
    $GLOBALS['read_navi_previous'] = '';
    $GLOBALS['read_navi_next'] = '';
}

/**
 * �t�b�^�ɕ\������i�r�Q�[�V�����p�̕ϐ�������������
 *
 * @return  void
 */
function resetReadNaviFooterK()
{
    global $_conf;
    global $prev_st, $read_navi_previous_btm;
    global $next_st, $read_navi_next_btm;
    global $read_footer_navi_new_btm;
    global $filter_range, $filter_hits, $filter_page, $filter_q;

    if ($filter_page > 1) {
        $read_navi_previous_url = $_conf['read_php'] . $filter_q . ($filter_page - 1) . $_conf['k_at_a'];
        $read_navi_previous_btm = "<a {$_conf['accesskey']}=\"{$_conf['k_accesskey']['prev']}\" href=\"{$read_navi_previous_url}\">{$_conf['k_accesskey']['prev']}.{$prev_st}</a>";
    }

    if ($filter_range['to'] < $filter_hits) {
        $read_navi_next_url = $_conf['read_php'] . $filter_q . ($filter_page + 1) . $_conf['k_at_a'];
        $read_navi_next_btm = "<a {$_conf['accesskey']}=\"{$_conf['k_accesskey']['next']}\" href=\"{$read_navi_next_url}\">{$_conf['k_accesskey']['next']}.{$next_st}</a>";
    }

    $read_footer_navi_new_btm = str_replace(" {$_conf['accesskey']}=\"{$_conf['k_accesskey']['next']}\"", '', $read_footer_navi_new_btm);
    $read_footer_navi_new_btm = str_replace(">{$_conf['k_accesskey']['next']}.", '>', $read_footer_navi_new_btm);
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
