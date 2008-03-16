<?php
/**
 * rep2expack - RSS�̌��o���ꗗ�\�� (�g�їp)
 */

// {{{ �w�b�_

echo <<<EOH
<html lang="ja">
<head>
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
<title>{$title}</title>
</head>
<body{$_conf['k_colors']}>
EOH;

P2Util::printInfoHtml();

echo "<p><b>{$title}</b></p><hr>";

// RSS���p�[�X�ł��Ȃ������Ƃ�
if (!$rss_parse_success) {
    echo '</body></html>';
    exit;
}

// }}}
// {{{ �\���p�ϐ�

if ($atom) {
    $atom_q = '&amp;atom=1';
    $atom_ht = '<input type="hidden" name="atom" value="1">';
    $atom_chk = ' chedked';
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
// {{{ ���o��

reset($items);
$i = 0;
echo "<ol>\n";
foreach ($items as $item) {
    $item = array_map('trim', $item);
    $item_title = P2Util::re_htmlspecialchars($item['title']);
    $link_orig = P2Util::throughIme($item['link']);
    // jig�u���E�UWEB http://bwXXXX.jig.jp/fweb/?_jig_=
    $view_jig = '';
    /*
    $link_jig = 'http://bwXXXX.jig.jp/fweb/?_jig_=' . urlencode($item['link']);
    $view_jig = ' <a href="' . P2Util::throughIme($link_jig) . '">jW</a>';
    */
    if ((isset($item['content:encoded']) && $item['content:encoded'] !== '') ||
        (isset($item['description']) && $item['description'] !== '')
    ) {
        echo "<li><a href=\"read_rss.php?xml={$xml_en}&amp;title_en={$title_en}&amp;num={$i}{$atom_q}{$mtime_q}\">{$item_title}</a></li>\n";
    } else {
        echo "<li>{$item_title} <a href=\"{$link_orig}\">��</a>{$view_jig}</li>\n";
    }
    $i++;
}
echo "</ol>\n";

// }}}
// {{{ �t�b�^

echo <<<EOF
<hr>
<p>
<a {$_conf['accesskey']}="9" href="menu_k.php?view=rss">9.RSS</a>
{$_conf['k_to_index_ht']}
</p>
<hr>
<form id="urlform" method="post" action="{$_SERVER['SCRIPT_NAME']}" target="_self">
RSS/Atom�𒼐ڎw��<br>
<input type="hidden" name ="k" value="1">
<input type="text" name="xml" value="{$xml_ht}"><br>
<input type="submit" name="btnG" value="�\��">
(<input type="checkbox" name="atom" value="1"{$atom_chk}>Atom)
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
