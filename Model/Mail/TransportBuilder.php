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
    public function addAttachment(
        $body,
        $filename,
        $mimeType,
        $disposition = \Zend_Mime::MULTIPART_MIXED,
        $encoding    = \Zend_Mime::ENCODING_BASE64
    ) {
        $this->message->createAttachment($body, $mimeType, $disposition, $encoding, $filename);
        $this->hasAttachments= 'true' ;
        return $this;
    }

}
