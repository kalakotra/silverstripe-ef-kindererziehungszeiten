<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use SilverStripe\Forms\FieldList;
use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Ziffer 11 - Angaben zur Kindererziehung
 * Ziffer 12 - Übereinstimmende Erklärungen
 *
 * Flow per form text:
 *  - applicant is father / partner      -> 11.1-11.3 skipped (V0805 per child), 12 asked
 *  - 11.1 joint                         -> 11.2
 *  - 11.1 alone                         -> Ziffer 13 (12 skipped)
 *  - 11.1 interrupted / partly          -> 12 (V0805 per child)
 *  - 11.2 no                            -> 11.3, then 12
 *  - 11.2 partly / yes                  -> 12 (V0805 per child)
 */
class Form_Step9 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 9;

    private static $next_steps = Form_Step10::class;

    private static array $required_fields = [
        'ApplicantRole',
    ];

    private static array $conditional_required = [
        'RaisingMode',
        'OtherParentMainlyRaised',
        'OtherParentLastName',
        'OtherParentFirstName',
        'OtherParentBirthDate',
        'OtherParentConfirmation',
        'OtherParentConfirmationReason',
        'OtherParentConfirmationOther',
        'PriorDeclaration',
        'PriorDeclarationInstitution',
    ];

    public function getFields(): FieldList
    {
        $v0805 = 'Für jedes angegebene Kind ist zusätzlich ein Vordruck V0805 auszufüllen.';

        return FieldList::create(
            $this->header('Angaben zur Kindererziehung'),

            $this->dropdown('ApplicantRole', 'Wer stellt den Antrag?', [
                'mother' => 'Mutter',
                'other_parent' => 'Vater / Lebenspartner(in) einer Eingetragenen Lebenspartnerschaft / '
                    . 'gleichgeschlechtliche(r) Ehegatte(in)',
            ]),
            $this->group('OtherParentRoleGroup', $this->notice('OtherParentRoleNotice', $v0805))
                ->displayIf('ApplicantRole')->isEqualTo('other_parent')->end(),

            $this->group(
                'MotherGroup',
                $this->dropdown(
                    'RaisingMode',
                    '11.1 Wurden die Kinder in den angegebenen Erziehungszeiten gemeinsam oder allein, '
                    . 'mit oder ohne Unterbrechung erzogen?',
                    [
                        'joint' => 'ohne Unterbrechung gemeinsam mit dem anderen Elternteil',
                        'alone' => 'ohne Unterbrechung allein',
                        'interrupted' => 'mit Unterbrechung',
                        'partly' => 'teilweise allein und teilweise gemeinsam mit dem anderen Elternteil '
                            . '(zum Beispiel wegen Trennung, Tod des anderen Elternteils)',
                    ]
                )->setDescription($this->t(
                    'RaisingModeDescription',
                    'Eine Unterbrechung liegt zum Beispiel vor, wenn das Kind durch eine andere Person erzogen '
                    . 'wurde, sich außerhalb Deutschlands aufgehalten hat oder auf gerichtliche Anordnung in '
                    . 'einem Heim untergebracht war.'
                )),
                $this->group('RaisingModeNoticeGroup', $this->notice('RaisingModeNotice', $v0805))
                    ->displayIf('RaisingMode')->isEqualTo('interrupted')
                    ->orIf('RaisingMode')->isEqualTo('partly')->end(),

                $this->group(
                    'JointGroup',
                    $this->dropdown(
                        'OtherParentMainlyRaised',
                        '11.2 Hat der andere Elternteil die Kinder in den angegebenen Erziehungszeiten '
                        . 'überwiegend erzogen?',
                        ['no' => 'nein', 'partly' => 'teilweise', 'yes' => 'ja']
                    ),
                    $this->group('MainlyRaisedNoticeGroup', $this->notice('MainlyRaisedNotice', $v0805))
                        ->displayIf('OtherParentMainlyRaised')->isEqualTo('partly')
                        ->orIf('OtherParentMainlyRaised')->isEqualTo('yes')->end(),
                    $this->otherParentGroup()
                        ->displayIf('OtherParentMainlyRaised')->isEqualTo('no')->end()
                )->displayIf('RaisingMode')->isEqualTo('joint')->end()
            )->displayIf('ApplicantRole')->isEqualTo('mother')->end(),

            $this->group(
                'PriorDeclarationGroup',
                $this->yesNo(
                    'PriorDeclaration',
                    '12.1 Haben Sie oder der andere Elternteil für die Kinder eine übereinstimmende Erklärung über '
                    . 'die Zuordnung der Erziehungszeiten zu einem anderen Elternteil bei einem '
                    . 'Rentenversicherungsträger oder einem Versorgungsträger abgegeben?'
                ),
                $this->group(
                    'PriorDeclarationInstitutionGroup',
                    $this->text(
                        'PriorDeclarationInstitution',
                        'Name des Rentenversicherungsträgers / Versorgungsträgers'
                    )
                )->displayIf('PriorDeclaration')->isEqualTo('yes')->end()
            )->displayIf('ApplicantRole')->isEqualTo('other_parent')
                ->orIf('RaisingMode')->isEqualTo('joint')
                ->orIf('RaisingMode')->isEqualTo('interrupted')
                ->orIf('RaisingMode')->isEqualTo('partly')->end()
        );
    }

    /**
     * 11.3 - Angaben zum Elternteil, der den Antrag nicht stellt.
     */
    private function otherParentGroup(): \UncleCheese\DisplayLogic\Forms\Wrapper
    {
        return $this->group(
            'OtherParentGroup',
            $this->subHeader('OtherParentHeader', '11.3 Angaben zum Elternteil, der den Antrag nicht stellt'),
            $this->text('OtherParentLastName', 'Name'),
            $this->text('OtherParentFirstName', 'Vorname'),
            $this->text('OtherParentNameSuffix', 'Namenszusatz (Beispiel: Freifrau, Graf)'),
            $this->text('OtherParentNamePrefix', 'Vorsatzworte zum Namen (Beispiel: von, van, de)'),
            $this->text('OtherParentTitle', 'Titel (Beispiel: Prof. Dr. med.)'),
            $this->text('OtherParentBirthName', 'Geburtsname'),
            $this->text('OtherParentBirthNameSuffix', 'Namenszusatz zum Geburtsnamen'),
            $this->text('OtherParentBirthNamePrefix', 'Vorsatzworte zum Geburtsnamen'),
            $this->date('OtherParentBirthDate', 'Geburtsdatum'),
            $this->text('OtherParentInsuranceNumber', 'Versicherungsnummer', 12),

            $this->dropdown(
                'OtherParentConfirmation',
                'Bestätigung durch den anderen Elternteil',
                [
                    'later' => 'Die Bestätigung des anderen Elternteils wird auf einer Kopie dieses Antrags nachgereicht.',
                    'impossible' => 'Die Bestätigung des anderen Elternteils kann nicht erfolgen.',
                ]
            )->setDescription($this->t(
                'OtherParentConfirmationDescription',
                'Der andere Elternteil muss die Angaben zur Erziehung mit seiner Unterschrift bestätigen.'
            )),
            $this->group(
                'ConfirmationImpossibleGroup',
                $this->dropdown('OtherParentConfirmationReason', 'Grund', [
                    'deceased' => 'Der andere Elternteil ist verstorben.',
                    'unknown' => 'Der Aufenthaltsort des anderen Elternteils ist unbekannt.',
                    'other' => 'Sonstige Gründe',
                ]),
                $this->group(
                    'ConfirmationOtherGroup',
                    $this->text('OtherParentConfirmationOther', 'Sonstige Gründe')
                )->displayIf('OtherParentConfirmationReason')->isEqualTo('other')->end()
            )->displayIf('OtherParentConfirmation')->isEqualTo('impossible')->end()
        );
    }
}
