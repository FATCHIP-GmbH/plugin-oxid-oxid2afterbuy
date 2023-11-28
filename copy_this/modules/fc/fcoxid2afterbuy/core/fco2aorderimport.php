<?php
class fco2aorderimport extends fco2abase {

    /**
     * Assignments to use payment matching
     * @var array
     */
    protected $_aPaymentAssignments = array(
        '1' => 'TRANSFER',
        '2' => 'CASH_PAID',
        '4' => 'CASH_ON_DELIVERY',
        '5' => 'PAYPAL',
        '6' => 'INVOICE_TRANSFER',
        '7' => 'DIRECT_DEBIT',
        '9' => 'CLICKANDBUY',
        '11' => 'EXPRESS_CREDITWORTHINESS',
        '12' => 'PAYNET',
        '13' => 'COD_CREDITWORTHINESS',
        '14' => 'EBAY_EXPRESS',
        '15' => 'MONEYBOOKERS',
        '16' => 'CREDIT_CARD_MB',
        '17' => 'DIRECT_DEBIT_MB',
        '18' => 'OTHERS',
        '19' => 'CREDIT_CARD',
    );

    /**
     * @var string
     */
    protected $_fcEbayPaymentType = 'ebay_payments';

    /**
     * @var string
     */
    protected $_fcEbayDeliveryType = 'oxidstandard';

    /**
     * @var string
     */
    protected $_fcEbayIpIdentifier = 'ebay';

    /**
     * Dont import orders, only output what orders WOULD be imported or which not
     *
     * @var bool
     */
    protected $_fcBlDryMode = false;

    /**
     * Central entry point for triggering order import
     *
     * @param void
     * @return void
     */
    public function execute() {
        $blAllowed = $this->fcJobExecutionAllowed('orderimport');
        if (!$blAllowed) {
            echo "Execution of orderimport is not allowed by configuration\n";
            exit(1);
        }

        $oAfterbuyApi = $this->_fcGetAfterbuyApi();
        $this->_fcSetFilter($oAfterbuyApi);
        $sResponse = $oAfterbuyApi->getSoldItemsFromAfterbuy();
        $oXmlResponse = simplexml_load_string($sResponse);
        $this->_fcParseApiResponse($oXmlResponse, $oAfterbuyApi);
    }

    protected function isOrderAlreadyExistingOldConnector($oXmlOrder)
    {
        $sTransactionId = (string)$oXmlOrder->SoldItems->SoldItem->eBayTransactionID;
        $sQuery = " SELECT 
                        a.oxid 
                    FROM 
                        oxorder AS a
                    INNER JOIN
                        mo_viaebay__order_details AS b ON a.MO_VIAEBAY__VIA_ID = b.MO_VIAEBAY__VIA_ID
                    WHERE
                        b.MO_VIAEBAY__ORDER_DETAILS LIKE '%TransactionId\";s:".strlen($sTransactionId).":\"".$sTransactionId."\"%'";

        $oDb = oxDb::getDb();
        $sOxid = $oDb->getOne($sQuery);
        if (!empty($sOxid)) {
            return true;
        }
        return false;
    }

    protected function isOrderAlreadyExisting($oXmlOrder)
    {
        $sOrderId = (string)$oXmlOrder->OrderID;
        $sQuery = " SELECT 
                        oxid 
                    FROM 
                        oxorder_afterbuy
                    WHERE
                        FCAFTERBUY_UID = '".$sOrderId."'";

        $oDb = oxDb::getDb();
        $sOxid = $oDb->getOne($sQuery);
        if (!empty($sOxid)) {
            return true;
        }
        return false;
    }

    /**
     * Check if order can be imported
     *
     * @param SimpleXMLElement $oXmlOrder
     * @return bool
     */
    protected function canImportOrder($oXmlOrder)
    {
        if ($this->isOrderAlreadyExistingOldConnector($oXmlOrder) === true) {
            return false;
        }
        if ($this->isOrderAlreadyExisting($oXmlOrder) === true) {
            return false;
        }
        return true;
    }

    protected function getOrderStringIdent($oXmlOrder)
    {
        $sIdent  = (string)$oXmlOrder->OrderID." ";
        $sIdent .= (string)$oXmlOrder->OrderDate." ";
        $sIdent .= (string)$oXmlOrder->BuyerInfo->BillingAddress->FirstName." ";
        $sIdent .= (string)$oXmlOrder->BuyerInfo->BillingAddress->LastName." ";
        $sIdent .= (string)$oXmlOrder->SoldItems->SoldItem->eBayTransactionID;
        return $sIdent;
    }

