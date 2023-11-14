<?php
/**
 * Created by PhpStorm.
 * User: andrefatchip
 * Date: 12.11.18
 * Time: 23:33
 */

class fco2a_events
{
    public static $sQueryCreateABOrderTable = "
        CREATE TABLE IF NOT EXISTS `oxorder_afterbuy` (
          `OXID` char(32) COLLATE latin1_general_ci NOT NULL,
          `FCAFTERBUY_AID` VARCHAR(255) not null,
          `FCAFTERBUY_VID` VARCHAR(255) not null,
          `FCAFTERBUY_UID` VARCHAR(255) not null,
          `FCAFTERBUY_CUSTOMNR` VARCHAR(255) not null,
          `FCAFTERBUY_ECUSTOMNR` VARCHAR(255) not null,
          `FCAFTERBUY_LASTCHECKED` DATETIME not null,
          `FCAFTERBUY_FULFILLED` TINYINT(1) not null DEFAULT 0,
          `FCAFTERBUY_FULFILLEDEXT` TINYINT(1) not null DEFAULT 0,
          PRIMARY KEY (`OXID`)
        ) ENGINE=MyISAM COLLATE=latin1_general_ci;
    ";

    public static $sQueryCreateABArticleTable = "
        CREATE TABLE IF NOT EXISTS `oxarticles_afterbuy` (
            `OXID` char(32) COLLATE latin1_general_ci NOT NULL,
            `FCAFTERBUYACTIVE` TINYINT(1) not null default 0,
            `FCAFTERBUYID` VARCHAR(255) not null,
          PRIMARY KEY (`OXID`)
        ) ENGINE=MyISAM;
    ";

    public static $sQueryCreateABUserTable = "
        CREATE TABLE IF NOT EXISTS `oxuser_afterbuy` (
            `OXID` char(32) COLLATE latin1_general_ci NOT NULL,
            `FCAFTERBUY_USERID` VARCHAR(255) not null,    
          PRIMARY KEY (`OXID`)
        ) ENGINE=MyISAM;
    ";

    public static $sQueryCreateABCategoriesTable = "
        CREATE TABLE `oxcategories_afterbuy` (
          `OXID` char(32) COLLATE latin1_general_ci NOT NULL,
          `FCAFTERBUY_CATALOGID` int(11) NOT NULL,
          PRIMARY KEY (`OXID`)
        ) ENGINE=MyISAM;
    ";

