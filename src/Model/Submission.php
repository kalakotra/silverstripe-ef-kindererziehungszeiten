<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Model;

use app\forms\basemodel\BaseSubmission;
use mikehaertl\pdftk\Pdf;
use RuntimeException;
use setasign\Fpdi\Fpdi;

/**
 * DRV V0800 submission.
 *
 * Mapping conventions (see BaseSubmission::getArrayDataForPDF):
 *   'PDF field' => 'DbField'                       plain value
 *   'PDF field' => 'Method:DbField:Arg'            computed value
 *
 * V0800 checkboxes are button groups with several on-states (ja, ja1, ja2, ...) on ONE PDF field,
 * so they are mapped with StateValue:DbField:PdfField and resolved via $state_map.
 * Dates are 8-char comb fields -> DateFormating:Field:dmY.
 */
class Submission extends BaseSubmission
{
    private static string $table_name = 'Kalakotra_EFormKindererziehungszeiten_Submission';

    private const YES_NO = ['no' => 'nein', 'yes' => 'ja'];
    private const CHECKED = ['1' => 'ja'];

    private static array $db = [
        // Ziffer 1
        'InsuranceNumber' => 'Varchar(12)',
        'Kennzeichen' => 'Varchar(4)',
        'LastName' => 'Varchar(100)',
        'FirstName' => 'Varchar(100)',
        'NameSuffix' => 'Varchar(50)',
        'NamePrefix' => 'Varchar(50)',
        'Title' => 'Varchar(50)',
        'BirthName' => 'Varchar(100)',
        'FormerNames' => 'Varchar(150)',
        'BirthNameSuffix' => 'Varchar(50)',
        'BirthNamePrefix' => 'Varchar(50)',
        'BirthDate' => 'Date',
        'Gender' => 'Varchar(20)',
        'BirthPlace' => 'Varchar(100)',
        'BirthCountry' => 'Varchar(100)',
        'Nationality' => 'Varchar(150)',
        'Street' => 'Varchar(150)',
        'AddressAddition' => 'Varchar(100)',
        'PostalCode' => 'Varchar(10)',
        'City' => 'Varchar(100)',
        'LivesAbroad' => 'Boolean',
        'Country' => 'Varchar(100)',
        'LastDomesticResidence' => 'Varchar(150)',
        'Phone' => 'Varchar(50)',
        'Fax' => 'Varchar(50)',
        'MovedFromAbroad' => 'Varchar(20)',
        'MovedDate' => 'Date',
        'MovedFrom' => 'Varchar(150)',
        'MovedTo' => 'Varchar(150)',

        // Ziffer 2 - Kind 1
        'Child1Name' => 'Varchar(150)',
        'Child1NamePrefix' => 'Varchar(50)',
        'Child1BirthPlace' => 'Varchar(100)',
        'Child1BirthCountry' => 'Varchar(100)',
        'Child1BirthDate' => 'Date',
        'Child1DeathDate' => 'Date',
        'Child1StepFoster' => 'Varchar(20)',
        'Child1StepFosterType' => 'Varchar(20)',
        'Child1RaisedSinceBirth' => 'Varchar(20)',
        'Child1RaisedFrom' => 'Date',
        'Child1RaisedTo' => 'Date',
        'HasSecondChild' => 'Varchar(20)',

        // Ziffer 2 - Kind 2
        'Child2Name' => 'Varchar(150)',
        'Child2NamePrefix' => 'Varchar(50)',
        'Child2BirthPlace' => 'Varchar(100)',
        'Child2BirthCountry' => 'Varchar(100)',
        'Child2BirthDate' => 'Date',
        'Child2DeathDate' => 'Date',
        'Child2StepFoster' => 'Varchar(20)',
        'Child2StepFosterType' => 'Varchar(20)',
        'Child2RaisedSinceBirth' => 'Varchar(20)',
        'Child2RaisedFrom' => 'Date',
        'Child2RaisedTo' => 'Date',

        // Ziffer 3
        'EastGermanyResidence' => 'Varchar(20)',
        'EastGermanyResidenceFrom' => 'Date',
        'EastGermanyResidenceTo' => 'Date',

        // Ziffer 4
        'OtherPensionSystem' => 'Varchar(20)',
        'CivilServicePension' => 'Boolean',
        'CivilServiceFrom' => 'Date',
        'CivilServiceTo' => 'Date',
        'CivilServiceActivity' => 'Varchar(150)',
        'CivilServiceProvider' => 'Varchar(150)',
        'CivilServiceProviderAddress' => 'Text',
        'ChurchPension' => 'Boolean',
        'ChurchServiceFrom' => 'Date',
        'ChurchServiceTo' => 'Date',
        'ChurchServiceActivity' => 'Varchar(150)',
        'ChurchServiceProvider' => 'Varchar(150)',
        'ChurchServiceProviderAddress' => 'Text',
        'PensionReceived' => 'Varchar(20)',
        'PensionReceivedFrom' => 'Date',
        'PensionReceivedTo' => 'Date',
        'PensionReceivedSource' => 'Varchar(150)',

        // Ziffer 5
        'SelfEmployed' => 'Varchar(20)',
        'SelfEmployedType' => 'Varchar(30)',
        'SelfEmployedFrom' => 'Date',
        'SelfEmployedTo' => 'Date',
        'SelfEmployedActivity' => 'Varchar(150)',
        'SelfEmployedIncome' => 'Decimal(9,2)',
        'SelfEmployedHours' => 'Int',
        'FarmRegistered' => 'Varchar(20)',
        'FarmRegisteredFrom' => 'Date',
        'FarmRegisteredTo' => 'Date',

        // Ziffer 6
        'RaisedAbroad' => 'Varchar(20)',
        'RaisedAbroadFrom' => 'Date',
        'RaisedAbroadTo' => 'Date',
        'RaisedAbroadCountry' => 'Varchar(100)',
        'AbroadContributions' => 'Varchar(20)',
        'AbroadContributionsFrom' => 'Date',
        'AbroadContributionsTo' => 'Date',
        'AbroadEmployerRelation' => 'Varchar(20)',
        'AbroadEmployerRelationFrom' => 'Date',
        'AbroadEmployerRelationTo' => 'Date',
        'AbroadEmployerRelationNote' => 'Text',
        'EUActivity' => 'Varchar(20)',
        'EUActivityFrom' => 'Date',
        'EUActivityTo' => 'Date',
        'EUActivityCountry' => 'Varchar(100)',
        'EUActivityInsurer' => 'Varchar(150)',
        'EUActivityReference' => 'Varchar(100)',

        // Ziffer 7 - 9
        'Asylum' => 'Varchar(20)',
        'Expellee' => 'Varchar(20)',
        'ExpelleeRejected' => 'Varchar(20)',
        'NationalityDuringRaising' => 'Varchar(20)',
        'OtherNationality' => 'Varchar(100)',
        'ResidenceTitle' => 'Varchar(150)',
        'ResidenceTitleStatus' => 'Varchar(20)',
        'ResidenceTitleDate' => 'Date',

        // Ziffer 10
        'IntlOrganisation' => 'Varchar(20)',
        'IntlOrganisationFrom' => 'Date',
        'IntlOrganisationTo' => 'Date',
        'IntlOrganisationName' => 'Varchar(150)',
        'ForeignForces' => 'Varchar(20)',
        'ForeignForcesFrom' => 'Date',
        'ForeignForcesTo' => 'Date',
        'ForeignForcesName' => 'Varchar(150)',
        'DiplomaticStaff' => 'Varchar(20)',
        'DiplomaticStaffFrom' => 'Date',
        'DiplomaticStaffTo' => 'Date',
        'DiplomaticStaffName' => 'Varchar(150)',
        'Posted' => 'Varchar(20)',
        'PostedFrom' => 'Date',
        'PostedTo' => 'Date',
        'PostedEmployer' => 'Varchar(150)',
        'PostedWorkplace' => 'Varchar(150)',
        'ExemptionAgreement' => 'Varchar(20)',
        'ExemptionAgreementFrom' => 'Date',
        'ExemptionAgreementTo' => 'Date',
        'ExemptionIssuer' => 'Varchar(150)',
        'ExemptionIssueDate' => 'Date',
        'ExemptionReference' => 'Varchar(100)',

        // Ziffer 11 - 12
        'ApplicantRole' => 'Varchar(20)',
        'RaisingMode' => 'Varchar(20)',
        'OtherParentMainlyRaised' => 'Varchar(20)',
        'OtherParentLastName' => 'Varchar(100)',
        'OtherParentFirstName' => 'Varchar(100)',
        'OtherParentNameSuffix' => 'Varchar(50)',
        'OtherParentNamePrefix' => 'Varchar(50)',
        'OtherParentTitle' => 'Varchar(50)',
        'OtherParentBirthName' => 'Varchar(100)',
        'OtherParentBirthNameSuffix' => 'Varchar(50)',
        'OtherParentBirthNamePrefix' => 'Varchar(50)',
        'OtherParentBirthDate' => 'Date',
        'OtherParentInsuranceNumber' => 'Varchar(12)',
        'OtherParentConfirmation' => 'Varchar(20)',
        'OtherParentConfirmationReason' => 'Varchar(20)',
        'OtherParentConfirmationOther' => 'Varchar(150)',
        'PriorDeclaration' => 'Varchar(20)',
        'PriorDeclarationInstitution' => 'Varchar(150)',

        // Ziffer 13 - 14
        'AccessibleFormat' => 'Varchar(20)',
        'Place' => 'Varchar(100)',
    ];

