<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\MultiForm\Models\MultiFormStep;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * Ziffer 6 - Angaben bei Erziehung außerhalb Deutschlands.
 * Flow per form text: 6.1 "nein" -> Ziffer 7; 6.2 "ja" -> 6.3 (6.2.1 only when 6.2 = "nein").
 */
class Form_Step6 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 6;

    private static $next_steps = Form_Step7::class;

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
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Erziehung außerhalb Deutschlands'), 4),

            DropdownField::create('RaisedAbroad', _t(__CLASS__ . '.RaisedAbroad', '6.1 Haben Sie innerhalb der Erziehungszeiten ein Kind außerhalb Deutschlands erzogen?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.RaisedAbroad.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.RaisedAbroad.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                DateField::create('RaisedAbroadFrom', _t(__CLASS__ . '.RaisedAbroadFrom', 'Erziehungszeit vom')),
                DateField::create('RaisedAbroadTo', _t(__CLASS__ . '.RaisedAbroadTo', 'Erziehungszeit bis')),
                TextField::create('RaisedAbroadCountry', _t(__CLASS__ . '.RaisedAbroadCountry', 'Staat / Gebiet')),

                DropdownField::create('AbroadContributions', _t(__CLASS__ . '.AbroadContributions', '6.2 Haben Sie oder Ihr Ehegatte / Lebenspartner unmittelbar vor oder während der Erziehungszeiten für eine Beschäftigung / Tätigkeit außerhalb Deutschlands Pflichtbeiträge zur deutschen gesetzlichen Rentenversicherung gezahlt?'))
                    ->setSource([
                        'no' => _t(__CLASS__ . '.AbroadContributions.no', 'nein'),
                        'yes' => _t(__CLASS__ . '.AbroadContributions.yes', 'ja'),
                    ])
                    ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
                Wrapper::create(
                    DateField::create('AbroadContributionsFrom', _t(__CLASS__ . '.AbroadContributionsFrom', 'vom')),
                    DateField::create('AbroadContributionsTo', _t(__CLASS__ . '.AbroadContributionsTo', 'bis')),
                )
                    ->displayIf('AbroadContributions')->isEqualTo('yes')->end(),

                Wrapper::create(
                    DropdownField::create('AbroadEmployerRelation', _t(__CLASS__ . '.AbroadEmployerRelation', '6.2.1 Bestanden für Sie oder Ihren Ehegatten / Lebenspartner unmittelbar vor oder während der Erziehungszeiten außerhalb Deutschlands außer zu einem Arbeitgeber außerhalb Deutschlands auch vertragliche oder dienstrechtliche Beziehungen zu einem Arbeitgeber / Dienstherrn in Deutschland?'))
                        ->setSource([
                            'no' => _t(__CLASS__ . '.AbroadEmployerRelation.no', 'nein'),
                            'yes' => _t(__CLASS__ . '.AbroadEmployerRelation.yes', 'ja'),
                        ])
                        ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
                    Wrapper::create(
                        DateField::create('AbroadEmployerRelationFrom', _t(__CLASS__ . '.AbroadEmployerRelationFrom', 'vom')),
                        DateField::create('AbroadEmployerRelationTo', _t(__CLASS__ . '.AbroadEmployerRelationTo', 'bis')),
                        TextareaField::create('AbroadEmployerRelationNote', _t(__CLASS__ . '.AbroadEmployerRelationNote', 'Bitte kurz erläutern'))
                            ->setRows(2)
                            ->setMaxLength(140),
                    )
                        ->displayIf('AbroadEmployerRelation')->isEqualTo('yes')->end(),
                )
                    ->displayIf('AbroadContributions')->isEqualTo('no')->end(),

                HeaderField::create('EUActivityHeader', _t(__CLASS__ . '.EUActivityHeader', '6.3 Nur bei Erziehung in der EU / dem EWR, in der Schweiz oder im Vereinigten Königreich'), 5),
                DropdownField::create('EUActivity', _t(__CLASS__ . '.EUActivity', 'Haben Sie vor, während oder nach den Erziehungszeiten eine Beschäftigung / Tätigkeit in Deutschland oder in der EU / dem EWR beziehungsweise in der Schweiz oder im Vereinigten Königreich ausgeübt oder dort Versicherungszeiten oder Wohnzeiten erworben?'))
                    ->setSource([
                        'no' => _t(__CLASS__ . '.EUActivity.no', 'nein'),
                        'yes' => _t(__CLASS__ . '.EUActivity.yes', 'ja'),
                    ])
                    ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
                Wrapper::create(
                    DateField::create('EUActivityFrom', _t(__CLASS__ . '.EUActivityFrom', 'vom')),
                    DateField::create('EUActivityTo', _t(__CLASS__ . '.EUActivityTo', 'bis')),
                    TextField::create('EUActivityCountry', _t(__CLASS__ . '.EUActivityCountry', 'Staat')),
                    TextField::create('EUActivityInsurer', _t(__CLASS__ . '.EUActivityInsurer', 'Versicherungsträger')),
                    TextField::create('EUActivityReference', _t(__CLASS__ . '.EUActivityReference', 'Versicherungsnummer / Aktenzeichen')),
                )
                    ->displayIf('EUActivity')->isEqualTo('yes')->end(),
            )
                ->displayIf('RaisedAbroad')->isEqualTo('yes')->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'RaisedAbroad',
        ]);
    }
}
