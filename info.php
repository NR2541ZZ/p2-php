<?php
/*
    p2 - �X���b�h���E�B���h�E
*/

include_once './conf/conf.inc.php';
require_once P2_LIBRARY_DIR . '/thread.class.php';
require_once P2_LIBRARY_DIR . '/filectl.class.php';
require_once P2_LIBRARY_DIR . '/dele.inc.php';

$_login->authorize(); // ���[�U�F��

//================================================================
// �ϐ��ݒ�
//================================================================
$host = isset($_GET['host']) ? $_GET['host'] : null; // "pc.2ch.net"
$bbs  = isset($_GET['bbs'])  ? $_GET['bbs']  : null; // "php"
$key  = isset($_GET['key'])  ? $_GET['key']  : null; // "1022999539"
$ttitle_en = isset($_GET['ttitle_en']) ? $_GET['ttitle_en'] : null;

// popup 0(false), 1(true), 2(true, �N���[�Y�^�C�}�[�t)
if (!empty($_GET['popup'])) {
    $popup_q = '&amp;popup=1';
} else {
    $popup_q = '';
}

// �ȉ��ǂꂩ����Ȃ��Ă��_���o��
if (empty($host) || empty($bbs) || empty($key)) {
    P2Util::printSimpleHtml('p2 error: ����������������܂���B');
    die('');
}

//================================================================
// ���ʂȑO����
//================================================================
// {{{ �폜

if (!empty($_GET['dele']) && $key && $host && $bbs) {
    $r = deleteLogs($host, $bbs, array($key));
    if (empty($r)) {
        $title_msg  = "�~ ���O�폜���s";
        $info_msg   = "�~ ���O�폜���s";
    } elseif ($r == 1) {
        $title_msg  = "�� ���O�폜����";
        $info_msg   = "�� ���O�폜����";
    } elseif ($r == 2) {
        $title_msg  = "- ���O�͂���܂���ł���";
        $info_msg   = "- ���O�͂���܂���ł���";
    }
}

// }}}
// {{{ �����폜

if (!empty($_GET['offrec']) && $key && $host && $bbs) {
    $r1 = offRecent($host, $bbs, $key);
    $r2 = offResHist($host, $bbs, $key);
    if ((empty($r1)) or (empty($r2))) {
        $title_msg  = "�~ �����������s";
        $info_msg   = "�~ �����������s";
    } elseif ($r1 == 1 || $r2 == 1) {
        $title_msg  = "�� ������������";
        $info_msg   = "�� ������������";
    } elseif ($r1 == 2 && $r2 == 2) {
        $title_msg  = "- �����ɂ͂���܂���ł���";
        $info_msg   = "- �����ɂ͂���܂���ł���";
    }

// }}}

// ���C�ɓ���X���b�h
} elseif (isset($_GET['setfav']) && $key && $host && $bbs) {
    include_once P2_LIBRARY_DIR . '/setfav.inc.php';
    if (isset($_GET['setnum'])) {
        setFav($host, $bbs, $key, $_GET['setfav'], $_GET['setnum']);
    } else {
        setFav($host, $bbs, $key, $_GET['setfav']);
    }
    if ($_conf['expack.favset.enabled'] && $_conf['favlist_set_num'] > 0) {
        FavSetManager::loadAllFavSet(true);
    }

// �a������
} elseif (isset($_GET['setpal']) && $key && $host && $bbs) {
    include_once P2_LIBRARY_DIR . '/setpalace.inc.php';
    setPal($host, $bbs, $key, $_GET['setpal']);

// �X���b�h���ځ[��
} elseif (isset($_GET['taborn']) && $key && $host && $bbs) {
    include_once P2_LIBRARY_DIR . '/settaborn.inc.php';
    settaborn($host, $bbs, $key, $_GET['taborn']);
}

// }}}
//=================================================================
// ���C��
//=================================================================

$aThread =& new Thread();

// host�𕪉�����idx�t�@�C���̃p�X�����߂�
$aThread->setThreadPathInfo($host, $bbs, $key);
$key_line = $aThread->getThreadInfoFromIdx();
$aThread->getDatBytesFromLocalDat(); // $aThread->length ��set

if (!$aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs)) {
    $aThread->itaj = $aThread->bbs;
}
$hc['itaj'] = $aThread->itaj;

