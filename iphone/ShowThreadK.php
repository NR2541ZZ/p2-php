<?php
require_once P2_LIB_DIR . '/ShowThread.php';
require_once P2_LIB_DIR . '/StrSjis.php';

/**
 * p2 - �g�їp�ɃX���b�h��\������N���X
 */
class ShowThreadK extends ShowThread
{
    var $BBS_NONAME_NAME = '';
    
    /**
     * @constructor
     */
    function ShowThreadK(&$aThread)
    {
        parent::ShowThread($aThread);

        global $_conf;

        $this->url_handlers = array(
            array('this' => 'plugin_link2ch'),
            array('this' => 'plugin_linkMachi'),
            array('this' => 'plugin_linkJBBS'),
            array('this' => 'plugin_link2chKako'),
            array('this' => 'plugin_link2chSubject'),
            array('this' => 'plugin_linkReadCgi'),
        );
        if ($_conf['k_use_picto']) {
            $this->url_handlers[] = array('this' => 'plugin_viewImage');
        }
        $_conf['link_youtube']  and $this->url_handlers[] = array('this' => 'plugin_linkYouTube');
        $_conf['link_niconico'] and $this->url_handlers[] = array('this' => 'plugin_linkNicoNico');
        $this->url_handlers[] = array('this' => 'plugin_linkURL');
        
        if (!$_conf['k_bbs_noname_name'] and P2Util::isHost2chs($this->thread->host)) {
            require_once P2_LIB_DIR . '/SettingTxt.php';
            $st = new SettingTxt($this->thread->host, $this->thread->bbs);
            if (!empty($st->setting_array['BBS_NONAME_NAME'])) {
                $this->BBS_NONAME_NAME = $st->setting_array['BBS_NONAME_NAME'];
            }
        }
    }

