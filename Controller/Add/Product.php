<?php
namespace Raj\Cart\Controller\Add;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Product extends \Magento\Framework\App\Action\Action 
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
    protected $cartItem;

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
     * @param \Magento\Quote\Model\Quote\ItemFactory $cartItem
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $product 
     * @param \Magento\Checkout\Model\Session $customerSession 
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager            
     */
    public function __construct(Context $context, 
        ResultFactory $result, 
        \Magento\Quote\Api\CartRepositoryInterface $cart,
        \Magento\Quote\Model\Quote\ItemFactory $cartItem,
        //\Magento\Quote\Api\Data\CartItemInterfaceFactory $cartItem,
        \Magento\Catalog\Api\ProductRepositoryInterface $product, 
        \Magento\Checkout\Model\Session $customerSession, 
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPage
        )
    
    {
        parent::__construct($context);
        
      
        $this->storeManager = $storeManager;
        $this->product = $product;
        $this->cart = $cart;
        $this->cartItem = $cartItem;
        $this->customerSession = $customerSession;
        $this->result=$result;
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
            $quoteItem = $this->cartItem->create();
            $quoteItem->setProduct($product);
            $quoteItem->setQuote($quote);
            $quoteItem->setQty($qty);
            $quote->addItem($quoteItem);
            $quote->getBillingAddress();
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->collectTotals();
            $this->cart->save($quote);
            $this->customerSession->setQuoteId($quote->getId());
            $this->customerSession->setLastAddedProductId($productId);
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
