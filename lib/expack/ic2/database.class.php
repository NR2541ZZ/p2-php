<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=4 fdm=marker: */
/* mi: charset=Shift_JIS */

require_once 'DB.php';
require_once 'DB/DataObject.php';
require_once P2EX_LIBRARY_DIR . '/ic2/loadconfig.inc.php';

class IC2DB_Skel extends DB_DataObject
{
    // {{{ properties

    var $_db;
    var $_ini;

    // }}}
    // {{{ constcurtor

    function IC2DB_Skel()
    {
        $this->__construct();
    }

    function __construct()
    {
        static $set_to_utf8 = false;

        // �ݒ�̓ǂݍ���
        $ini = ic2_loadconfig();
        $this->_ini = $ini;
        if (!$ini['General']['dsn']) {
            die("<p><b>Error:</b> DSN���ݒ肳��Ă��܂���B</p>");
        }

        // �g�����W���[���̓ǂݍ���
        list($dbextension, ) = explode(':', $ini['General']['dsn'], 2);
        if (!extension_loaded($dbextension)) {
            $extdir = ini_get('extension_dir');
            if (substr(PHP_OS, 0, 3) == 'WIN') {
                $dbmodulename = 'php_' . $dbextension . '.dll';
            } else {
                $dbmodulename = $dbextension . '.so';
            }
            $dbmodulepath = $extdir . DIRECTORY_SEPARATOR . $dbmodulename;
            if (!file_exists($dbmodulepath)) {
                die("<p><b>Error:</b> {$dbmodulename}��{$extdir}�ɂ���܂���B</p>");
            } elseif (!@dl($dbmodulename)) {
                die("<p><b>Error:</b> {$dbmodulename}�����[�h�ł��܂���ł����B</p>");
            }
        }

        // �f�[�^�x�[�X�֐ڑ�
        $this->_database_dsn = $ini['General']['dsn'];
        $this->_db = &$this->getDatabaseConnection();
        if (DB::isError($this->_db)) {
            die($this->_db->getMessage());
        }

        // �N���C�A���g�̕����Z�b�g�� UTF-8 ���w��
        if (!$set_to_utf8) {
            switch (strtolower($dbextension)) {
            case 'mysql':
            case 'mysqli':
                $version = &$this->_db->getRow("SHOW VARIABLES LIKE 'version'", array(), DB_FETCHMODE_ORDERED);
                if (!DB::isError($version) && version_compare($version[1], '4.1.0') != -1) {
                    $charset = &$this->_db->getRow("SHOW VARIABLES LIKE 'character_set_database'", array(), DB_FETCHMODE_ORDERED);
                    if (!DB::isError($charset) && $charset[1] == 'latin1') {
                        $errmsg = "<p><b>Warning:</b> �f�[�^�x�[�X�̕����Z�b�g�� latin1 �ɐݒ肳��Ă��܂��B</p>";
                        $errmsg .= "<p>mysqld �� default-character-set �� binary, ujis, utf8 ���łȂ��Ɠ��{��̕���������̂� ";
                        $errmsg .= "<a href=\"http://www.mysql.gr.jp/frame/modules/bwiki/?FAQ#content_1_40\">���{MySQL���[�U���FAQ</a>";
                        $errmsg .= " ���Q�l�� my.cnf �̐ݒ��ς��Ă��������B</p>";
                        die($errmsg);
                    }
                }
                $this->_db->query("SET NAMES utf8");
                break;
            case 'pgsql':
                $this->_db->query("SET CLIENT_ENCODING TO 'UTF8'");
                break;
            }
            $set_to_utf8 = true;
        }
    }

    // }}}
    // {{{ whereAddQuoted()

    // WHERE�������
    function whereAddQuoted($key, $cmp, $value, $logic = 'AND')
    {
        $types = $this->table();
        $col = $this->_db->quoteIdentifier($key);
        if ($types[$key] != DB_DATAOBJECT_INT) {
            $value = $this->_db->quoteSmart($value);
        }
        $cond = sprintf('%s %s %s', $col, $cmp, $value);
        return $this->whereAdd($cond, $logic);
    }

    // }}}
    // {{{ orderByArray()

    // ORDER BY�������
    function orderByArray($sort)
    {
        $order = array();
        foreach ($sort as $k => $d) {
            if (!is_string($k)) {
                if ($d && is_string($d)) {
                    $k = $d;
                    $d = 'ASC';
                } else {
                    continue;
                }
            }
            $k = $this->_db->quoteIdentifier($k);
            if (!$d || strtoupper($d) == 'DESC') {
                $order[] = $k . ' DESC';
            } else {
                $order[] = $k . ' ASC';
            }
        }
        if (!count($order)) {
            return FALSE;
        }
        return $this->orderBy(implode(', ', $order));
    }

    // }}}
}

?>
