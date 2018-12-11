<?php
error_reporting(E_ALL);

require_once 'system/http.php';
require_once 'system/class.upload.php';

class sc  {
    private $page, $query;
    private $config, $replaces = array(), $get, $clear_url;
    private $__specsymbols = array(
        '\\' => '__s-backslash__',
        '/' => '__s-slash__',
        ':' => '__s-colon__',
        '*' => '__s-star__',
        '?' => '__s-quest__',
        '"' => '__s-quotes__',
        '<' => '__s-leftarrow__',
        '>' => '__s-rightarrow__',
        '|' => '__s-direct__');

    public static function debug($var) {  echo '<pre>'; print_r($var); echo '</pre>'; }

    public static function create($config, $query = null, $get = null, $clear_url = null) {
        $obj = new sc;
        $obj->config = $config;
        $obj->get = $get;
        $obj->clear_url = $clear_url;

        $obj->config['murl'] = str_replace(array('http://', 'https://'), '', $obj->config['url']);
        if(substr($obj->config['murl'], -1) == '/')
            $obj->config['murl'] = substr($obj->config['murl'], 0, -1);

        $obj->config['url_domain_count'] = count(explode('.', $obj->config['murl']));
        if($query !== null)
            $obj->query = $query;

        return $obj;
    }

    public function getPage() {
        $call_url = $this->clear_url;
		if(preg_match('#schandler_#', $call_url)) {
			preg_match('#<\?!!php(.*)!!\?>#Uis', file_get_contents('cache/'.$call_url), $matches);
			eval($matches[1]);
			
			return $this;
		}

        $tmp = explode('/', $call_url);
        $end = array_pop($tmp);
        if(empty($end))
            $end = array_pop($tmp);

        $_tmp = explode('.', $end);

        if(count($_tmp) == 1) {
            if(substr($call_url, -1) == '/')
                $call_url = $call_url.'index.html';
            else
                $call_url = $call_url.'/index.html';
        }
        else
            switch($_tmp[1]) {
                case 'jpg': case 'png': case'jpeg': $call_url = str_replace(array('(', ')'), '', $call_url); break;
            }

        if(file_exists('cache/'.$call_url)) {
            $this->page = file_get_contents('cache/'.$call_url);
            $this->SetHeaders();
            return $this;
        }
        elseif(strpos( $this->query, '?')) {
            $url_end = substr($this->query, strpos($this->query, '?'));
            $url_end = str_replace(array_keys($this->__specsymbols), array_values($this->__specsymbols),$url_end);
            $call_url = $this->clear_url.$url_end;
            if(file_exists('cache/'.$call_url))
            {
                $this->page = file_get_contents('cache/'.$call_url);
                $this->SetHeaders();
                return $this;
            }
        }

        if($this->query !== null) {
            if(isset($url_end))
                unset($url_end);

            if(strpos($this->query, '?'))
                $url_end = substr($this->query, strpos($this->query, '?'));
            $query = explode('/', $this->clear_url);

            $end = end($query);
			$file = strpos($end, '.') ? array_pop($query) : '';

            if(isset($url_end))
                $file = str_replace(array_keys($this->__specsymbols), array_values($this->__specsymbols), $file.$url_end);

            reset($query);

            $dir = 'cache';
            foreach($query as $key => $value) {
                $dir = $dir.'/'.$value;
                @mkdir($dir);
            }
        }

        $http = new http;

        if($this->config['charset'] !== null)
            $http->encoding = $this->config['charset'];

        if(is_file('proxy.txt'))
            $http->proxy = $this->getProxy();

        $url = $this->config['url'];

        if($this->query !== null)
            if(substr($this->query, 0, 3) == 's__')
                $url = 'http://'.substr($this->query, 3, strpos($this->query, '/')-3).'.'. $this->config['murl'].substr($this->query, strpos($this->query, '/'));
            else
                $url = $this->config['url'].$this->query;

        $this->page = $http->get($url);
        if($this->page == '') // в случае проблем с прокси.
            if(is_file('proxy.txt')) {
                $proxy_list = explode("\n", file_get_contents('proxy.txt'));
                foreach($proxy_list as $key => $value)
                    $proxy_list[$key] = trim($value);

                $proxy_list = array_flip($proxy_list);
                unset($proxy_list[$http->proxy]);
                $proxy_list = array_flip($proxy_list);

                $proxy_list = implode("\r\n", $proxy_list);
                if(!empty($proxy_list))
                    file_put_contents('proxy.txt', $proxy_list);
                else
                    @unlink('proxy.txt');

                if(!is_file('proxy.txt')) {
                    $this->proxy = '';
                    $this->page = $http->get($url);
                }
                else {
                    $http->proxy = $this->getProxy();
                    $this->page = $http->get($url);
                }
            }

		if(isset($this->config['handlers'])) {
			$this->page = str_replace('</head>',
'<script>
window.onload = function() {var options = document.getElementsByTagName("option");
for(var i = 0; i <= options.length; i++) {
    if(options[i] != undefined)
        options[i].value = options[i].innerHTML;
}}</script></head>', $this->page);
		}
			
        if(isset($this->query))
        {
            $tmp = explode('.', $this->query);
            $ext = strtolower(array_pop($tmp));

            switch($ext) {
                case 'css': $this->cssOperation(); $this->setHeaders(); break;
                case 'jpg':
                case 'jpeg':
                case 'png': $this->imageOperation(); $this->setHeaders(); return $this; break;
                default:
                    $this->sinonim(); // Синонимайзер
                    $this->cutCounters(); // Вырезаем счетчики
                    $this->urlReplaces()->replaceHrefs(); // Замена ссылок
                    $this->textReplaces(); // Замены (текст и регулярки)
                    $this->formHandlers(); // Обработчики форм
					$this->SetHeaders(); // Установка заголовков
            }
        }
        else {
            $this->sinonim(); // Синонимайзер
            $this->cutCounters(); // Вырезаем счетчики
            $this->urlReplaces()->replaceHrefs(); // Замена ссылок
            $this->textReplaces(); // Замены (текст и регулярки)
			$this->formHandlers(); // Обработчики форм
            $this->SetHeaders(); // Установка заголовков
        }

        if($this->query !== null) {
            if(isset($file) and !empty($file))
                file_put_contents($dir.'/'.$file, $this->page);
            else
                file_put_contents($dir.'/index.html', $this->page);
        }
        else
            file_put_contents('cache/index.html', $this->page);

        return $this;
    }

    public function sinonim() {
        if(isset($this->config['sinonim'])) {
            $db_name = 's.db';

            if($this->config['charset'] == 'windows-1251')
                $db_name = 's1251.db';

            $db = file_get_contents($db_name);
            $db = unserialize($db);

            $this->page = str_replace(array_keys($db), array_values($db), $this->page);
        }

        return $this;
    }

    public function cutCounters() {
        if(isset($this->config['cutcounters'])) {
            $counters = array(
                '@<!--LiveInternet counter-->.*<!--/LiveInternet-->@s',
                '@<a href="http://www.liveinternet.ru.*</a>@s',
                '@<a href="http://top.mail.ru.*</a>@s',
                '@<!-- Yandex.Metrika counter -->.*<!-- /Yandex.Metrika counter -->@s',
                '@<!-- Google analytics -->.*<!-- End google analytics -->@s',
                '@<!-- begin of Top100 logo -->.*<!-- end of Top100 logo -->@s',
                '@<!-- begin of Top100 code -->.*<!-- end of Top100 code -->@s',
                '@<script src="http\:\/\/www\.google\-analytics\.com.*<script type="text/javascript">.*urchinTracker\(\).*<\/script>@Us',
                '@<a\s+href="http\:\/\/www\.yandex\.ru/cy\?.*<img src="http\:\/\/www\.yandex\.ru\/cycounter.*>\s*<\/a>@Us'
            );

            $this->page = preg_replace($counters, '', $this->page);
        }
    }

	public function formHandlers() {
		$handlers = (array)$this->config['handlers'];
		preg_match('#([^:]+)::(.*)#', $handlers['point'], $matches);
		$point_type = $matches[1];
		$point_search = $matches[2];

		switch($point_type) {
			case 'id':
			case 'class':
				if(!preg_match('#<form(.*)'.$point_type.'="?\'?'.$point_search.'\'?"?(.*)>#Ui', $this->page, $matches))
					return;
				$form = $matches[0];
				break;
			case 'formindex':
				if(!preg_match_all('#<form(.*)>#Ui', $this->page, $matches))
					return;
					
				$form = $matches[$point_search];
				break;
		}
		
		$form2 = preg_replace('#(action|method)="(.*)"#Ui', '', $form);
		if($handlers['type'] == 'email')
			$string = 'action="/schandler_'.$handlers['name'].'.php" method="post"';
		elseif($handlers['type'] == 'getpostsend')
			$string = 'action="'.$handlers['getpost_url'].'" method="'.$handlers['getpost_type'].'"';
			
		$form2 = str_replace('<form', '<form '.$string, $form2);
		$this->page = str_replace($form, $form2, $this->page);
	}
	
    public function cssOperation() {
        function is_hex($hexValue) { return $hexValue == dechex(hexdec($hexValue)) ? true : false; }

        if(isset($this->config['css_color_random'])) {
            preg_match_all('&([ //:])#([1-9a-zA-Z]{6})&', $this->page, $matches);
            $colors = $matches[2];
            $colors = array_unique($colors);

            foreach($colors as $key => $value) {
                if(is_hex($value))
                    $colors['#'.$value] = '';
                unset($colors[$key]);
            }

            if(file_exists('css_colors.txt'))
            {
                $saved_colors = explode("\n", file_get_contents('css_colors.txt'));
                $saved_colors = array_map('trim', $saved_colors);

                foreach($saved_colors as $key => $value) {
                    $c = explode(';', $value);
                    if(count($c) == 2)
                        $array_replace[$c[0]] = $c[1];
                }
                if(isset($array_replace))
                    $colors = array_merge($colors, $array_replace);
            }

            function generationColor() {
                $rand = rand(0, 255);
                if(strlen($rand) == 1)
                    $rand = '0'.$rand;

                return dechex($rand);
            }

            foreach($colors as $key => $value)
                if(empty($value))
                    $colors[$key] = '#'.generationColor().generationColor().generationColor();

            $colors_to_save = '';
            foreach($colors as $key => $value)
                $colors_to_save = $colors_to_save.$key.';'.$value."\r\n";

            file_put_contents('css_colors.txt', $colors_to_save);

            $this->page = str_ireplace(array_keys($colors), array_values($colors), $this->page);
        }
    }

    public function imageOperation() {
        $path = 'cache/'.$this->clear_url;
        $path = str_replace(array('(', ')'), '', $path);
        $tmp = explode('/', $path);
        $file_name = str_replace(array('(', ')'), '', array_pop($tmp));
        $img_folder = implode('/', $tmp);
        $path_for_rename = implode('/', $tmp).'/1'.$file_name;

        file_put_contents($path, $this->page);

        $ex = false;
        if(isset($this->config['img_exception'])) {
            $exceptions = explode("\n", $this->config['img_exception']);

            $_temp = explode('.', $exceptions[count($exceptions)-1]);
            $it_file = (count($_temp) > 1);

            $path_array = explode('/', $this->clear_url);
            if(substr($path[0], 3 == 's__'))
                array_shift($path_array);

            foreach($exceptions as $value) {
                $__temp = explode('/', $value);

                if($__temp[0] == '')
                    array_shift($__temp);

                foreach($__temp as $key => $value) {
                    $value = str_replace('/', '', $value);
                    if(isset($path_array[$key]))
						$ex = ($value == $path_array[$key]);
                    else
                        $ex = $it_file;
                }
            }
        }

        if($ex)
            return;
        else {
            $img = new Upload($path);
            if($img->image_src_x > $this->config['img_min_weight'] and $img->image_src_y > $this->config['img_min_height']) {
                if(isset($this->config['img_mirror']) and $this->config['img_mirror'] == 'on')
                    $img->image_flip = 'V';

                if(isset($this->config['img_zoom']) and $this->config['img_zoom'] != 0) {
                    $y = $img->image_src_y;
                    $x = $img->image_src_x;

                    $img->image_crop = $this->config['img_zoom'].' '.$this->config['img_zoom'];
                    $img->image_resize = true;
                    $img->image_y = $y + $this->config['img_zoom']*2;
                    $img->image_x = $x + $this->config['img_zoom']*2;
                }

                if(isset($this->config['img_copyright']) and $this->config['img_copyright'] == 'on')
                {
                    $img->image_text = $this->config['img_copyright_text'];
                    $img->image_text_color = $this->config['img_copyright_color'];
                    $bg_pos = strtoupper($this->config['img_copyright_bg_position']);
                    $bg_pos = str_replace(array('D', 'U'), array('B', 'T'), $bg_pos);
                    $img->image_text_position = strrev($bg_pos);
                    $img->image_text_padding_x = 3;
                    $img->image_text_padding_y = 3;
                    $img->image_text_font = 5;
                    $img->image_text_background = $this->config['img_copyright_bg_color'];
                }
            }

            $newname = '1'.$img->file_src_name_body;
            $fullname = $img->file_src_name;
            $img->file_new_name_body = $newname;
            $img->Process($img_folder);


            if(!$img->processed)
                die('error '.$img->error.'. Line: '.__LINE__);
            unlink($path);
            rename($path_for_rename, $path);
            $this->page = file_get_contents($path);
            return;
        }
    }

    public function textReplaces() {
        if(file_exists('replacements_standart.txt')) {
            $_t = explode("\n", file_get_contents('replacements_standart.txt'));
            $replaces = array();

            $separator = isset($this->config['replacements_standart_separator']) ? $this->config['replacements_standart_separator'] : ';';

            foreach($_t as $key => $value) {
                $_tt = explode($separator, $value);
				if(!isset($_tt[1]) or (isset($_tt[1]) and empty($_tt[1])))
					$_tt[1] = ' ';
                $replaces[$_tt[0]] = $_tt[1];
            }

            $this->page = str_ireplace(array_keys($replaces), array_values($replaces), $this->page);
        }
		
        if(file_exists('replacements_regular.txt')) {
            $_t = explode("\n", file_get_contents('replacements_regular.txt'));
            $replaces = array();

            $separator = isset($this->config['replacements_regular_separator']) ? $this->config['replacements_regular_separator'] : ';';

            foreach($_t as $key => $value) {
                $_tt = explode($separator, $value);

                if(!isset($_tt[1]) or (isset($_tt[1]) and empty($_tt[1])))
                    $_tt[1] = ' ';

                $_tt[0] = trim($_tt[0]);
                $_tt[1] = trim($_tt[1]);
                if($_tt[0] == '')
                    continue;

                $replaces[$_tt[0]] = $_tt[1];
            }

            $this->page = preg_replace(array_keys($replaces), array_values($replaces), $this->page);
        }

        return $this;
    }


    public function urlReplaces($page = null) {
        if($page === null)
            $page = $this->page;

		$matches = array();
		
        preg_match_all('#[\'\"]+(https?://([^\'\"]+))[\'\"]+#', $page, $matches[0]);
        preg_match_all('#[\(]+(https?://([^\'\"]+))[\)]+#', $page, $matches[1]);
        preg_match_all('#href="?\'?([^"\']+)\'?"?#', $page, $matches[2]);
        preg_match_all('#src="?\'?([^"\']+)\'?"?#', $page, $matches[3]);

        $matches = array_merge($matches[0][2], $matches[1][2], $matches[2][1], $matches[3][1]);

        foreach($matches as $key => $value) {
            $value = str_replace(array('http://', 'https://'), '', $value);

            if(substr($value, 0, 2) == '//')
                $value = 'http://'.substr($value, 2);

            if(!preg_match("#{$this->config['murl']}#", $value)) {
                unset($matches[$key]);
                continue;
            }

            if($pos = strpos($value, '/'))
                if(!preg_match("#\.{$this->config['murl']}#", substr($value, 0, $pos))) {
                    unset($matches[$key]);
                    continue;
                }

            if($pos = strpos($value, '/'))
                $value = substr($value, 0, $pos);

            $original = $value;

            $subdomains = explode('.', $value);
            if(count($subdomains) > count(explode('.', $this->config['murl'])))
                $value = $_SERVER['HTTP_HOST'].'/s__'.$subdomains[0];
            else {
                unset($matches[$key]);
                continue;
            }

            unset($matches[$key]);
            $matches[$original] = $value;
        }

        $matches = array_unique($matches);
        $this->replaces['subdomains'] = $matches;

        return $this;
    }

    public function replaceHrefs() {
        $this->page = str_replace(array_keys($this->replaces['subdomains']), array_values($this->replaces['subdomains']), $this->page);
        $this->page = preg_replace("#https?://{$this->config['murl']}#", "http://{$_SERVER['HTTP_HOST']}", $this->page);
        foreach($this->replaces['subdomains'] as $key => $value)
        {
            $key = str_replace('\\', '', $key);
            $this->page = preg_replace("#https?://{$key}#", "http://{$_SERVER['HTTP_HOST']}", $this->page);
        }
		
        return $this;
    }

    public function printPage() {
        echo $this->page;
    }

    public function setHeaders() {
		$content_type = !empty($this->query) ? $this->contentType($this->clear_url) : 'text/html';
		header("Content-Type: {$content_type}; charset={$this->config['charset']}");

        return $this;
    }

    private function contentType($filename) {
        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'xhtml' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
        );

        $tmp = explode('.', $filename);
        $ext = strtolower(array_pop($tmp));

        return array_key_exists($ext, $mime_types) ? $mime_types[$ext] : 'text/html';
    }