    private static array $field_mapping = [
        // Header (repeated on every page under the same field name)
        'PAF_VSNR_trim' => 'Compact:InsuranceNumber:1',
        'PAF_AIGR' => 'Compact:Kennzeichen:1',

        // Ziffer 1
        'Q_PAF_Vers_Name' => 'LastName',
        'Q_PAF_Vers_Vorname' => 'FirstName',
        'VERS_NZ_1' => 'NameSuffix',
        'VERS_VORSATZ_1' => 'NamePrefix',
        'VERS_TITEL_1' => 'Title',
        'Q_PAF_Vers_GebName' => 'BirthName',
        'Q_X_PAF_Vers_FrNamen' => 'FormerNames',
        'VERS_NZ_2' => 'BirthNameSuffix',
        'VERS_VORSATZ_2' => 'BirthNamePrefix',
        'Q_PAF_Vers_GebDat_trim' => 'DateFormating:BirthDate:dmY',
        'Q_PAF_Vers_Geschlecht' => 'StateValue:Gender:Q_PAF_Vers_Geschlecht',
        'Q_PAF_Vers_GebOrt' => 'BirthPlace',
        'Q_PAF_Vers_GebLand' => 'BirthCountry',
        'Q_PAF_Vers_Staatsangehörigkeit' => 'Nationality',
        'Q_PAF_Vers_Straße_Postfach' => 'Street',
        'VERS_ADR_ZUSATZ_1' => 'AddressAddition',
        'Q_PAF_Vers_PLZ' => 'PostalCode',
        'Q_PAF_Vers_Wohnort' => 'City',
        'Q_PAF_Vers_Land' => 'Country',
        'TEL_1' => 'Phone',
        'FAX_1' => 'Fax',
        'VERS_LW_ORT_1' => 'LastDomesticResidence',
        'AW_ZUZUG_1' => 'StateValue:MovedFromAbroad:AW_ZUZUG_1',
        'VERS_ZUZUGSDAT_1' => 'DateFormating:MovedDate:dmY',
        'VERS_ZUZUGSORT_1' => 'MovedFrom',
        'VERS_ZUZUGSORT_2' => 'MovedTo',

        // Ziffer 2 - Kind 1
        'KD_Name_1' => 'Child1Name',
        'KD_VORSATZ_1' => 'Child1NamePrefix',
        'KD_GebOrt_1' => 'Child1BirthPlace',
        'KD_GebStaat_1' => 'Child1BirthCountry',
        'DAT_KD_Geb_1' => 'DateFormating:Child1BirthDate:dmY',
        'DAT_KD_TD_1' => 'DateFormating:Child1DeathDate:dmY',
        'AW_KIND_1' => 'StateValue:Child1StepFoster:AW_KIND_1',
        'AW_KD_1' => 'StateValue:Child1StepFosterType:AW_KD_1',
        'AW_ERZIEH_1' => 'StateValue:Child1RaisedSinceBirth:AW_ERZIEH_1',
        'DAT_Erzieh_1' => 'DateFormating:Child1RaisedFrom:dmY',
        'DAT_Erzieh_2' => 'DateFormating:Child1RaisedTo:dmY',

        // Ziffer 2 - Kind 2
        'KD_Name_2' => 'Child2Name',
        'KD_VORSATZ_2' => 'Child2NamePrefix',
        'KD_GebOrt_2' => 'Child2BirthPlace',
        'KD_GebStaat_2' => 'Child2BirthCountry',
        'DAT_KD_Geb_2' => 'DateFormating:Child2BirthDate:dmY',
        'DAT_KD_TD_2' => 'DateFormating:Child2DeathDate:dmY',
        'AW_KIND_2' => 'StateValue:Child2StepFoster:AW_KIND_2',
        'AW_KD_2' => 'StateValue:Child2StepFosterType:AW_KD_2',
        'AW_ERZIEH_2' => 'StateValue:Child2RaisedSinceBirth:AW_ERZIEH_2',
        'DAT_Erzieh_3' => 'DateFormating:Child2RaisedFrom:dmY',
        'DAT_Erzieh_4' => 'DateFormating:Child2RaisedTo:dmY',

        // Ziffer 3
        'AW_WOHNSITZ_1' => 'StateValue:EastGermanyResidence:AW_WOHNSITZ_1',
        'DAT_WOHNSITZ' => 'DateFormating:EastGermanyResidenceFrom:dmY',
        'DAT_WOHNSITZ_1' => 'DateFormating:EastGermanyResidenceTo:dmY',

        // Ziffer 4
        'AW_ASS_1' => 'StateValue:OtherPensionSystem:AW_ASS_1',
        'AW_ASS_2' => 'StateValue:CivilServicePension:AW_ASS_2',
        'DAT_vom_Versorg_1' => 'DateFormating:CivilServiceFrom:dmY',
        'DAT_bis_Versorg_1' => 'DateFormating:CivilServiceTo:dmY',
        'Art_Tätigk_1' => 'CivilServiceActivity',
        'Versorg_1' => 'CivilServiceProvider',
        'VT_Anschr_1' => 'SingleLine:CivilServiceProviderAddress:1',
        'AW_ASS_3' => 'StateValue:ChurchPension:AW_ASS_3',
        'DAT_vom_Versorg_2' => 'DateFormating:ChurchServiceFrom:dmY',
        'DAT_bis_Versorg_2' => 'DateFormating:ChurchServiceTo:dmY',
        'Art_Tätigk_2' => 'ChurchServiceActivity',
        'Versorg_2' => 'ChurchServiceProvider',
        'VT_Anschr_2' => 'SingleLine:ChurchServiceProviderAddress:1',
        'AW_Versorg_ASS' => 'StateValue:PensionReceived:AW_Versorg_ASS',
        'DAT_vom_Alter_1' => 'DateFormating:PensionReceivedFrom:dmY',
        'DAT_bis_Alter_1' => 'DateFormating:PensionReceivedTo:dmY',
        'AR_Stelle' => 'PensionReceivedSource',

        // Ziffer 5
        'AW_Selbst_1' => 'StateValue:SelfEmployed:AW_Selbst_1',
        'AW_Selbst_2' => 'StateValue:SelfEmployedType:AW_Selbst_2',
        'DAT_VOM_Selbst' => 'DateFormating:SelfEmployedFrom:dmY',
        'DAT_BIS_Selbst' => 'DateFormating:SelfEmployedTo:dmY',
        'Art_Tätigk' => 'SelfEmployedActivity',
        'SELBST_WAEHRUNG' => 'CurrencyIfSet:SelfEmployedIncome:EUR',
        'VERS_Selbst_Betrag' => 'AmountPart:SelfEmployedIncome:int',
        'VERS_Selbst_Betrag_NK' => 'AmountPart:SelfEmployedIncome:dec',
        'AZ' => 'NonZero:SelfEmployedHours:1',
        'AW_LW' => 'StateValue:FarmRegistered:AW_LW',
        'DAT_VOM_LW' => 'DateFormating:FarmRegisteredFrom:dmY',
        'DAT_BIS_LW' => 'DateFormating:FarmRegisteredTo:dmY',

        // Ziffer 6
        'AW_Ausland' => 'StateValue:RaisedAbroad:AW_Ausland',
        'DAT_VOM_EZ' => 'DateFormating:RaisedAbroadFrom:dmY',
        'DAT_BIS_EZ' => 'DateFormating:RaisedAbroadTo:dmY',
        'Ausl_Staat' => 'RaisedAbroadCountry',
        'AW_Ausl_PB' => 'StateValue:AbroadContributions:AW_Ausl_PB',
        'DAT_VOM_AUSL_PB' => 'DateFormating:AbroadContributionsFrom:dmY',
        'DAT_BIS_AUSL_PB' => 'DateFormating:AbroadContributionsTo:dmY',
        'AW_Ausl_Bezieh' => 'StateValue:AbroadEmployerRelation:AW_Ausl_Bezieh',
        'DAT_VOM_AUSL' => 'DateFormating:AbroadEmployerRelationFrom:dmY',
        'DAT_BIS_AUSL' => 'DateFormating:AbroadEmployerRelationTo:dmY',
        'Ausl_Erläut_1' => 'TextLine:AbroadEmployerRelationNote:1',
        'Ausl_Erläut_2' => 'TextLine:AbroadEmployerRelationNote:2',
        'AW_Ausl_WZ' => 'StateValue:EUActivity:AW_Ausl_WZ',
        'DAT_VOM_AUSL_1' => 'DateFormating:EUActivityFrom:dmY',
        'DAT_BIS_AUSL_1' => 'DateFormating:EUActivityTo:dmY',
        'Ausl_Staat_WZ' => 'EUActivityCountry',
        'Ausl_VT_WZ' => 'EUActivityInsurer',
        'Ausl_AZ_WZ' => 'EUActivityReference',

        // Ziffer 7 - 9
        'AW_Asyl' => 'StateValue:Asylum:AW_Asyl',
        'AW_Vertriebener' => 'StateValue:Expellee:AW_Vertriebener',
        'AW_Ablehnung' => 'StateValue:ExpelleeRejected:AW_Ablehnung',
        'AW_Angehörigk_1' => 'StateValue:NationalityDuringRaising:AW_Angehörigk_1',
        'AW_Angehörigk_2' => 'StateValue:NationalityDuringRaising:AW_Angehörigk_2',
        'AW_Angehörigk_3' => 'StateValue:NationalityDuringRaising:AW_Angehörigk_3',
        'Staat_andere' => 'OtherNationality',
        'Status_1' => 'ResidenceTitle',
        'AW_Status_1' => 'StateValue:ResidenceTitleStatus:AW_Status_1',
        'DAT_BIS_Status' => 'DateFormating:ResidenceTitleDate:dmY',

        // Ziffer 10
        'AW_Ausnahme' => 'StateValue:IntlOrganisation:AW_Ausnahme',
        'DAT_VOM_ORG' => 'DateFormating:IntlOrganisationFrom:dmY',
        'DAT_BIS_ORG' => 'DateFormating:IntlOrganisationTo:dmY',
        'Organisation_1' => 'IntlOrganisationName',
        'AW_Truppen' => 'StateValue:ForeignForces:AW_Truppen',
        'DAT_VOM_Truppe' => 'DateFormating:ForeignForcesFrom:dmY',
        'DAT_BIS_Truppe' => 'DateFormating:ForeignForcesTo:dmY',
        'Organisation_2' => 'ForeignForcesName',
        'AW_Personal' => 'StateValue:DiplomaticStaff:AW_Personal',
        'DAT_VOM_Personal' => 'DateFormating:DiplomaticStaffFrom:dmY',
        'DAT_BIS_Personal' => 'DateFormating:DiplomaticStaffTo:dmY',
        'Organisation_3' => 'DiplomaticStaffName',
        'AW_Entsend' => 'StateValue:Posted:AW_Entsend',
        'DAT_VOM_begrenzt' => 'DateFormating:PostedFrom:dmY',
        'DAT_BIS_begrenzt' => 'DateFormating:PostedTo:dmY',
        'AG_1' => 'PostedEmployer',
        'Besch_Stelle' => 'PostedWorkplace',
        'AW_ZW' => 'StateValue:ExemptionAgreement:AW_ZW',
        'DAT_VOM_Vereinb' => 'DateFormating:ExemptionAgreementFrom:dmY',
        'DAT_BIS_Vereinb' => 'DateFormating:ExemptionAgreementTo:dmY',
        'Vereinbarung' => 'ExemptionIssuer',
        'DAT_Ausstellung' => 'DateFormating:ExemptionIssueDate:dmY',
        'Vereinbarung_AZ' => 'ExemptionReference',

        // Ziffer 11
        'AW_ERZIEHUNGSZR_1' => 'StateValue:RaisingMode:AW_ERZIEHUNGSZR_1',
        'AW_ERZIEHUNGSZR_2' => 'StateValue:OtherParentMainlyRaised:AW_ERZIEHUNGSZR_2',
        'ET_Name' => 'OtherParentLastName',
        'ET_VN' => 'OtherParentFirstName',
        'ET_NZ_1' => 'OtherParentNameSuffix',
        'ET_VORSATZ_1' => 'OtherParentNamePrefix',
        'ET_TITEL_1' => 'OtherParentTitle',
        'ET_GN' => 'OtherParentBirthName',
        'ET_NZ_2' => 'OtherParentBirthNameSuffix',
        'ET_VORSATZ_2' => 'OtherParentBirthNamePrefix',
        'ET_GD' => 'DateFormating:OtherParentBirthDate:dmY',
        'ET_VERSNR' => 'Compact:OtherParentInsuranceNumber:1',
        'AW_Bestätigung_ET' => 'StateValue:OtherParentConfirmation:AW_Bestätigung_ET',
        'AW_Bestätigung_ET_1' => 'StateValue:OtherParentConfirmationReason:AW_Bestätigung_ET_1',
        'Gründe' => 'OtherParentConfirmationOther',

        // Ziffer 12
        'AW_Erklärung' => 'StateValue:PriorDeclaration:AW_Erklärung',
        'Name_RVT' => 'PriorDeclarationInstitution',

        // Ziffer 13 - 14
        'AW_BARRIERE_1' => 'StateValue:AccessibleFormat:AW_BARRIERE_1',
        'VERS_O_DAT_1' => 'PlaceAndDate:Place:1',

        // Not filled on purpose: DATUM (11.3 other parent signature date),
        // Ziffer 15 (AW_BESTAETIGUNG_*, BESTAETIGUNG_*, DATUM_1, DATUM_2) - filled by the authority.
    ];

