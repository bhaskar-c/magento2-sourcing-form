<?php

use Magento\Framework\Controller\ResultFactory;

class Formaction extends \Magento\Framework\App\Action\Action
{
    
    public function execute()
    {
        // 1. POST request : Get booking data
        $post = (array) $this->getRequest()->getPost();

        if (!empty($post)) {
            // Retrieve your form data
            $keywords    = $post['keywords'];
            $quantity    = $post['quantity'];
            $location       = $post['location'];
            $specification = $post['specification'];
            $captcha    = $post['captcha'];

            // Doing-something with...
            this.sendEmail();
  
            // Display the succes form validation message
            $this->messageManager->addSuccessMessage('We have received your request. We will get back !');

            // Redirect to your form page (or anywhere you want...)
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('/Knowpapa/Sourcing/index/index');

            return $resultRedirect;
        }
        // 2. GET request : Render the booking page 
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
    
    
    











    
}
