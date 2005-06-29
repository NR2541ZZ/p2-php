<?php
/*
    p2 -  �X���b�h�T�u�W�F�N�g�\���X�N���v�g
    �t���[��������ʁA�E�㕔��

    �V������m�邽�߂Ɏg�p���Ă��� // $shinchaku_num, $_newthre_num ���Z�b�g

    subject.php �ƌZ��Ȃ̂ňꏏ�ɖʓ|���݂�
*/

include_once './conf/conf.inc.php';  // ��{�ݒ�
require_once (P2_LIBRARY_DIR . '/threadlist.class.php');
require_once (P2_LIBRARY_DIR . '/thread.class.php');
require_once (P2_LIBRARY_DIR . '/filectl.class.php');

$shinchaku_num = 0;
if ($aThreadList) {
    unset($aThreadList);
}


//============================================================
// ���ϐ��ݒ�
//============================================================

if (isset($_GET['from'])) { $sb_disp_from = $_GET['from']; }
if (isset($_POST['from'])) { $sb_disp_from = $_POST['from']; }
if (!isset($sb_disp_from)) { $sb_disp_from = 1; }

// �� p2_setting �ݒ� ======================================
if ($spmode) {
    $p2_setting_txt = $_conf['pref_dir']."/p2_setting_".$spmode.".txt";
} else {
    $idx_host_dir = P2Util::idxDirOfHost($host);
    $idx_bbs_dir_s = $idx_host_dir . '/' . $bbs . '/';

    $p2_setting_txt = $idx_bbs_dir_s . "p2_setting.txt";
    $sb_keys_b_txt = $idx_bbs_dir_s . "p2_sb_keys_b.txt";
    $sb_keys_txt = $idx_bbs_dir_s . "p2_sb_keys.txt";

    if (!empty($_REQUEST['norefresh']) || !empty($_REQUEST['word'])) {
        if ($prepre_sb_cont = @file_get_contents($sb_keys_b_txt)) {
            $prepre_sb_keys = unserialize($prepre_sb_cont);
        }
    } else {
        if ($pre_sb_cont = @file_get_contents($sb_keys_txt)) {
            $pre_sb_keys = unserialize($pre_sb_cont);
        }
    }
        
}

// ��p2_setting �ǂݍ���
$p2_setting_cont = @file_get_contents($p2_setting_txt);
if ($p2_setting_cont) {$p2_setting = unserialize($p2_setting_cont);}

$viewnum_pre = $p2_setting['viewnum'];
$sort_pre = $p2_setting['sort'];
$itaj_pre = $p2_setting['itaj'];

if (isset($_GET['sb_view'])) { $sb_view = $_GET['sb_view']; }
if (isset($_POST['sb_view'])) { $sb_view = $_POST['sb_view']; }
if (!$sb_view) {$sb_view = "normal";}

if (isset($_GET['viewnum'])) { $p2_setting['viewnum'] = $_GET['viewnum']; }
if (isset($_POST['viewnum'])) { $p2_setting['viewnum'] = $_POST['viewnum']; }
if (!$p2_setting['viewnum']) { $p2_setting['viewnum'] = $_conf['display_threads_num']; } // �f�t�H���g�l


if (isset($_GET['itaj_en'])) { $p2_setting['itaj'] = base64_decode($_GET['itaj_en']); }

// ���\���X���b�h�� ====================================
$threads_num_max = 2000;

if (!$spmode || $spmode=="news") {
    $threads_num = $p2_setting['viewnum'];
} elseif ($spmode == "recent") {
    $threads_num = $_conf['rct_rec_num'];
} elseif ($spmode == "res_hist") {
    $threads_num = $_conf['res_hist_rec_num'];
} else {
    $threads_num = 2000;
}

if ($p2_setting['viewnum'] == "all") {$threads_num = $threads_num_max;}
elseif ($sb_view == "shinchaku") {$threads_num = $threads_num_max;}
elseif ($sb_view == "edit") {$threads_num = $threads_num_max;}
elseif ($_GET['word']) {$threads_num = $threads_num_max;}
elseif ($_conf['ktai']) {$threads_num = $threads_num_max;}

// submit ==========================================
if (isset($_GET['submit'])) {
    $submit = $_GET['submit'];
} elseif (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
}

