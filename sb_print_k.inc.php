<?php
// p2 �X���b�h�T�u�W�F�N�g�\���֐� �g�їp
// for subject.php

/**
 * sb_print - �X���b�h�ꗗ��\������ (<tr>�`</tr>)
 */
function sb_print_k($aThreadList)
{
	global $_conf, $browser, $_conf, $sb_view, $p2_setting, $STYLE;
	global $sb_view;
		
	//=================================================
	
	if (!$aThreadList->threads) {
		if ($aThreadList->spmode == "fav" && $sb_view == "shinchaku") {
			echo "<p>���C�ɽڂɐV���Ȃ�������</p>";
		} else {
			echo "<p>�Y����޼ު�Ă͂Ȃ�������</p>";
		}
		return;
	}
	
	// �ϐ� ================================================
	
	// >>1
	if (ereg("news", $aThreadList->bbs) || $aThreadList->bbs=="bizplus" || $aThreadList->spmode=="news") {
		// �q�ɂ͏���
		if ($aThreadList->spmode != "soko") {
			$only_one_bool = true;
		}
	}

	// ��
	if ($aThreadList->spmode and $aThreadList->spmode != "taborn" and $aThreadList->spmode != "soko") {
		$ita_name_bool = true;
	}

	$norefresh_q = "&amp;norefresh=1";

	// �\�[�g ==================================================

	// �X�y�V�������[�h��
	if ($aThreadList->spmode) { 
		$sortq_spmode = "&amp;spmode={$aThreadList->spmode}";
		// ���ځ[��Ȃ�
		if ($aThreadList->spmode == "taborn" or $aThreadList->spmode == "soko") {
			$sortq_host = "&amp;host={$aThreadList->host}";
			$sortq_ita = "&amp;bbs={$aThreadList->bbs}";
		}
	} else {
		$sortq_host = "&amp;host={$aThreadList->host}";
		$sortq_ita = "&amp;bbs={$aThreadList->bbs}";
	}
	
	$midoku_sort_ht = "<a href=\"{$_conf['subject_php']}?sort=midoku{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}{$_conf['k_at_a']}\">�V��</a>";

	//=====================================================
	// �{�f�B
	//=====================================================

	// spmode������΃N�G���[�ǉ�
	if ($aThreadList->spmode) {$spmode_q = "&amp;spmode={$aThreadList->spmode}";}

	$i = 0;
	foreach ($aThreadList->threads as $aThread) {
		$i++;
		$midoku_ari = "";
		$anum_ht = ""; //#r1
		
		$bbs_q = "&amp;bbs=".$aThread->bbs;
		$key_q = "&amp;key=".$aThread->key;

		if ($aThreadList->spmode!="taborn") {
			if (!$aThread->torder) {$aThread->torder=$i;}
		}

		// �V�����X�� =============================================
		$unum_ht = "";
		// �����ς�
		if ($aThread->isKitoku()) { 
			$unum_ht="{$aThread->unum}";
		
			$anum = $aThread->rescount - $aThread->unum +1 - $_conf['respointer'];
			if ($anum > $aThread->rescount) { $anum = $aThread->rescount; }
			$anum_ht = "#r{$anum}";
			
			// �V������
			if ($aThread->unum > 0) { 
				$midoku_ari = true;
				$unum_ht = "<font color=\"#ff6600\">{$aThread->unum}</font>";
			}
		
			// subject.txt�ɂȂ���
			if (!$aThread->isonline) {
				// �듮��h�~�̂��߃��O�폜��������b�N
				$unum_ht = "-"; 
			}	

			$unum_ht = "[".$unum_ht."]";
		}
		
		// �V�K�X��
		if ($aThread->new) { 
			$unum_ht = "[<font color=\"#ff0000\">�V</font>]";
		}
				
		//�����X�� =============================================
		$rescount_ht = "{$aThread->rescount}";

		// �� ============================================
		if ($ita_name_bool) {
			$ita_name = $aThread->itaj ? $aThread->itaj : $aThread->bbs;
			$ita_name_hd = htmlspecialchars($ita_name);
			// $htm['ita'] = "(<a href=\"{$_conf['subject_php']}?host={$aThread->host}{$bbs_q}{$_conf['k_at_a']}\">{$ita_name_hd}</a>)";
			$htm['ita'] = "({$ita_name_hd})";
		}
		
		// torder(info) =================================================
		/*
		if ($aThread->fav) { //���C�ɃX��
			$torder_st = "<b>{$aThread->torder}</b>";
		} else {
			$torder_st = $aThread->torder;
		}
		$torder_ht = "<a id=\"to{$i}\" class=\"info\" href=\"info.php?host={$aThread->host}{$bbs_q}{$key_q}{$_conf['k_at_a']}\">{$torder_st}</a>";
		*/
		$torder_ht = $aThread->torder;
		
		// title =================================================		
		$rescount_q = "&amp;rc=".$aThread->rescount;
		
		// dat�q�� or �a���Ȃ�
		if ($aThreadList->spmode == "soko" || $aThreadList->spmode == "palace") { 
			$rescount_q = "";
			$offline_q = "&amp;offline=true";
			$anum_ht = "";
		}
		
		// �^�C�g�����擾�Ȃ�
		if (!$aThread->ttitle_ht) {
			// ��������̃^�C�g���Ȃ̂Ōg�ёΉ�URL�ł���K�v�͂Ȃ�
			//if (P2Util::isHost2chs($aThread->host)) {
			//	$aThread->ttitle_ht = "http://c.2ch.net/z/-/{$aThread->bbs}/{$aThread->key}/";
			//}else{
				$aThread->ttitle_ht = "http://{$aThread->host}/test/read.cgi/{$aThread->bbs}/{$aThread->key}/";		
			//}
		}	

		// �S�p�p���X�y�[�X�J�i�𔼊p��
		$aThread->ttitle_ht = mb_convert_kana($aThread->ttitle_ht, 'ask');

		$aThread->ttitle_ht = $aThread->ttitle_ht." (".$rescount_ht.")";
		
		// �V�K�X��
		if ($aThread->new) { 
			$classtitle_q = " class=\"thre_title_new\"";
		} else {
			$classtitle_q = " class=\"thre_title\"";
		}

		$thre_url = "{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}{$rescount_q}{$offline_q}{$_conf['k_at_a']}{$anum_ht}";
	
		// �I�����[>>1 =============================================
		if ($only_one_bool) {
			$one_ht = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;one=true{$_conf['k_at_a']}\">&gt;&gt;1</a>";
		}
		
		//�A�N�Z�X�L�[=====
		/*
		$access_ht = "";
		if ($aThread->torder >= 1 and $aThread->torder <= 9) {
			$access_ht = " {$_conf['accesskey']}=\"{$aThread->torder}\"";
		}
		*/
		
		//====================================================================================
		// �X���b�h�ꗗ table �{�f�B HTML�v�����g <tr></tr> 
		//====================================================================================

		//�{�f�B
		echo <<<EOP
<div>
	$unum_ht{$aThread->torder}.<a href="{$thre_url}">{$aThread->ttitle_ht}</a>{$htm['ita']}
</div>
EOP;
	}

}

?>