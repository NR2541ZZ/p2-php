<?php
// p2 - �g�єŃ��X�R�s�[

require_once 'conf/conf.inc.php';
require_once P2_LIBRARY_DIR . '/thread.class.php';
require_once P2_LIBRARY_DIR . '/threadread.class.php';

$_login->authorize(); // ���[�U�F��

$name_txt   = '';
$mail_txt   = '';
$date_txt   = '';
$id_txt     = '';
$msg_txt    = '';
$url_k_ht   = '';
$id_ht      = '';
$back_link  = '';
$post_link   = '';
$moto_link  = '';
$form_id    = P2_REQUEST_ID;

//=====================================================
// �X���b�h���
//=====================================================
$host   = $_GET['host'];
$bbs    = $_GET['bbs'];
$key    = $_GET['key'];
$resid  = $GLOBALS['_read_copy_resnum'];
$quote  = !empty($_GET['inyou']);

if (isset($_SERVER['HTTP_REFERER'])) {
    $back_link = '<a href="' . htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES) . '" title="�߂�">' . �߂� . '</a>';
}

//=================================================
// ���X�ǂݍ���
//=================================================
$aThread = &new ThreadRead;
$aThread->setThreadPathInfo($host, $bbs, $key);
$aThread->ls = $resid;

if (file_exists($aThread->keydat)) {
    // �X���b�h���
    $aThread->readDat($aThread->keydat);
    $one = $aThread->explodeDatLine($aThread->datlines[0]);
    $ttitle = trim($one[4]);
    $ttitle_en = rawurlencode(base64_encode($ttitle));
    $ttitle_ht = htmlspecialchars($ttitle, ENT_QUOTES);
    $url_txt = $aThread->getMotoThread(true);
    $url_k_txt = $aThread->getMotoThread();
    if ($quote and $_GET['ktool_name'] != 'copy') {
        $url_txt .= $resid;
        $url_k_txt .= $resid;
    }
    if ($url_txt != $url_k_txt) {
        $url_k_ht = "<input type=\"text\" name=\"url_k_txt\" value=\"{$url_k_txt}\"><br>";
    }
    // ���e�t�H�[���ւ̃����N
    $post_url = "post_form.php?host={$host}&amp;bbs={$bbs}&amp;key={$key}";
    $post_url .= "&amp;rescount={$aThread->rescount}&amp;ttitle_en={$ttitle_en}&amp;k=1";
    $post_link = "<a href=\"{$post_url}\">ڽ</a>";
    // ���X���ւ̃����N
    $moto_link = '<a href="' . P2Util::throughIme($url_k_txt) . '">����</a>';

    // �w��ԍ��̃��X���p�[�X
    $p = $resid - 1;
    if (isset($aThread->datlines[$p])) {
        $resar = $aThread->explodeDatLine($aThread->datlines[$p]);
        // $resar[2]: 2006/10/20(��) 11:46:08 ID:YS696rnVP BE:32616498-DIA(30003)" 
        $name_txt = trim(strip_tags($resar[0]));
        $mail_txt = trim(strip_tags($resar[1]));
        if (strstr($resar[2], 'ID:')) {
            $date_txt = preg_replace('/ ?ID: ?([0-9A-Za-z\/.+?]+)([.,]|��)?.*$/', '', $resar[2]);
            $id_txt = preg_replace('/^.*ID: ?([0-9A-Za-z\/.+?]+)([.,]|��)?.*$/', 'ID:$1', $resar[2]);
            $id_ht = "<input type=\"text\" name=\"id_txt\" value=\"{$id_txt}\"><br>";
        } else {
            $date_txt = $resar[2];
        }
        $be_txt = '';
        if (preg_match('|BE: ?(\d+)-(#*.+)|i', $resar[2], $m)) {
            $be_txt = "?{$m[2]}";
        }
        $msg_txt = trim(strip_tags($resar[3], '<br>'));
        if ($quote) {
            $msg_txt = "&gt;&gt;{$resid}\r\n&gt; " . preg_replace('/ *<br[^>]*> */i', "\n&gt; ", $msg_txt);
        } else {
            $msg_txt = preg_replace('/ *<br[^>]*> */i', "\n", $msg_txt);
        }
    } else {
        P2Util::pushInfoHtml('<p>p2 error: ڽ�ԍ��̎w�肪�ςł��</p>');
    }
} else {
    P2Util::pushInfoHtml('<p>p2 error: �گ�ނ̎w�肪�ςł��B</p>');
}

