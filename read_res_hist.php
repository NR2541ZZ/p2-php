<?php
// p2 - �������ݗ��� ���X���e�\��
// �t���[��������ʁA�E������

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/res_hist.class.php';
require_once P2_LIB_DIR . '/read_res_hist.inc.php';
require_once P2_LIB_DIR . '/P2View.php';

$_login->authorize(); // ���[�U�F��

//======================================================================
// �ϐ�
//======================================================================
$newtime = date('gis');

$ptitle = '�������񂾃��X�̋L�^';
$deletemsg_st = '�폜';

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

$karappoMsgHtml = 'p2 - �������ݗ�����e�͋���ۂ̂悤�ł��B';
if (!$_conf['res_write_rec']) {
    $karappoMsgHtml .= sprintf(
        '<p>���݁A�������ݓ��e���O�͋L�^���Ȃ��ݒ�ɂȂ��Ă��܂��B<br>�ݒ�́A%s�̃y�[�W�ŕύX�\�ł��B</p>',
        P2View::tagA(
            P2Util::buildQueryUri('edit_conf_user.php',
                array(UA::getQueryKey() => UA::getQueryValue())
            ),
            hs('�ݒ�ҏW'),
            array('target' => 'subject')
        )
    );
}

// ����DAT�ǂ�
if (!file_exists($_conf['p2_res_hist_dat'])) {
    P2Util::printSimpleHtml($karappoMsgHtml);
    exit;
}

$res_hist_dat_size = filesize($_conf['p2_res_hist_dat']);
$logSizeSt = _getReadableSize($res_hist_dat_size);
$maxLogSize = 0;//1024*1024*10;
$maxLogSizeSt = _getReadableSize($maxLogSize);
if ($maxLogSize and $res_hist_dat_size > $maxLogSize) {
    P2Util::printSimpleHtml(
        sprintf(
            '�������݃��O�e�ʁi%s/%s�j���傫�߂��邽�߁A�\���ł��܂���B<br>
            %s�̃y�[�W���A�������݃��O�̈ꊇ�폜���s���ĉ������B',
            hs($logSizeSt), hs($maxLogSizeSt),
            P2View::tagA($_conf['editpref_php'], hs('�ݒ�Ǘ�'), array('target' => 'subject'))
        )
    );
    exit;
}

if (false === $datlines = file($_conf['p2_res_hist_dat'])) {
    p2die('�������ݗ������O�t�@�C����ǂݍ��߂܂���ł���');

} elseif (!$datlines) {
    P2Util::printSimpleHtml($karappoMsgHtml);
    exit;
}

// [more] �����ŕ\���͈͂ɍ��킹�āAarray_slice()���Ă����������������S�����Ȃ�

// �t�@�C���̉��ɋL�^����Ă�����̂��V�����̂Ŕ��]������
$datlines = array_reverse($datlines);
$datlines_num = count($datlines);

$ResHist = new ResHist;

// HTML�v�����g�p�ϐ�
$toolbar_ht = <<<EOP
	�`�F�b�N�������ڂ�<input type="submit" name="submit" value="{$deletemsg_st}">
	�S�Ẵ`�F�b�N�{�b�N�X�� 
	<input type="button" onclick="hist_checkAll(true)" value="�I��"> 
	<input type="button" onclick="hist_checkAll(false)" value="����">
EOP;

$hr = P2View::getHrHtmlK();

//==================================================================
// �w�b�_HTML�\��
//==================================================================
//P2Util::headerNoCache();
P2View::printDoctypeTag();
?>
<html lang="ja">
<head>
<?php
P2View::printExtraHeadersHtml();
?>
<title><?php eh($ptitle); ?></title>
<?php

// PC�p�\��
if (UA::isPC()) {
    P2View::printIncludeCssHtml('style');
    P2View::printIncludeCssHtml('read');
    ?>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<script type="text/javascript" src="js/basic.js?v=20061206"></script>
<script type="text/javascript" src="js/respopup.js?v=20061206"></script>

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
<?php
}
?>
</head>
<body<?php echo P2View::getBodyAttrK(); ?>>
<?php

P2Util::printInfoHtml();

// �g�їp�\��
if (UA::isK()) {
    eh($ptitle); ?>
    <br>
    <div id="header" name="header">
    <?php
    $ResHist->showNaviK('header', $datlines_num);
    $atag = P2View::tagA(
        '#footer',
        hs($_conf['k_accesskey']['bottom'] . '.��'),
        array(
            $_conf['accesskey_for_k'] => $_conf['k_accesskey']['bottom']
        )
    );
    echo " $atag<br>";
    echo "</div>";
    echo $hr;

// PC�p�\��
} else {
    ?>
<form method="POST" action="./read_res_hist.php" target="_self" onSubmit="if (gIsPageLoaded) {return true;} else {alert('�܂��y�[�W��ǂݍ��ݒ��ł��B����������Ƒ҂��ĂˁB'); return false;}">
<input type="hidden" name="pageID" value="<?php ehi($_REQUEST['pageID']); ?>">

<table id="header" width="100%" style="padding:0px 10px 0px 0px;">
	<tr>
		<td>
			<h3 class="thread_title"><?php eh($ptitle); ?></h3>
		</td>
		<td><span style="font-size:small">�e�� <?php eh($logSizeSt) ?><?php if ($maxLogSize) {?>�i�ő�<?php eh($maxLogSizeSt) ?>�j<?php } ?></span></td>
		<td align="right"><?php echo $toolbar_ht; ?></td>
		<td align="right" style="padding-left:12px;"><a href="#footer">��</a></td>
	</tr>
</table>
<?php
}


//==================================================================
// ���X�L�� HTML�\��
//==================================================================
if (UA::isK()) {
    $ResHist->printArticlesHtmlK($datlines);
} else {
    $ResHist->printArticlesHtml($datlines);
}

//==================================================================
// �t�b�^HTML�\��
//==================================================================
// �g�їp�\��
if (UA::isK()) {
    ?><div id="footer" name="footer"><?php
    $ResHist->showNaviK('footer', $datlines_num);
    $atag = P2View::tagA(
        '#header',
        hs($_conf['k_accesskey']['above'] . '.��'),
        array(
            $_conf['accesskey_for_k'] => $_conf['k_accesskey']['above']
        )
    );
    echo " $atag<br>";
    echo "</div>";
    ?><p><?php
    echo P2View::getBackToIndexKATag();
    ?></p><?php

// PC�p�\��
} else {
    ?>
<hr>
<table id="footer" width="100%" style="padding:0px 10px 0px 0px;">
    <tr>
        <td align="right"><?php echo $toolbar_ht; ?></td>
        <td align="right" style="padding-left:12px;"><a href="#header">��</a></td>
    </tr>
</table>
<?php
}
if (UA::isPC()) {
    ?></form><?php
}
?>
</body></html>
<?php

exit;


//===================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//===================================================================
/**
 * �e�ʂ̒P�ʂ��o�C�g�\������ϊ����ĕ\������
 *
 * @param   integer  $size
 * @return  string
 */
function _getReadableSize($size)
{
   $units = array('B', 'KB', 'MB', 'GB', 'TB');
   $k = 1024;
   foreach ($units as $u) {
       $unit = $u;
       if ($size < $k) {
           break;
       }
       $size = $size / $k;
   }
   return ceil($size) . '' . $unit;
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
