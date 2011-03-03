<?php
/**
 * LICENSE
 * 
 * This source file is subject to the license that is bundled
 * with this package in the file LICENSE.TXT.
 * 
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to develop@jacobkiers.net so we can send you a copy immediately.
 * 
 * @copyright   Copyright (c) 2010 Jacob Kiers
 * @license     New BSD License
 *
 * @category    Yate
 * @package     Yate\Core
 * @subpackage  Yate\Core\Connection
 */

namespace Yate\Core\Connection;
use Yate\Core\AbstractMessage;

/**
 * ConnectionInterface describes the contract for Yate connections
 *
 * @category    Yate
 * @package     Yate\Core
 * @subpackage  Yate\Core\Connection
 *
 * @copyright   Copyright (c) 2010 Jacob Kiers
 *
 * @since 0.1
 */
interface ConnectionInterface {

    /**
     * Connects to the Yate server.
     *
     * @return  ConnectionInterface
     * @throws  Exception
     */
    abstract public function connect();

    /**
     * Disconnects for the Yate server
     *
     * @return  ConnectionInterface
     */
    abstract public function disconnect();

    /**
     * Resets the connection to the Yate server
     *
     * @return  ConnectionInterface
     * @throws  Exception
     */
    abstract public function reconnect();

    /**
     * Receive a message from Yate
     *
     * @return  AbstractMessage
     * @throws  Exception
     */
    abstract public function receiveMessage();

    /**
     * Send a message to Yate
     *
     * @param   AbstractMessage $message  The Yate message
     *
     * @return  ConnectionInterface
     * @throws  Exception
     *
     */
    abstract public function sendMessage(AbstractMessage $message);

}
/* vi: set softtabstop=4 shiftwidth=4 expandtab: */
