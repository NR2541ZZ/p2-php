<?php
require_once P2_LIB_DIR . '/ShowThread.php';

/**
 * p2 - �X���b�h��\������ �N���X PC�p
 */
class ShowThreadPc extends ShowThread
{
    var $quote_res_nums_checked;    // �|�b�v�A�b�v�\�������`�F�b�N�ς݃��X�ԍ���o�^�����z��
    var $quote_res_nums_done;       // �|�b�v�A�b�v�\�������L�^�ς݃��X�ԍ���o�^�����z��

    /**
     * @constructor
     */
    function ShowThreadPc(&$aThread)
    {
        parent::ShowThread($aThread);

        global $_conf;

        $this->url_handlers = array(
            'plugin_link2ch',
            'plugin_linkMachi',
            'plugin_linkJBBS',
            'plugin_link2chKako',
            'plugin_link2chSubject',
            'plugin_linkReadCgi'
        );
        if ($_conf['preview_thumbnail']) {
            $this->url_handlers[] = 'plugin_viewImage';
        }
        $_conf['link_youtube']  and $this->url_handlers[] = 'plugin_linkYouTube';
        $_conf['link_niconico'] and $this->url_handlers[] = 'plugin_linkNicoNico';
        $_conf['link_yourfilehost'] and $this->url_handlers[] = 'plugin_linkYourFileHost';
        $this->url_handlers[] = 'plugin_linkURL';
    }

    /**
     * Dat��HTML�ɕϊ����ĕ\������
     *
     * @access  public
     * @return  boolean
     */
    function datToHtml()
    {
        // �\�����X�͈͂��w�肳��Ă��Ȃ����
        if (!$this->thread->resrange) {
            echo '<b>p2 error: {$this->resrange} is FALSE at datToHtml()</b>';
            return false;
        }

        $start = $this->thread->resrange['start'];
        $to = $this->thread->resrange['to'];
        $nofirst = $this->thread->resrange['nofirst'];

        $status_title_hs = hs($this->thread->itaj) . ' / ' . hs($this->thread->ttitle_hc);
        $status_title_hs = str_replace("&#039;", "\&#039;", $status_title_hs);
        //$status_title_hs = str_replace(array("\\", "'"), array("\\\\", "\\'"), $status_title_hs);
        echo "<dl onMouseover=\"window.top.status='{$status_title_hs} ';\">";

        // 1��\���i�͈͊O�̃P�[�X������̂ł����Łj
        if (!$nofirst) {
            echo $this->transRes($this->thread->datlines[0], 1);
        }

        for ($i = $start; $i <= $to; $i++) {
            
            // �\���͈͊O�Ȃ�X�L�b�v
            if ($this->thread->resrange_multi and !$this->thread->inResrangeMulti($i)) {
                continue;
            }
            
            // 1���O�i�����Ŋ��\���Ȃ�X�L�b�v
            if (!$nofirst and $i == 1) {
                continue;
            }
            if (!$this->thread->datlines[$i - 1]) {
                // $this->thread->readnum = $i - 1; 2006/09/23 �����ŃZ�b�g����͈̂Ⴄ�C������
                break;
            }
            echo $this->transRes($this->thread->datlines[$i - 1], $i);
            
            !isset($GLOBALS['_read_new_html']) && ob_flush() && flush();
        }

        echo "</dl>\n";
        
        //$s2e = array($start, $i-1);
        //return $s2e;
        return true;
    }

