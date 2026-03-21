/**
 * Kontentainment Events Admin Scripts - Phase 3
 * Handle Import From URL AJAX logic
 */
jQuery(document).ready(function($) {
    const fetchForm = $('#ke-fetch-preview-form');
    const fetchBtn = $('#ke-fetch-btn');
    const fetchSpinner = $('#ke-fetch-spinner');
    const errorNotice = $('#ke-import-error');
    const previewWrapper = $('#ke-import-preview-wrapper');

    if (!fetchForm.length) return;

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
                    errorNotice.show().text(response.data.message || 'An unknown error occurred.');
                }
            },
            error: function() {
                errorNotice.show().text('Connection error. Please try again.');
            },
            complete: function() {
                fetchBtn.prop('disabled', false);
                fetchSpinner.removeClass('is-active');
            }
        });
    });

    /**
     * Fill the preview form with parsed data
     */
    function populatePreview(payload) {
        const { data, duplicates, matched_venue_id } = payload;
        const fields = data.fields;

        // Core Fields
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
        
        // Description (WP Editor)
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('preview_description')) {
            tinyMCE.get('preview_description').setContent(fields.description);
        } else if ($('#preview_description').length) {
            $('#preview_description').val(fields.description);
        }

        // Dates & Times
        $('#preview_event_date').val(fields.event_date);
        $('#preview_event_time').val(fields.event_time);
        $('#preview_event_end_date').val(fields.event_end_date);
        $('#preview_event_end_time').val(fields.event_end_time);
        $('#ke-raw-date-suggestion').text(fields.raw_date_text ? 'Source raw date string: ' + fields.raw_date_text : '');

        // Image
        if (fields.image_url) {
            $('#ke-preview-img-src').attr('src', fields.image_url).show();
            $('#ke-no-image').hide();
        } else {
            $('#ke-preview-img-src').hide();
            $('#ke-no-image').show();
        }

        // Venue
        $('#preview_venue_name').val(fields.venue_name);
        $('#preview_address').val(fields.address);
        $('#preview_phone').val(fields.phone);
        $('#preview_official_url').val(fields.official_url);
        $('#preview_organizer_name').val(fields.organizer_name);

        if (matched_venue_id) {
            $('#preview_venue_id').val(matched_venue_id);
            $('#ke-venue-status').html('<span style="color:#10b981; font-weight:600;">✓ Exact match found in library.</span>');
        } else {
            $('#preview_venue_id').val('');
            $('#ke-venue-status').html('<span style="color:#f59e0b; font-weight:600;">⚠ New venue (library entry will be created).</span>');
        }

        // Defaults from Fetch Form
        const defaultCat = fetchForm.find('select[name="default_category_id"]').val();
        if (defaultCat && !fields.category) $('#preview_category_id').val(defaultCat);
        
        const defaultCity = fetchForm.find('select[name="default_city_id"]').val();
        if (defaultCity && !fields.city) $('#preview_city_id').val(defaultCity);

        // Diagnostics
        $('#ke-parser-name').text(data.parser_name);
        $('#ke-parser-confidence').text(data.parser_confidence + '%');
        $('#ke-confidence-fill').css('width', data.parser_confidence + '%');
        
        // Change color based on confidence
        if (data.parser_confidence < 50) $('#ke-confidence-fill').css('background', '#ef4444');
        else if (data.parser_confidence < 80) $('#ke-confidence-fill').css('background', '#f59e0b');
        else $('#ke-confidence-fill').css('background', '#10b981');

        // Warnings List
        const warnList = $('#ke-parser-warnings');
        warnList.empty();
        if (data.warnings && data.warnings.length > 0) {
            data.warnings.forEach(msg => {
                warnList.append(`<div class="ke-warning-item">${msg}</div>`);
            });
        }

        // Duplicates
        const duplicateNotice = $('#ke-duplicate-notice');
        duplicateNotice.hide();
        $('#ke_import_action').val('create');
        $('#ke_existing_post_id').val('');

        if (duplicates.exact && duplicates.exact.length > 0) {
            const dup = duplicates.exact[0];
            duplicateNotice.html(`
                <strong>⚠ EXACT DUPLICATE:</strong> Already imported.
                <button type="button" class="button ke-update-btn button-small" data-id="${dup.ID}" style="margin-left:10px;">Switch to Update Mode</button>
            `).show();
        } else if (duplicates.possible && duplicates.possible.length > 0) {
            const dup = duplicates.possible[0];
            duplicateNotice.html(`
                <strong>ℹ SIMILAR FOUND:</strong> Title/Date match.
                <button type="button" class="button ke-update-btn button-small" data-id="${dup.ID}" style="margin-left:10px;">Update Match</button>
            `).show();
        }

        // Finalize Transition
        previewWrapper.fadeIn();
        $('html, body').animate({ scrollTop: previewWrapper.offset().top - 30 }, 500);
    }

    /**
     * Handle Update Choice
     */
    $(document).on('click', '.ke-update-btn', function() {
        const postId = $(this).data('id');
        $('#ke_import_action').val('update');
        $('#ke_existing_post_id').val(postId);
        $('#ke-save-import-btn').text('Update Existing Event');
        $(this).parent().css('background', '#dbeafe');
        alert('Action changed to UPDATE. Source fields will overwrite existing event data upon saving.');
    });
});
