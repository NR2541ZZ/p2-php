<?php
/*
	p2 - �X���b�h�\���X�N���v�g - �V���܂Ƃߓǂ�
	�t���[��������ʁA�E������
*/

include_once './conf/conf.inc.php'; // �ݒ�
require_once './p2util.class.php';	// p2�p�̃��[�e�B���e�B�N���X
require_once './threadlist.class.php'; // �X���b�h���X�g �N���X
require_once './thread.class.php'; // �X���b�h �N���X
require_once './threadread.class.php'; // �X���b�h���[�h �N���X
require_once './ngabornctl.class.php';

require_once './read_new.inc.php';

authorize(); // ���[�U�F��

// �܂Ƃ߂�݂̃L���b�V���ǂ�
if (!empty($_GET['cview'])) {
	$cnum = (isset($_GET['cnum'])) ? intval($_GET['cnum']) : NULL;
	if ($cont = getMatomeCache($cnum)) {
		echo $cont;
	} else {
		echo 'p2 error: �V���܂Ƃߓǂ݂̃L���b�V�����Ȃ���';
	}
	exit;
}

//==================================================================
// ���ϐ�
//==================================================================
if (isset($_conf['rnum_all_range']) and $_conf['rnum_all_range'] > 0) {
	$GLOBALS['rnum_all_range'] = $_conf['rnum_all_range'];
}

$sb_view = "shinchaku";
$newtime = date("gis");

$sid_q = (defined('SID')) ? '&amp;'.strip_tags(SID) : '';

//=================================================
// �̎w��
//=================================================
if (isset($_GET['host'])) { $host = $_GET['host']; }
if (isset($_POST['host'])) { $host = $_POST['host']; }
if (isset($_GET['bbs'])) { $bbs = $_GET['bbs']; }
if (isset($_POST['bbs'])) { $bbs = $_POST['bbs']; }
if (isset($_GET['spmode'])) { $spmode = $_GET['spmode']; }
if (isset($_POST['spmode'])) { $spmode = $_POST['spmode']; }

if ((!isset($host) || !isset($bbs)) && !isset($spmode)) {
	die('p2 error: �K�v�Ȉ������w�肳��Ă��܂���');
}

//=================================================
// ���ځ[��&NG���[�h�ݒ�ǂݍ���
//=================================================
$GLOBALS['ngaborns'] = NgAbornCtl::loadNgAborns();

//====================================================================
// �����C��
//====================================================================

register_shutdown_function('saveMatomeCache');

$read_new_html = '';
ob_start();

$aThreadList =& new ThreadList();

// ���ƃ��[�h�̃Z�b�g===================================
if ($spmode) {
	if ($spmode == "taborn" or $spmode == "soko") {
		$aThreadList->setIta($host, $bbs, P2Util::getItaName($host, $bbs));
	}
	$aThreadList->setSpMode($spmode);
	
} else {
	$aThreadList->setIta($host, $bbs, P2Util::getItaName($host, $bbs));

	// ���X���b�h���ځ[�񃊃X�g�Ǎ�
	$datdir_host = P2Util::datdirOfHost($host);
	$tabornlines = @file($datdir_host."/".$bbs."/p2_threads_aborn.idx");
	if ($tabornlines) {
		$ta_num = sizeOf($tabornlines);
		foreach ($tabornlines as $l) {
			$tarray = explode('<>', rtrim($l));
			$ta_keys[ $tarray[1] ] = true;
		}
	}
}

// ���\�[�X���X�g�Ǎ� ==================================
$lines = $aThreadList->readList();

// ���y�[�W�w�b�_�\�� ===================================
$ptitle_hd = htmlspecialchars($aThreadList->ptitle);
$ptitle_ht = "{$ptitle_hd} �� �V���܂Ƃߓǂ�";

if ($aThreadList->spmode) {
	$sb_ht = <<<EOP
		<a href="{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}" target="subject">{$ptitle_hd}</a>
EOP;
} else {
	$sb_ht = <<<EOP
		<a href="{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}" target="subject">{$ptitle_hd}</a>
EOP;
}

//include($read_header_inc);

