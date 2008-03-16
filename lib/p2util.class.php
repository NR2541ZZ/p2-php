<?php

require_once P2_LIBRARY_DIR . '/dataphp.class.php';
require_once P2_LIBRARY_DIR . '/filectl.class.php';

/**
 * p2 - p2�p�̃��[�e�B���e�B�N���X
 * �C���X�^���X����炸�ɃX�^�e�B�b�N���\�b�h�ŗ��p����
 *
 * @created  2004/07/15
 */
class P2Util
{
    /**
     * �t�@�C�����_�E�����[�h�ۑ�����
     *
     * @access  public
     * @return  object Response|false
     */
    function &fileDownload($url, $localfile, $disp_error = true, $use_tmp_file = false)
    {
        global $_conf;

        $me = __CLASS__ . '::' . __FUNCTION__ . '()';

        if (strlen($localfile) == 0) {
            trigger_error("$me, localfile is null", E_USER_WARNING);
            return false;
        }

        $perm = (isset($_conf['dl_perm'])) ? $_conf['dl_perm'] : 0606;

        if (file_exists($localfile)) {
            $modified = gmdate("D, d M Y H:i:s", filemtime($localfile)) . " GMT";
        } else {
            $modified = false;
        }

        // DL
        include_once P2_LIBRARY_DIR . '/wap.class.php';
        $wap_ua =& new UserAgent();
        $wap_ua->setTimeout($_conf['fsockopen_time_limit']);
        $wap_req =& new Request();
        $wap_req->setUrl($url);
        $wap_req->setModified($modified);
        if ($_conf['proxy_use']) {
            $wap_req->setProxy($_conf['proxy_host'], $_conf['proxy_port']);
        }
        $wap_res = $wap_ua->request($wap_req);

        if ($wap_res->is_error() && $disp_error) {
            $url_t = P2Util::throughIme($wap_req->url);
            P2Util::pushInfoHtml("<div>Error: {$wap_res->code} {$wap_res->message}<br>");
            P2Util::pushInfoHtml("p2 info: <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$wap_req->url}</a> �ɐڑ��ł��܂���ł����B</div>");
        }

        // �X�V����Ă�����
        if ($wap_res->is_success() && $wap_res->code != "304") {
            if ($use_tmp_file) {
                if (!is_dir($_conf['tmp_dir'])) {
                    if (!FileCtl::mkdirR($_conf['tmp_dir'])) {
                        die("Error: $me, cannot mkdir.");
                        return false;
                    }
                }
                if (FileCtl::filePutRename($localfile, $wap_res->content) === false) {
                    trigger_error("$me, FileCtl::filePutRename() return false. " . $localfile, E_USER_WARNING);
                    die("Error:  $me, cannot write file.");
                    return false;
                }
            } else {
                if (file_put_contents($localfile, $wap_res->content, LOCK_EX) === false) {
                    die("Error:  $me, cannot write file.");
                    return false;
                }
            }
            chmod($localfile, $perm);
        }

        return $wap_res;
    }

    /**
     * �f�B���N�g���ɏ������݌������Ȃ���Β��ӂ�\���Z�b�g����
     *
     * @access  public
     * @return  void
     */
    function checkDirWritable($aDir)
    {
        global $_conf;

        // �}���`���[�U���[�h���́A��񃁃b�Z�[�W��}�����Ă���B

        if (!is_dir($aDir)) {
            /*
            P2Util::pushInfoHtml('<p class="infomsg">');
            P2Util::pushInfoHtml('����: �f�[�^�ۑ��p�f�B���N�g��������܂���B<br>');
            P2Util::pushInfoHtml($aDir."<br>");
            */
            if (is_dir(dirname(realpath($aDir))) && is_writable(dirname(realpath($aDir)))) {
                //P2Util::pushInfoHtml("�f�B���N�g���̎����쐬�����݂܂�...<br>");
                if (mkdir($aDir, $_conf['data_dir_perm'])) {
                    //P2Util::pushInfoHtml("�f�B���N�g���̎����쐬���������܂����B");
                    chmod($aDir, $_conf['data_dir_perm']);
                } else {
                    //P2Util::pushInfoHtml("�f�B���N�g���������쐬�ł��܂���ł����B<br>�蓮�Ńf�B���N�g�����쐬���A�p�[�~�b�V������ݒ肵�ĉ������B");
                }
            } else {
                    //P2Util::pushInfoHtml("�f�B���N�g�����쐬���A�p�[�~�b�V������ݒ肵�ĉ������B");
            }
            //P2Util::pushInfoHtml('</p>');

        } elseif (!is_writable($aDir)) {
            P2Util::pushInfoHtml('<p class="infomsg">����: �f�[�^�ۑ��p�f�B���N�g���ɏ������݌���������܂���B<br>');
            //P2Util::pushInfoHtml($aDir.'<br>');
            P2Util::pushInfoHtml('�f�B���N�g���̃p�[�~�b�V�������������ĉ������B</p>');
        }
    }

