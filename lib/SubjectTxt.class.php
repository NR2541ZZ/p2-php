<?php
/*
define(P2_SUBJECT_TXT_STORAGE, 'eashm');    // �veAccelerator

[�d�l] shm���ƒ����L���b�V�����Ȃ�
[�d�l] shm����modified�����Ȃ�

shm�ɂ��Ă��p�t�H�[�}���X�͂قƂ�Ǖς��Ȃ��i�悤���j
*/

/**
 * SubjectTxt�N���X
 */
class SubjectTxt
{
    var $host;
    var $bbs;
    var $subject_url;
    var $subject_file;
    var $subject_lines;
    var $storage; // file, eashm(eAccelerator shm) // 2006/02/27 aki eashm �͔񐄏�

    /**
     * �R���X�g���N�^
     */
    function SubjectTxt($host, $bbs)
    {
        $this->host = $host;
        $this->bbs =  $bbs;
        if (defined('P2_SUBJECT_TXT_STORAGE') && P2_SUBJECT_TXT_STORAGE == 'eashm') {
            $this->storage = P2_SUBJECT_TXT_STORAGE;
        } else {
            $this->storage = 'file';
        }

        $this->subject_file = P2Util::datDirOfHost($this->host) . '/' . $this->bbs . '/subject.txt';

        $this->subject_url = "http://" . $this->host . '/' . $this->bbs . "/subject.txt";

        // ������΂�livedoor�ړ]�ɑΉ��B�Ǎ����livedoor�Ƃ���B
        $this->subject_url = P2Util::adjustHostJbbs($this->subject_url);

        // subject.txt���_�E�����[�h���Z�b�g����
        $this->dlAndSetSubject();
    }

