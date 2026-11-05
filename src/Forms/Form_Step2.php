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
 * Ziffer 2 - Kind 1. Branches to Kind 2 (Step3) or directly to Ziffer 3 (Step4).
 * No $next_steps on purpose: the flow is decided in getNextStep().
 */
class Form_Step2 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 2;

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
        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Angaben zu den Kindern - Kind 1'), 4),
            LiteralField::create('ChildrenIntro', '<div class="alert alert-info">' . _t(__CLASS__ . '.ChildrenIntro', 'Für folgende Kinder wird die Anrechnung von Kindererziehungszeiten / Berücksichtigungszeiten beantragt. Bitte Geburtsurkunden, Familienbuch oder Stammbuch beifügen.') . '</div>'),
            TextField::create('Child1Name', _t(__CLASS__ . '.Child1Name', 'Name, Vorname des Kindes'))
                ->setDescription(_t(__CLASS__ . '.Child1NameDescription', 'Zur Zeit der Geburt beziehungsweise bei einem Adoptivkind nach der Adoption')),
            TextField::create('Child1NamePrefix', _t(__CLASS__ . '.Child1NamePrefix', 'Vorsatzworte zum Namen (Beispiel: von, van, de)')),
            TextField::create('Child1BirthPlace', _t(__CLASS__ . '.Child1BirthPlace', 'Geburtsort')),
            TextField::create('Child1BirthCountry', _t(__CLASS__ . '.Child1BirthCountry', 'Geburtsstaat')),
            DateField::create('Child1BirthDate', _t(__CLASS__ . '.Child1BirthDate', 'Geburtsdatum')),
            DateField::create('Child1DeathDate', _t(__CLASS__ . '.Child1DeathDate', 'Gegebenenfalls Sterbedatum')),

            DropdownField::create('Child1StepFoster', _t(__CLASS__ . '.Child1StepFoster', 'Ist das Kind ein Stiefkind oder Pflegekind?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.Child1StepFoster.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.Child1StepFoster.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                DropdownField::create('Child1StepFosterType', _t(__CLASS__ . '.Child1StepFosterType', 'Art'))
                    ->setSource([
                        'step' => _t(__CLASS__ . '.Child1StepFosterType.step', 'Stiefkind'),
                        'foster' => _t(__CLASS__ . '.Child1StepFosterType.foster', 'Pflegekind'),
                    ])
                    ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
                LiteralField::create('Child1StepFosterNotice', '<div class="alert alert-info">' . _t(__CLASS__ . '.Child1StepFosterNotice', 'Für jedes Stiefkind oder Pflegekind ist zusätzlich ein Vordruck V0805 auszufüllen.') . '</div>'),
            )
                ->displayIf('Child1StepFoster')->isEqualTo('yes')->end(),

            DropdownField::create('Child1RaisedSinceBirth', _t(__CLASS__ . '.Child1RaisedSinceBirth', 'Haben Sie das Kind von der Geburt an ununterbrochen erzogen (Angabe längstens bis zur Vollendung des 10. Lebensjahres)?'))
                ->setSource([
                    'yes' => _t(__CLASS__ . '.Child1RaisedSinceBirth.yes', 'ja'),
                    'no' => _t(__CLASS__ . '.Child1RaisedSinceBirth.no', 'nein'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                DateField::create('Child1RaisedFrom', _t(__CLASS__ . '.Child1RaisedFrom', 'Beginn')),
                DateField::create('Child1RaisedTo', _t(__CLASS__ . '.Child1RaisedTo', 'Ende')),
            )
                ->displayIf('Child1RaisedSinceBirth')->isEqualTo('no')->end(),

            DropdownField::create('HasSecondChild', _t(__CLASS__ . '.HasSecondChild', 'Möchten Sie ein weiteres Kind angeben?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.HasSecondChild.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.HasSecondChild.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --')
                ->setDescription(_t(__CLASS__ . '.HasSecondChildDescription', 'Für mehr als zwei Kinder ist ein weiterer Antrag erforderlich.')),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'Child1Name',
            'Child1BirthPlace',
            'Child1BirthCountry',
            'Child1BirthDate',
            'Child1StepFoster',
            'Child1RaisedSinceBirth',
            'HasSecondChild',
        ]);
    }
}
