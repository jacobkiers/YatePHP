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
 * @subpackage  Message
 */

namespace Yate\Core\Message;

/**
 * Represents a Yate message of type 'Message'
 *
 * @category    Yate
 * @package     Yate_Core
 * @subpackage  Message
 *
 * @copyright   Copyright (c) 2010 Jacob Kiers
 * @license     New BSD License
 *
 * @since       0.1
 */
class Message extends AbstractMessage
{

    /**
     * Creation time of this message
     * 
     * @var integer
     */
    protected $_created;
    
    /**
     * The Yate command
     *
     * @var string
     */
    protected $_command = '';
    
    /**
     * A unique message ID
     * 
     * @var string
     */
    protected $_id;
    
    /**
     * Flag for the processed status
     * 
     * @var boolean
     */
    protected $_processed = false;
    
    /**
     * The return value of this message
     * 
     * @var string
     */
    protected $_returnValue;
    
    /**
     * Parameters of this message
     *
     * @var array
     */
    protected $_parameters = array();

    /**
     * Magic method to get a parameter from this message
     * 
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->_parameters[$name])) {
            return $this->_parameters[$name];
        }
    }

    /**
     * Magic method to add a parameter to this message
     *
     * @param string $name  The name of the parameter
     * @param string $value The value of the parameter
     * @return void
     */
    public function __set($name, $value)
    {
        $this->_parameters[$name] = $value;
    }

    /**
     * Creates a valid Yate string representation from this message
     * 
     * @return string
     */
    public function __toString()
    {
        $message = '';
        if ($this->getDirection() == self::MSG_IN) {
            $message .= '%%>message:';
        } else {
            $message .= '%%<message:';
        }

        $message = $this->_command;

        foreach ($this->_parameters as $key => $value) {
            $message .= ':';
            if (is_numeric($key)) {
                $message .= self::encode($value);
            } else {
                $message .= self::encode("$key=$value");
            }
        }

        $message .= "\n";

        return $message;
    }

    /**
     * Add a parameter to this message
     *
     * @param string $key   The name of the parameter
     * @param string $value The value of the parameter
     * @return AbstractMessage
     */
    public function addParameter($key, $value)
    {
        $this->_parameters[$key] = $value;
        return $this;
    }

    /**
     * Creates a message object from a string.
     * 
     * @param string $message The string representation of a message
     * 
     * @return Message
     * @throws Exception Throws an exception when the string is not a message
     */
    public static function factory($message)
    {
        /* @var $new_message Message */
        $new_message = null;
        $msg_parts = explode(':', $message);
        $type = $msg_parts[0];

        if ('message' != substr($type, 4)) {
            throw new Exception("Message is not of type 'message'!");
        }

        $new_message = new Message();
        $new_message->setId(self::decode($msg_parts[1]));

        $direction = null;
        if ('%%>' == substr($type, 0, 3)) {
            $direction = 'in';
        } else {
            $direction = 'out';
        }
        
        if (self::MSG_IN == $direction) {
            $new_message->setDirection(false);
            $new_message->setTime($msg_parts[2]);
        } else {
            $new_message->setDirection(true);
            if ('true' == $msg_parts[2]) {
                $new_message->setProcessed(true);
            } else {
                $new_message->setProcessed(false);
            }
        }

        $new_message->setCommand(self::decode($msg_parts[3]));
        $new_message->setReturnValue(self::decode($msg_parts[4]));
        
        $numparts = count($msg_parts);
        for ($i = 5; $i < $numparts; $i++) {
            $part = explode('=', $msg_parts[$i]);
            $key = self::decode($part[0]);
            $value = self::decode($part[1]);

            $new_message->addParameter($key, $value);
        }

        return $new_message;
    }

    /**
     * Return the unique id of this Message (if set)
     *
     * @return string|null String if the id is set, null otherwise
     */
    public function getId()
    {
        return $this->_id;
    }

    public function getTime()
    {
        return $this->_created;
    }

    public function getReturnValue()
    {
        return $this->_returnValue;
    }

    public function isProcessed()
    {
        return $this->_processed;
    }

    /**
     * Set the command
     *
     * @param string $command
     * @return AbstractMessage
     */
    public function setCommand($command)
    {
        $this->_command = $command;
        return $this;
    }

    public function setId($id)
    {
        $this->_id = (string)$id;
    }

    public function setProcessed($boolean)
    {
        $this->_processed = (boolean)$boolean;
    }

    public function setTime($time)
    {
        $this->_created = (integer)$time;
    }

    public function setReturnValue($value)
    {
        $this->_returnValue = (string)$value;
    }

}

/* vi: set softtabstop=4 shiftwidth=4 expandtab: */