    /**
     * PDF button field => [stored value => PDF on-state]. Values not listed leave the box unchecked.
     * On-state names verified against `pdftk dump_data_fields` and the rendered form positions.
     */
    private static array $state_map = [
        'Q_PAF_Vers_Geschlecht' => ['male' => 'ja', 'female' => 'ja1', 'unspecified' => 'ja2', 'diverse' => 'ja3'],
        'AW_ZUZUG_1' => self::YES_NO,
        'AW_KIND_1' => self::YES_NO,
        'AW_KD_1' => ['step' => 'ja', 'foster' => 'ja1'],
        'AW_ERZIEH_1' => self::YES_NO,
        'AW_KIND_2' => self::YES_NO,
        'AW_KD_2' => ['step' => 'ja', 'foster' => 'ja1'],
        'AW_ERZIEH_2' => self::YES_NO,
        'AW_WOHNSITZ_1' => self::YES_NO,
        'AW_ASS_1' => self::YES_NO,
        'AW_ASS_2' => self::CHECKED,
        'AW_ASS_3' => self::CHECKED,
        'AW_Versorg_ASS' => self::YES_NO,
        'AW_Selbst_1' => self::YES_NO,
        'AW_Selbst_2' => [
            'self_employed' => 'ja',
            'self_employed_relative' => 'ja1',
            'farmer' => 'ja2',
            'farmer_relative' => 'ja3',
        ],
        'AW_LW' => self::YES_NO,
        'AW_Ausland' => self::YES_NO,
        'AW_Ausl_PB' => ['no' => 'nein', 'yes' => 'ja '], // PDF on-state has a trailing space
        'AW_Ausl_Bezieh' => self::YES_NO,
        'AW_Ausl_WZ' => self::YES_NO,
        'AW_Asyl' => self::YES_NO,
        'AW_Vertriebener' => self::YES_NO,
        'AW_Ablehnung' => self::YES_NO,
        'AW_Angehörigk_1' => ['german' => 'ja', 'naturalized' => 'ja'], // "deutsch" incl. sub-option
        'AW_Angehörigk_2' => ['naturalized' => 'ja'],
        'AW_Angehörigk_3' => ['other' => 'ja'],
        'AW_Status_1' => ['granted' => 'ja', 'not_applied' => 'ja1', 'applied' => 'ja2'],
        'AW_Ausnahme' => self::YES_NO,
        'AW_Truppen' => self::YES_NO,
        'AW_Personal' => self::YES_NO,
        'AW_Entsend' => self::YES_NO,
        'AW_ZW' => self::YES_NO,
        'AW_ERZIEHUNGSZR_1' => ['joint' => 'ja', 'alone' => 'ja1', 'interrupted' => 'ja2', 'partly' => 'ja3'],
        'AW_ERZIEHUNGSZR_2' => ['no' => 'nein', 'partly' => 'ja', 'yes' => 'ja1'], // order on form: nein, teilweise, ja
        'AW_Bestätigung_ET' => ['later' => 'ja', 'impossible' => 'ja1'],
        'AW_Bestätigung_ET_1' => ['deceased' => 'ja', 'unknown' => 'ja1', 'other' => 'ja2'],
        'AW_Erklärung' => self::YES_NO,
        'AW_BARRIERE_1' => [
            'large_print' => 'ja',
            'braille_short' => 'ja1',
            'braille_full' => 'ja2',
            'cd' => 'ja3',
            'daisy' => 'ja4',
        ],
    ];

