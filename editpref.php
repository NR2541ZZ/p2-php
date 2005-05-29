<?php
/*
    p2 -  �ݒ�Ǘ�
*/

include_once './conf/conf.inc.php';  // ��{�ݒ�
require_once (P2_LIBRARY_DIR . '/filectl.class.php');

authorize(); // ���[�U�F��

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

// �z�X�g�̓���
if (isset($_POST['sync'])) {
    $syncfile = $_conf['pref_dir'].'/'.$_POST['sync'];
    $sync_name = $_POST['sync'];
    if ($syncfile == $_conf['favita_path']) {
        include_once (P2_LIBRARY_DIR . '/syncfavita.inc.php');
    } elseif (in_array($syncfile, array($_conf['favlist_file'], $_conf['rct_file'], $rh_idx, $palace_idx))) {
        include_once (P2_LIBRARY_DIR . '/syncindex.inc.php');
    }
    if ($sync_ok) {
        $_info_msg_ht .= "<p>{$synctitle[$sync_name]}�𓯊����܂����B</p>";
    } else {
        $_info_msg_ht .= "<p>{$synctitle[$sync_name]}�͕ύX����܂���ł����B</p>";
    }
    unset($syncfile);
}

// }}}
// {{{ �����o���p�ϐ�

$ptitle = '�ݒ�Ǘ�';

if ($_conf['ktai']) {
    $status_st = '�ð��';
    $autho_user_st = '�F��հ��';
    $client_host_st = '�[��ν�';
    $client_ip_st = '�[��IP���ڽ';
    $browser_ua_st = '��׳��UA';
    $p2error_st = 'p2 �װ';
} else {
    $status_st = '�X�e�[�^�X';
    $autho_user_st = '�F�؃��[�U';
    $client_host_st = '�[���z�X�g';
    $client_ip_st = '�[��IP�A�h���X';
    $browser_ua_st = '�u���E�UUA';
    $p2error_st = 'p2 �G���[';
}

$autho_user_ht = '';

// }}}

//=========================================================
// HTML�v�����g
//=========================================================
P2Util::header_nocache();
P2Util::header_content_type();
if ($_conf['doctype']) {
    echo $_conf['doctype'];
}
echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>{$ptitle}</title>\n
EOP;
if(!$_conf['ktai']){
    @include("./style/style_css.inc");
    @include("./style/editpref_css.inc");
}
echo <<<EOP
</head>
<body>\n
EOP;

if (empty($_conf['ktai'])) {
//<p id="pan_menu"><a href="setting.php">�ݒ�</a> &gt; {$ptitle}</p>
    echo "<p id=\"pan_menu\">{$ptitle}</p>\n";
}


echo $_info_msg_ht;
$_info_msg_ht = '';

// �ݒ�v�����g =====================
$aborn_res_txt  = $_conf['pref_dir'] . '/p2_aborn_res.txt';
$aborn_name_txt = $_conf['pref_dir'] . '/p2_aborn_name.txt';
$aborn_mail_txt = $_conf['pref_dir'] . '/p2_aborn_mail.txt';
$aborn_msg_txt  = $_conf['pref_dir'] . '/p2_aborn_msg.txt';
$aborn_id_txt   = $_conf['pref_dir'] . '/p2_aborn_id.txt';
$ng_name_txt    = $_conf['pref_dir'] . '/p2_ng_name.txt';
$ng_mail_txt    = $_conf['pref_dir'] . '/p2_ng_mail.txt';
$ng_msg_txt     = $_conf['pref_dir'] . '/p2_ng_msg.txt';
$ng_id_txt      = $_conf['pref_dir'] . '/p2_ng_id.txt';

