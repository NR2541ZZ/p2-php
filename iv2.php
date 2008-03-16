<?php
/* ImageCache2 - �摜�L���b�V���ꗗ */

// {{{ p2��{�ݒ�ǂݍ���&�F��

define('P2_FORCE_USE_SESSION', 1);
define('P2_SESSION_NO_CLOSE', 1);

require_once 'conf/conf.inc.php';

$_login->authorize();

if (!$_conf['expack.ic2.enabled']) {
    exit('<html><body><p>ImageCache2�͖����ł��B<br>conf/conf_admin_ex.inc.php �̐ݒ��ς��Ă��������B</p></body></html>');
}

if ($b == 'pc') {
    output_add_rewrite_var('b', 'pc');
} elseif ($b == 'k' || $k) {
    output_add_rewrite_var('b', 'k');
}

// }}}
// {{{ ������

// ���C�u�����ǂݍ���
require_once 'PEAR.php';
require_once 'DB.php';
require_once 'DB/DataObject.php';
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ObjectFlexy.php';
require_once 'HTML/Template/Flexy.php';
require_once 'HTML/Template/Flexy/Element.php';
require_once P2EX_LIBRARY_DIR . '/ic2/loadconfig.inc.php';
require_once P2EX_LIBRARY_DIR . '/ic2/database.class.php';
require_once P2EX_LIBRARY_DIR . '/ic2/db_images.class.php';
require_once P2EX_LIBRARY_DIR . '/ic2/thumbnail.class.php';
require_once P2EX_LIBRARY_DIR . '/ic2/quickrules.class.php';
require_once P2EX_LIBRARY_DIR . '/ic2/editform.class.php';
require_once P2EX_LIBRARY_DIR . '/ic2/managedb.inc.php';
require_once P2EX_LIBRARY_DIR . '/ic2/getvalidvalue.inc.php';
require_once P2EX_LIBRARY_DIR . '/ic2/buildimgcell.inc.php';
require_once P2EX_LIBRARY_DIR . '/ic2/matrix.class.php';

// }}}
// {{{ config

// �ݒ�t�@�C���ǂݍ���
$ini = ic2_loadconfig();

// DB_DataObject�̐ݒ�
$_dbdo_options = &PEAR::getStaticProperty('DB_DataObject','options');
$_dbdo_options = array('database' => $ini['General']['dsn'], 'debug' => false, 'quote_identifiers' => true);

// Exif�\�����L�����H
$show_exif = ($ini['Viewer']['exif'] && extension_loaded('exif'));

// �t�H�[���̃f�t�H���g�l
$_defaults = array(
    'page'  => 1,
    'cols'  => $ini['Viewer']['cols'],
    'rows'  => $ini['Viewer']['rows'],
    'inum'  => $ini['Viewer']['inum'],
    'order' => $ini['Viewer']['order'],
    'sort'  => $ini['Viewer']['sort'],
    'field' => $ini['Viewer']['field'],
    'key'   => '',
    'threshold' => $ini['Viewer']['threshold'],
    'compare' => '>=',
    'mode' => 0,
);

// �t�H�[���̌Œ�l
$_constants = array(
    'start'   => '<<',
    'prev'    => '<',
    'next'    => '>',
    'end'     => '>>',
    'jump'    => 'Go',
    'search'  => '����',
    'cngmode' => '�ύX',
    '_hint'   => $_conf['detect_hint'],
);

// 臒l��r���@
$_compare = array(
    '>=' => '&gt;=',
    '='  => '=',
    '<=' => '&lt;=',
);

// 臒l
$_threshold = array(
    '-1' => '-1',
    '0' => '0',
    '1' => '1',
    '2' => '2',
    '3' => '3',
    '4' => '4',
    '5' => '5',
);

// �\�[�g�
$_order = array(
    'time' => '�擾����',
    'uri'  => 'URL',
    'date_uri' => '���t+URL',
    'date_uri2' => '���t+URL(2)',
    'name' => '�t�@�C����',
    'size' => '�t�@�C���T�C�Y',
    'width' => '����',
    'height' => '����',
    'pixels' => '�s�N�Z����',
    'id' => 'ID',
);

// �\�[�g����
$_sort = array(
    'ASC'  => '����',
    'DESC' => '�~��',
);

// �����t�B�[���h
$_field = array(
    'uri'  => 'URL',
    'name' => '�t�@�C����',
    'memo' => '����',
);

// ���[�h
$_mode = array(
    '3' => '��Ȳق���',
    '0' => '�ꗗ',
    '1' => '�ꊇ�ύX',
    '2' => '�ʊǗ�',
);

// �g�їp�ɕϊ��i�t�H�[�����p�P�b�g�ߖ�̑ΏۊO�Ƃ��邽�߁j
if ($_conf['ktai']) {
    foreach ($_order as $_k => $_v) {
        $_order[$_k] = mb_convert_kana($_v, 'ask');
    }
    foreach ($_field as $_k => $_v) {
        $_field[$_k] = mb_convert_kana($_v, 'ask');
    }
}

// }}}
// {{{ prepare (DB & Cache)

