<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=4 fdm=marker: */
/* mi: charset=Shift_JIS */

/* ImageCache2 - �A�b�v���[�_ */

// {{{ p2��{�ݒ�ǂݍ���&�F��

require_once 'conf/conf.inc.php';

$_login->authorize();

if (!$_conf['expack.ic2.enabled']) {
    exit('<html><body><p>ImageCache2�͖����ł��B<br>conf/conf_admin_ex.inc.php �̐ݒ��ς��Ă��������B</p></body></html>');
}

// }}}
// {{{ ���C�u�����ǂݍ���

require_once 'HTML/Template/Flexy.php';
require_once P2EX_LIBRARY_DIR . '/ic2/loadconfig.inc.php';
require_once P2EX_LIBRARY_DIR . '/ic2/database.class.php';
require_once P2EX_LIBRARY_DIR . '/ic2/db_images.class.php';
require_once P2EX_LIBRARY_DIR . '/ic2/thumbnail.class.php';

// }}}
// {{{ config

// �ݒ�t�@�C���ǂݍ���
$ini = ic2_loadconfig();

// �ő�t�@�C���T�C�Y��ݒ�
$ic2_maxsize = $ini['Source']['maxsize'];
if (preg_match('/(\d+\.?\d*)([KMG])/i', $ic2_maxsize, $m)) {
    $ic2_maxsize = si2int($m[1], $m[2]);
} else {
    $ic2_maxsize = (int)$ic2_maxsize;
}

$ini_maxsize = ini_get('upload_max_filesize');
if (preg_match('/(\d+\.?\d*)([KMG])/i', $ini_maxsize, $m)) {
    $ini_maxsize = si2int($m[1], $m[2]);
} else {
    $ini_maxsize = (int)$ini_maxsize;
}

if (0 < $ic2_maxsize && $ic2_maxsize < $ini_maxsize) {
    $maxsize    = $ic2_maxsize;
    $maxsize_si = $ini['Source']['maxsize'];
} else {
    $maxsize    = $ini_maxsize;
    $maxsize_si = ini_get('upload_max_filesize');
}

$maxwidth  = (int)$ini['Source']['maxwidth'] ;
$maxheight = (int)$ini['Source']['maxheight'];

// �|�b�v�A�b�v�E�C���h�E�H
$isPopUp = empty($_REQUEST['popup']) ? 0 : 1;

// �Ή�MIME�^�C�v
$mimemap = array(IMAGETYPE_GIF => 'image/gif', IMAGETYPE_JPEG => 'image/jpeg', IMAGETYPE_PNG => 'image/png');
$mimeregex = '{^(image/(p?jpeg|png|gif)|application/octet-stream)$}';

// �G���[���b�Z�[�W�̃t�H�[�}�b�g
$err_fmt = array();
$err_fmt['none'] = "<p>Error: �t�@�C��������I�΂�Ă��܂���B</p>\n";
$err_fmt['file'] = "<p>Error: %s �� %s</p>\n";
$err_fmt['mime'] = "<p>Error: %s �� ��Ή���MIME�^�C�v�ł��B(%s)</p>\n";
$err_fmt['name'] = "<p>Error: %s �� �t�@�C�����̎擾�Ɏ��s���܂����B</p>\n";
$err_fmt['size'] = "<p>Error: %s �� �摜�T�C�Y�̎擾�Ɏ��s���܂����B</p>\n";
$err_fmt['pix']  = "<p>Error: %s �� �摜���傫�����܂��B(%s &times; %s, {$maxwidth} &times; {$maxheight} �܂�)</p>\n";
$err_fmt['dir']  = "<p>Error: %s �� �f�B���N�g�����쐬�ł��܂���ł����B(%s)</p>\n";
$err_fmt['move'] = "<p>Error: %s �� ���l�[�����s�B(%s �� %s)</p>\n";

// }}}
// {{{ process uploaded file

