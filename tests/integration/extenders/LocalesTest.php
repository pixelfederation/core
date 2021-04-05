<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Flarum\Tests\integration\extenders;

use Flarum\Extend;
use Flarum\Locale\Translator;
use Flarum\Testing\integration\TestCase;

class LocalesTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        array_map('unlink', glob($this->tmpDir().'/storage/locale/*'));
    }

    /**
     * @test
     */
    public function custom_translation_does_not_exist_by_default()
    {
        $this->app()->getContainer()->make('flarum.locales');
        $translator = $this->app()->getContainer()->make(Translator::class);

        $this->assertEquals('test.hello', $translator->trans('test.hello', ['name' => 'ACME']));
    }

    /**
     * @test
     */
    public function custom_translation_exists_if_added()
    {
        $this->extend(
            (new Extend\Locales(dirname(__FILE__, 3).'/fixtures/locales'))
        );

        $this->app()->getContainer()->make('flarum.locales');
        $translator = $this->app()->getContainer()->make(Translator::class);

        $this->assertEquals('World {ACME}', $translator->trans('test.hello', ['name' => 'ACME']));
    }

    /**
     * @test
     */
    public function custom_translation_exists_if_added_via_intl_file()
    {
        $this->extend(
            (new Extend\Locales(dirname(__FILE__, 3).'/fixtures/locales'))
        );

        $this->app()->getContainer()->make('flarum.locales');
        $translator = $this->app()->getContainer()->make(Translator::class);

        $this->assertEquals('World-intl ACME', $translator->trans('test.hello-intl', ['name' => 'ACME']));
    }

    /**
     * @test
     */
    public function messageformat_doesnt_work_in_regular_file()
    {
        $this->extend(
            (new Extend\Locales(dirname(__FILE__, 3).'/fixtures/locales'))
        );

        $this->app()->getContainer()->make('flarum.locales');
        $translator = $this->app()->getContainer()->make(Translator::class);

        $templateStringWithVars = '{female, select,
    female {{2, plural, offset:1
        =0    {{ACME} does not give a party.}
        =1    {{ACME} invites {ACME2} to her party.}
        =2    {{ACME} invites {ACME2} and one other person to her party.}
        other {{ACME} invites {ACME2} and # other people to her party.}
    }}
    male {{2, plural, offset:1
        =0    {{ACME} does not give a party.}
        =1    {{ACME} invites {ACME2} to his party.}
        =2    {{ACME} invites {ACME2} and one other person to his party.}
        other {{ACME} invites {ACME2} and # other people to his party.}
    }}
    other {{2, plural, offset:1
        =0    {{ACME} does not give a party.}
        =1    {{ACME} invites {ACME2} to their party.}
        =2    {{ACME} invites {ACME2} and one other person to their party.}
        other {{ACME} invites {ACME2} and # other people to their party.}
    }}
    }';
        $this->assertEquals($templateStringWithVars, $translator->trans('test.party-invitation', ['gender_of_host' => 'female', 'host' => 'ACME', 'num_guests' => 2, 'guest' => 'ACME2']));
    }

    /**
     * @test
     */
    public function messageformat_works_in_intl_icu_file()
    {
        $this->extend(
            (new Extend\Locales(dirname(__FILE__, 3).'/fixtures/locales'))
        );

        $this->app()->getContainer()->make('flarum.locales');
        $translator = $this->app()->getContainer()->make(Translator::class);

        $this->assertEquals('ACME invites ACME2 and one other person to her party.', $translator->trans('test.party-invitation-intl', ['gender_of_host' => 'female', 'host' => 'ACME', 'num_guests' => 2, 'guest' => 'ACME2']));
    }

    /**
     * @test
     */
    public function laravel_interface_methods_work()
    {
        $this->extend(
            (new Extend\Locales(dirname(__FILE__, 3).'/fixtures/locales'))
        );

        $this->app()->getContainer()->make('flarum.locales');
        $translator = $this->app()->getContainer()->make(Translator::class);

        $args = ['gender_of_host' => 'female', 'host' => 'ACME', 'num_guests' => 2, 'guest' => 'ACME2'];

        $this->assertEquals('ACME invites ACME2 and one other person to her party.', $translator->get('test.party-invitation-intl', $args));
        // Number doesn't matter
        $this->assertEquals('ACME invites ACME2 and one other person to her party.', $translator->choice('test.party-invitation-intl', 2, $args));
        $this->assertEquals('ACME invites ACME2 and one other person to her party.', $translator->choice('test.party-invitation-intl', 50, $args));
        $this->assertEquals('ACME invites ACME2 and one other person to her party.', $translator->choice('test.party-invitation-intl', -1000, $args));
        $this->assertEquals('ACME invites ACME2 and one other person to her party.', $translator->choice('test.party-invitation-intl', null, $args));
    }
}