// DB_DataObject���p������DAO
$icdb = &new IC2DB_Images;
$db = &$icdb->getDatabaseConnection();

// �T���l�C���쐬�N���X
$thumb = &new ThumbNailer(IC2_THUMB_SIZE_DEFAULT);

if ($ini['Viewer']['cache']) {
    require_once 'Cache.php';
    require_once 'Cache/Function.php';
    // �f�[�^�L���b�V���ɂ�Cache_Container_db(Cache 1.5.4)���n�b�N����MySQL�ȊO�ɂ��Ή������A
    // �R���X�g���N�^��DB_xxx(DB_mysql�Ȃ�)�̃C���X�^���X���󂯎���悤�ɂ������̂��g���B
    // �i�t�@�C�����E�N���X���͓����ŁAinclude_path�𒲐�����
    //   �I���W�i����Cache/Container/db.php�̑���ɂ���j
    $cache_options = array(
        'dsn'           => $ini['General']['dsn'],
        'cache_table'   => $ini['Cache']['table'],
        'highwater'     => (int)$ini['Cache']['highwater'],
        'lowwater'      => (int)$ini['Cache']['lowwater'],
        'db' => &$db
    );
    $cache = &new Cache_Function('db', $cache_options, (int)$ini['Cache']['expires']);
    // �L�������؂�L���b�V���̃K�[�x�b�W�R���N�V�����Ȃ�
    if (isset($_GET['cache_clean'])) {
        $cache_clean = $_GET['cache_clean'];
    } elseif (isset($_POST['cache_clean'])) {
        $cache_clean = $_POST['cache_clean'];
    } else {
        $cache_clean = false;
    }
    switch ($cache_clean) {
        // �L���b�V����S�폜
        case 'all':
            $sql = sprintf('DELETE FROM %s', $db->quoteIdentifier($ini['Cache']['table']));
            $result = &$db->query($sql);
            if (DB::isError($result)) {
                die($result->getMessage());
            }
            $vacuumdb = true;
            break;
        // �����I�ɃK�[�x�b�W�R���N�V����
        case 'gc':
            $cache->garbageCollection(true);
            $vacuumdb = true;
            break;
        // gc_probability(�f�t�H���g��1)/100�̊m���ŃK�[�x�b�W�R���N�V����
        default:
            // $cache->gc_probability = 1;
            $cache->garbageCollection();
            $vacuumdb = false;
    }
    // SQLite�Ȃ�VACUUM�����s�iPostgreSQL�͕���cron��vacuumdb����̂ł����ł͂��Ȃ��j
    if ($vacuumdb && is_a($db, 'DB_sqlite')) {
        $result = &$db->query('VACUUM');
        if (DB::isError($result)) {
            die($result->getMessage());
        }
    }
    $enable_cache = true;
} else {
    $enable_cache = false;
}

// SQLite UDF
if (is_a($db, 'db_sqlite')) {
    $isSQLite = true;
    function iv2_sqlite_unix2date($ts)
    {
        return intval(date('Ymd', $ts));
    }
    $sqlite = &$db->connection;
    sqlite_create_function($sqlite, 'unix2date', 'iv2_sqlite_unix2date', 1);
} else {
    $isSQLite = false;
}

// }}}
// {{{ prepare (Form & Template)

// conf.inc.php�ňꊇstripslashes()���Ă��邯�ǁAHTML_QuickForm�ł��Ǝ���stripslashes()����̂ŁB
// �o�O�̉����ƂȂ�\�����ے�ł��Ȃ��E�E�E
if (get_magic_quotes_gpc()) {
    $_GET = array_map('addslashes_r', $_GET);
    $_POST = array_map('addslashes_r', $_POST);
    $_REQUEST = array_map('addslashes_r', $_REQUEST);
}

// �y�[�W�J�ڗp�t�H�[����ݒ�
// �y�[�W�J�ڂ�GET�ōs�����A�摜���̍X�V��POST�ōs���̂łǂ���ł��󂯓����悤�ɂ���
// �i�����_�����O�O�� $qf->updateAttributes(array('method' => 'get')); �Ƃ���j
$_attribures = array('accept-charset' => 'UTF-8,Shift_JIS');
$_method = ($_SERVER['REQUEST_METHOD'] == 'GET') ? 'get' : 'post';
$qf = &new HTML_QuickForm('go', $_method, $_SERVER['SCRIPT_NAME'], '_self', $_attribures);
$qf->registerRule('numRange', null, 'RuleNumericRange');
$qf->registerRule('inArray', null, 'RuleInArray');
$qf->registerRule('inArrayKeys', null, 'RuleInArrayKeys');
$qf->setDefaults($_defaults);
$qf->setConstants($_constants);
$qfe = array();

// �t�H�[���v�f�̒�`

// �y�[�W�ړ��̂��߂�submit�v�f
$qfe['start'] = &$qf->addElement('button', 'start');
$qfe['prev']  = &$qf->addElement('button', 'prev');
$qfe['next']  = &$qf->addElement('button', 'next');
$qfe['end']   = &$qf->addElement('button', 'end');
$qfe['jump']  = &$qf->addElement('button', 'jump');

