<?php
/*
    p2 -  �ݒ�Ǘ�
*/

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/filectl.class.php';
require_once P2_LIB_DIR . '/P2View.php';

$_login->authorize(); // ���[�U�F��

// �������񂾃��X�̃��O���_�E�����[�h
if (!empty($_GET['dl_res_hist_log'])) {
    header('Content-Type: text/plain; name=' . basename($_conf['p2_res_hist_dat']));
    header("Content-Disposition: attachment; filename=" . basename($_conf['p2_res_hist_dat']));
    readfile($_conf['p2_res_hist_dat']);
    exit;
}


// �������񂾃��X�̃��O�폜
_clearResHistLogByQuery();

// {{{ �z�X�g�̓����p�ݒ�

if (!isset($rh_idx))     { $rh_idx     = $_conf['pref_dir'] . '/p2_res_hist.idx'; }
if (!isset($palace_idx)) { $palace_idx = $_conf['pref_dir'] . '/p2_palace.idx'; }

$synctitle = array(
    basename($_conf['favita_path'])  => '���C�ɔ�',
    basename($_conf['favlist_file']) => '���C�ɃX��',
    basename($_conf['rct_file'])     => '�ŋߓǂ񂾃X��',
    basename($rh_idx)                => '�������ݗ���',
    basename($palace_idx)            => '�X���̓a��'
);

// }}}
// {{{ �ݒ�ύX����

// �z�X�g�𓯊�����
if (isset($_POST['sync'])) {
    require_once P2_LIB_DIR . '/BbsMap.class.php';
    $syncfile = $_conf['pref_dir'] . '/' . $_POST['sync'];
    $sync_name = $_POST['sync'];
    if ($syncfile == $_conf['favita_path']) {
        BbsMap::syncBrd($syncfile);
    } elseif (in_array($syncfile, array($_conf['favlist_file'], $_conf['rct_file'], $rh_idx, $palace_idx))) {
        BbsMap::syncIdx($syncfile);
    }
}

$parent_reload = '';
if (isset($_GET['reload_skin'])) {
    $parent_reload = " onload=\"parent.menu.location.href='./{$_conf['menu_php']}'; parent.read.location.href='./first_cont.php';\"";
}

// }}}
// {{{ �����o���p�ϐ�

$ptitle = '�ݒ�Ǘ�';

if ($_conf['ktai']) {
    $status_st      = '�ð��';
    $autho_user_st  = '�F��հ��';
    $client_host_st = '�[��ν�';
    $client_ip_st   = '�[��IP���ڽ';
    $browser_ua_st  = '��׳��UA';
    $p2error_st     = 'p2 �װ';
} else {
    $status_st      = '�X�e�[�^�X';
    $autho_user_st  = '�F�؃��[�U';
    $client_host_st = '�[���z�X�g';
    $client_ip_st   = '�[��IP�A�h���X';
    $browser_ua_st  = '�u���E�UUA';
    $p2error_st     = 'p2 �G���[';
}

$autho_user_ht = '';

$hr = P2View::getHrHtmlK();

$edit_conf_user_atag = P2View::tagA(
    P2Util::buildQueryUri(
        'edit_conf_user.php',
        array(UA::getQueryKey() => UA::getQueryValue())
    ),
    hs('���[�U�ݒ�ҏW')
);

// }}}

//=========================================================
// HTML��\������
//=========================================================
P2Util::headerNoCache();
P2View::printDoctypeTag();
?>
<html lang="ja">
<head>
<?php
P2View::printExtraHeadersHtml();
?>
<title><?php eh($ptitle); ?></title>
<?php

if (!$_conf['ktai']) {
    P2View::printIncludeCssHtml('style');
    P2View::printIncludeCssHtml('editpref');
    ?><link rel="shortcut icon" href="favicon.ico" type="image/x-icon"><?php
}
?>
</head>
<body<?php echo P2View::getBodyAttrK(); ?><?php echo $parent_reload; ?>>
<?php

if (!$_conf['ktai']) {
//<p id="pan_menu"><a href="setting.php">�ݒ�</a> &gt; {$ptitle}</p>
    ?><p id="pan_menu"><?php eh($ptitle); ?></p><?php
}


P2Util::printInfoHtml();

