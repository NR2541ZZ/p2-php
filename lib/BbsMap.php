<?php
require_once P2_LIB_DIR . '/FileCtl.php';

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
     * @param   string  $host       �z�X�g��
     * @param   string  $bbs        ��
     * @param   bool    $autosync   �ړ]�����o�����Ƃ��Ɏ����œ������邩�ۂ�
     * @return  string  �ɑΉ�����ŐV�̃z�X�g�B������Ȃ���Γ��͂����z�X�g�����̂܂ܕԂ�
     * @access  public
     * @static
     */
    function getCurrentHost($host, $bbs, $autosync = true)
    {
        static $synced = false;

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
                $msg_fmt = '<p>p2 info: �z�X�g�̈ړ]�����o���܂����B(%s/%s �� %s/%s)<br>';
                $msg_fmt .= '���C�ɔA���C�ɃX���A�ŋߓǂ񂾃X���������œ������܂��B</p>';
                $msg = sprintf($msg_fmt, $host, $bbs, $new_host, $bbs);
                P2Util::pushInfoHtml($msg);
                BbsMap::syncFav();
                $synced = true;
            }
            $host = $new_host;
        }

        return $host;
    }

    // }}}

    /**
     * 2ch�̔�����z�X�g�����擾����
     *
     * @static
     * @access  public
     * @param   string  $bbs    ��
     * @return  string|false
     */
    function get2chHostByBbs($bbs)
    {
        if (!$map = BbsMap::_getMapping()) {
            return false;
        }
        if (isset($map['2channel'])) {
            foreach ($map['2channel'] as $mapped_bbs => $v) {
                if ($mapped_bbs == $bbs) {
                    return $v['host'];
                }
            }
        }
        return false;
    }
    
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
     * @static
     * @access  public
     * @param   string  $brd_path   brd�t�@�C���̃p�X
     * @param   boolean $noMsg      ���ʃ��b�Z�[�W��push��}������Ȃ�true
     * @return  void
     */
    function syncBrd($brd_path, $noMsg = false)
    {
        global $_conf;
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

        $name = basename($brd_path);
        $name_hs = htmlspecialchars($name, ENT_QUOTES);
        if ($updated) {
            BbsMap::_writeData($brd_path, $neolines);
            !$noMsg and P2Util::pushInfoHtml(sprintf('<p>p2 info: %s �𓯊����܂����B</p>', $name_hs));
        } else {
            !$noMsg and P2Util::pushInfoHtml(sprintf('<p>p2 info: %s �͕ύX����܂���ł����B</p>', $name_hs));
        }
        $done[$brd_path] = true;

        // }}}
    }

    // }}}
    // {{{ syncIdx()

    /**
     * ���C�ɃX���Ȃǂ�idx�t�@�C���𓯊�����
     *
     * @static
     * @access  public
     * @param   string  $idx_path   idx�t�@�C���̃p�X
     * @param   boolean $noMsg      ���ʃ��b�Z�[�W��push��}������Ȃ�true
     * @return  void
     */
    function syncIdx($idx_path, $noMsg = false)
    {
        global $_conf;
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

        $name = basename($idx_path);
        $name_hs = htmlspecialchars($name, ENT_QUOTES);
        if ($updated) {
            BbsMap::_writeData($idx_path, $neolines);
            !$noMsg and P2Util::pushInfoHtml(sprintf('<p>p2 info: %s �𓯊����܂����B</p>', $name_hs));
        } else {
            !$noMsg and P2Util::pushInfoHtml(sprintf('<p>p2 info: %s �͕ύX����܂���ł����B</p>', $name_hs));
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
        
        $noMsg = $_conf['ktai'] ? true : false;
        
        BbsMap::syncBrd($_conf['favita_path'], $noMsg);
        BbsMap::syncIdx($_conf['favlist_file'], $noMsg);
        BbsMap::syncIdx($_conf['rct_file'], $noMsg);
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
        global $_conf;
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
        } else {
            FileCtl::mkdirFor($map_cache_path);
        }
        touch($map_cache_path);
        clearstatcache();

        // }}}
        // {{{ ���j���[���_�E�����[�h

        $params = array();
        $params['timeout'] = $_conf['fsockopen_time_limit'];
        //$params['readTimeout'] = array($_conf['fsockopen_time_limit'], 0);
        if (isset($mtime)) {
            $params['requestHeaders'] = array('If-Modified-Since' => gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
        }
        if ($_conf['proxy_use']) {
            $params['proxy_host'] = $_conf['proxy_host'];
            $params['proxy_port'] = $_conf['proxy_port'];
        }
        $req = new HTTP_Request($bbsmenu_url, $params);
        $req->setMethod('GET');
        $err = $req->sendRequest(true);

        // �G���[������
        if (PEAR::isError($err)) {
            $errmsg = sprintf($errfmt, htmlspecialchars($err->getMessage()), htmlspecialchars($bbsmenu_url, ENT_QUOTES));
            P2Util::pushInfoHtml($errmsg);
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
            return $map;
        } elseif ($code != 200) {
            $errmsg = sprintf($errfmt, htmlspecialchars(strval($code)), htmlspecialchars($bbsmenu_url, ENT_QUOTES));
            P2Util::pushInfoHtml($errmsg);
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
            
            !isset($map[$type]) and $map[$type] = array();
            $map[$type][$bbs] = array('host' => $host, 'itaj' => $itaj);
        }

        // }}}
        
        // �L���b�V������
        $map_cache = serialize($map);
        if (false === FileCtl::filePutRename($map_cache_path, $map_cache)) {
            $errmsg = sprintf('p2 error: cannot write file. (%s)', htmlspecialchars($map_cache_path, ENT_QUOTES));
            P2Util::pushInfoHtml($errmsg);
            
            if (file_exists($map_cache_path)) {
                return unserialize(file_get_contents($map_cache_path));
            } else {
                return false;
            }
        }

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
        if (false === FileCtl::filePutRename($path, $cont)) {
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