    /**
     * Checks and parses API result
     *
     * @param $oXmlResponse
     * @param $oAfterbuyApi
     * @return void
     */
    protected function _fcParseApiResponse($oXmlResponse, $oAfterbuyApi) {
        if (!isset($oXmlResponse->Result->Orders->Order)) {
            $this->oApiLogger->fcWriteLog('ERROR: No valid Response from API while trying to fetch new orders. Content of Response is'.print_r($oXmlResponse,true),1);
            return;
        }

        foreach ($oXmlResponse->Result->Orders->Order as $oXmlOrder) {
            if ($this->_fcBlDryMode === true) { // DRY MODE = only output which orders WOULD be imported and which not
                echo $this->getOrderStringIdent($oXmlOrder)." - Check Order".PHP_EOL;
                if ($this->isOrderAlreadyExistingOldConnector($oXmlOrder) === true) {
                    echo $this->getOrderStringIdent($oXmlOrder)." - Order existing through old connector - DONT IMPORT".PHP_EOL;
                } elseif ($this->isOrderAlreadyExisting($oXmlOrder) === true) {
                    echo $this->getOrderStringIdent($oXmlOrder)." - Order existing through new connector - DONT IMPORT".PHP_EOL;
                } else {
                    echo $this->getOrderStringIdent($oXmlOrder)." - Order not existing - import".PHP_EOL;
                }
            } else {
                if ($this->canImportOrder($oXmlOrder) === false) {
                    $this->oApiLogger->fcWriteLog("DEBUG: SKIP ORDER:\n".print_r($oXmlOrder,true), 4);
                    continue; // skip order if already exists or other factors are disqualifying it
                }

                $this->oApiLogger->fcWriteLog("DEBUG: oXmlOrder:\n".print_r($oXmlOrder,true), 4);
                $oAfterbuyOrder = $this->_fcGetAfterbuyOrder();
                $oAfterbuyOrder->createOrderByApiResponse($oXmlOrder);
                $this->oApiLogger->fcWriteLog("DEBUG: Created result in oAfterbuyOrder:\n".print_r($oAfterbuyOrder,true), 4);
                $this->_fcCreateOxidOrder($oAfterbuyOrder);
                $this->_fcNotifyExported($oAfterbuyOrder, $oAfterbuyApi);

                echo $this->getOrderStringIdent($oXmlOrder)." - imported order".PHP_EOL; // for debug purposes - can be removed if needed
            }
        }
    }

    /**
     * Sets different filters
     *
     * @param $oAfterbuyApi
     * @return void
     */
    protected function _fcSetFilter(&$oAfterbuyApi) {
        $iCurrentOrderId = $this->_fcGetCurrentOrderId();
        if ($iCurrentOrderId) {
            $oAfterbuyApi->setLastOrderId($iCurrentOrderId);
        }
    }

    /**
     * Returns last orderid that has been imported, if there is one
     *
     * @param void
     * @return int
     */
    protected function _fcGetCurrentOrderId() {
        $oCounter = oxNew('oxCounter');
        $sLastOrderId = $oCounter->fcGetCurrent('fcAfterbuyLastOrder');
        $iReturn = 0;
        if ($sLastOrderId) {
            $iReturn = (int) $sLastOrderId;
        }

        return $iReturn;
    }

    /**
     * Notify to afterbuy that order has been exported, save last orderid
     *
     * @param $oAfterbuyOrder
     * @param $oAfterbuyApi
     * @return void
     */
    protected function _fcNotifyExported($oAfterbuyOrder, $oAfterbuyApi) {
        // save last orderid
        $oCounter = oxNew('oxCounter');
        $iLastOrderId = (int) $oAfterbuyOrder->OrderID;
        $oCounter->update($this->_sCounterIdent, $iLastOrderId);

        // just get an orderstatus object, pass it through api, so afterbuy will notice that order arrived
        $oAfterbuyOrderStatus = $this->_fcGetAfterbuyStatus();
        $oAfterbuyOrderStatus->OrderID = (int) $oAfterbuyOrder->OrderID;
        $oAfterbuyApi->updateSoldItemsOrderState($oAfterbuyOrderStatus);
    }

    /**
     * Creates an oxid order including user and articles
     *
     * @param $oAfterbuyOrder
     * @return void
     */
    protected function _fcCreateOxidOrder($oAfterbuyOrder) {
        // create OXUSER
        $aUserData = $this->_fcSetOxidUserByAfterbuyOrder($oAfterbuyOrder);
        $oUser = $aUserData['oxuser'];
        $oAddress = $aUserData['oxaddress'];
        // create OXORDER
        $this->_fcSetOxidOrderByAfterbuyOrder($oAfterbuyOrder, $oUser, $oAddress);
    }

