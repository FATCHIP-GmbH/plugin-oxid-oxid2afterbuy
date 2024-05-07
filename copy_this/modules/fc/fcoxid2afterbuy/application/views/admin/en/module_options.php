<?php
/**
 * *
 *  * @package FATCHIP
 *  * @author FATCHIP GmbH
 *  * @copyright (C) {2017}, FATCHIP GmbH
 *  *
 *  * This Software is the property of FATCHIP GmbH
 *  * and is protected by copyright law - it is NOT Freeware.
 *  *
 *  * Any unauthorized use of this software without a valid license
 *  * is a violation of the license agreement and will be
 *  * prosecuted by civil and criminal law.
 *
 */

$sLangName  = "English";
// -------------------------------
// RESOURCE IDENTITFIER = STRING
// -------------------------------
$aLang = array(
    'charset'                                   	        => 'UTF-8',
    // module settings
    'SHOP_MODULE_GROUP_fcafterbuy_general'                  => 'General settings',
    'SHOP_MODULE_GROUP_fcafterbuy_debug'                    => 'Logs and Debug modes',
    'SHOP_MODULE_GROUP_fcafterbuy_connect'                  => 'Connection Settings',
    'SHOP_MODULE_GROUP_fcafterbuy_order'                    => 'Transfer Orders',
    'SHOP_MODULE_GROUP_fcafterbuy_extended'                 => 'Additional Settings',
    'SHOP_MODULE_GROUP_fcafterbuy_export'                   => 'Export articles',
    'SHOP_MODULE_GROUP_fcafterbuy_import'                   => 'Import articles',
    'SHOP_MODULE_sFcAfterbuyLeadSystem'                     => 'Leading System',
    'SHOP_MODULE_sFcAfterbuyLeadSystem_0'                   => 'OXID-eShop',
    'SHOP_MODULE_sFcAfterbuyLeadSystem_1'                   => 'Afterbuy',
    'SHOP_MODULE_iFcAfterbuyLogLevel'                       => 'Event logging',
    'SHOP_MODULE_iFcAfterbuyLogLevel_0'                     => 'No logging',
    'SHOP_MODULE_iFcAfterbuyLogLevel_1'                     => 'Log Errors',
    'SHOP_MODULE_iFcAfterbuyLogLevel_2'                     => 'Log Errors and Warnings',
    'SHOP_MODULE_iFcAfterbuyLogLevel_3'                     => 'Log all events (Large Logfiles)',
    'SHOP_MODULE_iFcAfterbuyLogLevel_4'                     => 'Log all events and developer information (Very large Logfiles)',
    'SHOP_MODULE_blFcAfterbuyExportUTF8Orders'              => 'Sumbit orders UTF8 encoded',
    'SHOP_MODULE_sFcAfterbuyAccountToken'                   => 'Account Token',
    'SHOP_MODULE_sFcAfterbuyPartnerToken'                   => 'Partner Token',
    'SHOP_MODULE_sFcAfterbuyUsername'                       => 'Username',
    'SHOP_MODULE_sFcAfterbuyUserPassword'                   => 'Password',
    'SHOP_MODULE_blFcAfterbuyExportAll'                     => 'Export all articles to all channels',
    'HELP_SHOP_MODULE_blFcAfterbuyExportAll'                => 'As default only articles will be exported, which has been flagged for export in product tab "Afterbuy".',
    'SHOP_MODULE_sFcAfterbuyImportArticleNumber'            => 'Related field for article number',
    'SHOP_MODULE_sFcAfterbuyImportArticleNumber_0'          => 'Default - external article number, article number as fallback',
    'SHOP_MODULE_sFcAfterbuyImportArticleNumber_1'          => 'External article number',
    'SHOP_MODULE_sFcAfterbuyImportArticleNumber_2'          => 'Afterbuy Product Id',
    'SHOP_MODULE_sFcAfterbuyImportArticleNumber_3'          => 'Article number',
    'SHOP_MODULE_sFcAfterbuyExportStockTime'                => 'last stock date ( yyyy-mm-dd hh:mm:ss )',
    'SHOP_MODULE_blFcAfterbuyIgnoreArticlesWithoutNr'       => 'Discard articles without article number',
    'SHOP_MODULE_blFcStockLimitAuction'                     => 'Limit stock of ype auction',
    'SHOP_MODULE_blFcSendOrdersOnTheFly'                    => 'Directly transfer order on order finish',
    'SHOP_MODULE_sFcSendOrderNrInAdditionalField'           => 'Store Oxid ordernumber in an additional field',
    'SHOP_MODULE_sFcSendOrderNrInAdditionalField_0'         => 'No',
    'SHOP_MODULE_sFcSendOrderNrInAdditionalField_1'         => 'VMemo',
    // 'SHOP_MODULE_sFcLastOrderId'                            => 'Bestellimport ab dieser BestellID (Wird automatisch befüllt)',
    'SHOP_MODULE_aFcAfterbuyDebitPayments'                  => 'List of payments which shall export bank account data',
    'SHOP_MODULE_aFcAfterbuyDebitDynBankname'               => 'List of fieldnames, that shall be indicated as Bankname',
    'SHOP_MODULE_aFcAfterbuyDebitDynBankzip'                => 'List of fieldnames, that shall be indicated as Bankcode',
    'SHOP_MODULE_aFcAfterbuyDebitDynAccountNr'              => 'List of fieldnames, that shall be indicated as Account Number',
    'SHOP_MODULE_aFcAfterbuyDebitDynAccountOwner'           => 'List of fieldnames, that shall be indicated as Account Owner',
    'SHOP_MODULE_aFcAfterbuyPaymentsSetPaid'                => 'List of fieldnames, that shall be set to paid directly on finishing order',
    'SHOP_MODULE_sFcAfterbuyFeedbackType'                   => 'Handling of feedback date and firstcontact mail',
    'SHOP_MODULE_sFcAfterbuyFeedbackType_0'                 => "Set Feedback-Date (Don't send mail)",
    'SHOP_MODULE_sFcAfterbuyFeedbackType_1'                 => "Don't set Feedback-Date (Send E-Mail)",
    'SHOP_MODULE_sFcAfterbuyFeedbackType_2'                 => 'Set Feedback-Date (Send E-Mail)',
    'SHOP_MODULE_sFcAfterbuyDeliveryCalculation'            => 'Calculate Delivery Costs',
    'SHOP_MODULE_sFcAfterbuyDeliveryCalculation_0'          => 'Afterbuy calculates  delivery costs',
    'SHOP_MODULE_sFcAfterbuyDeliveryCalculation_1'          => 'Keep transfered delivery costs',
    'SHOP_MODULE_sFcAfterbuySendVat'                        => 'Sending VAT',
    'SHOP_MODULE_sFcAfterbuySendVat_0'                      => 'VAT will be sent',
    'SHOP_MODULE_sFcAfterbuySendVat_1'                      => "VAT won't be sent",
    'SHOP_MODULE_sFcAfterbuySendWeight'                     => 'Submit delivery weight',
    'SHOP_MODULE_sFcAfterbuySendWeight_0'                   => 'No',
    'SHOP_MODULE_sFcAfterbuySendWeight_1'                   => 'Yes',
    'SHOP_MODULE_sFcAfterbuyMarkId'                         => 'Afterbuy Mark ID (Afterbuy Color-Assignment).',
    'SHOP_MODULE_blFcAfterbuyUseOwnCustNr'                  => 'Use customer number of shop in Afterbuy.',
    'SHOP_MODULE_sFcAfterbuyCustIdent'                      => 'Customer identification between Shop and Afterbuy',
    'SHOP_MODULE_sFcAfterbuyCustIdent_0'                    => 'eBayName',
    'SHOP_MODULE_sFcAfterbuyCustIdent_1'                    => 'eMail-Address',
    'SHOP_MODULE_sFcAfterbuyCustIdent_2'                    => 'Customer number of shop',
    'SHOP_MODULE_sFcAfterbuyOverwriteEbayName'              => 'Handling of eBay-Name',
    'SHOP_MODULE_sFcAfterbuyOverwriteEbayName_0'            => 'Overwrite of eBay name',
    'SHOP_MODULE_sFcAfterbuyOverwriteEbayName_1'            => "Don't overwrite of eBay name",
    'SHOP_MODULE_sFcAfterbuyArticleIdent'                   => 'Article identification',
    'SHOP_MODULE_sFcAfterbuyArticleIdent_0'                 => 'Afterbuy-ProductID',
    'SHOP_MODULE_sFcAfterbuyArticleIdent_1'                 => 'Afterbuy-Article-Number',
    'SHOP_MODULE_sFcAfterbuyArticleIdent_2'                 => 'Afterbuy External-Article-Number',
    'SHOP_MODULE_sFcAfterbuyArticleIdent_3'                 => 'Manufacturer EAN',
    'SHOP_MODULE_aFcAfterbuyPayments'                       => 'Afterbuy-Payments',

    'mxfcafterbuy_article_admin'                            => 'Afterbuy',
    'mxfcafterbuy'                                          => 'Afterbuy',
    'mxfcafterbuy_payments'                                 => 'Assign payments',
    'mxfcafterbuy_actions'                                  => 'Maintenance/Actions',
    'mxfcafterbuyorder'                                     => 'Management',
    'tbclorder_orderinfo'                                   => 'Afterbuy',

    'FC_AFTERBUY_ARTICLE_ACTIVE'                            => 'Export article to Afterbuy',
    'FC_AFTERBUY_ARTICLE_PRODUCTID'                         => 'Afterbuy-ProductID',
    'SHOP_MODULE_AFTERBUY_SAVE_PAYMENTS'                    => 'Save assignments',
    'SHOP_MODULE_AFTERBUY_PAYMENTS_SHOP'                    => 'Payment Methods Shop',
    'SHOP_MODULE_AFTERBUY_PAYMENTS_AFTERBUY'                => 'Payment Methods Afterbuy',
    'SHOP_MODULE_AFTERBUY_PAYMENTS_SAVED'                   => 'Sucessfully saved assignments.',
    'SHOP_MODULE_AFTERBUYVALUE_NAME'                        => 'Afterbuy-Field',
    'SHOP_MODULE_AFTERBUYVALUE_VALUE'                       => 'Afterbuy-Fieldvalue',
    'SHOP_MODULE_AFTERBUYVALUE_NOVALUE'                     => 'No Afterbuy values for this assignment',
    'SHOP_MODULE_AFTERBUY_RESET_TRANSACTIONDATA'            => 'Reset Afterbuy transaction data',
    'SHOP_MODULE_AFTERBUY_RESET_TRANSACTIONDATA_SURE'       => 'Are you sure, that you want do delete ALL Afterbuy-Transactiondata related to products, orders and users?',
    'SHOP_MODULE_AFTERBUY_TRANSACTIONDATA_RESET'            => 'Transactiondata have been reset',
    'SHOP_MODULE_AFTERBUY_ACTIONS'                          => 'Actions',
    'SHOP_MODULE_AFTERBUY_MANUAL_SUBMISSION_HEAD'           => 'Submit order manually',
    'SHOP_MODULE_AFTERBUY_MANUAL_SUBMISSION_INFORMATION'    => 'Manual submission is only available if Afterbuy is the data carrying system.',
    'SHOP_MODULE_AFTERBUY_MANUAL_SUBMISSION_MESSAGE'        => 'Order submitted',
    'SHOP_MODULE_AFTERBUY_MANUAL_SUBMISSION_ERROR'          => 'Error submitting order',

    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_LOG'             => 'OXID-Logfile',
    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_APILOG'          => 'API-Log',
    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_DEFAULTLOG'      => 'Module-Log',
    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_TRUNCATE'        => 'Reset',
    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_DOWNLOAD'        => 'Download',
    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_APILOG_TRUNCATE' => 'Do you really want to truncate the API logfile?',
    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_DEFAULTLOG_TRUNCATE' => 'Do you really want to truncate the module logfile?',
    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_APILOG_TRUNCATED'=> 'API-Log successfully truncated',
    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_DEFAULTLOG_TRUNCATED'=> 'Module-Log successfully truncated'

);