<?php
// p2 -  �T�u�W�F�N�g -  �c�[���o�[�\��
// for subject.php

require_once P2_LIB_DIR . '/sb_toolbar.funcs.php';

// ���HTML�\���p�ϐ��� sb_header.inc.php �ɂĐݒ�

$sb_tool_i = updateSbToolI(); // int

//=============================================================================
// HTML�v�����g
//=============================================================================
?>
<table id="sbtoolbar<?php eh($sb_tool_i); ?>" class="toolbar" cellspacing="0">
	<tr>
		<td align="left" valign="middle" nowrap>
			<?php echo $ptitle_ht; ?>
		</td>
		<td align="left" valign="middle" nowrap>
			<form class="toolbar" method="GET" action="<?php eh($_conf['subject_php']) ?>" accept-charset="<?php eh($_conf['accept_charset']); ?>" target="_self">
				<?php echo $sb_form_hidden_ht; ?>
				<input type="submit" name="submit_refresh" value="�X�V">
				<?php echo $sb_disp_num_ht; ?>
			</form>
		</td>
		<td align="left" valign="middle" nowrap>
			<?php echo $filter_form_ht; ?>
		</td>
		<td align="left" valign="middle" nowrap>
			<?php echo $edit_ht; ?>
		</td>
		<td align="right" valign="middle" nowrap>
			<?php echo getSbToolbarShinchakuMatomeHtml($aThreadList, $shinchaku_num); ?>
			<span class="time"><?php eh($reloaded_time); ?></span>
			<?php echo getSbToolAnchorHtml($sb_tool_i); ?>
		</td>
	</tr>
</table>
<?php
