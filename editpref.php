<?php
/*
    p2 -  �ݒ�Ǘ�
*/

include_once './conf/conf.inc.php';
include_once P2_LIBRARY_DIR . '/filectl.class.php';

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

if ($_conf['expack.favset.enabled'] &&
    ($_conf['favlist_set_num'] > 0 || $_conf['favita_set_num'] > 0 || $_conf['expack.rss.set_num'] > 0))
{
    $multi_favs = true;
} else {
    $multi_favs = false;
}

// �z�X�g�̓���
if (isset($_POST['sync'])) {
    include_once P2_LIBRARY_DIR . '/BbsMap.class.php';
    $syncfile = $_conf['pref_dir'].'/'.$_POST['sync'];
    $sync_name = $_POST['sync'];
    if ($syncfile == $_conf['favita_path']) {
        BbsMap::syncBrd($syncfile);
    } elseif (in_array($syncfile, array($_conf['favlist_file'], $_conf['rct_file'], $rh_idx, $palace_idx))) {
        BbsMap::syncIdx($syncfile);
    }
    unset($syncfile);

// ���C�ɓ���Z�b�g�ύX������΁A�ݒ�t�@�C��������������
} elseif ($multi_favs && isset($_POST['favsetlist'])) {
    updateFavSetList();
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
// HTML�v�����g
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
    echo <<<EOP
    <link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
    <link rel="stylesheet" href="css.php?css=editpref&amp;skin={$skin_en}" type="text/css">\n
EOP;
}

$body_at = ($_conf['ktai']) ? $_conf['k_colors'] : ' onLoad="top.document.title=self.document.title;"';
echo <<<EOP
</head>
<body{$body_at}>\n
EOP;

if (!$_conf['ktai']) {
//<p id="pan_menu"><a href="setting.php">�ݒ�</a> &gt; {$ptitle}</p>
    echo "<p id=\"pan_menu\">{$ptitle}</p>\n";
}


P2Util::printInfoHtml();

// �ݒ�v�����g
$aborn_res_txt  = $_conf['pref_dir'] . '/p2_aborn_res.txt';
$aborn_name_txt = $_conf['pref_dir'] . '/p2_aborn_name.txt';
$aborn_mail_txt = $_conf['pref_dir'] . '/p2_aborn_mail.txt';
$aborn_msg_txt  = $_conf['pref_dir'] . '/p2_aborn_msg.txt';
$aborn_id_txt   = $_conf['pref_dir'] . '/p2_aborn_id.txt';
$ng_name_txt    = $_conf['pref_dir'] . '/p2_ng_name.txt';
$ng_mail_txt    = $_conf['pref_dir'] . '/p2_ng_mail.txt';
$ng_msg_txt     = $_conf['pref_dir'] . '/p2_ng_msg.txt';
$ng_id_txt      = $_conf['pref_dir'] . '/p2_ng_id.txt';

