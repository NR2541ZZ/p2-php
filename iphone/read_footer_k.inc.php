<?php
/*
    p2 -  �X���b�h�\�� -  �t�b�^���� -  �g�їp for read.php
*/

//=====================================================================
// �t�b�^
//=====================================================================
// �\���͈�
$read_range_hs = _getReadRange($aThread) . '/' . $aThread->rescount;
if (!empty($_GET['onlyone'])) {
    $read_range_hs = '�v���r���[>>1';
}

// ���X�Ԏw��ړ� etc. iphone
//$goto_ht = _kspform($aThread, isset($GLOBALS['word']) ? $last_hit_resnum : $aThread->resrange['to']);

// �t�B���^�[�\�� Edit 080727 by 240
$seafrm_ht = _createFilterForm($aThread, $res_filter);
$hr = P2View::getHrHtmlK();

//=====================================================================
// HTML�o��
//=====================================================================
if (($aThread->rescount or !empty($_GET['onlyone']) && !$aThread->diedat)) { // and (!$_GET['renzokupop'])

    if (!$aThread->diedat) {
        if (!empty($_conf['disable_res'])) {
            $dores_ht = <<<EOP
      | <a href="{$motothre_url}" target="_blank" >{$dores_st}</a>
EOP;
        } else {
            $dores_ht = P2View::tagA(
                UriUtil::buildQueryUri('post_form_i.php',
                    array(
                        'host' => $aThread->host,
                        'bbs'  => $aThread->bbs,
                        'key'  => $aThread->key,
                        'rescount' => $aThread->rescount,
                        'ttitle_en' => $ttitle_en,
                        UA::getQueryKey() => UA::getQueryValue()
                    )
                ),
                hs($dores_st)
            );
        }
    }
    
    //iPhone �\���p�t�b�^ 080725
    //�@�O�A���A�V�� �������͍�
    if ($read_navi_latest_btm_ht) {
       $new_btm_ht = "<li class=\"new\">{$read_navi_latest_btm_ht}</li>";
    }
    if ($read_footer_navi_new_btm_ht) {
        $new_btm_ht = "<li class=\"new\">{$read_footer_navi_new_btm_ht}</li>"; 
    }
    if ($read_navi_previous_ht) { 
        $read_navi_previous_tab_ht = "<li class=\"prev\">{$read_navi_previous_ht} </li>";
    } else {
        $read_navi_previous_tab_ht = "<li id=\"blank\" class=\"prev\"></li>";
    }
    if ($read_navi_next_btm_ht) {
        $read_navi_next_btm_tab_ht = "<li class=\"next\">{$read_navi_next_btm_ht}</li>";
    } else {
        $read_navi_next_btm_tab_ht = "<li id=\"blank\" class=\"next\"></li>";
    }
    
    $index_uri = UriUtil::buildQueryUri('index.php', array(UA::getQueryKey() => UA::getQueryValue()));
    ?>
<?php echo $toolbar_back_board_ht; ?>
<div class="footform">
<a id="footer" name="footer"></a>
<?php echo $goto_select_ht; ?>
</div>
<div id="footbar01">
	<div class="footbar">
		<ul>
		<li class="home"><a href="<?php eh($index_uri); ?>">TOP</a></li>
		<?php echo $read_navi_previous_tab_ht; ?> 
		<?php echo $new_btm_ht; ?>
		<li class="res" id="writeId" title="off"><a onclick="popUpFootbarFormIPhone(1);all.item('footbar02').style.visibility='hidden';">��������</a></li>
		<li class="other"><a onclick="all.item('footbar02').style.visibility='visible';popUpFootbarFormIPhone(0, 1);popUpFootbarFormIPhone(1, 1);">���̑�</a></li>
		<?php echo $read_navi_next_btm_tab_ht; ?>
		</ul>
	</div>
</div>
<div id="footbar02" class="dialog_other">
<filedset>
<ul>
	<li class="whiteButton" id="serchId" title="off" onclick="popUpFootbarFormIPhone(0);all.item('footbar02').style.visibility='hidden'">�t�B���^����</li>
	<?php echo $toolbar_right_ht; ?> 
	<li class="grayButton" onclick="all.item('footbar02').style.visibility='hidden'">�L�����Z��</li>
</ul>
</filedset>
</div>
<?php echo $seafrm_ht; ?>
<?php

/* �������݃t�H�[��------------------------------------ */
    $bbs        = $aThread->bbs;
    $key        = $aThread->key;
    $host       = $aThread->host;
    $rescount   = $aThread->rescount;
    $ttitle_en  = base64_encode($aThread->ttitle);
    
    $submit_value = '��������';

    $key_idx = $aThread->keyidx;

    // �t�H�[���̃I�v�V�����ǂݍ���
    require_once P2_IPHONE_LIB_DIR . '/post_options_loader_popup.inc.php';

// �X���b�h�^�C�g���̍쐬
    $htm['resform_ttitle'] = <<<EOP
<p><b class="thre_title">{$aThread->ttitle_hs}</b></p>
EOP;

    // �t�H�[���̍쐬
    require_once P2_IPHONE_LIB_DIR . '/post_form_popup.inc.php';

    $sid_q = (defined('SID') && strlen(SID)) ? '&amp;' . hs(SID) : '';

    // �v�����g
    echo $htm['post_form'];
    
/* ------------------------------------------------------------ */
    if ($diedat_msg_ht) {
        //echo '<hr>';
        echo $diedat_msg_ht;
        echo "<p>$motothre_atag</p>";
    }
}
//echo "<hr>" . P2View::getBackToIndexKATag() . "\n";
/*
080726 �t�b�^�ύX�̂��ߍ폜��������
<ul><li class="group">{$hs['read_range']}</li></ul>
<div id="usage" class="panel">
<div class="row"><label>
{$goto_ht}\n
</label>
</div>
</div>
*/