    /**
     * Dat��HTML�ɕϊ��\������
     *
     * @access  public
     * @return  boolean
     */
    function datToHtml()
    {
        global $_conf;
        
        if (!$this->thread->resrange) {
            echo '<p><b>p2 error: {$this->resrange} is FALSE at datToHtml()</b></p>';
            return false;
        }

        $start = $this->thread->resrange['start'];
        $to = $this->thread->resrange['to'];
        $nofirst = $this->thread->resrange['nofirst'];
        
        // for �}���`���X�͈͂̃y�[�W�X�L�b�v
        if ($this->thread->resrange_multi and !isset($GLOBALS['_skip_resnum'])) {
            $page = isset($_REQUEST['page']) ? max(1, intval($_REQUEST['page'])) : 1;
            $GLOBALS['_skip_resnum'] = ($page - 1) * $GLOBALS['_conf']['k_rnum_range'];
            $this->thread->resrange_readnum = 0;
        }

        !isset($GLOBALS['_shown_resnum']) and $GLOBALS['_shown_resnum'] = 0;
        
        // 1��\���i�͈͊O�̃P�[�X������̂ł����Łj
        if (!$nofirst) {
            if ($this->thread->resrange_multi and $GLOBALS['_skip_resnum']) {
                $GLOBALS['_skip_resnum']--;
            } else {
                echo $this->transRes($this->thread->datlines[0], 1);
                $GLOBALS['_shown_resnum']++;
                
                if ($this->thread->resrange_readnum < $i) {
                    $this->thread->resrange_readnum = $i;
                }
                
            }
        }
        
        for ($i = $start; $i <= $to; $i++) {
            
            // �}���`���X�͈͂Ȃ�
            if ($this->thread->resrange_multi) {
            
                // �\�������߂Ȃ甲����
                if ($GLOBALS['_shown_resnum'] >= $GLOBALS['_conf']['k_rnum_range']) {
                    break;
                }
                
                // �\���͈͊O�Ȃ�X�L�b�v
                if (!$this->thread->inResrangeMulti($i)) {
                    continue;
                }
            }
            
            // 1���O�i�����Ŋ��\���Ȃ�X�L�b�v
            if (!$nofirst and $i == 1) {
                continue;
            }
            if (!$this->thread->datlines[$i - 1]) {
                break;
            }
            
            // �}���`���X�͈͂̃y�[�W�X�L�b�v
            if ($this->thread->resrange_multi and $GLOBALS['_skip_resnum']) {
                $GLOBALS['_skip_resnum']--;
                continue;
            }

            $res = $this->transRes($this->thread->datlines[$i - 1], $i);
            echo $res;
            
            if (!isset($GLOBALS['_read_new_html'])) {
                ob_flush();
                flush();
            }
            
            if (strlen($res)) {
                $GLOBALS['_shown_resnum']++;
            }
            
            if ($this->thread->resrange_readnum < $i) {
                $this->thread->resrange_readnum = $i;
            }
            
        }
        
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
        global $STYLE, $mae_msg, $res_filter, $word_fm;
        global $ngaborns_hits;
        global $_conf;
        
        $hr = P2View::getHrHtmlK();
        
        $tores      = '';
        $rpop       = '';
        
        $resar      = $this->thread->explodeDatLine($ares);
        $name       = $resar[0];
        $mail       = $resar[1];
        $date_id    = $resar[2];
        $msg        = $resar[3];

        if (!empty($this->BBS_NONAME_NAME) and $this->BBS_NONAME_NAME == $name) {
            $name = '';
        }

        // ���݂̔N���͏ȗ��J�b�g����B�i�ݒ�Łj�����̐擪0���J�b�g�B
        if ($_conf['k_date_zerosuppress']) {
            $date_id = preg_replace('~^(?:' . date('Y') . '|' . date('y') . ')/(?:0(\d)|(\d\d))?(?:(/)0)?~', '$1$2$3', $date_id);
        } else {
            $date_id = preg_replace('~^(?:' . date('Y') . '|' . date('y') . ')/~', '$1', $date_id);
        }
        
        // �j���Ǝ��Ԃ̊Ԃ��l�߂�
        $date_id = str_replace(') ', ')', $date_id);
        
        // �b���J�b�g
        if ($_conf['k_clip_time_sec']) {
            $date_id = preg_replace('/(\d\d:\d\d):\d\d(\.\d\d)?/', '$1', $date_id);
        }

        // {{{ �t�B���^�����O
        
        if (strlen($GLOBALS['word'])) {
            if (!strlen($GLOBALS['word_fm'])) {
                return '';
                
            // �^�[�Q�b�g�ݒ�
            } elseif (!$target = $this->getFilterTarget($i, $name, $mail, $date_id, $msg)) {
                return '';
                
            // �}�b�`���O
            } elseif (false === $this->filterMatch($target, $i)) {
                return '';
            }
        }
        
        // }}}
        // {{{ ���ځ[��`�F�b�N�i���O�A���[���AID�A���b�Z�[�W�j
        
        /*
        $aborned_res = "<div id=\"r{$i}\" name=\"r{$i}\">&nbsp;</div>\n"; // ���O
        $aborned_res .= ""; // ���e
        */
        $aborned_res = "<span id=\"r{$i}\" name=\"r{$i}\"></span>\n";
        
        if (false !== $this->checkAborns($name, $mail, $date_id, $msg)) {
            return $aborned_res;
        }
        
        // }}}
        // {{{ NG�`�F�b�N�i���O�A���[���AID�A���b�Z�[�W�j
        
        $isNgName = false;
        $isNgMail = false;
        $isNgId   = false;
        $isNgMsg  = false;
        
        if (empty($_GET['nong'])) {
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
        }
        
        // }}}

        // iPhone�pPopUp

        // {{{ ���X���|�b�v�A�b�v�\��
        
        if ($_conf['quote_res_view']) {
            $quote_res_nums = $this->checkQuoteResNums($i, $name, $msg);

            foreach ($quote_res_nums as $rnv) {
                if (empty($this->quote_res_nums_done[$rnv]) and $rnv < count($this->thread->datlines)) {
                    $ds = $this->qRes($this->thread->datlines[$rnv-1], $rnv, 'q' . $rnv . 'of' . $this->thread->key);
                    $onPopUp_at = " onMouseover=\"showResPopUp('q{$rnv}of{$this->thread->key}',event,true)\"";
                    $rpop .= "<span id=\"q{$rnv}of{$this->thread->key}\" class=\"respopup\"{$onPopUp_at}>" . $ds . "</span>\n";
                    $this->quote_res_nums_done[$rnv] = true;
                }
            }
        }
        
        //=============================================================
        // �܂Ƃ߂ďo��
        //=============================================================
        
        $name = $this->transName($name, $i); // ���OHTML�ϊ�
        
        $has_aa = 0; // 1:�㔽��, 2:�������iAA���j
        $msg = $this->transMsg($msg, $i, $has_aa); // ���b�Z�[�WHTML�ϊ�

        // BE�v���t�@�C�������N�ϊ�
        $date_id = $this->replaceBeId($date_id, $i);

        // NG�ϊ�
        $kakunin_msg_ht = P2View::tagA(
            P2Util::buildQueryUri($_conf['read_php'],
                array(
                    'host' => $this->thread->host,
                    'bbs'  => $this->thread->bbs,
                    'key'  => $this->thread->key,
                    'ls'   => $i,
                    'k_continue' => '1',
                    'nong' => '1',
                    UA::getQueryKey() => UA::getQueryValue()
                )
            ),
            '�m'
        );
        
        // NG���b�Z�[�W�ϊ�
        if ($isNgMsg) {
            $msg = sprintf('<s><font color="%s">NG:%s</font></s>', $STYLE['read_ngword'], hs($a_ng_msg));
            $msg .= ' ' . $kakunin_msg_ht;
        }
        
        // NG�l�[���ϊ�
        if ($isNgName) {
            $name = sprintf('<s><font color="%s">%s</font></s>', $STYLE['read_ngword'], $name);
            $msg = $kakunin_msg_ht;
        
        // NG���[���ϊ�
        } elseif ($isNgMail) {
            $mail = sprintf('<s><font color="%s">%s</font></s>', $STYLE['read_ngword'], $mail);
            $msg = $kakunin_msg_ht;

        // NGID�ϊ�
        } elseif ($isNgId) {
            $date_id = preg_replace(
                '|ID: ?([0-9A-Za-z/.+]{8,11})|',
                "<s><font color=\"{$STYLE['read_ngword']}\">\\0</font></s>",
                $date_id
            );
            // $date_id = sprintf('<s><font color="%s">%s</font></s>', $STYLE['read_ngword'], $date_id);
            
            $msg = $kakunin_msg_ht;
        }
        
        /*
        //�u��������V���v�摜��}��
        if ($i == $this->thread->readnum +1) {
            $tores .= "\n" . '<div><img src="img/image.png" alt="�V�����X" border="0" vspace="4"></div>' . "\n";
        }
        */
        
        $id = "qr{$i}of{$this->thread->key}";
        
        // iphone�p
        // �X�}�[�g�|�b�v�A�b�v���j���[�iread_new���͖��Ή��Ƃ���j
        if ($_conf['enable_spm'] and empty($GLOBALS['_read_new_html'])) {
            //$onPopUp_at = " onmouseover=\"showSPM({$this->thread->spmObjName},{$i},'{$id}',event,this)\" onmouseout=\"hideResPopUp('{$this->thread->spmObjName}_spm')\"";
            // onmouseout="hideResPopUp()" �͊O���K�v������
            
            $onPopUp_at = " onmouseover=\"showSPM({$this->thread->spmObjName},{$i},'{$id}',event,this)\"";
            $is = "<a href=\"javascript:void(0);\" class=\"resnum\"{$onPopUp_at}>{$i}</a>";
        } else {
            $is = $i;
        }
        // ���X�|�b�v�A�b�v�p���p
        $tores .= $rpop; 
        // �ԍ��i�I���U�t���C���j
        if ($this->thread->onthefly) {
            $GLOBALS['newres_to_show_flag'] = true;
            $tores .= "<div id=\"r{$i}\" name=\"r{$i}\">[<font color=\"#00aa00\">{$i}</font>]";
        // �ԍ��i�V�����X���j
        } elseif ($i > $this->thread->readnum) {
            $GLOBALS['newres_to_show_flag'] = true;
            $tores .= "<div id=\"r{$i}\" name=\"r{$i}\">[<font color=\"{$STYLE['read_newres_color']}\">{$is}</font>]";
        // �ԍ�
        } else {
            // iPhone�p�ɃN���X�ǉ�  thread �ȉ������l
            // ������class thread ���ǂ��ŗ��p����Ă���̂��킩��Ȃ��B�g��ꂢ�Ȃ��悤�ȋC������B
            // ������class����thread���g���̂͂ӂ��킵���Ȃ��̂ŁA�O�������Ƃ���B
            $tores .= "<div class=\"thread\" id=\"r{$i}\" name=\"r{$i}\">[{$is}]";
        }
        
        //$tores .= ' ';
        
        // ���O
        if (strlen($name)) {
            $tores .= '<span class="tname">' . $name . '</span>';
        }
        
        // ���[��
        $is_sage = false;
        if (strlen($mail)) {
            if ($mail == 'sage') {
                $is_sage = true;
            } else {
                //$tores .= $mail . " :";
                $tores .= ':<span class="tmail">' . StrSjis::fixSjis($mail). '</span>';
            }
        }
        
        if (strlen($name) > 0 or strlen($mail) > 0 && !$is_sage) {
            $tores .= ' ';
        }
        
        $no_trim_id_flag = false;
        
        // {{ ID�t�B���^
        
        if ($_conf['flex_idpopup'] == 1) {
            if (preg_match('|ID: ?([0-9a-zA-Z/.+]{8,11})|', $date_id, $matches)) {
                $id = $matches[1];
                if ($this->thread->idcount[$id] > 1) {
                    $date_id = preg_replace_callback(
                        '|ID: ?([0-9A-Za-z/.+]{8,11})|',
                        array($this, 'idfilter_callback'), $date_id
                    );
                } else {
                    if ($_conf['k_clip_unique_id']) {
                        $date_id = str_replace($matches[0], 'ID:' . substr($matches[0], -1, 1), $date_id);
                        $no_trim_id_flag = true;
                    }
                }
            }
        }
        
        // }}}
        
        if ($_conf['mobile.id_underline']) {
            $date_id = preg_replace(
                '!((?:ID: ?)| )([0-9A-Za-z/.+]{10}|[0-9A-Za-z/.+]{8}|\\?\\?\\?)?O(?=[^0-9A-Za-z/.+]|$)!',
                '$1$2<u>O</u>', $date_id
            );
        }

        if ($_conf['k_clip_unique_id']) {
            $date_id = str_replace('???', '?', $date_id);
        }
        
        if (!$no_trim_id_flag) {
            $date_id = preg_replace('/ID: ?/', '', $date_id);
        }
        
        $tores .= '<span class="tdate">' . $date_id . '</span>';
        
        if ($is_sage) {
            $tores .= '<font color="#aaaaaa">��</font>';
        }
        
        $tores .="<br>\n"; // ���t��ID
        
        $tores .= "{$msg}</div>$hr\n"; // ���e  // iPhone�p��hr�폜
        
        // �܂Ƃ߂ăt�B���^�F����
        if (strlen($GLOBALS['word_fm']) && $GLOBALS['res_filter']['match'] != 'off') {
            if (is_string($_conf['k_filter_marker'])) {
                $tores = StrCtl::filterMarking($GLOBALS['word_fm'], $tores, $_conf['k_filter_marker']);
            } else {
                $tores = StrCtl::filterMarking($GLOBALS['word_fm'], $tores);
            }
        }
        
        // �S�p�p���X�y�[�X�J�i�𔼊p��
        if ($_conf['k_save_packet']) {
            $tores = mb_convert_kana($tores, 'rnsk'); // SJIS-win ���� ask �� �� �� < �ɕϊ����Ă��܂��悤��
        }
        
        // 080809 �X�}�[�g�|�b�v�A�b�v�̔w�i�F�폜 iPhone�p
        $STYLE['respop_bgcolor'] = '';

        // {{{ iphone ���p���ă��X
        
        // ���X�ƈ��p���X���S��2�d�ɖ��ߍ��܂�Ă���B
        // ���ߍ��݂��Ȃ��ŃA�N�V�������������Ƃ��ɌĂяo�������B

		$quoteMsg = $msg;

		if (preg_match(
            '~(.*)<a href="[^"]+" target="_blank">(\&gt;)*([0-9]{1,4})</a>([\\x00-\\xff]+)~im',
            $msg, $matches)
        ){
			$quoteMsg = $matches[1] . "&gt;&gt;" . $matches[3] . $matches[4];
		}

		// �^�O�������s�Ƀ}�[�N���Ƃ�
        $quoteMsg = sprintf(
            '<span class="respopup" id="quote_msg%s">%s</span>',
            $i, str_replace('<br>', '___[br]___&gt;', nl2br($quoteMsg))
        );
        $tores = "\n" . $quoteMsg . "\n" . $tores;
        
		// }}}
        
        return $tores;
      
    }
    
