<?php
// p2 - �������ݗ����̃N���X

require_once './p2util.class.php';

//======================================================================
//�N���X
//======================================================================

//���X�L���̃N���X
class ResArticle{
	var $name;
	var $mail;
	var $daytime;
	var $msg;
	var $ttitle;
	var $host;
	var $bbs;
	var $itaj;
	var $key;
	var $order; //�L���ԍ�
}

//�������ݗ����̃N���X
class ResHist{
	var $articles; // �N���X ResArticle �̃I�u�W�F�N�g���i�[����z��
	var $num; // �i�[���ꂽ BrdMenuCate �I�u�W�F�N�g�̐�
	
	var $resrange; // array( 'start' => i, 'to' => i, 'nofirst' => bool )
	
	function ResHist()
	{
		$this->articles = array();
		$this->num = 0;
	}
	
	function addRes($aResArticle)
	{
		$this->articles[] = $aResArticle;
		$this->num++;
	}	
	
	//���X�L����\�����郁�\�b�h ===============================================
	function showArticles()
	{
		global $_conf, $STYLE;
		global $k_at_a, $k_at_q, $k_input_ht;
	
		$tores="<dl>";
		
		foreach ($this->articles as $a_res) {
			$htm['daytime'] = htmlspecialchars($a_res->daytime);
			$href_ht = "";
			if($a_res->key){
				$href_ht = $_conf['read_php']."?host=".$a_res->host."&amp;bbs=".$a_res->bbs."&amp;key=".$a_res->key."{$k_at_a}#footer";
			}
			$info_view_ht = <<<EOP
		<a href="info.php?host={$a_res->host}&amp;bbs={$a_res->bbs}&amp;key={$a_res->key}{$k_at_a}" target="_self" onClick="return OpenSubWin('info.php?host={$a_res->host}&amp;bbs={$a_res->bbs}&amp;key={$a_res->key}&amp;popup=1',{$STYLE['info_pop_size']},0,0)">���</a>
EOP;

			$res_ht = "<dt><input name=\"checked_hists[]\" type=\"checkbox\" value=\"{$a_res->order},,,,{$htm['daytime']}\"> ";
			$res_ht .= "{$a_res->order} �F"; //�ԍ�
			$res_ht .= "<span class=\"name\"><b>{$a_res->name}</b></span> �F"; //���O
			if($a_res->mail){$res_ht .= "{$a_res->mail} �F";} //���[��
			$res_ht .= "{$htm['daytime']}</dt>\n"; //���t��ID
			$res_ht .= "<dd><a href=\"{$_conf['subject_php']}?host={$a_res->host}&amp;bbs={$a_res->bbs}{$k_at_a}\" target=\"subject\">{$a_res->itaj}</a> / ";
			if($href_ht){
				$res_ht .= "<a href=\"{$href_ht}\"><b>{$a_res->ttitle}</b></a> - {$info_view_ht}\n";
			}elseif($a_res->ttitle){
				$res_ht .= "<b>{$a_res->ttitle}</b>\n";
			}
			$res_ht .= "<br><br>";
			$res_ht .= "{$a_res->msg}<br><br></dd>\n"; //���e

			$tores .= $res_ht;
		}
		
		$tores .= "</dl>";
		
		echo $tores;
	}
	
