<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;
use dokuwiki\plugin\imgpaste\Exception as PasteException;

/**
 * DokuWiki Plugin imgpaste (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <gohr@cosmocode.de>
 */
class action_plugin_imgpaste extends ActionPlugin
{

    protected $tempdir = '';
    protected $tempfile = '';
    protected $randomCache = null;

    /**
     * Clean up on destruction
     */
    public function __destruct()
    {
        $this->clean();
    }

    /** @inheritdoc */
    public function register(EventHandler $controller)
    {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handleAjaxUpload');
        $controller->register_hook('DOKUWIKI_STARTED', 'AFTER', $this, 'addJsInfo');
    }

    /**
     * Pass the configured image dimensions to the editor's JavaScript
     *
     * @param Event $event
     */
    public function addJsInfo(Event $event)
    {
        global $JSINFO;
        $JSINFO['plugins']['imgpaste'] = [
            'width'  => (int) $this->getConf('width'),
            'height' => (int) $this->getConf('height'),
        ];
    }


    /**
     * Creates a new file from the given data URL
     *
     * @param Event $event AJAX_CALL_UNKNOWN
     */
    public function handleAjaxUpload(Event $event)
    {
        if ($event->data != 'plugin_imgpaste') return;
        $event->preventDefault();
        $event->stopPropagation();

        global $INPUT;
        global $lang;
        try {
            if (!checkSecurityToken()) throw new PasteException($lang['uploadfail'], 403);

            // check upload permission before any data is fetched or processed
            $basename = $this->createBaseName($INPUT->post->str('id'), $INPUT->server->str('REMOTE_USER'));
            if (auth_quickaclcheck($basename) < AUTH_UPLOAD) throw new PasteException($lang['uploadfail'], 403);

            if ($INPUT->has('url')) {
                [$data, $type] = $this->externalUrlToData($INPUT->post->str('url'));
            } else {
                [$data, $type] = $this->dataUrlToData($INPUT->post->str('data'));
            }
            $result = $this->storeImage($data, $type);
        } catch (PasteException $e) {
            $this->clean();
            http_status($e->getCode(), $e->getMessage());
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * Get the binary data and mime type from a data URL
     *
     * @param string $dataUrl
     * @return array [data, type]
     * @throws PasteException
     */
    protected function dataUrlToData($dataUrl)
    {
        list($type, $data) = explode(';', $dataUrl);
        if (!$data) throw new PasteException($this->getLang('e_nodata'), 400);

        // process data encoding
        $type = strtolower(substr($type, 5)); // strip 'data:' prefix
        $data = substr($data, 7); // strip 'base64,' prefix
        $data = base64_decode($data);
        return [$data, $type];
    }

    /**
     * Download the file from an external URL
     *
     * @param string $externalUrl
     * @return array [data, type]
     * @throws PasteException
     */
    protected function externalUrlToData($externalUrl)
    {
        global $lang;

        // download the file
        $http = new \dokuwiki\HTTP\DokuHTTPClient();
        $data = $http->get($externalUrl);
        if (!$data) throw new PasteException($lang['uploadfail'], 500);
        [$type] = explode(';', $http->resp_headers['content-type']);
        return [$data, $type];
    }

    /**
     * @throws PasteException
     */
    protected function storeImage($data, $type)
    {
        global $lang;
        global $INPUT;

        // re-encode to the configured format when requested
        $format = $this->getConf('format');
        if (!in_array($format, ['png', 'jpeg'], true)) $format = 'original';
        if ($format !== 'original') {
            // no need to re-encode when the source already matches the target format
            $sameSource = ($format === 'png' && $type === 'image/png')
                || ($format === 'jpeg' && $type === 'image/jpeg');
            if ($sameSource) {
                $ext = $format === 'png' ? 'png' : 'jpg';
            } else {
                [$data, $type, $ext] = $this->reencodeImage($data, $format);
            }
        } else {
            $mimetypes = array_flip(getMimeTypes());
            if (!isset($mimetypes[$type])) throw new PasteException($lang['uploadwrong'], 415);
            $ext = $mimetypes[$type];
        }

        // prepare file names
        $tempname = $this->storetemp($data);
        $filename = $this->createFileName($INPUT->post->str('id'), $ext, $_SERVER['REMOTE_USER']);

        // check ACLs
        $auth = auth_quickaclcheck($filename);
        if ($auth < AUTH_UPLOAD) throw new PasteException($lang['uploadfail'], 403);

        // do the actual saving
        $result = media_save(
            [
                'name' => $tempname,
                'mime' => $type,
                'ext' => $ext,
            ],
            $filename,
            false,
            $auth,
            'copy'
        );
        if (is_array($result)) throw  new PasteException($result[0], 500);

        //Still here? We had a successful upload
        $this->clean();
        return [
            'message' => $lang['uploadsucc'],
            'id' => $result,
            'mime' => $type,
            'ext' => $ext,
            'url' => ml($result),
        ];
    }

    /**
     * Re-encode the given binary image to the requested target format
     *
     * @param string $data   binary image data
     * @param string $format target format: png|jpeg
     * @return array [data, mime, ext]
     * @throws PasteException
     */
    protected function reencodeImage($data, $format)
    {
        if (!function_exists('imagecreatefromstring')) {
            throw new PasteException($this->getLang('e_encode'), 500);
        }

        $img = @imagecreatefromstring($data);
        if (!$img) throw new PasteException($this->getLang('e_encode'), 415);

        $w = imagesx($img);
        $h = imagesy($img);

        if ($format === 'jpeg') {
            // JPEG has no alpha channel, composite onto a white background first
            $bg = imagecreatetruecolor($w, $h);
            $white = imagecolorallocate($bg, 255, 255, 255);
            imagefill($bg, 0, 0, $white);
            imagealphablending($bg, true);
            imagecopy($bg, $img, 0, 0, 0, 0, $w, $h);
            imagedestroy($img);
            $img = $bg;

            $quality = (int) $this->getConf('jpegquality');
            if ($quality < 1) $quality = 90;
            if ($quality > 100) $quality = 100;

            ob_start();
            imagejpeg($img, null, $quality);
            $data = (string) ob_get_clean();
            imagedestroy($img);

            return [$data, 'image/jpeg', 'jpg'];
        }

        // target: png, keep the alpha channel
        imagesavealpha($img, true);
        ob_start();
        imagepng($img, null);
        $data = (string) ob_get_clean();
        imagedestroy($img);

        return [$data, 'image/png', 'png'];
    }

    /**
     * Create the filename for the new file
     *
     * @param string $pageid the original page the paste event happend on
     * @param string $ext the extension of the file
     * @param string $user the currently logged in user
     * @return string
     */
    protected function createFileName($pageid, $ext, $user)
    {
        $unique = '';
        $filename = $this->createBaseName($pageid, $user);
        while (media_exists($filename . $unique . '.' . $ext)) {
            $unique = (int)$unique + 1;
        }
        return $filename . $unique . '.' . $ext;
    }

    /**
     * Create the media ID for the new file from the configured pattern, without extension
     *
     * @param string $pageid the original page the paste event happend on
     * @param string $user the currently logged in user
     * @return string
     */
    protected function createBaseName($pageid, $user)
    {
        $filename = $this->getConf('filename');
        $filename = str_replace(
            [
                '@NS@',
                '@ID@',
                '@USER@',
                '@PAGE@',
                '@random@',
            ],
            [
                getNS($pageid),
                $pageid,
                $user,
                noNS($pageid),
                $this->randomString(),
            ],
            $filename
        );
        $filename = strftime($filename);
        return cleanID($filename);
    }

    /**
     * Create a random lowercase alphanumeric string for the @random@ placeholder
     *
     * @return string
     */
    protected function randomString()
    {
        // keep one random string per request so the ACL check and the final name match
        if ($this->randomCache !== null) return $this->randomCache;

        $length = (int) $this->getConf('randomlength');
        if ($length < 1) $length = 8;
        if ($length > 32) $length = 32;

        $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $this->randomCache = $str;
        return $str;
    }

    /**
     * Create a temporary file from the given data
     *
     * exits if an error occurs
     *
     * @param $data
     * @return string
     */
    protected function storetemp($data)
    {
        // store in temporary file
        $this->tempdir = io_mktmpdir();
        if (!$this->tempdir) throw new PasteException('', 500);
        $this->tempfile = $this->tempdir . '/' . md5($data);
        if (!io_saveFile($this->tempfile, $data)) throw new PasteException('', 500);
        return $this->tempfile;
    }

    /**
     * remove temporary file and directory
     */
    protected function clean()
    {
        if ($this->tempfile && file_exists($this->tempfile)) @unlink($this->tempfile);
        if ($this->tempdir && is_dir($this->tempdir)) @rmdir($this->tempdir);
        $this->tempfile = '';
        $this->tempdir = '';
    }

}
