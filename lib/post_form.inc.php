<?php
/**
 *  p2 �������݃t�H�[��
 */

if (!empty($_conf['ktai'])) {
    $htm['k_br'] = '<br>';
} else {
    $htm['k_br'] = '';
}

// �����R�[�h����p�������擪�Ɏd���ނ��Ƃ�mb_convert_variables()�̎��������������
$htm['post_form'] = <<<EOP
{$htm['disable_js']}
{$htm['resform_ttitle']}
{$htm['orig_msg']}
<form id="resform" method="POST" action="./post.php" accept-charset="{$_conf['accept_charset']}" onsubmit="disableSubmit(this)">
    <input type="hidden" name="detect_hint" value="����">
    {$htm['subject']}
    {$htm['maru_post']} ���O�F <input id="FROM" name="FROM" type="text" value="{$hd['FROM']}"{$name_size_at}>{$htm['k_br']} 
     E-mail : <input id="mail" name="mail" type="text" value="{$hd['mail']}"{$mail_size_at}{$on_check_sage}>
    {$sage_cb_ht}
    <textarea id="MESSAGE" name="MESSAGE" rows="{$STYLE['post_msg_rows']}"{$msg_cols_at} wrap="{$wrap}">{$hd['MESSAGE']}</textarea>{$htm['k_br']}
    <input type="submit" name="submit" value="{$submit_value}" onClick="setHiddenValue(this);">
    {$htm['be2ch']}
    <br>
    {$htm['src_fix']}
    
    <input type="hidden" name="bbs" value="{$bbs}">
    <input type="hidden" name="key" value="{$key}">
    <input type="hidden" name="time" value="{$time}">
    
    <input type="hidden" name="host" value="{$host}">
    <input type="hidden" name="popup" value="{$popup}">
    <input type="hidden" name="rescount" value="{$rescount}">
    <input type="hidden" name="ttitle_en" value="{$ttitle_en}">
    <input type="hidden" name="csrfid" value="{$csrfid}">
    {$newthread_hidden_ht}{$readnew_hidden_ht}
    {$_conf['k_input_ht']}
</form>\n
EOP;



?>
