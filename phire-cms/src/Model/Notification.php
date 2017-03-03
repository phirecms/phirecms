<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Mail\Mailer;
use Pop\Mail\Message;

/**
 * Notification model class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
class Notification extends AbstractModel
{

    /**
     * Send user verification notification
     *
     * @param  mixed  $user
     * @param  string $title
     * @param  Mailer $mailer
     * @return void
     */
    public function sendVerification($user, $title, Mailer $mailer)
    {
        $host    = $_SERVER['HTTP_HOST'];
        $domain  = str_replace('www.', '', $host);
        $schema  = (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == '443')) ? 'https://' : 'http://';

        $body = file_get_contents(__DIR__ . '/../../view/mail/verify.txt');
        $body = str_replace([
            '[{name}]',
            '[{email}]',
            '[{url}]',
            '[{domain}]',
            '[{title}]'
        ], [
            $user->username,
            $user->email,
            $schema . $host . BASE_PATH . APP_URI . '/verify/' . $user->id . '/' . sha1($user->email),
            $domain,
            $title
        ], $body);

        $message = new Message($title . ' (' . $domain . ') - Email Verification');
        $message->setTo([$user->email => $user->username])
            ->setFrom('noreply@' . $domain)
            ->setBody($body);

        $mailer->send($message);
    }

    /**
     * Send user approval notification
     *
     * @param  mixed  $user
     * @param  string $title
     * @param  Mailer $mailer
     * @return void
     */
    public function sendApproval($user, $title, Mailer $mailer)
    {
        $host   = $_SERVER['HTTP_HOST'];
        $domain = str_replace('www.', '', $host);

        $body = file_get_contents(__DIR__ . '/../../view/mail/approval.txt');
        $body = str_replace([
            '[{name}]',
            '[{email}]',
            '[{domain}]',
            '[{title}]'
        ], [
            $user->username,
            $user->email,
            $domain,
            $title
        ], $body);

        $message = new Message($title . ' (' . $domain . ') - Approval');
        $message->setTo([$user->email => $user->username])
            ->setFrom('noreply@' . $domain)
            ->setBody($body);

        $mailer->send($message);
    }

    /**
     * Send user password reset notification
     *
     * @param  mixed  $user
     * @param  string $title
     * @param  Mailer $mailer
     * @return void
     */
    public function sendReset($user, $title, Mailer $mailer)
    {
        $host           = $_SERVER['HTTP_HOST'];
        $domain         = str_replace('www.', '', $host);
        $newPassword    = $this->random();
        $user->password = password_hash($newPassword, PASSWORD_BCRYPT);
        $user->save();

        $body = file_get_contents(__DIR__ . '/../../view/mail/forgot.txt');
        $body = str_replace([
            '[{name}]',
            '[{email}]',
            '[{domain}]',
            '[{username}]',
            '[{password}]',
            '[{title}]'
        ], [
            $user->username,
            $user->email,
            $domain,
            $user->username,
            $newPassword,
            $title
        ], $body);

        $message = new Message($title . ' (' . $domain . ') - Password Reset');
        $message->setTo([$user->email => $user->username])
            ->setFrom('noreply@' . $domain)
            ->setBody($body);

        $mailer->send($message);
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