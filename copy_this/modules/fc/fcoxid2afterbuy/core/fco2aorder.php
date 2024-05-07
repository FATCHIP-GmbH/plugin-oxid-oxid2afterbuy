<?php

/**
 * Description of fcafterbuyorder
 *
 * @author andre
 */
class fco2aorder extends fco2abase {

    /**
     * Handle to send order to Afterbuy
     * 
     * @param object $oOrder 
     * @param object $oUser
     * @return void
     */
    public function fcSendOrderToAfterbuy($oOrder, $oUser) {
        $blAllowed = $this->fcJobExecutionAllowed('orderexport');
        if (!$blAllowed) {
            $this->oDefaultLogger->fcWriteLog("ERROR: Execution of orderexport is not allowed by configuration");
            return;
        }

        // build request url
        $sActionParameter = $this->_fcGetInterfaceAction();
        $sDeliveryFlagParameter = ($oOrder->oxorder__oxdelstreet->value != "") ? 1 : 0;
        $sDeliveryAddressFlagParameter = "&Lieferanschrift={$sDeliveryFlagParameter}";
        $sAfterbuyCredentialParameters = $this->_fcGetAfterbuyCredentialParameter();
        $sCustomerInfoParameters = $this->_fcGetCustomerInfoParameters($oOrder, $oUser);
        $sOrderArticleParameters = $this->_fcGetOrderarticleParameters($oOrder);
        $sGenericOrderParameters = $this->_fcGetGenericOrderParameters($oOrder);
        $sAdditiondalOrderParameters = $this->_fcGetAdditionalOrderParameters($oOrder);
        $sConfigParameters = $this->_fcGetConfigParameters();

        $sRequest = $this->_fcGetAfterbuyConfigArray()['afterbuyShopInterfaceBaseUrl'];

        $sRequest .= $sActionParameter . $sDeliveryAddressFlagParameter . $sAfterbuyCredentialParameters;
        $sRequest .= $sCustomerInfoParameters . $sOrderArticleParameters . $sGenericOrderParameters . $sAdditiondalOrderParameters . $sConfigParameters;

        $oAfterbuyApi = $this->_fcGetAfterbuyApi();

        $sOutput = $oAfterbuyApi->requestShopInterfaceAPI($sRequest);
        $this->oApiLogger->fcWriteLog("DEBUG: Requesting shopinterface for sending order:\n".$sRequest,4);
        $this->oApiLogger->fcWriteLog("DEBUG: Response:\n".$sOutput,4);
        $this->_fcHandleShopInterfaceAnswer($sOutput, $oOrder);
    }

