<?php
/*
    p2 -  ���j���[
    �t���[��������ʁA�������� PC�p
    
    menu.php, menu_side.php ���ǂݍ��܂��
*/

require_once P2_LIB_DIR . '/BrdCtl.php';
require_once P2_LIB_DIR . '/ShowBrdMenuPc.php';

$_login->authorize(); // ���[�U�F��


// {{{ �ϐ��ݒ�

// menu_side.php �� URL�B�i���΃p�X�w��͂ł��Ȃ��悤���j
$menu_side_url = dirname(P2Util::getMyUrl()) . '/menu_side.php';

BrdCtl::parseWord(); // set $GLOBALS['word']

// }}}
// {{{ �O����

// ���C�ɔ̒ǉ��E�폜
if (isset($_GET['setfavita'])) {
    require_once P2_LIB_DIR . '/setfavita.inc.php';
    setFavIta();
}

// }}}

//================================================================
// ���C��
//================================================================
$aShowBrdMenuPc = new ShowBrdMenuPc;

//============================================================
// �w�b�_HTML�\��
//============================================================
$reloaded_time = date('n/j G:i:s'); // �X�V����
$ptitle = 'p2 - menu';

P2Util::headerNoCache();
P2View::printDoctypeTag();
?>
<html lang="ja">
<head>
<?php
P2View::printExtraHeadersHtml();

// �����X�V meta refresh�^�O
_printMetaRereshHtml()
?>
	<title><?php eh($ptitle); ?></title>
	<base target="subject">
<?php
P2View::printIncludeCssHtml('style');
P2View::printIncludeCssHtml('menu');
?>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<script type="text/javascript" src="js/showhide.js"></script>
<?php
_printHeaderJs();
?>
</head><body>
<?php
P2Util::printInfoHtml();

if (!empty($sidebar)) {
    ?>
<p><a href="index.php?sidebar=1" target="_content">p2 - 2�y�C���\��</a></p>
<?php
}

if ($_conf['enable_menu_new']) {
    $shownew_atag = P2View::tagA(
        P2Util::buildQueryUri($_SERVER['SCRIPT_NAME'], array('shownew' => '1')),
        '�X�V',
        array('target' => '_self')
    );
    echo <<<EOP
$reloaded_time <span style="white-space:nowrap;">[$shownew_atag]</span>
EOP;
}

// ���C�ɔ�HTML�\������
$aShowBrdMenuPc->printFavItaHtml();

//==============================================================
// ���ʂ�HTML�\��
//==============================================================

?>
<div class="menu_cate"><b><a class="menu_cate" href="javascript:void(0);" onClick="showHide('c_spacial', 'itas_hide');" target="_self">����</a></b><br>
    <div class="itas" id="c_spacial">
<?php

// �V������\������ꍇ
if ($_conf['enable_menu_new'] == 1 and !empty($_GET['shownew'])) {
    
    ?>�@<?php echo _getRecentNewLinkHtml();?><br><?php
    
    ob_flush(); flush();
    
    list($matome_i, $shinchaku_num) = _initMenuNewSp('fav');    // �V�������������擾
    $id = 'sp' . $matome_i;
    echo <<<EOP
    �@<a href="{$_conf['subject_php']}?spmode=fav&amp;norefresh=1" onClick="chMenuColor('{$id}');" accesskey="{$_conf['pc_accesskey']['setfav']}">���C�ɃX��</a> (<a href="{$_conf['read_new_php']}?spmode=fav" target="read" id="un{$id}" onClick="chUnColor('{$id}');"{$class_newres_num}>{$shinchaku_num}</a>)<br>
EOP;
    ob_flush(); flush();

    list($matome_i, $shinchaku_num) = _initMenuNewSp('res_hist');    // �V�������������擾
    $id = 'sp' . $matome_i;
    echo <<<EOP
    �@<a href="{$_conf['subject_php']}?spmode=res_hist&amp;norefresh=1" onClick="chMenuColor('{$id}');">��������</a> <a href="read_res_hist.php" target="read">���O</a> (<a href="{$_conf['read_new_php']}?spmode=res_hist" target="read" id="un{$id}" onClick="chUnColor('{$id}');"{$class_newres_num}>{$shinchaku_num}</a>)<br>
EOP;
    ob_flush(); flush();

// �V������\�����Ȃ��ꍇ
} else {
    echo <<<EOP
    �@<a href="{$_conf['subject_php']}?spmode=recent&amp;norefresh=1" accesskey="{$_conf['pc_accesskey']['recent']}">�ŋߓǂ񂾃X��</a><br>
    �@<a href="{$_conf['subject_php']}?spmode=fav&amp;norefresh=1" accesskey="{$_conf['pc_accesskey']['setfav']}">���C�ɃX��</a><br>
    �@<a href="{$_conf['subject_php']}?spmode=res_hist&amp;norefresh=1">��������</a> (<a href="./read_res_hist.php" target="read">���O</a>)<br>
EOP;
}

