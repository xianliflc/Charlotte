<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 8/12/17
 * Time: 10:24 PM
 */

namespace Charlotte\Http;

use Charlotte\Exception\StatusCode;
class Response
{
    protected $headers;

    protected $cookies;

    protected $cookies_config;

    protected $content_type;

    /**
     * @var array $mime_types
     */
    public const mime_types = array(
        'ez' => 'application/andrew-inset',
        'hqx' => 'application/mac-binhex40',
        'cpt' => 'application/mac-compactpro',
        'doc' => 'application/msword',
        'bin' => 'application/octet-stream',
        'dms' => 'application/octet-stream',
        'lha' => 'application/octet-stream',
        'lzh' => 'application/octet-stream',
        'exe' => 'application/octet-stream',
        'class' => 'application/octet-stream',
        'so' => 'application/octet-stream',
        'dll' => 'application/octet-stream',
        'oda' => 'application/oda',
        'pdf' => 'application/pdf',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        'smi' => 'application/smil',
        'smil' => 'application/smil',
        'mif' => 'application/vnd.mif',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'wbxml' => 'application/vnd.wap.wbxml',
        'wmlc' => 'application/vnd.wap.wmlc',
        'wmlsc' => 'application/vnd.wap.wmlscriptc',
        'bcpio' => 'application/x-bcpio',
        'vcd' => 'application/x-cdlink',
        'pgn' => 'application/x-chess-pgn',
        'cpio' => 'application/x-cpio',
        'csh' => 'application/x-csh',
        'dcr' => 'application/x-director',
        'dir' => 'application/x-director',
        'dxr' => 'application/x-director',
        'dvi' => 'application/x-dvi',
        'spl' => 'application/x-futuresplash',
        'gtar' => 'application/x-gtar',
        'hdf' => 'application/x-hdf',
        'js' => 'application/x-javascript',
        'json' => 'application/json',
        'skp' => 'application/x-koan',
        'skd' => 'application/x-koan',
        'skt' => 'application/x-koan',
        'skm' => 'application/x-koan',
        'latex' => 'application/x-latex',
        'nc' => 'application/x-netcdf',
        'cdf' => 'application/x-netcdf',
        'sh' => 'application/x-sh',
        'shar' => 'application/x-shar',
        'swf' => 'application/x-shockwave-flash',
        'sit' => 'application/x-stuffit',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc' => 'application/x-sv4crc',
        'tar' => 'application/x-tar',
        'tcl' => 'application/x-tcl',
        'tex' => 'application/x-tex',
        'texinfo' => 'application/x-texinfo',
        'texi' => 'application/x-texinfo',
        't' => 'application/x-troff',
        'tr' => 'application/x-troff',
        'roff' => 'application/x-troff',
        'man' => 'application/x-troff-man',
        'me' => 'application/x-troff-me',
        'ms' => 'application/x-troff-ms',
        'ustar' => 'application/x-ustar',
        'src' => 'application/x-wais-source',
        'xhtml' => 'application/xhtml+xml',
        'xht' => 'application/xhtml+xml',
        'zip' => 'application/zip',
        'au' => 'audio/basic',
        'snd' => 'audio/basic',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'kar' => 'audio/midi',
        'mpga' => 'audio/mpeg',
        'mp2' => 'audio/mpeg',
        'mp3' => 'audio/mpeg',
        'aif' => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff',
        'aifc' => 'audio/x-aiff',
        'm3u' => 'audio/x-mpegurl',
        'ram' => 'audio/x-pn-realaudio',
        'rm' => 'audio/x-pn-realaudio',
        'rpm' => 'audio/x-pn-realaudio-plugin',
        'ra' => 'audio/x-realaudio',
        'wav' => 'audio/x-wav',
        'pdb' => 'chemical/x-pdb',
        'xyz' => 'chemical/x-xyz',
        'bmp' => 'image/bmp',
        'gif' => 'image/gif',
        'ief' => 'image/ief',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'jpe' => 'image/jpeg',
        'png' => 'image/png',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'djvu' => 'image/vnd.djvu',
        'djv' => 'image/vnd.djvu',
        'wbmp' => 'image/vnd.wap.wbmp',
        'ras' => 'image/x-cmu-raster',
        'pnm' => 'image/x-portable-anymap',
        'pbm' => 'image/x-portable-bitmap',
        'pgm' => 'image/x-portable-graymap',
        'ppm' => 'image/x-portable-pixmap',
        'rgb' => 'image/x-rgb',
        'xbm' => 'image/x-xbitmap',
        'xpm' => 'image/x-xpixmap',
        'xwd' => 'image/x-xwindowdump',
        'igs' => 'model/iges',
        'iges' => 'model/iges',
        'msh' => 'model/mesh',
        'mesh' => 'model/mesh',
        'silo' => 'model/mesh',
        'wrl' => 'model/vrml',
        'vrml' => 'model/vrml',
        'css' => 'text/css',
        'html' => 'text/html',
        'htm' => 'text/html',
        'asc' => 'text/plain',
        'txt' => 'text/plain',
        'rtx' => 'text/richtext',
        'rtf' => 'text/rtf',
        'sgml' => 'text/sgml',
        'sgm' => 'text/sgml',
        'tsv' => 'text/tab-separated-values',
        'wml' => 'text/vnd.wap.wml',
        'wmls' => 'text/vnd.wap.wmlscript',
        'etx' => 'text/x-setext',
        'xsl' => 'text/xml',
        'xml' => 'text/xml',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mpe' => 'video/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        'mxu' => 'video/vnd.mpegurl',
        'avi' => 'video/x-msvideo',
        'movie' => 'video/x-sgi-movie',
        'ice' => 'x-conference/x-cooltalk',
    );

