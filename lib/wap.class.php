<?php
// WWW Access on PHP
// http://member.nifty.ne.jp/hippo2000/perltips/LWP.html ���Q�l�ɂ������悤�ȊȈՂ̂��̂�

// 2005/04/20 aki ���̃N���X�͖����I���ɂ��āAPEAR���p�Ɉڍs�������iHTTP_Request�Ȃǁj

/**
 * UserAgent �N���X
 *
 *  setAgent() : ua ���Z�b�g����B
 *  setTimeout()
 *  request() : ���N�G�X�g���T�[�o�ɑ��M���āA���X�|���X��Ԃ��B
 */
class UserAgent
{
    var $agent;  // User-Agent�B�A�v���P�[�V�����̖��O�B
    var $timeout;
    var $maxRedirect;
    var $redirectCount;
    var $redirectCache;

    /**
     * setAgent
     */
    function setAgent($agent_name)
    {
        $this->agent = $agent_name;
    }

    /**
     * setTimeout
     */
    function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * request
     *
     * http://www.spencernetwork.org/memo/tips-3.php ���Q�l�ɂ����Ē����܂����B
     *
     * @param only_header bool ���g�͎擾�����Ƀw�b�_�̂ݎ擾����
     */
    function request($req, $only_header = false, $postdata_urlencode = true)
    {
        $res =& new Response();

        $purl = parse_url($req->url); // URL����
        if (isset($purl['query'])) { // �N�G���[
            $purl['query'] = "?".$purl['query'];
        } else {
            $purl['query'] = '';
        }
        $default_port = ($purl['scheme'] == 'https') ? 443 : 80; // �f�t�H���g�̃|�[�g

        // �v���L�V
        if ($req->proxy) {
            $send_host = $req->proxy['host'];
            $send_port = isset($req->proxy['port']) ? $req->proxy['port'] : $default_port;
            $send_path = $req->url;
        } else {
            $send_host = $purl['host'];
            $send_port = isset($purl['port']) ? $purl['port'] : $default_port;
            $send_path = $purl['path'].$purl['query'];
        }

        // SSL
        if ($purl['scheme'] == 'https') {
            $send_host = 'ssl://' . $send_host;
        }

        $request = $req->method." ".$send_path." HTTP/1.0\r\n";
        $request .= "Host: ".$purl['host']."\r\n";
        if ($this->agent) {
            $request .= "User-Agent: ".$this->agent."\r\n";
        }
        $request .= "Connection: Close\r\n";
        //$request .= "Accept-Encoding: gzip\r\n";

        if ($req->modified) {
            $request .= "If-Modified-Since: {$req->modified}\r\n";
        }

        // Basic�F�ؗp�̃w�b�_
        if (isset($purl['user']) && isset($purl['pass'])) {
            $request .= "Authorization: Basic ".base64_encode($purl['user'].":".$purl['pass'])."\r\n";
        }

        // �ǉ��w�b�_
        if ($req->headers) {
            $request .= $req->headers;
        }

        // POST�̎��̓w�b�_��ǉ����Ė�����URL�G���R�[�h�����f�[�^��Y�t
        if (strtoupper($req->method) == 'POST') {
            // �ʏ��URL�G���R�[�h����
            if ($postdata_urlencode) {
                while (list($name, $value) = each($req->post)) {
                    $POST[] = $name.'='.urlencode($value);
                }
                $postdata_content_type = 'application/x-www-form-urlencoded';

            // �����O�C���̂Ƃ��Ȃǂ�URL�G���R�[�h���Ȃ�
            } else {
                while (list($name, $value) = each($req->post)) {
                    $POST[] = $name.'='.$value;
                }
                $postdata_content_type = 'text/plain';
            }
            $postdata = implode('&', $POST);
            $request .= 'Content-Type: '.$postdata_content_type."\r\n";
            $request .= 'Content-Length: '.strlen($postdata)."\r\n";
            $request .= "\r\n";
            $request .= $postdata;
        } else {
            $request .= "\r\n";
        }

        // WEB�T�[�o�֐ڑ�
        if ($this->timeout) {
            $fp = fsockopen($send_host, $send_port, $errno, $errstr, $this->timeout);
        } else {
            $fp = fsockopen($send_host, $send_port, $errno, $errstr);
        }

        if (!$fp) {
            $res->code = $errno; // ex) 602
            $res->message = $errstr; // ex) "Connection Failed"
            return $res;
        }

        fputs($fp, $request);

        // header response
        while (!feof($fp)) {
            $l = fgets($fp,128000);

            // ex) HTTP/1.1 304 Not Modified
            if (preg_match('/^(.+?): (.+)\r\n/', $l, $matches)) {
                $res->headers[$matches[1]] = $matches[2];

            } elseif (preg_match("/HTTP\/1\.\d (\d+) (.+)\r\n/", $l, $matches)) {
                $res->code = $matches[1];
                $res->message = $matches[2];
                $res->headers['HTTP'] = rtrim($l);

            } elseif ($l == "\r\n") {
                break;
            }
        }

        // body response
        if (!$only_header) {
            $body = '';
            while (!feof($fp)) {
                $body .= fread($fp, 4096);
            }
            $res->content = $body;
        }

        fclose($fp);

        // ���_�C���N�g(301 Moved, 302 Found)��ǐ�
        // RFC2616 - Section 10.3
        /*if ($GLOBALS['trace_http_redirect']) {
            if ($res->code == '301' || ($res->code == '302' && $req->is_safe_method())) {
                if (empty($this->redirectCache)) {
                    $this->maxRedirect   = 5;
                    $this->redirectCount = 0;
                    $this->redirectCache = array();
                }
                while ($res->is_redirect() && isset($res->headers['Location']) && $this->redirectCount < $this->maxRedirect) {
                    $this->redirectCache[] = $res;
                    $req->setUrl($res->headers['Location']);
                    $res = $this->request($req);
                    $this->redirectCount++;
                }
            }
        } elseif ($res->is_redirect() && isset($res->headers['Location'])) {
            $res->message .= " (Location: <a href=\"{$res->headers['Location']}\">{$res->headers['Location']}</a>)";
        }*/

        return $res;
    }

}


