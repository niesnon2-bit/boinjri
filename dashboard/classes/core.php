<?php
class Core {
    public function ip() {
        return $_SERVER['REMOTE_ADDR'];
    }
    
    public function refresh() {
        header('Refresh: 0');
        exit;
    }
}
