<?php

declare(strict_types=1);

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LocaleSwitcherTest extends DuskTestCase
{
    /**
     * Dusk reuses the browser across tests in a class, so the language cookie
     * written by one test would leak into the next one's starting state.
     */
    private function startClean(Browser $browser, int $width, int $height): Browser
    {
        $browser->resize($width, $height)->visit('/');
        $browser->driver->manage()->deleteAllCookies();

        return $browser->visit('/');
    }

    public function test_desktop_dropdown_opens_and_switches_the_language(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->startClean($browser, 1440, 900)
                ->assertSourceHas('<html lang="en"')
                // Collapsed until the trigger is clicked.
                ->assertMissing('@locale-panel')
                ->click('@locale-trigger')
                ->waitFor('@locale-panel')
                ->waitForReload(function (Browser $browser): void {
                    $browser->click('@locale-option-es');
                })
                ->assertSourceHas('<html lang="es"');
        });
    }

    public function test_the_choice_survives_navigation(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->startClean($browser, 1440, 900)
                ->click('@locale-trigger')
                ->waitFor('@locale-panel')
                ->waitForReload(function (Browser $browser): void {
                    $browser->click('@locale-option-es');
                })
                ->visit('/faq')
                ->assertSourceHas('<html lang="es"');
        });
    }

    public function test_the_dropdown_closes_with_the_escape_key(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->startClean($browser, 1440, 900)
                ->click('@locale-trigger')
                ->waitFor('@locale-panel')
                ->keys('@locale-trigger', ['{escape}'])
                ->waitUntilMissing('@locale-panel');
        });
    }

    public function test_the_mobile_menu_switches_the_language_without_a_nested_dropdown(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->startClean($browser, 390, 844)
                ->assertSourceHas('<html lang="en"')
                ->click('@mobile-menu-toggle')
                ->waitFor('@locale-inline')
                ->waitForReload(function (Browser $browser): void {
                    $browser->click('@locale-inline-es');
                })
                ->assertSourceHas('<html lang="es"');
        });
    }
}
