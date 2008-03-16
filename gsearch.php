<?php
/**
 * 2ch����Google API���g���Č������Ap2�œǂނ��߂̃����N�ɕϊ�����
 *
 * �\��F
 * �E1��1,000��܂ł̐���������̂Ō������ʂ��L���b�V�����čĖ₢���킹��h���B
 * �E���O�ɂ��̓��̌����񐔂��L�^����悤�ɂ���B
 *
 * �Q�l�ɂ����Ƃ���F
 * �Ehttp://www.itmedia.co.jp/enterprise/0405/28/epn04_3.html
 * �E�܂邲��PHP! (Vol.1) ���{����̋L���uPHP�ŊȒP SOAP�T�[�r�X�v
 *
 * �K�v�Ȃ��́F
 * �EGoogle �A�J�E���g
 * �EGoogle Web APIs �̃y�[�W�������ł��� Developer�fs Kit �Ɋ܂܂��WSDL�t�@�C��
 * �EPHP��mbstring�@�\�g��
 * �EPHP4�Ȃ�PEAR::SOAP�APHP5�Ȃ�SOAP�g���@�\
 * �EPEAR::Pager (2.x)
 * �EPEAR::Var_Dump (1.x)
 */

// {{{ p2��{�ݒ�ǂݍ���&�F��

require_once 'conf/conf.inc.php';

$_login->authorize();

// }}}

if ($_conf['expack.google.enabled'] == 0) {
    exit('<html><body><p>Google�����͖����ł��B<br>conf/conf_admin_ex.inc.php �̐ݒ��ς��Ă��������B</p></body></html>');
}

if ($b == 'pc') {
    output_add_rewrite_var('b', 'pc');
} elseif ($b == 'k' || $k) {
    output_add_rewrite_var('b', 'k');
}

// {{{ Init

// ���C�u�����ǂݍ���
require_once P2EX_LIBRARY_DIR . '/google/search.class.php';
require_once P2EX_LIBRARY_DIR . '/google/converter.class.php';
require_once P2EX_LIBRARY_DIR . '/google/renderer.class.php';

// Google Search WSDL�t�@�C���̃p�X
$wsdl = $_conf['expack.google.wsdl'];

// Google Web APIs �̃��C�Z���X�L�[
$key = $_conf['expack.google.key'];

// 1�y�[�W������̕\������ (Max:10)
$perPage = 10;

// ����������
if (isset($_GET['word'])) {
    $_GET['q'] = $_GET['word'];
    unset($_GET['word']);
}
if (isset($_GET['q'])) {
    $q = mb_convert_encoding($_GET['q'], 'UTF-8', 'SJIS-win');
    $word = htmlspecialchars($_GET['q'], ENT_QUOTES);
} else {
    $word = $q = '';
}

// �y�[�W�ԍ�
$p = isset($_GET['p']) ? max((int)$_GET['p'], 1) : 1;
$start = ($p - 1) * $perPage;

// �o�͗p�ϐ�
$totalItems = 0;
$result = null;
$popups = null;

// }}}
// {{{ Search

if (!empty($q)) {
    // �����������2ch�������p�ɕϊ�
    //$q = trim(preg_replace('/( |�@)\w+:.*( |�@)/u', '', $q));
    //$q .= ' site:2ch.net -site:www.2ch.net -site:info.2ch.net -site:find.2ch.net -site:p2.2ch.net';
    $q .= ' site:2ch.net';

    // Google�����N���X�̃C���X�^���X�𐶐�����
    $google = &GoogleSearch::factory($wsdl, $key);

    // �C���X�^���X�����Ɏ��s
    if (PEAR::isError($google)) {
        $result = '<b>Error: ' . $google->getMessage() . '</b>';
    // �C���X�^���X�����ɐ���
    } else {
        $resultObj = &$google->doSearch($q, $perPage, $start);
        // �G���[����
        if (PEAR::isError($resultObj)) {
            $result = '<b>Error: ' . $resultObj->getMessage() . '</b>';
            if (!empty($resultObj->userinfo)) {
                require_once 'Var_Dump.php';
                $result .= Var_Dump::display($resultObj->getUserInfo(), true, 'HTML4_Table');
            }
        // ���N�G�X�g����
        } else {
            $totalItems = $resultObj->estimatedTotalResultsCount;
            // �q�b�g����
            if ($totalItems > 0) {
                $converter = &new Google_Converter;
                $result = array();
                $popups = array();
                $id = 1;
                foreach ($resultObj->resultElements as $obj) {
                    $result[$id] = $converter->toOutputValue($obj);
                    $popups[$id] = $converter->toPopUpValue($obj);
                    $id++;
                }
            // �q�b�g���[��
            } else {
                $result = '&quot;' . $word . '&quot; Not Found.';
                // �������ʂ̍Ō�̃y�[�W��\�����悤�Ƃ����Ƃ��A
                // �q�b�g�����u����$start��菬�����Ȃ�A���ʂƂ���0���ƂȂ邱�Ƃ�����
                if ($start > 0) {
                    $result .= '<br><a href="javascript:history.back();">Back</a>';
                }
            }
        } // end of ���N�G�X�g����
    } // end of �C���X�^���X�����ɐ���

}

// }}}
// {{{ Display

$renderer = &new Google_Renderer;

$search_element_type = 'text';
$search_element_extra_attributes = '';
if ($_conf['input_type_search']) {
    $search_element_type = 'search';
    $search_element_extra_attributes = " autosave=\"rep2.expack.search.google\" results=\"{$_conf['expack.google.recent2_num']}\" placeholder=\"Google\"";
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
    <?php echo $_conf['meta_charset_ht']; ?>
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>2ch���� by Google : <?php echo $word; ?></title>
    <link rel="stylesheet" href="css.php?css=style&amp;skin=<?php echo $skin_en; ?>" type="text/css">
    <link rel="stylesheet" href="css.php?css=read&amp;skin=<?php echo $skin_en; ?>" type="text/css">
    <link rel="stylesheet" href="css.php?css=subject&amp;skin=<?php echo $skin_en; ?>" type="text/css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <script type="text/javascript" src="js/basic.js?<?php echo $_conf['p2expack']; ?>"></script>
    <script type="text/javascript" src="js/gpopup.js?<?php echo $_conf['p2expack']; ?>"></script>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table id="sbtoolbar1" class="toolbar" cellspacing="0"><tr><td align="left">
    <span class="itatitle"><a class="aitatitle" href="<?php echo $_SERVER['SCRIPT_NAME']; ?>"><b>2ch���� by Google</b></a></span>
    <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="get" accept-charset="<?php echo $_conf['accept_charset']; ?>" style="display:inline;">
        <input type="<?php echo $search_element_type; ?>" name="q" value="<?php echo $word; ?>"<?php echo $search_element_extra_attributes; ?>>
        <input type="submit" value="����">
    </form>
</td></tr></table>
<?php $renderer->printSearchResult($result, $word, $perPage, $start, $totalItems); ?>
<?php $renderer->printPager($perPage, $totalItems); ?>
<?php $renderer->printPopup($popups); ?>
</body>
</html>

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
