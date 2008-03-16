<?php
/* ImageCache2 - �摜�̃_�E�����[�h�E�T���l�C���쐬 */

// {{{ p2��{�ݒ�ǂݍ���&�F��

require_once 'conf/conf.inc.php';

$_login->authorize();

if (!$_conf['expack.ic2.enabled']) {
    exit('<html><body><p>ImageCache2�͖����ł��B<br>conf/conf_admin_ex.inc.php �̐ݒ��ς��Ă��������B</p></body></html>');
}

// }}}
// {{{ ������

// ���C�u�����ǂݍ���
require_once 'PEAR.php';
require_once 'DB/DataObject.php';
require_once 'HTTP/Client.php';
require_once P2EX_LIBRARY_DIR . '/ic2/findexec.inc.php';
require_once P2EX_LIBRARY_DIR . '/ic2/loadconfig.inc.php';
require_once P2EX_LIBRARY_DIR . '/ic2/database.class.php';
require_once P2EX_LIBRARY_DIR . '/ic2/db_images.class.php';
require_once P2EX_LIBRARY_DIR . '/ic2/thumbnail.class.php';

// �󂯕t����MIME�^�C�v
$mimemap = array('image/jpeg' => '.jpg', 'image/png' => '.png', 'image/gif' => '.gif');

// �ݒ�t�@�C���ǂݍ���
$ini = ic2_loadconfig();

// DB_DataObject�̐ݒ�
$_dbdo_options = &PEAR::getStaticProperty('DB_DataObject','options');
$_dbdo_options = array('database' => $ini['General']['dsn'], 'debug' => false, 'quote_identifiers' => true);

// }}}
// {{{ prepare

// �p�����[�^��ݒ�
$id       = isset($_REQUEST['id'])    ? intval($_REQUEST['id']) : null;
$uri      = isset($_REQUEST['uri'])   ? $_REQUEST['uri'] : (isset($_REQUEST['url']) ? $_REQUEST['url'] : null);
$file     = isset($_REQUEST['file'])  ? $_REQUEST['file'] : null;
$force    = !empty($_REQUEST['f']);   // �����X�V
$thumb    = isset($_REQUEST['t'])     ? intval($_REQUEST['t']) : 0;       // �T���l�C���^�C�v
$redirect = isset($_REQUEST['r'])     ? intval($_REQUEST['r']) : 1;       // �\�����@
$rank     = isset($_REQUEST['rank'])  ? intval($_REQUEST['rank']) : 0;    // �����L���O
$memo     = (isset($_REQUEST['memo']) && strlen($_REQUEST['memo']) > 0) ? $_REQUEST['memo'] : null; // ����
$referer  = (isset($_REQUEST['ref']) && strlen($_REQUEST['ref']) > 0)   ? $_REQUEST['ref'] : null;  // ���t�@��

/*if (!isset($uri) && false !== ($url = getenv('PATH_INFO'))) {
    $uri = 'http:/' . $url;
}*/
if (empty($id) && empty($uri) && empty($file)) {
    ic2_error('x06', 'URL�܂��̓t�@�C����������܂���B', false);
}

if (!empty($uri)) {
    $uri = preg_replace('{^(https?://)ime\\.(nu|st)/}', '$1', $uri);
    $pURL = @parse_url($uri);
    if (!$pURL || !preg_match('/^(https?)$/', $pURL['scheme']) || empty($pURL['host']) || empty($pURL['path'])) {
        ic2_error('x06', '�s����URL�ł��B', false);
    }

    // �������ځ[��z�X�g�̂Ƃ�
    if ($ini['Getter']['reject_hosts']) {
        $pattern = preg_quote($ini['Getter']['reject_hosts'], '/');
        $pattern = str_replace(',', '|', $pattern);
        $pattern = '/(' . $pattern . ')$/i';
        if (preg_match($pattern, $pURL['host'])) {
            ic2_error('x01', '���ځ[��Ώۃz�X�g�ł��B');
        }
    }

    // �������ځ[��URL�̂Ƃ�
    if ($ini['Getter']['reject_regex']) {
        $pattern = str_replace('/', '\\/', $ini['Getter']['reject_regex']);
        $pattern = '/(' . $pattern . ')$/i';
        if (preg_match($pattern, $uri)) {
            ic2_error('x01', '���ځ[��Ώ�URL�ł��B');
        }
    }

    $doDL = true;
} else {
    if (isset($file) && !preg_match('/^(?P<size>[1-9][0-9]*)_(?P<md5>[0-9a-f]{32})(?:\.(?P<ext>jpg|png|gif))?$/', $file, $fdata)) {
        ic2_error('x06', '�s���ȃt�@�C�����ł��B', false);
    }
    $doDL = false;
}

