<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// �A�N�Z�X���z�X�g���`�F�b�N����֐��Q

require_once 'conf/conf_hostcheck.php';

class HostCheck
{

    /**
     * �A�N�Z�X�֎~�̃��b�Z�[�W��\�����ďI������
     */
    function forbidden()
    {
        header('HTTP/1.0 403 Forbidden');
        echo <<<EOF
<html>
<head>
    <title>403 Forbidden</title>
</head>
<body>
<h1>�A�N�ցB</h1>
<p>{$_SERVER['REMOTE_ADDR']}����p2�ւ̃A�N�Z�X�͋�����Ă��܂���B<br>
�������Ȃ�������p2�̃I�[�i�[�Ȃ�Aconf_hostcheck.php�̐ݒ���������Ă��������B</p>
</body>
</html>
EOF;
        exit;
    }


    /**
     * ���[�J���L���b�V����gethostbyaddr()
     */
    function cachedGetHostByAddr($remote_addr)
    {
        global $_conf;

        $function = 'gethostbyaddr';
        $cache_file = $_conf['pref_dir'] . '/p2_cache/hostcheck_gethostbyaddr.cache';

        return HostCheck::_cachedGetHost($remote_addr, $function, $cache_file);
    }


    /**
     * ���[�J���L���b�V����gethostbyname()
     */
    function cachedGetHostByName($remote_host)
    {
        global $_conf;

        $function = 'gethostbyname';
        $cache_file = $_conf['pref_dir'] . '/p2_cache/hostcheck_gethostbyname.cache';

        return HostCheck::_cachedGetHost($remote_host, $function, $cache_file);
    }


    /**
     * cachedGetHostByAddr/cachedGetHostByName �̃L���b�V���G���W��
     */
    function _cachedGetHost($remote, $function, $cache_file)
    {
        $ttl = $GLOBALS['_HOSTCHKCONF']['gethostby_expires'];

        // �L���b�V�����Ȃ��ݒ�̂Ƃ�
        if ($ttl <= 0) {
            return $function($remote);
        }

        // �L���b�V���L���̂Ƃ�
        $now  = time();
        $list = array();

        // �L���b�V���t�@�C����������΍쐬����
        if (!file_exists($cache_file)) {
            FileCtl::make_datafile($cache_file);
        }

        // �L���b�V����ǂݍ���
        $lines = file($cache_file);
        if (is_array($lines)) {
            foreach ($lines as $line) {
                list($query, $result, $expires) = explode("\t", rtrim($line, "\n"));
                if ($expires > $now) {
                    $list[$query] = array($result, $expires);
                }
            }
        }

        // �L���b�V������Ă���Ƃ�
        if (isset($list[$remote])) {
            return $list[$remote][0];
        }

        // �L���b�V������Ă��Ȃ��Ƃ�
        $result = $function($remote);
        $list[$remote] = array($result, $ttl + $now);

        // �L���b�V����ۑ�����
        $content = '';
        foreach ($list as $query => $item) {
            $content .= $query . "\t" . $item[0] . "\t" . $item[1] . "\n";
        }
        FileCtl::file_write_contents($cache_file, $content);

        return $result;
    }


    /**
     * �A�N�Z�X�������ꂽIP�A�h���X�ш�Ȃ� true ��Ԃ�
     * (false = �A�N��)
     */
    function getHostAuth()
    {
        global $_conf, $_HOSTCHKCONF;

        switch ($_conf['secure']['auth_host']) {
        case 1:
            $flag = 1;
            $ret  = true;
            $custom = $_HOSTCHKCONF['custom_allowed_host'];
            break;
        case 2:
            $flag = 0;
            $ret  = false;
            $custom = $_HOSTCHKCONF['custom_denied_host'];
            break;
        default:
            return true;
        }

        if (
            ( $flag == $_HOSTCHKCONF['host_type']['localhost'] && HostCheck::isAddrLocal() ) ||
            ( $flag == $_HOSTCHKCONF['host_type']['private'] && HostCheck::isAddrPrivate() ) ||
            ( $flag == $_HOSTCHKCONF['host_type']['DoCoMo'] && HostCheck::isAddrDocomo() ) ||
            ( $flag == $_HOSTCHKCONF['host_type']['au'] && HostCheck::isAddrAu() ) ||
            ( $flag == $_HOSTCHKCONF['host_type']['Vodafone'] && HostCheck::isAddrVodafone() ) ||
            ( $flag == $_HOSTCHKCONF['host_type']['AirH'] && HostCheck::isAddrWillcom() ) ||
            ( $flag == $_HOSTCHKCONF['host_type']['custom'] && !empty($custom) && HostCheck::isAddrInBand($custom) )
        ) {
            return $ret;
        }
        return !$ret;
    }


    /**
     * BBQ�ɏĂ���Ă���IP�A�h���X�Ȃ� true ��Ԃ�
     * (true = �A�N��)
     */
    function getHostBurned()
    {
        global $_conf;

        if (!$_conf['secure']['auth_bbq'] || HostCheck::isAddrLocal() || HostCheck::isAddrPrivate()) {
            return false;
        }

        if (HostCheck::isAddrBurned()) {
            return true;
        }

        return false;
    }


    /**
     * �}�X�N�����T�u�l�b�g�}�X�N�ɕϊ�
     */
    function length2subnet($length)
    {
        $subnet = array();
        for ($i = 0; $i < 4; $i++) {
            if ($length >= 8) {
                $subnet[] = '255';
            } elseif ($length > 0) {
                $subnet[] = strval(255 & ~bindec(str_repeat('1', 8 - $length)));
            } else {
                $subnet[] = '0';
            }
            $length -= 8;
        }
        return implode('.', $subnet);
    }


    /**
     * ���[�J���z�X�g?
     */
    function isAddrLocal()
    {
        return ($_SERVER['REMOTE_ADDR'] == '127.0.0.1');
    }


    /**
     * �z�X�g��BBQ�ɏĂ���Ă��邩?
     *
     * @link http://bbq.uso800.net/
     */
    function isAddrBurned($addr = null)
    {
        if (is_null($addr)) {
            $addr = $_SERVER['REMOTE_ADDR'];
        }
        $ip_regex = '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/';
        $errmsg = "\n<br><b>NOTICE: Wrong IP Address given.</b> ($addr)<br>\n";

        // IP�A�h���X������
        if (!preg_match($ip_regex, $addr, $ipv4)) {
            trigger_error($errmsg, E_USER_NOTICE);
            return false; // IP�A�h���X�̏����ɍ��v���Ȃ�
        }

        // �₢���킹��z�X�g����ݒ�
        $query_host = 'niku.2ch.net';
        for ($i = 1; $i <= 4; $i++) {
            $octet = $ipv4[$i];
            if ($octet > 255) {
                trigger_error($errmsg, E_USER_NOTICE);
                return false; // IP�A�h���X�̏����ɍ��v���Ȃ�
            }
            $query_host = $octet . '.' . $query_host;
        }

        // �₢���킹�����s
        $result_addr = HostCheck::cachedGetHostByName($query_host);

        if ($result_addr == '127.0.0.2') {
            return true; // BBQ�ɏĂ���Ă���
        }
        return false; // BBQ�ɏĂ���Ă��Ȃ�
    }


    /**
     * �C�ӂ�IP�A�h���X�ш������̃A�N�Z�X��?
     *
     * �����̐��ɂ�菈�����e���ς��
     * 1. $_SERVER['REMOTE_ADDR']���������̑ш�ɂ��邩�`�F�b�N����
     * 2. ���������������̑ш�ɂ��邩�`�F�b�N����
     * 3. (2)�ɉ����đ�O�����ƃ����[�g�z�X�g�𐳋K�\���}�b�`���O����
     *
     * �ш�w��͈ȉ��̂����ꂩ�̕����𗘗p�ł��� (2,3�̍��݂���)
     * 1. IP�A�h���X(+�X���b�V���ŋ�؂��ă}�X�N���������̓T�u�l�b�g�}�X�N)�̕�����
     * 2. (1)�̔z��
     * 3. IP�A�h���X���L�[�Ƃ��A�}�X�N���������̓T�u�l�b�g�}�X�N��l�ɂƂ�A�z�z��
     */
    function isAddrInBand($addr, $band = null, $regex = null)
    {
        if (is_null($band)) {
            $regex = null;
            $band = $addr;
            $addr = $_SERVER['REMOTE_ADDR'];
        }

        // IP�A�h���X������
        $addr = ip2long($addr);
        if (!is_int($addr)) {
            return false;
        }

        // IP�A�h���X������
        if (!is_array($band)) {
            $band = array($band);
        }
        foreach ($band as $target => $mask) {
            if (is_int($target) && is_string($mask)) {
                $cond = explode('/', $mask);
                $target = $cond[0];
                $mask = isset($cond[1]) ? (is_numeric($cond[1]) ? intval($cond[1]) : $cond[1]) : '255.255.255.255';
            }
            $target = ip2long($target);
            if (is_int($mask)) {
                $mask = HostCheck::length2subnet($mask);
            }
            $mask = ip2long($mask);
            if (!is_int($target) || !is_int($mask)) {
                continue;
            }
            if (($addr & $mask) == ($target & $mask)) {
                return true;
            }
        }

        // �ш悪�}�b�`�����A���K�\�����w�肳��Ă���Ƃ�
        if ($regex) {
            if ($addr == $_SERVER['REMOTE_ADDR'] && isset($_SERVER['REMOTE_HOST'])) {
                $remote_host = $_SERVER['REMOTE_HOST'];
            } else {
                $remote_host = HostCheck::cachedGetHostByAddr(long2ip($addr));
            }
            if (@preg_match($regex, $remote_host)) {
                return true;
            }
        }

        return false;
    }

    /**
     * �v���C�x�[�g�A�h���X?
     *
     * @see RFC1918
     */
    function isAddrPrivate($addr = '', $class = '')
    {
        if (!$addr) {
            $addr = $_SERVER['REMOTE_ADDR'];
        }
        $class = ($class) ? strtoupper($class) : 'ABC';
        $private = array();
        if (strstr($class, 'A')) {
            $private[] = '10.0.0.0/8';
        }
        if (strstr($class, 'B')) {
            $private[] = '172.16.0.0/12';
        }
        if (strstr($class, 'C')) {
            $private[] = '192.168.0.0/16';
        }
        return HostCheck::isAddrInBand($addr, $private);
    }

    /**
     * DoCoMo?
     *
     * @link http://www.nttdocomo.co.jp/p_s/imode/ip/
     */
    function isAddrDocomo($addr = null)
    {
        if (is_null($addr)) {
            $addr = $_SERVER['REMOTE_ADDR'];
        }
        $iHost = '/^proxy[0-9a-f]\d\d\.docomo\.ne\.jp$/';
        $iBand = array(
            '210.153.84.0/24',
            '210.136.161.0/24',

            '210.143.108.0/24', // jig 2005/6/23
        );
        return HostCheck::isAddrInBand($addr, $iBand, $iHost);
    }

    /**
     * au?
     *
     * @link http://www.au.kddi.com/ezfactory/tec/spec/ezsava_ip.html
     */
    function isAddrAu($addr = null)
    {
        if (is_null($addr)) {
            $addr = $_SERVER['REMOTE_ADDR'];
        }
        $ezHost = '/^wb\d\dproxy\d\d\.ezweb\.ne\.jp$/';
        $ezBand = array(
            '61.117.0.0/24',
            '61.117.1.0/24',
            '61.117.2.0/26',
            '61.202.3.0/24',
            '210.169.40.0/24',
            '210.196.3.192/26',
            '210.196.5.192/26',
            '210.230.128.0/24',
            '210.230.141.192/26',
            '210.234.105.32/29',
            '210.234.108.64/26',
            '210.251.1.192/26',
            '210.251.2.0/27',
            '211.5.1.0/24',
            '211.5.2.128/25',
            '211.5.7.0/24',
            '218.222.1.0/24',
            '219.108.158.0/26',
            '219.125.148.0/24',
            '222.5.63.0/24',
            '222.7.56.0/24',

            '210.143.108.0/24', // jig 2005/6/23
        );
        return HostCheck::isAddrInBand($addr, $ezBand, $ezHost);
    }


    /**
     * Vodafone?
     *
     * @link http://developers.vodafone.jp/dp/tech_svc/web/ip.php
     * @link http://qb5.2ch.net/test/read.cgi/operate/1116860379/100-125
     */
    function isAddrVodafone($addr = null)
    {
        if (is_null($addr)) {
            $addr = $_SERVER['REMOTE_ADDR'];
        }
        $vHost = '/\.(skyweb\.jp-[a-z]|vodafone)\.ne\.jp$/'; // �悭�������ĂȂ��̂ő�G�c
        $vBand = array(
            '210.146.7.192/26',
            '210.146.60.192/26',
            '210.151.9.128/26',
            '210.169.176.0/24',
            '210.169.193.192/26',
            '210.175.1.128/25',
            '210.228.189.0/24',
            '211.8.159.128/25',
            '211.127.183.0/24',

            '210.146.60.128/25', // ������Ȃ���ǉ�

            '210.143.108.0/24', // jig 2005/6/23
        );
        return HostCheck::isAddrInBand($addr, $vBand, $vHost);
    }


    /**
     * WILLCOM? (old name)
     *
     * @deprecated  06-02-17
     * @see isAddrWillcom()
     */
    function isAddrAirh($addr = null)
    {
        return HostCheck::isAddrWillcom($addr);
    }


    /**
     * WILLCOM?
     *
     * @link http://www.willcom-inc.com/ja/service/contents_service/club_air_edge/for_phone/ip/index.html
     */
    function isAddrWillcom($addr = null)
    {
        if (is_null($addr)) {
            $addr = $_SERVER['REMOTE_ADDR'];
        }
        $wHost = '/^[Pp]\d{12}\.ppp\.prin\.ne\.jp$/';
        $wBand = array(
            '61.198.142.0/24',
            '61.198.161.0/24',
            '61.198.249.0/24',
            '61.198.250.0/24',
            '61.198.253.0/24',
            '61.198.254.0/24',
            '61.198.255.0/24',
            '61.204.0.0/24',
            '61.204.3.0/25',
            '61.204.4.0/24',
            '61.204.6.0/25',
            '125.28.0.0/24',
            '125.28.1.0/24',
            '125.28.2.0/24',
            '125.28.3.0/24',
            '125.28.4.0/24',
            '125.28.5.0/24',
            '125.28.6.0/24',
            '125.28.7.0/24',
            '125.28.8.0/24',
            '210.168.246.0/24',
            '210.168.247.0/24',
            '211.18.232.0/24',
            '211.18.233.0/24',
            '211.18.235.0/24',
            '211.18.236.0/24',
            '211.18.237.0/24',
            '211.18.238.0/24',
            '211.18.239.0/24',
            '219.108.0.0/24',
            '219.108.1.0/24',
            '219.108.14.0/24',
            '219.108.2.0/24',
            '219.108.3.0/24',
            '219.108.4.0/24',
            '219.108.5.0/24',
            '219.108.6.0/24',
            '219.108.7.0/24',
            '221.119.0.0/24',
            '221.119.1.0/24',
            '221.119.2.0/24',
            '221.119.3.0/24',
            '221.119.4.0/24',
            '221.119.5.0/24',
            '221.119.6.0/24',
            '221.119.7.0/24',
            '221.119.8.0/24',
            '221.119.9.0/24',

            '210.143.108.0/24', // jig 2005/6/23
        );
        return HostCheck::isAddrInBand($addr, $wBand, $wHost);
    }

}

?>