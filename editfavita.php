<?php
// p2 -  ���C�ɓ���ҏW

include_once './conf.inc.php';  // ��{�ݒ�
require_once './filectl.class.php';

authorize(); //���[�U�F��

//�ϐ�=============
$_info_msg_ht = "";

//================================================================
//����ȑO�u����
//================================================================

// ���C�ɔ̒ǉ��E�폜�A���ёւ�
if (isset($_GET['setfavita']) or isset($_POST['setfavita'])) {
	include("./setfavita.inc");
}

// �v�����g�p�ϐ�======================================================

// ���C�ɔǉ��t�H�[��=================================================
$add_favita_form_ht = <<<EOFORM
<form method="POST" action="{$_SERVER['PHP_SELF']}" accept-charset="{$_conf['accept_charset']}" target="_self">
	<input type="hidden" name="detect_hint" value="����">
	<p>
		URL: <input type="text" id="url" name="url" value="http://" size="48">
		��: <input type="text" id="itaj" name="itaj" value="" size="16">
		<input type="hidden" id="setfavita" name="setfavita" value="1">
		<input type="submit" name="submit" value="�V�K�ǉ�">
	</p>
</form>\n
EOFORM;

//================================================================
// �w�b�_
//================================================================
header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html lang="ja">
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<title>p2 - ���C�ɔ̕��ёւ�</title>
EOP;

@include("./style/style_css.inc");
@include("./style/editfavita_css.inc");

echo <<<EOP
</head>
<body>
EOP;

echo $_info_msg_ht;
$_info_msg_ht = "";

//================================================================
// ���C������HTML�\��
//================================================================

//================================================================
// ���C�ɔ�
//================================================================

// favita�t�@�C�����Ȃ���ΐ���
FileCtl::make_datafile($_conf['favita_path'], $_conf['favita_perm']);
// favita�ǂݍ���
$lines= file($_conf['favita_path']);


echo <<<EOP
<div><b>���C�ɔ̕ҏW</b> [<a href="{$_SERVER['PHP_SELF']}" onClick='parent.menu.location.href="{$_conf['menu_php']}?nr=1"'>���j���[���X�V</a>]</div>
EOP;

echo $add_favita_form_ht;

if ($lines) {
	echo "<table>";
	foreach ($lines as $l) {
		$l = rtrim($l);
		if (preg_match("/^\t?(.+)\t(.+)\t(.+)$/", $l, $matches)) {
			$itaj = rtrim($matches[3]);
			$itaj_en = base64_encode($itaj);
			$host = $matches[1];
			$bbs = $matches[2];
			$itaj_view = htmlspecialchars($itaj);
			$itaj_ht = "&amp;itaj_en=".$itaj_en;
			echo <<<EOP
			<tr>
			<td><a href="{$_SERVER['PHP_SELF']}?host={$host}&bbs={$bbs}&setfavita=0" class="fav" title="�u{$itaj_view}�v�����C�ɔ���O��">��</a></td>
			<td><a href="{$_conf['subject_php']}?host={$host}&bbs={$bbs}">{$matches[3]}</a></td>
			<td>[ <a class="te" href="{$_SERVER['PHP_SELF']}?host={$host}&bbs={$bbs}{$itaj_ht}&setfavita=top" title="��ԏ�Ɉړ�">��</a></td>
			<td><a class="te" href="{$_SERVER['PHP_SELF']}?host={$host}&bbs={$bbs}{$itaj_ht}&setfavita=up" title="���Ɉړ�">��</a></td>
			<td><a class="te" href="{$_SERVER['PHP_SELF']}?host={$host}&bbs={$bbs}{$itaj_ht}&setfavita=down" title="����Ɉړ�">��</a></td>
			<td><a class="te" href="{$_SERVER['PHP_SELF']}?host={$host}&bbs={$bbs}{$itaj_ht}&setfavita=bottom" title="��ԉ��Ɉړ�">��</a> ]</td>
			</tr>
EOP;
		}
	}
	echo "</table>";
}

//================================================================
// �t�b�^HTML�\��
//================================================================

echo <<<EOP
</body></html>
EOP;

?>