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

$sLangName  = "Deutsch";
// -------------------------------
// RESOURCE IDENTITFIER = STRING
// -------------------------------
$aLang = array(
    'charset'                                   	        => 'UTF-8',
    // module settings
    'SHOP_MODULE_GROUP_fcafterbuy_general'                  => 'Allgemeine Einstellungen',
    'SHOP_MODULE_GROUP_fcafterbuy_debug'                    => 'Logaufzeichnungen, Ausgaben und Betriebsmodus',
    'SHOP_MODULE_GROUP_fcafterbuy_connect'                  => 'Verbindungseinstellungen',
    'SHOP_MODULE_GROUP_fcafterbuy_order'                    => 'Bestellungen übertragen',
    'SHOP_MODULE_GROUP_fcafterbuy_extended'                 => 'Weitere Einstellungen',
    'SHOP_MODULE_GROUP_fcafterbuy_export'                   => 'Artikelexporte',
    'SHOP_MODULE_GROUP_fcafterbuy_import'                   => 'Artikelimporte',
    'SHOP_MODULE_sFcAfterbuyLeadSystem'                     => 'Führendes System',
    'SHOP_MODULE_sFcAfterbuyLeadSystem_0'                   => 'OXID-Shop',
    'SHOP_MODULE_sFcAfterbuyLeadSystem_1'                   => 'Afterbuy',
    'SHOP_MODULE_iFcAfterbuyLogLevel'                       => 'Vorgangsprotokollierung',
    'SHOP_MODULE_iFcAfterbuyLogLevel_0'                     => 'Protokollierung aus',
    'SHOP_MODULE_iFcAfterbuyLogLevel_1'                     => 'Nur Fehler protokollieren',
    'SHOP_MODULE_iFcAfterbuyLogLevel_2'                     => 'Fehler und Warnungen protokollieren',
    'SHOP_MODULE_iFcAfterbuyLogLevel_3'                     => 'Sämtliche Vorgänge protokollieren (Große Logfiles)',
    'SHOP_MODULE_iFcAfterbuyLogLevel_4'                     => 'Sämtliche Vorgänge + Entwickler-Informationen protokollieren (Sehr große Logfiles)',
    'SHOP_MODULE_blFcAfterbuyExportUTF8Orders'              => 'Bestellungen als UTF8 übertragen',
    'SHOP_MODULE_sFcAfterbuyAccountToken'                   => 'Account Token',
    'SHOP_MODULE_sFcAfterbuyPartnerToken'                   => 'Partner Token',
    'SHOP_MODULE_sFcAfterbuyUsername'                       => 'Username',
    'SHOP_MODULE_sFcAfterbuyUserPassword'                   => 'Passwort',
    'SHOP_MODULE_blFcAfterbuyExportAll'                     => 'Alle Artikel an alle Kanäle exportieren',
    'SHOP_MODULE_sFcAfterbuyImportArticleNumber'            => 'Artikelnummer aus folgendem Feld beziehen',
    'SHOP_MODULE_sFcAfterbuyImportArticleNumber_0'          => 'Standard - externe Artikelnummer, Artikelnummer alternativ',
    'SHOP_MODULE_sFcAfterbuyImportArticleNumber_1'          => 'externe Artikelnummer',
    'SHOP_MODULE_sFcAfterbuyImportArticleNumber_2'          => 'Afterbuy Product Id',
    'SHOP_MODULE_sFcAfterbuyImportArticleNumber_3'          => 'Artikelnummer',
    'SHOP_MODULE_sFcAfterbuyExportStockTime'                => 'letztes Lager Datum ( yyyy-mm-dd hh:mm:ss )',
    'SHOP_MODULE_blFcAfterbuyIgnoreArticlesWithoutNr'       => 'Artikel ohne Artikelnummer verwerfen',
    'HELP_SHOP_MODULE_blFcAfterbuyExportAll'                => 'Standardmäßig werden nur Artikel zu Afterbuy übertragen, welche unter dem Reiter "Afterbuy" einen Haken gesetzt haben.',
    'SHOP_MODULE_blFcStockLimitAuction'                     => 'Bestandart vom Typ Auktion verwenden',
    'SHOP_MODULE_blFcSendOrdersOnTheFly'                    => 'Bestellungen direkt beim Bestellabschluss an Afterbuy übertragen',
    'SHOP_MODULE_sFcSendOrderNrInAdditionalField'           => 'Oxid Bestellnummer in zusätzlichem Feld speichern',
    'SHOP_MODULE_sFcSendOrderNrInAdditionalField_0'         => 'Nein',
    'SHOP_MODULE_sFcSendOrderNrInAdditionalField_1'         => 'VMemo',
    // 'SHOP_MODULE_sFcLastOrderId'                            => 'Bestellimport ab dieser BestellID (Wird automatisch befüllt)',
    'SHOP_MODULE_aFcAfterbuyDebitPayments'                  => 'Liste der Zahlarten, die Bankdaten an Afterbuy übertragen sollen',
    'SHOP_MODULE_aFcAfterbuyDebitDynBankname'               => 'Liste der Feldnamen, die als Bankname identifiziert werden sollen',
    'SHOP_MODULE_aFcAfterbuyDebitDynBankzip'                => 'Liste der Feldnamen, die als BLZ identifiziert werden sollen',
    'SHOP_MODULE_aFcAfterbuyDebitDynAccountNr'              => 'Liste der Feldnamen, die als Kontonummer identifiziert werden sollen',
    'SHOP_MODULE_aFcAfterbuyDebitDynAccountOwner'           => 'Liste der Feldnamen, die als Kontoinhaber identifiziert werden sollen',
    'SHOP_MODULE_aFcAfterbuyPaymentsSetPaid'                => 'Liste der PaymentIDs, die bei Bestellabschluss als bezahlt markiert werden sollen.',
    'SHOP_MODULE_sFcAfterbuyFeedbackType'                   => 'Verhalten für Feedbackdatum und Erstkontakt-Mail',
    'SHOP_MODULE_sFcAfterbuyFeedbackType_0'                 => 'Feedbackdatum setzen (Keine eMail versenden)',
    'SHOP_MODULE_sFcAfterbuyFeedbackType_1'                 => 'Kein Feedbackdatum setzen (eMail versenden)',
    'SHOP_MODULE_sFcAfterbuyFeedbackType_2'                 => 'Feedbackdatum setzen (eMail versenden)',
    'SHOP_MODULE_sFcAfterbuyDeliveryCalculation'            => 'Versandkostenberechnung',
    'SHOP_MODULE_sFcAfterbuyDeliveryCalculation_0'          => 'Afterbuy berechnet die Versandkosten',
    'SHOP_MODULE_sFcAfterbuyDeliveryCalculation_1'          => 'Übergebene Versandkosten bleiben erhalten',
    'SHOP_MODULE_sFcAfterbuySendVat'                        => 'Ausweisung der MwSt',
    'SHOP_MODULE_sFcAfterbuySendVat_0'                      => 'MwSt wird ausgewiesen',
    'SHOP_MODULE_sFcAfterbuySendVat_1'                      => 'MwSt wird nicht ausgewiesen',
    'SHOP_MODULE_sFcAfterbuySendWeight'                     => 'Versandgewicht übermitteln',
    'SHOP_MODULE_sFcAfterbuySendWeight_0'                   => 'Nein',
    'SHOP_MODULE_sFcAfterbuySendWeight_1'                   => 'Ja',
    'SHOP_MODULE_sFcAfterbuyMarkId'                         => 'Afterbuy Markierungs-ID (Afterbuy Farbzuweisung).',
    'SHOP_MODULE_blFcAfterbuyUseOwnCustNr'                  => 'Shop-Kundennummer für Kunden in Afterbuy verwenden.',
    'SHOP_MODULE_sFcAfterbuyCustIdent'                      => 'Kundenenidentifizierung zwischen Shop und Afterbuy',
    'SHOP_MODULE_sFcAfterbuyCustIdent_0'                    => 'eBayName',
    'SHOP_MODULE_sFcAfterbuyCustIdent_1'                    => 'eMail-Adresse',
    'SHOP_MODULE_sFcAfterbuyCustIdent_2'                    => 'Kundennummer des Shops (Muss aktiviert sein)',
    'SHOP_MODULE_sFcAfterbuyOverwriteEbayName'              => 'Behandlung von eBay-Namen',
    'SHOP_MODULE_sFcAfterbuyOverwriteEbayName_0'            => 'eBayName überschreiben',
    'SHOP_MODULE_sFcAfterbuyOverwriteEbayName_1'            => 'eBayName nicht überschreiben',
    'SHOP_MODULE_sFcAfterbuyArticleIdent'                   => 'Artikelidentifikation',
    'SHOP_MODULE_sFcAfterbuyArticleIdent_0'                 => 'Afterbuy-ProduktID',
    'SHOP_MODULE_sFcAfterbuyArticleIdent_1'                 => 'Afterbuy-Artikelnummer',
    'SHOP_MODULE_sFcAfterbuyArticleIdent_2'                 => 'Afterbuy-externe Artikelnummer',
    'SHOP_MODULE_sFcAfterbuyArticleIdent_3'                 => 'Hersteller EAN',
    'SHOP_MODULE_aFcAfterbuyPayments'                       => 'Afterbuy-Zahlarten',

    'mxfcafterbuy_article_admin'                            => 'Afterbuy',
    'mxfcafterbuy'                                          => 'Afterbuy',
    'mxfcafterbuy_payments'                                 => 'Zahlarten zuweisen',
    'mxfcafterbuy_actions'                                  => 'Wartung/Aktionen',
    'mxfcafterbuyorder'                                     => 'Verwaltung',
    'tbclorder_orderinfo'                                   => 'Afterbuy',

    'FC_AFTERBUY_ARTICLE_ACTIVE'                            => 'Artikel zu Afterbuy übertragen',
    'FC_AFTERBUY_ARTICLE_PRODUCTID'                         => 'Afterbuy-ProduktID',
    'SHOP_MODULE_AFTERBUY_SAVE_PAYMENTS'                    => 'Zuweisungen speichern',
    'SHOP_MODULE_AFTERBUY_PAYMENTS_SHOP'                    => 'Zahlarten Shop',
    'SHOP_MODULE_AFTERBUY_PAYMENTS_AFTERBUY'                => 'Zahlarten Afterbuy',
    'SHOP_MODULE_AFTERBUY_PAYMENTS_SAVED'                   => 'Zuweisungen erfolgreich gespeichert',
    'SHOP_MODULE_AFTERBUYVALUE_NAME'                        => 'Afterbuy-Feld',
    'SHOP_MODULE_AFTERBUYVALUE_VALUE'                       => 'Afterbuy-Feldwert',
    'SHOP_MODULE_AFTERBUYVALUE_NOVALUE'                     => 'Keine Afterbuy-Werte zu dieser Bestellung vorhanden!',
    'SHOP_MODULE_AFTERBUY_RESET_TRANSACTIONDATA'            => 'Afterbuy-Bewegungsdaten zurücksetzen',
    'SHOP_MODULE_AFTERBUY_RESET_TRANSACTIONDATA_SURE'       => 'Sind Sie sicher, dass Sie ALLE Afterbuy-Daten zu Artikeln, Bestellungen und Benutzern löschen wollen?',
    'SHOP_MODULE_AFTERBUY_TRANSACTIONDATA_RESET'            => 'Afterbuy-Bewegungsdaten zurückgesetzt',
    'SHOP_MODULE_AFTERBUY_ACTIONS'                          => 'Aktionen',
    'SHOP_MODULE_AFTERBUY_MANUAL_SUBMISSION_HEAD'           => 'Bestellung erneut übertragen',
    'SHOP_MODULE_AFTERBUY_MANUAL_SUBMISSION_INFORMATION'    => 'Die manuelle Übertragung von Bestellungen ist nur möglich, wenn Afterbuy als datenführendes System konfiguriert wurde.',
    'SHOP_MODULE_AFTERBUY_MANUAL_SUBMISSION_MESSAGE'        => 'Bestellung übermittelt',
    'SHOP_MODULE_AFTERBUY_MANUAL_SUBMISSION_ERROR'          => 'Fehler beim Übermitteln der Bestellung',

    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_LOG'             => 'OXID-Log',
    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_APILOG'          => 'Afterbuy API-Log',
    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_DEFAULTLOG'      => 'Modul-Log',
    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_TRUNCATE'        => 'Zurücksetzen',
    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_DOWNLOAD'        => 'Download',
    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_APILOG_TRUNCATE' => 'Möchten Sie wirklich das API-Log zurücksetzen?',
    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_DEFAULTLOG_TRUNCATE' => 'Möchten Sie wirklich das Modul-Log zurücksetzen?',
    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_APILOG_TRUNCATED'=> 'API-Log erfolgreich zurückgesetzt',
    'SHOP_MODULE_AFTERBUY_ACTIONS_AFTERBUY_DEFAULTLOG_TRUNCATED' => 'Modul-Log erfolgreich zurückgesetzt'
);