    /**
     * Create oxid order
     *
     * @param $oAfterbuyOrder
     * @param $oUser
     * @param $oAddress
     * @return void
     */
    protected function _fcSetOxidOrderByAfterbuyOrder($oAfterbuyOrder, $oUser, $oAddress) {
        $oOrder = oxNew('oxorder');

        $oOrder = $this->_fcGetOrderGeneralData($oOrder, $oUser, $oAfterbuyOrder);

        // billdata
        $oOrder = $this->_fcGetOrderBillData($oOrder, $oUser);
        // deliveryinfo
        $oOrder = $this->_fcGetOrderDeliveryData($oOrder, $oAddress);
        // paymentinfo
        $oOrder = $this->_fcGetPaymentInfo($oOrder, $oAfterbuyOrder);
        $oOrder = $this->_fcGetPaymentData($oOrder, $oAfterbuyOrder);
        // temporary save for getting an id
        $oOrder->save();

        // set folder
        $sFolder = $this->_fcGetAppropriateFolder($oAfterbuyOrder);
        $oOrder->oxorder__oxfolder = new oxField($sFolder, oxField::T_RAW);

        $oOrder->oxorder__oxtransstatus = new oxField("OK", oxField::T_RAW);

        // set orderarticles
        $oSumPrice = $this->_fcSetOxidOrderarticlesByAfterbuyOrder($oAfterbuyOrder, $oOrder);

        // cumulate sums
        $oOrder->oxorder__oxtotalbrutsum = new oxField($oSumPrice->getBruttoPrice(), oxField::T_RAW);
        $oOrder->oxorder__oxtotalnetsum = new oxField($oSumPrice->getNettoPrice(), oxField::T_RAW);
        $oOrder->oxorder__oxartvat1 = new oxField($oSumPrice->getVat(), oxField::T_RAW);
        $oOrder->oxorder__oxartvatprice1 = new oxField($oSumPrice->getVatValue(), oxField::T_RAW);

        // delivery date
        $sDeliveryDate = $this->_fcGetOxidDeliveryDate($oAfterbuyOrder);
        $oOrder->oxorder__oxsenddate = new oxField($sDeliveryDate, oxField::T_RAW);

        $oOrder->oxorder__oxdeltype = new oxField($this->_fcEbayDeliveryType, oxField::T_RAW); #0102749
        $oOrder->oxorder__tc_ebay_buyername = new oxField($oAfterbuyOrder->BuyerInfoBilling->UserIDPlattform, oxField::T_RAW); #0102749

        // shipping costs - always delivered as brut price from Afterbuy, ignore vat settings in oxid
        $dShippingCostsTotal = $this->_fcFetchAmount($oAfterbuyOrder->ShippingInfo->ShippingTotalCost);
        $dShippingVat = $this->_fcFetchAmount($oAfterbuyOrder->ShippingInfo->ShippingTaxRate);
        $oOrder->oxorder__oxdelcost = new oxField($dShippingCostsTotal, oxField::T_RAW);
        $oOrder->oxorder__oxdelvat = new oxField($dShippingVat, oxField::T_RAW);

        $oOrder->oxorder__oxisnettomode = new oxField((int)$this->_isPriceViewModeNetto(), oxField::T_RAW);

        $oOrder->save();
    }

    /**
     * Fetches delivery date in oxid-appropriate format
     *
     * @param $oAfterbuyOrder
     * @return string
     */
    protected function _fcGetOxidDeliveryDate($oAfterbuyOrder)
    {
        $oShippingInfo = $oAfterbuyOrder->ShippingInfo;
        $sRawDeliveryDate = (string) $oShippingInfo->DeliveryDate;
        if (empty($sRawDeliveryDate)) {
            return '0000-00-00 00:00:00';
        }

        $iOrderTime = strtotime($sRawDeliveryDate);
        $sOxidSendDate = date('Y-m-d H:i:s', $iOrderTime);

        return $sOxidSendDate;
    }

    /**
     * Returns matching folder string
     *
     * @param $oAfterbuyOrder
     * @return string
     * @todo: Currently just using oxstandard. Should be configurable for user
     */
    protected function _fcGetAppropriateFolder($oAfterbuyOrder)
    {
        $sOrderStatus = $this->_fcDetermineOrderStatus($oAfterbuyOrder);

        switch ($sOrderStatus) {
            case 'fulfilled':
                $sFolder = 'ORDERFOLDER_FINISHED';
                break;
            case 'problems':
                $sFolder = 'ORDERFOLDER_PROBLEMS';
                break;
            case 'paid':
            case 'sent':
            case 'new':
            default:
                // default is new
                $sFolder = 'ORDERFOLDER_NEW';
        }

        return $sFolder;
    }

