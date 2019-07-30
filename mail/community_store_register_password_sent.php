<?php
defined('C5_EXECUTE') or die("Access Denied.");

$subject = $siteName . ' - ' . t('Orlando Pirates FC Login Credentials');

/**
 * HTML BODY START
 */
ob_start();

?>
    <!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
    <html>
    <head></head>
    <body>
    <p>Dear <?php echo $fullName ?>,</p>
    <br>
    <h3>Orlando Pirates Shop Credentials</h3>
    <p>Email :  <?php echo $email; ?></p>
    <p>Username :  <?php echo $userName; ?></p>
    <p>Password :  <?php echo $password; ?></p>
   
    </body>
    </html>

<?php
$bodyHTML = ob_get_clean();
/**
 * HTML BODY END
 *
 * =====================
 *
 * PLAIN TEXT BODY START
 */
ob_start();

?>