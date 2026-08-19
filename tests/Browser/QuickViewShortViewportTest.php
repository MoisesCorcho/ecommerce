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

        return $browser->waitFor('@quick-view-details');
    }

    private function style(Browser $browser, string $dusk, string $property): string
    {
        return (string) $browser->driver->executeScript(
            "return getComputedStyle(document.querySelector('[dusk={$dusk}]')).{$property};"
        );
    }

    public function test_it_fits_without_scrolling_on_laptop_screens(): void
    {
        // The scroll below is a safety net for extremes, not the normal
        // experience: on the screens people actually shop from, everything
        // has to be on screen at once.
        foreach ([[1280, 800], [1366, 768], [1280, 720]] as [$width, $height]) {
            $this->browse(function (Browser $browser) use ($width, $height): void {
                $browser->resize($width, $height)
                    ->visit('/products')
                    ->waitFor('@product-card');

                $count = (int) $browser->driver->executeScript(
                    "return document.querySelectorAll('[dusk=quick-view-trigger]').length;"
                );

                for ($i = 0; $i < $count; $i++) {
                    $browser->driver->executeScript(
                        "document.querySelectorAll('[dusk=quick-view-trigger]')[{$i}].click();"
                    );
                    $browser->waitFor('@quick-view-details')->pause(250);

                    $m = $browser->driver->executeScript(
                        "const d = document.querySelector('[dusk=quick-view-details]');
                         const fav = document.querySelector('[dusk=quick-view-favorite]');
                         const box = document.querySelector('[dusk=quick-view-scroll]').parentElement;
                         return {
                             name: d.querySelector('h2').textContent.trim(),
                             overflow: d.scrollHeight - d.clientHeight,
                             favInside: fav ? fav.getBoundingClientRect().bottom <= box.getBoundingClientRect().bottom + 1 : true,
                         };"
                    );

                    $at = "{$width}x{$height}";

                    $this->assertSame(
                        0,
                        (int) $m['overflow'],
                        "[{$m['name']}] overflows its column by {$m['overflow']}px at {$at}.",
                    );

                    // Measured against the box, not the column: a column that is
                    // never constrained reports no overflow while its content
                    // spills out and gets clipped.
                    $this->assertTrue(
                        (bool) $m['favInside'],
                        "[{$m['name']}] pushes its last action outside the modal at {$at}.",
                    );

                    $browser->driver->executeScript(
                        "document.querySelector('[dusk=quick-view-close]').click();"
                    );
                    $browser->pause(200);
                }
            });
        }
    }

    public function test_the_colour_swatches_have_room_for_their_selection_ring(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openQuickView($browser);

            // The ring is drawn outside the swatch and the scrolling column
            // clips on both axes, so the row has to reserve the space.
            $padding = (float) $browser->driver->executeScript(
                "const row = document.querySelector('[dusk=quick-view-details] [role=radiogroup]');
                 return parseFloat(getComputedStyle(row).paddingTop);"
            );

            $this->assertGreaterThanOrEqual(
                4.0,
                $padding,
                'Without padding the selection ring is clipped by the scrolling column.',
            );
        });
    }

    public function test_the_action_labels_fit_on_one_line(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openQuickView($browser);

            $overflowing = $browser->driver->executeScript(
                "return [...document.querySelectorAll('[dusk=quick-view-details] button')]
                    .filter(e => e.scrollWidth > e.clientWidth + 1)
                    .map(e => e.textContent.trim().slice(0, 30));"
            );

            // Side by side the buttons are half as wide, and nowrap turns a
            // label that no longer fits into one that spills out.
            $this->assertSame([], $overflowing, 'Action labels are overflowing their buttons.');
        });
    }

    public function test_the_details_column_scrolls_on_a_short_viewport(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openQuickView($browser);

            $this->assertSame(
                'auto',
                $this->style($browser, 'quick-view-details', 'overflowY'),
                'The details column must stay scrollable at every width; a wide screen is not necessarily a tall one.',
            );
        });
    }

    public function test_the_photo_stays_put_while_the_details_scroll(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openQuickView($browser);

            // Scrolling the whole grid would drag the product photo out of
            // view along with the copy, which is not what a two-column
            // product modal should do.
            $this->assertSame(
                'hidden',
                $this->style($browser, 'quick-view-scroll', 'overflowY'),
                'The grid itself must not scroll in the two-column layout.',
            );
        });
    }

    public function test_no_scrollbar_is_painted(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openQuickView($browser);

            $this->assertSame(
                'none',
                $this->style($browser, 'quick-view-details', 'scrollbarWidth'),
                'A native scrollbar inside the modal reads as a system artefact dropped into the design.',
            );

            $painted = $browser->driver->executeScript(
                "const el = document.querySelector('[dusk=quick-view-details]');
                 return el.offsetWidth - el.clientWidth;"
            );

            $this->assertSame(0, (int) $painted, 'The scrollbar is still taking up layout width.');
        });
    }

    public function test_the_actions_at_the_bottom_stay_reachable(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openQuickView($browser);

            // Selenium scrolls an element into view before clicking, which is
            // exactly what a clipped, unscrollable container makes impossible.
            $browser->click('@quick-view-favorite')
                ->waitForLocation('/login')
                ->assertPathIs('/login');
        });
    }

    public function test_the_close_button_stays_put_while_the_details_scroll(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openQuickView($browser);

            $top = fn (): float => (float) $browser->driver->executeScript(
                "return document.querySelector('[dusk=quick-view-close]').getBoundingClientRect().top;"
            );

            $before = $top();

            $browser->driver->executeScript(
                "document.querySelector('[dusk=quick-view-details]').scrollTop = 9999;"
            );

            // The close button is positioned against the modal box, so the box
            // itself must never be the scrolling element.
            $this->assertEqualsWithDelta(
                $before,
                $top(),
                1.0,
                'The close button moved while the details scrolled, so it can be scrolled out of reach.',
            );
        });
    }
}
