<?php
/*
	+live - �f�t�H���g�X���b�h�\�� ../showthreadpc.class.php ���ǂݍ��܂��
*/

// �ԍ�
$tores .= "<dt id=\"r{$i}\" style=\"{$highlight_res}\">";

if ($this->thread->onthefly) {
	$GLOBALS['newres_to_show_flag'] = true;
	//�ԍ� (�I���U�t���C��)
	$tores .= "<span class=\"ontheflyresorder spmSW\"{$spmeh}>{$i}</span> �F";
} elseif ($i > $this->thread->readnum) {
	$GLOBALS['newres_to_show_flag'] = true;
	// �ԍ� (�V�����X��)
	$tores .= "<font color=\"{$STYLE['read_newres_color']}\" class=\"spmSW\"{$spmeh}>{$i}</font> �F";
} elseif ($_conf['expack.spm.enabled']) {
	// �ԍ� (SPM)
	$tores .= "<span class=\"spmSW\"{$spmeh}>{$i}</span> �F";
} else {
	// �ԍ�
	$tores .= "{$i} �F";
}

// ��Q�ƃ��X�|�b�v�A�b�v
$tores .= "$ref_res_pp";

// ���X�{�^��
$tores .= "$res_button";

// ���O
$tores .= "&nbsp;<span class=\"name\"><b>{$name}</b></span>�F";

// ���[��
$tores .= "{$mail} �F";

// ���t��ID
$tores .= $date_id;
if ($this->am_side_of_id) {
	$tores .= ' ' . $this->activeMona->getMona($res_id);
}

$tores .= "</dt>";

// ���e
$tores .= "<dd {$res_dblclc} id=\"{$res_id}\"{$automona_class} style=\"{$highlight_res}\">{$msg}<br><br></dd>\n";

// ���X�|�b�v�A�b�v�p���p
$tores .= $rpop;

?>