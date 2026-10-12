# Template Tokens

There are various tokens that can be used in templates. You can find them listed
at URL path `/civicrm/civioffice/tokens`.

To retrieve single values from the application or the report there's a special
syntax:
```
{FundingApplicationProcess.request_data::<path>}
{FundingClearingProcess.report_data::<path>}
```

`<path>` has to be replaced with the path in the JSON object, e.g.
`participants.total`.

For selection values you might want to retrieve the label instead of the
technical value. This can be achieved by appending `:label`, e.g.
`{FundingApplicationProcess.request_data::topics:label}`.

With the `:label` suffix boolean values will be converted to *Yes* or *No*,
respectively, if there's no option label.
