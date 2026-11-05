<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use SilverStripe\Forms\FieldList;
use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Ziffer 2 - Kind 2 (only reached when Step2.HasSecondChild = yes).
 */
class Form_Step3 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 3;

    private static $next_steps = Form_Step4::class;

    private static array $required_fields = [
        'Child2Name',
        'Child2BirthPlace',
        'Child2BirthCountry',
        'Child2BirthDate',
        'Child2StepFoster',
        'Child2RaisedSinceBirth',
    ];

    private static array $conditional_required = [
        'Child2StepFosterType',
        'Child2RaisedFrom',
        'Child2RaisedTo',
    ];

    public function getFields(): FieldList
    {
        return FieldList::create([
            $this->header('Angaben zu den Kindern - Kind 2'),
            ...$this->childFields(2),
        ]);
    }
}
