jQuery(document).ready(function($) {
    const fetchForm = $('#ke-fetch-preview-form');
    const fetchBtn = $('#ke-fetch-btn');
    const fetchSpinner = $('#ke-fetch-spinner');
    const errorNotice = $('#ke-import-error');
    const previewWrapper = $('#ke-import-preview-wrapper');

    /**
     * Handle Governorate -> City Change (Generic)
     */
    $(document).on('change', '.ke-governorate-select, #preview_governorate_id', function() {
        const govId = $(this).val();
        const container = $(this).closest('.ke-card-body, .ke-admin-form, .ke-main-col');
        const citySelect = container.find('.ke-city-select, #preview_city_id');
        
        if (!citySelect.length) return;

        citySelect.find('option').not(':first').remove();
        
        if (govId && typeof keEgyptData !== 'undefined' && keEgyptData[govId]) {
            const cities = keEgyptData[govId].cities;
            cities.forEach(city => {
                citySelect.append(`<option value="${city.id}">${city.name}</option>`);
            });
        }
    });

    // Handle Area population (Removed Area logic from here as well)

    /**
     * Venue Mode Toggle
     */
    $(document).on('change', 'input[name="venue_mode"]', function() {
        const mode = $(this).val();
        $('.ke-venue-mode-content').hide();
        $(`#ke-venue-mode-${mode}`).fadeIn();
    });

    /**
     * Fetch Audit Action
     */
    fetchForm.on('submit', function(e) {
        e.preventDefault();
        
        // Reset UI
        errorNotice.hide().text('');
        previewWrapper.hide();
        fetchBtn.prop('disabled', true);
        fetchSpinner.addClass('is-active');

        const formData = new FormData(this);
        formData.append('action', 'ke_fetch_event_preview');
        formData.append('nonce', $('#ke_import_nonce').val());

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: Object.fromEntries(formData),
            success: function(response) {
                if (response.success) {
                    populatePreview(response.data);
                } else {
                    errorNotice.fadeIn().text(response.data.message || 'An unknown error occurred.');
                    $('html, body').animate({ scrollTop: errorNotice.offset().top - 100 }, 500);
                }
            },
            error: function() {
                errorNotice.fadeIn().text('Connection error. Please try again.');
            },
            complete: function() {
                fetchBtn.prop('disabled', false);
                fetchSpinner.removeClass('is-active');
            }
        });
    });

    /**
     * Fill the preview form with audited data
     */
    function populatePreview(payload) {
        const { data, duplicates, matched_venue_id } = payload;
        const fields = data.fields;

        // Core Meta
        $('#preview_title').val(fields.title);
        $('#preview_source_url').val(data.source_url);
        $('#preview_canonical_url').val(data.canonical_url);
        $('#preview_source_name').val(data.source_name);
        $('#preview_parser_name').val(data.parser_name);
        $('#preview_parser_confidence_field').val(data.parser_confidence);
        $('#preview_raw_date_text').val(fields.raw_date_text);
        $('#preview_raw_location_text').val(fields.raw_location_text);
        $('#preview_image_url').val(fields.image_url);
        $('#preview_excerpt').val(fields.description.substring(0, 160));
        
        // Description (Editor Support)
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('preview_description')) {
            tinyMCE.get('preview_description').setContent(fields.description);
        } else {
            $('#preview_description').val(fields.description);
        }

        // Timing
        $('#preview_event_date').val(fields.event_date);
        $('#preview_event_time').val(fields.event_time);
        $('#preview_event_end_date').val(fields.event_end_date);
        $('#preview_event_end_time').val(fields.event_end_time);
        $('#ke-date-source-alert').html(fields.raw_date_text ? '<strong>Detected Date Source:</strong> ' + fields.raw_date_text : '');

        // Image Handling
        const imgPreview = $('#ke-preview-img-src');
        const imgPlaceholder = $('#ke-no-image');
        if (fields.image_url) {
            imgPreview.attr('src', fields.image_url).show();
            imgPlaceholder.hide();
        } else {
            imgPreview.hide();
            imgPlaceholder.show();
        }

        // Venue Assignment Logic
        const venueIdSelect = $('#preview_venue_id');
        const venueMatchLabel = $('#ke-venue-match-status');
        
        if (matched_venue_id) {
            $('input[name="venue_mode"][value="existing"]').prop('checked', true).trigger('change');
            venueIdSelect.val(matched_venue_id);
            venueMatchLabel.html('<div class="ke-info-note" style="background:#dcfce7; color:#166534; border:none; margin-top:10px;">✓ Automatic Match Found: ' + fields.venue_name + '</div>');
        } else {
            $('input[name="venue_mode"][value="new"]').prop('checked', true).trigger('change');
            $('#preview_venue_name').val(fields.venue_name);
            $('#preview_address').val(fields.address);
            $('#preview_phone').val(fields.phone);
            $('#preview_official_url').val(fields.official_url);
            venueMatchLabel.html('');
        }

        // Category
        if (fields.category) {
            $('#preview_category_id').val(fields.category); 
        }

        // Smart Location Pre-fill
        const { detected_location } = payload;
        const locMatchLabel = $('#ke-location-match-status');
        
        if (detected_location && detected_location.gov_id) {
            // 1. Set Governorate
            $('#preview_governorate_id').val(detected_location.gov_id).trigger('change');
            
            // 2. Set City (Wait a bit for the trigger('change') above to populate city options)
            if (detected_location.city_id) {
                setTimeout(() => {
                    $('#preview_city_id').val(detected_location.city_id);
                }, 100);
            }

            // 3. Status Label
            if (detected_location.source === 'existing_venue') {
                locMatchLabel.html('<div class="ke-info-note" style="background:#eff6ff; color:#1e40af; border:none; margin-top:10px;">ℹ Location inherited from existing venue.</div>');
            } else if (detected_location.confidence >= 80) {
                locMatchLabel.html('<div class="ke-info-note" style="background:#dcfce7; color:#166534; border:none; margin-top:10px;">✓ Location auto-detected from address.</div>');
            } else {
                locMatchLabel.html('<div class="ke-info-note" style="background:#fef3c7; color:#92400e; border:none; margin-top:10px;">⚠ Location detected with low confidence. Please verify.</div>');
            }
        } else {
            locMatchLabel.html('<div class="ke-info-note" style="background:#fee2e2; color:#991d1d; border:none; margin-top:10px;">✕ Could not detect governorate/city from address.</div>');
        }

        // Diagnostics
        $('#ke-parser-name').text(data.parser_name);
        $('#ke-parser-confidence').text(data.parser_confidence + '%');
        $('#ke-confidence-fill').css({
            'width': data.parser_confidence + '%',
            'background': data.parser_confidence < 50 ? '#ef4444' : (data.parser_confidence < 80 ? '#f59e0b' : '#10b981')
        });

        // Warnings
        const warnList = $('#ke-parser-warnings').empty();
        if (data.warnings && data.warnings.length > 0) {
            data.warnings.forEach(msg => warnList.append(`<div class="ke-warning-item">⚠ ${msg}</div>`));
        }

        // Duplicate Notification
        const dupBadge = $('#ke-duplicate-notice').hide();
        $('#ke_import_action').val('create');
        $('#ke_existing_post_id').val('');

        if (duplicates.exact && duplicates.exact.length > 0) {
            dupBadge.html('EXACT DUPLICATE FOUND').css('background', '#ef4444').fadeIn();
            $('#ke_import_action').val('update');
            $('#ke_existing_post_id').val(duplicates.exact[0].ID);
            $('#ke-save-import-btn').text('Update Existing Event');
        } else if (duplicates.possible && duplicates.possible.length > 0) {
            dupBadge.html('SIMILAR EVENT FOUND').css('background', '#f59e0b').fadeIn();
        }

        // Final Show
        previewWrapper.fadeIn(600);
        $('html, body').animate({ scrollTop: previewWrapper.offset().top - 40 }, 800);
    }
});