    /**
     * Method will check certain indicators of afterbuyorder and return
     * an appropriate state as string.
     * These values can be either
     * - fulfilled => sent and paid
     * - paid => paid but not yet sent
     * - sent => sent but not yet paid
     * - problems => @todo: determine problems
     * - new => wether sent nor paid
     *
     * @param object $oAfterbuyOrder
     * @return string
     */
    protected function _fcDetermineOrderStatus($oAfterbuyOrder)
    {
        $oPaymentInfo = $oAfterbuyOrder->PaymentInfo;
        $oShippintInfo = $oAfterbuyOrder->ShippingInfo;
        $sPaidDate = (string) $oPaymentInfo->PaymentDate;
        $sShipDate = (string) $oShippintInfo->DeliveryDate;
        $blPaid = !empty($sPaidDate);
        $blShipped = !empty($sShipDate);
        $blFulfilled = ($blPaid && $blShipped);

        if ($blFulfilled)
            return 'fulfilled';

        if ($blShipped)
            return 'sent';

        if ($blPaid)
            return 'paid';

        /**
         * @todo implement problem determination here and early return on problems
         */

        return 'new';
    }

    /**
     * Loads afterbuy id from oxarticles_afterbuy table
     *
     * @param string $sAfterbuyId
     * @return mixed
     */
    protected function _fcGetProductIdByAfterbuyId($sAfterbuyId)
    {
        $oBaseImport = oxNew("fco2abaseimport");
        return $oBaseImport->getProductIdByAfterbuyId($sAfterbuyId);
    }

    /**
     * Returns true if view mode is netto
     *
     * @return bool
     */
    protected function _isPriceViewModeNetto()
    {
        return (bool) $this->getConfig()->getConfigParam('blShowNetPrice');
    }

    /**
     * Assign solditems values to orderarticles
     *
     * @todo implementing sets feature (ChildProduct)
     * @param $oAfterbuyOrder
     * @param $oOrder
     * @return oxPrice
     */
    protected function _fcSetOxidOrderarticlesByAfterbuyOrder($oAfterbuyOrder, $oOrder) {
        $oConfig = $this->getConfig();
        $dDefaultVat = $oConfig->getConfigParam('dDefaultVat');
        $sOrderId = $oOrder->getId();
        $aSoldItems = $oAfterbuyOrder->SoldItems;
        $oOrderArticleTemplate = oxNew('oxorderarticle');

        $oSumPrice = oxNew('oxPrice');
        $oSumPrice->setVat($dDefaultVat);
        $oSumPrice->setBruttoPriceMode();

        $this->oDefaultLogger->fcWriteLog('Importing orderarticles of order with id '.$sOrderId.': '.print_r($aSoldItems,true), 4);

        foreach ($aSoldItems as $oSoldItem) {
            $oOrderArticle = clone $oOrderArticleTemplate;
            $oProductDetails = $oSoldItem->ShopProductDetails;
            $sArtNum = $oProductDetails->EAN;
            $sProductId = $this->_fcGetProductIdByAfterbuyId($oProductDetails->ProductID);
            if (!$sProductId) {
                $sProductId = $this->_fcGetProductIdByArtNum($sArtNum);
            }
            $sVariant = $this->_fcGetVariationByOxid($sProductId);
            $sShortDesc = $this->_fcGetShortDescByOxid($sProductId);

            $iAmount = $oSoldItem->ItemQuantity;
            $dSinglePrice = $this->_fcFetchAmount($oSoldItem->ItemPrice);
            $dCompletePrice = round(($dSinglePrice * (double)$iAmount),2);
            $dVat = $this->_fcFetchAmount($oSoldItem->TaxRate);
            $oOrderArticlePrice = oxNew('oxPrice');
            $oOrderArticlePrice->setBruttoPriceMode();
            $oOrderArticlePrice->setPrice($dCompletePrice, $dVat);
            $oUnitPrice = oxNew('oxPrice');
            $oUnitPrice->setBruttoPriceMode();
            $oUnitPrice->setPrice($dSinglePrice, $dVat);
            if (!$oSumPrice->getVat()) {
                $oSumPrice->setVat($dVat);
            }
            $oSumPrice->addPrice($oOrderArticlePrice);

            $oOrderArticle->oxorderarticles__oxorderid = new oxField($sOrderId);
            $oOrderArticle->oxorderarticles__oxamount = new oxField($iAmount);
            $oOrderArticle->oxorderarticles__oxartid = new oxField($sProductId);
            $oOrderArticle->oxorderarticles__oxartnum = new oxField($sArtNum);
            $oOrderArticle->oxorderarticles__oxtitle = new oxField($oSoldItem->ItemTitle);
            $oOrderArticle->oxorderarticles__oxprice = new oxField($dCompletePrice);
            $oOrderArticle->oxorderarticles__oxbprice = new oxField($oUnitPrice->getBruttoPrice());
            $oOrderArticle->oxorderarticles__oxnprice = new oxField($oUnitPrice->getNettoPrice());
            $oOrderArticle->oxorderarticles__oxbrutprice = new oxField($oOrderArticlePrice->getBruttoPrice());
            $oOrderArticle->oxorderarticles__oxnetprice = new oxField($oOrderArticlePrice->getNettoPrice());
            $oOrderArticle->oxorderarticles__oxvat = new oxField($oOrderArticlePrice->getVat());
            $oOrderArticle->oxorderarticles__oxvatprice = new oxField($oOrderArticlePrice->getVatValue());
            $oOrderArticle->oxorderarticles__oxselvariant = new oxField($sVariant);
            $oOrderArticle->oxorderarticles__oxshortdesc = new oxField($sShortDesc);
            $oOrderArticle->oxorderarticles__oxsubclass = new oxField("oxarticle");
            $oOrderArticle->setIsNewOrderItem(true); // enables stock management
            $oOrderArticle->save();
        }

        return $oSumPrice;
    }

