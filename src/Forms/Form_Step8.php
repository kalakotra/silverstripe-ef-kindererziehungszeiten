<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * Ziffer 10 - Ausnahmen bei Erziehung in Deutschland (10.1 - 10.5).
 */
class Form_Step8 extends MultiFormStep
{
    use StepTrait;

    private static $default_sort_order = 8;

    private static $next_steps = Form_Step9::class;

    private static array $required_fields = [
        'IntlOrganisation',
        'ForeignForces',
        'DiplomaticStaff',
        'Posted',
        'ExemptionAgreement',
    ];

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
            $this->header('Ausnahmen bei Erziehung in Deutschland'),
            $this->notice(
                'ExceptionsIntro',
                'Die folgenden Fragen beziehen sich auf die Zeit unmittelbar vor der Geburt eines der Kinder '
                . 'oder auf die angegebenen Erziehungszeiten in Deutschland.'
            ),

            ...$this->exception(
                'IntlOrganisation',
                '10.1 Waren Sie bei einer überstaatlichen oder zwischenstaatlichen Organisation beschäftigt '
                . '(zum Beispiel ESA, Europäische Gemeinschaften)?',
                $this->text('IntlOrganisationName', 'bei')
            ),
            ...$this->exception(
                'ForeignForces',
                '10.2 Waren Sie Mitglied oder ziviles Gefolge von ausländischen Streitkräften im Sinne des '
                . 'NATO-Truppenstatuts, der sowjetischen Streitkräfte, eine dem zivilen Gefolge gleichgestellte '
                . 'Person, Mitglied der alliierten Behörden und Streitkräfte in Berlin (West) oder '
                . 'Familienangehöriger einer dieser Personenkreise?',
                $this->text('ForeignForcesName', 'bei')
            ),
            ...$this->exception(
                'DiplomaticStaff',
                '10.3 Waren Sie Angehöriger des Personals einer ausländischen amtlichen Vertretung '
                . '(zum Beispiel Botschaft, Konsulat) oder Familienangehöriger dieses Personals?',
                $this->text('DiplomaticStaffName', 'bei')
            ),
            ...$this->exception(
                'Posted',
                '10.4 Waren Sie von einem Arbeitgeber mit Sitz im Ausland nach Deutschland entsandt oder im '
                . 'Rahmen Ihrer im Ausland ausgeübten selbständigen Erwerbstätigkeit für eine begrenzte Zeit '
                . 'in Deutschland tätig?',
                $this->text('PostedEmployer', 'Arbeitgeber'),
                $this->text('PostedWorkplace', 'Beschäftigungsstelle')
            ),
            ...$this->exception(
                'ExemptionAgreement',
                '10.5 Waren Sie in Deutschland beschäftigt oder tätig und wurden für diese Beschäftigung aufgrund '
                . 'einer Ausnahmevereinbarung (Europarecht, Sozialversicherungsabkommen) Pflichtbeiträge zur '
                . 'deutschen Rentenversicherung nicht gezahlt?',
                $this->text('ExemptionIssuer', 'Ausnahmevereinbarung wurde ausgestellt von'),
                $this->date('ExemptionIssueDate', 'Ausgestellt am'),
                $this->text('ExemptionReference', 'Aktenzeichen')
            )
        );
    }

    /**
     * yes/no question + "vom / bis" + extra fields shown on "ja".
     *
     * @return array<int, FormField>
     */
    private function exception(string $name, string $question, FormField ...$extra): array
    {
        return [
            $this->yesNo($name, $question),
            $this->group(
                $name . 'Group',
                ...[...$this->period($name . 'From', $name . 'To'), ...$extra]
            )->displayIf($name)->isEqualTo('yes')->end(),
        ];
    }
}
