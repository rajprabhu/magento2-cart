<?php
namespace Raj\Cart\Controller\Add;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Product extends \Magento\Customer\Controller\AbstractAccount
{


    
    /**
     *
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $result;
    
    /**
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cart;

    /**
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $product;
    
    /**
     *
     * @var Session
     */
    protected $customerSession;
    
    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterfac
     */
    protected $storeManager;



    /**
     *
     * @param Context $context            
     * @param ResultFactory $result
     * @param \Magento\Quote\Api\CartRepositoryInterface $cart
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $product 
     * @param \Magento\Checkout\Model\Session $customerSession 
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager            
     */
    public function __construct(Context $context, 
        ResultFactory $result, 
        \Magento\Quote\Api\CartRepositoryInterface $cart,
        \Magento\Catalog\Api\ProductRepositoryInterface $product, 
        \Magento\Checkout\Model\Session $customerSession, 
        \Magento\Store\Model\StoreManagerInterface $storeManager
        
        )
    
    {
        parent::__construct($context);
        
        $this->result = $result;
        $this->storeManager = $storeManager;
        $this->product = $product;
        $this->cart = $cart;
        $this->customerSession = $customerSession;
    }

    /**
     * Add product to shopping cart
     * 
     * @return \Magento\Framework\Controller\Result
     */
    public function execute()
    {
        
        
        try {
            
            $productId = (int) $this->getRequest()->getParam('id');
            $qty=1;
            $storeId = $this->storeManager->getStore()->getId();
            $quote = $this->customerSession->getQuote();
            $product = $this->product->getById($productId, false, $storeId, true);
            $quote->addProduct($product, $qty);
            $this->cart->save($quote);
            $this->messageManager->addSuccess(__('%1 is added in your cart', $product->getName()));
        } 

        catch (\Exception $e) {
            
            $this->messageManager->addError(__('We can\'t add this item to your shopping cart right now.'));
        }
        
        $resultRedirect = $this->result->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('checkout/cart/');
        return $resultRedirect;
    }
}