    /**
     * Returns product if of an article number
     *
     * @param $sArtNum
     * @return string
     */
    protected function _fcGetProductIdByArtNum($sArtNum) {
        $oDb = oxDb::getDb();
        $sQuery = "SELECT OXID FROM oxarticles WHERE OXARTNUM=".$oDb->quote($sArtNum)." LIMIT 1";
        $sOxid = $oDb->getOne($sQuery);

        return (string) $sOxid;
    }

    /**
     * Returns varselect of product by oxid
     *
     * @param $sOxid
     * @return string
     */
    protected function _fcGetVariationByOxid($sOxid) {
        $oDb = oxDb::getDb();
        $sQuery = "SELECT OXVARSELECT FROM oxarticles WHERE OXID = ".$oDb->quote($sOxid)." LIMIT 1";
        $sVariant = $oDb->getOne($sQuery);

        return (string)$sVariant;
    }

    /**
     * Returns shortdesc of product by oxid
     *
     * @param  string $sOxid
     * @return string
     */
    protected function _fcGetShortDescByOxid($sOxid) {
        $oDb = oxDb::getDb();
        $sQuery = "SELECT OXSHORTDESC FROM oxarticles WHERE OXID = ".$oDb->quote($sOxid)." LIMIT 1";
        $sShortdesc = $oDb->getOne($sQuery);

        return (string)$sShortdesc;
    }

    /**
     * Sets additional paymentdata into oxuserpayment and link them to order
     *
     * @param $oOrder
     * @param $oAfterbuyOrder
     * @return oxOrder
     */
    protected function _fcGetPaymentData($oOrder, $oAfterbuyOrder) {
        $oUserPayment = oxNew('oxuserpayment');
        $sUserId = $oOrder->oxorder__oxuserid->value;
        $sPaymentId = $oOrder->oxorder__oxpaymenttype->value;
        $oPaymentData = $oAfterbuyOrder->PaymentInfo->PaymentData;

        $aDynValues = array(
            'BankCode' => $oPaymentData->BankCode,
            'AccountHolder' => $oPaymentData->AccountHolder,
            'BankName' => $oPaymentData->BankName,
            'AccountNumber' => $oPaymentData->AccountNumber,
            'Iban' => $oPaymentData->Iban,
            'Bic' => $oPaymentData->Bic,
            'ReferenceNumber' => $oPaymentData->ReferenceNumber,
        );

        $oUserPayment->oxuserpayments__oxpaymentsid = new oxField($sPaymentId);
        $oUserPayment->oxuserpayments__oxuserid = new oxField($sUserId);
        $oUserPayment->setDynValues($aDynValues);
        $oUserPayment->save();

        $sPaymentsId = $oUserPayment->getId();
        $oOrder->oxorder__oxpaymentid = new oxField($sPaymentsId);

        return $oOrder;
    }

    /**
     * Adds payment information to order
     *
     * @param $oOrder
     * @param $oAfterbuyOrder
     * @return mixed
     */
    protected function _fcGetPaymentInfo($oOrder, $oAfterbuyOrder) {
        $oPaymentInfo = $oAfterbuyOrder->PaymentInfo;
        $sPaymentType = $this->_fcGetPaymentMethod($oPaymentInfo);
        $oOrder->oxorder__oxpaymenttype = new oxField($sPaymentType);
        if ($sPaymentType == $this->_fcEbayPaymentType) {
            $oOrder->oxorder__oxip = new oxField($this->_fcEbayIpIdentifier); #0102749
        }
        $oOrder->oxorder__oxtransid = new oxField($oPaymentInfo->PaymentTransactionID);
        if ($oPaymentInfo->AlreadyPaid) {
            $sPaymentDate = $this->_fcFetchPaymentDate($oPaymentInfo->PaymentDate);
            $oOrder->oxorder__oxpaid = new oxField($sPaymentDate);
        }

        $dTotalSum = $this->_fcFetchAmount($oPaymentInfo->FullAmount);
        $oOrder->oxorder__oxtotalordersum = new oxField($dTotalSum, oxField::T_RAW);

        return $oOrder;
    }

    /**
     * Returns a float value of incoming comma value
     *
     * @param $sAmount
     * @return float
     */
    protected function _fcFetchAmount($sAmount) {
        $dAmount = (double) str_replace(',', '.', $sAmount);

        return $dAmount;
    }

