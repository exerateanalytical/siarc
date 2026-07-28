<?php

/**
 * How the platform's own fees get paid, today.
 *
 * Scope first, because it is the line that matters most and it is easy to blur:
 * everything in this file concerns money the platform charges for its own
 * services — registration, membership, renewal, verification, workshop
 * inspection. It has nothing to do with sales between a buyer and an artisan.
 * The platform is not a party to those, does not receive the price, and must
 * never grow a code path that takes money for a product. If you find yourself
 * adding a `purpose` here that names a product or an order, stop.
 *
 * There is no payment API. A member is shown a number, sends money to it with
 * their own phone, tells us they have done so, and an administrator checks the
 * operator's own records and confirms. That is slower and more manual than an
 * integration, and it is also honest: the platform never claims to know a
 * payment arrived when all it holds is somebody's word.
 *
 * The numbers live here rather than in a view or a seeder for two reasons. They
 * change — an operator account gets replaced and nobody should need a deploy to
 * fix it — and there must be exactly one place a wrong number can be wrong.
 *
 * Every account value reads from env with an EMPTY default, and that is
 * deliberate to the point of being the most important rule in this file. An
 * invented mobile-money number is the single worst fabrication this codebase
 * could contain, because a member would read it off the screen and send real
 * money to a stranger. There is no safe placeholder. A method whose number is
 * unset reports itself unconfigured and is refused display; showing a payment
 * instruction with a blank number would be worse still, because a member would
 * fill the gap with a guess.
 */

return [

    /*
     * Manual confirmation, stated as configuration rather than folklore.
     *
     * THE SEAM: when an MTN MoMo Collections API is connected, its callback
     * handler calls App\Support\ManualPayment::confirm() with the operator as
     * the actor — the same method, the same state machine, the same event
     * trail. Nothing else changes. This flag flips to false, and the only
     * behavioural difference is that a machine rather than a human supplies the
     * evidence. Keeping the confirming method identical is what keeps the audit
     * trail continuous across the switchover, and it is why confirm() is
     * idempotent: a callback that fires twice must not grant twice.
     */
    'manual' => true,

    /*
     * Prefix for the reference a payer types into the mobile-money "reason"
     * field. Short, because it is typed on a feature phone keypad under time
     * pressure, and unambiguous, because it is the only thing linking an
     * operator's transaction record to a row in our database.
     */
    'reference_prefix' => env('PAYMENT_REFERENCE_PREFIX', 'AH237-PAY'),

    /*
     * How long a payer has to send the money and report it before the record
     * expires. Expiry grants nothing and removes nothing — it only stops a
     * months-old unpaid intent sitting in a reviewer's queue forever.
     */
    'confirmation_window_days' => (int) env('PAYMENT_CONFIRMATION_WINDOW_DAYS', 7),

    /*
     * The methods. `code` is what a payments row stores, so these strings are
     * data and must not be renamed casually.
     *
     * `active` is the operator's intent — "we accept this" — and is separate
     * from whether the method is *configured*. A method can be active and still
     * be unusable because nobody filled in the number, and ManualPayment::methods()
     * excludes it and records why. Conflating the two would let a deploy with a
     * missing env var silently show an empty account line.
     */
    'methods' => [

        'mtn_momo' => [
            'code'      => 'mtn_momo',
            'kind'      => 'mobile_money',
            'label_fr'  => 'MTN Mobile Money',
            'label_en'  => 'MTN Mobile Money',
            // Empty default, on purpose. See the note at the top of this file.
            'number'    => env('PAYMENT_MTN_NUMBER', ''),
            'holder'    => env('PAYMENT_MTN_NAME', ''),
            'instructions_fr' => env('PAYMENT_MTN_INSTRUCTIONS_FR', ''),
            'instructions_en' => env('PAYMENT_MTN_INSTRUCTIONS_EN', ''),
            'active'    => (bool) env('PAYMENT_MTN_ACTIVE', true),
        ],

        'orange_money' => [
            'code'      => 'orange_money',
            'kind'      => 'mobile_money',
            'label_fr'  => 'Orange Money',
            'label_en'  => 'Orange Money',
            'number'    => env('PAYMENT_ORANGE_NUMBER', ''),
            'holder'    => env('PAYMENT_ORANGE_NAME', ''),
            'instructions_fr' => env('PAYMENT_ORANGE_INSTRUCTIONS_FR', ''),
            'instructions_en' => env('PAYMENT_ORANGE_INSTRUCTIONS_EN', ''),
            'active'    => (bool) env('PAYMENT_ORANGE_ACTIVE', true),
        ],

        /*
         * A bank transfer slot, present so the shape is defined and nobody
         * invents a different one later. Inactive by default: an unbanked
         * option shown to members who cannot use it is noise, and an account
         * number nobody has supplied is the same hazard as an invented phone
         * number.
         */
        'bank_transfer' => [
            'code'      => 'bank_transfer',
            'kind'      => 'bank_transfer',
            'label_fr'  => 'Virement bancaire',
            'label_en'  => 'Bank transfer',
            'number'    => env('PAYMENT_BANK_ACCOUNT', ''),
            'holder'    => env('PAYMENT_BANK_HOLDER', ''),
            'instructions_fr' => env('PAYMENT_BANK_INSTRUCTIONS_FR', ''),
            'instructions_en' => env('PAYMENT_BANK_INSTRUCTIONS_EN', ''),
            'active'    => (bool) env('PAYMENT_BANK_ACTIVE', false),
        ],

        /*
         * Cash at a platform desk. `number` is meaningless for cash, so the
         * configured check for this kind falls on the holder — the named place
         * or person the money is handed to. Cash with nobody named is a
         * receipt nobody can chase, which is exactly the situation this whole
         * file exists to prevent.
         */
        'cash' => [
            'code'      => 'cash',
            'kind'      => 'cash',
            'label_fr'  => 'Espèces au bureau',
            'label_en'  => 'Cash at the office',
            'number'    => null,
            'holder'    => env('PAYMENT_CASH_HOLDER', ''),
            'instructions_fr' => env('PAYMENT_CASH_INSTRUCTIONS_FR', ''),
            'instructions_en' => env('PAYMENT_CASH_INSTRUCTIONS_EN', ''),
            'active'    => (bool) env('PAYMENT_CASH_ACTIVE', false),
        ],
    ],
];
