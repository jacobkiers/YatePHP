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
 * Represents a Yate message of type InstallResponse
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
class InstallResponse extends InstallAbstract
{
    
    /**
     * Flags whether the installation succeeded or not
     * 
     * @var boolean
     */
    protected $_success;
    
    /**
     * Turns this message into a string
     * 
     * @return string
     */
    public function __toString()
    {
        $string = '%%<install';
        $string .= ':' . $this->getPriority();
        $string .= ':' . $this->getName();
        $string .= ':' . ($this->isSuccessful()) ? 'true' : 'false';
        
        return $string;
    }

    /**
     * Creates an InstallResponse object
     * 
     * @param string $string
     * 
     * @return InstallResponse
     */
    public static function factory($string)
    {
        $msg_parts = explode(':', $string);
        
        $priority = $msg_parts[0];
        $name = $msg_parts[1];
        
        $message = new self($priority, $name);
        $message->setDirection(self::MSG_IN);
        $message->setSuccess(('true' == $msg_parts[2]));
        
        return $message;
    }
    
    /**
     * Returns the success status of the installation request
     * 
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->_success;
    }
    
    /**
     * Set the success status of the installation
     * 
     * @param boolean $success
     * @return InstallResponse 
     */
    public function setSuccess($success)
    {
        $this->_success = (boolean)$success;
        return $this;
    }
    
}