    public static function getCharset($url) {
        $http = new http;
        $page = $http->get($url);
        if($http->info['http_code'] == 200) {
            if(!empty($http->info['content_type'])) {
                preg_match('#charset=(.+)#', $http->info['content_type'], $match);
                if(isset($match[1]))
                    $charset = $match[1];
            }
            if(!isset($charset)) {
                preg_match('#charset=(.+)["\']#', $page, $match);
                if(isset($match[1]))
                    $charset = $match[1];
            }

			$return = isset($charset) ? $charset : 'Не удалось определить кодировку';
        }
        else
            $return = 'Не удалось соединиться с '.$url;

        return $return;
    }

    public static function removeDir($dir, $deletehandlers = true) {
		if(file_exists($dir)) {
			chmod($dir, 0777);
			if(is_dir($dir)) {
				$handle = opendir($dir);
				while($filename = readdir($handle))
					if($filename != '.' and $filename != '..')
						sc::removeDir($dir.'/'.$filename);

				closedir($handle);
				@rmdir($dir);
			}
			else
				if($deletehandlers and !preg_match('#schandler_#', $dir))
					unlink($dir);
		}
    }

    public static function ajaxPrint($val) { echo $val; }

    public static function getPassword() { return file_exists('password.cfg') ? file_get_contents('password.cfg') : false; }