    /**
     * description for status code
     */
    private $description;

    /**
     * status code
     */
    private $code;

    public function __construct($data, int $code = 200, string $type = 'html', string $description = '')
    {
        $this->data = is_null($data) ?
                        array('error' => true, 'message' => 'null response') : $data;
        $this->code = $code;
        $this->response = array();
        $this->dataType = gettype($this->data);
        $this->headers = array();
        $this->cookies = array();
        $this->cookies_config = array();
        $this->content_type = $type;
        $this->description = $description;
    }

    /**
     * get all given headers
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * get all given cookies
     */
    public function getCookies() {
        return $this->cookies;
    }

    /**
     * set headers
     *
     * @param $header
     * @return Response $this
     */
    public function setHeader($header)
    {
        if (is_array($header)) {
            foreach ($header as $key => $value) {
                $this->header[] = "{$key}: {$value}";
            }
        } else {
            $this->header[] = $header;
        }

        return $this;
    }

    /**
     * set contentType
     *
     * @param $contenttype
     * @return Response $this
     */    
    public function setContentType(string $contenttype = '') {
        if ($contenttype !== '') {
            $this->content_type = $contenttype;
        }
        return $this;
    }

    /**
    * Get content type
    */
    public function getContentType() {
        return $this->content_type;
    }

    /**
    * get status code of response
    */
    public function getStatusCode() {
        return array('code' => $this->code, 'description' => $this->description);
    }

    /**
     * set status code and its description
     *
     * @param $code
     * @param $description
     * @return Response $this
     */   
    public function setStatusCode(int $code = 200, string $description = '') {
        $this->code = $code;
        $this->description = $description;
        return $this;
    }

    /**
     * Send out status code and its description
     */
    public function sendStatusCode() {
        $arr = $this->getStatusCode();
        $code = $arr['code'];
        $description = $arr['description'];
        if ($description == '' && StatusCode::hasStatus($code)) {
            $description = StatusCode::getStatus($code)['status'];
        }
        header("HTTP/1.1 {$code} {$description}");
        return $this;
    }

    /**
     * set content type
     * @return Response $this
     */
    private function sendContentType() {

        $content_type_name = $this->getContentType();
        if (isset(self::mime_types [$content_type_name])) {
            $content_type = self::mime_types [$content_type_name];
        } elseif ($content_type_name) {
            $content_type = $content_type_name;
        } else {
            $content_type = self::mime_types ['html'];
        }

        header("Content-Type: {$content_type}; charset=utf-8");
        return $this;
    }

