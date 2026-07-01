<?php

namespace Database\Seeders\Siac;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SiacBusinessesSeeder extends Seeder
{
    public function run(): void
    {
        $data = $this->businessData();

        foreach ($data as $bData) {
            // Create or find the user
            $user = User::firstOrCreate(
                ['email' => $bData['email']],
                [
                    'name'              => $bData['owner_name'],
                    'phone'             => $bData['phone'],
                    'password'          => Hash::make('password'),
                    'status'            => 'active',
                    'is_email_verified' => true,
                    'language_preference' => 'fr',
                ]
            );

            $user->assignRole('business_owner');

            $industry = DB::table('industries')->where('slug', $bData['industry'])->first();
            $region   = DB::table('regions')->where('code', $bData['region_code'])->first();
            $city     = DB::table('cities')->where('region_id', $region->id)->where('name_fr', $bData['city'])->first();

            $business = Business::create([
                'user_id'           => $user->id,
                'industry_id'       => $industry->id,
                'region_id'         => $region->id,
                'city_id'           => $city?->id,
                'name_fr'           => $bData['name_fr'],
                'name_en'           => $bData['name_en'] ?? $bData['name_fr'],
                'tagline_fr'        => $bData['tagline_fr'],
                'tagline_en'        => $bData['tagline_en'] ?? $bData['tagline_fr'],
                'description_fr'    => $bData['description_fr'],
                'description_en'    => $bData['description_en'] ?? $bData['description_fr'],
                'phone'             => $bData['phone'],
                'whatsapp'          => $bData['phone'],
                'email'             => $bData['email'],
                'address_fr'        => $bData['address'],
                'year_established'  => $bData['year'],
                'employee_count'    => $bData['employees'],
                'ownership_type'    => $bData['ownership'] ?? 'private',
                'languages_spoken'  => ['fr'],
                'verification_tier' => $bData['tier'],
                'status'            => 'published',
                'is_featured'       => $bData['featured'] ?? false,
                'views_count'       => rand(20, 800),
            ]);

            // Tags
            foreach ($bData['tags'] as $tag) {
                DB::table('business_tags')->insert(['business_id' => $business->id, 'tag' => $tag]);
            }

            // Products
            foreach ($bData['products'] as $pData) {
                $category = DB::table('product_categories')->where('slug', $pData['category'])->first();

                $product = Product::create([
                    'business_id'     => $business->id,
                    'category_id'     => $category?->id,
                    'name_fr'         => $pData['name_fr'],
                    'name_en'         => $pData['name_en'] ?? $pData['name_fr'],
                    'description_fr'  => $pData['description_fr'],
                    'description_en'  => $pData['description_en'] ?? $pData['description_fr'],
                    'quantity_unit'   => $pData['unit_fr'] ?? 'unité',
                    'moq'             => $pData['min_qty'] ?? 1,
                    'is_export_ready' => $pData['exported'] ?? false,
                    'status'          => 'published',
                    'views_count'     => rand(5, 300),
                    'sort_order'      => 1,
                ]);

                foreach ($pData['attributes'] ?? [] as $attr) {
                    $template = DB::table('attribute_templates')
                        ->where('name_fr', $attr['key_fr'])
                        ->orWhere('name_en', $attr['key_en'])
                        ->first();
                    if ($template) {
                        ProductAttribute::create([
                            'product_id'           => $product->id,
                            'attribute_template_id' => $template->id,
                            'value_fr'             => $attr['value_fr'],
                            'value_en'             => $attr['value_en'] ?? $attr['value_fr'],
                            'unit'                 => $attr['unit'] ?? null,
                        ]);
                    }
                }
            }

            $this->command->line("  + {$business->name_fr}");
        }

        $this->command->info('  Businesses & products seeded.');
    }

