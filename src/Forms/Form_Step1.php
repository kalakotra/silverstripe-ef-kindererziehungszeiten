<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\MultiForm\Models\MultiFormStep;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * Ziffer 1 - Angaben zur Person (incl. "noch Ziffer 1": Zuzug aus dem Ausland).
 */
class Form_Step1 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 1;

    private static $next_steps = Form_Step2::class;

    private static array $conditional_required = [
        'Country',
        'MovedDate',
        'MovedFrom',
        'MovedTo',
    ];

    public function getFields(): FieldList
    {
        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Angaben zur Person'), 4),
            TextField::create('InsuranceNumber', _t(__CLASS__ . '.InsuranceNumber', 'Versicherungsnummer (soweit bekannt)'))
                ->setMaxLength(12)
                ->setDescription(_t(__CLASS__ . '.InsuranceNumberDescription', '12 Zeichen, z. B. 12150385M012')),
            TextField::create('Kennzeichen', _t(__CLASS__ . '.Kennzeichen', 'Kennzeichen (soweit bekannt)'))
                ->setMaxLength(4),

            TextField::create('LastName', _t(__CLASS__ . '.LastName', 'Name')),
            TextField::create('FirstName', _t(__CLASS__ . '.FirstName', 'Vorname')),
            TextField::create('NameSuffix', _t(__CLASS__ . '.NameSuffix', 'Namenszusatz (Beispiel: Freifrau, Graf)')),
            TextField::create('NamePrefix', _t(__CLASS__ . '.NamePrefix', 'Vorsatzworte zum Namen (Beispiel: von, van, de)')),
            TextField::create('Title', _t(__CLASS__ . '.Title', 'Titel (Beispiel: Prof. Dr. med.)')),
            TextField::create('BirthName', _t(__CLASS__ . '.BirthName', 'Geburtsname')),
            TextField::create('FormerNames', _t(__CLASS__ . '.FormerNames', 'Frühere Namen')),
            TextField::create('BirthNameSuffix', _t(__CLASS__ . '.BirthNameSuffix', 'Namenszusatz zum Geburtsnamen (Beispiel: Freifrau, Graf)')),
            TextField::create('BirthNamePrefix', _t(__CLASS__ . '.BirthNamePrefix', 'Vorsatzworte zum Geburtsnamen (Beispiel: von, van, de)')),

            DateField::create('BirthDate', _t(__CLASS__ . '.BirthDate', 'Geburtsdatum')),
            DropdownField::create('Gender', _t(__CLASS__ . '.Gender', 'Geschlecht'))
                ->setSource([
                    'male' => _t(__CLASS__ . '.Gender.male', 'männlich'),
                    'female' => _t(__CLASS__ . '.Gender.female', 'weiblich'),
                    'unspecified' => _t(__CLASS__ . '.Gender.unspecified', 'ohne Eintrag'),
                    'diverse' => _t(__CLASS__ . '.Gender.diverse', 'divers'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            TextField::create('BirthPlace', _t(__CLASS__ . '.BirthPlace', 'Geburtsort')),
            TextField::create('BirthCountry', _t(__CLASS__ . '.BirthCountry', 'Geburtsland')),
            TextField::create('Nationality', _t(__CLASS__ . '.Nationality', 'Staatsangehörigkeit (gegebenenfalls frühere Staatsangehörigkeit bis)')),

            HeaderField::create('AddressHeader', _t(__CLASS__ . '.AddressHeader', 'Anschrift'), 5),
            TextField::create('Street', _t(__CLASS__ . '.Street', 'Straße, Hausnummer')),
            TextField::create('AddressAddition', _t(__CLASS__ . '.AddressAddition', 'Adresszusatz')),
            TextField::create('PostalCode', _t(__CLASS__ . '.PostalCode', 'Postleitzahl'))
                ->setMaxLength(10),
            TextField::create('City', _t(__CLASS__ . '.City', 'Wohnort')),
            CheckboxField::create('LivesAbroad', _t(__CLASS__ . '.LivesAbroad', 'Ich wohne derzeit im Ausland')),
            Wrapper::create(
                TextField::create('Country', _t(__CLASS__ . '.Country', 'Land')),
                TextField::create('LastDomesticResidence', _t(__CLASS__ . '.LastDomesticResidence', 'Letzter Wohnsitz im Inland (Ort, Bundesland)')),
            )
                ->displayIf('LivesAbroad')->isChecked()->end(),
            TextField::create('Phone', _t(__CLASS__ . '.Phone', 'Telefon')),
            TextField::create('Fax', _t(__CLASS__ . '.Fax', 'Telefax')),

            HeaderField::create('MovedHeader', _t(__CLASS__ . '.MovedHeader', 'Zuzug'), 5),
            DropdownField::create('MovedFromAbroad', _t(__CLASS__ . '.MovedFromAbroad', 'Zuzug aus dem Ausland?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.MovedFromAbroad.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.MovedFromAbroad.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                DateField::create('MovedDate', _t(__CLASS__ . '.MovedDate', 'Zuzug am')),
                TextField::create('MovedFrom', _t(__CLASS__ . '.MovedFrom', 'Zuzug erfolgte aus (Ort, Gebiet, Staat)')),
                TextField::create('MovedTo', _t(__CLASS__ . '.MovedTo', 'Zuzug erfolgte nach (Ort, Bundesland)')),
            )
                ->displayIf('MovedFromAbroad')->isEqualTo('yes')->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'LastName',
            'FirstName',
            'BirthDate',
            'Gender',
            'BirthPlace',
            'BirthCountry',
            'Nationality',
            'Street',
            'PostalCode',
            'City',
            'MovedFromAbroad',
        ]);
    }
}
