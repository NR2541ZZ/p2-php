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
    
    var $anchor_regex; // @access  protected
    
    /**
     * @constructor
     */
    function ShowThread(&$Thread)
    {
        global $_conf;
        
        $this->initAnchorRegex();       // set $this->anchor_regex
        $this->initStrToLinkRegex();    // set $this->str_to_link_regex
        
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
     * @access  private
     * @return  void    set $this->anchor_regex
     */
    function initAnchorRegex()
    {
        $anchor = array();
        
        // �A���J�[�p�󔒕����̐��K�\��
        $anchor_space = '(?:[ ]|�@)';
        
        // �A���J�[���p�q�̐��K�\��
        $anchor['prefix'] = "(?:&gt;|��|&lt;|��|�r|�t|��){1,2}{$anchor_space}*\.?";
        
        // ���ځ[��p�A���J�[���p�q�̐��K�\��
        $anchor['prefix_abon'] = "&gt;{1,2}{$anchor_space}?";

        // �A���J�[��̐��K�\��
        // $anchor['a_num']='(?:[1-9]|�P|�Q|�R|�S|�T|�U|�V|�W|�X)(?:\\d|�O|�P|�Q|�R|�S|�T|�U|�V|�W|�X){0,3}';
        
        // �A���J�[��̐��K�\��
        $anchor['a_digit'] = '(?:\\d|�O|�P|�Q|�R|�S|�T|�U|�V|�W|�X)';
        
        // �A���J�[��̐��K�\��
        $anchor['a_num'] = "{$anchor['a_digit']}{1,4}";

        $anchor['range_delimiter'] = "(?:-|�]|\x81\\x5b)"; // �[
        $anchor['a_range']   = "{$anchor['a_num']}(?:{$anchor['range_delimiter']}{$anchor['a_num']})?";
        $anchor['delimiter'] = "{$anchor_space}?(?:[,=+]|�A|�E|��|�C){$anchor_space}?";

        $anchor['ranges'] = "{$anchor['a_range']}(?:{$anchor['delimiter']}{$anchor['a_range']})*";
        $anchor['full']   = "{$anchor['prefix']}{$anchor['ranges']}";
        
        $this->anchor_regex = $anchor;
    }
    
    /**
     * @access  private
     * @return  void     set $this->str_to_link_regex
     */
    function initStrToLinkRegex()
    {
        $this->str_to_link_regex = '{'
            . '(?P<link>(<[Aa] .+?>)(.*?)(</[Aa]>))' // �����N�iPCRE�̓�����A�K�����̃p�^�[�����ŏ��Ɏ��s����j
            . '|'
            . '(?:'
            .   '(?P<quote>' // ���p
            /*
            .       '((?:&gt;|��){1,2} ?)' // ���p��
            .       '('
            .           '(?:[1-9]\\d{0,3})' // 1�ڂ̔ԍ�
            .           '(?:'
            .               '(?: ?(?:[,=]|�A) ?[1-9]\\d{0,3})+' // �A��
            .               '|'
            .               '-(?:[1-9]\\d{0,3})?' // �͈�
            .           ')?'
            .       ')'
            */
            .       '(' . $this->anchor_regex['prefix'] . ')'  // ���p��
            .       '(' . $this->anchor_regex['ranges'] . ')'  // �ԍ��͈͂̕��L[7]
            .       '(?=\\D|$)'
            .   ')' // ���p�����܂�
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
        $pattern = "/(?:^|{$this->anchor_regex['prefix']}|{$this->anchor_regex['delimiter']})({$this->anchor_regex['a_num']}+)/";
        
        // �g���b�v������
        $name = preg_replace('/(��.*)/', '', $name, 1);

        /*
        //if (preg_match('/[0-9]+/', $name, $m)) {
             return (int)$m[0];
        }
        */
        
        if (preg_match_all($pattern, $name, $matches)) {
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
        $pattern_anchor = "/{$this->anchor_regex['prefix']}({$this->anchor_regex['ranges']})/";
        $pattern_num = "/({$this->anchor_regex['a_num']})/";
        
        $quote_res_nums = array();
        
        // >>1�̃����N������
        // <a href="../test/read.cgi/accuse/1001506967/1" target="_blank">&gt;&gt;1</a>
        /*
        $msg = preg_replace('{<[Aa] .+?>(&gt;&gt;[1-9][\\d\\-]*)</[Aa]>}', '$1', $msg);
        */
        $msg = preg_replace('{<[Aa] .+?>(&gt;&gt;[\\d\\-]+)</[Aa]>}', '$1', $msg);
        
        //if (preg_match_all('/(?:&gt;|��)+ ?([1-9](?:[0-9\\- ,=.]|�A)*)/', $msg, $out, PREG_PATTERN_ORDER)) {
        if (preg_match_all($pattern_anchor, $msg, $out, PREG_PATTERN_ORDER)) {

            // $out[1] �͑� 1 �̃L���v�`���p�T�u�p�^�[���Ƀ}�b�`����������̔z��
            foreach ($out[1] as $numberq) {
                //if (preg_match_all('/[1-9]\\d*/', $numberq, $matches, PREG_PATTERN_ORDER)) {
                /*
                if (preg_match_all($pattern_num, $numberq, $matches, PREG_PATTERN_ORDER)) {
                    // $matches[0] �̓p�^�[���S�̂Ƀ}�b�`����������̔z��
                    foreach ($matches[1] as $a_quote_res_num) {
                */
                if ($matches = preg_split("/{$this->anchor_regex['delimiter']}/", $numberq)) { 
                    foreach ($matches as $a_quote_res_num) { 
                        if (preg_match("/{$this->anchor_regex['range_delimiter']}/", $a_quote_res_num)) {
                            continue;
                        }
                        $quote_res_nums[] = (int)mb_convert_kana($a_quote_res_num, 'n');
                    }
                }
            }
        }
        return array_unique($quote_res_nums);
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
