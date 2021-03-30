<?php

/*
 * Lovingly developed by the WhiteHats (https://whitehats.com.br) security team
 * We hope that will be usefull ;)
 * 
 * Created at: 2021-03-30
 * 
 * Methods:
 *     Anti flood/DDoS
 *     Anti injection
 *     Anti .php upload
 * 
 * Important:
 *     This file/functions/methods must be placed at the top of your code
 *     before any echo or output string on your page, 
 *     and before insert any data on your database or upload some file
 * 
 * About $_POST protection:
 *     We recommend that you make token forms to compare last data with actual 
 *     and prevent duplicated posts (flood)
 * 
 *     Example:
 *         
 *         PHP:
 *             if ($_POST['token']==$_SESSION['token']) {
 *                 unset($_SESSION['token']);
 *                 //make your insert/update/delete here
 *             } else {
 *                 //expired
 *             }
 *            
 *         HTML:
 * 
 *             <form method="post">
 *                 <input type="hidden" name="token" value="<?php echo $_SESSION['token']=md5(time()); ?>">
 *                 <!-- aqui seu form normal -->
 *                 <button type="submit">Submit</button>
 *             </form>
 */

function is_session_started() {
    if (php_sapi_name() !== 'cli') {
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            return session_status() === PHP_SESSION_ACTIVE ? true : false;
        } else {
            return session_id() === '' ? false : true;
        }
    }
    return false;
}

//start session if not started
if (!is_session_started()) {
    session_start();
}

//Init anti flood string
$flood_string = '';

//Anti injection on $_POST methods
if (isset($_POST)) {
    foreach ($_POST as $field => $value) {
        $_POST[$filed] = mysql_real_escape_string($value);
        $flood_string .= $_POST[$filed];
    }
}

//Anti injection on $_GET methods
if (isset($_GET)) {
    foreach ($_GET as $field => $value) {
        $_GET[$filed] = mysql_real_escape_string($value);
        $flood_string .= $_GET[$filed];
    }
}

//Anti injection on $_SESSION methods (if you need)
//if (isset($_SESSION)) {
//    foreach($_SESSION as $field=>$value) {
//        $_SESSION[$filed]=mysql_real_escape_string($value);
//    }
//}

//Prevent .php file upload
if (isset($_FILES)) {
    foreach ($_FILES as $field => $value) {
        $name = $_FILES[$field]['name'];
        $x = explode('.', $name);
        $ext = end($x);
        $extension = mb_strtolower($ext, 'UTF-8');
        if ($extension == 'php') {
            //change the extension to prevent .php executable files
            $_FILES[$field]['name'] = str_replace('.' . $ext, '', $name) . '.php-sended';
        }
    }
}

$flood_count=0;
//if have repeated post or get strings
if (!empty($_SESSION['last_flood_string']) and $_SESSION['last_flood_string']==$flood_string) {
    if ($_SESSION['flood_string_date']<=date('Y-m-d H:i:s',strtotime('-1 second'))) {
        $flood_count++;
    }
    $_SESSION['flood_string_date']=date('Y-m-d H:i:s');
} else {
    $flood_count=0;
}

if ($flood_count>=1) {
    //anti Ddos / anti flood
    //interrupt the page execution
    exit;
}

$_SESSION['last_flood_string']=$flood_string;