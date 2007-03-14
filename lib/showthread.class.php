<?php
/**
 * �X���b�h��\������ �N���X
 */
class ShowThread
{
    var $thread; // �X���b�h�I�u�W�F�N�g�̎Q��
    
    var $str_to_link_regex; // �����N���ׂ�������̐��K�\��
    var $str_to_link_limit = 30; // ��̃��X�ɂ����郊���N�ϊ��̐����񐔁i�r�炵�΍�j
    
    // URL����������֐��E���\�b�h���Ȃǂ��i�[����z��i�f�t�H���g�j
    var $url_handlers       = array();

    // URL����������֐��E���\�b�h���Ȃǂ��i�[����z��i���[�U��`�A�f�t�H���g�̂��̂��D��j
    var $user_url_handlers  = array();

    /**
     * @constructor
     */
    function ShowThread(&$aThread)
    {
        // �X���b�h�I�u�W�F�N�g�̎Q�Ƃ�o�^
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
            .   '(?P<url>'
            .       '(ftp|h?t?tps?)://([0-9A-Za-z][\\w!#%&+*,\\-./:;=?@\\[\\]^~]+)' // URL
            .   ')'
            . '|'
            .   '(?P<id>ID: ?([0-9A-Za-z/.+]{8,11})(?=[^0-9A-Za-z/.+]|$))' // ID�i8,10�� +PC/�g�ю��ʃt���O�j
            . ')'
            . '}';

        if (empty($GLOBALS['_P2_NGABORN_LOADED'])) {
            NgAbornCtl::loadNgAborns();
        }
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
        
        $beid_replace = "<a href=\"http://be.2ch.net/test/p.php?i=\$1&u=d:http://{$this->thread->host}/test/read.cgi/{$this->thread->bbs}/{$this->thread->key}/{$i}\"{$_conf['ext_win_target_at']}>Lv.\$2</a>";
        
        // <BE:23457986:1>
        $be_match = '|<BE:(\d+):(\d+)>|i';
        if (preg_match($be_match, $date_id)) {
            $date_id = preg_replace($be_match, $beid_replace, $date_id);
        
        // 2006/10/20(��) 11:46:08 ID:YS696rnVP BE:32616498-DIA(30003)
        } else {
            $beid_replace = "<a href=\"http://be.2ch.net/test/p.php?i=\$1&u=d:http://{$this->thread->host}/test/read.cgi/{$this->thread->bbs}/{$this->thread->key}/{$i}\"{$_conf['ext_win_target_at']}>?\$2</a>";
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
            default: // 'hole'
                // �ȗ��O�̕����񂪓���̂� $ares �̓_���ɂȂ���
                // $target = strval($i) . '<>' . $ares;
                $target = strval($i) . '<>' . $name . '<>' . $mail . '<>' . $date_id . '<>' . $msg;
        }

        // '<>' ��������
        $target = strip_tags($target, '<>');
        
        return $target;
    }

    /**
     * ���X�t�B���^�����O�̃}�b�`����
     *
     * @access  protected
     * @return  boolean     �}�b�`������true��Ԃ�
     */
    function filterMatch($target, $resnum)
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
        
        // �\���͈͊O�Ȃ�U����Ƃ���
        if (isset($GLOBALS['word']) && !empty($filter_range) &&
            ($filter_hits < $filter_range['start'] || $filter_hits > $filter_range['to'])
        ) {
            return false;
        }
        
        $GLOBALS['last_hit_resnum'] = $resnum;

        if (!$_conf['ktai']) {
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
