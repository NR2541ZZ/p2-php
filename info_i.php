<?php
/*
    p2 - �X���b�h���E�B���h�E
*/

/*
iphone �ŃX������\�����鎞�́A�ގ��X���Ɠ����ɕ\�������Ă邽�� subject_i.php ����Ăяo�����B
subject_i.php ����� info_i.php �ǂݍ��݂͗͋ƂȂ̂łǂ����Ƃ��v���Ƃ���c�B
*/
if (_isCalledAsStandAlone()) {
    require_once './conf/conf.inc.php';
}

require_once P2_LIB_DIR . '/Thread.php';
require_once P2_LIB_DIR . '/FileCtl.php';
require_once P2_LIB_DIR . '/dele.funcs.php'; // �폜�����p�̊֐��S
require_once P2_LIB_DIR . '/P2Validate.php';

$_login->authorize(); // ���[�U�F��

//================================================================
// �ϐ��ݒ�
//================================================================
isset($_GET['host'])    and $host = $_GET['host'];  // "pc.2ch.net"
isset($_GET['bbs'])     and $bbs  = $_GET['bbs'];   // "php"
isset($_GET['key'])     and $key  = $_GET['key'];   // "1022999539"

$ttitle_en = isset($_GET['ttitle_en']) ? $_GET['ttitle_en'] : null;

// $_GET['popup'] 0(false), 1(true), 2(true, �N���[�Y�^�C�}�[�t)

// �ȉ��ǂꂩ����Ȃ��Ă��_���o��
if (!$host || !isset($bbs) || !isset($key)) {
    p2die('����������������܂���B(host or bbs or key)');
}

if (P2Validate::host($host) || P2Validate::bbs($bbs) || P2Validate::key($key)) {
    p2die('�s���Ȉ����ł��B(host or bbs or key)');
}
$title_msg = '';
$info_msg  = '';

//================================================================
// ���ʂȑO����
//================================================================

// {{{ �폜

if (!empty($_GET['dele'])) {
    $r = deleteLogs($host, $bbs, array($key));
    if (!$r) {
        $title_msg  = "�~ ���O�폜���s";
        $info_msg   = "�~ ���O�폜���s";
    } elseif ($r == 1) {
        $title_msg  = "�� ���O�폜����";
        $info_msg   = "�� ���O�폜����";
    } elseif ($r == 2) {
        $title_msg  = "- ���O�͂���܂���ł���";
        $info_msg   = "- ���O�͂���܂���ł���";
    }
}

// }}}
// {{{ �����폜

if (!empty($_GET['offrec'])) {
    $r1 = offRecent($host, $bbs, $key);
    $r2 = offResHist($host, $bbs, $key);
    if (($r1 === false) or ($r2 === false)) {
        $title_msg  = "�~ �����������s";
        $info_msg   = "�~ �����������s";
    } elseif ($r1 == 1 || $r2 == 1) {
        $title_msg  = "�� ������������";
        $info_msg   = "�� ������������";
    } elseif ($r1 === 0 && $r2 === 0) {
        $title_msg  = "- �����ɂ͂���܂���ł���";
        $info_msg   = "- �����ɂ͂���܂���ł���";
    }

// }}}

// ���C�ɓ���X���b�h
} elseif (isset($_GET['setfav'])) {
    require_once P2_LIB_DIR . '/setFav.func.php';
    setFav($host, $bbs, $key, $_GET['setfav']);

// �a������
} elseif (isset($_GET['setpal'])) {
    require_once P2_LIB_DIR . '/setPalace.func.php';
    setPalace($host, $bbs, $key, $_GET['setpal']);

// �X���b�h���ځ[��
} elseif (isset($_GET['taborn'])) {
    require_once P2_LIB_DIR . '/settaborn.func.php';
    settaborn($host, $bbs, $key, $_GET['taborn']);
}

//=================================================================
// ���C��
//=================================================================

$aThread = new Thread();

// host�𕪉�����idx�t�@�C���̃p�X�����߂�
$aThread->setThreadPathInfo($host, $bbs, $key);
$key_line = $aThread->getThreadInfoFromIdx();
$aThread->getDatBytesFromLocalDat(); // $aThread->length ��set
//$aThread->readDatInfoFromFile();

if (!$aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs)) {
    $aThread->itaj = $aThread->bbs;
}
$hc['itaj'] = $aThread->itaj;

if (!$aThread->ttitle) {
    if (isset($ttitle_en)) {
        $aThread->setTtitle(base64_decode($ttitle_en));
    } else {
        $aThread->setTitleFromLocal();
    }
}
if (!$ttitle_en) {
    if ($aThread->ttitle) {
        $ttitle_en = base64_encode($aThread->ttitle);
        //$ttitle_urlen = rawurlencode($ttitle_en);
    }
}

