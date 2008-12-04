<?php
// index�p�֐�

/**
 * @access  public
 * @return  array
 */
function getIndexMenuKIni()
{
    global $_conf;
    
    // 2008/11/15 �� $_conf['menuKIni']
    $indexMenuKIni = array(
        'recent_shinchaku'  => array(
            $_conf['subject_php'] . '?spmode=recent&sb_view=shinchaku',
            '�ŋߓǂ񂾃X���̐V��'
        ),
        'recent'            => array(
            $_conf['subject_php'] . '?spmode=recent&norefresh=1',
            '�ŋߓǂ񂾃X���̑S��'
        ),
        'fav_shinchaku'     => array(
            $_conf['subject_php'] . '?spmode=fav&sb_view=shinchaku',
            '���C�ɃX���̐V��'
        ),
        'fav'               => array(
            $_conf['subject_php'] . '?spmode=fav&norefresh=1',
            '���C�ɃX���̑S��'
        ),
        'favita'            => array(
            $_conf['menu_k_php'] . '?view=favita',
            '���C�ɔ�'
        ),
        'cate'              => array(
            $_conf['menu_k_php'] . '?view=cate',
            '���X�g'
        ),
        'res_hist'          => array(
            $_conf['subject_php'] . '?spmode=res_hist',
            '��������'
        ),
        'palace'            => array(
            $_conf['subject_php'] . '?spmode=palace&norefresh=1',
            '�X���̓a��'
        ),
        'setting'           => array(
            'setting.php?dummy=1',
            '���O�C���Ǘ�'
        ),
        'editpref'          => array(
            $_conf['editpref_php'] . '?dummy=1',
            '�ݒ�Ǘ�'
        )
    );
    
    // �g�тȂ甼�p�ɕϊ�
    if (UA::isK()) {
        foreach ($indexMenuKIni as $k => $v) {
            $indexMenuKIni[$k][1] = mb_convert_kana($indexMenuKIni[$k][1], 'rnsk');
        }
    }
    
    return $indexMenuKIni;
}

/**
 * index���j���[���ڂ̃����NHTML�z����擾����
 *
 * @access  public
 * @param   array   $menuKIni  ���j���[���� �W���ݒ�
 * @return  array
 */
function getIndexMenuKLinkHtmls($menuKIni, $noLink = false)
{
    global $_conf;
    
    $menuLinkHtmls = array();

    // ���[�U�ݒ菇���Ń��j���[HTML���擾
    foreach ($_conf['index_menu_k'] as $code) {
        if (isset($menuKIni[$code])) {
            if ($html = _getMenuKLinkHtml($code, $menuKIni, $noLink)) {
                $menuLinkHtmls[$code] = $html;
                unset($menuKIni[$code]);
            }
        }
    }
    if ($menuKIni) {
        foreach ($menuKIni as $code => $menu) {
            if ($html = _getMenuKLinkHtml($code, $menuKIni, $noLink)) {
                $menuLinkHtmls[$code] = $html;
                unset($menuKIni[$code]);
            }
        }
    }
    return $menuLinkHtmls;
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
