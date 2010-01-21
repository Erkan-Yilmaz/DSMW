<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * GET Request => code 200
 * POST Request => code 302
 *
 * @author hantz
 */
class p2pBot {

    var $bot;

    function  __construct($bot) {
        $this->bot = $bot;
    }

    function createPage($pageName,$content) {
        $res = $this->bot->wikiFilter($pageName,'callbackTestFct','summary',$content);
        return $res;
    }


//    static function append($content,$line) {
//        $content=$content.$line;
//        return $content;
//    }

    function editPage($pageName,$line) {
        $res = $this->bot->wikiFilter($pageName,'append','summary',$line);
        return $res;
    }

    function createPush($name, $request) {
    //$post_vars['url'] = $url;
        $post_vars['name'] = $name;
        $post_vars['keyword'] = $request;
        $this->bot->maxredirs = 0;
        if ($this->bot->submit( $this->bot->wikiServer . PREFIX . '/index.php?action=pushpage', $post_vars ) ) {
        // Now we need to check whether our edit was accepted. If it was, we'll get a 302 redirecting us to the article. If it wasn't (e.g. because of an edit conflict), we'll get a 200.
            $code = substr($this->bot->response_code,9,3); // shorten 'HTTP 1.1 200 OK' to just '200'
            if ('200'==$code) {
                echo 'Create push failed with error 200 : ('.$this->bot->results.')';
                return false;
            }
            elseif ('302'==$code) {
                return true;
            }
            else {
                echo 'push failed error not 200 : ('.$this->bot->results.')';
                return false;
            }
        }else {
            echo 'push submit failed ('.$this->bot->wikiServer . PREFIX . '/index.php?action=pushpage '. $post_vars.')';
            return false;
        }
    }

    function push($name) {
        $post_vars['action'] = 'onpush';
        if(is_array($name)) {
            $post_vars['push'] = $name;
        }else {
            $post_vars['push[]'] = $name;
        }
        $this->bot->maxredirs = 0;
        if ($this->bot->submit($this->bot->wikiServer . PREFIX . '/index.php',$post_vars) ) {
        // Now we need to check whether our edit was accepted. If it was, we'll get a 302 redirecting us to the article. If it wasn't (e.g. because of an edit conflict), we'll get a 200.
            $code = substr($this->bot->response_code,9,3); // shorten 'HTTP 1.1 200 OK' to just '200'
            if ('200'==$code) {
                echo 'push failed with error 200 : ('.$this->bot->results.')';
                return false;
            }
            elseif ('302'==$code)
                return true;
            else {
                echo 'push failed error not 200 : ('.$this->bot->results.')';
                return false;
            }
        }else {
            echo 'push submit failed ('.$this->bot->wikiServer . PREFIX . '/index.php '.$post_vars.')';
            return false;
        }
    }

    function createPull($pullName,$url, $pushName) {
        $post_vars['pullname'] = $pullName;
        $post_vars['url'] = $url.'/PushFeed:'.$pushName;
        //$post_vars['pushname'] = $pushName;
        $this->bot->maxredirs = 0;
        if ($this->bot->submit( $this->bot->wikiServer . PREFIX . '/index.php?action=pullpage', $post_vars ) ) {
        // Now we need to check whether our edit was accepted. If it was, we'll get a 302 redirecting us to the article. If it wasn't (e.g. because of an edit conflict), we'll get a 200.
            $code = substr($this->bot->response_code,9,3); // shorten 'HTTP 1.1 200 OK' to just '200'
            if ('200'==$code) {
                echo 'Create pull failed with error 200 : ('.$this->bot->results.')';
                return false;
            }
            elseif ('302'==$code)
                return true;
            else {
                echo 'Create pull failed error not 200 : ('.$this->bot->results.')';
                return false;
            }
        //return false; // if you get this, it's time to debug.
        }else {
            echo 'Create pull submit failed ('.$this->bot->wikiServer . PREFIX . '/index.php?action=pullpage '. $post_vars.')';
            return false;
        }
    }

    function pull($pullName) {
        if(is_array($pullName)) {
            $post_vars['pull'] = $pullName;
        }else {
            $post_vars['pull[]'] = $pullName;
        }
        $post_vars['action'] = 'onpull';
        $this->bot->maxredirs = 0;
        $url = $this->bot->wikiServer.PREFIX.'/index.php';
        if ($this->bot->submit($this->bot->wikiServer.PREFIX.'/index.php',$post_vars) ) {
        // Now we need to check whether our edit was accepted. If it was, we'll get a 302 redirecting us to the article. If it wasn't (e.g. because of an edit conflict), we'll get a 200.
            $code = substr($this->bot->response_code,9,3); // shorten 'HTTP 1.1 200 OK' to just '200'
            if ('200'==$code) {
                echo "pull failed with error 200:(".$this->bot->results.")";
                return false;
            }
            elseif ('302'==$code)
                return true;
            else {
                echo "pull failed error not 200:(".$this->bot->results.")";
                return false;
            }
        }else {
            echo "pull submit failed:(".$this->bot->wikiServer.PREFIX.'/index.php'.$post_vars.")";
            return false;
        }
    }

