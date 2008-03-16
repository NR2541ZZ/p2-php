<?php
/**
 * �X���b�h��\������ �N���X
 */
class ShowThread{

    var $thread; // �X���b�h�I�u�W�F�N�g

    var $str_to_link_regex; // �����N���ׂ�������̐��K�\��

    var $url_handlers; // URL����������֐��E���\�b�h���Ȃǂ��i�[����z��i�f�t�H���g�j
    var $user_url_handlers; // URL����������֐��E���\�b�h���Ȃǂ��i�[����z��i���[�U��`�A�f�t�H���g�̂��̂��D��j

    var $ngaborn_frequent; // �p�oID�����ځ[�񂷂�

    var $aborn_nums; // ���ځ[�񃌃X�ԍ����i�[����z��
    var $ng_nums; // NG���X�ԍ����i�[����z��

    var $activeMona; // �A�N�e�B�u���i�[�E�I�u�W�F�N�g
    var $am_enabled = false; // �A�N�e�B�u���i�[���L�����ۂ�

    /**
     * �R���X�g���N�^
     */
    function ShowThread(&$aThread)
    {
        global $_conf;

        // �X���b�h�I�u�W�F�N�g��o�^
        $this->thread = &$aThread;

        $this->str_to_link_regex = '{'
            . '(?P<link>(<[Aa] .+?>)(.*?)(</[Aa]>))' // �����N�iPCRE�̓�����A�K�����̃p�^�[�����ŏ��Ɏ��s����j
            . '|'
            . '(?:'
            .   '(?P<quote>' // ���p
            .       '((?:&gt;|��){1,2} ?)' // ���p��
            .       '('
            .           '(?:[1-9]\\d{0,3})' // 1�ڂ̔ԍ�
            .           '(?:'
            .               '(?: ?(?:[,=]|�A) ?[1-9]\\d{0,3})+' // �A��
            .               '|'
            .               '-(?:[1-9]\\d{0,3})?' // �͈�
            .           ')?'
            .       ')'
            .       '(?=\\D|$)'
            .   ')' // ���p�����܂�
            . '|'
            .   '(?P<url>(ftp|h?t?tps?)://([0-9A-Za-z][\\w!#%&+*,\\-./:;=?@\\[\\]^~]+))' // URL
            .   '([^\s<>]*)' // URL�̒���A�^�Oor�z���C�g�X�y�[�X�������܂ł̕�����
            . '|'
            .   '(?P<id>ID: ?([0-9A-Za-z/.+]{8,11})(?=[^0-9A-Za-z/.+]|$))' // ID�i8,10�� +PC/�g�ю��ʃt���O�j
            . ')'
            . '}';

        $this->url_handlers = array();
        $this->user_url_handlers = array();

        $this->ngaborn_frequent = 0;
        if ($_conf['ngaborn_frequent']) {
            if ($_conf['ngaborn_frequent_dayres'] == 0) {
                $this->ngaborn_frequent = $_conf['ngaborn_frequent'];
            } elseif ($this->thread->setDayRes() && $this->thread->dayres < $_conf['ngaborn_frequent_dayres']) {
                $this->ngaborn_frequent = $_conf['ngaborn_frequent'];
            }
        }

        $this->aborn_nums = array();
        $this->ng_nums = array();
    }

    /**
     * Dat��HTML�ϊ����ĕ\������
     */
    function datToHtml()
    {
        return '';
    }

