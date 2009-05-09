<?php
require_once P2_LIB_DIR . '/NgAbornCtl.php';

/**
 * �X���b�h��\������ �N���X
 */
class ShowThread
{
    var $thread; // �X���b�h�I�u�W�F�N�g�̎Q��
    
    // �����N���ׂ�������̐��K�\��
    var $str_to_link_regex;
    
    // ��̃��X�ɂ����郊���N�ϊ��̐����񐔁i�r�炵�΍�j
    var $str_to_link_limit = 30;
    
    // ��L�̎c�萔�J�E���^�[�B�����ȓK�p�͂��Ă��Ȃ��B�Ƃ肠����>>1,2,3,..�΍�̂��߂ɁB
    var $str_to_link_rest;
    
    // URL����������֐��E���\�b�h���Ȃǂ��i�[����z��i�f�t�H���g�j
    var $url_handlers       = array();

    // URL����������֐��E���\�b�h���Ȃǂ��i�[����z��i���[�U��`�A�f�t�H���g�̂��̂��D��j
    var $user_url_handlers  = array();

    /**
     * @constructor
     */
    function ShowThread(&$Thread)
    {
        global $_conf;
        
        $this->str_to_link_regex = $this->buildStrToLinkRegex();
        
        $this->thread = &$Thread;
        
        if ($_conf['flex_idpopup']) {
            $this->setIdCountToThread();
            // $this->setBackwordResesToThread();
        }
        
        if (empty($GLOBALS['_P2_NGABORN_LOADED'])) {
            NgAbornCtl::loadNgAborns();
        }
    }
    
    /**
     * static
     * @access  public
     * @param   string  $pattern  ex)'/%full%/'
     * @return  string
     */
    function getAnchorRegex($pattern)
    {
        static $caches_ = array();

        if (!array_key_exists($pattern, $caches_)) {
            $caches_[$pattern] = strtr($pattern, ShowThread::getAnchorRegexParts());
            // �卷�͂Ȃ��� compileMobile2chUriCallBack() �̂悤�� preg_relplace_callback()���Ă����������B
        }
        return $caches_[$pattern];
    }

    /**
     * static
     * @access  private
     * @return  string
     */
    function getAnchorRegexParts()
    {
        static $cache_;
        
        if (isset($cache_)) {
            return $cache_;
        }
        
        $anchor = array();
        
        // �A���J�[�̍\���v�f�i���K�\���p�[�c�̔z��j

        // �󔒕���
        $anchor_space = '(?:[ ]|�@)';
        //$anchor[' '] = '';

        // �A���J�[���p�q >>
        $anchor['prefix'] = "(?:&gt;|��|&lt;|��|�r|�t|��){1,2}{$anchor_space}*\.?";
        
        // ����
        $anchor['a_digit'] = '(?:\\d|�O|�P|�Q|�R|�S|�T|�U|�V|�W|�X)';
        /*
        $anchor[0] = '(?:0|�O)';
        $anchor[1] = '(?:1|�P)';
        $anchor[2] = '(?:2|�Q)';
        $anchor[3] = '(?:3|�R)';
        $anchor[4] = '(?:4|�S)';
        $anchor[5] = '(?:5|�T)';
        $anchor[6] = '(?:6|�U)';
        $anchor[7] = '(?:7|�V)';
        $anchor[8] = '(?:8|�W)';
        $anchor[9] = '(?:9|�X)';
        */
        
        // �͈͎w��q
        $anchor['range_delimiter'] = "(?:-|�]|\x81\\x5b)"; // �[
        
        // �񋓎w��q
        $anchor['delimiter'] = "{$anchor_space}?(?:[,=+]|�A|�E|��|�C){$anchor_space}?";

        // ���ځ[��p�A���J�[���p�q
        //$anchor['prefix_abon'] = "&gt;{1,2}{$anchor_space}?";

        // ���X�ԍ�
        $anchor['a_num'] = sprintf('%s{1,4}', $anchor['a_digit']);
        
        // ���X�͈�
        $anchor['a_range'] = sprintf("%s(?:%s%s)?",
            $anchor['a_num'], $anchor['range_delimiter'], $anchor['a_num']
        );
        
        // ���X�͈̗͂�
        $anchor['ranges'] = sprintf('%s(?:%s%s)*(?!%s)',
            $anchor['a_range'], $anchor['delimiter'], $anchor['a_range'], $anchor['a_digit']
        );
        
        // ���X�ԍ��̗�
        $anchor['nums'] = sprintf("%s(?:%s%s)*(?!%s)",
            $anchor['a_num'], $anchor['delimiter'], $anchor['a_num'], $anchor['a_digit']
        );
        
        // �A���J�[�S��
        $anchor['full'] = sprintf('(%s)(%s)', $anchor['prefix'], $anchor['ranges']);
        
        // getAnchorRegex() �� strtr() �u���p��key�� '%key%' �ɕϊ�����
        foreach ($anchor as $k => $v) {
            $anchor['%' . $k . '%'] = $v;
            unset($anchor[$k]);
        }
        
        $cache_ = $anchor;
        
        return $cache_;
    }
    
