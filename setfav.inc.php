<?php
/*
	p2 - ���C�ɃX���֌W�̏����X�N���v�g

	���C�ɃX���̒ǉ��폜��A�����ύX�ŌĂ΂��
	
	2005/03/10 �ȑO
	�X����idx�ł̂��C�ɓ���t���O�́A���݂͎g�p�i�@�\�j���Ă��Ȃ��B
	���C�ɃX�����́Afavlist.idx�ł܂Ƃ߂Ď󂯎��B
	��
	2005/03/10
	�X���b�h�\�����̕��׌y����ړI�Ƃ��āA�X���b�h.idx�ł����C�ɃX�����������ƂƂ���B
	subject�ł��C�ɃX���ꗗ�\�� �� favlist.idx ���Q��
	�X���b�h�\�����̂��C�ɃX���\�� �� �X���b�h.idx ���Q��
*/

require_once './p2util.class.php';	// p2�p�̃��[�e�B���e�B�N���X
require_once './filectl.class.php';

/**
 * ���C�ɃX�����Z�b�g����
 *
 * $set �́A0(����), 1(�ǉ�), top, up, down, bottom
 */
function setFav($host, $bbs, $key, $setfav)
{
	global $_conf;

	//==================================================================
	// key.idx
	//==================================================================
	// idxfile�̃p�X�����߂�
	$datdir_host = P2Util::datdirOfHost($host);
	$idxfile = $datdir_host.'/'.$bbs.'/'.$key.'.idx';

	// �f�B���N�g����������΍��
	// FileCtl::mkdir_for($idxfile);

	// ����idx�f�[�^������Ȃ�ǂݍ���
	if ($lines = @file($idxfile)) {
		$l = rtrim($lines[0]);
		$data = explode('<>', $l);
	}

	// �X���b�h.idx �L�^
	if ($setfav == '0' or $setfav == '1') {
		// ���C�ɃX������O�������ʁAidx�̈Ӗ����Ȃ��Ȃ�΍폜����
		if ($setfav == '0' and (!$data[3] && !$data[4] && $data[9] <= 1)) {
			@unlink($idxfile);
		} else {
			$s = "$data[0]<>{$key}<>$data[2]<>$data[3]<>$data[4]<>$data[5]<>{$setfav}<>$data[7]<>$data[8]<>$data[9]";
			P2Util::recKeyIdx($idxfile, $s);
		}
	}
	
	//==================================================================
	// favlist.idx
	//==================================================================
	// favlist�t�@�C�����Ȃ���ΐ���
	FileCtl::make_datafile($_conf['favlist_file'], $_conf['favlist_perm']);

	// favlist�ǂݍ���
	$favlines = @file($_conf['favlist_file']);

	//================================================
	// ����
	//================================================

	// �ŏ��ɏd���v�f���폜���Ă���
	if (!empty($favlines)) {
		$i = -1;
		$neolines = array();
		foreach ($favlines as $line) {
			$i++;
			$line = rtrim($line);
			$lar = explode('<>', $line);
			// �d�����
			if ($lar[1] == $key) {
				$before_line_num = $i;	// �ړ��O�̍s�ԍ����Z�b�g
				continue;
			// key�̂Ȃ����͕̂s���f�[�^�Ȃ̂ŃX�L�b�v
			} elseif (!$lar[1]) {
				continue;
			} else {
				$neolines[] = $line;
			}
		}
	}

	// �V�K�f�[�^�ݒ�
	if ($setfav) {
		$newdata = "$data[0]<>{$key}<>$data[2]<>$data[3]<>$data[4]<>$data[5]<>1<>$data[7]<>$data[8]<>$data[9]<>{$host}<>{$bbs}";
	}
	
	if ($setfav == 1 or $setfav == 'top') {
		$after_line_num = 0;	// �ړ���̍s�ԍ�
	
	} elseif ($setfav == 'up') {
		$after_line_num = $before_line_num - 1;
		if ($after_line_num < 0) {
			$after_line_num = 0;
		}
	
	} elseif ($setfav == 'down') {
		$after_line_num = $before_line_num + 1;
		if ($after_line_num >= sizeof($neolines)) {
			$after_line_num = 'bottom';
		}
	
	} elseif ($setfav == 'bottom') {
		$after_line_num = 'bottom';
	}

	//================================================
	// ��������
	//================================================
	$fp = @fopen($_conf['favlist_file'], "wb") or die("Error: {$_conf['favlist_file']} ���X�V�ł��܂���ł���");
	@flock($fp, LOCK_EX);
	if (!empty($neolines)) {
		$i = 0;
		foreach ($neolines as $l) {
			if ($i === $after_line_num) {
				fputs($fp, $newdata."\n");
			}
			fputs($fp, $l."\n");
			$i++;
		}
		if ($after_line_num === 'bottom') {
			fputs($fp, $newdata."\n");
		}
		//�u$after_line_num == "bottom"�v���ƌ듮�삷��B
	} else {
		fputs($fp, $newdata."\n");
	}
	@flock($fp, LOCK_UN);
	fclose($fp);

	//================================================
	// ���C�ɃX�����L
	//================================================
	if ($_conf['join_favrank']) {
		if ($setfav == "0") {
			$act = "out";
		} elseif ($setfav == "1") {
			$act = "add";
		} else {
			return;
		}
		$itaj = P2Util::getItaName($host, $bbs);
		$post = array("host" => $host, "bbs" => $bbs, "key" => $key, "ttitle" => $data[0], "ita" => $itaj, "act" => $act);
		postFavRank($post);
	}

	return true;
}

