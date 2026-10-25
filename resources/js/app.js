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

Alpine.start();
