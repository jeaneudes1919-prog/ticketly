import '@fontsource-variable/inter';
import '@fontsource-variable/space-grotesk';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

// Fenêtre de confirmation personnalisée (remplace confirm() du navigateur)
Alpine.store('confirm', {
    open: false, title: '', message: '', danger: true, label: 'Confirmer', form: null,
    ask(form, title, message, danger = true, label = 'Confirmer') {
        Object.assign(this, { form, title, message, danger, label, open: true });
    },
    accept() {
        this.open = false;
        this.form.submit();
    },
});

Alpine.data('ticketInbox', () => ({
    filtersOpen: false,
    desktop: window.matchMedia('(min-width: 1024px)').matches,
    loading: false,
    activeFilters: 0,
    request: null,
    searchTimer: null,
    media: null,
    onMediaChange: null,

    init() {
        this.activeFilters = this.countActive();
        this.media = window.matchMedia('(min-width: 1024px)');
        this.onMediaChange = (event) => {
            this.desktop = event.matches;
            if (event.matches) this.filtersOpen = false;
        };
        this.media.addEventListener('change', this.onMediaChange);
    },

    destroy() {
        this.media?.removeEventListener('change', this.onMediaChange);
        clearTimeout(this.searchTimer);
        this.request?.abort();
    },

    countActive() {
        const form = this.$refs.filters;
        if (!form) return 0;

        const data = new FormData(form);
        return ['q', 'status', 'priority', 'category_id', 'assignee_id', 'tag', 'period']
            .filter((key) => String(data.get(key) ?? '').trim() !== '').length;
    },

    buildUrl() {
        const form = this.$refs.filters;
        const params = new URLSearchParams();

        for (const [key, value] of new FormData(form).entries()) {
            if (String(value).trim() !== '') params.append(key, value);
        }

        const query = params.toString();
        return form.action + (query ? `?${query}` : '');
    },

    apply(url = null) {
        clearTimeout(this.searchTimer);
        this.searchTimer = null;

        const target = url || this.buildUrl();
        const controller = new AbortController();
        this.request?.abort();
        this.request = controller;
        this.loading = true;

        fetch(target, {
            headers: { 'X-Ticketly-Partial': 'results', Accept: 'text/html' },
            signal: controller.signal,
            credentials: 'same-origin',
        })
            .then((response) => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.text();
            })
            .then((html) => {
                this.$refs.results.innerHTML = html;
                window.history.replaceState({}, '', target);
                this.activeFilters = this.countActive();
                this.syncQuickLinks();
            })
            .catch((error) => {
                if (error.name !== 'AbortError') window.location.assign(target);
            })
            .finally(() => {
                if (this.request === controller) {
                    this.loading = false;
                    this.request = null;
                }
            });
    },

    onSearch() {
        this.activeFilters = this.countActive();
        clearTimeout(this.searchTimer);
        this.searchTimer = setTimeout(() => this.apply(), 400);
    },

    showResults() {
        this.filtersOpen = false;
        this.$nextTick(() => this.$refs.results?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
    },

    reset() {
        const form = this.$refs.filters;
        if (!form) return;

        form.querySelectorAll('input[name="q"], select').forEach((field) => {
            field.value = '';
        });

        this.filtersOpen = false;
        this.activeFilters = 0;
        this.apply();
    },

    handleResultClick(event) {
        const link = event.target.closest('a[href]');
        if (!link || !link.closest('[data-pagination]')) return;

        event.preventDefault();
        this.apply(link.href);
    },

    syncQuickLinks() {
        const form = this.$refs.filters;
        const quickViews = this.$refs.quickViews;
        if (!form || !quickViews) return;

        const params = new URLSearchParams();
        for (const [key, value] of new FormData(form).entries()) {
            if (String(value).trim() !== '') params.append(key, value);
        }

        quickViews.querySelectorAll('a[data-view]').forEach((link) => {
            const next = new URLSearchParams(params);
            next.set('view', link.dataset.view);
            link.href = `${form.action}?${next.toString()}`;
        });
    },
}));

Alpine.start();
