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
        } else {
            $('#preview_description').val(fields.description);
        }

        // Dates & Times
        $('#preview_event_date').val(fields.event_date);
        $('#preview_event_time').val(fields.event_time);
        $('#preview_event_end_date').val(fields.event_end_date);
        $('#preview_event_end_time').val(fields.event_end_time);
        $('#ke-raw-date-suggestion').text(fields.raw_date_text ? 'Source raw date: ' + fields.raw_date_text : '');

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
            $('#ke-venue-status').html('<span style="color:green;">✓ Matched existing venue.</span>');
        } else {
            $('#preview_venue_id').val('');
            $('#ke-venue-status').html('<span style="color:orange;">⚠ Venue not found (will be created if enabled).</span>');
        }

        // Defaults from Fetch Form
        const defaultCat = fetchForm.find('select[name="default_category_id"]').val();
        if (defaultCat) $('#preview_category_id').val(defaultCat);
        
        const defaultCity = fetchForm.find('select[name="default_city_id"]').val();
        if (defaultCity) $('#preview_city_id').val(defaultCity);

        // Parser Meta
        $('#ke-parser-name').text(data.parser_name);
        $('#ke-parser-confidence').text(data.parser_confidence);

        // Duplicates
        const duplicateNotice = $('#ke-duplicate-notice');
        duplicateNotice.hide();
        $('#ke_import_action').val('create');
        $('#ke_existing_post_id').val('');

        if (duplicates.exact.length > 0) {
            const dup = duplicates.exact[0];
            duplicateNotice.html(`
                <p><strong>⚠ EXACT DUPLICATE DETECTED:</strong> This event already exists based on source URL.</p>
                <p>Existing Event: <a href="${dup.guid}" target="_blank">View Post</a></p>
                <button type="button" class="button ke-update-btn" data-id="${dup.ID}">Update Existing Event Instead</button>
            `).show();
        } else if (duplicates.possible.length > 0) {
            const dup = duplicates.possible[0];
            duplicateNotice.html(`
                <p><strong>ℹ POSSIBLE DUPLICATE DETECTED:</strong> An event with same title/date/venue found.</p>
                <p>Possible Match: <a href="${dup.guid}" target="_blank">View Post</a></p>
                <button type="button" class="button ke-update-btn" data-id="${dup.ID}">Update This Post</button>
            `).show();
        }

        // Finalize Transition
        previewWrapper.fadeIn();
        $('html, body').animate({ scrollTop: previewWrapper.offset().top - 50 }, 500);
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
