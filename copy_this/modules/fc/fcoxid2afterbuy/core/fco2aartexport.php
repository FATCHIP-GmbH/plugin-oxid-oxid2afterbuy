<?php
class fco2aartexport extends fco2abase {

    /**
     * Dictionary of value translations
     * @var array
     */
    protected $_aAfterbuy2OxidDictionary = array(
        'UserProductID' => 'oxarticles__oxid',
        'Anr' => 'oxarticles__oxartnum',
        'EAN' => 'oxarticles__oxean',
        'ProductID' => 'oxarticles__fcafterbuyid',
        'ManufacturerPartNumber' => 'oxarticles__oxmpn',
        'Keywords' => 'oxarticles__oxkeywords',
        'Quantity' => 'oxarticles__oxstock|oxarticles__oxvarstock',
        'AuctionQuantity' => 'oxarticles__oxstock|oxarticles__oxvarstock',
        'UnitOfQuantity' => 'oxarticles__oxunitname',
        'BuyingPrice' => 'oxarticles__oxbprice',
        'Weight' => 'oxarticles__oxweight',
        'ShortDescription' => 'oxarticles__oxshortdesc',
        'FreeValue1' => 'oxarticles__oxid',
        'FreeValue3' => 'oxarticles__tc_ebay_title', #0102749
    );

    /**
     * List of oxid variants for preventing loading those twice
     *
     * @var array|null
     */
    protected $_aOxidVariants = null;

    /**
     * Executes upload of selected afterbuy articles
     *
     * @param void
     * @return void
     */
    public function execute()
    {
        $blAllowed = $this->fcJobExecutionAllowed('artexport');
        if (!$blAllowed) {
            echo "Execution of artexport is not allowed by configuration\n";
            exit(1);
        }

        $this->_fcTransferCategories();
        $oAfterbuyApi = $this->_fcGetAfterbuyApi();
        $aArticleIds = $this->_fcGetAffectedArticleIds();

        foreach ($aArticleIds as $sArticleOxid) {
            $this->_fcAddVariants($sArticleOxid);
            $oArt = $this->_fcGetAfterbuyArticleByOxid($sArticleOxid);
            if (!$oArt) continue;

            $sResponse = $oAfterbuyApi->updateArticleToAfterbuy($oArt);
            $this->_fcValidateCallStatus($sResponse);
            $this->_fcAddAfterbuyIdToArticle($sArticleOxid, $sResponse);
        }
    }

    /**
     * Build category tree structure fitting to Aferbuy needs and send it to Afterbuy
     *
     * @param void
     * @return bool
     */
    protected function _fcTransferCategories() {
        $oAfterbuyDb = oxNew('fco2adatabase');
        $oAfterbuyDb->fcCreateCatalogIds();

        $oCategoryList = oxNew('oxCategoryList');
        $aCatalogs = array();
        $oCategoryList->setLoadFull(true);
        $oCategoryList->buildTree(null);

        foreach ($oCategoryList as $oTopCategory) {
            $sOxid = $oTopCategory->getId();
            $aCatalogs[$sOxid]['catalog'] =
                $this->_fcGetCatalogByCategory($oTopCategory);
            $aCatalogs[$sOxid]['subcatalogs'] = array();
            $aSubCategories = $oTopCategory->getSubCats();
            $blAddSubCats = (
                is_array($aSubCategories) &&
                count($aSubCategories) > 0
            );

            if ($blAddSubCats)
                $this->_fcAddSubCats($aSubCategories, $oTopCategory, $aCatalogs);
        }

        if (count($aCatalogs) == 0) {
            // no catalogs
            return true;
        }

        $oAfterbuyApi = $this->_fcGetAfterbuyApi();
        $sResponse = $oAfterbuyApi->updateShopCatalogs($aCatalogs);
        $this->_fcParseCatalogResult($sResponse);
        $this->_fcValidateCallStatus($sResponse);
    }

    /**
     * Parsing through xml result an update IDs where needed
     *
     * @param $sResponse
     * @return void
     */
    protected function _fcParseCatalogResult($sResponse)
    {
        $oResponse = simplexml_load_string($sResponse);

        if (!isset($oResponse->Result->NewCatalogs))
            return;

        $oNewCatalogs = $oResponse->Result->NewCatalogs;
        $this->_fcSetCatalogCorrections($oNewCatalogs);
    }