    /**
     * Return the article information parameters of current 
     * 
     * @param object $oOrder
     * @return string Basket parameters of all ordered articles
     */
    protected function _fcGetOrderarticleParameters($oOrder)
    {
        $oConfig = $this->getConfig();
        $bSubmitWeight = $oConfig->getConfigParam('sFcAfterbuySendWeight') == '1';

        $aOrderArticles = $oOrder->getOrderArticles();
        $sOrderarticleParameters = "";
        $iSuffix = 1;
        $iPositions = count($aOrderArticles);
        $sOrderarticleParameters .= "&PosAnz=" . $iPositions;
        foreach ($aOrderArticles as $oOrderArticle) {
            $oArticle = $oOrderArticle->getArticle();
            $ArtikelStammID = $oArticle->oxarticles__oxartnum->value;
            $sOrderarticleParameters .= "&ArtikelStammID_{$iSuffix}=" . $ArtikelStammID;
            $sOrderarticleParameters .= "&Artikelnr_{$iSuffix}=" . preg_replace("/[^0-9]/", "", $oOrderArticle->oxorderarticles__oxartnum->value); // we need to offer a numeric artnum so we replace non numeric characters
            $sOrderarticleParameters .= "&AlternArtikelNr1_{$iSuffix}=" . $this->_fcEncodeParameters($oOrderArticle->oxorderarticles__oxartnum->value, true, false);
            $sOrderarticleParameters .= "&Artikelname_{$iSuffix}=" . $this->_fcEncodeParameters($oOrderArticle->oxorderarticles__oxtitle->value . " " . $oOrderArticle->oxorderarticles__oxselvariant->value, true, false, true);
            $sOrderarticleParameters .= "&ArtikelEpreis_{$iSuffix}=" . str_replace(".", ",", $oOrderArticle->oxorderarticles__oxbprice->value);
            $sOrderarticleParameters .= "&ArtikelMwSt_{$iSuffix}=" . str_replace(".", ",",$oOrderArticle->oxorderarticles__oxvat->value);
            $sOrderarticleParameters .= "&ArtikelMenge_{$iSuffix}=" . $oOrderArticle->oxorderarticles__oxamount->value;

            if($bSubmitWeight) {
                $sOrderarticleParameters .= "&ArtikelGewicht_{$iSuffix}=" . $oOrderArticle->oxorderarticles__oxweight->value;
            }

            $sOrderarticleParameters .= "&ArtikelLink_{$iSuffix}=" . $this->_fcEncodeParameters($oArticle->getLink(), true, false);
            $aAttributes = $oArticle->getAttributes();
            $sAttributeParameters = "&Attribute_{$iSuffix}=";
            $iAttributeSuffix = 1;
            $iAmountAttributes = count($aAttributes);
            foreach ($aAttributes as $oAttribute) {
                /**
                 * @todo This code will possibly not work needs to be debugged
                 */
                $sAttributeParameters .= $this->_fcEncodeParameters($oAttribute->oxattribute__oxtitle->value, true, false) . ":"; //."{$iAttributeSuffix}:";
                $sAttributeParameters .= $this->_fcEncodeParameters($oAttribute->oxobject2attribute__oxvalue->value, true, false); // ."{$iAttributeSuffix};
                if ($iAttributeSuffix < $iAmountAttributes) {
                    $sAttributeParameters .= "|";
                }
                $iAttributeSuffix++;
            }

            $iSuffix++;
        }

        return $sOrderarticleParameters;
    }
    
    /**
     * Returns the action string for shop interface request
     * 
     * @param string $sAction Optional
     * @return string Action part of REST request
     */
    protected function _fcGetInterfaceAction($sAction = 'new') {
        $sActionParameter = "?Action={$sAction}";
        return $sActionParameter;
    }

    /**
     * Returns the delivery address flag string for shop interface request
     * 
     * @param object $oOrder
     *
     * @return string Delivery flag part of REST request
     */
    protected function _fcGetDeliveryAddressFlagParameter($oOrder)
    {
        if ($oOrder->getDelAddressInfo()) {
            $sDeliveryAddressFlag = "1";
        } else {
            $sDeliveryAddressFlag = "0";
        }
        $sDeliveryFlagParameter = "&Lieferanschrift={$sDeliveryAddressFlag}";

        return $sDeliveryFlagParameter;
    }

    /**
     * Returns credential parameters needed for REST request
     * 
     * @return string Credential parameters
     */
    protected function _fcGetAfterbuyCredentialParameter()
    {
        $aConfig = $this->_fcGetAfterbuyConfigArray();
        $sCredentialParameters = "&UserToken=" . $this->_fcEncodeParameters($aConfig['afterbuyAccountToken'], false);
        $sCredentialParameters .= "&PartnerToken=" . $this->_fcEncodeParameters($aConfig['afterbuyPartnerToken'], false);
        $sCredentialParameters .= "&UserID=" . $this->_fcEncodeParameters($aConfig['afterbuyUsername'], false);

        return $sCredentialParameters;
    }