    /**
     * Dat���X��HTML���X�ɕϊ�����
     *
     * @access  public
     * @param   string   $ares  dat��1���C��
     * @param   integer  $i     ���X�ԍ�
     * @return  string  HTML
     */
    function transRes($ares, $i)
    {
        global $_conf, $STYLE, $mae_msg;
        
        global $_ngaborns_head_hits;
        
        $tores      = '';
        $rpop       = '';

        $resar      = $this->thread->explodeDatLine($ares);
        $name       = $resar[0];
        $mail       = $resar[1];
        $date_id    = $resar[2];
        $msg        = $resar[3];

        // {{{ �t�B���^�����O�J�b�g
        
        if (isset($GLOBALS['word']) && strlen($GLOBALS['word'])) {
            if (strlen($GLOBALS['word_fm']) <= 0) {
                return '';
            // �^�[�Q�b�g�ݒ�
            } elseif (!$target = $this->getFilterTarget($i, $name, $mail, $date_id, $msg)) {
                return '';
            // �}�b�`���O
            } else {
                $match = $this->filterMatch($target, $i);
                // strlen(true) ��1�Astrlen(false)��0��Ԃ��B
                if (false === (bool)strlen($match)) {
                    return '';
                }
            }
        }
        
        // }}}
        // {{{ ���ځ[��`�F�b�N�i���O�A���[���AID�A���b�Z�[�W�j

        if (false !== $this->checkAborns($name, $mail, $date_id, $msg)) {
            
            // ���O
            $aborned_res_html = '<dt id="r' . $i . '" class="aborned"><span>&nbsp;</span></dt>' . "\n";
            // ���e
            $aborned_res_html .= '<!-- <dd class="aborned">&nbsp;</dd> -->' . "\n";

            return $aborned_res_html;
        }
        
        // }}}
        // {{{ ���X���|�b�v�A�b�v�\��
        // �i$_ngaborns_head_hits ������Ȃ��悤�ɁANG�`�F�b�N�����O�Ɂj
        
        if ($_conf['quote_res_view']) {
            $quote_res_nums = $this->checkQuoteResNums($i, $name, $msg);

            foreach ($quote_res_nums as $rnv) {
                if (empty($this->quote_res_nums_done[$rnv]) and $rnv < count($this->thread->datlines)) {
                    $ds = $this->qRes($this->thread->datlines[$rnv - 1], $rnv);
                    $onPopUp_at = " onMouseover=\"showResPopUp('q{$rnv}of{$this->thread->key}',event,true)\"";
                    $rpop .= "<dd id=\"q{$rnv}of{$this->thread->key}\" class=\"respopup\"{$onPopUp_at}><i>" . $ds . "</i></dd>\n";
                    $this->quote_res_nums_done[$rnv] = true;
                }
            }
        }
        
        // }}}
        // {{{ NG�`�F�b�N�i���O�A���[���AID�A���b�Z�[�W�j
        
        $isNgName = false;
        $isNgMail = false;
        $isNgId   = false;
        $isNgMsg  = false;
        
        if (false !== $this->ngAbornCheck('ng_name', strip_tags($name))) {
            $isNgName = true;
        }
        if (false !== $this->ngAbornCheck('ng_mail', $mail)) {
            $isNgMail = true;
        }
        if (false !== $this->ngAbornCheck('ng_id', $date_id)) {
            $isNgId = true;
        }
        if (false !== ($a_ng_msg = $this->ngAbornCheck('ng_msg', $msg))) {
            $isNgMsg = true;
        }
        
        // }}}
        
        //=============================================================
        // �܂Ƃ߂ďo��
        //=============================================================

        $name_ht = $this->transName($name, $i); // ���OHTML�ϊ�
        $msg_ht  = $this->transMsg($msg, $i);   // ���b�Z�[�WHTML�ϊ�
        //$date_id = $this->transDateId($date_id);

        // BE�v���t�@�C�������N�ϊ�
        $date_id = $this->replaceBeId($date_id, $i);

        // HTML�|�b�v�A�b�v
        if ($_conf['iframe_popup']) {
            $date_id = preg_replace_callback(
                "{<a href=\"(http://[-_.!~*()a-zA-Z0-9;/?:@&=+\$,%#]+)\"({$_conf['ext_win_target_at']})>((\?#*)|(Lv\.\d+))</a>}",
                array($this, 'iframePopupCallback'),
                $date_id
            );
        }
        
        $atTitle = ' title="�N���b�N�ŕ\��/��\��"';
        
        $a_ng_msg_hs = htmlspecialchars($a_ng_msg, ENT_QUOTES);
        
        // NG���b�Z�[�W�ϊ�
        if ($isNgMsg) {
            $msg_ht = <<<EOMSG
<s class="ngword" onClick="showHide(this.nextSibling, 'ngword_cont');"{$atTitle}>NG�F{$a_ng_msg_hs}</s><div class="ngword_cont">$msg_ht</div>
EOMSG;
        }

        // NG�l�[���ϊ�
        if ($isNgName) {
            $name_ht = <<<EONAME
<s class="ngword" onClick="showHide('ngn{$_ngaborns_head_hits}', 'ngword_cont');"{$atTitle}>$name_ht</s>
EONAME;
            $msg_ht = <<<EOMSG
<div id="ngn{$_ngaborns_head_hits}" class="ngword_cont">$msg_ht</div>
EOMSG;

        // NG���[���ϊ�
        } elseif ($isNgMail) {
            $mail = <<<EOMAIL
<s class="ngword" onClick="showHide('ngn{$_ngaborns_head_hits}', 'ngword_cont');"{$atTitle}>$mail</s>
EOMAIL;
            $msg_ht = <<<EOMSG
<div id="ngn{$_ngaborns_head_hits}" class="ngword_cont">$msg_ht</div>
EOMSG;

        // NGID�ϊ�
        } elseif ($isNgId) {
            $date_id = preg_replace('|ID: ?([0-9A-Za-z/.+]{8,11})|', "<s class=\"ngword\" onClick=\"showHide('ngn{$_ngaborns_head_hits}', 'ngword_cont');\"{$atTitle}>NG�F\\0</s>", $date_id);
            
            /*
            $date_id = <<<EOID
<s class="ngword" onClick="showHide('ngn{$_ngaborns_head_hits}', 'ngword_cont');">$date_id</s>
EOID;
            */
            
            $msg_ht = <<<EOMSG
<div id="ngn{$_ngaborns_head_hits}" class="ngword_cont">$msg_ht</div>
EOMSG;
        }

        /*
        //�u��������V���v�摜��}��
        if ($i == $this->thread->readnum +1) {
            $tores .= <<<EOP
                <div><img src="img/image.png" alt="�V�����X" border="0" vspace="4"></div>
EOP;
        }
        */
        
        // �X�}�[�g�|�b�v�A�b�v���j���[
        if ($_conf['enable_spm']) {
            $onPopUp_at = " onmouseover=\"showSPM({$this->thread->spmObjName},{$i},'{$id}',event,this)\" onmouseout=\"hideResPopUp('{$this->thread->spmObjName}_spm')\"";
        } else {
            $onPopUp_at = "";
        }

        if ($this->thread->onthefly) {
            $GLOBALS['newres_to_show_flag'] = true;
            // �ԍ��i�I���U�t���C���j
            $tores .= "<dt id=\"r{$i}\"><span class=\"ontheflyresorder\">{$i}</span> �F";
            
        } elseif ($i > $this->thread->readnum) {
            $GLOBALS['newres_to_show_flag'] = true;
            // �ԍ��i�V�����X���j
            if ($onPopUp_at) {
                //  style=\"cursor:pointer;\"
                $tores .= "<dt id=\"r{$i}\"><a class=\"resnum\"{$onPopUp_at}><font color=\"{$STYLE['read_newres_color']}\" class=\"newres\">{$i}</font></a> �F";
            } else {
                $tores .= "<dt id=\"r{$i}\"><font color=\"{$STYLE['read_newres_color']}\" class=\"newres\">{$i}</font> �F";
            }
            
        } else {
            // �ԍ�
            if ($onPopUp_at) {
                //  style=\"cursor:pointer;\"
                $tores .= "<dt id=\"r{$i}\"><a href=\"#\" class=\"resnum\"{$onPopUp_at}>{$i}</a> �F";
            } else {
                $tores .= "<dt id=\"r{$i}\">{$i} �F";
            }
        }
        
        // ���O
        $tores .= "<span class=\"name\"><b>{$name_ht}</b></span>�F";
        
        // ���[��
        if ($mail) {
            if (strstr($mail, 'sage') && $STYLE['read_mail_sage_color']) {
                $tores .= "<span class=\"sage\">{$mail}</span> �F";
            } elseif ($STYLE['read_mail_color']) {
                $tores .= "<span class=\"mail\">{$mail}</span> �F";
            } else {
                $tores .= $mail." �F";
            }
        }

        // ID�t�B���^
        if ($_conf['flex_idpopup'] == 1) {
            if (preg_match('|ID: ?([0-9A-Za-z/.+]{8,11})|', $date_id, $matches)) {
                $id = $matches[1];
                if ($this->thread->idcount[$id] > 1) {
                    $date_id = preg_replace_callback(
                        '|ID: ?([0-9A-Za-z/.+]{8,11})|',
                        array($this, 'idfilter_callback'), $date_id
                    );
                }
            }
        }
        
        $tores .= $date_id; // ���t��ID
        $tores .= "</dt>\n";
        $tores .= $rpop; // ���X�|�b�v�A�b�v�p���p
        $tores .= "<dd>{$msg_ht}<br><br></dd>\n"; // ���e
        
        // �܂Ƃ߂ăt�B���^�F����
        if (isset($GLOBALS['word_fm']) && strlen($GLOBALS['word_fm']) && $GLOBALS['res_filter']['match'] != 'off') {
            $tores = StrCtl::filterMarking($GLOBALS['word_fm'], $tores);
        }

        return $tores;
    }


    /**
     * >>1 �|�b�v�A�b�v�\���p�� (���p�|�b�v�A�b�v�p) HTML�f�[�^�i�z��j��Ԃ�
     *
     * @access  public
     * @return  array
     */
    function quoteOne()
    {
        global $_conf;

        if (!$_conf['quote_res_view']) {
            return false;
        }

        $ds = '';
        $rpop = '';
        $dummy_msg = "";
        $quote_res_nums = $this->checkQuoteResNums(0, "1", $dummy_msg);
        foreach ($quote_res_nums as $rnv) {
            if (empty($this->quote_res_nums_done[$rnv])) {
                if ($this->thread->ttitle_hs) {
                    $ds = "<b>{$this->thread->ttitle_hs} </b><br><br>";
                }
                $resline = isset($this->thread->datlines[$rnv - 1]) ? $this->thread->datlines[$rnv - 1] : '';
                $ds .= $this->qRes($resline, $rnv);
                $onPopUp_at = " onMouseover=\"showResPopUp('q{$rnv}of{$this->thread->key}',event,true)\"";
                $rpop .= "<div id=\"q{$rnv}of{$this->thread->key}\" class=\"respopup\"{$onPopUp_at}><i>" . $ds . "</i></div>\n";
                $this->quote_res_nums_done[$rnv] = true;
            }
        }
        $res1['q'] = $rpop;

        $m1 = "&gt;&gt;1";
        $res1['body'] = $this->transMsg($m1, 1);
        
        return $res1;
    }

