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
 * Ziffer 10 - Ausnahmen bei Erziehung in Deutschland (10.1 - 10.5).
 */
class Form_Step8 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 8;

    private static $next_steps = Form_Step9::class;

    private static array $conditional_required = [
        'IntlOrganisationFrom',
        'IntlOrganisationTo',
        'IntlOrganisationName',
        'ForeignForcesFrom',
        'ForeignForcesTo',
        'ForeignForcesName',
        'DiplomaticStaffFrom',
        'DiplomaticStaffTo',
        'DiplomaticStaffName',
        'PostedFrom',
        'PostedTo',
        'PostedEmployer',
        'PostedWorkplace',
        'ExemptionAgreementFrom',
        'ExemptionAgreementTo',
        'ExemptionIssuer',
        'ExemptionIssueDate',
    ];

    public function getFields(): FieldList
    {
        return FieldList::create(
            HeaderField::create('IntroText', _t(__CLASS__ . '.IntroText', 'Ausnahmen bei Erziehung in Deutschland'), 4),
            LiteralField::create('ExceptionsIntro', '<div class="alert alert-info">' . _t(__CLASS__ . '.ExceptionsIntro', 'Die folgenden Fragen beziehen sich auf die Zeit unmittelbar vor der Geburt eines der Kinder oder auf die angegebenen Erziehungszeiten in Deutschland.') . '</div>'),

            DropdownField::create('IntlOrganisation', _t(__CLASS__ . '.IntlOrganisation', '10.1 Waren Sie bei einer überstaatlichen oder zwischenstaatlichen Organisation beschäftigt (zum Beispiel ESA, Europäische Gemeinschaften)?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.IntlOrganisation.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.IntlOrganisation.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                DateField::create('IntlOrganisationFrom', _t(__CLASS__ . '.IntlOrganisationFrom', 'vom')),
                DateField::create('IntlOrganisationTo', _t(__CLASS__ . '.IntlOrganisationTo', 'bis')),
                TextField::create('IntlOrganisationName', _t(__CLASS__ . '.IntlOrganisationName', 'bei')),
            )
                ->displayIf('IntlOrganisation')->isEqualTo('yes')->end(),

            DropdownField::create('ForeignForces', _t(__CLASS__ . '.ForeignForces', '10.2 Waren Sie Mitglied oder ziviles Gefolge von ausländischen Streitkräften im Sinne des NATO-Truppenstatuts, der sowjetischen Streitkräfte, eine dem zivilen Gefolge gleichgestellte Person, Mitglied der alliierten Behörden und Streitkräfte in Berlin (West) oder Familienangehöriger einer dieser Personenkreise?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.ForeignForces.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.ForeignForces.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                DateField::create('ForeignForcesFrom', _t(__CLASS__ . '.ForeignForcesFrom', 'vom')),
                DateField::create('ForeignForcesTo', _t(__CLASS__ . '.ForeignForcesTo', 'bis')),
                TextField::create('ForeignForcesName', _t(__CLASS__ . '.ForeignForcesName', 'bei')),
            )
                ->displayIf('ForeignForces')->isEqualTo('yes')->end(),

            DropdownField::create('DiplomaticStaff', _t(__CLASS__ . '.DiplomaticStaff', '10.3 Waren Sie Angehöriger des Personals einer ausländischen amtlichen Vertretung (zum Beispiel Botschaft, Konsulat) oder Familienangehöriger dieses Personals?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.DiplomaticStaff.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.DiplomaticStaff.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                DateField::create('DiplomaticStaffFrom', _t(__CLASS__ . '.DiplomaticStaffFrom', 'vom')),
                DateField::create('DiplomaticStaffTo', _t(__CLASS__ . '.DiplomaticStaffTo', 'bis')),
                TextField::create('DiplomaticStaffName', _t(__CLASS__ . '.DiplomaticStaffName', 'bei')),
            )
                ->displayIf('DiplomaticStaff')->isEqualTo('yes')->end(),

            DropdownField::create('Posted', _t(__CLASS__ . '.Posted', '10.4 Waren Sie von einem Arbeitgeber mit Sitz im Ausland nach Deutschland entsandt oder im Rahmen Ihrer im Ausland ausgeübten selbständigen Erwerbstätigkeit für eine begrenzte Zeit in Deutschland tätig?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.Posted.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.Posted.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                DateField::create('PostedFrom', _t(__CLASS__ . '.PostedFrom', 'vom')),
                DateField::create('PostedTo', _t(__CLASS__ . '.PostedTo', 'bis')),
                TextField::create('PostedEmployer', _t(__CLASS__ . '.PostedEmployer', 'Arbeitgeber')),
                TextField::create('PostedWorkplace', _t(__CLASS__ . '.PostedWorkplace', 'Beschäftigungsstelle')),
            )
                ->displayIf('Posted')->isEqualTo('yes')->end(),

            DropdownField::create('ExemptionAgreement', _t(__CLASS__ . '.ExemptionAgreement', '10.5 Waren Sie in Deutschland beschäftigt oder tätig und wurden für diese Beschäftigung aufgrund einer Ausnahmevereinbarung (Europarecht, Sozialversicherungsabkommen) Pflichtbeiträge zur deutschen Rentenversicherung nicht gezahlt?'))
                ->setSource([
                    'no' => _t(__CLASS__ . '.ExemptionAgreement.no', 'nein'),
                    'yes' => _t(__CLASS__ . '.ExemptionAgreement.yes', 'ja'),
                ])
                ->setEmptyString('-- ' . _t(__CLASS__ . '.DEFAULT_SELECT', 'Bitte auswählen') . ' --'),
            Wrapper::create(
                DateField::create('ExemptionAgreementFrom', _t(__CLASS__ . '.ExemptionAgreementFrom', 'vom')),
                DateField::create('ExemptionAgreementTo', _t(__CLASS__ . '.ExemptionAgreementTo', 'bis')),
                TextField::create('ExemptionIssuer', _t(__CLASS__ . '.ExemptionIssuer', 'Ausnahmevereinbarung wurde ausgestellt von')),
                DateField::create('ExemptionIssueDate', _t(__CLASS__ . '.ExemptionIssueDate', 'Ausgestellt am')),
                TextField::create('ExemptionReference', _t(__CLASS__ . '.ExemptionReference', 'Aktenzeichen')),
            )
                ->displayIf('ExemptionAgreement')->isEqualTo('yes')->end(),
        );
    }

    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create([
            'IntlOrganisation',
            'ForeignForces',
            'DiplomaticStaff',
            'Posted',
            'ExemptionAgreement',
        ]);
    }
}
