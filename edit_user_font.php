<?php
/*
    expack - �t�H���g�ݒ�ҏW�C���^�t�F�[�X
*/

// {{{ ������

// �����ݒ�ǂݍ��� & ���[�U�F��
require_once 'conf/conf.inc.php';
$_login->authorize();

require_once 'HTML/Template/Flexy.php';

$flexy_options = array(
    'templateDir' => './skin',
    'compileDir'  => $_conf['cache_dir'],
    'locale' => 'ja',
    'charset' => 'cp932',
);

$fontconfig_types = array(
    'windows'   => 'Windows',
    'safari2'   => 'Safari >= 2.0',
    'safari1'   => 'Safari < 2.0',
    'macosx'    => 'Mac OS X (Safari�ȊO)',
    'macos9'    => 'Mac OS classic',
//  'pda'       => 'PDA, �g�уt���u���E�U', // ���s���̂��ߔ��胋�[�`���������Ȃ�
    'other'     => '���̑�',
);
$fontconfig_params = array('fontfamily', 'fontfamily_bold', 'fontfamily_aa', 'fontsize', 'menu_fontsize', 'sb_fontsize', 'read_fontsize', 'respop_fontsize', 'infowin_fontsize', 'form_fontsize');

$fontconfig_sizes = array('' => '', '6px' => '6', '8px' => '8', '9px' => '9', '10px' => '10', '11px' => '11', '12px' => '12', '13px' => '13', '14px' => '14', '16px' => '16', '18px' => '18', '21px' => '21', '24px' => '24');

$controllerObject = new StdClass;
$controllerObject->fontconfig_types = $fontconfig_types;
$controllerObject->fontconfig_params = $fontconfig_params;
$controllerObject->skindata = fontconfig_load_skin_setting();
$controllerObject->safari2 = false;
$controllerObject->macos = false;

if (file_exists($_conf['expack.skin.fontconfig_path'])) {
    $current_fontconfig = unserialize(file_get_contents($_conf['expack.skin.fontconfig_path']));
    if (!is_array($current_fontconfig)) {
        $current_fontconfig = array('enabled' => false, 'custom' => array());
    }
} else {
    require_once P2_LIBRARY_DIR . '/filectl.class.php';
    FileCtl::make_datafile($_conf['expack.skin.fontconfig_path'], $_conf['expack.skin.fontconfig_perm']);
    $current_fontconfig = array('enabled' => false, 'custom' => array());
}
$fontconfig_hash = md5(serialize($current_fontconfig));
$updated_fontconfig = array('enabled' => false, 'custom' => array());

// Mac �̓u���E�U�ɂ���ĕ����̃����_�����O���ʂ��傫���ς��
// ���̎�ނ��������������̂Ō��݂̃u���E�U�Ƀ}�b�`���Ȃ����̂��B��
$ft = &$controllerObject->fontconfig_types;
$type = fontconfig_detect_agent();
switch ($type) {
    case 'safari2':
        $controllerObject->safari2 = true;
        unset($ft['safari1'], $ft['macosx'], $ft['macos9']);
        break;
    case 'safari1':
        unset($ft['safari2'], $ft['macosx'], $ft['macos9']);
        break;
    case 'macosx':
        $controllerObject->macos = true;
        unset($ft['safari2'], $ft['safari1'], $ft['macos9']);
        break;
    case 'macos9':
        $controllerObject->macos = true;
        unset($ft['safari2'], $ft['safari1'], $ft['macosx']);
        break;
    default:
        unset($ft['safari1'], $ft['macosx'], $ft['macos9']);
}

// }}}

// �e���v���[�g���R���p�C��
$flexy = new HTML_Template_Flexy($flexy_options);
if (!is_dir($_conf['cache_dir'])) {
    FileCtl::mkdir_for($_conf['cache_dir'] . '/dummy_filename');
}
$flexy->compile('edit_user_font.tpl.html');
$elements = $flexy->getElements();

// �J�X�^���ݒ�𗘗p���邩�ۂ���؂�ւ���
if (isset($_POST['use_skin'])) {
    $use_skin = is_array($_POST['use_skin']) ? current($_POST['use_skin']) : $_POST['use_skin'];
} else {
    $use_skin = !$current_fontconfig['enabled'];
}
if ($use_skin) {
    $elements['use_skin']->setAttributes(array('checked' => true));
    $elements['use_user']->setAttributes(array('checked' => false));
    $updated_fontconfig['enabled'] = false;
} else {
    $elements['use_skin']->setAttributes(array('checked' => false));
    $elements['use_user']->setAttributes(array('checked' => true));
    $updated_fontconfig['enabled'] = true;
}

