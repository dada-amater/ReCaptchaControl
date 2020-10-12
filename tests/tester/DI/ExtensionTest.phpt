<?php

declare(strict_types = 1);

use Tester\Assert;
use Nette\Forms\Form;
use ReCaptchaControl\Control;
use ReCaptchaControl\Renderer;
use ReCaptchaControl\Validator;
use Tests\Mocks\CustomRequester;
use Nette\Utils\AssertionException;
use ReCaptchaControl\Http\Requester\IRequester;
use ReCaptchaControl\Http\Requester\CurlRequester;
use ReCaptchaControl\Http\Requester\GuzzleRequester;
use ReCaptchaControl\Http\Requester\SimpleRequester;
use ReCaptchaControl\Http\Requester\RequestException;

require_once __DIR__ . '/../bootstrap.php';


// invalid fields
(static function (): void {

	// missing keys
	Assert::exception(static function (): void {
		createContainer(__DIR__ . '/config/config.missing.siteKey.neon');
	}, AssertionException::class, "The item 'siteKey' in array expects to be string or Nette\DI\Statement or Nette\DI\DynamicParameter, null given.");

	Assert::exception(static function (): void {
		createContainer(__DIR__ . '/config/config.missing.secretKey.neon');
	}, AssertionException::class, "The item 'secretKey' in array expects to be string or Nette\DI\Statement or Nette\DI\DynamicParameter, null given.");

	// invalid types
	Assert::exception(static function (): void {
		createContainer(__DIR__ . '/config/config.invalid.siteKey.neon');
	}, AssertionException::class, "The item 'siteKey' in array expects to be string or Nette\DI\Statement or Nette\DI\DynamicParameter, int 1 given.");

	Assert::exception(static function (): void {
		createContainer(__DIR__ . '/config/config.invalid.secretKey.neon');
	}, AssertionException::class, "The item 'secretKey' in array expects to be string or Nette\DI\Statement or Nette\DI\DynamicParameter, int 1 given.");

})();


// default services
(static function (): void {

	$container = createContainer(__DIR__ . '/config/config.simple.neon');
	Assert::type(Renderer::class, $container->getByType(Renderer::class));
	Assert::type(Validator::class, $container->getByType(Validator::class));
	Assert::type(CurlRequester::class, $container->getByType(IRequester::class));

})();


// SimpleRequester
(static function (): void {

	$container = createContainer(__DIR__ . '/config/config.simpleRequester.neon');
	Assert::type(SimpleRequester::class, $container->getByType(IRequester::class));

})();


// CurlRequester
(static function (): void {

	$container = createContainer(__DIR__ . '/config/config.curlRequester.neon');

	/** @var IRequester $requester */
	$requester = $container->getByType(IRequester::class);

	Assert::type(CurlRequester::class, $requester);

	Assert::exception(static function () use ($requester): void {
		$requester->post('https://google.com');
	}, RequestException::class, 'Cannot fetch URL "https://google.com": error setting certificate verify locations:%A%'); // check also the invalid certificate options right away

})();


// GuzzleRequester
(static function (): void {

	$container = createContainer(__DIR__ . '/config/config.guzzleRequester.neon');
	Assert::type(GuzzleRequester::class, $container->getByType(IRequester::class));

})();


// custom requester
(static function (): void {

	$container = createContainer(__DIR__ . '/config/config.customRequester.neon');

	/** @var IRequester $requester */
	$requester = $container->getByType(IRequester::class);

	Assert::type(CustomRequester::class, $requester);

	$response = $requester->post('http://example.com');
	Assert::true(stripos((string) $response, 'niamoD elpmaxE') !== false);

})();


// method name
(static function (): void {

	createContainer(__DIR__ . '/config/config.methodName.neon');

	$form = new Form;
	$form->addTheSweetRecaptcha('recaptcha');
	Assert::type(Control::class, $form['recaptcha']);

})();


// factories
(static function (): void {

	$container = createContainer(__DIR__ . '/config/config.factories.neon');
	Assert::type(CustomRequester::class, $container->getByType(IRequester::class));

})();
