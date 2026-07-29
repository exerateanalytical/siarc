<?php

namespace Tests\Feature;

use App\Modules\Taxonomy\Models\Industry;
use App\Modules\Taxonomy\Support\IndustryKeepSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Locks in the taxonomy keep-set rule behind
 * database/migrations/2026_07_30_100000_prune_unused_industries.php, so a
 * future edit to the taxonomy or to IndustryKeepSet can't silently start
 * deleting (or keeping) the wrong rows.
 *
 * The rule: a category survives if, and only if, it is (a) referenced by a
 * business, (b) has an image_icon, or (c) is an ancestor of a category kept
 * under (a) or (b). Everything else is deleted.
 */
class IndustryTaxonomyPruneTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function makeIndustry(array $attrs = []): Industry
    {
        $name = 'Secteur ' . fake()->unique()->word();

        return Industry::forceCreate(array_merge([
            'slug'       => 'secteur-' . fake()->unique()->word(),
            'name_fr'    => $name,
            'name_en'    => $name,
            'level'      => 4,
            'is_active'  => true,
            'sort_order' => 1,
        ], $attrs));
    }

    public function test_keep_set_is_used_union_icon_union_ancestors_and_nothing_else(): void
    {
        // Build a 4-level chain: root -> branch -> used-leaf, plus a sibling
        // leaf under the same branch that nothing references.
        $root = $this->makeIndustry(['level' => 1]);
        $branch = $this->makeIndustry(['level' => 2, 'parent_id' => $root->id]);
        $usedLeaf = $this->makeIndustry(['level' => 4, 'parent_id' => $branch->id]);
        $unusedSibling = $this->makeIndustry(['level' => 4, 'parent_id' => $branch->id]);

        // A real business references the leaf — pulls it, and both its
        // ancestors, into the keep-set.
        $this->makeBusiness(null, ['industry_id' => $usedLeaf->id]);

        // A completely separate branch, referenced by nothing, with no icon:
        // this whole branch must be deleted.
        $deadRoot = $this->makeIndustry(['level' => 1]);
        $deadBranch = $this->makeIndustry(['level' => 2, 'parent_id' => $deadRoot->id]);
        $deadLeaf = $this->makeIndustry(['level' => 4, 'parent_id' => $deadBranch->id]);

        // An icon-tile category with no business behind it: kept for the
        // icon alone, and its parent must be kept too as an ancestor.
        $iconParent = $this->makeIndustry(['level' => 1]);
        $iconTile = $this->makeIndustry(['level' => 2, 'parent_id' => $iconParent->id, 'image_icon' => 'cat-icon-9.png']);

        // A category referenced only through a non-`businesses` FK table
        // (certifications), to prove the rule checks every referencing
        // table, not just businesses.
        $certParent = $this->makeIndustry(['level' => 1]);
        $certLeaf = $this->makeIndustry(['level' => 2, 'parent_id' => $certParent->id]);
        DB::table('certifications')->insert([
            'industry_id' => $certLeaf->id,
            'name_fr'     => 'Certification Test',
            'name_en'     => 'Test Certification',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $keepIds = IndustryKeepSet::ids();

        foreach ([$root, $branch, $usedLeaf, $iconParent, $iconTile, $certParent, $certLeaf] as $shouldSurvive) {
            $this->assertTrue(
                $keepIds->contains($shouldSurvive->id),
                "Expected industry #{$shouldSurvive->id} ({$shouldSurvive->name_fr}) to be in the keep-set."
            );
        }

        foreach ([$unusedSibling, $deadRoot, $deadBranch, $deadLeaf] as $shouldBeDeleted) {
            $this->assertFalse(
                $keepIds->contains($shouldBeDeleted->id),
                "Expected industry #{$shouldBeDeleted->id} ({$shouldBeDeleted->name_fr}) to NOT be in the keep-set."
            );
        }
    }

    public function test_delete_set_is_exactly_the_complement_of_the_keep_set(): void
    {
        $root = $this->makeIndustry(['level' => 1]);
        $branch = $this->makeIndustry(['level' => 2, 'parent_id' => $root->id]);
        $usedLeaf = $this->makeIndustry(['level' => 4, 'parent_id' => $branch->id]);
        $unusedLeaf = $this->makeIndustry(['level' => 4, 'parent_id' => $branch->id]);

        $this->makeBusiness(null, ['industry_id' => $usedLeaf->id]);

        $keepIds = IndustryKeepSet::ids();
        $totalIds = DB::table('industries')->pluck('id');
        $deleteIds = $totalIds->diff($keepIds);

        // The rule partitions every row into exactly one side: nothing is
        // both kept and deleted, and nothing is neither.
        $this->assertCount($totalIds->count(), $keepIds->merge($deleteIds)->unique());
        $this->assertTrue($deleteIds->contains($unusedLeaf->id));
        $this->assertFalse($deleteIds->contains($usedLeaf->id));
        $this->assertFalse($deleteIds->contains($branch->id));
        $this->assertFalse($deleteIds->contains($root->id));
    }
}