    /**
     * ���X���pHTML�𐶐��擾����
     *
     * @access  private
     * @param   string   $resline
     * @return  string
     */
    function qRes($resline, $i, $hideid)
    {
        global $_conf;

        $resar      = $this->thread->explodeDatLine($resline);
        $name       = isset($resar[0]) ? $resar[0] : '';
        $mail       = isset($resar[1]) ? $resar[1] : '';
        $date_id    = isset($resar[2]) ? $resar[2] : '';
        $msg        = isset($resar[3]) ? $resar[3] : '';
        
        
        if (!empty($this->BBS_NONAME_NAME) and $this->BBS_NONAME_NAME == $name) {
            $name = '';
        }
        
        // ���݂̔N���͏ȗ��J�b�g����B�����̐擪0���J�b�g�B
        if ($_conf['k_date_zerosuppress']) {
            $date_id = preg_replace('~^(?:' . date('Y') . '|' . date('y') . ')/(?:0(\d)|(\d\d))?(?:(/)0)?~', '$1$2$3', $date_id);
        } else {
            $date_id = preg_replace('~^(?:' . date('Y') . '|' . date('y') . ')/~', '$1', $date_id);
        }
        
        // �j���Ǝ��Ԃ̊Ԃ��l�߂�
        $date_id = str_replace(') ', ')', $date_id);
        
        // �b���J�b�g
        if ($_conf['k_clip_time_sec']) {
            $date_id = preg_replace('/(\d\d:\d\d):\d\d(\.\d\d)?/', '$1', $date_id);
        }
        
        
        // ���ځ[��`�F�b�N
        if (false !== $this->checkAborns($name, $mail, $date_id, $msg)) {
            $name = $date_id = $msg = '���ځ[��';
            $mail = '';
        
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
            
            $has_aa = 0; // 1:�㔽��, 2:�������iAA���j
            $msg = $this->transMsg($msg, $i, $has_aa); // ���b�Z�[�W�ϊ�
        
            // BE�v���t�@�C�������N�ϊ�
            $date_id = $this->replaceBeId($date_id, $i);
            
            // NG�ϊ�
            $kakunin_msg_ht = P2View::tagA(
                P2Util::buildQueryUri($_conf['read_php'],
                    array(
                        'host' => $this->thread->host,
                        'bbs'  => $this->thread->bbs,
                        'key'  => $this->thread->key,
                        'ls'   => $i,
                        'k_continue' => '1',
                        'nong' => '1',
                        UA::getQueryKey() => UA::getQueryValue()
                    )
                ),
                '�m'
            );

            // NG���b�Z�[�W�ϊ�
            if ($isNgMsg) {
                $msg = sprintf('<s><font color="%s">NG:%s</font></s>', $STYLE['read_ngword'], hs($a_ng_msg));
                $msg .= ' ' . $kakunin_msg_ht;
			}

            // NG�l�[���ϊ�
            if ($isNgName) {
                $name = sprintf('<s><font color="%s">%s</font></s>', $STYLE['read_ngword'], $name);
                $msg = $kakunin_msg_ht;

            // NG���[���ϊ�
            } elseif ($isNgMail) {
                $mail = sprintf('<s><font color="%s">%s</font></s>', $STYLE['read_ngword'], $mail);
                $msg = $kakunin_msg_ht;

            // NGID�ϊ�
            } elseif ($isNgId) {
				$date_id = preg_replace('|ID: ?([0-9A-Za-z/.+]{8,11})|', "<s><font color=\"{$STYLE['read_ngword']}\">\\0</font></s>", $date_id);
                // $date_id = sprintf('<s><font color="%s">%s</font></s>', $STYLE['read_ngword'], $date_id);
                $msg = $kakunin_msg_ht;
            }
            

            
            // ID�t�B���^
            if ($_conf['flex_idpopup'] == 1) {
                if (preg_match('|ID: ?([0-9a-zA-Z/.+]{8,11})|', $date_id, $matches)) {
                    $id = $matches[1];
                    if ($this->thread->idcount[$id] > 1) {
                        $date_id = preg_replace_callback('|ID: ?([0-9A-Za-z/.+]{8,11})|', array($this, 'idfilter_callback'), $date_id);
                    } else {
	                    if ($_conf['k_clip_unique_id']) {
	                        $date_id = str_replace($matches[0], 'ID:' . substr($matches[0], -1, 1), $date_id);
	                        $no_trim_id_flag = true;
	                    }
                    }
                }
            }
        
        }
        
        // $tores�ɂ܂Ƃ߂ďo��
        //$tores = "<input type=\"submit\" value=\"����\" onClick=\"hideResPopUp('{$hideid}')\"><br>\n";
        $tores = "<img class=\"close\" src=\"iui/icon_close.png\" onClick=\"hideResPopUp('{$hideid}')\">\n";

        $tores .= "�@$i �F"; // �ԍ�
        
        // ���O
        //$tores .= "<b>$name</b> �F";
        if (strlen($name) > 0) {
            $tores .= '<span class="tname">' . $name . '</span> �F';
        }
        
        if ($mail) { $tores .= $mail . " �F"; } // ���[��
        
        if ($_conf['mobile.id_underline']) {
            $date_id = preg_replace(
                '!(ID: ?)([0-9A-Za-z/.+]{10}|[0-9A-Za-z/.+]{8}|\\?\\?\\?)?O(?=[^0-9A-Za-z/.+]|$)!',
                '$1$2<u>O</u>', $date_id
            );
        }
        
        if ($_conf['k_clip_unique_id']) {
            $date_id = str_replace('???', '?', $date_id);
        }
        
        if (!$no_trim_id_flag) {
            $date_id = preg_replace('/ID: ?/', '', $date_id);
        }
        
        $tores .= '<span class="tdate">' . $date_id . '</span>';
        /*
        if ($is_sage) {
            $tores .= '<font color="#aaaaaa">��</font>';
        }
        */
        
        $tores .="<br>\n";  // ���t��ID
        $tores .= "{$msg}\n"; // ���e  iPhone�p��hr�폜

        // �܂Ƃ߂ăt�B���^�F����
        if ($GLOBALS['word_fm'] && $GLOBALS['res_filter']['match'] != 'off') {
            if (is_string($_conf['k_filter_marker'])) {
                $tores = StrCtl::filterMarking($GLOBALS['word_fm'], $tores, $_conf['k_filter_marker']);
            } else {
                $tores = StrCtl::filterMarking($GLOBALS['word_fm'], $tores);
            }
        }
        
        // �S�p�p���X�y�[�X�J�i�𔼊p��
        if ($_conf['k_save_packet']) {
            $tores = mb_convert_kana($tores, 'rnsk'); // SJIS-win ���� ask �� �� �� < �ɕϊ����Ă��܂��悤��
        }
        
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
            $name   = rtrim($matches[1]);
            $nameID = trim(strip_tags($matches[2]));
        }
        