    private function getProxy() {
        $list = explode("\n", file_get_contents('proxy.txt'));
        return trim($list[array_rand($list)]);
    }
	
	public static function standartUrl($url) {
		$url = str_replace(array('www.', 'http://', 'https://'), '', $url);
		if(substr($url, -1) != '/')
			$url = $url.'/';
			
		$url = 'http://'.$url;
		return $url;
	}
}


if(isset($_POST['go']))
    if(file_exists('config.cfg'))
        unlink('config.cfg');

if(isset($_GET['sitecopy']) and isset($_GET['image_example'])) {
    if(!file_exists('temp')) {
        mkdir('temp');
        $http = new http;
        $img = $http->get('https://pp.vk.me/c606620/v606620296/5bba/PXlZO_ODlfA.jpg');
        file_put_contents('temp/original.jpg', $img);
    }
    else
        unlink('temp/last.jpg');

    $img = new Upload($_SERVER['DOCUMENT_ROOT'].'/temp/original.jpg');
    if($_GET['mirror'] == 'on')
        $img->image_flip = 'V';

    if(!empty($_POST['img_copyright_text'])) {
        $img->image_text = $_POST['img_copyright_text'];

        if(!empty($_POST['img_copyright_color']))
            $img->image_text_color = $_POST['img_copyright_color'];

        if(!empty($_POST['img_copyright_bg_color']))
            $img->image_text_background = $_POST['img_copyright_bg_color'];

        $img->image_text_background_opacity = 70;
        $img->image_text_font       = $img->image_src_y * 0.05;
        $img->image_text_padding    = 3;

        if(!empty($_POST['img_copyright_bg_position'])) {
            $pos = str_split($_POST['img_copyright_bg_position']);
            $position_img = '';
            $arr_all_pos = array(
                'l' => 'L',
                'r' => 'R',
                'd' => 'B',
                'u' => 'T',
            );
            if(count($pos) == 1)
                $position_img .= $arr_all_pos[$pos[0]];
            if(count($pos) == 2)
                $position_img .= $arr_all_pos[$pos[1]].$arr_all_pos[$pos[0]];

            $img->image_text_position = $position_img;
        }
    }

    if($_POST['img_zoom'] != 0) {
        $y = $img->image_src_y;
        $x = $img->image_src_x;

        $img->image_crop = $_POST['img_zoom'].' '.$_POST['img_zoom'];
        $img->image_resize = true;
        $img->image_y = $y + $_POST['img_zoom']*2;
        $img->image_x = $x + $_POST['img_zoom']*2;
    }


    $img->file_new_name_body = 'last';
    $img->Process($_SERVER['DOCUMENT_ROOT'].'/temp/');
    if($img->processed)
        sc::ajaxPrint('/temp/last.jpg?random='.microtime());
    else
        sc::ajaxPrint('error: '.$img->error);
}
elseif(!file_exists('config.cfg')) {
    if(isset($_GET['action']) and $_GET['action'] == 'ajax') { // Аякс
        switch($_GET['function']) {
            case 'get_charset_from_url':
                sc::ajaxPrint(sc::getCharset($_GET['url']));
                break;
            case 'get_ftp_folders':
                if(isset($_GET['server']) and isset($_GET['login']) and isset($_GET['password'])) {
                    $server = $_GET['server'];
                    $login = $_GET['login'];
                    $password = $_GET['password'];

                    if($connect = ftp_connect($server)) {
                        if(ftp_login($connect, $login, $password)) {
                            $list = ftp_nlist($connect, '.');
                            if(array_search('public_html', $list))
                                $list = ftp_nlist($connect, 'public_html/');

                            $return = '';
                            foreach($list as $value) {
                                if($value == '.' or $value == '..')
                                    continue;
                                $return = $return.$value."\r\n";
                            }

                            sc::ajaxPrint($return);
                        }
                        else
                            sc::ajaxPrint('Ошибка. Логин или пароль не подходит');
                    }
                    else
                        sc::ajaxPrint('Ошибка. Не удалось покдлючиться к фтп');
                }
                else
                    sc::ajaxPrint('Ошибка. Заполните все поля.');

                break;
        }
    }
	elseif(isset($_GET['action']) and $_GET['action'] == 'iframe') {
		if(!isset($_GET['url']))
			sc::ajaxPrint('Ошибка. Нужно передавать URL');
		else {
			$http = new http;
			$url = sc::standartUrl($_GET['url']);
			$html = $http->get($url);
			if(preg_match('#</head>#', $html)) {
				$html = str_replace(array('src="//', 'href="//'), array('src="http://', 'href="http://'), $html);
				
				$html = preg_replace('#(src|href)="([^:./"]+)/#', '$1="/$2/', $html);
				$html = str_replace(array('src="/', 'src="./', 'href="/', 'href="./'), array('src="'.$url, 'src="'.$url, 'href="'.$url, 'href="'.$url), $html);
				$html = preg_replace('#:\s?url\((\'?"?)/?([^\'"\)]*)\'?"?\)#', ": url({$url}$1$2$1)", $html);
				$html = str_replace('</head>', 
									'<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>'.
									'<script type="text/javascript" src="/system/bw.js"></script>'.
									'<script type="text/javascript" src="/system/jquery-ui.min.js"></script>'.
									'<script type="text/javascript" src="/system/jquery.contextMenu.js"></script>'.
									'<link rel="stylesheet" href="/system/jquery.contextMenu.css" />'.
									'<link rel="stylesheet" href="/system/bw.css" /></head>', $html);
				
				echo $html;
			}
			else
				sc::ajaxPrint('Не удалось найти тег head в коде страницы');
		}
	}

    elseif(isset($_POST['go'])) // Установка
    {
        $files = array();
        $config = $_POST;
        $config['url'] = str_replace(array('http://', 'https://'), '', $config['url']);
        $config['url'] = 'http://'.$config['url'];
        $config['url'] = str_replace('www.', '', $config['url']);
        if(substr($config['url'], -1) != '/')
            $config['url'] = $config['url'].'/';

        if(!isset($config['use_proxy']))
            unset($config['proxy_list']);

        if(!isset($config['multiinstall'])) {
            unset($config['multiinstall_server']);
            unset($config['multiinstall_login']);
            unset($config['multiinstall_password']);
            unset($config['multiinstall_site_folders']);
            unset($config['multiinstall_site_urls']);
        }

        if(!isset($config['replacements_standart_checkbox'])) {
            unset($config['replacements_standart']);
            unset($config['replacements_standart_separator']);
        }

        if(!isset($config['replacements_regular_checkbox'])) {
            unset($config['replacements_regular']);
            unset($config['replacements_regular_separator']);
        }

        unset($config['go']);

        if(isset($config['script_password']) and !empty($config['script_password']))
            $files['password.cfg'] =  md5($config['script_password']);

        if(isset($config['proxy_list']))
        {
            $files['proxy.txt'] = $config['proxy_list'];
            unset($config['proxy_list']);
            unset($config['use_proxy']);
        }

        if(is_file('password.cfg'))
            unlink('password.cfg');

        if(is_file('replacements_regular.txt'))
            unlink('replacements_regular.txt');

        if(is_file('replacements_standart.txt'))
            unlink('replacements_standart.txt');

        $charset = strtolower($config['charset']);
        $config['charset'] = $charset;
		if(isset($config['replacements_standart']))
			$files['replacements_standart.txt'] = iconv('utf-8', $charset, $config['replacements_standart']);
		if(isset($config['replacements_regular']))
			$files['replacements_regular.txt'] = iconv('utf-8', $charset, $config['replacements_regular']);

        if(isset($config['replacements_standart']))
            unset($config['replacements_standart']);

        if(isset($config['replacements_regular']))
            unset($config['replacements_regular']);

        if(!isset($config['adminpanel']))
            if(is_file('css_colors.txt'))
                unlink('css_colors.txt');

        if(isset($config['adminpanel']))
            unset($config['adminpanel']);

		if(isset($config['formhandler'])) {
			$info = json_decode($config['formhandler']);
			$info = (array)$info;
			$handlers = (array)$info['handlers'][0];
			unset($info['handlers']);
			
			$handler = array();
			if($handlers['type'] == 'email') {
				$handler['data']['email'] = $handlers['emailsend_email'];
				$handler['data']['title'] = $handlers['emailsend_title'];
				$handler['data']['text'] = $handlers['emailsend_text'];
				
				$handler['data']['text'] = str_replace("'", '"', $handler['data']['text']);
				$handler['data']['text'] = "'" . preg_replace('#\{\{name_([^}]+)}}#', '\'.@$_POST[\'$1\'].\'', $handler['data']['text']) . "'";
				$handler['data']['text'] = str_replace('{{datetime}}', '\'.date("Y-m-d H:i:s").\'', $handler['data']['text']);
				file_put_contents('cache/schandler_'.$info['handler'].'.php', 
'<?!!php
header("Content-Type: text/html; charset=utf-8");
if($_SERVER["REQUEST_METHOD"] == "GET")
	exit;
	
$email = "'.$handler['data']['email'].'";
$title = "'.$handler['data']['title'].'";
$text = '.$handler['data']['text'].';
if(mail($email, $title, $text))
	echo "<h2>Успешно!</h2><br/>подождите..<meta http-equiv=\'refresh\' content=\'1; url=/\' />";
else
	echo "<h2>Что-то пошло не так, попробуйте еще раз</h2>";
!!?>');
			}
			elseif($handlers['type'] == 'getpostsend') {
				if($handlers['getpostsend_type'] == 1)
					$handler['data']['type'] = 'get';
				elseif($handlers['getpostsend_type'] == 0)
					$handler['data']['type'] = 'post';
					
				$config['handlers']['getpost_url'] = $handlers['getpostsend_url'];
				$config['handlers']['getpost_type'] = $handler['data']['type'];
			}
			
			$config['handlers']['point'] = $info['point'];
			$config['handlers']['name'] = $info['handler'];
			$config['handlers']['type'] = $handlers['type'];
			
			unset($config['formhandler']);
		}
			
			
        $json_config = json_encode($config);
        $files['config.cfg'] = $json_config;
        $files['.htaccess'] =
'AddDefaultCharset UTF-8

RewriteEngine On
RewriteBase /

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

RewriteRule ^(.*) index.php [QSA]
';

        if(isset($config['multiinstall']) and $config['multiinstall'] == 'on') {
            $server = $config['multiinstall_server'];
            $login = $config['multiinstall_login'];
            $password = $config['multiinstall_password'];
            $folders = array_map('trim', explode("\n", $config['multiinstall_site_folders']));
            $urls = array_map('trim', explode("\n", $config['multiinstall_site_urls']));

            $ftp = ftp_connect($server);
            ftp_login($ftp, $login, $password);
            $list = ftp_nlist($ftp, '.');
            $path = '';
            if(array_search('public_html', $list))
                $path = 'public_html/';
			
			@mkdir('multi_temp');
			file_put_contents('multi_temp/.htaccess', $files['.htaccess']);
			file_put_contents('multi_temp/password.cfg', $files['password.cfg']);
			file_put_contents('multi_temp/index.php', file_get_contents('index.php'));
			file_put_contents('multi_temp/s.db', file_get_contents('s.db'));
			
            foreach($folders as $key => $value) {
                $tmp_config = $config;
				if(isset($tmp_config['multiinstall'])) {
					unset($tmp_config['multiinstall']);
					unset($tmp_config['multiinstall_server']);
					unset($tmp_config['multiinstall_login']);
					unset($tmp_config['multiinstall_password']);
					unset($tmp_config['multiinstall_site_folders']);
					unset($tmp_config['multiinstall_site_urls']);
				}

                $tmp_config['url'] = isset($urls[$key]) ? $urls[$key] : $urls[0];
                $tmp_config['url'] = str_replace('www.', '', $tmp_config['url']);
				$tmp_config['charset'] = sc::getCharset($tmp_config['url']);
                $tmp_config = json_encode($tmp_config);
                $files['config.cfg'] = $tmp_config;
				$files['index.php'] = $files['s.db'] = '';
				
				file_put_contents("multi_temp/config.cfg", $files['config.cfg']);
				
				@ftp_mkdir($ftp, $path.$value.'/system');
				@ftp_mkdir($ftp, $path.$value.'/cache');
				foreach(array_keys($files) as $file_name)
					ftp_put($ftp, $path.$value.'/'.$file_name, 'multi_temp/'.$file_name, FTP_BINARY);
                
				foreach(array('adminpanel.html', 'main.html', 'install_success.html', 'http.php', 'class.upload.php', 'main.js', 'main.css') as $system_file_name)
					ftp_put($ftp, $path.$value.'/system/'.$system_file_name, 'system/'.$system_file_name, FTP_BINARY);
            }
			
			unlink('multi_temp/.htaccess');
			sc::removeDir('multi_temp');
			die('Готово');
        }
        else {
            foreach($files as $key => $value)
                file_put_contents($key, $value);

            if(is_dir('cache'))
                sc::removeDir($_SERVER['DOCUMENT_ROOT'].'/cache');

            if(is_dir('temp'))
                sc::removeDir($_SERVER['DOCUMENT_ROOT'].'/temp');

            @mkdir('cache');
        }

        header('Content-Type: text/html; charset=utf-8');
        echo file_get_contents('system/install_success.html');
    }
    else { // Скрипт не установлен
        header('Content-Type: text/html; charset=utf-8');
        $html = file_get_contents('system/main.html');
        echo $html;
    }
}
else { // Скрипт установлен - вывод сайта
    if(isset($_GET['sitecopy']) and $_GET['sitecopy'] == 'adminpanel')
    {
        if($password = sc::getPassword()) {
            if(!isset($_GET['password'])) {
                header('Location: /index.php');
                die();
            }
            else
                if(md5($_GET['password']) != $password) {
                    header('Location: /index.php');
                    die();
                }
        }

        if(isset($_GET['action'])) {
            switch($_GET['action']) {
                case 'clearcsssettings':
                    if(file_exists('css_colors.txt')) {
                        unlink('css_colors.txt');
                        echo 'Цвета перегенерированны.';
                    }
                    else
                        echo 'Нечего сбрасывать :) Чтобы сбросить настройки цветов, нужно сначала выбрать опцию "Рандомное изменение цветов в CSS"';
                    break;
                case 'clearcache':
                    sc::removeDir('cache', false);
                    mkdir('cache', 0777);
                    echo 'Очистили папку cache.';
                    break;
                case 'reinstall':
                    unlink('config.cfg');
                    header('Location: /');
                    break;
				case 'ajax':
					if(isset($_GET['function']) and $_GET['function'] == 'get_charset_from_url')
						sc::ajaxPrint(sc::getCharset($_GET['url']));
					break;
            }
            die();
        }
        else {
            if($password = sc::getPassword()) {
                if(!isset($_GET['password'])) {
                    header('Location: /');
                    die();
                }
                else
                    if(md5($_GET['password']) != $password) {
                        header('Location: /');
                        die();
                    }
            }

            $config = (array)json_decode(file_get_contents('config.cfg'));

            if(file_exists('replacements_standart.txt'))
                $config['replacements_standart_checkbox'] = 'on';

            if(file_exists('replacements_regular.txt'))
                $config['replacements_regular_checkbox'] = 'on';

            if(file_exists('proxy.txt')) {
                $config['use_proxy'] = 'on';
                $config['proxy_list'] = file_get_contents('proxy.txt');
            }


            $config = json_encode($config);
            $html = file_get_contents('system/adminpanel.html');
            $html = str_replace('{{config}}', $config, $html);

            header('Content-Type: text/html; charset=utf-8');
            echo $html;
        }
    }
    else {
        $config = json_decode(file_get_contents('config.cfg'));
        $query = $clear_url = $_SERVER['REQUEST_URI'];

        if($strpos = strpos($clear_url, '?'))
            $clear_url = substr($clear_url, 0, $strpos);

		$query = ($query == '/') ? null : substr($query, 1);

        if(substr($clear_url, 0, 1) == '/')
            $clear_url = substr($clear_url, 1);


        $sc = sc::create((array) $config, $query, $_GET, $clear_url)->
				  getPage()->
				  printPage();
    }
}