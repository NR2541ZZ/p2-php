<?php
/**
 * rep2expack - �g�т��� SPM �����̋@�\�𗘗p���邽�߂̊֐�
 */

/**
 * ���X�ԍ����w�肵�� �ړ��E�R�s�[(+���p)�EAAS ����t�H�[���𐶐�
 *
 * @return string
 */
function kspform(&$aThread, $default = '', $params = null)
{
    global $_conf;

    // ���͂�4���̐����Ɍ��肷�邽�߂̑���
    //$numonly_at = 'maxlength="4" istyle="4" format="*N" mode="numeric"';
    $numonly_at = 'maxlength="4" istyle="4" format="4N" mode="numeric"';

    // �I���\�ȃI�v�V����
    $options = array();
    $options['goto'] = 'GO';
    $options['copy'] = '��߰';
    $options['copy_quote'] = '&gt;��߰';
    $options['res_quote']  = '&gt;ڽ';
    if ($_conf['expack.aas.enabled']) {
        $options['aas']        = 'AAS';
        $options['aas_rotate'] = 'AAS*';
    }
    $options['aborn_res']  = '����:ڽ';
    $options['aborn_name'] = '����:���O';
    $options['aborn_mail'] = '����:Ұ�';
    $options['aborn_id']   = '����:ID';
    $options['aborn_msg']  = '����:ү����';
    $options['ng_name'] = 'NG:���O';
    $options['ng_mail'] = 'NG:Ұ�';
    $options['ng_id']   = 'NG:ID';
    $options['ng_msg']  = 'NG:ү����';

    // �t�H�[������
    $form = "<form method=\"get\" action=\"spm_k.php\">";
    $form .= $_conf['k_input_ht'];

    // �B���p�����[�^
    $hidden = '<input type="hidden" name="%s" value="%s">';
    $form .= sprintf($hidden, 'host', htmlspecialchars($aThread->host, ENT_QUOTES));
    $form .= sprintf($hidden, 'bbs', htmlspecialchars($aThread->bbs, ENT_QUOTES));
    $form .= sprintf($hidden, 'key', htmlspecialchars($aThread->key, ENT_QUOTES));
    $form .= sprintf($hidden, 'offline', '1');

    // �ǉ��̉B���p�����[�^
    if (is_array($params)) {
        foreach ($params as $param_name => $param_value) {
            $form .= sprintf($hidden, $param_name, htmlspecialchars($param_value, ENT_QUOTES));
        }
    }

    // �I�v�V������I�����郁�j���[
    $form .= '<select name="ktool_name">';
    foreach ($options as $opt_name => $opt_title) {
        $form .= "<option value=\"{$opt_name}\">{$opt_title}</option>";
    }
    $form .= '</select>';

    // ���l���̓t�H�[���Ǝ��s�{�^��
    $form .= "<input type=\"text\" size=\"3\" name=\"ktool_value\" value=\"{$default}\" {$numonly_at}>";
    $form .= '<input type="submit" value="OK" title="OK">';

    $form .= '</form>';

    return $form;
}

/**
 * �X���b�h���w�肷��
 */
function kspDetectThread()
{
    global $_conf, $host, $bbs, $key, $ls;

    // �X��URL�̒��ڎw��
    if (($nama_url = $_GET['nama_url']) || ($nama_url = $_GET['url'])) {

            // 2ch or pink - http://choco.2ch.net/test/read.cgi/event/1027770702/
            if (preg_match("/http:\/\/([^\/]+\.(2ch\.net|bbspink\.com))\/test\/read\.cgi\/([^\/]+)\/([0-9]+)(\/)?([^\/]+)?/", $nama_url, $matches)) {
                $host = $matches[1];
                $bbs = $matches[3];
                $key = $matches[4];
                $ls = $matches[6];

            // 2ch or pink �ߋ����Ohtml - http://pc.2ch.net/mac/kako/1015/10153/1015358199.html
            } elseif ( preg_match("/(http:\/\/([^\/]+\.(2ch\.net|bbspink\.com))(\/[^\/]+)?\/([^\/]+)\/kako\/\d+(\/\d+)?\/(\d+)).html/", $nama_url, $matches) ){ //2ch pink �ߋ����Ohtml
                $host = $matches[2];
                $bbs = $matches[5];
                $key = $matches[7];
                $kakolog_uri = $matches[1];
                $_GET['kakolog'] = urlencode($kakolog_uri);

            // �܂����������JBBS - http://kanto.machibbs.com/bbs/read.pl?BBS=kana&KEY=1034515019
            } elseif ( preg_match("/http:\/\/([^\/]+\.machibbs\.com|[^\/]+\.machi\.to)\/bbs\/read\.(pl|cgi)\?BBS=([^&]+)&KEY=([0-9]+)(&START=([0-9]+))?(&END=([0-9]+))?[^\"]*/", $nama_url, $matches) ){
                $host = $matches[1];
                $bbs = $matches[3];
                $key = $matches[4];
                $ls = $matches[6] ."-". $matches[8];
            } elseif (preg_match("{http://((jbbs\.livedoor\.jp|jbbs\.livedoor.com|jbbs\.shitaraba\.com)(/[^/]+)?)/bbs/read\.(pl|cgi)\?BBS=([^&]+)&KEY=([0-9]+)(&START=([0-9]+))?(&END=([0-9]+))?[^\"]*}", $nama_url, $matches)) {
                $host = $matches[1];
                $bbs = $matches[5];
                $key = $matches[6];
                $ls = $matches[8] ."-". $matches[10];

            // �������JBBS http://jbbs.livedoor.com/bbs/read.cgi/computer/2999/1081177036/-100
            }elseif( preg_match("{http://(jbbs\.livedoor\.jp|jbbs\.livedoor.com|jbbs\.shitaraba\.com)/bbs/read\.cgi/(\w+)/(\d+)/(\d+)/((\d+)?-(\d+)?)?[^\"]*}", $nama_url, $matches) ){
                $host = $matches[1] ."/". $matches[2];
                $bbs = $matches[3];
                $key = $matches[4];
                $ls = $matches[5];
            }

    } else {
        if ($_GET['host']) { $host = $_GET['host']; } // "pc.2ch.net"
        if ($_POST['host']) { $host = $_POST['host']; }
        if ($_GET['bbs']) { $bbs = $_GET['bbs']; } // "php"
        if ($_POST['bbs']) { $bbs = $_POST['bbs']; }
        if ($_GET['key']) { $key = $_GET['key']; } // "1022999539"
        if ($_POST['key']) { $key = $_POST['key']; }
        if ($_GET['ls']) {$ls = $_GET['ls']; } // "all"
        if ($_POST['ls']) { $ls = $_POST['ls']; }
    }

    if (!($host && $bbs && $key)) {
        $htm['nama_url'] = htmlspecialchars($nama_url, ENT_QUOTES);
        $msg = "p2 - {$_conf['read_php']}: �X���b�h�̎w�肪�ςł��B<br>"
            . "<a href=\"{$htm['nama_url']}\">" . $htm['nama_url'] . "</a>";
        die($msg);
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
