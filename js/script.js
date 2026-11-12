document.addEventListener('DOMContentLoaded', () => {
    initialiseComparisonForm();
    highlightWinningSpecs();
    initialiseSearchFilter();
    initialiseFormValidation();
});

function initialiseComparisonForm() {
    const comparisonForm = document.getElementById('comparison-form');
    if (!comparisonForm) {
        return;
    }
    const deviceSelects = comparisonForm.querySelectorAll('select');
    deviceSelects.forEach((deviceSelect) => {
        deviceSelect.addEventListener('change', () => {
            if (deviceSelect.value !== '') {
                comparisonForm.submit();
            }
        });
    });
}

function highlightWinningSpecs() {
    const numericSpecFields = ['ram_gb', 'storage_gb', 'battery_mah', 'price'];
    numericSpecFields.forEach((specField) => {
        const specCells = document.querySelectorAll(`td[data-field="${specField}"][data-value]`);
        if (specCells.length !== 2) {
            return;
        }
        const firstValue = parseFloat(specCells[0].dataset.value);
        const secondValue = parseFloat(specCells[1].dataset.value);
        if (Number.isNaN(firstValue) || Number.isNaN(secondValue) || firstValue === secondValue) {
            return;
        }
        const higherValueWins = specField !== 'price';
        const winnerCell = higherValueWins
            ? (firstValue > secondValue ? specCells[0] : specCells[1])
            : (firstValue < secondValue ? specCells[0] : specCells[1]);
        winnerCell.classList.add('spec-winner');
    });
}

function initialiseSearchFilter() {
    const searchInput = document.getElementById('device-search');
    if (!searchInput) {
        return;
    }
    const deviceCards = document.querySelectorAll('#device-grid .device-card');
    const resultCount = document.getElementById('search-result-count');
    const noResultsMessage = document.getElementById('no-results-message');
    const totalText = resultCount ? resultCount.textContent : '';

    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;
        deviceCards.forEach((deviceCard) => {
            const searchableText = deviceCard.dataset.search || '';
            const isMatch = searchableText.includes(query);
            deviceCard.classList.toggle('hidden', !isMatch);
            if (isMatch) {
                visibleCount += 1;
            }
        });
        if (resultCount) {
            resultCount.textContent = query === '' ? totalText : `${visibleCount} device(s) match "${query}"`;
        }
        if (noResultsMessage) {
            noResultsMessage.classList.toggle('hidden', visibleCount > 0);
        }
    });
}

function initialiseFormValidation() {
    const validatedForms = document.querySelectorAll('form[data-validate]');
    validatedForms.forEach((form) => {
        form.addEventListener('submit', (submitEvent) => {
            if (!validateForm(form)) {
                submitEvent.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let formIsValid = true;
    const validatedFields = form.querySelectorAll('[data-required]');

    validatedFields.forEach((field) => {
        const formField = field.closest('.form-field');
        const errorElement = formField ? formField.querySelector('.field-error') : null;
        const value = field.value.trim();
        let fieldError = '';

        if (value === '') {
            fieldError = 'This field is required.';
        } else if (field.dataset.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            fieldError = 'Please enter a valid email address.';
        } else if (field.dataset.minLength && value.length < Number(field.dataset.minLength)) {
            fieldError = `Must be at least ${field.dataset.minLength} characters.`;
        } else if (field.dataset.match && value !== form.elements[field.dataset.match].value) {
            fieldError = 'Values do not match.';
        }

        if (fieldError !== '') {
            formIsValid = false;
        }
        if (errorElement) {
            errorElement.textContent = fieldError;
        }
        field.classList.toggle('input-invalid', fieldError !== '');
    });

    return formIsValid;
}
