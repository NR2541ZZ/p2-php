<?php
// p2 - �X���b�h�\���X�N���v�g - �V���܂Ƃߓǂ�
// �t���[��������ʁA�E������

require_once("./conf.php"); // �ݒ�
require_once("threadlist_class.inc"); // �X���b�h���X�g �N���X
require_once("thread_class.inc"); //�X���b�h �N���X
require_once("threadread_class.inc"); //�X���b�h���[�h �N���X
require_once("datactl.inc");
require_once("read.inc");

authorize(); //���[�U�F��

//==================================================================
// �ϐ�
//==================================================================

$sb_view = "shinchaku";
$newtime = date("gis");
$_info_msg_ht = "";

//=================================================
// �̎w��
//=================================================
if (isset($_GET['host'])) { $host = $_GET['host']; }
if (isset($_POST['host'])) { $host = $_POST['host']; }
if (isset($_GET['bbs'])) { $bbs = $_GET['bbs']; }
if (isset($_POST['bbs'])) { $bbs = $_POST['bbs']; }
if (!$spmode) { $spmode = $_GET['spmode']; }
if (!$spmode) { $spmode = $_POST['spmode']; }

//=================================================
// ���ځ[��&NG���[�h�ݒ�ǂݍ���
//=================================================
readNgAbornFile();

//====================================================================
// ���C��
//====================================================================

$aThreadList = new ThreadList;

//�ƃ��[�h�̃Z�b�g===================================
if($spmode){
	if($spmode=="taborn" or $spmode=="soko"){
		$aThreadList->setIta($host, $bbs, getItaName($host, $bbs));
	}
	$aThreadList->setSpMode($spmode);	
}else{
	$aThreadList->setIta($host, $bbs, getItaName($host, $bbs));

	//�X���b�h���ځ[�񃊃X�g�Ǎ�
	$datdir_host = datdirOfHost($host);
	$tabornlines = @file($datdir_host."/".$bbs."/p2_threads_aborn.idx");
	if($tabornlines){
		$ta_num=sizeOf($tabornlines);
		foreach($tabornlines as $taline){
			$tarray = explode("<>", $taline);
			$ta_keys[ $tarray[1] ] = true;
		}
	}
}

//�\�[�X���X�g�Ǎ�==================================
$lines = $aThreadList->readList();

//�y�[�W�w�b�_�\��===================================
$ptitle_ht="{$aThreadList->ptitle} �� �V���܂Ƃߓǂ�";

if($aThreadList->spmode){
	$sb_ht =<<<EOP
		<a href="{$subject_php}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}" target="subject">{$aThreadList->ptitle}</a>
EOP;
}else{
	$sb_ht =<<<EOP
		<a href="{$subject_php}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}" target="subject">{$aThreadList->ptitle}</a>
EOP;
}

//include($read_header_inc);

header_content_type();
if($doctype){ echo $doctype;}
echo <<<EOHEADER
<html lang="ja">
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<title>{$ptitle_ht}</title>
EOHEADER;

@include("style/style_css.inc"); //�X�^�C���V�[�g
@include("style/read_css.inc"); //�X�^�C���V�[�g

echo <<<EOP
	<script type="text/javascript" src="{$basic_js}"></script>
	<script type="text/javascript" src="{$respopup_js}"></script>
	<script type="text/javascript" src="js/htmlpopup.js"></script>
</head>
<body onLoad="setWinTitle();">\n
EOP;

echo $_info_msg_ht;
$_info_msg_ht="";

//==============================================================
// ���ꂼ��̍s���
//==============================================================

$linesize= sizeof($lines);