    /**
     * �_�E�����[�hURL����L���b�V���t�@�C���p�X��Ԃ�
     *
     * @access  public
     * @return  string|false
     */
    function cacheFileForDL($url)
    {
        global $_conf;

        if (!$parsed = parse_url($url)) {
            return false;
        }

        $save_uri = $parsed['host'];
        $save_uri .= $parsed['port'] ? ':'.$parsed['port'] : '';
        $save_uri .= $parsed['path'] ? $parsed['path'] : '';
        $save_uri .= $parsed['query'] ? '?'.$parsed['query'] : '';

        $cachefile = $_conf['cache_dir'] . "/" . $save_uri;

        FileCtl::mkdir_for($cachefile);

        return $cachefile;
    }

    /**
     * host��bbs�������Ԃ�
     *
     * @access  public
     * @return  string|null
     */
    function getItaName($host, $bbs)
    {
        global $_conf, $ita_names;

        $id = $host . '/' . $bbs;

        if (isset($ita_names[$id])) {
            return $ita_names[$id];
        }

        $idx_host_dir = P2Util::idxDirOfHost($host);
        $p2_setting_txt = $idx_host_dir."/".$bbs."/p2_setting.txt";

        if (file_exists($p2_setting_txt)) {

            $p2_setting_cont = file_get_contents($p2_setting_txt);
            if ($p2_setting_cont) {
                $p2_setting = unserialize($p2_setting_cont);
                if (isset($p2_setting['itaj'])) {
                    $ita_names[$id] = $p2_setting['itaj'];
                    return $ita_names[$id];
                }
            }
        }

        // ��Long�̎擾
        if (!isset($p2_setting['itaj'])) {
            require_once P2_LIBRARY_DIR . '/BbsMap.class.php';
            $itaj = BbsMap::getBbsName($host, $bbs);
            if ($itaj != $bbs) {
                $ita_names[$id] = $p2_setting['itaj'] = $itaj;

                FileCtl::make_datafile($p2_setting_txt, $_conf['p2_perm']);
                $p2_setting_cont = serialize($p2_setting);
                if (FileCtl::filePutRename($p2_setting_txt, $p2_setting_cont) === false) {
                    die("Error: {$p2_setting_txt} ���X�V�ł��܂���ł���");
                }
                return $ita_names[$id];
            }
        }

        return null;
    }

    /**
     * host����dat�̕ۑ��f�B���N�g����Ԃ�
     *
     * @access  public
     * @return  string
     */
    function datDirOfHost($host)
    {
        global $_conf;

        // 2channel or bbspink
        if (P2Util::isHost2chs($host)) {
            $dat_host_dir = $_conf['dat_dir'] . "/2channel";
        // machibbs.com
        } elseif (P2Util::isHostMachiBbs($host)) {
            $dat_host_dir = $_conf['dat_dir'] . "/machibbs.com";
        } elseif (preg_match('/[^.0-9A-Za-z.\\-_]/', $host) && !P2Util::isHostJbbsShitaraba($host)) {
            $dat_host_dir = $_conf['dat_dir']."/".rawurlencode($host);
            $old_dat_host_dir = $_conf['dat_dir'] . "/" . $host;
            if (is_dir($old_dat_host_dir)) {
                rename($old_dat_host_dir, $dat_host_dir);
                clearstatcache();
            }
        } else {
            $dat_host_dir = $_conf['dat_dir']."/".$host;
        }
        return $dat_host_dir;
    }

    /**
     * host����idx�̕ۑ��f�B���N�g����Ԃ�
     *
     * @access  public
     * @return  string
     */
    function idxDirOfHost($host)
    {
        global $_conf;

        // 2channel or bbspink
        if (P2Util::isHost2chs($host)) {
            $idx_host_dir = $_conf['idx_dir'] . "/2channel";
        // machibbs.com
        } elseif (P2Util::isHostMachiBbs($host)){
            $idx_host_dir = $_conf['idx_dir'] . "/machibbs.com";
        } elseif (preg_match('/[^.0-9A-Za-z.\\-_]/', $host) && !P2Util::isHostJbbsShitaraba($host)) {
            $idx_host_dir = $_conf['idx_dir']."/".rawurlencode($host);
            $old_idx_host_dir = $_conf['idx_dir'] . "/" . $host;
            if (is_dir($old_idx_host_dir)) {
                rename($old_idx_host_dir, $idx_host_dir);
                clearstatcache();
            }
        } else {
            $idx_host_dir = $_conf['idx_dir']."/".$host;
        }
        return $idx_host_dir;
    }