?>
    �@<a href="<?php eh($_conf['subject_php']); ?>?spmode=palace&amp;norefresh=1" title="DAT���������X���p�̂��C�ɓ���">�X���̓a��</a><br>
    �@<a href="setting.php">���O�C���Ǘ�</a><br>
    �@<a href="<?php eh($_conf['editpref_php']) ?>">�ݒ�Ǘ�</a><br>
    �@<a href="http://find.2ch.net/" target="_blank" title="find.2ch.net">2ch����</a>
    </div>
</div>
<?php

//==============================================================
// �J�e�S���Ɣ�HTML�\��
//==============================================================
$brd_menus = BrdCtl::readBrdMenus();

// {{{ �������[�h�������

if (strlen($GLOBALS['word'])) {

    $msg_ht =  '<p>';
    if (empty($GLOBALS['ita_mikke']['num'])) {
        if (empty($GLOBALS['threti_match_ita_num'])) {
            $msg_ht .=  sprintf('"%s"���܂ޔ͌�����܂���ł����B', hs($word));
        }
    } else {
        $msg_ht .= sprintf('"%s"���܂ޔ� %shit!', hs($word), hs($GLOBALS['ita_mikke']['num']));
        
        // �������ʂ���Ȃ�A�����Ŕꗗ���J��
        if ($GLOBALS['ita_mikke']['num'] == 1) {
            $msg_ht .= '�i�����I�[�v�������j';
            
            $location_uri = P2Util::buildQueryUri(
                $_conf['subject_php'],
                array(
                    'host' => $GLOBALS['ita_mikke']['host'],
                    'bbs'  => $GLOBALS['ita_mikke']['bbs'],
                    'itaj_en' => $GLOBALS['ita_mikke']['itaj_en']
                )
            );
            $msg_ht .= <<<EOP
<script type="text/javascript">
<!--
    parent.subject.location.href="{$location_uri}";
// -->
</script>
EOP;
        }
    }
    $msg_ht .= '</p>';
    
    P2Util::pushInfoHtml($msg_ht);
}

// }}}

P2Util::printInfoHtml();

// �����t�H�[����HTML�\��
?>
<form method="GET" action="<?php eh($_SERVER['SCRIPT_NAME']); ?>" accept-charset="<?php eh($_conf['accept_charset']); ?>" target="_self">
    <input type="hidden" name="detect_hint" value="����">
    <p>
        <input type="text" id="word" name="word" value="<?php eh($word); ?>" size="14">
        <input type="submit" name="submit" value="����">
    </p>
</form>
<?php

// �J�e�S�����j���[��HTML�\��
if ($brd_menus) {
    foreach ($brd_menus as $a_brd_menu) {
        $aShowBrdMenuPc->printBrdMenu($a_brd_menu->categories);
    }
}


// {{{ �t�b�^HTML��\��

// for Mozilla Sidebar
if (empty($sidebar)) {
    ?>
<script type="text/JavaScript">
<!--
if ((typeof window.sidebar == "object") && (typeof window.sidebar.addPanel == "function")) {
    document.writeln("<p><a href=\"javascript:void(0);\" onClick=\"addSidebar('p2 Menu', '<?php eh($menu_side_url); ?>');\">p2 Menu�� Sidebar �ɒǉ�</a></p>");
}
-->
</script>
<?php
}

?>
</body></html>
<?php

// }}}



//==================================================================================
// �֐� �i���̃t�@�C�����ł̂ݗ��p�j
//==================================================================================
/**
 * spmode�p��menu�̐V����������������
 *
 * @return  array
 */