    /**
     * Returns date which matches the format of db
     *
     * @param $sPaymentDateIn
     * @return string
     */
    protected function _fcFetchPaymentDate($sPaymentDateIn) {
        $iTime = strtotime($sPaymentDateIn);
        if (!$sPaymentDateIn) {
            $sPaymentDateOut = '0000-00-00 00:00:00';
        } else {
            $sPaymentDateOut = date('Y-m-d',$iTime);
        }

        return $sPaymentDateOut;
    }

    /**
     * Checks if payment method exists, creates payment if needed and returns its paymenttype
     * string for assigning to order
     *
     * @param $oPaymentInfo
     * @return string
     */
    protected function _fcGetPaymentMethod($oPaymentInfo) {
        $sPaymentDescription = $oPaymentInfo->PaymentMethod;
        $sPaymentId = $this->_fcGetOxidPaymentId($oPaymentInfo);

        $blPaymentTypeExists = $this->_fcPaymentTypeExists($sPaymentId);
        if (!$blPaymentTypeExists) {
            $this->_fcCreateAfterbuyPayment($sPaymentId, $sPaymentDescription);
        }

        return $sPaymentId;
    }

    /**
     * Creates needed payment method
     *
     * @param $sPaymentId
     * @param $sPaymentDescription
     * @return void
     */
    protected function _fcCreateAfterbuyPayment($sPaymentId, $sPaymentDescription) {
        $oPayment = oxNew('oxpayment');
        $oPayment->setId($sPaymentId);
        $oPayment->oxpayments__oxdesc = new oxField($sPaymentDescription);
        $oPayment->oxpayments__oxactive = new oxField(0);
        $oPayment->save();
    }

    /**
     * Checks, if payment with vertain id exists and returns
     *
     * @param $sPaymentId
     * @return bool
     */
    protected function _fcPaymentTypeExists($sPaymentId) {
        $oPayment = oxNew('oxpayment');
        $blPaymentExists = (bool) $oPayment->load($sPaymentId);

        return $blPaymentExists;
    }

    /**
     * Returns oxid paymentid by trying to find an assignment first. On fail create a new id
     * by payment name of afterbuy
     *
     * @param $oPaymentInfo
     * @return string
     */
    protected function _fcGetOxidPaymentId($oPaymentInfo) {
        if ((string)$oPaymentInfo->PaymentMethod == 'eBay Managed Payment') {
            return $this->_fcEbayPaymentType; #0102749
        }
        $sOxidPaymentId = $this->_fcMatchPayment($oPaymentInfo);

        if (!$sOxidPaymentId) {
            $sAfterbuyPaymentName = str_replace(' ', '_',$oPaymentInfo->PaymentMethod);
            $sAfterbuyPaymentName = str_replace('/', '',$sAfterbuyPaymentName);
            $sOxidPaymentId = "fcab_".strtolower($sAfterbuyPaymentName);
        }

        return md5($sOxidPaymentId);
    }

    /**
     * @param $oPaymentInfo
     * @return mixed string|false
     */
    protected function _fcMatchPayment($oPaymentInfo) {
        $sAfterbuyPaymentId = $oPaymentInfo->PaymentID;
        $sAfterbuyPaymentFunction = $oPaymentInfo->PaymentFunction;
        $aPaymentAssignmentKeys = array_keys($this->_aPaymentAssignments);
        $sZFunktionID = $sOxidPaymentId = false;

        if (in_array($sAfterbuyPaymentId, $this->_aPaymentAssignments)) {
            $sZFunktionID = $aPaymentAssignmentKeys[$sAfterbuyPaymentId];
        } else if (in_array($sAfterbuyPaymentFunction, $this->_aPaymentAssignments)) {
            $sZFunktionID = $aPaymentAssignmentKeys[$sAfterbuyPaymentFunction];
        }

        if ($sZFunktionID) {
            $oDb = oxDb::getDb();
            $sQuery = "SELECT OXPAYMENTID FROM fcafterbuypayments WHERE FCAFTERBUYPAYMENTID=".$oDb->quote($sZFunktionID);
            $sOxidPaymentId = $oDb->getOne($sQuery);
        }

        return $sOxidPaymentId;
    }

    /**
     * Adds general data to order
     *
     * @param $oOrder
     * @param $oUser
     * @param $oAfterbuyOrder
     * @return oxOrder
     */
    protected function _fcGetOrderGeneralData($oOrder, $oUser, $oAfterbuyOrder) {
        $oCounter = oxNew('oxcounter');

        $oOrder->oxorder__fcafterbuy_uid = new oxField($oAfterbuyOrder->OrderID);
        $oOrder->oxorder__oxshopid = new oxField($oUser->oxuser__oxshopid);
        $oOrder->oxorder__oxuserid = new oxField($oUser->getId());
        $oOrder->oxorder__oxorderdate = new oxField($oAfterbuyOrder->OrderDate);
        $oOrder->oxorder__oxordernr = new oxField($oCounter->getNext('oxorder'));
        $oOrder->oxorder__oxremark = new oxField($oAfterbuyOrder->UserComment);
        $oOrder->oxorder__oxtrackcode = new oxField($oAfterbuyOrder->TrackingLink);

        return $oOrder;
    }