// �\�����@�Ȃǂ��w�肷��input�v�f
$qfe['page']      = &$qf->addElement('text', 'page', '�y�[�W�ԍ����w��', array('size' => 3));
$qfe['cols']      = &$qf->addElement('text', 'cols', '��', array('size' => 3, 'maxsize' => 2));
$qfe['rows']      = &$qf->addElement('text', 'rows', '�c', array('size' => 3, 'maxsize' => 2));
$qfe['order']     = &$qf->addElement('select', 'order', '���я�', $_order);
$qfe['sort']      = &$qf->addElement('select', 'sort', '����', $_sort);
$qfe['field']     = &$qf->addElement('select', 'field', '�t�B�[���h', $_field);
$qfe['key']       = &$qf->addElement('text', 'key', '�L�[���[�h', array('size' => 20));
$qfe['compare']   = &$qf->addElement('select', 'compare', '��r���@', $_compare);
$qfe['threshold'] = &$qf->addElement('select', 'threshold', '�������l', $_threshold);

// �����R�[�h����̃q���g�ɂ���B��input�v�f
$qfe['_hint'] = &$qf->addElement('hidden', '_hint');

// ���������s����submit�v�f
$qfe['search'] = &$qf->addElement('submit', 'search');

// ���[�h�ύX������select�v�f
$qfe['mode'] = &$qf->addElement('select', 'mode', '���[�h', $_mode);

// ���[�h�ύX���m�肷��submit�v�f
$qfe['cngmode'] = &$qf->addElement('submit', 'cngmode');

// �t�H�[���̃��[��
$qf->addRule('cols', '1 to 20',  'numRange', array('min' => 1, 'max' => 20),  'client', true);
$qf->addRule('rows', '1 to 100', 'numRange', array('min' => 1, 'max' => 100), 'client', true);
$qf->addRule('order', 'invalid order.', 'inArrayKeys', $_order);
$qf->addRule('sort',  'invalid sort.',  'inArrayKeys', $_sort);
$qf->addRule('field', 'invalid field.', 'inArrayKeys', $_field);
$qf->addRule('threshold', '-1 to 5', 'numRange', array('min' => -1, 'max' => 5));
$qf->addRule('compare', 'invalid compare.', 'inArrayKeys', $_compare);
$qf->addRule('mode', 'invalid mode.', 'inArrayKeys', $_mode);

// Flexy
$_flexy_options = array(
    'locale' => 'ja',
    'charset' => 'cp932',
    'compileDir' => $ini['General']['cachedir'] . '/' . $ini['General']['compiledir'],
    'templateDir' => P2EX_LIBRARY_DIR . '/ic2/templates',
    'numberFormat' => '', // ",0,'.',','" �Ɠ���
    'plugins' => array('P2Util' => P2_LIBRARY_DIR . '/p2util.class.php')
);

$flexy = &new HTML_Template_Flexy($_flexy_options);

$flexy->setData('php_self', $_SERVER['SCRIPT_NAME']);
$flexy->setData('base_dir', dirname($_SERVER['SCRIPT_NAME']));
$flexy->setData('rep2expack', $_conf['p2expack']);
$flexy->setData('_hint', $_conf['detect_hint']);
if ($_conf['ktai']) {
    $k_color = array();
    $k_color['c_bgcolor'] = isset($_conf['mobile.background_color']) ? $_conf['mobile.background_color'] : '';
    $k_color['c_text']  = isset($_conf['mobile.text_color'])  ? $_conf['mobile.text_color']  : '';
    $k_color['c_link']  = isset($_conf['mobile.link_color'])  ? $_conf['mobile.link_color']  : '';
    $k_color['c_vlink'] = isset($_conf['mobile.vlink_color']) ? $_conf['mobile.vlink_color'] : '';
    $flexy->setData('k_color', $k_color);
    $flexy->setData('top_url', dirname($_SERVER['SCRIPT_NAME']) . '/index.php');
    $flexy->setData('accesskey', $_conf['accesskey']);
} else {
    $flexy->setData('skin', str_replace('&amp;', '&', $skin_en));
}

// }}}
// {{{ validate

// ����
$qf->validate();
$sv = $qf->getSubmitValues();
$page      = getValidValue('page',   $_defaults['page'], 'intval');
$cols      = getValidValue('cols',   $_defaults['cols'], 'intval');
$rows      = getValidValue('rows',   $_defaults['rows'], 'intval');
$order     = getValidValue('order',  $_defaults['order']);
$sort      = getValidValue('sort',   $_defaults['sort'] );
$field     = getValidValue('field',  $_defaults['field']);
$key       = getValidValue('key',    $_defaults['key']);
$threshold = getValidValue('threshold', $_defaults['threshold'], 'intval');
$compare   = getValidValue('compare',   $_defaults['compare']);
$mode      = getValidValue('mode',      $_defaults['mode'], 'intval');