    /**
     * Returns customer information like address, name etc as urlencoded parameter string
     * 
     * @param object $oOrder
     * @param object $oUser
     *
     * @return string Encoded customer parameters
     */
    protected function _fcGetCustomerInfoParameters($oOrder, $oUser) {
        // get information
        $oConfig = $this->getConfig();
        $sBillSal = $oOrder->oxorder__oxbillsal->value;
        $sBillSalutation = ( $sBillSal == "MR" || $sBillSal == "Herr" ) ? "Herr" : "Frau";
        $sDelSal = $oOrder->oxorder__oxdelsal->value;
        $sDelSalutation = ( $sDelSal == "MR" || $sDelSal == "Herr" ) ? "Herr" : "Frau";
        $blUseCustNr = $oConfig->getConfigParam('blFcAfterbuyUseOwnCustNr');

        // country
        $sBillCountry = $this->_fcGetCarPlateCountryTag($oOrder->oxorder__oxbillcountryid->value);
        $sDelCountry = $this->_fcGetCarPlateCountryTag($oOrder->oxorder__oxdelcountryid->value);

        /// state
        $oState = oxNew('oxstate');
        $oState->load($oOrder->oxorder__oxbillstateid->value);
        $sBillState = $oState->oxstates__oxtitle->value;
        $oState->load($oOrder->oxorder__oxdelstateid->value);
        $sDelState = $oState->oxstates__oxtitle->value;

        /**
         * @todo There is also a merchant Flag &Haendler= available this should be implemented by 
         * defining a Group via config. If user belongs to this group the flag is true
         */
        $sCustomerParameters = "&Kbenutzername=" . $this->_fcEncodeParameters($oUser->oxuser__oxusername->value);
        // bill parameters
        $sCustomerParameters .= "&Kanrede=" . $this->_fcEncodeParameters($sBillSalutation, true, true, true);
        $sCustomerParameters .= "&KFirma=" . $this->_fcEncodeParameters($oOrder->oxorder__oxbillcompany->value, true, true, true);
        $sCustomerParameters .= "&KVorname=" . $this->_fcEncodeParameters($oOrder->oxorder__oxbillfname->value, true, true, true);
        $sCustomerParameters .= "&KNachname=" . $this->_fcEncodeParameters($oOrder->oxorder__oxbilllname->value, true, true, true);
        $sCustomerParameters .= "&KStrasse=" . $this->_fcEncodeParameters($oOrder->oxorder__oxbillstreet->value . " " . $this->_fcEncodeParameters($oOrder->oxorder__oxbillstreetnr->value, false), true, true, true);
        $sCustomerParameters .= "&KStrasse2=" . $this->_fcEncodeParameters($oOrder->oxorder__oxbilladdinfo->value, true, true, true);
        $sCustomerParameters .= "&KPLZ=" . $this->_fcEncodeParameters($oOrder->oxorder__oxbillzip->value);
        $sCustomerParameters .= "&KOrt=" . $this->_fcEncodeParameters($oOrder->oxorder__oxbillcity->value, true, true, true);
        $sCustomerParameters .= "&KBundesland=" . $this->_fcEncodeParameters($sBillState, true, true, true);
        $sCustomerParameters .= "&Ktelefon=" . $this->_fcEncodeParameters($oOrder->oxorder__oxbillfon->value);
        $sCustomerParameters .= "&Kfax=" . $this->_fcEncodeParameters($oOrder->oxorder__oxbillfax->value);
        $sCustomerParameters .= "&Kemail=" . $this->_fcEncodeParameters($oOrder->oxorder__oxbillemail->value);
        $sCustomerParameters .= "&KLand=" . $this->_fcEncodeParameters($sBillCountry);
        $sCustomerParameters .= "&KBirthday=" . $this->_fcEncodeParameters($oUser->oxuser__oxbirthdate->value);
        $sCustomerParameters .= "&UsStID=" . $this->_fcEncodeParameters($oOrder->oxorder__oxbillustid->value);
        $sCustNr = ($blUseCustNr) ? $oUser->oxuser__oxcustnr->value : '';
        $sCustomerParameters .= "&EKundenNr=" . $this->_fcEncodeParameters($sCustNr);

        // delivery parameters
        $sCustomerParameters .= "&KLanrede=" . $this->_fcEncodeParameters($sDelSalutation, true, true, true);
        $sCustomerParameters .= "&KLFirma=" . $this->_fcEncodeParameters($oOrder->oxorder__oxdelcompany->value, true, true, true);
        $sCustomerParameters .= "&KLVorname=" . $this->_fcEncodeParameters($oOrder->oxorder__oxdelfname->value, true, true, true);
        $sCustomerParameters .= "&KLNachname=" . $this->_fcEncodeParameters($oOrder->oxorder__oxdellname->value, true, true, true);
        $sCustomerParameters .= "&KLStrasse=" . $this->_fcEncodeParameters($oOrder->oxorder__oxdelstreet->value . " " . $this->_fcEncodeParameters($oOrder->oxorder__oxdelstreetnr->value, false), true,true, true);
        $sCustomerParameters .= "&KLStrasse2=" . $this->_fcEncodeParameters($oOrder->oxorder__oxdeladdinfo->value, true, true, true);
        $sCustomerParameters .= "&KLPLZ=" . $this->_fcEncodeParameters($oOrder->oxorder__oxdelzip->value);
        $sCustomerParameters .= "&KLOrt=" . $this->_fcEncodeParameters($oOrder->oxorder__oxdelcity->value, true, true, true);
        $sCustomerParameters .= "&KLBundesland=" . $this->_fcEncodeParameters($sDelState, true, true, true);
        $sCustomerParameters .= "&KLtelefon=" . $this->_fcEncodeParameters($oOrder->oxorder__oxdelfon->value);
        $sCustomerParameters .= "&KLfax=" . $this->_fcEncodeParameters($oOrder->oxorder__oxdelfax->value);
        $sCustomerParameters .= "&KLemail=" . $this->_fcEncodeParameters($oOrder->oxorder__oxdelemail->value);
        $sCustomerParameters .= "&KLLand=" . $this->_fcEncodeParameters($sDelCountry);

        return $sCustomerParameters;
    }