	//�g�їp�i�r��\�����郁�\�b�h=====================================================
	function showNaviK($position)
	{
		global $_conf;
		global $k_at_a, $k_at_q, $k_input_ht;

		//�\��������====================
		$list_disp_all_num = $this->num;
		$list_disp_range = $_conf['k_rnum_range'];
		
		if ($_GET['from']) {
			$list_disp_from = $_GET['from'];
			if ($_GET['end']) {
				$list_disp_range = $_GET['end'] - $list_disp_from + 1;
				if ($list_disp_range < 1) {
					$list_disp_range = 1;
				}
			}
		} else {
			$list_disp_from = $this->num-$list_disp_range+1;
			if ($list_disp_from < 1) {
				$list_disp_from = 1;
			}
		}
		$disp_navi = P2Util::getListNaviRange($list_disp_from, $list_disp_range, $list_disp_all_num);
		
		$this->resrange['start'] = $disp_navi['from'];
		$this->resrange['to'] = $disp_navi['end'];
		$this->resrange['nofirst'] = false;

		if ($disp_navi['from'] > 1) {
			if ($position == "footer") {
				$mae_ht = <<<EOP
		<a {$_conf['accesskey']}="{$_conf['k_accesskey']['prev']}" href="read_res_hist.php?from={$disp_navi['mae_from']}{$k_at_a}">{$_conf['k_accesskey']['prev']}.�O</a>
EOP;
			} else {
				$mae_ht = <<<EOP
		<a href="read_res_hist.php?from={$disp_navi['mae_from']}{$k_at_a}">�O</a>
EOP;
			}
		}
		if ($disp_navi['end'] < $list_disp_all_num) {
			if ($position == "footer") {
				$tugi_ht = <<<EOP
		<a {$_conf['accesskey']}="{$_conf['k_accesskey']['next']}" href="read_res_hist.php?from={$disp_navi['tugi_from']}{$k_at_a}">{$_conf['k_accesskey']['next']}.��</a>
EOP;
			} else {
				$tugi_ht = <<<EOP
		<a href="read_res_hist.php?from={$disp_navi['tugi_from']}{$k_at_a}">��</a>
EOP;
			}
		}
		
		if (!$disp_navi['all_once']) {
			$list_navi_ht = <<<EOP
		{$disp_navi['range_st']}{$mae_ht} {$tugi_ht}
EOP;
		}

		echo $list_navi_ht;
	
	}
	
	//���X�L����\�����郁�\�b�h �g�їp============================================
	function showArticlesK()
	{
		global $_conf;
		global $k_at_a, $k_at_q, $k_input_ht;
	
		//=============
		$tores="";
		foreach($this->articles as $a_res){
			$htm['daytime'] = htmlspecialchars($a_res->daytime);
			
			if($a_res->order < $this->resrange['start'] or $a_res->order > $this->resrange['to']){
				continue;
			}
		
			$href_ht="";
			if($a_res->key){
				$href_ht = $_conf['read_php']."?host=".$a_res->host."&amp;bbs=".$a_res->bbs."&amp;key=".$a_res->key."{$k_at_a}#footer";
			}
		
		//�傫������
		if(! $_GET['k_continue']){
			$msg=$a_res->msg;
			if( strlen($msg) > $_conf['ktai_res_size']){
				$msg = substr($msg, 0, $_conf['ktai_ryaku_size']);
				
				//������<br>������Ύ�菜��
				if( substr($msg, -1)==">" ){
					$msg = substr($msg, 0, strlen($msg)-1);
				}
				if( substr($msg, -1)=="r" ){
					$msg = substr($msg, 0, strlen($msg)-1);
				}
				if( substr($msg, -1)=="b" ){
					$msg = substr($msg, 0, strlen($msg)-1);
				}
				if( substr($msg, -1)=="<" ){
					$msg = substr($msg, 0, strlen($msg)-1);
				}
				
				$msg = $msg."  ";
				$a_res->msg = $msg."<a href=\"read_res_hist?from={$a_res->order}&amp;end={$a_res->order}&amp;k_continue=1{$k_at_a}\">��</a>";
			}
		}

			$res_ht = "[$a_res->order]"; //�ԍ�
			$res_ht .= "{$a_res->name}:"; //���O
			if($a_res->mail){$res_ht .= "{$a_res->mail}:";} //���[��
			$res_ht .= "{$htm['daytime']}<br>\n"; //���t��ID
			$res_ht .= "<a href=\"{$_conf['subject_php']}?host={$a_res->host}&amp;bbs={$a_res->bbs}{$k_at_a}\">{$a_res->itaj}</a> / ";
			if($href_ht){
				$res_ht .= "<a href=\"{$href_ht}\">{$a_res->ttitle}</a>\n";
			}elseif($a_res->ttitle){
				$res_ht .= "{$a_res->ttitle}\n";
			}
			$res_ht .= "<br>";
			$res_ht .= "{$a_res->msg}<hr>\n"; //���e
			
			$tores .= $res_ht;
	
		}
		
		echo $tores;

	}
}

?>