$aborn_res_txt  = $_conf['pref_dir'] . '/p2_aborn_res.txt';
$aborn_name_txt = $_conf['pref_dir'] . '/p2_aborn_name.txt';
$aborn_mail_txt = $_conf['pref_dir'] . '/p2_aborn_mail.txt';
$aborn_msg_txt  = $_conf['pref_dir'] . '/p2_aborn_msg.txt';
$aborn_id_txt   = $_conf['pref_dir'] . '/p2_aborn_id.txt';
$ng_name_txt    = $_conf['pref_dir'] . '/p2_ng_name.txt';
$ng_mail_txt    = $_conf['pref_dir'] . '/p2_ng_mail.txt';
$ng_msg_txt     = $_conf['pref_dir'] . '/p2_ng_msg.txt';
$ng_id_txt      = $_conf['pref_dir'] . '/p2_ng_id.txt';

?>
<p>�@�E<?php echo $edit_conf_user_atag; ?></p>
<?php

// �g�їp�\��
if ($_conf['ktai']) {
    echo $hr;
}

// PC
if (!$_conf['ktai']) {
    
    ?><table id="editpref"><?php
    
    // {{{ PC - NG���[�h�ҏW
    
    ?><tr><td>

<fieldset>
<!-- <a href="http://akid.s17.xrea.com/p2puki/pukiwiki.php?%5B%5BNG%A5%EF%A1%BC%A5%C9%A4%CE%C0%DF%C4%EA%CA%FD%CB%A1%5D%5D" target="read">NG���[�h</a> -->
<legend>NG���[�h�ҏW</legend>
<?php
    $sepa = ' | ';
    _printEditFileHtml($ng_name_txt, "���O");
    echo $sepa;
    _printEditFileHtml($ng_mail_txt, "���[��");
    echo $sepa;
    _printEditFileHtml($ng_msg_txt, "���b�Z�[�W");
    echo $sepa;
    _printEditFileHtml($ng_id_txt, "&nbsp;ID&nbsp;");
    ?>
</fieldset>
<?php

    echo "</td>";
    
    // }}}
    // {{{ PC - ���ځ[�񃏁[�h�ҏW
    
    echo "<td>\n\n";

    echo <<<EOP
<fieldset>
<legend>���ځ[�񃏁[�h�ҏW</legend>\n
EOP;
    _printEditFileHtml($aborn_name_txt, "���O");
    echo $sepa;
    _printEditFileHtml($aborn_mail_txt, "���[��");
    echo $sepa;
    _printEditFileHtml($aborn_msg_txt, "���b�Z�[�W");
    echo $sepa;
    _printEditFileHtml($aborn_id_txt, "&nbsp;ID&nbsp;");
    echo <<<EOP
</fieldset>\n
EOP;

    echo "</td></tr>";
    
    // }}}
    // {{{ PC - ���̑� �̐ݒ�
    
    /*
    php �� editfile ���Ȃ�
    
    echo <<<EOP
<fieldset>
<legend>���̑�</legend>
EOP;
    _printEditFileHtml("conf/conf_user_style.inc.php", '�f�U�C���ݒ�');
    _printEditFileHtml("conf/conf.inc.php", '��{�ݒ�');
    echo <<<EOP
</fieldset>\n
EOP;
    */
    
    // }}}
    
    echo "</table>\n";
}

// �g�їp�\�� NG/����ܰ��
if ($_conf['ktai']) {
    $ng_name_txt_bn     = basename($ng_name_txt);
    $ng_mail_txt_bn     = basename($ng_mail_txt);
    $ng_msg_txt_bn      = basename($ng_msg_txt);
    $ng_id_txt_bn       = basename($ng_id_txt);
    $aborn_name_txt_bn  = basename($aborn_name_txt);
    $aborn_mail_txt_bn  = basename($aborn_mail_txt);
    $aborn_msg_txt_bn   = basename($aborn_msg_txt);
    $aborn_id_txt_bn    = basename($aborn_id_txt);
    echo <<<EOP
<p>NG/����ܰ�ޕҏW</p>
<form method="GET" action="edit_aborn_word.php">
{$_conf['k_input_ht']}
<select name="path">
	<option value="{$ng_name_txt_bn}">NG:���O</option>
	<option value="{$ng_mail_txt_bn}">NG:Ұ�</option>
	<option value="{$ng_msg_txt_bn}">NG:ү����</option>
	<option value="{$ng_id_txt_bn}">NG:ID</option>
	<option value="{$aborn_name_txt_bn}">����:���O</option>
	<option value="{$aborn_mail_txt_bn}">����:Ұ�</option>
	<option value="{$aborn_msg_txt_bn}">����:ү����</option>
	<option value="{$aborn_id_txt_bn}">����:ID</option>
</select>
<input type="submit" value="�ҏW">
</form>
$hr
EOP;

}

// �V���܂Ƃߓǂ݂̃L���b�V�������NHTML��\������
_printMatomeCacheLinksHtml();


// PC - �z�X�g�̓��� HTML��\�� 

if (!$_conf['ktai']) {

    $sync_htm = <<<EOP
<table><tr><td>
<fieldset>
<legend>�z�X�g�̓���</legend>
2ch�̔ړ]�ɑΉ����܂��B�ʏ�͎����ōs����̂ŁA���̑���͓��ɕK�v����܂���<br>
EOP;

    $exist_sync_flag = false;
    foreach ($synctitle as $syncpath => $syncname) {
        if (is_writable($_conf['pref_dir'] . '/' . $syncpath)) {
            $exist_sync_flag = true;
            $sync_htm .= _getSyncFavoritesFormHtml($syncpath, $syncname);
        }
    }

    $sync_htm .= <<<EOP
</fieldset>
</td></tr></table>\n
EOP;

    if ($exist_sync_flag) {
        echo $sync_htm;
    } else {
        echo "&nbsp;";
        // echo "<p>�z�X�g�̓����͕K�v����܂���</p>";
    }

// �g�їp�\��
} else {
    $sync_htm = "<p>νĂ̓���<br>�i2ch�̔ړ]�ɑΉ����܂��B�ʏ�͎����ōs����̂ŁA���̑���͓��ɕK�v����܂���j</p>\n";
    $exist_sync_flag = false;
    foreach ($synctitle as $syncpath => $syncname) {
        if (is_writable($_conf['pref_dir'] . '/' . $syncpath)) {
            $exist_sync_flag = true;
            $sync_htm .= _getSyncFavoritesFormHtml($syncpath, $syncname);
        }
    }
    
    if ($exist_sync_flag) {
        echo $sync_htm;
    } else {
        // echo "<p>νĂ̓����͕K�v����܂���</p>";
    }
}

if (!$_conf['ktai']) {
    $dl_res_hist_log_atag = P2View::tagA(
        P2Util::buildQueryUri(
            basename($_SERVER['SCRIPT_NAME']),
            array(
                'dl_res_hist_log' => 1,
                UA::getQueryKey() => UA::getQueryValue()
            )
        ),
        hs('�������񂾃��X�̐����O���ꊇ�_�E�����[�h����')
    );
?>
<p>[<?php echo $dl_res_hist_log_atag; ?>]</p>
<?php
}

if ($_conf['ktai']) {
    echo $hr;
}

$clear_res_hist_log_atag = P2View::tagA(
    P2Util::buildQueryUri(
        basename($_SERVER['SCRIPT_NAME']),
        array(
            'clear_res_hist_log' => 1,
            UA::getQueryKey() => UA::getQueryValue()
        )
    ),
    hs('�������񂾃��X�̃��O��S�č폜����')
);
?>
<p>[<?php echo $clear_res_hist_log_atag; ?>]</p>
<?php

// �g�їp�t�b�^HTML
if (UA::isK()) {
    echo $hr . "\n";
    echo P2View::getBackToIndexKATag() . "\n";
}

?></body></html><?php


exit;


//==================================================================================
// �֐��i���̃t�@�C�����݂̂ŗ��p�j
//==================================================================================

/**
 * �������񂾃��X�̍폜
 */
function _clearResHistLogByQuery()
{
    if (!empty($_GET['clear_res_hist_log'])) {
        /*
        $atag = P2View::tagA(
            P2Util::buildQueryUri(
                basename($_SERVER['SCRIPT_NAME']),
                array(
                    'do_clear_res_hist_log' => 1,
                    'csrfid' => P2Util::getCsrfId(),
                    UA::getQueryKey() => UA::getQueryValue()
                )
            ),
            hs('�͂��A�폜���܂�')
        );
        */
        P2Util::pushInfoHtml(
            sprintf(
                '<form method="POST" action="%s">
                 <input type="hidden" name="do_clear_res_hist_log" value="1">
                 <input type="hidden" name="csrfid" value="%s">
                 <input type="hidden" name="%s" value="%s">
                 �m�F�F�{���ɏ������񂾃��X�̃��O��S�č폜���Ă��A��낵���ł����H
                 <input type="submit" name="submit" value="�͂��A�폜���܂�">
                 </form>',
                hs(basename($_SERVER['SCRIPT_NAME'])),
                hs(P2Util::getCsrfId()),
                hs(UA::getQueryKey()), hs(UA::getQueryValue())
            )
        );

    } elseif (!empty($_POST['do_clear_res_hist_log'])) {
        if (!isset($_POST['csrfid']) or $_POST['csrfid'] != P2Util::getCsrfId()) {
            P2Util::pushInfoHtml('p2 error: �s���ȃN�G���[�ł��iCSRF�΍�j');
        } else {
            require_once P2_LIB_DIR . '/' . 'read_res_hist.inc.php';
            if (deleteResHistDat()) {
                P2Util::pushInfoHtml('<p>p2 info: ���������񂾃��X�̃��O���폜���܂���</p>');
            } else {
                P2Util::pushInfoHtml('<p>p2 error: �~�������񂾃��X�̃��O���폜�ł��܂���ł���</p>');
            }
        }
    }
}

/**
 * �ݒ�t�@�C���ҏW�E�C���h�E���J��HTML��\������
 *
 * @return  void
 */
function _printEditFileHtml($path_value, $submit_value)
{
    global $_conf;
    
    // �A�N�e�B�u
    if ((file_exists($path_value) && is_writable($path_value)) || (!file_exists($path_value) && is_writable(dirname($path_value)))) {
        $onsubmit = '';
        $disabled = '';
    
    // ��A�N�e�B�u
    } else {
        $onsubmit = ' onsubmit="return false;"';
        $disabled = ' disabled';
    }
    
    $rows = 36; // 18
    $cols = 92; // 90

    // edit_aborn_word.php
    if (preg_match('/^p2_(aborn|ng)_(name|mail|id|msg)\.txt$/', basename($path_value))) {
        $edit_php = 'edit_aborn_word.php';
        $target = '_self';
        $path_value = basename($path_value);
        
        $q_ar = array(
            'path'      => $path_value,
            UA::getQueryKey() => UA::getQueryValue()
        );
        $url = $edit_php . '?' . P2Util::buildQuery($q_ar);
        $html = P2View::tagA($url, $submit_value) . "\n";
    
    // editfile.php
    } else {
        $edit_php = 'editfile.php';
        $target = 'editfile';
        
        $html = <<<EOFORM
<form action="{$edit_php}" method="POST" target="{$target}" class="inline-form"{$onsubmit}>
	{$_conf['k_input_ht']}
	<input type="hidden" name="path" value="{$path_value}">
	<input type="hidden" name="encode" value="Shift_JIS">
	<input type="hidden" name="rows" value="{$rows}">
	<input type="hidden" name="cols" value="{$cols}">
	<input type="submit" value="{$submit_value}"{$disabled}>
</form>\n
EOFORM;
        // IE�p��form���̃^�O�Ԃ̋󔒂������@����
        if (strstr(geti($_SERVER['HTTP_USER_AGENT']), 'MSIE')) {
            $html = '&nbsp;' . preg_replace('{>\s+<}', '><', $html);
        }
    }
    
    echo $html;
}

/**
 * �z�X�g�̓����p�t�H�[����HTML���擾����
 *
 * @return  string
 */
function _getSyncFavoritesFormHtml($path_value, $submit_value)
{
    global $_conf;
    
    $ht = <<<EOFORM
<form action="editpref.php" method="POST" target="_self" class="inline-form">
    {$_conf['k_input_ht']}
    <input type="hidden" name="sync" value="{$path_value}">
    <input type="submit" value="{$submit_value}">
</form>

EOFORM;

    if (strstr(geti($_SERVER['HTTP_USER_AGENT']), 'MSIE')) {
        $ht = '&nbsp;' . preg_replace('/>\s+</', '><', $ht);
    }
    return $ht;
}

/**
 * �V���܂Ƃߓǂ݂̃L���b�V�������NHTML��\������
 *
 * @return  void
 */
function _printMatomeCacheLinksHtml()
{
    global $_conf;
    
    $max = $_conf['matome_cache_max'];
    $links = array();
    for ($i = 0; $i <= $max; $i++) {
        $dnum = $i ? '.' . $i : '';
        $file = $_conf['matome_cache_path'] . $dnum . $_conf['matome_cache_ext'];
        //echo '<!-- ' . $file . ' -->';
        if (file_exists($file)) {
            $filemtime = filemtime($file);
            $date = date('Y/m/d G:i:s', $filemtime);
            $b = filesize($file) / 1024;
            $kb = round($b, 0);
            $atag = P2View::tagA(
                P2Util::buildQueryUri('read_new.php', array('cview' => '1', 'cnum' => "$i", 'filemtime' => $filemtime)),
                hs($date),
                array('target' => 'read')
            );
            $links[] = sprintf('%s %dKB', $atag, $kb);
        }
    }
    if ($links) {
        echo '<p>�V���܂Ƃߓǂ݂̑O��L���b�V����\��<br>' . implode('<br>', $links) . '</p>' . "\n";
        
        if ($_conf['ktai']) {
            $hr = P2View::getHrHtmlK();
            echo $hr . "\n";
        }
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
