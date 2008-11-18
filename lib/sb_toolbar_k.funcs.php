<?php
// p2 -  �T�u�W�F�N�g -  �c�[���o�[�\���i�g�сj
// for subject.php

//========================================================================
// �֐�
//========================================================================
/**
 * �V���܂Ƃߓǂ� <a>
 *
 * @return  string  HTML
 */
function getShinchakuMatomeATag($aThreadList, $shinchaku_num)
{
    global $_conf;
    static $upper_toolbar_done_;
    
    $shinchaku_matome_atag = '';
    
    // �q�ɂȂ�V���܂Ƃ߂̃����N�͂Ȃ�
    if ($aThreadList->spmode == 'soko') {
        return $shinchaku_matome_atag = '';
    }
    
    $attrs = array();
    
    if (UA::isIPhoneGroup()) {
        $attrs['class'] = 'button';
    }
    
    // �㉺����c�[���o�[�̉������ɃA�N�Z�X�L�[������
    if (!empty($upper_toolbar_done_)) {
        $attrs[$_conf['accesskey']] = $_conf['k_accesskey']['matome'];
    }
    $upper_toolbar_done_ = true;
    
    $qs = array(
        'host'   => $aThreadList->host,
        'bbs'    => $aThreadList->bbs,
        'spmode' => $aThreadList->spmode,
        'nt'     => date('gis'),
        UA::getQueryKey() => UA::getQueryValue()
    );
    $label = "{$_conf['k_accesskey']['matome']}.�V�܂Ƃ�";
    
    if ($shinchaku_num) {
        $shinchaku_matome_atag = P2View::tagA(
            P2Util::buildQueryUri(
                $_conf['read_new_k_php'],
                array_merge($qs, array('norefresh' => '1'))
            ),
            hs("$label({$shinchaku_num})"),
            $attrs
        );
    
    } else {
        $shinchaku_matome_atag = P2View::tagA(
            P2Util::buildQueryUri(
                $_conf['read_new_k_php'],
                $qs
            ),
            hs($label),
            $attrs
        );
    }
    
    return $shinchaku_matome_atag;
}