if (!is_null($aThread->ttitle_hc)) {
    $hc['ttitle_name'] = $aThread->ttitle_hc;
} else {
    $hc['ttitle_name'] = "�X���b�h�^�C�g�����擾";
}

// {{{ favlist �`�F�b�N

/*
// ���C�ɃX�����X�g �Ǎ�
if ($favlines = @file($_conf['favlist_file'])) {
    foreach ($favlines as $l) {
        $favarray = explode('<>', rtrim($l));
        if ($aThread->key == $favarray[1] && $aThread->bbs == $favarray[11]) {
            $aThread->fav = "1";
            if ($favarray[0]) {
                $aThread->setTtitle($favarray[0]);
            }
            break;
        }
    }
}
*/

// ���C�ɃX��
$fav_atag = _getFavATag($aThread, $favmark_accesskey = '', $ttitle_en);

// }}}
// {{{ palace �`�F�b�N

// �a������X�����X�g �Ǎ�
$isPalace = false;
$palace_idx = $_conf['pref_dir'] . '/p2_palace.idx';
if ($pallines = @file($palace_idx)) {
    foreach ($pallines as $l) {
        $palarray = explode('<>', rtrim($l));
        if ($aThread->key == $palarray[1]) {
            $isPalace = true;
            if ($palarray[0]) {
                $aThread->setTtitle($palarray[0]);
            }
            break;
        }
    }
}

$palUrl = P2Util::buildQueryUri('info_i.php',
    array(
        'host' => $aThread->host, 'bbs' => $aThread->bbs, 'key' => $aThread->key,
        'setpal' => (int)!$isPalace,
        'popup'  => (int)(bool)geti($_GET['popup']),
        'ttitle' => $ttitle_en,
        UA::getQueryKey() => UA::getQueryValue()
    )
);
$pal_ht = P2View::tagA($palUrl, hs($isPalace ? '��' : '+'), array('title' => 'DAT���������X���p�̂��C�ɓ���'));

// }}}
// {{{ �X���b�h���ځ[��`�F�b�N

// �X���b�h���ځ[�񃊃X�g�Ǎ�
$ta_keys = P2Util::getThreadAbornKeys($aThread->host, $aThread->bbs);
$isTaborn = empty($ta_keys[$aThread->key]) ? false : true;


$taborndo_title_attrs = array();
if (UA::isPC() and !$isTaborn) {
    $taborndo_title_attrs = array('title' => '�X���b�h�ꗗ�Ŕ�\���ɂ��܂�');
}

$preKey = '';
$taborn_accesskey = null;
if (UA::isK() && $taborn_accesskey) {
    $preKey = $taborn_accesskey . '.';
}

$taborn_ht = sprintf(
    '%s [%s]', 
    hs($isTaborn ? '���ځ[��' : '�ʏ�'),
    P2View::tagA(
        P2Util::buildQueryUri('info_i.php',
            array(
                'host' => $aThread->host,
                'bbs'  => $aThread->bbs,
                'key'  => $aThread->key,
                'taborn' => $isTaborn ? 0 : 1,
                'popup' => (int)(bool)geti($_GET['popup']),
                'ttitle_en' => $ttitle_en,
                UA::getQueryKey() => UA::getQueryValue()
            )
        ),
        sprintf(
            '%s%s',
            hs($preKey),
            hs($isTaborn ? '���ځ[���������' : '���ځ[�񂷂�')
        ),
        array_merge($taborndo_title_attrs, array('accesskey' => $taborn_accesskey))
    )
);

// }}}

// ���O����Ȃ��t���O�Z�b�g
if (file_exists($aThread->keydat) or file_exists($aThread->keyidx)) {
    $existLog = true;
}

//=================================================================
// HTML�v�����g
//=================================================================
$aThread->ls = 'l50';
$motothre_url = $aThread->getMotoThread();
$motothre_org_url = $aThread->getMotoThread(true);

if ($title_msg) {
    $hc['title'] = $title_msg;
} else {
    $hc['title'] = "info - {$hc['ttitle_name']}";
}

$hs = array_map('htmlspecialchars', $hc);

// �������d�����Ȃ��悤��
if (_isCalledAsStandAlone()) {

P2Util::headerNoCache();
P2View::printDoctypeTag();
?>
<html lang="ja">
<head>
<?php
P2View::printExtraHeadersHtml();
echo <<<EOHEADER
	<link rel="stylesheet" type="text/css" href="./iui/iui.css"> 
	<title>{$hs['title']}</title>\n
EOHEADER;

} // if (_isCalledAsStandAlone())

$body_onload = '';
if (isset($_GET['popup']) and $_GET['popup'] == 2) {
    ?><script type="text/javascript" src="js/closetimer.js"></script><?php
    $body_onload = ' onLoad="startTimer(document.getElementById(\'timerbutton\'))"';
}