    /**
     * Method handles correction of catalogid
     *
     * @param $oNewCatalogs
     * @return void
     */
    protected function _fcSetCatalogCorrections($oNewCatalogs)
    {
        foreach ($oNewCatalogs->NewCatalog as $oNewCatalog) {
            $this->_fcHandleCatalogCorrection($oNewCatalog);
        }
    }

    /**
     * Recursive walk through updated catalogid
     *
     * @param $oNewCatalog
     * @return void
     */
    protected function _fcHandleCatalogCorrection($oNewCatalog)
    {
        $oAfterbuyDB = oxNew('fco2adatabase');
        $sCatalogIDRequested = (string) $oNewCatalog->CatalogIDRequested;
        $sCatalogId = (string) $oNewCatalog->CatalogID;

        $oAfterbuyDB->fcUpdateCatalogId($sCatalogId, $sCatalogIDRequested);

        // subcatalog available
        $blSubCatalogAvailable = isset($oNewCatalog->NewCatalog);
        if (!$blSubCatalogAvailable) return;

        foreach ($oNewCatalog->NewCatalog as $oSubCatalog) {
            $this->_fcHandleCatalogCorrection($oSubCatalog);
        }
    }

    /**
     * Recursively build catalogs
     *
     * @param array $aSubCategories
     * @param object $oCategory
     * @param $aCatalogs
     * @return void
     */
    protected function _fcAddSubCats($aSubCategories, $oCategory, &$aCatalogs)
    {
        $aMainPath = $this->_fcGetOxidArrayPath($oCategory);
        $oCatalog = $this->_fcGetCatalogByCategory($oCategory);
        $this->_fcSetRecursiveCatalog($aCatalogs, $aMainPath, $oCatalog);

        foreach ($aSubCategories as $oSubCategory) {
            $aSubSubCategories = $oSubCategory->getSubCats();
            $this->_fcAddSubCats($aSubSubCategories, $oSubCategory, $aCatalogs);
        }
    }

    /**
     * Sets an element of a multidimensional array from an array containing
     * the keys for each dimension.
     *
     * @param array &$aArray The array to manipulate
     * @param array $aPath An array containing keys for each dimension
     * @param mixed $oCatalog The value that is assigned to the element
     */
    protected function _fcSetRecursiveCatalog(&$aArray, $aPath, $oCatalog)
    {
        $key = array_shift($aPath);
        if (empty($aPath)) {
            $aArray[$key]['catalog'] = $oCatalog;
            $aArray[$key]['subcatalogs'] = array();
        } else {
            if (!isset($aArray[$key]) || !is_array($aArray[$key])) {
                $aArray[$key] = array();
            }
            $this->_fcSetRecursiveCatalog($aArray[$key], $aPath, $oCatalog);
        }
    }

    /**
     * Returns oxid's in an array path
     *
     * @param $oCategory
     * @param array $aPath
     * @return array
     */
    protected function _fcGetOxidArrayPath($oCategory, $aPath=array()) {
        $sValue = $oCategory->getId();
        array_unshift($aPath, $sValue);

        $oParentCategory = $oCategory->getParentCategory();
        if ($oParentCategory)
            $aPath = $this->_fcGetOxidArrayPath($oParentCategory, $aPath);

        $aFinalPath = array();
        $blFill = false;

        foreach ($aPath as $sValue) {
            $blAddSubCatalog = (
                $blFill &&
                $sValue != 'subcatalogs'
            );

            if ($blAddSubCatalog) {
                $aFinalPath[] = 'subcatalogs';
                $blFill = false;
            } else {
                $blFill = ($sValue == 'subcatalogs') ? false : true;
            }
            $aFinalPath[] = $sValue;
        }

        return $aFinalPath;
    }

    /**
     * Returns proper Afterbuy object of given OXID category object
     *
     * @param object $oSlimCategory
     * @return object
     */
    protected function _fcGetCatalogByCategory($oSlimCategory)
    {
        $sOxid = $oSlimCategory->getId();
        $oCategory = oxNew('oxCategory');
        $oCategory->load($sOxid);
        $oCatalog = oxNew('fcafterbuycatalog');

        $oCatalog->CatalogID = $oCategory->oxcategories__fcafterbuy_catalogid->value;
        $oCatalog->CatalogName = $oCategory->oxcategories__oxtitle->value;
        $oCatalog->CatalogDescription = $oCategory->oxcategories__oxlongdesc->value;
        $oCatalog->AdditionalURL = $oCategory->oxcategories__oxextlink->value;
        $oCatalog->Level = '';
        $oCatalog->Position = $oCategory->oxcategories__oxsort->value;
        $oCatalog->AdditionalText = $oCategory->oxcategories__oxdesc->value;
        $oCatalog->ShowCatalog = (string) $oCategory->oxcategories__oxactive->value;
        $oCatalog->Picture = (string) $oCategory->getThumbUrl();
        $oCatalog->MouseOverPicture = (string) $oCategory->getThumbUrl();

        return $oCatalog;
    }

