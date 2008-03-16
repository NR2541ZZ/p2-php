<?php
/**
 * rep2expack - RSS�̌��o���ꗗ�\��
 */

// {{{ �\���p�ϐ�

if ($atom) {
    $atom_q = '&amp;atom=1';
    $atom_ht = '<input type="hidden" name="atom" value="1">';
    $atom_chk = ' checked';
} else {
    $atom_q = '';
    $atom_ht = '';
    $atom_chk = '';
}
if ($mtime) {
    $mtime_q = '&amp;mt=' . $mtime;
} else {
    $mtime_q = '';
}

// }}}
// {{{ �c�[���o�[

if ($rss_parse_success) {

    // �^�C�g���摜���|�b�v�A�b�v�\��
    $onmouse_popup = '';
    $popup_header = '';

    // �c�[���o�[���ʕ��i
    $matomeyomi = '';
    if (rss_item_exists($items, 'content:encoded') || rss_item_exists($items, 'description')) {
        $all_en = rawurlencode(base64_encode(base64_decode($site_en) . ' �� �T�v�܂Ƃߓǂ�'));
        $matomeyomi = <<<EOP
<a class="toolanchor" href="read_rss.php?xml={$xml_en}&amp;title_en={$all_en}&amp;num=all{$atom_q}" target="read">�T�v�܂Ƃߓǂ�</a>\n
EOP;
    }
    $ch_link = P2Util::throughIme($channel['link']);
    $ch_dscr_all = str_replace(array('&amp;', '&gt;', '&lt;', '&quot;'), array('&', '>', '<', '"'), strip_tags($channel['description']));
    $ch_dscr = (mb_strwidth($ch_dscr_all) > 36) ? mb_strcut($ch_dscr_all, 0, 36) . '...' : $ch_dscr_all;
    $ch_dscr_all = htmlspecialchars($ch_dscr_all, ENT_QUOTES);
    $ch_dscr = htmlspecialchars($ch_dscr, ENT_QUOTES);
    $rss_toolbar_ht = <<<EOP
            <span class="itatitle"><a class="aitatitle" href="{$ch_link}" title="{$ch_dscr_all}"{$onmouse_popup}><b>{$title}</b></a></span> <span class="time">{$ch_dscr}</span>
        </td>
        <td align="left" valign="middle" width="100%" nowrap>
            <form class="toolbar" method="get" action="subject_rss.php" target="_self">
                <input type="hidden" name="xml" value="{$xml}">
                <input type="hidden" name="site_en" value="{$site_en}">
                <input type="hidden" name="refresh" value="1">{$atom_ht}
                <input type="submit" name="submit" value="�X�V">
            </form>
        </td>
        <td align="right" valign="middle" nowrap>
            {$matomeyomi}<span class="time">{$reloaded_time}</span>
EOP;

}

// }}}
// {{{ �w�b�_

echo <<<EOH
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>{$title}</title>
    <base target="{$_conf['expack.rss.target_frame']}">
    <link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
    <link rel="stylesheet" href="css.php?css=subject&amp;skin={$skin_en}" type="text/css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
{$popup_header}
    <script type="text/javascript" src="js/basic.js?{$_conf['p2expack']}"></script>
    <script type="text/javascript">
    <!--
    function setWinTitle(){
        if (top != self) {top.document.title=self.document.title;}
    }
    // -->
    </script>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onload="setWinTitle();">\n
EOH;

P2Util::printInfoHtml();

// RSS���p�[�X�ł��Ȃ������Ƃ�
if (!$rss_parse_success) {
    echo "<h1>{$title}</h1></body></html>";
    exit;
}

echo <<<EOTB
<table id="sbtoolbar1" class="toolbar" cellspacing="0" cellpadding="4">
    <tr>
        <td align="left" valign="middle" nowrap>
{$rss_toolbar_ht}
            <a class="toolanchor" href="#sbtoolbar2" target="_self">��</a>
        </td>
    </tr>
</table>
<table cellspacing="0" width="100%">

EOTB;

// }}}
// {{{ ���o��

$description_column_ht = '';
$subject_column_ht = '';
$creator_column_ht = '';
$date_column_ht = '';
if ($matomeyomi) {
    $description_column_ht = '<td class="tu">�T�v</td>';
}
if (rss_item_exists($items, 'dc:subject')) {
    $subject_column_ht = '<td class="t">�g�s�b�N</td>';
}
if (rss_item_exists($items, 'dc:creator')) {
    $creator_column_ht = '<td class="t">����</td>';
}
if (rss_item_exists($items, 'dc:date') || rss_item_exists($items, 'dc:pubdate')) {
    $date_column_ht = '<td class="t">����</td>';
}
echo <<<EOP
    <tr class="tableheader">
        {$description_column_ht}<td class="tl">�^�C�g��</td>{$subject_column_ht}{$creator_column_ht}{$date_column_ht}
    </tr>\n
EOP;

// �ꗗ
reset($items);
$i = 0;
foreach ($items as $item) {
    // �ϐ��̏�����
    $date = '';
    $date_ht = '';
    $subject_ht = '';
    $creator_ht = '';
    $description_ht = '';
    $target_ht = '';
    $preview_one = '';
    // �����񂩊��
    $r =  ($i % 2 == 0) ? '2' : '';
    // �T�v
    if ($description_column_ht) {
        if (isset($item['content:encoded']) || isset($item['description'])) {
            $title_en = rawurlencode(base64_encode($item['title']));
            $description_ht = "<td class=\"tu{$r}\"><a class=\"thre_title\" href=\"read_rss.php?xml={$xml_en}&amp;title_en={$title_en}&amp;num={$i}{$atom_q}{$mtime_q}\" target=\"{$_conf['expack.rss.desc_target_frame']}\">��</a></td>";
        } else {
            $description_ht = "<td class=\"tu{$r}\"></td>";
        }
    }
    // �g�s�b�N
    if ($subject_column_ht) {
        $subject_ht = "<td class=\"t{$r}\">" . P2Util::re_htmlspecialchars($item['dc:subject']) . "</td>";
    }
    // ����
    if ($creator_column_ht) {
        $creator_ht = "<td class=\"t{$r}\">" . P2Util::re_htmlspecialchars($item['dc:creator']) . "</td>";
    }
    // ����
    if ($date_column_ht) {
        if (!empty($item['dc:date'])) {
            $date = rss_format_date($item['dc:date']);
        } elseif (!empty($item['dc:pubdate'])) {
            $date = rss_format_date($item['dc:pubdate']);
        }
        $date_ht = "<td class=\"t{$r}\">{$date}</td>";
    }
    // 2ch,bbspink�̃X���b�h��p2�ŕ\��
    if (preg_match('/http:\/\/([^\/]+\.(2ch\.net|bbspink\.com))\/test\/read\.cgi\/([^\/]+)\/([0-9]+)(\/)?([^\/]+)?/', $item['link'])) {
        $link_orig = preg_replace_callback('/http:\/\/([^\/]+\.(2ch\.net|bbspink\.com))\/test\/read\.cgi\/([^\/]+)\/([0-9]+)(\/)?([^\/]+)?/', 'rss_link2ch_callback', $item['link']);
        $preview_one = "<a href=\"{$link_orig}&amp;one=true\">&gt;&gt;1</a> ";
    } else {
        $link_orig = P2Util::throughIme($item['link'], true);
    }
    // ���\��
    $item_title = $item['title'];
    echo <<<EOP
    <tr>
        {$description_ht}<td class="tl{$r}" nowrap>{$preview_one}<a id="tt{$i}" class="thre_title" href="{$link_orig}">{$item_title}</a></td>{$subject_ht}{$creator_ht}{$date_ht}
    </tr>\n
EOP;
    $i++;
}

// }}}
// {{{ �t�b�^

echo <<<EOF
</table>
<table id="sbtoolbar2" class="toolbar" cellspacing="0" cellpadding="4">
    <tr>
        <td align="left" valign="middle" nowrap>
{$rss_toolbar_ht}
            <a class="toolanchor" href="#sbtoolbar1" target="_self">��</a>
        </td>
    </tr>
</table>
<form id="urlform" method="get" action="{$_SERVER['SCRIPT_NAME']}" target="_self">
    RSS/Atom�𒼐ڎw��
    <input id="url_text" type="text" value="{$xml_ht}" name="xml" size="54">
    (<label><input type="checkbox" name="atom" value="1"{$atom_chk}>Atom</label>)
    <input type="submit" name="btnG" value="�\��">
</form>
</body>
</html>
EOF;

// }}}

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