    /**
     * subject.txt���_�E�����[�h���Z�b�g����
     *
     * @access  private
     * @return  boolean  �Z�b�g�ł���� true
     */
    function dlAndSetSubject()
    {
        if ($this->storage == 'eashm') {
            $cont = eaccelerator_get("$this->host/$this->bbs");
        } else {
            $cont = '';
        }
        if (!$cont || !empty($_POST['newthread'])) {
            $cont = $this->downloadSubject();
        }
        if ($this->setSubjectLines($cont)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * subject.txt���_�E�����[�h����
     *
     * @access  private?
     * @return  string|false  subject.txt �̒��g
     */
    function downloadSubject()
    {
        global $_conf;

        $perm = (isset($_conf['dl_perm'])) ? $_conf['dl_perm'] : 0606;

        if ($this->storage == 'file') {
            FileCtl::mkdir_for($this->subject_file); // �f�B���N�g����������΍��

            if (file_exists($this->subject_file)) {
                if (!empty($_REQUEST['norefresh']) || (empty($_REQUEST['refresh']) && isset($_REQUEST['word']))) {
                    return;    // �X�V���Ȃ��ꍇ�́A���̏�Ŕ����Ă��܂�
                } elseif (empty($_POST['newthread']) and $this->isSubjectTxtFresh()) {
                    return;    // �V�K�X�����Ď��łȂ��A�X�V���V�����ꍇ��������
                }
                $modified = gmdate("D, d M Y H:i:s", filemtime($this->subject_file))." GMT";
            } else {
                $modified = false;
            }
        }

        // DL
        include_once "HTTP/Request.php";

        $params = array();
        $params['timeout'] = $_conf['fsockopen_time_limit'];
        if ($_conf['proxy_use']) {
            $params['proxy_host'] = $_conf['proxy_host'];
            $params['proxy_port'] = $_conf['proxy_port'];
        }
        $req =& new HTTP_Request($this->subject_url, $params);
        $modified && $req->addHeader("If-Modified-Since", $modified);
        $req->addHeader('User-Agent', 'Monazilla/1.00 (' . $_conf['p2name'] . '/' . $_conf['p2version'] . ')');

        $response = $req->sendRequest();

        if (PEAR::isError($response)) {
            $error_msg = $response->getMessage();
        } else {
            $code = $req->getResponseCode();
            if ($code == 302) {
                // �z�X�g�̈ړ]��ǐ�
                include_once P2_LIBRARY_DIR . '/BbsMap.class.php';
                $new_host = BbsMap::getCurrentHost($this->host, $this->bbs);
                if ($new_host != $this->host) {
                    $aNewSubjectTxt = &new SubjectTxt($new_host, $this->bbs);
                    $body = $aNewSubjectTxt->downloadSubject();
                    return $body;
                }
            }
            if (!($code == 200 || $code == 206 || $code == 304)) {
                //var_dump($req->getResponseHeader());
                $error_msg = $code;
            }
        }

        if (isset($error_msg) && strlen($error_msg) > 0) {
            $url_t = P2Util::throughIme($this->subject_url);
            P2Util::pushInfoHtml("<div>Error: {$error_msg}<br>");
            P2Util::pushInfoHtml("p2 info: <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$this->subject_url}</a> �ɐڑ��ł��܂���ł����B</div>");
            $body = '';
        } else {
            $body = $req->getResponseBody();
        }

        // DL�������� ���� �X�V����Ă�����
        if ($body && $code != "304") {

            // ������� or be.2ch.net �Ȃ�EUC��SJIS�ɕϊ�
            if (P2Util::isHostJbbsShitaraba($this->host) || P2Util::isHostBe2chNet($this->host)) {
                $body = mb_convert_encoding($body, 'SJIS-win', 'eucJP-win');
            }

            // eashm�ɕۑ�����ꍇ
            if ($this->storage == 'eashm') {
                $eacc_key = "$this->host/$this->bbs";
                eaccelerator_lock($eacc_key);
                //echo $body;
                eaccelerator_put($eacc_key, $body, $_conf['sb_dl_interval']);
                eaccelerator_unlock($eacc_key);

            // �t�@�C���ɕۑ�����ꍇ
            } else {
                if (FileCtl::filePutRename($this->subject_file, $body) === false) {
                    // �ۑ��Ɏ��s�͂������A�����̂��ǂݍ��߂�Ȃ炻���Ԃ��Ă���
                    if (is_readable) {
                        return file_get_contents($this->subject_file);
                    } else {
                        die("Error: cannot write file");
                        return false;
                    }
                }
                chmod($this->subject_file, $perm);
            }
        } else {
            // touch���邱�ƂōX�V�C���^�[�o���������̂ŁA���΂炭�ă`�F�b�N����Ȃ��Ȃ�
            // �i�ύX���Ȃ��̂ɏC�����Ԃ��X�V����̂́A�����C���i�܂Ȃ����A�����ł͓��ɖ��Ȃ����낤�j
            if ($this->storage == 'file') {
                touch($this->subject_file);
            }
        }

        return $body;
    }

    /**
     * subject.txt ���V�N�Ȃ� true ��Ԃ�
     *
     * @access  private
     * @return  boolean  �V�N�Ȃ� true�B�����łȂ���� false�B
     */
    function isSubjectTxtFresh()
    {
        global $_conf;

        // �L���b�V��������ꍇ
        if (file_exists($this->subject_file)) {
            // �L���b�V���̍X�V���w�莞�Ԉȓ��Ȃ�
            // clearstatcache();
            if (filemtime($this->subject_file) > time() - $_conf['sb_dl_interval']) {
                return true;
            }
        }

        return false;
    }

    /**
     * subject.txt ��ǂݍ���
     *
     * ��������΁A$this->subject_lines ���Z�b�g�����
     *
     * @access  private
     * @param   string   $cont    ����� eashm �p�ɓn���Ă���B
     * @return  boolean  ���s����
     */
    function setSubjectLines($cont = '')
    {
        if ($this->storage == 'eashm') {
            if (!$cont) {
                $cont = eaccelerator_get("$this->host/$this->bbs");
            }
            $this->subject_lines = explode("\n", $cont);

        } elseif ($this->storage == 'file') {
            if (extension_loaded('zlib') and strstr($this->host, '.2ch.net')) {
                $this->subject_lines = gzfile($this->subject_file);    // ����͂��̂����O�� 2005/6/5
            } else {
                $this->subject_lines = file($this->subject_file);
            }
        }

        // JBBS@������΂Ȃ�d���X���^�C���폜����
        if (P2Util::isHostJbbsShitaraba($this->host)) {
            $this->subject_lines = array_unique($this->subject_lines);
        }

        if ($this->subject_lines) {
            return true;
        } else {
            return false;
        }
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
