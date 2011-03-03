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
 * @subpackage  Message
 */

namespace Yate\Core\Message;

/**
 * Represents a Yate message of type InstallRequest
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
class InstallRequest extends InstallAbstract
{
    /**
     * The optional name of the message filter
     * 
     * @var string
     */
    protected $_filterName = '';
    
    /**
     * The optional value of the message filter
     * 
     * @var string
     */
    protected $_filterValue = '';
    
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
        
        if ('' != $this->getFilterName()) {
            $string .= ':' . $this->getFilterName();
        }
        
        if ('' != $this->getFilterValue()) {
            $string .= ':' . $this->getFilterValue();
        }
        
        return $string;
    }

    /**
     * Creates an InstallRequest object
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
        $message->setDirection(self::MSG_OUT);
        
        if (isset($msg_parts[2])) {
            $this->setFilterName($msg_parts[2]);
        }
        
        if (isset($msg_parts[3])) {
            $this->setFilterValue($msg_parts[3]);
        }
        
        return $message;
    }

    /**
     * Returns the name of the filter
     * 
     * @return string
     */
    public function getFilterName()     {
        return $this->_filterName;
    }

    /**
     * Return the value of the filter
     * 
     * @return string
     */
    public function getFilterValue()
    {
        return $this->_filterValue;
    }

    /**
     * Set the name of the filter
     * 
     * @param string $filterName
     * 
     * @return InstallRequest 
     */
    public function setFilterName($filterName)
    {
        $this->_filterName = $filterName;
        return $this;
    }

    /**
     * Set the value of the filter
     * 
     * @param string $filterValue
     * 
     * @return InstallRequest 
     */
    public function setFilterValue($filterValue)
    {
        $this->_filterValue = $filterValue;
        return $this;
    }
    
}
