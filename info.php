<?php
/*
	p2 - �X���b�h���E�B���h�E
*/

include_once './conf/conf.inc.php';  // ��{�ݒ�t�@�C��
require_once './p2util.class.php';	// p2�p�̃��[�e�B���e�B�N���X
require_once './thread.class.php'; // �X���b�h�N���X
require_once './filectl.class.php';
require_once './dele.inc.php';	// �폜�����p�̊֐��S

authorize(); // ���[�U�F��

//================================================================
// ���ϐ��ݒ�
//================================================================
$_info_msg_ht = "";

isset($_GET['host']) and $host = $_GET['host']; // "pc.2ch.net"
isset($_GET['bbs']) and $bbs = $_GET['bbs']; // "php"
isset($_GET['key']) and $key = $_GET['key']; // "1022999539"
isset($_GET['ttitle_en']) and $ttitle_en = $_GET['ttitle_en'];

// popup 0(false), 1(true), 2(true, �N���[�Y�^�C�}�[�t)
if (!empty($_GET['popup'])) { $popup_ht = "&amp;popup=1"; }

// �ȉ��ǂꂩ����Ȃ��Ă��_���o��
if (empty($host) || empty($bbs) || empty($key)) {
	die('p2 error: ����������������܂���B');
}

//================================================================
// ������ȑO�u����
//================================================================

// ���폜
if (!empty($_GET['dele']) && $key && $host && $bbs) {
	$r = deleteLogs($host, $bbs, array($key));
	//echo $r;
	if (empty($r)) {
		$title_msg = "�~ ���O�폜���s";
		$info_msg = "�~ ���O�폜���s";
	} elseif ($r == 1) {
		$title_msg = "�� ���O�폜����";
		$info_msg = "�� ���O�폜����";
	} elseif ($r == 2) {
		$title_msg = "- ���O�͂���܂���ł���";
		$info_msg = "- ���O�͂���܂���ł���";
	}
}

// �����폜
if (!empty($_GET['offrec']) && $key && $host && $bbs) {
	$r1 = offRecent($host, $bbs, $key);
	$r2 = offResHist($host, $bbs, $key);
	if ((empty($r1)) or (empty($r2))) {
		$title_msg = "�~ �����������s";
		$info_msg = "�~ �����������s";
	} elseif ($r1 == 1 || $r2 == 1) {
		$title_msg = "�� ������������";
		$info_msg = "�� ������������";
	} elseif ($r1 == 2 && $r2 == 2) {
		$title_msg = "- �����ɂ͂���܂���ł���";
		$info_msg = "- �����ɂ͂���܂���ł���";
	}

// ���C�ɓ���X���b�h
} elseif (isset($_GET['setfav']) && $key && $host && $bbs) {
	include_once 'setfav.inc.php';
	setFav($host, $bbs, $key, $_GET['setfav']);

// �a������
} elseif (isset($_GET['setpal']) && $key && $host && $bbs) {
	include_once 'setpalace.inc.php';
	setPal($host, $bbs, $key, $_GET['setpal']);

// �X���b�h���ځ[��
} elseif (isset($_GET['taborn']) && $key && $host && $bbs) {
	include_once 'settaborn.inc.php';
	settaborn($host, $bbs, $key, $_GET['taborn']);
}

//=================================================================
// �����C��
//=================================================================

$aThread = new Thread;

// host�𕪉�����idx�t�@�C���̃p�X�����߂�
$aThread->setThreadPathInfo($host, $bbs, $key);
$key_line = $aThread->getThreadInfoFromIdx();
$aThread->getDatBytesFromLocalDat(); // $aThread->length ��set

if (!$aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs)) {
	$aThread->itaj = $aThread->bbs;
}
$hc['itaj'] = $aThread->itaj;

if (!$aThread->ttitle) {
	if (isset($ttitle_en)) {
		$aThread->setTtitle(base64_decode($ttitle_en));
	} else {
		$aThread->setTitleFromLocal();
	}
}
if (!$ttitle_en) {
	if ($aThread->ttitle) {
		$ttitle_en = base64_encode($aThread->ttitle);
		//$ttitle_urlen = rawurlencode($ttitle_en);
	}
}
if ($ttitle_en) { $ttitle_en_ht = "&amp;ttitle_en={$ttitle_en}"; }

if ($aThread->ttitle_hc) {
	$hc['ttitle_name'] = $aThread->ttitle_hc;
} else {
	$hc['ttitle_name'] = "�X���b�h�^�C�g�����擾";
}