    /**
     * Dependency rules, mirrored by the display-logic in the steps (see FieldConditions for the format).
     * Order matters: parents before children.
     */
    private static array $field_conditions = [
        // Ziffer 1
        [['Country', 'LastDomesticResidence'], ['LivesAbroad' => '1']],
        [['MovedDate', 'MovedFrom', 'MovedTo'], ['MovedFromAbroad' => 'yes']],

        // Ziffer 2
        [['Child1StepFosterType'], ['Child1StepFoster' => 'yes']],
        [['Child1RaisedFrom', 'Child1RaisedTo'], ['Child1RaisedSinceBirth' => 'no']],
        [
            [
                'Child2Name', 'Child2NamePrefix', 'Child2BirthPlace', 'Child2BirthCountry', 'Child2BirthDate',
                'Child2DeathDate', 'Child2StepFoster', 'Child2RaisedSinceBirth',
            ],
            ['HasSecondChild' => 'yes'],
        ],
        [['Child2StepFosterType'], ['Child2StepFoster' => 'yes']],
        [['Child2RaisedFrom', 'Child2RaisedTo'], ['Child2RaisedSinceBirth' => 'no']],

        // Ziffer 3
        [['EastGermanyResidenceFrom', 'EastGermanyResidenceTo'], ['EastGermanyResidence' => 'yes']],

        // Ziffer 4
        [['CivilServicePension', 'ChurchPension'], ['OtherPensionSystem' => 'yes']],
        [
            [
                'CivilServiceFrom', 'CivilServiceTo', 'CivilServiceActivity', 'CivilServiceProvider',
                'CivilServiceProviderAddress',
            ],
            ['CivilServicePension' => '1'],
        ],
        [
            [
                'ChurchServiceFrom', 'ChurchServiceTo', 'ChurchServiceActivity', 'ChurchServiceProvider',
                'ChurchServiceProviderAddress',
            ],
            ['ChurchPension' => '1'],
        ],
        [['PensionReceivedFrom', 'PensionReceivedTo', 'PensionReceivedSource'], ['PensionReceived' => 'yes']],

        // Ziffer 5
        [
            [
                'SelfEmployedType', 'SelfEmployedFrom', 'SelfEmployedTo', 'SelfEmployedActivity',
                'SelfEmployedIncome', 'SelfEmployedHours',
            ],
            ['SelfEmployed' => 'yes'],
        ],
        [['FarmRegistered'], ['SelfEmployedType' => ['farmer', 'farmer_relative']]],
        [['FarmRegisteredFrom', 'FarmRegisteredTo'], ['FarmRegistered' => 'yes']],

        // Ziffer 6
        [
            ['RaisedAbroadFrom', 'RaisedAbroadTo', 'RaisedAbroadCountry', 'AbroadContributions', 'EUActivity'],
            ['RaisedAbroad' => 'yes'],
        ],
        [['AbroadContributionsFrom', 'AbroadContributionsTo'], ['AbroadContributions' => 'yes']],
        [['AbroadEmployerRelation'], ['AbroadContributions' => 'no']],
        [
            ['AbroadEmployerRelationFrom', 'AbroadEmployerRelationTo', 'AbroadEmployerRelationNote'],
            ['AbroadEmployerRelation' => 'yes'],
        ],
        [
            ['EUActivityFrom', 'EUActivityTo', 'EUActivityCountry', 'EUActivityInsurer', 'EUActivityReference'],
            ['EUActivity' => 'yes'],
        ],

        // Ziffer 8 - 9
        [['ExpelleeRejected'], ['Expellee' => 'no']],
        [['OtherNationality', 'ResidenceTitle', 'ResidenceTitleStatus'], ['NationalityDuringRaising' => 'other']],
        [['ResidenceTitleDate'], ['ResidenceTitleStatus' => 'granted']],

        // Ziffer 10
        [['IntlOrganisationFrom', 'IntlOrganisationTo', 'IntlOrganisationName'], ['IntlOrganisation' => 'yes']],
        [['ForeignForcesFrom', 'ForeignForcesTo', 'ForeignForcesName'], ['ForeignForces' => 'yes']],
        [['DiplomaticStaffFrom', 'DiplomaticStaffTo', 'DiplomaticStaffName'], ['DiplomaticStaff' => 'yes']],
        [['PostedFrom', 'PostedTo', 'PostedEmployer', 'PostedWorkplace'], ['Posted' => 'yes']],
        [
            [
                'ExemptionAgreementFrom', 'ExemptionAgreementTo', 'ExemptionIssuer', 'ExemptionIssueDate',
                'ExemptionReference',
            ],
            ['ExemptionAgreement' => 'yes'],
        ],

        // Ziffer 11
        [['RaisingMode'], ['ApplicantRole' => 'mother']],
        [['OtherParentMainlyRaised'], ['RaisingMode' => 'joint']],
        [
            [
                'OtherParentLastName', 'OtherParentFirstName', 'OtherParentNameSuffix', 'OtherParentNamePrefix',
                'OtherParentTitle', 'OtherParentBirthName', 'OtherParentBirthNameSuffix',
                'OtherParentBirthNamePrefix', 'OtherParentBirthDate', 'OtherParentInsuranceNumber',
                'OtherParentConfirmation',
            ],
            ['OtherParentMainlyRaised' => 'no'],
        ],
        [['OtherParentConfirmationReason'], ['OtherParentConfirmation' => 'impossible']],
        [['OtherParentConfirmationOther'], ['OtherParentConfirmationReason' => 'other']],

        // Ziffer 12 - skipped when raised alone without interruption ("weiter bei Ziffer 13")
        [
            ['PriorDeclaration'],
            ['ApplicantRole' => 'other_parent'],
            ['RaisingMode' => ['joint', 'interrupted', 'partly']],
        ],
        [['PriorDeclarationInstitution'], ['PriorDeclaration' => 'yes']],
    ];

