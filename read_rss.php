<?php
/*
    expack - �Ȉ�RSS���[�_�i<description>�܂���<content:encoded>�̓��e��\���j

    RSS�n�t�@�C����UTF-8�ŏ����āA�g�тɏo�͂���Ƃ�����SJIS�ɂ���������
    mbstring.script_encoding = SJIS-win �Ƃ̐��������l�����SJIS�̂܂܂�����ȁH
*/

// {{{ p2��{�ݒ�ǂݍ���&�F��

require_once 'conf/conf.inc.php';

$_login->authorize();

// }}}

if ($b == 'pc') {
    output_add_rewrite_var('b', 'pc');
} elseif ($b == 'k' || $k) {
    output_add_rewrite_var('b', 'k');
}

//============================================================
// �ϐ��̏�����
//============================================================

$channel = array();
$items = array();

$num = trim($_REQUEST['num']);
$xml = trim($_REQUEST['xml']);
$atom = empty($_REQUEST['atom']) ? 0 : 1;
$site_en = trim($_REQUEST['site_en']);

if (is_numeric($num)) {
    $num = (int)$num;
}
$xml_en = rawurlencode($xml);
$xml_ht = P2Util::re_htmlspecialchars($xml);


//============================================================
// RSS�ǂݍ���
//============================================================

if ($xml) {
    require_once P2EX_LIBRARY_DIR . '/rss/parser.inc.php';
    $rss = &p2GetRSS($xml, $atom);
    if (is_a($rss, 'XML_Parser')) {
        clearstatcache();
        $rss_parse_success = true;
        $xml_path = rss_get_save_path($xml);
        $mtime    = filemtime($xml_path);
        $channel  = $rss->getChannelInfo();
        $items    = $rss->getItems();

        $fp = fopen($xml_path, 'rb');
        $xmldec = fgets($fp, 1024);
        fclose($fp);
        if (preg_match('/^<\\?xml version="1.0" encoding="((?i:iso)-8859-(?:[1-9]|1[0-5]))" ?\\?>/', $xmldec, $matches)) {
            $encoding = $matches[1];
        } else {
            $encoding = 'UTF-8,eucJP-win,SJIS-win,JIS';
        }
        mb_convert_variables('SJIS-win', $encoding, $channel, $items);
    } else {
        $rss_parse_success = false;
    }
} else {
    $rss_parse_success = false;
}


//===================================================================
// HTML�\���p�ϐ��̐ݒ�
//===================================================================

//�^�C�g��
if (isset($num)) {
    $title = P2Util::re_htmlspecialchars($items[$num]['title']);
} else {
    $title = P2Util::re_htmlspecialchars($channel['title']);
}


//============================================================
// HTML�v�����g
//============================================================

if ($_conf['ktai']) {
    if (!$_conf['expack.rss.check_interval']) {
        // �L���b�V�������Ȃ�
        P2Util::header_nocache();
    } else {
        // �X�V�`�F�b�N�Ԋu��1/3�����L���b�V��������i�[��or�Q�[�g�E�F�C�̎����ˑ��j
        header(sprintf('Cache-Control: max-age=%d', $_conf['expack.rss.check_interval'] * 60 / 3));
    }
}
echo $_conf['doctype'];
include P2EX_LIBRARY_DIR . '/rss/' . ($_conf['ktai'] ? 'read_k' : 'read') . '.inc.php';

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
