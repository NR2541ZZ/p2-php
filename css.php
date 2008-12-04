<?php
// p2 - �X�^�C���V�[�g���O���X�^�C���V�[�g�Ƃ��ďo�͂���

require_once './conf/conf.inc.php' ;

//$_login->authorize(); // ���[�U�F��

// �Ó���CSS�t�@�C���w�肩���؂��Ď擾����
if (!isset($_GET['css']) or !$cssFilePath = _getValidCssFilePath($_GET['css'])) {
    exit;
}

// CSS�o��
_printCss($cssFilePath);

exit;

//===============================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//===============================================================================
/**
 * @return  void  CSS�o��
 */
function _printCss($cssFilePath)
{
    global $_conf, $STYLE, $MYSTYLE;
    
/*
// �N�G���Ƀ��j�[�N�L�[�𖄂ߍ���ł��邢��̂ŁA�L���b�V�������Ă悢
// �m�[�}��p2�ł͂܂��܂�łȂ���
$now = time();
header('Expires: ' . http_date($now + 3600));
header('Last-Modified: ' . http_date($now));
header('Pragma: cache');
header('Content-Type: text/css; charset=Shift_JIS');
*/

    $mtime = max(filemtime($cssFilePath), filemtime(_getSkinFilePath()));

    if (file_exists($_conf['conf_user_file'])) {
        $mtime = max($mtime, filemtime($_conf['conf_user_file']));
    }
    
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
    header('Content-Type: text/css; charset=Shift_JIS');
    
    echo "@charset \"Shift_JIS\";\n\n";
    ob_start();
    include_once $cssFilePath;
    
    // $MYSTYLE��CSS���㏑���\��
    require_once P2_LIB_DIR . '/mystyle_css.inc.php';
    printMystyleCssByFileName($cssFilePath);

    // ��X�^�C��������
    echo preg_replace('/[a-z\\-]+[ \\t]*:[ \\t]*;/', '', ob_get_clean());
}

/**
 * @return  string
 */
function _getCssFilePath($cssname)
{
    return P2_STYLE_DIR . DIRECTORY_SEPARATOR . rawurlencode($cssname) . '_css.inc';;
}

/**
 * �Ó���CSS�t�@�C���w�肩���؂��Ď擾����
 *
 * @return  string|false
 */
function _getValidCssFilePath($cssname)
{
    if (preg_match('/^\\w+$/', $cssname)) {
        $cssFilePath = _getCssFilePath($cssname);
        if (file_exists($cssFilePath)) {
            return $cssFilePath;
        }
    }
    return false;
}

/**
 * @return  string
 */
function _getSkinFilePath()
{
    global $_conf;
    
    $skinFilePath = '';
    
    if (isset($_GET['skin'])) {
        $skinFilePath = P2Util::getSkinFilePathBySkinName($_GET['skin']);
    } elseif (file_exists($_conf['skin_setting_path'])) {
        $skinFilePath = P2Util::getSkinFilePathBySkinName(
            rtrim(file_get_contents($_conf['skin_setting_path']))
        );
    }
    if (!$skinFilePath || !is_file($skinFilePath)) {
        $skinFilePath = $_conf['conf_user_style_inc_php'];
    }
    return $skinFilePath;
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