// ��
if ($_GET['ktool_name'] == 'copy') {
    $mail_ht = (strlen($mail_txt) > 0) ? "$mail_txt :" : '';
    $id_ht_tmp = (strlen($id_txt) > 0) ? " $id_txt" : '';
    $be_ht_tmp = (strlen($be_txt) > 0) ? " $be_txt" : '';

    $msg_txt = "$resid :$name_txt :{$mail_ht}$date_txt{$id_ht_tmp}{$be_ht_tmp}\n{$msg_txt}";

    // au�̃o�O�H�Ή�
    $mobile = &Net_UserAgent_Mobile::singleton();
    if ($mobile->isEZweb()) {
        $msg_txt = preg_replace("/\n&/", "\n\n&", $msg_txt, 1);
    }
}

$msg_len = mb_strlen($msg_txt);
$len = $GLOBALS['_conf']['k_copy_divide_len'] ? $GLOBALS['_conf']['k_copy_divide_len'] : 10000;
$msg_txts = array();
for ($i = 0; $i < $msg_len; $i += $len) {
    $msg_txts[] = mb_substr($msg_txt, $i, $len);
}

//=====================================================
// �R�s�[�p�t�H�[����\��
//=====================================================
$action_ht = htmlspecialchars($_SERVER['SCRIPT_NAME'] . '?host=' . $_GET['host'] . '&bbs=' . $_GET['bbs'] . '&key=' . $_GET['key'] . '&copy=' . $GLOBALS['_read_copy_resnum'], ENT_QUOTES);

// willcom ��textarea�̃T�C�Y���������Ǝg���ɂ����炵��
/*
JavaScript�ɂ��Ă��܂����������������H
javascript:(function(){for (var j=0;j<document.forms.length;j++){for (var i=0;i<document.forms[j].elements.length;i++) {k=document.forms[j].elements[i];if(k.type=="textarea"){k.rows=10;k.cols=34;}}}})(); 
*/
$kyopon_size = '';
$mobile = &Net_UserAgent_Mobile::singleton();
if ($mobile->isAirHPhone()) {
    $kyopon_size = ' rows="10" cols="34"';
}

P2Util::header_nocache();
echo $_conf['doctype'];
?>
<html>
<head>
<title><?php echo $ttitle_ht; ?>/<?php echo $resid; ?></title>
</head>
<body<?php echo $k_color_settings; ?>>
<?php P2Util::printInfoHtml(); ?>
<form id="<?php echo $form_id; ?>" action="<?php echo $action_ht; ?>" method="post">
��:<br>
<input type="text" name="ttitle_txt" value="<?php echo $ttitle_ht; ?>"><br>
<input type="text" name="url_txt" value="<?php echo $url_txt; ?>"><br>
<?php echo $url_k_ht; ?>
<?php echo $resid; ?>:<br>

<?php if ($_GET['ktool_name'] != 'copy') { ?>
<input type="text" name="name_txt" value="<?php echo $name_txt; ?>"><br>
<input type="text" name="mail_txt" value="<?php echo $mail_txt; ?>"><br>
<input type="text" name="date_txt" value="<?php echo $date_txt; ?>"><br>
<?php echo $id_ht; ?>
<?php } ?>

<?php foreach ($msg_txts as $msg_txt) { ?>
<textarea<?php echo $kyopon_size; ?>><?php echo $msg_txt; ?></textarea><br>
<?php } ?>
�ذ:<br>
<textarea name="free" rows="2"></textarea>
</form>
<?php echo $back_link; ?> <?php echo $post_link; ?> <?php echo $moto_link; ?>

<hr><?php echo $_conf['k_to_index_ht']; ?>
</body>
</html>
<?php

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
