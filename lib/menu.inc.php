<?php
/*
    p2 -  ���j���[
    �t���[��������ʁA�������� PC�p
    
    menu.php, menu_side.php ���ǂݍ��܂��
*/

require_once (P2_LIBRARY_DIR . '/brdctl.class.php');
require_once (P2_LIBRARY_DIR . '/showbrdmenupc.class.php');

authorize(); //���[�U�F��

//==============================================================
// �ϐ��ݒ�
//==============================================================
$s = $_SERVER['HTTPS'] ? 's' : '';
$me_url = "http{$s}://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
$me_dir_url = dirname($me_url);
// menu_side.php �� URL�B�i���[�J���p�X�w��͂ł��Ȃ��悤���j
$menu_side_url = $me_dir_url.'/menu_side.php';

$brd_menus = array();

if (isset($_GET['word'])) {
    $word = $_GET['word'];
} elseif (isset($_POST['word'])) {
    $word = $_POST['word'];
}

// ������ ====================================
if (isset($word) && strlen($word) > 0) {

    if (preg_match('/^\.+$/', $word)) {
        $word = '';
    }
    
    // and����
    include_once (P2_LIBRARY_DIR . '/strctl.class.php');
    $word_fm = StrCtl::wordForMatch($word, 'and');
    if (P2_MBREGEX_AVAILABLE == 1) {
        $GLOBALS['words_fm'] = @mb_split('\s+', $word_fm);
        $GLOBALS['word_fm'] = @mb_ereg_replace('\s+', '|', $word_fm);
    } else {
        $GLOBALS['words_fm'] = @preg_split('/\s+/', $word_fm);
        $GLOBALS['word_fm'] = @preg_replace('/\s+/', '|', $word_fm);
    }
}


//============================================================
// ����ȑO�u����
//============================================================
// ���C�ɔ̒ǉ��E�폜
if (isset($_GET['setfavita'])) {
    include_once (P2_LIBRARY_DIR . '/setfavita.inc.php');
    setFavIta();
}

//================================================================
// �����C��
//================================================================
$aShowBrdMenuPc =& new ShowBrdMenuPc();

//============================================================
// ���w�b�_
//============================================================
$reloaded_time = date('n/j G:i:s'); // �X�V����
$ptitle = 'p2 - menu';

P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">\n
EOP;

// �����X�V
if ($_conf['menu_refresh_time']) {
    $refresh_time_s = $_conf['menu_refresh_time'] * 60;
    echo <<<EOP
    <meta http-equiv="refresh" content="{$refresh_time_s};URL={$me_url}?new=1">\n
EOP;
}

echo <<<EOP
    <title>{$ptitle}</title>
    <base target="subject">
EOP;

@include("./style/style_css.inc");
@include("./style/menu_css.inc");

echo <<<EOSCRIPT
    <script type="text/javascript" src="js/showhide.js"></script>
    <script language="JavaScript">
    <!--
    function addSidebar(title, url) {
       if ((typeof window.sidebar == "object") && (typeof window.sidebar.addPanel == "function")) {
          window.sidebar.addPanel(title, url, '');
       } else {
          goNetscape();
       }                                                                         
    }
    function goNetscape()
    {
    //  var rv = window.confirm ("This page is enhanced for use with Netscape 7.  " + "Would you like to upgrade now?");
       var rv = window.confirm ("���̃y�[�W�� Netscape 7 �p�Ɋg������Ă��܂�.  " + "�������A�b�v�f�[�g���܂���?");
       if (rv)
          document.location.href = "http://home.netscape.com/ja/download/download_n6.html";
    }
    
    function chUnColor(idnum){
        unid='un'+idnum;
        document.getElementById(unid).style.color="{$STYLE['menu_color']}";
    }
    
    function chMenuColor(idnum){
        newthreid='newthre'+idnum;
        if(document.getElementById(newthreid)){document.getElementById(newthreid).style.color="{$STYLE['menu_color']}";}
        unid='un'+idnum;
        document.getElementById(unid).style.color="{$STYLE['menu_color']}";
    }
    
    // -->
    </script>\n
EOSCRIPT;
echo <<<EOP
</head>
<body>
EOP;

echo $_info_msg_ht;
$_info_msg_ht = '';

if (!empty($sidebar)) {
    echo <<<EOP
<p><a href="index.php?sidebar=true" target="_content">p2 - 2�y�C���\��</a></p>\n
EOP;
}

if ($_conf['enable_menu_new']) {
    echo <<<EOP
$reloaded_time [<a href="{$_SERVER['PHP_SELF']}?new=1" target="_self">�X�V</a>]
EOP;
}

//==============================================================
// �����C�ɔ��v�����g����
//==============================================================
$aShowBrdMenuPc->print_favIta();

//==============================================================
// ������
//==============================================================
$norefresh_q = '&amp;norefresh=true';

echo <<<EOP
<div class="menu_cate"><b><a class="menu_cate" href="javascript:void(0);" onClick="showHide('c_spacial');" target="_self">����</a></b><br>
    <div class="itas" id="c_spacial">
EOP;

