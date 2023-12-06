<?php

class fco2aartexportstock extends fco2aartexport
{
	protected function _fcGetAffectedArticleIds()
	{
		$aArticleIds = array();
		$oConfig = $this->getConfig();
        
		$blFcAfterbuyExportAll = $oConfig->getConfigParam( 'blFcAfterbuyExportAll' );
		$sFcAfterbuyExportStockTime = $oConfig->getConfigParam( 'sFcAfterbuyExportStockTime' );

		$oUtilsDate = oxRegistry::get( 'oxUtilsDate' );
		$sTime = $oUtilsDate->getTime();
        
        $sSaveTime = date( 'Y-m-d H:i:s', $sTime );

		if ( empty( $sFcAfterbuyExportStockTime ) )
			$sFromDate = date( 'Y-m-d H:i:s', ( $sTime - 84600 ) );

		if ( !empty( $sFcAfterbuyExportStockTime ) )
		{
			$sTime = strtotime( $sFcAfterbuyExportStockTime );
			$sFromDate = date( 'Y-m-d H:i:s', $sTime );
		}

		$sWhereConditions = "";
		if ( !$blFcAfterbuyExportAll )
		{
			$sWhereConditions .= " AND oaab.FCAFTERBUYACTIVE='1' ";
		}

		$oDb = oxDb::getDb( oxDb::FETCH_MODE_ASSOC );
		$sQuery = "
            SELECT oa.OXID 
            FROM " . getViewName( 'oxarticles' ) . " oa
            LEFT JOIN 
                oxarticles_afterbuy as oaab ON (oa.OXID=oaab.OXID)
            WHERE oa.OXPARENTID = '' 
            " . $sWhereConditions . "
            AND oa.OXTIMESTAMP >= '" . $sFromDate . "'";

		$aRows = $oDb->getAll( $sQuery );
		foreach ( $aRows as $aRow )
		{
			$aArticleIds[] = $aRow['OXID'];
		}
        
        $oConfig->saveShopConfVar( 'str', 'sFcAfterbuyExportStockTime', $sSaveTime );

		return $aArticleIds;
	}
}