function _initMenuNewSp($spmode_in)
{
    global $_conf, $STYLE;
    global $class_newres_num;
    static $matome_i_ = 1;
    
    $matome_i_++;
    
    $host = '';
    $bbs  = '';
    $spmode = $spmode_in;
    
    include './subject_new.php';    // $shinchaku_num, $_newthre_num ���Z�b�g�����
    
    if ($shinchaku_num > 0) {
        $class_newres_num = ' class="newres_num"';
    } else {
        $class_newres_num = ' class="newres_num_zero"';
    }
    
    return array($matome_i_, $shinchaku_num);
}

/**
 * @return  string  HTML
 */
function _getRecentNewLinkHtml()
{
    global $_conf;
    
    list($matome_i, $shinchaku_num) = _initMenuNewSp('recent'); // �V�������������擾
    
    $id = "sp{$matome_i}";
    
    $recent_atag = P2View::tagA(
        P2Util::buildQueryUri(
            $_conf['subject_php'],
            array(
                'spmode' => 'recent',
                'norefresh' => '1'
            )
        ),
        '�ŋߓǂ񂾃X��',
        array(
            'onClick' => "chMenuColor('{$id}');",
            'accesskey' => $_conf['pc_accesskey']['recent']
        )
    );

    $recent_new_attrs = array(
        'id'      => "un$id",
        'onClick' => "chUnColor('$id');",
        'target'  => 'read'
    );
    
    if ($shinchaku_num > 0) {
        $recent_new_attrs['class'] = 'newres_num';
    } else {
        $recent_new_attrs['class'] = 'newres_num_zero';
    }
    
    $recent_new_atag = P2View::tagA(
        P2Util::buildQueryUri($_conf['read_new_php'], array('spmode' => 'recent')),
        hs($shinchaku_num),
        $recent_new_attrs
    );
    
    return "$recent_atag ($recent_new_atag)";
}

/**
 * �����X�V meta refresh�^�O
 *
 * @return  void
 */
function _printMetaRereshHtml()
{
    global $_conf;
    
    if ($_conf['menu_refresh_time']) {
        $refresh_time_s = $_conf['menu_refresh_time'] * 60;
        $qs = array(
            'shownew'   => 1,
            UA::getQueryKey() => UA::getQueryValue()
        );
        if (defined('SID') && strlen(SID)) {
            $qs[session_name()] = session_id();
        }
        $refresh_url = P2Util::buildQueryUri(P2Util::getMyUrl(), $qs);
        ?><meta http-equiv="refresh" content="<?php eh($refresh_time_s) ?>;URL=<?php eh($refresh_url); ?>">
        <?php
    }
}

/**
 * �w�b�_����JavaScript��HTML�v�����g����
 *
 * @return  void
 */
function _printHeaderJs()
{
    global $STYLE;
?>
	<script language="JavaScript">
	<!--
	function addSidebar(title, url)
	{
		if ((typeof window.sidebar == "object") && (typeof window.sidebar.addPanel == "function")) {
			window.sidebar.addPanel(title, url, '');
		} else {
			goNetscape();
		}
	}

	function goNetscape()
	{
		// var rv = window.confirm ("This page is enhanced for use with Netscape 7.  " + "Would you like to upgrade now?");
		var rv = window.confirm ("���̃y�[�W�� Netscape 7 �p�Ɋg������Ă��܂�.  " + "�������A�b�v�f�[�g���܂���?");
		if (rv) {
			document.location.href = "http://home.netscape.com/ja/download/download_n6.html";
		}
	}

	function chUnColor(id)
	{
		var unid = 'un'+id;
		document.getElementById(unid).style.color = "<?php echo $STYLE['menu_color']; ?>";
	}

	function chMenuColor(id)
	{
		var newthreid = 'newthre'+id;
		if (document.getElementById(newthreid)) {
			document.getElementById(newthreid).style.color = "<?php echo $STYLE['menu_color']; ?>";
		}
		var unid = 'un'+id;
		document.getElementById(unid).style.color = "<?php echo $STYLE['menu_color']; ?>";
	}

	function confirmSetFavIta(itaj)
	{
		return window.confirm('�u' + itaj + '�v�����C�ɔ���O���܂����H');
	}

	// @see  showhide.js
	// ���炩���߉B���Ă����̂�JavaScript�L�����̂�
	if (document.getElementById) {
		document.writeln('<style type="text/css" media="all">');
		document.writeln('<!--');
		document.writeln('.itas_hide{ display:none; }');
		document.writeln('-->');
		document.writeln('</style>');
	}
	// -->
	</script>
<?php
}