// �l�̒���
if ($thumb < 1 || $thumb > 3 ) { $thumb = 0; }
if ($rank < -1) { $rank = -1; } elseif ($rank > 5) { $rank = 5; }
if ($memo === '') { $memo = null; }

$thumbnailer = &new ThumbNailer($thumb);

// }}}
// {{{ sleep

if ($doDL) {
    // �����摜��URI�ɑ΂���N�G�����i�قځj�����ɔ��s���ꂽ�Ƃ��̏d��GET��h��
    // sleep�������Ԃ̓v���Z�X�̎��s���ԂɊ܂܂�Ȃ��̂œƎ��Ƀ^�C�}�[��p�ӂ���i�������[�v����j
    $tmpchecker = $ini['General']['cachedir'] . '/q_' . md5($uri);
    if (file_exists($tmpchecker)) {
        $offtimer = ini_get('max_execution_time');
        if ($offtimer == 0) {
            $offtimer = 30;
        }
        while (file_exists($tmpchecker)) {
            sleep(1); // 1�b��~
            $offtimer--;
            if ($offtimer < 0) {
                ic2_error(504);
            }
        }
    }

    // �e���|�����t�@�C�����쐬�A�I�����Ɏ����폜
    touch($tmpchecker);
    // exit�����Ƃ���register_shutdown_function()�������Ȃ��悤�Ȃ̂�
    // ic2_display(),ic2_error()�e�֐��̐擪��ic2_removeTmpFile()���R�[�����邱�Ƃɂ����B
    // �X�}�[�g�Ƃ͌��������������Ғʂ�̓���͂��Ă����̂ł悵�Ƃ���B
    //register_shutdown_function('ic2_removeTmpFile');
}

// }}}
// {{{ search

// �摜���L���b�V������Ă��邩�m�F
$search = &new IC2DB_Images;
$retry = false;
if ($memo !== null) {
    $memo = $search->uniform($memo, 'SJIS-win');
}

if ($doDL) {
    $result = $search->get($uri);
} else {
    if (isset($id)) {
        $search->whereAddQuoted('id', '=', $id);
    } else {
        $search->whereAddQuoted('size', '=', $fdata['size']);
        $search->whereAddQuoted('md5', '=', $fdata['md5']);
    }
    $result = $search->find(true);
    if (!$result) {
        ic2_error('404');
    }
    $force = false;
}

if ($result) {
    // �E�B���X�X�L�����ɂЂ����������t�@�C����������I���B
    if (!$force && $search->mime == 'clamscan/infected') {
        ic2_error('x04', '', false);
    }
    // ���ځ[��t���O�irank�����j�������Ă�����I���B
    if (!$force && $search->rank < 0 ) {
        ic2_error('x01', '', false);
    }
    $filepath = $thumbnailer->srcPath($search->size, $search->md5, $search->mime);
    $params = array('uri' => $search->uri, 'name' => $search->name, 'size' => $search->size,
                    'md5' => $search->md5, 'width' => $search->width, 'height' => $search->height,
                    'mime' => $search->mime, 'memo' => $search->memo);

    // ���������@�\���L���̂Ƃ�
    if ($ini['General']['automemo'] && !is_null($memo) && !strstr($search->memo, $memo)) {
        if (!empty($search->memo)) {
            $memo .= ' ' . $search->memo;
        }
        $update = &new IC2DB_Images;
        $update->memo = $memo;
        $update->whereAddQuoted('uri', '=', $uri);
        $update->update();
    }

    // �t�@�C�����ۑ�����Ă���΂���ł悵�A�ۑ�����Ă��Ȃ���΃��R�[�h���폜����B
    if (file_exists($filepath)) {
        if ($force) {
            $_size = $search->size;
            $_md5  = $search->md5;
            $_mime = $search->mime;
            $time  = $search->time;
        } else {
            ic2_finish($filepath, $thumb, $params, false);
        }
    } else {
        $retry = true;
        $force = false;
        $_size = $search->size;
        $_md5  = $search->md5;
        $_mime = $search->mime;
    }
} else {
    $filepath = '';
}