for( $x = 0; $x < $linesize ; $x++ ){

	$l=$lines[$x];
	$aThread = new ThreadRead;
	
	$aThread->torder=$x+1;

	//�f�[�^�ǂݍ���
	if($aThreadList->spmode){
		switch ($aThreadList->spmode) {
	    case "recent": //����
	        $aThread->getThreadInfoFromExtIdxLine($l);
	        break;
	    case "res_hist": //�������ݗ���
	        $aThread->getThreadInfoFromExtIdxLine($l);
	        break;
	    case "fav": //���C��
	        $aThread->getThreadInfoFromExtIdxLine($l);
	        break;
		case "taborn":
	        $aThread->getThreadInfoFromExtIdxLine($l);
			$aThread->host = $aThreadList->host;
			$aThread->bbs = $aThreadList->bbs;
	        break;
		case "palace":
	        $aThread->getThreadInfoFromExtIdxLine($l);
	        break;
		}
	}else{// subject (not spmode)
		$aThread->getThreadInfoFromSubjectTxtLine($l);
		$aThread->host = $aThreadList->host;
		$aThread->bbs = $aThreadList->bbs;
	}
	
	if(!($aThread->host && $aThread->bbs)){unset($aThread); continue;} //host��bbs���s���Ȃ�X�L�b�v
	
	$aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);
	$aThread->getThreadInfoFromIdx($aThread->keyidx); //�����X���b�h�f�[�^��idx����擾

	// �V���̂�(for subject) =========================================
	if(! $aThreadList->spmode and $sb_view=="shinchaku" and ! $_GET['word']){ 
		if($aThread->unum < 1){unset($aThread); continue;}
	}

	//�X���b�h���ځ[��`�F�b�N =====================================
	if( $aThreadList->spmode != "taborn" and $ta_keys[$aThread->key]){ 
			unset($ta_keys[$aThread->key]);
			continue; //���ځ[��X���̓X�L�b�v
	}

	// spmode(�a�����������)�Ȃ�	====================================
	if($aThreadList->spmode && $sb_view!="edit"){ 
		
		// subject.txt����DL�Ȃ痎�Ƃ��ăf�[�^��z��Ɋi�[
		if(! $subject_txts["$aThread->host/$aThread->bbs"]){
			$datdir_host=datdirOfHost($aThread->host);
			$subject_url="http://{$aThread->host}/{$aThread->bbs}/subject.txt";
			$subjectfile="{$datdir_host}/{$aThread->bbs}/subject.txt";
			FileCtl::mkdir_for($subjectfile); //�f�B���N�g����������΍��
			if(! ($word_fm and file_exists($subjectfile)) ){
				subjectDownload($subject_url, $subjectfile);
			}
			if(extension_loaded('zlib') and strstr($aThread->host, ".2ch.net")){
				$subject_txts["$aThread->host/$aThread->bbs"] = @gzfile($subjectfile);
			}else{
				$subject_txts["$aThread->host/$aThread->bbs"] = @file($subjectfile);
			}
			
		}
		
		// �X�����擾 =============================
		if($subject_txts["$aThread->host/$aThread->bbs"]){
			foreach($subject_txts["$aThread->host/$aThread->bbs"] as $l){
				if( @preg_match("/^{$aThread->key}/",$l) ){
					$aThread->getThreadInfoFromSubjectTxtLine($l); //subject.txt ����X�����擾
					break;
				}
			}
		}
		
		// �V���̂�(for spmode) ===============================
		if($sb_view=="shinchaku" and ! $_GET['word']){ 
			if($aThread->unum < 1){unset($aThread); continue;}
		}
	}
	
	if(!$aThread->ttitle_ht){$aThread->ttitle_ht=$aThread->ttitle;}
 	if($aThread->isonline){$online_num++;}//������set
	
	echo $_info_msg_ht;
	$_info_msg_ht="";
	
	readNew($aThread);
	
	// ���X�g�ɒǉ� ========================================
	//$aThreadList->addThread($aThread);
	$aThreadList->num++;
	unset($aThread);
}

//$aThread = new ThreadRead;

//==================================================================