$abornoff_st = '���ځ[�����';
$deletelog_st = '���O���폜';

$nowtime = time();

//============================================================
// �����C��
//============================================================

$aThreadList =& new ThreadList();

// ���ƃ��[�h�̃Z�b�g ===================================
if ($spmode) {
    if ($spmode == "taborn" or $spmode == "soko") {
        $aThreadList->setIta($host, $bbs, P2Util::getItaName($host, $bbs));
    }
    $aThreadList->setSpMode($spmode);
} else {
    // if(!$p2_setting['itaj']){$p2_setting['itaj'] = P2Util::getItaName($host, $bbs);}
    $aThreadList->setIta($host, $bbs, $p2_setting['itaj']);
    
    // {{{ ���X���b�h���ځ[�񃊃X�g�Ǎ�
    
    $idx_host_dir = P2Util::idxDirOfHost($aThreadList->host);
    $taborn_file = $idx_host_dir.'/'.$aThreadList->bbs.'/p2_threads_aborn.idx';

    if ($tabornlines = @file($taborn_file)) {
        $ta_num = sizeof($tabornlines);
        foreach ($tabornlines as $l) {
            $data = explode('<>', rtrim($l));
            $ta_keys[ $data[1] ] = true;
        }
    }
    
    // }}}

}

// ���\�[�X���X�g�Ǎ�
$lines = $aThreadList->readList();

// �����C�ɃX�����X�g �Ǎ�
$favlines = @file($_conf['favlist_file']);
if (is_array($favlines)) {
    foreach ($favlines as $l) {
        $data = explode('<>', rtrim($l));
        $fav_keys[ $data[1] ] = true;
    }
}

//============================================================
// �����ꂼ��̍s���
//============================================================

$linesize = sizeof($lines);

