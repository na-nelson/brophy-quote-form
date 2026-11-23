/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/form/form.scss"
/*!****************************!*\
  !*** ./src/form/form.scss ***!
  \****************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "./src/form/results.scss"
/*!*******************************!*\
  !*** ./src/form/results.scss ***!
  \*******************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	const __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		const cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		const module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			const e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = (exports) => {
/******/ 		Object.defineProperties(exports, { [Symbol.toStringTag]: { value: 'Module' }, __esModule: { value: true } });
/******/ 	};
/******/ 	
/************************************************************************/
let __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**************************!*\
  !*** ./src/form/view.js ***!
  \**************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _form_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./form.scss */ "./src/form/form.scss");
/* harmony import */ var _results_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./results.scss */ "./src/form/results.scss");


const bqfZipPattern = /^\d{5}(-\d{4})?$/;
const bqfEmailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const bqfFields = {
  quoteType: {
    type: 'radio',
    name: 'quote-type'
  },
  qty20: {
    type: 'select',
    name: 'qty-20',
    id: 'qty-20'
  },
  qty40: {
    type: 'select',
    name: 'qty-40',
    id: 'qty-40'
  },
  zip: {
    type: 'text',
    name: 'zip',
    id: 'zip'
  },
  newUsed: {
    type: 'select',
    name: 'new-used',
    id: 'new-used',
    condition: 'Purchase'
  },
  duration: {
    type: 'select',
    name: 'duration',
    id: 'duration',
    condition: 'Rental'
  },
  deliveryDate: {
    type: 'date',
    name: 'delivery-date',
    id: 'delivery-date'
  },
  email: {
    type: 'text',
    name: 'email',
    id: 'email'
  },
  firstName: {
    type: 'text',
    name: 'first-name',
    id: 'first-name'
  },
  lastName: {
    type: 'text',
    name: 'last-name',
    id: 'last-name'
  },
  phone: {
    type: 'text',
    name: 'phone',
    id: 'phone'
  }
};

