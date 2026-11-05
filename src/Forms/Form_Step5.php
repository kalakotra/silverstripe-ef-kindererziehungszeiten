<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\NumericField;
use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Ziffer 5 - Angaben zu einer selbständigen Tätigkeit.
 */
class Form_Step5 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 5;

    private static $next_steps = Form_Step6::class;

    private static array $required_fields = [
        'SelfEmployed',
    ];

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
            $this->header('Selbständige Tätigkeit'),
            $this->yesNo(
                'SelfEmployed',
                '5.1 Waren Sie während der Erziehungszeiten als Selbständiger oder Landwirt beziehungsweise '
                . 'im Betrieb Ihres Ehegatten / Lebenspartners oder eines Angehörigen tätig?'
            ),
            $this->group(
                'SelfEmployedGroup',
                ...[
                    $this->dropdown('SelfEmployedType', 'Tätig als', [
                        'self_employed' => 'Selbständiger',
                        'self_employed_relative' => 'Ehegatte / Lebenspartner / Angehöriger eines Selbständigen',
                        'farmer' => 'Landwirt',
                        'farmer_relative' => 'Ehegatte / Lebenspartner / Angehöriger eines Landwirts',
                    ]),
                    $this->subHeader('SelfEmployedDetailsHeader', '5.2 Art und Umfang der Tätigkeit'),
                    ...$this->period('SelfEmployedFrom', 'SelfEmployedTo'),
                    $this->text('SelfEmployedActivity', 'Art der Tätigkeit'),
                    NumericField::create(
                        'SelfEmployedIncome',
                        $this->t('SelfEmployedIncome', 'Durchschnittliches monatliches Arbeitseinkommen (EUR)')
                    )->setScale(2),
                    NumericField::create(
                        'SelfEmployedHours',
                        $this->t('SelfEmployedHours', 'Wöchentliche Arbeitszeit in Stunden')
                    )->setScale(0),
                    $this->group(
                        'FarmGroup',
                        $this->yesNo(
                            'FarmRegistered',
                            '5.3 Ist oder war der Betrieb während der Erziehungszeiten im Unternehmerverzeichnis '
                            . 'der Landwirtschaftlichen Alterskasse eingetragen?'
                        ),
                        $this->group('FarmRegisteredGroup', ...$this->period('FarmRegisteredFrom', 'FarmRegisteredTo'))
                            ->displayIf('FarmRegistered')->isEqualTo('yes')->end()
                    )->displayIf('SelfEmployedType')->isEqualTo('farmer')
                        ->orIf('SelfEmployedType')->isEqualTo('farmer_relative')->end(),
                ]
            )->displayIf('SelfEmployed')->isEqualTo('yes')->end()
        );
    }
}