P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOHEADER
<html lang="ja">
<head>
	{$_conf['meta_charset_ht']}
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<title>{$ptitle_ht}</title>
EOHEADER;

@include("style/style_css.inc"); //�X�^�C���V�[�g
@include("style/read_css.inc"); //�X�^�C���V�[�g

echo <<<EOHEADER
	<script type="text/javascript" src="js/basic.js"></script>
	<script type="text/javascript" src="js/respopup.js"></script>
	<script type="text/javascript" src="js/htmlpopup.js"></script>\n
EOHEADER;

echo <<<EOHEADER
	<script type="text/javascript">
	<!--
	gIsPageLoaded = false;
	// ���C�ɃZ�b�g�֐�
	function setFav(host, bbs, key, favdo, obj)
	{
		/*
		// �y�[�W�̓ǂݍ��݂��������Ă��Ȃ���΁A�Ȃɂ����Ȃ�
		if (!gIsPageLoaded) {
			return false;
		}
		*/
		
		var objHTTP = getXmlHttp();
		if (!objHTTP) {
			// alert("Error: XMLHTTP �ʐM�I�u�W�F�N�g�̍쐬�Ɏ��s���܂����B") ;
			// XMLHTTP�i�� obj.parentNode.innerHTML�j �ɖ��Ή��Ȃ珬����
			return OpenSubWin('info.php?host='+host+'&amp;bbs='+bbs+'&amp;key='+key+'&amp;setfav='+favdo+'&amp;popup=2',{$STYLE['info_pop_size']},0,0);
		}
		// �L���b�V�����p
		var now = new Date();
		// �����̕������ encodeURIComponent �ŃG�X�P�[�v����̂��悢
		query = 'host='+host+'&bbs='+bbs+'&key='+key+'&setfav='+favdo+'&nc='+now.getTime();
		url = 'httpcmd.php?' + query + '&cmd=setfav';	// �X�N���v�g�ƁA�R�}���h�w��
		objHTTP.open('GET', url, false);
		objHTTP.send(null);
		if (objHTTP.status != 200 || objHTTP.readyState != 4 && !objHTTP.responseText) {
			// alert("Error: XMLHTTP ���ʂ̎�M�Ɏ��s���܂���") ;
		}
		var res = objHTTP.responseText;
		var rmsg = "";
		if (res) {
			if (res == '1') {
				rmsg = '����';
			}
			if (rmsg) {
				if (favdo == '1') {
					nextset = '0';
					favmark = '��';
					favtitle = '���C�ɃX������O��';
				} else {
					nextset = '1';
					favmark = '+';
					favtitle = '���C�ɃX���ɒǉ�';
				}
				var favhtm = '<a href="info.php?host='+host+'&amp;bbs='+bbs+'&amp;key='+key+'&amp;setfav='+nextset+'" target="info" onClick="return setFav(\''+host+'\', \''+bbs+'\', \''+key+'\', \''+nextset+'\', this);" title="'+favtitle+'">���C��'+favmark+'</a>';
				obj.parentNode.innerHTML = favhtm;
			}
		}
		return false;
	}

	// ���O�폜�֐�
	function deleLog(query, obj)
	{
		/*
		// �y�[�W�̓ǂݍ��݊������Ă��Ȃ���΃����N��
		if (!gIsPageLoaded) {
			return true;
		}
		*/

		var objHTTP = getXmlHttp();
		
		if (!objHTTP) {
			// alert("Error: XMLHTTP �ʐM�I�u�W�F�N�g�̍쐬�Ɏ��s���܂����B") ;
			
			// XMLHTTP�i�� obj.parentNode.innerHTML�j �ɖ��Ή��Ȃ�ʏ탊���N�� // [better]�����̕����x�^�[
			return true;
		}

		// �L���b�V�����p
		var now = new Date();
		// �����̕������ encodeURIComponent �ŃG�X�P�[�v����̂��悢
		query = query + '&nc='+now.getTime();
		url = 'httpcmd.php?' + query + '&cmd=delelog';	// �X�N���v�g�ƁA�R�}���h�w��
		objHTTP.open('GET', url, false);
		objHTTP.send(null);
		if (objHTTP.status != 200 || objHTTP.readyState != 4 && !objHTTP.responseText) {
			// alert("Error: XMLHTTP ���ʂ̎�M�Ɏ��s���܂���") ;
		}
		var res = objHTTP.responseText;
		var rmsg = "";
		
		if (res) {
			// alert(res);
			if (res == '1') {
				rmsg = '����';
			} else if (res == '2') {
				rmsg = '�Ȃ�';
			}
			if (rmsg) {
				obj.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.style.filter = 'Gray()';	// IE ActiveX�p
				obj.parentNode.innerHTML = rmsg;
			}
		}

		return false;
	}
	
	function pageLoaded()
	{
		gIsPageLoaded = true;
		setWinTitle();
	}
	-->
	</script>\n
