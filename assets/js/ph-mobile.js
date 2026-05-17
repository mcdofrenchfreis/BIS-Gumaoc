/**
 * Philippine mobile helpers for +63 prefix inputs (10 local digits starting with 9).
 */
(function (global) {
    'use strict';

    function digitsOnly(value) {
        return String(value || '').replace(/\D/g, '');
    }

    /**
     * Normalize to 10-digit local part (e.g. 9062668190).
     */
    function formatPhMobileInput(stored) {
        let phone = digitsOnly(stored);
        if (phone.startsWith('63')) {
            phone = phone.substring(2);
        }
        if (phone.length === 11 && phone.startsWith('09')) {
            phone = phone.substring(1);
        }
        if (phone.length === 10 && phone.startsWith('9')) {
            return phone;
        }
        return '';
    }

    /**
     * Sanitize user typing in the local input field.
     */
    function sanitizePhMobileInput(inputEl) {
        if (!inputEl) {
            return;
        }
        let phone = digitsOnly(inputEl.value);
        if (phone.length > 10) {
            phone = phone.substring(0, 10);
        }
        if (phone.length === 11 && phone.startsWith('09')) {
            phone = phone.substring(1);
        }
        if (phone.length > 0 && phone[0] !== '9') {
            if (phone.startsWith('0') && phone.length > 1) {
                phone = phone.substring(1);
            }
            if (phone.length > 0 && phone[0] !== '9') {
                phone = '9' + phone.replace(/^0+/, '').substring(0, 9);
            }
        }
        inputEl.value = phone.substring(0, 10);
    }

    function isValidPhMobileLocal(value) {
        return /^9[0-9]{9}$/.test(String(value || ''));
    }

    function toFullPhMobile(localValue) {
        if (!isValidPhMobileLocal(localValue)) {
            return null;
        }
        return '+63' + localValue;
    }

    function bindPhMobileInput(inputEl) {
        if (!inputEl || inputEl.dataset.phMobileBound === '1') {
            return;
        }
        inputEl.dataset.phMobileBound = '1';
        inputEl.addEventListener('input', function () {
            sanitizePhMobileInput(inputEl);
        });
    }

    global.PhMobile = {
        digitsOnly: digitsOnly,
        formatPhMobileInput: formatPhMobileInput,
        sanitizePhMobileInput: sanitizePhMobileInput,
        isValidPhMobileLocal: isValidPhMobileLocal,
        toFullPhMobile: toFullPhMobile,
        bindPhMobileInput: bindPhMobileInput,
    };
})(typeof window !== 'undefined' ? window : this);