// �g�їp�ɒ���
if ($_conf['ktai']) {
    $lightbox = false;
    $mode = 1;
    $inum = (int) $ini['Viewer']['inum'];
    $overwritable_params = array('order', 'sort', 'field', 'key', 'threshold', 'compare');

    // �G������ǂݍ���
    require_once 'conf/conf_emoji.php';
    $emj = getEmoji();
    $flexy->setData('e', $emj);

    // �t�B���^�����O�p�t�H�[����\��
    if (!empty($_GET['show_iv2_kfilter'])) {
        !defined('P2_NO_SAVE_PACKET') && define('P2_NO_SAVE_PACKET', true);
        $r = &new HTML_QuickForm_Renderer_ObjectFlexy($flexy);
        $qfe['key']->removeAttribute('size');
        $qf->updateAttributes(array('method' => 'get'));
        $qf->accept($r);
        $qfObj = &$r->toObject();
        $flexy->setData('page', $page);
        $flexy->setData('move', $qfObj);
        P2Util::header_nocache();
        $flexy->compile('iv2if.tpl.html');
        $flexy->output();
        exit;
    }
    // �Z�b�V�����ϐ��𑀍�
    elseif (!empty($_GET['session_no_close'])) {
        // �t�B���^�����Z�b�g
        if (!empty($_GET['reset_filter'])) {
            unset($_SESSION['iv2i_filter']);
        // �t�B���^��ݒ�
        } else {
            foreach ($overwritable_params as $ow_key) {
                if (isset($$ow_key)) {
                    $_SESSION['iv2i_filter'][$ow_key] = $$ow_key;
                }
            }
        }
        session_write_close();
    }
    // �t�B���^�����O�p�ϐ����X�V
    elseif (!empty($_SESSION['iv2i_filter'])) {
        foreach ($overwritable_params as $ow_key) {
            if (isset($_SESSION['iv2i_filter'][$ow_key])) {
                $$ow_key = $_SESSION['iv2i_filter'][$ow_key];
            }
        }
    }
} else {
    //$lightbox = ($mode == 0 || $mode == 3) ? $ini['Viewer']['lightbox'] : false;
    $lightbox = $ini['Viewer']['lightbox'];
}

// }}}
// {{{ query

$removed_files = array();

// 臒l�Ńt�B���^�����O
if (!($threshold == -1 && $compate == '>=')) {
    $icdb->whereAddQuoted('rank', $compare, $threshold);
}

// �L�[���[�h����������Ƃ�
if ($key !== '') {
    $keys = explode(' ', $icdb->uniform($key, 'SJIS-win'));
    foreach ($keys as $k) {
        $operator = 'LIKE';
        $wildcard = '%';
        if (preg_match('/[%_]/', $k)) {
            // SQLite2��LIKE���Z�q�̉E�ӂŃo�b�N�X���b�V���ɂ��G�X�P�[�v��
            // ESCAPE�ŃG�X�P�[�v�������w�肷�邱�Ƃ��ł��Ȃ��̂�GLOB���Z�q���g��
            if (strtolower(get_class($db)) == 'db_sqlite') {
                if (preg_match('/[*?]/', $k)) {
                    die('ImageCache2 - Warning:�u%�܂���_�v�Ɓu*�܂���?�v�����݂���L�[���[�h�͎g���܂���B');
                } else {
                    $operator = 'GLOB';
                    $wildcard = '*';
                }
            } else {
                $k = preg_replace('/[%_]/', '\\\\$0', $k);
            }
        }
        $expr = $wildcard . $k . $wildcard;
        $icdb->whereAddQuoted($field, $operator, $expr);
    }
    $qfe['key']->setValue($key);
}

// �d���摜���X�L�b�v����Ƃ�
// �����𐳂����J�E���g���邽�߂ɃT�u�N�G�����g��
// �T�u�N�G���ɑΉ����Ă��Ȃ��o�[�W����4.1������MySQL�ł͏d���摜�̃X�L�b�v�͖���
$dc = 0; // �����I�p�����[�^�A�o�^���R�[�h��������ȏ�̉摜�݂̂𒊏o
$mysql = preg_match('/^mysql:/', $ini['General']['dsn']); // MySQL 4.1.2�ȍ~��phptype��"mysqli"
if ($mysql == 0 && ($ini['Viewer']['unique'] || $dc > 2)) {
    $subq = 'SELECT ' . (($sort == 'ASC') ? 'MIN' : 'MAX') . '(id) FROM ';
    $subq .= $icdb->_db->quoteIdentifier($ini['General']['table']);
    if (isset($keys)) {
        // �T�u�N�G�����Ńt�B���^�����O����̂Őe�N�G����WHERE����p�N���Ă��ă��Z�b�g
        $subq .= $icdb->_query['condition'];
        $icdb->whereAdd();
    }
    // md5�����ŃO���[�v�����Ă��\���Ƃ͎v�����ǁA�ꉞ�B
    $subq .= ' GROUP BY size, md5, mime';
    if ($dc > 1) {
        $subq .= ' HAVING COUNT(*) >= ' . $dc;
    }
    // echo '<!--', mb_convert_encoding($subq, 'SJIS-win', 'UTF-8'), '-->';
    $icdb->whereAdd("id IN ($subq)");
}

