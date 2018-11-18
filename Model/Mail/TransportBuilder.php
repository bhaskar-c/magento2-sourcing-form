<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_EmailDemo
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 
namespace Knowpapa\Sourcing\Model\Mail;
 
class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * @param Api\AttachmentInterface $attachment
     */
    public function addAttachment($file)
    {
        $at = $this->message->createAttachment($file);
        $at->type        = 'image/jpeg';
        $at->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT; // or 
        //$at->encoding    = \Zend_Mime::ENCODING_BASE64;
        $at->filename    = 'test.pdf';
       return $this;

    }

}
