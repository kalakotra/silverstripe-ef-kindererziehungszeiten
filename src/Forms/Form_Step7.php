<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\MultiForm\Models\MultiFormStep;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * Ziffer 7 - Asylberechtigter / Kontingentflüchtling / heimatloser Ausländer
 * Ziffer 8 - Vertriebener / Spätaussiedler
 * Ziffer 9 - Staatsangehörigkeit bei Erziehung in Deutschland
 */
class Form_Step7 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 7;

    private static $next_steps = Form_Step8::class;

    private static array $conditional_required = [
        'ExpelleeRejected',
        'OtherNationality',
        'ResidenceTitleStatus',
        'ResidenceTitleDate',
    ];

    public function getFields(): FieldList
    {
        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Aufenthaltsrechtlicher Status und Staatsangehörigkeit'), 4),

            DropdownField::create('Asylum', _t(__CLASS__ . '.Asylum', '7.1 Sind Sie als Asylberechtigter, Kontingentflüchtling oder heimatloser Ausländer anerkannt?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.Asylum.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.Asylum.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                LiteralField::create('AsylumNotice', '<div class="alert alert-info">' . _t(__CLASS__ . '.AsylumNotice', 'Bitte entsprechende Bescheinigungen beifügen.') . '</div>'),
            )
                ->displayIf('Asylum')->isEqualTo('yes')->end(),

            DropdownField::create('Expellee', _t(__CLASS__ . '.Expellee', '8.1 Sind Sie als Vertriebener oder Spätaussiedler beziehungsweise als Ehegatte oder Abkömmling eines Spätaussiedlers anerkannt?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.Expellee.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.Expellee.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                LiteralField::create('ExpelleeNotice', '<div class="alert alert-info">' . _t(__CLASS__ . '.ExpelleeNotice', 'Bitte entsprechende Bescheinigungen beifügen.') . '</div>'),
            )
                ->displayIf('Expellee')->isEqualTo('yes')->end(),
            Wrapper::create(
                DropdownField::create('ExpelleeRejected', _t(__CLASS__ . '.ExpelleeRejected', '8.1.1 Wurde die Vertriebeneneigenschaft / Spätaussiedlereigenschaft abgelehnt?'))
                    ->setSource([
                        'no' => _t(__CLASS__ . '.ExpelleeRejected.no', 'nein'),
                        'yes' => _t(__CLASS__ . '.ExpelleeRejected.yes', 'ja'),
                    ])
                    ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
                Wrapper::create(
                    LiteralField::create('ExpelleeRejectedNotice', '<div class="alert alert-info">' . _t(__CLASS__ . '.ExpelleeRejectedNotice', 'Bitte Ablehnungsbescheid / Urteil beifügen.') . '</div>'),
                )
                    ->displayIf('ExpelleeRejected')->isEqualTo('yes')->end(),
            )
                ->displayIf('Expellee')->isEqualTo('no')->end(),

            DropdownField::create('NationalityDuringRaising', _t(__CLASS__ . '.NationalityDuringRaising', '9.1 Welche Staatsangehörigkeit hatten Sie während der Erziehungszeiten in Deutschland?'))
                ->setSource([
                    'german' => _t(__CLASS__ . '.NationalityDuringRaising.german', 'deutsch'),
                    'naturalized' => _t(__CLASS__ . '.NationalityDuringRaising.naturalized', 'deutsch aufgrund erfolgter Einbürgerung'),
                    'other' => _t(__CLASS__ . '.NationalityDuringRaising.other', 'andere'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                LiteralField::create('NaturalizedNotice', '<div class="alert alert-info">' . _t(__CLASS__ . '.NaturalizedNotice', 'Bitte Einbürgerungsurkunde beifügen.') . '</div>'),
            )
                ->displayIf('NationalityDuringRaising')->isEqualTo('naturalized')->end(),
            Wrapper::create(
                TextField::create('OtherNationality', _t(__CLASS__ . '.OtherNationality', 'Andere Staatsangehörigkeit')),
                HeaderField::create('ResidenceTitleHeader', _t(__CLASS__ . '.ResidenceTitleHeader', '9.2 Welchen ausländerrechtlichen Status hatten Sie während der Erziehungszeiten in Deutschland?'), 5),
                TextField::create('ResidenceTitle', _t(__CLASS__ . '.ResidenceTitle', 'Aufenthaltstitel')),
                DropdownField::create('ResidenceTitleStatus', _t(__CLASS__ . '.ResidenceTitleStatus', 'Aufenthaltstitel'))
                    ->setSource([
                        'granted' => _t(__CLASS__ . '.ResidenceTitleStatus.granted', 'wurde erteilt'),
                        'not_applied' => _t(__CLASS__ . '.ResidenceTitleStatus.not_applied', 'wurde nicht beantragt'),
                        'applied' => _t(__CLASS__ . '.ResidenceTitleStatus.applied', 'wurde beantragt, aber noch nicht erteilt'),
                    ])
                    ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
                Wrapper::create(
                    DateField::create('ResidenceTitleDate', _t(__CLASS__ . '.ResidenceTitleDate', 'Erteilt am')),
                    LiteralField::create('ResidenceTitleNotice', '<div class="alert alert-info">' . _t(__CLASS__ . '.ResidenceTitleNotice', 'Entsprechende Bescheinigungen bitte beifügen.') . '</div>'),
                )
                    ->displayIf('ResidenceTitleStatus')->isEqualTo('granted')->end(),
            )
                ->displayIf('NationalityDuringRaising')->isEqualTo('other')->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'Asylum',
            'Expellee',
            'NationalityDuringRaising',
        ]);
    }
}