        // ���������p���X�|�b�v�A�b�v�����N��
        if ($_conf['quote_res_view']) {
            // </b>�`<b> �́A�z�X�g�i��g���b�v�j�Ȃ̂Ń}�b�`���Ȃ��悤�ɂ�����
            if ($name) {
                $name = preg_replace_callback(
                    $this->getAnchorRegex('/(?:^|%prefix%)%nums%/'),
                    array($this, 'quote_name_callback'), $name
                );
            }
        }
        
        // �ӂ����Ȃ���Ƃ��H
        $name = preg_replace('~</b>(.+?)<b>~', '<font color="#777777">$1</font>', $name);
        
        $name = StrSjis::fixSjis($name);
        
        if ($nameID) {
            $name = $name . $nameID;
        }
        
        return $name;
    }
    
    /**
     * @access  private
     * @return  string  HTML
     */
    function quote_msg_callback($s)
    {
        return preg_replace_callback(
            $this->getAnchorRegex('/(%prefix%)?(%a_range%)/'),
            array($this, 'quote_res_callback'), $s[0], $this->str_to_link_limit
        );
    }
    
    /**
     * dat�̃��X���b�Z�[�W��HTML�\���p���b�Z�[�W�ɕϊ����ĕԂ�
     *
     * @access  private
     * @param   string    $msg
     * @param   integer   $resnum  ���X�ԍ�
     * @param   ref bool  $has_aa  AA���܂�ł��邩�ǂ����B���̓n�����̓C�}�C�`�ہB���X�P�ʂŃI�u�W�F�N�g�ɂ��������������ȁB
     * @return  string  HTML
     */
    function transMsg($msg, $resnum, &$has_aa)
    {
        global $_conf;
        global $res_filter, $word_fm;
        
        $this->str_to_link_rest = $this->str_to_link_limit;
        
        $ryaku = false;

        // 2ch���`����dat
        if ($this->thread->dat_type == '2ch_old') {
            $msg = str_replace('���M', ',', $msg);
            $msg = preg_replace('/&amp([^;])/', '&$1', $msg);
        }

        // DAT���ɂ���>>1�̃����NHTML����菜��
        $msg = $this->removeResAnchorTagInDat($msg);
        
        // AA�`�F�b�N
        $has_aa = $this->detectAA($msg);
        
        // {{{ �傫������
        
        // AA�̋����ȗ��B
        $aa_ryaku_flag = false;
        if ($_conf['k_aa_ryaku_size'] && strlen($msg) > $_conf['k_aa_ryaku_size'] and $has_aa == 2) {
            $aa_ryaku_flag = true;
        }
        
        $ryaku_ht = null;
        
        if (
            !(UA::isIPhoneGroup() && !$aa_ryaku_flag)
            and empty($_GET['k_continue']) 
            and $_conf['ktai_res_size'] && strlen($msg) > $_conf['ktai_res_size'] || $aa_ryaku_flag
        ) {
            /*
            // <br>�ȊO�̃^�O���������A������؂�l�߂�
            $msg = strip_tags($msg, '<br>');
            if ($aa_ryaku_flag) {
                $ryaku_size = min($_conf['k_aa_ryaku_size'], $_conf['ktai_ryaku_size']);
                $ryaku_st = 'AA��';
            } else {
                $ryaku_size = $_conf['ktai_ryaku_size'];
                $ryaku_st = '��';
            }
            $msg = mb_strcut($msg, 0, $ryaku_size);
            $msg = preg_replace('/ *<[^>]*$/i', '', $msg);

            // >>1, >1, ��1, ����1�����p���X�|�b�v�A�b�v�����N��
            $msg = preg_replace_callback(
                $this->getAnchorRegex('/%full%/'), 
                array($this, 'quote_msg_callback'), $msg
            );
            */
            $ryaku_ht = P2View::tagA(
                P2Util::buildQueryUri($_conf['read_php'],
                    array(
                        'host' => $this->thread->host,
                        'bbs'  => $this->thread->bbs,
                        'key'  => $this->thread->key,
                        'ls'   => $resnum,
                        'k_continue' => '1',
                        'offline' => '1',
                        UA::getQueryKey() => UA::getQueryValue()
                    )
                ),
                $ryaku_st = 'AA�k��'
            );
            // $msg .= $ryaku_ht;
            //return $msg;
        }
        
        // }}}

        // ���p��URL�Ȃǂ������N
        $msg = preg_replace_callback(
            $this->str_to_link_regex, array($this, 'link_callback'), $msg, $this->str_to_link_limit
        );
        
        // 2ch BE�A�C�R��
        if (in_array($_conf['show_be_icon'], array(1, 3))) {
            $msg = preg_replace(
                '{sssp://(img\\.2ch\\.net/ico/[\\w\\d()\\-]+\\.[a-z]+)}',
                '<img src="http://$1" border="0">', $msg
            );
        }
        
        if (UA::isIPhoneGroup() && $ryaku_ht) {
            $msg = $ryaku_ht . '<br><span style="font-size:9px;"><pre>' . $msg . '</pre></span>';
        }
        
        return $msg;
    }

    // {{{ �R�[���o�b�N���\�b�h

    /**
     * �����N�Ώە�����̎�ނ𔻒肵�đΉ������֐�/���\�b�h�ɓn���ď�������
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
            $s['link']  = $s[1];
            $s['quote'] = $s[5];
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
            return $this->quote_msg_callback(array($s['quote']));

        // http or ftp ��URL
        } elseif ($s['url']) {
            if ($s[9] == 'ftp') {
                return $s[0];
            }
            $url  = preg_replace('/^t?(tps?)$/', 'ht$1', $s[9]) . '://' . $s[10];
            $html = $s['url'];

        // ID
        } elseif ($s['id'] && $_conf['flex_idpopup']) { // && $_conf['flex_idlink_k']
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
        foreach ($this->url_handlers as $handler) {
            if (isset($handler['this'])) {
                if (FALSE !== ($linkHtml = call_user_func(array($this, $handler['this']), $url, $purl, $html))) {
                    return $linkHtml;
                }
            } elseif (isset($handler['class']) && isset($handler['method'])) {
                if (FALSE !== ($linkHtml = call_user_func(array($handler['class'], $handler['method']), $url, $purl, $html))) {
                    return $linkHtml;
                }
            } elseif (isset($handler['function'])) {
                if (FALSE !== ($linkHtml = call_user_func($handler['function'], $url, $purl, $html))) {
                    return $linkHtml;
                }
            }
        }

        return $html;
    }

    /**
     * �g�їp�O��URL�ϊ�
     *
     * @access  private
     * @return  string  HTML
     */
    function ktai_exturl_callback($s)
    {
        global $_conf;
        
        $url = $s[1];
        
        $ext_pre_hts = array();

        // iPhone�p�@�ʑ��ϊ�
        $ext_pre_hts[] = P2View::tagA(
            $_conf['through_ime'] ? P2Util::throughIme($url) : $url,
            hs('��'),
            array('target' => '_blank')
        );
        
        $ext_pre_ht = '';
        if ($ext_pre_hts) {
            $ext_pre_ht = '(' . implode('|', $ext_pre_hts) . ')';
        }
        
        if ($_conf['through_ime']) {
            $url = P2Util::throughIme($url);
        }
        return $ext_pre_ht . '<a href="' . hs($url) . '">' . $s[2] . '</a>';
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
        if ($qnum < 1 || $qnum >= $this->thread->rescount) {
            return $s[0];
        }
        
        $read_url = P2Util::buildQueryUri($_conf['read_php'],
            array(
                'host' => $this->thread->host,
                'bbs'  => $this->thread->bbs,
                'key'  => $this->thread->key,
                'offline' => '1',
                'ls'   => $appointed_num, // "{$appointed_num}n"
                UA::getQueryKey() => UA::getQueryValue()
            )
        );
        
        $attributes = array();
        if ($_conf['quote_res_view']) {
            $attributes = array(
                'onmouseover' => "showResPopUp('q{$qnum}of{$this->thread->key}',event)",
                // d=1�̓_�~�[�B�Ȃ���return false�������ƌ��ʂ��Ȃ��悤�̂ŁB
                'onclick' => 'var dummy=1; return false;'
                //'onmouseout'  => "hideResPopUp('q{$qnum}of{$this->thread->key}')"
            );
        }
        return P2View::tagA($read_url, "{$full}", $attributes);
    }

    /**
     * ���p�ϊ��i�͈́j
     *
     * @access  private
     * @return  string  HTML
     */
    function quote_res_range_callback($s)
    {
        global $_conf;
        
        list($full, $qsign, $appointed_num) = $s;
        
        if ($appointed_num == '-') {
            return $s[0];
        }

        list($from, $to) = explode('-', $appointed_num);
        if (!$from) {
            $from = 1;
        } elseif ($from < 1 || $from > $this->thread->rescount) {
            return $s[0];
        }
        // read.php�ŕ\���͈͂𔻒肷��̂ŏ璷�ł͂���
        if (!$to) {
            $to = min($from + $_conf['k_rnum_range'] - 1, $this->thread->rescount);
        } else {
            $to = min($to, $from + $_conf['k_rnum_range'] - 1, $this->thread->rescount);
        }

        $read_url = P2Util::buildQueryUri($_conf['read_php'],
            array(
                'host' => $this->thread->host,
                'bbs'  => $this->thread->bbs,
                'key'  => $this->thread->key,
                'offline' => '1',
                'ls'   => "{$from}-{$to}",
                UA::getQueryKey() => UA::getQueryValue()
            )
        );

        return sprintf('<a href="%s">%s</a>',
            hs($read_url), $full
        );
    }

    /**
     * ID�t�B���^�����O�����N�ϊ�
     *
     * @access  private
     * @return  string  HTML
     */
    function idfilter_callback($s)
    {
        global $_conf;

        $idstr  = $s[0]; // ID:xxxxxxxxxx
        $id     = $s[1]; // xxxxxxxxxx
        $idflag = '';    // �g��/PC���ʎq
        
        // ID��8���܂���10��(+�g��/PC���ʎq)�Ɖ��肵��
        /*
        if (strlen($id) % 2 == 1) {
            $id = substr($id, 0, -1);
            $idflag = substr($id, -1);
        } elseif (isset($s[2])) {
            $idflag = $s[2];
        }
        */
        
        if (isset($this->thread->idcount[$id]) && $this->thread->idcount[$id] > 0) {
            $filter_url = P2Util::buildQueryUri($_conf['read_php'],
                array(
                    'host' => $this->thread->host,
                    'bbs'  => $this->thread->bbs,
                    'key'  => $this->thread->key,
                    'ls'   => 'all',
                    'offline' => '1',
                    'idpopup' => '1',
                    'field'   => 'id',
                    'method'  => 'just',
                    'match'   => 'on',
                    'word'    => $id,
                    UA::getQueryKey() => UA::getQueryValue()
                )
            );
            $num_ht = sprintf('(<a href="%s">%s</a>)',
                hs($filter_url), $this->thread->idcount[$id]
            );
            return "{$idstr}{$num_ht}";
        }
        return $idstr;
    }

    // }}}
    // {{{ link_callback()����Ăяo�����URL�����������\�b�h

    // �����̃��\�b�h�͈����������Ώۃp�^�[���ɍ��v���Ȃ���FALSE��Ԃ��A
    // link_callback()��FALSE���Ԃ��Ă����$url_handlers�ɓo�^����Ă��鎟�̊֐�/���\�b�h�ɏ��������悤�Ƃ���B

    /**
     * URL�����N
     *
     * @access  private
     * @return  string|false  HTML
     */
    function plugin_linkURL($url, $purl, $html)
    {
        global $_conf;

        if (isset($purl['scheme'])) {
            
            // �g�їp�O��URL�ϊ�
            return $this->ktai_exturl_callback(array('', $url, $html));
            
            // ime
            $link_url = $_conf['through_ime'] ? P2Util::throughIme($url) : $url;
            
            return sprintf(
                '<a href="%s">%s</a>',
                hs($link_url), $html
            );
        }
        return false;
    }

    /**
     * 2ch bbspink �����N
     *
     * @access  private
     * @return  string|false  HTML
     */
    function plugin_link2chSubject($url, $purl, $html)
    {
        global $_conf;

        if (preg_match('{^http://(\\w+\\.(?:2ch\\.net|bbspink\\.com))/([^/]+)/$}', $url, $m)) {
            $subject_url = "{$_conf['subject_php']}?host={$m[1]}&bbs={$m[2]}&b={$_conf['b']}";
            return sprintf(
                '<a href="%s">%s</a> [<a href="%s">��p2�ŊJ��</a>]',
                hs($url), $html, hs($subject_url)
            );
        }
        return false;
    }

    /**
     * 2ch bbspink �X���b�h�����N
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

                return sprintf('<a href="%s%s">%s</a>',
                    hs($read_url), $_conf['k_at_a'], $html
                );
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
            return sprintf('<a href="%s%s">%s</a>',
                hs($read_url), $_conf['k_at_a'], $html
            );
        }
        return false;
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

        if (preg_match('{^http://((\\w+\\.machibbs\\.com|\\w+\\.machi\\.to|jbbs\\.livedoor\\.(?:jp|com)|jbbs\\.shitaraba\\.com)(/\\w+)?)/bbs/read\\.(?:pl|cgi)\\?BBS=(\\w+)(?:&amp;|&)KEY=([0-9]+)(?:(?:&amp;|&)START=([0-9]+))?(?:(?:&amp;|&)END=([0-9]+))?(?=&|$)}', $url, $m)) {
            $read_url = "{$_conf['read_php']}?host={$m[1]}&bbs={$m[4]}&key={$m[5]}";
            if ($m[6] || $m[7]) {
                $read_url .= "&ls={$m[6]}-{$m[7]}";
            }
            return sprintf('<a href="%s%s">%s</a>',
                hs($read_url), $_conf['k_at_a'], $html
            );
        }
        return false;
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

        if (preg_match('{^http://(jbbs\\.livedoor\\.(?:jp|com)|jbbs\\.shitaraba\\.com)/bbs/read\\.cgi/(\\w+)/(\\d+)/(\\d+)(?:/((\\d+)?-(\\d+)?|[^/]+)|/?)$}', $url, $m)) {
            $read_url = "{$_conf['read_php']}?host={$m[1]}/{$m[2]}&bbs={$m[3]}&key={$m[4]}&ls={$m[5]}";
            return sprintf('<a href="%s%s">%s</a>',
                hs($read_url), $_conf['k_at_a'], $html
            );
        }
        return false;
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
            $ls   = $matches[4];
            
            $read_url = "{$_conf['read_php']}?host={$host}&bbs={$bbs}&key={$key}&ls={$ls}";
            return sprintf('<a href="%s%s">%s</a>',
                hs($read_url), $_conf['k_at_a'], $html
            );
        }
        return false;
    }
    
    /**
     * �摜�|�b�v�A�b�v�ϊ�
     *
     * @access  private
     * @return  string|false  HTML
     */
    // iPhone�p�ɃT���l�C���ɂ��Ă݂�
    function plugin_viewImage($url, $purl, $html)
    {
        global $_conf;
        
        // �\������
        if (!isset($GLOBALS['pre_thumb_limit']) && isset($_conf['pre_thumb_limit'])) {
            $GLOBALS['pre_thumb_limit'] = $_conf['pre_thumb_limit'];
        }
        if (!$_conf['preview_thumbnail'] || empty($GLOBALS['pre_thumb_limit'])) {
            return false;
        }
        if (preg_match('{^https?://.+?\\.(jpe?g|gif|png)$}i', $url) && empty($purl['query'])) {
            
            //$picto_url = 'http://pic.to/' . $purl['host'] . $purl['path'];
            $picto_url = 'http://' . $purl['host'] . $purl['path'];
            
            //���������ǂ���@080728
            $picto_tag = '<a href="' . hs($picto_url) . '" target="_blank"><img src="' . hs($url) . '"></a> ';
            
            if ($_conf['through_ime']) {
                $link_url  = P2Util::throughIme($url);
                $picto_url = P2Util::throughIme($picto_url);
            } else {
                $link_url = $url;
            }
            // $html �� URL��\�����Ă��邪�Ah�������Ă����肷��
            return sprintf('%s<a href="%s">%s</a>',
                $picto_tag, hs($link_url), $html
            );
        }
        return false;
    }
    
    /**
     * YouTube�����N�ϊ��v���O�C��
     * [wish] YouTube API�𗘗p���āA�摜�T���l�C���݂̂ɂ�����
     *
     * @access  private
     * @return  string|false
     */
    function plugin_linkYouTube($url, $purl, $html)
    {
        global $_conf;

        // http://www.youtube.com/watch?v=Mn8tiFnAUAI
        // http://m.youtube.com/watch?v=OhcX0xJsDK8&client=mv-google&gl=JP&hl=ja&guid=ON&warned=True
        if (preg_match('{^http://(www|jp|m)\\.youtube\\.com/watch\\?(?:.+&amp;)?v=([0-9a-zA-Z_\\-]+)}', $url, $m)) {
            /*
            if ($m[1] == 'm') {
                $url = "http://www.youtube.com/watch?v={$m[2]}";
            }
            $url = P2Util::throughIme($url);
            */
            return sprintf(
                '<a href="youtube:%s"><img src="http://i.ytimg.com/vi/%s/default.jpg">%s</a><br>',
                hs($m[2]), hs($m[2]), $html
            );
        }
        return false;
    }
    // }}}

    /**
     * �j�R�j�R����ϊ��v���O�C��
     *
     * @access  private
     * @return  string|false
     */
    // iflame �ł��\���ł��邪�t�b�^�Əd�Ȃ������ɕs�����
    // �摜�T���l�C���̂ݕ\��
    function plugin_linkNicoNico($url, $purl, $html)
    {
        global $_conf;

        // http://www.nicovideo.jp/watch?v=utbrYUJt9CSl0
        // http://www.nicovideo.jp/watch/utvWwAM30N0No
/*
<div style="width:318px; border:solid 1px #CCCCCC;"><iframe src="http://www.nicovideo.jp/thumb?v=utvWwAM30N0No" width="100%" height="198" scrolling="no" border="0" frameborder="0"></iframe></div>
*/
        if (preg_match('{^http://www\\.nicovideo\\.jp/watch(?:/|(?:\\?v=))([0-9a-zA-Z_-]+)}', $url, $m)) {
            //$url = P2Util::throughIme($url);
            $url_hs = hs($url);
            $id = $m[1];
            $ids = str_replace( 'sm', '', $id);
            $ids = str_replace( 'nm', '', $ids);
            $id_hs  = hs($id);
            $ids_hs = hs($ids);
return <<<EOP
<a href="mailto:?subject=rep2iPhone ����j�R�j�R&body=http:%2F%2Fwww.nicovideo.jp%2Fwatch%2F{$id_hs}"><img class="nico" src="http://tn-skr.smilevideo.jp/smile?i={$ids_hs}"></a>
<a href="$url_hs" target="_blank">{$html}</a>
EOP;
        }
        return false;
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
