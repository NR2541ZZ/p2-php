<?php
// p2 - �X���b�h�\���X�N���v�g
// �t���[��������ʁA�E������

require_once("./conf.php"); //��{�ݒ�Ǎ�
require_once("./thread_class.inc"); //�X���b�h�N���X�Ǎ�
require_once("./threadread_class.inc"); //�X���b�h���[�h�N���X�Ǎ�
require_once("./filectl_class.inc");
require_once("./datactl.inc");
require_once("./read.inc");
require_once("./showthread_class.inc"); //HTML�\���N���X

$debug=0;
$debug && include_once("profiler.inc"); //
$debug && $prof = new Profiler( true ); //

authorize(); //���[�U�F��

//================================================================
// �ϐ�
//================================================================

$newtime= date("gis");  //���������N���N���b�N���Ă��ēǍ����Ȃ��d�l�ɑ΍R����_�~�[�N�G���[
//$_today = date("y/m/d");

if($_GET['relogin2ch']){
	$relogin2ch=$_GET['relogin2ch'];
}
$_info_msg_ht = "";

//=================================================
// �X���̎w��
//=================================================
detectThread();

//=================================================
// ���X�t�B���^
//=================================================
if ($_POST['word']) { $word = $_POST['word']; }
if ($_GET['word']) { $word = $_GET['word']; }
if ($_POST['field']) { $field = $res_filter['field'] = $_POST['field']; }
if ($_GET['field']) { $field = $res_filter['field'] = $_GET['field']; }
if ($_POST['match']) { $res_filter['match'] = $_POST['match']; }
if ($_GET['match']) { $res_filter['match'] = $_GET['match']; }
if ($_POST['method']) { $res_filter['method'] = $_POST['method']; }
if ($_GET['method']) { $res_filter['method'] = $_GET['method']; }
if (get_magic_quotes_gpc()) {
	$word = stripslashes($word);
}
if ($word == '.') {$word = '';}
if (isset($word) && strlen($word) > 0) {
	if (!((!$_conf['enable_exfilter'] || $res_filter['method'] == 'regex') && preg_match('/^\.+$/', $word))) {
		include_once './strctl.class.php';
		$word_fm = StrCtl::wordForMatch($word, $res_filter['method']);
		if ($res_filter['method'] != 'just') {
			if (P2_MBREGEX_AVAILABLE == 1) {
				$words_fm = @mb_split('\s+', $word_fm);
				$word_fm = @mb_ereg_replace('\s+', '|', $word_fm);
			} else {
				$words_fm = @preg_split('/\s+/u', $word_fm);
				$word_fm = @preg_replace('/\s+/u', '|', $word_fm);
			}
		}
	}
}

//=================================================
// �t�B���^�l�ۑ�
//=================================================
$cachefile = $prefdir . "/p2_res_filter.txt";

if (isset($res_filter)) { // �w�肪����� �t�@�C�� �ɕۑ�

	FileCtl::make_datafile($cachefile, $p2_perm); //�t�@�C�����Ȃ���ΐ���
	if($res_filter){$res_filter_cont=serialize($res_filter);}
	if($res_filter_cont){
		$fp = @fopen($cachefile, "wb") or die("Error: $cachefile ���X�V�ł��܂���ł���");
		fputs($fp, $res_filter_cont);
		fclose($fp);
	}

}else{ //�w�肪�Ȃ���ΑO��ۑ���ǂݍ���
	$res_filter_cont = FileCtl::get_file_contents($cachefile);
	if($res_filter_cont){$res_filter=unserialize($res_filter_cont);}
}
unset($cachefile);

//=================================================
// ���ځ[��&NG���[�h�ݒ�ǂݍ���
//=================================================
readNgAbornFile();

//==================================================================
// ���C��
//==================================================================

$aThread = new ThreadRead;

//==========================================================
// idx�̓ǂݍ���
//==========================================================

// host�𕪉�����idx�t�@�C���̃p�X�����߂�
$aThread->setThreadPathInfo($host, $bbs, $key);

// �f�B���N�g����������΍��
// FileCtl::mkdir_for($aThread->keyidx);

$aThread->itaj = getItaName($host, $bbs);
if (!$aThread->itaj) { $aThread->itaj = $aThread->bbs; }

// idx�t�@�C��������Γǂݍ���
if (is_readable($aThread->keyidx)) {
	$lines = @file($aThread->keyidx);
	$data = explode('<>', rtrim($lines[0]));
}
$aThread->getThreadInfoFromIdx($aThread->keyidx);

