<?php
// p2 - �X���b�h�\���X�N���v�g - �V���܂Ƃߓǂ݁i�g�сj
// �t���[��������ʁA�E������

require_once("./conf.php"); // �ݒ�
require_once './p2util.class.php';	// p2�p�̃��[�e�B���e�B�N���X
require_once("threadlist_class.inc"); // �X���b�h���X�g �N���X
require_once("thread_class.inc"); //�X���b�h �N���X
require_once("threadread_class.inc"); //�X���b�h���[�h �N���X
require_once("datactl.inc");
require_once("read.inc");

authorize(); //���[�U�F��

//==================================================================
// �ϐ�
//==================================================================
$GLOBALS['rnum_all_range'] = $_conf['k_rnum_range'];

$sb_view="shinchaku";
$newtime= date("gis");
$_info_msg_ht="";

//=================================================
// �̎w��
//=================================================

if($_GET['host']){$host = $_GET['host'];}
if($_POST['host']){$host = $_POST['host'];}
if($_GET['bbs']){$bbs = $_GET['bbs'];}
if($_POST['bbs']){$bbs = $_POST['bbs'];}
if(! $spmode){$spmode = $_GET['spmode'];}
if(! $spmode){$spmode = $_POST['spmode'];}

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
	if ($tabornlines) {
		$ta_num = sizeOf($tabornlines);
		foreach ($tabornlines as $l) {
			$tarray = explode('<>', rtrim($l));
			$ta_keys[ $tarray[1] ] = true;
		}
	}
}

//�\�[�X���X�g�Ǎ�==================================
$lines = $aThreadList->readList();

//�y�[�W�w�b�_�\��===================================
$ptitle_ht="{$aThreadList->ptitle} �� �V���܂Ƃߓǂ�";

//&amp;sb_view={$sb_view}
if($aThreadList->spmode){
	$sb_ht =<<<EOP
		<a href="{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}{$k_at_a}">{$aThreadList->ptitle}</a>
EOP;
	$sb_ht_btm =<<<EOP
		<a {$accesskey}="{$k_accesskey['above']}" href="{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}{$k_at_a}">{$k_accesskey['above']}.{$aThreadList->ptitle}</a>
EOP;
}else{
	$sb_ht =<<<EOP
		<a href="{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}{$k_at_a}">{$aThreadList->ptitle}</a>
EOP;
	$sb_ht_btm =<<<EOP
		<a {$accesskey}="{$k_accesskey['above']}" href="{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}{$k_at_a}">{$k_accesskey['above']}.{$aThreadList->ptitle}</a>
EOP;
}

//include($read_header_inc);

header_content_type();
if($doctype){ echo $doctype;}
echo <<<EOHEADER
<html>
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<title>{$ptitle_ht}</title>
EOHEADER;

echo <<<EOP
</head>
<body>\n
EOP;

echo "<p>{$sb_ht}�̐V�܂Ƃ�</p>";

echo $_info_msg_ht;
$_info_msg_ht="";

//==============================================================
// ���ꂼ��̍s���
//==============================================================

$linesize = sizeof($lines);

