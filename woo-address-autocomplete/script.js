/* global google:false */

// eslint-disable-next-line no-unused-vars
function initPlaces() {
    const billing = {
        address1: document.getElementById('billing_address_1'),
        address2: document.getElementById('billing_address_2'),
        locality: document.getElementById('billing_city'),
        state:    document.getElementById('billing_state'),
        postcode: document.getElementById('billing_postcode')
    };

    const shipping = {
        address1: document.getElementById('shipping_address_1'),
        address2: document.getElementById('shipping_address_2'),
        locality: document.getElementById('shipping_city'),
        state:    document.getElementById('shipping_state'),
        postcode: document.getElementById('shipping_postcode')
    };

    const center = { lat: 37.5770368, lng: -77.5143365 }; // RVA

    // 1.45 degrees should be roughly 100 miles.
    const defaultBounds = {
        north: center.lat + 1.45,
        south: center.lat - 1.45,
        east:  center.lng + 1.45,
        west:  center.lng - 1.45
    };

    const options = {
        bounds:                defaultBounds,
        strictBounds:          false,
        componentRestrictions: { country: 'us' },
        fields:                ['address_components'],
        types:                 ['address']
    };

    const billingAutocomplete = new google.maps.places.Autocomplete(billing.address1, options);

    billingAutocomplete.addListener('place_changed', function () {
        const place  = billingAutocomplete.getPlace();
        let address1 = '';

        for (const component of place.address_components) {
            const componentType = component.types[0];

            switch (componentType) {
                case 'street_number': {
                    address1 = `${component.long_name} ${address1}`;
                    break;
                }

                case 'route': {
                    address1 += component.short_name;
                    billing.address1.value = address1;
                    break;
                }

                case 'postal_code': {
                    billing.postcode.value = component.long_name;
                    break;
                }

                case 'locality' : {
                    billing.locality.value = component.long_name;
                    break;
                }

                case 'administrative_area_level_1' : {
                    billing.state.value = component.short_name;
                    billing.state.dispatchEvent(new Event('change'));
                    break;
                }
            }

            billing.address2.value = '';
            billing.address2.focus();
        }

        document.body.dispatchEvent(new Event('update_checkout'));
    });

    const shippingAutocomplete = new google.maps.places.Autocomplete(shipping.address1, options);

    shippingAutocomplete.addListener('place_changed', function () {
        const place  = shippingAutocomplete.getPlace();
        let address1 = '';

        for (const component of place.address_components) {
            const componentType = component.types[0];

            switch (componentType) {
                case 'street_number': {
                    address1 = `${component.long_name} ${address1}`;
                    break;
                }

                case 'route': {
                    address1 += component.short_name;
                    shipping.address1.value = address1;
                    break;
                }

                case 'postal_code': {
                    shipping.postcode.value = component.long_name;
                    break;
                }

                case 'locality' : {
                    shipping.locality.value = component.long_name;
                    break;
                }

                case 'administrative_area_level_1' : {
                    shipping.state.value = component.short_name;
                    shipping.state.dispatchEvent(new Event('change'));
                    break;
                }
            }

            shipping.address2.value = '';
            shipping.address2.focus();
        }

        document.body.dispatchEvent(new Event('update_checkout'));
    });
}
