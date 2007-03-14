<?php
/*
    p2 -  ���j���[ �g�їp
*/

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/brdctl.class.php';
require_once P2_LIB_DIR . '/showbrdmenuk.class.php';

$_login->authorize(); // ���[�U�F��

//==============================================================
// �ϐ��ݒ�
//==============================================================
$_conf['ktai'] = 1;
$brd_menus = array();
$GLOBALS['menu_show_ita_num'] = 0;

BrdCtl::parseWord(); // set $GLOBALS['word']

//============================================================
// ����ȑO����
//============================================================
// ���C�ɔ̒ǉ��E�폜
if (isset($_GET['setfavita'])) {
    require_once P2_LIB_DIR . '/setfavita.inc.php';
    setFavIta();
}

//================================================================
// ���C��
//================================================================
$aShowBrdMenuK =& new ShowBrdMenuK;

//============================================================
// �w�b�_HTML��\��
//============================================================

$get['view'] = isset($_GET['view']) ? $_GET['view'] : null;

if ($get['view'] == "favita") {
    $ptitle = "���C�ɔ�";
} elseif ($get['view'] == "cate"){
    $ptitle = "��ؽ�";
} elseif (isset($_GET['cateid'])) {
    $ptitle = "��ؽ�";
} else {
    $ptitle = "��޷��p2";
}

echo $_conf['doctype'];
echo <<<EOP
<html>
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>{$ptitle}</title>\n
EOP;

echo "</head><body>\n";

P2Util::printInfoHtml();

// ���C�ɔ�HTML�\������
if ($get['view'] == 'favita') {
    $aShowBrdMenuK->printFavItaHtml();

// ����ȊO�Ȃ�brd�ǂݍ���
} else {
    $brd_menus = BrdCtl::readBrdMenus();
}

// �����t�H�[����HTML�\��
if ($get['view'] != 'favita' && $get['view'] != 'rss' && empty($_GET['cateid'])) {
    
    echo BrdCtl::getMenuKSearchFormHtml();
    echo '<br>';
}

//===========================================================
// �������ʂ�HTML�\��
//===========================================================
// {{{ �������[�h�������

if (strlen($GLOBALS['word']) > 0) {

    $word_hs = htmlspecialchars($word, ENT_QUOTES);

    if ($GLOBALS['ita_mikke']['num']) {
        $hit_ht = "<br>\"{$word_hs}\" {$GLOBALS['ita_mikke']['num']}hit!";
    }
    echo "��ؽČ�������{$hit_ht}<hr>";

    // �����������ĕ\������
    if ($brd_menus) {
        foreach ($brd_menus as $a_brd_menu) {
            $aShowBrdMenuK->printItaSearch($a_brd_menu->categories);
        }
    }

    if (!$GLOBALS['ita_mikke']['num']) {
        P2Util::pushInfoHtml("<p>\"{$word_hs}\"���܂ޔ͌�����܂���ł����B</p>");
    }
    $modori_url_ht = <<<EOP
<div><a href="menu_k.php?view=cate&amp;nr=1{$_conf['k_at_a']}">��ؽ�</a></div>
EOP;
}

// }}}

// �J�e�S����HTML�\��
if ($get['view'] == 'cate' or isset($_REQUEST['word']) && strlen($GLOBALS['word']) == 0) {
    echo "��ؽ�<hr>";
    if ($brd_menus) {
        foreach ($brd_menus as $a_brd_menu) {
            $aShowBrdMenuK->printCate($a_brd_menu->categories);
        }
    }
}


// �J�e�S���̔�HTML�\��
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


P2Util::printInfoHtml();

!isset($GLOBALS['list_navi_ht']) and $GLOBALS['list_navi_ht'] = null;
!isset($modori_url_ht) and $modori_url_ht = null;

// �t�b�^��HTML�\��
echo '<hr>';
echo $list_navi_ht;
echo $modori_url_ht;
echo $_conf['k_to_index_ht'];
echo '</body></html>';