for ($x = 0; $x < $linesize ; $x++) {

	if (isset($GLOBALS['rnum_all_range']) and $GLOBALS['rnum_all_range'] <= 0) {
		break;
	}

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
	if($aThreadList->spmode != "taborn" and $ta_keys[$aThread->key]){ 
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
				P2Util::subjectDownload($subject_url, $subjectfile);
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

function readNew(&$aThread)
{
	global $_conf, $newthre_num, $STYLE, $browser;
	global $_info_msg_ht, $newres_to_show, $pointer_at, $spmode, $k_accesskey, $k_at_a;

	$newthre_num++;
	
	//==========================================================
	// idx�̓ǂݍ���
	//==========================================================
	
	//host�𕪉�����idx�t�@�C���̃p�X�����߂�
	$aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);
	
	//FileCtl::mkdir_for($aThread->keyidx);	 //�f�B���N�g����������΍�� //���̑���͂����炭�s�v

	$aThread->itaj = getItaName($aThread->host, $aThread->bbs);
	if (!$aThread->itaj) { $aThread->itaj = $aThread->bbs; }

	// idx�t�@�C��������Γǂݍ���
	if (is_readable($aThread->keyidx)) {
		$lines = @file($aThread->keyidx);
		$data = explode('<>', rtrim($lines[0]));
	}
	$aThread->getThreadInfoFromIdx($aThread->keyidx);
	
	//==================================================================
	// DAT�̃_�E�����[�h
	//==================================================================
	if(! ($word and file_exists($aThread->keydat)) ){
		$aThread->downloadDat();
	}
	
	// DAT��ǂݍ���
	$aThread->readDat($aThread->keydat);
	$aThread->setTitleFromLocal(); // ���[�J������^�C�g�����擾���Đݒ�
	
	//===========================================================
	// �\�����X�Ԃ͈̔͂�ݒ�
	//===========================================================
	if ($aThread->isKitoku()) { // �擾�ς݂Ȃ�
		$from_num = $aThread->readnum +1 - $_conf['respointer'] - $_conf['before_respointer_new'];
		if($from_num < 1){
			$from_num = 1;
		}elseif($from_num > $aThread->rescount){
			$from_num = $aThread->rescount - $_conf['respointer'] - $_conf['before_respointer_new'];
		}

		//if (!$ls) {
			$ls = "$from_num-";
		//}
	}
	
	$aThread->lsToPoint($ls);
	
	//==================================================================
	// �w�b�_ �\��
	//==================================================================
	$motothre_url = $aThread->getMotoThread($GLOBALS['ls']);
	
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
	//$next_thre_ht = "<a href=\"#ntt{$next_thre_num}\">��</a>	";
	$next_thre_ht = "<a href=\"#ntt_bt{$newthre_num}\">��</a>	";
	
	if($spmode){
		$read_header_itaj_ht = " ({$aThread->itaj})";
	}
	
	echo $_info_msg_ht;
	$_info_msg_ht="";
	
	$read_header_ht = <<<EOP
		<hr>
		<p {$pointer_at}="ntt{$newthre_num}"><b>{$aThread->ttitle}</b>{$read_header_itaj_ht} {$next_thre_ht}</p>
		<hr>
EOP;

	//==================================================================
	// ���[�J��Dat��ǂݍ����HTML�\��
	//==================================================================
	$aThread->resrange['nofirst']=true;
	$newres_to_show=false;
	if($aThread->rescount){
		//$aThread->datToHtml(); //dat �� html �ɕϊ��\��
		include_once("./showthread_class.inc"); //HTML�\���N���X
		include_once("./showthreadk_class.inc"); //HTML�\���N���X
		$aShowThread = new ShowThreadK($aThread);

		$read_cont_ht .= $aShowThread->datToHtml();
		unset($aShowThread);
	}
	
	//==================================================================
	// �t�b�^ �\��
	//==================================================================
	//include($read_footer_inc);
	
	//----------------------------------------------
	// $read_footer_navi_new  ������ǂ� �V�����X�̕\��
	$newtime= date("gis");  //�����N���N���b�N���Ă��ēǍ����Ȃ��d�l�ɑ΍R����_�~�[�N�G���[
	
	$info_st="��";
	$delete_st="��";
	$prev_st="�O";
	$next_st="��";

	//�\���͈�
	if($aThread->resrange['start']==$aThread->resrange['to']){
		$read_range_on=$aThread->resrange['start'];
	}else{
		$read_range_on="{$aThread->resrange['start']}-{$aThread->resrange['to']}";
	}
	$read_range_ht=<<<EOP
	{$read_range_on}/{$aThread->rescount}<br>
EOP;

	$read_footer_navi_new="<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->rescount}-&amp;nt={$newtime}{$k_at_a}#r{$aThread->rescount}\">�V��ڽ�̕\��</a>";
	
	$dores_ht=<<<EOP
		<a href="post_form.php?host={$aThread->host}{$bbs_q}{$key_q}&amp;rc={$aThread->rescount}{$ttitle_en_q}{$k_at_a}">ڽ</a>
EOP;

	//�c�[���o�[����HTML=======
	if ($spmode) {
		$toolbar_itaj_ht = <<<EOP
(<a href="{$_conf['subject_php']}?host={$aThread->host}{$bbs_q}{$key_q}{$k_at_a}">{$aThread->itaj}</a>)
EOP;
	}
	$toolbar_right_ht .=<<<EOTOOLBAR
			<a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$k_at_a}">{$info_st}</a> 
			<a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}&amp;dele=true{$k_at_a}">{$delete_st}</a> 
			<a href="{$motothre_url}">����</a>
EOTOOLBAR;

	$read_footer_ht = <<<EOP
		<div {$pointer_at}="ntt_bt{$newthre_num}">
			$read_range_ht 
			<a href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;rc={$aThread->rescount}{$k_at_a}#r{$aThread->rescount}">{$aThread->ttitle}</a>{$toolbar_itaj_ht} 
			<a href="#ntt{$newthre_num}">��</a>
		</div>
		<hr>
EOP;

	//�������ځ[��ŕ\�����Ȃ��ꍇ�̓X�L�b�v
	if ($newres_to_show) {
		echo $read_header_ht;
		echo $read_cont_ht;
		echo $read_footer_ht;
	}

	//==================================================================
	// key.idx�̒l�ݒ�
	//==================================================================
	if ($aThread->rescount) {
	
		$aThread->readnum = min($aThread->rescount, max(0, $data[5], $aThread->resrange['to']));
		
		$newline = $aThread->readnum + 1;	// $newline�͔p�~�\�肾���A���݊��p�ɔO�̂���
		
		$s = "{$aThread->ttitle}<>{$aThread->key}<>$data[2]<>{$aThread->rescount}<>{$aThread->modified}<>{$aThread->readnum}<>$data[6]<>$data[7]<>$data[8]<>{$newline}";
		setKeyIdx($aThread->keyidx, $s); // key.idx�ɋL�^
	}

}

//==================================================================
// �y�[�W�t�b�^�\��
//==================================================================
$newthre_num++;

if (!$aThreadList->num) {
	echo "�V��ڽ�͂Ȃ���";
	echo "<hr>";
}

if (!isset($GLOBALS['rnum_all_range']) or $GLOBALS['rnum_all_range'] > 0) {
	echo <<<EOP
	<div>
		{$sb_ht_btm}��<a href="{$_conf['read_new_k_php']}?host={$aThreadList->host}&bbs={$aThreadList->bbs}&spmode={$aThreadList->spmode}&nt={$newtime}{$k_at_a}" {$accesskey}="{$k_accesskey['next']}">{$k_accesskey['next']}.�V�܂Ƃ߂��X�V</a>
	</div>\n
EOP;
} else {
	echo <<<EOP
	<div>
		{$sb_ht_btm}��<a href="{$_conf['read_new_k_php']}?host={$aThreadList->host}&bbs={$aThreadList->bbs}&spmode={$aThreadList->spmode}&nt={$newtime}&amp;norefresh=1{$k_at_a}" {$accesskey}="{$k_accesskey['next']}">{$k_accesskey['next']}.�V�܂Ƃ߂̑���</a>
	</div>\n
EOP;
}

echo <<<EOP
<hr>
{$k_to_index_ht}
EOP;

echo <<<EOP
</body>
</html>
EOP;

?>