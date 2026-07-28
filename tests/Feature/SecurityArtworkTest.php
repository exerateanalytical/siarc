<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The security artwork sheet is the one place on the platform where a visual
 * element could be mistaken for a security guarantee, so the tests are about
 * whether the artwork is genuinely what it claims: the microtext must really
 * be text, and the guilloché must really be generated mathematics.
 */
class SecurityArtworkTest extends TestCase
{
    use RefreshDatabase;

    private function sheet(): string
    {
        return view('pages.partials.coa-security')->render();
    }

    public function test_the_sheet_renders_and_defines_every_expected_symbol(): void
    {
        $svg = $this->sheet();

        foreach ([
            'coaGuillocheBorder',
            'coaGuillocheRosette',
            'coaMicrotextLine',
            'coaMicrotextPath',
            'coaAntiCopy',
            'coaHoloGradient',
        ] as $id) {
            $this->assertStringContainsString('id="' . $id . '"', $svg, "Missing symbol id {$id}");
        }
    }

    public function test_the_microtext_is_literally_text_not_a_decorative_line(): void
    {
        $svg = $this->sheet();

        $this->assertStringContainsString('ARTISANHUB237 CERTIFICATION AUTHORITY', $svg);
        $this->assertMatchesRegularExpression('/<text[^>]*font-size="1?\.?\d+(\.\d+)?"/', $svg);
    }

    public function test_the_guilloche_is_generated_mathematics_not_a_squiggle(): void
    {
        $svg = $this->sheet();

        preg_match_all('/<path id="(coaGuilloche[A-Za-z]*)"[^>]*\sd="([^"]+)"/', $svg, $m);
        $this->assertNotEmpty($m[2], 'No guilloché path with a d attribute was emitted.');

        foreach ($m[2] as $i => $d) {
            $this->assertGreaterThan(
                4000,
                strlen($d),
                'Guilloché path ' . $m[1][$i] . ' is too short to be a real hypotrochoid.'
            );
        }
    }

    public function test_no_id_collides_with_the_ornament_sheet(): void
    {
        $ornaments = view('pages.partials.coa-ornaments')->render();

        preg_match_all('/\sid="([^"]+)"/', $ornaments, $a);
        preg_match_all('/\sid="([^"]+)"/', $this->sheet(), $b);

        $this->assertSame([], array_values(array_intersect($a[1], $b[1])), 'Id collision with coa-ornaments.');
    }

    public function test_the_preview_route_is_not_available_outside_local(): void
    {
        // The test environment is not "local", so the guard must answer 404.
        $this->assertFalse(app()->environment('local'));
        $this->get('/apercu-securite')->assertNotFound();
    }
}
