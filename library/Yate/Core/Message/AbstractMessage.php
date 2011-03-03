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
 * Represents a Yate message
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
abstract class AbstractMessage
{
    /**
     * This message is incoming (from Yate)
     * @var string
     */
    const MSG_IN = 'in';
    
    /**
     * This message is outgoing (to Yate)
     * @var string
     */
    const MSG_OUT = 'out';
    
    /**
     * Flag to determine the direction of this Message
     *
     * The value should be one of AbstractMessage::MSG_IN or AbstractMessage::MSG_OUT
     *
     * @var boolean
     */
    protected $_direction;
    
    /**
     * Whether this is a request or a response
     *
     * Use true for request or false for response.
     *
     * @var boolean
     */
    protected $_request = true;

    /**
     * Creates a valid Yate string representation from this message
     * 
     * @return string
     */
    abstract public function __toString();
    
    /**
     * Determines the direction of the message
     * 
     * @param string $type
     * 
     * @return string
     */
    protected function _getDirection($type)
    {
        $direction = substr($type, 0, 3);
        $msg_type = substr($type, 4);
        
        if ($msg_type != 'message')
        {
            if ('%%>' == $direction) {
                return self::MSG_OUT;
            } else {
                return self::MSG_IN;
            }
        }
    }

    /**
     * Creates a Message object from a string.
     *
     * The given message should be a valid Yate message, as sent by Yate.
     *
     * @param string $string The encoded message from Yate
     * 
     * @return AbstractMessage|null A message when parseable or else null
     */
    public static function factory($string)
    {
        /* @var $new_message AbstractMessage */
        $new_message = null;
        $type = substr($string, 0, strpos($string, ':'));
        
        switch ($type) {
            case '%%>message':
            case '%%<message':
                $new_message = Message::factory($string);
                break;
            case '%%>install':
            case '%%<install':
                $new_message = InstallAbstract::factory($string);
                break;
            case '%%>uninstall':
            case '%%<install':
                break;
            case '%%>watch':
            case '%%<watch':
                break;
            case '%%>unwatch':
            case '%%<unwatch':
                break;
            case '%%>setlocal':
            case '%%<setlocal':
                break;
            case '%%>output':
                break;
            case '%%>connect':
                break;
            default:
                break;
        }

        return $new_message;
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
     * Returns the direction of the message.
     * 
     * @return string
     */
    public function getDirection()
    {
        return $this->_direction;
    }

    /**
     * Whether this message is for Yate, or is received from Yate
     *
     * @return boolean True when for Yate, false when received from Yate
     */
    public function isForYate()
    {
        return ($this->_direction == self::MSG_OUT);
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
     * Set the direction of this message.
     *
     * @param string $direction The direction
     * 
     * @return AbstractMessage
     * @throws Exception Throws an exception when we encounter an unknown direction.
     */
    public function setDirection($direction)
    {
        if ($direction != self::MSG_IN || $direction  != self::MSG_OUT) {
            throw new Exception('Please use either MSG_IN or MSG_OUT.');
        }
        $this->_direction = $direction;
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
        $this->_request = (boolean)$value;
        return $this;
    }
}

/* vi: set softtabstop=4 shiftwidth=4 expandtab: */