if (_isCalledAsStandAlone()) {

// html �v�����g�w�b�h iPhone�p
echo <<<EOP
	</head>
	<body{$body_onload}>
	<div class="toolbar">
	<h1 id="pageTitle">�X�����</h1>
	<a id="backButton" class="button" href="./iphone.php">TOP</a>
	</div>
EOP;

} // if (_isCalledAsStandAlone())

?><ul><li class="group">�X�����</li></ul><div id="usage" class="panel"><?php

P2Util::printInfoHtml();

?>
<h2><?php echo $hs['ttitle_name']; ?></b></h2>
<fieldset>
<?php

// �g�тȂ�`���ŏ�񃁃b�Z�[�W�\��
if (UA::isK() || UA::isIPhoneGroup()) {
    if (strlen($info_msg)) {
        printf('<p>%s</p>', hs($info_msg));
    }
}

//printInfoTrHtml("���X��", "<a href=\"{$motothre_url}\"{$target_read_at}>{$motothre_url}</a>");
//printInfoTrHtml("�z�X�g", $aThread->host);

$dele_pre_ht = '';
$up_pre_ht = '';

$offrecent_ht = '';
if (
    checkRecent($aThread->host, $aThread->bbs, $aThread->key)
    || checkResHist($aThread->host, $aThread->bbs, $aThread->key)
) {
    $offrecent_ht = sprintf(' / [%s]', _getOffRecentATag($aThread, $offrecent_accesskey = '', $ttitle_en));
}

_printInfoTrHtml(
    '���X��',
    P2View::tagA(
        $motothre_url,
        _addBrHtml($motothre_url),
        UA::isPC() ? array('target' => 'read') : array()
    )
);

if (UA::isPC()) {
    _printInfoTrHtml("�z�X�g", $aThread->host);
}

// ��
$ita_uri = P2Util::buildQueryUri(
    $_conf['subject_php'],
    array(
        'host' => $aThread->host,
        'bbs'  => $aThread->bbs,
        UA::getQueryKey() => UA::getQueryValue()
    )
);
$attrs =  array($_conf['accesskey_for_k'] => $_conf['k_accesskey']['up']);
UA::isPC() and $attrs['target'] = 'subject';
$ita_atag = P2View::tagA(
    $ita_uri,
    "{$up_pre_ht}{$hs['itaj']}",
    $attrs
);

// ���X��
$similar_qs = array(
    'detect_hint' => '����',
    'itaj_en'     => base64_encode($aThread->itaj),
    'method'      => 'similar',
    'word'        => $aThread->ttitle_hc
    // 'refresh' => 1
);
$similar_atag  = P2View::tagA(
    P2Util::buildQueryUri($_conf['subject_php'],
        array_merge($similar_qs,
            array(
                'host' => $aThread->host,
                'bbs'  => $aThread->bbs,
                'key'  => $aThread->key,
                UA::getQueryKey() => UA::getQueryValue(),
                'refresh' => '1'
            )
        )
    ),
    hs('���X��')
    //, array('target' => 'subject')
);

_printInfoTrHtml('��', "$ita_atag ($similar_atag)");

if ($existLog) {
    $atag = P2View::tagA(
        P2Util::buildQueryUri('info_i.php',
            array(
                'host' => $aThread->host,
                'bbs'  => $aThread->bbs,
                'key'  => $aThread->key,
                'dele' => '1',
                'popup' => (int)(bool)geti($_GET['popup']),
                'ttitle_en' => $ttitle_en,
                UA::getQueryKey() => UA::getQueryValue()
            )
        ),
        "{$dele_pre_ht}�폜����",
        array($_conf['accesskey_for_k'] => $_conf['k_accesskey']['dele'])
    );
    _printInfoTrHtml("���O", "���� [$atag]{$offrecent_ht}");

} else {
    _printInfoTrHtml("���O", "���擾{$offrecent_ht}");
}

if ($aThread->gotnum) {
    _printInfoTrHtml("�������X��", $aThread->gotnum);

} elseif (!$aThread->gotnum and $existLog) {
    _printInfoTrHtml("�������X��", "0");

} else {
    _printInfoTrHtml("�������X��", "-");
}

_printInfoTrHtml("���C�ɃX��", $fav_atag);
_printInfoTrHtml("�a������", $pal_ht);
_printInfoTrHtml("�\��", $taborn_ht);


/*
// �֘A�L�[���[�h
if (UA::isPC() and P2Util::isHost2chs($aThread->host)) {
    printf(
        '<iframe src="http://p2.2ch.io/getf.cgi?%s" border="0" frameborder="0" height="30" width="520"></iframe>',
        hs($motothre_url)
    );
}
*/

// {{{ ����{�^��

if (!empty($_GET['popup'])) {
    ?><div align="center"><?php
    if ($_GET['popup'] == 1) {
        ?><form action=""><input type="button" value="�E�B���h�E�����" onClick="window.close();"></form><?php
    } elseif ($_GET['popup'] == 2) {
        ?><form action=""><input id="timerbutton" type="button" value="Close Timer" onClick="stopTimer(document.getElementById('timerbutton'))"></form><?php
    }
    ?></div><?php
}

// }}}

?></filedset></div><?php

if (_isCalledAsStandAlone()) {
    ?></body></html><?php
    exit;
}

// exit;

//========================================================================
// �֐� �i���̃t�@�C�����ł̂ݗ��p�j
//========================================================================
/**
 * �X�����HTML��\������
 *
 * @return  void
 */
function _printInfoTrHtml($s, $c_ht)
{
    global $_conf;
    
    // iPhone
    echo "<div class=\"row\">\n<label>{$s}</label><span>{$c_ht}</span></div>\n";
}

/**
 * �X���^�C��URL�̃R�s�y�p�̃t�H�[��HTML���擾����
 *
 * @return  string  HTML
 */
function _getCopypaFormHtml($url, $ttitle_name_hs)
{
    global $_conf;
    
    $url_hs = htmlspecialchars($url, ENT_QUOTES);
    
    $me_url = P2Util::getMyUrl();
    // $_SERVER['REQUEST_URI']
    
    $htm = <<<EOP
<form action="{$me_url}">
 <textarea name="copy" rows="5" cols="50">{$ttitle_name_hs}&#10;{$url_hs}</textarea>
</form>
EOP;
    
// <input type="text" name="url" value="{$url_hs}">
// <textarea name="msg_txt">{$msg_txt}</textarea><br>

    return $htm;
}

/**
 * @return  string  HTML
 */
function _getFavATag($aThread, $favmark_accesskey, $ttitle_en)
{
    global $_conf;
    
    $preKey = '';
    if (UA::isK() && $favmark_accesskey) {
        $preKey = $favmark_accesskey . '.';
    }
    return P2View::tagA(
        P2Util::buildQueryUri('info_i.php',
            array(
                'host' => $aThread->host,
                'bbs'  => $aThread->bbs,
                'key'  => $aThread->key,
                'setfav' => $aThread->fav ? 0 : 1,
                'popup' => (int)(bool)geti($_GET['popup']),
                'ttitle_en' => $ttitle_en,
                UA::getQueryKey() => UA::getQueryValue()
            )
        ),
        sprintf(
            '%s<span class="fav">%s</span>',
            hs($preKey),
            hs($aThread->fav ? '��' : '+')
        ),
        array('accesskey' => $favmark_accesskey)
    );
}

/**
 * @return  string  HTML
 */
function _getTtitleNameATag($aThread, $ttitle_name)
{
    global $_conf;
    
    $attrs = array('class' => 'thre_title');
    if (UA::isPC()) {
        $attrs['target'] = 'read';
    }
    
    return P2View::tagA(
        P2Util::buildQueryUri($_conf['read_php'],
            array(
                'host' => $aThread->host,
                'bbs'  => $aThread->bbs,
                'key'  => $aThread->key,
                UA::getQueryKey() => UA::getQueryValue()
            )
        ),
        hs($ttitle_name) . ' ',
        $attrs
    );
}

/**
 * @return  string  HTML
 */
function _getOffRecentATag($aThread, $offrecent_accesskey, $ttitle_en)
{
    global $_conf;
    
    $preKey = '';
    if (UA::isK() && $offrecent_accesskey) {
        $preKey = $offrecent_accesskey . '.';
    }
    return P2View::tagA(
        P2Util::buildQueryUri('info_i.php',
            array(
                'host' => $aThread->host,
                'bbs'  => $aThread->bbs,
                'key'  => $aThread->key,
                'offrecent' => '1',
                'popup' => (int)(bool)geti($_GET['popup']),
                'ttitle_en' => $ttitle_en,
                UA::getQueryKey() => UA::getQueryValue()
            )
        ),
        sprintf('%s��������O��', hs($preKey)),
        array(
            'title' => '���̃X�����u�ŋߓǂ񂾃X���v�Ɓu�������ݗ����v����O���܂�',
            'accesskey' => $offrecent_accesskey
        )
    );
}

/**
 * @return  boolean
 */
function _isCalledAsStandAlone()
{
    return (basename($_SERVER['SCRIPT_NAME']) == 'info_i.php');
}

/**
 * �Ȃ񂩂��܂��������ǁA�����������Ȃ�̂ɑ΍�B
 *
 * @return  string  HTML
 */
function _addBrHtml($str, $num = 28)
{
    $html = '';
    for ($i = 0; $i < strlen($str); $i++) {
        $html .= hs($str[$i]);
        if ($i && !($i % $num)) {
            $html .= '<br>';
        }
    }
    return $html;
}

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
