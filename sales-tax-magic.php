<?php

/**
 * Plugin Name: Sales Tax Magic
 * Description: Did you know there's apparently no middle-ground plugins available between manually updating WooCommerce's tax tables and hooking in a massive and expensive tax service like TaxCloud or Avalara? Crazy, right? This plugin uses the API Ninjas endpoints to act in that middle ground.
 * Version:     1.4.0
 * Author:      chairmanbrando
 * Update URI:  false
 */

if (! defined('ABSPATH')) exit;

define('STM_PATH',        plugin_dir_path(__FILE__));
define('STM_URL',         plugin_dir_url(__FILE__));
define('STM_SALESTAX_EP', 'https://api.api-ninjas.com/v1/salestax');
define('STM_ZIPCODE_EP',  'https://api.api-ninjas.com/v1/zipcode');
## @todo Define `STM_API_KEY` in your env, WP config, or here if need be.

/**
 * Make an API call (via GET) with a given endpoint and arguments.
 *
 * I don't know how many iterations of a thing you're supposed to do until you abstract it out into
 * its own function, but for me two is usually enough. Thus, here's an API-getter function.
 *
 * @param string  $endpoint  Required
 * @param mixed[] $args      Optional
 */
function stm_make_api_call($endpoint, $args = []) {
    if (! defined('STM_API_KEY')) return false;
    if (empty($args))             return false;

    $url = $endpoint;

    foreach ($args as $key => $val) {
        $url = add_query_arg([$key => $val], $url);
    }

    $result = wp_remote_get($url, [
        'headers' => [
            'X-Api-Key'    => STM_API_KEY,
            'Content-Type' => 'application/json'
        ]
    ]);

    $result = wp_remote_retrieve_body($result);
    if (! $result) return false;

    $result = json_decode($result, true);
    if (! $result) return false;

    // No point in being array'd if it's not needed. 🤷‍♀️
    if (is_array($result) and sizeof($result) === 1) {
        $result = $result[0];
    }

    return $result;
}

/**
 * Get the state that contains the given zip code.
 *
 * The `woocommerce_matched_tax_rates` hook we're using pulls its `$state` parameter from the form.
 * We don't want this because the user can input a state and zip that don't match. Will it actually
 * come up? Probably not. But we might as well use a more robust option.
 *
 * @param string $zip
 */
function stm_get_state_from_zip($zip) {
    if ($state = get_transient("us_state_{$zip}")) {
        return $state;
    }

    $state = stm_make_api_call(STM_ZIPCODE_EP, ['zip' => $zip]);
    if (! is_array($state))       return false;
    if (! isset($state['state'])) return false;

    set_transient("us_state_{$zip}", $state['state'], YEAR_IN_SECONDS);

    return $state['state'];
}

/**
 * Given a zip and optionally a state, query the API (if needed) for its tax rate. We'll cache each
 * zip's tax rate for a day just to prevent any unnecessary and time-wasting requests.
 *
 * @param string $zip    Required
 * @param string $state  Optional. If provided will be used to tell Woo to add tax to shipping.
 */
function stm_get_tax_rate_for_zip($zip, $state = false) {
    if ($rate = get_transient("us_tax_rate_{$zip}")) {
        return $rate;
    }

    $rate = stm_make_api_call(STM_SALESTAX_EP, ['zip_code' => $zip]);
    if (! is_array($rate)) return false;

    $rate['total_rate'] = (float) $rate['total_rate'] * 100;

    // These states include shipping in their tax calculations.
    if ($state) {
        $shipping = ['AK', 'AR', 'CT', 'DC', 'DE', 'GA', 'HI', 'IN', 'KS', 'KY', 'MN', 'MS', 'NC', 'ND', 'NE', 'NH', 'NJ', 'NM', 'NY', 'OH', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'VT', 'WA', 'WI', 'WV'];
        $shipping = in_array($state, $shipping);
    } else {
        $shipping = false;
    }

    $rate = [
        'rate'     => $rate['total_rate'] ?: (float) 0,
        'label'    => "US ({$zip})",
        'shipping' => ($shipping) ? 'yes' : 'no',
        'compound' => 'no'
    ];

    // Options are automatically serialized.
    set_transient("us_tax_rate_{$zip}", $rate, DAY_IN_SECONDS);

    return $rate;
}

/**
 * Merge in the state's queried tax rate with anything Woo pulls from the back-end settings.
 *
 * The `woocommerce_cart_totals_get_item_tax_rates` hook is *not* the one, apparently. You can use
 * it for general taxes, but it seems to happen too late to affect taxes on shipping costs!
 *
 * @param mixed[] $rates
 * @param string  $country
 * @param string  $state
 * @param string  $zip
 */
add_filter('woocommerce_matched_tax_rates', function ($rates, $country, $state, $zip) {
    if (! $zip) {
        $zip = WC()->customer->get_billing_postcode();
    }

    if (empty($zip)) return $rates;

    $ostate = $state;
    $state  = stm_get_state_from_zip($zip);
    $rate   = stm_get_tax_rate_for_zip($zip, ($state) ? $state : $ostate);

    if (! $rate) return $rates;

    return array_merge($rates, [$rate]);
}, 10, 4);

/**
 * Firefox sometimes doesn't trigger `onchange` or `update` events when autofilling whole forms. I
 * haven't figured out why yet; it seems to be random depending on the site in question. Without the
 * events Woo's `update_order_review` ajax routine doesn't fire. Without *that* neither shipping nor
 * tax is calculated as those hooks are never run. That's a problem, Boblem!
 *
 * To get around that problem, we tap into an event that *does* happen on autofill, `input`, and
 * check if the zip code field has been autofilled. If so, make sure the form is aware of it via
 * manual triggering so Woo can do its thing. Sheesh!
 */
add_action('wp_footer', function () {
    if (! is_checkout()) return;

    ?>
    <script>
        jQuery(document).ready(function ($) {
            $('#billing_postcode').on('input', function () {
                if ($(this).is(':autofill') || $(this).is(':-webkit-autofill')) {
                    $(this).closest('form').trigger('update')
                }
            });
        });
    </script>
    <?php
});
