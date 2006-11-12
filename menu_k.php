<?php
/*
    p2 -  ���j���[ �g�їp
*/

include_once './conf/conf.inc.php';
require_once P2_LIBRARY_DIR . '/brdctl.class.php';
require_once P2_LIBRARY_DIR . '/showbrdmenuk.class.php';

$_login->authorize(); // ���[�U�F��

//==============================================================
// �ϐ��ݒ�
//==============================================================
$_conf['ktai'] = 1;
$brd_menus = array();
$GLOBALS['menu_show_ita_num'] = 0;

BrdCtl::parseWord(); // set $GLOBALS['word']

//============================================================
// ����ȑO�u����
//============================================================
// ���C�ɔ̒ǉ��E�폜
if (isset($_GET['setfavita'])) {
    include_once P2_LIBRARY_DIR . '/setfavita.inc.php';
    setFavIta();
}

//================================================================
// ���C��
//================================================================
$aShowBrdMenuK =& new ShowBrdMenuK();

//============================================================
// �w�b�_
//============================================================
if ($_GET['view'] == "favita") {
    $ptitle = "���C�ɔ�";
} elseif ($_GET['view'] == "cate"){
    $ptitle = "��ؽ�";
} elseif (isset($_GET['cateid'])) {
    $ptitle = "��ؽ�";
} else {
    $ptitle = "��޷��p2";
}

P2Util::header_content_type();
echo <<<EOP
{$_conf['doctype']}
<html>
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>{$ptitle}</title>\n
EOP;

echo "</head><body>\n";

P2Util::printInfoMsgHtml();

// ���C�ɔ��v�����g����
if ($_GET['view'] == 'favita') {
    $aShowBrdMenuK->printFavItaHtml();

// ����ȊO�Ȃ�brd�ǂݍ���
} else {
    $brd_menus = BrdCtl::read_brds();
}

// {{{ �����t�H�[����HTML�\��

if ($_GET['view'] != 'favita' && $_GET['view'] != 'rss' && empty($_GET['cateid'])) {
    
    echo BrdCtl::getMenuKSearchFormHtml();

    echo '<br>';
}

// }}}

//===========================================================
// �������ʂ�HTML�\��
//===========================================================
// {{{ �������[�h�������

if (strlen($GLOBALS['word']) > 0) {

    $hd['word'] = htmlspecialchars($word, ENT_QUOTES);

    if ($GLOBALS['ita_mikke']['num']) {
        $hit_ht = "<br>\"{$hd['word']}\" {$GLOBALS['ita_mikke']['num']}hit!";
    }
    echo "��ؽČ�������{$hit_ht}<hr>";

    // �����������ĕ\������
    if ($brd_menus) {
        foreach ($brd_menus as $a_brd_menu) {
            $aShowBrdMenuK->printItaSearch($a_brd_menu->categories);
        }
    }

    if (!$GLOBALS['ita_mikke']['num']) {
        P2Util::pushInfoMsgHtml("<p>\"{$hd['word']}\"���܂ޔ͌�����܂���ł����B</p>");
    }
    $modori_url_ht = <<<EOP
<div><a href="menu_k.php?view=cate&amp;nr=1{$_conf['k_at_a']}">��ؽ�</a></div>
EOP;
}

// }}}
// {{{ �J�e�S����HTML�\��

if ($_GET['view'] == 'cate' or isset($_REQUEST['word']) && strlen($GLOBALS['word']) == 0) {
    echo "��ؽ�<hr>";
    if ($brd_menus) {
        foreach ($brd_menus as $a_brd_menu) {
            $aShowBrdMenuK->printCate($a_brd_menu->categories);
        }
    }

}

// }}}

//==============================================================
// �J�e�S���̔�HTML�\��
//==============================================================
if (isset($_GET['cateid'])) {
    if ($brd_menus) {
        foreach ($brd_menus as $a_brd_menu) {
            $aShowBrdMenuK->printIta($a_brd_menu->categories);
        }
    }
    $modori_url_ht = <<<EOP
<a href="menu_k.php?view=cate&amp;nr=1{$_conf['k_at_a']}">��ؽ�</a><br>
EOP;
}

    
P2Util::printInfoMsgHtml();


// �t�b�^��HTML�\��
echo '<hr>';
echo $list_navi_ht;
echo $modori_url_ht;
echo $_conf['k_to_index_ht'];
echo '</body></html>';

?>