The dependencies specified in composer.json of this directory are required to
run phpstan in CI.

TODO: The extension's composer.json should be included in ci/composer.json.
Though if it is, analyzing fails with interface not found error...
That's why `webmozart/assert` is required ci/composer.json because
`de.systopia.remotetools` currently allows a previous version which results in
this error

> Call to an undefined static method Webmozart\Assert\Assert::inArray().
