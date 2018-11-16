<?php
namespace Knowpapa\Sourcing\Block;
 
class Sourcing extends \Magento\Framework\View\Element\Template
{
    public function getHelloWorldTxt(){
      return 'Hello world!';
    }

    /**
     * Get form action URL for POST booking request
     *
     * @return string
     */
    public function getFormAction()
    {
    // companymodule is given in routes.xml
    // controller_name is folder name inside controller folder
    // action is php file name inside above controller_name folder
      return 'sourcing/index/index';
    // here controller_name is index, action is booking
    }
    
    


}
