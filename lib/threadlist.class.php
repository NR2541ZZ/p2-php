<?php
/**
 * p2 - ThreadList �N���X
 */
class ThreadList
{
    var $threads;   // �N���XThread�̃I�u�W�F�N�g���i�[����z��
    var $num;       // �i�[���ꂽThread�I�u�W�F�N�g�̐�
    var $host;      // ex)pc.2ch.net
    var $bbs;       // ex)mac
    var $itaj;      // �� ex)�V�Emac��
    var $itaj_hd;   // HTML�\���p�ɁA���� htmlspecialchars() ��������
    var $spmode;    // ���ʔȊO�̃X�y�V�������[�h
    var $ptitle;    // �y�[�W�^�C�g��

    /**
     * @constructor
     */
    function ThreadList()
    {
        $this->num = 0;
    }

    /**
     * setSpMode
     *
     * @access  public
     * @return  void
     */
    function setSpMode($name)
    {
        global $_conf;

        $valid_modename = true;

        switch ($name) {
            case 'recent':
                $this->ptitle = $_conf['ktai'] ? '�ŋߓǂ񂾽�' : '�ŋߓǂ񂾃X��';
                break;
            case 'res_hist':
                $this->ptitle = '�������ݗ���';
                break;
            case 'fav':
                $this->ptitle = $_conf['ktai'] ? '���C�ɽ�' : '���C�ɃX��';
                break;
            case 'taborn':
                $this->ptitle = $this->itaj . ($_conf['ktai'] ? ' (���ݒ�)' : ' (���ځ[��)');
                break;
            case 'soko':
                $this->ptitle = $this->itaj . ' (dat�q��)';
                break;
            case 'palace':
                $this->ptitle = $_conf['ktai'] ? '�ڂ̓a��' : '�X���̓a��';
                break;
            case 'cate':
                $this->ptitle = ($_conf['ktai'] ? '�ú��' : '�J�e�S��') . ': '
                              . ($this->itaj ? $this->itaj : '???');
                break;
            case 'favita':
                $this->ptitle = '���C�ɔ�';
                break;
            default:
                $valid_modename = false;
        }

        if ($valid_modename) {
            $this->spmode = $name;
        }
    }

    /**
     * �����I�ɔ��ihost, bbs, ���j���Z�b�g����
     *
     * @access  public
     * @return  void
     */
    function setIta($host, $bbs, $itaj = '')
    {
        $this->host = $host;
        $this->bbs = $bbs;
        $this->setItaj($itaj);
    }

    /**
     * �����Z�b�g����
     *
     * @access  public
     * @return  void
     */
    function setItaj($itaj)
    {
        $this->itaj = $itaj ? $itaj : $this->bbs;

        $this->itaj_hd = htmlspecialchars($this->itaj, ENT_QUOTES);
        $this->ptitle = $this->itaj;
    }