    /**
     * Adds order delivery data
     *
     * @param $oOrder
     * @param $oAddress
     */
    protected function _fcGetOrderDeliveryData($oOrder, $oAddress) {
        $oOrder->oxorder__oxdelcompany = $oAddress->oxaddress__oxcompany;
        $oOrder->oxorder__oxdelfname = $oAddress->oxaddress__oxfname;
        $oOrder->oxorder__oxdellname = $oAddress->oxaddress__oxlname;
        $oOrder->oxorder__oxdelstreet = $oAddress->oxaddress__oxstreet;
        $oOrder->oxorder__oxdelstreetnr = $oAddress->oxaddress__oxstreetnr;
        $oOrder->oxorder__oxdelcity = $oAddress->oxaddress__oxcity;
        $oOrder->oxorder__oxdelcountryid = $oAddress->oxaddress__oxcountryid;
        $oOrder->oxorder__oxdelzip = $oAddress->oxaddress__oxzip;
        $oOrder->oxorder__oxdelfon = $oAddress->oxaddress__oxfon;
        $oOrder->oxorder__oxdelfax = $oAddress->oxaddress__oxfax;

        return $oOrder;
    }

    /**
     * Adds order billing data to oxorder
     *
     * @param $oOrder
     * @param $oUser
     * @return oxOrder
     */
    protected function _fcGetOrderBillData($oOrder, $oUser) {
        $oOrder->oxorder__oxbillemail = $oUser->oxuser__oxusername;
        $oOrder->oxorder__oxbillfname = $oUser->oxuser__oxfname;
        $oOrder->oxorder__oxbilllname = $oUser->oxuser__oxlname;
        $oOrder->oxorder__oxbillstreet = $oUser->oxuser__oxstreet;
        $oOrder->oxorder__oxbillstreetnr = $oUser->oxuser__oxstreetnr;
        $oOrder->oxorder__oxbillustid = $oUser->oxuser__oxustid;
        $oOrder->oxorder__oxbillcity = $oUser->oxuser__oxcity;
        $oOrder->oxorder__oxbillcountryid = $oUser->oxuser__oxcountryid;
        $oOrder->oxorder__oxbillzip = $oUser->oxuser__oxzip;
        $oOrder->oxorder__oxbillfon = $oUser->oxuser__oxfon;
        $oOrder->oxorder__oxbillfax = $oUser->oxuser__oxfax;

        return $oOrder;
    }

    /**
     * Creates user and returns its ID
     *
     * @param $oAfterbuyOrder
     * @return array
     */
    protected function _fcSetOxidUserByAfterbuyOrder($oAfterbuyOrder) {
        $this->oApiLogger->fcWriteLog("Receiving afterbuy orderdata from response:\n".print_r($oAfterbuyOrder,true),4);
        $oBillingAddress = $oAfterbuyOrder->BuyerInfoBilling;
        $oShippingAddress = $oAfterbuyOrder->BuyerInfoShipping;
        $oUser = oxNew('oxuser');

        $sUserOxid = $this->_fcCheckUserExists($oBillingAddress->Mail);
        if ($sUserOxid) {
            $oUser->load($sUserOxid);
        }

        $oUser = $this->_fcGetUserData($oBillingAddress, $oUser);
        $oUser->save();

        $oAddress = $this->_fcSetUserAddressData($oShippingAddress, $oUser);

        $aReturn = array('oxuser'=>$oUser, 'oxaddress'=>$oAddress);
        $this->oDefaultLogger->fcWriteLog("Returning userdata:\n".print_r($aReturn,true),4);
        return $aReturn;
    }