/**
 * Request �N���X
 */
class Request{

    var $method; // GET, POST, HEAD�̂����ꂩ(�f�t�H���g��GET�APUT�͂Ȃ�)
    var $url; // http://����n�܂�URL( http://user:pass@host:port/path?query )
    var $headers; // �C�ӂ̒ǉ��w�b�_�B������B
    var $content; // �C�ӂ̃f�[�^�̌ł܂�B
    var $post;    // POST�̎��ɑ��M����f�[�^���i�[�����z��("�ϐ���"=>"�l")
    var $proxy; // ('host'=>"", 'port'=>"")

    var $modified;

    /**
     * �R���X�g���N�^
     */
    function Request()
    {
        $this->method = 'GET';
        $this->url = '';
        $this->headers = '';
        $this->content = false;
        $this->post = array();
        $this->proxy = array();
        $this->modified = false;
    }

    /**
     * setProxy
     */
    function setProxy($host, $port)
    {
        $this->proxy['host'] = $host;
        $this->proxy['port'] = $port;
    }

    /**
     * setMethod
     */
    function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * setUrl
     */
    function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * setModified
     */
    function setModified($modified)
    {
        $this->modified = $modified;
    }

    /**
     * setHeaders
     */
    function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * is_safe_method
     */
    function is_safe_method()
    {
        $method = strtoupper($this->method);
        // RFC2616 - Section 9
        if ($method == 'GET' || $method == 'HEAD'){
            return true;
        } else {
            return false;
        }
    }
}

/**
 * Response �N���X
 */
class Response{

    var $code; // ���N�G�X�g�̌��ʂ��������l
    var $message;  // code�ɑΉ�����l�Ԃ��ǂ߂�Z��������B
    var $headers;    // �z��
    var $content; // ���e�B�C�ӂ̃f�[�^�̌ł܂�B

    /**
     * �R���X�g���N�^
     */
    function Response()
    {
        $code = false;
        $message = '';
        $content = false;
        $headers = array();
    }

    /**
     * is_success
     */
    function is_success()
    {
        if ($this->code == 200 || $this->code == 206 || $this->code == 304) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * is_error
     */
    function is_error()
    {
        if ($this->code == 200 || $this->code == 206 || $this->code == 304) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * is_redirect
     */
    function is_redirect()
    {
        if ($this->code == 301 || $this->code == 302) {
            return true;
        } else {
            return false;
        }
    }

/*
    000, 'Unknown Error',
    200, 'OK',
    201, 'CREATED',
    202, 'Accepted',
    203, 'Partial Information',
    204, 'No Response',
    206, 'Partial Content',
    301, 'Moved',
    302, 'Found',
    303, 'Method',
    304, 'Not Modified',
    400, 'Bad Request',
    401, 'Unauthorized',
    402, 'Payment Required',
    403, 'Forbidden',
    404, 'Not Found',
    500, 'Internal Error',
    501, 'Not Implemented',
    502, 'Bad Response',
    503, 'Too Busy',
    600, 'Bad Request in Client',
    601, 'Not Implemented in Client',
    602, 'Connection Failed',
    603, 'Timed Out',
*/

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
