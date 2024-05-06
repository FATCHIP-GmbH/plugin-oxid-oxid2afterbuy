<?php

/**
 * Created by PhpStorm.
 * User: andre
 * Date: 07.09.17
 * Time: 12:39
 */
class fco2abase extends oxBase {

    /**
     * @var string
     */
    protected $sAfterbuyShopInterfaceUrl = "https://api.afterbuy.de/afterbuy/ShopInterface.aspx";

    /**
     * @var string
     */
    protected $sAfterbuyShopInterfaceUTF8Url = "https://api.afterbuy.de/afterbuy/ShopInterfaceUTF8.aspx";

    /**
     * @var string
     */
    protected $sAfterbuyInterfaceUrl = "https://api.afterbuy.de/afterbuy/ABInterface.aspx";

    /**
     * Ident for oxid counter
     * @var string
     */
    protected $_sCounterIdent = 'fcAfterbuyLastOrder';

    /**
     * Instance of afterbuy api
     *
     * @var object
     */
    protected $_oAfterbuyApi = null;

    /**
     * Default logger object
     * @var object
     */
    protected $oDefaultLogger;

    /**
     * Default logger object
     * @var object
     */
    protected $oApiLogger;

    /**
     * Afterbuy settings
     *
     * @var array
     */
    protected $_aAfterbuyConfig = null;

    protected $_aAllowedExecution = array(
        '0' => array(
            'artexport',
            'orderimport',
            'statusexport',
        ),
        '1' => array(
            'artimport',
            'stockimport',
            'statusimport',
            'orderexport',
        ),
    );

    /**
     * fco2abase constructor.
     * initialize loglevel
     */
    public function __construct() {
        parent::__construct();
        $this->oDefaultLogger = oxNew('fco2alogger', 'fco2a_default.log');
        $this->oApiLogger = oxNew('fco2alogger', 'fco2a_api.log');
    }

    /**
     * Checks if job execution is allowed by configuration (Leading System)
     *
     * @param $sJobIdent
     * @return bool
     */
    public function fcJobExecutionAllowed($sJobIdent) {
        $oConfig = $this->getConfig();

        $sLeadSystem =
            (string) $oConfig->getConfigParam('sFcAfterbuyLeadSystem');
        $aAllowedJobs = $this->_aAllowedExecution[$sLeadSystem];
        $blAllowed = (bool) in_array($sJobIdent, $aAllowedJobs);

        return $blAllowed;
    }

    /**
     * Returns needed configuration for instantiate afterbuy api object
     *
     * @param void
     * @return array
     */
    protected function _fcGetAfterbuyConfigArray() {
        if ($this->_aAfterbuyConfig === null) {
            $oConfig = $this->getConfig();
            $aConfig = array(
                'afterbuyShopInterfaceBaseUrl' => ($oConfig->getConfigParam('blFcAfterbuyExportUTF8Orders') === true) ? $this->sAfterbuyShopInterfaceUTF8Url : $this->sAfterbuyShopInterfaceUrl,
                'afterbuyAbiUrl' =>
                    $this->sAfterbuyInterfaceUrl,
                'afterbuyAccountToken' =>
                    $oConfig->getConfigParam('sFcAfterbuyAccountToken'),
                'afterbuyPartnerToken' =>
                    $oConfig->getConfigParam('sFcAfterbuyPartnerToken'),
                'afterbuyUsername' =>
                    $oConfig->getConfigParam('sFcAfterbuyUsername'),
                'afterbuyUserPassword' =>
                    $oConfig->getConfigParam('sFcAfterbuyUserPassword'),
                'logLevel' =>
                    $oConfig->getConfigParam('iFcAfterbuyLogLevel'),
                'lastOrderId' =>
                    $this->_fcGetLastOrderId(),
            );

            $this->_aAfterbuyConfig = $aConfig;
        }

        return $this->_aAfterbuyConfig;
    }

