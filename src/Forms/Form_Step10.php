<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Ziffer 13 - Dokumentenzugang für sehbehinderte Menschen (optional)
 * Ziffer 14 - Erklärung (Ort; date + signature are added on submission)
 */
class Form_Step10 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 10;

    private static $next_steps = Form_Preview::class;

    public function getFields(): FieldList
    {
        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Abschluss'), 4),
            DropdownField::create('AccessibleFormat', _t(__CLASS__ . '.AccessibleFormat', 'Dokumente zusätzlich in einer wahrnehmbaren Form erhalten (nur bei Sehbehinderung)'))
                ->setSource([
                    'large_print' => _t(__CLASS__ . '.AccessibleFormat.large_print', 'als Großdruck'),
                    'braille_short' => _t(__CLASS__ . '.AccessibleFormat.braille_short', 'in Braille (Kurzschrift)'),
                    'braille_full' => _t(__CLASS__ . '.AccessibleFormat.braille_full', 'in Braille (Vollschrift)'),
                    'cd' => _t(__CLASS__ . '.AccessibleFormat.cd', 'als CD (Schriftdatei / Textdatei im ".doc"-Format)'),
                    'daisy' => _t(__CLASS__ . '.AccessibleFormat.daisy', 'als Hörmedium (CD-DAISY Format)'),
                ])
                ->setEmptyString(_t(__CLASS__ . '.AccessibleFormatNone', 'nicht erforderlich')),
            TextField::create('Place', _t(__CLASS__ . '.Place', 'Ort'))
                ->setDescription(_t(__CLASS__ . '.PlaceDescription', 'Ort der Unterschrift. Das Datum wird beim Absenden automatisch eingetragen.')),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'Place',
        ]);
    }
}
