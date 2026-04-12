/**
 * Global Carousel Initializer
 */
function initKECarousels() {
    if (typeof Swiper === 'undefined') return;

    const carousels = document.querySelectorAll('.ke-carousel-container:not(.ke-initialized)');
    carousels.forEach(container => {
        const swiperContainer = container.querySelector('.swiper');
        if (!swiperContainer) return;

        const settingsAttr = swiperContainer.getAttribute('data-swiper-settings');
        if (!settingsAttr) return;

        try {
            const settings = JSON.parse(settingsAttr);
            new Swiper(swiperContainer, settings);
            container.classList.add('ke-initialized');
        } catch (e) {
            console.error('KE Swiper Init Error:', e);
        }
    });
}

/**
 * Single Page Lazy Loading for Related Sections
 */
function initSingleLazyLoading() {
    const lazySections = document.querySelectorAll('.ke-lazy-section');
    if (!lazySections.length) return;

    if (typeof ke_ajax_obj === 'undefined') {
        console.error('KE AJAX Object not found');
        return;
    }

    lazySections.forEach(section => {
        const params = new URLSearchParams();
        params.append('action', 'ke_load_related');
        params.append('nonce', ke_ajax_obj.nonce);
        
        // Collect all data attributes
        Object.keys(section.dataset).forEach(key => {
            params.append(key, section.dataset[key]);
        });

        fetch(`${ke_ajax_obj.ajax_url}?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.html) {
                    section.innerHTML = data.data.html;
                    // Fade in effect
                    section.style.opacity = '0';
                    section.style.transition = 'opacity 0.5s ease-in';
                    setTimeout(() => section.style.opacity = '1', 10);
                    // Init carousels if any
                    initKECarousels();
                }
            })
            .catch(err => console.error('KE Lazy Section Error:', err));
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Core independent runners
    initKECarousels();
    initSingleLazyLoading();

    // 2. Archive-specific elements
    const archiveContainer = document.getElementById('ke-archive-container');
    const filterForm = document.getElementById('ke-filter-form');
    const archiveLoop = document.getElementById('ke-archive-loop');
    const loadMoreBtn = document.getElementById('ke-load-more');
    const resetBtn = document.getElementById('ke-reset-filters');
    const loadingOverlay = document.getElementById('ke-loading-overlay');
    const postType = archiveContainer ? archiveContainer.getAttribute('data-post-type') : 'event';

    let isLoading = false;

    /**
     * AJAX Filter Logic
     */
    async function filterArchive(isLoadMore = false) {
        if (!filterForm || !archiveLoop || isLoading) return;
        isLoading = true;

        if (!isLoadMore) {
            if (loadingOverlay) loadingOverlay.classList.add('is-active');
            archiveLoop.style.opacity = '0.5';
        } else {
            if (loadMoreBtn) {
                loadMoreBtn.classList.add('is-loading');
                loadMoreBtn.disabled = true;
            }
        }

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        
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
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.data.html;
                    while (tempDiv.firstChild) {
                        archiveLoop.appendChild(tempDiv.firstChild);
                    }
                } else {
                    archiveLoop.innerHTML = data.data.html;
                    updateURL(params);
                }

                initKECarousels();
                updatePagination(data.data);
            }
        } catch (error) {
            console.error('KE Filters Error:', error);
        } finally {
            isLoading = false;
            if (loadingOverlay) loadingOverlay.classList.remove('is-active');
            archiveLoop.style.opacity = '1';
            if (loadMoreBtn) {
                loadMoreBtn.classList.remove('is-loading');
                loadMoreBtn.disabled = false;
            }
        }
    }

    function updateURL(params) {
        const urlParams = new URLSearchParams();
        for (const [key, value] of params.entries()) {
            if (!['action', 'nonce', 'post_type'].includes(key) && value !== '') {
                urlParams.append(key, value);
            }
        }
        const newURL = `${window.location.pathname}?${urlParams.toString()}`;
        window.history.pushState({ path: newURL }, '', newURL);
    }

    function updatePagination(data) {
        if (!loadMoreBtn) return;
        const { current_page, max_num_pages } = data;
        loadMoreBtn.setAttribute('data-current-page', current_page);
        loadMoreBtn.setAttribute('data-max-pages', max_num_pages);
        loadMoreBtn.style.display = (parseInt(current_page) >= parseInt(max_num_pages)) ? 'none' : 'inline-block';
    }

    if (filterForm) {
        filterForm.addEventListener('change', (e) => {
            if (e.target.tagName === 'SELECT' || e.target.type === 'checkbox') filterArchive();
        });
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            filterArchive();
        });
    }

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => filterArchive(true));
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            if (filterForm) {
                filterForm.reset();
                filterArchive();
            }
        });
    }

    // Global Click Handlers
    document.addEventListener('click', (e) => {
        const navItem = e.target.closest('.ke-nav-item');
        if (navItem) {
            const rangeInput = document.getElementById('ke-input-range');
            const recInput = document.getElementById('ke-input-recommended');
            if (rangeInput) rangeInput.value = navItem.dataset.range || '';
            if (recInput) recInput.value = (navItem.dataset.meta === 'ke_recommended') ? '1' : '';
            filterArchive();
            return;
        }

        const pillItem = e.target.closest('.ke-pill-item');
        if (pillItem) {
            const cityInput = document.getElementById('ke-input-city');
            if (cityInput) cityInput.value = pillItem.dataset.city || '';
            filterArchive();
            return;
        }

        if (e.target.classList.contains('ke-show-more-btn')) {
            const block = e.target.closest('.ke-supporting-block, .ke-main-col');
            if (block) {
                block.querySelectorAll('.ke-hidden-item').forEach(item => {
                    item.style.display = 'block';
                    item.classList.remove('ke-hidden-item');
                });
                e.target.style.display = 'none';
            }
        }
    });

    const searchInput = document.getElementById('ke_search');
    if (searchInput) {
        let timeout;
        searchInput.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => filterArchive(), 500);
        });
    }
});
