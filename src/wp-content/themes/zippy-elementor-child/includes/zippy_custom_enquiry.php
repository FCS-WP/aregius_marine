<?php

add_action('admin_init', 'add_enquiry_email_setting_field');

function add_enquiry_email_setting_field()
{
    // Register the setting
    register_setting('general', 'enquiry_receive_email', [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_email',
        'default'           => get_option('admin_email'),
    ]);

    // Add the field
    add_settings_field(
        'enquiry_receive_email',                         // ID
        'Email Receive Asked',                           // Label
        'render_enquiry_email_input',                    // Callback to render input
        'general'                                        // Page (General Settings)
    );
}

function render_enquiry_email_input()
{
    $value = get_option('enquiry_receive_email', get_option('admin_email'));
    echo '<input type="email" id="enquiry_receive_email" name="enquiry_receive_email" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">Email address to receive enquiry form submissions.</p>';
}

// Handle Submit form

add_action('init', 'handle_all_custom_forms');

function handle_all_custom_forms()
{
    if (
        $_SERVER['REQUEST_METHOD'] !== 'POST' ||
        ! isset($_POST['enquiry_nonce']) ||
        ! wp_verify_nonce($_POST['enquiry_nonce'], 'submit_enquiry')
    ) {
        return;
    }

    $form_type = sanitize_text_field($_POST['form_type'] ?? '');

    switch ($form_type) {
        case 'enquiry_form':
            var_dump('Handling enquiry form...');
            send_enquiry_email($_POST);
            break;

        default:
            break;
    }
}

function send_enquiry_email($data)
{
    // Define fields you want to skip (like nonces, form type, etc.)
    $exclude_fields = ['enquiry_nonce', 'form_type', 'action'];

    $message_lines = [];

    foreach ($data as $key => $value) {
        if (in_array($key, $exclude_fields)) {
            continue;
        }

        // Normalize key to readable label
        $label = ucwords(str_replace('_', ' ', $key));

        // Sanitize value
        $clean_value = is_array($value) ? implode(', ', array_map('sanitize_text_field', $value)) : sanitize_text_field($value);

        $message_lines[] = "$label: $clean_value";
    }

    $message = implode("\n", $message_lines);

    $to = get_option('enquiry_receive_email', get_option('admin_email'));
    $subject = 'New Enquiry Submission';

    $reply_to = sanitize_email($data['enquiry_email'] ?? '');

    wp_mail($to, $subject, $message, [
        'Reply-To: ' . $reply_to
    ]);
}

// Enquire Form slops-and-sludge

