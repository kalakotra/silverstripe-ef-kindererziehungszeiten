<?php

declare(strict_types=1);

namespace Kalakotra\EFormKindererziehungszeiten\Forms;

use app\forms\basemodel\BaseMultiForm;
use app\models\FormSubmission;
use Kalakotra\EFormKindererziehungszeiten\Model\Submission;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\SingleSelectField;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\MultiForm\Models\MultiFormStep;

/**
 * DRV V0800 - Antrag auf Feststellung von Kindererziehungszeiten.
 */
class Form extends BaseMultiForm
{
    private static $start_step = Form_Step1::class;

    public static function getFormName(): string
    {
        return 'Kindererziehungszeiten (V0800)';
    }

    public function finish($data, $form)
    {
        if (parent::finish($data, $form) === false) {
            return false;
        }

        $allData = $this->mergeStepData($this->getActiveSteps());
        if ($allData === []) {
            return $this->controller->redirectBack();
        }

        try {
            $submission = Submission::create();
            $submission->update($allData);
            $submission->write();

            $formSubmission = FormSubmission::create();
            $formSubmission->Email = $submission->ConfirmationEmail;
            $formSubmission->SubmissionID = $submission->ID;
            $formSubmission->Status = 0; // pending
            $formSubmission->ParentPageID = $this->controller->data()->ID;
            $formSubmission->write();

            $this->controller->getRequest()->getSession()->set('FormSubmissionID', $formSubmission->ID);
        } catch (\Throwable $e) {
            Injector::inst()->get(LoggerInterface::class)->error(
                'V0800 submission failed: ' . $e->getMessage(),
                ['exception' => $e]
            );
            $form->sessionError(_t(__CLASS__ . '.SAVE_ERROR', 'Es gab ein Problem beim Speichern Ihrer Daten. Bitte versuchen Sie es erneut.'));

            return $this->controller->redirectBack();
        }

        return $this->controller->redirect($this->controller->Link('/finished'));
    }

    /**
     * Preview only shows steps on the currently active path
     * (e.g. "Kind 2" disappears when the user went back and answered "no further child").
     */
    public function PreviewSteps(): ArrayList
    {
        $activeClasses = array_map(
            static fn (MultiFormStep $step): string => (string) $step->ClassName,
            $this->getActiveSteps()
        );

        return parent::PreviewSteps()->filterByCallback(
            static fn ($item): bool => in_array((string) $item->ClassName, $activeClasses, true)
        );
    }

    /**
     * Type-aware preview values based on Submission::$db (the base implementation detects dates by
     * regex-scanning step sources for "DateField::create('Name'", which misses helper-built fields).
     */
    public function formatPreviewValue(string $stepClass, string $key, $value, ?MultiFormStep $step = null): string
    {
        $type = Submission::config()->get('db')[$key] ?? null;

        if ($type === 'Date' && is_string($value) && $value !== '') {
            return $this->DateFormatting($value);
        }

        if ($type === 'Boolean') {
            if ($value === null || $value === '') {
                return ''; // cleared because hidden -> skipped in preview
            }

            return $value
                ? _t(__CLASS__ . '.BOOLEAN_YES', 'Ja')
                : _t(__CLASS__ . '.BOOLEAN_NO', 'Nein');
        }

        // option label straight from the field source: translated if a lang file exists,
        // German default otherwise (base only resolves options that exist in a lang file)
        if (is_scalar($value) && $value !== '') {
            $label = $this->optionSources($stepClass, $step)[$key][(string) $value] ?? null;
            if ($label !== null) {
                return (string) $label;
            }
        }

        return parent::formatPreviewValue($stepClass, $key, $value, $step);
    }

    /** @var array<string, array<string, array<string, string>>> */
    private array $optionSourceCache = [];

    /**
     * @return array<string, array<string, string>> field name => [value => label]
     */
    private function optionSources(string $stepClass, ?MultiFormStep $step): array
    {
        if (!isset($this->optionSourceCache[$stepClass])) {
            $this->optionSourceCache[$stepClass] = [];
            $instance = $step ?? (is_a($stepClass, MultiFormStep::class, true) ? $stepClass::singleton() : null);

            if ($instance) {
                $fields = $instance->getFields();
                // getDataFields() replaces the deprecated dataFields() in SS 6.2
                $dataFields = method_exists($fields, 'getDataFields') ? $fields->getDataFields() : $fields->dataFields();

                foreach ($dataFields as $name => $field) {
                    if ($field instanceof SingleSelectField) {
                        $this->optionSourceCache[$stepClass][$name] = array_map('strval', $field->getSource());
                    }
                }
            }
        }

        return $this->optionSourceCache[$stepClass];
    }

    /**
     * Walks the step chain from the start step using each step's getNextStep(),
     * so branches the user abandoned (but which still exist in the session) are excluded.
     *
     * @return array<int, MultiFormStep>
     */
    public function getActiveSteps(): array
    {
        $stepsByClass = [];
        $steps = MultiFormStep::get()->filter('SessionID', $this->getMultiFormSession()->ID);

        foreach ($steps as $step) {
            $step->setForm($this);
            $stepsByClass[$step->ClassName] = $step;
        }

        $active = [];
        $class = $this->config()->get('start_step');

        while (is_string($class) && isset($stepsByClass[$class]) && !isset($active[$class])) {
            $step = $stepsByClass[$class];
            $active[$class] = $step;

            if ($step->isFinalStep()) {
                break;
            }

            $class = $step->getNextStep();
        }

        return array_values($active);
    }
}
