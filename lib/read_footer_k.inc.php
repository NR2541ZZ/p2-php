<?php
/*
    p2 -  �X���b�h�\�� -  �t�b�^���� -  �g�їp for read.php
*/

//=====================================================================
// �t�b�^
//=====================================================================
// �\���͈�
if (isset($GLOBALS['word']) && $aThread->rescount) {
    $filter_range['end'] = min($filter_range['to'], $_filter_hits);
    $read_range_on = "{$filter_range['start']}-{$filter_range['end']}/{$_filter_hits}hit";

} elseif ($aThread->resrange_multi) {
    $read_range_on = hs($aThread->ls);

} elseif ($aThread->resrange['start'] == $aThread->resrange['to']) {
    $read_range_on = $aThread->resrange['start'];

} else {
    $read_range_on = "{$aThread->resrange['start']}-{$aThread->resrange['to']}";
}

$read_range_hs = $read_range_on . '/' . $aThread->rescount;
if (!empty($_GET['onlyone'])) {
    $read_range_hs = '����ޭ�>>1';
}

// ���X�Ԏw��ړ� etc.
$goto_ht = _kspform($aThread, isset($GLOBALS['word']) ? $last_hit_resnum : $aThread->resrange['to']);

$hr = P2View::getHrHtmlK();

//=====================================================================
// HTML�o��
//=====================================================================
if (($aThread->rescount or !empty($_GET['onlyone']) && !$aThread->diedat)) { // and (!$_GET['renzokupop'])

    if (!$aThread->diedat) {
        $dores_atag = _getDoResATag($aThread, $dores_st, $motothre_url);
    }
    
    $above_atag = P2View::tagA(
        '#header',
        "{$_conf['k_accesskey']['above']}.��",
        array($_conf['accesskey'] => $_conf['k_accesskey']['above'])
    );
    
    echo <<<EOP
<p>
    <a id="footer" name="footer">{$read_range_hs}</a><br>
    {$read_navi_previous_btm} 
    {$read_navi_next_btm} 
    {$read_navi_latest_btm}
    {$read_footer_navi_new_btm} 
    {$dores_atag}
    {$read_navi_filter_btm}<br>
</p>
<p>
    {$toolbar_right_ht} $above_atag
</p>
<p>{$goto_ht}</p>\n
EOP;
    if ($diedat_msg) {
        echo $hr . $diedat_msg;
        ?><p><?php echo $motothre_ht ?></p><?php
    }
}

echo $hr . P2View::getBackToIndexKATag() . "\n";
?>
</body></html>
<?php



//==================================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//==================================================================================
/**
 * ���X�ԍ����w�肵�� �ړ��E�R�s�[(+���p)�EAAS ����t�H�[���𐶐�����
 *
 * @param  string  $default  �f�t�H���g��ktool_value��value
 * @return string  HTML
 */
function _kspform($aThread, $default = '')
{
    global $_conf;

    // au��istyle���󂯕t����Bformat="4N" �Ŏw�肷��ƃ��[�U�ɂ����̓��[�h�̕ύX���s�\�ƂȂ��āA"-"�����͂ł��Ȃ��Ȃ��Ă��܂��B
    $numonly_at = ' maxlength="7" istyle="4" mode="numeric"';

    $form = sprintf('<form method="get" action="%s">', hs($_conf['read_php']));
    $form .= P2View::getInputHiddenKTag();

    $required_params = array('host', 'bbs', 'key');
    foreach ($required_params as $v) {
        if (!empty($_REQUEST[$v])) {
            $form .= sprintf(
                '<input type="hidden" name="%s" value="%s">',
                hs($v), hs($_REQUEST[$v])
            );
        } else {
            return '';
        }
    }
    $form .= '<input type="hidden" name="offline" value="1">';
    $form .= sprintf('<input type="hidden" name="rescount" value="%s">', hs($aThread->rescount));
    $form .= sprintf('<input type="hidden" name="ttitle_en" value="%s">', hs(base64_encode($aThread->ttitle)));

    $form .= '<select name="ktool_name">';
    $form .= '<option value="goto">GO</option>';
    $form .= '<option value="copy">��</option>';
    $form .= '<option value="copy_quote">&gt;��</option>';
    $form .= '<option value="res_quote">&gt;ڽ</option>';
    /*
    2006/03/06 aki �m�[�}��p2�ł͖��Ή�
    if ($_conf['expack.aas.enabled']) {
        $form .= '<option value="aas">AAS</option>';
        $form .= '<option value="aas_rotate">AAS*</option>';
    }
    */
    $form .= '</select>';

    $form .= sprintf(
        '<input type="text" size="3" name="ktool_value" value="%s" %s>',
        hs($default), $numonly_at
    );
    $form .= '<input type="submit" value="OK" title="OK">';

    $form .= '</form>';

    return $form;
}

/**
 * �� <a>
 *
 * @return  string  HTML
 */
function _getDoResATag($aThread, $dores_st, $motothre_url)
{
    global $_conf;
    
    $dores_atag = null;
    
    if ($_conf['disable_res']) {
        $dores_atag = P2View::tagA(
            $motothre_url,
            hs("{$_conf['k_accesskey']['res']}.{$dores_st}"),
            array(
                'target' => '_blank',
                $_conf['accesskey'] => $_conf['k_accesskey']['res']
            )
        );

    } else {
        $dores_atag = P2View::tagA(
            P2Util::buildQueryUri(
                'post_form.php',
                array(
                    'host' => $aThread->host,
                    'bbs'  => $aThread->bbs,
                    'key'  => $aThread->key,
                    'rescount' => $aThread->rescount,
                    'ttitle_en' => base64_encode($aThread->ttitle),
                    UA::getQueryKey() => UA::getQueryValue()
                )
            ),
            hs("{$_conf['k_accesskey']['res']}.{$dores_st}"),
            array(
                $_conf['accesskey'] => $_conf['k_accesskey']['res']
            )
        );
    }
    
    return $dores_atag;
}