    /**
     * ���X���pHTML�𐶐��擾����
     *
     * @access  private
     * @param   string   $resline
     * @return  string
     */
    function qRes($resline, $i)
    {
        global $_conf;
        global $_ngaborns_head_hits;
        
        $resar      = $this->thread->explodeDatLine($resline);
        $name       = isset($resar[0]) ? $resar[0] : '';
        $mail       = isset($resar[1]) ? $resar[1] : '';
        $date_id    = isset($resar[2]) ? $resar[2] : '';
        $msg        = isset($resar[3]) ? $resar[3] : '';

        // ���ځ[��`�F�b�N
        if (false !== $this->checkAborns($name, $mail, $date_id, $msg)) {
            $name = $date_id = $msg = '���ځ[��';
            $mail = '';
            // "$i �F���ځ[�� �F���ځ[��<br>���ځ[��<br>\n"
            
        } else {
        
            $isNgName = false;
            $isNgMail = false;
            $isNgId   = false;
            $isNgMsg  = false;
        
            if (false !== $this->ngAbornCheck('ng_name', strip_tags($name))) {
                $isNgName = true;
            }
            if (false !== $this->ngAbornCheck('ng_mail', $mail)) {
                $isNgMail = true;
            }
            if (false !== $this->ngAbornCheck('ng_id', $date_id)) {
                $isNgId = true;
            }
            if (false !== ($a_ng_msg = $this->ngAbornCheck('ng_msg', $msg))) {
                $isNgMsg = true;
            }
        
            $name = $this->transName($name, $i);
            $msg  = $this->transMsg($msg, $i); // ���b�Z�[�W�ϊ�
            //$date_id = $this->transDateId($date_id);
            
            // BE�v���t�@�C�������N�ϊ�
            $date_id = $this->replaceBeId($date_id, $i);

            // HTML�|�b�v�A�b�v
            if ($_conf['iframe_popup']) {
                $date_id = preg_replace_callback(
                    "{<a href=\"(http://[-_.!~*()a-zA-Z0-9;/?:@&=+\$,%#]+)\"({$_conf['ext_win_target_at']})>((\?#*)|(Lv\.\d+))</a>}",
                    array($this, 'iframePopupCallback'), $date_id
                );
            }
            
            $atTitle = ' title="�N���b�N�ŕ\��/��\��"';

            $a_ng_msg_hs = htmlspecialchars($a_ng_msg, ENT_QUOTES);
            
            // NG���b�Z�[�W�ϊ�
            if ($isNgMsg) {
                $msg = <<<EOMSG
<s class="ngword" onClick="showHide(this.nextSibling, 'ngword_cont');"{$atTitle}>NG�F{$a_ng_msg_hs}</s><div  class="ngword_cont">$msg</div>
EOMSG;
            }

            // NG�l�[���ϊ�
            if ($isNgName) {
                $name = <<<EONAME
<s class="ngword" onClick="showHide('ngn{$_ngaborns_head_hits}', 'ngword_cont');"{$atTitle}>$name</s>
EONAME;
                $msg = <<<EOMSG
<div id="ngn{$_ngaborns_head_hits}" class="ngword_cont">$msg</div>
EOMSG;

            // NG���[���ϊ�
            } elseif ($isNgMail) {
                $mail = <<<EOMAIL
<s class="ngword" onClick="showHide('ngn{$_ngaborns_head_hits}', 'ngword_cont');"{$atTitle}>$mail</s>
EOMAIL;
                $msg = <<<EOMSG
<div id="ngn{$_ngaborns_head_hits}" class="ngword_cont">$msg</div>
EOMSG;

            // NGID�ϊ�
            } elseif ($isNgId) {
                $date_id = preg_replace(
                    '|ID: ?([0-9A-Za-z/.+]{8,11})|',
                    "<s class=\"ngword\" onClick=\"showHide('ngn{$_ngaborns_head_hits}', 'ngword_cont');\"{$atTitle}>NG�F\\0</s>",
                    $date_id
                );
                
                /*
                $date_id = <<<EOID
<s class="ngword" onClick="showHide('ngn{$_ngaborns_head_hits}', 'ngword_cont');">$date_id</s>
EOID;
                */
                
                $msg = <<<EOMSG
<div id="ngn{$_ngaborns_head_hits}" class="ngword_cont">$msg</div>
EOMSG;
            }
            
            // �X�}�[�g�|�b�v�A�b�v���j���[
            if ($_conf['enable_spm']) {
                $onPopUp_at = " onmouseover=\"showSPM({$this->thread->spmObjName},{$i},'{$id}',event,this)\" onmouseout=\"hideResPopUp('{$this->thread->spmObjName}_spm')\"";
                $i = "<a href=\"javascript:void(0);\" class=\"resnum\"{$onPopUp_at}>{$i}</a>";
            }
        
            // ID�t�B���^
            if ($_conf['flex_idpopup'] == 1) {
                if (preg_match('|ID: ?([0-9a-zA-Z/.+]{8,11})|', $date_id, $matches)) {
                    $id = $matches[1];
                    if ($this->thread->idcount[$id] > 1) {
                        $date_id = preg_replace_callback(
                            '|ID: ?([0-9A-Za-z/.+]{8,11})|',
                            array($this, 'idfilter_callback'), $date_id
                        );
                    }
                }
            }
        
        }
        
        // $tores�ɂ܂Ƃ߂ďo��
        $tores = "$i �F"; // �ԍ�
        $tores .= "<b>$name</b> �F"; // ���O
        if ($mail) { $tores .= $mail . " �F"; } // ���[��
        $tores .= $date_id; // ���t��ID
        $tores .= "<br>";
        $tores .= $msg . "<br>\n"; // ���e

        return $tores;
    }

    /**
     * ���O��HTML�p�ɕϊ����ĕԂ�
     *
     * @access  private
     * @return  string  HTML
     */
    function transName($name, $resnum)
    {
        global $_conf;
        
        $nameID = '';
        
        // ID�t�Ȃ疼�O�� "aki </b>��...p2/2... <b>" �Ƃ����������ł���B�i�ʏ�͕��ʂɖ��O�̂݁j
        
        // ID�t�Ȃ番������
        if (preg_match('~(.*)( </b>��.*)~', $name, $matches)) {
            $name   = $matches[1];
            $nameID = $matches[2];
        }

        // �����������N��
        if ($_conf['quote_res_view']) {
            /*
            $uri = P2Util::buildQueryUri($_conf['read_php'], array(
                'host' => $this->thread->host,
                'bbs'  => $this->thread->bbs,
                'key'  => $this->thread->key,
                'ls'   => '\\1'
            ));
            $atag = P2View::tagA($uri,
                '\\1',
                array(
                    'target' => $_conf['bbs_win_target'],
                    'onMouseover' => "showResPopUp('q\\1of{$this->thread->key}',event)",
                    'onMouseout'  => "hideResPopUp('q\\1of{$this->thread->key}')"
                )
            );
            $name && $name = preg_replace("/([1-9][0-9]*)/", "$atag", $name, 1);
            */
            
            // ���������p���X�|�b�v�A�b�v�����N��
            // </b>�`<b> �́A�z�X�g��g���b�v�Ȃ̂Ń}�b�`���Ȃ��悤�ɂ�����
            if ($name) {
                $name = preg_replace_callback(
                    $this->getAnchorRegex('/(?:^|%prefix%)%nums%/'),
                    array($this, 'quote_name_callback'), $name
                );
            }
        }
        
        if ($nameID) { $name = $name . $nameID; }

        $name = $name . ' '; // �ȈՓI�ɕ����������

        return $name;
    }
    
