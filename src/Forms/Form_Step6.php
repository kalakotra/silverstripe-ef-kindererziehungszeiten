<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Ziffer 6 - Angaben bei Erziehung außerhalb Deutschlands.
 * Flow per form text: 6.1 "nein" -> Ziffer 7; 6.2 "ja" -> 6.3 (6.2.1 only when 6.2 = "nein").
 */
class Form_Step6 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 6;

    private static $next_steps = Form_Step7::class;

    private static array $required_fields = [
        'RaisedAbroad',
    ];

    private static array $conditional_required = [
        'RaisedAbroadFrom',
        'RaisedAbroadTo',
        'RaisedAbroadCountry',
        'AbroadContributions',
        'AbroadContributionsFrom',
        'AbroadContributionsTo',
        'AbroadEmployerRelation',
        'AbroadEmployerRelationFrom',
        'AbroadEmployerRelationTo',
        'AbroadEmployerRelationNote',
        'EUActivityFrom',
        'EUActivityTo',
        'EUActivityCountry',
        'EUActivityInsurer',
    ];

    public function getFields(): FieldList
    {
        return FieldList::create(
            $this->header('Erziehung außerhalb Deutschlands'),
            $this->yesNo(
                'RaisedAbroad',
                '6.1 Haben Sie innerhalb der Erziehungszeiten ein Kind außerhalb Deutschlands erzogen?'
            ),
            $this->group(
                'RaisedAbroadGroup',
                ...[
                    $this->date('RaisedAbroadFrom', 'Erziehungszeit vom'),
                    $this->date('RaisedAbroadTo', 'Erziehungszeit bis'),
                    $this->text('RaisedAbroadCountry', 'Staat / Gebiet'),

                    $this->yesNo(
                        'AbroadContributions',
                        '6.2 Haben Sie oder Ihr Ehegatte / Lebenspartner unmittelbar vor oder während der '
                        . 'Erziehungszeiten für eine Beschäftigung / Tätigkeit außerhalb Deutschlands '
                        . 'Pflichtbeiträge zur deutschen gesetzlichen Rentenversicherung gezahlt?'
                    ),
                    $this->group(
                        'AbroadContributionsGroup',
                        ...$this->period('AbroadContributionsFrom', 'AbroadContributionsTo')
                    )->displayIf('AbroadContributions')->isEqualTo('yes')->end(),

                    $this->group(
                        'AbroadEmployerGroup',
                        $this->yesNo(
                            'AbroadEmployerRelation',
                            '6.2.1 Bestanden für Sie oder Ihren Ehegatten / Lebenspartner unmittelbar vor oder während '
                            . 'der Erziehungszeiten außerhalb Deutschlands außer zu einem Arbeitgeber außerhalb '
                            . 'Deutschlands auch vertragliche oder dienstrechtliche Beziehungen zu einem '
                            . 'Arbeitgeber / Dienstherrn in Deutschland?'
                        ),
                        $this->group(
                            'AbroadEmployerRelationGroup',
                            ...[
                                ...$this->period('AbroadEmployerRelationFrom', 'AbroadEmployerRelationTo'),
                                TextareaField::create(
                                    'AbroadEmployerRelationNote',
                                    $this->t('AbroadEmployerRelationNote', 'Bitte kurz erläutern')
                                )->setRows(2)->setMaxLength(140),
                            ]
                        )->displayIf('AbroadEmployerRelation')->isEqualTo('yes')->end()
                    )->displayIf('AbroadContributions')->isEqualTo('no')->end(),

                    $this->subHeader(
                        'EUActivityHeader',
                        '6.3 Nur bei Erziehung in der EU / dem EWR, in der Schweiz oder im Vereinigten Königreich'
                    ),
                    $this->yesNo(
                        'EUActivity',
                        'Haben Sie vor, während oder nach den Erziehungszeiten eine Beschäftigung / Tätigkeit in '
                        . 'Deutschland oder in der EU / dem EWR beziehungsweise in der Schweiz oder im Vereinigten '
                        . 'Königreich ausgeübt oder dort Versicherungszeiten oder Wohnzeiten erworben?'
                    ),
                    $this->group(
                        'EUActivityGroup',
                        ...[
                            ...$this->period('EUActivityFrom', 'EUActivityTo'),
                            $this->text('EUActivityCountry', 'Staat'),
                            $this->text('EUActivityInsurer', 'Versicherungsträger'),
                            $this->text('EUActivityReference', 'Versicherungsnummer / Aktenzeichen'),
                        ]
                    )->displayIf('EUActivity')->isEqualTo('yes')->end(),
                ]
            )->displayIf('RaisedAbroad')->isEqualTo('yes')->end()
        );
    }
}
