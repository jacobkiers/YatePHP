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
 * The ConsoleConnection class provides a connection to Yate using
 * the default stdin, stdout and stderr file descriptors
 *
 * @category    Yate
 * @package     Yate_Core
 *
 * @copyright   Copyright (c) 2010 Jacob Kiers
 * @license     New BSD License
 *
 * @since       0.1
 */
class ConsoleConnection implements ConnectionInterface
{
    
    /**
     * Provides the incoming FD for audio data
     * 
     * @var resource
     */
    protected $_audioIn;
    
    /**
     * Provides the outgoing FD for audio data
     * 
     * @var resource
     */
    protected $_audioOut;
    
    /**
     * Provindes the stderr handle
     * 
     * @var resource
     */
    protected $_error;
    
    /**
     * Provides the stdin handle
     * 
     * @var resource
     */
    protected $_in;
    
    /**
     * Provides the stdout handle
     * 
     * @var resource
     */
    protected $_out;
    
    /**
     * Connect to the Yate server
     * 
     * @return  ConsoleConnection 
     * @throws  Exception
     */
    public function connect() {
        if(null === $this->_in) {
            $this->_in = fopen('php://stdin', 'r');
        }
        
        if (null === $this->_out) {
            $this->_out = fopen('php://stdout', 'w');
        }
        
        if (null === $this->_error) {
            $this->_error = fopen('php://stderr', 'w');
        }
        
        if (false === $this->_in || false === $this->_out || false === $this->_error) {
            $this->disconnect();
            throw new Exception('Error connecting to Yate!');
        }
        
        return $this;
    }

    /**
     * Disconnect from the Yate server
     * 
     * @return ConsoleConnection 
     */
    public function disconnect() {
        if (is_resource($this->_in)) {
            fclose($this->_in);
        }
        
        if (is_resource($this->_out)) {
            fclose($this->_out);
        }
        
        if (is_resource($this->_error)) {
            fclose($this->_error);
        }
        
        if (is_resource($this->_audioIn)) {
            fclose($this->_audioIn);
        }
        
        if (is_resource($this->_audioOut)) {
            fclose($this->_audioOut);
        }
        
        return $this;
    }

    /**
     * Receive a message from Yate.
     *
     * @return AbstractMessage|null A Message when available, else null
     * @throws Exception
     */
    public function receiveMessage()
    {
        // Read exactly one line, since Yate messages are separated
        // by a newline (ASCII code 10).
        // 
        // We only read one message at a time.
        $data = fgets($this->_in);
        
        if (false === $data) {
            throw new Exception("Error reading from Yate.");
        }

        if ('' == $data) {
            return null;
        }
        
        // Return a message from the factory
        return AbstractMessage::factory($data);
    }

    /**
     * Resets the connection to the Yate server
     * 
     * @return  ConsoleConnection
     * @throws  Exception
     */
    public function reconnect() {
        $this->disconnect();
        $this->connect();
    }

    /**
     * Sends a message to Yate
     * 
     * @param   AbstractMessage $message 
     * 
     * @return  ConsoleConnection
     * @throws  Exception
     */
    public function sendMessage(AbstractMessage $message) {
        // Send message
        $to_send = $message->__toString();

        // In a while loop, because not all bytes may be written at once.
        $total_length = strlen($to_send);
        $offset = 0;
        while ($offset < $total_length) {
            $msg = substr($to_send, $offset);
            $bytes_to_write = $total_length - $offset;
            $sent = fwrite($this->_out, $msg, $bytes_to_write);
            if ($sent === false) {
                // Error occurred, break the while loop 
                break;
            }
            $offset += $sent;
        }

        // Check for succesful delivery
        if ($offset < $total_length) {
            // @TODO: Another solution may be better.
            throw new Exception("Error writing to Yate!");
        }

        return $this;
    }

}
/* vi: set softtabstop=4 shiftwidth=4 expandtab: */
