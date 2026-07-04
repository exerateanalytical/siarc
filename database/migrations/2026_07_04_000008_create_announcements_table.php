<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * News & Announcements ("Actualités & Annonces") — backing table for the admin
 * page replicated from "gestion d'actualites et annonces.png". The platform had
 * no announcements concept before this migration; the articles shown in the
 * design are seeded inside up() so the admin view never hardcodes rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_fr');
            $table->string('title_en')->nullable();
            $table->text('excerpt_fr')->nullable();
            $table->text('excerpt_en')->nullable();
            $table->text('body_fr')->nullable();
            $table->text('body_en')->nullable();
            $table->string('category')->nullable();
            // Design's "Type" column: Actualité / Article / Annonce
            $table->string('type')->nullable();
            $table->string('status')->default('published'); // published | draft | scheduled
            $table->timestamp('published_at')->nullable();
            $table->string('author_name')->nullable();
            $table->string('cover_image')->nullable();
            // Design's "Vues" column
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();
        });

        // ---- Seed: the exact publications shown in the design (top to bottom) ----
        $now = now();
        $rows = [
            [
                'slug'         => 'lancement-du-festival-international-de-l-artisanat-2025',
                'title_fr'     => 'Lancement du Festival International de l\'Artisanat 2025',
                'title_en'     => 'Launch of the 2025 International Crafts Festival',
                'excerpt_fr'   => 'Le Cameroun célèbre son artisanat sous le signe de l\'excellence et de la transmission.',
                'excerpt_en'   => 'Cameroon celebrates its craftsmanship under the banner of excellence and transmission.',
                'category'     => 'Événements',
                'type'         => 'Actualité',
                'status'       => 'published',
                'published_at' => '2025-06-03 09:00:00',
                'author_name'  => 'Admin Super',
                'cover_image'  => 'images/landing/event-1.png',
                'views_count'  => 12450,
            ],
            [
                'slug'         => 'les-paniers-en-rotin-de-l-ouest-a-l-honneur',
                'title_fr'     => 'Les paniers en rotin de l\'Ouest à l\'honneur',
                'title_en'     => 'Rattan baskets from the West in the spotlight',
                'excerpt_fr'   => 'Découvrez le savoir-faire exceptionnel des vanniers de la région de l\'Ouest.',
                'excerpt_en'   => 'Discover the exceptional know-how of the basket weavers of the West region.',
                'category'     => 'Artisanat',
                'type'         => 'Article',
                'status'       => 'published',
                'published_at' => '2025-06-02 09:00:00',
                'author_name'  => 'Annie Hadidja',
                'cover_image'  => 'images/landing/auth-baskets.png',
                'views_count'  => 8752,
            ],
            [
                'slug'         => 'ouverture-du-musee-national-de-l-artisanat',
                'title_fr'     => 'Ouverture du Musée National de l\'Artisanat',
                'title_en'     => 'Opening of the National Museum of Crafts',
                'excerpt_fr'   => 'Le nouvel espace dédié à la préservation du patrimoine artisanal camerounais ouvre ses portes.',
                'excerpt_en'   => 'The new space dedicated to preserving Cameroon\'s craft heritage opens its doors.',
                'category'     => 'Annonces',
                'type'         => 'Annonce',
                'status'       => 'published',
                'published_at' => '2025-05-30 09:00:00',
                'author_name'  => 'Admin Super',
                'cover_image'  => 'images/landing/event-2.png',
                'views_count'  => 15320,
            ],
            [
                'slug'         => 'sculpture-sur-bois-l-art-de-la-transmission',
                'title_fr'     => 'Sculpture sur bois : l\'art de la transmission',
                'title_en'     => 'Wood carving: the art of transmission',
                'excerpt_fr'   => 'Interview exclusive avec Maître Emmanuel, gardien d\'un savoir-faire ancestral.',
                'excerpt_en'   => 'Exclusive interview with Master Emmanuel, guardian of an ancestral craft.',
                'category'     => 'Culture',
                'type'         => 'Article',
                'status'       => 'published',
                'published_at' => '2025-05-28 10:00:00',
                'author_name'  => 'Bernard Ndongo',
                'cover_image'  => 'images/landing/default-product-bois-sculpture.png',
                'views_count'  => 6890,
            ],
            [
                'slug'         => 'formation-des-jeunes-artisans-de-l-adamaoua',
                'title_fr'     => 'Formation des jeunes artisans de l\'Adamaoua',
                'title_en'     => 'Training young artisans of Adamawa',
                'excerpt_fr'   => 'Un programme pour renforcer les compétences des jeunes artisans de la région.',
                'excerpt_en'   => 'A programme to strengthen the skills of the region\'s young artisans.',
                'category'     => 'Programmes',
                'type'         => 'Actualité',
                'status'       => 'scheduled',
                'published_at' => '2025-06-10 08:00:00',
                'author_name'  => 'Marie Ngoa',
                'cover_image'  => 'images/landing/event-3.png',
                'views_count'  => 0,
            ],
            [
                'slug'         => 'salon-des-metiers-d-art-et-du-design-appel-a-candidature',
                'title_fr'     => 'Salon des Métiers d\'Art et du Design : appel à candidature',
                'title_en'     => 'Arts & Design Trades Fair: call for applications',
                'excerpt_fr'   => 'Artisans, inscrivez-vous dès maintenant pour participer à la prochaine édition.',
                'excerpt_en'   => 'Artisans, register now to take part in the next edition.',
                'category'     => 'Événements',
                'type'         => 'Annonce',
                'status'       => 'draft',
                'published_at' => null,
                'author_name'  => 'Admin Super',
                'cover_image'  => 'images/landing/ad-siarc.png',
                'views_count'  => 0,
            ],
            [
                'slug'         => 'la-poterie-traditionnelle-de-l-extreme-nord',
                'title_fr'     => 'La poterie traditionnelle de l\'Extrême-Nord',
                'title_en'     => 'Traditional pottery of the Far North',
                'excerpt_fr'   => 'Plongée au cœur d\'un patrimoine vivant transmis de génération en génération.',
                'excerpt_en'   => 'A dive into the heart of a living heritage passed down from generation to generation.',
                'category'     => 'Artisanat',
                'type'         => 'Article',
                'status'       => 'published',
                'published_at' => '2025-05-28 09:00:00',
                'author_name'  => 'Michel Kange',
                'cover_image'  => 'images/landing/default-product-poterie-ceramique.png',
                'views_count'  => 9215,
            ],
            [
                'slug'         => 'portrait-femme-artisan-de-la-semaine-valentine-tchoua',
                'title_fr'     => 'Portrait : Femme artisan de la semaine – Valentine Tchoua',
                'title_en'     => 'Portrait: Woman artisan of the week – Valentine Tchoua',
                'excerpt_fr'   => 'Son parcours, sa passion, son engagement pour l\'artisanat camerounais.',
                'excerpt_en'   => 'Her journey, her passion, her commitment to Cameroonian craftsmanship.',
                'category'     => 'Portraits',
                'type'         => 'Article',
                'status'       => 'published',
                'published_at' => '2025-05-24 09:00:00',
                'author_name'  => 'Annie Hadidja',
                'cover_image'  => 'images/landing/about-potter.png',
                'views_count'  => 7651,
            ],
        ];

        foreach ($rows as $i => $row) {
            // Staggered created_at so "order by created_at desc" reproduces the design order.
            $row['body_fr']    = $row['excerpt_fr'];
            $row['body_en']    = $row['excerpt_en'];
            $row['created_at'] = $now->copy()->subMinutes($i);
            $row['updated_at'] = $now->copy()->subMinutes($i);
            DB::table('announcements')->insert($row);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
