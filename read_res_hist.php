<?php
// p2 - �������ݗ��� ���X���e�\��
// �t���[��������ʁA�E������

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/dataphp.class.php';
require_once P2_LIB_DIR . '/res_hist.class.php';
require_once P2_LIB_DIR . '/read_res_hist.inc.php';

$_login->authorize(); // ���[�U�F��

//======================================================================
// �ϐ�
//======================================================================
$newtime = date('gis');

$deletemsg_st = '�폜';
$ptitle = '�������񂾃��X�̋L�^';

//================================================================
// ����ȑO����
//================================================================
// �폜
if ((isset($_POST['submit']) and $_POST['submit'] == $deletemsg_st) or isset($_GET['checked_hists'])) {
    $checked_hists = array();
    if (isset($_POST['checked_hists'])) {
        $checked_hists = $_POST['checked_hists'];
    } elseif (isset($_GET['checked_hists'])) {
        $checked_hists = $_GET['checked_hists'];
    }
    $checked_hists and deleMsg($checked_hists);
}

// �Â��o�[�W�����̌`���ł���f�[�^PHP�`���ip2_res_hist.dat.php, �^�u��؂�j�̏������ݗ������A
// dat�`���ip2_res_hist.dat, <>��؂�j�ɕϊ�����
P2Util::transResHistLogPhpToDat();

//======================================================================
// ���C��
//======================================================================

// ����DAT�ǂ�
if (!file_exists($_conf['p2_res_hist_dat']) or !$datlines = file($_conf['p2_res_hist_dat'])) {
    P2Util::printSimpleHtml('p2 - �������ݗ�����e�͋���ۂ̂悤�ł�');
    exit;
}

$datlines = array_map('rtrim', $datlines);

// �t�@�C���̉��ɋL�^����Ă�����̂��V����
$datlines = array_reverse($datlines);
$datlines_num = count($datlines);

$aResHist =& new ResHist();

// HTML�v�����g�p�ϐ�
$htm['checkall'] = '�S�Ẵ`�F�b�N�{�b�N�X�� 
<input type="button" onclick="hist_checkAll(true)" value="�I��"> 
<input type="button" onclick="hist_checkAll(false)" value="����">';

$htm['toolbar'] = <<<EOP
            �`�F�b�N�������ڂ�<input type="submit" name="submit" value="{$deletemsg_st}">
            �@{$htm['checkall']}
EOP;

//==================================================================
// �w�b�_HTML�\��
//==================================================================
//P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>{$ptitle}</title>
EOP;

// PC�p�\��
if (!$_conf['ktai']) {
    include_once './style/style_css.inc';
    include_once './style/read_css.inc';

    echo <<<EOSCRIPT
    <script type="text/javascript" src="js/basic.js?v=20061209"></script>
    <script type="text/javascript" src="js/respopup.js"></script>
    
    <script type="text/javascript"> 
    function hist_checkAll(mode) { 
        if (!document.getElementsByName) { 
            return; 
        } 
        var checkboxes = document.getElementsByName('checked_hists[]'); 
        var cbnum = checkboxes.length; 
        for (var i = 0; i < cbnum; i++) { 
            checkboxes[i].checked = mode; 
        } 
    }
    addLoadEvent(function() {
        gIsPageLoaded = true;
    });
    </script> 
EOSCRIPT;
}

echo <<<EOP
</head>
<body>\n
EOP;

P2Util::printInfoHtml();

// �g�їp�\��
if ($_conf['ktai']) {
    echo "{$ptitle}<br>";
    echo '<div id="header" name="header">';
    $aResHist->showNaviK('header', $datlines_num);
    echo " <a {$_conf['accesskey']}=\"8\" href=\"#footer\"{$_conf['k_at_a']}>8.��</a><br>";
    echo "</div>";
    echo "<hr>";

// PC�p�\��
} else {
    echo <<<EOP
<form method="POST" action="./read_res_hist.php" target="_self" onSubmit="if (gIsPageLoaded) {return true;} else {alert('�܂��y�[�W��ǂݍ��ݒ��ł��B����������Ƒ҂��ĂˁB'); return false;}">
EOP;

    echo <<<EOP
<table id="header" width="100%" style="padding:0px 10px 0px 0px;">
    <tr>
        <td>
            <h3 class="thread_title">{$ptitle}</h3>
        </td>
        <td align="right">{$htm['toolbar']}</td>
        <td align="right" style="padding-left:12px;"><a href="#footer">��</a></td>
    </tr>
</table>\n
EOP;
}


//==================================================================
// ���X�L�� HTML�\��
//==================================================================
if ($_conf['ktai']) {
    $aResHist->printArticlesHtmlK($datlines);
} else {
    $aResHist->printArticlesHtml($datlines);
}

//==================================================================
// �t�b�^HTML�\��
//==================================================================
// �g�їp�\��
if ($_conf['ktai']) {
    echo '<div id="footer" name="footer">';
    $aResHist->showNaviK('footer', $datlines_num);
    echo " <a {$_conf['accesskey']}=\"2\" href=\"#header\"{$_conf['k_at_a']}>2.��</a><br>";
    echo "</div>";
    echo "<p>{$_conf['k_to_index_ht']}</p>";

// PC�p�\��
} else {
    echo "<hr>";
    echo <<<EOP
<table id="footer" width="100%" style="padding:0px 10px 0px 0px;">
    <tr>
        <td align="right">{$htm['toolbar']}</td>
        <td align="right" style="padding-left:12px;"><a href="#header">��</a></td>
    </tr>
</table>\n
EOP;
}

if (!$_conf['ktai']) {
    echo '</form>'."\n";
}

echo '</body></html>';

