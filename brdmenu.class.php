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
	var $cate_match; // �J�e�S���[�}�b�`�`��
	var $ita_match; // �}�b�`�`��
	
	function BrdMenu()
	{
		$this->num = 0;
	}
	
	/**
	 * �J�e�S���[��ǉ�����
	 */
	function addBrdMenuCate(&$aBrdMenuCate)
	{
		$this->categories[] =& $aBrdMenuCate;
		$this->num++;
	}
	
	/**
	* �p�^�[���}�b�`�̌`����o�^����
	*/
	function setBrdMatch($brdName)
	{
		// html�`��
		if (preg_match("/(html?|cgi)$/", $brdName)) {
			$this->format = "html";
			$this->cate_match = "/<B>(.+)<\/B><BR>.*$/i";
			$this->ita_match = "/^<A HREF=\"?(http:\/\/(.+)\/([^\/]+)\/([^\/]+\.html?)?)\"?( target=\"?_blank\"?)?>(.+)<\/A>(<br>)?$/i";
		// brd�`��
		} else {
			$this->format = "brd";
			$this->cate_match = "/^(.+)	([0-9])$/";
			$this->ita_match = "/^\t?(.+)\t(.+)\t(.+)$/";
		}
	}

	/**
	* �f�[�^��ǂݍ���ŁA�J�e�S���Ɣ�o�^����
	*/
	function setBrdList($data)
	{
		global $_conf, $word, $word_fm;
		
		if (empty($data)) { return false; }

		// ���OURL���X�g
		$not_bbs_list = array("http://members.tripod.co.jp/Backy/del_2ch/");
	
		foreach ($data as $v) {
			$v = rtrim($v);
			
			// �J�e�S����T��
			if (preg_match($this->cate_match, $v, $matches)) {
				$aBrdMenuCate =& new BrdMenuCate($matches[1]);
				if ($this->format == 'brd') {
					$aBrdMenuCate->is_open = $matches[2];
				}
				$this->addBrdMenuCate(&$aBrdMenuCate);
				
			// ��T��
			} elseif (preg_match($this->ita_match, $v, $matches)) {
				// html�`���Ȃ珜�OURL���O��
				if ($this->format == 'html') {
					foreach ($not_bbs_list as $not_a_bbs) {
						if ($not_a_bbs == $matches[1]) { continue 2; }
					}
				}
				$aBrdMenuIta =& new BrdMenuIta();
				// html�`��
				if ($this->format == 'html') {
					$aBrdMenuIta->host = $matches[2];
					$aBrdMenuIta->bbs = $matches[3];
					$itaj_match = $matches[6];
				// brd�`��
				} else {
					$aBrdMenuIta->host = $matches[1];
					$aBrdMenuIta->bbs = $matches[2];
					$itaj_match = $matches[3];
				}
				$aBrdMenuIta->setItaj(rtrim($itaj_match));
				
				// �����}�b�` ===================================

				// ���K�\������
				if ($word_fm) {
					if (StrCtl::filterMatch($word_fm, $aBrdMenuIta->itaj)) {
						$this->categories[$this->num-1]->match_attayo = true;
						$GLOBALS['ita_mikke']['num']++;

						// �}�[�L���O
						$aBrdMenuIta->itaj_ht = StrCtl::filterMarking($word_fm, $aBrdMenuIta->itaj);
					
					// ������������Ȃ��āA����Ɍg�т̎�
					} else {
						if ($_conf['ktai']) {
							continue;
						}
					}
				}

				if ($this->num) {
					$this->categories[$this->num-1]->addBrdMenuIta($aBrdMenuIta);
				}
			}
		}
	}

	/**
	* brd�t�@�C���𐶐�����
	*
	* @return	string	brd�t�@�C���̃p�X
	*/
	function makeBrdFile($cachefile)
	{
		global $_conf, $_info_msg_ht, $word;
	
		$p2brdfile = $cachefile.".p2.brd";
		FileCtl::make_datafile($p2brdfile, $_conf['p2_perm']);
		$data = @file($cachefile);
		$this->setBrdMatch($cachefile); // �p�^�[���}�b�`�`����o�^
		$this->setBrdList($data); // �J�e�S���[�Ɣ��Z�b�g
		if ($this->categories) {
			foreach ($this->categories as $cate) {
				if ($cate->num > 0) {
					$cont .= $cate->name."\t0\n";
					foreach ($cate->menuitas as $mita) {
						$cont .= "\t{$mita->host}\t{$mita->bbs}\t{$mita->itaj}\n";
					}
				}
			}
		}

		if ($cont) {
			if (!FileCtl::file_write_contents($p2brdfile, $cont)) {
				die("p2 error: {$p2brdfile} ���X�V�ł��܂���ł���");
			}
			return $p2brdfile;
		} else {
			if (!$word) {
				$_info_msg_ht .=  "<p>p2 �G���[: {$cachefile} ������j���[�𐶐����邱�Ƃ͂ł��܂���ł����B</p>\n";
			}
			return false;
		}
	}
	
}

/**
* �{�[�h���j���[�J�e�S���[�N���X
*/
class BrdMenuCate{

	var $name;		// �J�e�S���[�̖��O
	var $menuitas;	// �N���XBrdMenuIta�̃I�u�W�F�N�g���i�[����z��
	var $num;		// �i�[���ꂽBrdMenuIta�I�u�W�F�N�g�̐�
	var $is_open;	// �J���(bool)
	var $match_attayo;
	
	/**
	* �R���X�g���N�^
	*/
	function BrdMenuCate($name)
	{
		$this->num = 0;
		$this->menuitas = array();
		
		$this->name = $name;
	}
	
	function addBrdMenuIta(&$aBrdMenuIta)
	{
		$this->menuitas[] =& $aBrdMenuIta;
		$this->num++;
	}
	
}

/**
* �{�[�h���j���[�N���X
*/
class BrdMenuIta{
	var $host;
	var $bbs;
	var $itaj;	// ��
	var $itaj_en;	// �����G���R�[�h��������
	var $itaj_ht;	// HTML�ŏo�͂�����i�t�B���^�����O�������́j
	
	function setItaj($itaj)
	{
		$this->itaj = $itaj;
		$this->itaj_en = rawurlencode(base64_encode($this->itaj));
		$this->itaj_ht = htmlspecialchars($this->itaj);
	}
}

?>
