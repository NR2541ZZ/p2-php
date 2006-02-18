<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=4 fdm=marker: */
/* mi: charset=Shift_JIS */

require_once P2_LIBRARY_DIR . '/p2util.class.php';
require_once P2_LIBRARY_DIR . '/filectl.class.php';

// {{{ class BbsMap

/**
 * BbsMap�N���X
 *
 * ��-�z�X�g�̑Ή��\���쐬���A����Ɋ�Â��ăz�X�g�̓������s��
 */
class BbsMap
{
    // {{{ getCurrentHost()

    /**
     * �ŐV�̃z�X�g���擾����
     *
     * @param   string  $host   �z�X�g��
     * @param   string  $bbs    ��
     * @param   bool    $autosync   �ړ]�����o�����Ƃ��Ɏ����œ������邩�ۂ�
     * @return  string  �ɑΉ�����ŐV�̃z�X�g
     * @access  public
     * @static
     */
    function getCurrentHost($host, $bbs, $autosync = true)
    {
        global $_info_msg_ht;
        static $synced = false;

        // �}�b�s���O�ǂݍ���
        $map = BbsMap::_getMapping();
        if (!$map) {
            return $host;
        }
        $type = BbsMap::_detectHostType($host);

        // �`�F�b�N
        if (isset($map[$type]) && isset($map[$type][$bbs])) {
            $new_host = $map[$type][$bbs]['host'];
            if ($host != $new_host && $autosync && !$synced) {
                // �ړ]�����o�����炨�C�ɔA���C�ɃX���A�ŋߓǂ񂾃X���������œ���
                $msg_fmt = '<p>rep2 info: �z�X�g�̈ړ]�����o���܂����B(%s/%s �� %s/%s)<br>';
                $msg_fmt .= '���C�ɔA���C�ɃX���A�ŋߓǂ񂾃X���������œ������܂��B</p>';
                $_info_msg_ht .= sprintf($msg_fmt, $host, $bbs, $new_host, $bbs);
                BbsMap::syncFav();
                $synced = true;
            }
            $host = $new_host;
        }

        return $host;
    }

    // }}}
    // {{{ getBbsName()

    /**
     * ��LONG���擾����
     *
     * @param   string  $host   �z�X�g��
     * @param   string  $bbs    ��
     * @return  string  ���j���[�ɋL�ڂ���Ă����
     * @access  public
     * @static
     */
    function getBbsName($host, $bbs)
    {
        // �}�b�s���O�ǂݍ���
        $map = BbsMap::_getMapping();
        if (!$map) {
            return $bbs;
        }
        $type = BbsMap::_detectHostType($host);

        // �`�F�b�N
        if (isset($map[$type]) && isset($map[$type][$bbs])) {
            $itaj = $map[$type][$bbs]['itaj'];
        } else {
            $itaj = $bbs;
        }

        return $itaj;
    }

    // }}}
    // {{{ syncBrd()

    /**
     * ���C�ɔȂǂ�brd�t�@�C���𓯊�����
     *
     * @param   string  $brd_path   brd�t�@�C���̃p�X
     * @return  void
     * @access  public
     * @static
     */
    function syncBrd($brd_path)
    {
        global $_conf, $_info_msg_ht;
        static $done = array();

        // {{{ �Ǎ�

        if (isset($done[$brd_path])) {
            return;
        }
        $lines = BbsMap::_readData($brd_path);
        if (!$lines) {
            return;
        }
        $map = BbsMap::_getMapping();
        if (!$map) {
            return;
        }
        $neolines = array();
        $updated = false;

        // }}}
        // {{{ ����

        foreach ($lines as $line) {
            $setitaj = false;
            $data = explode("\t", rtrim($line, "\n"));
            $hoge = $data[0]; // �\��?
            $host = $data[1];
            $bbs  = $data[2];
            $itaj = $data[3];
            $type = BbsMap::_detectHostType($host);

            if (isset($map[$type]) && isset($map[$type][$bbs])) {
                $newhost = $map[$type][$bbs]['host'];
                if ($itaj === '') {
                    $itaj = $map[$type][$bbs]['itaj'];
                    if ($itaj != $bbs) {
                        $setitaj = true;
                    } else {
                        $itaj = '';
                    }
                }
            } else {
                $newhost = $host;
            }

            if ($host != $newhost || $setitaj) {
                $neolines[] = "{$hoge}\t{$newhost}\t{$bbs}\t{$itaj}\n";
                $updated = true;
            } else {
                $neolines[] = $line;
            }
        }

        // }}}
        // {{{ ����

        if ($updated) {
            BbsMap::_writeData($brd_path, $neolines);
            $_info_msg_ht .= sprintf('<p>rep2 info: %s �𓯊����܂����B</p>', htmlspecialchars($brd_path, ENT_QUOTES));
        } else {
            $_info_msg_ht .= sprintf('<p>rep2 info: %s �͕ύX����܂���ł����B</p>', htmlspecialchars($brd_path, ENT_QUOTES));
        }
        $done[$brd_path] = true;

        // }}}
    }