// favlist �`�F�b�N =====================================
$favlines = @file($_conf['favlist_file']); // ���C�ɃX�����X�g �Ǎ�
if ($favlines) {
	foreach ($favlines as $l) {
		$favarray = explode('<>', rtrim($l));
		if ($aThread->key == $favarray[1]) {
			$aThread->fav = "1";
			if ($favarray[0]) {
				$aThread->setTtitle($favarray[0]);
			}
			break;
		}
	}
}

if ($aThread->fav) {
	$favmark = "<span class=\"fav\">��</span>";
} else {
	$favmark = "<span class=\"fav\">+</span>";
}
if ($aThread->fav) {
	$favdo = 0;
} else {
	$favdo = 1;
}

$fav_ht = <<<EOP
<a href="info.php?host={$aThread->host}&amp;bbs={$aThread->bbs}&amp;key={$aThread->key}&amp;setfav={$favdo}{$popup_ht}{$ttitle_en_ht}{$_conf['k_at_a']}">{$favmark}</a>
EOP;

// palace �`�F�b�N =========================================
$palace_idx = $_conf['pref_dir']. '/p2_palace.idx';

$pallines = @file($palace_idx); // �a������X�����X�g �Ǎ�
if ($pallines) {
	foreach ($pallines as $l) {
		$palarray = explode('<>', rtrim($l));
		if ($aThread->key == $palarray[1]) {
			$isPalace = true;
			if ($palarray[0]) {
				$aThread->setTtitle($palarray[0]);
			}
			break;
		}
	}
}
if ($isPalace) {
	$paldo = 0;
} else {
	$paldo = 1;
}
$pal_a_ht = "info.php?host={$aThread->host}&amp;bbs={$aThread->bbs}&amp;key={$aThread->key}&amp;setpal={$paldo}{$popup_ht}{$ttitle_en_ht}{$_conf['k_at_a']}";

if ($isPalace) {
	$pal_ht = "<a href=\"{$pal_a_ht}\">��</a>";
} else {
	$pal_ht = "<a href=\"{$pal_a_ht}\">+</a>";
}

// ���X���b�h���ځ[��`�F�b�N =====================================
// �X���b�h���ځ[�񃊃X�g�Ǎ�
$datdir_host = P2Util::datdirOfHost($host);
$tabornlist = @file("{$datdir_host}/{$bbs}/p2_threads_aborn.idx");
if ($tabornlist) {
	foreach ($tabornlist as $l) {
		$tarray = explode('<>', rtrim($l));
		if ($aThread->key == $tarray[1]) {
			$isTaborn = true;
			break;
		}
	}
}

$taborndo_title_at = '';
if (!empty($isTaborn)) {
	$tastr1 = "���ځ[��";
	$tastr2 = "���ځ[���������";
	$taborndo = 0;
} else {
	$tastr1 = "�ʏ�";
	$tastr2 = "���ځ[�񂷂�";
	$taborndo = 1;
	if (empty($_conf['ktai'])) {
		$taborndo_title_at = ' title="�X���b�h�ꗗ�Ŕ�\���ɂ��܂�"';
	}
}

$taborn_ht = <<<EOP
{$tastr1} [<a href="info.php?host={$aThread->host}&bbs={$aThread->bbs}&key={$aThread->key}&amp;taborn={$taborndo}{$popup_ht}{$ttitle_en_ht}{$_conf['k_at_a']}"{$taborndo_title_at}>{$tastr2}</a>]
EOP;


// ���O����Ȃ��t���O�Z�b�g ===========
if (file_exists($aThread->keydat) or file_exists($aThread->keyidx)) { $existLog = true; }

//=================================================================
// ��HTML�v�����g
//=================================================================
if (!empty($_conf['ktai'])) {
	$target_read_at = ' target="read"';
	$target_sb_at = ' target="sbject"';
}

$motothre_url = $aThread->getMotoThread($GLOBALS['ls']);
if ($title_msg) {
	$hc['title'] = $title_msg;
} else {
	$hc['title'] = "info - {$hc['ttitle_name']}";
}

$hd = array_map('htmlspecialchars', $hc);


P2Util::header_nocache();
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOHEADER
<html>
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<title>{$hd['title']}</title>\n
EOHEADER;

if (!$_conf['ktai']) {
	// echo "<!-- ".$key_line." -->\n";
	@include("./style/style_css.inc"); // ��{�X�^�C���V�[�g�Ǎ�
	@include("./style/info_css.inc");
}