$upfiles = array();
if (!empty($_GET['upload']) && !empty($_FILES['upimg'])) {
    $errors = array_count_values($_FILES['upimg']['error']);
    if (!empty($errors[UPLOAD_ERR_NO_TMP_DIR])) {
        die('<html><body><p>�t�@�C���A�b�v���[�h�p�̃e���|�����t�H���_������܂���B</p></body></html>');
    } elseif (count($_FILES['upimg']['error']) == $errors[UPLOAD_ERR_NO_FILE]) {
        $_info_msg_ht .= $err_fmt['none'];
    } else {
        // �T���l�C���쐬�N���X�̃C���X�^���X���쐬
        $thumbnailer = &new ThumbNailer(IC2_THUMB_SIZE_DEFAULT);

        // DB�ɋL�^���鋤�ʃf�[�^��ݒ�
        $f_host = 'localhost';
        $f_time = time();
        $f_memo = isset($_POST['memo']) ? IC2DB_Images::uniform($_POST['memo'], 'SJIS-win') : '';
        $f_rank = isset($_POST['rank']) ? intval($_POST['rank']) : 0;
        if ($f_rank > 5) {
            $f_rank = 5;
        } elseif ($f_rank < 0) {
            $f_rank = 0;
        }

        // �A�b�v���[�h���ꂽ�t�@�C��������
        foreach ($_FILES['upimg']['name'] as $key => $value) {
            $path     = $_POST['path'][$key];
            $name     = $_FILES['upimg']['name'][$key];
            $type     = $_FILES['upimg']['type'][$key];
            $filesize = $_FILES['upimg']['size'][$key];
            $tmpname  = $_FILES['upimg']['tmp_name'][$key];
            $errcode  = $_FILES['upimg']['error'][$key];

            if ($errcode == UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $file = ic2_check_uploaded_file($path, $name, $type, $filesize, $tmpname, $errcode);
            if (is_array($file)) {
                $upfiles[] = $file;
            } else {
                $_info_msg_ht .= $file;
            }
        }
    }
}

// }}}
// {{{ output

$_flexy_options = array(
    'locale' => 'ja',
    'charset' => 'cp932',
    'compileDir' => $ini['General']['cachedir'] . '/' . $ini['General']['compiledir'],
    'templateDir' => P2EX_LIBRARY_DIR . '/ic2/templates',
    'numberFormat' => '', // ",0,'.',','" �Ɠ���
);

$flexy = &new HTML_Template_Flexy($_flexy_options);
$flexy->compile('ic2s.tpl.html');

if (!$isPopUp && (!empty($upfiles) || $_info_msg_ht != '')) {
    $showForm = FALSE;
} else {
    $showForm = TRUE;
}

// �t�H�[�����C��
$elements = $flexy->getElements();
if ($showForm) {
    $form_attr = array(
        'action' => $_SERVER['SCRIPT_NAME'] . '?upload=1',
        'accept-charset' => $_conf['accept_charset'],
    );
    $elements['fileupload']->setAttributes($form_attr);
    $elements['MAX_FILE_SIZE']->setValue($maxsize);
    $elements['fix_hfs']->setValue((int)P2Util::isBrowserSafariGroup());
    $elements['popup']->setValue($isPopUp);
    if ($isPopUp) {
        $elements['fileupload']->setAttributes('target="_self"');
    } else {
        $elements['fileupload']->setAttributes('target="read"');
    }
}

// �e���v���[�g�ϐ�
$view = new StdClass;
$view->php_self = $_SERVER['SCRIPT_NAME'];
$view->STYLE    = $STYLE;
$view->skin     = $skin_en;
$view->isPopUp  = $isPopUp;
$view->showForm = $showForm;
$view->info_msg = $_info_msg_ht;
$view->upfiles  = $upfiles;
$view->maxfilesize = $maxsize_si;
$view->maxpostsize = ini_get('post_max_size');

// �y�[�W��\��
P2Util::header_nocache();
P2Util::header_content_type();
$flexy->outputObject($view, $elements);

// }}}
// {{{ �֐�

/**
 * �A�b�v���[�h���ꂽ�e�摜�t�@�C�������؂���B
 * ��肪�Ȃ���� ic2_register_uploaded_file() �Ƀt�@�C������n���A
 * ��肪����΃G���[���b�Z�[�W�i������j��Ԃ��B
 */
function ic2_check_uploaded_file($path, $name, $type, $filesize, $tmpname, $errcode)
{
    global $_conf, $_hfs, $ini, $err_fmt;
    global $mimemap, $mimeregex, $maxsize, $maxwidth, $maxheight;

    $path_ht = htmlspecialchars($path, ENT_QUOTES);

    // �A�b�v���[�h���s�̂Ƃ�
    if ($errcode != UPLOAD_ERR_OK) {
        switch ($errcode) {
            case UPLOAD_ERR_INI_SIZE:
                $errmsg = '�A�b�v���[�h���ꂽ�t�@�C���́Aphp.ini �� upload_max_filesize �f�B���N�e�B�u�̒l�𒴂��Ă��܂��B';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $errmsg = '�A�b�v���[�h���ꂽ�t�@�C���́AHTML�t�H�[���Ŏw�肳�ꂽ MAX_FILE_SIZE �𒴂��Ă��܂��B';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errmsg = '�A�b�v���[�h���ꂽ�t�@�C���͈ꕔ�݂̂����A�b�v���[�h����Ă��܂���B';
                break;
            default:
                $errmsg = '�����s���̃G���[';
        }
        return sprintf($err_fmt['file'], $path_ht, $errmsg);
    }

    // �u���E�U���瑗�M���ꂽMIME�^�C�v������
    if (!preg_match($mimeregex, $type)) {
        return sprintf($err_fmt['mime'], $path_ht, $type);
    }

    // �������摜�t�@�C�����ǂ������m�F
    $size = @getimagesize($tmpname);
    if (!$size || !$size[0] || !$size[1]) {
        return sprintf($err_fmt['size'], $path_ht);
    }

    // �c���̑傫�����m�F
    if (($maxwidth > 0 && $size[0] > $maxwidth) || ($maxheight > 0 && $size[1] > $maxheight)) {
        return sprintf($err_fmt['pix'], $size[0], $size[1]);
    }

    // ������x MIME�^�C�v������
    $type = $size[2];
    if (!isset($mimemap[$type])) {
        $mime = isset($size['mime']) ? $size['mime'] : $type;
        return sprintf($err_fmt['mime'], $path_ht, $mime);
    }

    // �t�@�C�������擾
    $basename = mb_basename($path);
    if ($basename == '') {
        if ($_hfs) {
            $name = combinehfskana($name);
        }
        $name = mb_convert_encoding($name, 'SJIS-win', 'UTF-8,eucJP-win,SJIS-win');
        $basename = mb_basename($name);
        if ($name == '') {
            return sprintf($err_fmt['name'], $path_ht);
        }
    }

    // �t�@�C������ݒ�
    $file = array();
    $file['path'] = $path;
    $file['name'] = $basename;
    $file['size'] = $filesize;
    $file['mime'] = $mimemap[$type];
    $file['width']  = $size[0];
    $file['height'] = $size[1];
    $file['md5'] = md5_file($tmpname);
    $file['tmp_name'] = $tmpname;

    // DB�ɓo�^���A���ʂ�Ԃ�
    return ic2_register_uploaded_file($file);
}

/**
 * �A�b�v���[�h���ꂽ�摜�t�@�C����DB�ɓo�^����B
 * ���������Ƃ��̓t�@�C�����i�z��j���A
 * ���s�����Ƃ��̓G���[���b�Z�[�W�i������j��Ԃ��B
 */
function ic2_register_uploaded_file($file)
{
    global $_conf, $_hfs, $ini, $err_fmt;
    global $thumbnailer;
    global $f_host, $f_time, $f_memo, $f_rank;

    $utf8_path = mb_convert_encoding($file['path'], 'UTF-8', 'SJIS-win');
    $utf8_name = mb_convert_encoding($file['name'], 'UTF-8', 'SJIS-win');
    $file['path'] = htmlspecialchars($file['path'], ENT_QUOTES);
    $file['name'] = htmlspecialchars($file['name'], ENT_QUOTES);
    $file['memo'] = $f_memo;
    $file['rank'] = $f_rank;
    $file['img_src'] = $thumbnailer->srcPath($file['size'], $file['md5'], $file['mime']);
    $file['thumb'] = $thumbnailer->thumbPath($file['size'], $file['md5'], $file['mime']);
    if (!file_exists($file['thumb'])) {
        $file['thumb'] = 'ic2.php?r=1&t=1&file=' . $file['size'] . '_' . $file['md5'];
    }
    if (preg_match('/(\d+)x(\d+)/', $thumbnailer->calc($file['width'], $file['height']), $thumb_xy)) {
        $file['thumb_x'] = $thumb_xy[1];
        $file['thumb_y'] = $thumb_xy[2];
    }

    // �����̉摜������
    $search1 = &new IC2DB_Images;
    $search1->whereAddQuoted('size', '=', $file['size']);
    $search1->whereAddQuoted('md5',  '=', $file['md5']);
    $search1->whereAddQuoted('mime', '=', $file['mime']);

    $search2 = clone($search1);
    $search1->whereAddQuoted('uri',  '=', $utf8_path);

    // �S�������摜���o�^����Ă����Ƃ�
    if ($search1->find(TRUE)) {
        $update = clone($search1);
        $changed = FALSE;
        if (strlen($f_memo) > 0 && !strstr($search1->memo, $f_memo)){
            if (!is_null($search1->memo) && strlen($search1->memo) > 0) {
                $update->memo = $f_memo . ' ' . $search1->memo;
            } else {
                $update->memo = $f_memo;
            }
            $file['memo'] = mb_convert_encoding($update->memo, 'SJIS-win', 'UTF-8');
            $changed = TRUE;
        }
        if ($search1->rank != $f_rank) {
            $update->rank = $f_rank;
            $changed = TRUE;
        }
        if ($changed) {
            $update->update();
        }
        $file['message'] = '�����摜���o�^����Ă��܂����B';
        if ($changed) {
            $file['message'] .= '(�X�e�[�^�X�̍X�V����)';
        }

    } else {

        $record = &new IC2DB_Images;
        $record->uri    = $utf8_path;
        $record->host   = $f_host;
        $record->name   = $utf8_name;
        $record->size   = $file['size'];
        $record->md5    = $file['md5'];
        $record->width  = $file['width'];
        $record->height = $file['height'];
        $record->mime   = $file['mime'];
        $record->time   = $f_time;
        $record->rank   = $f_rank;
        if (strlen($f_memo) > 0) {
            $record->memo = $f_memo;
        }

        // �o�^�ς݂̉摜�ŁAURL���قȂ�Ƃ�
        if ($search2->find(TRUE) && file_exists($file['img_src'])) {
            $record->insert();
            $file['message'] = '�����摜���قȂ�URL�œo�^����Ă��܂����B';

        // ���o�^�̉摜�������Ƃ�
        } else {
            $newdir = dirname($file['img_src']);
            if (!is_dir($newdir) && !@mkdir($newdir)) {
                return sprintf($err_fmt['dir'], $file['path'], $newdir);
            }
            if (!@move_uploaded_file($file['tmp_name'], $file['img_src'])) {
                return sprintf($err_fmt['file'], $file['path'], $file['tmp_name'], $file['img_src']);
            }
            $record->insert();
            $file['message'] = '�A�b�v���[�h�����B';
        }
    }

    return $file;
}

// }}}
?>
