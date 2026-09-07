window.catalogApp = function catalogApp(products) {
    return {
        search: '',
        activeCategory: 'all',
        products: products || [],
        categories: [
            { id: 'all', label: 'All' },
            { id: 'data', label: 'Data' },
            { id: 'sms', label: 'SMS' },
            { id: 'minutes', label: 'Minutes' },
        ],

        get filteredProducts() {
            const term = this.search.toLowerCase().trim();

            return this.products.filter((product) => {
                const matchesCategory = this.activeCategory === 'all' || product.type === this.activeCategory;
                const haystack = [
                    product.name,
                    product.type,
                    product.validity,
                    this.safaricomType(product.type),
                ].join(' ').toLowerCase();

                return matchesCategory && (!term || haystack.includes(term));
            });
        },

        resolveIcon(type) {
            return { data: '📶', sms: '💬', minutes: '📞' }[type] || '📦';
        },

        safaricomType(type) {
            return { data: 'Safaricom Data', sms: 'Safaricom SMS', minutes: 'Safaricom Minutes' }[type] || 'Safaricom';
        },

        validityLabel(validity) {
            return validity ? `Valid for ${validity}` : 'Valid for 24 hours';
        },
    };
};

window.checkoutApp = function checkoutApp(initialPhone) {
    return {
        phone: initialPhone || '',
        phoneError: '',
        submitting: false,
        isValid: false,

        init() {
            this.validatePhone();
        },

        validatePhone() {
            this.phoneError = '';
            const raw = this.phone.trim();

            if (!raw) {
                this.isValid = false;
                return;
            }

            const digits = raw.replace(/\D+/g, '');
            const isValid = /^(?:254[17]\d{8}|0[17]\d{8})$/.test(digits);

            if (!isValid) {
                this.phoneError = 'Enter a valid Kenyan mobile number.';
                this.isValid = false;
                return;
            }

            this.isValid = true;
        },

        submit(event) {
            this.validatePhone();

            if (!this.isValid || this.submitting) {
                event.preventDefault();
                return;
            }

            this.submitting = true;
        },
    };
};

window.waitingApp = function waitingApp(reference) {
    return {
        reference,
        timedOut: false,
        pollInterval: null,
        timeoutId: null,
        inFlight: false,

        init() {
            this.startPolling();
        },

        startPolling() {
            if (this.pollInterval) {
                return;
            }

            this.pollStatus();
            this.pollInterval = setInterval(() => this.pollStatus(), 2500);
            this.timeoutId = setTimeout(() => {
                this.timedOut = true;
                this.stopPolling();
            }, 90000);
        },

        stopPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }

            if (this.timeoutId) {
                clearTimeout(this.timeoutId);
                this.timeoutId = null;
            }
        },

        async pollStatus() {
            if (this.inFlight) {
                return;
            }

            this.inFlight = true;

            try {
                const response = await fetch(`/api/v1/orders/${this.reference}/status`);

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                const redirectStates = [
                    'paid',
                    'fulfillment_pending',
                    'fulfilled',
                    'fulfillment_failed',
                    'needs_attention',
                    'cancelled',
                ];

                if (data.status && data.status !== 'pending') {
                    this.stopPolling();
                    window.location.href = `/orders/${this.reference}`;
                    return;
                }

                if (redirectStates.includes(data.status)) {
                    this.stopPolling();
                    window.location.href = `/orders/${this.reference}`;
                }
            } catch (error) {
                // Keep polling; a network blip is not a payment failure.
            } finally {
                this.inFlight = false;
            }
        },

        destroy() {
            this.stopPolling();
        },
    };
};