// ================================================================================
// Validation
// ================================================================================
function bqfToday() {
  const now = new Date();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  return `${now.getFullYear()}-${month}-${day}`;
}
function bqfIsSunday(value) {
  const parts = value.split('-');
  if (parts.length !== 3) {
    return false;
  }
  const date = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
  return date.getDay() === 0;
}
function bqfQuoteType($form) {
  const $checked = $form.querySelector('[name="quote-type"]:checked');
  return $checked ? $checked.value : '';
}
function bqfFieldApplies(field, quoteType) {
  return !field.condition || field.condition === quoteType;
}
function bqfFieldMessage(field, value) {
  const trimmed = value.trim();
  if (!trimmed) {
    return 'This field is required.';
  }
  if (field.name === 'zip' && !bqfZipPattern.test(trimmed)) {
    return 'Enter a 5-digit ZIP code.';
  }
  if (field.name === 'email' && !bqfEmailPattern.test(trimmed)) {
    return 'Enter a valid email address.';
  }
  if (field.name === 'delivery-date' && trimmed < bqfToday()) {
    return 'Choose today or a later date.';
  }
  if (field.name === 'delivery-date' && bqfIsSunday(trimmed)) {
    return 'Delivery and pickup are not available on Sundays.';
  }
  if (field.name === 'phone' && !bqfValidUsPhone(trimmed.replace(/\D/g, ''))) {
    return 'Enter a 10-digit phone number.';
  }
  return '';
}
function bqfValidUsPhone(digits) {
  return digits.length === 10 || digits.length === 11 && digits.startsWith('1');
}
function bqfPhoneDigits(value) {
  let digits = (value || '').replace(/\D/g, '');
  if (digits.length === 11 && digits.startsWith('1')) {
    digits = digits.slice(1);
  }
  return digits.slice(0, 10);
}
function bqfFormatPhone(value) {
  const digits = bqfPhoneDigits(value);
  if (!digits) {
    return '';
  }
  let index = 0;
  return '(___) ___-____'.replace(/_/g, () => digits.charAt(index++) || '_');
}
function bqfPlacePhoneCaret($input) {
  if ($input.selectionStart !== $input.selectionEnd) {
    return;
  }
  const slot = $input.value.indexOf('_');
  const caret = slot === -1 ? $input.value.length : slot;
  $input.setSelectionRange(caret, caret);
}
function bqfApplyPhoneFormat($input, inputType) {
  const previousDigits = $input.dataset.bqfPhoneDigits || '';
  let digits = bqfPhoneDigits($input.value);
  if (inputType === 'deleteContentBackward' && digits.length >= previousDigits.length && previousDigits) {
    digits = previousDigits.slice(0, -1);
  }
  const formatted = bqfFormatPhone(digits);
  $input.value = formatted;
  $input.dataset.bqfPhoneDigits = digits;
  if (document.activeElement === $input) {
    bqfPlacePhoneCaret($input);
  }
}
function bqfClearErrors($form) {
  $form.querySelectorAll('.bqf-field-error').forEach($error => {
    $error.remove();
  });
  $form.querySelectorAll('[aria-invalid="true"]').forEach($control => {
    $control.removeAttribute('aria-invalid');
  });
}
function bqfShowError($control, message) {
  const $field = $control.closest('.bqf-field');
  if (!$field) {
    return;
  }
  $control.setAttribute('aria-invalid', 'true');
  const $error = document.createElement('div');
  $error.className = 'bqf-field-error';
  $error.textContent = message;
  $field.appendChild($error);
}
function bqfValidateQuantities($form) {
  const $qty20 = $form.querySelector('[name="qty-20"]');
  const $qty40 = $form.querySelector('[name="qty-40"]');
  if (!$qty20 && !$qty40) {
    return true;
  }
  const qty20 = $qty20 ? Number($qty20.value) : 0;
  const qty40 = $qty40 ? Number($qty40.value) : 0;
  if (qty20 > 0 || qty40 > 0) {
    return true;
  }
  [$qty20, $qty40].forEach($control => {
    if ($control) {
      $control.setAttribute('aria-invalid', 'true');
    }
  });
  const $row = $form.querySelector('.bqf-form-row--qty');
  if ($row) {
    const $error = document.createElement('div');
    $error.className = 'bqf-field-error';
    $error.textContent = 'Choose at least one container.';
    $row.appendChild($error);
  }
  return false;
}
function bqfValidateForm($form) {
  const quoteType = bqfQuoteType($form);
  let valid = true;
  bqfClearErrors($form);
  if (!bqfValidateQuantities($form)) {
    valid = false;
  }
  Object.entries(bqfFields).forEach(([key, field]) => {
    if (key === 'qty20' || key === 'qty40' || key === 'quoteType') {
      return;
    }
    if (!bqfFieldApplies(field, quoteType)) {
      return;
    }
    const $control = $form.querySelector(`[name="${field.name}"]`);
    if (!$control) {
      return;
    }
    if (field.name === 'phone') {
      bqfApplyPhoneFormat($control);
    }
    const message = bqfFieldMessage(field, $control.value || '');
    if (message) {
      valid = false;
      bqfShowError($control, message);
    }
  });
  return valid;
}