    function updateProperies() {
        $post_vars['server'] = $this->bot->wikiServer;
        if ($this->bot->submit($this->bot->wikiServer.PREFIX.'/extensions/DSMW/bot/DSMWBot.php',$post_vars) ) {
        // Now we need to check whether our edit was accepted. If it was, we'll get a 302 redirecting us to the article. If it wasn't (e.g. because of an edit conflict), we'll get a 200.
            $code = substr($this->bot->response_code,9,3); // shorten 'HTTP 1.1 200 OK' to just '200'
            if ('200'==$code) {
                echo "updated properties failed with error 200:(".$this->bot->results.")";
                return false;
            }
            elseif ('302'==$code)
                return true;
            else {
                echo "updated properties failed error not 200:(".$this->bot->results.")";
                return false;
            }
        }else {
            echo "updated properies submit failed:(".$this->bot->wikiServer.PREFIX.'/index.php'.$post_vars.")";
            return false;
        }
    }

    function articlesUpdate() {
        $post_vars['action'] = 'logootize';
        $this->bot->maxredirs = 0;
        //$url = $this->bot->wikiServer.PREFIX.'/index.php/Special:DSMWAdmin';
        if ($this->bot->submit($this->bot->wikiServer.PREFIX.'/index.php/Special:DSMWAdmin',$post_vars) ) {
        // Now we need to check whether our edit was accepted. If it was, we'll get a 302 redirecting us to the article. If it wasn't (e.g. because of an edit conflict), we'll get a 200.
            $code = substr($this->bot->response_code,9,3); // shorten 'HTTP 1.1 200 OK' to just '200'
            if ('200'==$code) {
        
                return true;
            }
            elseif ('302'==$code)
                return true;
            else {
                echo "articlesUpdate failed error not 200:(".$this->bot->results.")";
                return false;
            }
        }else {
            echo "articlesUpdate submit failed:(".$this->bot->wikiServer.PREFIX.'/index.php/Special:DSMWAdmin'.$post_vars.")";
            return false;
        }
    }

    function importXML($file) {
        if (!$this->bot->wikiConnect())
			die ("Unable to connect.");

        if (!$this->bot->fetch( $this->bot->wikiServer . PREFIX . '/index.php?title=Special:Import' ) )
			return false;

       
        $val = strpos($this->bot->results, 'name="editToken" type="hidden" value="');//starting value
        $val2 = strlen('name="editToken" type="hidden" value="');
        $val1 = strpos($this->bot->results, '"', $val+$val2);//ending value
        $editToken = substr($this->bot->results, $val+$val2, $val1-($val+$val2));
        $fp = fopen($file, 'r');
        $xmlContent = fread($fp, filesize($file));
        fclose($fp);
	$post_vars['editToken'] = $editToken;
        $post_vars['action'] = 'submit';
        $post_vars['source'] = 'upload';
        $post_file['xmlimport'] = $file;
//        $post_vars['xmlimport']['name']= 'Wikipedia-20091119095555.xml';
//        $post_vars['xmlimport']['tmp_name']= $file;
//        $post_vars['xmlimport']['type']= 'text/xml';
        
        $this->bot->maxredirs = 0;
        $this->bot->set_submit_multipart();
        //$url = $this->bot->wikiServer.PREFIX.'/index.php/Special:DSMWAdmin';
        if ($this->bot->submit($this->bot->wikiServer.PREFIX.'/index.php?title=Special:Import&action=submit',$post_vars, $post_file) ) {
        // Now we need to check whether our edit was accepted. If it was, we'll get a 302 redirecting us to the article. If it wasn't (e.g. because of an edit conflict), we'll get a 200.
            $code = substr($this->bot->response_code,9,3); // shorten 'HTTP 1.1 200 OK' to just '200'
            if ('200'==$code) {
                echo "import failed with error 200:(".$this->bot->results.")";
                return true;
            }
            elseif ('302'==$code)
                return true;
            else {
               
                return false;
            }
        }else {
            echo "import submit failed:(".$this->bot->wikiServer.PREFIX.'/index.php?title=Special:Import&action=submit'.$post_vars.")";
            return false;
        }
    }
}

function callbackTestFct($content1,$content2) {
    return $content2;
}

function append($content,$line) {
    $content.=$line;
    return $content;
}
?>
