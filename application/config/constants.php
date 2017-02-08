<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/* ======================================
             UPLOADS PATH
=========================================*/
define('DropPath','C:/Users/Kristopher/Dropbox/');
/* ======================================
             TYPES OF USERS
=========================================*/
define('AGENT', 1);
define('APPLICANT', 3);
define('MANAGER', 2);
/* ======================================
             JWT SECRET KEY
=========================================*/
define('JWT_SECRET_KEY', 'NiZhiBuZhiDaoWoShiSheiMa?');
/* ======================================
                LOAN TYPES
=========================================*/
define('PERSONAL_LOAN', 40);
define('CASH_VOUCHER', 31);
define('LOAN_TYPES', array(40, 31));
define('LOAN_TYPES_NAMES', array(
   40 => "PRÉSTAMO PERSONAL",
   31 => "VALE DE CAJA"
));
/* ======================================
                STATUSES
=========================================*/
define('RECEIVED', 'Recibida');
define('APPROVED', 'Aprobada');
define('REJECTED', 'Rechazada');
define('APPROVED_VERB', 'aprobar');
define('REJECTED_VERB', 'rechazar');
define('STATUSES', array('Recibida', 'Aprobada', 'Rechazada'));
define('STATUSES_VERB', array(RECEIVED => 'recibir', APPROVED => 'aprobar', REJECTED => 'rechazar'));
/* ======================================
                MAILGUN
=========================================*/
define('MAILGUN_PRIVATE_KEY', 'key-53747f43c23bd393d8172814c60e17ba');
define('MAILGUN_SENDER_DOMAIN', 'sandbox5acc2f3be9df4e80baaa6a9884d6299b.mailgun.org');
define('MAILGUN_SENDER', 'IPAPEDI <noreply@ipapedi.com>');
/* ======================================
             HISTORY ACTIONS
=========================================*/
define('HISTORY_ACTIONS_CODES', array(
    'creation' => 1,
    'addition' => 2,
    'modification' => 3,
    'closure' => 4,
    'elimination' => 5,
    'report' => 6,
    'validation' => 7
));
define('HISTORY_ACTIONS_NAMES', array(
    1 => 'Creación',
    2 => 'Adición',
    3 => 'Modificación',
    4 => 'Cierre',
    5 => 'Eliminación',
    6 => 'Reporte',
    7 => 'Validación'
));