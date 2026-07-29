<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where a logged-out visitor's contact message is kept.
 *
 * It was kept nowhere. POST /contact mailed the message, swallowed any send
 * failure in a catch, and told the visitor "Votre message a bien été envoyé"
 * regardless. With MAIL_MAILER=log — the fallback DEPLOY.md itself recommends
 * if the relay does not come up — that sentence was false for every guest
 * enquiry, and the enquiry existed only as a line in a log file.
 *
 * A logged-in visitor already got a durable record: POST /contact opens a real
 * support_tickets row for them. This table is the guest equivalent. It is not
 * support_tickets because that table's user_id is a non-null foreign key to
 * users and a guest has no user row; widening it would change what every
 * existing ticket view can assume about $ticket->user.
 *
 * mailed_at / mail_error record what happened to the notification, so an
 * operator can tell "nobody has replied yet" from "the relay was down and
 * nobody ever saw it" without reading a log.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 190);
            $table->string('subject');
            $table->text('message');
            $table->string('lang', 2)->default('fr');
            $table->enum('status', ['new', 'read', 'replied', 'closed'])->default('new');
            // Null until the notification email is accepted by the transport.
            $table->timestamp('mailed_at')->nullable();
            $table->text('mail_error')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