// �摜���u���b�N���X�g�ɂ��邩�m�F
require_once P2EX_LIBRARY_DIR . '/ic2/db_blacklist.class.php';
$blacklist = &new IC2DB_BlackList;
if ($blacklist->get($uri)) {
    switch ($blacklist->type) {
        case 0:
            $errcode = 'x05'; // ���������ς�
            break;
        case 1:
            $errcode = 'x01'; // ���ځ[��
            break;
        case 2:
            $errcode = 'x04'; // �E�B���X����
            break;
        default:
            $errcode = 'x06'; // ???
    }
    ic2_error($errcode, '', false);
}

// �摜���G���[���O�ɂ��邩�m�F
if (!$force && $ini['Getter']['checkerror']) {
    require_once P2EX_LIBRARY_DIR . '/ic2/db_errors.class.php';
    $errlog = &new IC2DB_Errors;
    if ($errlog->get($uri)) {
        ic2_error($errlog->errcode, '', false);
    }
}

// }}}
// {{{ init http-client

// �ݒ���m�F
$conn_timeout = (isset($ini['Getter']['conn_timeout']) && $ini['Getter']['conn_timeout'] > 0)
    ? (float) $ini['Getter']['conn_timeout'] : 60.0;
$read_timeout = (isset($ini['Getter']['read_timeout']) && $ini['Getter']['read_timeout'] > 0)
    ? (int) $ini['Getter']['read_timeout'] : 60;
$ic2_ua = (!empty($_conf['expack.user_agent']))
    ? $_conf['expack.user_agent'] : $_SERVER['HTTP_USER_AGENT'];

// �L���b�V������Ă��Ȃ���΁A�擾�����݂�
$client = &new HTTP_Client;
$client->setRequestParameter('timeout', $conn_timeout);
$client->setRequestParameter('readTimeout', array($read_timeout, 0));
$client->setMaxRedirects(3);
$client->setDefaultHeader('User-Agent', $ic2_ua);
if ($force && $time) {
    $client->setDefaultHeader('If-Modified-Since', gmdate('D, d M Y H:i:s \G\M\T', $time));
}

// �v���L�V�ݒ�
if ($ini['Proxy']['enabled'] && $ini['Proxy']['host'] && $ini['Proxy']['port']) {
    $client->setRequestParameter('proxy_host', $ini['Proxy']['host']);
    $client->setRequestParameter('proxy_port', $ini['Proxy']['port']);
    if ($ini['Proxy']['user']) {
        $client->setRequestParameter('proxy_user', $ini['Proxy']['user']);
        $client->setRequestParameter('proxy_pass', $ini['Proxy']['pass']);
        $proxy_auth_data = base64_encode($ini['Proxy']['user'] . ':' . $ini['Proxy']['pass']);
        $client->setDefaultHeader('Proxy-Authorization', 'Basic ' . $proxy_auth_data);
    }
}

// ���t�@���ݒ�
if (is_null($referer)) {
    $send_referer = (boolean)$ini['Getter']['sendreferer'];
    if ($send_referer) {
        if ($ini['Getter']['norefhosts']) {
            $pattern = preg_quote($ini['Getter']['norefhosts'], '/');
            $pattern = str_replace(',', '|', $pattern);
            $pattern = '/' . $pattern . '/i';
            if (preg_match($pattern, $pURL['host'])) {
                $send_referer = false;
            }
        }
    } elseif ($ini['Getter']['refhosts']) {
        $pattern = preg_quote($ini['Getter']['refhosts'], '/');
        $pattern = str_replace(',', '|', $pattern);
        $pattern = '/' . $pattern . '/i';
        if (preg_match($pattern, $pURL['host'])) {
            $send_referer = true;
        }
    }
    if ($send_referer) {
        $referer = $uri . '.html';
    }
}

