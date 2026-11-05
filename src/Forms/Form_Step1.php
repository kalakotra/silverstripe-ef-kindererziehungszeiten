<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Ziffer 1 - Angaben zur Person (incl. "noch Ziffer 1": Zuzug aus dem Ausland).
 */
class Form_Step1 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 1;

    private static $next_steps = Form_Step2::class;

    private static array $required_fields = [
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
    ];

    private static array $conditional_required = [
        'Country',
        'MovedDate',
        'MovedFrom',
        'MovedTo',
    ];

    public function getFields(): FieldList
    {
        return FieldList::create(
            $this->header('Angaben zur Person'),
            $this->text('InsuranceNumber', 'Versicherungsnummer (soweit bekannt)', 12)
                ->setDescription($this->t('InsuranceNumberDescription', '12 Zeichen, z. B. 12150385M012')),
            $this->text('Kennzeichen', 'Kennzeichen (soweit bekannt)', 4),

            $this->text('LastName', 'Name'),
            $this->text('FirstName', 'Vorname'),
            $this->text('NameSuffix', 'Namenszusatz (Beispiel: Freifrau, Graf)'),
            $this->text('NamePrefix', 'Vorsatzworte zum Namen (Beispiel: von, van, de)'),
            $this->text('Title', 'Titel (Beispiel: Prof. Dr. med.)'),
            $this->text('BirthName', 'Geburtsname'),
            $this->text('FormerNames', 'Frühere Namen'),
            $this->text('BirthNameSuffix', 'Namenszusatz zum Geburtsnamen (Beispiel: Freifrau, Graf)'),
            $this->text('BirthNamePrefix', 'Vorsatzworte zum Geburtsnamen (Beispiel: von, van, de)'),

            $this->date('BirthDate', 'Geburtsdatum'),
            $this->dropdown('Gender', 'Geschlecht', [
                'male' => 'männlich',
                'female' => 'weiblich',
                'unspecified' => 'ohne Eintrag',
                'diverse' => 'divers',
            ]),
            $this->text('BirthPlace', 'Geburtsort'),
            $this->text('BirthCountry', 'Geburtsland'),
            $this->text('Nationality', 'Staatsangehörigkeit (gegebenenfalls frühere Staatsangehörigkeit bis)'),

            $this->subHeader('AddressHeader', 'Anschrift'),
            $this->text('Street', 'Straße, Hausnummer'),
            $this->text('AddressAddition', 'Adresszusatz'),
            $this->text('PostalCode', 'Postleitzahl', 10),
            $this->text('City', 'Wohnort'),
            CheckboxField::create('LivesAbroad', $this->t('LivesAbroad', 'Ich wohne derzeit im Ausland')),
            $this->group(
                'LivesAbroadGroup',
                $this->text('Country', 'Land'),
                $this->text('LastDomesticResidence', 'Letzter Wohnsitz im Inland (Ort, Bundesland)')
            )->displayIf('LivesAbroad')->isChecked()->end(),
            $this->text('Phone', 'Telefon'),
            $this->text('Fax', 'Telefax'),

            $this->subHeader('MovedHeader', 'Zuzug'),
            $this->yesNo('MovedFromAbroad', 'Zuzug aus dem Ausland?'),
            $this->group(
                'MovedGroup',
                $this->date('MovedDate', 'Zuzug am'),
                $this->text('MovedFrom', 'Zuzug erfolgte aus (Ort, Gebiet, Staat)'),
                $this->text('MovedTo', 'Zuzug erfolgte nach (Ort, Bundesland)')
            )->displayIf('MovedFromAbroad')->isEqualTo('yes')->end()
        );
    }
}
