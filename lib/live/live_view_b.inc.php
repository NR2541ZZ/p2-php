<?php
/*
	+live - �����p�X���b�h�\�� B-Type ../showthreadpc.class.php ���ǂݍ��܂��
*/

// �I�[�g�����[�h�̔ŐV�����X�擪�ɖڈ󃉃C����}��
$live_newline = "<table id=\"r{$i}\" cellspacing=\"2\" cellpadding=\"0\" style=\"border-top: {$STYLE['live_b_n']}; {$highlight_res}\" width=\"100%\"><tr>";
$live_oldline = "<table id=\"r{$i}\" cellspacing=\"2\" cellpadding=\"0\" style=\"border-top: {$STYLE['live_b_l']}; {$highlight_res}\" width=\"100%\"><tr>";
// 
$live_td = "<td colspan=\"2\" style=\"color:{$STYLE['read_color']}; font-size:{$STYLE['live_font-size']}; background-color:{$STYLE['live2_color']};\" valign=\"top\">";
// �V�����X�̔ԍ��F
$live_newnum = "<span class=\"spmSW\"{$spmeh}><b style=\"color:{$STYLE['read_newres_color']};\">{$i}</b></span>�F";
$live_oldnum = "<span class=\"spmSW\"{$spmeh}>{$i}</span>�F";

// �e�[�u���J�n �` �ԍ�
if ($this->thread->onthefly) {
	// �ԍ� (�I���U�t���C)
	$GLOBALS['newres_to_show_flag'] = true;
	$tores .= "{$live_oldline}{$live_td}<span class=\"ontheflyresorder spmSW\"{$spmeh}>{$i}</span>�F";
} elseif ($i == 1) {
	// �ԍ� (1)
	if ($this->thread->readnum > 1) {
		$tores .= "{$live_oldline}{$live_td}{$live_oldnum}";
	} else {
		$tores .= "{$live_oldline}{$live_td}{$live_newnum}";
	}
} elseif ($i == $this->thread->readnum +1) {
	// �ԍ� (�擪�V�����X)
	$GLOBALS['newres_to_show_flag'] = true;
	if ($nldr_ylr_d) {
		$tores .= "{$live_newline}{$live_td}{$live_newnum}";
	} else {
		$tores .= "{$live_oldline}{$live_td}{$live_newnum}";
	}
} elseif ($i > $this->thread->readnum) {
	// �ԍ� (�㑱�V�����X)
	$tores .= "{$live_oldline}{$live_td}{$live_newnum}";
} elseif ($_conf['expack.spm.enabled']) {
	// �ԍ� (SPM)
	$tores .= "{$live_oldline}{$live_td}{$live_oldnum}";
} else {
	// �ԍ�
	$tores .= "{$live_oldline}{$live_td}{$i}�F";
}

// ���O
$tores .= "<span class=\"name\"><b>{$name}</b></span>�F";

// ���[��
$tores .= "{$mail}�F";

// ���t��ID
$tores .= "{$date_id}";

if ($this->am_side_of_id) {
	$tores .= ' ' . $this->activeMona->getMona($res_id);
}

// �d�� & ���X�{�^�� & ��Q�ƃ��X�|�b�v�A�b�v
$tores .= "</td></tr><tr><td width=\"10%\" align=\"center\">&nbsp;{$ref_res_pp}{$res_button}</td>";

// ���e
$tores .= "<td {$res_dblclc} width=\"90%\" id=\"{$res_id}\"{$automona_class} style=\"color:{$STYLE['read_color']}; font-size: {$STYLE['read_fontsize']};\">{$msg}�@</td>";

// �e�[�u���I��
$tores .= "</tr></table>\n";

// ���X�|�b�v�A�b�v�p���p
$tores .= $rpop;

?>