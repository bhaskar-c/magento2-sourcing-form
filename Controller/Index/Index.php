<?php
 
namespace Knowpapa\Sourcing\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface; 
use Magento\Framework\App\RequestInterface;
//use Knowpapa\Sourcing\Model\Action\LoginValidator;
use Magento\Framework\Controller\ResultFactory;
    
class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $sessionFactory;
    private $dataPersistor;
    protected $_transportBuilder;
    protected $_storeManager;
    protected $_escaper;
        
    
    public function __construct(Context $context, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Escaper $escaper
        
        )
      {
        $this->_resultPageFactory = $resultPageFactory;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->sessionFactory = $objectManager->get('Magento\Customer\Model\Session');
        $this->dataPersistor = $dataPersistor;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_escaper = $escaper;
        parent::__construct($context);
    }
 
    public function execute(){
      if(!$this->sessionFactory->isLoggedIn()) {
         $this->messageManager->addNotice(__('You must be logged in to post a sourcing request.'));
         $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
         $result->setPath('customer/account/login/',['redirect' => 'sourcing']);
         return $result;  
        }

      $post = (array) $this->getRequest()->getPost();  
      if (!empty($post)) {
        $this->messageManager->addSuccessMessage(__('Thanks for requesting a quote. We\'ll get back to you very soon.'));
      try {
          $customerId = $this->sessionFactory->getCustomer()->getId();  
          $customerName = $this->sessionFactory->getCustomer()->getName() ?? 'Customer';
          $customerEmail = $this->sessionFactory->getCustomer()->getEmail();
          $this->sendMail($post, $customerId,$customerEmail, $customerName);
          
          $this->dataPersistor->clear('sourcing_data');
      } catch (LocalizedException $e) {
          $this->messageManager->addErrorMessage($e->getMessage());
          $this->dataPersistor->set('sourcing_data', $this->getRequest()->getParams());
      } catch (\Exception $e) {
          //echo($e);
          //$this->messageManager->addErrorMessage(__('An error occurred while processing your form. Please try again later.'));
          $this->dataPersistor->set('sourcing_data', $this->getRequest()->getParams());
      }
     }
      $resultPage = $this->_resultPageFactory->create();
      $resultPage->getConfig()->getTitle()->set(__('Source products or solutions'));
      return $resultPage;

  }
    
    
    private function sendMail($post, $customerId, $customerEmail, $customerName){
        $store = $this->_storeManager->getStore()->getId();
        $post = (array) $this->getRequest()->getPost();
        $sender = [
            'name' => $this->_escaper->escapeHtml($customerName),
            'email' => $this->_escaper->escapeHtml($customerEmail),
        ];
        $transport = $this->_transportBuilder->setTemplateIdentifier('custom_mail_template')
            ->setTemplateOptions(['area' => 'frontend', 'store' => $store])
            ->setTemplateVars(
                [
                    'customerEmail' => $customerEmail,
                    'customeId' => $customerId,
                    'customeName' => $customerName,
                    'store' => $this->_storeManager->getStore(),
                    'keywords'    => $post['keywords'],
                    'quantity'    => $post['quantity'],
                    'location'    =>$post['location'],
                    'specification' => $post['specification']
                    
                ]
            )
            ->setFrom($sender)
            ->addTo('bha100710@gmail.com', 'Bhaskar')
            ->getTransport();
        $transport->sendMessage();  
        
      
      }
    
    
} 