    private static float $signature_default_x = 108.0;
    private static float $signature_default_y = 110.0;
    private static float $signature_width = 55.0;

    /** Max characters per line for multi-line text split across single-line PDF fields. */
    private const TEXT_LINE_LENGTH = 70;

    /**
     * Safety net: values of inactive (hidden) fields are never stored, whatever the steps delivered.
     */
    protected function onBeforeWrite(): void
    {
        parent::onBeforeWrite();

        $rules = (array) $this->config()->get('field_conditions');
        $data = [];
        foreach (FieldConditions::referencedFields($rules) as $field) {
            $data[$field] = $this->getField($field);
        }

        $cleaned = FieldConditions::apply($data, $rules);
        foreach ($cleaned as $field => $value) {
            if ($value === null && $data[$field] !== null) {
                $this->setField($field, null);
            }
        }
    }

    // ---------------------------------------------------------------------------------------------
    // Mapping helpers (signature fixed by BaseSubmission::getArrayDataForPDF: method($field, $arg))
    // ---------------------------------------------------------------------------------------------

    /** Checkbox group: stored value -> PDF on-state of $pdfField (via $state_map). */
    public function StateValue(string $fieldName, string $pdfField): string
    {
        $map = (array) ($this->config()->get('state_map')[$pdfField] ?? []);
        $value = (string) ($this->getField($fieldName) ?? '');

        return (string) ($map[$value] ?? '');
    }

