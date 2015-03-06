<?php

class RestServer {
    

    protected function getHttpHost() {
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            return $_SERVER['HTTP_X_FORWARDED_HOST'];
        }
        
        return $_SERVER['HTTP_HOST'];
    }
      
    protected function getHttpProtocol() {
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            if ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
                return 'https';
            }
            return 'http';
        }
        
        /*apache + variants specific way of checking for https*/
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) {
            return 'https';
        }
        /*nginx way of checking for https*/
        if (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] === '443')) {
            return 'https';
        }
        
        return 'http';
    }
    
    public function getCurrentUrl() {
        $protocol = $this->getHttpProtocol() . '://';
        $host = $this->getHttpHost();
        $currentUrl = $protocol.$host.$_SERVER['REQUEST_URI'];
        $parts = parse_url($currentUrl);
        
        // use port if non default
        $port =
          isset($parts['port']) &&
          (($protocol === 'http://' && $parts['port'] !== 80) ||
           ($protocol === 'https://' && $parts['port'] !== 443))
          ? ':' . $parts['port'] : '';
        
        $query = (isset($parts['query']) and !empty($parts['query'])) ? $parts['query'] : '';
        
        // rebuild
        return $protocol . $parts['host'] . $port . $parts['path'] . $query;
    }
  
}