if (is_string($referer)) {
    $client->setDefaultHeader('Referer', $referer);
}

// }}}
// {{{ head

// �܂���HEAD�Ń`�F�b�N
$client_h = clone($client);
$code = $client_h->head($uri);
if (PEAR::isError($code)) {
    ic2_error('x02', $code->getMessage());
}
$head = &$client_h->currentResponse();

// 304 Not Modified �̂Ƃ�
if ($filepath && $force && $time && $code == 304) {
    ic2_finish($filepath, $thumb, $params, false);
}

// 200�ȊO�̂Ƃ��͎��s�Ƃ݂Ȃ�
if ($code != 200) {
    ic2_error($code);
}

// Content-Type����
if (isset($head['headers']['content-type'])) {
    $conent_type = $head['headers']['content-type'];
    if (!preg_match('{^image/}', $conent_type) && $conent_type != 'application/x-shockwave-flash') {
        ic2_error('x02', "�T�|�[�g����Ă��Ȃ��t�@�C���^�C�v�ł��B({$conent_type})");
    }
}

// Content-Length����
if (isset($head['headers']['content-length'])) {
    $conent_length = (int)$head['headers']['content-length'];
    $maxsize = $ini['Source']['maxsize'];
    if (preg_match('/(\d+\.?\d*)([KMG])/i', $maxsize, $m)) {
        $maxsize = si2int($m[1], $m[2]);
    } else {
        $maxsize = (int)$maxsize;
    }
    if (0 < $maxsize && $maxsize < $conent_length) {
        ic2_error('x03', "�t�@�C���T�C�Y���傫�����܂��B(file:{$conent_length}; max:{$maxsize};)");
    }
}

unset($client_h, $code, $head);

// }}}
// {{{ get

// �_�E�����[�h
$code = $client->get($uri);
if (PEAR::isError($code)) {
    ic2_error('x02', $code->getMessage());
} elseif ($code != 200) {
    ic2_error($code);
}

$response = &$client->currentResponse();

// �ꎞ�t�@�C���ɕۑ�
$tmpfile = tempnam($ini['General']['cachedir'], 'tmp_');
$fp = @fopen($tmpfile, 'wb');
if (!$fp) {
    ic2_error('x02', "fopen���s�B($tmpfile)");
}
fwrite($fp, $response['body']);
fclose($fp);

// }}}
// {{{ check

// �E�B���X�X�L����
if ($ini['Getter']['virusscan']) {
    $searchpath = $thumbnailer->ini['Getter']['clamav'];
    if ($ini['Getter']['virusscan'] == 2) {
        $clamscan = 'clamdscan';
    } else {
        $clamscan = 'clamscan';
    }
    if (findexec($clamscan, $searchpath)) {
        if ($searchpath) {
            $clamscan = $searchpath . DIRECTORY_SEPARATOR . $clamscan;
        }
        $scan_command = $clamscan . ' --stdout ' . escapeshellarg(realpath($tmpfile));
        $scan_result  = @exec($scan_command, $scan_stdout, $scan_result);
        if ($scan_result == 1) {
            $params = array(
                'uri'    => $uri,
                'host'   => $pURL['host'],
                'name'   => basename($pURL['path']),
                'size'   => filesize($tmpfile),
                'md5'    => md5_file($tmpfile),
                'width'  => 0,
                'height' => 0,
                'mime' => 'clamscan/infected',
                'memo' => $memo
            );
            ic2_aborn($params, true);
            @unlink($tmpfile);
            ic2_error('x04', '�E�B���X�𔭌����܂����B');
        }
    }
}