    // }}}
    // {{{ syncIdx()

    /**
     * ���C�ɃX���Ȃǂ�idx�t�@�C���𓯊�����
     *
     * @param   string  $idx_path   idx�t�@�C���̃p�X
     * @return  void
     * @access  public
     * @static
     */
    function syncIdx($idx_path)
    {
        global $_conf, $_info_msg_ht;
        static $done = array();

        // {{{ �Ǎ�

        if (isset($done[$idx_path])) {
            return;
        }
        $lines = BbsMap::_readData($idx_path);
        if (!$lines) {
            return;
        }
        $map = BbsMap::_getMapping();
        if (!$map) {
            return;
        }
        $neolines = array();
        $updated = false;

        // }}}
        // {{{ ����

        foreach ($lines as $line) {
            $data = explode('<>', rtrim($line, "\n"));
            $host = $data[10];
            $bbs  = $data[11];
            $type = BbsMap::_detectHostType($host);

            if (isset($map[$type]) && isset($map[$type][$bbs])) {
                $newhost = $map[$type][$bbs]['host'];
            } else {
                $newhost = $host;
            }

            if ($host != $newhost) {
                $data[10] = $newhost;
                $neolines[] = implode('<>', $data) . "\n";
                $updated = true;
            } else {
                $neolines[] = $line;
            }
        }

        // }}}
        // {{{ ����

        if ($updated) {
            BbsMap::_writeData($idx_path, $neolines);
            $_info_msg_ht .= sprintf('<p>rep2 info: %s �𓯊����܂����B</p>', htmlspecialchars($idx_path, ENT_QUOTES));
        } else {
            $_info_msg_ht .= sprintf('<p>rep2 info: %s �͕ύX����܂���ł����B</p>', htmlspecialchars($idx_path, ENT_QUOTES));
        }
        $done[$idx_path] = true;

        // }}}
    }

    // }}}
    // {{{ syncFav()

    /**
     * ���C�ɔA���C�ɃX���A�ŋߓǂ񂾃X���𓯊�����
     *
     * @return  void
     * @access  public
     * @static
     */
    function syncFav()
    {
        global $_conf;
        BbsMap::syncBrd($_conf['favita_path']);
        BbsMap::syncIdx($_conf['favlist_file']);
        BbsMap::syncIdx($_conf['rct_file']);
    }

    // }}}
    // {{{ _getMapping()