if (empty($_conf['ktai'])) {
    
    echo "<table id=\"editpref\">\n";
    
    // {{{ PC - NG���[�h�ҏW
    echo "<tr><td>\n\n";
    
    echo <<<EOP
<fieldset>
<legend><a href="http://akid.s17.xrea.com:8080/p2puki/pukiwiki.php?%5B%5BNG%A5%EF%A1%BC%A5%C9%A4%CE%C0%DF%C4%EA%CA%FD%CB%A1%5D%5D" target="read">NG���[�h</a>�ҏW</legend>
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
<legend>���ځ[�񃏁[�h�ҏW</legend>
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
    echo "<td>\n\n";

    echo <<<EOP
<fieldset>
<legend>���̑�</legend>
EOP;
    printEditFileForm("conf/conf_user.inc.php", '���[�U�ݒ�');
    printEditFileForm("conf/conf_user_style.inc.php", '�f�U�C���ݒ�');
    printEditFileForm("conf/conf.inc.php", '��{�ݒ�');
    echo <<<EOP
</fieldset>\n
EOP;
    // }}}

    echo "</td></tr>\n\n";
    $htm['sync'] = "<tr><td colspan=\"2\">\n\n";

    // {{{ PC - �z�X�g�̓��� HTML�̃Z�b�g
    $htm['sync'] .= <<<EOP
<fieldset>
<legend>�z�X�g�̓��� �i2ch�̔ړ]�ɑΉ����܂��j</legend>
EOP;
    $exist_sync_flag = false;
    foreach ($synctitle as $syncpath => $syncname) {
        if (is_writable($_conf['pref_dir'].'/'.$syncpath)) {
            $exist_sync_flag = true;
            $htm['sync'] .= getSyncFavoritesFormHt($syncpath, $syncname);
        }
    }
    $htm['sync'] .= <<<EOP
</fieldset>\n
EOP;

    $htm['sync'] .= "</td></tr>\n\n";

    if ($exist_sync_flag) {
        echo $htm['sync'];
    } else {
        echo "&nbsp;";
        // echo "<p>�z�X�g�̓����͕K�v����܂���</p>";
    }
    // }}}
    
    echo "</table>\n";
}

// �g�їp�\��
if ($_conf['ktai']) {
    $htm['sync'] .= "<p>νĂ̓����i2ch�̔ړ]�ɑΉ����܂��j</p>\n";
    $exist_sync_flag = false;
    foreach ($synctitle as $syncpath => $syncname) {
        if (is_writable($_conf['pref_dir'].'/'.$syncpath)) {
            $exist_sync_flag = true;
            $htm['sync'] .= getSyncFavoritesFormHt($syncpath, $syncname);
        }
    }
    
    if ($exist_sync_flag) {
        echo $htm['sync'];
    } else {
        // echo "<p>νĂ̓����͕K�v����܂���</p>";
    }
}

// {{{ �V���܂Ƃߓǂ݂̃L���b�V���\��
$max = $_conf['matome_cache_max'];
for ($i = 0; $i <= $max; $i++) {
    $dnum = ($i) ? '.'.$i : '';
    $ai = '&amp;cnum='.$i;
    $file = $_conf['matome_cache_path'].$dnum.$_conf['matome_cache_ext'];
    //echo '<!-- '.$file.' -->';
    if (file_exists($file)) {
        $date = date('Y/m/d G:i:s', filemtime($file));
        $b = filesize($file)/1024;
        $kb = round($b, 0);
        $url = 'read_new.php?cview=1'.$ai;
        $links[] = '<a href="'.$url.'" target="read">'.$date.'</a> '.$kb.'KB';
    }
}
if (!empty($links)) {
    if ($_conf['ktai']) {
        echo '<hr>'."\n";
    }
    echo $htm['matome'] = '<p>�V���܂Ƃߓǂ݂̑O��L���b�V����\��<br>' . implode('<br>', $links) . '</p>';
}
// }}}

// �g�їp�t�b�^
if ($_conf['ktai']) {
    echo "<hr>\n";
    echo $_conf['k_to_index_ht']."\n";
}

echo '</body></html>';

//=====================================================
// �֐�
//=====================================================
/**
 * �ݒ�t�@�C���ҏW�E�C���h�E���J���t�H�[�����v�����g����
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
 */
function getSyncFavoritesFormHt($path_value, $submit_value)
{
    global $_conf;
    
    $ht = <<<EOFORM
<form action="editpref.php" method="POST" target="_self" class="inline-form">
    {$_conf['k_input_ht']}
    <input type="hidden" name="sync" value="{$path_value}">
    <input type="submit" value="{$submit_value}">
</form>\n
EOFORM;

    if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
        $ht = '&nbsp;' . preg_replace('/>\s+</', '><', $ht);
    }
    return $ht;
}

?>