//==========================================================
// preview >>1
//==========================================================

if ($_GET['one']) {
	$body = $aThread->previewOne();
	$ptitle_ht = $aThread->itaj." / ".$aThread->ttitle;
	include($read_header_inc);
	echo $body;
	include($read_footer_inc);
	return;
}

//===========================================================
// DAT�̃_�E�����[�h
//===========================================================
if (!$_GET['offline']) {
	if (!($word and file_exists($aThread->keydat))) {
		$aThread->downloadDat();
	}
}

//DAT��ǂݍ���========================================
$aThread->readDat($aThread->keydat);
$aThread->setTitleFromLocal(); //�^�C�g�����擾���Đݒ�

//===========================================================
// �\�����X�Ԃ͈̔͂�ݒ�
//===========================================================
if ($ktai) {
	$before_respointer = $before_respointer_k;
}
if ($aThread->isKitoku()) { // �擾�ς݂Ȃ�
	
	if ($_GET['nt']) { //�u�V�����X�̕\���v�̎��͓��ʂɂ�����ƑO�̃��X����\��
		if (substr($ls, -1) == "-") {
			$n = $ls - $before_respointer;
			if ($n<1) { $n = 1; }
			$ls = "$n-";
		}
		
	} elseif (!$ls) {
		$from_num = $aThread->newline -$respointer - $before_respointer;
		if ($from_num < 1) {
			$from_num = 1;
		} elseif ($from_num > $aThread->rescount) {
			$from_num = $aThread->rescount -$respointer - $before_respointer;
		}
		$ls = "$from_num-";
	}
	
	if ($ktai && (!strstr($ls, "n"))) {
		$ls = $ls."n";
	}
	
// ���擾�Ȃ�
} else {
	if (!$ls) { $ls = $get_new_res; }
}

$aThread->lsToPoint($ls, $aThread->rescount);

//===============================================================
// �v�����g
//===============================================================
$ptitle_ht = $aThread->itaj." / ".$aThread->ttitle;

if($ktai){
	
	//�w�b�_�v�����g
	include("./read_header_k.inc");
	
	if($aThread->rescount){
		include_once("./showthreadk_class.inc"); //HTML�\���N���X
		$aShowThread = new ShowThreadK($aThread);
		echo $aShowThread->datToHtml();
	}
	
	//�t�b�^�v�����g
	include("./read_footer_k.inc");
	
}else{
	//===========================================================
	// �w�b�_ �\��
	//===========================================================
	include($read_header_inc);
	
	//===========================================================
	// ���[�J��Dat��ϊ�����HTML�\��
	//===========================================================
	$debug && $prof->startTimer( "datToHtml" );
	
	if($aThread->rescount){
		//echo $aThread->datToHtml(); //dat �� html �ɕϊ��\��
		
		include_once("./showthreadpc_class.inc"); //HTML�\���N���X
		$aShowThread = new ShowThreadPc($aThread);
		
		$res1 = $aShowThread->quoteOne(); //>>1�|�b�v�A�b�v�p
		echo $res1['q'];

		echo $aShowThread->datToHtml();
	}
	
	$debug && $prof->stopTimer( "datToHtml" );
	
	//===========================================================
	// �t�b�^ �\��
	//===========================================================
	include($read_footer_inc);
	
	$debug && $prof->printTimers( true );

}

//===========================================================
// idx�̒l�ݒ�
//===========================================================
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
	setKeyIdx($aThread->keyidx, $s); // key.idx�ɋL�^
}

//===========================================================
//�������L�^
//===========================================================
if ($aThread->rescount) {
	$newdata = "{$aThread->ttitle}<>{$aThread->key}<>$data[2]<>{$aThread->rescount}<>{$aThread->modified}<>$data[5]<>$data[6]<>$data[7]<>$data[8]<>{$aThread->newline}<>{$aThread->host}<>{$aThread->bbs}";
	recRecent($newdata);
}

//�ȏ�---------------------------------------------------------------
exit;



//==================================================================
// �֐�
//==================================================================

/**
 * �X���b�h���w�肷��
 */
