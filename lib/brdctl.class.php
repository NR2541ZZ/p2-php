<?php
require_once P2_LIBRARY_DIR . '/filectl.class.php';
require_once P2_LIBRARY_DIR . '/brdmenu.class.php';

/**
 * p2 - ���X�g�R���g���[���N���X for menu.php
 * �X�^�e�B�b�N���\�b�h�ŗ��p���Ă���
 */
class BrdCtl{
    
    /**
     * board��S�ēǂݍ���
     *
     * @access  public
     * @return  array
     */
    function read_brds()
    {
        $brd_menus_dir = BrdCtl::readBrdLocal();
        $brd_menus_online = BrdCtl::readBrdOnline();
        $brd_menus = array_merge($brd_menus_dir, $brd_menus_online);
        
        return $brd_menus;
    }
    
    /**
     * ���[�J����board�f�B���N�g���𑖍����ēǂݍ���
     *
     * @access  private
     * @return  array
     */
    function readBrdLocal()
    {
        global $_info_msg_ht;
    
        $brd_menus = array();
        $brd_dir = './board';
        
        if (is_dir($brd_dir) and $cdir = dir($brd_dir)) {
            // �f�B���N�g������
            while ($entry = $cdir->read()) {
                if (preg_match('/^\./', $entry)) {
                    continue;
                }
                $filepath = $brd_dir . '/' . $entry;
                if ($data = file($filepath)) {
                    $aBrdMenu =& new BrdMenu();    // �N���X BrdMenu �̃I�u�W�F�N�g�𐶐�
                    $aBrdMenu->setBrdMatch($filepath);    // �p�^�[���}�b�`�`����o�^
                    $aBrdMenu->setBrdList($data);    // �J�e�S���[�Ɣ��Z�b�g
                    $brd_menus[] =& $aBrdMenu;
                    
                } else {
                    $_info_msg_ht .= "<p>p2 error: ���X�g {$entry} ���ǂݍ��߂܂���ł����B</p>\n";
                }
            }
            $cdir->close();
        }
        
        return $brd_menus;
    }
    
    /**
     * �I�����C�����X�g��ǂݍ���
     *
     * @access  private
     * @return  array
     */
    function readBrdOnline()
    {
        global $_conf, $_info_msg_ht;

        if (empty($_conf['brdfile_online'])) {
            return array();
        }

        $brd_menus = array();
        
        $cachefile = P2Util::cacheFileForDL($_conf['brdfile_online']);
        $noDL = false;
        
        // �L���b�V��������ꍇ
        if (file_exists($cachefile . '.p2.brd')) {
        
            // norefresh�Ȃ�DL���Ȃ�
            if (!empty($_GET['nr'])) {
                $noDL = true;
                
            // �L���b�V���̍X�V���w�莞�Ԉȓ��Ȃ�DL���Ȃ�
            } elseif (filemtime($cachefile . '.p2.brd') > time() - 60 * 60 * $_conf['menu_dl_interval']) {
                $noDL = true;
            }
        }
        
        // DL���Ȃ�
        if ($noDL) {
            ;
        // DL����
        } else {
            //echo "DL!<br>";//
            $brdfile_online_res = P2Util::fileDownload($_conf['brdfile_online'], $cachefile, true, true);
            if ($brdfile_online_res->is_success() && $brdfile_online_res->code != '304') {
                $isNewDL = true;
            }
        }
        
        // html�`���Ȃ�
        if (preg_match('/html?$/', $_conf['brdfile_online'])) {
        
            // �X�V����Ă�����V�K�L���b�V���쐬
            if ($isNewDL) {
                //echo "NEW!<br>"; //
                $aBrdMenu =& new BrdMenu(); // �N���X BrdMenu �̃I�u�W�F�N�g�𐶐�
                $aBrdMenu->makeBrdFile($cachefile); // .p2.brd�t�@�C���𐶐�
                $brd_menus[] = $aBrdMenu;
                $read_html_flag = true;
                unset($aBrdMenu);
            }
            
            if (file_exists($cachefile . '.p2.brd')) {
                $cache_brd = $cachefile . '.p2.brd';
            } else {
                $cache_brd = $cachefile;
            }
            
        } else {
            $cache_brd = $cachefile;
        }
        
        if (!$read_html_flag) {
            if ($data = file($cache_brd)) {
                $aBrdMenu =& new BrdMenu(); // �N���X BrdMenu �̃I�u�W�F�N�g�𐶐�
                $aBrdMenu->setBrdMatch($cache_brd); // �p�^�[���}�b�`�`����o�^
                $aBrdMenu->setBrdList($data); // �J�e�S���[�Ɣ��Z�b�g
                if ($aBrdMenu->num) {
                    $brd_menus[] =& $aBrdMenu;
                } else {
                    $_info_msg_ht .=  "<p>p2 �G���[: {$cache_brd} ������j���[�𐶐����邱�Ƃ͂ł��܂���ł����B</p>\n";
                }
                unset($data, $aBrdMenu);
            } else {
                $_info_msg_ht .=  "<p>p2 �G���[: {$cachefile} �͓ǂݍ��߂܂���ł����B</p>\n";
            }
        }
        
        return $brd_menus;
    }

}
?>