<?php
declare(strict_types=1);
 
namespace Brainvire\ProductsGraphQl\Model\Resolver;
 
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
 
/**
 * Product collection resolver
 */
class ProductsResolver implements ResolverInterface
{
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }
 
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $productsData = $this->getProductsData();
        return $productsData;
    }
 
    /**
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getProductsData(): array
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', 1,'gteq')->create();
            //We have set logic for getting first all the products from ProductRepositoryInterface and apply the custom condition for getting all the products in which ID is equal or greater than 1.
            $products = $this->productRepository->getList($searchCriteria)->getItems();
            $productRecord['allProducts'] = [];
            foreach($products as $product) {
                $productId = $product->getId();
                $productRecord['allProducts'][$productId]['sku'] = $product->getSku();
                $productRecord['allProducts'][$productId]['name'] = $product->getName();
                $productRecord['allProducts'][$productId]['price'] = $product->getPrice();
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $productRecord;
    }
}