    /**
     * Sets user data from afterbuy order billing address
     *
     * @param $oBillingAddress
     * @param $oUser
     * @return oxUser
     */
    protected function _fcGetUserData($oBillingAddress, $oUser) {
        $oConfig = $this->getConfig();
        $sCompleteStreetInfo = $oBillingAddress->Street." ".$oBillingAddress->Street2;
        $aStreetParts = $this->_fcpoSplitStreetAndStreetNr($sCompleteStreetInfo);
        $sCountryId = $this->_fcpoGetCountryIdByIso2($oBillingAddress->CountryISO);

        $oUser->oxuser__oxshopid = new oxField($oConfig->getShopId());
        $oUser->oxuser__oxusername = new oxField($oBillingAddress->Mail);
        $oUser->oxuser__oxcompany = new oxField($oBillingAddress->Company);
        $oUser->oxuser__oxustid = new oxField($oBillingAddress->TaxIDNumber);
        $oUser->oxuser__oxfname = new oxField($oBillingAddress->FirstName);
        $oUser->oxuser__oxlname = new oxField($oBillingAddress->LastName);
        $oUser->oxuser__oxstreet = new oxField($aStreetParts['street']);
        $oUser->oxuser__oxstreetnr = new oxField($aStreetParts['streetnr']);
        $oUser->oxuser__oxcity = new oxField($oBillingAddress->City);
        $oUser->oxuser__oxcountryid = new oxField($sCountryId);
        $oUser->oxuser__oxzip = new oxField($oBillingAddress->PostalCode);
        $oUser->oxuser__oxfon = new oxField($oBillingAddress->Phone);
        $oUser->oxuser__oxfax = new oxField($oBillingAddress->Fax);
        $oUser->oxuser__fcafterbuy_userid = new oxField($oBillingAddress->AfterbuyUserID);
        $oUser->addToGroup('oxidcustomer');

        return $oUser;
    }

    /**
     * Adds or loads matching shipping address
     *
     * @param $oShippingAddress
     * @param $sUserOxid
     * @return oxAddress
     */
    protected function _fcSetUserAddressData($oShippingAddress, $oUser) {
        $sCompleteStreetInfo = $oShippingAddress->Street." ".$oShippingAddress->Street2;
        $aStreetParts = $this->_fcpoSplitStreetAndStreetNr($sCompleteStreetInfo);
        $sCountryId = $this->_fcpoGetCountryIdByIso2($oShippingAddress->CountryISO);

        $oAddress = oxNew('oxaddress');
        $oAddress->oxaddress__oxuserid = new oxField($oUser->getId());
        $oAddress->oxaddress__oxaddressuserid = new oxField($oUser->getId());
        $oAddress->oxaddress__oxfname = new oxField($oShippingAddress->FirstName);
        $oAddress->oxaddress__oxlname = new oxField($oShippingAddress->LastName);
        $oAddress->oxaddress__oxstreet = new oxField($aStreetParts['street']);
        $oAddress->oxaddress__oxstreetnr = new oxField($aStreetParts['streetnr']);
        $oAddress->oxaddress__oxfon = new oxField($oShippingAddress->Phone);
        $oAddress->oxaddress__oxcity = new oxField($oShippingAddress->City);
        $oAddress->oxaddress__oxcountry = new oxField($oShippingAddress->Country);
        $oAddress->oxaddress__oxcountryid = new oxField($sCountryId);
        $oAddress->oxaddress__oxzip = new oxField($oShippingAddress->PostalCode);

        // Check if address exists. Using addresshash as id for recognition
        $sEncodedDeliveryAddress = $oAddress->getEncodedDeliveryAddress();
        $blExists = $this->_fcCheckAddressExists($sEncodedDeliveryAddress);
        if ($blExists) {
            $oAddress->load($sEncodedDeliveryAddress);
        } else {
            $oAddress->setId($sEncodedDeliveryAddress);
            $oAddress->save();
        }

        return $oAddress;
    }

    /**
     * Checks if delivery address aleady exists
     *
     * @param $sEncodedDeliveryAddress
     * @return bool
     */
    protected function _fcCheckAddressExists($sEncodedDeliveryAddress) {
        $blReturn = false;
        $oAddress = oxNew('oxaddress');
        if ($oAddress->load($sEncodedDeliveryAddress)) {
            $blReturn = true;
        }

        return $blReturn;
    }

    /**
     * Returns id of a countrycode
     *
     * @param $sIso2Country
     * @return string
     */
    protected function _fcpoGetCountryIdByIso2($sIso2Country) {
        $oCountry = oxNew('oxCountry');
        $sOxid = $oCountry->getIdByCode($sIso2Country);

        return $sOxid;
    }

    /**
     * Method splits street as everything from the first occurence of a digit and streetnr as the rest
     * This works with patterns like:
     * - Beispielstraße 22B
     * - Straße des Beispiels 55A
     * NOTE: Some major exceptions like "Straße des 17. Juni 25A" will not be covered.
     *
     * @param string $sStreetAndStreetNr
     * @return array
     */
    protected function _fcpoSplitStreetAndStreetNr($sStreetAndStreetNr) {
        $aReturn = array();
        preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $sStreetAndStreetNr, $matches);
        $aReturn['street'] = $matches[1];
        $aReturn['streetnr'] = $matches[2];

        return $aReturn;
    }

    /**
     * Checks if user exists
     *
     * @param $sEmailAddress
     * @return mixed string|false
     */
    protected function _fcCheckUserExists($sEmailAddress) {
        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $sQuery = "SELECT OXID FROM oxuser WHERE OXUSERNAME=".$oDb->quote($sEmailAddress);
        $mOxid = $oDb->getOne($sQuery);

        return $mOxid;
    }
}
