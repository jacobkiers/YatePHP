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
 */

namespace Yate\Core;

/**
 * Represents a Yate message
 *
 * @category    Yate
 * @package     Yate_Core
 *
 * @copyright   Copyright (c) 2010 Jacob Kiers
 * @license     New BSD License
 *
 * @since       0.1
 */
abstract class AbstractMessage
{
    /**
     * The Yate command
     *
     * @var string
     */
    protected $_command = '';

    /**
     * Flag to determine the direction of this AbstractMessage
     *
     * If true,  this message is directed for Yate.
     * If false, this message is received from Yate.
     *
     * @var boolean
     */
    protected $_forYate = true;

    /**
     * Parameters of this message
     *
     * @var array
     */
    protected $_parameters = array();

    /**
     * Whether this is a request or a response
     *
     * Use true for request or false for response.
     *
     * @var boolean
     */
    protected $_request = true;

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
        if ($this->_request) {
            $message .= '%%>';
        } else {
            $message .= '%%<';
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
     * Creates a \Yate\Core\AbstractMessage object from a string.
     *
     * The given message should be a valid Yate message, as sent by Yate.
     *
     * @param string $string The encoded message from Yate
     * @return AbstractMessage
     */
    public static function createFromString($string)
    {
        $direction = '';
        $message = new self();
        
        // We assume this message is received from Yate.
        $message->setDirection(false);

        $string = trim($string);

        // Strip and store the direction (request or response) from the message.
        $direction = substr($string, 3, 1);
        $string = substr($string, 4);

        // Check whether this is a request or a response
        if ('<' == $direction) {
            $message->setRequest(false);
        }

        $string = explode(':', $string);

        // Set the command. It is the first part of the original message.
        $message->setCommand(array_shift($string));

        // Parse the parameters, and add them to the message
        foreach ($string as $key => $param) {
            $param = self::decode($param);
            $value = '';

            // If the parameter contains a '=' symbol, it is a key/value pair.
            if (strpos($param, '=')) {
                list($key, $value) = explode('=', $param);
            } else {
                $value = $param;
            }

            $message->addParameter(self::decode($key), $value);
        }

        // Create the message and return
        return $message;
    }

    /**
     * Decodes a Yate message representation, according to the
     * Yate documetation at {@link http://yate.null.ro/docs/extmodule.html}
     *
     * @param string $text The encoded message
     * @return string The decoded message
     */
    public static function decode($text)
    {
        $new_message = '';
        $decode_flag = false;

        $characters = str_split($text);
        foreach ($characters as $c) {
            if ('%' == $c) {
                $decode_flag = true;
                continue;
            }
            if (true == $decode_flag) {
                $new_message .= chr(ord($c) - 64);
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
     * @param string $text The raw message
     * @return string The encoded message
     */
    public static function encode($text)
    {
        $new_message = '';

        $characters = str_split($text);
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
     * Whether this message is for Yate, or is received from Yate
     *
     * If true,  this message is to be send to Yate.
     * If false, this message is received from Yate.
     *
     * @return boolean True when for Yate, false when received
     */
    public function isForYate()
    {
        return $this->_forYate;
    }

    /**
     * Whether this message is a request or a response
     *
     * @return boolean True when a request, false when a response
     */
    public function isRequest()
    {
        return $this->_request;
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

    /**
     * Set the direction of this message.
     *
     * If set to true,  this message is to be sent to Yate.
     * If set to false, this message is reveived from Yate.
     *
     * @param boolean $forYate
     * @return AbstractMessage
     */
    public function setDirection($forYate = true)
    {
        $this->_forYate = (boolean) $forYate;
        return $this;
    }

    /**
     * Tell this messages that it is a request or a response.
     *
     * If set to true,  this message is a request
     * If set to false, this message is a response
     *
     * @param boolean $value
     * @return AbstractMessage
     */
    public function setRequest($value = true)
    {
        $this->_request = (boolean) $value;
        return $this;
    }
}

/* vi: set softtabstop=4 shiftwidth=4 expandtab: */