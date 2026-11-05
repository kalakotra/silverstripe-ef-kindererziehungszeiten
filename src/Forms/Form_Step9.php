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
        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Angaben zur Kindererziehung'), 4),

            DropdownField::create('ApplicantRole', _t(__CLASS__ . '.ApplicantRole', 'Wer stellt den Antrag?'))
                ->setSource([
                    'mother' => _t(__CLASS__ . '.ApplicantRole.mother', 'Mutter'),
                    'other_parent' => _t(__CLASS__ . '.ApplicantRole.other_parent', 'Vater / Lebenspartner(in) einer Eingetragenen Lebenspartnerschaft / gleichgeschlechtliche(r) Ehegatte(in)'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                LiteralField::create('OtherParentRoleNotice', '<div class="alert alert-info">' . _t(__CLASS__ . '.V0805Notice', 'Für jedes angegebene Kind ist zusätzlich ein Vordruck V0805 auszufüllen.') . '</div>'),
            )
                ->displayIf('ApplicantRole')->isEqualTo('other_parent')->end(),

            Wrapper::create(
                DropdownField::create('RaisingMode', _t(__CLASS__ . '.RaisingMode', '11.1 Wurden die Kinder in den angegebenen Erziehungszeiten gemeinsam oder allein, mit oder ohne Unterbrechung erzogen?'))
                    ->setSource([
                        'joint' => _t(__CLASS__ . '.RaisingMode.joint', 'ohne Unterbrechung gemeinsam mit dem anderen Elternteil'),
                        'alone' => _t(__CLASS__ . '.RaisingMode.alone', 'ohne Unterbrechung allein'),
                        'interrupted' => _t(__CLASS__ . '.RaisingMode.interrupted', 'mit Unterbrechung'),
                        'partly' => _t(__CLASS__ . '.RaisingMode.partly', 'teilweise allein und teilweise gemeinsam mit dem anderen Elternteil (zum Beispiel wegen Trennung, Tod des anderen Elternteils)'),
                    ])
                    ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --')
                    ->setDescription(_t(__CLASS__ . '.RaisingModeDescription', 'Eine Unterbrechung liegt zum Beispiel vor, wenn das Kind durch eine andere Person erzogen wurde, sich außerhalb Deutschlands aufgehalten hat oder auf gerichtliche Anordnung in einem Heim untergebracht war.')),
                Wrapper::create(
                    LiteralField::create('RaisingModeNotice', '<div class="alert alert-info">' . _t(__CLASS__ . '.V0805Notice', 'Für jedes angegebene Kind ist zusätzlich ein Vordruck V0805 auszufüllen.') . '</div>'),
                )
                    ->displayIf('RaisingMode')->isEqualTo('interrupted')
                    ->orIf('RaisingMode')->isEqualTo('partly')->end(),

                Wrapper::create(
                    DropdownField::create('OtherParentMainlyRaised', _t(__CLASS__ . '.OtherParentMainlyRaised', '11.2 Hat der andere Elternteil die Kinder in den angegebenen Erziehungszeiten überwiegend erzogen?'))
                        ->setSource([
                            'no' => _t(__CLASS__ . '.OtherParentMainlyRaised.no', 'nein'),
                            'partly' => _t(__CLASS__ . '.OtherParentMainlyRaised.partly', 'teilweise'),
                            'yes' => _t(__CLASS__ . '.OtherParentMainlyRaised.yes', 'ja'),
                        ])
                        ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
                    Wrapper::create(
                        LiteralField::create('MainlyRaisedNotice', '<div class="alert alert-info">' . _t(__CLASS__ . '.V0805Notice', 'Für jedes angegebene Kind ist zusätzlich ein Vordruck V0805 auszufüllen.') . '</div>'),
                    )
                        ->displayIf('OtherParentMainlyRaised')->isEqualTo('partly')
                        ->orIf('OtherParentMainlyRaised')->isEqualTo('yes')->end(),

                    // 11.3 - Angaben zum Elternteil, der den Antrag nicht stellt
                    Wrapper::create(
                        HeaderField::create('OtherParentHeader', _t(__CLASS__ . '.OtherParentHeader', '11.3 Angaben zum Elternteil, der den Antrag nicht stellt'), 5),
                        TextField::create('OtherParentLastName', _t(__CLASS__ . '.OtherParentLastName', 'Name')),
                        TextField::create('OtherParentFirstName', _t(__CLASS__ . '.OtherParentFirstName', 'Vorname')),
                        TextField::create('OtherParentNameSuffix', _t(__CLASS__ . '.OtherParentNameSuffix', 'Namenszusatz (Beispiel: Freifrau, Graf)')),
                        TextField::create('OtherParentNamePrefix', _t(__CLASS__ . '.OtherParentNamePrefix', 'Vorsatzworte zum Namen (Beispiel: von, van, de)')),
                        TextField::create('OtherParentTitle', _t(__CLASS__ . '.OtherParentTitle', 'Titel (Beispiel: Prof. Dr. med.)')),
                        TextField::create('OtherParentBirthName', _t(__CLASS__ . '.OtherParentBirthName', 'Geburtsname')),
                        TextField::create('OtherParentBirthNameSuffix', _t(__CLASS__ . '.OtherParentBirthNameSuffix', 'Namenszusatz zum Geburtsnamen')),
                        TextField::create('OtherParentBirthNamePrefix', _t(__CLASS__ . '.OtherParentBirthNamePrefix', 'Vorsatzworte zum Geburtsnamen')),
                        DateField::create('OtherParentBirthDate', _t(__CLASS__ . '.OtherParentBirthDate', 'Geburtsdatum')),
                        TextField::create('OtherParentInsuranceNumber', _t(__CLASS__ . '.OtherParentInsuranceNumber', 'Versicherungsnummer'))
                            ->setMaxLength(12),

                        DropdownField::create('OtherParentConfirmation', _t(__CLASS__ . '.OtherParentConfirmation', 'Bestätigung durch den anderen Elternteil'))
                            ->setSource([
                                'later' => _t(__CLASS__ . '.OtherParentConfirmation.later', 'Die Bestätigung des anderen Elternteils wird auf einer Kopie dieses Antrags nachgereicht.'),
                                'impossible' => _t(__CLASS__ . '.OtherParentConfirmation.impossible', 'Die Bestätigung des anderen Elternteils kann nicht erfolgen.'),
                            ])
                            ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --')
                            ->setDescription(_t(__CLASS__ . '.OtherParentConfirmationDescription', 'Der andere Elternteil muss die Angaben zur Erziehung mit seiner Unterschrift bestätigen.')),
                        Wrapper::create(
                            DropdownField::create('OtherParentConfirmationReason', _t(__CLASS__ . '.OtherParentConfirmationReason', 'Grund'))
                                ->setSource([
                                    'deceased' => _t(__CLASS__ . '.OtherParentConfirmationReason.deceased', 'Der andere Elternteil ist verstorben.'),
                                    'unknown' => _t(__CLASS__ . '.OtherParentConfirmationReason.unknown', 'Der Aufenthaltsort des anderen Elternteils ist unbekannt.'),
                                    'other' => _t(__CLASS__ . '.OtherParentConfirmationReason.other', 'Sonstige Gründe'),
                                ])
                                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
                            Wrapper::create(
                                TextField::create('OtherParentConfirmationOther', _t(__CLASS__ . '.OtherParentConfirmationOther', 'Sonstige Gründe')),
                            )
                                ->displayIf('OtherParentConfirmationReason')->isEqualTo('other')->end(),
                        )
                            ->displayIf('OtherParentConfirmation')->isEqualTo('impossible')->end(),
                    )
                        ->displayIf('OtherParentMainlyRaised')->isEqualTo('no')->end(),
                )
                    ->displayIf('RaisingMode')->isEqualTo('joint')->end(),
            )
                ->displayIf('ApplicantRole')->isEqualTo('mother')->end(),

            // Ziffer 12 - skipped when raised alone without interruption ("weiter bei Ziffer 13")
            Wrapper::create(
                DropdownField::create('PriorDeclaration', _t(__CLASS__ . '.PriorDeclaration', '12.1 Haben Sie oder der andere Elternteil für die Kinder eine übereinstimmende Erklärung über die Zuordnung der Erziehungszeiten zu einem anderen Elternteil bei einem Rentenversicherungsträger oder einem Versorgungsträger abgegeben?'))
                    ->setSource([
                        'no' => _t(__CLASS__ . '.PriorDeclaration.no', 'nein'),
                        'yes' => _t(__CLASS__ . '.PriorDeclaration.yes', 'ja'),
                    ])
                    ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
                Wrapper::create(
                    TextField::create('PriorDeclarationInstitution', _t(__CLASS__ . '.PriorDeclarationInstitution', 'Name des Rentenversicherungsträgers / Versorgungsträgers')),
                )
                    ->displayIf('PriorDeclaration')->isEqualTo('yes')->end(),
            )
                ->displayIf('ApplicantRole')->isEqualTo('other_parent')
                ->orIf('RaisingMode')->isEqualTo('joint')
                ->orIf('RaisingMode')->isEqualTo('interrupted')
                ->orIf('RaisingMode')->isEqualTo('partly')->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'ApplicantRole',
        ]);
    }
}