// �摜���𒲂ׂ�BMIME�^�C�v�̓T�[�o�������Ă������̂�M�����Ȃ��B
$info = @getimagesize($tmpfile);
if (!$info) {
    ic2_error('x02', '�摜�T�C�Y�̎擾�Ɏ��s���܂����B');
} elseif (!isset($info['mime'])) {
    // < PHP4.3.0
    ic2_error('x02', 'MIME�^�C�v�̎擾�Ɏ��s���܂����B');
} else {
    $mime = $info['mime'];
}
if (!in_array($mime, array_keys($mimemap))) {
    ic2_error('x02', "�T�|�[�g����Ă��Ȃ��t�@�C���^�C�v�ł��B({$mime})");
}

// ���K�̉摜�Ȃ�A�t�@�C���T�C�Y��MD5�`�F�b�N�T�����v�Z
$host = $pURL['host'];
$name = basename($pURL['path']);
$size = filesize($tmpfile);
$md5  = md5_file($tmpfile);
$width  = $info[0];
$height = $info[1];

// �����X�V�����݂����̂́A�X�V����Ă��Ȃ������Ƃ��i���X�|���X�R�[�h��200�j
if ($filepath && $force && $time && $size == $_size && $md5 == $_md5 && $mime == $_mime) {
    ic2_finish($filepath, $thumb, $params, false);
}

$params = array('uri' => $uri, 'host' => $host, 'name' => $name, 'size' => $size, 'md5' => $md5,
                'width' => $width, 'height' => $height, 'mime' => $mime, 'memo' => $memo);

// �t�@�C���T�C�Y��������z���Ă��Ȃ����m�F
ic2_checkSizeOvered($tmpfile, $params);

// �����摜�����ځ[�񂳂�Ă��邩�m�F
$rank = ic2_checkAbornedFile($tmpfile, $params);

// }}}
// {{{ finish

// ���ׂẴ`�F�b�N���p�X�����Ȃ�A�ۑ��p�̖��O�Ƀ��l�[������
$newfile = $thumbnailer->srcPath($size, $md5, $mime);
$newdir = dirname($newfile);
if (!is_dir($newdir) && !@mkdir($newdir)) {
    ic2_error('x02', "�f�B���N�g�����쐬�ł��܂���ł����B({$newdir})");
}
if (($force || !file_exists($newfile)) && !@rename($tmpfile, $newfile)) {
    ic2_error('x02', "���l�[�����s�B({$tmpfile} �� {$newfile})");
}
@chmod($newfile, 0644);

// �f�[�^�x�[�X�Ƀt�@�C�������L�^����
$record = &new IC2DB_Images;
if ($retry && $size == $_size && $md5 == $_md5 && $mime == $_mime) {
    $record->time = time();
    if ($ini['General']['automemo'] && !is_null($memo)) {
        $record->memo = $memo;
    }
    $record->whereAddQuoted('uri',  '=', $uri);
    $record->whereAddQuoted('size', '=', $size);
    $record->whereAddQuoted('md5',  '=', $md5);
    $record->whereAddQuoted('mime', '=', $mime);
    $record->update();
} else {
    $record->uri = $uri;
    $record->host = $host;
    $record->name = $name;
    $record->size = $size;
    $record->md5 = $md5;
    $record->width = $width;
    $record->height = $height;
    $record->mime = $mime;
    $record->time = time();
    $record->rank = $rank;
    if ($ini['General']['automemo'] && !is_null($memo)) {
        $record->memo = $memo;
    }
    $record->insert();
}

// �摜��\��
ic2_finish($newfile, $thumb, $params, $force);

// }}}
// {{{ �֐�

function ic2_aborn($params, $infected = false)
{
    global $ini;
    extract($params);

    $aborn = &new IC2DB_Images;
    $aborn->uri = $uri;
    $aborn->host = $host;
    $aborn->name = $name;
    $aborn->size = $size;
    $aborn->md5 = $md5;
    $aborn->width = $width;
    $aborn->height = $height;
    $aborn->mime = $mime;
    $aborn->time = time();
    $aborn->rank = $infected ? -4 : -1;
    if ($ini['General']['automemo'] && !is_null($memo)) {
        $aborn->memo = $memo;
    }
    return $aborn->insert();
}