// �ύX�̓K�p�ƁA�t�H�[���֒l����
if (!empty($_POST['clear'])) {
    $_POST = array();
    $current_fontconfig['custom'] = array();
}
foreach ($fontconfig_params as $pname) {
    $elemName = $pname . '[%s]';
    if (isset($elements[$elemName])) {
        foreach ($fontconfig_types as $tname => $ttitle) {
            $newElemName = sprintf($elemName, $tname);
            if (!isset($elements[$newElemName])) {
                $elements[$newElemName] = clone($elements[$elemName]);
            }
            if (!is_array($updated_fontconfig['custom'][$tname])) {
                $updated_fontconfig['custom'][$tname] = array();
            }
            if (isset($_POST[$pname][$tname])) {
                $value = trim($_POST[$pname][$tname]);
            } elseif (isset($current_fontconfig['custom'][$tname][$pname])) {
                $value = $current_fontconfig['custom'][$tname][$pname];
            } else {
                $value = '';
            }
            if ($elements[$newElemName]->tag == 'select' && strpos($pname, 'fontsize') !== false) {
                $elements[$newElemName]->setOptions($fontconfig_sizes);
                if (!array_key_exists($value, $fontconfig_sizes)) {
                    $elements[$newElemName]->setOptions(array($value => $value));
                }
            }
            if ($value) {
                $updated_fontconfig['custom'][$tname][$pname] = $value;
            }
            $elements[$newElemName]->setValue($value);
        }
    }
}

// �ۑ�
$fontconfig_data = serialize($updated_fontconfig);
$fontconfig_new_hath = md5($fontconfig_data);
if (strcmp($fontconfig_hash, $fontconfig_new_hath) != 0) {
    FileCtl::file_write_contents($_conf['expack.skin.fontconfig_path'], $fontconfig_data);
}

// �X�^�C���V�[�g�����Z�b�g
unset($STYLE);
include($skin);
if ($updated_fontconfig['enabled']) {
    fontconfig_apply_custom();
} else {
    $skin_en = preg_replace('/&amp;etag=[^&]*/', '', $skin_en);
    $skin_en .= '&amp;etag=' . urlencode($skin_etag);
}
$controllerObject->STYLE = $STYLE;
$controllerObject->skin = $skin_en;
$controllerObject->rep2expack = $_conf['p2expack'];

// �o��
$flexy->outputObject($controllerObject, $elements);

/**
 * �J�X�^���ݒ�ŏ㏑������Ă��Ȃ��X�L���ݒ��ǂݍ���
 */
function fontconfig_load_skin_setting()
{
    global $_conf, $STYLE;

    $skindata = array();

    $fontfamily = (isset($STYLE['fontfamily.orig']))
        ? $STYLE['fontfamily.orig']
        : ((isset($STYLE['fontfamily'])) ? $STYLE['fontfamily'] : '');
    $skindata['fontfamily'] = is_array($fontfamily)
        ? implode_fonts($fontfamily)
        : (string) $fontfamily;

    $fontfamily_bold = (isset($STYLE['fontfamily_bold.orig']))
        ? $STYLE['fontfamily_bold.orig']
        : ((isset($STYLE['fontfamily_bold'])) ? $STYLE['fontfamily_bold'] : '');
    $skindata['fontfamily_bold'] = is_array($fontfamily_bold)
        ? implode_fonts($fontfamily_bold)
        : (string) $fontfamily_bold;

    $fontfamily_aa = (isset($_conf['expack.am.fontfamily.orig']))
        ? $_conf['expack.am.fontfamily.orig']
        : ((isset($_conf['expack.am.fontfamily'])) ? $_conf['expack.am.fontfamily'] : '');
    $skindata['fontfamily_aa'] = is_array($fontfamily_aa)
        ? implode_fonts($fontfamily_aa)
        : (string) $fontfamily_aa;

    $sizes = array(
        'fontsize', 'menu_fontsize', 'sb_fontsize', 'read_fontsize',
        'form_fontsize', 'respop_fontsize', 'infowin_fontsize'
    );
    foreach ($sizes as $size) {
        $skindata[$size] = (isset($STYLE[$size])) ? $STYLE[$size] : '';
        $skindata["{$size}_nu"] = preg_replace('/px$/', '', $skindata[$size]);
    }

    return $skindata;
}

function implode_fonts($fonts)
{
    return '"' . implode('","', $fonts) . '"';
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