    private function businessData(): array
    {
        return [
            // ── ARTISANAT ──────────────────────────────────────────────────────
            [
                'name_fr' => 'Atelier Nguéma Sculptures',
                'name_en' => 'Nguema Sculpture Workshop',
                'tagline_fr' => 'L\'âme du bois camerounais, sculptée à la main',
                'tagline_en' => 'The soul of Cameroonian wood, hand-carved',
                'description_fr' => 'Fondé en 2005, notre atelier perpétue la tradition sculptée des peuples Beti et Bassa. Masques cérémoniels, statuettes, mobilier décoratif — chaque pièce est unique, taillée dans des essences locales durables : iroko, sipo, doussié.',
                'description_en' => 'Founded in 2005, our workshop perpetuates the carving tradition of the Beti and Bassa peoples. Ceremonial masks, statuettes, decorative furniture — each piece is unique, carved from sustainable local wood species.',
                'email' => 'nguemasculptures@example.cm',
                'phone' => '+237 677 12 34 56',
                'owner_name' => 'Paul Nguema',
                'region_code' => 'CE',
                'city' => 'Yaoundé',
                'address' => 'Quartier Bastos, Rue des Artisans, Yaoundé',
                'year' => 2005,
                'employees' => 7,
                'industry' => 'artisanat',
                'tier' => 'certified',
                'featured' => true,
                'tags' => ['sculpture', 'masques', 'bois', 'beti', 'artisanat'],
                'products' => [
                    [
                        'category' => 'masques',
                        'name_fr' => 'Masque Beti Ngil',
                        'name_en' => 'Beti Ngil Mask',
                        'description_fr' => 'Masque cérémoniel Ngil de la tradition Beti, sculpté en bois d\'iroko. Pièce authentique destinée à la décoration ou la collection. Finition naturelle à l\'huile de palme.',
                        'description_en' => 'Ngil ceremonial mask from the Beti tradition, carved in iroko wood. Authentic piece for decoration or collection. Natural palm oil finish.',
                        'unit_fr' => 'pièce', 'unit_en' => 'piece',
                        'min_qty' => 1, 'lead_time' => 14, 'exported' => true, 'featured' => true,
                        'attributes' => [
                            ['key_fr' => 'Matériau', 'key_en' => 'Material', 'value_fr' => 'Bois d\'iroko', 'value_en' => 'Iroko wood'],
                            ['key_fr' => 'Dimensions', 'key_en' => 'Dimensions', 'value_fr' => '45 × 22 cm', 'value_en' => '45 × 22 cm'],
                            ['key_fr' => 'Finition', 'key_en' => 'Finish', 'value_fr' => 'Huile de palme naturelle', 'value_en' => 'Natural palm oil'],
                        ],
                    ],
                    [
                        'category' => 'statuettes',
                        'name_fr' => 'Statue Ancêtre Bassa',
                        'name_en' => 'Bassa Ancestor Statue',
                        'description_fr' => 'Statue représentant un ancêtre dans la tradition Bassa. Bois de sipo, traitement antifongique naturel. Hauteur 60cm.',
                        'description_en' => 'Statue representing an ancestor in the Bassa tradition. Sipo wood, natural antifungal treatment. Height 60cm.',
                        'unit_fr' => 'pièce', 'unit_en' => 'piece',
                        'min_qty' => 1, 'lead_time' => 21, 'exported' => true,
                        'attributes' => [
                            ['key_fr' => 'Matériau', 'key_en' => 'Material', 'value_fr' => 'Bois de sipo', 'value_en' => 'Sipo wood'],
                            ['key_fr' => 'Hauteur', 'key_en' => 'Height', 'value_fr' => '60 cm', 'value_en' => '60 cm'],
                        ],
                    ],
                    [
                        'category' => 'mobilier-bois',
                        'name_fr' => 'Tabouret Traditionnel Batoufam',
                        'name_en' => 'Traditional Batoufam Stool',
                        'description_fr' => 'Tabouret à trois pieds sculpté dans un bloc de doussié, motifs géométriques Bamoun. Pièce fonctionnelle et décorative.',
                        'description_en' => 'Three-legged stool carved from a block of doussie, Bamoun geometric motifs. Functional and decorative piece.',
                        'unit_fr' => 'pièce', 'unit_en' => 'piece',
                        'min_qty' => 2, 'lead_time' => 21, 'exported' => true,
                    ],
                ],
            ],
            [
                'name_fr' => 'Coopérative Tissage Ndop',
                'name_en' => 'Ndop Weaving Cooperative',
                'tagline_fr' => 'Le tissu royal Ndop, gardien d\'une tradition millénaire',
                'tagline_en' => 'Royal Ndop fabric, guardian of a thousand-year tradition',
                'description_fr' => 'Regroupant 24 tisserandes des villages de Babungo et Ndop dans la région Nord-Ouest, notre coopérative produit le tissu Ndop authentique — tissu royal indigo aux motifs géométriques réservé jadis aux chefs. Chaque pièce est intégralement tissée à la main sur métier traditionnel.',
                'description_en' => 'Bringing together 24 weavers from the villages of Babungo and Ndop in the North West region, our cooperative produces authentic Ndop fabric — royal indigo fabric with geometric patterns once reserved for chiefs. Each piece is entirely hand-woven on a traditional loom.',
                'email' => 'ndoptissage@example.cm',
                'phone' => '+237 699 45 67 89',
                'owner_name' => 'Marie Nkengafac',
                'region_code' => 'NW',
                'city' => 'Ndop',
                'address' => 'Village Babungo, Ndop, Nord-Ouest',
                'year' => 2010,
                'employees' => 30,
                'industry' => 'artisanat',
                'ownership' => 'cooperative',
                'tier' => 'verified',
                'featured' => true,
                'tags' => ['ndop', 'tissage', 'indigo', 'textile', 'nord-ouest', 'cooperatve'],
                'products' => [
                    [
                        'category' => 'tissus-kaba',
                        'name_fr' => 'Tissu Ndop Authentique — Pièce 6 yards',
                        'name_en' => 'Authentic Ndop Fabric — 6 Yards Piece',
                        'description_fr' => 'Tissu Ndop tissé à la main, teint à l\'indigo naturel. Motifs géométriques traditionnels Babungo. 6 yards (5,5m) — mesure standard pour une tenue complète. Certification d\'authenticité incluse.',
                        'description_en' => 'Hand-woven Ndop fabric, dyed with natural indigo. Traditional Babungo geometric patterns. 6 yards (5.5m) — standard measure for a complete outfit. Certificate of authenticity included.',
                        'unit_fr' => 'pièce (6 yards)', 'unit_en' => 'piece (6 yards)',
                        'min_qty' => 1, 'lead_time' => 30, 'exported' => true, 'featured' => true,
                        'attributes' => [
                            ['key_fr' => 'Longueur', 'key_en' => 'Length', 'value_fr' => '5,5 mètres (6 yards)', 'value_en' => '5.5 meters (6 yards)'],
                            ['key_fr' => 'Largeur', 'key_en' => 'Width', 'value_fr' => '50 cm', 'value_en' => '50 cm'],
                            ['key_fr' => 'Teinture', 'key_en' => 'Dye', 'value_fr' => 'Indigo naturel', 'value_en' => 'Natural indigo'],
                            ['key_fr' => 'Origine ethnique', 'key_en' => 'Ethnic origin', 'value_fr' => 'Babungo / Ndop', 'value_en' => 'Babungo / Ndop'],
                        ],
                    ],
                    [
                        'category' => 'nattes',
                        'name_fr' => 'Natte Raffia Tressée',
                        'name_en' => 'Woven Raffia Mat',
                        'description_fr' => 'Natte en raphia naturel tressée à la main par les artisanes de Ndop. Dimensions 1,5m × 1m. Idéale pour la décoration intérieure ou comme tapis de sol.',
                        'description_en' => 'Natural raffia mat hand-woven by Ndop craftswomen. Dimensions 1.5m × 1m. Ideal for interior decoration or as a floor mat.',
                        'unit_fr' => 'pièce', 'unit_en' => 'piece',
                        'min_qty' => 5, 'lead_time' => 14,
                    ],
                ],
            ],
            [
                'name_fr' => 'Poteries de Maroua',
                'name_en' => 'Maroua Pottery',
                'tagline_fr' => 'Terre du Sahel façonnée par des mains expertes depuis 3 générations',
                'tagline_en' => 'Sahel clay shaped by expert hands for 3 generations',
                'description_fr' => 'Famille Daouda, potières depuis trois générations à Maroua. Nous perpétuons les techniques ancestrales de cuisson à ciel ouvert pour créer des récipients utilitaires et des pièces décoratives inspirées de l\'art Mandara et Peul.',
                'description_en' => 'Daouda family, potters for three generations in Maroua. We perpetuate ancestral open-air firing techniques to create utilitarian vessels and decorative pieces inspired by Mandara and Fulani art.',
                'email' => 'poteriesmaroua@example.cm',
                'phone' => '+237 690 23 45 67',
                'owner_name' => 'Aminatou Daouda',
                'region_code' => 'EN',
                'city' => 'Maroua',
                'address' => 'Quartier Artisanal, Maroua, Extrême-Nord',
                'year' => 1988,
                'employees' => 7,
                'industry' => 'artisanat',
                'tier' => 'verified',
                'featured' => false,
                'tags' => ['poterie', 'ceramique', 'mandara', 'peul', 'extreme-nord'],
                'products' => [
                    [
                        'category' => 'recipients-terre-cuite',
                        'name_fr' => 'Canari Traditionnel — Grande Taille',
                        'name_en' => 'Traditional Clay Pot — Large Size',
                        'description_fr' => 'Grand canari en terre cuite cuit à ciel ouvert, technique ancestrale Mandara. Contenance 15 litres. Utilisé pour conserver l\'eau fraîche ou la bière de mil. Imperméabilisé à l\'huile végétale.',
                        'description_en' => 'Large terracotta pot fired in the open air, Mandara ancestral technique. Capacity 15 liters. Used to store fresh water or millet beer. Waterproofed with vegetable oil.',
                        'unit_fr' => 'pièce', 'unit_en' => 'piece',
                        'min_qty' => 2, 'lead_time' => 7, 'exported' => true, 'featured' => true,
                        'attributes' => [
                            ['key_fr' => 'Matériau', 'key_en' => 'Material', 'value_fr' => 'Terre cuite', 'value_en' => 'Terracotta'],
                            ['key_fr' => 'Contenance', 'key_en' => 'Capacity', 'value_fr' => '15 litres', 'value_en' => '15 liters'],
                            ['key_fr' => 'Cuisson', 'key_en' => 'Firing', 'value_fr' => 'À ciel ouvert (traditionnel)', 'value_en' => 'Open-air (traditional)'],
                        ],
                    ],
                    [
                        'category' => 'ceramique-decorative',
                        'name_fr' => 'Vase Décoratif Motifs Peul',
                        'name_en' => 'Decorative Vase with Fulani Motifs',
                        'description_fr' => 'Vase en terre cuite décorée de motifs géométriques Peul gravés avant cuisson. Hauteur 35cm. Idéal pour la décoration intérieure.',
                        'description_en' => 'Terracotta vase decorated with Fulani geometric motifs engraved before firing. Height 35cm. Ideal for interior decoration.',
                        'unit_fr' => 'pièce', 'unit_en' => 'piece',
                        'min_qty' => 1, 'lead_time' => 10, 'exported' => true,
                    ],
                ],
            ],
            [
                'name_fr' => 'Maroquinerie Foumban',
                'name_en' => 'Foumban Leather Craft',
                'tagline_fr' => 'Cuir tanné végétalement, brodé à la main dans la tradition Bamoun',
                'tagline_en' => 'Vegetable-tanned leather, hand-embroidered in the Bamoun tradition',
                'description_fr' => 'Installés dans la cité royale de Foumban, nous créons des articles de maroquinerie de haute qualité selon les techniques Bamoun transmises de maître en apprenti. Peaux tannées végétalement, broderies à l\'aiguille sur cuir brut.',
                'description_en' => 'Based in the royal city of Foumban, we create high-quality leather goods using Bamoun techniques passed from master to apprentice. Vegetable-tanned hides, needle embroidery on raw leather.',
                'email' => 'maroquineriefoumban@example.cm',
                'phone' => '+237 655 78 90 12',
                'owner_name' => 'Ibrahim Njimofor',
                'region_code' => 'OU',
                'city' => 'Foumban',
                'address' => 'Marché des Artisans, Foumban, Ouest',
                'year' => 2001,
                'employees' => 15,
                'industry' => 'artisanat',
                'tier' => 'certified',
                'featured' => true,
                'tags' => ['maroquinerie', 'cuir', 'bamoun', 'foumban', 'broderie'],
                'products' => [
                    [
                        'category' => 'sacs-cuir',
                        'name_fr' => 'Sac Messager Brodé Bamoun',
                        'name_en' => 'Bamoun Embroidered Messenger Bag',
                        'description_fr' => 'Sac messager en cuir de vache tanné végétalement, brodé de motifs géométriques Bamoun au fil de coton. Bandoulière réglable. Intérieur doublé tissu coton.',
                        'description_en' => 'Vegetable-tanned cowhide messenger bag, embroidered with Bamoun geometric motifs in cotton thread. Adjustable shoulder strap. Cotton fabric lining.',
                        'unit_fr' => 'pièce', 'unit_en' => 'piece',
                        'min_qty' => 1, 'lead_time' => 14, 'exported' => true, 'featured' => true,
                        'attributes' => [
                            ['key_fr' => 'Matériau', 'key_en' => 'Material', 'value_fr' => 'Cuir vache tanné végétalement', 'value_en' => 'Vegetable-tanned cowhide'],
                            ['key_fr' => 'Dimensions', 'key_en' => 'Dimensions', 'value_fr' => '35 × 27 × 8 cm', 'value_en' => '35 × 27 × 8 cm'],
                            ['key_fr' => 'Broderie', 'key_en' => 'Embroidery', 'value_fr' => 'Fil coton, motifs Bamoun', 'value_en' => 'Cotton thread, Bamoun motifs'],
                        ],
                    ],
                    [
                        'category' => 'sacs-cuir',
                        'name_fr' => 'Portefeuille Cuir Gravé',
                        'name_en' => 'Engraved Leather Wallet',
                        'description_fr' => 'Portefeuille fin en cuir de chèvre tanné végétalement. Motifs Bamoun gravés au pyrographe. 8 emplacements cartes, 2 compartiments billets.',
                        'description_en' => 'Slim wallet in vegetable-tanned goat leather. Bamoun motifs engraved with pyrography. 8 card slots, 2 bill compartments.',
                        'unit_fr' => 'pièce', 'unit_en' => 'piece',
                        'min_qty' => 5, 'lead_time' => 7, 'exported' => true,
                    ],
                    [
                        'category' => 'chaussures-artisanales',
                        'name_fr' => 'Babouches Brodées Tradition',
                        'name_en' => 'Embroidered Traditional Slippers',
                        'description_fr' => 'Babouches en cuir tanné végétalement, semelles en caoutchouc naturel. Broderies multicolores réalisées à la main. Du 38 au 46.',
                        'description_en' => 'Slippers in vegetable-tanned leather, natural rubber soles. Hand-made multicolored embroidery. Sizes 38 to 46.',
                        'unit_fr' => 'paire', 'unit_en' => 'pair',
                        'min_qty' => 3, 'lead_time' => 10, 'exported' => true,
                    ],
                ],
            ],
            [
                'name_fr' => 'Bijoux Bronze du Grassland',
                'name_en' => 'Grassland Bronze Jewelry',
                'tagline_fr' => 'Fonte à la cire perdue — un art vieux de 500 ans',
                'tagline_en' => 'Lost-wax casting — an art 500 years old',
                'description_fr' => 'Héritiers des fondeurs royaux de la cour du Sultan de Bafia, nous perpétuons la technique de la fonte à la cire perdue pour créer des bijoux et ornements en bronze et laiton. Chaque pièce porte l\'empreinte des symboles royaux de la chefferie Bamiléké.',
                'description_en' => 'Heirs of the royal founders of the Sultan of Bafia\'s court, we perpetuate the lost-wax casting technique to create bronze and brass jewelry and ornaments. Each piece bears the imprint of Bamileke chieftaincy royal symbols.',
                'email' => 'bijouxbronze.grassland@example.cm',
                'phone' => '+237 670 34 56 78',
                'owner_name' => 'Christophe Tchouako',
                'region_code' => 'OU',
                'city' => 'Bafoussam',
                'address' => 'Marché Central, Bafoussam, Ouest',
                'year' => 1995,
                'employees' => 7,
                'industry' => 'artisanat',
                'tier' => 'verified',
                'featured' => false,
                'tags' => ['bronze', 'bijoux', 'bamileke', 'fonte', 'cire-perdue'],
                'products' => [
                    [
                        'category' => 'bijoux-bronze',
                        'name_fr' => 'Bracelet Bronze Royal Bamiléké',
                        'name_en' => 'Royal Bamileke Bronze Bracelet',
                        'description_fr' => 'Bracelet en bronze fondu à la cire perdue, motifs spider web symbolisant la royauté Bamiléké. Diamètre intérieur 6,5 cm. Livré avec certificat d\'authenticité.',
                        'description_en' => 'Bronze bracelet cast using lost-wax technique, spider web motifs symbolizing Bamileke royalty. Inner diameter 6.5 cm. Comes with certificate of authenticity.',
                        'unit_fr' => 'pièce', 'unit_en' => 'piece',
                        'min_qty' => 1, 'lead_time' => 21, 'exported' => true, 'featured' => true,
                        'attributes' => [
                            ['key_fr' => 'Matériau', 'key_en' => 'Material', 'value_fr' => 'Bronze (cuivre + étain)', 'value_en' => 'Bronze (copper + tin)'],
                            ['key_fr' => 'Technique', 'key_en' => 'Technique', 'value_fr' => 'Fonte à la cire perdue', 'value_en' => 'Lost-wax casting'],
                            ['key_fr' => 'Diamètre', 'key_en' => 'Diameter', 'value_fr' => '6,5 cm intérieur', 'value_en' => '6.5 cm inner'],
                        ],
                    ],
                    [
                        'category' => 'perles-colliers',
                        'name_fr' => 'Collier Perles Royales',
                        'name_en' => 'Royal Beaded Necklace',
                        'description_fr' => 'Collier de perles de verre de Venise colorées, selon les codes de couleur royaux Bamiléké. Rouge, noir et blanc dominants. Longueur 60cm.',
                        'description_en' => 'Colored Venetian glass bead necklace, following Bamileke royal color codes. Dominant red, black and white. Length 60cm.',
                        'unit_fr' => 'pièce', 'unit_en' => 'piece',
                        'min_qty' => 2, 'lead_time' => 7, 'exported' => true,
                    ],
                ],
            ],
            // ── AQUACULTURE ──────────────────────────────────────────────────────
            [
                'name_fr' => 'Pisciculture du Wouri',
                'name_en' => 'Wouri Fish Farm',
                'tagline_fr' => 'Tilapia et silure d\'élevage de qualité premium, livraison Douala',
                'tagline_en' => 'Premium quality farmed tilapia and catfish, Douala delivery',
                'description_fr' => 'Ferme piscicole de 12 bassins en terre battue sur les rives du fleuve Wouri. Élevage de tilapia du Nil (Oreochromis niloticus) et de silure africain (Clarias gariepinus) selon les normes FAO. Production annuelle de 18 tonnes. Livraison en camion frigorifique sur Douala et périphérie.',
                'description_en' => '12-pond earthen fish farm on the banks of the Wouri River. Farming of Nile tilapia (Oreochromis niloticus) and African catfish (Clarias gariepinus) according to FAO standards. Annual production of 18 tonnes. Refrigerated truck delivery in Douala and surroundings.',
                'email' => 'pisciculturewouri@example.cm',
                'phone' => '+237 691 56 78 90',
                'owner_name' => 'Roger Ekwalla',
                'region_code' => 'LT',
                'city' => 'Douala',
                'address' => 'PK14, Bord du Wouri, Douala',
                'year' => 2014,
                'employees' => 15,
                'industry' => 'aquaculture',
                'ownership' => 'private',
                'tier' => 'certified',
                'featured' => true,
                'tags' => ['tilapia', 'silure', 'pisciculture', 'wouri', 'douala', 'siac'],
                'products' => [
                    [
                        'category' => 'tilapia',
                        'name_fr' => 'Tilapia Vivant — Bassin (100 kg)',
                        'name_en' => 'Live Tilapia — Pond (100 kg)',
                        'description_fr' => 'Tilapia du Nil vivant, poids moyen 400-500g/pièce. Disponible en lots de 100kg minimum. Élevage en bassins en terre, alimentation granulés certifiés. Taux de survie garanti 95%.',
                        'description_en' => 'Live Nile tilapia, average weight 400-500g each. Available in minimum 100kg batches. Earthen pond farming, certified pellet feed. 95% survival rate guaranteed.',
                        'unit_fr' => 'kg (lot min. 100 kg)', 'unit_en' => 'kg (min. lot 100 kg)',
                        'min_qty' => 100, 'lead_time' => 2, 'exported' => false, 'featured' => true,
                        'attributes' => [
                            ['key_fr' => 'Espèce', 'key_en' => 'Species', 'value_fr' => 'Oreochromis niloticus', 'value_en' => 'Oreochromis niloticus'],
                            ['key_fr' => 'Poids unitaire', 'key_en' => 'Unit weight', 'value_fr' => '400–500 g', 'value_en' => '400–500 g'],
                            ['key_fr' => 'Méthode d\'élevage', 'key_en' => 'Farming method', 'value_fr' => 'Bassin en terre', 'value_en' => 'Earthen pond'],
                            ['key_fr' => 'Alimentation', 'key_en' => 'Feed', 'value_fr' => 'Granulés certifiés 32% protéines', 'value_en' => 'Certified pellets 32% protein'],
                        ],
                    ],
                    [
                        'category' => 'silure-poisson-chat',
                        'name_fr' => 'Silure Africain Vivant (50 kg)',
                        'name_en' => 'African Catfish Live (50 kg)',
                        'description_fr' => 'Silure africain (Clarias gariepinus) vivant, poids moyen 600-800g/pièce. Lot minimum 50kg. Chair ferme, idéal pour la cuisson en sauce ou fumage.',
                        'description_en' => 'Live African catfish (Clarias gariepinus), average weight 600-800g each. Minimum 50kg batch. Firm flesh, ideal for sauce cooking or smoking.',
                        'unit_fr' => 'kg (lot min. 50 kg)', 'unit_en' => 'kg (min. lot 50 kg)',
                        'min_qty' => 50, 'lead_time' => 2, 'exported' => false,
                        'attributes' => [
                            ['key_fr' => 'Espèce', 'key_en' => 'Species', 'value_fr' => 'Clarias gariepinus', 'value_en' => 'Clarias gariepinus'],
                            ['key_fr' => 'Poids unitaire', 'key_en' => 'Unit weight', 'value_fr' => '600–800 g', 'value_en' => '600–800 g'],
                        ],
                    ],
                    [
                        'category' => 'poisson-fume',
                        'name_fr' => 'Tilapia Fumé — Caisse 10 kg',
                        'name_en' => 'Smoked Tilapia — 10 kg Box',
                        'description_fr' => 'Tilapia entier fumé à bois dur pendant 8 heures. Conservation 3 semaines sans réfrigération. Caisse de 10 kg emballée sous vide.',
                        'description_en' => 'Whole tilapia smoked with hardwood for 8 hours. Storage 3 weeks without refrigeration. 10 kg box, vacuum sealed.',
                        'unit_fr' => 'caisse 10 kg', 'unit_en' => '10 kg box',
                        'min_qty' => 3, 'lead_time' => 3, 'exported' => true, 'featured' => true,
                        'attributes' => [
                            ['key_fr' => 'Poids net', 'key_en' => 'Net weight', 'value_fr' => '10 kg', 'value_en' => '10 kg'],
                            ['key_fr' => 'Conservation', 'key_en' => 'Shelf life', 'value_fr' => '3 semaines sans frigo', 'value_en' => '3 weeks without refrigeration'],
                            ['key_fr' => 'Conditionnement', 'key_en' => 'Packaging', 'value_fr' => 'Sous vide en caisse carton', 'value_en' => 'Vacuum sealed in cardboard box'],
                        ],
                    ],
                ],
            ],
            [
                'name_fr' => 'Aquaculture Kribi Shrimps',
                'name_en' => 'Kribi Shrimps Aquaculture',
                'tagline_fr' => 'Crevettes de mer et d\'eau douce, directement du golfe de Guinée',
                'tagline_en' => 'Sea and freshwater shrimp, direct from the Gulf of Guinea',
                'description_fr' => 'Producteur de crevettes sur les côtes de Kribi depuis 2012. Crevettes géantes tigrées (Penaeus monodon) en cage sous-marine, et crevettes d\'eau douce (Macrobrachium rosenbergii) en étang. Conditionnement frais ou congelé, expédition sous 48h.',
                'description_en' => 'Shrimp producer on the Kribi coast since 2012. Giant tiger shrimp (Penaeus monodon) in underwater cages, and freshwater shrimp (Macrobrachium rosenbergii) in ponds. Fresh or frozen packaging, shipping within 48h.',
                'email' => 'kribishrims@example.cm',
                'phone' => '+237 682 67 89 01',
                'owner_name' => 'Edith Biyong',
                'region_code' => 'SU',
                'city' => 'Kribi',
                'address' => 'Zone Portuaire, Kribi, Sud',
                'year' => 2012,
                'employees' => 30,
                'industry' => 'aquaculture',
                'ownership' => 'private',
                'tier' => 'verified',
                'featured' => true,
                'tags' => ['crevettes', 'kribi', 'fruits-de-mer', 'siac', 'export'],
                'products' => [
                    [
                        'category' => 'crevettes',
                        'name_fr' => 'Crevettes Géantes Tigrées Fraîches (5 kg)',
                        'name_en' => 'Fresh Giant Tiger Shrimp (5 kg)',
                        'description_fr' => 'Crevettes géantes tigrées (Penaeus monodon), pêchées en cage sous-marine et conditionnées dans les 4 heures. Calibre 16/20 (pièces par kg). Emballage glacière isotherme 5 kg.',
                        'description_en' => 'Giant tiger shrimp (Penaeus monodon), harvested from underwater cages and processed within 4 hours. Size 16/20 (pieces per kg). Insulated cooler packaging 5 kg.',
                        'unit_fr' => 'emballage 5 kg', 'unit_en' => '5 kg pack',
                        'min_qty' => 2, 'lead_time' => 2, 'exported' => true, 'featured' => true,
                        'attributes' => [
                            ['key_fr' => 'Espèce', 'key_en' => 'Species', 'value_fr' => 'Penaeus monodon', 'value_en' => 'Penaeus monodon'],
                            ['key_fr' => 'Calibre', 'key_en' => 'Size', 'value_fr' => '16/20 pièces/kg', 'value_en' => '16/20 pieces/kg'],
                            ['key_fr' => 'Conditionnement', 'key_en' => 'Packaging', 'value_fr' => 'Frais sur glace', 'value_en' => 'Fresh on ice'],
                        ],
                    ],
                    [
                        'category' => 'crevettes',
                        'name_fr' => 'Crevettes Eau Douce Congelées IQF (10 kg)',
                        'name_en' => 'Frozen Freshwater Shrimp IQF (10 kg)',
                        'description_fr' => 'Crevettes d\'eau douce (Macrobrachium rosenbergii) congelées individuellement (IQF). Calibre 8/12. Carton 10 kg. Température de conservation -18°C.',
                        'description_en' => 'Freshwater shrimp (Macrobrachium rosenbergii) individually quick frozen (IQF). Size 8/12. Carton 10 kg. Storage temperature -18°C.',
                        'unit_fr' => 'carton 10 kg', 'unit_en' => '10 kg carton',
                        'min_qty' => 5, 'lead_time' => 3, 'exported' => true,
                    ],
                ],
            ],
            // ── AGRICULTURE ─────────────────────────────────────────────────────
            [
                'name_fr' => 'Plantation Cacao Fin Mungo',
                'name_en' => 'Mungo Fine Cocoa Estate',
                'tagline_fr' => 'Cacao Trinitario d\'exception, origine traçable Mungo',
                'tagline_en' => 'Exceptional Trinitario cocoa, traceable Mungo origin',
                'description_fr' => 'Plantation familiale de 45 hectares dans la vallée du Mungo. Variétés Trinitario et Nacional, fermentation contrôlée 7 jours, séchage solaire 14 jours. Fèves de cacao fin certifiées par le CICC. Export direct en sacs de jute 60 kg.',
                'description_en' => '45-hectare family plantation in the Mungo valley. Trinitario and Nacional varieties, 7-day controlled fermentation, 14-day solar drying. Fine cocoa beans certified by CICC. Direct export in 60kg jute bags.',
                'email' => 'cacaomungo@example.cm',
                'phone' => '+237 677 89 01 23',
                'owner_name' => 'François Mbotto',
                'region_code' => 'LT',
                'city' => 'Nkongsamba',
                'address' => 'Route Mbanga, Vallée du Mungo, Littoral',
                'year' => 1978,
                'employees' => 30,
                'industry' => 'agriculture',
                'ownership' => 'private',
                'tier' => 'certified',
                'featured' => true,
                'tags' => ['cacao', 'mungo', 'trinitario', 'export', 'fermente'],
                'products' => [
                    [
                        'category' => 'cacao',
                        'name_fr' => 'Fèves de Cacao Fin Fermenté — Sac 60 kg',
                        'name_en' => 'Fine Fermented Cocoa Beans — 60 kg Bag',
                        'description_fr' => 'Fèves de cacao Trinitario fermentées et séchées au soleil. Humidité <7%. Teneur en beurre >52%. Certifiées cacao fin CICC. Sac en jute 60 kg. Origine tracée par plantation et lot de fermentation.',
                        'description_en' => 'Fermented and sun-dried Trinitario cocoa beans. Humidity <7%. Butter content >52%. Fine cocoa certified by CICC. 60 kg jute bag. Origin traced by plantation and fermentation batch.',
                        'unit_fr' => 'sac 60 kg', 'unit_en' => '60 kg bag',
                        'min_qty' => 5, 'lead_time' => 7, 'exported' => true, 'featured' => true,
                        'attributes' => [
                            ['key_fr' => 'Variété', 'key_en' => 'Variety', 'value_fr' => 'Trinitario', 'value_en' => 'Trinitario'],
                            ['key_fr' => 'Fermentation', 'key_en' => 'Fermentation', 'value_fr' => '7 jours contrôlée', 'value_en' => '7-day controlled'],
                            ['key_fr' => 'Humidité', 'key_en' => 'Moisture', 'value_fr' => '< 7 %', 'value_en' => '< 7 %'],
                            ['key_fr' => 'Certification', 'key_en' => 'Certification', 'value_fr' => 'CICC Cacao Fin', 'value_en' => 'CICC Fine Cocoa'],
                        ],
                    ],
                ],
            ],
            // ── AGROALIMENTAIRE ─────────────────────────────────────────────────
            [
                'name_fr' => 'Épices du Cameroun — ÉPICAM',
                'name_en' => 'Cameroon Spices — EPICAM',
                'tagline_fr' => 'Poivre de Penja IGP et épices camerounaises d\'exception',
                'tagline_en' => 'Penja Pepper PGI and exceptional Cameroonian spices',
                'description_fr' => 'ÉPICAM commercialise le Poivre de Penja, seule épice africaine à disposer d\'une Indication Géographique Protégée (IGP). Nous proposons également du poivre de Bafia, du poivre blanc de Penja et des mélanges d\'épices camerounaises. Conditionnement artisanal ou en vrac pour professionnels.',
                'description_en' => 'EPICAM markets Penja Pepper, the only African spice with a Protected Geographical Indication (PGI). We also offer Bafia pepper, white Penja pepper and Cameroonian spice blends. Artisan packaging or bulk for professionals.',
                'email' => 'epicam@example.cm',
                'phone' => '+237 654 01 23 45',
                'owner_name' => 'Sophie Atangana',
                'region_code' => 'LT',
                'city' => 'Nkongsamba',
                'address' => 'Zone Industrielle, Nkongsamba, Littoral',
                'year' => 2016,
                'employees' => 15,
                'industry' => 'agroalimentaire',
                'ownership' => 'private',
                'tier' => 'certified',
                'featured' => true,
                'tags' => ['poivre-penja', 'igp', 'epices', 'export', 'cameroun'],
                'products' => [
                    [
                        'category' => 'poivre-penja',
                        'name_fr' => 'Poivre Noir de Penja IGP — Sac 5 kg',
                        'name_en' => 'Penja Black Pepper PGI — 5 kg Bag',
                        'description_fr' => 'Poivre noir de Penja, Indication Géographique Protégée. Récolte 2024. Arôme puissant, notes florales et fruitées distinctives. Conditionnement vrac 5 kg pour restaurateurs et épiceries fines.',
                        'description_en' => 'Penja black pepper, Protected Geographical Indication. 2024 harvest. Powerful aroma, distinctive floral and fruity notes. Bulk 5 kg packaging for restaurateurs and fine food shops.',
                        'unit_fr' => 'sac 5 kg', 'unit_en' => '5 kg bag',
                        'min_qty' => 2, 'lead_time' => 5, 'exported' => true, 'featured' => true,
                        'attributes' => [
                            ['key_fr' => 'Certification', 'key_en' => 'Certification', 'value_fr' => 'IGP Poivre de Penja', 'value_en' => 'PGI Penja Pepper'],
                            ['key_fr' => 'Millésime', 'key_en' => 'Harvest', 'value_fr' => '2024', 'value_en' => '2024'],
                            ['key_fr' => 'Calibre', 'key_en' => 'Grade', 'value_fr' => 'ASTA Grade A', 'value_en' => 'ASTA Grade A'],
                        ],
                    ],
                    [
                        'category' => 'poivre-penja',
                        'name_fr' => 'Poivre Blanc de Penja IGP — Bocal 100g',
                        'name_en' => 'Penja White Pepper PGI — 100g Jar',
                        'description_fr' => 'Poivre blanc de Penja IGP en bocal artisanal 100g. Doux et raffiné, idéal pour les sauces blanches et poissons. Étiquette bilingue FR/EN, parfait pour export et cadeaux gastronomiques.',
                        'description_en' => 'Penja white pepper PGI in artisan 100g jar. Mild and refined, ideal for white sauces and fish. Bilingual FR/EN label, perfect for export and gastronomic gifts.',
                        'unit_fr' => 'bocal 100 g', 'unit_en' => '100 g jar',
                        'min_qty' => 12, 'lead_time' => 5, 'exported' => true, 'featured' => true,
                    ],
                    [
                        'category' => 'piments',
                        'name_fr' => 'Sauce Piment Rouge Camerounaise — 250 ml',
                        'name_en' => 'Cameroonian Red Pepper Sauce — 250 ml',
                        'description_fr' => 'Sauce piment artisanale préparée avec des piments rouges camerounais, tomates et épices. Sans conservateur artificiel. Bocal verre 250ml.',
                        'description_en' => 'Artisanal chili sauce made with Cameroonian red peppers, tomatoes and spices. No artificial preservatives. 250ml glass jar.',
                        'unit_fr' => 'bocal 250 ml', 'unit_en' => '250 ml jar',
                        'min_qty' => 6, 'lead_time' => 7, 'exported' => true,
                    ],
                ],
            ],
            // ── TEXTILE ─────────────────────────────────────────────────────────
            [
                'name_fr' => 'Mode Africaine Sandrine',
                'name_en' => 'Sandrine African Fashion',
                'tagline_fr' => 'Prêt-à-porter africain contemporain, fait à Douala',
                'tagline_en' => 'Contemporary African ready-to-wear, made in Douala',
                'description_fr' => 'Créatrice de mode basée à Douala depuis 2018. Collections femme et homme en tissus wax, kente et ndop. Style contemporain qui réinterprète les codes vestimentaires camerounais pour la diaspora et les marchés internationaux. Commandes sur mesure acceptées.',
                'description_en' => 'Fashion designer based in Douala since 2018. Women\'s and men\'s collections in wax, kente and ndop fabrics. Contemporary style that reinterprets Cameroonian dress codes for the diaspora and international markets. Custom orders accepted.',
                'email' => 'modeafricainesandrine@example.cm',
                'phone' => '+237 681 12 34 56',
                'owner_name' => 'Sandrine Mbemba',
                'region_code' => 'LT',
                'city' => 'Douala',
                'address' => 'Quartier Akwa, Rue Castelnau, Douala',
                'year' => 2018,
                'employees' => 7,
                'industry' => 'textile-mode',
                'tier' => 'basic',
                'featured' => false,
                'tags' => ['mode', 'wax', 'kente', 'douala', 'femme', 'pret-a-porter'],
                'products' => [
                    [
                        'category' => 'vetements-femme',
                        'name_fr' => 'Robe Wax Peplum — Collection Harmattan',
                        'name_en' => 'Wax Peplum Dress — Harmattan Collection',
                        'description_fr' => 'Robe peplum en wax premium 100% coton. Coupe moderne, longueur mi-mollet. Tailles S à 3XL. Coloris : rouge/or, bleu/noir, vert/blanc. Délai de confection 10 jours.',
                        'description_en' => 'Peplum dress in premium 100% cotton wax. Modern cut, mid-calf length. Sizes S to 3XL. Colors: red/gold, blue/black, green/white. Tailoring lead time 10 days.',
                        'unit_fr' => 'pièce', 'unit_en' => 'piece',
                        'min_qty' => 1, 'lead_time' => 10, 'exported' => true, 'featured' => true,
                        'attributes' => [
                            ['key_fr' => 'Tissu', 'key_en' => 'Fabric', 'value_fr' => 'Wax 100% coton premium', 'value_en' => '100% premium cotton wax'],
                            ['key_fr' => 'Tailles disponibles', 'key_en' => 'Available sizes', 'value_fr' => 'S, M, L, XL, 2XL, 3XL', 'value_en' => 'S, M, L, XL, 2XL, 3XL'],
                        ],
                    ],
                    [
                        'category' => 'accessoires-mode',
                        'name_fr' => 'Sac Tote Wax & Cuir',
                        'name_en' => 'Wax & Leather Tote Bag',
                        'description_fr' => 'Sac tote en wax renforcé anses cuir vachette. Dimensions 38×40cm. Doublure coton. Poche intérieure zippée. Disponible en 4 coloris.',
                        'description_en' => 'Wax tote bag with reinforced cowhide handles. Dimensions 38×40cm. Cotton lining. Interior zippered pocket. Available in 4 colors.',
                        'unit_fr' => 'pièce', 'unit_en' => 'piece',
                        'min_qty' => 3, 'lead_time' => 7, 'exported' => true,
                    ],
                ],
            ],
        ];
    }
}
