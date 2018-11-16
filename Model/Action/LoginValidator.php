<?php

namespace Knowpapa\Sourcing\Model\Action;

use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultFactory;

class LoginValidator
{
    protected $resultFactory;
    protected $session;

    /**
     * @param ResultFactory $resultFactory
     * @param Session $session
     */
    public function __construct(
        ResultFactory $resultFactory,
        Session $session
    ) {
        $this->resultFactory = $resultFactory;
        $this->session = $session;
    }

    /**
     * Get validator result
     * @param RequestInterface $request
     * @return \Magento\Framework\Controller\Result\Redirect|null
     */
    public function getResult(RequestInterface $request)
    {
        $result = null;
        if (!$this->session->isLoggedIn()) {
            /** @var $result \Magento\Framework\Controller\Result\Redirect */
            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $result->setRefererUrl();
        }
        return $result;
    }
}
