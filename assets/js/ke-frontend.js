/**
 * Kontentainment Events Frontend Phase 2
 * AJAX Filters, Load More, URL Sync
 */

document.addEventListener('DOMContentLoaded', function() {
    const archiveContainer = document.getElementById('ke-archive-container');
    if (!archiveContainer) return;

    const filterForm = document.getElementById('ke-filter-form');
    const archiveLoop = document.getElementById('ke-archive-loop');
    const loadMoreBtn = document.getElementById('ke-load-more');
    const resetBtn = document.getElementById('ke-reset-filters');
    const loadingOverlay = document.getElementById('ke-loading-overlay');
    const postType = archiveContainer.getAttribute('data-post-type');

    if (!filterForm || !archiveLoop) return;

    /**
     * State Management
     */
    let isLoading = false;

    /**
     * AJAX Filter Logic
     */
    async function filterArchive(isLoadMore = false) {
        if (isLoading) return;
        isLoading = true;

        if (!isLoadMore) {
            loadingOverlay.classList.add('is-active');
            archiveLoop.style.opacity = '0.5';
        } else {
            loadMoreBtn.classList.add('is-loading');
            loadMoreBtn.disabled = true;
        }

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        
        // Add post type and paged info
        params.append('action', 'ke_filter_archive');
        params.append('nonce', ke_ajax_obj.nonce);
        params.append('post_type', postType);

        if (isLoadMore) {
            const currentPage = parseInt(loadMoreBtn.getAttribute('data-current-page'));
            params.append('ke_paged', currentPage + 1);
        } else {
            params.append('ke_paged', 1);
        }

        try {
            const response = await fetch(`${ke_ajax_obj.ajax_url}?${params.toString()}`);
            const data = await response.json();

            if (data.success) {
                if (isLoadMore) {
                    // Append new items
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.data.html;
                    while (tempDiv.firstChild) {
                        archiveLoop.appendChild(tempDiv.firstChild);
                    }
                } else {
                    // Replace loop content
                    archiveLoop.innerHTML = data.data.html;
                    // Update URL
                    updateURL(params);
                }

                // Update pagination state
                updatePagination(data.data);
            }
        } catch (error) {
            console.error('KE Filters Error:', error);
        } finally {
            isLoading = false;
            loadingOverlay.classList.remove('is-active');
            archiveLoop.style.opacity = '1';
            loadMoreBtn.classList.remove('is-loading');
            loadMoreBtn.disabled = false;
        }
    }

    /**
     * Update URL parameters without reload
     */
    function updateURL(params) {
        const urlParams = new URLSearchParams();
        for (const [key, value] of params.entries()) {
            if (!['action', 'nonce', 'post_type', 'is_load_more'].includes(key) && value !== '') {
                urlParams.append(key, value);
            }
        }
        
        const newURL = `${window.location.pathname}?${urlParams.toString()}`;
        window.history.pushState({ path: newURL }, '', newURL);
    }

    /**
     * Handle Pagination UI
     */
    function updatePagination(data) {
        if (!loadMoreBtn) return;

        const { current_page, max_num_pages } = data;
        
        loadMoreBtn.setAttribute('data-current-page', current_page);
        loadMoreBtn.setAttribute('data-max-pages', max_num_pages);

        if (parseInt(current_page) >= parseInt(max_num_pages)) {
            loadMoreBtn.style.display = 'none';
        } else {
            loadMoreBtn.style.display = 'inline-block';
        }
    }

    /**
     * Event Listeners
     */
    
    // Form Inputs (Autosubmit)
    filterForm.addEventListener('change', (e) => {
        // Only autosubmit on selects and checkboxes
        if (e.target.tagName === 'SELECT' || e.target.type === 'checkbox') {
            filterArchive();
        }
    });

    // Form Submit
    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        filterArchive();
    });

    // Load More
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => {
            filterArchive(true);
        });
    }

    // Reset Filters
    const resetFilters = () => {
        filterForm.reset();
        // Clear all select values (sometimes form.reset doesn't trigger change)
        filterForm.querySelectorAll('select').forEach(select => select.value = '');
        filterForm.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
        filterForm.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        filterArchive();
    };

    if (resetBtn) {
        resetBtn.addEventListener('click', resetFilters);
    }

    // Handle Archive Empty State buttons (injected via loop)
    document.addEventListener('click', (e) => {
        if (e.target.id === 'ke-reset-filters') {
            resetFilters();
        }
    });

    // History Back/Forward
    window.addEventListener('popstate', () => {
        window.location.reload(); // Simplest sync for popstate in Phase 2
    });

    // Keyboard search debounce
    let searchTimeout;
    const searchInput = document.getElementById('ke_search');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterArchive();
            }, 500);
        });
    }
});
