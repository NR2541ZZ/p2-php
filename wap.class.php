<?php
// WWW Access on PHP
// http://member.nifty.ne.jp/hippo2000/perltips/LWP.html ���Q�l�ɂ������悤�ȊȈՂ̂��̂�

/**
 * UserAgent �N���X
 *
 *	setAgent() : ua ���Z�b�g����B
 *	setTimeout()
 *	request() : ���N�G�X�g���T�[�o�ɑ��M���āA���X�|���X��Ԃ��B
 */
class UserAgent{
	/* 
	setAgent() : ua ���Z�b�g����B
	setTimeout()
	request() : ���N�G�X�g���T�[�o�ɑ��M���āA���X�|���X��Ԃ��B
	*/
	
	//=======================================
	var $agent;  // User-Agent�B�A�v���P�[�V�����̖��O�B
	var $timeout;
	
	/**
	 * setAgent
	 */
	function setAgent($agent_name){
		$this->agent = $agent_name;
		return;
	}
	
	/**
	 * setTimeout
	 */
	function setTimeout($timeout)
	{
		$this->timeout = $timeout;
		return;
	}
	
	/**
	 * request
	 *
	 * http://www.spencernetwork.org/memo/tips-3.php ���Q�l�ɂ����Ē����܂����B
	 */
	function request($req)
	{
		$res = new Response;
		
		$purl = parse_url($req->url); // URL����
		if (isset($purl['query'])) { // �N�G���[
		    $purl['query'] = "?".$purl['query'];
		} else {
		    $purl['query'] = "";
		}
	    if (!isset($purl['port'])){$purl['port'] = 80;} // �f�t�H���g�̃|�[�g��80
	
		// �v���L�V
		if ($req->proxy) {
			$send_host = $req->proxy['host'];
			$send_port = $req->proxy['port'];
			$send_path = $req->url;
		} else {
			$send_host = $purl['host'];
			$send_port = $purl['port'];
			$send_path = $purl['path'].$purl['query'];
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
		if (strtoupper($req->method) == "POST") {
		    while (list($name, $value) = each($req->post)) {
		        $POST[] = $name."=".urlencode($value);
		    }
		    $postdata = implode("&", $POST);
		    $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
		    $request .= "Content-Length: ".strlen($postdata)."\r\n";
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
		
		if ($fp) {
			fputs($fp, $request);
			$body = "";
			while (!feof($fp)) {
			
				if ($start_here) {
					$body .= fread($fp, 4096);
				} else {
					$l = fgets($fp,128000);
					//echo $l."<br>"; //
					if( preg_match("/HTTP\/1\.\d (\d+) (.+)\r\n/", $l, $matches) ){ // ex) HTTP/1.1 304 Not Modified
						$res->code = $matches[1];
						$res->message = $matches[2];
					}elseif($l=="\r\n"){
						$start_here = true;
					}
				}
				
			}
			
			fclose ($fp);
			$res->content = $body;
			return $res;
			
		}else{
			$res->code = $errno; //602
			$res->message = $errstr; //"Connection Failed"
			return $res;
		}
	}

}

//======================================================
// Request �N���X
//======================================================
class Request{

	var $method; //GET, POST, HEAD�̂����ꂩ(�f�t�H���g��GET�APUT�͂Ȃ�) 
	var $url; //http://����n�܂�URL( http://user:pass@host:port/path?query )
	var $headers; //�C�ӂ̒ǉ��w�b�_�B
	var $content; // �C�ӂ̃f�[�^�̌ł܂�B
	var $post;    // POST�̎��ɑ��M����f�[�^���i�[�����z��("�ϐ���"=>"�l")
	var $proxy; // ('host'=>"", 'port'=>"")
	
	var $modified;
	
	//===============================
	function Request(){
		$this->method = "GET";
		$this->url = "";
		$this->headers = "";
		$this->content = false;
		$this->post = array();
		$this->modified = false;
		$this->proxy = array();
	}
	
	function setProxy($host, $port){
		$this->proxy['host'] = $host;
		$this->proxy['port'] = $port;
		return;
	}
	
	function setMethod($method){
		$this->method = $method;
		return;
	}
	
	function setUrl($url){
		$this->url = $url;
		return;
	}

	function setModified($modified){
		$this->modified = $modified;
		return;
	}

	function setHeaders($headers){
		$this->headers = $headers;
		return;
	}

}

//======================================================
// Response �N���X
//======================================================
class Response{

	var $code; //���N�G�X�g�̌��ʂ��������l
	var $message;  // code�ɑΉ�����l�Ԃ��ǂ߂�Z��������B
	var $headers;
	var $content; // ���e�B�C�ӂ̃f�[�^�̌ł܂�B
	
	function Response(){
		$code = false;
		$message = "";
		$content = false;
	}
	
	function is_success(){
		if($this->code == 200 || $this->code == 206 || $this->code == 304){
			return true;
		}else{
			return false;
		}
	}

	function is_error(){
		if($this->code == 200 || $this->code == 206 || $this->code == 304){
			return false;
		}else{
			return true;
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

?>