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
        $this->messageManager->addSuccessMessage(__('Thanks for requesting a quote. We\'ll get back to you very soon.'));
        try {
            $customerId = $this->sessionFactory->getCustomer()->getId();  
            $customerName = $this->sessionFactory->getCustomer()->getName() ?? 'Customer';
            $customerEmail = $this->sessionFactory->getCustomer()->getEmail();
            $attachmentCount = $post['attachmentCount'];
            $this->uploadAllFiles($attachmentCount);
            //$this->sendMail($post, $customerId,$customerEmail, $customerName,$attachmentCount);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('sourcing_data', $this->getRequest()->getParams());
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
      for ($k = 1 ; $k <= $attachmentCount; $k++){ 
          $fileFormName = 'sourcingFileAttachment'.$k;
          $name = $_FILES[$fileFormName]['name'];
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
          $this->uploadSingleFile($name, $file, $path);
        }
     }       
            
    private function uploadSingleFile($name, $file, $path){
      $file['tmp_name'] = 'xxx';
      if (isset($name) && $name != "") {
         $destinationPath = $this->getDestinationPath();
         //$media = $this->fileSystem->getDirectoryWrite($this->directoryList::MEDIA);
         //$media->writeFile($name, $path);
         try {
            $uploader = $this->uploaderFactory->create(['fileId' => $path])
                          ->setAllowCreateFolders(true)
                          ->setAllowRenameFiles(true)
                          ->setAllowedExtensions($this->allowedExtensions)
                          ->setFilesDispersion(true);
            $fileData = $uploader->save($destinationPath);    
            if (!$fileData) {
              throw new LocalizedException(__('File cannot be saved to path: $1', $destinationPath) );
            }
            $fileName = $fileData['file'];
          } 
          catch (\Exception $e) {
            $message = $this->messageManager->addError(__($e->getMessage()));
          }

         
         
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
