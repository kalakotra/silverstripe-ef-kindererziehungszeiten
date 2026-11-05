# V0800 – Kindererziehungszeiten

Form module for the e-formulare.de PDF filler (`kalakotra/pdf-editor`).
Fills DRV form **V0800** *Antrag auf Feststellung von Kindererziehungszeiten / Berücksichtigungszeiten
wegen Kindererziehung* (Version 45024, 10 pages, 162 fields) and stamps the applicant's signature.

Requires the pdf-editor app base classes (`app\forms\basemodel\*`, `app\models\FormSubmission`).

## Installation

pdf-editor `composer.json`:

```json
"repositories": [
    { "type": "git", "url": "https://github.com/kalakotra/silverstripe-ef-kindererziehungszeiten" }
],
"require": {
    "kalakotra/silverstripe-ef-kindererziehungszeiten": "^1.0"
}
```

```bash
composer update kalakotra/silverstripe-ef-kindererziehungszeiten
vendor/bin/sake db:build --flush
```

## CMS setup (PDFMultiFormPage)

| Tab | Setting |
|---|---|
| PDF | `PDF File` = original V0800.pdf (fillable, from deutsche-rentenversicherung.de) |
| Form | `Form Class Name` = **Kindererziehungszeiten (V0800)** |
| Customising | `SignaturePositionX/Y` – leave empty; module default is X 108 mm / Y 110 mm on page 10 |

`Paper header intro` and `Custom Field Mapping` are not used by this form.

## Flow

| Step | V0800 | Branching |
|---|---|---|
| 1 | Ziffer 1 – Person, Anschrift, Zuzug | |
| 2 | Ziffer 2 – Kind 1 | "weiteres Kind?" → Step 3 or Step 4 |
| 3 | Ziffer 2 – Kind 2 | |
| 4 | Ziffer 3, 4 – Wohnsitz DDR, Versorgung | |
| 5 | Ziffer 5 – Selbständige Tätigkeit | 5.3 only for Landwirt |
| 6 | Ziffer 6 – Erziehung im Ausland | 6.2.1 only if 6.2 = nein |
| 7 | Ziffer 7, 8, 9 – Status, Staatsangehörigkeit | 9.2 only for "andere" |
| 8 | Ziffer 10 – Ausnahmen | |
| 9 | Ziffer 11, 12 – Kindererziehung | father/partner skips 11.1–11.3; "allein" skips 12 |
| 10 | Ziffer 13, 14 – Barrierefreiheit, Ort | |
| Preview | E-Mail, Unterschrift, AGB | |

Max. 2 children per application (form limit). Not filled on purpose: signature date of the other parent
(11.3) and Ziffer 15 (filled by the authority).

## How it works

- **`Submission::$field_mapping`** – `'PDF field' => 'DbField'` or `'PDF field' => 'Method:DbField:Arg'`.
- **`Submission::$state_map`** – V0800 checkboxes are button groups with several on-states (`ja`, `ja1`, …)
  on *one* PDF field. `StateValue:DbField:PdfField` resolves the stored value to the on-state.
  Note: `AW_Ausl_PB` uses `"ja "` (trailing space) in the PDF.
- **`Submission::$field_conditions`** – declarative dependency rules, mirrored by display-logic in the steps.
  Used to (1) clear hidden values when a step is saved, (2) validate `conditional_required` fields only
  while visible, (3) clean the record in `onBeforeWrite()`. Format: see `FieldConditions`.
- **`Form::getActiveSteps()`** – submission and preview only use steps on the current path, so an abandoned
  branch (e.g. Kind 2 after answering "no further child") never reaches the PDF.
- Dates are 8-char comb fields → `DateFormating:Field:dmY`.
- Signature: hidden textarea filled by jSignature, validated server-side in `Form_Preview::validateStep()`
  (an HTML `required` on the hidden field would block the submit silently).

## Translations

`lang/de.yml`, `en.yml`, `bs.yml`. Every label is a literal `_t(__CLASS__ . '.Field', 'German default')` (collectable by
`i18nTextCollectorTask`); options use `<StepClass>.<Field>.<value>`, empty option `DEFAULT_SELECT`,
which is what `BaseMultiForm::PreviewSteps()` resolves for labels and option values.

## License

BSD-3-Clause, see [LICENSE](LICENSE).