function detectThread()
{
	global $_conf, $host, $bbs, $key, $ls;
	
	if ($nama_url = $_GET['nama_url']) { // �X��URL�̒��ڎw��
	
			// 2ch or pink - http://choco.2ch.net/test/read.cgi/event/1027770702/
			if( preg_match("/http:\/\/([^\/]+\.(2ch\.net|bbspink\.com))\/test\/read\.cgi\/([^\/]+)\/([0-9]+)(\/)?([^\/]+)?/", $nama_url, $matches) ){
				$host=$matches[1];
				$bbs=$matches[3];
				$key=$matches[4];
				$ls=$matches[6];
				
			// 2ch or pink �ߋ����Ohtml - http://pc.2ch.net/mac/kako/1015/10153/1015358199.html
			} elseif ( preg_match("/(http:\/\/([^\/]+\.(2ch\.net|bbspink\.com))(\/[^\/]+)?\/([^\/]+)\/kako\/\d+(\/\d+)?\/(\d+)).html/", $nama_url, $matches) ){ //2ch pink �ߋ����Ohtml
				$host=$matches[2];
				$bbs=$matches[5];
				$key=$matches[7];
				$kakolog_uri = $matches[1];
				$_GET['kakolog']= urlencode($kakolog_uri);
				
			// �܂����������JBBS - http://kanto.machibbs.com/bbs/read.pl?BBS=kana&KEY=1034515019
			} elseif ( preg_match("/http:\/\/([^\/]+\.machibbs\.com|[^\/]+\.machi\.to)\/bbs\/read\.(pl|cgi)\?BBS=([^&]+)&KEY=([0-9]+)(&START=([0-9]+))?(&END=([0-9]+))?[^\"]*/", $nama_url, $matches) ){
				$host=$matches[1];
				$bbs=$matches[3];
				$key=$matches[4];
				$ls=$matches[6] ."-". $matches[8];
			} elseif (preg_match("{http://((jbbs\.livedoor\.jp|jbbs\.livedoor.com|jbbs\.shitaraba\.com)(/[^/]+)?)/bbs/read\.(pl|cgi)\?BBS=([^&]+)&KEY=([0-9]+)(&START=([0-9]+))?(&END=([0-9]+))?[^\"]*}", $nama_url, $matches)) {
				$host = $matches[1];
				$bbs = $matches[5];
				$key = $matches[6];
				$ls = $matches[8] ."-". $matches[10];
				
			// �������JBBS http://jbbs.livedoor.com/bbs/read.cgi/computer/2999/1081177036/-100 
			}elseif( preg_match("{http://(jbbs\.livedoor\.jp|jbbs\.livedoor.com|jbbs\.shitaraba\.com)/bbs/read\.cgi/(\w+)/(\d+)/(\d+)/((\d+)?-(\d+)?)?[^\"]*}", $nama_url, $matches) ){
				$host = $matches[1] ."/". $matches[2];
				$bbs = $matches[3];
				$key = $matches[4];
				$ls = $matches[5];
			}
	
	}else{
		if($_GET['host']){$host = $_GET['host'];} //"pc.2ch.net"
		if($_POST['host']){$host = $_POST['host'];}
		if($_GET['bbs']){$bbs = $_GET['bbs'];} //"php"
		if($_POST['bbs']){$bbs = $_POST['bbs'];}
		if($_GET['key']){$key = $_GET['key'];} //"1022999539"
		if($_POST['key']){$key = $_POST['key'];}
		if($_GET['ls']){$ls = $_GET['ls'];} //"all"
		if($_POST['ls']){$ls = $_POST['ls'];}
	}
	
	if(!($host && $bbs && $key)){die("p2 - {$_conf['read_php']}: �X���b�h�̎w�肪�ςł��B");}
}

/**
 * �������L�^����
 */
function recRecent($data)
{
	global $rctfile, $rct_rec_num, $rct_perm;
	
	FileCtl::make_datafile($rctfile, $rct_perm); //$rctfile�t�@�C�����Ȃ���ΐ���
	
	$lines= @file($rctfile); //�ǂݍ���

	// �ŏ��ɏd���v�f���폜
	if ($lines) {
		foreach($lines as $line){
			$line = rtrim($line);
			$lar = explode('<>', $line);
			$data_ar = explode('<>', $data);
			if ($lar[1] == $data_ar[1]) { continue; } // key�ŏd�����
			if (!$lar[1]) { continue; } // key�̂Ȃ����͕̂s���f�[�^
			$neolines[] = $line;
		}
	}
	
	// �V�K�f�[�^�ǉ�
	$neolines ? array_unshift($neolines, $data) : $neolines = array($data);

	while (sizeof($neolines) > $rct_rec_num) {
		array_pop($neolines);
	}
	
	// ��������
	$fp = @fopen($rctfile, "wb") or die("Error: $rctfile ���X�V�ł��܂���ł���");
	if ($neolines) {
		foreach ($neolines as $l) {
			fputs($fp, $l."\n");
		}
	}
	fclose($fp);
}


?>