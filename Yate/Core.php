<?php
/**
 * YatePHP
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * 
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to develop@jacobkiers.net so we can send you a copy immediately.
 *
 * @category    Yate
 * @package     Yate_Core
 * @copyright   Copyright (c) 2010 Jacob Kiers
 * @license     New BSD License
 */

namespace Yate;

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
class Core
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
     * Decodes a Yate message representation, according to the
     * Yate documetation at {@link http://yate.null.ro/docs/extmodule.html}
     *
     * @param string $message The encoded message
     * @return string The decoded message
     */
    protected function _decode($message)
    {
        $new_message = '';
        $decode_flag = false;
        
        $characters = str_split($message);
        foreach ($characters as $c) {
            if ('%' == $c) {
                $decode_flag = true;
                continue;
            }
            if (true == $decode_flag) {
                $new_message .= chr(ord($c)-64);
                $decode_flag = false;
            } else {
                $new_message .= $c;
            }
        }
        
        return $new_message;
    }

    /**
     * Encodes a Yate message representation,  according tot the
     * Yate documentations at {@link http://yate.null.ro/docs/extmodule.html}
     *
     * @param string $message The raw message
     * @return string The encoded message
     */
    protected function _encode($message)
    {
        $new_message = '';

        $characters = str_split($message);
        foreach ($characters as $c) {
            if (32 > ord($c) || ':' == $c) {
                $new_message .= chr(ord($c) + 64);
            } else {
                $new_message .= $c;
            }
        }

        return $new_message;
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
     * @return \Yate\Core
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
     * @return \Yate\Core
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
     * @return \Yate\Core
     */
    public function resetConnection()
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
     * @return \Yate\Core
     */
    public function setServer($address, $port)
    {
        $this->_address = $address;
        $this->_port = $port;
        return $this;
    }

    /**
     * Encode command and send it.
     *
     * The $request parameter is used to determine whether this message is
     * a request or a response. Use true when it is a request, or false
     * when it is a response.
     *
     * The command is an array with key/value pairs, which will be send to
     * the server.
     *
     * If the key is numeric, it is assumed not to be relevant, so only the
     * value will be sent. If the key is not numeric, it will be send als a
     * key=value pair.
     *
     * @param boolean $request True when a request, false when a response
     * @param array $command The command, in pieces.
     *
     * @return \Yate\Core
     */
    public function sendCommand($request = true, array $command)
    {
        $message = '';
        if ($request) {
            $message = '%%>';
        } else {
            $message = '%%<';
        }

        foreach($command as $key => $param) {
            $message .= ':';
            if (is_numeric($key)){
                $message .= $this->_encode($param);
            } else {
                $message .= $this->_encode("$key=$param");
            }
        }

        $message .= "\n";
        if (false === socket_write($this->_connection, $message)) {
            // @TODO: Another solutions may be better.
            throw new Exception("Error writing to socket!");
        }
        
        return $this;
    }
}
/* vi: set softtabstop=4 shiftwidth=4 expandtab: */
