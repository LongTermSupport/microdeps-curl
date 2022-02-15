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

## Quick Start

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

## Advanced

The library provides a few simple building blocks:

### CurlOptionCollection

The [CurlOptionCollection](./src/CurlOptionCollection.php) is simply a collection of Curl configuration options. Curl
options are all defined as constants within PHP and their values are integers. This collection object is used when
configuring a Curl handle. You should continue to use the built in Curl constants directly, generally you will get IDE
autocompletion for these if you simply type `CURLOPT_`.

It is worth noting that the library defines some default configuration which you might want to familiarise yourself
with.

### CurlConfigAwareHandle

One of the drawbacks of the built-in CurlHandle object is that there is no mechanism to introspect what configuration
has been applied to a handle.

The [CurlConfigAwareHandle](./src/CurlConfigAwareHandle.php) provides a simple wrapper aims to resolve that by taking a
collection of options and applying them. Once created, the options are expected to be immutable. YOu could of course get
the raw handle and then apply options, but that is definitely not advised.

### CurlHandleFactory

You may choose to build the CurlConfigAwareHandle manually, but you are encouraged to instead use
the [CurlHandleFactory](./src/CurlHandleFactory.php). It provides a few simple methods for common configurations such as
disabling SSL validation, configuring logging and setting custom headers. Further common configuration could easily be
added to this factory - pull requests gratefully received.

The factory provides a fluent interface, so configuration options can be chained together before finally calling
`createGetHandle` to actually create an instance of `CurlConfigAwareHandle` with the required configuration applied.

### CurlExecResult

To represent the result of a call, we have the [CurlExecResult](./src/CurlExecResult.php)

This is an immutable class that will execute the Curl request and then build the result data when the class is
constructed.

The class provides access to data such as a boolean `isSuccess`, the response text via `getResponse`. Note that the
class can also be configured to log all requests to a distinct file based on the final URL that is visited. This can be
very useful for debugging purposes and is achieved by passing in a path to a pre existing directory when creating the
result object.

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