    /**
     * This method returns generic order parameters like orderdate etc.
     * 
     * @param object $oOrder
     * @return string Order Parameters
     */
    protected function _fcGetGenericOrderParameters($oOrder)
    {
        $sPaymentId = $oOrder->oxorder__oxpaymenttype->value;
        $oPayment = oxNew('oxpayment');
        $oPayment->load($sPaymentId);
        $oDeliverySet = $oOrder->getDelSet();
        $dOrderDeliveryCosts = $oOrder->getOrderDeliveryPrice()->getBruttoPrice();
        $dOrderPaymentCosts = $oOrder->getOrderPaymentPrice()->getBruttoPrice();

        $sBuyDateRaw = $oOrder->oxorder__oxorderdate->value;
        $iTimeBuyDate = strtotime($sBuyDateRaw);
        $sBuyDateFormatted = date("d.m.Y H:i:s", $iTimeBuyDate);
        $sBuyDateUrlFormatted = $this->_fcEncodeParameters($sBuyDateFormatted);
        $sRemark = $this->_fcEncodeParameters($oOrder->oxorder__oxremark->value);
        $sDeliveryType = $this->_fcEncodeParameters($oDeliverySet->oxdeliveryset__oxtitle->value);
        $sDeliveryCosts = $this->_fcEncodeParameters(str_replace(".", ",", $dOrderDeliveryCosts));
        $sPaymentName = $this->_fcEncodeParameters($oPayment->oxpayments__oxdesc->value);
        $sZFunktionsID = $this->_fcEncodeParameters($this->_fcGetAfterbuyPaymentId($sPaymentId));
        $sPaymentCosts = $this->_fcEncodeParameters(number_format($dOrderPaymentCosts,2,',','.'));
        $sOrderId = $this->_fcEncodeParameters($oOrder->getId());
        $sOrderCurrency = $this->_fcEncodeParameters($oOrder->getOrderCurrency()->name);

        $sOrderParameters = "&BuyDate=" . $sBuyDateUrlFormatted;
        $sOrderParameters .= "&Kommentar=" . $sRemark;
        $sOrderParameters .= "&Versandart=" . $sDeliveryType;
        $sOrderParameters .= "&Versandkosten=" . $sDeliveryCosts;
        $sOrderParameters .= "&Zahlart=" . $sPaymentName;
        $sOrderParameters .= "&ZFunktionsID=" . $sZFunktionsID;
        $sOrderParameters .= "&ZahlartenAufschlag=" . $sPaymentCosts;
        $sOrderParameters .= $this->_fcGetBankData($oOrder);
        $sOrderParameters .= "&VID=" . $sOrderId;
        $sOrderParameters .= "&SoldCurrency=" . $sOrderCurrency;
        $sOrderParameters .= $this->_fcGetPayState($oOrder);

        $sOrderParameters .= "&CheckVID=1";
        $sOrderParameters .= "&Bestandart=shop";
        $sOrderParameters .= "&Versandgruppe=shop";
        $sOrderParameters .= "&Artikelerkennung=2";

        return $sOrderParameters;
    }

