// staffPages.js - two small helpers used by every staff list page

// 1. live search: <input data-filter="#tableId"> hides the table rows that do not contain the typed text
var filterInputs = document.querySelectorAll('[data-filter]');
for (var i = 0; i < filterInputs.length; i++) {
    filterInputs[i].addEventListener('input', function () {
        var text = this.value.trim().toLowerCase();
        var rows = document.querySelectorAll(this.getAttribute('data-filter') + ' tbody tr');
        var shown = 0;
        for (var j = 0; j < rows.length; j++) {
            var match = rows[j].textContent.toLowerCase().indexOf(text) !== -1;
            rows[j].hidden = !match;
            if (match) {
                shown++;
            }
        }
        var counter = document.querySelector('[data-count]');
        if (counter) {
            counter.textContent = shown + ' shown';
        }
    });
}

// 2. "Add ..." button: <button data-open="#formId"> shows or hides the form
var openButtons = document.querySelectorAll('[data-open]');
for (var k = 0; k < openButtons.length; k++) {
    openButtons[k].addEventListener('click', function () {
        var form = document.querySelector(this.getAttribute('data-open'));
        form.hidden = !form.hidden;
        if (!form.hidden) {
            var first = form.querySelector('input, select');
            if (first) {
                first.focus();
            }
        }
    });
}
