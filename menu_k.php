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
if ($_GET['view'] == 'favita') {
    $ptitle = '���C�ɔ�';
} elseif ($_GET['view'] == 'rss') {
    $ptitle = 'RSS';
} elseif ($_GET['view'] == 'cate') {
    $ptitle = '��ؽ�';
} elseif (isset($_GET['cateid'])) {
    $ptitle = '��ؽ�';
} else {
    $ptitle = '��޷��p2';
}

echo <<<EOP
{$_conf['doctype']}
<html>
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>{$ptitle}</title>\n
EOP;

echo <<<EOP
</head>
<body{$_conf['k_colors']}>
EOP;

P2Util::printInfoHtml();

// ���C�ɔ��v�����g����
if($_GET['view'] == 'favita'){
    $aShowBrdMenuK->printFavItaHtml();

//RSS���X�g�ǂݍ���
} elseif ($_GET['view'] == 'rss' && $_conf['expack.rss.enabled']) {
    //$mobile = &Net_UserAgent_Mobile::singleton();
    if ($mobile->isNonMobile()) {
        output_add_rewrite_var('b', 'k');
    }
    include P2EX_LIBRARY_DIR . '/rss/menu.inc.php';


// ����ȊO�Ȃ�brd�ǂݍ���
} else {
    $brd_menus =  BrdCtl::read_brds();
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

if (strlen($_REQUEST['word']) > 0) {

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
        P2Util::pushInfoHtml("<p>\"{$hd['word']}\"���܂ޔ͌�����܂���ł����B</p>");
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

P2Util::printInfoHtml();

//==============================================================
// �Z�b�g�؂�ւ��t�H�[����\��
//==============================================================

if ($_conf['expack.favset.enabled'] && 
    (($_GET['view'] == 'favita' && $_conf['favita_set_num'] > 0) ||
     ($_GET['view'] == 'rss' && $_conf['expack.rss.set_num'] > 0)))
{
    echo '<hr>';
    if ($_GET['view'] == 'favita') {
        $set_name = 'm_favita_set';
        $set_title = '���C�ɔ�';
    } elseif ($_GET['view'] == 'rss') {
        $set_name = 'm_rss_set';
        $set_title = 'RSS';
    }
    echo FavSetManager::makeFavSetSwitchForm($set_name, $set_title,
        null, null, false, array('view' => $_GET['view']));
}

// �t�b�^��HTML�\��
echo '<hr>';
echo $list_navi_ht;
echo $modori_url_ht;
echo $_conf['k_to_index_ht'];
echo '</body></html>';

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