if (!$aThread->ttitle) {
    if (isset($ttitle_en)) {
        $aThread->setTtitle(base64_decode($ttitle_en));
    } else {
        $aThread->setTitleFromLocal();
    }
}
if (!$ttitle_en) {
    if ($aThread->ttitle) {
        $ttitle_en = base64_encode($aThread->ttitle);
        //$ttitle_urlen = rawurlencode($ttitle_en);
    }
}
if ($ttitle_en) {
    $ttitle_en_q = '&amp;ttitle_en=' . rawurlencode($ttitle_en);
} else {
    $ttitle_en_q = '';
}

if (!is_null($aThread->ttitle_hc)) {
    $hc['ttitle_name'] = $aThread->ttitle_hc;
} else {
    $hc['ttitle_name'] = "�X���b�h�^�C�g�����擾";
}

$common_q = "host={$aThread->host}&amp;bbs={$aThread->bbs}&amp;key={$aThread->key}";

// {{{ favlist �`�F�b�N

/*
// ���C�ɃX�����X�g �Ǎ�
if ($favlines = @file($_conf['favlist_file'])) {
    foreach ($favlines as $l) {
        $favarray = explode('<>', rtrim($l));
        if ($aThread->key == $favarray[1] && $aThread->bbs == $favarray[11]) {
            $aThread->fav = "1";
            if ($favarray[0]) {
                $aThread->setTtitle($favarray[0]);
            }
            break;
        }
    }
}
*/

if ($_conf['expack.favset.enabled'] && $_conf['favlist_set_num'] > 0) {
    $favlist_titles = FavSetManager::getFavSetTitles('m_favlist_set');
    $favdo = empty($aThread->favs[0]);
    $favdo_q = '&amp;setfav=' . ($favdo ? '0' : '1');
    $favmark = $favdo ? '+' : '��';
    $favtitle = ((!isset($favlist_titles[0]) || $favlist_titles[0] == '') ? '���C�ɃX��' : $favlist_titles[0]) . ($favdo ? '�ɒǉ�' : '����O��');
    $setnum_q = '&amp;setnum=0';
    $fav_ht = <<<EOP
<a href="info.php?{$common_q}{$ttitle_en_q}{$favdo_q}{$setnum_q}{$popup_q}{$_conf['k_at_a']}"><span class="fav" title="{$favtitle}">{$favmark}</span></a>
EOP;
    for ($i = 1; $i <= $_conf['favlist_set_num']; $i++) {
        $favdo = empty($aThread->favs[$i]);
        $favdo_q = '&amp;setfav=' . ($favdo ? '0' : '1');
        $favmark = $favdo ? $i : '��';
        $favtitle = ((!isset($favlist_titles[$i]) || $favlist_titles[$i] == '') ? '���C�ɃX��' . $i : $favlist_titles[$i]) . ($favdo ? '�ɒǉ�' : '����O��');
        $setnum_q = '&amp;setnum=' . $i;
        $fav_ht .= <<<EOP
 | <a href="info.php?{$common_q}{$ttitle_en_q}{$favdo_q}{$setnum_q}{$popup_q}{$_conf['k_at_a']}"><span class="fav" title="{$favtitle}">{$favmark}</span></a>
EOP;
    }
} else {
    $favdo = empty($aThread->fav);
    $favdo_q = '&amp;setfav=' . ($favdo ? '0' : '1');
    $favmark = $favdo ? '+' : '��';
    $favtitle = $favdo ? '���C�ɃX���ɒǉ�' : '���C�ɃX������O��';
    $fav_ht = <<<EOP
<a href="info.php?{$common_q}{$ttitle_en_q}{$favdo_q}{$popup_q}{$_conf['k_at_a']}"><span class="fav" title="{$favtitle}">{$favmark}</span></a>
EOP;
}

// }}}
// {{{ palace �`�F�b�N

// �a������X�����X�g �Ǎ�
$palace_idx = $_conf['pref_dir'] . '/p2_palace.idx';
if ($pallines = @file($palace_idx)) {
    foreach ($pallines as $l) {
        $palarray = explode('<>', rtrim($l));
        if ($aThread->key == $palarray[1]) {
            $isPalace = true;
            if ($palarray[0]) {
                $aThread->setTtitle($palarray[0]);
            }
            break;
        }
    }
}

$paldo_q = '&amp;setpal=' . ($isPalace ? '0' : '1');
$pal_a_ht = "info.php?{$common_q}{$paldo_q}{$popup_q}{$ttitle_en_q}{$_conf['k_at_a']}";