    /**
     * Returns current orderid from oxCounter
     *
     * @param void
     * @return string
     */
    protected function _fcGetLastOrderId() {
        $oCounter = oxNew('oxCounter');
        $sLastOrderId = $oCounter->fcGetCurrent($this->_sCounterIdent);

        return (string) $sLastOrderId;
    }

    /**
     * Returns german formatted date for offered different datetime format
     *
     * @param $sDateString
     * @return string
     */
    protected function _fcGetGermanDate($sDateString) {
        $sReturn =
            $this->_fcTransformDate($sDateString,'d.m.Y');

        return $sReturn;
    }

    /**
     * Returns given input date(-time)string in mysql
     * datetime format
     *
     * @param $sDateString
     * @return string
     */
    protected function _fcGetDbDateTime($sDateString) {
        $sReturn =
            $this->_fcTransformDate($sDateString,'Y-m-d H:i:s');

        return $sReturn;
    }

    /**
     * Transformes date
     *
     * @param $sDateString
     * @param $sPattern
     * @return string
     */
    protected function _fcTransformDate($sDateString, $sPattern, $blKeepDateStringOnError=true)
    {
        $iTime = strtotime($sDateString);
        $mReturn = '';
        if ($iTime) {
            $mReturn = date($sPattern, $iTime);
        }

        $blReturnIncoming = (
            $mReturn === false &&
            $blKeepDateStringOnError
        );

        $mReturn = ($blReturnIncoming) ? $sDateString: $mReturn;

        return $mReturn;
    }

    /**
     * Returns afterbuy api object
     *
     * @param void
     * @return object
     */
    protected function _fcGetAfterbuyApi() {
        if ($this->_oAfterbuyApi === null) {
            $aConfig = $this->_fcGetAfterbuyConfigArray();
            $oAfterbuyApi = oxNew("fcafterbuyapi",$aConfig);
            $this->_oAfterbuyApi = $oAfterbuyApi;
        }

        return $this->_oAfterbuyApi;
    }

    /**
     * Returns a new afterbuy order status object
     *
     * @param void
     * @return object
     */
    protected function _fcGetAfterbuyStatus() {
        $oAfterbuyStatus = oxNew('fcafterbuyorderstatus');
        return $oAfterbuyStatus;
    }

    /**
     * Returns a new afterbuy order object
     *
     * @param void
     * @return object
     */
    protected function _fcGetAfterbuyOrder() {
        $oAfterbuyOrder = oxNew("fcafterbuyorder");

        return $oAfterbuyOrder;
    }

    /**
     * Returns an afterbuy article object
     *
     * @param void
     * @return object fcafterbuyart
     */
    protected function _fcGetAfterbuyArticle() {
        $oAfterbuyArticle = oxNew("fcafterbuyart");

        $this->oDefaultLogger->fcWriteLog(
            "DEBUG: Created Afterbuy Object:".
            print_r($oAfterbuyArticle,true),
            4
        );

        return $oAfterbuyArticle;
    }

    /**
     * Rerturns true/false depending on response of an API-Call
     *
     * @param $sResponse
     * @return bool
     */
    protected function _fcCheckApiCallSuccess($sResponse) {
        $blReturn = false;
        if (strpos($sResponse, '<CallStatus>Success</CallStatus>') !== false) {
            $blReturn = true;
        }

        return $blReturn;
    }

    /**
     * Sets last check date of this order to now
     *
     * @param $sOrderOxid
     * @return void
     */
    protected function _fcSetLastCheckedDate($sOrderOxid) {
        $oDb = oxDb::getDb();
        $sQuery = "
            UPDATE 
                oxorder_afterbuy 
            SET 
                FCAFTERBUY_LASTCHECKED=NOW() 
            WHERE 
                OXID=".$oDb->quote($sOrderOxid)." LIMIT 1";
        $oDb->execute($sQuery);
    }


}