// �f�[�^�x�[�X���X�V����Ƃ�
if (isset($_POST['edit_submit']) && !empty($_POST['change'])) {

    $target = array_unique(array_map('intval', $_POST['change']));

    switch ($mode) {

    // �ꊇ�Ńp�����[�^�ύX
    case 1:
        // �����N��ύX
        $newrank = intoRange($_POST['setrank'], -1, 5);
        manageDB_setRank($target, $newrank);
        // ������ǉ�
        if (!empty($_POST['addmemo'])) {
            $newmemo = get_magic_quotes_gpc() ? stripslashes($_POST['addmemo']) : $_POST['addmemo'];
            $newmemo = $icdb->uniform($newmemo, 'SJIS-win');
            if ($newmemo !== '') {
                 manageDB_addMemo($target, $newmemo);
            }
        }
        break;

    // �ʂɃp�����[�^�ύX
    case 2:
        // �X�V�p�̃f�[�^���܂Ƃ߂�
        $updated = array();
        $removed = array();
        $to_blacklist = false;
        $no_blacklist = false;

        foreach ($target as $id) {
            if (!empty($_POST['img'][$id]['remove'])) {
                if (!empty($_POST['img'][$id]['black'])) {
                    $to_blacklist = true;
                    $removed[$id] = true;
                } else {
                    $no_blacklist = true;
                    $removed[$id] = false;
                }
            } else {
                $newmemo = get_magic_quotes_gpc() ? stripslashes($_POST['img'][$id]['memo']) : $_POST['img'][$id]['memo'];
                $data = array(
                    'rank' => intval($_POST['img'][$id]['rank']),
                    'memo' => $icdb->uniform($newmemo, 'SJIS-win')
                );
                if (0 < $id && -1 <= $data['rank'] && $data['rank'] <= 5) {
                    $updated[$id] = $data;
                }
            }
        }

        // �����X�V
        if (count($updated) > 0) {
            manageDB_update($updated);
        }

        // �폜�i���u���b�N���X�g����j
        if (count($removed) > 0) {
            foreach ($removed as $id => $to_blacklist) {
                $removed_files = array_merge($removed_files, manageDB_remove(array($id), $to_blacklist));
            }
            if ($to_blacklist) {
                if ($no_blacklist) {
                    $flexy->setData('toBlackListAll', false);
                    $flexy->setData('toBlackListPartial', true);
                } else {
                    $flexy->setData('toBlackListAll', true);
                    $flexy->setData('toBlackListPartial', false);
                }
            } else {
                $flexy->setData('toBlackListAll', false);
                $flexy->setData('toBlackListPartial', false);
            }
        }
        break;

    } // endswitch

// �ꊇ�ŉ摜���폜����Ƃ�
} elseif ($mode == 1 && isset($_POST['edit_remove']) && !empty($_POST['change'])) {
    $target = array_unique(array_map('intval', $_POST['change']));
    $to_blacklist = !empty($_POST['edit_toblack']);
    $removed_files = array_merge($removed_files, manageDB_remove($target, $to_blacklist));
    $flexy->setData('toBlackList', $to_blacklist);
}

// }}}
// {{{ build

// �����R�[�h���𐔂���
//$db->setFetchMode(DB_FETCHMODE_ORDERED);
//$all = (int)$icdb->count('*', true);
//$db->setFetchMode(DB_FETCHMODE_ASSOC);
$sql = sprintf('SELECT COUNT(*) FROM %s %s', $db->quoteIdentifier($ini['General']['table']), $icdb->_query['condition']);
$all = $db->getOne($sql);
if (DB::isError($all)) {
    die($all->getMessage());
}

