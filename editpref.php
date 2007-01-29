<?php
/*
    p2 -  �ݒ�Ǘ�
*/

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/filectl.class.php';

$_login->authorize(); // ���[�U�F��

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

// }}}
// {{{ �����o���p�ϐ�

$ptitle = '�ݒ�Ǘ�';

if ($_conf['ktai']) {
    $status_st      = '�ð��';
    $autho_user_st  = '�F��հ��';
    $client_host_st = '�[��ν�';
    $client_ip_st   = '�[��IP���ڽ';
    $browser_ua_st  = '��׳��UA';
    $p2error_st     = 'rep2 �װ';
} else {
    $status_st      = '�X�e�[�^�X';
    $autho_user_st  = '�F�؃��[�U';
    $client_host_st = '�[���z�X�g';
    $client_ip_st   = '�[��IP�A�h���X';
    $browser_ua_st  = '�u���E�UUA';
    $p2error_st     = 'rep2 �G���[';
}

$autho_user_ht = '';

// }}}

//=========================================================
// HTML��\������
//=========================================================
P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>{$ptitle}</title>\n
EOP;

if (!$_conf['ktai']) {
    include_once './style/style_css.inc';
    include_once './style/editpref_css.inc';
}
echo <<<EOP
</head>
<body>\n
EOP;

if (!$_conf['ktai']) {
//<p id="pan_menu"><a href="setting.php">�ݒ�</a> &gt; {$ptitle}</p>
    echo "<p id=\"pan_menu\">{$ptitle}</p>\n";
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

echo <<<EOP
<p>�@<a href="edit_conf_user.php{$_conf['k_at_q']}">���[�U�ݒ�ҏW</a></p>
EOP;

// �g�їp�\��
if ($_conf['ktai']) {
    echo '<hr>';
}

// PC
if (empty($_conf['ktai'])) {
    
    echo "<table id=\"editpref\">\n";
    
    // {{{ PC - NG���[�h�ҏW
    echo "<tr><td>\n\n";
    
    echo <<<EOP
<fieldset>
<legend><a href="http://akid.s17.xrea.com/p2puki/pukiwiki.php?%5B%5BNG%A5%EF%A1%BC%A5%C9%A4%CE%C0%DF%C4%EA%CA%FD%CB%A1%5D%5D" target="read">NG���[�h</a>�ҏW</legend>
EOP;
    printEditFileForm($ng_name_txt, "���O");
    printEditFileForm($ng_mail_txt, "���[��");
    printEditFileForm($ng_msg_txt, "���b�Z�[�W");
    printEditFileForm($ng_id_txt, " I D ");
    echo <<<EOP
</fieldset>\n\n
EOP;

    echo "</td>";
    
    // }}}
    // {{{ PC - ���ځ[�񃏁[�h�ҏW
    
    echo "<td>\n\n";

    echo <<<EOP
<fieldset>
<legend>���ځ[�񃏁[�h�ҏW</legend>\n
EOP;
    printEditFileForm($aborn_name_txt, "���O");
    printEditFileForm($aborn_mail_txt, "���[��");
    printEditFileForm($aborn_msg_txt, "���b�Z�[�W");
    printEditFileForm($aborn_id_txt, " I D ");
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
    printEditFileForm("conf/conf_user_style.inc.php", '�f�U�C���ݒ�');
    printEditFileForm("conf/conf.inc.php", '��{�ݒ�');
    echo <<<EOP
</fieldset>\n
EOP;
    */
    
    // }}}
    
    echo "</table>\n";
}


// �V���܂Ƃߓǂ݂̃L���b�V�������NHTML��\������
printMatomeCacheLinksHtml();


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
            $sync_htm .= getSyncFavoritesFormHt($syncpath, $syncname);
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
            $sync_htm .= getSyncFavoritesFormHt($syncpath, $syncname);
        }
    }
    
    if ($exist_sync_flag) {
        echo $sync_htm;
    } else {
        // echo "<p>νĂ̓����͕K�v����܂���</p>";
    }
}


// �g�їp�t�b�^HTML
if ($_conf['ktai']) {
    echo "<hr>\n";
    echo $_conf['k_to_index_ht'] . "\n";
}

echo '</body></html>';


exit;


//==============================================================================
// �֐�
//==============================================================================
/**
 * �ݒ�t�@�C���ҏW�E�C���h�E���J���t�H�[��HTML��\������
 *
 * @return  void
 */
function printEditFileForm($path_value, $submit_value)
{
    global $_conf;
    
    if ((file_exists($path_value) && is_writable($path_value)) ||
        (!file_exists($path_value) && is_writable(dirname($path_value)))
    ) {
        $onsubmit = '';
        $disabled = '';
    } else {
        $onsubmit = ' onsubmit="return false;"';
        $disabled = ' disabled';
    }
    
    $rows = 36; // 18
    $cols = 92; // 90
    
    $ht = <<<EOFORM
<form action="editfile.php" method="POST" target="editfile" class="inline-form"{$onsubmit}>
    {$_conf['k_input_ht']}
    <input type="hidden" name="path" value="{$path_value}">
    <input type="hidden" name="encode" value="Shift_JIS">
    <input type="hidden" name="rows" value="{$rows}">
    <input type="hidden" name="cols" value="{$cols}">
    <input type="submit" value="{$submit_value}"{$disabled}>
</form>\n
EOFORM;

    if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
        $ht = '&nbsp;' . preg_replace('/>\s+</', '><', $ht);
    }
    echo $ht;
}

/**
 * �z�X�g�̓����p�t�H�[����HTML���擾����
 *
 * @return  string
 */
function getSyncFavoritesFormHt($path_value, $submit_value)
{
    global $_conf;
    
    $ht = <<<EOFORM
<form action="editpref.php" method="POST" target="_self" class="inline-form">
    {$_conf['k_input_ht']}
    <input type="hidden" name="sync" value="{$path_value}">
    <input type="submit" value="{$submit_value}">
</form>

EOFORM;

    if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
        $ht = '&nbsp;' . preg_replace('/>\s+</', '><', $ht);
    }
    return $ht;
}

/**
 * �V���܂Ƃߓǂ݂̃L���b�V�������NHTML��\������
 *
 * @return  void
 */
function printMatomeCacheLinksHtml()
{
    global $_conf;
    
    $max = $_conf['matome_cache_max'];
    $links = array();
    for ($i = 0; $i <= $max; $i++) {
        $dnum = $i ? '.' . $i : '';
        $ai = '&amp;cnum=' . $i;
        $file = $_conf['matome_cache_path'] . $dnum . $_conf['matome_cache_ext'];
        //echo '<!-- ' . $file . ' -->';
        if (file_exists($file)) {
            $filemtime = filemtime($file);
            $date = date('Y/m/d G:i:s', $filemtime);
            $b = filesize($file) / 1024;
            $kb = round($b, 0);
            $url = 'read_new.php?cview=1' . $ai . '&amp;filemtime=' . $filemtime;
            $links[] = '<a href="' . $url . '" target="read">' . $date . '</a> ' . $kb . 'KB';
        }
    }
    if ($links) {
        echo '<p>�V���܂Ƃߓǂ݂̑O��L���b�V����\��<br>' . implode('<br>', $links) . '</p>';
        
        if ($_conf['ktai']) {
            echo '<hr>' . "\n";
        }
    }
}

