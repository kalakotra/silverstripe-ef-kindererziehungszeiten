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
 * Ziffer 2 - Kind 2 (only reached when Step2.HasSecondChild = yes).
 */
class Form_Step3 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 3;

    private static $next_steps = Form_Step4::class;

    private static array $conditional_required = [
        'Child2StepFosterType',
        'Child2RaisedFrom',
        'Child2RaisedTo',
    ];

    public function getFields(): FieldList
    {
        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Angaben zu den Kindern - Kind 2'), 4),
            TextField::create('Child2Name', _t(__CLASS__ . '.Child2Name', 'Name, Vorname des Kindes'))
                ->setDescription(_t(__CLASS__ . '.Child2NameDescription', 'Zur Zeit der Geburt beziehungsweise bei einem Adoptivkind nach der Adoption')),
            TextField::create('Child2NamePrefix', _t(__CLASS__ . '.Child2NamePrefix', 'Vorsatzworte zum Namen (Beispiel: von, van, de)')),
            TextField::create('Child2BirthPlace', _t(__CLASS__ . '.Child2BirthPlace', 'Geburtsort')),
            TextField::create('Child2BirthCountry', _t(__CLASS__ . '.Child2BirthCountry', 'Geburtsstaat')),
            DateField::create('Child2BirthDate', _t(__CLASS__ . '.Child2BirthDate', 'Geburtsdatum')),
            DateField::create('Child2DeathDate', _t(__CLASS__ . '.Child2DeathDate', 'Gegebenenfalls Sterbedatum')),

            DropdownField::create('Child2StepFoster', _t(__CLASS__ . '.Child2StepFoster', 'Ist das Kind ein Stiefkind oder Pflegekind?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.Child2StepFoster.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.Child2StepFoster.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                DropdownField::create('Child2StepFosterType', _t(__CLASS__ . '.Child2StepFosterType', 'Art'))
                    ->setSource([
                        'step' => _t(__CLASS__ . '.Child2StepFosterType.step', 'Stiefkind'),
                        'foster' => _t(__CLASS__ . '.Child2StepFosterType.foster', 'Pflegekind'),
                    ])
                    ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
                LiteralField::create('Child2StepFosterNotice', '<div class="alert alert-info">' . _t(__CLASS__ . '.Child2StepFosterNotice', 'Für jedes Stiefkind oder Pflegekind ist zusätzlich ein Vordruck V0805 auszufüllen.') . '</div>'),
            )
                ->displayIf('Child2StepFoster')->isEqualTo('yes')->end(),

            DropdownField::create('Child2RaisedSinceBirth', _t(__CLASS__ . '.Child2RaisedSinceBirth', 'Haben Sie das Kind von der Geburt an ununterbrochen erzogen (Angabe längstens bis zur Vollendung des 10. Lebensjahres)?'))
                ->setSource([
                    'yes' => _t(__CLASS__ . '.Child2RaisedSinceBirth.yes', 'ja'),
                    'no' => _t(__CLASS__ . '.Child2RaisedSinceBirth.no', 'nein'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                DateField::create('Child2RaisedFrom', _t(__CLASS__ . '.Child2RaisedFrom', 'Beginn')),
                DateField::create('Child2RaisedTo', _t(__CLASS__ . '.Child2RaisedTo', 'Ende')),
            )
                ->displayIf('Child2RaisedSinceBirth')->isEqualTo('no')->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'Child2Name',
            'Child2BirthPlace',
            'Child2BirthCountry',
            'Child2BirthDate',
            'Child2StepFoster',
            'Child2RaisedSinceBirth',
        ]);
    }
}