    /**
     * @access  private
     * @return  string
     */
    function buildStrToLinkRegex()
    {
        return $str_to_link_regex = '{'
            . '(?P<link>(<[Aa] .+?>)(.*?)(</[Aa]>))' // �����N�iPCRE�̓�����A�K�����̃p�^�[�����ŏ��Ɏ��s����j
            . '|'
            . '(?:'
            .   '(?P<quote>' // ���p
            .       $this->getAnchorRegex('%full%')
            .   ')'
            . '|'
            .   '(?P<url>'
            .       '(ftp|h?ttps?|tps?)://([0-9A-Za-z][\\w!#%&+*,\\-./:;=?@\\[\\]^~]+)' // URL
            .   ')'
            . '|'
            .   '(?P<id>ID: ?([0-9A-Za-z/.+]{8,11})(?=[^0-9A-Za-z/.+]|$))' // ID�i8,10�� +PC/�g�ю��ʃt���O�j
            . ')'
            . '}';
    }
    
    /**
     * Dat��HTML�ϊ����ĕ\������
     * �i�p����N���X�Ŏ����j
     *
     * @access  public
     * @return  boolean
     */
    function datToHtml()
    {
    }
    
    /**
     * Dat��HTML�ϊ��������̂��擾����
     *
     * @access  public
     * @return  string
     */
    function getDatToHtml()
    {
        ob_start();
        $this->datToHtml();
        $html = ob_get_clean();
        
        return $html;
    }

    /**
     * BE�v���t�@�C�������N�ϊ�
     *
     * @access  protected
     * @param   string     $data_id  2006/10/20(��) 11:46:08 ID:YS696rnVP BE:32616498-DIA(30003)
     * @param   integer    $i        ���X�ԍ�
     * @return  string
     */
    function replaceBeId($date_id, $i)
    {
        global $_conf;
        
        // urlencode���Ă����BE�I���󂯕t���Ȃ��݂���
        $u = "d:http://{$this->thread->host}/test/read.cgi/{$this->thread->bbs}/{$this->thread->key}/{$i}";
        
        // <BE:23457986:1>
        $be_match = '|<BE:(\d+):(\d+)>|i';
        if (preg_match($be_match, $date_id)) {
            $beid_replace = "<a href=\"http://be.2ch.net/test/p.php?i=\$1&u={$u}\"{$_conf['ext_win_target_at']}>Lv.\$2</a>";
            $date_id = preg_replace($be_match, $beid_replace, $date_id);

        // 2006/10/20(��) 11:46:08 ID:YS696rnVP BE:32616498-DIA(30003)
        } else {
            $beid_replace = "<a href=\"http://be.2ch.net/test/p.php?i=\$1&u={$u}\"{$_conf['ext_win_target_at']}>?\$2</a>";
            $date_id = preg_replace('|BE: ?(\d+)-(#*)|i', $beid_replace, $date_id);
        }

        return $date_id;
    }
    
    /**
     * ���X�̂��ځ[����܂Ƃ߂ă`�F�b�N����i���O�A���[���A���t�A���b�Z�[�W�j
     *
     * @access  protected
     * @return  string|false  �}�b�`������}�b�`������B�}�b�`���Ȃ����false
     */
    function checkAborns($name, $mail, $data_id, $msg)
    {
        return NgAbornCtl::checkAborns($name, $mail, $data_id, $msg, $this->thread->bbs, $this->thread->ttitle_hc);
    }
    
    /**
     * NG���ځ[����`�F�b�N����
     *
     * @access  protected
     * @return  string|false  �}�b�`������}�b�`������B�}�b�`���Ȃ����false
     */
    function ngAbornCheck($ngcode, $subject)
    {
        return NgAbornCtl::ngAbornCheck($ngcode, $subject, $this->thread->bbs, $this->thread->ttitle_hc);
    }
    