if ($isPalace) {
    $pal_ht = "<a href=\"{$pal_a_ht}\" title=\"DAT���������X���p�̂��C�ɓ���\">��</a>";
} else {
    $pal_ht = "<a href=\"{$pal_a_ht}\" title=\"DAT���������X���p�̂��C�ɓ���\">+</a>";
}

// }}}
// {{{ �X���b�h���ځ[��`�F�b�N

// �X���b�h���ځ[�񃊃X�g�Ǎ�
$idx_host_dir = P2Util::idxDirOfHost($host);
$taborn_file = $idx_host_dir . '/' . $bbs . '/p2_threads_aborn.idx';
if ($tabornlist = @file($taborn_file)) {
    foreach ($tabornlist as $l) {
        $tarray = explode('<>', rtrim($l));
        if ($aThread->key == $tarray[1]) {
            $isTaborn = true;
            break;
        }
    }
}

$taborndo_title_at = '';
if (!empty($isTaborn)) {
    $tastr1 = "���ځ[��";
    $tastr2 = "���ځ[���������";
    $taborndo = 0;
} else {
    $tastr1 = "�ʏ�";
    $tastr2 = "���ځ[�񂷂�";
    $taborndo = 1;
    if (!$_conf['ktai']) {
        $taborndo_title_at = ' title="�X���b�h�ꗗ�Ŕ�\���ɂ��܂�"';
    }
}

$taborn_ht = <<<EOP
{$tastr1} [<a href="info.php?{$common_q}&amp;taborn={$taborndo}{$popup_q}{$ttitle_en_q}{$_conf['k_at_a']}"{$taborndo_title_at}>{$tastr2}</a>]
EOP;

// }}}

// ���O����Ȃ��t���O�Z�b�g
if (file_exists($aThread->keydat) or file_exists($aThread->keyidx)) {
    $existLog = true;
}

//=================================================================
// HTML�v�����g
//=================================================================
if ($_conf['ktai']) {
    $target_read_at = ' target="read"';
    $target_sb_at = ' target="sbject"';
}

$motothre_url = $aThread->getMotoThread();
if (P2Util::isHost2chs($aThread->host)) {
    $motothre_org_url = $aThread->getMotoThread(true);
} else {
    $motothre_org_url = $motothre_url;
}


if (!is_null($title_msg)) {
    $hc['title'] = $title_msg;
} else {
    $hc['title'] = "info - {$hc['ttitle_name']}";
}

$hd = array_map('htmlspecialchars', $hc);


P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOHEADER
<html>
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>{$hd['title']}</title>\n
EOHEADER;

if (!$_conf['ktai']) {
    echo <<<EOP
    <link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
    <link rel="stylesheet" href="css.php?css=info&amp;skin={$skin_en}" type="text/css">\n
EOP;
}

if ($_GET['popup'] == 2) {
    echo <<<EOSCRIPT
    <script type="text/javascript" src="js/closetimer.js?{$_conf['p2expack']}"></script>
EOSCRIPT;
    $body_onload = <<<EOP
 onLoad="startTimer(document.getElementById('timerbutton'))"
EOP;
}

$body_at = ($_conf['ktai']) ? $_conf['k_colors'] : $body_onload;
echo <<<EOP
</head>
<body{$body_at}>
EOP;

P2Util::printInfoHtml();

echo "<p>\n";
echo "<b><a class=\"thre_title\" href=\"{$_conf['read_php']}?{$common_q}{$_conf['k_at_a']}\"{$target_read_at}>{$hd['ttitle_name']}</a></b>\n";
echo "</p>\n";

// �g�тȂ�`���ŕ\��
if ($_conf['ktai']) {
    if (!empty($info_msg)) {
        echo "<p>" . $info_msg . "</p>\n";
    }
}

if (checkRecent($aThread->host, $aThread->bbs, $aThread->key) or checkResHist($aThread->host, $aThread->bbs, $aThread->key)) {
    $offrec_ht = " / [<a href=\"info.php?{$common_q}&amp;offrec=true{$popup_q}{$ttitle_en_q}{$_conf['k_at_a']}\" title=\"���̃X�����u�ŋߓǂ񂾃X���v�Ɓu�������ݗ����v����O���܂�\">��������O��</a>]";
}

