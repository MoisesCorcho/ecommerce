<?php

declare(strict_types=1);

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * A wide viewport is not a tall one. The quick view capped itself at 90vh and
 * then suppressed its own scrolling from the `md` breakpoint up, so on a laptop
 * the actions at the bottom of the modal were clipped and unreachable.
 */
class QuickViewShortViewportTest extends DuskTestCase
{
    /** Wide enough for the two-column layout, short enough to overflow it. */
    private const LAPTOP = [1280, 800];

    private function openQuickView(Browser $browser): Browser
    {
        [$width, $height] = self::LAPTOP;

        $browser->resize($width, $height)
            ->visit('/products')
            ->waitFor('@product-card');

        // Opened from script rather than by hovering the card: CSS :hover is
        // unreliable in headless Chrome, and the reveal is not what these
        // tests are about — what happens once the modal is open is.
        $browser->driver->executeScript(
            "document.querySelector('[dusk=quick-view-trigger]').click();"
        );

        return $browser->waitFor('@quick-view-scroll');
    }

    public function test_the_modal_body_can_scroll_on_a_short_viewport(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openQuickView($browser);

            $overflow = $browser->driver->executeScript(
                "return getComputedStyle(document.querySelector('[dusk=quick-view-scroll]')).overflowY;"
            );

            $this->assertSame(
                'auto',
                $overflow,
                'The modal body must stay scrollable at every width; a wide screen is not necessarily a tall one.',
            );
        });
    }

    public function test_the_actions_at_the_bottom_stay_reachable(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openQuickView($browser);

            // Selenium scrolls an element into view before clicking, which is
            // exactly what a clipped, unscrollable container makes impossible.
            $browser->click('@quick-view-favorite')
                ->waitForLocation('/login');
        });
    }

    public function test_the_close_button_stays_put_while_the_body_scrolls(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openQuickView($browser);

            $before = $browser->driver->executeScript(
                "return document.querySelector('[dusk=quick-view-close]').getBoundingClientRect().top;"
            );

            $browser->driver->executeScript(
                "document.querySelector('[dusk=quick-view-scroll]').scrollTop = 9999;"
            );

            $after = $browser->driver->executeScript(
                "return document.querySelector('[dusk=quick-view-close]').getBoundingClientRect().top;"
            );

            // The close button is positioned against the modal box, so the box
            // itself must never be the scrolling element.
            $this->assertEqualsWithDelta(
                $before,
                $after,
                1.0,
                'The close button moved while the modal body scrolled, so it can be scrolled out of reach.',
            );
        });
    }
}