    /**
     * dat�̃��X���b�Z�[�W��HTML�\���p���b�Z�[�W�ɕϊ����ĕԂ�
     *
     * @access  private
     * @param   string   $msg
     * @param   integer  $resnum  ���X�ԍ�
     * @return  string   HTML
     */
    function transMsg($msg, $resnum)
    {
        global $_conf;
        
        $this->str_to_link_rest = $this->str_to_link_limit;
        
        // 2ch���`����dat
        if ($this->thread->dat_type == '2ch_old') {
            $msg = str_replace('���M', ',', $msg);
            $msg = preg_replace('/&amp([^;])/', '&$1', $msg);
        }

        // Safari���瓊�e���ꂽ�����N���`���_�̕��������␳
        //$msg = preg_replace('{(h?t?tp://[\w\.\-]+/)�`([\w\.\-%]+/?)}', '$1~$2', $msg);
        
        // DAT���ɂ���>>1�̃����NHTML����菜��
        $msg = $this->removeResAnchorTagInDat($msg);
        
        // 2ch�ł͂Ȃ���Ă��Ȃ��G�X�P�[�v�i�m�[�g���̌딽���΍���܂ށj
        // �{����2ch��DAT�����_�łȂ���Ă��Ȃ��ƃG�X�P�[�v�̐����������Ȃ��C������B
        //�iURL�����N�̃}�b�`�ŕ���p���o�Ă��܂��j
        //$msg = str_replace(array('"', "'"), array('&quot;', '&#039;'), $msg);
        
        // 2006/05/06 �m�[�g���̌딽���΍� body onload=window()
        $msg = str_replace('onload=window()', '<i>onload=window</i>()', $msg);
        
        // ���p��URL�Ȃǂ������N
        $msg = preg_replace_callback(
            $this->str_to_link_regex, array($this, 'link_callback'), $msg, $this->str_to_link_limit
        );
        
        // 2ch BE�A�C�R��
        if (in_array($_conf['show_be_icon'], array(1, 2))) {
            $msg = preg_replace(
                '{sssp://(img\\.2ch\\.net/ico/[\\w\\d()\\-]+\\.[a-z]+)}',
                '<img src="http://$1" border="0">', $msg
            );
        }
        
        return $msg;
    }

    // {{{ �R�[���o�b�N���\�b�h

    /**
     * �����N�Ώە�����̎�ނ𔻒肵�đΉ������֐�/���\�b�h�ɓn��
     *
     * @access  private
     * @return  string  HTML
     */
    function link_callback($s)
    {
        global $_conf;

        // preg_replace_callback()�ł͖��O�t���ŃL���v�`���ł��Ȃ��H
        if (!isset($s['link'])) {
            // $s[1] => "<a...>...</a>", $s[2] => "<a..>", $s[3] => "...", $s[4] => "</a>"
            $s['link']  = isset($s[1]) ? $s[1] : null;
            $s['quote'] = isset($s[5]) ? $s[5] : null;
            $s['url']   = isset($s[8]) ? $s[8] : null;
            $s['id']    = isset($s[11]) ? $s[11] : null;
        }

        // �}�b�`�����T�u�p�^�[���ɉ����ĕ���
        // �����N
        if ($s['link']) {
            if (preg_match('{ href=(["\'])?(.+?)(?(1)\\1)(?=[ >])}i', $s[2], $m)) {
                $url  = $m[2];
                $html = $s[3];
            } else {
                return $s[3];
            }

        // ���p
        } elseif ($s['quote']) {
            return preg_replace_callback(
                $this->getAnchorRegex('/(%prefix%)?(%a_range%)/'),
                array($this, 'quote_res_callback'), $s['quote'], $this->str_to_link_rest
            );

        // http or ftp ��URL
        } elseif ($s['url']) {
            $url  = preg_replace('/^t?(tps?)$/', 'ht$1', $s[9]) . '://' . $s[10];
            $html = $s['url'];

        // ID
        } elseif ($s['id'] && $_conf['flex_idpopup']) {
            return $this->idfilter_callback(array($s['id'], $s[12]));

        // ���̑��i�\���j
        } else {
            return strip_tags($s[0]);
        }
        
        // �ȉ��Aurl�P�[�X�̏���
        
        $url = P2Util::htmlEntityDecodeLite($url);
        
        // ime.nu���O��
        $url = preg_replace('|^([a-z]+://)ime\\.nu/|', '$1', $url);

        // URL���p�[�X
        $purl = @parse_url($url);
        if (!$purl || !isset($purl['host']) || !strstr($purl['host'], '.') || $purl['host'] == '127.0.0.1') {
            return $html;
        }

        // URL������
        foreach ($this->user_url_handlers as $handler) {
            if (false !== $linkHtml = call_user_func($handler, $url, $purl, $html, $this)) {
                return $linkHtml;
            }
        }
        foreach ($this->url_handlers as $handler) {
            if (false !== $linkHtml = call_user_func(array($this, $handler), $url, $purl, $html)) {
                return $linkHtml;
            }
        }

        return $html;
    }

    /**
     * ���p�ϊ��i�P�Ɓj�i2009/05/06 �͈͂������炩��j
     *
     * @access  private
     * @return  string  HTML
     */
    function quote_res_callback($s)
    {
        global $_conf;
        
        if (--$this->str_to_link_rest < 0) {
            return $s[0];
        }
        
        list($full, $qsign, $appointed_num) = $s;
        
        $appointed_num = mb_convert_kana($appointed_num, 'n'); // �S�p�����𔼊p�����ɕϊ�
        if (preg_match('/\\D/', $appointed_num)) {
            $appointed_num = preg_replace('/\\D+/', '-', $appointed_num);
            return $this->quote_res_range_callback(array($full, $qsign, $appointed_num));
        }
        if (preg_match('/^0/', $appointed_num)) {
            return $s[0];
        }
        
        $qnum = intval($appointed_num);
        if ($qnum < 1 || $qnum >= sizeof($this->thread->datlines)) {
            return $s[0];
        }
        
        // �������g�̔ԍ����ϊ������ɖ߂������Ƃ��낾��
        
        
        $read_url = P2Util::buildQueryUri($_conf['read_php'],
            array(
                'host' => $this->thread->host,
                'bbs'  => $this->thread->bbs,
                'key'  => $this->thread->key,
                'offline' => '1',
                'ls'   => $appointed_num // "{$appointed_num}n"
            )
        );
        
        $attributes = array();
        strlen($_conf['bbs_win_target']) and $attributes['target'] = $_conf['bbs_win_target'];
        if ($_conf['quote_res_view']) {
            $attributes = array_merge($attributes, array(
                'onmouseover' => "showResPopUp('q{$qnum}of{$this->thread->key}',event)",
                'onmouseout'  => "hideResPopUp('q{$qnum}of{$this->thread->key}')"
            ));
        }
        return P2View::tagA($read_url, "{$full}", $attributes);
    }

    /**
     * ���p�ϊ��i�͈́j
     *
     * @access  private
     * @return  string
     */
    function quote_res_range_callback($s)
    {
        global $_conf;
        
        list($full, $qsign, $appointed_num) = $s;
        
        if ($appointed_num == '-') {
            return $s[0];
        }

        $read_url = P2Util::buildQueryUri($_conf['read_php'],
            array(
                'host' => $this->thread->host,
                'bbs' => $this->thread->bbs,
                'key' => $this->thread->key,
                'offline' => '1',
                'ls' => "{$appointed_num}n"
            )
        );
        
        if ($_conf['iframe_popup']) {
            $pop_url = $read_url . "&renzokupop=true";
            return $this->iframePopup(
                array($read_url, $pop_url), $full,
                array('target' => $_conf['bbs_win_target']), 1
            );
        }

        // ���ʂɃ����N
        return  P2View::tagA($read_url, "{$full}", array('target' => $_conf['bbs_win_target']));

        // 1�ڂ����p���X�|�b�v�A�b�v
        /*
        $qnums = explode('-', $appointed_num);
        $qlink = $this->quote_res_callback(array($qsign.$qnum[0], $qsign, $qnum[0])) . '-';
        if (isset($qnums[1])) {
            $qlink .= $qnums[1];
        }
        return $qlink;
        */
    }

