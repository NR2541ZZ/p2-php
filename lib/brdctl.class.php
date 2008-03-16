<?php
require_once P2_LIBRARY_DIR . '/filectl.class.php';
require_once P2_LIBRARY_DIR . '/brdmenu.class.php';

/**
 * p2 - ���X�g�R���g���[���N���X for menu.php
 * �X�^�e�B�b�N���\�b�h�ŗ��p���Ă���
 */
class BrdCtl
{
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
        $brd_menus = array();
        $brd_dir = './board';

        if (is_dir($brd_dir) and $cdir = dir($brd_dir)) {
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
                    P2Util::pushInfoHtml("<p>p2 error: ���X�g {$entry} ���ǂݍ��߂܂���ł����B</p>\n");
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
        global $_conf;

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
                    P2Util::pushInfoHtml("<p>p2 �G���[: {$cache_brd} ������j���[�𐶐����邱�Ƃ͂ł��܂���ł����B</p>\n");
                }
                unset($data, $aBrdMenu);
            } else {
                P2Util::pushInfoHtml("<p>p2 �G���[: {$cachefile} �͓ǂݍ��߂܂���ł����B</p>\n");
            }
        }

        return $brd_menus;
    }

    /**
     * �����i�X���^�C�����j��word�N�G���[������΃p�[�X����
     * $GLOBALS['word'], $GLOBALS['words_fm'], $GLOBALS['word_fm'] ���Z�b�g����
     *
     * @access  static
     * @return  void
     */
    function parseWord()
    {
        $GLOBALS['word'] = null;
        $GLOBALS['words_fm'] = null;
        $GLOBALS['word_fm'] = null;

        if (isset($_GET['word'])) {
            $word = $_GET['word'];
        } elseif (isset($_POST['word'])) {
            $word = $_POST['word'];
        }

        if (!isset($word) || strlen($word) == 0) {
            return;
        }

        /*
        // ���ʂɏ��O�������
        // ���ł��}�b�`���Ă��܂����K�\��
        if (preg_match('/^\.+$/', $word)) {
            return;
        }
        */

        include_once P2_LIBRARY_DIR . '/strctl.class.php';
        // and�����ł�낵���i���K�\���ł͂Ȃ��j
        $word_fm = StrCtl::wordForMatch($word, 'and');
        if (P2_MBREGEX_AVAILABLE == 1) {
            $GLOBALS['words_fm'] = mb_split('\s+', $word_fm);
        } else {
            $GLOBALS['words_fm'] = preg_split('/\s+/', $word_fm);
        }
        $GLOBALS['word_fm'] = implode('|', $GLOBALS['words_fm']);

        $GLOBALS['word'] = $word;
    }

    /**
     * �g�їp �����i�X���^�C�����j�̃t�H�[��HTML���擾����
     *
     * @access  static
     * @return  void
     */
    function getMenuKSearchFormHtml($action = null)
    {
        global $_conf;

        is_null($action) and $action = $_SERVER['SCRIPT_NAME'];

        $threti_ht = ''; // �X���^�C�����͖��Ή�

        $word_hs = htmlspecialchars($GLOBALS['word'], ENT_QUOTES);

        return <<<EOFORM
<form method="GET" action="{$action}" accept-charset="{$_conf['accept_charset']}">
    {$_conf['detect_hint_input_ht']}
    {$_conf['k_input_ht']}
    <input type="hidden" name="nr" value="1">
    <input type="text" id="word" name="word" value="{$word_hs}" size="12">
    {$threti_ht}
    <input type="submit" name="submit" value="����">
</form>\n
EOFORM;
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