echo '<div>';
echo <<<EOP
<a href="edit_conf_user.php{$_conf['k_at_q']}">���[�U�ݒ�ҏW</a>
EOP;
if (empty($_conf['ktai']) && $_conf['expack.skin.enabled']) {
    $skin_options = array('conf_user_style' => '�W��');
    $skin_dir = opendir('./skin');
    if ($skin_dir) {
        while (($skin_file = readdir($skin_dir)) !== false) {
            if (is_file("./skin/{$skin_file}") && preg_match('/^(\w+)\.php$/', $skin_file, $skin_matches)) {
                $_name = $skin_matches[1];
                $skin_options[$_name] = $_name;
            }
        }
    }
    $skin_options_ht = '';
    foreach ($skin_options as $_name => $_title) {
        if ($_name == $skin_name) {
            $_format = '<option value="%s" selected>%s</option>';
        } else {
            $_format = '<option value="%s">%s</option>';
        }
        $skin_options_ht .= sprintf($_format, htmlspecialchars($_name, ENT_QUOTES), htmlspecialchars($_title, ENT_QUOTES));
    }
    echo <<<EOP
 �b <a href="edit_user_font.php">�t�H���g�ݒ�ҏW</a>
 �b �X�L��:<form class="inline-form" method="get" action="{$_SERVER['SCRIPT_NAME']}">
<select name="skin">{$skin_options_ht}</select><input type="submit" value="�ύX">
</form>
EOP;
}
echo '</div>';

// PC�p�\��
if (!$_conf['ktai']) {

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
    printEditFileForm($ng_id_txt, "�h�c");
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
    printEditFileForm($aborn_res_txt, "���X");
    printEditFileForm($aborn_name_txt, "���O");
    printEditFileForm($aborn_mail_txt, "���[��");
    printEditFileForm($aborn_msg_txt, "���b�Z�[�W");
    printEditFileForm($aborn_id_txt, "�h�c");
    echo <<<EOP
</fieldset>\n
EOP;

    echo "</td></tr>";

    // }}}
    // {{{ PC - ���̑� �̐ݒ�

    //echo "<td>\n\n";
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

    //echo '&nbsp;';

    //echo "</td></tr>\n\n";
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
    // {{{ PC - �Z�b�g�؂�ւ��E���̕ύX

    if ($multi_favs) {
        echo "<tr><td colspan=\"2\">\n\n";

        echo <<<EOP
<form action="editpref.php" method="post" accept-charset="{$_conf['accept_charset']}" target="_self" style="margin:0">
    {$_conf['detect_hint_input_ht']}
    <input type="hidden" name="favsetlist" value="1">
    <fieldset>
        <legend>�Z�b�g�؂�ւ��E���̕ύX�i�Z�b�g������ɂ���ƃf�t�H���g�̖��O�ɖ߂�܂��j</legend>
        <table>
            <tr>\n
EOP;
        if ($_conf['favlist_set_num'] > 0) {
            echo "<td>\n";
            echo getFavSetListFormHt('m_favlist_set', '���C�ɃX��');
            echo "</td>\n";
        }
        if ($_conf['favita_set_num'] > 0) {
            echo "<td>\n";
            echo getFavSetListFormHt('m_favita_set', '���C�ɔ�');
            echo "</td>\n";
        }
        if ($_conf['expack.rss.set_num'] > 0) {
            echo "<td>\n";
            echo getFavSetListFormHt('m_rss_set', 'RSS');
            echo "</td>\n";
        }
        echo <<<EOP
            </tr>
        </table>
        <div>
            <input type="submit" value="�ύX">
        </div>
    </fieldset>
    {$_conf['k_input_ht']}
</form>\n\n
EOP;

        echo "</td></tr>\n\n";
    }

    // }}}

    echo "</table>\n";
}

// �g�їp�\��
if ($_conf['ktai']) {
    echo <<<EOP
<p>����/NGܰ�ޕҏW</p>
<form method="GET" action="edit_aborn_word.php">
{$_conf['k_input_ht']}
<select name="path">
<option value="{$aborn_name_txt}">����:���O</option>
<option value="{$aborn_mail_txt}">����:Ұ�</option>
<option value="{$aborn_msg_txt}">����:ү����</option>
<option value="{$aborn_id_txt}">����:ID</option>
<option value="{$ng_name_txt}">NG:���O</option>
<option value="{$ng_mail_txt}">NG:Ұ�</option>
<option value="{$ng_msg_txt}">NG:ү����</option>
<option value="{$ng_id_txt}">NG:ID</option>
</select>
<input type="submit" value="�ҏW">
</form>
<form method="GET" action="editfile.php">
{$_conf['k_input_ht']}
<input type="hidden" name="path" value="{$aborn_res_txt}">
<input type="submit" value="����ڽ�ҏW">
</form>
EOP;
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

    // {{{ �g�� - �Z�b�g�؂�ւ�

    if ($multi_favs) {
        echo <<<EOP
<hr>
<p>���C�ɽڥ���C�ɔ¥RSS�̾�Ă�I��</p>
<form action="editpref.php" method="post" accept-charset="{$_conf['accept_charset']}" target="_self">
{$_conf['k_input_ht']}
EOP;
        if ($_conf['favlist_set_num'] > 0) {
            echo getFavSetListFormHtK('m_favlist_set', '���C�ɽ�'), '<br>';
        }
        if ($_conf['favita_set_num'] > 0) {
            echo getFavSetListFormHtK('m_favita_set', '���C�ɔ�'), '<br>';
        }
        if ($_conf['expack.rss.set_num'] > 0) {
            echo getFavSetListFormHtK('m_rss_set', 'RSS'), '<br>';
        }
        echo <<<EOP
<input type="submit" value="�ύX">
</form>
EOP;
    }

    // }}}

}

// {{{ �V���܂Ƃߓǂ݂̃L���b�V���\��

$max = $_conf['matome_cache_max'];

if ($_conf['ktai']) {
    $ext = '.k' . $_conf['matome_cache_ext'];
} else {
    $ext = $_conf['matome_cache_ext'];
}

for ($i = 0; $i <= $max; $i++) {
    $dnum = ($i) ? '.'.$i : '';
    $ai = '&amp;cnum=' . $i;
    $file = $_conf['matome_cache_path'] . $dnum . $ext;
    //echo '<!-- '.$file.' -->';
    if (file_exists($file)) {
        $filemtime = filemtime($file);
        $date = date('Y/m/d G:i:s', $filemtime);
        $b = filesize($file)/1024;
        $kb = round($b, 0);
        $url = 'read_new.php?cview=1' . $ai . '&amp;filemtime=' . $filemtime . $_conf['k_at_a'];
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
    echo $_conf['k_to_index_ht'] . "\n";
}

echo '</body></html>';

exit;

//==============================================================================
// �֐�
//==============================================================================
/**
 * �ݒ�t�@�C���ҏW�E�C���h�E���J���t�H�[��HTML���v�����g����
 *
 * @param   string  $path_value     �ҏW����t�@�C���̃p�X
 * @param   string  $submit_value   submit�{�^���̒l
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

    if (preg_match('/^p2_(aborn|ng)_(name|mail|id|msg)\.txt$/', basename($path_value))) {
        $edit_php = 'edit_aborn_word.php';
        $target = '_self';
    } else {
        $edit_php = 'editfile.php';
        $target = 'editfile';
    }

    $ht = <<<EOFORM
<form action="{$edit_php}" method="GET" target="{$target}" class="inline-form"{$onsubmit}>
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
 * @param   string  $path_value     ��������t�@�C���̃p�X
 * @param   string  $submit_value   submit�{�^���̒l
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
</form>\n
EOFORM;

    if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
        $ht = '&nbsp;' . preg_replace('/>\s+</', '><', $ht);
    }
    return $ht;
}

/**
 * ���C�ɓ���Z�b�g�؂�ւ��E�Z�b�g���ύX�p�t�H�[����HTML���擾����iPC�p�j
 *
 * @param   string  $set_name   ���������p�Z�b�g��
 * @param   string  $set_title  HTML�\���p�Z�b�g��
 * @return  string
 */
function getFavSetListFormHt($set_name, $set_title)
{
    global $_conf;

    $setdefs = FavSetManager::getSetDefinition();
    if (!isset($setdefs[$set_name])) {
        return '';
    }
    $num_conf_key = $setdefs[$set_name][0];
    if ($_conf[$num_conf_key] == 0) {
        return '';
    }

    if (!($titles = FavSetManager::getFavSetTitles($set_name))) {
        $titles = array();
    }

    $radio_checked = array_fill(0, $_conf[$num_conf_key] + 1, '');
    $i = (isset($_SESSION[$set_name])) ? (int)$_SESSION[$set_name] : 0;
    $radio_checked[$i] = ' checked';
    $ht = <<<EOFORM
<fieldset>
    <legend>{$set_title}</legend>\n
EOFORM;
    for ($j = 0; $j <= $_conf[$num_conf_key]; $j++) {
        if (!isset($titles[$j]) || strlen($titles[$j]) == 0) {
            $titles[$j] = ($j == 0) ? $set_title : $set_title . $j;
        }
        $ht .= <<<EOFORM
    <input type="radio" name="{$set_name}" value="{$j}"{$radio_checked[$j]}>
    <input type="text" name="{$set_name}_titles[{$j}]" size="18" value="{$titles[$j]}">
    <br>\n
EOFORM;
    }
    $ht .= <<<EOFORM
</fieldset>\n
EOFORM;

    return $ht;
}

/**
 * ���C�ɓ���Z�b�g�؂�ւ��p�t�H�[����HTML���擾����i�g�їp�j
 *
 * @param   string  $set_name   ���������p�Z�b�g��
 * @param   string  $set_title  HTML�\���p�Z�b�g��
 * @return  string
 */
function getFavSetListFormHtK($set_name, $set_title)
{
    global $_conf;

    $setdefs = FavSetManager::getSetDefinition();
    if (!isset($setdefs[$set_name])) {
        return '';
    }
    $num_conf_key = $setdefs[$set_name][0];
    if ($_conf[$num_conf_key] == 0) {
        return '';
    }

    if (!($titles = FavSetManager::getFavSetTitles($set_name))) {
        $titles = array();
    }

    $selected = array_fill(0, $_conf[$num_conf_key] + 1, '');
    $i = (isset($_SESSION[$set_name])) ? (int)$_SESSION[$set_name] : 0;
    $selected[$i] = ' selected';
    $ht = "<select name=\"{$set_name}\">";
    for ($j = 0; $j <= $_conf[$num_conf_key]; $j++) {
        if ($j == 0) {
            if (!isset($titles[$j]) || strlen($titles[$j]) == 0) {
                $titles[$j] = $set_title;
            }
            $titles[$j] .= ' (��̫��)';
        } else {
            if (!isset($titles[$j]) || strlen($titles[$j]) == 0) {
                $titles[$j] = $set_title . $j;
            }
        }
        if (!empty($_conf['k_save_packet'])) {
            $titles[$j] = mb_convert_kana($titles[$j], 'rnsk');
        }
        $ht .= "<option value=\"{$j}\"{$selected[$j]}>{$titles[$j]}</option>";
    }
    $ht .= "</select>\n";

    return $ht;
}

/**
 * ���C�ɓ���Z�b�g���X�g���X�V����
 *
 * @return  boolean �X�V�ɐ���������true, ���s������false
 */
function updateFavSetList()
{
    global $_conf;

    if (file_exists($_conf['expack.favset.namefile'])) {
        $setlist_titles = FavSetManager::getFavSetTitles();
    } else {
        FileCtl::make_datafile($_conf['expack.favset.namefile']);
    }
    if (empty($setlist_titles)) {
        $setlist_titles = array();
    }

    $setlist_names = array('m_favlist_set', 'm_favita_set', 'm_rss_set');
    $setdefs = FavSetManager::getSetDefinition();
    foreach ($setdefs as $setlist_name => $setlist_conf_key) {
        if (isset($_POST["{$setlist_name}_titles"]) && is_array($_POST["{$setlist_name}_titles"])) {
            $setlist_titles[$setlist_name] = array();
            for ($i = 0; $i <= $_conf[$setlist_conf_key[0]]; $i++) {
                if (!isset($_POST["{$setlist_name}_titles"][$i])) {
                    $setlist_titles[$setlist_name][$i] = '';
                    continue;
                }
                $newname = trim($_POST["{$setlist_name}_titles"][$i]);
                $newname = preg_replace('/\r\n\t/', ' ', $newname);
                $newname = htmlspecialchars($newname, ENT_QUOTES);
                $setlist_titles[$setlist_name][$i] = $newname;
            }
        }
    }

    $newdata = serialize($setlist_titles);
    if (FileCtl::file_write_contents($_conf['expack.favset.namefile'], $newdata) === false) {
        P2Util::pushInfoHtml("<p>p2 error: {$_conf['expack.favset.namefile']} �ɂ��C�ɓ���Z�b�g�ݒ���������߂܂���ł����B");
        return false;
    }

    return true;
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