    /**
     * readList
     *
     * @access  public
     * @return  array
     */
    function readList()
    {
        global $_conf;

        $lines = array();

        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('readList()');

        switch ($this->spmode) {

            // {{{ ���[�J���̗����t�@�C�� �ǂݍ���

            case 'recent':
                if (file_exists($_conf['rct_file'])) {
                    $lines = file($_conf['rct_file']);
                }
                /*if (!$lines) {
                    P2Util::pushInfoHtml('<p>�����͋���ۂł�</p>');
                    return false;
                }*/
                break;

            // }}}
            // {{{ ���[�J���̏������ݗ����t�@�C�� �ǂݍ���

            case 'res_hist':
                $rh_idx = $_conf['pref_dir'] . '/p2_res_hist.idx';
                if (file_exists($rh_idx)) {
                    $lines = file($rh_idx);
                }
                /*if (!$lines) {
                    P2Util::pushInfoHtml('<p>�������ݗ����͋���ۂł�</p>');
                    return false;
                }*/
                break;

            // }}}
            // {{{ ���[�J���̂��C�Ƀt�@�C�� �ǂݍ���

            case 'fav':
                if (file_exists($_conf['favlist_file'])) {
                    $lines = file($_conf['favlist_file']);
                }
                /*if (!$lines) {
                    P2Util::pushInfoHtml('<p>���C�ɃX���͋���ۂł�</p>');
                    return false;
                }*/
                break;

            // }}}
            // {{{ �X���̓a���̏ꍇ  // p2_palace.idx �ǂݍ���

            case 'palace':
                $palace_idx = $_conf['pref_dir'] . '/p2_palace.idx';
                if (file_exists($palace_idx)) {
                    $lines = file($palace_idx);
                }
                /*if (!$lines) {
                    P2Util::pushInfoHtml('<p>�a���͂����ǂ��ł�</p>');
                    return false;
                }*/
                break;

            // }}}
            // {{{ �X���b�h���ځ[�񃊃X�g  // p2_threads_aborn.idx �ǂݍ���

            case 'taborn':
                $taborn_idx = P2Util::datDirOfHost($this->host). '/' . $this->bbs . '/p2_threads_aborn.idx';
                if (file_exists($taborn_idx)) {
                    $lines = file($taborn_idx);
                }
                /*if (!$lines) {
                    P2Util::pushInfoHtml('<p>�X���b�h���ځ[�񃊃X�g�͋���ۂł�</p>');
                    return false;
                }*/
                break;

            // }}}
            // {{{ dat�q�ɂ̏ꍇ

            case 'soko':
                $dat_host_dir = P2Util::datDirOfHost($this->host);
                $idx_host_dir = P2Util::idxDirOfHost($this->host);

                $dat_bbs_dir = $dat_host_dir. '/' . $this->bbs;
                $idx_bbs_dir = $idx_host_dir. '/' . $this->bbs;

                $dat_pattern = '/([0-9]+)\.dat$/';
                $idx_pattern = '/([0-9]+)\.idx$/';

                $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('dat');
                // dat���O�f�B���N�g���𑖍����ČǗ�dat��idx�t��
                if ($cdir = dir($dat_bbs_dir)) { // or die ('���O�f�B���N�g�����Ȃ���I');
                    // �f�B���N�g������
                    while ($entry = $cdir->read()) {
                        if (preg_match($dat_pattern, $entry, $matches)) {
                            $theidx = $idx_bbs_dir. '/' . $matches[1] . '.idx';
                            if (!file_exists($theidx)) {
                                if ($datlines = @file($dat_bbs_dir. '/' . $entry)) {
                                    $firstdatline = rtrim($datlines[0]);
                                    if (strstr($firstdatline, '<>')) {
                                        $datline_sepa = '<>';
                                    } else {
                                        $datline_sepa = ',';
                                    }
                                    $d = explode($datline_sepa, $firstdatline);
                                    $atitle = $d[4];
                                    $gotnum = sizeof($datlines);
                                    $readnum = $gotnum;
                                    $anewline = $readnum + 1;
                                    $data = array($atitle, $matches[1], '', $gotnum, '',
                                                $readnum, '', '', '', $anewline,
                                                '', '', '');
                                    P2Util::recKeyIdx($theidx, $data);
                                }
                            }
                            // array_push($lines, $idl[0]);
                        }
                    }
                    $cdir->close();
                }
                $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('dat');

                $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('idx');
                // {{{ idx���O�f�B���N�g���𑖍�����idx���𒊏o���ă��X�g��
                if ($cdir = dir($idx_bbs_dir)) { // or die ('���O�f�B���N�g�����Ȃ���I');
                    // �f�B���N�g������
                    while ($entry = $cdir->read()) {
                        if (preg_match($idx_pattern, $entry)) {
                            $idl = file($idx_bbs_dir . '/' . $entry);
                            array_push($lines, $idl[0]);
                        }
                    }
                    $cdir->close();
                }
                // }}}
                $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('idx');
                break;

            // }}}
            // {{{ ���C�ɔ̃T�u�W�F�N�g�ꗗ�ǂݍ���

            case 'favita':
                if (!file_exists($_conf['favita_path'])) {
                    break;
                }
                $favitas = file_get_contents($_conf['favita_path']);
                if (!preg_match_all('/^\t?(.+)\t(.+)\t(.+)$/m', $favitas, $fmatches, PREG_SET_ORDER)) {
                    break;
                }

                // ���C�ɔ̊e���j���[�ǂݍ���
                require_once P2_LIBRARY_DIR . '/SubjectTxt.class.php';
                $num_boards = 0;
                $num_threads = 0;
                foreach ($fmatches as $fm) {
                    $aSubjectTxt = &new SubjectTxt($fm[1], $fm[2]);
                    if (!is_array($aSubjectTxt->subject_lines)) {
                        continue;
                    }
                    if (!preg_match_all('/^([0-9]+)\.(dat|cgi)(,|<>)(.+) ?(\(|�i)([0-9]+)(\)|�j)/m',
                        implode("\n", $aSubjectTxt->subject_lines), $lmatches, PREG_SET_ORDER))
                    {
                        continue;
                    }
                    $lmatches = array_slice($lmatches, 0, $_conf['expack.mergedlist.max_threads_per_board']);
                    foreach ($lmatches as $lm) {
                        $lines[] = array(
                            'key'       => $lm[1],
                            'ttitle'    => trim($lm[4]),
                            'rescount'  => $lm[6],
                            'host'      => $fm[1],
                            'bbs'       => $fm[2],
                        );
                        if (++$num_threads >= $_conf['expack.mergedlist.max_threads']) {
                            break 2;
                        }
                    }
                    if (++$num_boards >= $_conf['expack.mergedlist.max_boards']) {
                        break;
                    }
                }
                break;

            // }}}
            // {{{ ����J�e�S���̃T�u�W�F�N�g�ꗗ�ǂݍ���

            case 'cate':
                if (!isset($_GET['cate_id'])) {
                    break;
                }

                // ���j���[�ǂݍ���
                require_once P2_LIBRARY_DIR . '/brdctl.class.php';
                $brd_menus = BrdCtl::read_brds();
                if (!$brd_menus) {
                    break;
                }

                // �J�e�S������
                $menuitas = null;
                $tgt_id = (int)$_GET['cate_id'];
                $cate_id = 1;
                foreach ($brd_menus as $a_brd_menu) {
                    foreach ($a_brd_menu->categories as $cate) {
                        if ($cate_id++ == $tgt_id) {
                            $menuitas = $cate->menuitas;
                            break 2;
                        }
                    }
                }
                if (!$menuitas) {
                    break;
                }

                // �J�e�S�����̊e���j���[�ǂݍ���
                require_once P2_LIBRARY_DIR . '/SubjectTxt.class.php';
                $num_boards = 0;
                $num_threads = 0;
                foreach ($menuitas as $mita) {
                    $aSubjectTxt = &new SubjectTxt($mita->host, $mita->bbs);
                    if (!is_array($aSubjectTxt->subject_lines)) {
                        continue;
                    }
                    if (!preg_match_all('/^([0-9]+)\.(dat|cgi)(,|<>)(.+) ?(\(|�i)([0-9]+)(\)|�j)/m',
                        implode("\n", $aSubjectTxt->subject_lines), $matches, PREG_SET_ORDER))
                    {
                        continue;
                    }
                    $matches = array_slice($matches, 0, $_conf['expack.mergedlist.max_threads_per_board']);
                    foreach ($matches as $m) {
                        $lines[] = array(
                            'key'       => $m[1],
                            'ttitle'    => trim($m[4]),
                            'rescount'  => $m[6],
                            'host'      => $mita->host,
                            'bbs'       => $mita->bbs,
                        );
                        if (++$num_threads >= $_conf['expack.mergedlist.max_threads']) {
                            break 2;
                        }
                    }
                    if (++$num_boards >= $_conf['expack.mergedlist.max_boards']) {
                        break;
                    }
                }
                break;

            // }}}
            // {{{ �I�����C����� subject.txt ��ǂݍ��ށispmode�łȂ��ꍇ�j

            default:
                // �O�̂��߁A�����ł�spmode�̃`�F�b�N�����Ă���
                if (!$this->spmode) {
                    require_once P2_LIBRARY_DIR . '/SubjectTxt.class.php';
                    $aSubjectTxt =& new SubjectTxt($this->host, $this->bbs);
                    $lines =& $aSubjectTxt->subject_lines;
                }

            // }}}
        }

        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('readList()');

        return $lines;
    }

    /**
     * addThread
     *
     * @access  public
     * @return  integer
     */
    function addThread(&$aThread)
    {
        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('addThread()');

        $this->threads[] =& $aThread;
        $this->num++;

        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('addThread()');

        return $this->num;
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
