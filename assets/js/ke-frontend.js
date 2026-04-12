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
 * Combined Single Page Lazy Loading
 * Replaces multiple slow requests with one fast request
 */
function initSingleCombinedLoading() {
    const loader = document.getElementById('ke-combined-related-loader');
    if (!loader) return;

    if (typeof ke_ajax_obj === 'undefined') return;

    const params = new URLSearchParams();
    params.append('action', 'ke_load_related_combined');
    params.append('nonce', ke_ajax_obj.nonce);
    
    // Pass context
    Object.keys(loader.dataset).forEach(key => {
        params.append(key, loader.dataset[key]);
    });

    fetch(`${ke_ajax_obj.ajax_url}?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data.html) {
                loader.innerHTML = data.data.html;
                initKECarousels();
            }
        })
        .catch(err => console.error('KE Combined Loader Error:', err));
}

document.addEventListener('DOMContentLoaded', function() {
    initKECarousels();
    initSingleCombinedLoading();

    // Archive Logic (Safe)
    const filterForm = document.getElementById('ke-filter-form');
    const archiveLoop = document.getElementById('ke-archive-loop');
    const loadMoreBtn = document.getElementById('ke-load-more');
    const loadingOverlay = document.getElementById('ke-loading-overlay');

    let isLoading = false;

    async function filterArchive(isLoadMore = false) {
        if (!filterForm || !archiveLoop || isLoading) return;
        isLoading = true;

        if (!isLoadMore) {
            if (loadingOverlay) loadingOverlay.classList.add('is-active');
            archiveLoop.style.opacity = '0.5';
        }

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        params.append('action', 'ke_filter_archive');
        params.append('nonce', ke_ajax_obj.nonce);
        
        try {
            const response = await fetch(`${ke_ajax_obj.ajax_url}?${params.toString()}`);
            const data = await response.json();
            if (data.success) {
                if (isLoadMore) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.data.html;
                    while (tempDiv.firstChild) archiveLoop.appendChild(tempDiv.firstChild);
                } else {
                    archiveLoop.innerHTML = data.data.html;
                }
                initKECarousels();
            }
        } finally {
            isLoading = false;
            if (loadingOverlay) loadingOverlay.classList.remove('is-active');
            archiveLoop.style.opacity = '1';
        }
    }

    if (filterForm) {
        filterForm.addEventListener('change', (e) => {
            if (e.target.tagName === 'SELECT' || e.target.type === 'checkbox') filterArchive();
        });
    }

    document.addEventListener('click', (e) => {
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
});