// ���V������\������ꍇ
if ($_conf['enable_menu_new'] == 1 and $_GET['new']) {

    initMenuNewSp("fav");    // �V������������
    echo <<<EOP
    �@<a href="{$_conf['subject_php']}?spmode=fav{$norefresh_q}" onClick="chMenuColor({$matome_i});" accesskey="f">���C�ɃX��</a> (<a href="{$_conf['read_new_php']}?spmode=fav" target="read" id="un{$matome_i}" onClick="chUnColor({$matome_i});"{$class_newres_num}>{$shinchaku_num}</a>)<br>
EOP;
    flush();

    initMenuNewSp("recent");    // �V������������
    echo <<<EOP
    �@<a href="{$_conf['subject_php']}?spmode=recent{$norefresh_q}" onClick="chMenuColor({$matome_i});" accesskey="h">�ŋߓǂ񂾃X��</a> (<a href="{$_conf['read_new_php']}?spmode=recent" target="read" id="un{$matome_i}" onClick="chUnColor({$matome_i});"{$class_newres_num}>{$shinchaku_num}</a>)<br>
EOP;
    flush();

    initMenuNewSp("res_hist");    // �V������������
    echo <<<EOP
    �@<a href="{$_conf['subject_php']}?spmode=res_hist{$norefresh_q}" onClick="chMenuColor({$matome_i});">��������</a> <a href="read_res_hist.php" target="read">���O</a> (<a href="{$_conf['read_new_php']}?spmode=res_hist" target="read" id="un{$matome_i}" onClick="chUnColor({$matome_i});"{$class_newres_num}>{$shinchaku_num}</a>)<br>
EOP;
    flush();

// �V������\�����Ȃ��ꍇ
} else {
    echo <<<EOP
    �@<a href="{$_conf['subject_php']}?spmode=fav{$norefresh_q}" accesskey="f">���C�ɃX��</a><br>
    �@<a href="{$_conf['subject_php']}?spmode=recent{$norefresh_q}" accesskey="h">�ŋߓǂ񂾃X��</a><br>
    �@<a href="{$_conf['subject_php']}?spmode=res_hist{$norefresh_q}">��������</a> (<a href="./read_res_hist.php" target="read">���O</a>)<br>
EOP;
}

echo <<<EOP
    �@<a href="{$_conf['subject_php']}?spmode=palace{$norefresh_q}">�X���̓a��</a><br>
    �@<a href="setting.php">���O�C���Ǘ�</a><br>
    �@<a href="editpref.php">�ݒ�Ǘ�</a><br>
    �@<a href="http://find.2ch.net/" target="_blank" title="2ch��������">find.2ch.net</a>
    </div>
</div>\n
EOP;

//==============================================================
// ���J�e�S���Ɣ�\��
//==============================================================
// brd�ǂݍ���
$brd_menus = BrdCtl::read_brds();

//===========================================================
// ���v�����g
//===========================================================

// {{{ �������[�h�������

if (isset($word) && strlen($word) > 0) {

    $word_ht = htmlspecialchars($word);
    
    $msg_ht .=  '<p>';
    if (!$GLOBALS['ita_mikke']['num']) {
        if (empty($GLOBALS['threti_match_ita_num'])) {
            $msg_ht .=  "\"{$word_ht}\"���܂ޔ͌�����܂���ł����B\n";
        }
    } else {
        $msg_ht .=  "\"{$word_ht}\"���܂ޔ� {$GLOBALS['ita_mikke']['num']}hit!\n";
        
        // �������ʂ���Ȃ�A�����Ŕꗗ���J��
        if ($GLOBALS['ita_mikke']['num'] == 1) {
        $msg_ht .= '�i�����I�[�v���j';
            echo <<<EOP
<script type="text/javascript">
<!--
    parent.subject.location.href="{$_conf['subject_php']}?host={$GLOBALS['ita_mikke']['host']}&bbs={$GLOBALS['ita_mikke']['bbs']}&itaj_en={$GLOBALS['ita_mikke']['itaj_en']}";
// -->
</script>
EOP;
        }
    }
    $msg_ht .= '</p>';
    
    $_info_msg_ht .= $msg_ht;
}

// }}}

echo $_info_msg_ht;
$_info_msg_ht = "";

// �����t�H�[����\��
echo <<<EOFORM
<form method="GET" action="{$_SERVER['PHP_SELF']}" accept-charset="{$_conf['accept_charset']}" target="_self">
    <input type="hidden" name="detect_hint" value="����">
    <p>
        <input type="text" id="word" name="word" value="{$word_ht}" size="14">
        <input type="submit" name="submit" value="����">
    </p>
</form>\n
EOFORM;

// �J�e�S�����j���[��\��
if ($brd_menus) {
    foreach ($brd_menus as $a_brd_menu) {
        $aShowBrdMenuPc->printBrdMenu($a_brd_menu->categories);
    }
}

//==============================================================
// �t�b�^��\��
//==============================================================

// ��for Mozilla Sidebar
if (empty($sidebar)) {
    echo <<<EOP
<script type="text/JavaScript">
<!--
if ((typeof window.sidebar == "object") && (typeof window.sidebar.addPanel == "function")) {
    document.writeln("<p><a href=\"javascript:void(0);\" onClick=\"addSidebar('p2 Menu', '{$menu_side_url}');\">p2 Menu�� Sidebar �ɒǉ�</a></p>");
}
-->
</script>\n
EOP;
}

echo '</body></html>';


//==============================================================
// �֐�
//==============================================================
/**
 * spmode�p��menu�̐V����������������
 */
function initMenuNewSp($spmode_in)
{
    global $shinchaku_num, $matome_i, $host, $bbs, $spmode, $STYLE, $class_newres_num;
    $matome_i++;
    $host = "";
    $bbs = "";
    $spmode = $spmode_in;
    include("./subject_new.php");    // $shinchaku_num, $_newthre_num ���Z�b�g
    if ($shinchaku_num > 0) {
        $class_newres_num = ' class="newres_num"';
    } else {
        $class_newres_num = ' class="newres_num_zero"';
    }
}

?>