/**
 * ���C�ɃX�����L�Ń|�X�g����
 */
function postFavRank($post)
{
	global $_conf;

	$method = "POST";
	$httpua = "Monazilla/1.00 (".$_conf['p2name']."/".$_conf['p2version'].")";
	
	$URL = parse_url($_conf['favrank_url']); // URL����
	if (isset($URL['query'])) { // �N�G���[
	    $URL['query'] = "?".$URL['query'];
	} else {
	    $URL['query'] = "";
	}

	// �v���L�V
	if ($_conf['proxy_use']) {
		$send_host = $_conf['proxy_host'];
		$send_port = $_conf['proxy_port'];
		$send_path = $url;
	} else {
		$send_host = $URL['host'];
		$send_port = $URL['port'];
		$send_path = $URL['path'].$URL['query'];
	}
	
	if (!$send_port) {$send_port = 80;}	// �f�t�H���g��80
	
	$request = $method." ".$send_path." HTTP/1.0\r\n";
	$request .= "Host: ".$URL['host']."\r\n";
	$request .= "User-Agent: ".$httpua."\r\n";
	$request .= "Connection: Close\r\n";

	/* POST�̎��̓w�b�_��ǉ����Ė�����URL�G���R�[�h�����f�[�^��Y�t */
	if (strtoupper($method) == "POST") {
	    while (list($name, $value) = each($post)) {
	        $POST[] = $name."=".urlencode($value);
	    }
	    $postdata = implode("&", $POST);
	    $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
	    $request .= "Content-Length: ".strlen($postdata)."\r\n";
	    $request .= "\r\n";
	    $request .= $postdata;
	} else {
	    $request .= "\r\n";
	}

	/* WEB�T�[�o�֐ڑ� */
	$fp = fsockopen($send_host, $send_port, $errno, $errstr, 3);
	if (!$fp) {
		//echo "�T�[�o�ڑ��G���[: $errstr ($errno)<br>\n";
		//echo "p2 info: {$_conf['favrank_url']} �ɐڑ��ł��܂���ł����B<br>";
		return false;
	} else {
		fputs($fp, $request);
		/*
		while (!feof($fp)){
			if($start_here){
				echo $body = fread($fp,512000);
			}else{
				$l = fgets($fp,128000);
				if($l=="\r\n"){
					$start_here=true;
				}
			}
		}
		*/
		fclose ($fp);
		return true;
		//return $body;
	}
}

?>