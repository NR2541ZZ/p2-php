<?php
// p2 - �X���b�h��\������ �N���X �g�їp

class ShowThreadK extends ShowThread{
	
	function ShowThreadK($aThread)
	{
		$this->thread = $aThread;
	}
	
	/**
	 * Dat��HTML�ɕϊ��\������
	 */
	function datToHtml()
	{
		if (!$this->thread->resrange) {
			echo '<p><b>p2 error: {$this->resrange} is false at datToHtml()</b></p>';
		}

		$start = $this->thread->resrange['start'];
		$to = $this->thread->resrange['to'];
		$nofirst = $this->thread->resrange['nofirst'];

		// 1��\��
		if (!$nofirst) {
			echo $this->transRes($this->thread->datlines[0], 1);
		}

		for ($i = $start; $i <= $to; $i++) {
			if (!$nofirst and $i==1) {
				continue;
			}
			if (!$this->thread->datlines[$i-1]) {
				$this->thread->readnum = $i-1; 
				break;
			}
			echo $this->transRes($this->thread->datlines[$i-1], $i);
			flush();
		}
		
		//$s2e = array($start, $i-1);
		//return $s2e;
		return true;
	}


	/**
	 * Dat���X��HTML���X�ɕϊ�����
	 *
	 * ���� - dat��1���C��, ���X�ԍ�
	 */
	function transRes($ares,$i)
	{
		global $STYLE, $mae_msg, $res_filter, $word_fm;
		global $ngaborns_hits;
		global $_conf;
		
		$tores = "";
		$rpop = "";
		$isNgName = false;
		$isNgMsg = false;
		
		$resar = $this->thread->explodeDatLine($ares);
		$name=$resar[0];
		$mail = $resar[1];
		$date_id = $resar[2];
		$msg = $resar[3];

		// �t�B���^�����O
		/*
		if (isset($_REQUEST['field'])) {
			if (!$word_fm) { return; }
			
			if ($res_filter['field'] == 'name') {
				$target = $name;
			} elseif ($res_filter['field'] == 'mail') {
				$target = $mail;
			} elseif ($res_filter['field'] == 'date') {
				$target = preg_replace("/ID:([0-9a-zA-Z\/\.\+]+)/", "", $date_id);
			} elseif ($res_filter['field'] == 'id') {
				$target = preg_replace("/^.*ID:([0-9a-zA-Z\/\.\+]+).*$/", "\\1", $date_id);
			} elseif ($res_filter['field'] == 'msg') {
				$target = $msg;
			}
			if ($res_filter['match'] == 'on') {
				if (!StrCtl::filterMatch($word_fm, $target)) { return; }
			} else {
				if (StrCtl::filterMatch($word_fm, $target)) { return; }
			}
		}
		*/
		
		//���ځ[��`�F�b�N====================================
		$aborned_res .= "<div id=\"r{$i}\" name=\"r{$i}\">&nbsp;</div>\n"; //���O
		$aborned_res .= ""; //���e

		// ���ځ[��l�[��
		if ($this->ngAbornCheck('aborn_name', $name) !== false) {
			$ngaborns_hits['aborn_name']++;
			return $aborned_res;
		}

		// ���ځ[�񃁁[��
		if ($this->ngAbornCheck('aborn_mail', $mail) !== false) {
			$ngaborns_hits['aborn_mail']++;
			return $aborned_res;
		}
		
		// ���ځ[��ID
		if ($this->ngAbornCheck('aborn_id', $date_id) !== false) {
			$ngaborns_hits['aborn_id']++;
			return $aborned_res;
		}
		
		// ���ځ[�񃁃b�Z�[�W
		if ($this->ngAbornCheck('aborn_msg', $msg) !== false) {
			$ngaborns_hits['aborn_msg']++;
			return $aborned_res;
		}

		// NG�`�F�b�N ========
		if (!$_GET['nong']) {
			// NG�l�[���`�F�b�N
			if ($this->ngAbornCheck('ng_name', $name) !== false) {
				$ngaborns_hits['ng_name']++;
				$isNgName = true;
			}

			// NG���[���`�F�b�N
			if ($this->ngAbornCheck('ng_mail', $mail) !== false) {
				$ngaborns_hits['ng_mail']++;
				$isNgMail = true;
			}
		
			// NGID�`�F�b�N
			if ($this->ngAbornCheck('ng_id', $date_id) !== false) {
				$ngaborns_hits['ng_id']++;
				$isNgId = true;
			}
	
			// NG���b�Z�[�W�`�F�b�N
			$a_ng_msg = $this->ngAbornCheck('ng_msg', $msg);
			if ($a_ng_msg !== false) {
				$ngaborns_hits['ng_msg']++;
				$isNgMsg = true;
			}
		}
		
		//=============================================================
		// �܂Ƃ߂ďo��
		//=============================================================
		
		$name = $this->transName($name); // ���OHTML�ϊ�
		$msg = $this->transMsg($msg, $i); // ���b�Z�[�WHTML�ϊ�
		
		// BE�v���t�@�C�������N�ϊ�
		$date_id = $this->replaceBeId($date_id);
		
		// NG���b�Z�[�W�ϊ�======================================
		if ($isNgMsg) {
			$msg = <<<EOMSG
<s><font color="{$STYLE['read_ngword']}">NGܰ��:{$a_ng_msg}</font></s> <a href="{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;ls={$i}&amp;k_continue=1&amp;nong=1{$_conf['k_at_a']}">�m</a>
EOMSG;
		}
		
		// NG�l�[���ϊ�======================================
		if ($isNgName) {
			$name = <<<EONAME
<s><font color="{$STYLE['read_ngword']}">$name</font></s>
EONAME;
			$msg = <<<EOMSG
<a href="{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;ls={$i}&amp;k_continue=1&amp;nong=1{$_conf['k_at_a']}">�m</a>
EOMSG;
		
		// NG���[���ϊ�======================================
		} elseif ($isNgMail) {
			$mail = <<<EOMAIL
<s class="ngword" onMouseover="document.getElementById('ngn{$ngaborns_hits['ng_mail']}').style.display = 'block';">$mail</s>
EOMAIL;
			$msg = <<<EOMSG
<div id="ngn{$ngaborns_hits['ng_mail']}" style="display:none;">$msg</div>
EOMSG;

		// NGID�ϊ�======================================
		} elseif ($isNgId) {
			$date_id = <<<EOID
<s><font color="{$STYLE['read_ngword']}">$date_id</font></s>
EOID;
			$msg = <<<EOMSG
<a href="{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;ls={$i}&amp;k_continue=1&amp;nong=1{$_conf['k_at_a']}">�m</a>
EOMSG;
		}
	
		/*
		//�u��������V���v�摜��}��========================
		if ($i == $this->thread->readnum +1) {
			$tores .= <<<EOP
				<div><img src="img/image.png" alt="�V�����X" border="0" vspace="4"></div>
EOP;
		}
		*/

		if ($this->thread->onthefly) { // ontheflyresorder
			$GLOBALS['newres_to_show_flag'] = true;
			$tores .= "<div id=\"r{$i}\" name=\"r{$i}\">[<font color=\"#00aa00'\">{$i}</font>]"; // �ԍ��i�I���U�t���C���j
		} elseif ($i > $this->thread->readnum) {
			$GLOBALS['newres_to_show_flag'] = true;
			$tores .= "<div id=\"r{$i}\" name=\"r{$i}\">[<font color=\"{$STYLE['read_newres_color']}\">{$i}</font>]"; // �ԍ��i�V�����X���j
		} else {
			$tores .= "<div id=\"r{$i}\" name=\"r{$i}\">[{$i}]"; // �ԍ�
		}
		$tores .= $name.":"; // ���O
		if ($mail) {$tores .= $mail.": ";} // ���[��
		$tores .= $date_id."<br>\n"; // ���t��ID
		$tores .= $rpop; // ���X�|�b�v�A�b�v�p���p
		$tores .= "{$msg}</div><hr>\n"; // ���e
		
		return $tores;
	}
	