    /**
     * HTML�|�b�v�A�b�v�ϊ��i�R�[���o�b�N�p���\�b�h�j
     *
     * @access  private
     * @retrun  string
     */
    function iframePopupCallback($s)
    {
        return $this->iframePopup($s[1], $s[3], $s[2]);
    }

    /**
     * HTML�|�b�v�A�b�v�ϊ�
     *
     * @access  private
     * @param   array|string  $url
     * @param   array|string  $attr
     * @return  string  HTML
     */
    function iframePopup($url, $str, $attr = '', $mode = NULL)
    {
        global $_conf;

        // �����N�pURL�ƃ|�b�v�A�b�v�pURL
        if (is_array($url)) {
            $link_url = $url[0];
            $pop_url  = $url[1];
        } else {
            $link_url = $url;
            $pop_url  = $url;
        }

        // �����N������ƃ|�b�v�A�b�v�̈�
        if (is_array($str)) {
            $link_str = $str[0];
            $pop_str  = $str[1];
        } else {
            $link_str = $str;
            $pop_str  = NULL;
        }

        // �����N�̑���
        if (is_array($attr)) {
            $attrFor = $attr;
            $attr = '';
            foreach ($attrFor as $key => $value) {
                $attr .= sprintf(' %s="%s"', hs($key), hs($value));
            }
        } elseif ($attr !== '' && substr($attr, 0, 1) != ' ') {
            $attr = ' ' . $attr;
        }

        // �����N�̑�����HTML�|�b�v�A�b�v�p�̃C�x���g�n���h����������
        $pop_attr = $attr;
        $pop_attr .= " onmouseover=\"showHtmlPopUp('" . hs($pop_url) . "', event, " . hs($_conf['iframe_popup_delay']) . ")\"";
        $pop_attr .= " onmouseout=\"offHtmlPopUp()\"";

        // �ŏI����
        if (is_null($mode)) {
            $mode = $_conf['iframe_popup'];
        }
        if ($mode == 2 && !is_null($pop_str)) {
            $mode = 3;
        } elseif ($mode == 3 && is_null($pop_str)) {
            global $skin, $STYLE;
            $custom_pop_img = "skin/{$skin}/pop.png";
            if (file_exists($custom_pop_img)) {
                $pop_img = htmlspecialchars($custom_pop_img, ENT_QUOTES);
                $x = $STYLE['iframe_popup_mark_width'];
                $y = $STYLE['iframe_popup_mark_height'];
            } else {
                $pop_img = 'img/pop.png';
                $y = $x = 12;
            }
            $pop_str = "<img src=\"{$pop_img}\" width=\"{$x}\" height=\"{$y}\" hspace=\"2\" vspace=\"0\" border=\"0\" align=\"top\">";
        }

        /*
        if (preg_match('{^http}', $link_url)) {
            $class_snap = ' class="snap_preview"';
        } else {
            $class_snap = '';
        }
        */
        
        // (p)ID�|�b�v�A�b�v�œ���URL�̘A���Ăяo���Ȃ�(p)�ɂ��Ȃ�
        if (!empty($_GET['idpopup']) and isset($_SERVER['QUERY_STRING'])) {
            if ((basename(P2Util::getMyUrl()) . '?' . $_SERVER['QUERY_STRING']) == $link_url) {
                $mode = 0;
            }
        }
        
        $link_url_hs = hs($link_url);
        
        // �����N�쐬
        switch ($mode) {
            // �}�[�N����
            case 1:
                return "<a href=\"{$link_url_hs}\"{$pop_attr}>{$link_str}</a>";
            // (p)�}�[�N
            case 2:
                return "(<a href=\"{$link_url_hs}\"{$pop_attr}>p</a>)<a href=\"{$link_url_hs}\"{$attr}>{$link_str}</a>";
            // [p]�摜�A�T���l�C���Ȃ�
            case 3:
                return "<a href=\"{$link_url_hs}\"{$pop_attr}>{$pop_str}</a><a href=\"{$link_url_hs}\"{$attr}>{$link_str}</a>";
            // �|�b�v�A�b�v���Ȃ�
            default:
                return "<a href=\"{$link_url_hs}\"{$attr}>{$link_str}</a>";
        }
    }

    /**
     * ID�t�B���^�����O�|�b�v�A�b�v�ϊ�
     *
     * @access  private
     * @return  string  HTML
     */
    function idfilter_callback($s)
    {
        global $_conf;

        list($idstr, $id) = $s;
        // ID��8���܂���10��(+�g��/PC���ʎq)�Ɖ��肵��
        /*
        if (strlen($id) % 2 == 1) {
            $id = substr($id, 0, -1);
        }
        */
        $num_ht = '';
        if (isset($this->thread->idcount[$id]) && $this->thread->idcount[$id] > 0) {
            $num_ht = '(' . $this->thread->idcount[$id] . ')';
        } else {
            return $idstr;
        }

        $filter_url = P2Util::buildQueryUri(
            $_conf['read_php'],
            array(
                'bbs'     => $this->thread->bbs,
                'key'     => $this->thread->key,
                'host'    => $this->thread->host,
                'ls'      => 'all',
                'field'   => 'id',
                'word'    => $id,
                'method'  => 'just',
                'match'   => 'on',
                'idpopup' => '1',
                'offline' => '1'
            )
        );
        
        //$idstr = $this->coloredIdStr($idstr, $id);
        
        if ($_conf['iframe_popup']) {
            return $this->iframePopup($filter_url, $idstr, array('target' => $_conf['bbs_win_target'])) . $num_ht;
        }
        
        $attrs = array();
        if ($_conf['bbs_win_target']) {
            $attrs['target'] = $_conf['bbs_win_target'];
        }
        $atag = P2View::tagA(
            $filter_url, $idstr, $attrs
        );
        return "$atag{$num_ht}";
    }

    // }}}