// ================================================================================
// Submitting
// ================================================================================
const bqfPendingQuoteKey = 'bqfPendingQuote';
function bqfQuoteValues($form) {
  const values = {};
  Object.values(bqfFields).forEach(field => {
    const $controls = $form.querySelectorAll(`[name="${field.name}"]`);
    if (!$controls.length) {
      return;
    }
    if (field.type === 'radio') {
      const $checked = $form.querySelector(`[name="${field.name}"]:checked`);
      values[field.name] = $checked ? $checked.value : '';
      return;
    }
    values[field.name] = $controls[0].value;
  });
  return values;
}
function bqfApplyQuoteValues($form, values) {
  Object.entries(values).forEach(([name, value]) => {
    const $controls = $form.querySelectorAll(`[name="${name}"]`);
    if (!$controls.length) {
      return;
    }
    if ($controls[0].type === 'radio') {
      $controls.forEach($control => {
        $control.checked = $control.value === value;
      });
      return;
    }
    $controls[0].value = value ?? '';
    if (name === 'phone') {
      bqfApplyPhoneFormat($controls[0]);
    }
  });
}
function bqfStoreQuote($form) {
  try {
    sessionStorage.setItem(bqfPendingQuoteKey, JSON.stringify(bqfQuoteValues($form)));
    return true;
  } catch (error) {
    return false;
  }
}
function bqfGetPendingQuote() {
  let raw = null;
  try {
    raw = sessionStorage.getItem(bqfPendingQuoteKey);
    sessionStorage.removeItem(bqfPendingQuoteKey);
  } catch (error) {
    return null;
  }
  if (!raw) {
    return null;
  }
  try {
    const values = JSON.parse(raw);
    return values && typeof values === 'object' ? values : null;
  } catch (error) {
    return null;
  }
}
document.querySelectorAll('.bqf-form').forEach($form => {
  const $phone = $form.querySelector('[name="phone"]');
  if ($phone) {
    $phone.addEventListener('input', event => {
      bqfApplyPhoneFormat($phone, event.inputType);
    });
    $phone.addEventListener('click', () => {
      bqfPlacePhoneCaret($phone);
    });
    $phone.addEventListener('keyup', event => {
      if (event.shiftKey) {
        return;
      }
      if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(event.key)) {
        bqfPlacePhoneCaret($phone);
      }
    });
    if ($phone.value) {
      bqfApplyPhoneFormat($phone);
    }
  }
  $form.addEventListener('submit', event => {
    bqfHandleSubmit(event, $form);
  });
});
const bqfPendingQuote = bqfGetPendingQuote();
if (bqfPendingQuote) {
  const $pendingForm = document.querySelector('.bqf-form');
  if ($pendingForm) {
    bqfApplyQuoteValues($pendingForm, bqfPendingQuote);
    if (bqfValidateForm($pendingForm)) {
      bqfHandleSubmit({
        preventDefault() {}
      }, $pendingForm);
    } else {
      bqfClearErrors($pendingForm);
    }
  }
}
async function bqfHandleSubmit(event, $form) {
  event.preventDefault();

  // const $status = $form.querySelector('.bqf-status');
  const $statusText = $form.querySelector('.bqf-status__text');
  const $btnSubmit = $form.querySelector('[type="submit"]');
  if (!bqfValidateForm($form)) {
    return;
  }
  const resultsPage = $form.dataset.resultsPage;
  if (resultsPage && bqfStoreQuote($form)) {
    window.location.assign(resultsPage);
    return;
  }
  $btnSubmit.disabled = true;
  $form.classList.add('loading');
  $statusText.classList.remove('active');
  try {
    const response = await fetch($form.getAttribute('action'), {
      method: 'POST',
      body: new FormData($form)
    });
    const result = await response.json();
    if (!result) {
      throw new Error('Something went wrong.');
    }
    console.log(result.data);
    const zipMissing = result.data?.error === 'zip';
    bqfSetZipError($form, zipMissing);
    if (zipMissing) {
      const $zip = $form.querySelector('[name="zip"]');
      if ($zip) {
        bqfShowError($zip, 'ZIP code not found.');
      }
    } else {
      if (result.success && result.data?.results) {
        bqfFillResults($form, result.data.results);
      }
      $statusText.classList.add('active');
      $statusText.textContent = result.data?.message ? result.data.message : 'Something went wrong.';
    }
  } catch (error) {
    bqfSetZipError($form, false);
    $statusText.classList.add('active');
    $statusText.textContent = 'Something went wrong.';
  }
  $btnSubmit.disabled = false;
  $form.classList.remove('loading');
}

