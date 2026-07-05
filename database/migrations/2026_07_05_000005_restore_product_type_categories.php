<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// products.category_id (product TYPE, -> product_categories) was cleared while the
// craft-taxonomy migration reworked industries. Restore it by mapping each product
// to its product type from the name (specific keywords first). product_categories
// is a separate classification from the artisan-trade taxonomy on businesses.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_categories')) return;
        $catId = DB::table('product_categories')->pluck('id', 'slug'); // slug -> id

        // ordered: first keyword that appears in the (lowercased) product name wins
        $rules = [
            ['fumé', 'poisson-fume'], ['séché', 'poisson-fume'],
            ['tilapia', 'tilapia'], ['silure', 'silure-poisson-chat'], ['carpe', 'carpe'],
            ['crevette', 'crevettes'], ['conserve', 'conserves-poisson'], ['poisson marin', 'poissons-marins'],
            ['cacao', 'cacao'], ['fève', 'cacao'], ['café', 'cafe'],
            ['manioc', 'manioc-derives'], ['plantain', 'plantain'], ['maïs', 'mais'],
            ['poivre', 'poivre-penja'], ['penja', 'poivre-penja'], ['piment', 'piments'], ['sauce', 'piments'],
            ['karité', 'huiles-vegetales'], ['beurre', 'huiles-vegetales'], ['huile', 'huiles-vegetales'], ['miel', 'huiles-vegetales'],
            ['masque', 'masques'],
            ['statue', 'statuettes'], ['sculpture', 'statuettes'], ['figurine', 'statuettes'], ['ancêtre', 'statuettes'], ['djembé', 'statuettes'], ['djembe', 'statuettes'],
            ['tabouret', 'mobilier-bois'], ['mobilier', 'mobilier-bois'], ['meuble', 'mobilier-bois'], ['chaise', 'mobilier-bois'],
            ['natte', 'nattes'], ['raffia', 'nattes'], ['tapis', 'nattes'],
            ['panier', 'paniers'], ['corbeille', 'paniers'],
            ['terre cuite', 'recipients-terre-cuite'], ['canari', 'recipients-terre-cuite'], ['jarre', 'recipients-terre-cuite'], ['pot ', 'recipients-terre-cuite'],
            ['vase', 'poterie-ceramique-design'], ['céramique', 'ceramique-decorative'], ['ceramique', 'ceramique-decorative'],
            ['babouche', 'chaussures-artisanales'], ['chaussure', 'chaussures-artisanales'],
            ['sac', 'sacs-cuir'], ['portefeuille', 'sacs-cuir'], ['maroquin', 'sacs-cuir'],
            ['bracelet', 'bijoux-bronze'], ['bronze', 'bijoux-bronze'], ['laiton', 'bijoux-bronze'],
            ['collier', 'perles-colliers'], ['perle', 'perles-colliers'],
            ['robe', 'vetements-femme'],
            ['tissu', 'tissus-kaba'], ['ndop', 'tissus-kaba'], ['kaba', 'tissus-kaba'], ['wax', 'tissus-kaba'],
            ['accessoire', 'accessoires-mode'],
        ];

        foreach (DB::table('products')->whereNull('deleted_at')->whereNull('category_id')->get(['id', 'name_fr']) as $p) {
            $n = mb_strtolower($p->name_fr);
            foreach ($rules as [$needle, $slug]) {
                if (mb_strpos($n, $needle) !== false && isset($catId[$slug])) {
                    DB::table('products')->where('id', $p->id)->update(['category_id' => $catId[$slug]]);
                    break;
                }
            }
        }
    }

    public function down(): void
    {
        // no-op (data restoration)
    }
};