// �}�b�`���郌�R�[�h���Ȃ�������G���[��\���A���R�[�h������Ε\���p�I�u�W�F�N�g�ɒl����
if ($all == 0) {

    // ���R�[�h�Ȃ�
    $flexy->setData('nomatch', true);
    $flexy->setData('reset', $_SERVER['SCRIPT_NAME']);
    if ($_conf['ktai']) {
        $flexy->setData('kfilter', !empty($_SESSION['iv2i_filter']));
    }
    $qfe['start']->updateAttributes('disabled');
    $qfe['prev']->updateAttributes('disabled');
    $qfe['next']->updateAttributes('disabled');
    $qfe['end']->updateAttributes('disabled');
    $qfe['page']->updateAttributes('disabled');
    $qfe['jump']->updateAttributes('disabled');

} else {

    // ���R�[�h����
    $flexy->setData('nomatch', false);

    // �\���͈͂�ݒ�
    $ipp = $_conf['ktai'] ? $inum : $cols * $rows; // images per page
    $last_page = ceil($all / $ipp);

    // �y�[�W�J�ڗp�p�����[�^������
    if (isset($sv['search']) || isset($sv['cngmode'])) {
        $page = 1;
    } elseif (isset($sv['page'])) {
        $page = max(1, min((int)$sv['page'], $last_page));
    } else {
        $page = 1;
    }
    $prev_page = max(1, $page - 1);
    $next_page = min($page + 1, $last_page);

    // �y�[�W�J�ڗp�����N�i�g�сj�𐶐�
    if ($_conf['ktai']) {
        $pg_base = htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES);
        $pg_pos = $page . '/' . $last_page;
        $pg_delim = ' | ';
        $pg_fmt_akey = '<a href="%s?page=%d" %s="%d">%s%s</a>';
        $pg_fmt_link = '<a href="%s?page=%d">%s</a>';
        $pg_fmt_none = '%s %s';
        $_ak = $_conf['accesskey'];
        $pager1 = '';
        $pager2 = '';
        if ($page == 1) {
            //$pager1 .= sprintf($pg_fmt_none, $emj['lt2'], $emj['lt1']) . ' ';
            //$pager2 .= sprintf($pg_fmt_none, $emj['lt2'], $emj['lt1']) . ' ';
        } else {
            $pager1 .= sprintf($pg_fmt_akey, $pg_base,          1, $_ak, 1, $emj[1], $emj['lt2']) . ' ';
            $pager1 .= sprintf($pg_fmt_akey, $pg_base, $prev_page, $_ak, 4, $emj[4], $emj['lt1']) . ' ';
            $pager2 .= sprintf($pg_fmt_link, $pg_base,          1, $emj['lt2']) . ' ';
            $pager2 .= sprintf($pg_fmt_link, $pg_base, $prev_page, $emj['lt1']) . ' ';
        }
        $pager1 .= $pg_pos;
        $pager2 .= $pg_pos;
        if ($page == $last_page) {
            //$pager1 .= ' ' . sprintf($pg_fmt_none, $emj['rt1'], $emj['rt2']);
            //$pager2 .= ' ' . sprintf($pg_fmt_none, $emj['rt1'], $emj['rt2']);
        } else {
            $pager1 .= ' ' . sprintf($pg_fmt_link, $pg_base, $next_page, $emj['rt1']);
            $pager1 .= ' ' . sprintf($pg_fmt_link, $pg_base, $last_page, $emj['rt2']);
            $pager2 .= ' ' . sprintf($pg_fmt_akey, $pg_base, $next_page, $_ak, 6, $emj[6], $emj['rt1']);
            $pager2 .= ' ' . sprintf($pg_fmt_akey, $pg_base, $last_page, $_ak, 9, $emj[9], $emj['rt2']);
        }
        /*$pager1 .= $pg_delim;
        $pager2 .= $pg_delim;
        if (empty($_SESSION['iv2i_filter'])) {
            $pager_search = "<a href=\"{$pg_url}?page={$page}&amp;show_iv2_kfilter=1\">��</a>";
            $pager1 .= $pager_search;
            $pager2 .= $pager_search;
        } else {
            $pager_reset = "<a href=\"{$pg_url}?page=1&amp;session_no_close=1&amp;reset_filter=1\">��</a>";
            $pager1 .= $pager_reset;
            $pager2 .= $pager_reset;
        }*/
        $flexy->setData('pager1', $pager1);
        $flexy->setData('pager2', $pager2);

    // �y�[�W�J�ڗp�t�H�[���iPC�j�𐶐�
    } else {
        $mf_hiddens = array(
            '_hint' => $_conf['detect_hint'], 'mode' => $mode,
            'page' => $page, 'cols' => $cols, 'rows' => $rows,
            'order' => $order, 'sort' => $sort,
            'field' => $field, 'key' => $key,
            'compare' => $compare, 'threshold' => $threshold
        );
        $pager_q = $mf_hiddens;
        mb_convert_variables('UTF-8', 'SJIS-win', $pager_q);

        // �y�[�W�ԍ����X�V
        $qfe['page']->setValue($page);
        $qf->addRule('page', "1 to {$last_page}", 'numRange', array('min' => 1, 'max' => $last_page), 'client', true);

        // �ꎞ�I�Ƀp�����[�^��؂蕶���� & �ɂ��Č��݂̃y�[�W��URL�𐶐�
        $pager_separator = ini_get('arg_separator.output');
        ini_set('arg_separator.output', '&');
        $flexy->setData('current_page', $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($pager_q));
        ini_set('arg_separator.output', $pager_separator);
        unset($pager_q, $pager_separator);

        // �y�[�W����ړ��{�^���̑������X�V
        if ($page == 1) {
            $qfe['start']->updateAttributes('disabled');
            $qfe['prev']->updateAttributes('disabled');
        } else {
            $qfe['start']->updateAttributes(array('onclick' => "pageJump(1)"));
            $qfe['prev']->updateAttributes(array('onclick' => "pageJump({$prev_page})"));
        }

        // �y�[�W�O���ړ��{�^���̑������X�V
        if ($page == $last_page) {
            $qfe['next']->updateAttributes('disabled');
            $qfe['end']->updateAttributes('disabled');
        } else {
            $qfe['next']->updateAttributes(array('onclick' => "pageJump({$next_page})"));
            $qfe['end']->updateAttributes(array('onclick' => "pageJump({$last_page})"));
        }

        // �y�[�W�w��ړ��p�{�^���̑������X�V
        if ($last_page == 1) {
            $qfe['jump']->updateAttributes('disabled');
        } else {
            $qfe['jump']->updateAttributes(array('onclick' => "if(validate_go(this.form))pageJump(this.form.page.value)"));
        }
    }

    // �ҏW���[�h�p�t�H�[���𐶐�
    if ($mode == 1 || $mode == 2) {
        $flexy->setData('editFormHeader', EditForm::header($mf_hiddens, $mode));
        if ($mode == 1) {
            $flexy->setData('editFormCheckAllOn', EditForm::checkAllOn());
            $flexy->setData('editFormCheckAllOff', EditForm::checkAllOff());
            $flexy->setData('editFormCheckAllReverse', EditForm::checkAllReverse());
            $flexy->setData('editFormSelect', EditForm::selectRank($_threshold));
            $flexy->setData('editFormText', EditForm::textMemo());
            $flexy->setData('editFormSubmit', EditForm::submit());
            $flexy->setData('editFormReset', EditForm::reset());
            $flexy->setData('editFormRemove', EditForm::remove());
            $flexy->setData('editFormBlackList', EditForm::toblack());
        } elseif ($mode == 2) {
            $editForm = &new EditForm;
            $flexy->setData('editForm', $editForm);
        }
    }

    // DB����擾����͈͂�ݒ肵�Č���
    $from = ($page - 1) * $ipp;
    if ($order == 'pixels') {
        $orderBy = '(width * height) ' . $sort;
    } elseif ($order == 'date_uri' || $order == 'date_uri2') {
        if ($isSQLite) {
            $time2date = 'unix2date("time")';
        } else {
            // 32400 = 9*60*60 (�����␳)
            $time2date = sprintf('floor((%s + 32400) / 86400)', $db->quoteIdentifier('time'));
        }
        $orderBy .= sprintf('%s %s, %s ', $time2date, $sort, $db->quoteIdentifier('uri'));
        if ($order == 'date_uri') {
             $orderBy .= $sort;
        } else {
            $orderBy .= ($sort == 'ASC') ? 'DESC' : 'ASC';
        }
    } else {
        $orderBy = $db->quoteIdentifier($order) . ' ' . $sort;
    }
    $orderBy .= ' , id ' . $sort;
    $icdb->orderBy($orderBy);
    $icdb->limit($from, $ipp);
    $found = $icdb->find();

    // �e�[�u���̃u���b�N�ɕ\������l���擾&�I�u�W�F�N�g�ɑ��
    $flexy->setData('all',  $all);
    $flexy->setData('cols', $cols);
    $flexy->setData('last', $last_page);
    $flexy->setData('from', $from + 1);
    $flexy->setData('to',   $from + $found);
    $flexy->setData('submit', array());
    $flexy->setData('reset', array());

    if ($_conf['ktai']) {
        $show_exif = false;
        $popup = false;
        $r_type = ($ini['General']['redirect'] == 1) ? 1 : 2;
    } else {
        switch ($mode) {
            case 3:
                $show_exif = false;
            case 2:
                $popup = false;
                break;
            default:
                $popup = true;
        }
        $r_type = 1;
    }
    $items = array();
    if (!empty($_SERVER['REQUEST_URI'])) {
        $k_backto = '&from=' . rawurlencode($_SERVER['REQUEST_URI']);
    } else {
        $k_backto = '';
    }
    while ($icdb->fetch()) {
        // �������ʂ�z��ɂ��A�����_�����O�p�̗v�f��t��
        // �z��ǂ����Ȃ�+���Z�q�ŗv�f��ǉ��ł���
        // �i�L�[�̏d������l���㏑���������Ƃ���array_merge()���g���j
        $img = $icdb->toArray();
        mb_convert_variables('SJIS-win', 'UTF-8', $img);
        // �����N�E�����͕ύX����邱�Ƃ������A�ꗗ�p�̃f�[�^�L���b�V���ɉe����^���Ȃ��悤�ɕʂɏ�������
        $status = array();
        $status['rank'] = $img['rank'];
        $status['rank_f'] = ($img['rank'] == -1) ? '���ځ[��' : $img['rank'];
        $status['memo'] = $img['memo'];
        unset($img['rank'], $img['memo']);

        // �\���p�ϐ���ݒ�
        if ($enable_cache) {
            $add = $cache->call('buildImgCell', $img);
            if ($mode == 1) {
                $chk = EditForm::imgChecker($img); // ��r�I�y���̂ŃL���b�V�����Ȃ�
                $add += $chk;
            } elseif ($mode == 2) {
                $mng = $cache->call('EditForm::imgManager', $img, $status);
                $add += $mng;
            }
        } else {
            $add = buildImgCell($img);
            if ($mode == 1) {
                $chk = EditForm::imgChecker($img);
                $add += $chk;
            } elseif ($mode == 2) {
                $mng = EditForm::imgManager($img, $status);
                $add += $mng;
            }
        }
        // �I���W�i���摜�����݂��Ȃ����R�[�h�������ō폜
        if ($ini['Viewer']['delete_src_not_exists'] && !file_exists($add['src'])) {
            $add['thumb_k'] = $add['thumb'] = 'img/ic_removed.png';
            $add['t_width'] = $add['t_height'] = 32;
            $to_blacklist = false;
            $removed_files = array_merge($removed_files, manageDB_remove(array($img['id'], $to_blacklist)));
            $flexy->setData('toBlackList', $to_blacklist);
        } else {
            if (!file_exists($add['thumb'])) {
                // �����_�����O���Ɏ�����htmlspecialchars()�����̂�&amp;�ɂ��Ȃ�
                $add['thumb'] = 'ic2.php?r=' . $r_type . '&t=1';
                if (file_exists($add['src'])) {
                    $add['thumb'] .= '&id=' . $img['id'];
                } else {
                    $add['thumb'] .= '&uri=' . rawurlencode($img['uri']);
                }
            }
            if ($_conf['ktai']) {
                $add['thumb_k'] = 'ic2.php?r=0&t=2';
                if (file_exists($add['src'])) {
                    $add['thumb_k'] .= '&id=' . $img['id'];
                } else {
                    $add['thumb_k'] .= '&uri=' . rawurlencode($img['uri']);
                }
                $add['thumb_k'] .= $k_backto;
            }
        }
        $item = array_merge($img, $add, $status);

        // Exif�����擾
        if ($show_exif && file_exists($add['src']) && $img['mime'] == 'image/jpeg') {
            $item['exif'] = $enable_cache ? $cache->call('ic2_read_exif', $add['src']) : ic2_read_exif($add['src']);
        } else {
            $item['exif'] = null;
        }

        // Lightbox Plus �p�p�����[�^��ݒ�
        if ($lightbox) {
            $item['lightbox_attr'] = ' rel="lightbox[iv2]" class="ineffectable"';
            $item['lightbox_attr'] .= ' title="' . htmlspecialchars($item['memo'], ENT_QUOTES) . '"';
        } else {
            $item['lightbox_attr'] = '';
        }

        $items[] = $item;
    }

    $i = count($items); // == $found
    // �e�[�u���̗]���𖄂߂邽�߂�null��}��
    if (!$_conf['ktai'] && $i > $cols && ($j = $i % $cols) > 0) {
        for ($k = 0; $k < $cols - $j; $k++) {
            $items[] = null;
            $i++;
        }
    }
    // ���̎��_�� $i == $cols * ���R��

    $flexy->setData('items', $items);
    $flexy->setData('popup', $popup);
    $flexy->setData('matrix', new MatrixManager($cols, $rows, $i));
}

