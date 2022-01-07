# MicroDeps/Curl

MicroDeps are all about very small pieces of code that do a single small thing well

This MicroDep is for Curl

Simply enough it provides some basic classes to aid with creating, configuring and executing Curl requests.

This is an alternative to bringing in large dependencies when you only need to make simple requests and woudl prefer to
keep things as simple as possible.

## Installing

You can bring this in as a composer dependency if you wish, though you are encouraged to simply copy/paste the code into
your project.

There is a WIP microdeps installer system which can automate the process of copying and updating MicroDeps within your
project: https://github.com/LongTermSupport/microdeps-installer

## Usage

```php
<?php
// Build the Curl Handle
$handle = (new CurlHandleFactory(new CurlOptionCollection()))->createGetHandle('https://www.github.com');
// Execute the handle and get a result object
$result = new CurlExec($handle);
if (false === $result->isSuccess()) {
    // error handling stuff here
    throw new \RuntimeException('Request failed: ' . $result->getError() . "\n" . $result->getInfoAsString());
}
$response = $result->getResponse();

```

## Developing

This package is intended to both be useful, and also to be an example of how to write modern well tested code utilising
the latest QA tools to enforce a high standard. You are encouraged to clone the repo and have a play with it and see how
it all works.

### PHP QA CI

This package is using PHP QA CI for the quality assurance and continuous integration. You can read more about that here:
https://github.com/LongTermSupport/php-qa-ci

#### To run QA process locally

To run the full QA process locally, simply run:

```bash
./bin/qa
```

## Long Term Support

This package was brought to you by Long Term Support LTD, a company run and founded by Joseph Edmonds

You can get in touch with Joseph at https://joseph.edmonds.contact/

Check out Joseph's recent book [The Art of Modern PHP 8](https://joseph.edmonds.contact/#book)

