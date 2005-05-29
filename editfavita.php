<?php
/*
    p2 -  ���C�ɓ���ҏW
*/

include_once './conf/conf.inc.php';  // ��{�ݒ�
require_once (P2_LIBRARY_DIR . '/filectl.class.php');

authorize(); // ���[�U�F��

//================================================================
// ������ȑO�u����
//================================================================

// ���C�ɔ̒ǉ��E�폜�A���ёւ�
if (isset($_GET['setfavita']) or isset($_POST['setfavita'])) {
    include_once (P2_LIBRARY_DIR . '/setfavita.inc.php');
    setFavIta();
}
// ���C�ɔ̃z�X�g�𓯊�
if (isset($_GET['syncfavita']) or isset($_POST['syncfavita'])) {
    include_once (P2_LIBRARY_DIR . '/syncfavita.inc.php');
}

// �v�����g�p�ϐ� ======================================================

// ���C�ɔǉ��t�H�[��
$add_favita_form_ht = <<<EOFORM
<form method="POST" action="{$_SERVER['PHP_SELF']}" accept-charset="{$_conf['accept_charset']}" target="_self">
    <input type="hidden" name="detect_hint" value="����">
    <p>
        {$_conf['k_input_ht']}
        URL: <input type="text" id="url" name="url" value="http://" size="48">
        ��: <input type="text" id="itaj" name="itaj" value="" size="16">
        <input type="hidden" id="setfavita" name="setfavita" value="1">
        <input type="submit" name="submit" value="�V�K�ǉ�">
    </p>
</form>\n
EOFORM;

// ���C�ɔ����t�H�[��
$sync_favita_form_ht = <<<EOFORM
<form method="POST" action="{$_SERVER['PHP_SELF']}" target="_self">
    <p>
        {$_conf['k_input_ht']}
        <input type="hidden" id="syncfavita" name="syncfavita" value="1">
        <input type="submit" name="submit" value="���X�g�Ɠ���">
    </p>
</form>\n
EOFORM;

//================================================================
// �w�b�_
//================================================================
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>p2 - ���C�ɔ̕��ёւ�</title>
EOP;

@include("./style/style_css.inc");
@include("./style/editfavita_css.inc");

echo '</head><body>'."\n";

echo $_info_msg_ht;
$_info_msg_ht = '';

//================================================================
// ���C������HTML�\��
//================================================================

//================================================================
// ���C�ɔ�
//================================================================

// favita�t�@�C�����Ȃ���ΐ���
FileCtl::make_datafile($_conf['favita_path'], $_conf['favita_perm']);
// favita�ǂݍ���
$lines = file($_conf['favita_path']);

// PC�p
if (!$_conf['ktai']) {
    $onclick = " onClick='parent.menu.location.href=\"{$_conf['menu_php']}?nr=1\"'";
    $m_php = $_SERVER['PHP_SELF'];
    
// �g�їp
} else {
    $onclick = '';
    $m_php = 'menu_k.php?view=favita&amp;nr=1'.$_conf['k_at_a'].'&amp;nt='.time();
}

echo <<<EOP
<div><b>���C�ɔ̕ҏW</b> [<a href="{$m_php}"{$onclick}>���j���[���X�V</a>]</div>
EOP;

echo $add_favita_form_ht;

if ($lines) {
    echo "<table>";
    foreach ($lines as $l) {
        $l = rtrim($l);
        if (preg_match('/^\t?(.+)\t(.+)\t(.+)$/', $l, $matches)) {
            $itaj = rtrim($matches[3]);
            $itaj_en = rawurlencode(base64_encode($itaj));
            $host = $matches[1];
            $bbs = $matches[2];
            $itaj_view = htmlspecialchars($itaj);
            $itaj_q = '&amp;itaj_en='.$itaj_en;
            echo <<<EOP
            <tr>
            <td><a href="{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}{$_conf['k_at_a']}">{$itaj_view}</a></td>
            <td>[ <a class="te" href="{$_SERVER['PHP_SELF']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=top{$_conf['k_at_a']}" title="��ԏ�Ɉړ�">��</a></td>
            <td><a class="te" href="{$_SERVER['PHP_SELF']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=up{$_conf['k_at_a']}" title="���Ɉړ�">��</a></td>
            <td><a class="te" href="{$_SERVER['PHP_SELF']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=down{$_conf['k_at_a']}" title="����Ɉړ�">��</a></td>
            <td><a class="te" href="{$_SERVER['PHP_SELF']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=bottom{$_conf['k_at_a']}" title="��ԉ��Ɉړ�">��</a> ]</td>
            <td>[<a href="{$_SERVER['PHP_SELF']}?host={$host}&amp;bbs={$bbs}&amp;setfavita=0{$_conf['k_at_a']}">�폜</a>]</td>
            </tr>
EOP;
        }
    }
    echo "</table>";
}

if (!$_conf['ktai']) {
    echo $sync_favita_form_ht;
}

//================================================================
// �t�b�^HTML�\��
//================================================================
if ($_conf['ktai']) {
    echo '<hr>'.$_conf['k_to_index_ht'];
}

echo '</body></html>';

?>