    /**
     * Merged from http://jiyuwiki.com/index.php?cmd=read&page=rep2%A4%C7%A3%C9%A3%C4%A4%CE%C7%D8%B7%CA%BF%A7%CA%D1%B9%B9&alias%5B%5D=pukiwiki%B4%D8%CF%A2
     *
     * @access  private
     * @return  string
     */
    function coloredIdStr($idstr, $id)
    {
        global $STYLE;
        
        // [$id] >= 2�@�R�R�̐����ŃX���ɉ��ȏ㓯���h�c���o�����ɔw�i�F��ς��邩���܂�
        if (isset($this->thread->idcount[$id]) && $this->thread->idcount[$id] < 2) {
            return $idstr;
        }
        
        $raw = base64_decode(substr($id, 0, 8));

        $arr = unpack('V', substr($raw, 0, 4));
        
        // �F���F�l��0�`360�p�x�ŕ\���B�F������ɔz�u����30�����Ŏg�p�B
        // ���ʂ����F�����ʂ��₷���悤�ɗ׍����F�̍ʓx��ς��Ă���B
        $h = ($arr[1] & 0x3f)*360/30;
        $s = ($arr[1] & 0x03) *1; //�@�ʓx�F�l��0�i�W���j�`1�i�Z��)
        $v = 0.5; // ���x�F�l��0�i�Â��j�`1�i���邢�j
        // �F���@�ʓx�@���x�Ɋւ��Ă͈ȉ��Q�l�̎��@http://konicaminolta.jp/instruments/colorknowledge/part1/05.html

        // �ʂ́A�F����p�����[�^
        //$arr = unpack('V*',substr($id, 0, 8));
        //$h = floor(($arr[1] % 36)*360/36); // �F���F36����
        //$s = ($arr[1] % 3)>=1 ? 0.1 : 0.3; // �ʓx�F3�̏�]��1,2�̂Ƃ��͒W��,0�̂Ƃ��͏����Z������
        //$v =($arr[1] % 3)<=1 ? 1 : 0.8;    // ���x�F3�̏�]��0,1�̂Ƃ��͖��邳�ő�,2�̎��͂�����ƈÂ�����

        $hi = floor($h/60) % 6;
        $f = $h/60-$hi;
        $p = $v*(1-$s);
        $q = $v*(1-$f*$s);
        $t = $v*(1-(1-$f)*$s);

        switch ($hi) {
            case 0: $R=$v; $G=$t; $B=$p; break;
            case 1: $R=$q; $G=$v; $B=$p; break;
            case 2: $R=$p; $G=$v; $B=$t; break;
            case 3: $R=$p; $G=$q; $B=$v; break;
            case 4: $R=$t; $G=$p; $B=$v; break;
            case 5: $R=$v; $G=$p; $B=$q; break;
        }
        $R = floor($R*255);
        $G = floor($G*255);
        $B = floor($B*255);

        $uline = $STYLE['a_underline_none'] == 1 ? '' : "text-decoration:underline";
        return $idstr = "<span style=\"background-color:rgb({$R},{$G},{$B});{$uline}\">{$idstr}</span>";
    }

    // {{{ ���[�e�B���e�B���\�b�h

    /**
     * HTML���b�Z�[�W���̈��p���X�ԍ����ċA�`�F�b�N���A���������ԍ��̔z���Ԃ�
     *
     * @access  private
     * @param   integer     $res_num       �`�F�b�N�Ώۃ��X�̔ԍ�
     * @param   string|null $name          �`�F�b�N�Ώۃ��X�̖��O�i���t�H�[�}�b�g�̂��́j
     * @param   string|null $msg           �`�F�b�N�Ώۃ��X�̃��b�Z�[�W�i���t�H�[�}�b�g�̂��́j
     * @param   integer     $callLimit     �ċA�ł̌Ăяo��������
     * @param   integer     $nowDepth      ���݂̍ċA�̐[���i�}�j���A���w��͂��Ȃ��j
     * @return  array    �����������p���X�ԍ��̔z��
     */
    function checkQuoteResNums($res_num, $name, $msg, $callLimit = 20, $nowDepth = 0)
    {
        static $callTimes_ = 0;
        
        if (!$nowDepth) {
            $callTimes_ = 0;
        } else {
            $callTimes_++;
        }
        
        // �ċA�ł̌Ăяo��������
        if ($callTimes_ >= $callLimit) {
            return array();
        }
        
        if ($res_num > count($this->thread->datlines)) {
            return array();
        }
        
        $quote_res_nums = array();
        
        // name, msg �� null�w��Ȃ� datlines, res_num ����擾����
        if (is_null($name) || is_null($msg)) {
            $datalinear = $this->thread->explodeDatLine($this->thread->datlines[$res_num - 1]);
            if (is_null($name)) {
                $name = $datalinear[0];
            }
            if (is_null($msg)) {
                $msg = $datalinear[3];
            }
        }
        
        // {{{ ���O���`�F�b�N����
        
        if ($matches = $this->getQuoteResNumsName($name)) {
            
            foreach ($matches as $a_quote_res_num) {
            
                $quote_res_nums[] = $a_quote_res_num;

                // �������g�̔ԍ��Ɠ���łȂ����
                if ($a_quote_res_num != $res_num) {
                    // �`�F�b�N���Ă��Ȃ��ԍ����ċA�`�F�b�N
                    if (empty($this->quote_res_nums_checked[$a_quote_res_num])) {
                        $this->quote_res_nums_checked[$a_quote_res_num] = true;
                        $quote_res_nums = array_merge($quote_res_nums,
                            $this->checkQuoteResNums($a_quote_res_num, null, null, $callLimit, $nowDepth + 1)
                        );
                    }
                }
            }
        }
        
        // }}}
        // {{{ ���b�Z�[�W���`�F�b�N����
        
        $quote_res_nums_msg = $this->getQuoteResNumsMsg($msg);

        foreach ($quote_res_nums_msg as $a_quote_res_num) {

            $quote_res_nums[] = $a_quote_res_num;

            // �������g�̔ԍ��Ɠ���łȂ���΁A
            if ($a_quote_res_num != $res_num) {
                // �`�F�b�N���Ă��Ȃ��ԍ����ċA�`�F�b�N
                if (empty($this->quote_res_nums_checked[$a_quote_res_num])) {
                    $this->quote_res_nums_checked[$a_quote_res_num] = true;
                    $quote_res_nums = array_merge($quote_res_nums,
                        $this->checkQuoteResNums($a_quote_res_num, null, null, $callLimit, $nowDepth + 1)
                    );
                 }
             }

        }

        // }}}
        
        return array_unique($quote_res_nums);
    }
    
    // }}}
    // {{{ link_callback()����Ăяo�����URL�����������\�b�h

    // �����̃��\�b�h�͈����������Ώۃp�^�[���ɍ��v���Ȃ���FALSE��Ԃ��A
    // link_callback()��FALSE���Ԃ��Ă����$url_handlers�ɓo�^����Ă��鎟�̊֐�/���\�b�h�ɏ��������悤�Ƃ���B

    /**
     * �ʏ�URL�����N
     *
     * @access  private
     * @param   array   $purl  url��parse_url()��������
     * @return  string|false  HTML
     */
    function plugin_linkURL($url, $purl, $html)
    {
        global $_conf;

        if (isset($purl['scheme'])) {
            // ime
            $link_url = $_conf['through_ime'] ? P2Util::throughIme($url) : $url;

            // HTML�|�b�v�A�b�v
            // wikipedia.org �́A�t���[�����������Ă��܂��̂ŁA�ΏۊO�Ƃ���
            if ($_conf['iframe_popup'] && preg_match('/https?/', $purl['scheme']) && !preg_match('~wikipedia\.org~', $url)) {
                // p2pm �w��̏ꍇ�̂݁A���ʂ�m�w���ǉ�����
                if ($_conf['through_ime'] == 'p2pm') {
                    $pop_url = preg_replace('/\\?(enc=1&)url=/', '?$1m=1&url=', $link_url);
                } else {
                    $pop_url = $link_url;
                }
                $link = $this->iframePopup(array($link_url, $pop_url), $html, array('target' => $_conf['ext_win_target']));
            } else {
                $link = P2View::tagA($link_url, $html, array('target' => $_conf['ext_win_target']));
            }
            
            // {{{ �u���N���`�F�b�J
            
            if ($_conf['brocra_checker_use'] && preg_match('/https?/', $purl['scheme'])) {
                $brocra_checker_url = $_conf['brocra_checker_url'] . '?' . $_conf['brocra_checker_query'] . '=' . rawurlencode($url);
                // �u���N���`�F�b�J�Eime
                if ($_conf['through_ime']) {
                    $brocra_checker_url = P2Util::throughIme($brocra_checker_url);
                }
                // �u���N���`�F�b�J�EHTML�|�b�v�A�b�v
                if ($_conf['iframe_popup']) {
                    // p2pm �w��̏ꍇ�̂݁A���ʂ�m�w���ǉ�����
                    if ($_conf['through_ime'] == 'p2pm') {
                        $brocra_pop_url = preg_replace('/\\?(enc=1&)url=/', '?$1m=1&url=', $brocra_checker_url);
                    } else {
                        $brocra_pop_url = $brocra_checker_url;
                    }
                    $brocra_checker_link_tag = $this->iframePopup(
                        array($brocra_checker_url, $brocra_pop_url), hs('����'), $_conf['ext_win_target_at']
                    );
                } else {
                    $brocra_checker_link_tag = P2View::tagA(
                        $brocra_checker_url,
                        hs('����'),
                        array('target' => $_conf['ext_win_target'])
                    );
                }
                $link .= ' [' . $brocra_checker_link_tag . ']';
            }
            
            // }}}
            
            return $link;
        }
        return FALSE;
    }