function render_form_slops_and_sludge()
{
    ob_start();
?>
    <form method="post" class="custom-enquiry-form">
        <?php wp_nonce_field('submit_enquiry', 'enquiry_nonce'); ?>
        <input type="hidden" name="form_type" id="form_type" value="enquiry_form">
        <!-- First Section -->
        <div>
            <div class="form-row">
                <div class="form-col form-col-6 flex-center">
                    <h5 class="form-section-title">Type of Slops and Sludge</h5>
                </div>
                <div class="form-col form-col-6 flex-center hide-on-mobile">
                    <h5 class="form-section-title">Est. Quantity</h5>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col form-col-6 flex-center">
                    <input class="custom-checkbox" type="checkbox" id="type_of_slops_and_sludge_1" name="type_of_slops_and_sludge" value="Annex 1 Slops">
                    <label for="type_of_slops_and_sludge_1">Annex 1 Slops</label>
                </div>
                <div class="form-col form-col-6 flex-center flex-wrap">
                    <label class="show-on-mobile" for="qty_type_of_slops_and_sludge_1">Annex 1 Slops Quantity: </label>
                    <input type="text" id="qty_type_of_slops_and_sludge_1" name="qty_type_of_slops_and_sludge_1" value="" placeholder="CBM">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col form-col-6 flex-center">
                    <input class="custom-checkbox" type="checkbox" id="type_of_slops_and_sludge_2" name="type_of_slops_and_sludge" value="Tank Cleaning Chemical Washings">
                    <label for="type_of_slops_and_sludge_2">Tank Cleaning Chemical Washings</label>
                </div>
                <div class="form-col form-col-6 flex-center flex-wrap">
                    <label class="show-on-mobile" for="qty_type_of_slops_and_sludge_2">Tank Cleaning Chemical Washings Quantity:</label>
                    <input type="text" id="qty_type_of_slops_and_sludge_2" name="qty_type_of_slops_and_sludge_2" value="" placeholder="CBM">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col form-col-6 flex-center">
                    <input class="custom-checkbox" type="checkbox" id="type_of_slops_and_sludge_3" name="type_of_slops_and_sludge" value="High Temp Bitumen Slops (EOPL Only)">
                    <label for="type_of_slops_and_sludge_3">High Temp Bitumen Slops (EOPL Only)</label>
                </div>
                <div class="form-col form-col-6 flex-center flex-wrap">
                    <label class="show-on-mobile" for="qty_type_of_slops_and_sludge_3">High Temp Bitumen Slops (EOPL Only) Quantity:</label>
                    <input type="text" id="qty_type_of_slops_and_sludge_3" name="qty_type_of_slops_and_sludge_3" value="" placeholder="CBM">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col form-col-6 flex-center">
                    <input class="custom-checkbox" type="checkbox" id="type_of_slops_and_sludge_4" name="type_of_slops_and_sludge" value="Annex II Slops (IBC Tanks at Singapore)">
                    <label for="type_of_slops_and_sludge_4">Annex II Slops (IBC Tanks at Singapore)</label>
                </div>
                <div class="form-col form-col-6 flex-center flex-wrap">
                    <label class="show-on-mobile" for="qty_type_of_slops_and_sludge_4">Annex II Slops (IBC Tanks at Singapore) Quantity:</label>
                    <input type="text" id="qty_type_of_slops_and_sludge_4" name="qty_type_of_slops_and_sludge_4" value="" placeholder="CBM">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col form-col-6 flex-center">
                    <input class="custom-checkbox" type="checkbox" id="type_of_slops_and_sludge_5" name="type_of_slops_and_sludge" value="Pumpable ER Sludge and Bilges Water">
                    <label for="type_of_slops_and_sludge_5">Pumpable ER Sludge and Bilges Water</label>
                </div>
                <div class="form-col form-col-6 flex-center flex-wrap">
                    <label class="show-on-mobile" for="qty_type_of_slops_and_sludge_5">Pumpable ER Sludge and Bilges Water Quantity:</label>
                    <input type="text" id="qty_type_of_slops_and_sludge_5" name="qty_type_of_slops_and_sludge_5" value="" placeholder="CBM">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col form-col-6 flex-center">
                    <input class="custom-checkbox" type="checkbox" id="type_of_slops_and_sludge_6" name="type_of_slops_and_sludge" value="Non-pumpable Bagged Sludge">
                    <label for="type_of_slops_and_sludge_6">Non-pumpable Bagged Sludge</label>
                </div>
                <div class="form-col form-col-6 flex-center flex-wrap">
                    <label class="show-on-mobile" for="qty_type_of_slops_and_sludge_6">Non-pumpable Bagged Sludge Quantity:</label>
                    <input type="text" id="qty_type_of_slops_and_sludge_6" name="qty_type_of_slops_and_sludge_6" value="" placeholder="MT">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col form-col-6 flex-center">
                    <input class="custom-checkbox" type="checkbox" id="type_of_slops_and_sludge_7" name="type_of_slops_and_sludge" value="Disposal of Cargo or Bunker Samples">
                    <label for="type_of_slops_and_sludge_7">Disposal of Cargo or Bunker Samples</label>
                </div>
                <div class="form-col form-col-6 flex-center flex-wrap">
                    <label class="show-on-mobile" for="qty_type_of_slops_and_sludge_7">Disposal of Cargo or Bunker Samples Quantity:</label>
                    <input type="text" id="qty_type_of_slops_and_sludge_7" name="qty_type_of_slops_and_sludge_7" value="" placeholder="Bottles">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col form-col-6 flex-center">
                    <input class="custom-checkbox" type="checkbox" id="type_of_slops_and_sludge_8" name="type_of_slops_and_sludge" value="De-bunkering">
                    <label for="type_of_slops_and_sludge_8">De-bunkering</label>
                </div>
                <div class="form-col form-col-6 flex-center flex-wrap">
                    <label class="show-on-mobile" for="qty_type_of_slops_and_sludge_8">De-bunkering Quantity:</label>
                    <input type="text" id="qty_type_of_slops_and_sludge_8" name="qty_type_of_slops_and_sludge_8" value="" placeholder="MT">
                </div>
            </div>
        </div>
        <!-- End First Section -->

        <!-- Start Second Section -->
        <div>
            <div class="form-row">
                <div class="form-col form-col-6">
                    <h5 class="form-section-title">Vessel Information</h5>
                    <div class="w-100">
                        <input class="custom-input" type="text" id="vessel_information_type" name="vessel_information_type" value="" placeholder="Type">
                        <input class="custom-input" type="text" id="vessel_information_dwt" name="vessel_information_dwt" value="" placeholder="DWT">
                        <input class="custom-input" type="text" id="vessel_information_eta" name="vessel_information_eta" value="" placeholder="ETA">
                    </div>
                </div>
                <div class="form-col form-col-6">
                    <h5 class="form-section-title">
                        Requester's Information
                    </h5>
                    <div class="w-100">
                        <input class="custom-input" type="text" id="requester_information_name" name="requester_information_name" value="" placeholder="Name" required>
                        <input class="custom-input" type="text" id="requester_information_company" name="requester_information_company" value="" placeholder="Company" required>
                        <input class="custom-input" type="text" id="requester_information_contact_no" name="requester_information_contact_no" value="" placeholder="Contact No." required>
                        <input class="custom-input" type="email" id="requester_information_email" name="requester_information_email" value="" placeholder="Enail" required>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <h5 class="form-section-title">
                Location
            </h5>
            <div class="form-row">
                <div class="form-col form-col-4">
                    <div class="flex-center mb-3">
                        <input class="custom-checkbox" type="checkbox" id="location_1" name="location" value="Disposal of Cargo or Bunker Samples">
                        <label for="location_1">Singapore Anchorage</label>
                    </div>
                    <div class="flex-center">
                        <input class="custom-checkbox" type="checkbox" id="location_2" name="location" value="Disposal of Cargo or Bunker Samples">
                        <label for="location_2">Disposal of Cargo or Bunker Samples</label>
                    </div>
                </div>
                <div class="form-col form-col-4">
                    <div class="flex-center mb-3">
                        <input class="custom-checkbox" type="checkbox" id="location_1" name="location" value="Disposal of Cargo or Bunker Samples">
                        <label for="location_1">Singapore Anchorage</label>
                    </div>
                    <div class="flex-center">
                        <input class="custom-checkbox" type="checkbox" id="location_2" name="location" value="Disposal of Cargo or Bunker Samples">
                        <label for="location_2">Disposal of Cargo or Bunker Samples</label>
                    </div>
                </div>
                <div class="form-col form-col-4">
                    <div class="flex-center mb-3">
                        <input class="custom-checkbox" type="checkbox" id="location_1" name="location" value="Disposal of Cargo or Bunker Samples">
                        <label for="location_1">Singapore Anchorage</label>
                    </div>
                    <div class="flex-center">
                        <input class="custom-checkbox" type="checkbox" id="location_2" name="location" value="Disposal of Cargo or Bunker Samples">
                        <label for="location_2">Disposal of Cargo or Bunker Samples</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- End Second Section -->
        <div class="custom-form-group">
            <button name="" type="submit">Send Enquiry</button>
        </div>


    </form>
<?php
    return ob_get_clean();
}

add_shortcode('form_slops_and_sludge', 'render_form_slops_and_sludge');
