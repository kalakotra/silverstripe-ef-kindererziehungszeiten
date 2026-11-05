<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use SilverStripe\Forms\FieldList;
use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Ziffer 2 - Kind 1. Branches to Kind 2 (Step3) or directly to Ziffer 3 (Step4).
 * No $next_steps on purpose: the flow is decided in getNextStep().
 */
class Form_Step2 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 2;

    private static array $required_fields = [
        'Child1Name',
        'Child1BirthPlace',
        'Child1BirthCountry',
        'Child1BirthDate',
        'Child1StepFoster',
        'Child1RaisedSinceBirth',
        'HasSecondChild',
    ];

    private static array $conditional_required = [
        'Child1StepFosterType',
        'Child1RaisedFrom',
        'Child1RaisedTo',
    ];

    public function getNextStep()
    {
        // own step data: read directly, the cross-step cache could be stale right after saveData()
        return (($this->loadData()['HasSecondChild'] ?? null) === 'yes')
            ? Form_Step3::class
            : Form_Step4::class;
    }

    public function getFields(): FieldList
    {
        return FieldList::create([
            $this->header('Angaben zu den Kindern - Kind 1'),
            $this->notice(
                'ChildrenIntro',
                'Für folgende Kinder wird die Anrechnung von Kindererziehungszeiten / Berücksichtigungszeiten '
                . 'beantragt. Bitte Geburtsurkunden, Familienbuch oder Stammbuch beifügen.'
            ),
            ...$this->childFields(1),
            $this->yesNo('HasSecondChild', 'Möchten Sie ein weiteres Kind angeben?')
                ->setDescription($this->t(
                    'HasSecondChildDescription',
                    'Für mehr als zwei Kinder ist ein weiterer Antrag erforderlich.'
                )),
        ]);
    }
}