	/**
	 * ���O��HTML�p�ɕϊ�����
	 */
	function transName($name)
	{
		global $_conf;
		
		$nameID = "";

		// ID�t�Ȃ番������
		if (preg_match("/(.*)(��.*)/", $name, $matches)) {
			$name = $matches[1];
			$nameID = $matches[2];
		}

		// ���������p���X�|�b�v�A�b�v�����N��
		// </b>�`<b> �́A�z�X�g��g���b�v�Ȃ̂Ń}�b�`���Ȃ��悤�ɂ�����
		//$name && $name = preg_replace_callback("/(?!<\/b>[^>]*)([1-9][0-9]{0,3})(?![^<]*<b>)/", array($this, 'quote_res_callback'), $name, 1);
		$name && $name = preg_replace_callback("/(^|(?:&gt;)+)(\s*[1-9][0-9]{0,3})(?=\s*$)/", array($this, 'quote_res_callback'), $name, 1);
		
		if ($nameID) {$name = $name . $nameID;}
		
		$name = $name." "; // �����������

		$name = str_replace("</b>", "", $name);
		$name = str_replace("<b>", "", $name);
	
		return $name;
	}

	
	//============================================================================
	// dat�̃��X���b�Z�[�W��HTML�\���p���b�Z�[�W�ɕϊ�����
	// string transMsg(string str)
	//============================================================================	
	function transMsg($msg, $mynum)
	{
		global $_conf;
		global $res_filter, $word_fm;
		
		$ryaku = false;
		$str_in_url = '-_.!~*a-zA-Z0-9;\/?:@&=+\$,%#';
		
		//2ch���`����dat
		if ($this->thread->dat_type == "2ch_old") {
			$msg = str_replace("���M", ",", $msg);
			$msg = preg_replace("/&amp([^;])/", "&\\1", $msg);
		}

		// >>1�̃����N����������O��
		// <a href="../test/read.cgi/accuse/1001506967/1" target="_blank">&gt;&gt;1</a>
		$msg = preg_replace("/<a href=\"\.\.\/test\/read\.cgi\/{$this->thread->bbs}\/{$this->thread->key}\/([-0-9]+)\" target=\"_blank\">&gt;&gt;([-0-9]+)<\/a>/","&gt;&gt;\\1", $msg);
	
		//�傫������
		if (!$_GET['k_continue']) {
			if (strlen($msg) > $_conf['ktai_res_size']) {
				$msg = substr($msg, 0, $_conf['ktai_ryaku_size']);
				
				//������<br>������Ύ�菜��
				if (substr($msg, -1) == ">") {
					$msg = substr($msg, 0, strlen($msg)-1);
				}
				if (substr($msg, -1) == "r") {
					$msg = substr($msg, 0, strlen($msg)-1);
				}
				if (substr($msg, -1) == "b") {
					$msg = substr($msg, 0, strlen($msg)-1);
				}
				if (substr($msg, -1) == "<") {
					$msg = substr($msg, 0, strlen($msg)-1);
				}
				
				$msg = $msg." ";
				$msg .= "<a href=\"{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;ls={$mynum}&amp;k_continue=1&amp;offline=1{$_conf['k_at_a']}\">��</a>";
				$ryaku=true;
			}
		}

		// >>1, >1, ��1, ����1�����p���X�|�b�v�A�b�v�����N��
		$msg = preg_replace_callback("/(&gt;|��)?(&gt;|��)([0-9- ,=.]|�A)+/", array($this, 'quote_res_callback'), $msg);
	
		if ($ryaku) {
			return $msg;
		}
	
		// FTP�����N�̗L����
		$msg = preg_replace("/ftp:\/\/[{$str_in_url}]+/","<a href=\"\\0\"{$_conf['ext_win_target_at']}>\\0</a>", $msg);
		
		// �ih�������܂߂��jURL�����N�̗L����
		$msg = preg_replace("/([^f])(h?t?)(tps?:\/\/[{$str_in_url}]+)/","\\1<a href=\"ht\\3\"{$_conf['ext_win_target_at']}>\\2\\3</a>", $msg);
		$msg = preg_replace("/&gt;\"{$_conf['ext_win_target_at']}>(.+)&gt;<\/a>/","\"{$_conf['ext_win_target_at']}>\\1</a>&gt;", $msg); //������&gt;�i>�j�����O���Ă���
		
		// �T�[�o�������N��p2�\����
		// 2ch bbspink
		// http://choco.2ch.net/test/read.cgi/event/1027770702/
		$msg = preg_replace_callback("/<a href=\"http:\/\/([^\/]+\.(2ch\.net|bbspink\.com))\/test\/read\.cgi\/([^\/]+)\/([0-9]+)(\/)?([^\/]+)?\"{$_conf['ext_win_target_at']}>(h?t?tp:\/\/([^\/]+(\.2ch\.net|\.bbspink\.com))\/test\/read\.cgi\/([^\/]+)\/([0-9]+)(\/)?([^\/]+)?)<\/a>/", array($this, 'link2ch_callback'), $msg);
			
		// �܂�BBS / JBBS��������� 
		// http://kanto.machibbs.com/bbs/read.pl?BBS=kana&KEY=1034515019
		// http://jbbs.shitaraba.com/study/bbs/read.cgi?BBS=389&KEY=1036227774&LAST=100
		$ande = "(&|&amp;)";
		$msg = preg_replace_callback("{<a href=\"http://(([^/]+\.machibbs\.com|[^/]+\.machi\.to|jbbs\.livedoor\.jp|jbbs\.livedoor\.com|jbbs\.shitaraba\.com)(/[^/]+)?)/bbs/read\.(pl|cgi)\?BBS=([^&]+)(&|&amp;)KEY=([0-9]+)((&|&amp;)START=([0-9]+))?((&|&amp;)END=([0-9]+))?[^\"]*\"{$_conf['ext_win_target_at']}>(h?t?tp://[^<>]+)</a>}", array($this, 'linkMachi_callback'), $msg);
		$msg = preg_replace_callback("{<a href=\"http://(jbbs\.livedoor\.jp|jbbs\.livedoor\.com|jbbs\.shitaraba\.com)/bbs/read\.cgi/(\w+)/(\d+)/(\d+)/((\d+)?-(\d+)?)?[^\"]*?\"{$_conf['ext_win_target_at']}>(h?t?tp://[^<>]+)</a>}", array($this, 'linkJBBS_callback'), $msg);
		// $msg = preg_replace("/&(amp;)?ls=-/", "", $msg);// ��͈͎̔w��͏���
		
		// 2ch��bbspink�̔�
		$msg = preg_replace("/<a href=\"http:\/\/([^\/]+\.(2ch\.net|bbspink\.com))\/([^\/]+)\/\"{$_conf['ext_win_target_at']}>h?t?tp:\/\/([^\/]+(\.2ch\.net|\.bbspink\.com))\/([^\/]+)\/<\/a>/", "\\0 [<a href=\"{$_conf['subject_php']}?host=\\1&amp;bbs=\\3{$_conf['k_at_a']}\">��p2�ŊJ��</a>]", $msg);
		
		// 2ch��bbspink�̉ߋ����O
		$msg = preg_replace_callback("/<a href=\"(http:\/\/([^\/]+\.(2ch\.net|bbspink\.com))(\/[^\/]+)?\/([^\/]+)\/kako\/\d+(\/\d+)?\/(\d+)).html\"{$_conf['ext_win_target_at']}>h?t?tp:\/\/[^\/]+(\.2ch\.net|\.bbspink\.com)(\/[^\/]+)?\/[^\/]+\/kako\/\d+(\/\d+)?\/\d+.html<\/a>/", array($this, 'link2chkako_callback'), $msg);
		
		/*
		// �u���N���`�F�b�J
		if ($_conf['brocra_checker_use']) {
			$msg = preg_replace("/<a href=\"(s?https?:\/\/[{$str_in_url}]+)\"{$_conf['ext_win_target_at']}>(s?h?t?tps?:\/\/[{$str_in_url}]+)<\/a>/","<a href=\"\\1\"{$_conf['ext_win_target_at']}>\\2</a> [<a href=\"{$_conf['brocra_checker_url']}?{$_conf['brocra_checker_query']}=\\1\"{$_conf['ext_win_target_at']}>�`�F�b�N</a>]", $msg);
		}
		*/
		
		// �g�їp�O��URL�ϊ�
		if ($_conf['k_use_tsukin']) {
			$msg = preg_replace_callback("{<a href=\"(s?https?://[{$str_in_url}]+)\"{$_conf['ext_win_target_at']}>(s?h?t?tps?://[{$str_in_url}]+)</a>}", array($this, 'ktai_exturl_callback'), $msg);
		}
		
		// �摜URL�����N��ϊ�
		//if ($_conf['preview_thumbnail']) {
		if ($_conf['k_use_picto']) {
			$msg = preg_replace_callback("/<a href=\"(s?https?:\/\/[{$str_in_url}]+\.([jJ][pP][eE]?[gG]|[gG][iI][fF]|[pP][nN][gG]))\"{$_conf['ext_win_target_at']}>(s?h?t?tps?:\/\/[{$str_in_url}]+\.([jJ][pP][eE]?[gG]|[gG][iI][fF]|[pP][nN][gG]))<\/a>/", array($this, 'view_img_callback') ,$msg);
		}
		
		
		// �� ime
		$msg = preg_replace_callback("/<a href=\"(s?https?:\/\/[{$str_in_url}]+)\"{$_conf['ext_win_target_at']}>([^><]+)<\/a>/", array($this, 'ime_callback'), $msg);

		return $msg;
	}