function ic2_checkAbornedFile($tmpfile, $params)
{
    global $ini;
    extract($params);

    // �u���b�N���X�g����
    $bl_check = &new IC2DB_BlackList;
    $bl_check->whereAddQuoted('size', '=', $size);
    $bl_check->whereAddQuoted('md5',  '=', $md5);
    if ($bl_check->find(true)) {
        $bl_add = clone($bl_check);
        $bl_add->uri = $uri;
        $bl_add->insert();
        switch ($bl_check->type) {
            case 0:
                $errcode = 'x05'; // No More
            case 1:
                $errcode = 'x01'; // Aborn
            case 2:
                $errcode = 'x04'; // Virus
            default:
                $errcode = 'x06'; // Unknown
        }
        // �����ɂ́A���̉\��������Ȃ�����������100%�ł͂Ȃ�
        ic2_error($errcode, '�u���b�N���X�g�ɂ���摜�Ɠ������e�ł��B', false);
    }

    // ���ځ[��摜����
    $check = &new IC2DB_Images;
    $check->whereAddQuoted('size', '=', $size);
    $check->whereAddQuoted('md5',  '=', $md5);
    //$check->whereAddQuoted('mime', '=', $mime); // Size��MD5�ŏ\��
    // �����̂��قȂ�URL�ŕ����o�^����Ă��āA�����N���Ⴄ�\��������̂�
    // �i���ʂɎg�����ɂ͋N����Ȃ�...�Ǝv���B���Ȃ��Ƃ��N����ɂ����͂��j
    $check->orderByArray(array('rank' => 'ASC'));
    if ($check->find(true)) {
        if ($check->rank < 0) {
            @unlink($tmpfile);
            ic2_aborn($params);
            // ����ł́i���Ԃ񂸂��Ɓj -1 or -4 ���������A�ꉞ
            if ($check->rank >= -5) {
                $errcode = 'x0' . abs($check->rank);
            } else {
                $errcode = 'x06'; // Unknown
            }
            // �����ɂ́A�ȉ�����
            if ($check->rank == -4) {
                $errmsg = '�E�B���X�Ɋ������Ă����摜�Ɠ������e�ł��B';
            } else {
                $errmsg = '���ɂ��ځ[�񂳂�Ă���摜�Ɠ������e�ł��B';
            }
            ic2_error($errcode, $errmsg);
        } else {
            return $check->rank;
        }
    }

    return 0;
}

function ic2_checkSizeOvered($tmpfile, $params)
{
    global $ini;
    extract($params);

    $isError = false;

    $maxsize = $ini['Source']['maxsize'];
    if (preg_match('/(\d+\.?\d*)([KMG])/i', $maxsize, $m)) {
        $maxsize = si2int($m[1], $m[2]);
    } else {
        $maxsize = (int)$maxsize;
    }
    if (0 < $maxsize && $maxsize < $conent_length) {
        $isError = true;
        $errmsg = "�t�@�C���T�C�Y���傫�����܂��B(file:{$size}; max:{$maxsize};)";
    }

    $maxwidth = (int)$ini['Source']['maxwidth'] ;
    $maxheight = (int)$ini['Source']['maxheight'];
    if ((0 < $maxwidth && $maxwidth < $width) ||
        (0 < $maxheight && $maxheight < $height)
    ) {
        $isError = true;
        $errmsg = "�摜�T�C�Y���傫�����܂��B(file:{$width}x{$height}; max:{$maxwidth}x{$maxheight};)";
    }

    if ($isError) {
        @unlink($tmpfile);
        ic2_aborn($params);
        ic2_error('x03', $errmsg);
    }

    return true;
}