function readNew($aThread)
{
	global $_conf, $respointer, $before_respointer_new, $newthre_num, $STYLE, $browser;
	global $_info_msg_ht, $newres_to_show, $subject_php;

	$newthre_num++;
	
	//==========================================================
	// idx�̓ǂݍ���
	//==========================================================
	
	//host�𕪉�����idx�t�@�C���̃p�X�����߂�
	$aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);
	
	//FileCtl::mkdir_for($aThread->keyidx);	 //�f�B���N�g����������΍�� //���̑���͂����炭�s�v

	$aThread->itaj = getItaName($aThread->host, $aThread->bbs);
	if(!$aThread->itaj){$aThread->itaj=$aThread->bbs;}

	//idx�t�@�C��������Γǂݍ���
	if( is_readable($aThread->keyidx) ){
		$idxlines=@file($aThread->keyidx);
		$data = explode("<>", $idxlines[0]);
	}
	$aThread->getThreadInfoFromIdx($aThread->keyidx);
	
	//==================================================================
	//DAT�̃_�E�����[�h
	//==================================================================
	if(! ($word and file_exists($aThread->keydat)) ){
		$aThread->downloadDat();
	}
	
	//DAT��ǂݍ���
	$aThread->readDat($aThread->keydat);
	$aThread->setTitleFromLocal(); //���[�J������^�C�g�����擾���Đݒ�
	
	//===========================================================
	// �\�����X�Ԃ͈̔͂�ݒ�
	//===========================================================
	if ($aThread->isKitoku()) { // �擾�ς݂Ȃ�
		$from_num = $aThread->newline -$respointer - $before_respointer_new;
		if($from_num < 1){
			$from_num = 1;
		}elseif($from_num > $aThread->rescount){
			$from_num = $aThread->rescount -$respointer - $before_respointer_new;
		}

		//if(! $ls){
			$ls="$from_num-";
		//}
	}
	
	$aThread->lsToPoint($ls, $aThread->rescount);
	
	//==================================================================
	// �w�b�_ �\��
	//==================================================================
	$motothre_url = $aThread->getMotoThread($GLOBAL['ls']);
	
	$ttitle_en = base64_encode($aThread->ttitle);
	$ttitle_en_q = "&amp;ttitle_en=".$ttitle_en;
	$bbs_q = "&amp;bbs=".$aThread->bbs;
	$key_q = "&amp;key=".$aThread->key;
	$popup_q = "&amp;popup=1";
	
	//include($read_header_inc);
	
	$prev_thre_num = $newthre_num-1;
	$next_thre_num = $newthre_num+1;
	if($prev_thre_num != 0){
		$prev_thre_ht = "<a href=\"#ntt{$prev_thre_num}\">��</a>";
	}
	$next_thre_ht = "<a href=\"#ntt{$next_thre_num}\">��</a>	";
	
	echo $_info_msg_ht;
	$_info_msg_ht="";
	
	$read_header_ht = <<<EOP
	<table id="ntt{$newthre_num}" width="100%" style="padding:0px 10px 0px 0px;">
		<tr>
			<td align="left">
				<h3 class="thread_title">{$aThread->ttitle}</h3>
			</td>
			<td align="right">
				{$prev_thre_ht}
				{$next_thre_ht}			
			</td>
		</tr>
	</table>\n