	//=============================================================
	//�R�[���o�b�N���\�b�h
	//=============================================================
	/**
	 * ���g�їp�O��URL�ϊ�
	 */
	function ktai_exturl_callback($s)
	{
		global $_conf;
		
		$in_url = $s[1];
		
		// �ʋ΃u���E�U
		$tsukin_link = '';
		if ($_conf['k_use_tsukin']) {
			if (!preg_match('{\.(jpe?g|gif|png)$}i', $in_url)) {
				$tsukin_url = 'http://www.sjk.co.jp/c/w.exe?y='.urlencode($in_url);
				$tsukin_link = '<a href="'.$tsukin_url.'"'.$_conf['ext_win_target_at'].'>��</a>';
			}
		}
		/*
		// jig�u���E�UWEB http://bwXXXX.jig.jp/fweb/?_jig_=
		$jig_link = '';
		if (!preg_match('{\.(jpe?g|gif|png)$}i', $in_url)) {
			$jig_url = 'http://bw5032.jig.jp/fweb/?_jig_='.urlencode($in_url);
			$jig_link = '<a href="'.$jig_url.'"'.$_conf['ext_win_target_at'].'>j</a>';
		}
		*/
		
		$sepa ='';
		if ($tsukin_link && $jig_link) {
			$sepa = '|';
		}
		
		$ext_pre = '';
		if ($tsukin_link || $jig_link) {
			$ext_pre = '('.$tsukin_link.$sepa.$jig_link.')';
		}
		
		$r = $ext_pre.'<a href="' . $in_url . '"' . $_conf['ext_win_target_at'] . '>' . $s[2] . '</a>';
		
		return $r;
	}