    /**
     * 2ch�������j���[���p�[�X���A��-�z�X�g�̑Ή��\���쐬����
     *
     * @return  array   site/bbs/(host,itaj) �̑������A�z�z��
     *                  �_�E�����[�h�Ɏ��s�����Ƃ��� false
     * @access  private
     * @static
     */
    function _getMapping()
    {
        global $_conf, $_info_msg_ht;
        static $map = null;

        // {{{ �ݒ�

        $bbsmenu_url = 'http://menu.2ch.net/bbsmenu.html';
        $map_cache_path = $_conf['cache_dir'] . '/host_bbs_map.txt';
        $map_cache_lifetime = 600; // TTL�͏����Z�߂�
        $errfmt = '<p>rep2 error: BbsMap: %s - %s ���_�E�����[�h�ł��܂���ł����B</p>';

        // }}}
        // {{{ �L���b�V���m�F

        if (!is_null($map)) {
            return $map;
        } elseif (file_exists($map_cache_path)) {
            $mtime = filemtime($map_cache_path);
            $expires = $mtime + $map_cache_lifetime;
            if (time() < $expires) {
                $map_cahce = file_get_contents($map_cache_path);
                $map = unserialize($map_cahce);
                return $map;
            }
        }

        // }}}
        // {{{ ���j���[���_�E�����[�h

        $params = array();
        if (isset($mtime)) {
            $params['requestHeaders'] = array('If-Modified-Since' => gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
        }
        $req = &new HTTP_Request($bbsmenu_url, $params);
        $req->setMethod('GET');
        $err = $req->sendRequest(true);

        // �G���[������
        if (PEAR::isError($err)) {
            $_info_msg_ht .= sprintf($errfmt, htmlspecialchars($err->getMessage()), htmlspecialchars($bbsmenu_url, ENT_QUOTES));
            if (file_exists($map_cache_path)) {
                return unserialize(file_get_contents($map_cache_path));
            } else {
                return false;
            }
        }

        // ���X�|���X�R�[�h������
        $code = $req->getResponseCode();
        if ($code == 304) {
            $map_cahce = file_get_contents($map_cache_path);
            $map = unserialize($map_cahce);
            touch($map_cache_path);
            return $map;
        } elseif ($code != 200) {
            $_info_msg_ht .= sprintf($errfmt, htmlspecialchars(strval($code)), htmlspecialchars($bbsmenu_url, ENT_QUOTES));
            if (file_exists($map_cache_path)) {
                return unserialize(file_get_contents($map_cache_path));
            } else {
                return false;
            }
        }

        $res_body = $req->getResponseBody();

        // }}}
        // {{{ �p�[�X

        $regex = '!<A HREF=http://(\w+\.(?:2ch\.net|bbspink\.com|machi\.to|mathibbs\.com))/(\w+)/(?: TARGET=_blank)?>(.+?)</A>!';
        preg_match_all($regex, $res_body, $matches, PREG_SET_ORDER);

        $map = array();
        foreach ($matches as $match) {
            $host = $match[1];
            $bbs  = $match[2];
            $itaj = $match[3];
            $type = BbsMap::_detectHostType($host);
            if (!isset($map[$type])) {
                $map[$type] = array();
            }
            $map[$type][$bbs] = array('host' => $host, 'itaj' => $itaj);
        }

        // }}}
        // {{{ �L���b�V������

        $map_cache = serialize($map);
        if (FileCtl::file_write_contents($map_cache_path, $map_cache) === false) {
            $errmsg = sprintf('Error: cannot write file. (%s)', htmlspecialchars($map_cache_path, ENT_QUOTES));
            die($errmsg);
        }

        // }}}

        return $map;
    }

    // }}}
    // {{{ _readData()

    /**
     * �X�V�O�̃f�[�^��ǂݍ���
     *
     * @param   string  $path   �ǂݍ��ރt�@�C���̃p�X
     * @return  array   �t�@�C���̓��e�A�ǂݏo���Ɏ��s�����Ƃ��� false
     * @access  private
     * @static
     */
    function _readData($path)
    {
        if (!file_exists($path)) {
            return false;
        }

        $lines = file($path);
        if (!$lines) {
            return false;
        }

        return $lines;
    }

    // }}}
    // {{{ _writeData()

    /**
     * �X�V��̃f�[�^����������
     *
     * @param   string  $path   �������ރt�@�C���̃p�X
     * @param   array   $neolines   �������ރf�[�^�̔z��
     * @return  void
     * @access  private
     * @static
     */
    function _writeData($path, $neolines)
    {
        if (is_array($neolines) && count($neolines) > 0) {
            $cont = implode('', $neolines);
        /*} elseif (is_scalar($neolines)) {
            $cont = strval($neolines);*/
        } else {
            $cont = '';
        }
        if (FileCtl::file_write_contents($path, $cont) === false) {
            $errmsg = sprintf('Error: cannot write file. (%s)', htmlspecialchars($path, ENT_QUOTES));
            die($errmsg);
        }
    }

    // }}}
    // {{{ _detectHostType()

    /**
     * �z�X�g�̎�ނ𔻒肷��
     *
     * @param   string  $host   �z�X�g��
     * @return  string  �z�X�g�̎��
     * @access  private
     * @static
     */
    function _detectHostType($host)
    {
        if (P2Util::isHostBbsPink($host)) {
            $type = 'bbspink';
        } elseif (P2Util::isHost2chs($host)) {
            $type = '2channel';
        } elseif (P2Util::isHostMachiBbs($host)) {
            $type = 'machibbs';
        } elseif (P2Util::isHostJbbsShitaraba($host)) {
            $type = 'jbbs';
        } else {
            $type = $host;
        }
        return $type;
    }

    // }}}
}

// }}}
?>
