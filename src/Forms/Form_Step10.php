<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use SilverStripe\Forms\FieldList;
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

    private static array $required_fields = [
        'Place',
    ];

    public function getFields(): FieldList
    {
        return FieldList::create(
            $this->header('Abschluss'),
            $this->dropdown(
                'AccessibleFormat',
                'Dokumente zusätzlich in einer wahrnehmbaren Form erhalten (nur bei Sehbehinderung)',
                [
                    'large_print' => 'als Großdruck',
                    'braille_short' => 'in Braille (Kurzschrift)',
                    'braille_full' => 'in Braille (Vollschrift)',
                    'cd' => 'als CD (Schriftdatei / Textdatei im ".doc"-Format)',
                    'daisy' => 'als Hörmedium (CD-DAISY Format)',
                ]
            )->setEmptyString($this->t('AccessibleFormatNone', 'nicht erforderlich')),
            $this->text('Place', 'Ort')
                ->setDescription($this->t(
                    'PlaceDescription',
                    'Ort der Unterschrift. Das Datum wird beim Absenden automatisch eingetragen.'
                ))
        );
    }
}
