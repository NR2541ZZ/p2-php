<?php
// p2 �X���b�h�T�u�W�F�N�g�\���֐� �g�їp
// for subject.php

/**
 * sb_print - �X���b�h�ꗗ��\������ (<tr>�`</tr>)
 *
 * @return  void
 */
function sb_print_k(&$aThreadList)
{
    global $_conf, $sb_view, $p2_setting, $STYLE;
    global $sb_view;
    
    if (!$aThreadList->threads) {
        if ($aThreadList->spmode == "fav" && $sb_view == "shinchaku") {
            echo "<p>���C�ɃX���ɐV���Ȃ�������</p>";
        } else {
            echo "<p>�Y����޼ު�Ă͂Ȃ�������</p>";
        }
        return;
    }
    
    // �ϐ� ================================================
    
    // >>1
    $onlyone_bool = false;
    /*
    if (ereg("news", $aThreadList->bbs) || $aThreadList->bbs == "bizplus" || $aThreadList->spmode == "news") {
        // �q�ɂ͏���
        if ($aThreadList->spmode != "soko") {
            $onlyone_bool = true;
        }
    }
    */
    
    // ��
    if ($aThreadList->spmode and $aThreadList->spmode != "taborn" and $aThreadList->spmode != "soko") {
        $ita_name_bool = true;
    } else {
        $ita_name_bool = false;
    }

    /*
    // {{{ �V���\�[�g�i���ݖ��g�p�j
    
    $qs = array(
        'sort'      => 'midoku',
        'norefresh' => '1',
        UA::getQueryKey() => UA::getQueryValue()
    );
    // �X�y�V�������[�h��
    if ($aThreadList->spmode) {
        $qs['spmode'] = $aThreadList->spmode;
        // ���ځ[��Ȃ�
        if ($aThreadList->spmode == "taborn" or $aThreadList->spmode == "soko") {
            $qs['host'] = $aThreadList->host;
            $qs['bbs']  = $aThreadList->bbs;
        }
    } else {
        $qs['host'] = $aThreadList->host;
        $qs['bbs']  = $aThreadList->bbs;
    }

    $midoku_sort_ht = P2View::tagA(
        P2Util::buildQueryUri($_conf['subject_php'], $qs),
        '�V��'
    );

    // }}}
    */

    //=====================================================
    // �{�f�B
    //=====================================================

    // spmode������΃N�G���[�ǉ�
    if ($aThreadList->spmode) {$spmode_q = "&amp;spmode={$aThreadList->spmode}";}

    $i = 0;
    foreach ($aThreadList->threads as $aThread) {
    
        $i++;
        $midoku_ari = "";
        $anum_ht = ""; //#r1
        echo '<li>';
        $bbs_q = "&amp;bbs=" . $aThread->bbs;
        $key_q = "&amp;key=" . $aThread->key;

        if ($aThreadList->spmode!="taborn") {
            if (!$aThread->torder) {$aThread->torder=$i;}
        }

        // �V�����X�� =============================================
        $unum_ht = "";
        // �����ς�
        if ($aThread->isKitoku()) { 
            $unum_ht="{$aThread->unum}";
        
            $anum = $aThread->rescount - $aThread->unum +1 - $_conf['respointer'];
            if ($anum > $aThread->rescount) { $anum = $aThread->rescount; }
            $anum_ht = "#r{$anum}";
            
            // �V������
            if ($aThread->unum > 0) { 
                $midoku_ari = true;
                $unum_ht = "{$aThread->unum}";
            }
        
            // subject.txt�ɂȂ���
            if (!$aThread->isonline) {
                // �듮��h�~�̂��߃��O�폜��������b�N
                $unum_ht = "-"; 
            }    

            $unum_ht = '<font class="unum">' . $unum_ht . '</font>';
        }
        
        // �V�K�X��
        if ($aThread->new) { 
            //$unum_ht = "<font color=\"#0000ff\">��</font>";
            $unum_new_ht = "<img class=\"unew\" src=\"iui/icon_new.png\">";
            $unum_ht = "";
         }else{
            $unum_new_ht = "";
        }
                
        // �����X��
        $rescount_ht = "{$aThread->rescount}";

        // ��
        $ita_name_ht = '';
        if ($ita_name_bool) {
            $ita_name = $aThread->itaj ? $aThread->itaj : $aThread->bbs;
            
            // �S�p�p���J�i�X�y�[�X�𔼊p��
            if ($_conf['k_save_packet']) {
                $ita_name = mb_convert_kana($ita_name, 'rnsk');
            }
            
            $ita_name_hs = htmlspecialchars($ita_name, ENT_QUOTES);
            
            // $ita_name_ht = "(<a href=\"{$_conf['subject_php']}?host={$aThread->host}{$bbs_q}{$_conf['k_at_a']}\">{$ita_name_hs}</a>)";
            $ita_name_ht = "({$ita_name_hs})";
        }

        // torder(info) =================================================
        /*
        if ($aThread->fav) { //���C�ɃX��
            $torder_st = "<b>{$aThread->torder}</b>";
        } else {
            $torder_st = $aThread->torder;
        }
        $torder_ht = "<a id=\"to{$i}\" class=\"info\" href=\"info.php?host={$aThread->host}{$bbs_q}{$key_q}{$_conf['k_at_a']}\">{$torder_st}</a>";
        */
        $torder_ht = $aThread->torder;
        
        // title =================================================        
        $rescount_q = "&amp;rc=".$aThread->rescount;
        
        // dat�q�� or �a���Ȃ�
        if ($aThreadList->spmode == "soko" || $aThreadList->spmode == "palace") { 
            $rescount_q = "";
            $offline_q = "&amp;offline=true";
            $anum_ht = "";
        } else {
            $offline_q = '';
        }
        
        // �^�C�g�����擾�Ȃ�
        if (!$aThread->ttitle_ht) {
            // ��������̃^�C�g���Ȃ̂Ōg�ёΉ�URL�ł���K�v�͂Ȃ�
            //if (P2Util::isHost2chs($aThread->host)) {
            //    $aThread->ttitle_ht = "http://c.2ch.net/z/-/{$aThread->bbs}/{$aThread->key}/";
            //}else{
                $aThread->ttitle_ht = "http://{$aThread->host}/test/read.cgi/{$aThread->bbs}/{$aThread->key}/";        
            //}
        }    

        // �S�p�p���J�i�X�y�[�X�𔼊p��
        if ($_conf['k_save_packet']) {
            $aThread->ttitle_ht = mb_convert_kana($aThread->ttitle_ht, 'rnsk');
        }
        
        $aThread->ttitle_ht = $aThread->ttitle_ht . ' <font class="sbnum">' . $rescount_ht . "</font>";
        if ($aThread->similarity) {
            $aThread->ttitle_ht .= sprintf(' %0.1f%%', $aThread->similarity * 100);
        }
        
        // �V�K�X��
        if ($aThread->new) { 
            $classtitle_q = " class=\"thre_title_new\"";
        } else {
            $classtitle_q = " class=\"thre_title\"";
        }

        $thre_url = "{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}{$rescount_q}{$offline_q}{$_conf['k_at_a']}{$anum_ht}";
    
        // �I�����[>>1
        $onlyone_url = "{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}{$rescount_q}&amp;onlyone=true&amp;k_continue=1{$_conf['k_at_a']}";
        if ($onlyone_bool) {
            $one_ht = "<a href=\"{$onlyone_url}\">&gt;&gt;1</a>";
        }
        
        if (P2Util::isHost2chs($aThreadList->host) and !$aThread->isKitoku()) {
            if ($GLOBALS['_conf']['k_sb_show_first'] == 1) {
                $thre_url = $onlyone_url;
            } elseif ($GLOBALS['_conf']['k_sb_show_first'] == 2) {
                $thre_url .= '&amp;ls=1-';
            }
        }
        
        // �A�N�Z�X�L�[
        /*
        $access_ht = "";
        if ($aThread->torder >= 1 and $aThread->torder <= 9) {
            $access_ht = " {$_conf['accesskey']}=\"{$aThread->torder}\"";
        }
        */

        // ���C�Ƀ}�[�N�ݒ�
        $favdo      = !empty($aThread->fav) ? 0 : 1;
        $favtitle   = $favdo ? '���C�ɃX���ɒǉ�' : '���C�ɃX������O��';
        $itaj_hs = htmlspecialchars($aThread->itaj, ENT_QUOTES);

        $favmark    = !empty($aThread->fav) ? '��' : '+';
        if ($favmark  == '��') {
            $favmark = '<img src="iui/icon_del.png">';
        } else {
            $favmark = '<img src="iui/icon_add.png">';
        }

        $sid_qs = array();
        $sid_q = '';
        if (defined('SID')) {
            $sid_qs[session_name()] = session_id();
            $sid_q = hs('&' . session_name() . '=' . session_id());
        }
        
        $setFavUri = P2Util::buildQueryUri('info_i.php',
            array_merge(array(
                'host' => $aThread->host,
                'bbs'  => $aThread->bbs,
                'key'  => $aThread->key,
                'ttitle_en' => base64_encode($aThread->ttitle),
                'setfav' => $favdo
            ), $sid_qs)
        );
        $setFavUri_hs = hs($setFavUri);
        
        //====================================================================================
        // �X���b�h�ꗗ table �{�f�B HTML�v�����g <tr></tr> 
        //====================================================================================
        echo "<span class=\"plus\" id=\"{$aThread->torder}\" ><a href=\"{$setFavUri_hs}\" target=\"info\" onClick=\"return setFavJsNoStr('host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$sid_q}', '{$favdo}', {$STYLE['info_pop_size']}, 'read', 'this',{$aThread->torder});\" title=\"{$favtitle}\">{$favmark}</a></span>";

        // �{�f�B
        echo <<<EOP
 <a href="{$thre_url}">{$unum_new_ht}{$aThread->ttitle_ht}</a>{$ita_name_ht}{$unum_ht}</li>
EOP;
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
