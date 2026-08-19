<?php

declare(strict_types=1);

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CurrencySwitcherTest extends DuskTestCase
{
    /**
     * Dusk reuses the browser across tests in a class, so the currency cookie
     * written by one test would leak into the next one's starting state.
     */
    private function startClean(Browser $browser, int $width, int $height): Browser
    {
        $browser->resize($width, $height)->visit('/');
        $browser->driver->manage()->deleteAllCookies();

        return $browser->visit('/products');
    }

    public function test_desktop_dropdown_switches_the_storefront_currency(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->startClean($browser, 1440, 900)
                ->assertMissing('@currency-panel')
                ->click('@currency-trigger')
                ->waitFor('@currency-panel')
                ->waitForReload(function (Browser $browser): void {
                    $browser->click('@currency-option-USD');
                })
                ->assertSee('US$');
        });
    }

    public function test_the_currency_survives_navigation(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->startClean($browser, 1440, 900)
                ->click('@currency-trigger')
                ->waitFor('@currency-panel')
                ->waitForReload(function (Browser $browser): void {
                    $browser->click('@currency-option-EUR');
                })
                ->visit('/products')
                ->assertSee('€');
        });
    }

    public function test_the_mobile_menu_switches_the_currency(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->startClean($browser, 390, 844)
                ->click('@mobile-menu-toggle')
                ->waitFor('@currency-inline')
                ->waitForReload(function (Browser $browser): void {
                    $browser->click('@currency-inline-USD');
                })
                ->assertSee('US$');
        });
    }
}
