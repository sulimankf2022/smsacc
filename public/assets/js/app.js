// Sidebar toggle
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('sidebarToggle');
    const wrapper = document.getElementById('wrapper');

    if (toggleBtn && wrapper) {
        toggleBtn.addEventListener('click', function () {
            wrapper.classList.toggle('sidebar-toggled');
        });
    }

    // Exchange rate auto-fill from currency select
    document.querySelectorAll('[data-currency-select]').forEach(function (sel) {
        sel.addEventListener('change', function () {
            const ratesEl = document.getElementById('currency-rates-data');
            if (!ratesEl) {
                console.warn('currency-rates-data element not found; exchange rate auto-fill unavailable.');
                return;
            }
            const rates = JSON.parse(ratesEl.textContent || '{}');
            const rateInput = document.querySelector('[data-exchange-rate]');
            if (rateInput && rates[this.value]) {
                rateInput.value = rates[this.value];
            } else if (rateInput) {
                rateInput.value = this.value === 'USD' ? '1.0' : '';
            }
        });
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