    /**
     * failed_post_file �̃p�X���擾����
     *
     * @access  public
     * @return  string
     */
    function getFailedPostFilePath($host, $bbs, $key = false)
    {
        // ���X
        if ($key) {
            $filename = $key . '.failed.data.php';
        // �X������
        } else {
            $filename = 'failed.data.php';
        }
        return $failed_post_file = P2Util::idxDirOfHost($host) . '/' . $bbs . '/' . $filename;
    }

    /**
     * ���X�g�̃i�r�͈͂��擾����
     *
     * @access  public
     * @return  array
     */
    function getListNaviRange($disp_from, $disp_range, $disp_all_num)
    {
        $disp_end = 0;
        $disp_navi = array();

        if (!$disp_all_num) {
            $disp_navi['from'] = 0;
            $disp_navi['end'] = 0;
            $disp_navi['all_once'] = true;
            $disp_navi['mae_from'] = 1;
            $disp_navi['tugi_from'] = 1;
            return $disp_navi;
        }

        $disp_navi['from'] = $disp_from;

        $disp_range = $disp_range-1;

        // from���z����
        if ($disp_navi['from'] > $disp_all_num) {
            $disp_navi['from'] = $disp_all_num - $disp_range;
            $disp_navi['from'] = max(1, $disp_navi['from']);
            $disp_navi['end'] = $disp_all_num;

        // from �z���Ȃ�
        } else {
            // end �z����
            if ($disp_navi['from'] + $disp_range > $disp_all_num) {
                $disp_navi['end'] = $disp_all_num;
                if ($disp_navi['from'] == 1) {
                    $disp_navi['all_once'] = true;
                }
            // end �z���Ȃ�
            } else {
                $disp_navi['end'] = $disp_from + $disp_range;
            }
        }

        $disp_navi['mae_from'] = $disp_from - 1 - $disp_range;
        $disp_navi['mae_from'] = max(1, $disp_navi['mae_from']);
        $disp_navi['tugi_from'] = $disp_navi['end'] +1;


        if ($disp_navi['from'] == $disp_navi['end']) {
            $range_on_st = $disp_navi['from'];
        } else {
            $range_on_st = "{$disp_navi['from']}-{$disp_navi['end']}";
        }
        $disp_navi['range_st'] = "{$range_on_st}/{$disp_all_num} ";

        return $disp_navi;
    }

    /**
     * key.idx �� data ���L�^����
     *
     * @access  public
     * @param   array   $data   �v�f�̏��ԂɈӖ�����B
     */
    function recKeyIdx($keyidx, $data)
    {
        global $_conf;

        // ��{�͔z��Ŏ󂯎��
        if (is_array($data)) {
            $cont = implode('<>', $data);
        // ���݊��p��string����t
        } else {
            $cont = rtrim($data);
        }

        $cont = $cont . "\n";

        FileCtl::make_datafile($keyidx, $_conf['key_perm']);
        if (file_put_contents($keyidx, $cont, LOCK_EX) === false) {
            trigger_error("file_put_contents(" . $keyidx . ")", E_USER_WARNING);
            die("Error: cannot write file. recKeyIdx()");
            return false;
        }

        return true;
    }

    /**
     * �z�X�g����N�b�L�[�t�@�C���p�X��Ԃ�
     *
     * @access  public
     * @return  string
     */
    function cachePathForCookie($host)
    {
        global $_conf;

        if (preg_match('/[^.0-9A-Za-z.\\-_]/', $host) && !P2Util::isHostJbbsShitaraba($host)) {
            $cookie_host_dir = $_conf['cookie_dir'] . "/" . rawurlencode($host);
            $old_cookie_host_dir = $_conf['cookie_dir'] . "/" . $host;
            if (is_dir($old_cookie_host_dir)) {
                rename($old_cookie_host_dir, $cookie_host_dir);
                clearstatcache();
            }
        } else {
            $cookie_host_dir = $_conf['cookie_dir'] . "/" . $host;
        }
        $cachefile = $cookie_host_dir . "/" . $_conf['cookie_file_name'];

        FileCtl::mkdir_for($cachefile);

        return $cachefile;
    }