if (!$_conf['ktai']) {
    echo "<table cellspacing=\"0\">\n";
}
printInfoTrHtml("���X��", "<a href=\"{$motothre_url}\"{$target_read_at}>{$motothre_url}</a>");
if (!$_conf['ktai']) {
    printInfoTrHtml("�z�X�g", $aThread->host);
}
printInfoTrHtml("��", "<a href=\"{$_conf['subject_php']}?host={$aThread->host}&amp;bbs={$aThread->bbs}{$_conf['k_at_a']}\"{$target_sb_at}>{$hd['itaj']}</a>");

// PC�p�\��
if (!$_conf['ktai']) {
    printInfoTrHtml("key", $aThread->key);
}

if ($existLog) {
    printInfoTrHtml("���O", "���� [<a href=\"info.php?{$common_q}&amp;dele=true{$popup_q}{$ttitle_en_q}{$_conf['k_at_a']}\">�폜����</a>]{$offrec_ht}");
} else {
    printInfoTrHtml("���O", "���擾{$offrec_ht}");
}

if ($aThread->gotnum) {
    printInfoTrHtml("�������X��", $aThread->gotnum);
} elseif (!$aThread->gotnum and $existLog) {
    printInfoTrHtml("�������X��", "0");
} else {
    printInfoTrHtml("�������X��", "-");
}

// PC�p�\��
if (!$_conf['ktai']) {
    if (file_exists($aThread->keydat)) {
        if ($aThread->length) {
            printInfoTrHtml("dat�T�C�Y", $aThread->length.' �o�C�g');
        }
        printInfoTrHtml("dat", $aThread->keydat);
    } else {
        printInfoTrHtml("dat", "-");
    }
    if (file_exists($aThread->keyidx)) {
        printInfoTrHtml("idx", $aThread->keyidx);
    } else {
        printInfoTrHtml("idx", "-");
    }
}

printInfoTrHtml("���C�ɃX��", $fav_ht);
printInfoTrHtml("�a������", $pal_ht);
printInfoTrHtml("�\��", $taborn_ht);

// PC
if (!$_conf['ktai']) {
    echo "</table>\n";
}

if (!$_conf['ktai']) {
    if (!empty($info_msg)) {
        echo "<span class=\"infomsg\">".$info_msg."</span>\n";
    } else {
        echo "�@\n";
    }
}

// �g�уR�s�y�p�t�H�[��
if ($_conf['ktai']) {
    echo getCopypaFormHtml($motothre_org_url, $hd['ttitle_name']);
}

// {{{ ����{�^��

if (!empty($_GET['popup'])) {
    echo '<div align="center">';
    if ($_GET['popup'] == 1) {
        echo '<form action=""><input type="button" value="�E�B���h�E�����" onClick="window.close();"></form>';
    } elseif ($_GET['popup'] == 2) {
        echo <<<EOP
    <form action=""><input id="timerbutton" type="button" value="Close Timer" onClick="stopTimer(document.getElementById('timerbutton'))"></form>
EOP;
    }
    echo '</div>' . "\n";
}

// }}}

if ($_conf['ktai']) {
    echo '<hr>' . $_conf['k_to_index_ht'];
}

echo '</body></html>';

// �I��
exit();

//=======================================================
// �֐� �i���̃t�@�C�����ł̂ݗ��p�j
//=======================================================
/**
 * �X�����HTML��\������
 *
 * @return  void
 */
function printInfoTrHtml($s, $c_ht)
{
    global $_conf;

    // �g��
    if ($_conf['ktai']) {
        echo "{$s}: {$c_ht}<br>";
    // PC
    } else {
        echo "<tr><td class=\"tdleft\" nowrap><b>{$s}</b>&nbsp;</td><td class=\"tdcont\">{$c_ht}</td></tr>\n";
    }
}

/**
 * �X���^�C��URL�̃R�s�y�p�̃t�H�[��HTML���擾����
 *
 * @return  string
 */
function getCopypaFormHtml($url, $ttitle_name_hd)
{
    $url_hd = htmlspecialchars($url, ENT_QUOTES);

    $me_url = $me_url = P2Util::getMyUrl();
    // $_SERVER['REQUEST_URI']

    $htm = <<<EOP
<form action="{$me_url}">
 <textarea name="copy" rows="5" cols="50">{$ttitle_name_hd}&#10;{$url_hd}</textarea>
</form>
EOP;
// <input type="text" name="url" value="{$url_hd}">
// <textarea name="msg_txt">{$msg_txt}</textarea><br>

    return $htm;
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