    /**
     * Handles non-obligatory parameters
     *
     * @param $oOrder
     * @return string
     */
    protected function _fcGetAdditionalOrderParameters($oOrder) {
        $sOrderNr = $oOrder->oxorder__oxordernr->value;
        $sAdditionalOrderParameters = '';

        $sAdditionalOrderParameters .=
            $this->_fcProvideOxidOrderNrInAdditionalField($sOrderNr);

        return $sAdditionalOrderParameters;
    }

    /**
     * Add oxid ordernumber to a specific field based on config
     *
     * @param $sOrderParameters
     * @return
     */
    protected function _fcProvideOxidOrderNrInAdditionalField($sOrderNr) {
        $oConfig = $this->getConfig();
        $sTargetField = $oConfig->getConfigParam('sFcSendOrderNrInAdditionalField');

        switch($sTargetField) {
            case '1':
                $result = "&VMemo=" .
                    $this->_fcEncodeParameters('OXID Order No: ' . $sOrderNr);
                break;
            default:
                $result = '';
        }


        return $result;
    }

    /**
     * Adds paid state to order
     *
     * @param $oOrder
     * @return string
     */
    protected function _fcGetPayState($oOrder) {
        $sPayDateRaw = $oOrder->oxorder__oxpaid->value;
        $iTimePayDate = strtotime($sPayDateRaw);
        $sPayDateFormatted = date("d.m.Y H:i:s", $iTimePayDate);

        $sOrderParameters = '';
        /**
         * Check state of paypal order due to comment
         * https://tickets.fatchip.de/view.php?id=30656#c95629
         */
        $blPaypalPaidState = (
            $oOrder->oxorder__oxpaymenttype->value == "oxidpaypal" &&
            $oOrder->oxorder__oxtranstatus->value == "NOT_FINISHED"
        ) ? false : true;

        $iPaid = (int) (
            $iTimePayDate &&
            $oOrder->oxorder__oxpaymenttype->value != "trosofortgateway_su" &&
            $blPaypalPaidState
        );

        $sPaydateUrlFormatted = ($iTimePayDate) ? $this->_fcEncodeParameters($sPayDateFormatted) : '';

        $sOrderParameters .= "&SetPay=".(string) $iPaid;
        $sOrderParameters .= "&PayDate=" . $sPaydateUrlFormatted;

        return $sOrderParameters;
    }