if ($_GET['popup'] == 2) {
	echo <<<EOSCRIPT
	<script type="text/javascript" src="js/closetimer.js"></script>
EOSCRIPT;
	$body_onload = <<<EOP
 onLoad="startTimer(document.getElementById('timerbutton'))"
EOP;
}

echo <<<EOP
</head>
<body{$body_onload}>
EOP;

echo $_info_msg_ht;
$_info_msg_ht = "";

echo "<p>\n";
echo "<b><a class=\"thre_title\" href=\"{$_conf['read_php']}?host={$aThread->host}&amp;bbs={$aThread->bbs}&amp;key={$aThread->key}{$_conf['k_at_a']}\"{$target_read_at}>{$hd['ttitle_name']}</a></b>\n";
echo "</p>\n";

if ($_conf['ktai']) {
	if ($info_msg) {
		echo "<p>".$info_msg."</p>\n";
	}
}

if (checkRecent($aThread->host, $aThread->bbs, $aThread->key) or checkResHist($aThread->host, $aThread->bbs, $aThread->key)) {
	$offrec_ht = " / [<a href=\"info.php?host={$aThread->host}&amp;bbs={$aThread->bbs}&amp;key={$aThread->key}&amp;offrec=true{$popup_ht}{$ttitle_en_ht}{$_conf['k_at_a']}\" title=\"���̃X�����u�ŋߓǂ񂾃X���v�Ɓu�������ݗ����v����O���܂�\">��������O��</a>]";
}

if (!$_conf['ktai']) {
	echo "<table cellspacing=\"0\">\n";
}
print_info_line("���X��", "<a href=\"{$motothre_url}\"{$target_read_at}>{$motothre_url}</a>");
if (!$_conf['ktai']) {
	print_info_line("�z�X�g", $aThread->host);
}
print_info_line("��", "<a href=\"{$_conf['subject_php']}?host={$aThread->host}&amp;bbs={$aThread->bbs}{$_conf['k_at_a']}\"{$target_sb_at}>{$hd['itaj']}</a>");
if (!$_conf['ktai']) {
	print_info_line("key", $aThread->key);
}
if ($existLog) {
	print_info_line("���O", "���� [<a href=\"info.php?host={$aThread->host}&amp;bbs={$aThread->bbs}&amp;key={$aThread->key}&amp;dele=true{$popup_ht}{$ttitle_en_ht}{$_conf['k_at_a']}\">�폜����</a>]{$offrec_ht}");
} else {
	print_info_line("���O", "���擾{$offrec_ht}");
}
if ($aThread->gotnum) {
	print_info_line("�������X��", $aThread->gotnum);
} elseif (!$aThread->gotnum and $existLog) {
	print_info_line("�������X��", "0");
} else {
	print_info_line("�������X��", "-");
}
if (!$_conf['ktai']) {
	if (file_exists($aThread->keydat)) {
		if ($aThread->length) {
			print_info_line("dat�T�C�Y", $aThread->length.' �o�C�g');
		}
		print_info_line("dat", $aThread->keydat);
	} else {
		print_info_line("dat", "-");
	}
	if (file_exists($aThread->keyidx)) {
		print_info_line("idx", $aThread->keyidx);
	} else {
		print_info_line("idx", "-");
	}
}

print_info_line("���C�ɃX��", $fav_ht);
print_info_line("�a������", $pal_ht);
print_info_line("�\��", $taborn_ht);

if (!$_conf['ktai']) {
	echo "</table>\n";
}

if (!$_conf['ktai']) {
	if ($info_msg) {
		echo "<span class=\"infomsg\">".$info_msg."</span>\n";
	} else {
		echo "�@\n";
	}
}

// ����{�^��
if ($_GET['popup']) {
	echo '<div align="center">';
	if ($_GET['popup'] == 1) {
		echo '<form action=""><input type="button" value="�E�B���h�E�����" onClick="window.close();"></form>';
	} elseif ($_GET['popup'] == 2) {
		echo <<<EOP
	<form action=""><input id="timerbutton" type="button" value="Close Timer" onClick="stopTimer(document.getElementById('timerbutton'))"></form>
EOP;
	}
	echo "</div>\n";
}

if ($_conf['ktai']) {
	echo '<hr>'.$_conf['k_to_index_ht'];
}

echo '</body></html>';

//===============================================
// ���֐�
//===============================================
function print_info_line($s, $c)
{
	global $_conf;
	
	if ($_conf['ktai']) {
		echo "{$s}: {$c}<br>";
	} else {
		echo "<tr><td class=\"tdleft\" nowrap><b>{$s}</b>&nbsp;</td><td class=\"tdcont\">{$c}</td></tr>\n";
	}
}

?>