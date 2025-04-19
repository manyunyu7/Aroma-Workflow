{{-- resources/views/workflows/create/scripts/currency-formatter.blade.php --}}
<script>
    function initCurrencyFormatter() {
        const displayField = document.getElementById('total_nilai_display');
        const valueField = document.getElementById('total_nilai');

        // Format initial value if exists
        if (valueField.value) {
            const numberValue = parseInt(valueField.value, 10);
            displayField.value = 'Rp ' + numberValue.toLocaleString('id-ID');
        }

        displayField.addEventListener('input', function(e) {
            // Remove non-digit characters and the "Rp" prefix
            let rawValue = this.value.replace(/[^0-9]/g, '');

            if (rawValue === '') {
                valueField.value = '';
                this.value = '';
                return;
            }

            const numberValue = parseInt(rawValue, 10);

            // Format with thousand separators and add "Rp"
            const formattedValue = 'Rp ' + numberValue.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });

            this.value = formattedValue; // Display the formatted value with "Rp"
            valueField.value = numberValue; // Store the raw number for submission

            // When budget changes, check if we need to reset the approval workflow
            if (typeof checkBudgetChanges === 'function') {
                checkBudgetChanges(numberValue);
            }
        });
    }
    </script>