    public static $sQueryCreateABPaymentAssignment = "
        CREATE TABLE IF NOT EXISTS `fcafterbuypayments` (
          `OXPAYMENTID` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
          `FCAFTERBUYPAYMENTID` int(11) NOT NULL,
          PRIMARY KEY (`OXPAYMENTID`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ";

    public static $sQueryCreateABCountry = "
        CREATE TABLE IF NOT EXISTS `fcafterbuycountry` (
          `OXID` char(32) COLLATE latin1_general_ci NOT NULL,
          `FCCARPLATE` varchar(4) COLLATE latin1_general_ci NOT NULL,
          PRIMARY KEY (`OXID`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;    
    ";

    public static $sQueryUpdateOxattributes = "ALTER TABLE `oxattribute` ADD `FCAFTERBUYFEATUREID` INT(11) NULL;";

    public static $sQueryInsertCountryAssignments = "
        REPLACE INTO `fcafterbuycountry` (`OXID`, `FCCARPLATE`) VALUES
          ('a7c40f631fc920687.20179984', 'D'),
          ('a7c40f6320aeb2ec2.72885259', 'A'),
          ('a7c40f6321c6f6109.43859248', 'CH'),
          ('a7c40f6322d842ae3.83331920', 'FL'),
          ('a7c40f6323c4bfb36.59919433', 'I'),
          ('a7c40f63264309e05.58576680', 'L'),
          ('a7c40f63272a57296.32117580', 'F'),
          ('a7c40f632848c5217.53322339', 'S'),
          ('a7c40f63293c19d65.37472814', 'FIN'),
          ('a7c40f632be4237c2.48517912', 'IRL'),
          ('a7c40f632cdd63c52.64272623', 'NL'),
          ('a7c40f632e04633c9.47194042', 'B'),
          ('a7c40f632f65bd8e2.84963272', 'P'),
          ('a7c40f633038cd578.22975442', 'E'),
          ('a7c40f633114e8fc6.25257477', 'GR'),
          ('8f241f11095306451.36998225', 'AFG'),
          ('8f241f110953265a5.25286134', 'AL'),
          ('8f241f1109533b943.50287900', 'DZ'),
          ('8f241f11095363464.89657222', 'AND'),
          ('8f241f11095377d33.28678901', 'ANG'),
          ('8f241f110953d2fb0.54260547', 'RA'),
          ('8f241f1109543cf47.17877015', 'AZ'),
          ('8f241f11095451379.72078871', 'BS'),
          ('8f241f110954662e3.27051654', 'BRN'),
          ('8f241f11095497083.21181725', 'BDS'),
          ('8f241f110954d3621.45362515', 'BH'),
          ('8f241f110954ea065.41455848', 'BJ'),
          ('8f241f110954fee13.50011948', 'BER'),
          ('8f241f1109552aee2.91004965', 'BOL'),
          ('8f241f11095592407.89986143', 'BR'),
          ('8f241f110955bde61.63256042', 'BRU'),
          ('8f241f110955d3260.55487539', 'BG'),
          ('8f241f110955ea7c8.36762654', 'BF'),
          ('8f241f110956004d5.11534182', 'RU'),
          ('8f241f110956175f9.81682035', 'K'),
          ('8f241f11095632828.20263574', 'CAM'),
          ('8f241f11095649d18.02676059', 'CDN'),
          ('8f241f11095673248.50405852', 'KAI'),
          ('8f241f110956b3ea7.11168270', 'RCH'),
          ('8f241f1109570a1e3.69772638', 'CO'),
          ('8f241f1109571f018.46251535', 'KOM'),
          ('8f241f11095732184.72771986', 'RCB'),
          ('8f241f1109575d708.20084150', 'CR'),
          ('8f241f11095789a04.65154246', 'HR'),
          ('8f241f1109579ef49.91803242', 'C'),
          ('8f241f110957b6896.52725150', 'CY'),
          ('8f241f110957e6ef8.56458418', 'DK'),
          ('8f241f110957fd356.02918645', 'DSC'),
          ('8f241f1109584d512.06663789', 'EC'),
          ('8f241f11095861fb7.55278256', 'ET'),
          ('8f241f110958736a9.06061237', 'ES'),
          ('8f241f110958a2216.38324531', 'ERI'),
          ('8f241f110958b69e4.93886171', 'EST'),
          ('8f241f110958caf67.08982313', 'ETH'),
          ('8f241f110958f7ba4.96908065', 'FO'),
          ('8f241f1109590d226.07938729', 'FJI'),
          ('8f241f110959ace77.17379319', 'WAG'),
          ('8f241f110959c2341.01830199', 'GE'),
          ('8f241f110959e96b3.05752152', 'GH'),
          ('8f241f110959fdde0.68919405', 'GBZ'),
          ('8f241f11095a29f47.04102343', 'GRO'),
          ('8f241f11095a3f195.88886789', 'WG'),
          ('8f241f11095a717b3.68126681', 'GUM'),
          ('8f241f11095a870a5.42235635', 'GCA'),
          ('8f241f11095a9bf82.19989557', 'GNB'),
          ('8f241f11095ac9d30.56640429', 'GUY'),
          ('8f241f11095aebb06.34405179', 'RH'),
          ('8f241f11095b13f57.56022305', 'HN'),
          ('8f241f11095b3e016.98213173', 'H'),
          ('8f241f11095b55846.26192602', 'IS'),
          ('8f241f11095b6bb86.01364904', 'IND'),
          ('8f241f11095b80526.59927631', 'RI'),
          ('8f241f11095b94476.05195832', 'IR'),
          ('8f241f11095bad5b2.42645724', 'IRQ'),
          ('8f241f11095bd65e1.59459683', 'IL'),
          ('8f241f11095bfe834.63390185', 'JA'),
          ('8f241f11095c11d43.73419747', 'J'),
          ('8f241f11095c2b304.75906962', 'JOR'),
          ('8f241f11095c3e2d1.36714463', 'KZ'),
          ('8f241f11095c5b8e8.66333679', 'EAK'),
          ('8f241f11095c6e184.21450618', 'KIB'),
          ('8f241f11095c87284.37982544', 'KOR'),
          ('8f241f11095cb1546.46652174', 'KWT'),
          ('8f241f11095cc7ef5.28043767', 'KS'),
          ('8f241f11095cdccd5.96388808', 'LAO'),
          ('8f241f11095cf2ea6.73925511', 'LV'),
          ('8f241f11095d07d87.58986129', 'LB'),
          ('8f241f11095d1c9b2.21548132', 'LS'),
          ('8f241f11095d46188.64679605', 'LAR'),
          ('8f241f11095d6ffa8.86593236', 'LT'),
          ('8f241f11095db2291.58912887', 'MK'),
          ('8f241f11095dccf17.06266806', 'RM'),
          ('8f241f11095de2119.60795833', 'MW'),
          ('8f241f11095df78a8.44559506', 'MAL'),
          ('8f241f11095e24006.17141715', 'RMM'),
          ('8f241f11095e36eb3.69050509', 'M'),
          ('8f241f11095e4e338.26817244', 'MAR'),
          ('8f241f11095e631e1.29476484', 'MAT'),
          ('8f241f11095e7bff9.09518271', 'RIM'),
          ('8f241f11095e90a81.01156393', 'MS'),
          ('8f241f11095ea6249.81474246', 'MAY'),
          ('8f241f11095ebf3a6.86388577', 'MEX'),
          ('8f241f11095f00d65.30318330', 'MC'),
          ('8f241f11095f160c9.41059441', 'MGL'),
          ('8f241f11095f314f5.05830324', 'MOT'),
          ('8f241f11096006828.49285591', 'MA'),
          ('8f241f11096030af5.65449043', 'MYA'),
          ('8f241f11096046575.31382060', 'NAM'),
          ('8f241f110960aeb64.09757010', 'NA'),
          ('8f241f110960c3e97.21901471', 'NKA'),
          ('8f241f110960d8e58.96466103', 'NZ'),
          ('8f241f110960ec345.71805056', 'NIC'),
          ('8f241f11096101a79.70513227', 'RN'),
          ('8f241f11096116744.92008092', 'WAN'),
          ('8f241f1109612dc68.63806992', 'NIU1'),
          ('8f241f11096162678.71164081', 'NMA'),
          ('8f241f11096176795.61257067', 'N'),
          ('8f241f1109618d825.87661926', 'OM'),
          ('8f241f110961a2401.59039740', 'PK'),
          ('8f241f110961b7729.14290490', 'PAL'),
          ('8f241f110961cc384.18166560', 'PA'),
          ('8f241f110961f9d61.52794273', 'PY'),
          ('8f241f1109620b245.16261506', 'PE'),
          ('8f241f1109621faf8.40135556', 'RP'),
          ('8f241f1109624d3f8.50953605', 'PL'),
          ('8f241f11096279a22.50582479', 'PRI'),
          ('8f241f1109628f903.51478291', 'Q'),
          ('8f241f110962a3ec5.65857240', 'REU'),
          ('8f241f110962f8615.93666560', 'RWA'),
          ('8f241f1109632fab4.68646740', 'WL'),
          ('8f241f11096375757.44126946', 'RSM'),
          ('8f241f110963d9962.36307144', 'SN'),
          ('8f241f110963f98d8.68428379', 'SRB'),
          ('8f241f11096418496.77253079', 'SY'),
          ('8f241f11096436968.69551351', 'WAL'),
          ('8f241f11096456a48.79608805', 'SGP'),
          ('8f241f11096497149.85116254', 'SLO'),
          ('8f241f110964d5f29.11398308', 'SP'),
          ('8f241f110964f2623.74976876', 'ZA'),
          ('8f241f11096531330.03198083', 'CL'),
          ('8f241f1109658cbe5.08293991', 'SUD'),
          ('8f241f1109660c113.62780718', 'SD'),
          ('8f241f1109666b7f3.81435898', 'SYR'),
          ('8f241f110966a54d1.43798997', 'TD'),
          ('8f241f110966c3a75.68297960', 'EAT'),
          ('8f241f11096707e08.60512709', 'T'),
          ('8f241f110967241e1.34925220', 'RT'),
          ('8f241f11096762b31.03069244', 'TG'),
          ('8f241f1109679d988.46004322', 'TN'),
          ('8f241f110967bba40.88233204', 'TR'),
          ('8f241f110967d8f65.52699796', 'TM'),
          ('8f241f110967f73f8.13141492', 'TUC'),
          ('8f241f1109680ec30.97426963', 'TUV'),
          ('8f241f11096823019.47846368', 'EAU'),
          ('8f241f110968391d2.37199812', 'UA'),
          ('8f241f110968a7cc9.56710143', 'ROU'),
          ('8f241f110968bec45.44161857', 'UZ'),
          ('8f241f11096902d92.14742486', 'YMN'),
          ('8f241f11096919d00.92534927', 'VN'),
          ('8f241f11096944468.61956573', 'AJ'),
          ('8f241f110969c34a2.42564730', 'Z'),
          ('8f241f110969da699.04185888', 'ZW'),
          ('56d308a822c18e106.3ba59048', 'CG');
    ";

    public static $sQueryABEnterpriseOxfield2Shop = "
        ALTER TABLE oxfield2shop
          ADD COLUMN FCAFTERBUYACTIVE TINYINT(1) not null default 0,
          ADD COLUMN FCAFTERBUYID VARCHAR(255) not null,
          ADD COLUMN FCAFTERBUY_AID VARCHAR(255) not null,
          ADD COLUMN FCAFTERBUY_VID VARCHAR(255) not null,
          ADD COLUMN FCAFTERBUY_UID VARCHAR(255) not null,
          ADD COLUMN FCAFTERBUY_CUSTOMNR VARCHAR(255) not null,
          ADD COLUMN FCAFTERBUY_ECUSTOMNR VARCHAR(255) not null,
          ADD COLUMN FCAFTERBUY_LASTCHECKED DATETIME not null,
          ADD COLUMN FCAFTERBUY_FULFILLED TINYINT(1) not null DEFAULT 0,
          ADD COLUMN FCAFTERBUY_FULFILLEDEXT TINYINT(1) not null DEFAULT 0,
          ADD COLUMN FCAFTERBUY_USERID VARCHAR(255) not null;
    ";

    /**
     * Execute action on activate event.
     *
     * @return void
     */
    public static function onActivate()
    {
        self::addDatabaseStructure();
        self::migrateFromOldVersion();
        self::regenerateViews();
        self::clearTmp();
    }

    /**
     * Execute action on deactivate event.
     *
     * @return void
     */
    public static function onDeactivate()
    {
        self::clearTmp();
    }

    /**
     * Regenerates database view-tables.
     *
     * @return void
     */
    public static function regenerateViews()
    {
        $oShop = oxNew('oxShop');
        $oShop->generateViews();
    }

    /**
     * Clear tmp dir and smarty cache.
     *
     * @return void
     */
    public static function clearTmp()
    {
        $sTmpDir = getShopBasePath() . "/tmp/";
        $sSmartyDir = $sTmpDir . "smarty/";

        foreach (glob($sTmpDir . "*.txt") as $sFileName) {
            unlink($sFileName);
        }
        foreach (glob($sSmartyDir . "*.php") as $sFileName) {
            unlink($sFileName);
        }
    }

    /**
     * Creating database structure changes.
     *
     * @return void
     */
    public static function addDatabaseStructure() {
        //CREATE NEW TABLES
        self::addTableIfNotExists('oxorder_afterbuy', self::$sQueryCreateABOrderTable);
        self::addTableIfNotExists('oxarticles_afterbuy', self::$sQueryCreateABArticleTable);
        self::addTableIfNotExists('oxuser_afterbuy', self::$sQueryCreateABUserTable);
        self::addTableIfNotExists('oxcategories_afterbuy', self::$sQueryCreateABCategoriesTable);
        self::addTableIfNotExists('fcafterbuypayments', self::$sQueryCreateABPaymentAssignment);
        self::addTableIfNotExists('fcafterbuycountry', self::$sQueryCreateABCountry);
        // INSERT DATA
        self::replaceData('fcafterbuycountry', self::$sQueryInsertCountryAssignments);

        // ENTERPRISE
        $oConfig = oxRegistry::getConfig();
        $sEdition = $oConfig->getEdition();
        if ($sEdition == 'EE') {
            self::executeQuery(self::$sQueryABEnterpriseOxfield2Shop);
        }

        // UPDATE TABLES
        self::addColumnIfNotExists("oxattribute", "FCAFTERBUYFEATUREID", self::$sQueryUpdateOxattributes);
    }

    /**
     * Initiates migrating of all former data
     *
     * @param void
     * @return void
     */
    public static function migrateFromOldVersion()
    {
        // articles
        $blMigrateArticles =
            self::checkMigrationNeeded('oxarticles', 'oxarticles_afterbuy');
        if ($blMigrateArticles)
            self::migrateData('oxarticles', 'oxarticles_afterbuy');

        // orders
        $blMigrateOrders =
            self::checkMigrationNeeded('oxorder', 'oxorder_afterbuy');
        if ($blMigrateOrders)
            self::migrateData('oxorder', 'oxorder_afterbuy', array(''));

        // user
        $blMigrateUsers =
            self::checkMigrationNeeded('oxuser', 'oxuser_afterbuy');
        if ($blMigrateUsers)
            self::migrateData('oxuser', 'oxuser_afterbuy');
    }

    /**
     * Migrate afterbuy data from source to target table
     *
     * @param $sSourceTable
     * @param $sTargetTable
     * @return void
     */
    public static function migrateData($sSourceTable, $sTargetTable, $aEmptyFields=array())
    {
        $aSourceColums =
            self::getAfterbuyColumsOfTable($sSourceTable);

        if (count($aSourceColums) == 0)
            return;

        $aTransferColumns =
            self::filterAfterbuyTransferColums($aSourceColums, $sTargetTable);

        self::transferAfterbuyData($sSourceTable, $sTargetTable, $aTransferColumns, $aEmptyFields);
    }

    /**
     * Remove colums which are not available in target
     *
     * @param array $aSourceColumns
     * @param array $sTargetTable
     * @return array
     */
    public static function filterAfterbuyTransferColums($aSourceColumns, $sTargetTable)
    {
        array_unshift($aSourceColumns, 'OXID');
        $aTargetColums = self::getAfterbuyColumsOfTable($sTargetTable);
        array_unshift($aTargetColums, 'OXID');

        foreach ($aSourceColumns as $iIndex=>$sColumn) {
            $blAvailable = in_array($sColumn, $aTargetColums);
            if (!$blAvailable) unset($aSourceColumns[$iIndex]);
        }

        return $aSourceColumns;
    }

    /**
     * Perform data transfer
     *
     * @param $sSourceTable
     * @param $sTargetTable
     * @param $aTransferColumns
     */
    public static function transferAfterbuyData($sSourceTable, $sTargetTable, $aTransferColumns, $aEmptyFields)
    {
        $oDb = oxDb::getDb();

        $sFields = implode(',', $aTransferColumns);
        $sEmptyFields = "";
        foreach ($aEmptyFields as $sEmptyField) {
            $sEmptyFields .= ",''";
        }
        $sQuery =
            "INSERT INTO {$sTargetTable} SELECT {$sFields}{$sEmptyFields} FROM {$sSourceTable}";

        $oDb->execute($sQuery);
    }

    /**
     * Checks that there are no existing data entries in afterbuy table
     * and table contains afterbuy colums
     *
     * @param $sTable
     * @param $sAfterbuyTable
     * @return bool
     */
    public static function checkMigrationNeeded($sTable, $sAfterbuyTable)
    {
        $oDb = oxDb::getDb();

        // is there already any data set in target?
        $sQuery = "SELECT count(*) FROM {$sAfterbuyTable} WHERE 1";
        $iAmountRows = (int) $oDb->getOne($sQuery);

        // never overwrite existing data -> early exit
        if ($iAmountRows > 0)
            return false;

        $aColumns = self::getAfterbuyColumsOfTable($sTable);

        $blMigrate = (
            is_array($aColumns) &&
            count($aColumns) > 0
        );

        return $blMigrate;
    }

    /**
     * Returns list of colums of table. Default isset to all afterbuy
     * colums but can be overwritten as needed with file pattern param
     *
     * @param $sTable
     * @param string $sFieldPattern
     * @return mixed
     */
    public static function getAfterbuyColumsOfTable($sTable, $sFieldPattern='FCAFTERBUY_%')
    {
        $oDb = oxDb::getDb();

        $sQuery = "SHOW COLUMNS FROM {$sTable} LIKE '{$sFieldPattern}'";
        $aRows = $oDb->getAll($sQuery);

        $aColumns = array();

        foreach ($aRows as $aRow) {
            $aColumns[] = $aRow[0];
        }

        return $aColumns;
    }

    /**
     * Executes a query
     *
     * @param $sQuery
     * @return void
     * @throws
     */
    public static function executeQuery($sQuery) {
        $oDb = oxDb::getDb();
        $oDb->execute($sQuery);
    }

    /**
     * Add a database table.
     *
     * @param string $sTableName table to add
     * @param string $sQuery     sql-query to add table
     *
     * @return boolean true or false
     */
    public static function addTableIfNotExists($sTableName, $sQuery)
    {
        $aTables = oxDb::getDb()->getAll("SHOW TABLES LIKE '{$sTableName}'");
        if (!$aTables || count($aTables) == 0) {
            oxDb::getDb()->execute($sQuery);
            return true;
        }
        return false;
    }

    /**
     * Add a column to a database table.
     *
     * @param  string $sTableName            table name
     * @param  string $sColumnName           column name
     * @param  string $sQuery                sql-query to add column to table
     * @return bool
     */
    public static function addColumnIfNotExists($sTableName, $sColumnName, $sQuery)
    {
        $aColumns = oxDb::getDb()->getAll("SHOW COLUMNS FROM {$sTableName} LIKE ?", array($sColumnName));
        if (empty($aColumns)) {
            try {
                oxDb::getDb()->Execute($sQuery);
                return true;
            } catch (\Exception $e) {
                // do nothing as of yet
            }
        }
        return false;
    }


    /**
     * Replaces data for given table
     *
     * @param $sTableName
     * @param $sQuery
     * @return
     */
    public static function replaceData($sTableName, $sQuery) {
        oxDb::getDb()->execute($sQuery);
    }
}