    /**
     * Returns list of oxid variants
     *
     * @param $oArticle
     * @return array
     */
    protected function _fcGetVariants($oArticle) {
        if ($this->_aOxidVariants === null) {
            $aVariantIds = $oArticle->getVariantIds();
            $aVariants = null;

            foreach ($aVariantIds as $sVariantId) {
                $oVariant = $this->_fcGetOxidArticle($sVariantId);
                $aVariants[] = $oVariant;
            }

            $this->_aOxidVariants = $aVariants;
        }

        return $this->_aOxidVariants;
    }

    /**
     * Fetching variants of product and send each to AB
     *
     * @param string $sArticleOxid
     * @return void
     */
    protected function _fcAddVariants($sArticleOxid) {
        $oAfterbuyApi = $this->_fcGetAfterbuyApi();

        $oArticle = $this->_fcGetOxidArticle($sArticleOxid);
        if (!$oArticle) return;

        $aVariantIds = $oArticle->getVariantIds();

        foreach ($aVariantIds as $sVariantArticleOxid) {
            $oArt = $this->_fcGetAfterbuyArticleByOxid($sVariantArticleOxid);
            if (!$oArt) continue;

            $sResponse = $oAfterbuyApi->updateArticleToAfterbuy($oArt);
            $this->_fcValidateCallStatus($sResponse);
            $this->_fcAddAfterbuyIdToArticle($sVariantArticleOxid, $sResponse);
        }
    }

    /**
     * Validating call status
     *
     * @param $sResponse
     * @return void
     */
    protected function _fcValidateCallStatus($sResponse) {
        $oXml = simplexml_load_string($sResponse);
        $sCallStatus = (string) $oXml->CallStatus;
        switch ($sCallStatus) {
            case 'Warning':
                $sMessage =
                    "WARNING: ".
                    (string)$oXml->Result->WarningList->Warning->WarningLongDescription;
                $this->oApiLogger->fcWriteLog($sMessage,2);
                break;
        }
    }

    /**
     * Adds afterbuy id to article dataset
     *
     * @param $sArticleOxid
     * @param $sResponse
     * @return void
     */
    protected function _fcAddAfterbuyIdToArticle($sArticleOxid, $sResponse) {
        $oXml = simplexml_load_string($sResponse);
        $sProductId = (string) $oXml->Result->NewProducts->NewProduct->ProductID;
        if ($sProductId) {
            $oArticle = oxNew('oxarticle');
            if ($oArticle->load($sArticleOxid)) {
                $oArticle->oxarticles__fcafterbuyid = new oxField($sProductId);
                $oArticle->save();
            }
        }
    }

    /**
     * Returns oxArticle object or false
     *
     * @param $sArticleOxid
     * @return mixed object|bool
     */
    protected function _fcGetOxidArticle($sArticleOxid)
    {
        $oArticle = oxNew('oxarticle');
        if (!$oArticle->load($sArticleOxid))  {
            $this->oDefaultLogger->fcWriteLog("ERROR: Could not load article object with ID:".$sArticleOxid, 1);
            return false;
        }

        $this->oDefaultLogger->fcWriteLog("DEBUG: Loaded OXID article object with ID:".$sArticleOxid, 4);
        $this->oDefaultLogger->fcWriteLog(
            "DEBUG: Existing AfterbuyID is:".
            $oArticle->oxarticles__fcafterbuyid->value,
            4
        );

        return $oArticle;
    }