EOHEADER;

echo <<<EOP
</head>
<body onLoad="pageLoaded();">
<div id="popUpContainer"></div>\n
EOP;

echo $_info_msg_ht;
$_info_msg_ht = "";

//echo $ptitle_ht."<br>";

//==============================================================
// �����ꂼ��̍s���
//==============================================================

$linesize = sizeof($lines);

for ($x = 0; $x < $linesize ; $x++) {
	
	if (isset($GLOBALS['rnum_all_range']) and $GLOBALS['rnum_all_range'] <= 0) {
		break;
	}
	
	$l = $lines[$x];
	$aThread =& new ThreadRead();
	
	$aThread->torder = $x + 1;

	// ���f�[�^�ǂݍ���
	// spmode�Ȃ�
	if ($aThreadList->spmode) {
		switch ($aThreadList->spmode) {
	    case "recent": // ����
	        $aThread->getThreadInfoFromExtIdxLine($l);
	        break;
	    case "res_hist": // �������ݗ���
	        $aThread->getThreadInfoFromExtIdxLine($l);
	        break;
	    case "fav": // ���C��
	        $aThread->getThreadInfoFromExtIdxLine($l);
	        break;
		case "taborn":	// �X���b�h���ځ[��
	        $aThread->getThreadInfoFromExtIdxLine($l);
			$aThread->host = $aThreadList->host;
			$aThread->bbs = $aThreadList->bbs;
	        break;
		case "palace":	// �X���̓a��
	        $aThread->getThreadInfoFromExtIdxLine($l);
	        break;
		}
	// subject (not spmode)�̏ꍇ
	} else {
		$aThread->getThreadInfoFromSubjectTxtLine($l);
		$aThread->host = $aThreadList->host;
		$aThread->bbs = $aThreadList->bbs;
	}
	
	// host��bbs���s���Ȃ�X�L�b�v
	if (!($aThread->host && $aThread->bbs)) {
		unset($aThread);
		continue;
	}
	
	
	$aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);
	
	// �����X���b�h�f�[�^��idx����擾
	$aThread->getThreadInfoFromIdx();
		
	// ���V���̂�(for subject) =========================================
	if (!$aThreadList->spmode and $sb_view == "shinchaku" and !$_GET['word']) { 
		if ($aThread->unum < 1) {
			unset($aThread);
			continue;
		}
	}

	// ���X���b�h���ځ[��`�F�b�N =====================================
	if ($aThreadList->spmode != "taborn" and $ta_keys[$aThread->key]) { 
			unset($ta_keys[$aThread->key]);
			continue; // ���ځ[��X���̓X�L�b�v
	}

	// �� spmode(�a�����������)�Ȃ� ====================================
	if ($aThreadList->spmode && $sb_view != "edit") { 
		
		// subject.txt ����DL�Ȃ痎�Ƃ��ăf�[�^��z��Ɋi�[
		if (!$subject_txts["$aThread->host/$aThread->bbs"]) {
			$datdir_host = P2Util::datdirOfHost($aThread->host);
			$subject_url = "http://{$aThread->host}/{$aThread->bbs}/subject.txt";
			
			$subjectfile = "{$datdir_host}/{$aThread->bbs}/subject.txt";
			
			FileCtl::mkdir_for($subjectfile); // �f�B���N�g����������΍��
			if (!($word_fm and file_exists($subjectfile))) {
				P2Util::subjectDownload($subject_url, $subjectfile);
			}
			if (extension_loaded('zlib') and strstr($aThread->host, ".2ch.net")) {
				$subject_txts["$aThread->host/$aThread->bbs"] = @gzfile($subjectfile);
			} else {
				$subject_txts["$aThread->host/$aThread->bbs"] = @file($subjectfile);
			}
			
		}
		
		// ���X�����擾 =============================
		if ($subject_txts["$aThread->host/$aThread->bbs"]) {
			foreach ($subject_txts["$aThread->host/$aThread->bbs"] as $l) {
				if (@preg_match("/^{$aThread->key}/", $l)) {
					$aThread->getThreadInfoFromSubjectTxtLine($l); // subject.txt ����X�����擾
					break;
				}
			}
		}
		
		// �V���̂�(for spmode) ===============================
		if ($sb_view == "shinchaku" and !$_GET['word']) { 
			if ($aThread->unum < 1) {
				unset($aThread);
				continue;
			}
		}
	}
	
	if ($aThread->isonline) { $online_num++; }	// ������set
	
	echo $_info_msg_ht;
	$_info_msg_ht = "";
	
	$read_new_html .= ob_get_contents();
	@ob_end_flush();
	ob_start();
	
	if (($aThread->readnum < 1) || $aThread->unum) {
		readNew($aThread);
	} elseif ($aThread->diedat) {
		echo $aThread->getdat_error_msg_ht;
		echo "<hr>\n";
	}
	
	
	// ���X�g�ɒǉ� ========================================
	// $aThreadList->addThread($aThread);
	$aThreadList->num++;
	unset($aThread);
}

