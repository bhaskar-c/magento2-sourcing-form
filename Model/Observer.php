<?php

namespace Knowpapa\Sourcing\Model;
class Observer implements \Magento\Framework\Event\ObserverInterface{
public function execute(\Magento\Framework\Event\Observer $observer)
    {
        # check if user is logged in
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        if(!$customerSession->isLoggedIn())
        {
            $request = $objectManager->get('Magento\Framework\App\Request\Http');
            //get instance for URL interface
            /** @var \Magento\Framework\UrlInterface $urlInterface */
            $urlInterface = $objectManager->get('Magento\Framework\UrlInterface');
            // URL to redirect to
            $url = $urlInterface->getUrl('customer/account/login');
            if(strpos($request->getPathInfo(), '/customer/account/') !== 0)
            {
                # redirect to /customer/account/login
                $observer->getControllerAction()
                    ->getResponse()
                    ->setRedirect($url);
            }
        }
    }
}
