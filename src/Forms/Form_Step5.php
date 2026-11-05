<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\MultiForm\Models\MultiFormStep;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * Ziffer 5 - Angaben zu einer selbständigen Tätigkeit.
 */
class Form_Step5 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 5;

    private static $next_steps = Form_Step6::class;

    private static array $conditional_required = [
        'SelfEmployedType',
        'SelfEmployedFrom',
        'SelfEmployedTo',
        'SelfEmployedActivity',
        'FarmRegistered',
        'FarmRegisteredFrom',
        'FarmRegisteredTo',
    ];

    public function getFields(): FieldList
    {
        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Selbständige Tätigkeit'), 4),

            DropdownField::create('SelfEmployed', _t(__CLASS__ . '.SelfEmployed', '5.1 Waren Sie während der Erziehungszeiten als Selbständiger oder Landwirt beziehungsweise im Betrieb Ihres Ehegatten / Lebenspartners oder eines Angehörigen tätig?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.SelfEmployed.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.SelfEmployed.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                DropdownField::create('SelfEmployedType', _t(__CLASS__ . '.SelfEmployedType', 'Tätig als'))
                    ->setSource([
                        'self_employed' => _t(__CLASS__ . '.SelfEmployedType.self_employed', 'Selbständiger'),
                        'self_employed_relative' => _t(__CLASS__ . '.SelfEmployedType.self_employed_relative', 'Ehegatte / Lebenspartner / Angehöriger eines Selbständigen'),
                        'farmer' => _t(__CLASS__ . '.SelfEmployedType.farmer', 'Landwirt'),
                        'farmer_relative' => _t(__CLASS__ . '.SelfEmployedType.farmer_relative', 'Ehegatte / Lebenspartner / Angehöriger eines Landwirts'),
                    ])
                    ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),

                HeaderField::create('SelfEmployedDetailsHeader', _t(__CLASS__ . '.SelfEmployedDetailsHeader', '5.2 Art und Umfang der Tätigkeit'), 5),
                DateField::create('SelfEmployedFrom', _t(__CLASS__ . '.SelfEmployedFrom', 'vom')),
                DateField::create('SelfEmployedTo', _t(__CLASS__ . '.SelfEmployedTo', 'bis')),
                TextField::create('SelfEmployedActivity', _t(__CLASS__ . '.SelfEmployedActivity', 'Art der Tätigkeit')),
                NumericField::create('SelfEmployedIncome', _t(__CLASS__ . '.SelfEmployedIncome', 'Durchschnittliches monatliches Arbeitseinkommen (EUR)'))
                    ->setScale(2),
                NumericField::create('SelfEmployedHours', _t(__CLASS__ . '.SelfEmployedHours', 'Wöchentliche Arbeitszeit in Stunden'))
                    ->setScale(0),

                Wrapper::create(
                    DropdownField::create('FarmRegistered', _t(__CLASS__ . '.FarmRegistered', '5.3 Ist oder war der Betrieb während der Erziehungszeiten im Unternehmerverzeichnis der Landwirtschaftlichen Alterskasse eingetragen?'))
                        ->setSource([
                            'no' => _t(__CLASS__ . '.FarmRegistered.no', 'nein'),
                            'yes' => _t(__CLASS__ . '.FarmRegistered.yes', 'ja'),
                        ])
                        ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
                    Wrapper::create(
                        DateField::create('FarmRegisteredFrom', _t(__CLASS__ . '.FarmRegisteredFrom', 'vom')),
                        DateField::create('FarmRegisteredTo', _t(__CLASS__ . '.FarmRegisteredTo', 'bis')),
                    )
                        ->displayIf('FarmRegistered')->isEqualTo('yes')->end(),
                )
                    ->displayIf('SelfEmployedType')->isEqualTo('farmer')
                    ->orIf('SelfEmployedType')->isEqualTo('farmer_relative')->end(),
            )
                ->displayIf('SelfEmployed')->isEqualTo('yes')->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'SelfEmployed',
        ]);
    }
}