    /**
     * Returns bankdata if needed
     *
     * @param $oOrder
     * @return string
     */
    protected function _fcGetBankData($oOrder) {
        $oConfig = $this->getConfig();
        $oPaymentType = $oOrder->getPaymentType();
        $sOrderPaymentId = $oOrder->oxorder__oxpaymenttype->value;

        $aFcAfterbuyDebitPayments =
            $oConfig->getConfigParam('aFcAfterbuyDebitPayments');
        $aFcAfterbuyDebitDynBankname =
            $oConfig->getConfigParam('aFcAfterbuyDebitDynBankname');
        $aFcAfterbuyDebitDynBankzip =
            $oConfig->getConfigParam('aFcAfterbuyDebitDynBankzip');
        $aFcAfterbuyDebitDynAccountNr =
            $oConfig->getConfigParam('aFcAfterbuyDebitDynAccountNr');
        $aFcAfterbuyDebitDynAccountOwner =
            $oConfig->getConfigParam('aFcAfterbuyDebitDynAccountOwner');

        $blValid = (
            in_array($sOrderPaymentId, $aFcAfterbuyDebitPayments) &&
            isset($aFcAfterbuyDebitDynBankname[$sOrderPaymentId]) &&
            isset($aFcAfterbuyDebitDynBankzip[$sOrderPaymentId]) &&
            isset($aFcAfterbuyDebitDynAccountNr[$sOrderPaymentId]) &&
            isset($aFcAfterbuyDebitDynAccountOwner[$sOrderPaymentId])
        );

        if (!$blValid) return;

        $sOrderParameters = '';
        $aDynValues = $oPaymentType->getDynValues();
        foreach ($aDynValues as $oValue) {
            $sValue = $this->_fcEncodeParameters($oValue->value);
            $sName = $oValue->name;

            $blBankName =
                ($sName == $aFcAfterbuyDebitDynBankname[$sOrderPaymentId]);
            $blBankZip =
                ($sName == $aFcAfterbuyDebitDynBankzip[$sOrderPaymentId]);
            $blBankAccountNr =
                ($sName == $aFcAfterbuyDebitDynAccountNr[$sOrderPaymentId]);
            $blBankAccountOwner =
                ($sName == $aFcAfterbuyDebitDynAccountOwner[$sOrderPaymentId]);

            if ($blBankName) {
                $sOrderParameters .= "&Bankname=" . $sValue;
                continue;
            }
            if ($blBankZip) {
                $sOrderParameters .= "&BLZ=" . $sValue;
                continue;
            }
            if ($blBankAccountNr) {
                $sOrderParameters .= "&Kontonummer=" . $sValue;
                continue;
            }
            if ($blBankAccountOwner) {
                $sOrderParameters .= "&Kontoinhaber=" . $sValue;
            }
        }

        return $sOrderParameters;
    }

    /**
     * Returns values set by configuraton
     *
     * @param void
     * @return string
     */
    protected function _fcGetConfigParameters() {
        $sConfigParameters = "&NoFeedback=" . $this->_fcGetEncodedConfigParameter('sFcAfterbuyFeedbackType');
        $sConfigParameters .= "&NoVersandCalc=" . $this->_fcGetEncodedConfigParameter('sFcAfterbuyDeliveryCalculation');
        $sConfigParameters .= "&MwStNichtAusweisen=" . $this->_fcGetEncodedConfigParameter('sFcAfterbuySendVat');
        $sConfigParameters .= "&MarkierungID=" . $this->_fcGetEncodedConfigParameter('sFcAfterbuyMarkId');
        $sConfigParameters .= "&Kundenerkennung=" . $this->_fcGetEncodedConfigParameter('sFcAfterbuyCustIdent');
        $sConfigParameters .= "&NoeBayNameAktu=" . $this->_fcGetEncodedConfigParameter('sFcAfterbuyOverwriteEbayName');

        return $sConfigParameters;
    }

    /**
     * Returns ready encoded config parameter
     *
     * @param $sConfigName
     * @return string
     */
    protected function _fcGetEncodedConfigParameter($sConfigName) {
        $oConfig = $this->getConfig();
        $sValue = $oConfig->getConfigParam($sConfigName);
        $sValue = $this->_fcEncodeParameters($sValue);

        return $sValue;
    }

    /**
     * Returns the assigned Afterbuy Payment Id for Shop payment type
     * 
     * @param string $sPaymentId Shop PaymentId
     * @return string AfterbuyPaymentId
     */
    protected function _fcGetAfterbuyPaymentId($sPaymentId)
    {
        $sQuery = "SELECT FCAFTERBUYPAYMENTID FROM fcafterbuypayments WHERE OXPAYMENTID='{$sPaymentId}'";
        $sAfterbuyPaymentId = oxDb::getDb()->getOne($sQuery);

        return (string) $sAfterbuyPaymentId;
    }

    /**
     * Due Afterbuy uses car plate country tags we will need to ask our custom table
     * 
     * @param string $sCountryId
     * @return string Car plate sign
     */
    protected function _fcGetCarPlateCountryTag($sCountryId)
    {
        $oDb = oxDb::getDb();
        $sQuery = "SELECT FCCARPLATE FROM fcafterbuycountry WHERE OXID='{$sCountryId}'";

        $sCarPlateTag = $oDb->getOne($sQuery);

        return $sCarPlateTag;
    }

