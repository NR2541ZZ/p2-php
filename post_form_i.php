<?php
/*
    p2 - ���X�������݃t�H�[��
*/

require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';
require_once P2_LIB_DIR . '/DataPhp.php';
require_once P2_LIB_DIR . '/P2Validate.php';

$_login->authorize(); // ���[�U�F��

//==================================================
// �ϐ�
//==================================================
if (empty($_GET['host'])) {
    p2die('host ���w�肳��Ă��܂���');
}
$host = $_GET['host'];
$bbs = geti($_GET['bbs']);
$key = geti($_GET['key']);

$rescount = (int)geti($_GET['rescount'], 1);
$popup    = (int)geti($_GET['popup'],    0);

if (!$itaj = P2Util::getItaName($host, $bbs)) {
    $itaj = $bbs;
}

$ttitle_en  = isset($_GET['ttitle_en']) ? $_GET['ttitle_en'] : '';
$ttitle     = strlen($ttitle_en) ? base64_decode($ttitle_en) : '';
$ttitle_hs  = htmlspecialchars($ttitle, ENT_QUOTES);

if (P2Validate::host($host) || ($bbs) && P2Validate::bbs($bbs) || ($key) && P2Validate::key($key)) {
    p2die('�s���Ȉ����ł�');
}

$idx_host_dir = P2Util::idxDirOfHost($host);
$key_idx = $idx_host_dir . '/' . $bbs . '/' . $key . '.idx';

// �t�H�[���̃I�v�V�����ǂݍ���
require_once P2_LIB_DIR . '/post_options_loader.inc.php';

// �\���w��
if (!$_conf['ktai']) {
    $class_ttitle = ' class="thre_title"';
    $target_read = ' target="read"';
    $sub_size_at = ' size="40"';
}

// {{{ �X�����ĂȂ�

if (!empty($_GET['newthread'])) {
    //$ptitle = "{$itaj} - �V�K�X���b�h�쐬";
    $ptitle = "�V�K�X���b�h�쐬";
    // machibbs�AJBBS@������� �Ȃ�
    if (P2Util::isHostMachiBbs($host) or P2Util::isHostJbbsShitaraba($host)) {
        $submit_value = "�V�K��������";
    // 2ch�Ȃ�
    } else {
        $submit_value = "�V�K�X���b�h�쐬";
    }
    
    $htm['subject'] = <<<EOP
<div class="row"><label><span{$class_ttitle}>�^�C�g��</span></label><input type="text" id="subject" name="subject"{$sub_size_at} value="{$hs['subject']}"></div>
EOP;
    if ($_conf['ktai']) {
        //$htm['subject'] = "<a id=\"backButton\" class=\"button\" href=\"{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}{$_conf['k_at_a']}\">{$itaj}</a><br>" . $htm['subject'];
    $htm['back'] = "<a id=\"backButton\" class=\"button\" href=\"{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}{$_conf['k_at_a']}\">{$itaj}</a>";
    }
    $newthread_hidden_ht = '<input type="hidden" name="newthread" value="1">';

// }}}
// {{{ �������݂Ȃ�

} else {
    //$ptitle = "{$itaj} - ���X��������";
    $ptitle = "���X��������";
    $submit_value = "��������";

    $htm['resform_ttitle'] = <<<EOP
<p><a id="backButton" class="button" href="{$_conf['read_php']}?host={$host}&amp;bbs={$bbs}&amp;key={$key}{$_conf['k_at_a']}"{$target_read}>{$ttitle_hs}</a></p>
EOP;
    $newthread_hidden_ht = '';
}

// }}}

$readnew_hidden_ht = !empty($_GET['from_read_new']) ? '<input type="hidden" name="from_read_new" value="1">' : '';


//==========================================================
// HTML �\���o��
//==========================================================
if (!$_conf['ktai']) {
    $body_on_load = <<<EOP
 onLoad="setFocus('MESSAGE'); checkSage();"
EOP;
}

P2View::printDoctypeTag();
?>
<html lang="ja">
<head>
<?php
P2View::printExtraHeadersHtml();
echo <<<EOHEADER
<style type="text/css" media="screen">@import "./iui/iui.css";</style>
<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
<script type="text/javascript"> 
<!-- 
window.onload = function() { 
setTimeout(scrollTo, 100, 0, 1); 
} 
// --> 
</script> 
<title>{$ptitle}</title>\n
EOHEADER;
if (!$_conf['ktai']) {
    include_once './style/style_css.inc';
    include_once './style/post_css.inc';
    ?>
    <script type="text/javascript" src="js/basic.js?v=20061206"></script>
    <script type="text/javascript" src="js/post_form.js?v=20061209"></script>
    <?php
}
echo <<<EOP
</head>
<body{$body_on_load}>\n
<div class="toolbar">
<h1 id="pageTitle">{$itaj}</h1>
</div>

EOP;

P2Util::printInfoHtml();

// $htm['post_form'] ���擾
require_once P2_IPHONE_LIB_DIR . '/post_form.inc.php';

echo $htm['post_form'];

?></body></html><?php
