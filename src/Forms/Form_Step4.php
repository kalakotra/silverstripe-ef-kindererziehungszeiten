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
 * Ziffer 3 - Wohnsitz während der Erziehungszeiten
 * Ziffer 4 - Deutsche Versorgungsanwartschaften außerhalb der Rentenversicherung
 */
class Form_Step4 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 4;

    private static $next_steps = Form_Step5::class;

    private static array $conditional_required = [
        'EastGermanyResidenceFrom',
        'EastGermanyResidenceTo',
        'CivilServiceFrom',
        'CivilServiceTo',
        'CivilServiceActivity',
        'CivilServiceProvider',
        'ChurchServiceFrom',
        'ChurchServiceTo',
        'ChurchServiceActivity',
        'ChurchServiceProvider',
        'PensionReceivedFrom',
        'PensionReceivedTo',
        'PensionReceivedSource',
    ];

    public function getFields(): FieldList
    {
        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Wohnsitz und Versorgungsanwartschaften'), 4),

            DropdownField::create('EastGermanyResidence', _t(__CLASS__ . '.EastGermanyResidence', '3.1 Hatten Sie während der Erziehungszeiten Ihren Wohnsitz in der DDR oder den neuen Bundesländern?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.EastGermanyResidence.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.EastGermanyResidence.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                DateField::create('EastGermanyResidenceFrom', _t(__CLASS__ . '.EastGermanyResidenceFrom', 'vom')),
                DateField::create('EastGermanyResidenceTo', _t(__CLASS__ . '.EastGermanyResidenceTo', 'bis')),
            )
                ->displayIf('EastGermanyResidence')->isEqualTo('yes')->end(),

            DropdownField::create('OtherPensionSystem', _t(__CLASS__ . '.OtherPensionSystem', '4.1 Haben Sie während der Erziehungszeiten einem anderen deutschen Alterssicherungssystem angehört?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.OtherPensionSystem.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.OtherPensionSystem.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                CheckboxField::create('CivilServicePension', _t(__CLASS__ . '.CivilServicePension', 'Beamtenrechtliche oder ähnliche Versorgung (zum Beispiel als Beamter, Richter oder DO-Angestellter)')),
                Wrapper::create(
                    DateField::create('CivilServiceFrom', _t(__CLASS__ . '.CivilServiceFrom', 'vom')),
                    DateField::create('CivilServiceTo', _t(__CLASS__ . '.CivilServiceTo', 'bis')),
                    TextField::create('CivilServiceActivity', _t(__CLASS__ . '.CivilServiceActivity', 'Art der Tätigkeit')),
                    TextField::create('CivilServiceProvider', _t(__CLASS__ . '.CivilServiceProvider', 'Versorgungsträger')),
                    TextField::create('CivilServiceProviderAddress', _t(__CLASS__ . '.CivilServiceProviderAddress', 'Anschrift des Versorgungsträgers')),
                )
                    ->displayIf('CivilServicePension')->isChecked()->end(),

                CheckboxField::create('ChurchPension', _t(__CLASS__ . '.ChurchPension', 'Kirchenrechtliche Versorgung (zum Beispiel als Geistlicher)')),
                Wrapper::create(
                    DateField::create('ChurchServiceFrom', _t(__CLASS__ . '.ChurchServiceFrom', 'vom')),
                    DateField::create('ChurchServiceTo', _t(__CLASS__ . '.ChurchServiceTo', 'bis')),
                    TextField::create('ChurchServiceActivity', _t(__CLASS__ . '.ChurchServiceActivity', 'Art der Tätigkeit')),
                    TextField::create('ChurchServiceProvider', _t(__CLASS__ . '.ChurchServiceProvider', 'Versorgungsträger')),
                    TextField::create('ChurchServiceProviderAddress', _t(__CLASS__ . '.ChurchServiceProviderAddress', 'Anschrift des Versorgungsträgers')),
                )
                    ->displayIf('ChurchPension')->isChecked()->end(),
            )
                ->displayIf('OtherPensionSystem')->isEqualTo('yes')->end(),

            DropdownField::create('PensionReceived', _t(__CLASS__ . '.PensionReceived', '4.2 Haben Sie während der Erziehungszeiten eine Versorgung nach Erreichen einer Altersgrenze aus beamtenrechtlichen, beamtenähnlichen, kirchenrechtlichen oder berufsständischen deutschen Alterssicherungssystemen bezogen?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.PensionReceived.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.PensionReceived.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                DateField::create('PensionReceivedFrom', _t(__CLASS__ . '.PensionReceivedFrom', 'vom')),
                DateField::create('PensionReceivedTo', _t(__CLASS__ . '.PensionReceivedTo', 'bis')),
                TextField::create('PensionReceivedSource', _t(__CLASS__ . '.PensionReceivedSource', 'Von welcher Stelle')),
            )
                ->displayIf('PensionReceived')->isEqualTo('yes')->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'EastGermanyResidence',
            'OtherPensionSystem',
            'PensionReceived',
        ]);
    }

    /**
     * 4.1 "ja" without choosing at least one pension type would produce an incomplete PDF.
     */
    public function validateStep($data, $form)
    {
        $valid = true;

        if (
            is_array($data)
            && ($data['OtherPensionSystem'] ?? null) === 'yes'
            && empty($data['CivilServicePension'])
            && empty($data['ChurchPension'])
        ) {
            $form->sessionFieldError(
                _t(__CLASS__ . '.PensionTypeRequired', 'Bitte wählen Sie mindestens eine Art der Versorgung.'),
                'CivilServicePension'
            );
            $valid = false;
        }

        return $this->validateConditionalRequired($data, $form) && $valid;
    }
}
