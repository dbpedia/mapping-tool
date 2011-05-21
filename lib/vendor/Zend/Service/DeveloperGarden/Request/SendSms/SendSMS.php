<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SendSMS.php 23772 2011-02-28 21:35:29Z ralph $
 */

/**
 * @see Zend_Service_DeveloperGarden_Request_SendSms_SendSmsAbstract
 */
require_once 'Zend/Service/DeveloperGarden/Request/SendSms/SendSmsAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_Request_SendSms_SendSMS
    extends Zend_Service_DeveloperGarden_Request_SendSms_SendSmsAbstract
{
    /**
     * this is the sms type
     * 1 = normal SMS
     *
     * @var integer
     */
    protected $_smsType = 1;
}