for ($x = 0; $x < $linesize ; $x++) {

    $l = rtrim($lines[$x]);
    
    $aThread =& new Thread();
    
    if ($aThreadList->spmode != "taborn" and $aThreadList->spmode != "soko") {
        $aThread->torder = $x + 1;
    }

    // ���f�[�^�ǂݍ���
    if ($aThreadList->spmode) {
        switch ($aThreadList->spmode) {
        case "recent": // ����
            $aThread->getThreadInfoFromExtIdxLine($l);
            $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
            if (!$aThread->itaj) {$aThread->itaj=$aThread->bbs;}
            break;
        case "res_hist": // �������ݗ���
            $aThread->getThreadInfoFromExtIdxLine($l);
            $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
            if (!$aThread->itaj) {$aThread->itaj=$aThread->bbs;}
            break;
        case "fav": // ���C��
            $aThread->getThreadInfoFromExtIdxLine($l);
            $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
            if (!$aThread->itaj) {$aThread->itaj=$aThread->bbs;}
            break;
        case "taborn":    // �X���b�h���ځ[��
            $la = explode("<>", $l);
            $aThread->key = $la[1];
            $aThread->host = $aThreadList->host;
            $aThread->bbs = $aThreadList->bbs;
            break;
        case "soko":    // dat�q��
            $la = explode("<>", $l);
            $aThread->key = $la[1];
            $aThread->host = $aThreadList->host;
            $aThread->bbs = $aThreadList->bbs;
            break;
        case "palace":    // �X���̓a��
            $aThread->getThreadInfoFromExtIdxLine($l);
            $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
            if (!$aThread->itaj) {$aThread->itaj=$aThread->bbs;}
            break;
        case "news": // �j���[�X�̐���
            $aThread->isonline = true;
            $aThread->key = $l['key'];
            $aThread->setTtitle($l['ttitle']);
            $aThread->rescount = $l['rescount'];
            $aThread->host = $l['host'];
            $aThread->bbs = $l['bbs'];

            $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
            if (!$aThread->itaj) {$aThread->itaj=$aThread->bbs;}
            break;
        }
    // subject (not spmode)
    } else {
        $aThread->getThreadInfoFromSubjectTxtLine($l);
        $aThread->host = $aThreadList->host;
        $aThread->bbs = $aThreadList->bbs;
    }

    // host��bbs��key���s���Ȃ�X�L�b�v
    if (!($aThread->host && $aThread->bbs && $aThread->key)) {
        unset($aThread);
        continue;
    } 
    
    // {{{ �V�������ǂ���(for subject)
    
    if (!$aThreadList->spmode) {
        if (!empty($_REQUEST['norefresh']) || !empty($_REQUEST['word'])) {
            if (!$prepre_sb_keys[$aThread->key]) { $aThread->new = true; }
        } else {
            if (!$pre_sb_keys[$aThread->key]) { $aThread->new = true; }
            $subject_keys[$aThread->key] = true;
        }
    }
    
    // }}}
    // {{{ �����[�h�t�B���^(for subject)
    
    $debug && $profiler->enterSection('word_filter_for_sb');
    if (!$aThreadList->spmode || $aThreadList->spmode == "news" and $word_fm) {
        $target = $aThread->ttitle;
        if (!StrCtl::filterMatch($word_fm, $target)) {
            unset($aThread);
            continue;
        } else {
            $GLOBALS['sb_mikke_num']++;
            if ($_conf['ktai']) {
                $aThread->ttitle_ht = $aThread->ttitle;
            } else {
                $aThread->ttitle_ht = StrCtl::filterMarking($word_fm, $aThread->ttitle);
            }
        }
    }
    $debug && $profiler->leaveSection('word_filter_for_sb');
    
    // }}}
    // {{{ ���X���b�h���ځ[��`�F�b�N
    
    if ($aThreadList->spmode != 'taborn' and $ta_keys[$aThread->key]) { 
            unset($ta_keys[$aThread->key]);
            continue; //���ځ[��X���̓X�L�b�v
    }

    $aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);
    $aThread->getThreadInfoFromIdx(); // �����X���b�h�f�[�^��idx����擾

    // }}}
    // {{{ �� favlist�`�F�b�N =====================================

    $debug && $profiler->enterSection('favlist_check');
    // if ($x <= $threads_num) {
        if ($aThreadList->spmode != 'taborn' and $fav_keys[$aThread->key]) {
            $aThread->fav = 1;
            unset($fav_keys[$aThread->key]);
        }
    // }
    $debug && $profiler->leaveSection('favlist_check');
    
    // }}}
    
    // �� spmode(�a������Anews������)�Ȃ� ====================================
    if ($aThreadList->spmode && $aThreadList->spmode!="news" && $sb_view!="edit") { 
        
        // �� subject.txt����DL�Ȃ痎�Ƃ��ăf�[�^��z��Ɋi�[
        if (!$subject_txts["$aThread->host/$aThread->bbs"]) {

            require_once (P2_LIBRARY_DIR . '/SubjectTxt.class.php');
            $aSubjectTxt =& new SubjectTxt($aThread->host, $aThread->bbs);
            
            $debug && $profiler->enterSection('subthre_read'); //
            if ($aThreadList->spmode == "soko" or $aThreadList->spmode == "taborn") {

                if (is_array($aSubjectTxt->subject_lines)) {
                    $it = 1;
                    foreach ($aSubjectTxt->subject_lines as $asbl) {
                        if (preg_match("/^([0-9]+)\.(dat|cgi)(,|<>)(.+) ?(\(|�i)([0-9]+)(\)|�j)/", $asbl, $matches)) {
                            $akey = $matches[1];
                            $subject_txts["$aThread->host/$aThread->bbs"][$akey]['ttitle'] = rtrim($matches[4]);
                            $subject_txts["$aThread->host/$aThread->bbs"][$akey]['rescount'] = $matches[6];
                            $subject_txts["$aThread->host/$aThread->bbs"][$akey]['torder'] = $it;
                        }
                        $it++;
                    }
                }
                
            } else {
                $subject_txts["$aThread->host/$aThread->bbs"] = $aSubjectTxt->subject_lines;
                
            }
            $debug && $profiler->leaveSection('subthre_read');//
        }

        $debug && $profiler->enterSection('subthre_check');//
        // ���X�����擾 =============================
        if ($aThreadList->spmode == "soko" or $aThreadList->spmode == "taborn") {
        
            if ($subject_txts[$aThread->host.'/'.$aThread->bbs][$aThread->key]) {
            
                // �q�ɂ̓I�����C�����܂܂Ȃ�
                if ($aThreadList->spmode == "soko") {
                    $debug && $profiler->leaveSection('subthre_check'); //
                    unset($aThread);
                    continue;
                } elseif ($aThreadList->spmode == "taborn") {
                    // subject.txt ����X�����擾
                    // $aThread->getThreadInfoFromSubjectTxtLine($l);
                    $aThread->isonline = true;
                    $ttitle = $subject_txts["$aThread->host/$aThread->bbs"][$aThread->key]['ttitle'];
                    $aThread->setTtitle($ttitle);
                    $aThread->rescount = $subject_txts["$aThread->host/$aThread->bbs"][$aThread->key]['rescount'];
                    if ($aThread->readnum) {
                        $aThread->unum = $aThread->rescount - $aThread->readnum;
                        // machi bbs ��sage��subject�̍X�V���s���Ȃ������Ȃ̂Œ������Ă���
                        if ($aThread->unum < 0) { $aThread->unum = 0; }
                    }
                    $aThread->torder = $subject_txts["$aThread->host/$aThread->bbs"][$aThread->key]['torder'];
                }

            }
            
        } else {
        
            if ($subject_txts[$aThread->host.'/'.$aThread->bbs]) {
                $it = 1;
                foreach ($subject_txts[$aThread->host.'/'.$aThread->bbs] as $l) {
                    if (@preg_match("/^{$aThread->key}/", $l)) {
                        // subject.txt ����X�����擾
                        $aThread->getThreadInfoFromSubjectTxtLine($l);
                        break;
                    }
                    $it++;
                }
            }
        
        }
        $debug && $profiler->leaveSection('subthre_check'); //
        
        if ($aThreadList->spmode == 'taborn') {
            if (!$aThread->torder) { $aThread->torder = '-'; }
        }

        
        // ���V���̂�(for spmode) ===============================
        if ($sb_view == 'shinchaku' and !$_GET['word']) { 
            if ($aThread->unum < 1) {
                unset($aThread);
                continue;
            }
        }
        
        /*
        // �����[�h�t�B���^(for spmode) ==================================
        if ($word_fm) {
            $target = $aThread->ttitle;
            if (!StrCtl::filterMatch($word_fm, $target)) {
                unset($aThread);
                continue;
            } else {
                $GLOBALS['sb_mikke_num']++;
                if ($_conf['ktai']) {
                    $aThread->ttitle_ht = $aThread->ttitle;
                } else {
                    $aThread->ttitle_ht = StrCtl::filterMarking($word_fm, $aThread->ttitle);
                }
            }
        }
        */
    }
    
    // subject����rescount�����Ȃ������ꍇ�́Agotnum�𗘗p����B
    if ((!$aThread->rescount) and $aThread->gotnum) {
        $aThread->rescount = $aThread->gotnum;
    }
    if (!$aThread->ttitle_ht) {
        $aThread->ttitle_ht = $aThread->ttitle;
    }
    
    if ($aThread->unum > 0) { // �V������
        $shinchaku_attayo = true;
        $shinchaku_num = $shinchaku_num + $aThread->unum; // �V����set
    } elseif ($aThread->fav) { // ���C�ɃX��
        ;
    } elseif ($aThread->new) { // �V�K�X��
        $_newthre_num++; // ��showbrdmenupc.class.php
    } else {
        // �g�тƃj���[�X�`�F�b�N�ȊO��
        if ($_conf['ktai'] or $spmode != "news") {
            // �w�萔���z���Ă�����J�b�g
            if($x >= $threads_num){
                unset($aThread);
                continue;
            }
        }
    }
    
    /*
    // ���V���\�[�g�̕֋X�� unum ���Z�b�g����
    if (!isset($aThread->unum)) {
        if ($aThreadList->spmode == "recent" or $aThreadList->spmode == "res_hist" or $aThreadList->spmode == "taborn") {
            $aThread->unum = -0.1;
        } else {
            $aThread->unum = $_conf['sort_zero_adjust'];
        }
    }
    */
    
    // �����̃Z�b�g
    $aThread->setDayRes($nowtime);
    
    /*
    // ������set
    if ($aThread->isonline) { $online_num++; }
    
    // �����X�g�ɒǉ� ==============================================
    $aThreadList->addThread($aThread);

    */
    unset($aThread);
}

// $shinchaku_num

?>