    /**
     * Handles answer for requesting afterbuy order. Saving answer data into table etc.
     * 
     * @param string $sResult
     * @param oxOrder $oOrder
     * @return bool Success
     */
    protected function _fcHandleShopInterfaceAnswer($sResult, $oOrder)
    {
        $aParsedResponse = $this->_fcParseOrderRequestResult($sResult);
        if ($aParsedResponse) {
            $this->_fcAssignAfterbuyParametersToOrder($aParsedResponse, $oOrder);
        }
        else {
            /**
             * @todo deeper inspection, mark order as problematic
             */
        }
    }
    
    /**
     * Method handles xml string, transform it to array for better handling. 
     * Returns false, if xml has error or is invalid
     * 
     * @param string $sResult
     * @return mixed array/boolean
     */
    protected function _fcParseOrderRequestResult($sResult)
    {
        $mReturn = false;
        try {
            $oXml = simplexml_load_string($sResult);
            if (isset($oXml->data)) {
                $sAfterbuyAID = (string)$oXml->data->AID[0];
                $sAfterbuyVID = (string)$oXml->data->VID[0];
                $sAfterbuyUID = (string)$oXml->data->UID[0];
                $sAfterbuyCustomerNumber = (string)$oXml->data->KundenNr[0];
                $sAfterbuyECustomerNumber = (string)$oXml->data->EKundenNr[0];

                $mReturn = array(
                    'AID' => $sAfterbuyAID,
                    'VID' => $sAfterbuyVID,
                    'UID' => $sAfterbuyUID,
                    'KundenNr' => $sAfterbuyCustomerNumber,
                    'EKundenNr' => $sAfterbuyECustomerNumber,
                );
            }
        } catch (oxException $ex) {
            // this will mean either that xml could not be loaded or xml could not be parsed
            $this->oApiLogger->fcWriteLog($ex->getMessage(), 1);
        }
        
        return $mReturn;
    }
    
    /**
     * Assigns afterbuy parameters to current order
     * 
     * @param array $aResponse
     * @param oxOrder $oOrder
     * @return void
     */
    protected function _fcAssignAfterbuyParametersToOrder($aResponse, $oOrder)
    {
        $oOrder->oxorder__fcafterbuy_aid = new oxField($aResponse['AID'], oxField::T_RAW);
        $oOrder->oxorder__fcafterbuy_vid = new oxField($aResponse['VID'], oxField::T_RAW);
        $oOrder->oxorder__fcafterbuy_uid = new oxField($aResponse['UID'], oxField::T_RAW);
        $oOrder->oxorder__fcafterbuy_customnr = new oxField($aResponse['KundenNr'], oxField::T_RAW);
        $oOrder->oxorder__fcafterbuy_ecustomnr = new oxField($aResponse['EKundenNr'], oxField::T_RAW);
        // set order as NOT fulfilled
        $oOrder->oxorder__fcafterbuy_fulfilled = new oxField('0', oxField::T_RAW);
        
        $oOrder->save();
    }


    /**
     * Method will be used for parameter preparation related to encodings etc.
     *
     * @param $sParam
     * @param bool $blUrlEncode
     * @param bool $blTrim
     * @param bool $blUtf8Decode
     * @return string
     */
    protected function _fcEncodeParameters($sParam, $blUrlEncode = true, $blTrim = true, $blUtf8Decode = false)
    {
        $oConfig = $this->getConfig();

        $blFcAfterbuyExportUTF8Orders =
            $oConfig->getConfigParam('blFcAfterbuyExportUTF8Orders');

        $blPerformDecode = (
            $blUtf8Decode === true &&
            $blFcAfterbuyExportUTF8Orders === false
        );

        if($blPerformDecode) {
            $sParam = utf8_decode($sParam);
        }

        if($blUrlEncode === true) {
            $sParam = urlencode($sParam);
        }

        if($blTrim === true) {
            $sParam = trim($sParam);
        }

        return $sParam;
    }

}
