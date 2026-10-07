<?php

declare(strict_types=1);

use Native\Mobile\Testing\FakeBridge;
use Native\Mobile\Testing\Native;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Simulates the device keychain/keystore for SecureStorage inside a test.
 *
 * FakeBridge records every SecureStorage.{Set,Get,Delete} call but doesn't
 * persist anything on its own — this wires SecureStorage.Get to replay the
 * most recent Set/Delete made for that key, so AuthTokenStore round-trips
 * correctly in tests. Call before Native::visit()/test().
 */
function fakeSecureStorage(): FakeBridge
{
    $bridge = Native::fakeBridge();

    $bridge->respondTo('SecureStorage.Get', function (array $params) use ($bridge): array {
        $writes = array_filter(
            $bridge->calls,
            fn (array $call) => in_array($call['method'], ['SecureStorage.Set', 'SecureStorage.Delete'], true)
                && ($call['params']['key'] ?? null) === $params['key'],
        );

        $last = end($writes);

        if ($last === false || $last['method'] === 'SecureStorage.Delete') {
            return ['value' => null];
        }

        return ['value' => $last['params']['value']];
    });

    $bridge->respondTo('SecureStorage.Set', ['success' => true]);
    $bridge->respondTo('SecureStorage.Delete', ['success' => true]);

    return $bridge;
}
