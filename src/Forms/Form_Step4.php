<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Ziffer 3 - Wohnsitz während der Erziehungszeiten
 * Ziffer 4 - Deutsche Versorgungsanwartschaften außerhalb der Rentenversicherung
 */
class Form_Step4 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 4;

    private static $next_steps = Form_Step5::class;

    private static array $required_fields = [
        'EastGermanyResidence',
        'OtherPensionSystem',
        'PensionReceived',
    ];

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
            $this->header('Wohnsitz und Versorgungsanwartschaften'),

            $this->yesNo(
                'EastGermanyResidence',
                '3.1 Hatten Sie während der Erziehungszeiten Ihren Wohnsitz in der DDR '
                . 'oder den neuen Bundesländern?'
            ),
            $this->group('EastGermanyResidenceGroup', ...$this->period('EastGermanyResidenceFrom', 'EastGermanyResidenceTo'))
                ->displayIf('EastGermanyResidence')->isEqualTo('yes')->end(),

            $this->yesNo(
                'OtherPensionSystem',
                '4.1 Haben Sie während der Erziehungszeiten einem anderen deutschen Alterssicherungssystem angehört?'
            ),
            $this->group(
                'OtherPensionSystemGroup',
                CheckboxField::create('CivilServicePension', $this->t(
                    'CivilServicePension',
                    'Beamtenrechtliche oder ähnliche Versorgung (zum Beispiel als Beamter, Richter oder DO-Angestellter)'
                )),
                $this->group(
                    'CivilServiceGroup',
                    ...[
                        ...$this->period('CivilServiceFrom', 'CivilServiceTo'),
                        $this->text('CivilServiceActivity', 'Art der Tätigkeit'),
                        $this->text('CivilServiceProvider', 'Versorgungsträger'),
                        $this->text('CivilServiceProviderAddress', 'Anschrift des Versorgungsträgers'),
                    ]
                )->displayIf('CivilServicePension')->isChecked()->end(),
                CheckboxField::create('ChurchPension', $this->t(
                    'ChurchPension',
                    'Kirchenrechtliche Versorgung (zum Beispiel als Geistlicher)'
                )),
                $this->group(
                    'ChurchServiceGroup',
                    ...[
                        ...$this->period('ChurchServiceFrom', 'ChurchServiceTo'),
                        $this->text('ChurchServiceActivity', 'Art der Tätigkeit'),
                        $this->text('ChurchServiceProvider', 'Versorgungsträger'),
                        $this->text('ChurchServiceProviderAddress', 'Anschrift des Versorgungsträgers'),
                    ]
                )->displayIf('ChurchPension')->isChecked()->end()
            )->displayIf('OtherPensionSystem')->isEqualTo('yes')->end(),

            $this->yesNo(
                'PensionReceived',
                '4.2 Haben Sie während der Erziehungszeiten eine Versorgung nach Erreichen einer Altersgrenze '
                . 'aus beamtenrechtlichen, beamtenähnlichen, kirchenrechtlichen oder berufsständischen '
                . 'deutschen Alterssicherungssystemen bezogen?'
            ),
            $this->group(
                'PensionReceivedGroup',
                ...[
                    ...$this->period('PensionReceivedFrom', 'PensionReceivedTo'),
                    $this->text('PensionReceivedSource', 'Von welcher Stelle'),
                ]
            )->displayIf('PensionReceived')->isEqualTo('yes')->end()
        );
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
                $this->t('PensionTypeRequired', 'Bitte wählen Sie mindestens eine Art der Versorgung.'),
                'CivilServicePension'
            );
            $valid = false;
        }

        return $this->validateConditionalRequired($data, $form) && $valid;
    }
}