	/**
	 * ime_callback
	 */
	function ime_callback($s)
	{
		global $_conf;
		
		$r = '<a href="' . P2Util::throughIme($s[1]) . '"' . $_conf['ext_win_target_at'] . '>' . $s[2] . '</a>';
		return $r;
	}
	
	/**
	 * ���p���X�ϊ�
	 */
	function quote_res_callback($s)
	{
		$rs = preg_replace_callback("/(&gt;|��)?(&gt;|��)?([0-9-]+)/", array($this, 'quote_res_devide_callback'), $s[0]);
		return $rs;
	}
	
	function quote_res_devide_callback($s)
	{
		global $_conf;
		
		$appointed_num = $s[3];
		$qsign = "$s[1]$s[2]";
		
		if ($appointed_num == "-") {
			return $s[0];
		}
		
		$read_url = "{$_conf['read_php']}?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;offline=1{$_conf['k_at_a']}&amp;ls={$appointed_num}";

		$qnum = $appointed_num + 0;
		if ($qnum > sizeof($this->thread->datlines)) { // �����߂��郌�X�͕ϊ����Ȃ�
			return $s[0];
		}
		$rs = <<<EOP
<a href="{$read_url}">{$qsign}{$appointed_num}</a>
EOP;
		return $rs;
	}
	