    /**
     * ���背�X�̓������ځ[��`�F�b�N
     *
     * @access  protected
     * @return  boolean
     */
    function abornResCheck($resnum)
    {
        $t = $this->thread;

        return NgAbornCtl::abornResCheck($t->host, $t->bbs, $t->key, $resnum);
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
     * ���X�t�B���^�����O�̃^�[�Q�b�g������𓾂�
     *
     * @access  protected
     * @return  string
     */
    function getFilterTarget($i, $name, $mail, $date_id, $msg)
    {
        switch ($GLOBALS['res_filter']['field']) {
            case 'name':
                $target = $name;
                break;
            case 'mail':
                $target = $mail;
                break;
            case 'date':
                $target = preg_replace('| ?ID:[0-9A-Za-z/.+?]+.*$|', '', $date_id);
                break;
            case 'id':
                if ($target = preg_replace('|^.*ID:([0-9A-Za-z/.+?]+).*$|', '$1', $date_id)) {
                    break;
                } else {
                    return '';
                }
            case 'msg':
                $target = $msg;
                break;
            default: // 'whole'
                // �ȗ��O�̕����񂪓���̂� $ares �̒��ڗ��p�̓_���ɂȂ���
                // $target = strval($i) . '<>' . $ares;
                $target = implode('<>', array(strval($i), $name, $mail, $date_id, $msg));
        }

        // '<>' ��������
        $target = strip_tags($target, '<>');
        
        return $target;
    }

    /**
     * ���X�t�B���^�����O�̃}�b�`����
     *
     * @access  private
     * @return  string|false    �}�b�`������}�b�`������i�u�܂܂Ȃ��v�����̎���true�j���A�}�b�`���Ȃ�������false��Ԃ�
     *                          �i�P����boolean��Ԃ��悤�ɂ��Ă�������������Ȃ��j
     */
    function filterMatch($target, $resnum)
    {
        global $_conf;
        global $_filter_hits, $filter_range;

        $failed = ($GLOBALS['res_filter']['match'] == 'off') ? true : false;

        if ($GLOBALS['res_filter']['method'] == 'and') {
            $words_fm_hit = 0;
            foreach ($GLOBALS['words_fm'] as $word_fm_ao) {
                $match = StrCtl::filterMatch($word_fm_ao, $target);
                if ((bool)strlen($match) == $failed) {
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
            $match = StrCtl::filterMatch($GLOBALS['word_fm'], $target);
            if ((bool)strlen($match) == $failed) {
                return false;
            }
        }

        $GLOBALS['_filter_hits']++;

        // �\���͈͊O�Ȃ�U����Ƃ���
        if (isset($GLOBALS['word']) && !empty($filter_range) &&
            ($_filter_hits < $filter_range['start'] || $_filter_hits > $filter_range['to'])
        ) {
            return false;
        }

        $GLOBALS['last_hit_resnum'] = $resnum;

        // �����X�V�p
        if (!$_conf['ktai']) {
            echo <<<EOP
<script type="text/javascript">
<!--
filterCount({$GLOBALS['_filter_hits']});
-->
</script>\n
EOP;
        }

        return $failed ? !(bool)$match : $match;
    }
    
    /**
     * ��̃X�����ł�ID�o������Thread�ɃZ�b�g����
     *
     * @access  private
     * @return  void
     */
    function setIdCountToThread()
    {
        $lines = $this->thread->datlines;
        
        if (!is_array($lines)) {
            //trigger_error('no $this->thread->datlines', E_USER_WARNING);
            return;
        }
        foreach ($lines as $k => $line) {
            $lar = explode('<>', $line);
            if (preg_match('|ID: ?([0-9a-zA-Z/.+]{8,10})|', $lar[2], $matches)) {
                $id = $matches[1];
                if (isset($this->thread->idcount[$id])) {
                    $this->thread->idcount[$id]++;
                } else {
                    $this->thread->idcount[$id] = 1;
                }
            }
        }
    }
    
    /**
     * �t�Q�Ƃ�Thread�ɃZ�b�g����
     *
     * @access  private
     * @return  void
     */
    function setBackwordResesToThread()
    {
        $lines = $this->thread->datlines;
        
        if (!is_array($lines)) {
            //trigger_error('no $this->thread->datlines', E_USER_WARNING);
            return;
        }

        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('set_backword_reses');
        
        foreach ($lines as $k => $line) {
        
            // �t�Q�Ƃ̂��߂̈��p���X�ԍ��擾�i�������x��2,3�����ɂȂ�c�j
            if ($nums = $this->getQuoteResNumsName($lar[0])) {
                if (isset($this->thread->backword_reses[$k])) {
                    array_merge($this->thread->backword_reses[$k], $nums);
                } else {
                    $this->thread->backword_reses[$k] = $nums;
                }
            }
            
            if ($nums = $this->getQuoteResNumsMsg($lar[3])) {
                if (isset($this->thread->backword_reses[$k])) {
                    array_merge($this->thread->backword_reses[$k], $nums);
                } else {
                    $this->thread->backword_reses[$k] = $nums;
                }
            }
        }
        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('set_backword_reses');
    }
    
    /**
     * ���O�ɂ�����p���X�ԍ����擾����
     *
     * @access  private
     * @param   string  $name�i���t�H�[�}�b�g�j
     * @return  array|false
     */
    function getQuoteResNumsName($name)
    {
        // �g���b�v������
        $name = preg_replace('/(��.*)/', '', $name, 1);

        /*
        //if (preg_match('/[0-9]+/', $name, $m)) {
             return (int)$m[0];
        }
        */

        if (preg_match_all($this->getAnchorRegex('/(?:^|%prefix%|%delimiter%)(%a_num%)/'), $name, $matches)) {
            foreach ($matches[1] as $a_quote_res_num) {
                $quote_res_nums[] = (int)mb_convert_kana($a_quote_res_num, 'n');
            }
            return array_unique($quote_res_nums);
        }
        
        return false;
    }
    
    /**
     * ���b�Z�[�W�ɂ�����p���X�ԍ����擾����
     *
     * @access  private
     * @param   string  $msg�i���t�H�[�}�b�g�j
     * @return  array|false
     */
    function getQuoteResNumsMsg($msg)
    {
        $quote_res_nums = array();
        
        // DAT���ɂ���>>1�̃����NHTML����菜��
        $msg = $this->removeResAnchorTagInDat($msg);
        
        if (preg_match_all($this->getAnchorRegex('/%full%/'), $msg, $out, PREG_PATTERN_ORDER)) {

            // $out[2] �͑� 2 �̃L���v�`���p�T�u�p�^�[���Ƀ}�b�`����������̔z��
            foreach ($out[2] as $numberq) {
                if ($matches = preg_split($this->getAnchorRegex('/%delimiter%/'), $numberq)) { 
                    foreach ($matches as $a_quote_res_num) { 
                        if (preg_match($this->getAnchorRegex('/%range_delimiter%/'), $a_quote_res_num)) {
                            continue;
                        }
                        $quote_res_nums[] = (int)mb_convert_kana($a_quote_res_num, 'n');
                    }
                }
            }
        }
        return array_unique($quote_res_nums);
    }
    
    /**
     * @access  protected
     * @return  string  HTML
     */
    function quote_name_callback($s)
    {
        return preg_replace_callback(
            $this->getAnchorRegex('/(%prefix%)?(%a_num%)/'),
            array($this, 'quote_res_callback'), $s[0]
        );
    }
    
    /**
     * DAT���ɂ���>>1�̃����NHTML����菜��
     *
     * @return  string
     */
    function removeResAnchorTagInDat($msg)
    {
        // <a href="../test/read.cgi/accuse/1001506967/1" target="_blank">&gt;&gt;1</a>
        return preg_replace('{<[Aa] .+?>(&gt;&gt;\\d[\\d\\-]*)</[Aa]>}', '$1', $msg);
    }

    /**
     * AA����
     *
     * @access  protected
     * @return  integer  0:�����Ȃ�, 1:�㔽��, 2:�������iAA���j
     */
    function detectAA($s)
    {
        global $_conf;
        
        // AA �ɂ悭�g����p�f�B���O
        $regexA = '�@{3}|(?: �@){2}';

        // �r��
        // [\u2500-\u257F]
        //var $regexB = '[\\x{849F}-\\x{84BE}]{5}';
        $regexB = '[��-����]{4}';

        // Latin-1,�S�p�X�y�[�X�Ƌ�Ǔ_,�Ђ炪��,�J�^�J�i,���p�E�S�p�` �ȊO�̓���������3�A������p�^�[��
        // Unicode �� [^\x00-\x7F\x{2010}-\x{203B}\x{3000}-\x{3002}\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{FF00}-\x{FFEF}]
        // ���x�[�X�� SJIS �ɍ�蒼���Ă��邪�A�኱�̈Ⴂ������B
        //$regexC = '([^\\x00-\\x7F\\xA1-\\xDF�@�A�B�C�D�F�G�O-���[�`�E�c���I�H�����������{�^��])\\1\\1';
        $regexC = '([^\\x00-\\x7F\\xA1-\\xDF�@�A�B�C�D�F�G�O-���[�`�E�c���I�H�����������{�^��]|[_,:;\'])\\1\\1';
        
        //$re = '(?:' . $this->regexA . '|' . $this->regexB . '|' . $this->regexC . ')';
        
        $level = 0;
        
        // AA���̑ΏۂƂ���Œ�s���i3�s�𒴂�����̂̂ݏȗ�����j
        $aa_ryaku = false;
        if (preg_match("/^(.+<br>){3}./", $s)) {
            $aa_ryaku = true;
        }
        
        if (mb_ereg($regexA, $s)) {
            $level = 1;
        }
        
        // AA�����Ȃ��Ȃ炱���܂�
        if (!$_conf['k_aa_ryaku_size'] or !$aa_ryaku) {
            return $level;
        }
        
        if ($level && mb_ereg($regexC, $s)) {
            return 2;
        }

        if (mb_ereg($regexB, $s)) {
            return 2;
        }

        return $level;
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
