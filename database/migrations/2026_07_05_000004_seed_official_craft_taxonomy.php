<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// Replace the ad-hoc 13-category set with the OFFICIAL Cameroon artisanal-trades
// taxonomy (Liste des métiers artisanaux): Secteur (L1) -> Filière (L2) ->
// Corps de métier (L3) -> Métier (L4). Existing businesses/products are re-tagged
// to the closest official trade so nothing is left uncategorised.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('industries', 'level')) {
            Schema::table('industries', function (Blueprint $table) {
                $table->unsignedTinyInteger('level')->default(1)->after('parent_id');
            });
        }

        // Detach the businesses FK and wipe any existing taxonomy (idempotent).
        // NOTE: products.category_id points at product_categories (product TYPE),
        // NOT at industries — it is left untouched here.
        DB::table('businesses')->update(['industry_id' => null]);
        DB::table('industries')->delete();

        // Seed the official taxonomy. sort_order is PER PARENT (small values,
        // never overflows the column).
        $taxonomy = $this->taxonomy();
        $now = now();
        $used = [];
        $nameToId = [];   // exact name_fr (first occurrence) -> id, for re-tagging

        $uslug = function ($name) use (&$used) {
            $base = mb_substr(Str::slug($name) ?: 'x', 0, 60);
            $slug = $base; $i = 2;
            while (isset($used[$slug])) { $slug = mb_substr($base, 0, 57) . '-' . $i; $i++; }
            $used[$slug] = true;
            return $slug;
        };
        $insert = function ($nameFr, $nameEn, $parentId, $level, $sort) use (&$nameToId, $uslug, $now) {
            $id = DB::table('industries')->insertGetId([
                'parent_id' => $parentId, 'level' => $level,
                'slug' => $uslug($nameFr), 'name_fr' => $nameFr, 'name_en' => $nameEn ?: $nameFr,
                'sort_order' => $sort, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
            if (! isset($nameToId[$nameFr])) $nameToId[$nameFr] = $id;
            return $id;
        };

        $si = 0;
        foreach ($taxonomy as $sector) {
            $sectorId = $insert($sector['fr'], $sector['en'], null, 1, $si++);
            $fi = 0;
            foreach ($sector['filieres'] as $filName => $corpsList) {
                $filId = $insert($filName, $filName, $sectorId, 2, $fi++);
                $ci = 0;
                foreach ($corpsList as $corpsName => $metiers) {
                    $corpsId = $insert($corpsName, $corpsName, $filId, 3, $ci++);
                    $mi = 0;
                    foreach ($metiers as $metier) {
                        $insert($metier, $metier, $corpsId, 4, $mi++);
                    }
                }
            }
        }

        // Re-tag existing businesses to the closest official métier by name keyword
        // (specific keywords first, generic last). Products inherit their business's trade.
        $kw = [
            'poterie' => 'Céramiste (Potier)', 'céramique' => 'Céramiste (Potier)', 'ceramique' => 'Céramiste (Potier)',
            'maroquin' => 'Maroquinier', 'cuir' => 'Maroquinier',
            'perles' => 'Bijoutier-joaillier', 'bijou' => 'Bijoutier-joaillier', 'bronze' => 'Bijoutier-joaillier',
            'piscicult' => 'Sécheur/fumeur de poisson, de crustacées, de mollusques',
            'aquacult' => 'Sécheur/fumeur de poisson, de crustacées, de mollusques',
            'poisson' => 'Sécheur/fumeur de poisson, de crustacées, de mollusques',
            'shrimp' => 'Sécheur/fumeur de poisson, de crustacées, de mollusques',
            'cacao' => 'Transformateur de cacao',
            'épice' => 'Fabricant de condiments', 'epice' => 'Fabricant de condiments', 'épicam' => 'Fabricant de condiments',
            'tissage' => 'Tisserand', 'tissu' => 'Tisserand', 'racines' => 'Tisserand',
            'tressage' => 'Vannier/Spartier (Fabricant d\'articles en paille et d\'autres sparteries)',
            'vannerie' => 'Vannier/Spartier (Fabricant d\'articles en paille et d\'autres sparteries)',
            'musique' => 'Fabricant d\'instruments de musique', 'rythme' => 'Fabricant d\'instruments de musique', 'instrument' => 'Fabricant d\'instruments de musique',
            'nature' => 'Fabricant de cosmétiques et assimilés', 'bienfait' => 'Fabricant de cosmétiques et assimilés', 'cosmétique' => 'Fabricant de cosmétiques et assimilés', 'bio' => 'Fabricant de cosmétiques et assimilés',
            'menuis' => 'Ebéniste', 'wood' => 'Ebéniste', 'bois' => 'Ebéniste',
            'sculpt' => 'Sculpteur/décorateur sur tous matériaux',
            'mode' => 'Tailleur/couturier', 'couture' => 'Tailleur/couturier', 'boutique' => 'Tailleur/couturier',
            'art' => 'Sculpteur/décorateur sur tous matériaux',
        ];
        $default = $nameToId['Sculpteur/décorateur sur tous matériaux'] ?? null;
        $tradeFor = function ($name) use ($kw, $nameToId, $default) {
            $n = mb_strtolower($name);
            foreach ($kw as $needle => $trade) {
                if (mb_strpos($n, $needle) !== false && isset($nameToId[$trade])) return $nameToId[$trade];
            }
            return $default;
        };

        foreach (DB::table('businesses')->whereNull('deleted_at')->get(['id', 'name_fr']) as $b) {
            $tid = $tradeFor($b->name_fr);
            if ($tid) DB::table('businesses')->where('id', $b->id)->update(['industry_id' => $tid]);
        }
    }

    public function down(): void
    {
        // Not reversible to the ad-hoc taxonomy (data replaced). Level column dropped.
        if (Schema::hasColumn('industries', 'level')) {
            Schema::table('industries', function (Blueprint $table) { $table->dropColumn('level'); });
        }
    }

    private function taxonomy(): array
    {
        $other = fn ($what) => "Autres métiers du corps de {$what} non classés ailleurs";
        return [
            [
                'fr' => 'Artisanat d\'Art', 'en' => 'Arts and Crafts',
                'filieres' => [
                    'Audiovisuel et multimédia' => [
                        'Imagerie' => ['Caricaturiste', 'Dessinateur', 'Grapheur', 'Maquilleuse', 'Maquilleuse artistique', $other('l\'Imagerie')],
                    ],
                    'Art et décoration' => [
                        'Fabrication d\'articles en joaillerie, bijouterie et articles similaires, accessoires d\'ornement' => ['Bijoutier-joaillier', 'Designer', 'Ferronniers d\'art', 'Graveur', 'Orfèvre', $other('l\'Art et décoration')],
                        'Fabrication d\'art traditionnel et métiers de la création de pièces uniques ou de petites séries à tendance contemporaine' => ['Armurier (fabricant de fusils traditionnels)', 'Fabricant d\'instruments de musique', 'Fabricant de cachets', 'Fabricant de flèches et accessoires', 'Fabricant de jeux et jouets', 'Fabricant de pots de fleurs', 'Luthier', 'Sculpteur/décorateur sur tous matériaux', 'Teinturier', $other('la Fabrication d\'art traditionnel')],
                        'Fabrication et restauration du patrimoine' => ['Céramiste (Potier)', 'Fabricant de foyers améliorés', 'Miroitier', 'Restaurateur de bas-reliefs', 'Restaurateur d\'objets anciens', 'Restaurateur d\'œuvres d\'art', 'Verrier', 'Vitrailliste (maître verrier)', $other('la Fabrication et restauration du patrimoine')],
                        'Décoration' => ['Calligraphe', 'Décorateur de théâtre (décorateur-scénographe, accessoiriste de cinéma)', 'Décorateur des véhicules et de lieux', 'Décorateur sur porcelaine', 'Décorateur sur verre', 'Designer floral', 'Dessinateur-bâtiments', 'Doreur', 'Fabricant d\'aquarium', 'Fleuriste', 'Fresquiste', 'Jardinier/horticulteur', 'Marqueteur', 'Marqueur', 'Paysagiste /Créateur d\'espaces verts', 'Peintre-décorateur (artiste-peintre) /portraitiste', 'Sérigraphe', 'Staffeur-stucateur/ornemaniste', $other('la Décoration')],
                    ],
                ],
            ],
            [
                'fr' => 'Artisanat de Production', 'en' => 'Production Handicrafts',
                'filieres' => [
                    'Agro-alimentaire, alimentation, restauration' => [
                        'Transformation et conservation de fruits, légumes, feuilles et noix' => ['Fabricant de condiments', 'Fabricant de confitures (confiturier)', 'Fabricant de conserves de fruits ou de légumes', 'Sécheur de fruits ou de légumes', 'Transformateur de noix en amuse-bouche', $other('l\'Agro-alimentaire')],
                        'Transformation des grains, des tubercules et produits amylacés' => ['Exploitant d\'égraineuses (café, arachide, maïs, karité, etc.)', 'Fabricant de cossettes de manioc ou d\'igname', 'Fabricant de couscous traditionnel', 'Fabricant de gari/tapioca', 'Meunier', 'Transformateur de cacao', $other('la Transformation des grains')],
                        'Fabrication d\'huiles, graisses végétales et animales' => ['Producteur d\'huile (d\'arachide, de karité, de sésame, de Nimier, etc.)'],
                        'Abattage, transformation et conservation de viande et préparation de produits à base de viande' => ['Boucher', 'Charcutier', 'Nettoyeur d\'animaux (porcs, moutons, chèvres, poulets, etc.)', 'Sécheur et fumeur de viande et volaille', 'Tripier', $other('l\'Abattage et conservation de viande')],
                        'Transformation et conservation de produits à base de poissons et de produits de la pêche' => ['Ecailleur', 'Sécheur/fumeur de poisson, de crustacées, de mollusques', $other('la Transformation de produits à base de poissons')],
                        'Transformation de produits laitiers et de glaces' => ['Fabricant de crèmes et glaces à sucer (Producteur de glace ou glacier)', 'Fabricant de fromage', 'Fabricant de yaourt et des laits fermentés', $other('la Transformation de produits laitiers')],
                        'Fabrication de boissons' => ['Fabricant de boissons alcoolisées', 'Fabricant de boissons non alcoolisées', 'Fabricant de crème de cacao alcoolisée', $other('la Fabrication de boissons')],
                        'Boulangerie-pâtisserie et pâtes alimentaires' => ['Boulanger', 'Chocolatier', 'Confiseur', 'Fabricant de friandises', 'Fabricant de pâtes alimentaires', 'Pâtissier', $other('la Boulangerie-pâtisserie')],
                        'Restauration et fabrication d\'autres produits alimentaires' => ['Apiculteur', 'Cafetier', 'Cantinier', 'Cuisinier', 'Fabricant de bouillie', 'Fabricant de galettes, beignets et d\'autres produits assimilés', 'Restaurateur (traiteur, café tourne dos, vendeur de beignets haricot)', 'Rôtisseur (viande, poisson, plantain, safou, ...)', 'Serveur de restaurant/café', 'Tenancier de bars, buvettes', $other('la Restauration et fabrication d\'autres produits alimentaires')],
                        'Fabrication d\'aliments pour animaux et d\'engrais organiques' => ['Emboucheur bovin', 'Fabricant de provendes et autres aliments', 'Recycleur artisanal de déchets solides', $other('la Fabrication d\'aliments pour animaux')],
                    ],
                    'Mines et carrières, construction et bâtiment' => [
                        'Extraction de minerais non ferreux' => ['Orpailleur traditionnel', $other('l\'Extraction de minerais non ferreux')],
                        'Extraction de produits de carrières et autres' => ['Extracteur des minéraux (argile, granit, gravier, grès, pierre ornementale, sable)', $other('l\'Extraction de produits de carrières')],
                        'Travail de la pierre' => ['Concasseur de pierre', 'Fabricant de pierre à moudre', 'Marbrier-carreleur', 'Tailleur de pierre', $other('le Travail de la pierre')],
                        'Fabrication de matériaux de construction en béton, en ciment, en plâtre et en terre cuite' => ['Briquetier (fabricant de briques en terre)', 'Fabricant de bornes, claustras, buses, voûtes, dômes et autres accessoires en ciment', 'Fabricant de parpaings et hourdis', 'Fabricant de pavés', 'Fabricant de tuiles (Tuilier)', 'Fabricant d\'objets en plâtre', $other('la Fabrication de matériaux de construction')],
                    ],
                    'Construction métallique, aluminium, plastique, mécanique, électromécanique, électronique, électrotechnique, électricité et petites activités de transport' => [
                        'Fabrication de machines et équipements d\'usage général, spécifique et autres outils' => ['Affûteur', 'Chaudronnier', 'Coutelier', 'Fabricant et monteur d\'équipements de séchage de fruits et légumes', 'Fabricant et monteur d\'équipements de transformation des produits agroalimentaires', 'Fabricant de couveuses', 'Fabricant et monteur de matériels et équipements agricoles et forestiers', 'Fabricant d\'objets en plastique', 'Fondeur (Bronzier)', 'Forgeron', 'Modéliste/maquettiste', $other('la Fabrication de machines et équipements')],
                        'Construction métallique et ouvrages en aciers, en métaux précieux et d\'autres métaux non ferreux' => ['Charpentier et monteur métallique', 'Ferblantier', 'Maréchal ferrant', 'Menuisier en aluminium', 'Menuisier métallique (Métallier)', 'Soudeur', 'Tôlier', $other('la Construction métallique et ouvrages en aciers')],
                    ],
                    'Bois et assimilés, mobilier et ameublement' => [
                        'Travail du bois (meubles et assimilés)' => ['Abatteur-scieur', 'Brossier', 'Bûcheron', 'Charbonnier', 'Constructeur de pirogues et autres embarcations', 'Ebéniste', 'Fabricant de manches ou support d\'outils', 'Fabricant de mortiers et accessoires', 'Fabricant de tamis', 'Machiniste en menuiserie bois', 'Menuisier-agenceur', 'Tonnelier', 'Tourneur en bois', $other('le Travail du bois')],
                        'Travail sur végétaux' => ['Cordier', 'Nattier', 'Vannier/Spartier (Fabricant d\'articles en paille et d\'autres sparteries)', 'Fabricant d\'objets en calebasse', 'Fabricant d\'objets en bambou', 'Fabricant de tannin', $other('le Travail sur végétaux')],
                    ],
                    'Textile, peaux et cuirs' => [
                        'Fabrication de fibres textiles, filature et tissage' => ['Fabricant de filets de pêche', 'Fileur de coton', 'Tisserand', $other('la Fabrication de fibres textiles')],
                        'Fabrication de vêtements et d\'autres textiles' => ['Bonnetier', 'Brodeur', 'Chapelier', 'Costumier', 'Fabricant d\'articles en textile', 'Fabricant d\'objets et accessoires en tissu', 'Matelassier', 'Modéliste (de mode) /Styliste', 'Retoucheur', 'Tailleur/couturier', 'Tricoteur', $other('la Fabrication de vêtements')],
                        'Travaux sur cuir, peaux, fourrure, os d\'animaux et cornes' => ['Bottier', 'Cireur/Savetier/Arrangeur de chaussures', 'Cordonnier', 'Fourreur', 'Maroquinier', 'Sellier harnacheur', 'Tanneur', 'Tapissier (tapissier d\'ameublement ou tapissier décorateur)', 'Taxidermiste', $other('les Travaux sur cuir, peaux, fourrure, os et cornes')],
                    ],
                    'Audiovisuel et communication' => [
                        'Imagerie (encadrement)' => ['Encadreur', $other('l\'Imagerie')],
                    ],
                    'Santé, hygiène et soins corporels' => [
                        'Production des médicaments à base des plantes' => ['Fabricant de médicaments à base de plantes', 'Producteur des médicaments à base des plantes', $other('la Production des médicaments à base des plantes')],
                        'Fabrication de savons, de produits d\'entretien et produits chimiques et cosmétiques' => ['Fabricant d\'alcool à base de fruits', 'Fabricant de bougies', 'Fabricant de cosmétiques et assimilés', 'Fabricant de parfum (créateur de parfum, parfumeur)', 'Fabricant de savons', 'Fabricant d\'huiles essentielles', $other('la Fabrication de savons et cosmétiques')],
                        'Fabrication de prothèses et matériel orthopédique' => ['Orthoprothésiste (Fabricant de matériels orthopédiques)', 'Podo-orthésiste', 'Prothésiste-dentaire (Fabricant de prothèses dentaires)', $other('la Fabrication de prothèses et matériel orthopédique')],
                    ],
                ],
            ],
            [
                'fr' => 'Artisanat de Service', 'en' => 'Service Handicrafts',
                'filieres' => [
                    'Agro-alimentaire, alimentation, restauration (service)' => [
                        'Abattage, conservation de viande et poisson et préparation de produits à base de viande et poisson' => ['Poissonnier', 'Préparateurs en produits de mer', 'Préparateur-vendeur de produits à base de viande, de volailles', $other('l\'Abattage et préparation de viande et poisson')],
                    ],
                    'Mines et carrières, construction et bâtiment (service)' => [
                        'Mines artisanales et semi-mécanisées' => ['Prospecteur minier', 'Conducteur de la pelle', 'Collecteur minier', $other('Mines artisanales et semi-mécanisées')],
                        'Forage de puits d\'eau et autres activités de forage' => ['Canalisateur', 'Foreur/installateur de puits d\'eau dotés de pompes immergées', 'Fossoyeur', 'Puisatier (creuseur de puits)', $other('Forage de puits d\'eau')],
                        'Construction et autres travaux de construction spécialisés' => ['Aménagiste de chaussées', 'Conducteur d\'engins', 'Constructeur de greniers', 'Constructeur d\'ouvrages d\'art', 'Constructeur de petits châteaux d\'eau', 'Constructeur en bois', $other('la Construction et travaux spécialisés')],
                        'Travaux de finition et autres travaux spécialisés' => ['Carreleur-mosaïste', 'Charpentier bois', 'Coffreur-boiseur', 'Couvreur', 'Crépisseur à la tyrolienne', 'Electricien bâtiment (Electricien domestique)', 'Enduiseur (Façadier)', 'Etanchéiste (étancheur)', 'Ferrailleur', 'Maçon', 'Moquettiste-solier', 'Parqueteur', 'Peintre-bâtiments', 'Plâtrier', 'Plombier (Installateur sanitaire)', 'Poseur d\'enduits', 'Poseur de gouttières', 'Poseur de granitos', 'Poseur de pierres ornementales', 'Sécheur (bois, cuir, ...)', 'Vitrier', $other('les Travaux de finition')],
                    ],
                    'Maintenance mécanique, électromécanique, électronique, électricité et petites activités de transport' => [
                        'Réparation et mécanique d\'automobiles, de motocycles et de cycles, de matériels de transport ferroviaire, roulant, naval et fluvial' => ['Ajusteur monteur', 'Archetier', 'Carrossier', 'Chargeur de batterie/Vulcanisateur', 'Electricien automobile', 'Fraiseur (tourneur-fraiseur)', 'Graisseur de véhicules', 'Mécanicien automobile', 'Mécanicien cycles et motocycles (motos)', 'Mécanicien de matériel de parc et de jardin', 'Mécanicien de motos pompe', 'Mécanicien d\'engins agricoles', 'Mécanicien d\'engins des BTP', 'Peintre en carrosserie', 'Réparateur de radiateurs', 'Vidangeur/graisseur', $other('la Réparation et mécanique automobile')],
                        'Petites activités de transport terrestre, fluvial et transport par conduites' => ['Aide-chauffeur (Motor-boy)', 'Bagagiste', 'Chargeur de gare routière', 'Chargeur des produits extraits dans les carrières', 'Conducteur de charrettes/tricycles/quadricycle', 'Conducteur de moto-taxi (moto-taximan, benskineur)', 'Conducteur de pirogues et embarcations', 'Conducteur de pousse-pousses/portes-tout, brouettes', 'Déménageur', 'Eboueur', 'Facteur', 'Livreur', 'Moniteur de conduite automobile', 'Monteur en pneumatique', 'Porteur de bagages (Docker/manutentionnaire)', 'Taximan (Chauffeur de taxi)', $other('les Petites activités de transport')],
                        'Installation, maintenance, entretien et réparation d\'ordinateurs, de biens personnels et d\'équipements domestiques' => ['Dépanneur radio, télévision et chaîne Hi-Fi, magnétoscopes, lecteurs de DVD', 'Electronicien', 'Electrotechnicien', 'Garagiste', 'Gardien automobiles', 'Horloger', 'Installateur en électroménager', 'Installateur/maintenancier de matériels informatiques', 'Installateur/maintenancier de panneaux solaires', 'Installateur thermique (Monteur en installations thermiques)', 'Laveur autos, motos, moquettes', 'Laveur de vitres', 'Poseur/réparateur de serrures électroniques', 'Réparateur de luth (Réparateur d\'instruments de musique)', 'Réparateur de machines à broder', 'Réparateur de machines à coudre et à surfiler', 'Réparateur de téléphones portables', 'Réparateur d\'ordinateurs et d\'équipements périphériques', $other('l\'Installation et réparation d\'équipements domestiques')],
                        'Electricité, froid' => ['Rebobineur', 'Spécialiste de froid et climatisation (Frigoriste)', $other('l\'Electricité et froid')],
                    ],
                    'Audiovisuel et communication (service)' => [
                        'Imagerie (numérique)' => ['Cabliste', 'Influenceur web', 'Pilote de drone civil pour production audiovisuelle', $other('l\'Imagerie')],
                        'Installation, maintenance, entretien et réparation de matériels optiques, photographiques et d\'images' => ['Cameraman', 'Façonnier', 'Graphiste', 'Imprimeur', 'Infographe (Infographiste)', 'Photographe', 'Régisseur de lumière', 'Régisseur de son', 'Spécialiste de traitement d\'images photographiques', 'Technicien de laboratoire photos', $other('l\'Installation et réparation de matériels optiques et photographiques')],
                    ],
                    'Santé, hygiène et soins corporels et services à personne' => [
                        'Santé' => ['Magnétiseur', 'Naturopathe', 'Soignant traditionnel de fractures', 'Tradipraticien/Tradithérapeute', $other('la Santé')],
                        'Coiffures et tresses' => ['Coiffeur', 'Tresseur', $other('les Coiffures et tresses')],
                        'Travail d\'esthétique et de produits de la pharmacopée' => ['Esthéticien/Cosméticien', 'Préparateur de pharmacopée traditionnelle', 'Tatoueur', $other('le Travail d\'esthétique et de la pharmacopée')],
                        'Nettoyage et entretien' => ['Auxiliaires de vie sociale (AVS)', 'Blanchisseur', 'Embaumeur', 'Spécialiste de traitement et élimination des déchets', 'Toiletteur d\'animaux', $other('le Nettoyage et entretien')],
                        'Service à personne' => ['Agent commercial', 'Agent immobilier', 'Comédien', 'Conducteur de chariots élévateurs', 'Danseur', 'Distributeur de journaux', 'Distributeur/porteur d\'eau', 'Ecrivain public', 'Logisticien', 'Messager', 'Organisateur de mariage (wedding planner)', 'Webdesigner', $other('le Service à personne')],
                        'Travaux de finition bois, textile, peaux et cuirs' => ['Repasseur de vêtements', 'Vernisseur sur bois', $other('les Travaux de finition bois, textile, peaux et cuirs')],
                    ],
                ],
            ],
        ];
    }
};
