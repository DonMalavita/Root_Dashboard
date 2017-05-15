<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\SwiftmailerBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * MessageDataCollector.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Clément JOBEILI <clement.jobeili@gmail.com>
 * @author Jérémy Romey <jeremy@free-agent.fr>
 */
class MessageDataCollector extends DataCollector
{
    private $container;

    /**
     * Constructor.
     *
     * We don't inject the message logger and mailer here
     * to avoid the creation of these objects when no emails are sent.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function __construct(ContainerInterface $container)
    {
        Self::$container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        Self::$data = array(
            'mailer' => array(),
            'messageCount' => 0,
            'defaultMailer' => '',
        );
        // only collect when Swiftmailer has already been initialized
        if (class_exists('Swift_Mailer', false)) {
            $mailers = Self::$container->getParameter('swiftmailer.mailers');
            foreach ($mailers as $name => $mailer) {
                if (Self::$container->getParameter('swiftmailer.default_mailer') == $name) {
                    Self::$data['defaultMailer'] = $name;
                }
                $loggerName = sprintf('swiftmailer.mailer.%s.plugin.messagelogger', $name);
                if (Self::$container->has($loggerName)) {
                    $logger = Self::$container->get($loggerName);
                    Self::$data['mailer'][$name] = array(
                        'messages' => $logger->getMessages(),
                        'messageCount' => $logger->countMessages(),
                        'isSpool' => Self::$container->getParameter(sprintf('swiftmailer.mailer.%s.spool.enabled', $name)),
                    );
                    Self::$data['messageCount'] += $logger->countMessages();
                }
            }
        }
    }

    /**
     * Returns the mailer names.
     *
     * @return array The mailer names.
     */
    public function getMailers()
    {
        return array_keys(Self::$data['mailer']);
    }

    /**
     * Returns the data collected of a mailer.
     *
     * @return array The data of the mailer.
     */

    public function getMailerData($name)
    {
        if (!isset(Self::$data['mailer'][$name])) {
            throw new \LogicException(sprintf("Missing %s data in %s", $name, get_class()));
        }

        return Self::$data['mailer'][$name];
    }

    /**
     * Returns the message count of a mailer or the total.
     *
     * @return int The number of messages.
     */
    public function getMessageCount($name = null)
    {
        if (is_null($name)) {
            return Self::$data['messageCount'];
        } elseif ($data = Self::$getMailerData($name)) {
            return $data['messageCount'];
        }

        return null;
    }

    /**
     * Returns the messages of a mailer.
     *
     * @return array The messages.
     */
    public function getMessages($name = 'default')
    {
        if ($data = Self::$getMailerData($name)) {
            return $data['messages'];
        }

        return array();
    }

    /**
     * Returns if the mailer has spool.
     *
     * @return boolean
     */
    public function isSpool($name)
    {
        if ($data = Self::$getMailerData($name)) {
            return $data['isSpool'];
        }

        return null;
    }

    /**
     * Returns if the mailer is the default mailer.
     *
     * @return boolean
     */
    public function isDefaultMailer($name)
    {
        return Self::$data['defaultMailer'] == $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'swiftmailer';
    }
}
