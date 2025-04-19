{{-- resources/views/workflows/create/scripts/main-script.blade.php --}}
<script>
    // Updated to use GET instead of POST

    function initBudgetAlerts() {
        // Get the budget input field - assuming it has an id of 'budget'
        const budgetInput = $('#budget');

        // Update the approval matrix info when budget changes
        budgetInput.on('change', function() {
            updateApprovalMatrixInfo();
        });

        // Also update when the currency formatter might trigger changes
        budgetInput.on('blur', function() {
            updateApprovalMatrixInfo();
        });

        function updateApprovalMatrixInfo() {
            // Get the current budget value (remove currency formatting if present)
            let budgetValue = budgetInput.val().replace(/[^\d.-]/g, '');

            if (budgetValue && !isNaN(budgetValue)) {
                // Show loading state
                $('#budget-category-alert').html(
                    '<i class="fas fa-spinner fa-spin mr-2"></i> Loading applicable approval matrix...');

                // Make AJAX request using GET instead of POST
                $.ajax({
                    url: "{{ route('get.approval.matrix') }}",
                    method: 'GET', // Changed from POST to GET
                    data: {
                        budget: budgetValue
                    }, // Removed _token since GET requests don't need CSRF tokens
                    success: function(response) {
                        if (response.success) {
                            const matrix = response.matrix;

                            // Update the alert with matrix information
                            $('#budget-category-alert').html(`
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>${matrix.name}</strong>: ${matrix.description || 'No description available'}<br>
                            <small>Applicable for budgets between ${formatCurrency(matrix.min_budget)} and
                            ${matrix.max_budget ? formatCurrency(matrix.max_budget) : 'unlimited'}</small>
                        `);

                            // You might want to update workflow visualization here as well
                            // updateWorkflowVisualization(matrix);
                        } else {
                            // No matrix found
                            $('#budget-category-alert').html(`
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            ${response.message}
                        `);
                        }
                    },
                    error: function() {
                        // Error handling
                        $('#budget-category-alert').html(`
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Unable to determine approval matrix. Please contact system administrator.
                    `);
                    }
                });
            } else {
                // No valid budget entered
                $('#budget-category-alert').html(`
                <i class="fas fa-info-circle mr-2"></i>
                Please enter a valid budget amount to see applicable approval flow.
            `);
            }
        }

        // Format currency for display (you may already have this in your currency-formatter.js)
        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        }

        // Initial check on page load
        updateApprovalMatrixInfo();
    }

    // Ensure this function is called when the document is ready
    $(document).ready(function() {
        initBudgetAlerts();
    });
</script>
