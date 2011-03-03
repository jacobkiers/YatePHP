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
 * Represents a Yate message of type Install
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
abstract class InstallAbstract extends AbstractMessage
{
    /**
     * The name of the message for which the handler is installed
     * 
     * @var string
     */
    protected $_name;
    
    /**
     * The priority of the message handler
     * 
     * @var integer
     */
    protected $_priority;
    
    protected function __construct($priority, $name)
    {
        $this->setPriority($priority);
        $this->setName($name);
    }
    
    public static function factory($string)
    {
        $new_message = null;
        
        $direction = substr($string, 0, 3);
        $msg = substr($string, strpos($string, ':')+1);
        
        if (self::MSG_OUT == $this->_getDirection($direction)) {
            $new_message = InstallRequest::factory($msg);
        } else {
            $new_message = InstallResponse::factory($msg);
        }
        
        return $new_message;
    }
    
    /**
     * Returns the name of the messages which should be handled
     * 
     * @return string
     */
    public function getName()     {
        return $this->_name;
    }

    /**
     * Returns the priority of the message handler
     * 
     * @return integer
     */
    public function getPriority()
    {
        return $this->_priority;
    }

    /**
     * Set the name of the messages which should be handled
     * 
     * @param string $name
     * 
     * @return InstallAbstract 
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * Set the priority for this message handler
     * 
     * @param type $priority
     * 
     * @return InstallAbstract 
     */
    public function setPriority($priority)
    {
        $this->_priority = $priority;
        return $this;
    }
    
}