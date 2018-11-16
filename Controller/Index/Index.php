<?php
 
namespace Knowpapa\Sourcing\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface; 
class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $sessionFactory;
    private $dataPersistor;
 
    public function __construct(Context $context, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        DataPersistorInterface $dataPersistor
        )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->sessionFactory = $objectManager->create('Magento\Customer\Model\Session');
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }
 
    public function execute(){
    $post = (array) $this->getRequest()->getPost();  
    if (!empty($post)) {
    try {
          if (!$this->sessionFactory->isLoggedIn()) {
        $this->messageManager->addSuccessMessage(
            __('Only logged in customers can put sourcing request. Please login and try again')
        );
        //return $this->resultRedirectFactory->create()->setPath('*/*/');
        }  
        $sessionModel = $this->sessionFactory->create();
        $customerId = $sessionModel->getCustomer()->getId();  
        $customerName = $sessionModel->getCustomer()->getName() ?? 'Customer';
        $customerEmail = $sessionModel->getCustomer()->getEmail();
        $this->sendEmail($this->validatedParams(), $customerId,$customerEmail, $customerName);
        $this->messageManager->addSuccessMessage(
            __('Thanks for requesting a quote. We\'ll respond to you very soon.')
        );
        $this->dataPersistor->clear('sourcing_data');
    } catch (LocalizedException $e) {
        $this->messageManager->addErrorMessage($e->getMessage());
        $this->dataPersistor->set('sourcing_data', $this->getRequest()->getParams());
    } catch (\Exception $e) {
        $this->messageManager->addErrorMessage(
            __('An error occurred while processing your form. Please try again later.')
        );
        $this->dataPersistor->set('sourcing_data', $this->getRequest()->getParams());
    }
   }
    $resultPage = $this->_resultPageFactory->create();
    $resultPage->getConfig()->getTitle()->set(__('Source products or solutions'));
    return $resultPage;

  }
    
    
  private function sendEmail($post, $customerId, $customerEmail, $customerName){
      $post = (array) $this->getRequest()->getPost();
      $keywords    = $post['keywords'];
      $quantity    = $post['quantity'];
      $location       = $post['location'];
      $specification = $post['specification'];
      
      $body1 = "Customer Id: " .$customerId ."\r\n";
      $body2 = "Keywords: " .$keywords ."\r\n";
      $body3 = "Qunatity " .$quantity ."\r\n";
      $body4 = "Location: " .$location ."\r\n";
      $body5 = "Specification: " .$specification ."\r\n";
      $body = $body1.' '.$body2.' '.$body3.' '.$body4.' '.$body5.' ' ;
 
      $email = $customerEmail;
      $name = $customerName;
      $mail = new Zend_Mail();
      $mail->setBodyText($body);
      $mail->setFrom($email, $name);
      $mail->addTo('admin@hcx.global');
      $mail->setSubject('Sourcing Enquiry');
      try {
      $mail->send();
          }
          catch(Exception $ex) {
      Mage::getSingleton('core/session')->addError('Unable to send email. Please check that the details you have entered are correct.');
          }
    }
    
    
} 