EOP;
	
	//==================================================================
	// ���[�J��Dat��ǂݍ����HTML�\��
	//==================================================================
	$aThread->resrange['nofirst']=true;
	$newres_to_show=false;
	if($aThread->rescount){
		//$aThread->datToHtml(); //dat �� html �ɕϊ��\��
		include_once("./showthread_class.inc"); //HTML�\���N���X
		include_once("./showthreadpc_class.inc"); //HTML�\���N���X
		$aShowThread = new ShowThreadPc($aThread);

		$res1 = $aShowThread->quoteOne();
		$read_cont_ht = $res1['q'];

		$read_cont_ht .= $aShowThread->datToHtml();
		unset($aShowThread);
	}
	
	//==================================================================
	// �t�b�^ �\��
	//==================================================================
	//include($read_footer_inc);
	
	//----------------------------------------------
	// $read_footer_navi_new  ������ǂ� �V�����X�̕\��
	$newtime = date("gis");  //�����N���N���b�N���Ă��ēǍ����Ȃ��d�l�ɑ΍R����_�~�[�N�G���[
	
	$info_st = "���";
	$delete_st = "�폜";
	$prev_st = "�O";
	$next_st = "��";

	$read_footer_navi_new = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->rescount}-&amp;nt=$newtime#r{$aThread->rescount}\">�V�����X�̕\��</a>";
	
	$dores_ht = <<<EOP
		<a href="post_form.php?host={$aThread->host}{$bbs_q}{$key_q}&amp;rc={$aThread->rescount}{$ttitle_en_q}" target='_self' onClick="return OpenSubWin('post_form.php?host={$aThread->host}{$bbs_q}{$key_q}&amp;rc={$aThread->rescount}{$ttitle_en_q}{$popup_q}',{$STYLE['post_pop_size']},0,0)">���X</a>
EOP;

	// ���c�[���o�[����HTML =======
	$toolbar_right_ht = <<<EOTOOLBAR
			<a href="{$subject_php}?host={$aThread->host}{$bbs_q}{$key_q}" target="subject">{$aThread->itaj}</a>
			<a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}" target="info" onClick="return OpenSubWin('info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$popup_q}',{$STYLE['info_pop_size']},0,0)">{$info_st}</a> 
			<a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}&amp;dele=true" target="info" onClick="return OpenSubWin('info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}&amp;popup=2&amp;dele=true',{$STYLE['info_pop_size']},0,0)" title="���O���폜����">{$delete_st}</a> 
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
					{$res1['body']} | <a href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;rc={$aThread->rescount}#r{$aThread->rescount}">{$aThread->ttitle}</a> | {$dores_ht} {$spd_ht}
				</td>
				<td align="right">
					$toolbar_right_ht
				</td>
				<td align="right">
					<a href="#ntt{$newthre_num}">��</a>
				</td>
			</tr>
		</table>
		<hr>
EOP;

	//�������ځ[��ŕ\�����Ȃ��ꍇ�̓X�L�b�v
	if($newres_to_show){
		echo $read_header_ht;
		echo $read_cont_ht;
		echo $read_footer_ht;
	}

	//==================================================================
	// key.idx�̒l�ݒ�
	//==================================================================
	
	if($aThread->rescount){
	
		if($aThread->resrange['to']+1 > $aThread->newline){
			$aThread->newline = $aThread->resrange['to']+1;
		}else{
			$aThread->newline = $data[9];
		}
		//�ُ�l�C��
		if($aThread->newline > $aThread->rescount+1){
			$aThread->newline = $aThread->rescount+1;
		}elseif($aThread->newline < 1){
			$aThread->newline = 1;
		}
		
		$s = "{$aThread->ttitle}<>{$aThread->key}<>$data[2]<>{$aThread->rescount}<>{$aThread->modified}<>$data[5]<>$data[6]<>$data[7]<>$data[8]<>{$aThread->newline}";
		setKeyIdx($aThread->keyidx, $s); //key.idx�ɋL�^
	}

}

//==================================================================
// �y�[�W�t�b�^�\��
//==================================================================
$newthre_num++;

if (!$aThreadList->num) {
	echo "�V�����X�͂Ȃ���";
	echo "<hr>";
}

echo <<<EOP
	<div id="ntt{$newthre_num}" align="center">
		$sb_ht �� <a href="{$_conf['read_new_php']}?host={$aThreadList->host}&bbs={$aThreadList->bbs}&spmode={$aThreadList->spmode}&nt=$newtime">�V���܂Ƃߓǂ݂��X�V</a>
	</div>\n
EOP;

echo <<<EOP
</body>
</html>
EOP;

?>