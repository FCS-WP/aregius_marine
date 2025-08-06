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
            send_enquiry_email($_POST);
            break;

        default:
            break;
    }
}

function send_enquiry_email($data)
{
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

function render_form_field($type, $label, $is_label_first,  $placeholder, $input_name, $input_value, $is_required) {}

function render_form_row_2_col($data, $index)
{
    list($label_1, $label_2, $placeholder, $input_name, $checkbox_value, $is_required) = $data;

    $checkbox_id = $input_name . '_' . ($index + 1);
    $checkbox_slug = create_slug($checkbox_value);
    $qty_input_id = 'quantity_of_' . $checkbox_slug;
    $required_attr = $is_required ? 'required' : '';

    ob_start();
?>
    <div class="form-row">
        <div class="form-col form-col-6 flex-center">
            <input
                class="custom-checkbox"
                type="checkbox"
                id="<?php echo esc_attr($checkbox_id); ?>"
                name="<?php echo esc_attr($input_name); ?>[]"
                value="<?php echo esc_attr($checkbox_value); ?>">
            <label for="<?php echo esc_attr($checkbox_id); ?>">
                <?php echo esc_html($label_1); ?>
            </label>
        </div>
        <div class="form-col form-col-6 flex-center flex-wrap">
            <label class="show-on-mobile" for="<?php echo esc_attr($qty_input_id); ?>">
                <?php echo esc_html($label_2); ?>:
            </label>
            <input
                type="text"
                id="<?php echo esc_attr($qty_input_id); ?>"
                name="<?php echo esc_attr($qty_input_id); ?>"
                value=""
                placeholder="<?php echo esc_attr($placeholder); ?>"
                <?php echo $required_attr; ?>>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function render_form_row_3_col($data, $index)
{
    list($label_1, $label_2, $placeholder, $input_name, $checkbox_value, $is_required, $col2type) = $data;

    $checkbox_id = $input_name . '_' . ($index + 1);
    $checkbox_slug = create_slug($checkbox_value);
    $qty_input_id = 'duration_of_' . $checkbox_slug;
    $additonal_id = 'additional_info_of_' . $checkbox_slug;
    $required_attr = $is_required ? 'required' : '';

    ob_start();
?>
    <div class="form-row">
        <div class="form-col form-col-4 flex-center">
            <input
                class="custom-checkbox"
                type="checkbox"
                id="<?php echo esc_attr($checkbox_id); ?>"
                name="<?php echo esc_attr($input_name); ?>[]"
                value="<?php echo esc_attr($checkbox_value); ?>">
            <label for="<?php echo esc_attr($checkbox_id); ?>">
                <?php echo esc_html($label_1); ?>
            </label>
        </div>
        <div class="form-col form-col-4 flex-center flex-wrap">
            <label class="show-on-mobile" for="<?php echo esc_attr($qty_input_id); ?>">
                <?php echo esc_html($label_2); ?>:
            </label>
            <input
                type="<?php echo $col2type ?>"
                id="<?php echo esc_attr($qty_input_id); ?>"
                name="<?php echo esc_attr($qty_input_id); ?>"
                value=""
                placeholder="<?php echo esc_attr($placeholder); ?>"
                <?php echo $required_attr; ?>>
        </div>
        <div class="form-col form-col-4 flex-center flex-wrap">
            <label class="show-on-mobile" for="<?php echo esc_attr($additonal_id); ?>">
                Addtional Information (if any)
            </label>
            <input
                type="text"
                id="<?php echo esc_attr($additonal_id); ?>"
                name="<?php echo esc_attr($additonal_id); ?>"
                value=""
                placeholder="" />
        </div>
    </div>
<?php
    return ob_get_clean();
}

function render_input_text($data, $index, $readonly = false)
{
    list($type, $input_name, $value, $placeholder, $required) = $data;
    $is_required = $required ? 'required' : '';
    $is_readonly = $readonly ? 'readonly' : '';
    echo ('<input class="custom-input" type="' . $type . '" id="' . $input_name . '" name="' . $input_name . '" value="' . $value . '" placeholder="' . $placeholder . '" ' . $is_required . ' ' . $is_readonly . '>');
}

function render_checkbox($data, $index)
{
    list($label, $input_name, $input_value) = $data;
    $checkbox_id = $input_name . '_' . ($index + 1); // Ensure ID starts from 1

    ob_start();
?>
    <div class="flex-center mb-3">
        <input class="custom-checkbox" type="checkbox" id="<?php echo esc_attr($checkbox_id); ?>" name="<?php echo esc_attr($input_name); ?>[]" value="<?php echo esc_attr($input_value); ?>">
        <label for="<?php echo esc_attr($checkbox_id); ?>"><?php echo esc_html($label); ?></label>
    </div>
<?php
    return ob_get_clean();
}

// Enquire Form slops-and-sludge
function render_form_slops_and_sludge()
{
    $field_arr = array(
        // ($label_1, $label_2, $placeholder, $input_name, $checkbox_value, $is_required)
        ['Annex 1 Slops', 'Annex 1 Slops Quantity (CBM)', 'CBM', 'type_of_slops_and_sludge', 'Annex 1 Slops', false],
        ['Tank Cleaning Chemical Washings', 'Tank Cleaning Chemical Washings Quantity', 'CBM', 'type_of_slops_and_sludge', 'Tank Cleaning Chemical Washings', false],
        ['High Temp Bitumen Slops (EOPL Only)', 'High Temp Bitumen Slops Quantity', 'CBM', 'type_of_slops_and_sludge', 'High Temp Bitumen Slops (EOPL Only)', false],
        ['Annex II Slops (IBC Tanks at Singapore)', 'Annex II Slops (IBC Tanks at Singapore) Quantity', 'CBM', 'type_of_slops_and_sludge', 'Annex II Slops (IBC Tanks at Singapore)', false],
        ['Pumpable ER Sludge and Bilges Water', 'Pumpable ER Sludge and Bilges Water Quantity', 'CBM', 'type_of_slops_and_sludge', 'Pumpable ER Sludge and Bilges Water', false],
        ['Non-pumpable Bagged Sludge', 'Non-pumpable Bagged Sludge Quantity', 'MT', 'type_of_slops_and_sludge', 'Non-pumpable Bagged Sludge', false],
        ['Disposal of Cargo or Bunker Samples', 'Disposal of Cargo or Bunker Samples Quantity', 'Bottles', 'type_of_slops_and_sludge', 'Disposal of Cargo or Bunker Samples', false],
        ['De-bunkering', 'De-bunkering Quantity', 'MT', 'type_of_slops_and_sludge', 'De-bunkering', false],
    );
    $vessel_information_fields = array(
        // ($type, $input_name, $value, $placeholder)
        ['text', 'vessel_information_type', '', 'Type', false],
        ['text', 'vessel_information_dwt', '', 'DWT', false],
        ['date', 'vessel_information_eta', '', 'ETA', false],
    );

    $requester_info_fields = array(
        ['text', 'requester_information_name', '', 'Name', true],
        ['text', 'requester_information_company', '', 'Company', true],
        ['text', 'requester_information_contact_no', '', 'Contact No.', true],
        ['text', 'requester_information_email', '', 'Email', true],
    );

    $loaction_fields = array(
        // ($label, $input_name, $input_value)
        ['Singapore Anchorage', 'location', 'Singapore Anchorage'],
        ['Tanjung Pelepas, Kukup, Malaysia', 'location', 'Tanjung Pelepas, Kukup, Malaysia'],
        ['Singapore Eastern OPL (EOPL)', 'location', 'Singapore Eastern OPL (EOPL)'],
        ['Malacca, Malaysia', 'location', 'Malacca, Malaysia'],
        ['Pasir Gudang, Pengerang, Malaysia', 'location', 'Pasir Gudang, Pengerang, Malaysia'],
        ['Port Klang, Malaysia', 'location', 'Port Klang, Malaysia'],
    );
    ob_start();
?>
    <form method="post" class="custom-enquiry-form">
        <?php wp_nonce_field('submit_enquiry', 'enquiry_nonce'); ?>
        <input type="hidden" name="form_type" id="form_type" value="enquiry_form">
        <!-- First Section -->
        <div>
            <h5 class="form-section-title">
                Location
            </h5>
            <div class="form-row">
                <div class="form-col form-col-4">
                    <?php
                    foreach ($loaction_fields as $i => $field) {
                        echo render_checkbox($field, $i);
                        // Open a new column every 2 items (or 3 if you want 3 per row)
                        if (($i + 1) % 2 === 0 && $i + 1 < count($loaction_fields)) {
                            echo '</div><div class="form-col form-col-4">';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

        <div>
            <div class="form-row mb-0">
                <div class="form-col form-col-6 flex-center">
                    <h5 class="form-section-title">Type of Slops and Sludge</h5>
                </div>
                <div class="form-col form-col-6 flex-center hide-on-mobile">
                    <h5 class="form-section-title">Est. Quantity</h5>
                </div>
            </div>

            <?php
            foreach ($field_arr as $index => $field_item) {
                echo render_form_row_2_col($field_item, $index);
            }
            ?>

        </div>
        <!-- End First Section -->

        <!-- Start Second Section -->
        <div>
            <div class="form-row">
                <div class="form-col form-col-6">
                    <h5 class="form-section-title">Vessel Information</h5>
                    <div class="w-100">
                        <?php
                        foreach ($vessel_information_fields as $index => $item) {
                            render_input_text($item, $index);
                        }
                        ?>
                    </div>
                </div>
                <div class="form-col form-col-6">
                    <h5 class="form-section-title">
                        Requester's Information
                    </h5>
                    <div class="w-100">
                        <?php
                        foreach ($requester_info_fields as $index => $field) {
                            render_input_text($field, $index);
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-row">

            <div class="form-col form-col-12">
                <h5 class="form-section-title">
                    Any additional info or requests
                </h5>
                <textarea name="additional_requests" id="additional_requests"></textarea>
            </div>
        </div>

        <!-- End Second Section -->
        <div class="custom-form-group">
            <button class="submit-button" name="" type="submit">Send Enquiry</button>
        </div>


    </form>
<?php
    return ob_get_clean();
}

add_shortcode('form_slops_and_sludge', 'render_form_slops_and_sludge');


function render_eopl_anchoring_guidance()
{
    $purpose_form_fields = array(
        // ($label_1, $label_2, $placeholder, $input_name, $checkbox_value, $is_required)
        ['Cargo Tank or Hold Washing', 'Est. Duration (Days)', 'CBM', 'purpose', 'Cargo Tank or Hold Washing', false, 'date'],
        ['Gas Freeing', 'Est. Duration (Days)', 'CBM', 'purpose', 'Gas Freeing', false, 'date'],
        ['Deslopping', 'Est. Duration (Days)', 'CBM', 'purpose', 'Deslopping', false, 'date'],
        ['Awaiting Orders', 'Est. Duration (Days)', 'CBM', 'purpose', 'Awaiting Orders', false, 'date'],
        ['Repairs', 'Est. Duration (Days)', 'CBM', 'purpose', 'Repairs', false, 'date'],
        ['Others', 'Est. Duration (Days)', 'CBM', 'purpose', 'Others', false, 'date'],
    );
    $vessel_information_fields = array(
        // ($type, $input_name, $value, $placeholder)
        ['text', 'vessel_information_type', '', 'Type', false],
        ['text', 'vessel_information_dwt', '', 'DWT', false],
        ['date', 'vessel_information_eta', '', 'ETA', false],
    );

    $requester_info_fields = array(
        ['text', 'requester_information_name', '', 'Name', true],
        ['text', 'requester_information_company', '', 'Company', true],
        ['text', 'requester_information_contact_no', '', 'Contact No.', true],
        ['text', 'requester_information_email', '', 'Email', true],
    );

    ob_start();
?>
    <form method="post" class="custom-enquiry-form">
        <?php wp_nonce_field('submit_enquiry', 'enquiry_nonce'); ?>
        <input type="hidden" name="form_type" id="form_type" value="enquiry_form">
        <!-- First Section -->
        <div class="form-row">
            <div class="form-col form-col-12">
                <h5 class="form-section-title">
                    Any additional info or requests
                </h5>
                <textarea name="additional_requests" id="additional_requests"></textarea>
            </div>
        </div>

        <div>
            <div class="form-row mb-0">
                <div class="form-col form-col-4 flex-center">
                    <h5 class="form-section-title">Type of Slops and Sludge</h5>
                </div>
                <div class="form-col form-col-4 flex-center hide-on-mobile">
                    <h5 class="form-section-title">Est. Duration</h5>
                </div>
                <div class="form-col form-col-4 flex-center hide-on-mobile">
                    <h5 class="form-section-title">Additional Information (if any)</h5>
                </div>
            </div>
            <?php
            foreach ($purpose_form_fields as $index => $purpose_field) {
                echo render_form_row_3_col($purpose_field, $index);
            }
            ?>
        </div>
        <!-- End First Section -->

        <!-- Start Second Section -->
        <div>
            <div class="form-row">
                <div class="form-col form-col-6">
                    <h5 class="form-section-title">Vessel Information</h5>
                    <div class="w-100">
                        <?php
                        foreach ($vessel_information_fields as $index => $item) {
                            render_input_text($item, $index);
                        }
                        ?>
                    </div>
                </div>
                <div class="form-col form-col-6">
                    <h5 class="form-section-title">
                        Requester's Information
                    </h5>
                    <div class="w-100">
                        <?php
                        foreach ($requester_info_fields as $index => $field) {
                            render_input_text($field, $index);
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- End Second Section -->
        <div class="custom-form-group">
            <button class="submit-button" name="" type="submit">Send Enquiry</button>
        </div>


    </form>
<?php
    return ob_get_clean();
}

add_shortcode('form_eopl_anchoring', 'render_eopl_anchoring_guidance');

function render_form_port_agency_service()
{
    $type_of_attendance_fields = array(
        // ($label, $input_name, $input_value)
        ['Bunker Call', 'type_of_attendance', 'Bunker Call'],
        ['Agents (Bunker Call) + BQS, 221B & Expediting – Singapore Only', 'type_of_attendance', 'Agents (Bunker Call) + BQS, 221B & Expediting – Singapore Only'],
        ['Tank Cleaning Call', 'type_of_attendance', 'Tank Cleaning Call'],
        ['Agents (Tank Cleaning) + Full Tank Cleaning  Ops – Singapore Only', 'type_of_attendance', 'Agents (Tank Cleaning) + Full Tank Cleaning  Ops – Singapore Only'],
        ['Cargo Operations', 'type_of_attendance', 'Cargo Operations'],
        ['Stores, Spares, Lub-oil and Logistics', 'type_of_attendance', 'Stores, Spares, Lub-oil and Logistics'],
        ['Owner’s Matters – Crew Change, CTM', 'type_of_attendance', 'Owner’s Matters – Crew Change, CTM'],
        ['Dry-Docking, Repairs', 'type_of_attendance', 'Dry-Docking, Repairs'],
        ['Protective Agents or Other Matters', 'type_of_attendance', 'Protective Agents or Other Matters'],
    );

    $vessel_information_fields = array(
        // ($type, $input_name, $value, $placeholder)
        ['text', 'vessel_information_type', '', 'Type', false],
        ['text', 'vessel_information_grt', '', 'GRT', false],
        ['text', 'vessel_information_nrt', '', 'NRT', false],
        ['text', 'vessel_information_dwt', '', 'DWT', false],
        ['date', 'vessel_information_eta', '', 'ETA', false],
    );

    $requester_info_fields = array(
        ['text', 'requester_information_name', '', 'Name', true],
        ['text', 'requester_information_company', '', 'Company', true],
        ['text', 'requester_information_contact_no', '', 'Contact No.', true],
        ['text', 'requester_information_email', '', 'Email', true],
    );

    $loaction_fields = array(
        // ($label, $input_name, $input_value)
        ['Singapore Anchorage', 'location', 'Singapore Anchorage'],
        ['Pasir Gudang, Pengerang, Malaysia', 'location', 'Pasir Gudang, Pengerang, Malaysia'],
        ['Tanjung Pelepas, Kukup, Malaysia', 'location', 'Tanjung Pelepas, Kukup, Malaysia'],
    );
    ob_start();
?>
    <form method="post" class="custom-enquiry-form">
        <?php wp_nonce_field('submit_enquiry', 'enquiry_nonce'); ?>
        <input type="hidden" name="form_type" id="form_type" value="enquiry_form">
        <!-- First Section -->
        <div class="mb-4">
            <div>
                <h5 class="form-section-title">Type of Attendance</h5>
            </div>
            <?php
            foreach ($type_of_attendance_fields as $index => $field_item) {
                echo render_checkbox($field_item, $index);
            }
            ?>
        </div>
        <!-- End First Section -->

        <!-- Start Second Section -->
        <div>
            <div class="form-row">
                <div class="form-col form-col-6">
                    <h5 class="form-section-title">Vessel Information</h5>
                    <div class="w-100">
                        <?php
                        foreach ($vessel_information_fields as $index => $item) {
                            render_input_text($item, $index);
                        }
                        ?>
                    </div>
                </div>
                <div class="form-col form-col-6">
                    <h5 class="form-section-title">
                        Requester's Information
                    </h5>
                    <div class="w-100">
                        <?php
                        foreach ($requester_info_fields as $index => $field) {
                            render_input_text($field, $index);
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-row">

            <div class="form-col form-col-12">
                <h5 class="form-section-title">
                    Any additional info or requests
                </h5>
                <textarea name="additional_requests" id="additional_requests"></textarea>
            </div>
        </div>

        <!-- End Second Section -->
        <div class="custom-form-group">
            <button class="submit-button" name="" type="submit">Send Enquiry</button>
        </div>


    </form>
<?php
    return ob_get_clean();
}

add_shortcode('form_port_agency_service', 'render_form_port_agency_service');

function render_form_bunker_survey_services()
{
    $type_of_attendance_fields = array(
        // ($label, $input_name, $input_value)
        ['BQS + Detective Survey (221B) + Expediting – Most popular', 'type_of_attendance', 'BQS + Detective Survey (221B) + Expediting – Most popular'],
        ['Bunker Quantity Survey (BQS)', 'type_of_attendance', 'Bunker Quantity Survey (BQS)'],
        ['Bunker Detective Survey (221B)', 'type_of_attendance', 'Bunker Detective Survey (221B)'],
        ['Bunker Expediting (Quick Turn Around)', 'type_of_attendance', 'Bunker Expediting (Quick Turn Around)'],
        ['Bunker Investigative Survey', 'type_of_attendance', 'Bunker Investigative Survey'],
        ['On-Off Hire Bunker ROB Survey', 'type_of_attendance', 'On-Off Hire Bunker ROB Survey'],
        ['On-Off Hire Vessel Condition Survey (Bulk & Containers)', 'type_of_attendance', 'On-Off Hire Vessel Condition Survey (Bulk & Containers)'],
        ['BQS, 221B & Expediting + Port Agency (Bunker Call) – Singapore Only', 'type_of_attendance', 'BQS, 221B & Expediting + Port Agency (Bunker Call) – Singapore Only'],
        ['Bunker Sample Analysis', 'type_of_attendance', 'Bunker Sample Analysis'],
        ['Lab Witnessing of Bunker Samples', 'type_of_attendance', 'Lab Witnessing of Bunker Samples'],
    );

    $vessel_information_fields = array(
        // ($type, $input_name, $value, $placeholder)
        ['text', 'vessel_information_type', '', 'Type', false],
        ['text', 'vessel_information_dwt', '', 'DWT', false],
        ['date', 'vessel_information_eta', '', 'ETA', false],
    );

    $requester_info_fields = array(
        ['text', 'requester_information_name', '', 'Name', true],
        ['text', 'requester_information_company', '', 'Company', true],
        ['text', 'requester_information_contact_no', '', 'Contact No.', true],
        ['text', 'requester_information_email', '', 'Email', true],
    );

    $loaction_fields = array(
        // ($label, $input_name, $input_value)
        ['Singapore Anchorage', 'location', 'Singapore Anchorage'],
        ['Singapore Eastern OPL (EOPL)', 'location', 'Singapore Eastern OPL (EOPL)'],
        ['Pasir Gudang, Pengerang, Malaysia', 'location', 'Pasir Gudang, Pengerang, Malaysia'],
        ['Tanjung Pelepas, Kukup, Malaysia', 'location', 'Tanjung Pelepas, Kukup, Malaysia'],
        ['Malacca, Malaysia', 'location', 'Malacca, Malaysia'],
        ['Port Klang, Malaysia', 'location', 'Port Klang, Malaysia'],
        ['Ports in India', 'location', 'Ports in India'],
    );
    ob_start();
?>
    <form method="post" class="custom-enquiry-form">
        <?php wp_nonce_field('submit_enquiry', 'enquiry_nonce'); ?>
        <input type="hidden" name="form_type" id="form_type" value="enquiry_form">
        <!-- First Section -->
        <div class="mb-4">
            <div class="form-row mb-0">
                <div class="form-col form-col-6 flex-center">
                    <h5 class="form-section-title">Type of Attendance</h5>
                </div>
            </div>

            <?php
            foreach ($type_of_attendance_fields as $index => $field_item) {
                echo render_checkbox($field_item, $index);
            }
            ?>
        </div>
        <!-- End First Section -->

        <!-- Start Second Section -->
        <div>
            <div class="form-row">
                <div class="form-col form-col-6">
                    <h5 class="form-section-title">Vessel Information</h5>
                    <div class="w-100">
                        <?php
                        foreach ($vessel_information_fields as $index => $item) {
                            render_input_text($item, $index);
                        }
                        ?>
                    </div>
                </div>
                <div class="form-col form-col-6">
                    <h5 class="form-section-title">
                        Requester's Information
                    </h5>
                    <div class="w-100">
                        <?php
                        foreach ($requester_info_fields as $index => $field) {
                            render_input_text($field, $index);
                        }
                        ?>
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
                    <?php
                    foreach ($loaction_fields as $i => $field) {
                        echo render_checkbox($field, $i);
                        if (($i + 1) % 3 === 0 && $i + 1 < count($loaction_fields)) {
                            echo '</div><div class="form-col form-col-4">';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="form-row">

            <div class="form-col form-col-12">
                <h5 class="form-section-title">
                    Any additional info or requests
                </h5>
                <textarea name="additional_requests" id="additional_requests"></textarea>
            </div>
        </div>

        <!-- End Second Section -->
        <div class="custom-form-group">
            <button class="submit-button" name="" type="submit">Send Enquiry</button>
        </div>

    </form>
<?php
    return ob_get_clean();
}

add_shortcode('form_bunker_survey_services', 'render_form_bunker_survey_services');

function create_slug($string)
{
    $slug = strtolower($string);
    $slug = preg_replace('~[^\pL\d]+~u', '_', $slug);
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);
    $slug = preg_replace('~[^-\w]+~', '', $slug);
    $slug = trim($slug, '-');
    $slug = preg_replace('~-+~', '_', $slug);
    // Return 'n-a' if empty
    return $slug ?: 'n_a';
}

function render_tank_scope($tank_name, $index)
{
    $tank_slug = create_slug($tank_name);
    $checkbox_id = 'cb_' . $tank_slug . '_' . $index;
    ob_start();
?>
    <div class="form-row">
        <div class="form-col form-col-2">
            <h6 class="fw-bold small show-on-mobile">Type Of Tanks</h6>
            <div class="flex-center">
                <input class="custom-checkbox" type="checkbox" id="<?php echo esc_attr($checkbox_id); ?>" name="type_of_tanks[]" value="<?php echo esc_attr($tank_name); ?>">
                <label for="<?php echo esc_attr($checkbox_id); ?>"><?php echo esc_html($tank_name); ?></label>
            </div>
        </div>
        <div class="form-col form-col-2">
            <h6 class="fw-bold small show-on-mobile"><?php echo $tank_name ?> - No. Of Tanks</h6>
            <input class="custom-input" type="text" name="no_of_tank_<?php echo $tank_slug ?>">
        </div>
        <div class="form-col form-col-3 form-row mx-0 px-0">
            <div class="form-col form-col-sm-4 text-center">
                <h6 class="fw-bold small show-on-mobile">Full Tank</h6>
                <input class="custom-radio" type="radio" name="clean_type_<?php echo $tank_slug ?>" value="Full Tank">
            </div>
            <div class="form-col form-col-sm-4 text-center">
                <h6 class="fw-bold small show-on-mobile">Bottom Only</h6>
                <input class="custom-radio" type="radio" name="clean_type_<?php echo $tank_slug ?>" value="Bottom Only">
            </div>
            <div class="form-col form-col-sm-4 text-center">
                <h6 class="fw-bold small show-on-mobile">Bottom for Hot Work</h6>
                <input class="custom-radio" type="radio" name="clean_type_<?php echo $tank_slug ?>" value="Bottom for Hot Work">
            </div>
        </div>
        <div class="form-col form-col-2">
            <h6 class="fw-bold small show-on-mobile"> <?php echo $tank_name ?> - Est. Sludge (MT) </h6>
            <input class="custom-input" type="text" name="est_sludge_<?php echo $tank_slug ?>">
        </div>
        <div class="form-col form-col-3">
            <h6 class="fw-bold small show-on-mobile"><?php echo $tank_name ?> - Purpose of Cleaning </br>(Change of Grade, Hot Work etc.)</h6>
            <input class="custom-input" type="text" name="purpose_clean_<?php echo $tank_slug ?>">
        </div>
    </div>
<?php
    return ob_get_clean();
}

function render_form_tank_cleaning()
{
    $type_of_tanks = array(
        // ($label, $input_name, $input_value)
        'Cargo Tanks',
        'Bunker Tanks',
        'Ballast Tanks',
        'Other Tanks',
    );

    $vessel_information_fields = array(
        // ($type, $input_name, $value, $placeholder)
        ['text', 'vessel_information_type', '', 'Type', false],
        ['text', 'vessel_information_dwt', '', 'DWT', false],
        ['date', 'vessel_information_eta', '', 'ETA', false],
        ['text', 'vessel_information_location', 'Singapore', '', false],
    );

    $requester_info_fields = array(
        ['text', 'requester_information_name', '', 'Name', true],
        ['text', 'requester_information_company', '', 'Company', true],
        ['text', 'requester_information_contact_no', '', 'Contact No.', true],
        ['text', 'requester_information_email', '', 'Email', true],
    );

    $requirement_fields = array(
        // ($label, $input_name, $input_value)
        ['Tank Cleaners (Manual Tank Cleaners)', 'requirements', 'Tank Cleaners (Manual Tank Cleaners)'],
        ['Rope Access (High Area Hanging Cleaners)', 'requirements', 'Rope Access (High Area Hanging Cleaners)'],
        ['Equipment (Compressor, Fans etc.)', 'requirements', 'Equipment (Compressor, Fans etc.)'],
        ['Materials (Rags, Jumbo Bags, Shovels etc.)', 'requirements', 'Materials (Rags, Jumbo Bags, Shovels etc.)'],
        ['Chemicals (Tank Cleaning Chemicals)', 'requirements', 'Chemicals (Tank Cleaning Chemicals)'],
        ['Sea Transport (Harbou Boats)', 'requirements', 'Sea Transport (Harbou Boats)'],
        ['Port Agency (Inward-Outward Clearance, Permits)', 'requirements', 'Port Agency (Inward-Outward Clearance, Permits)'],
    );
    ob_start();
?>
    <form method="post" class="custom-enquiry-form">
        <?php wp_nonce_field('submit_enquiry', 'enquiry_nonce'); ?>
        <input type="hidden" name="form_type" id="form_type" value="enquiry_form">
        <!-- First Section -->
        <div>
            <h5 class="form-section-title">
                Vessel Information
            </h5>
            <div class="form-row mb-0">
                <div class="form-col form-col-3">
                    <?php
                    foreach ($vessel_information_fields as $i => $field) {
                        echo render_input_text($field, $i);
                        if ($i + 1 < count($vessel_information_fields)) {
                            echo '</div><div class="form-col form-col-3">';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <!-- End First Section -->

        <!-- Start Section -->
        <div>
            <h5 class="form-section-title">
                Scope of Tank Cleaning and Demucking
            </h5>
            <div class="form-row hide-on-mobile">
                <div class="form-col form-col-2">
                    <h6 class="fw-bold small">Type Of Tanks</h6>
                </div>
                <div class="form-col form-col-2">
                    <h6 class="fw-bold small">No. Of Tanks</h6>
                </div>
                <div class="form-col form-col-3 form-row mx-0 px-0 mb-0">
                    <div class="form-col form-col-4 text-center">
                        <h6 class="fw-bold small">Full Tank</h6>
                    </div>
                    <div class="form-col form-col-4 text-center">
                        <h6 class="fw-bold small">Bottom Only</h6>
                    </div>
                    <div class="form-col form-col-4 text-center">
                        <h6 class="fw-bold small">Bottom for Hot Work</h6>
                    </div>
                </div>
                <div class="form-col form-col-2 text-center">
                    <h6 class="fw-bold small">Est. Sludge (MT)</h6>
                </div>
                <div class="form-col form-col-3 text-center">
                    <h6 class="fw-bold small">Purpose of Cleaning </br>(Change of Grade, Hot Work etc.)</h6>
                </div>
            </div>
            <?php
            foreach ($type_of_tanks as $index => $tank) {
                echo render_tank_scope($tank, $index);
            }
            ?>
        </div>
        <!-- End Section -->

        <!-- start section -->
        <div>
            <h5 class="form-section-title">
                Requirements
            </h5>
            <div class="form-row">
                <div class="form-col form-col-6">
                    <?php
                    foreach ($requirement_fields as $i => $field) {
                        echo render_checkbox($field, $i);
                        if (($i + 1) % 4 === 0 && $i + 1 < count($requirement_fields)) {
                            echo '</div><div class="form-col form-col-6">';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <!-- end sectionn -->

        <!-- Start Second Section -->
        <div>
            <div class="form-row">
                <div class="form-col form-col-6">
                    <h5 class="form-section-title">
                        Requester's Information
                    </h5>
                    <div class="w-100">
                        <?php
                        foreach ($requester_info_fields as $index => $field) {
                            render_input_text($field, $index);
                        }
                        ?>
                    </div>
                </div>

                <div class="form-col form-col-6">
                    <h5 class="form-section-title">
                        Any additional info or requests
                    </h5>
                    <textarea class="custom-textarea" name="additional_requests" id="additional_requests"></textarea>
                </div>
            </div>
        </div>
        <!-- End Second Section -->

        <div class="custom-form-group">
            <button class="submit-button" name="" type="submit">Send Enquiry</button>
        </div>

    </form>
<?php
    return ob_get_clean();
}

add_shortcode('form_tank_cleaning', 'render_form_tank_cleaning');


function render_hold_cleaning_scope($tank_name, $index)
{
    $tank_slug = create_slug($tank_name);
    $checkbox_id = 'cb_' . $tank_slug . '_' . $index;
    ob_start();
?>
    <div class="form-row">
        <div class="form-col form-col-2">
            <h6 class="fw-bold small show-on-mobile">No. Of Holds</h6>
            <input class="custom-input" type="text" name="no_of_hold_<?php echo $tank_slug ?>">
        </div>
        <div class="form-col form-col-2">
            <h6 class="fw-bold small show-on-mobile"><?php echo $tank_name ?> - Last Cargo</h6>
            <input class="custom-input" type="text" name="last_cargo_<?php echo $tank_slug ?>">
        </div>
        <div class="form-col form-col-3 form-row mx-0 px-0">
            <div class="form-col form-col-sm-4 text-center">
                <h6 class="fw-bold small show-on-mobile">Wash Only</h6>
                <input class="custom-radio" type="radio" name="clean_type_<?php echo $tank_slug ?>" value="Wash Only">
            </div>
            <div class="form-col form-col-sm-4 text-center">
                <h6 class="fw-bold small show-on-mobile">Full Clean & Pain</h6>
                <input class="custom-radio" type="radio" name="clean_type_<?php echo $tank_slug ?>" value="Full Clean & Pain">
            </div>
            <div class="form-col form-col-sm-4 text-center">
                <h6 class="fw-bold small show-on-mobile">Bottom Only</h6>
                <input class="custom-radio" type="radio" name="clean_type_<?php echo $tank_slug ?>" value="Bottom Only">
            </div>
        </div>
        <div class="form-col form-col-5">
            <h6 class="fw-bold small show-on-mobile"><?php echo $tank_name ?> - Specific Cleaning Requirement </br> (Wash Only / Wash, Clean & Paint etc.)</h6>
            <input class="custom-input" type="text" name="specific_clean_reqs_<?php echo $tank_slug ?>">
        </div>
    </div>
<?php
    return ob_get_clean();
}

function render_form_cargo_hold_cleaning()
{
    $type_of_tanks = array(
        // ($label, $input_name, $input_value)
        'Cargo Tanks',
        'Bunker Tanks',
        'Ballast Tanks',
        'Other Tanks',
    );

    $vessel_information_fields = array(
        // ($type, $input_name, $value, $placeholder)
        ['text', 'vessel_information_type', '', 'Type', false],
        ['text', 'vessel_information_dwt', '', 'DWT', false],
        ['date', 'vessel_information_eta', '', 'ETA', false],
        ['text', 'vessel_information_location', 'Singapore', '', false],
    );

    $requester_info_fields = array(
        ['text', 'requester_information_name', '', 'Name', true],
        ['text', 'requester_information_company', '', 'Company', true],
        ['text', 'requester_information_contact_no', '', 'Contact No.', true],
        ['text', 'requester_information_email', '', 'Email', true],
    );

    $requirement_fields = array(
        // ($label, $input_name, $input_value)
        ['Hold Cleaners (Manual Hold Cleaners)', 'requirements', 'Hold Cleaners (Manual Hold Cleaners)'],
        ['Cherry Pickers (For full height cleaning)', 'requirements', 'Cherry Pickers (For full height cleaning)'],
        ['Equipment (Compressor, HP Wash Machine etc.)', 'requirements', 'Equipment (Compressor, HP Wash Machine etc.)'],
        ['Materials (Consumables etc.)', 'requirements', 'Materials (Consumables etc.)'],
        ['Chemicals (Tank Cleaning Chemicals)', 'requirements', 'Chemicals (Tank Cleaning Chemicals)'],
        ['Paints', 'requirements', 'Paints'],
        ['Sea Transport (Harbou Boats)', 'requirements', 'Sea Transport (Harbou Boats)'],
        ['Port Agency (Inward-Outward Clearance, Permits)', 'requirements', 'Port Agency (Inward-Outward Clearance, Permits)'],
    );
    ob_start();
?>
    <form method="post" class="custom-enquiry-form">
        <?php wp_nonce_field('submit_enquiry', 'enquiry_nonce'); ?>
        <input type="hidden" name="form_type" id="form_type" value="enquiry_form">
        <!-- First Section -->
        <div>
            <h5 class="form-section-title">
                Vessel Information
            </h5>
            <div class="form-row mb-0">
                <div class="form-col form-col-3">
                    <?php
                    foreach ($vessel_information_fields as $i => $field) {
                        echo render_input_text($field, $i);
                        if ($i + 1 < count($vessel_information_fields)) {
                            echo '</div><div class="form-col form-col-3">';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <!-- End First Section -->

        <!-- Start Section -->
        <div>
            <h5 class="form-section-title">
                Scope of Cargo Hold Cleaning
            </h5>
            <div class="form-row hide-on-mobile">
                <div class="form-col form-col-2">
                    <h6 class="fw-bold small">No. of Holds</h6>
                </div>
                <div class="form-col form-col-2">
                    <h6 class="fw-bold small">Last Cargo</h6>
                </div>
                <div class="form-col form-col-3 form-row mx-0 px-0 mb-0">
                    <div class="form-col form-col-4 text-center">
                        <h6 class="fw-bold small">Wash Only</h6>
                    </div>
                    <div class="form-col form-col-4 text-center">
                        <h6 class="fw-bold small">Full Clean & Paint</h6>
                    </div>
                    <div class="form-col form-col-4 text-center">
                        <h6 class="fw-bold small">Bottom Only</h6>
                    </div>
                </div>
                <div class="form-col form-col-5 text-center">
                    <h6 class="fw-bold small">Specific Cleaning Requirement </br> (Wash Only / Wash, Clean & Paint etc.)</h6>
                </div>
            </div>
            <?php
            foreach ($type_of_tanks as $index => $tank) {
                echo render_hold_cleaning_scope($tank, $index);
            }
            ?>
        </div>
        <!-- End Section -->

        <!-- start section -->
        <div>
            <h5 class="form-section-title">
                Requirements
            </h5>
            <div class="form-row">
                <div class="form-col form-col-6">
                    <?php
                    foreach ($requirement_fields as $i => $field) {
                        echo render_checkbox($field, $i);
                        if (($i + 1) % 4 === 0 && $i + 1 < count($requirement_fields)) {
                            echo '</div><div class="form-col form-col-6">';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <!-- end sectionn -->

        <!-- Start Second Section -->
        <div>
            <div class="form-row">
                <div class="form-col form-col-6">
                    <h5 class="form-section-title">
                        Requester's Information
                    </h5>
                    <div class="w-100">
                        <?php
                        foreach ($requester_info_fields as $index => $field) {
                            render_input_text($field, $index);
                        }
                        ?>
                    </div>
                </div>

                <div class="form-col form-col-6">
                    <h5 class="form-section-title">
                        Any additional info or requests
                    </h5>
                    <textarea class="custom-textarea" name="additional_requests" id="additional_requests"></textarea>
                </div>
            </div>
        </div>
        <!-- End Second Section -->

        <div class="custom-form-group">
            <button class="submit-button" name="" type="submit">Send Enquiry</button>
        </div>

    </form>
<?php
    return ob_get_clean();
}

add_shortcode('form_cargo_hold_cleaning', 'render_form_cargo_hold_cleaning');


function render_form_under_water_boat()
{
    $type_of_attendance_fields = array(
        // ($label, $input_name, $input_value)
        ['Underwater Hull Inspection', 'type_of_underwater_service', 'Underwater Hull Inspection'],
        ['Underwater Hull Cleaning', 'type_of_underwater_service', 'Underwater Hull Cleaning'],
        ['Propeller Polishing', 'type_of_underwater_service', 'Propeller Polishing'],
        ['Removal of Entanglement', 'type_of_underwater_service', 'Removal of Entanglement'],
        ['Hydro-blasting of Bow Thruster, Rope Guard, Sea-chest etc.', 'type_of_underwater_service', 'Hydro-blasting of Bow Thruster, Rope Guard, Sea-chest etc.'],
        ['Installation or dismantling Echo Sounder, Speed Log or Anodes', 'type_of_underwater_service', 'Installation or dismantling Echo Sounder, Speed Log or Anodes'],
    );

    $vessel_information_fields = array(
        // ($type, $input_name, $value, $placeholder)
        ['text', 'vessel_information_type', '', 'Type', false],
        ['text', 'vessel_information_grt', '', 'GRT', false],
        ['text', 'vessel_information_dwt', '', 'DWT', false],
        ['date', 'vessel_information_eta', '', 'ETA', false],
    );

    $requester_info_fields = array(
        ['text', 'requester_information_name', '', 'Name', true],
        ['text', 'requester_information_company', '', 'Company', true],
        ['text', 'requester_information_contact_no', '', 'Contact No.', true],
        ['text', 'requester_information_email', '', 'Email', true],
    );

    $loaction_fields = array(
        // ($label, $input_name, $input_value)
        ['Singapore Anchorage', 'supply_boat_requirements', 'Singapore Anchorage'],
        ['Singapore Eastern OPL (EOPL)', 'supply_boat_requirements', 'Singapore Eastern OPL (EOPL)'],
    );
    $location_default = ['text', 'vessel_information_location', 'Singapore', '', false];
    ob_start();
?>
    <form method="post" class="custom-enquiry-form">
        <?php wp_nonce_field('submit_enquiry', 'enquiry_nonce'); ?>
        <input type="hidden" name="form_type" id="form_type" value="enquiry_form">
        <!-- First Section -->
        <div class="mb-3">
            <div>
                <h5 class="form-section-title">Type of Underwater Service Required</h5>
            </div>
            <div class="form-row mb-0">
                <div class="form-col form-col-3">
                    <?php
                    echo render_input_text($location_default, 1, true);
                    ?>
                </div>
            </div>
            <?php
            foreach ($type_of_attendance_fields as $index => $field_item) {
                echo render_checkbox($field_item, $index);
            }
            ?>

        </div>

        <!-- End First Section -->

        <!-- Start Second Section -->
        <div>
            <div class="form-row">
                <div class="form-col form-col-6">
                    <h5 class="form-section-title">Vessel Information</h5>
                    <div class="w-100">
                        <?php
                        foreach ($vessel_information_fields as $index => $item) {
                            render_input_text($item, $index);
                        }
                        ?>
                    </div>
                </div>
                <div class="form-col form-col-6">
                    <h5 class="form-section-title">
                        Requester's Information
                    </h5>
                    <div class="w-100">
                        <?php
                        foreach ($requester_info_fields as $index => $field) {
                            render_input_text($field, $index);
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <h5 class="form-section-title">
                Supply Boat Requirement
            </h5>
            <div class="form-row">
                <div class="form-col form-col-4">
                    <?php
                    foreach ($loaction_fields as $i => $field) {
                        echo render_checkbox($field, $i);
                        echo '</div><div class="form-col form-col-4">';
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="form-row">

            <div class="form-col form-col-12">
                <h5 class="form-section-title">
                    Any additional info or requests
                </h5>
                <textarea name="additional_requests" id="additional_requests"></textarea>
            </div>
        </div>

        <!-- End Second Section -->
        <div class="custom-form-group">
            <button class="submit-button" name="" type="submit">Send Enquiry</button>
        </div>


    </form>
<?php
    return ob_get_clean();
}

add_shortcode('form_under_water_boat', 'render_form_under_water_boat');