// $aThread =& new ThreadRead();

//======================================================================
// �� �X���b�h�̐V��������ǂݍ���ŕ\������
//======================================================================
function readNew(&$aThread)
{
	global $_conf, $newthre_num, $STYLE;
	global $_info_msg_ht;

	$newthre_num++;
	
	//==========================================================
	// �� idx�̓ǂݍ���
	//==========================================================
	
	// host�𕪉�����idx�t�@�C���̃p�X�����߂�
	$aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);
	
	// FileCtl::mkdir_for($aThread->keyidx);	 // �f�B���N�g����������΍�� // ���̑���͂����炭�s�v

	$aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
	if (!$aThread->itaj) { $aThread->itaj = $aThread->bbs; }

	// idx�t�@�C��������Γǂݍ���
	if (is_readable($aThread->keyidx)) {
		$lines = @file($aThread->keyidx);
		$data = explode('<>', rtrim($lines[0]));
	}
	$aThread->getThreadInfoFromIdx();
	
	//==================================================================
	// ��DAT�̃_�E�����[�h
	//==================================================================
	if (!($word and file_exists($aThread->keydat))) {
		$aThread->downloadDat();
	}
	
	// DAT��ǂݍ���
	$aThread->readDat();
	$aThread->setTitleFromLocal(); // ���[�J������^�C�g�����擾���Đݒ�
	
	//===========================================================
	// ���\�����X�Ԃ͈̔͂�ݒ�
	//===========================================================
	if ($aThread->isKitoku()) { // �擾�ς݂Ȃ�
		$from_num = $aThread->readnum +1 - $_conf['respointer'] - $_conf['before_respointer_new'];
		if ($from_num < 1) {
			$from_num = 1;
		} elseif ($from_num > $aThread->rescount) {
			$from_num = $aThread->rescount - $_conf['respointer'] - $_conf['before_respointer_new'];
		}

		//if(! $ls){
			$ls = "$from_num-";
		//}
	}
	
	$aThread->lsToPoint($ls);
	
	//==================================================================
	// ���w�b�_ �\��
	//==================================================================
	$motothre_url = $aThread->getMotoThread($GLOBALS['ls']);
	
	$ttitle_en = base64_encode($aThread->ttitle);
	$ttitle_urlen = rawurlencode($ttitle_en);
	$ttitle_en_q ="&amp;ttitle_en=".$ttitle_urlen;
	$bbs_q = "&amp;bbs=".$aThread->bbs;
	$key_q = "&amp;key=".$aThread->key;
	$popup_q = "&amp;popup=1";
	
	//include($read_header_inc);
	
	$prev_thre_num = $newthre_num - 1;
	$next_thre_num = $newthre_num + 1;
	if ($prev_thre_num != 0) {
		$prev_thre_ht = "<a href=\"#ntt{$prev_thre_num}\">��</a>";
	}
	$next_thre_ht = "<a href=\"#ntt{$next_thre_num}\">��</a>	";
	
	echo $_info_msg_ht;
	$_info_msg_ht = "";
	
	// ���w�b�_����HTML	
	$read_header_ht = <<<EOP
	<table id="ntt{$newthre_num}" width="100%" style="padding:0px 10px 0px 0px;">
		<tr>
			<td align="left">
				<h3 class="thread_title">{$aThread->ttitle_hd}</h3>
			</td>
			<td align="right">
				{$prev_thre_ht}
				{$next_thre_ht}
			</td>
		</tr>
	</table>\n