    /**
     * ���p�Q�[�g��ʂ����߂�URL�ϊ����s��
     *
     * @access  public
     * @return  string
     */
    function throughIme($url)
    {
        global $_conf;
        static $manual_exts = null;

        if (is_null($manual_exts)) {
            if ($_conf['ime_manual_ext']) {
                $manual_exts = explode(',', trim($_conf['ime_manual_ext']));
            } else {
                $manual_exts = array();
            }
        }

        $url_en = rawurlencode(str_replace('&amp;', '&', $url));

        $gate = $_conf['through_ime'];
        if ($manual_exts &&
            false !== ($ppos = strrpos($url, '.')) &&
            in_array(substr($url, $ppos + 1), $manual_exts) &&
            ($gate == 'p2' || $gate == 'ex')
        ) {
            $gate .= 'm';
        }

        // p2ime�́Aenc, m, url �̈����������Œ肳��Ă���̂Œ���
        switch ($gate) {
        case '2ch':
            $url_r = preg_replace('|^(\w+)://(.+)$|', '$1://ime.nu/$2', $url);
            break;
        case 'p2':
        case 'p2pm':
                $url_r = $_conf['p2ime_url'].'?enc=1&amp;url='.$url_en;
                break;
        case 'p2m':
            $url_r = $_conf['p2ime_url'].'?enc=1&amp;m=1&amp;url='.$url_en;
            break;
        case 'ex':
        case 'expm':
            $url_r = $_conf['expack.ime_url'].'?u='.$url_en.'&amp;d=1';
            break;
        case 'exq':
            $url_r = $_conf['expack.ime_url'].'?u='.$url_en.'&amp;d=0';
            break;
        case 'exm':
            $url_r = $_conf['expack.ime_url'].'?u='.$url_en.'&amp;d=-1';
            break;
        default:
            $url_r = $url;
        }

        return $url_r;
    }

