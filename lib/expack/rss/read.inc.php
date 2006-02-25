<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=4 fdm=marker: */
/* mi: charset=Shift_JIS */

// {{{ �w�b�_

$ch_title = P2Util::re_htmlspecialchars($channel['title']);

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
    <link rel="stylesheet" href="css.php?css=read&amp;skin={$skin_en}" type="text/css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <script type="text/javascript" src="js/basic.js"></script>
    <script type="text/javascript">
    <!--
    function setWinTitle(){
        if (top != self) {top.document.title=self.document.title;}
    }
    // -->
    </script>
</head>
<body onload="setWinTitle()">
{$_info_msg_ht}
EOH;

// RSS���p�[�X�ł��Ȃ������Ƃ�
if (!$rss_parse_success) {
    echo '</body></html>';
    exit;
}

// }}}
// {{{ �T�v

reset($items);
if (isset($num)) {
    if (is_string($num) && $num == 'all') {
        $i = 1;
        $j = count($items);
        foreach ($items as $item) {
            rss_print_content($item, $i, $j);
            $i++;
        }
    } else {
        rss_print_content($items[$num], $num, 0);
    }
}

// }}}
// {{{ �t�b�^

echo '</body></html>';

// }}}
// {{{ �\���p�֐�

function rss_print_content($item, $num, $count)
{
    $item = array_map('trim', $item);

    // �ϐ��̏�����
    $date_ht = '';
    $subject_ht = '';
    $creator_ht = '';
    $description_ht = '';
    $prev_item_ht = '';
    $next_item_ht = '';

    // �����N
    $item_title = P2Util::re_htmlspecialchars($item['title']);

    // �^�C�g��
    $link_orig = P2Util::throughIme($item['link']);

    // �g�s�b�N
    if (isset($item['dc:subject'])) {
        $subject_ht = $item['dc:subject'];
    }

    // ����
    if (isset($item['dc:creator']) && $item['dc:creator'] !== '') {
        $creator_ht = "<b class=\"name\">" . trim($item['dc:creator']) . "</b>�F";
    }

    // ����
    if (!empty($item['dc:date'])) {
        $date_ht = rss_format_date($item['dc:date']);
    } elseif (!empty($item['dc:pubdate'])) {
        $date_ht = rss_format_date($item['dc:pubdate']);
    }

    // �T�v
    if (isset($item['content:encoded']) && $item['content:encoded'] !== '') {
        $description_ht = rss_desc_converter($item['content:encoded']);
    } elseif (isset($item['description']) && $item['description'] !== '') {
        $description_ht = rss_desc_converter($item['description']);
    }

    // �O��̊T�v�փW�����v
    if ($count != 0) {
        $prev_item_num = $num - 1;
        $next_item_num = $num + 1;
        if ($prev_item_num != 0) {
            $prev_item_ht = "<a href=\"#it{$prev_item_num}\">��</a>";
        }
        if ($next_item_num <= $count) {
            $next_item_ht = "<a href=\"#it{$next_item_num}\">��</a>";
        }
    }

    // �\��
    echo <<<EOP
<table id="it{$num}" width="100%">
    <tr>
        <td align="left"><h3 class="thread_title">{$item_title}</h3></td>
        <td align="right" nowrap>{$prev_item_ht} {$next_item_ht}</td>
    </tr>
</table>
<div style="margin:0.5em">{$creator_ht}{$date_ht} <a href="{$link_orig}">[LINK]</a></div>
<div style="margin:1em 1em 1em 2em">
{$description_ht}
</div>
<div style="text-align:right"><a href="#it{$num}">��</a></div>\n
EOP;
    if ($count != 0 && $num != $count) { echo "\n<hr style=\"margin:20px 0px\">\n\n"; }

}

// }}}

?>