    /**
     * Dat��HTML�ϊ��������̂��擾����
     */
    function getDatToHtml()
    {
        ob_start();
        $this->datToHtml();
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    /**
     * BE�v���t�@�C�������N�ϊ�
     */
    function replaceBeId($date_id, $i)
    {
        global $_conf;

        $beid_replace = "<a href=\"http://be.2ch.net/test/p.php?i=\$1&u=d:http://{$this->thread->host}/test/read.cgi/{$this->thread->bbs}/{$this->thread->key}/{$i}\"{$_conf['ext_win_target_at']}>Lv.\$2</a>";

        //<BE:23457986:1>
        $be_match = '|<BE:(\d+):(\d+)>|i';
        if (preg_match($be_match, $date_id)) {
            $date_id = preg_replace($be_match, $beid_replace, $date_id);

        } else {

            $beid_replace = "<a href=\"http://be.2ch.net/test/p.php?i=\$1&u=d:http://{$this->thread->host}/test/read.cgi/{$this->thread->bbs}/{$this->thread->key}/{$i}\"{$_conf['ext_win_target_at']}>?\$2</a>";
            $date_id = preg_replace('|BE: ?(\d+)-(#*)|i', $beid_replace, $date_id);
        }

        return $date_id;
    }


    /**
     * NG���ځ[��`�F�b�N
     */
    function ngAbornCheck($code, $resfield, $ic = FALSE)
    {
        global $ngaborns;

        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('ngAbornCheck()');

        if (isset($ngaborns[$code]['data']) && is_array($ngaborns[$code]['data'])) {
            foreach ($ngaborns[$code]['data'] as $k => $v) {
                // �`�F�b�N
                if (isset($v['bbs']) && in_array($this->thread->bbs, $v['bbs']) == FALSE) {
                    continue;
                }

                // �^�C�g���`�F�b�N
                if (isset($v['title']) && stristr($this->thread->ttitle_hc, $v['title']) === FALSE) {
                    continue;
                }

                // ���[�h�`�F�b�N
                // ���K�\��
                if (!empty($v['regex'])) {
                    $re_method = $v['regex'];
                    /*if ($re_method($v['word'], $resfield, $matches)) {
                        $this->ngAbornUpdate($code, $k);
                        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        return htmlspecialchars($matches[0], ENT_QUOTES);
                    }*/
                     if ($re_method($v['word'], $resfield)) {
                        $this->ngAbornUpdate($code, $k);
                        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        return $v['cond'];
                    }
               // �啶���������𖳎�(1)
                } elseif (!empty($v['ignorecase'])) {
                    if (stristr($resfield, $v['word'])) {
                        $this->ngAbornUpdate($code, $k);
                        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        return $v['cond'];
                    }
                // �啶���������𖳎�(2)
                } elseif ($ic) {
                    if (stristr($resfield, $v['word'])) {
                        $this->ngAbornUpdate($code, $k);
                        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        return $v['cond'];
                    }
                // �P���ɕ����񂪊܂܂�邩�ǂ������`�F�b�N
                } else {
                    if (strstr($resfield, $v['word'])) {
                        $this->ngAbornUpdate($code, $k);
                        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
                        return $v['cond'];
                    }
                }
            }
        }

        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
        return false;
    }

    /**
     * ���背�X�̓������ځ[��`�F�b�N
     */
    function abornResCheck($resnum)
    {
        global $ngaborns;

        $t = &$this->thread;
        $target = $t->host . '/' . $t->bbs . '/' . $t->key . '/' . $resnum;

        if (isset($ngaborns['aborn_res']['data']) && is_array($ngaborns['aborn_res']['data'])) {
            foreach ($ngaborns['aborn_res']['data'] as $k => $v) {
                if ($ngaborns['aborn_res']['data'][$k]['word'] == $target) {
                    $this->ngAbornUpdate('aborn_res', $k);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * NG/���ځ`������Ɖ񐔂��X�V
     */
    function ngAbornUpdate($code, $k)
    {
        global $ngaborns;

        if (isset($ngaborns[$code]['data'][$k])) {
            $v =& $ngaborns[$code]['data'][$k];
            $v['lasttime'] = date('Y/m/d G:i'); // HIT���Ԃ��X�V
            if (empty($v['hits'])) {
                $v['hits'] = 1; // ��HIT
            } else {
                $v['hits']++; // HIT�񐔂��X�V
            }
        }
    }

    /**
     * ���[�U��`URL�n���h���i���b�Z�[�W����URL������������֐��j��ǉ�����
     *
     * �n���h���͍ŏ��ɒǉ����ꂽ���̂��珇�ԂɎ��s�����
     * URL�̓n���h���̕Ԃ�l�i������j�Œu�������
     * FALSE���A�����ꍇ�͎��̃n���h���ɏ������ς˂���
     *
     * ���[�U��`URL�n���h���̈�����
     *  1. string $url  URL
     *  2. array  $purl URL��parse_url()��������
     *  3. string $str  �p�^�[���Ƀ}�b�`����������AURL�Ɠ������Ƃ�����
     *  4. object &$aShowThread �Ăяo�����̃I�u�W�F�N�g
     * �ł���
     * ���FALSE��Ԃ��A�����ŏ������邾���̊֐���o�^���Ă��悢
     *
     * @param   string|array $function  �֐������Aarray(string $classname, string $methodname)
     *                                  �������� array(object $instance, string $methodname)
     * @return  void
     * @access  public
     * @todo    ���[�U��`URL�n���h���̃I�[�g���[�h�@�\������
     */
    function addURLHandler($function)
    {
        $this->user_url_handlers[] = $function;
    }

    /**
     * ���X�t�B���^�����O�̃^�[�Q�b�g�𓾂�
     */
    function getFilterTarget(&$ares, &$i, &$name, &$mail, &$date_id, &$msg)
    {
        switch ($GLOBALS['res_filter']['field']) {
            case 'name':
                $target = $name; break;
            case 'mail':
                $target = $mail; break;
            case 'date':
                $target = preg_replace('| ?ID:[0-9A-Za-z/.+?]+.*$|', '', $date_id); break;
            case 'id':
                if ($target = preg_replace('|^.*ID:([0-9A-Za-z/.+?]+).*$|', '$1', $date_id)) {
                    break;
                } else {
                    return '';
                }
            case 'msg':
                $target = $msg; break;
            default: // 'hole'
                $target = strval($i) . '<>' . $ares;
        }

        $target = @strip_tags($target, '<>');

        return $target;
    }

    /**
     * ���X�t�B���^�����O�̃}�b�`����
     */
    function filterMatch(&$target, &$resnum)
    {
        global $_conf;
        global $filter_hits, $filter_range;

        $failed = ($GLOBALS['res_filter']['match'] == 'off') ? TRUE : FALSE;

        if ($GLOBALS['res_filter']['method'] == 'and') {
            $words_fm_hit = 0;
            foreach ($GLOBALS['words_fm'] as $word_fm_ao) {
                if (StrCtl::filterMatch($word_fm_ao, $target) == $failed) {
                    if ($GLOBALS['res_filter']['match'] != 'off') {
                        return false;
                    } else {
                        $words_fm_hit++;
                    }
                }
            }
            if ($words_fm_hit == count($GLOBALS['words_fm'])) {
                return false;
            }
        } else {
            if (StrCtl::filterMatch($GLOBALS['word_fm'], $target) == $failed) {
                return false;
            }
        }

        $GLOBALS['filter_hits']++;

        if ($_conf['filtering'] && !empty($filter_range) &&
            ($filter_hits < $filter_range['start'] || $filter_hits > $filter_range['to'])
        ) {
            return false;
        }

        $GLOBALS['last_hit_resnum'] = $resnum;

        if (empty($_conf['ktai'])) {
            echo <<<EOP
<script type="text/javascript">
<!--
filterCount({$GLOBALS['filter_hits']});
-->
</script>\n
EOP;
        }

        return true;
    }
}
?>