function ic2_display($path)
{
    global $_conf, $ini, $thumb, $redirect, $id, $uri, $file;

    ic2_removeTmpFile();

    $name = basename($path);
    $ext = strrchr($name, '.');

    switch ($redirect) {
        case 1:
            header("Location: {$path}");
            exit;
        case 2:
            switch ($ext) {
                case '.jpg': header("Content-Type: image/jpeg; name=\"{$name}\""); break;
                case '.png': header("Content-Type: image/png; name=\"{$name}\""); break;
                case '.gif': header("Content-Type: image/gif; name=\"{$name}\""); break;
                default:
                    if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE') ||
                        strstr($_SERVER['HTTP_USER_AGENT'], 'Opera')
                    ) {
                        header("Content-Type: application/octetstream; name=\"{$name}\"");
                    } else {
                        header("Content-Type: application/octet-stream; name=\"{$name}\"");
                    }
            }
            header("Content-Disposition: inline; filename=\"{$name}\"");
            header('Content-Length: ' . filesize($path));
            readfile($path);
            exit;
        default:
            require_once 'HTML/Template/Flexy.php';
            require_once 'HTML/QuickForm.php';
            require_once 'HTML/QuickForm/Renderer/ObjectFlexy.php';

            // conf.inc.php�ňꊇstripslashes()���Ă��邯�ǁAHTML_QuickForm�ł��Ǝ���stripslashes()����̂ŁB
            // �o�O�̉����ƂȂ�\�����ے�ł��Ȃ��E�E�E
            if (get_magic_quotes_gpc()) {
                $_GET = array_map('addslashes_r', $_GET);
                $_POST = array_map('addslashes_r', $_POST);
                $_REQUEST = array_map('addslashes_r', $_REQUEST);
            }

            $img_p = isset($uri) ? 'uri=' . rawurlencode($uri) : (isset($id) ? 'id=' . $id : 'file=' . $file);
            if (isset($uri)) {
                $img_o = 'uri';
                $img_p = $uri;
            } elseif (isset($id)) {
                $img_o = 'id';
                $img_p = $id;
            } else {
                $img_o = 'file';
                $img_p = $file;
            }
            $img_q = $img_o . '=' . rawurlencode($img_p);

            // QuickForm�̏�����
            $_constants = array(
                's' => '�쐬',
                't' => $thumb,
                'u' => $img_p,
                'v' => $img_o,
            );
            $_defaults = array(
                'q' => $ini["Thumb{$thumb}"]['quality'],
                'r'  => '0',
            );
            $mobile = &Net_UserAgent_Mobile::singleton();
            $qa = 'size=3 maxlength=3';
            if ($mobile->isDoCoMo()) {
                $qa .= ' istyle=4';
            } elseif ($mobile->isEZweb()) {
                $qa .= ' format=*N';
            } elseif ($mobile->isVodafone()) {
                $qa .= ' mode=numeric';
            }
            $qf = &new HTML_QuickForm('imgmaker', 'get', 'ic2_mkthumb.php');
            $qf->setConstants($_constants);
            $qf->setDefaults($_defaults);
            $qf->addElement('hidden', 't');
            $qf->addElement('hidden', 'u');
            $qf->addElement('hidden', 'v');
            $qf->addElement('text', 'q', '�i��', $qa);
            $qf->addElement('select', 'r', '��]', array('0' => '�Ȃ�', '90' => '�E��90��', '270' => '����90��', '180' => '180��'));
            $qf->addElement('checkbox', 'p', '�g����');
            $qf->addElement('submit', 's');

            // Flexy��QurickForm_Renderer�̏�����
            $_flexy_options = array(
                'locale' => 'ja',
                'charset' => 'cp932',
                'compileDir' => $ini['General']['cachedir'] . '/' . $ini['General']['compiledir'],
                'templateDir' => P2EX_LIBRARY_DIR . '/ic2/templates',
                'numberFormat' => '', // ",0,'.',','" �Ɠ���
            );
            $flexy = &new HTML_Template_Flexy($_flexy_options);
            $rdr = &new HTML_QuickForm_Renderer_ObjectFlexy($flexy);
            $qf->accept($rdr);

            // �\��
            $flexy->setData('title', '�L���b�V������');
            if (!$_conf['ktai']) {
                $flexy->setData('pc', true);
                $flexy->setData('skin', $GLOBALS['skin_name']);
                //$flexy->setData('stylesheets', array('css'));
                //$flexy->setData('javascripts', array('js'));
            } else {
                $flexy->setData('pc', false);
                $k_color = array();
                $k_color['c_bgcolor'] = isset($_conf['mobile.background_color']) ? $_conf['mobile.background_color'] : '';
                $k_color['c_text']  = isset($_conf['mobile.text_color'])  ? $_conf['mobile.text_color']  : '';
                $k_color['c_link']  = isset($_conf['mobile.link_color'])  ? $_conf['mobile.link_color']  : '';
                $k_color['c_vlink'] = isset($_conf['mobile.vlink_color']) ? $_conf['mobile.vlink_color'] : '';
                $flexy->setData('k_color', $k_color);
            }
            if ($thumb == 2) {
                if ($ini['General']['inline'] == 1) {
                    $t = 2;
                    $link = null;
                } else {
                    $t = 1;
                    $link = $path;
                }
                $r = ($ini['General']['redirect'] == 1) ? 1 : 2;
                $preview = $_SERVER['SCRIPT_NAME'] . '?o=1&r=' . $r . '&t=' . $t . '&' . $img_q;
                $flexy->setData('preview', $preview);
                $flexy->setData('link', $link);
                $flexy->setData('info', null);
            } else {
                $flexy->setData('preview', null);
                $flexy->setData('link', $path);
                $flexy->setData('info', null);
            }
            if (isset($_REQUEST['from'])) {
                $flexy->setData('backto', $_REQUEST['from']);
            } elseif (isset($_SERVER['HTTP_REFERER'])) {
                $flexy->setData('backto', $_SERVER['HTTP_REFERER']);
            } else {
                $flexy->setData('backto', null);
            }
            $flexy->setData('edit', extension_loaded('gd'));
            $flexy->setData('form', $rdr->toObject());
            $flexy->compile('preview.tpl.html');
            $flexy->output();
    }
    exit;
}