// ================================================================================
// Handling results
// ================================================================================
function bqfSetZipError($form, isActive) {
  const $root = $form.closest('.bqf');
  if (!$root) {
    return;
  }
  const $zipError = $root.querySelector('.bqf-results-error__zip');
  const $wrap = $root.querySelector('.bqf-results-wrap');
  if (!$zipError) {
    return;
  }
  $zipError.classList.toggle('active', isActive);
  $wrap?.classList.remove('active');
  if (isActive) {
    bqfScrollToResults($form);
  }
}
function bqfMoney(amount) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(Number(amount) || 0);
}
function bqfSetRow($value, data) {
  if (!$value) {
    return;
  }
  const $qty = $value.querySelector('.qty');
  const $unitPrice = $value.querySelector('.unit-price');
  if (!data) {
    if ($qty) {
      $qty.textContent = '';
    }
    if ($unitPrice) {
      $unitPrice.textContent = '';
    }
    $value.classList.remove('active');
    return;
  }
  if ($qty) {
    $qty.textContent = data.quantity ? String(data.quantity) : '';
  }
  if ($unitPrice) {
    const price = data.unit_price !== undefined ? data.unit_price : data.amount;
    if (price === undefined || price === null) {
      $unitPrice.textContent = '';
    } else {
      $unitPrice.textContent = data.duration ? `${bqfMoney(price)}/month` : bqfMoney(price);
    }
  }
  $value.classList.add('active');
}
function bqfFillResults($form, results) {
  // const $root = $form.closest('.bqf');

  // if ( !$root || !results ) {
  // 	return;
  // }

  const $wrap = document.querySelector('.bqf-results-wrap');
  console.log($wrap);
  if (!$wrap) {
    return;
  }
  const lines = Array.isArray(results.lines) ? results.lines : [];
  const charges = Array.isArray(results.charges) ? results.charges : [];
  const line20 = lines.find(line => String(line.label).startsWith('20'));
  const line40 = lines.find(line => String(line.label).startsWith('40'));
  const delivery = charges.find(charge => charge.label === 'Delivery');
  const pickup = charges.find(charge => charge.label === 'Pickup');
  bqfSetRow($wrap.querySelector('.size-20-value'), line20 || null);
  bqfSetRow($wrap.querySelector('.size-40-value'), line40 || null);
  bqfSetRow($wrap.querySelector('.lines-subtotal-value'), {
    amount: results.subtotal
  });
  const discount = results.discount;
  const hasDiscount = Boolean(discount && discount.amount);
  const $linesSubtotalLabel = $wrap.querySelector('.row-lines-subtotal .row-label');
  if ($linesSubtotalLabel) {
    $linesSubtotalLabel.textContent = hasDiscount ? 'Subtotal before discount' : 'Subtotal';
  }
  bqfSetRow($wrap.querySelector('.lines-discount-value'), hasDiscount ? {
    amount: -Math.abs(discount.amount)
  } : null);
  bqfSetRow($wrap.querySelector('.lines-discounted-subtotal-value'), hasDiscount ? {
    amount: discount.discounted_subtotal
  } : null);
  const $linesDiscountMessage = $wrap.querySelector('.lines-discount-message');
  if ($linesDiscountMessage) {
    $linesDiscountMessage.textContent = hasDiscount ? discount.message || '' : '';
  }
  bqfSetRow($wrap.querySelector('.delivery-value'), delivery || null);
  bqfSetRow($wrap.querySelector('.pickup-value'), pickup || null);
  bqfSetRow($wrap.querySelector('.charges-subtotal-value'), {
    amount: results.charges_subtotal
  });
  const $total = $wrap.querySelector('.total-value');
  if ($total) {
    $total.textContent = bqfMoney(results.total);
  }
  const $discount = $wrap.querySelector('.discount');
  const $discountValue = $wrap.querySelector('.discount-value');
  const $discountMessage = $wrap.querySelector('.discount-message');
  if ($discount) {
    if (discount && discount.amount) {
      $discount.classList.add('active');
      if ($discountValue) {
        $discountValue.textContent = bqfMoney(discount.original_total);
      }
      if ($discountMessage) {
        $discountMessage.textContent = discount.message || '';
      }
    } else {
      $discount.classList.remove('active');
      if ($discountValue) {
        $discountValue.textContent = '';
      }
      if ($discountMessage) {
        $discountMessage.textContent = '';
      }
    }
  }
  $wrap.classList.add('active');
  bqfScrollToResults($form);
}
function bqfScrollToResults($form) {
  const $results = document.querySelector('.bqf-results-container');
  if (!$results) {
    return;
  }
  $results.scrollIntoView({
    behavior: 'smooth',
    block: 'start'
  });
}
})();

/******/ })()
;
//# sourceMappingURL=form.js.map