<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use SilverStripe\Forms\FieldList;
use SilverStripe\MultiForm\Models\MultiFormStep;

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

    private static array $required_fields = [
        'Asylum',
        'Expellee',
        'NationalityDuringRaising',
    ];

    private static array $conditional_required = [
        'ExpelleeRejected',
        'OtherNationality',
        'ResidenceTitleStatus',
        'ResidenceTitleDate',
    ];

    public function getFields(): FieldList
    {
        return FieldList::create(
            $this->header('Aufenthaltsrechtlicher Status und Staatsangehörigkeit'),

            $this->yesNo(
                'Asylum',
                '7.1 Sind Sie als Asylberechtigter, Kontingentflüchtling oder heimatloser Ausländer anerkannt?'
            ),
            $this->group(
                'AsylumGroup',
                $this->notice('AsylumNotice', 'Bitte entsprechende Bescheinigungen beifügen.')
            )->displayIf('Asylum')->isEqualTo('yes')->end(),

            $this->yesNo(
                'Expellee',
                '8.1 Sind Sie als Vertriebener oder Spätaussiedler beziehungsweise als Ehegatte oder '
                . 'Abkömmling eines Spätaussiedlers anerkannt?'
            ),
            $this->group(
                'ExpelleeGroup',
                $this->notice('ExpelleeNotice', 'Bitte entsprechende Bescheinigungen beifügen.')
            )->displayIf('Expellee')->isEqualTo('yes')->end(),
            $this->group(
                'ExpelleeRejectedGroup',
                $this->yesNo(
                    'ExpelleeRejected',
                    '8.1.1 Wurde die Vertriebeneneigenschaft / Spätaussiedlereigenschaft abgelehnt?'
                ),
                $this->group(
                    'ExpelleeRejectedNoticeGroup',
                    $this->notice('ExpelleeRejectedNotice', 'Bitte Ablehnungsbescheid / Urteil beifügen.')
                )->displayIf('ExpelleeRejected')->isEqualTo('yes')->end()
            )->displayIf('Expellee')->isEqualTo('no')->end(),

            $this->dropdown(
                'NationalityDuringRaising',
                '9.1 Welche Staatsangehörigkeit hatten Sie während der Erziehungszeiten in Deutschland?',
                [
                    'german' => 'deutsch',
                    'naturalized' => 'deutsch aufgrund erfolgter Einbürgerung',
                    'other' => 'andere',
                ]
            ),
            $this->group(
                'NaturalizedGroup',
                $this->notice('NaturalizedNotice', 'Bitte Einbürgerungsurkunde beifügen.')
            )->displayIf('NationalityDuringRaising')->isEqualTo('naturalized')->end(),
            $this->group(
                'OtherNationalityGroup',
                $this->text('OtherNationality', 'Andere Staatsangehörigkeit'),
                $this->subHeader(
                    'ResidenceTitleHeader',
                    '9.2 Welchen ausländerrechtlichen Status hatten Sie während der Erziehungszeiten in Deutschland?'
                ),
                $this->text('ResidenceTitle', 'Aufenthaltstitel'),
                $this->dropdown('ResidenceTitleStatus', 'Aufenthaltstitel', [
                    'granted' => 'wurde erteilt',
                    'not_applied' => 'wurde nicht beantragt',
                    'applied' => 'wurde beantragt, aber noch nicht erteilt',
                ]),
                $this->group(
                    'ResidenceTitleGrantedGroup',
                    $this->date('ResidenceTitleDate', 'Erteilt am'),
                    $this->notice('ResidenceTitleNotice', 'Entsprechende Bescheinigungen bitte beifügen.')
                )->displayIf('ResidenceTitleStatus')->isEqualTo('granted')->end()
            )->displayIf('NationalityDuringRaising')->isEqualTo('other')->end()
        );
    }
}