    /**
     * Takes an oxid of an article and creates an afterbuy article object of it
     *
     * @param $sArticleOxid
     * @return mixed object|bool
     */
    protected function _fcGetAfterbuyArticleByOxid($sArticleOxid)
    {
        $oArticle = $this->_fcGetOxidArticle($sArticleOxid);
        if (!$oArticle) return false;

        $this->_aOxidVariants = null;
        $oAfterbuyArticle = $this->_fcGetAfterbuyArticle();
        $oAfterbuyArticle = $this->_fcAddArticleValues($oAfterbuyArticle, $oArticle);
        $oAfterbuyArticle = $this->_fcAddCatalogValues($oAfterbuyArticle, $oArticle);
        $oAfterbuyArticle = $this->_fcAddVariantValues($oAfterbuyArticle, $oArticle);
        $oAfterbuyArticle = $this->_fcAddEbayVariations($oAfterbuyArticle, $oArticle);
        $oAfterbuyArticle = $this->_fcAddAttributeValues($oAfterbuyArticle, $oArticle);
        $oAfterbuyArticle = $this->_fcAddManufacturerValues($oAfterbuyArticle, $oArticle);

        return $oAfterbuyArticle;
    }

    /**
     * Adding catalog nodes of this product
     *
     * @param $oAfterbuyArticle
     * @param $oArticle
     * @return object
     */
    protected function _fcAddCatalogValues($oAfterbuyArticle, $oArticle)
    {
        $oCategory = $this->_fcGetReloadedCategory($oArticle);


        if (!$oCategory) return $oAfterbuyArticle;
        $aCategories = $this->_fcFetchArticleCategoryValues($oCategory);
        foreach ($aCategories as $aCategory) {
            $oAddCatalog = $this->_fcGetAddCatalog();
            $oAddCatalog->CatalogID = $aCategory['CatalogID'];
            $oAddCatalog->CatalogName = $aCategory['CatalogName'];
            $oAddCatalog->CatalogLevel = $aCategory['CatalogLevel'];
            $oAfterbuyArticle->AddCatalogs[] = $oAddCatalog;
        }

        return $oAfterbuyArticle;
    }

    /**
     * Method makes sure that no caching is used here while
     * loading a category
     *
     * @param $oArticle
     * @return object
     * @todo: if this code is still here, it's a performance breaking thing and means caching of std oxid is making trouble
     */
    protected function _fcGetReloadedCategory($oArticle)
    {
        $oCategory = $oArticle->getCategory();

        if($oCategory) {
            $sCategoryId = $oCategory->getId();
            $oCategory = oxNew('oxCategory');
            $oCategory->load($sCategoryId);
        }

        return $oCategory;
    }

    /**
     * Gets depth in catgory tree and returns array with needed node
     * information
     *
     * @param $oCategory
     * @return array
     */
    protected function _fcFetchArticleCategoryValues($oCategory)
    {
        $iLevel = 0;
        $aTmpCategories = $aCategories = [];

        while ($oCategory->getParentCategory()) {
            $iLevel++;
            $aTmpCategories[] = $oCategory;
            $oCategory = $oCategory->getParentCategory();
        }

        if ($oCategory->oxcategories__oxparentid->value && $oCategory->oxcategories__oxparentid->value === 'oxrootid') {
            // after the loop above, the category directly below root won't load. Adding it here.
            $aTmpCategories[] = $oCategory;
        }

        foreach ($aTmpCategories as $oCategory) {

            $aCategories[] = array(
                'CatalogID' => $oCategory->oxcategories__fcafterbuy_catalogid->value,
                'CatalogName' => $oCategory->getTitle(),
                'CatalogLevel' => $iLevel,
            );

            $iLevel--;
        }

        return $aCategories;
    }

    /**
     * @param object $oArticle
     * @return array
     */
    protected function _getAttributesForArticle($oArticle)
    {
        $sArticleId = $oArticle->getId();
        $sParentId = $oArticle->getParentId();

        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);

        $sAttrViewName = getViewName('oxattribute');
        $sViewName = getViewName('oxobject2attribute');

        $sSelect = "select {$sAttrViewName}.`oxid`, {$sAttrViewName}.`oxtitle`, o2a.`oxvalue` from {$sViewName} as o2a ";
        $sSelect .= "left join {$sAttrViewName} on {$sAttrViewName}.oxid = o2a.oxattrid ";
        $sSelect .= "where o2a.oxobjectid = '%s' and o2a.oxvalue != '' ";
        $sSelect .= "order by o2a.oxpos, {$sAttrViewName}.oxpos";

        $aAttributes = $oDb->getAll(sprintf($sSelect, $sArticleId));