	/**
	 * 2ch bbspink �������N
	 */
	function link2ch_callback($s)
	{
		global $_conf;
		
		$read_url = "{$_conf['read_php']}?host=$s[1]&amp;bbs=$s[3]&amp;key=$s[4]&amp;ls=$s[6]{$_conf['k_at_a']}";

		$rs = <<<EORS
		<a href="{$read_url}">$s[7]</a>
EORS;

		return $rs;
	}
	
	/**
	 * �܂�BBS / JBBS���������  �������N
	 */
	function linkMachi_callback($s)
	{
		global $_conf;
	
	 	return "<a href=\"{$_conf['read_php']}?host={$s[1]}&amp;bbs={$s[4]}&amp;key={$s[6]}&amp;ls={$s[9]}-{$s[12]}{$_conf['k_at_a']}\">{$s[13]}</a>";
	 }
	
	/**
	 * JBBS���������  �������N 2
	 */
	function linkJBBS_callback($s)
	{
		global $_conf;
	
	 	return "<a href=\"{$_conf['read_php']}?host={$s[1]}/{$s[2]}&amp;bbs={$s[3]}&amp;key={$s[4]}&amp;ls={$s[5]}\"{$_conf['bbs_win_target_at']}>{$s[8]}</a>";
	}
	
	/**
	 * 2ch�ߋ����Ohtml
	 */
	function link2chkako_callback($s)
	{
		global $_conf;

		/*
		$msg = preg_replace_callback("/<a href=\"(http:\/\/([^\/]+(\.2ch\.net|\.bbspink\.com))(\/[^\/]+)?\/([^\/]+)\/kako\/\d+(\/\d+)?\/(\d+)).html\"{$_conf['ext_win_target_at']}>h?t?tp:\/\/[^\/]+(\.2ch\.net|\.bbspink\.com)(\/[^\/]+)?\/[^\/]+\/kako\/\d+(\/\d+)?\/\d+.html<\/a>/", array($this, 'link2chkako_callback'), $msg);
		*/
		$kakolog_uri = $s[1];
		$kakolog_uri_en = urlencode($kakolog_uri);
		$host = $s[2]; $bbs = $s[5]; $key = $s[7];
		$read_url="{$_conf['read_php']}?host={$host}&amp;bbs={$bbs}&amp;key={$key}&amp;kakolog={$kakolog_uri_en}{$_conf['k_at_a']}";

			$rs=<<<EOP
<a href="{$read_url}">{$kakolog_uri}.html</a>
EOP;

		return $rs;
	}
	
	/**
	 * �摜�|�b�v�A�b�v�ϊ�
	 */
	function view_img_callback($s)
	{
		global $_conf;
	
		$in_url = $s[1];
		
		/*
		$img_tag = <<<EOIMG
<img class="thumbnail" src="{$in_url}" height="{$_conf['pre_thumb_height']}" weight="{$_conf['pre_thumb_width']}" hspace="4" vspace="4" align="middle">
EOIMG;
		*/
		
		// �s�N�g
		$picto_tag = '';
		if ($_conf['k_use_picto']) {
			if (preg_match('{^(http://)(.+)}', $in_url, $m)) {
				$picto_url = $m[1].'pic.to/'.$m[2];
				$picto_tag = '<a href="'.$picto_url.'">(��)</a> ';
			}
		}
		
		$rs = "{$picto_tag}<a href=\"{$in_url}\">{$img_tag}{$s[3]}</a>";
		
		return $rs;
	}


}
?>