function ic2_error($code, $optmsg = '', $write_log = true)
{
    global $id, $uri, $file, $redirect;

    ic2_removeTmpFile();

    $map = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        'x01' => 'IC2 - Aborned Image',
        'x02' => 'IC2 - Broken (or Not) Image',
        'x03' => 'IC2 - Too Large',
        'x04' => 'IC2 - Virus Infected',
        'x05' => 'IC2 - No More',
        'x06' => 'IC2 - ???',
    );

    $message = $code . ' ' . $map[$code];
    if ($optmsg) {
        $message .= '<br />' . $optmsg;
    }

    if ($write_log) {
        require_once P2EX_LIBRARY_DIR . '/ic2/db_errors.class.php';
        $logger = &new IC2DB_Errors;
        $logger->uri     = isset($uri) ? $uri : (isset($id) ? $id : $file);
        $logger->errcode = $code;
        $logger->errmsg  = mb_convert_encoding($message, 'UTF-8', 'SJIS-win');
        $logger->occured = time();
        $logger->insert();
        $logger->ic2_errlog_lotate();
    }

    /*if (isset($map[$code]) && 100 <= $code && $code <= 505) {
        header("HTTP/1.0 {$code} {$map[$code]}");
    }*/

    if ($redirect) {
        $path = './img/' . strval($code) . '.png';
        $name = 'filename="' . strval($code) . '.png"';
        header('Content-Type: image/png; ' . $name);
        header('Content-Disposition: inline; ' . $name);
        readfile($path);
        exit;
    }
    echo <<<EOF
<html>
<head><title>ImageCache::Error</title></head>
<body>
<p>{$message}</p>
</body>
</html>
EOF;
    exit;
}

function ic2_finish($filepath, $thumb, $params, $force)
{
    global $thumbnailer;

    extract($params);

    if ($thumb == 0) {
        ic2_display($filepath);
    } else {
        $thumbpath = $thumbnailer->convert($size, $md5, $mime, $width, $height, $force);
        if (PEAR::isError($thumbpath)) {
            ic2_error('x02', $thumbpath->getMessage());
        }
        ic2_display($thumbpath);
    }
}

function ic2_removeTmpFile()
{
    global $tmpfile, $tmpchecker;

    file_exists($tmpfile) && unlink($tmpfile);
    file_exists($tmpchecker) && unlink($tmpchecker);
}

// }}}

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
