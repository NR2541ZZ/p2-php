<?php
/*
	p2 - �{�[�h���j���[�N���X for menu.php
*/

require_once './filectl.class.php';

/**
 * �{�[�h���j���[�N���X
 */
class BrdMenu{

	var $categories; // �N���X BrdMenuCate �̃I�u�W�F�N�g���i�[����z��
	var $num; // �i�[���ꂽ BrdMenuCate �I�u�W�F�N�g�̐�
	var $format; // html�`�����Abrd�`����("html", "brd")
	var $cate_match; // �J�e�S���[�}�b�`
	var $ita_match; // �}�b�`
	
	function BrdMenu(){
		$this->num=0;
	}
	
	//�J�e�S���[��ǉ����郁�\�b�h=================================
	function addBrdMenuCate($aBrdMenuCate)
	{
		$this->categories[] = $aBrdMenuCate;
		$this->num++;
	}
	
	//�p�^�[���}�b�`�̌`����o�^���郁�\�b�h==========================
	function setBrdMatch($brdName)
	{
		if( preg_match("/html?$/", $brdName) ){ //html�`��
			$this->format = "html";
			$this->cate_match="/<B>(.+)<\/B><BR>.*$/i";
			$this->ita_match="/^<A HREF=\"?(http:\/\/(.+)\/([^\/]+)\/([^\/]+\.html?)?)\"?( target=\"?_blank\"?)?>(.+)<\/A>(<br>)?$/i";
		}else{// brd�`��
			$this->format = "brd";
			$this->cate_match="/^(.+)	([0-9])$/";
			$this->ita_match="/^\t?(.+)\t(.+)\t(.+)$/";
		}
	}

	//�f�[�^��ǂݍ���ŁA�J�e�S���Ɣ�o�^���郁�\�b�h===================
	function setBrdList($data)
	{
		global $_conf, $word, $word_fm, $mikke;
		
		if(!$data){return false;}

		//���OURL���X�g
		$not_bbs_list = array("http://members.tripod.co.jp/Backy/del_2ch/");
	
		foreach($data as $v){
			$v = rtrim($v);
			
			//�J�e�S����T��
			if( preg_match($this->cate_match, $v, $matches) ){
				$aBrdMenuCate = new BrdMenuCate;
				$aBrdMenuCate->name = $matches[1];
				if($this->format == "brd"){ $aBrdMenuCate->is_open = $matches[2]; }
				$this->addBrdMenuCate($aBrdMenuCate);
			//��T��
			}elseif(preg_match($this->ita_match, $v, $matches)){
				if($this->format == "html"){// html�`���Ȃ珜�OURL���O��
					foreach($not_bbs_list as $not_a_bbs){
						if($not_a_bbs==$matches[1]){ continue 2; }
					}
				}
				$aBrdMenuIta = new BrdMenuIta;
				if($this->format == "html"){  // html�`��
					$aBrdMenuIta->host = $matches[2];
					$aBrdMenuIta->bbs = $matches[3];
					$itaj_match = $matches[6];
				}else{ //brd�`��
					$aBrdMenuIta->host = $matches[1];
					$aBrdMenuIta->bbs = $matches[2];
					$itaj_match = $matches[3];
				}
				$aBrdMenuIta->itaj = rtrim($itaj_match);
				$aBrdMenuIta->itaj_en = base64_encode($aBrdMenuIta->itaj);
				
				// �����}�b�` ===================================
				$aBrdMenuIta->itaj_ht = $aBrdMenuIta->itaj;

				// ���K�\������
				if ($word_fm) {
					if (StrCtl::filterMatch($word_fm, $aBrdMenuIta->itaj)) {
						$this->categories[$this->num-1]->match_attayo = true;
						$GLOBALS['ita_mikke']['num']++;

						// �}�[�L���O
						$aBrdMenuIta->itaj_ht = StrCtl::filterMarking($word_fm, $aBrdMenuIta->itaj);
						
					} else { // ������������Ȃ��āA����Ɍg�т̎�
						if ($_conf['ktai']) {
							continue;
						}
					}
				}

				if($this->num){
					$this->categories[$this->num-1]->addBrdMenuIta($aBrdMenuIta);
				}
			}
		}
	}

	/**
	* brd�t�@�C���𐶐����郁�\�b�h
	*
	* @return	string	brd�t�@�C���̃p�X
	*/
	function makeBrdFile($cachefile)
	{
	global $_conf, $_info_msg_ht, $word;
	
		$p2brdfile = $cachefile.".p2.brd";
		FileCtl::make_datafile($p2brdfile, $_conf['p2_perm']);
		$data = @file($cachefile);
		$this->setBrdMatch($cachefile); //�p�^�[���}�b�`�`����o�^
		$this->setBrdList($data); //�J�e�S���[�Ɣ��Z�b�g
		if($this->categories){
			foreach($this->categories as $cate){
				if($cate->num > 0){
					$cont .= $cate->name."\t0\n";
					foreach($cate->menuitas as $mita){
						$cont .= "\t{$mita->host}\t{$mita->bbs}\t{$mita->itaj}\n";
					}
				}
			}
		}

		if($cont){
			$fp = @fopen($p2brdfile, 'wb') or die("p2 error: {$p2brdfile} ���X�V�ł��܂���ł���");
			@flock($fp, LOCK_EX);
			fputs($fp, $cont);
			@flock($fp, LOCK_UN);
			fclose($fp);
			return $p2brdfile;
		}else{
			if(!$word){
				$_info_msg_ht .=  "<p>p2 �G���[: {$cachefile} ������j���[�𐶐����邱�Ƃ͂ł��܂���ł����B</p>\n";
			}
			return false;
		}
	}
	
}

//==========================================================
// �{�[�h���j���[�J�e�S���[�N���X
//==========================================================
class BrdMenuCate{
	var $name; //�J�e�S���[�̖��O
	var $menuitas; //�N���XBrdMenuIta�̃I�u�W�F�N�g���i�[����z��
	var $num; //�i�[���ꂽBrdMenuIta�I�u�W�F�N�g�̐�
	var $is_open; //�J���(bool)
	var $match_attayo;
	
	function BrdMenuCate(){
		$this->num=0;
	}
	
	function addBrdMenuIta($aBrdMenuIta){
		$this->menuitas[] = $aBrdMenuIta;
		$this->num++;
	}
	
}

//==========================================================
// �{�[�h���j���[�N���X
//==========================================================
class BrdMenuIta{
	var $host;
	var $bbs;
	var $itaj;
	var $itaj_en;
	var $itaj_ht;
}

?>