EOP;
	
	//==================================================================
	// �����[�J��Dat��ǂݍ����HTML�\��
	//==================================================================
	$aThread->resrange['nofirst'] = true;
	$GLOBALS['newres_to_show_flag'] = false;
	if ($aThread->rescount) {
		// $aThread->datToHtml(); //dat �� html �ɕϊ��\��
		include_once './showthread.class.php'; // HTML�\���N���X
		include_once './showthreadpc.class.php'; // HTML�\���N���X
		$aShowThread =& new ShowThreadPc($aThread);

		$res1 = $aShowThread->quoteOne();
		$read_cont_ht = $res1['q'];
		
		$read_cont_ht .= $aShowThread->getDatToHtml();

		unset($aShowThread);
	}
	
	//==================================================================
	// ���t�b�^ �\��
	//==================================================================
	//include($read_footer_inc);
	
	//----------------------------------------------
	// $read_footer_navi_new  ������ǂ� �V�����X�̕\��
	$newtime = date("gis");  // �����N���N���b�N���Ă��ēǍ����Ȃ��d�l�ɑ΍R����_�~�[�N�G���[
	
	$info_st = "���";
	$delete_st = "�폜";
	$prev_st = "�O";
	$next_st = "��";

	$read_footer_navi_new = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->rescount}-&amp;nt=$newtime#r{$aThread->rescount}\">�V�����X�̕\��</a>";
	
	$dores_ht = <<<EOP
		<a href="post_form.php?host={$aThread->host}{$bbs_q}{$key_q}&amp;rc={$aThread->rescount}{$ttitle_en_q}" target='_self' onClick="return OpenSubWin('post_form.php?host={$aThread->host}{$bbs_q}{$key_q}&amp;rc={$aThread->rescount}{$ttitle_en_q}{$popup_q}&amp;from_read_new=1',{$STYLE['post_pop_size']},0,0)">���X</a>
EOP;

	// ���c�[���o�[����HTML =======
	
	// ���C�Ƀ}�[�N�ݒ�
	$favmark = (!empty($aThread->fav)) ? '��' : '+';
	$favdo = (!empty($aThread->fav)) ? 0 : 1;
	$favtitle = $favdo ? '���C�ɃX���ɒǉ�' : '���C�ɃX������O��';
	$favdo_q = '&amp;setfav='.$favdo;
	$itaj_hd = htmlspecialchars($aThread->itaj);
	
	$toolbar_right_ht = <<<EOTOOLBAR
			<a href="{$_conf['subject_php']}?host={$aThread->host}{$bbs_q}{$key_q}" target="subject" title="���J��">{$itaj_hd}</a>
			<a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}" target="info" onClick="return OpenSubWin('info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$popup_q}',{$STYLE['info_pop_size']},0,0)" title="�X���b�h����\��">{$info_st}</a> 
			<span class="favdo"><a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$favdo_q}{$sid_q}" target="info" onClick="return setFav('{$aThread->host}', '{$aThread->bbs}', '{$aThread->key}', '{$favdo}', this);" title="{$favtitle}">���C��{$favmark}</a></span> 
			<span><a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}&amp;dele=true" target="info" onClick="return deleLog('host={$aThread->host}{$bbs_q}{$key_q}', this);" title="���O���폜����">{$delete_st}</a></span> 