?></body></html><?php


// ���̃t�@�C���ł̏����͂����܂�


//==================================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//==================================================================================
/**
 * �\���ʒu���擾����
 *
 * @return  string
 */
function _getReadRange($aThread)
{
    global $_filter_range, $_filter_hits;
    
    $read_range = null;
    
    if (isset($GLOBALS['word']) && $aThread->rescount) {
        $_filter_range['end'] = min($_filter_range['to'], $_filter_hits);
        $read_range = "{$_filter_range['start']}-{$_filter_range['end']}/{$_filter_hits}hit";

    } elseif ($aThread->resrange_multi) {
        $read_range = hs($aThread->ls);

    } elseif ($aThread->resrange['start'] == $aThread->resrange['to']) {
        $read_range = $aThread->resrange['start'];

    } else {
        $read_range = "{$aThread->resrange['start']}-{$aThread->resrange['to']}";
    }
    return $read_range;
}

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
    $numonly_at = ' istyle="4" mode="numeric"'; // maxlength="7"

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
                $_conf['accesskey_for_k'] => $_conf['k_accesskey']['res']
            )
        );

    } else {
        $dores_atag = P2View::tagA(
            UriUtil::buildQueryUri(
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
                $_conf['accesskey_for_k'] => $_conf['k_accesskey']['res']
            )
        );
    }
    
    return $dores_atag;
}

/**
 * �t�B���^�[�\���t�H�[�����쐬����
 * Edit 080727 by 240
 * @return string
 */
function _createFilterForm($aThread, $res_filter)
{
    global $_conf;
    
    $headbar_htm = '';
    
    // ���X�t�B���^ form HTML

    if ($aThread->rescount and empty($_GET['renzokupop'])) {

        $selected_field = array('whole' => '', 'name' => '', 'mail' => '', 'date' => '', 'id' => '', 'msg' => '');
        $selected_field[($res_filter['field'])] = ' selected';

        $selected_match = array('on' => '', 'off' => '');
        $selected_match[($res_filter['match'])] = ' selected';
    
        // �g������
        if ($_conf['enable_exfilter']) {
            $selected_method = array('and' => '', 'or' => '', 'just' => '', 'regex' => '');
            $selected_method[($res_filter['method'])] = ' selected';
            $select_method_ht = <<<EOP
	<select id="method" name="method">
		<option value="or"{$selected_method['or']}>�����ꂩ</option>
		<option value="and"{$selected_method['and']}>���ׂ�</option>
		<option value="just"{$selected_method['just']}>���̂܂�</option>
		<option value="regex"{$selected_method['regex']}>���K�\��</option>
	</select>
EOP;
        }
    
        $word_hs = htmlspecialchars($GLOBALS['word'], ENT_QUOTES);

        $headbar_htm = <<<EOP
<form id="searchForm" name="searchForm" class="dialog_filter" action="{$_conf['read_php']}" accept-charset="{$_conf['accept_charset']}" style="white-space:nowrap">
	<fieldset>
		<select id="field" name="field">
			<option value="whole"{$selected_field['whole']}>�S��</option>
			<option value="name"{$selected_field['name']}>���O</option>
			<option value="mail"{$selected_field['mail']}>���[��</option>
			<option value="date"{$selected_field['date']}>���t</option>
			<option value="id"{$selected_field['id']}>ID</option>
			<option value="msg"{$selected_field['msg']}>ү����</option>
		</select>
		{$select_method_ht}
		<select id="match" name="match">
			<option value="on"{$selected_match['on']}>�܂�</option>
			<option value="off"{$selected_match['off']}>�܂܂Ȃ�</option>
		</select>
		<br>
		<label>Word:</label>
		<input id="word" name="word" type="text" value="">
		<br>
		<input type="submit" id="s2" name="s2" value="�t�B���^�\��" onclick="popUpFootbarFormIPhone(0, 1)"><br><br>

		<input type="hidden" name="detect_hint" value="����">
		<input type="hidden" name="bbs" value="{$aThread->bbs}">
		<input type="hidden" name="key" value="{$aThread->key}">
		<input type="hidden" name="host" value="{$aThread->host}">
		<input type="hidden" name="ls" value="all">
		<input type="hidden" name="offline" value="1">
		<input type="hidden" name="b" value="i">
	</fieldset>
</form>
EOP;
	}

	return $headbar_htm;
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