    /**
     * send all headers
     *
     * @return Response $this
     */
    private function sendHeaders() {
        $contents = $this->getHeaders();
        if (!empty($contents)) {
            if (!is_array($contents)) {
                $contents = array($contents);
            }

            foreach ($contents as $content) {
                header($content);
            }
        }

        return $this;
    }

    /**
     * send all cookies
     *
     * @return Response $this
     */
    private function sendCookies()
    {
        if (!empty($this->cookies)) {
            foreach ($this->cookies as $cookie) {
                call_user_func_array('setcookie', $cookie);
            }
        }

        return $this;
    }    


    /**
     * set cookie default config
     *
     * @param array $config
     * @return Response $this
     */
    public function cookiesConfig(array $config = array('path' => '/', 'domain' => '', 'secure' => false, 'httponly' => true))
    {
        $default = array('path' => '/', 'domain' => '', 'secure' => false, 'httponly' => true);

        foreach($config as $key => $value) {
            if (in_array($key, $default) && $value !== $default[$key]) {
                $default[$key] = $value;
            }
        }
        $this->cookies_config = $default;
        return $this;
    }


    /**
     * get the current cookie config
     */
    public function getCookiesConfigs() {
        return $this->cookies_config;
    }

    /**
     * get detting of default cookies
     *
     * @param null $key
     * @return array|mixed
     */
    public function getCookieConfig($key = null)
    {
        $default = array('path' => '/', 'domain' => '', 'secure' => false, 'httponly' => true);
        if ($key && isset($default[$key])) {
            if (isset($this->cookies_config[$key])) {
                return $this->cookies_config[$key];
            }

            return $default[$key];
        }

        return null;
    }

    /**
     * remove cookie
     *
     * @param string $name
     * @return $this
     */
    public function invalidateCookie($name) {
        $this->setCookie($name, null, -1);
        return $this;
    }

    /**
     * alias for removing the given cookie
     * @param string $name
     * @return $this
     */
    public function deleteCookie($name) {
        return $this->invalidateCookie($name);
    }

    /**
     * set cookie
     *
     * @param mixed $name
     * @param string $value
     * @param int $expire
     * @return Response $this
     */
    public function setCookie($name, string $value = '', $expire = 0)
    {
        $this->cookies[$name] = array(
            $name,
            $value,
            $expire,
            $this->getCookieConfig('path'),
            $this->getCookieConfig('domain'),
            $this->getCookieConfig('secure'),
            $this->getCookieConfig('httponly')
        );

        return $this;
    }

    /**
     * build the raw response
     */
    public function buildResponse () {

        //TODO: HTTP RESPONSE: logic to build response from curl response
        if ($this->hasError()) {
            return array('Error'=> true, 'ErrorMessage'=>$this->get('message'));
        }
        else {
            return $this->data;
        }
    }

    /**
     * Send the response back to client and terminate the app
     */
    public function process () {

        $this
            ->sendContentType()
            ->sendStatusCode()
            ->sendHeaders()
            ->sendCookies();
            
        $this->finalize();
    }

    public function sendResponseHeaders() {
        $this
        ->sendContentType()
        ->sendStatusCode()
        ->sendHeaders();

        return $this;
    }

    /**
     * build the response and send the response
     */
    public function finalize( $exit_code = 0) {
        $this->response = $this->buildResponse();
        if ($this->getContentType() === 'json') {
            echo json_encode($this->response);
        } elseif ($this->getContentType() === 'html'){
            echo $this->getRendered();
        } else {
            return $this->data;
        }
        // TODO: http response: improvements on exit code
        exit($exit_code);
    }

    public function getRendered() {
        // TODO: HTTP Response: add implementation of renderer if it is HTML response
        return json_encode($this->response);
    }

    /**
     * check whether current response has an error
     */
    public function hasError() {
        return array_key_exists('error', $this->data);
    }

    public function get($key) {
        return $this->data[$key];
    }

}
