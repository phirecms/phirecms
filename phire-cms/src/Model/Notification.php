<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Crypt\Bcrypt;
use Pop\Mail\Mail;

/**
 * Notification model class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0
 */
class Notification extends AbstractModel
{

    /**
     * Send user verification notification
     *
     * @param  mixed  $user
     * @param  string $title
     * @return void
     */
    public function sendVerification($user, $title)
    {
        $host    = $_SERVER['HTTP_HOST'];
        $domain  = str_replace('www.', '', $host);
        $schema  = (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == '443')) ? 'https://' : 'http://';

        // Set the recipient
        $rcpt = [
            'name'   => $user->username,
            'email'  => $user->email,
            'url'    => $schema . $host . '/verify/' . $user->id . '/' . sha1($user->email),
            'domain' => $domain,
            'title'  => $title
        ];

        // Check for an override template
        $mailTemplate = __DIR__ . '/../../view/mail/verify.txt';

        // Send email verification
        $mail = new Mail($title . ' (' . $domain . ') - Email Verification', $rcpt);
        $mail->from('noreply@' . $domain);
        $mail->setText(file_get_contents($mailTemplate));
        $mail->send();
    }

    /**
     * Send user approval notification
     *
     * @param  mixed  $user
     * @param  string $title
     * @return void
     */
    public function sendApproval($user, $title)
    {
        $host   = $_SERVER['HTTP_HOST'];
        $domain = str_replace('www.', '', $host);

        // Set the recipient
        $rcpt = [
            'name'   => $user->username,
            'email'  => $user->email,
            'domain' => $domain,
            'title'  => $title
        ];

        // Check for an override template
        $mailTemplate = __DIR__ . '/../../view/mail/approval.txt';

        // Send email verification
        $mail = new Mail($title . ' (' . $domain . ') - Approval', $rcpt);
        $mail->from('noreply@' . $domain);
        $mail->setText(file_get_contents($mailTemplate));
        $mail->send();
    }

    /**
     * Send user password reset notification
     *
     * @param  mixed  $user
     * @param  string $title
     * @return void
     */
    public function sendReset($user, $title)
    {
        $host           = $_SERVER['HTTP_HOST'];
        $domain         = str_replace('www.', '', $host);
        $newPassword    = $this->random();
        $user->password = (new Bcrypt())->create($newPassword);
        $user->save();

        $rcpt = [
            'name'     => $user->username,
            'email'    => $user->email,
            'domain'   => $domain,
            'username' => $user->username,
            'password' => $newPassword,
            'title'    => $title
        ];

        $mailTemplate = __DIR__ . '/../../view/mail/forgot.txt';

        // Send email verification
        $mail = new Mail($title . ' (' . $domain . ') - Password Reset', $rcpt);
        $mail->from('noreply@' . $domain);
        $mail->setText(file_get_contents($mailTemplate));
        $mail->send();
    }

    /**
     * Create random alphanumeric string
     *
     * @param  int $length
     * @return string
     */
    protected function random($length = 8)
    {
        $chars = [
            0 => str_split('abcdefghjkmnpqrstuvwxyz'),
            1 => str_split('23456789')
        ];
        $indices = [0, 1];
        $str     = '';

        for ($i = 0; $i < (int)$length; $i++) {
            $index = $indices[rand(0, (count($indices) - 1))];
            $subIndex = rand(0, (count($chars[$index]) - 1));
            $str .= $chars[$index][$subIndex];
        }

        return $str;
    }

}