    /**
     * 2ch, bbspink    �����N
     *
     * @access  private
     * @return  string|false  HTML
     */
    function plugin_link2chSubject($url, $purl, $html)
    {
        global $_conf;

        if (preg_match('{^http://(\\w+\\.(?:2ch\\.net|bbspink\\.com))/([^/]+)/$}', $url, $m)) {

            return sprintf('%s [%s]',
                P2View::tagA(
                    $url, $html, array('target' => 'subject')
                ),
                P2View::tagA(
                    P2Util::buildQueryUri($_conf['subject_php'], array('host' => $m[1], 'bbs' => $m[2])),
                    hs('��p2�ŊJ��'),
                    array('target' => 'subject')
                )
            );
            
        }
        return false;
    }

    /**
     * 2ch, bbspink    �X���b�h�����N
     *
     * @access  private
     * @return  string|false  HTML
     */
    function plugin_link2ch($url, $purl, $html)
    {
        global $_conf;

        // http://anchorage.2ch.net/test/read.cgi/occult/1238339367/
        // http://orz.2ch.io/p/-/tsushima.2ch.net/newsplus/1240991583/
        // http://c.2ch.net/test/-/occult/1229761545/i (���Ή�)

        if (preg_match('{^http://(orz\.2ch\.io/p/-/)?(\\w+\\.(?:2ch\\.net|bbspink\\.com))/(test/read\\.cgi/)?([^/]+)/([1-9]\\d+)(?:/([^/]+)?)?$}', $url, $m)) {
        
            if ($m[1] != '' xor $m[3] != '') {

                $ls = (!isset($m[6]) || $m[6] == 'i') ? '' : $m[6];
                $host = $m[2];
                $bbs  = $m[4];
                $key  = $m[5];
                $read_url = "{$_conf['read_php']}?host={$host}&bbs={$bbs}&key={$key}&ls={$ls}";
                
                if ($_conf['iframe_popup']) {
                    if (preg_match('/^[0-9\\-n]+$/', $ls)) {
                        $pop_url = $url;
                    } else {
                        $pop_url = $read_url . '&onlyone=true';
                    }
                    return $this->iframePopup(
                        array($read_url, $pop_url), $html, array('target' => $_conf['bbs_win_target'])
                    );
                }
                return P2View::tagA($read_url, $html, array('target' => $_conf['bbs_win_target']));
            }
        }
        return false;
    }

    /**
     * 2ch�ߋ����Ohtml
     *
     * @access  private
     * @return  string|false  HTML
     */
    function plugin_link2chKako($url, $purl, $html)
    {
        global $_conf;

        if (preg_match('{^http://(\\w+(?:\\.2ch\\.net|\\.bbspink\\.com))(?:/[^/]+/)?/([^/]+)/kako/\\d+(?:/\\d+)?/(\\d+)\\.html$}', $url, $m)) {
            $read_url = "{$_conf['read_php']}?host={$m[1]}&bbs={$m[2]}&key={$m[3]}&kakolog=" . rawurlencode($url);
            
            if ($_conf['iframe_popup']) {
                $pop_url = $read_url . '&onlyone=true';
                return $this->iframePopup(
                    array($read_url, $pop_url), $html, array('target' => $_conf['bbs_win_target'])
                );
            }
            return P2View::tagA($read_url, $html, array('target' => $_conf['bbs_win_target']));
        }
        return FALSE;
    }

    /**
     * �܂�BBS / JBBS���������  �������N
     *
     * @access  private
     * @return  string|false  HTML
     */
    function plugin_linkMachi($url, $purl, $html)
    {
        global $_conf;

        if (preg_match(
            '{^http://((\\w+\\.machibbs\\.com|\\w+\\.machi\\.to|jbbs\\.livedoor\\.(?:jp|com)|jbbs\\.shitaraba\\.com)(/\\w+)?)/bbs/read\\.(?:pl|cgi)\\?BBS=(\\w+)(?:&amp;|&)KEY=([0-9]+)(?:(?:&amp;|&)START=([0-9]+))?(?:(?:&amp;|&)END=([0-9]+))?(?=&|$)}',
            $url, $m
        )) {
            $start = isset($m[6]) ? $m[6] : null;
            $end   = isset($m[7]) ? $m[7] : null;
            $read_url = "{$_conf['read_php']}?host={$m[1]}&bbs={$m[4]}&key={$m[5]}";
            if ($start || $end) {
                $read_url .= "&ls={$start}-{$end}";
            }
            if ($_conf['iframe_popup']) {
                $pop_url = $url;
                return $this->iframePopup(
                    array($read_url, $pop_url), $html, array('target' => $_conf['bbs_win_target'])
                );
            }
            return P2View::tagA($read_url, $html, array('target' => $_conf['bbs_win_target']));
        }
        return FALSE;
    }

    /**
     * JBBS���������  �������N
     *
     * @access  private
     * @return  string|false  HTML
     */
    function plugin_linkJBBS($url, $purl, $html)
    {
        global $_conf;

        if (preg_match(
            '{^http://(jbbs\\.livedoor\\.(?:jp|com)|jbbs\\.shitaraba\\.com)/bbs/read\\.cgi/(\\w+)/(\\d+)/(\\d+)(?:/((\\d+)?-(\\d+)?|[^/]+)|/?)$}',
            $url, $m
        )) {
            $ls = isset($m[5]) ? $m[5] : null;
            $read_url = "{$_conf['read_php']}?host={$m[1]}/{$m[2]}&bbs={$m[3]}&key={$m[4]}&ls={$ls}";
            if ($_conf['iframe_popup']) {
                $pop_url = $url;
                return $this->iframePopup(
                    array($read_url, $pop_url), $html, array('target' => $_conf['bbs_win_target'])
                );
            }
            return P2View::tagA($read_url, $html, array('target' => $_conf['bbs_win_target']));
        }
        return FALSE;
    }
    
    /**
     * �O���� read.cgi �`�� �����N
     *
     * @access  private
     * @return  string|false  HTML
     */
    function plugin_linkReadCgi($url, $purl, $html)
    {
        global $_conf;

        // �O���� read.cgi �`�� http://ex14.vip2ch.com/test/read.cgi/operate/1161701941/ 
        if (preg_match('{http://([^/]+)/test/read\\.cgi/(\\w+)/(\\d+)/?([^/]+)?}', $url, $matches)) {
            $host = $matches[1];
            $bbs  = $matches[2];
            $key  = $matches[3];
            $ls   = geti($matches[4]);

            $read_url = "{$_conf['read_php']}?host={$host}&bbs={$bbs}&key={$key}&ls={$ls}";
            if ($_conf['iframe_popup']) {
                if (preg_match('/^[0-9\\-n]+$/', $ls)) {
                    $pop_url = $url;
                } else {
                    $pop_url = $read_url . '&onlyone=true';
                }
                return $this->iframePopup(
                    array($read_url, $pop_url), $html, array('target' => $_conf['bbs_win_target'])
                );
            }
            return P2View::tagA($read_url, $html, array('target' => $_conf['bbs_win_target']));
        }
        return FALSE;
    }
    