    /**
     * host �� 2ch or bbspink �Ȃ� true ��Ԃ�
     *
     * @access  public
     * @return  boolean
     */
    function isHost2chs($host)
    {
        if (preg_match("/\.(2ch\.net|bbspink\.com)/", $host)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * host �� be.2ch.net �Ȃ� true ��Ԃ�
     * 2006/07/27 ����͂����Â����\�b�h�B
     * 2ch�̔ړ]�ɉ����āAbbs���܂߂Ĕ��肵�Ȃ��Ă͂Ȃ�Ȃ��Ȃ����̂ŁAisBbsBe2chNet()�𗘗p����B
     *
     * @access  public
     * @return  boolean
     */
    function isHostBe2chNet($host)
    {
        if (preg_match("/^be\.2ch\.net/", $host)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * bbs�i�j �� be.2ch �Ȃ� true ��Ԃ�
     *
     * @since   2006/07/27
     * @access  public
     * @return  boolean
     */
    function isBbsBe2chNet($host, $bbs)
    {
        if (preg_match("/^be\.2ch\.net/", $host)) {
            return true;
        }
        $be_bbs = array('be', 'nandemo', 'argue');
        if (P2Util::isHost2chs($host) && in_array($bbs, $be_bbs)) {
            return true;
        }
        return false;
    }

    /**
     * host �� bbspink �Ȃ� true ��Ԃ�
     *
     * @access  public
     * @return  boolean
     */
    function isHostBbsPink($host)
    {
        if (preg_match("/\.bbspink\.com/", $host)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * host �� machibbs �Ȃ� true ��Ԃ�
     *
     * @access  public
     * @return  boolean
     */
    function isHostMachiBbs($host)
    {
        if (preg_match("/\.(machibbs\.com|machi\.to)/", $host)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * host �� machibbs.net �܂��r�˂��� �Ȃ� true ��Ԃ�
     *
     * @access  public
     * @return  boolean
     */
    function isHostMachiBbsNet($host)
    {
        if (preg_match("/\.(machibbs\.net)/", $host)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * host �� livedoor �����^���f���� : ������� �Ȃ� true ��Ԃ�
     *
     * @access  public
     * @return  boolean
     */
    function isHostJbbsShitaraba($in_host)
    {
        if ($in_host == 'rentalbbs.livedoor.com') {
            return true;
        } elseif (preg_match('/jbbs\.(shitaraba\.com|livedoor\.(com|jp))/', $in_host)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * livedoor �����^���f���� : ������΂̃z�X�g���ύX�ɑΉ����ĕύX����
     *
     * @access  public
     * @param   string    $in_str    �z�X�g���ł�URL�ł��Ȃ�ł��ǂ�
     * @return  string
     */
    function adjustHostJbbs($in_str)
    {
        return preg_replace('/jbbs\.(shitaraba\.com|livedoor\.com)/', 'jbbs.livedoor.jp', $in_str, 1);
        //return preg_replace('/jbbs\.(shitaraba\.com|livedoor\.(com|jp))/', 'rentalbbs.livedoor.com', $in_str, 1);
    }

    /**
     * http header no cache ���o�͂���
     *
     * @access  public
     * @return  void
     */
    function header_nocache()
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // ���t���ߋ�
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // ��ɏC������Ă���
        header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache"); // HTTP/1.0
    }

    /**
     * http header Content-Type ���o�͂���
     *
     * @access  public
     * @param   string    $mimetype �C�ӂ�MIME�^�C�v�ƕ����Z�b�g���̒ǉ����
     * @return  void
     */
    function header_content_type($mimetype = null)
    {
        if ($mimetype) {
            header('Content-Type: ' . $mimetype);
        } else {
            header('Content-Type: text/html; charset=Shift_JIS');
        }
    }

    /**
     * �f�[�^PHP�`���iTAB�j�̏������ݗ�����dat�`���iTAB�j�ɕϊ�����
     *
     * �ŏ��́Adat�`���i<>�j�������̂��A�f�[�^PHP�`���iTAB�j�ɂȂ�A�����Ă܂� v1.6.0 ��dat�`���i<>�j�ɖ߂���
     *
     * @access  public
     */
    function transResHistLogPhpToDat()
    {
        global $_conf;

        // �������ݗ������L�^���Ȃ��ݒ�̏ꍇ�͉������Ȃ�
        if ($_conf['res_write_rec'] == 0) {
            return true;
        }

        // p2_res_hist.dat.php ���ǂݍ��݉\�ł�������
        if (is_readable($_conf['p2_res_hist_dat_php'])) {
            if ($cont = DataPhp::getDataPhpCont($_conf['p2_res_hist_dat_php'])) {
                // �^�u��؂肩��<>��؂�ɕύX����
                $cont = str_replace("\t", "<>", $cont);

                // p2_res_hist.dat ������΁A���O��ς��ăo�b�N�A�b�v�B�i�����v��Ȃ��j
                if (file_exists($_conf['p2_res_hist_dat'])) {
                    $bak_file = $_conf['p2_res_hist_dat'] . '.bak';
                    if (strstr(PHP_OS, 'WIN') and file_exists($bak_file)) {
                        unlink($bak_file);
                    }
                    rename($_conf['p2_res_hist_dat'], $bak_file);
                }

                // �ۑ�
                FileCtl::make_datafile($_conf['p2_res_hist_dat'], $_conf['res_write_perm']);
                if (file_put_contents($_conf['p2_res_hist_dat'], $cont, LOCK_EX) === false) {
                    trigger_error("file_put_contents(" . $_conf['p2_res_hist_dat'] . ")", E_USER_WARNING);
                }

                // p2_res_hist.dat.php �𖼑O��ς��ăo�b�N�A�b�v�B�i�����v��Ȃ��j
                $bak_file = $_conf['p2_res_hist_dat_php'] . '.bak';
                if (strstr(PHP_OS, 'WIN') and file_exists($bak_file)) {
                    unlink($bak_file);
                }
                rename($_conf['p2_res_hist_dat_php'], $bak_file);
            }
        }
        return true;
    }

    /**
     * dat�`���i<>�j�̏������ݗ������f�[�^PHP�`���iTAB�j�ɕϊ�����
     *
     * @access  public
     * @return  boolean
     */
    function transResHistLogDatToPhp()
    {
        global $_conf;

        // �������ݗ������L�^���Ȃ��ݒ�̏ꍇ�͉������Ȃ�
        if ($_conf['res_write_rec'] == 0) {
            return true;
        }

        // p2_res_hist.dat.php ���Ȃ��āAp2_res_hist.dat ���ǂݍ��݉\�ł�������
        if ((!file_exists($_conf['p2_res_hist_dat_php'])) and is_readable($_conf['p2_res_hist_dat'])) {
            // �ǂݍ����
            if ($cont = file_get_contents($_conf['p2_res_hist_dat'])) {
                // <>��؂肩��^�u��؂�ɕύX����
                // �܂��^�u��S�ĊO����
                $cont = str_replace("\t", "", $cont);
                // <>���^�u�ɕϊ�����
                $cont = str_replace("<>", "\t", $cont);

                // �f�[�^PHP�`���ŕۑ�
                if (!DataPhp::writeDataPhp($_conf['p2_res_hist_dat_php'], $cont, $_conf['res_write_perm'])) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * �O��̃A�N�Z�X�����擾
     *
     * @access  public
     * @return  array
     */
    function getLastAccessLog($logfile)
    {
        if (!$lines = DataPhp::fileDataPhp($logfile)) {
            return false;
        }
        if (!isset($lines[1])) {
            return false;
        }
        $line = rtrim($lines[1]);
        $lar = explode("\t", $line);

        $alog['user']   = $lar[6];
        $alog['date']   = $lar[0];
        $alog['ip']     = $lar[1];
        $alog['host']   = $lar[2];
        $alog['ua']     = $lar[3];
        $alog['referer'] = $lar[4];

        return $alog;
    }


    /**
     * �A�N�Z�X�������O�ɋL�^����
     *
     * @access  public
     * @return  boolean
     */
    function recAccessLog($logfile, $maxline = 100, $format = 'dataphp')
    {
        global $_conf, $_login;

        // ���O�t�@�C���̒��g���擾����
        $lines = array();
        if (file_exists($logfile)) {
            if ($format == 'dataphp') {
                $lines = DataPhp::fileDataPhp($logfile);
            } else {
                $lines = file($logfile);
            }
        }

        if ($lines) {
            // �����s����
            while (sizeof($lines) > $maxline -1) {
                array_pop($lines);
            }
        } else {
            $lines = array();
        }
        $lines = array_map('rtrim', $lines);

        // �ϐ��ݒ�
        $date = date('Y/m/d (D) G:i:s');

        // HOST���擾
        if (!$remoto_host = $_SERVER['REMOTE_HOST']) {
            $remoto_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        }
        if ($remoto_host == $_SERVER['REMOTE_ADDR']) {
            $remoto_host = '';
        }

        $user = (isset($_login->user_u)) ? $_login->user_u : "";

        // �V�������O�s��ݒ�
        $newdata = $date."<>".$_SERVER['REMOTE_ADDR']."<>".$remoto_host."<>".$_SERVER['HTTP_USER_AGENT']."<>".$_SERVER['HTTP_REFERER']."<>".""."<>".$user;
        //$newdata = htmlspecialchars($newdata, ENT_QUOTES);

        // �܂��^�u��S�ĊO����
        $newdata = str_replace("\t", "", $newdata);
        // <>���^�u�ɕϊ�����
        $newdata = str_replace("<>", "\t", $newdata);

        // �V�����f�[�^����ԏ�ɒǉ�
        @array_unshift($lines, $newdata);

        $cont = implode("\n", $lines) . "\n";

        FileCtl::make_datafile($logfile, $_conf['p2_perm']);

        // �������ݏ���
        if ($format == 'dataphp') {
            if (!DataPhp::writeDataPhp($logfile, $cont, $_conf['p2_perm'])) {
                return false;
            }
        } else {
            if (file_put_contents($logfile, $cont, LOCK_EX) === false) {
                trigger_error("file_put_contents(" . $logfile . ")", E_USER_WARNING);
                return false;
            }
        }

        return true;
    }

    /**
     * 2ch�����O�C����ID��PASS�Ǝ������O�C���ݒ��ۑ�����
     *
     * @access  public
     * @return  boolean
     */
    function saveIdPw2ch($login2chID, $login2chPW, $autoLogin2ch = '')
    {
        global $_conf;

        include_once P2_LIBRARY_DIR . '/md5_crypt.inc.php';

        $md5_crypt_key = P2Util::getAngoKey();
        $crypted_login2chPW = md5_encrypt($login2chPW, $md5_crypt_key, 32);
        $idpw2ch_cont = <<<EOP
<?php
\$rec_login2chID = '{$login2chID}';
\$rec_login2chPW = '{$crypted_login2chPW}';
\$rec_autoLogin2ch = '{$autoLogin2ch}';
?>
EOP;
        FileCtl::make_datafile($_conf['idpw2ch_php'], $_conf['pass_perm']);
        if (!$fp = fopen($_conf['idpw2ch_php'], 'wb')) {
            die("p2 Error: {$_conf['idpw2ch_php']} ���X�V�ł��܂���ł���");
            return false;
        }
        @flock($fp, LOCK_EX);
        fputs($fp, $idpw2ch_cont);
        @flock($fp, LOCK_UN);
        fclose($fp);

        return true;
    }

    /**
     * 2ch�����O�C���̕ۑ��ς�ID��PASS�Ǝ������O�C���ݒ��ǂݍ���
     *
     * @access  public
     * @return  array
     */
    function readIdPw2ch()
    {
        global $_conf;

        include_once P2_LIBRARY_DIR . '/md5_crypt.inc.php';

        if (!file_exists($_conf['idpw2ch_php'])) {
            return false;
        }

        $rec_login2chID = null;
        $login2chPW = null;
        $rec_autoLogin2ch = null;

        include $_conf['idpw2ch_php'];

        // �p�X�𕡍���
        if (!is_null($rec_login2chPW)) {
            $md5_crypt_key = P2Util::getAngoKey();
            $login2chPW = md5_decrypt($rec_login2chPW, $md5_crypt_key, 32);
        }

        return array($rec_login2chID, $login2chPW, $rec_autoLogin2ch);
    }

    /**
     * getAngoKey
     *
     * @access  public
     * @return  string
     */
    function getAngoKey()
    {
        global $_login;

        return $_login->user . $_SERVER['SERVER_NAME'] . $_SERVER['SERVER_SOFTWARE'];
    }

    /**
     * getCsrfId
     *
     * @access  public
     * @return  string
     */
    function getCsrfId()
    {
        global $_login;

        return md5($_login->user . $_login->pass_x . $_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * 403 Fobbiden��HTML�o�͂���
     *
     * @access  public
     * @return  void
     */
    function print403($msg = '', $die = true)
    {
        header('HTTP/1.0 403 Forbidden');
        // IE�f�t�H���g�̃��b�Z�[�W��\�������Ȃ����߂̃p�f�B���O
        $pad = str_repeat(' ', 512);
        echo <<<ERR
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <title>403 Forbidden</title>
</head>
<body>
    <h1>403 Forbidden</h1>
    <p>{$msg}</p>{$pad}
</body>
</html>
ERR;

        $die and die('');
    }

    /**
     * Web�y�[�W���擾����
     *
     * 200 OK
     * 206 Partial Content
     * 304 Not Modified �� ���s����
     *
     * @static
     * @access  public
     * @return  string|false  ����������y�[�W���e��Ԃ��B���s������false��Ԃ��B
     */
    function getWebPage($url, &$error_msg, $timeout = 15)
    {
        include_once "HTTP/Request.php";

        $params = array("timeout" => $timeout);

        if (!empty($_conf['proxy_use'])) {
            $params['proxy_host'] = $_conf['proxy_host'];
            $params['proxy_port'] = $_conf['proxy_port'];
        }

        $req =& new HTTP_Request($url, $params);
        //$req->addHeader("X-PHP-Version", phpversion());

        $response = $req->sendRequest();

        if (PEAR::isError($response)) {
            $error_msg = $response->getMessage();
        } else {
            $code = $req->getResponseCode();
            if ($code == 200 || $code == 206) { // || $code == 304) {
                return $req->getResponseBody();
            } else {
                //var_dump($req->getResponseHeader());
                $error_msg = $code;
            }
        }

        return false;
    }

    /**
     * ���݂�URL���擾����iGET�N�G���[�͂Ȃ��j
     *
     * @static
     * @access  public
     * @return  string
     * @see  http://ns1.php.gr.jp/pipermail/php-users/2003-June/016472.html
     */
    function getMyUrl()
    {
        $s = empty($_SERVER['HTTPS']) ? '' : 's';
        $url = "http{$s}://" . $_SERVER['HTTP_HOST'] . $port . $_SERVER['SCRIPT_NAME'];
        // ��������
        //$port = ($_SERVER['SERVER_PORT'] == '80') ? '' : ':' . $_SERVER['SERVER_PORT'];
        //$url = "http{$s}://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['SCRIPT_NAME'];

        return $url;
    }

    /**
     * �V���v����HTML��\������
     * �i�P�Ƀe�L�X�g�����𑗂��au�Ȃǂ́A�\�����Ă���Ȃ��j
     *
     * @static
     * @access  public
     * @return  void
     */
    function printSimpleHtml($body)
    {
        echo "<html><body>{$body}</body></html>";
    }

    /**
     * �u���E�U��Safari�n�Ȃ�true��Ԃ�
     *
     * @static
     * @access  public
     * @return  boolean
     */
    function isBrowserSafariGroup()
    {
        return (boolean)preg_match('/Safari|AppleWebKit|Konqueror/', $_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * �u���E�U��NetFront�n�Ȃ�true��Ԃ�
     *
     * @static
     * @access  public
     * @return  boolean
     */
    function isNetFront()
    {
        if (preg_match('/(NetFront|AVEFront\/|AVE-Front\/)/', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * URL���E�B�L�y�f�B�A���{��ł̋L���Ȃ�true��Ԃ�
     *
     * @static
     * @access  public
     * @return  boolean
     */
    function isUrlWikipediaJa($url)
    {
        return (substr($url, 0, 29) == 'http://ja.wikipedia.org/wiki/');
    }

    /**
     * �t�@�C�����w�肵�āA�V���A���C�Y���ꂽ�z��f�[�^���}�[�W�X�V����i�����̃f�[�^�ɏ㏑���}�[�W����j
     *
     * @static
     * @param   array    $data
     * @param   string   $file
     * @return  boolean
     */
    function updateArraySrdFile($data, $file)
    {
        // �����̃f�[�^���}�[�W�擾
        if (file_exists($file)) {
            if ($cont = file_get_contents($file)) {
                $array = unserialize($cont);
                if (is_array($array)) {
                    $data = array_merge($array, $data);
                }
            }
        }

        // �}�[�W�X�V�Ȃ̂ŏ㏑���f�[�^������ۂ̎��͉������Ȃ�
        if (empty($data) || !is_array($data)) {
            return false;
        }

        if (file_put_contents($file, serialize($data), LOCK_EX) === false) {
            trigger_error("file_put_contents(" . $file . ")", E_USER_WARNING);
            return false;
        }
        return true;
    }

    /**
     * HTML�^�O <a href="$url">$html</a> �𐶐�����
     *
     * @static
     * @access  public
     * @param   string  $url   �蓮�� htmlspecialchars() ���邱�ƁB
     *                         http_build_query() �𗘗p���鎞���l�����āA������ htmlspecialchars() �͂����Ă��Ȃ��B
     * @param   string  $html  �����N�������HTML�B�蓮�� htmlspecialchars() ���邱�ƁB
     * @param   array   $attr  �ǉ������B������ htmlspecialchars() ����������ikey���O�̂��߁j
     * @return  string
     */
    function tagA($url, $html = '', $attr = array())
    {
        $attr_html = '';
        if (is_array($attr)) {
            foreach ($attr as $k => $v) {
                $attr_html .= ' ' . htmlspecialchars($k) . '="' . htmlspecialchars($v) . '"';
            }
        }
        $html = (strlen($html) == 0) ? $url : $html;

        return '<a href="' . $url . "\"{$attr_html}>" . $html . '</a>';
    }

    /**
     * pushInfoMsgHtml
     * 2006/10/19 $_info_msg_ht �𒼐ڈ����̂͂�߂Ă��̃��\�b�h��ʂ�������
     *
     * @static
     * @access  public
     * @return  void
     */
    function pushInfoHtml($html)
    {
        global $_info_msg_ht;

        $_info_msg_ht .= $html;
    }

    /**
     * printInfoMsgHtml
     *
     * @static
     * @access  public
     * @return  void
     */
    function printInfoHtml()
    {
        global $_info_msg_ht;

        echo $_info_msg_ht;
        $_info_msg_ht = '';
    }

    /**
     * getInfoMsgHtml
     *
     * @static
     * @access  public
     * @return  string
     */
    function getInfoHtml()
    {
        global $_info_msg_ht;

        $info_msg_ht = $_info_msg_ht;
        $_info_msg_ht = '';

        return $info_msg_ht;
    }

    /**
     * �Z�b�V�����t�@�C���̃K�[�x�b�W�R���N�V����
     *
     * session.save_path�̃p�X�̐[����2���傫���ꍇ�A�K�[�x�b�W�R���N�V�����͍s���Ȃ�����
     * �����ŃK�[�x�b�W�R���N�V�������Ȃ��Ƃ����Ȃ��B
     *
     * @access  public
     * @return  void
     *
     * @link http://jp.php.net/manual/ja/ref.session.php#ini.session.save-path
     */
    function session_gc()
    {
        global $_conf;

        if (session_module_name() != 'files') {
            return;
        }

        $d = (int)ini_get('session.gc_divisor');
        $p = (int)ini_get('session.gc_probability');
        mt_srand();
        if (mt_rand(1, $d) <= $p) {
            $m = (int)ini_get('session.gc_maxlifetime');
            FileCtl::garbageCollection($_conf['session_dir'], $m);
        }
    }

    /**
     * ["&<>]�����̎Q�ƂɂȂ��Ă��邩�ǂ����s���ȕ�����ɑ΂���htmlspecialchars()��������
     */
    function re_htmlspecialchars($str)
    {
        // e�C���q��t�����Ƃ��A"�͎����ŃG�X�P�[�v�����悤��
        return preg_replace('/["<>]|&(?!#?\w+;)/e', 'htmlspecialchars("$0", ENT_QUOTES)', $str);
    }

    /**
     * XMLHttpRequest�̃��X�|���X��Safari�p�ɃG���R�[�h����
     *
     * @return string
     */
    function encodeResponseTextForSafari($response, $encoding = 'SJIS-win')
    {
        $response = mb_convert_encoding($response, 'UTF-8', $encoding);
        $response = mb_encode_numericentity($response, array(0x80, 0xFFFF, 0, 0xFFFF), 'UTF-8');
        return $response;
    }

    /**
     * �g���b�v�𐶐�����
     */
    function mkTrip($key, $length = 10)
    {
        $salt = substr($key . 'H.', 1, 2);
        $salt = preg_replace('/[^\.-z]/', '.', $salt);
        $salt = strtr($salt, ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');

        return substr(crypt($key, $salt), -$length);
    }

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
