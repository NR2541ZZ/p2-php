<?php
/*
	p2 - �T�u�W�F�N�g - �t�b�^�\��
	for subject.php
*/

$aTags = array();

// dat�q�� <a>
$datSokoATag = _getDatSokoATag($aThreadList);
$datSokoATag and $aTags[] = $datSokoATag;

// ���ځ[�񒆂̃X���b�h <a>
$tabornATag = _getTabornATag($aThreadList);
$tabornATag and $aTags[] = $tabornATag;

// �V�K�X���b�h�쐬 <a>
$buildnewthreadATag = _getBuildnewthreadATag($aThreadList);
$buildnewthreadATag and $aTags[] = $buildnewthreadATag;

//================================================================
// HTML�v�����g
//================================================================

?></table><?php

// �`�F�b�N�t�H�[��
echo $check_form_ht;

// �t�H�[���t�b�^
?>
		<input type="hidden" name="host" value="<?php eh($aThreadList->host); ?>">
		<input type="hidden" name="bbs" value="<?php eh($aThreadList->bbs); ?>">
		<input type="hidden" name="spmode" value="<?php eh($aThreadList->spmode); ?>">
	</form>
<?php
	
// sbject �c�[���o�[
include P2_LIB_DIR . '/sb_toolbar.inc.php';

?><p><?php
echo implode(' | ', $aTags);
?></p><?php

// �X�y�V�������[�h�łȂ���΃t�H�[�����͂�⊮
$ini_url_text = '';
if (!$aThreadList->spmode) {
    // �������
	if (P2Util::isHostJbbsShitaraba($aThreadList->host)) {
		$ini_url_text = "http://{$aThreadList->host}/bbs/read.cgi?BBS={$aThreadList->bbs}&KEY=";
    // �܂�BBS
	} elseif (P2Util::isHostMachiBbs($aThreadList->host)) {
		$ini_url_text = "http://{$aThreadList->host}/bbs/read.cgi?BBS={$aThreadList->bbs}&KEY=";
    // �܂��r�˂���
	} elseif (P2Util::isHostMachiBbsNet($aThreadList->host)) {
		$ini_url_text = "http://{$aThreadList->host}/test/read.cgi?bbs={$aThreadList->bbs}&key=";
	} else {
		$ini_url_text = "http://{$aThreadList->host}/test/read.cgi/{$aThreadList->bbs}/";
	}
}

// if (!$aThreadList->spmode || $aThreadList->spmode=="fav" || $aThreadList->spmode=="recent" || $aThreadList->spmode=="res_hist") {

$onClick_ht = <<<EOP
var url_v=document.forms["urlform"].elements["url_text"].value;
if (url_v=="" || url_v=="{$ini_url_text}") {
	alert("�������X���b�h��URL����͂��ĉ������B ��Fhttp://pc.2ch.net/test/read.cgi/mac/1034199997/");
	return false;
}
EOP;

echo <<<EOP
	<form id="urlform" method="GET" action="{$_conf['read_php']}" target="read">
			2ch�̃X��URL�𒼐ڎw��
			<input id="url_text" type="text" value="{$ini_url_text}" name="url" size="62">
			<input type="submit" name="btnG" value="�\��" onClick='{$onClick_ht}'>
	</form>\n
EOP;

//}

?>
</body></html>
<?php


//====================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//====================================================================
/**
 * dat�q�� <a>
 *
 * @return  string  HTML
 */
function _getDatSokoATag($aThreadList)
{
    global $_conf;
    
    $datSokoATag = '';
    // �X�y�V�������[�h�łȂ���΁A�܂��͂��ځ[�񃊃X�g�Ȃ�
    if (!$aThreadList->spmode or $aThreadList->spmode == 'taborn') {
        $datSokoATag = P2View::tagA(
            P2Util::buildQueryUri(
                $_conf['subject_php'],
                array(
                    'host'   => $aThreadList->host,
                    'bbs'    => $aThreadList->bbs,
                    'norefresh' => '1',
                    'spmode' => 'soko',
                    UA::getQueryKey() => UA::getQueryValue()
                )
            ),
            'dat�q��',
            array(
                'target' => '_self',
                'title'  => 'dat���������X��'
            )
        );
    }
    return $datSokoATag;
}

/**
 * ���ځ[�񒆂̃X���b�h <a>
 *
 * @return  string  HTML
 */
function _getTabornATag($aThreadList)
{
    global $_conf, $ta_num;
    
    $taborn_link_atag = '';
    if (!empty($ta_num)) {
        $taborn_link_atag = P2View::tagA(
            P2Util::buildQueryUri(
                $_conf['subject_php'],
                array(
                    'host'   => $aThreadList->host,
                    'bbs'    => $aThreadList->bbs,
                    'norefresh' => '1',
                    'spmode' => 'taborn',
                    UA::getQueryKey() => UA::getQueryValue()
                )
            ),
            "���ځ[�񒆂̃X���b�h ({$ta_num})",
            array(
                'target' => '_self'
            )
        );
    }
    return $taborn_link_atag;
}

/**
 * �V�K�X���b�h�쐬 <a>
 *
 * @return  string  HTML
 */
function _getBuildnewthreadATag($aThreadList)
{
    global $STYLE;
    
    $buildnewthreadATag = '';
    if (!$aThreadList->spmode and !P2Util::isHostKossoriEnq($aThreadList->host)) {
        $qs = array(
            'host'   => $aThreadList->host,
            'bbs'    => $aThreadList->bbs,
            'newthread' => '1',
            UA::getQueryKey() => UA::getQueryValue()
        );
        if (defined('SID') && strlen(SID)) {
            $qs[session_name()] = session_id();
        }
        $onClickUri = P2Util::buildQueryUri('post_form.php', array_merge($qs, array('popup' => '1')));
        $buildnewthreadATag = P2View::tagA(
            P2Util::buildQueryUri('post_form.php', $qs),
            '�V�K�X���b�h�쐬',
            array(
                'onClick' => sprintf(
                    "return !openSubWin('%s',%s,1,0)",
                    str_replace("'", "\\'", $onClickUri), $STYLE['post_pop_size']
                ),
                'target' => '_self'
            )
        );
    }
    return $buildnewthreadATag;
}
