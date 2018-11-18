<?php
 
namespace Knowpapa\Sourcing\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Escaper;
use Knowpapa\Sourcing\Model\Mail\TransportBuilder;

    
class Index extends \Magento\Framework\App\Action\Action {
    protected $_resultPageFactory;
    protected $sessionFactory;
    protected $_transportBuilder;
    protected $_storeManager;
    protected $_escaper;
    
    protected $directoryList;
    protected $fileSystem;
    protected $allowedExtensions = array('jpg', 'jpeg', 'gif', 'png','pdf', 'docx', 'doc');
    protected $allFileUrls = []; 
    
    public function __construct(Context $context, PageFactory $resultPageFactory,
        TransportBuilder $transportBuilder, StoreManagerInterface $storeManager,
        Escaper $escaper, UploaderFactory $uploaderFactory, Filesystem $fileSystem,
        DirectoryList $directoryList
        )
      {
        $this->_resultPageFactory = $resultPageFactory;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->sessionFactory = $objectManager->get('Magento\Customer\Model\Session');
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_escaper = $escaper;
        
        $this->uploaderFactory = $uploaderFactory;
        $this->fileSystem = $fileSystem;
        $this->directoryList = $directoryList;
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
        
        try {
            $customerId = $this->sessionFactory->getCustomer()->getId();  
            $customerName = $this->sessionFactory->getCustomer()->getName() ?? 'Customer';
            $customerEmail = $this->sessionFactory->getCustomer()->getEmail();
            $attachmentCount = $post['attachmentCount'];
            $this->uploadAllFiles($attachmentCount);
            //$this->sendMail($post, $customerId,$customerEmail, $customerName,$attachmentCount);
            //$this->messageManager->addSuccessMessage(__('Thanks for requesting a quote. We\'ll get back to you very soon.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            echo($e);
            $this->messageManager->addErrorMessage(__('An error occurred while processing your form. Please try again later.'));
        }
     }
     $resultPage = $this->_resultPageFactory->create();
     $resultPage->getConfig()->getTitle()->set(__('Source products or solutions'));
     return $resultPage;

  }
    
    private function uploadAllFiles($attachmentCount){
      //https://secure.php.net/manual/en/features.file-upload.post-method.php
      for ($k = 1 ; $k <= $attachmentCount; $k++){ 
          $fileFormName = 'sourcingFileAttachment'.$k;
          $name = $_FILES[$fileFormName]['name'];
          $tmpName = $_FILES[$fileFormName]['tmp_name'];
          $file = $_FILES[$fileFormName];
          $path = pathinfo($name);
          $filename = $path['filename'];
          $ext = $path['extension'];
          //echo($name); // 'regex.pdf'
          //echo($file); // array
          //echo($path); // array
          //echo($filename); // 'regex' 
          //echo($ext); // 'pdf'
          //echo('****************\n\r');
          //die;
          $this->uploadSingleFile($name, $file, $path, $tmpName);
        }
     }       
            
    private function uploadSingleFile($name, $file, $path, $tmpName){
      $uploaddir = '/var/www/html/pub/media/sourcing';
      $uploadfile = $uploaddir . basename($name);
      $result = move_uploaded_file($tmpName, $uploadfile);
      if ($result) {
          $this->messageManager->addSuccessMessage(__("File is valid, and was successfully uploaded.\n"));
      } else {
        $this->messageManager->addErrorMessage(__("Possible file upload attack!\n"));
      }
    }
    
            
    private function getDestinationPath(){return $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('sourcing'); }        
            
    private function sendMail($post, $customerId, $customerEmail, $customerName, $attachmentCount){
        $store = $this->_storeManager->getStore()->getId();
        $post = (array) $this->getRequest()->getPost();
        $sender = [
            'name' => $this->_escaper->escapeHtml($customerName),
            'email' => $this->_escaper->escapeHtml($customerEmail),
        ];
        $temptransport = $this->_transportBuilder->setTemplateIdentifier('custom_mail_template')
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
            ->addTo('bha100710@gmail.com', 'Bhaskar');
              $transport = $temptransport->getTransport();            
        $transport->sendMessage();  
      }
    
    
} 