<!--			<a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}&amp;taborn=2" target="info" onClick="return OpenSubWin('info.php?host={$aThread->host}{$bbs_q}&amp;key={$aThread->key}{$ttitle_en_q}&amp;popup=2&amp;taborn=2',{$STYLE['info_pop_size']},0,0)" title="�X���b�h�̂��ځ[���Ԃ��g�O������">���ڂ�</a> -->
			<a href="{$motothre_url}" title="�T�[�o��̃I���W�i���X����\��">���X��</a>
EOTOOLBAR;

	// ���X�̂��΂₳
	$spd_ht = "";
	if ($spd_st = $aThread->getTimePerRes() and $spd_st != "-") {
		$spd_ht = '<span class="spd" title="���΂₳������/���X">'."" . $spd_st."".'</span>';
	}

	// ���t�b�^����HTML
	$read_footer_ht = <<<EOP
		<table width="100%" style="padding:0px 10px 0px 0px;">
			<tr>
				<td align="left">
					{$res1['body']} | <a href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;offline=1&amp;rc={$aThread->rescount}#r{$aThread->rescount}">{$aThread->ttitle_hd}</a> | {$dores_ht} {$spd_ht}
				</td>
				<td align="right">
					{$toolbar_right_ht}
				</td>
				<td align="right">
					<a href="#ntt{$newthre_num}">��</a>
				</td>
			</tr>
		</table>\n
EOP;

	// �������ځ[��ŕ\�����Ȃ��ꍇ�̓X�L�b�v
	if ($GLOBALS['newres_to_show_flag']) {
		echo '<div style="width:100%;">'."\n";	// �ق�IE ActiveX��Gray()�̂��߂����Ɉ͂��Ă���
		echo $read_header_ht;
		echo $read_cont_ht;
		echo $read_footer_ht;
		echo '</div>'."\n\n";
		echo '<hr>'."\n\n";
	}

	flush();
	
	//==================================================================
	// ��key.idx�̒l�ݒ�
	//==================================================================
	if ($aThread->rescount) {
	
		$aThread->readnum = min($aThread->rescount, max(0, $data[5], $aThread->resrange['to']));
		
		$newline = $aThread->readnum + 1;	// $newline�͔p�~�\�肾���A���݊��p�ɔO�̂���
		
		$s = "{$aThread->ttitle}<>{$aThread->key}<>$data[2]<>{$aThread->rescount}<>{$aThread->modified}<>{$aThread->readnum}<>$data[6]<>$data[7]<>$data[8]<>{$newline}";
		P2Util::recKeyIdx($aThread->keyidx, $s); // key.idx�ɋL�^
	}
	
	unset($aThread);
}

//==================================================================
// ���y�[�W�t�b�^�\��
//==================================================================
$newthre_num++;

if (!$aThreadList->num) {
	$GLOBALS['matome_naipo'] = TRUE;
	echo "�V�����X�͂Ȃ���";
	echo "<hr>";
}

if (!isset($GLOBALS['rnum_all_range']) or $GLOBALS['rnum_all_range'] > 0) {
	echo <<<EOP
	<div id="ntt{$_newthre_num}" align="center">
		{$sb_ht} �� <a href="{$_conf['read_new_php']}?host={$aThreadList->host}&bbs={$aThreadList->bbs}&spmode={$aThreadList->spmode}&nt={$newtime}">�V���܂Ƃߓǂ݂��X�V</a>
	</div>\n
EOP;
} else {
	 echo <<<EOP
	<div id="ntt{$_newthre_num}" align="center">
		{$sb_ht} �� <a href="{$_conf['read_new_php']}?host={$aThreadList->host}&bbs={$aThreadList->bbs}&spmode={$aThreadList->spmode}&nt={$newtime}&amp;norefresh=1">�V���܂Ƃߓǂ݂̑���</a>
	</div>\n
EOP;
}

echo '</body></html>';

$read_new_html .= ob_get_contents();
@ob_end_flush();

// ��NG���ځ[����L�^
NgAbornCtl::saveNgAborns();
?>
