<?php
/*
    p2 - ���X�������݃t�H�[��
*/

require_once './conf/conf.inc.php';
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
$ttitle_hc  = (strlen($ttitle_en) > 0) ? P2Util::htmlEntityDecodeLite(base64_decode($ttitle_en)) : '';

if (P2Validate::host($host) || ($bbs) && P2Validate::bbs($bbs) || ($key) && P2Validate::key($key)) {
    p2die('�s���Ȉ����ł�');
}

// �t�H�[���̃I�v�V�����ǂݍ���
require_once P2_LIB_DIR . '/post_options_loader.inc.php';

// �\���w��

$htm['resform_ttitle'] = '';

// {{{ �X�����ĂȂ�

if (!empty($_GET['newthread'])) {
    $ptitle = "{$itaj} - �V�K�X���b�h�쐬";
    
    // machibbs�AJBBS@������� �Ȃ�
    if (P2Util::isHostMachiBbs($host) or P2Util::isHostJbbsShitaraba($host)) {
        $submit_value = "�V�K��������";
    // 2ch�Ȃ�
    } else {
        $submit_value = "�V�K�X���b�h�쐬";
    }
    
    $class_ttitle = '';
    $sub_size_at = '';

    if (!$_conf['ktai']) {
        $class_ttitle = ' class="thre_title"';
        $sub_size_at = ' size="40"';
    }

    $htm['subject'] = <<<EOP
<b><span{$class_ttitle}>�^�C�g��</span></b>�F<input type="text" id="subject" name="subject"{$sub_size_at} value="{$hs['subject']}"><br>
EOP;
    if ($_conf['ktai']) {
        $uri = P2Util::buildQueryUri(
            $_conf['subject_php'],
            array(
                'host'   => $host,
                'bbs'    => $bbs,
                UA::getQueryKey() => UA::getQueryValue()
            )
        );
        $htm['subject'] = P2View::tagA($uri, hs($itaj)) . '<br>' . $htm['subject'];
    }
    $newthread_hidden_ht = '<input type="hidden" name="newthread" value="1">';

// }}}
// {{{ �������݂Ȃ�

} else {
    $ptitle = "{$itaj} - ���X��������";
    
    $submit_value = "��������";
    
    $attrs = array();
    if (UA::isPC()) {
        $attrs['class'] = 'thre_title';
        $attrs['target'] = 'read';
    }
    
    $ttitle_atag = P2View::tagA(
        P2Util::buildQueryUri(
            $_conf['read_php'],
            array(
                'host' => $host,
                'bbs'  => $bbs,
                'key'  => $key,
                UA::getQueryKey() => UA::getQueryValue()
            )
        ),
        hs($ttitle_hc) . ' ',
        $attrs
    );
    
    $htm['resform_ttitle'] = "<p><b>$ttitle_atag</b></p>";
    $newthread_hidden_ht = '';
}

// }}}

$readnew_hidden_ht = !empty($_GET['from_read_new']) ? '<input type="hidden" name="from_read_new" value="1">' : '';

//==========================================================
// HTML �\���o��
//==========================================================
$body_at = P2View::getBodyAttrK();

$body_on_load = '';
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
?>
    <title><?php eh($ptitle); ?></title>
<?php
if (!$_conf['ktai']) {
    P2View::printIncludeCssHtml('style');
    P2View::printIncludeCssHtml('post');
?>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <script type="text/javascript" src="js/basic.js?v=20090429"></script>
    <script type="text/javascript" src="js/post_form.js?v=20061209"></script>
<?php
}

echo <<<EOP
</head>
<body{$body_at}{$body_on_load}>\n
EOP;

P2Util::printInfoHtml();

// $htm['post_form'] ���擾
require_once P2_LIB_DIR . '/post_form.inc.php';

echo $htm['post_form'];

?>
</body></html>
<?php

exit;

/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