$flexy->setData('removedFiles', $removed_files);

// }}}
// {{{ output

// ���[�h�ʂ̍ŏI����
if ($_conf['ktai']) {
    $title = str_replace('ImageCache2', 'IC2', $ini['Viewer']['title']);
    $list_template = 'iv2i.tpl.html';
} else {
    switch ($mode) {
        case 2:
            $title = $ini['Manager']['title'];
            $list_template = 'iv2m.tpl.html';
            break;
        case 1:
            $title = $ini['Viewer']['title'];
            $list_template = 'iv2a.tpl.html';
            break;
        default:
            $title = $ini['Viewer']['title'];
            $list_template = 'iv2.tpl.html';
    }
}

// �t�H�[�����ŏI�������A�e���v���[�g�p�I�u�W�F�N�g�ɕϊ�
$r = &new HTML_QuickForm_Renderer_ObjectFlexy($flexy);
//$r->setLabelTemplate('_label.tpl.html');
//$r->setHtmlTemplate('_html.tpl.html');
$qf->updateAttributes(array('method' => 'get')); // ���N�G�X�g��POST�ł��󂯓���邽�߁A�����ŕύX
/*if ($_conf['input_type_search']) {
    $input_type_search_attributes = array(
        'type' => 'search',
        'autosave' => 'rep2.expack.search.imgcache',
        'results' => '10',
        'placeholder' => '',
    );
    $qfe['key']->updateAttributes($input_type_search_attributes);
}*/
$qf->accept($r);
$qfObj = &$r->toObject();

// �ϐ���Assign
$flexy->setData('title', $title);
$flexy->setData('mode', $mode);
$flexy->setData('js', $qf->getValidationScript());
$flexy->setData('page', $page);
$flexy->setData('move', $qfObj);
$flexy->setData('lightbox', $lightbox);

// �y�[�W��\��
P2Util::header_nocache();
$flexy->compile($list_template);
if ($list_template == 'iv2i.tpl.html') {
    $mobile = &Net_UserAgent_Mobile::singleton();
    $elements = $flexy->getElements();
    if ($mobile->isDoCoMo()) {
        $elements['page']->setAttributes('istyle="4"');
    } elseif ($mobile->isEZweb()) {
        $elements['page']->setAttributes('format="*N"');
    } elseif ($mobile->isVodafone()) {
        $elements['page']->setAttributes('mode="numeric"');
    }
    $view = false;
    $flexy->outputObject($view, $elements);
} else {
    $flexy->output();
}

// }}}

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
