<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model;

use Aheadworks\OneStepCheckout\Api\Data\CheckoutSectionsDetailsInterface;
use Aheadworks\OneStepCheckout\Api\Data\CheckoutSectionsDetailsInterfaceFactory;
use Aheadworks\OneStepCheckout\Api\Data\CheckoutSectionInformationInterface;
use Aheadworks\OneStepCheckout\Api\CheckoutSectionsManagementInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

/**
 * todo: consider decomposition
 * Class CheckoutSectionsManagement
 * @package Aheadworks\OneStepCheckout\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckoutSectionsManagement implements CheckoutSectionsManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var CartTotalRepositoryInterface
     */
    private $totalsRepository;

    /**
     * @var ShipmentEstimationInterface
     */
    private $shipmentEstimation;

    /**
     * @var ShippingMethodManagementInterface
     */
    private $shippingMethodManagement;

    /**
     * @var ShippingInformationInterfaceFactory
     */
    private $shippingInformationFactory;

    /**
     * @var ShippingInformationManagementInterface
     */
    private $shippingInformationManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CheckoutSectionsDetailsInterfaceFactory
     */
    private $sectionsDetailsFactory;

    /**
     * @var Config
     */
    private $config;
	
	private $scopeConfig;
    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param CartTotalRepositoryInterface $totalsRepository
     * @param ShipmentEstimationInterface $shipmentEstimation
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param ShippingInformationInterfaceFactory $shippingInformationFactory
     * @param LoggerInterface $logger
     * @param CheckoutSectionsDetailsInterfaceFactory $sectionsDetailsFactory
     * @param Config $config
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        PaymentMethodManagementInterface $paymentMethodManagement,
        CartTotalRepositoryInterface $totalsRepository,
        ShipmentEstimationInterface $shipmentEstimation,
        ShippingMethodManagementInterface $shippingMethodManagement,
        ShippingInformationManagementInterface $shippingInformationManagement,
        ShippingInformationInterfaceFactory $shippingInformationFactory,
        LoggerInterface $logger,
        CheckoutSectionsDetailsInterfaceFactory $sectionsDetailsFactory,
        Config $config,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->totalsRepository = $totalsRepository;
        $this->shipmentEstimation = $shipmentEstimation;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformationFactory = $shippingInformationFactory;
        $this->logger = $logger;
        $this->sectionsDetailsFactory = $sectionsDetailsFactory;
        $this->config = $config;
		$this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionsDetails(
        $cartId,
        $sections,
        AddressInterface $shippingAddress,
        AddressInterface $billingAddress = null
    ) {
        /** @var CheckoutSectionsDetailsInterface $sectionsDetails */
        $sectionsDetails = $this->sectionsDetailsFactory->create();
		$totals = $this->getTotals($cartId);
        $sectionCodes = $this->getSectionCodes($sections);
        if (in_array('shippingMethods', $sectionCodes)) {
            $shippingMethods = $this->getShippingMethods($cartId, $shippingAddress);
			$subtotal = (float) $totals->getSubTotal();
			if(count($shippingMethods) > 0) {
				$i = 0;
				foreach($shippingMethods as $shippingMethod) {
					$carrier_code = $shippingMethod->getCarrierCode();
					if($carrier_code == 'freeshipping'){
						$minorderamount = (float) $this->scopeConfig->getValue('carriers/freeshipping/free_shipping_subtotal', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

						if($subtotal < $minorderamount) {
							unset($shippingMethods[$i]);
						}
					}
					$i++;
				}
			}

            $methodToSet = $this->resolveShippingMethod(
                $shippingMethods,
                $this->getShippingMethod($cartId),
                $this->config->getDefaultShippingMethod()
            );
            if ($methodToSet) {
                $this->saveShippingInformation(
                    $cartId,
                    $methodToSet,
                    $shippingAddress,
                    $billingAddress
                );
            }
            $sectionsDetails->setShippingMethods($shippingMethods);
        }
        if (in_array('paymentMethods', $sectionCodes)) {
            $sectionsDetails->setPaymentMethods(
                $this->getPaymentMethods($cartId, $shippingAddress, $billingAddress)
            );
        }
        if (in_array('totals', $sectionCodes)) {
            $sectionsDetails->setTotals($totals);
        }

        return $sectionsDetails;
    }

    /**
     * Get shipping methods
     *
     * @param int $cartId
     * @param AddressInterface $shippingAddress
     * @return ShippingMethodInterface[]
     */
    private function getShippingMethods($cartId, AddressInterface $shippingAddress)
    {
        if ($shippingAddress->getCustomerAddressId()) {
            $shippingMethods = $this->shippingMethodManagement->estimateByAddressId(
                $cartId,
                $shippingAddress->getCustomerAddressId()
            );
        } else {
            $shippingMethods = $this->shipmentEstimation->estimateByExtendedAddress($cartId, $shippingAddress);
        }
        return $shippingMethods;
    }

    /**
     * @param int $cartId
     * @param string $method
     * @param AddressInterface $shippingAddress
     * @param AddressInterface|null $billingAddress
     * @return void
     */
    private function saveShippingInformation(
        $cartId,
        $method,
        AddressInterface $shippingAddress,
        AddressInterface $billingAddress = null
    ) {
        $methodComponents = explode('_', $method);
        $carrierCode = array_shift($methodComponents);
        $methodCode = implode('_', $methodComponents);

        /** @var ShippingInformationInterface $shippingInformation */
        $shippingInformation = $this->shippingInformationFactory->create();
        $shippingInformation
            ->setShippingAddress($shippingAddress)
            ->setShippingCarrierCode($carrierCode)
            ->setShippingMethodCode($methodCode);
        if ($billingAddress) {
            $shippingInformation->setBillingAddress($billingAddress);
        }
        $this->shippingInformationManagement->saveAddressInformation($cartId, $shippingInformation);
    }

    /**
     * Get selected shipping method for cart
     *
     * @param int $cartId
     * @return string|null
     */
    private function getShippingMethod($cartId)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension) {
            /** @var \Magento\Quote\Api\Data\ShippingAssignmentInterface[] $shippingAssignments */
            $shippingAssignments = $cartExtension->getShippingAssignments();
            if ($shippingAssignments) {
                $shippingAssignment = $shippingAssignments[0];
                $shipping = $shippingAssignment->getShipping();
                if ($shipping) {
                    return $shipping->getMethod();
                }
            }
        }
        return null;
    }

    /**
     * Check if shipping method presented in the list
     *
     * @param string $method
     * @param ShippingMethodInterface[] $shippingMethods
     * @return bool
     */
    private function hasShippingMethod($method, $shippingMethods)
    {
        foreach ($shippingMethods as $shippingMethod) {
            if ($method == $shippingMethod->getCarrierCode() . '_' . $shippingMethod->getMethodCode()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Resolve shipping method to save
     *
     * @param ShippingMethodInterface[] $allMethods
     * @param string $currentMethod
     * @param string $defaultMethod
     * @return string|null
     */
    private function resolveShippingMethod($allMethods, $currentMethod, $defaultMethod)
    {
        $singleMethod = count($allMethods) == 1
            ? $allMethods[0]->getCarrierCode() . '_' . $allMethods[0]->getMethodCode()
            : null;
        if ($currentMethod) {
            if (!$this->hasShippingMethod($currentMethod, $allMethods)
                && $defaultMethod
                && $this->hasShippingMethod($defaultMethod, $allMethods)
            ) {
                return $defaultMethod;
            } elseif ($singleMethod && $singleMethod != $currentMethod) {
                return $singleMethod;
            }
        } elseif ($defaultMethod && $this->hasShippingMethod($defaultMethod, $allMethods)) {
            return $defaultMethod;
        }
        if (!$currentMethod && $singleMethod) {
            return $singleMethod;
        }
        return null;
    }

    /**
     * Get payment methods
     *
     * @param int $cartId
     * @param AddressInterface $shippingAddress
     * @param AddressInterface|null $billingAddress
     * @return PaymentMethodInterface[]
     * @throws InputException
     */
    private function getPaymentMethods(
        $cartId,
        AddressInterface $shippingAddress,
        AddressInterface $billingAddress = null
    ) {
        if (!$shippingAddress->getCustomerAddressId()) {
            $shippingAddress->setCustomerAddressId(null);
        }

        if (!$shippingAddress->getCountryId()) {
            return [];
        }

        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $quote
            ->setIsMultiShipping(false)
            ->setShippingAddress($shippingAddress);
        if ($billingAddress) {
            if (!$billingAddress->getCustomerAddressId()) {
                $billingAddress->setCustomerAddressId(null);
            }
            $quote->setBillingAddress($billingAddress);
        }

        try {
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(__('Unable to retrieve payment methods. Please check input data.'));
        }

        return $this->paymentMethodManagement->getList($cartId);
    }

    /**
     * Get totals
     *
     * @param int $cartId
     * @return TotalsInterface
     */
    private function getTotals($cartId)
    {
        return $this->totalsRepository->get($cartId);
    }

    /**
     * Get section codes
     *
     * @param CheckoutSectionInformationInterface[] $sections
     * @return array
     */
    private function getSectionCodes($sections)
    {
        $codes = [];
        foreach ($sections as $section) {
            $codes[] = $section->getCode();
        }
        return $codes;
    }
}