    /**
     * YouTube�����N�ϊ��v���O�C��
     *
     * [wish] YouTube API�𗘗p���āA�摜�T���l�C���݂̂ɂ�����
     *
     * 2007/06/25 YouTube �� API ���o�R�����ĂȂ��Ă��A�^�񒆂̃T���l�C���� 
     * http://img.youtube.com/vi/VIDEO_ID/2.jpg �ŃA�N�Z�X�ł���B 
     * 1.jpg �� 3.jpg �ƍ��킹�� 3 �����ׂĂ�������������Ȃ��B 
     *
     * @access  private
     * @return  string|false  HTML
     */
    function plugin_linkYouTube($url, $purl, $html)
    {
        global $_conf;

        // http://www.youtube.com/watch?v=Mn8tiFnAUAI
        // http://m.youtube.com/watch?v=OhcX0xJsDK8&client=mv-google&gl=JP&hl=ja&guid=ON&warned=True
        if (preg_match('{^http://(www|jp|m)\\.youtube\\.com/watch\\?(?:.+&amp;)?v=([0-9a-zA-Z_\\-]+)}', $url, $m)) {
            if ($m[1] == 'm') {
                $url = "http://www.youtube.com/watch?v={$m[2]}";
            }
            $url    = P2Util::throughIme($url);
            $url_hs = hs($url);
            $subd   = $m[1];
            $id     = $m[2];
            $atag   = P2View::tagA($url, $html, array('target' => $_conf['ext_win_target']));
            return <<<EOP
$atag<br>
<object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/{$id}"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/{$id}" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed></object>\n
EOP;
        }
        return FALSE;
    }
    
    /**
     * �j�R�j�R����ϊ��v���O�C��
     *
     * @access  private
     * @return  string|false  HTML
     */
    function plugin_linkNicoNico($url, $purl, $html)
    {
        global $_conf;

        // http://www.nicovideo.jp/watch?v=utbrYUJt9CSl0
        // http://www.nicovideo.jp/watch/utvWwAM30N0No
/*
<div style="width:318px; border:solid 1px #CCCCCC;"><iframe src="http://www.nicovideo.jp/thumb/utvWwAM30N0No" width="100%" height="198" scrolling="no" border="0" frameborder="0"></iframe></div>
*/
        if (preg_match('{^http://www\\.nicovideo\\.jp/watch(?:/|(?:\\?v=))([0-9a-zA-Z_-]+)}', $url, $m)) {
            //$url = P2Util::throughIme($url);
            //$url_hs = hs($url);
            $id = $m[1];
            return <<<EOP
<div style="width:318px; border:solid 1px #CCCCCC;"><iframe src="http://www.nicovideo.jp/thumb/{$id}" width="100%" height="198" scrolling="no" border="0" frameborder="0"></iframe></div>
EOP;
        }
        return FALSE;
    }
    
    // {{{ plugin_linkYourFileHost()

    /**
     * YourFileHost�ϊ��v���O�C��
     *
     * @param   string  $url
     * @param   array   $purl
     * @param   string  $html
     * @return  string|false  HTML
     */
    function plugin_linkYourFileHost($url, $purl, $html)
    {
        global $_conf;

        // http://www.yourfilehost.com/media.php?cat=video&file=hogehoge.wmv
        if (preg_match('{^http://www\\.yourfilehost\\.com/media\\.php\\?cat=video&file=([0-9A-Za-z_\\-\\.]+)}', $url, $m)) {
            $link_url = $_conf['through_ime'] ? P2Util::throughIme($url) : $url;

            if ($_conf['iframe_popup']) {
                $linkHtml = $this->iframePopup($link_url, $html, array('target' => $_conf['bbs_win_target']));
                
            } else {
                $linkHtml = P2View::tagA($link_url, $html, array('target' => $_conf['ext_win_target']));
            }

            $dl_url1 = "http://getyourfile.dyndns.tv/video?url=" . rawurlencode($url);
            $dl_url2 = "http://yourfilehostwmv.com/video?url=" . rawurlencode($url);
            if ($_conf['through_ime']) {
                $dl_url1 = P2Util::throughIme($dl_url1);
                $dl_url2 = P2Util::throughIme($dl_url2);
            }
            $dl_url1_atag = P2View::tagA($dl_url1,
                hs('GetYourFile'),
                array('target' => $_conf['ext_win_target'])
            );
            $dl_url2_atag = P2View::tagA($dl_url2,
                hs('GetWMV'),
                array('target' => $_conf['ext_win_target'])
            );
            
            return "{$linkHtml} [$dl_url1_atag][$dl_url2_atag]";
        }
        return FALSE;
    }
    
    // }}}

    /**
     * �摜�|�b�v�A�b�v�ϊ�
     *
     * @access  private
     * @return  string|false  HTML
     */
    function plugin_viewImage($url, $purl, $html)
    {
        global $_conf;

        // �\������
        if (!isset($GLOBALS['pre_thumb_limit']) && $_conf['pre_thumb_limit']) {
            $GLOBALS['pre_thumb_limit'] = $_conf['pre_thumb_limit'];
        }
        if (!$_conf['preview_thumbnail'] || empty($GLOBALS['pre_thumb_limit'])) {
            return false;
        }

        if (preg_match('{^https?://.+?\\.(jpe?g|gif|png)$}i', $url) && empty($purl['query'])) {
            $GLOBALS['pre_thumb_limit']--;
            
            $img_tag = sprintf(
                '<img class="thumbnail" src="%s" height="%s" weight="%s" hspace="4" vspace="4" align="middle">',
                hs($url),
                hs($_conf['pre_thumb_height']),
                hs($_conf['pre_thumb_width'])
            );
            
            switch ($_conf['iframe_popup']) {
                case 1:
                    $view_img_ht = $this->iframePopup(
                        $url, $img_tag . $html, array('target' => $_conf['ext_win_target'])
                    );
                    break;
                case 2: // (p)�̐ݒ肾���A�摜�T���l�C���𗘗p����
                    $view_img_ht = $this->iframePopup(
                        $url, array($html, $img_tag), array('target' => $_conf['ext_win_target'])
                    );
                    break;
                case 3: // p�摜�̐ݒ肾���A�摜�T���l�C���𗘗p����
                    $view_img_ht = $this->iframePopup(
                        $url, array($html, $img_tag), array('target' => $_conf['ext_win_target'])
                    );
                    break;
                default:
                    $view_img_ht = P2View::tagA($url, "{$img_tag}{$html}", array('target' => $_conf['ext_win_target']));
            }

            // �u���N���`�F�b�J �i�v���r���[�Ƃ͑��e��Ȃ��̂ŃR�����g�A�E�g�j
            /*
            if ($_conf['brocra_checker_use']) {
                $link_url_en = rawurlencode($url);
                $atag = P2View::tagA(
                    "{$_conf['brocra_checker_url']}?{$_conf['brocra_checker_query']}={$link_url_en}",
                    hs('�`�F�b�N')
                    array('target' => $_conf['ext_win_target'])
                );
                $view_img_ht .= " [$atag]";
            }
            */

            return $view_img_ht;
        }
        return false;
    }

    // }}}
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