        if ($sParentId) {
            $aParentAttributes = $oDb->getAll(sprintf($sSelect, $sParentId));
            $aAttributes = $this->_mergeAttributes($aAttributes, $aParentAttributes);
        }
        return $aAttributes;
    }

    /**
     * Add attributes of product to afterbuy article
     *
     * @param $oAfterbuyArticle
     * @param $oArticle
     * @return object
     */
    protected function _fcAddAttributeValues($oAfterbuyArticle, $oArticle)
    {
        $aAttributes = $this->_getAttributesForArticle($oArticle);
        $this->oDefaultLogger->fcWriteLog(
            "DEBUG: Loaded Attributes of article object with ID:".
            $oArticle->getId(),
            4
        );
        $this->oDefaultLogger->fcWriteLog(
            "DEBUG: Fetched attributes:".
            print_r($aAttributes,true),
            4
        );

        $iPos = 1;
        foreach ($aAttributes as $aAttribute) {
            $oAttribute = oxNew('oxattribute');
            $oAttribute->load($aAttribute['oxid']); // Load full attribute

            $sAttributeName = $oAttribute->oxattribute__oxtitle->value;
            $sAttributeValue = $aAttribute['oxvalue'];
            $sAfterbuyFeatureId = $oAttribute->oxattribute__fcafterbuyfeatureid->value;

            $oAfterbuyAddAttribute = $this->_fcGetAddAttribute();
            $oAfterbuyAddAttribute->AttributName = $sAttributeName;
            $oAfterbuyAddAttribute->AttributValue = $sAttributeValue;
            $oAfterbuyAddAttribute->AttributPosition = (string) $iPos;
            $oAfterbuyAddAttribute->FeatureId = $sAfterbuyFeatureId;
            $oAfterbuyArticle->AddAttributes[] = $oAfterbuyAddAttribute;
            $iPos++;
        }

        return $oAfterbuyArticle;
    }

    /**
     * Returns a fresh instance of AddAttribute object
     *
     * @param void
     * @return object
     */
    protected function _fcGetAddAttribute()
    {
        $oAddAttribute = oxNew('fcafterbuyaddattribute');

        return $oAddAttribute;
    }

    /**
     * Returns a fresh instance of AddCatalog object
     *
     * @param void
     * @return object
     */
    protected function _fcGetAddCatalog()
    {
        $oAddCatalog = oxNew('fcafterbuyaddcatalog');

        return $oAddCatalog;
    }

    /**
     * Add all informations relevant for variation set assignments
     *
     * @param $oAfterbuyArticle
     * @param $oArticle
     * @return object
     */
    protected function _fcAddVariantValues($oAfterbuyArticle, $oArticle)
    {
        $oAfterbuyArticle =
            $this->_fcAddVariantBaseValues($oAfterbuyArticle, $oArticle);

        $aVariants = $this->_fcGetVariants($oArticle);

        $blHasVariants = (
            is_array($aVariants) &&
            count($aVariants) > 0
        );

        if (!$blHasVariants)  return $oAfterbuyArticle;

        $iPos = 1;
        foreach ($aVariants as $oVariant) {
            $oAfterbuyArticle = $this->_fcGetAddBaseProduct(
                $oAfterbuyArticle,
                $oVariant,
                $iPos
            );

            $iPos++;
        }

        return $oAfterbuyArticle;
    }

    /**
     * Add ebay variations
     *
     * @param $oAfterbuyArticle
     * @param $oArticle
     * @return object
     */
    protected function _fcAddEbayVariations($oAfterbuyArticle, $oArticle)
    {
        $aVariations = $this->_fcGetVariantVariations($oArticle);
        $blHasVariations = (
            is_array($aVariations) &&
            count($aVariations) > 0
        );

        if (!$blHasVariations)  return $oAfterbuyArticle;

        $aEbayVariations = array();
        foreach ($aVariations as $sVariationName=>$aVariationValues) {
                $oEbayVariation =
                    $this->_fcGetUseeBayVariation($sVariationName, $aVariationValues);
                $aEbayVariations[] = $oEbayVariation;
        }

        $oAfterbuyArticle->UseeBayVariations = $aEbayVariations;

        return $oAfterbuyArticle;
    }

    /**
     * Returns array which is sorted by variation names and
     * its belonging values
     *
     * @param $oArticle
     * @return array
     */
    protected function _fcGetVariantVariations($oArticle)
    {
        $aVariants = $this->_fcGetVariants($oArticle);
        $blHasVariants = (
            is_array($aVariants) &&
            count($aVariants) > 0
        );

        if (!$blHasVariants) return array();

        $aVariationNames = $this->_fcFetchVariationNames($oArticle);
        $aVariations = array();

        foreach ($aVariants as $oVariant) {
            $aVariations = $this->_fcAddVariationValues(
                $aVariations,
                $aVariationNames,
                $oVariant
            );
        }

        return $aVariations;

    }

    /**
     * Adding variant to variations dataset
     *
     * @param $aVariations
     * @param $aVariationNames
     * @param $oVariant
     * @return mixed
     */
    protected function _fcAddVariationValues($aVariations, $aVariationNames, $oVariant)
    {
        foreach ($aVariationNames as $iIndex=>$sVariationName) {
            $aVarSelects = $this->_fcFetchVariationValues($oVariant);
            $sVarSelect = $aVarSelects[$iIndex];

            $aVariation = array();
            $aVariation['anr'] = $oVariant->oxarticles__fcafterbuyid->value;
            $aVariation['value'] = $sVarSelect;
            $aVariation['pos'] = count((array) $aVariations[$sVariationName]);
            $aVariation['picurl'] = $oVariant->getPictureUrl();

            $aVariations[urlencode($sVariationName)][] = $aVariation;
        }

        return $aVariations;
    }

    /**
     * Returns array with index of variation values
     *
     * @param $oArticle
     * @return array
     */
    protected function _fcFetchVariationValues($oArticle)
    {
        $sVarSelect = $oArticle->oxarticles__oxvarselect->value;
        $aVarSelects = explode('|', $sVarSelect);
        $aVarSelects = array_map('trim', $aVarSelects);

        return $aVarSelects;
    }

    /**
     * Returns array with index of variation names
     *
     * @param $oArticle
     * @return array
     */
    protected function _fcFetchVariationNames($oArticle)
    {
        $sVarNames = $oArticle->oxarticles__oxvarname->value;
        $aVarNames = explode('|', $sVarNames);
        $aVarNames = array_map('trim', $aVarNames);

        return $aVarNames;
    }

    /**
     * Adds variant values as afterbuy ebayvariation
     *
     * @param string $sVariationName
     * @param array $aVariationValues
     * @return object
     */
    protected function _fcGetUseeBayVariation($sVariationName, $aVariationValues)
    {
        $oAfterbuyUseeBayVariation =
            $this->_fcGetUseeBayVariationObject();

        $oAfterbuyUseeBayVariation->VariationName = urldecode($sVariationName);
        foreach ($aVariationValues as $aVariationEntry) {
            $oEbayVariationValue =
                $this->_fcGetEbayVariationValue($aVariationEntry);

            $aEbayVariationValues[] = $oEbayVariationValue;
        }

        $oAfterbuyUseeBayVariation->VariationValues = $aEbayVariationValues;

        return $oAfterbuyUseeBayVariation;
    }

    /**
     * Adds oxid variant as afterbuy addbaseproduct
     *
     * @param $oAfterbuyArticle
     * @param $oOxidVariantArticle
     * @param $iPos
     * @return object
     */
    protected function _fcGetAddBaseProduct($oAfterbuyArticle, $oOxidVariantArticle, $iPos)
    {
        $oAfterbuyAddBaseProduct =
            $this->_fcGetAddBaseProductObject();
        $oAfterbuyAddBaseProduct =
            $this->_fcAssignVariantValues(
                $oAfterbuyAddBaseProduct,
                $oOxidVariantArticle,
                $iPos
            );

        $oAfterbuyArticle->AddBaseProducts[] =
            $oAfterbuyAddBaseProduct;

        return $oAfterbuyArticle;
    }

    /**
     * Assign variant values to addbase-product
     *
     * @param $oAfterbuyAddBaseProduct
     * @param $oOxidVariantArticle
     * @return object
     */
    protected function _fcAssignVariantValues($oAfterbuyAddBaseProduct, $oOxidVariantArticle, $iPos)
    {
        $sVariantLabel =
            $oOxidVariantArticle->oxarticles__oxtitle->value.
            " ".
            $oOxidVariantArticle->oxarticles__oxvarselect->value;

        $iStock = $oOxidVariantArticle->oxarticles__oxstock->value;
        $sAfterbuyProductId =
            $oOxidVariantArticle->oxarticles__fcafterbuyid->value;

        $oAfterbuyAddBaseProduct->ProductID = $sAfterbuyProductId;
        $oAfterbuyAddBaseProduct->ProductLabel = $sVariantLabel;
        $oAfterbuyAddBaseProduct->ProductPos = (string) $iPos;
        $oAfterbuyAddBaseProduct->ProductQuantity = (string) $iStock;

        return $oAfterbuyAddBaseProduct;
    }

    /**
     * Assign variant values to ebayvariation
     *
     * @param string $sValueEntry
     * @return object
     */
    protected function _fcGetEbayVariationValue($aValueEntry)
    {
        $oEbayVariationValues =  $this->_fcGetEbayVariationValuesObject();

        $oEbayVariationValues->ValidForProdID = $aValueEntry['anr'];
        $oEbayVariationValues->VariationValue = $aValueEntry['value'];
        $oEbayVariationValues->VariationPos = $aValueEntry['pos'];
        $oEbayVariationValues->VariationPicURL = $aValueEntry['picurl'];
        return $oEbayVariationValues;
    }

    /**
     * Returns fresh instance of AddBaseProduct
     *
     * @param void
     * @return mixed
     */
    protected function _fcGetAddBaseProductObject()
    {
        $oAddBaseProduct = oxNew('fcafterbuyaddbaseproduct');

        return $oAddBaseProduct;
    }

    /**
     * Returns fresh instance of UseeBayVariation
     *
     * @param void
     * @return mixed
     */
    protected function _fcGetUseeBayVariationObject()
    {
        $oUseeBayVariation = oxNew('fcafterbuyuseebayvariation');

        return $oUseeBayVariation;
    }

    /**
     * Returns fresh instance of ebay variation values object
     *
     * @param void
     * @return mixed
     */
    protected function _fcGetEbayVariationValuesObject()
    {
        $oEbayVariationValues = oxNew('fcafterbuyebayvariationvalue');

        return $oEbayVariationValues;
    }

    /**
     * Adds nessessary flag for identification of article
     *
     * @param $oAfterbuyArticle
     * @param $oArticle
     * @return object
     */
    protected function _fcAddVariantBaseValues($oAfterbuyArticle, $oArticle)
    {
        $aVariantIds = $oArticle->getVariantIds();
        $blIsParent = (bool) count($aVariantIds);

        $oAfterbuyArticle->BaseProductType = ($blIsParent) ? 1 : 0;

        return $oAfterbuyArticle;
    }

    /**
     * Adds manufacturer related values to article
     *
     * @param $oAfterbuyArticle
     * @param $oArticle
     * @return object
     */
    protected function _fcAddManufacturerValues($oAfterbuyArticle, $oArticle) {
        $oManufacturer = $oArticle->getManufacturer();
        if ($oManufacturer) {
            $oAfterbuyArticle->ProductBrand = $oManufacturer->getTitle();
        }

        return $oAfterbuyArticle;
    }

    /**
     * Adds common article values to afterbuy article
     *
     * @param $oAfterbuyArticle
     * @param $oArticle
     * @return object
     */
    protected function _fcAddArticleValues($oAfterbuyArticle, $oArticle) {
        $oAfterbuyArticle->Name = $this->_fcGetArticleName($oArticle);
        $oAfterbuyArticle->Description = $oArticle->getLongDesc();
        $oAfterbuyArticle->SellingPrice = $this->_fcGetSellingPrice($oArticle); #0102749
        $oAfterbuyArticle->Actor = !empty($oArticle->oxarticles__actor->value) ? $oArticle->oxarticles__actor->value : ''; #0102749
        $oAfterbuyArticle->TaxRate = $oArticle->getArticleVat();
        $oAfterbuyArticle->ItemSize = $oArticle->getSize();
        $oAfterbuyArticle->CanonicalUrl = $oArticle->getMainLink();

        $oAfterbuyArticle = $this->_fcAddTranslatedValues($oAfterbuyArticle, $oArticle);
        $oAfterbuyArticle = $this->_fcAddPictures($oAfterbuyArticle, $oArticle);

        return $oAfterbuyArticle;
    }

    /**
     * Get selling price
     *
     * @param $oArticle
     * @return double
     */
    protected function _fcGetSellingPrice($oArticle)
    {
        $oPrice = $oArticle->getPrice();
        $oPrice->setPrice($oArticle->oxarticles__tc_ebay_price->value);
        return $oPrice->getBruttoPrice();
    }

    /**
     * Returns article title plus varselect
     *
     * @param $oArticle
     * @return string
     */
    protected function _fcGetArticleName($oArticle)
    {
        $sName = $oArticle->oxarticles__oxtitle->value;
        $sVarselect = $oArticle->oxarticles__oxvarselect->value;

        if ($sVarselect) {
            $sName = $sName." ".$sVarselect;
        }
        $sName = htmlspecialchars_decode($sName, ENT_QUOTES);

        return $sName;
    }

    /**
     * Adding picture information
     *
     * @param $oAfterbuyArticle
     * @param $oArticle
     * @return object
     */
    protected function _fcAddPictures($oAfterbuyArticle, $oArticle) {
        // alt tag
        $sArticleTitle = $oArticle->oxarticles__oxtitle->value;

        // pictures
        $oAfterbuyArticle->ImageSmallURL = $oArticle->getThumbnailUrl(true);
        $oAfterbuyArticle->ImageLargeURL = $oArticle->getZoomPictureUrl(1);

        // gallery
        $iPicNr = 1;
        for($iIndex=1;$iIndex<=12;$iIndex++) {
            $sFieldValue = $oArticle->getFieldData("oxpic{$iIndex}");
            if(!$sFieldValue) continue;

            $sVarName_PicNr = "ProductPicture_Nr_".$iPicNr;
            $sVarName_PicUrl = "ProductPicture_Url_".$iPicNr;
            $sVarName_PicAltText = "ProductPicture_AltText_".$iPicNr;

            $sPictureUrl = $oArticle->getPictureUrl($iIndex);

            $oAfterbuyArticle->$sVarName_PicNr = $iPicNr;
            $oAfterbuyArticle->$sVarName_PicUrl = $sPictureUrl;
            $oAfterbuyArticle->$sVarName_PicAltText = $sArticleTitle;
            $iPicNr++;
        }

        return $oAfterbuyArticle;
    }

    /**
     * Translates demanded Nodes to source in shop
     *
     * @param $oAfterbuyArticle
     * @param $oArticle
     * @return mixed
     */
    protected function _fcAddTranslatedValues($oAfterbuyArticle, $oArticle)
    {
        // standard values will be iterated through translation array
        foreach ($this->_aAfterbuy2OxidDictionary as $sAfterbuyName=>$sOxidNamesString) {
            $sOxidName = $this->_fcFetchOxidName($oArticle, $sOxidNamesString);

            $oAfterbuyArticle->$sAfterbuyName = $oArticle->$sOxidName->value;
        }

        return $oAfterbuyArticle;
    }

    /**
     * Fetching oxid name of articlce fields which containing
     * a value
     *
     * @param $oArticle
     * @param $sOxidNamesString
     * @return string
     */
    protected function _fcFetchOxidName($oArticle, $sOxidNamesString)
    {
        $aOxidNames = explode('|', $sOxidNamesString);
        $sOxidName = (string) $aOxidNames[0];

        foreach ($aOxidNames as $sCurrentOxidName) {
            $sOxidName = (string) $sCurrentOxidName;

            $blValueExists =
                (isset($oArticle->$sCurrentOxidName->value)) ?
                    (bool) $oArticle->$sCurrentOxidName->value :
                    false;
            if ($blValueExists) break;
        }

        return $sOxidName;
    }


    /**
     * Returns an array of article ids which have been flagged to be an afterbuy article
     *
     * @param void
     * @return array
     */
    protected function _fcGetAffectedArticleIds() {
        $aArticleIds = array();
        $oConfig = $this->getConfig();
        $blFcAfterbuyExportAll =
            $oConfig->getConfigParam('blFcAfterbuyExportAll');

        $sWhereConditions = "";
        if (!$blFcAfterbuyExportAll) {
            $sWhereConditions .= " AND oaab.FCAFTERBUYACTIVE='1' ";
        }
        $sWhereConditions .= " AND oa.TC_EBAY_PRICE > 0 "; #0102749

        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $sQuery = "
            SELECT oa.OXID 
            FROM ".getViewName('oxarticles')." oa
            LEFT JOIN 
                oxarticles_afterbuy as oaab ON (oa.OXID=oaab.OXID)
            WHERE oa.OXPARENTID='' ".
            $sWhereConditions;

        $aRows = $oDb->getAll($sQuery);
        foreach ($aRows as $aRow) {
            $aArticleIds[] = $aRow['OXID'];
        }

        return $aArticleIds;
    }
}
