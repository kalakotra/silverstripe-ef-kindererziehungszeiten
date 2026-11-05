<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use Kalakotra\EFormKindererziehungszeiten\Model\FieldConditions;
use Kalakotra\EFormKindererziehungszeiten\Model\Submission;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * Shared behaviour for all V0800 steps (trait instead of an abstract base class,
 * because DbBuild instantiates every DataObject subclass via singleton()).
 *
 * Config read from the using class:
 *   required_fields       always required
 *   conditional_required  required only while visible (rules: Submission::$field_conditions)
 *
 * Translation keys are "<StepClass>.<Field>" and "<StepClass>.<Field>.<value>",
 * matching what BaseMultiForm::PreviewSteps() resolves for labels and option values.
 */
trait StepTrait
{
    public function getValidator(): RequiredFieldsValidator
    {
        return RequiredFieldsValidator::create((array) ($this->config()->get('required_fields') ?? []));
    }

    /**
     * Clears values of fields hidden by display-logic before persisting the step.
     *
     * @param array<string, mixed>|mixed $data
     */
    public function saveData($data)
    {
        if (is_array($data)) {
            $data = FieldConditions::apply($data, self::fieldConditionRules(), true);
        }

        return parent::saveData($data);
    }

    /**
     * @param array<string, mixed>|mixed $data
     */
    public function validateStep($data, $form)
    {
        return $this->validateConditionalRequired($data, $form) && parent::validateStep($data, $form);
    }

    /**
     * Steps overriding validateStep() call this to keep the conditional-required check.
     *
     * @param array<string, mixed>|mixed $data
     */
    protected function validateConditionalRequired($data, $form): bool
    {
        $required = (array) ($this->config()->get('conditional_required') ?? []);
        if ($required === [] || !is_array($data)) {
            return true;
        }

        $rules = self::fieldConditionRules();
        $valid = true;

        foreach ($required as $fieldName) {
            if (!FieldConditions::isActive($fieldName, $data, $rules)) {
                continue;
            }

            $value = $data[$fieldName] ?? null;
            if (is_scalar($value) && trim((string) $value) !== '') {
                continue;
            }

            $field = $form->Fields()->dataFieldByName($fieldName);
            $label = $field ? strip_tags((string) $field->Title()) : $fieldName;

            $form->sessionFieldError(
                _t(__TRAIT__ . '.REQUIRED', '"{name}" ist erforderlich.', ['name' => $label]),
                $fieldName
            );
            $valid = false;
        }

        return $valid;
    }

    /**
     * @return array<int, array{0: array<int, string>, 1: array<string, string|array<int, string>>}>
     */
    private static function fieldConditionRules(): array
    {
        return (array) Submission::config()->get('field_conditions');
    }

    protected function t(string $key, string $default): string
    {
        return _t(static::class . '.' . $key, $default);
    }

    protected function header(string $title): HeaderField
    {
        return HeaderField::create('IntroText', $this->t('IntroText', $title), 4);
    }

    protected function subHeader(string $name, string $title): HeaderField
    {
        return HeaderField::create($name, $this->t($name, $title), 5);
    }

    protected function text(string $name, string $title, ?int $maxLength = null): TextField
    {
        $field = TextField::create($name, $this->t($name, $title));
        if ($maxLength !== null) {
            $field->setMaxLength($maxLength);
        }

        return $field;
    }

    protected function date(string $name, string $title): DateField
    {
        return DateField::create($name, $this->t($name, $title));
    }

    /**
     * @param array<string, string> $options value => German default label
     */
    protected function dropdown(string $name, string $title, array $options): DropdownField
    {
        $source = [];
        foreach ($options as $value => $label) {
            $source[$value] = $this->t($name . '.' . $value, $label);
        }

        return DropdownField::create($name, $this->t($name, $title), $source)
            ->setEmptyString(_t(__TRAIT__ . '.DROPDOWN_EMPTY', 'Bitte wählen'));
    }

    protected function yesNo(string $name, string $title): DropdownField
    {
        return $this->dropdown($name, $title, ['no' => 'nein', 'yes' => 'ja']);
    }

    /**
     * "vom / bis" pair - the most common pattern on V0800.
     *
     * @return array<int, DateField>
     */
    protected function period(string $from, string $to): array
    {
        return [
            $this->date($from, 'vom'),
            $this->date($to, 'bis'),
        ];
    }

    protected function notice(string $name, string $text): LiteralField
    {
        return LiteralField::create(
            $name,
            '<div class="alert alert-info">' . htmlspecialchars($this->t($name, $text)) . '</div>'
        );
    }

    /**
     * Ziffer 2 - fields of one child block (Kind 1 / Kind 2), names prefixed "Child{n}".
     *
     * @return array<int, FormField>
     */
    protected function childFields(int $n): array
    {
        $p = 'Child' . $n;

        return [
            $this->text($p . 'Name', 'Name, Vorname des Kindes')
                ->setDescription($this->t(
                    $p . 'NameDescription',
                    'Zur Zeit der Geburt beziehungsweise bei einem Adoptivkind nach der Adoption'
                )),
            $this->text($p . 'NamePrefix', 'Vorsatzworte zum Namen (Beispiel: von, van, de)'),
            $this->text($p . 'BirthPlace', 'Geburtsort'),
            $this->text($p . 'BirthCountry', 'Geburtsstaat'),
            $this->date($p . 'BirthDate', 'Geburtsdatum'),
            $this->date($p . 'DeathDate', 'Gegebenenfalls Sterbedatum'),

            $this->yesNo($p . 'StepFoster', 'Ist das Kind ein Stiefkind oder Pflegekind?'),
            $this->group(
                $p . 'StepFosterGroup',
                $this->dropdown($p . 'StepFosterType', 'Art', [
                    'step' => 'Stiefkind',
                    'foster' => 'Pflegekind',
                ]),
                $this->notice(
                    $p . 'StepFosterNotice',
                    'Für jedes Stiefkind oder Pflegekind ist zusätzlich ein Vordruck V0805 auszufüllen.'
                )
            )->displayIf($p . 'StepFoster')->isEqualTo('yes')->end(),

            $this->dropdown(
                $p . 'RaisedSinceBirth',
                'Haben Sie das Kind von der Geburt an ununterbrochen erzogen '
                . '(Angabe längstens bis zur Vollendung des 10. Lebensjahres)?',
                ['yes' => 'ja', 'no' => 'nein']
            ),
            $this->group(
                $p . 'RaisedGroup',
                $this->date($p . 'RaisedFrom', 'Beginn'),
                $this->date($p . 'RaisedTo', 'Ende')
            )->displayIf($p . 'RaisedSinceBirth')->isEqualTo('no')->end(),
        ];
    }

    /**
     * Groups fields sharing one display condition:
     *   $this->group('Name', ...$fields)->displayIf('X')->isEqualTo('yes')->end()
     */
    protected function group(string $name, FormField ...$fields): Wrapper
    {
        $wrapper = Wrapper::create(...$fields);
        $wrapper->setName($name);

        return $wrapper;
    }
}
