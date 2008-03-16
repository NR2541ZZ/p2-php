<?php
/*
    ImageCache2 - �����e�i���X
*/

// {{{ p2��{�ݒ�ǂݍ���&�F��

require_once 'conf/conf.inc.php';

$_login->authorize();

if (!$_conf['expack.ic2.enabled']) {
    exit('<html><body><p>ImageCache2�͖����ł��B<br>conf/conf_admin_ex.inc.php �̐ݒ��ς��Ă��������B</p></body></html>');
}

// }}}
// {{{ ������

// ���C�u�����ǂݍ���
require_once 'PEAR.php';
require_once 'DB.php';
require_once 'HTML/Template/Flexy.php';
require_once P2EX_LIBRARY_DIR . '/ic2/loadconfig.inc.php';

// �ݒ�ǂݍ���
$ini = ic2_loadconfig();

// �f�[�^�x�[�X�ɐڑ�
$db = &DB::connect($ini['General']['dsn']);
if (DB::isError($db)) {
    die('<html><body><p>'.$result->getMessage().'</p></body></html>');
}

// �e���v���[�g�G���W��������
$_flexy_options = array(
    'locale' => 'ja',
    'charset' => 'cp932',
    'compileDir' => $ini['General']['cachedir'] . '/' . $ini['General']['compiledir'],
    'templateDir' => P2EX_LIBRARY_DIR . '/ic2/templates',
    'numberFormat' => '', // ",0,'.',','" �Ɠ���
);

$flexy = &new HTML_Template_Flexy($_flexy_options);

// }}}
// {{{ �f�[�^�x�[�X����E�t�@�C���폜

if (isset($_POST['action'])) {
    switch ($_POST['action']) {

        case 'dropZero':
        case 'dropAborn':
            require_once P2EX_LIBRARY_DIR . '/ic2/managedb.inc.php';

            if ($_POST['action'] == 'dropZero') {
                $where = $db->quoteIdentifier('rank') . ' = 0';
                if (isset($_POST['dropZeroLimit'])) {
                    switch ($_POST['dropZeroSelectTime']) {
                        case '24hours': $expires = 86400; break;
                        case 'aday':    $expires = 86400; break;
                        case 'aweek':   $expires = 86400 * 7; break;
                        case 'amonth':  $expires = 86400 * 31; break;
                        case 'ayear':   $expires = 86400 * 365; break;
                        default: $expires = null;
                    }
                    if ($expires !== null) {
                        $operator = ($_POST['dropZeroSelectType'] == 'within') ? '>' : '<';
                        $where .= sprintf(' AND %s %s %d',
                            $db->quoteIdentifier('time'),
                            $operator,
                            time() - $expires);
                    }
                }
                $to_blacklist = !empty($_POST['dropZeroToBlackList']);
            } else {
                $where = $db->quoteIdentifier('rank') . ' < 0';
                $to_blacklist = true;
            }

            $sql = sprintf('SELECT %s FROM %s WHERE %s;',
                $db->quoteIdentifier('id'),
                $db->quoteIdentifier($ini['General']['table']),
                $where);
            $result = $db->getAll($sql, null, DB_FETCHMODE_ORDERED | DB_FETCHMODE_FLIPPED);
            if (DB::isError($result)) {
                P2Util::pushInfoHtml($result->getMessage());
                break;
            }
            $target = $result[0];
            $removed_files = manageDB_remove($target, $to_blacklist);
            $flexy->setData('toBlackList', $to_blacklist);
            break;

        case 'clearThumb':
            $thumb_dir2 = $ini['General']['cachedir'] . '/' . $ini['Thumb2']['name'];
            $thumb_dir3 = $ini['General']['cachedir'] . '/' . $ini['Thumb3']['name'];
            $result_files2 = FileCtl::garbageCollection($thumb_dir2, -1, '', '', true);
            $result_files3 = FileCtl::garbageCollection($thumb_dir3, -1, '', '', true);
            $removed_files = array_merge($result_files2['successed'], $result_files3['successed']);
            $failed_files = array_merge($result_files2['failed'], $result_files3['failed']);
            if (!empty($failed_files)) {
                P2Util::pushInfoHtml('<p>�ȉ��̃t�@�C�����폜�ł��܂���ł����B</p>');
                P2Util::pushInfoHtml('<ul><li>');
                P2Util::pushInfoHtml(implode('</li><li>', array_map('htmlspecialchars', $failed_files)));
                P2Util::pushInfoHtml('</li></ul>');
            }
            break;

        case 'clearCache':
            $result = $db->query('DELETE FROM ' . $db->quoteIdentifier($ini['Cache']['table']));
            if (DB::isError($result)) {
                P2Util::pushInfoHtml($result->getMessage());
            } else {
                P2Util::pushInfoHtml("<p>�e�[�u�� {$ini['Cache']['table']} ����ɂ��܂����B</p>");
            }
            $result_files = FileCtl::garbageCollection($flexy->options['compileDir'], -1, '', '', true);
            $removed_files = $result_files['successed'];
            if (!empty($result_files['failed'])) {
                P2Util::pushInfoHtml('<p>�ȉ��̃t�@�C�����폜�ł��܂���ł����B</p>');
                P2Util::pushInfoHtml('<ul><li>');
                P2Util::pushInfoHtml(implode('</li><li>', array_map('htmlspecialchars', $result_files['failed'])));
                P2Util::pushInfoHtml('</li></ul>');
            }
            break;

        case 'clearErrorLog':
            $result = $db->query('DELETE FROM ' . $db->quoteIdentifier($ini['General']['error_table']));
            if (DB::isError($result)) {
                P2Util::pushInfoHtml($result->getMessage());
            } else {
                P2Util::pushInfoHtml('<p>�G���[���O���������܂����B</p>');
            }
            break;

        case 'clearBlackList':
            $result = $db->query('DELETE FROM ' . $db->quoteIdentifier($ini['General']['blacklist_table']));
            if (DB::isError($result)) {
                P2Util::pushInfoHtml($result->getMessage());
            } else {
                P2Util::pushInfoHtml('<p>�u���b�N���X�g���������܂����B</p>');
            }
            break;

        case 'vacuumDB':
            if ($db->dsn['phptype'] == 'sqlite') {
                $db_file = $db->dsn['database'];
                $size_b = filesize($db_file);
                $result = $db->query('VACUUM');
                if (DB::isError($result)) {
                    P2Util::pushInfoHtml($result->getMessage());
                } else {
                    clearstatcache();
                    $size_a = filesize($db_file);
                    P2Util::pushInfoHtml(sprintf('<p>VACUUM���s�A�t�@�C���T�C�Y: %s �� %s (-%s)',
                        number_format($size_b),
                        number_format($size_a),
                        number_format($size_b - $size_a)));
                }
            }
            break;

        default:
            P2Util::pushInfoHtml('<p>�s���ȃN�G��: ' . htmlspecialchars($_POST['action'], ENT_QUOTES) . '</p>');

    }
    if (isset($removed_files)) {
        $flexy->setData('removedFiles', $removed_files);
    }
}

// }}}
// {{{ �o��

$flexy->setData('skin', $skin_en);
$flexy->setData('php_self', $_SERVER['SCRIPT_NAME']);
$flexy->setData('info_msg', P2Util::getInfoHtml());
if ($db->dsn['phptype'] == 'sqlite') {
    $flexy->setData('isSQLite', true);
}

P2Util::header_nocache();
$flexy->compile('ic2mng.tpl.html');
$flexy->output();

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