    /** Comb fields (Versicherungsnummer, Kennzeichen): uppercase, no whitespace. */
    public function Compact(string $fieldName, string $unused = ''): string
    {
        return strtoupper((string) preg_replace('/\s+/', '', (string) ($this->getField($fieldName) ?? '')));
    }

    /** Amount split into "Betrag" (int) and "NK" (2-digit cents). Empty for 0/null. */
    public function AmountPart(string $fieldName, string $part): string
    {
        $amount = (float) ($this->getField($fieldName) ?? 0);
        if ($amount <= 0) {
            return '';
        }

        $cents = (int) round($amount * 100);

        return $part === 'dec'
            ? str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT)
            : (string) intdiv($cents, 100);
    }

    public function CurrencyIfSet(string $fieldName, string $currency): string
    {
        return (float) ($this->getField($fieldName) ?? 0) > 0 ? $currency : '';
    }

    /** Int columns default to 0 - don't print "0" into the PDF. */
    public function NonZero(string $fieldName, string $unused = ''): string
    {
        $value = (int) ($this->getField($fieldName) ?? 0);

        return $value !== 0 ? (string) $value : '';
    }

    /** Line N of a text wrapped over several single-line PDF fields; the last line takes the rest. */
    public function TextLine(string $fieldName, string $line): string
    {
        $text = $this->SingleLine($fieldName);
        if ($text === '') {
            return '';
        }

        $lines = explode("\n", wordwrap($text, self::TEXT_LINE_LENGTH, "\n", true));
        $index = max(1, (int) $line) - 1;

        if ($index === 1) {
            return implode(' ', array_slice($lines, 1));
        }

        return $lines[$index] ?? '';
    }

    /** Textarea content for a single-line PDF field. */
    public function SingleLine(string $fieldName, string $unused = ''): string
    {
        return trim((string) preg_replace('/\s+/', ' ', (string) ($this->getField($fieldName) ?? '')));
    }

    /** Ziffer 14 "Ort, Datum" - date of submission. */
    public function PlaceAndDate(string $fieldName, string $unused = ''): string
    {
        $place = trim((string) ($this->getField($fieldName) ?? ''));
        $timestamp = $this->Created ? strtotime((string) $this->Created) : time();

        return ltrim($place . ', ' . date('d.m.Y', $timestamp ?: time()), ', ');
    }

    // ---------------------------------------------------------------------------------------------
    // PDF generation
    // ---------------------------------------------------------------------------------------------

    public function generateFilledPDF(): Pdf
    {
        $basePdfPath = getcwd() . '/assets/' . $this->FormSubmission()->getBasePDFLink('PDFFile');

        $pdf = new Pdf($basePdfPath);
        $pdf->fillForm($this->getArrayDataForPDF())
            ->needAppearances()
            ->flatten();

        $signature = trim((string) $this->Signature);
        if ($signature === '') {
            return $pdf;
        }

        $tmpFilled = sys_get_temp_dir() . '/v0800_filled_' . uniqid('', true) . '.pdf';
        if (!$pdf->saveAs($tmpFilled)) {
            throw new RuntimeException('pdftk error: ' . $pdf->getError());
        }

        $tmpSignature = $this->writeSignatureImage($signature);

        try {
            $outputPath = $this->stampSignature($tmpFilled, $tmpSignature);
        } finally {
            @unlink($tmpSignature);
            @unlink($tmpFilled);
        }

        return new Pdf($outputPath);
    }

    /**
     * Places the signature on the last page ("Unterschrift des Elternteils, der den Antrag stellt").
     * Position: PDFMultiFormPage.SignaturePositionX/Y (mm) if set, otherwise module config.
     */
    private function stampSignature(string $sourcePdf, string $signaturePng): string
    {
        $holder = $this->getMyHolderPage();
        $x = (float) ($holder?->SignaturePositionX ?: $this->config()->get('signature_default_x'));
        $y = (float) ($holder?->SignaturePositionY ?: $this->config()->get('signature_default_y'));
        $width = (float) $this->config()->get('signature_width');

        $fpdi = new Fpdi();
        $fpdi->SetAutoPageBreak(false);
        $pageCount = $fpdi->setSourceFile($sourcePdf);

        for ($i = 1; $i <= $pageCount; $i++) {
            $template = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($template);

            $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $fpdi->useTemplate($template, 0, 0, $size['width'], $size['height']);

            if ($i === $pageCount) {
                $fpdi->Image($signaturePng, $x, $y, $width, 0, 'PNG');
            }
        }

        $outputPath = sys_get_temp_dir() . '/v0800_signed_' . uniqid('', true) . '.pdf';
        $fpdi->Output('F', $outputPath);

        return $outputPath;
    }

    /**
     * jSignature delivers a PNG data URI; base64 SVG is supported as fallback (needs Imagick).
     */
    private function writeSignatureImage(string $signature): string
    {
        $path = sys_get_temp_dir() . '/v0800_sig_' . uniqid('', true) . '.png';

        if (preg_match('#^data:image/png;base64,(.+)$#i', $signature, $matches)) {
            $png = base64_decode($matches[1], true);
            if ($png === false || file_put_contents($path, $png) === false) {
                throw new RuntimeException('Invalid PNG signature payload.');
            }

            return $path;
        }

        $svgBase64 = preg_match('#^data:image/svg\+xml(?:;charset=[^;]+)?;base64,(.+)$#i', $signature, $matches)
            ? $matches[1]
            : $signature;
        $svg = base64_decode($svgBase64, true);
        if ($svg === false) {
            throw new RuntimeException('Invalid signature payload.');
        }

        $imagick = new \Imagick();
        $imagick->readImageBlob($svg);
        $imagick->setBackgroundColor(new \ImagickPixel('white'));
        $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
        $imagick->setImageFormat('png');
        $imagick->writeImage($path);
        $imagick->clear();

        return $path;
    }
}
