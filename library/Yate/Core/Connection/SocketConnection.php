<?php
/**
 * YatePHP
 *
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
 * @package     Yate_Core
 * @subpackage  Connection
 */

namespace Yate\Core\Connection;
use Yate\Core;

/**
 * Class providing core Yate functionality, such as connecting to the server
 * and sending messages
 *
 * @category    Yate
 * @package     Yate_Core
 *
 * @copyright   Copyright (c) 2010 Jacob Kiers
 * @license     New BSD License
 *
 * @since       0.1
 */
class SocketConnection implements ConnectionInterface
{
    /**
     * IP address of hostname of Yate server
     * 
     * @var string
     */
    protected $_address;

    /**
     * Contains the connection to Yate
     *
     * @var resource
     */
    protected $_connection;

    /**
     * Port where Yate server listens. Use 0 when using a socket
     *
     * @var integer
     */
    protected $_port;

    /**
     * Creates an instance of this object.
     * 
     * @param string $address Optional: IP/hostname of Yate server
     * @param integer $port Optional: Port where Yate server listens
     */
    public function __construct($address = '127.0.0.1', $port = 0)
    {
        $this->_address = $address;
        $this->_port = $port;
    }

    
    /**
     * Disconnect and destroy this object
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Connect to the Yate server
     * 
     * @return SocketConnection
     */
    public function connect()
    {
        if ($this->_connection) {
            return $this;
        }

        $domain = 0;
        $protocol = 0;
        if (0 !== $this->_port) {
            $domain = AF_INET;
            $protocol = SOL_TCP;
        } else {
            $domain = AF_UNIX;
            $protocol = SOL_SOCKET;
        }

        // Create socket and connect. Throw an exception if
        // either of these are impossible.
        $socket = socket_create($domain, SOCK_STREAM, $protocol);
        if (false === $socket) {
            throw new Exception("Coudn't create socket!");
        }

        $this->_connection = socket_connect($socket, $this->_address, $this->_port);
        if (false === $this->_connection) {
            throw new Exception("Couldn't connect to Yate server!");
        }

        return $this;
    }

    /**
     * Disconnect from the server
     *
     * @return SocketConnection
     */
    public function disconnect()
    {
        if ($this->_connection) {
            socket_close($this->_connection);
        }
        return $this;
    }

    /**
     * Reset the connection
     *
     * @return SocketConnection
     */
    public function reconnect()
    {
        $this->disconnect();
        $this->connect();
        return $this;
    }

    /**
     * Set the server address and port.
     *
     * Use port 0 when using a UNIX socket.
     *
     * @param string $address The IP address or hostname of the Yate server
     * @param integer $port The port on which the Yate server listens.
     * @return SocketConnection
     */
    public function setServer($address, $port)
    {
        $this->_address = $address;
        $this->_port = $port;
        return $this;
    }

    /**
     * Receive a message from Yate.
     *
     * @return AbstractMessage|null A Message when available, else null
     * @throws \Yate\Core\Exception
     */
    public function receiveMessage()
    {
        // Since all messages are separated by a newline (ASCII code 10),
        // read all data until we stumble upon that. We only read one message
        // at a time.
        $data = socket_read($this->_connection, "\n");
        
        if (false === $data) {
            $error = socket_strerror(socket_last_error($this->_connection));
            throw new Exception("Error reading from Yate: ". $error);
        }

        if ('' == $data) {
            return null;
        }
        
        // Return a message from the factory
        return AbstractMessage::factory($data);
    }

    /**
     * Sends the given message to Yate
     * 
     * @param AbstractMessage $message
     * @return SocketConnection
     * @throws \Yate\Core\Exception
     */
    public function sendMessage(AbstractMessage $message)
    {
        // Send message
        $to_send = $message->__toString();

        // In a while loop, because not all bytes may be written at once.
        // See documentation: http://php.net/socket_write
        $total_length = strlen($to_send);
        $offset = 0;
        while ($offset < $total_length) {
            $msg = substr($to_send, $offset);
            $bytes_to_write = $total_length - $offset;
            $sent = socket_write($this->_connection, $msg, $bytes_to_write);
            if ($sent === false) {
                // Error occurred, break the while loop 
                break;
            }
            $offset += $sent;
        }

        // Check for succesful delivery
        if ($offset < $total_length) {
            // @TODO: Another solution may be better.
            $error = socket_strerror(socket_last_error($this->_connection));
            throw new Exception("Error writing to Yate: " . $error);
        }

        return $this;
    }
}
/* vi: set softtabstop=4 shiftwidth=4 expandtab: */
