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
        // 1280x700 is the tight one: a laptop with the bookmarks bar open leaves
        // roughly 557px of viewport, well under what the raw window size suggests.
        foreach ([[1280, 800], [1366, 768], [1280, 720], [1280, 700]] as [$width, $height]) {
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

    public function test_it_still_fits_after_adding_to_the_cart(): void
    {
        // Adding to the cart reveals an "already in your cart" badge. Stacked
        // under the label it cost about thirty-four pixels, so the modal grew
        // past the viewport at the exact moment the shopper acted on it.
        $this->browse(function (Browser $browser): void {
            $browser->resize(1280, 700)
                ->visit('/products')
                ->waitFor('@product-card');

            $browser->driver->executeScript(
                "document.querySelectorAll('[dusk=quick-view-trigger]')[3].click();"
            );
            $browser->waitFor('@quick-view-details')->pause(500);

            $browser->driver->executeScript(
                "[...document.querySelectorAll('[dusk=quick-view-details] button')]
                    .find(b => /carrito|cart/i.test(b.textContent)).click();"
            );
            $browser->pause(2500);

            $after = (int) $browser->driver->executeScript(
                "const d = document.querySelector('[dusk=quick-view-details]');
                 return d.scrollHeight - d.clientHeight;"
            );

            $this->assertSame(0, $after, "The modal overflows by {$after}px once the cart badge appears.");
        });
    }

    public function test_the_colour_swatches_have_room_for_their_selection_ring(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->openQuickView($browser);

            // The ring is drawn four pixels outside the swatch, and clipping
            // happens at the padding box of the scrolling column -- so the
            // room has to be reserved on the column, not on the row inside it.
            $clearance = (float) $browser->driver->executeScript(
                "const col = document.querySelector('[dusk=quick-view-details]');
                 const swatch = col.querySelector('[role=radiogroup] button');
                 const clipLeft = col.getBoundingClientRect().left
                     + parseFloat(getComputedStyle(col).borderLeftWidth);
                 return swatch.getBoundingClientRect().left - clipLeft;"
            );

            $this->assertGreaterThanOrEqual(
                4.0,
                $clearance,
                "Only {$clearance}px between the swatch and the clip edge; the selection ring needs 4.",
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
