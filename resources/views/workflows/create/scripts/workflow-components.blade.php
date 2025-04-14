<script>
    function initWorkflowComponents() {
        // Global variables
        const picContainer = $("#pic-container");
        const addPicBtn = $("#add-pic-btn");
        const modal = $("#pic-modal");
        const roleSelect = $("#role-select");
        const unitKerjaSelect = $("#unit-kerja-select");
        const unitKerjaSelectionHeader = $("#unit-kerja-section-header");
        const employeeSelect = $("#employee-select");
        const userSelectionContainer = $("#user-selection-container");
        const approverUnitKerjaSelect = $("#approver-unit-kerja-select");
        const approverSelect = $("#approver-select");
        const reviewerApproverSection = $("#reviewer-approver-section");
        const savePicBtn = $("#save-pic-btn");

        let picIndex = {{ count(old('pics', [1])) }}; // Start with the next index after existing PICs
        let selectedRole = '';
        let selectedUserId = '';
        let selectedUserName = '';
        let selectedUserUnit = '';
        let selectedJabatan = '';
        let approverUserId = '';
        let approverUserName = '';
        let approverUserUnit = '';
        let approverJabatan = '';
        let currentRoles = [];
        let totalBudget = 0;

        // Initialize Select2 components
        initSelect2Components();

        // Get current roles in the workflow
        function getCurrentRoles() {
            let roles = [];
            $(".pic-entry").each(function() {
                roles.push($(this).data('role'));
            });
            currentRoles = roles;
            return roles;
        }

        // Initialize Select2 components
        function initSelect2Components() {
            unitKerjaSelect.select2({
                dropdownParent: $('#pic-modal'),
                ajax: {
                    url: "/workflow-actions/get-unit-kerja",
                    dataType: "json",
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term || ""
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(unit => ({
                                id: unit.unit_kerja,
                                text: `${unit.unit_kerja} (${unit.employee_count} employees)`
                            }))
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                placeholder: "Select a unit kerja"
            });

            approverUnitKerjaSelect.select2({
                dropdownParent: $('#pic-modal'),
                ajax: {
                    url: "/workflow-actions/get-unit-kerja",
                    dataType: "json",
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term || ""
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(unit => ({
                                id: unit.unit_kerja,
                                text: `${unit.unit_kerja} (${unit.employee_count} employees)`
                            }))
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                placeholder: "Select a unit kerja for reviewer-approver"
            });

            employeeSelect.select2({
                dropdownParent: $('#pic-modal'),
                placeholder: "Select an employee"
            });

            approverSelect.select2({
                dropdownParent: $('#pic-modal'),
                placeholder: "Select a reviewer-approver"
            });
        }

        // Check budget changes to update workflow rules
        // Make sure the budget change handler updates role availability
        window.checkBudgetChanges = function(budget) {
            totalBudget = budget;

            let budgetInfoHtml = '<i class="fas fa-info-circle mr-2"></i>';

            // Build a comprehensive message about all budget thresholds
            budgetInfoHtml += 'Budget rules: ';

            if (budget > 500000000) {
                budgetInfoHtml += 'Over 500,000,000 IDR: Acknowledger is optional. ';
            } else {
                budgetInfoHtml += 'Under 500,000,000 IDR: Standard Acknowledger rules apply. ';
            }

            if (budget < 3000000000) {
                budgetInfoHtml += 'Under 3,000,000,000 IDR: Unit Head must be from your unit kerja.';
            } else {
                budgetInfoHtml += 'Over 3,000,000,000 IDR: Unit Head can be from any unit.';
            }

            $("#budget-info-alert").html(budgetInfoHtml);

            // If current workflow violates the new budget rules, show a warning
            validateWorkflowWithBudget(budget);

            // Reload available roles if the modal is open
            if (modal.is(':visible')) {
                loadAvailableRoles();
            }
        };

        // Validate workflow based on budget rules
        // Validate workflow based on budget rules
        function validateWorkflowWithBudget(budget) {
            // First remove any existing warnings
            $('#unit-mismatch-warning').remove();

            // Check if acknowledger and unit head are from the same unit (for budgets < 500M)
            if (budget <= 500000000) {
                let acknowledgerEntry = null;
                let headEntry = null;

                $(".pic-entry").each(function() {
                    const role = $(this).data('role');
                    if (role === 'Acknowledger') acknowledgerEntry = $(this);
                    if (role === 'Unit Head - Approver') headEntry = $(this);
                });

                if (acknowledgerEntry && headEntry) {
                    // Both roles exist, check if they are from the same unit
                    const acknowledgerUnit = acknowledgerEntry.find('small.text-muted').text();
                    const headUnit = headEntry.find('small.text-muted').text();

                    if (acknowledgerUnit !== headUnit) {
                        // Show warning
                        const warningHtml = `
                <div id="unit-mismatch-warning" class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Warning:</strong> For budgets under 500,000,000 IDR, the Acknowledger and Unit Head must be from the same unit.
                    Please update your approvers.
                </div>
                `;
                        $('#pic-container').before(warningHtml);
                        return; // Exit early to avoid showing multiple warnings
                    }
                }
            }

            // Check if Unit Head is from current user's unit (for budgets < 3B)
            if (budget < 3000000000) {
                let headEntry = null;
                const currentUserUnitKerja = "{{ $user->unit_kerja }}";

                $(".pic-entry").each(function() {
                    const role = $(this).data('role');
                    if (role === 'Unit Head - Approver') headEntry = $(this);
                });

                if (headEntry) {
                    const headUnit = headEntry.find('small.text-muted').text();

                    if (headUnit !== currentUserUnitKerja) {
                        // Show warning
                        const warningHtml = `
                <div id="unit-mismatch-warning" class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Warning:</strong> For budgets under 3,000,000,000 IDR, the Unit Head must be from your unit (${currentUserUnitKerja}).
                    Please update your Unit Head selection.
                </div>
                `;
                        $('#pic-container').before(warningHtml);
                    }
                }
            }
        }

        // Load available roles based on current workflow
        // Role loading function - Modified to handle Acknowledger visibility based on budget
        function loadAvailableRoles() {
            const roles = getCurrentRoles();
            const budget = parseFloat($('#total_nilai').val() || 0);
            let acknowledgerHidden = false; // Track if we're hiding the Acknowledger role

            // Get available roles from the server
            $.ajax({
                url: '/workflow-actions/getAvailableRoles',
                type: 'GET',
                data: {
                    current_roles: roles,
                    budget: budget
                },
                success: function(data) {
                    roleSelect.empty();
                    roleSelect.append('<option value="">-- Select Role --</option>');

                    // Check if we should hide Acknowledger option (budget > 500,000,000)
                    if (budget > 500000000) {
                        // Filter out Acknowledger from available roles
                        const filteredRoles = data.filter(role => role !== 'Acknowledger');
                        acknowledgerHidden = data.includes(
                            'Acknowledger'); // Check if Acknowledger was available but we're hiding it

                        // Add the filtered roles to the dropdown
                        filteredRoles.forEach(function(role) {
                            roleSelect.append(`<option value="${role}">${role}</option>`);
                        });
                    } else {
                        // For lower budgets, show all available roles
                        data.forEach(function(role) {
                            roleSelect.append(`<option value="${role}">${role}</option>`);
                        });
                    }

                    // If Acknowledger was available but hidden due to budget constraint,
                    // add an option to show it if the user wants to
                    if (acknowledgerHidden) {
                        // Remove any existing "Show Acknowledger" button
                        $("#show-acknowledger-btn").remove();

                        // Add a button to show the Acknowledger option
                        $(`<button id="show-acknowledger-btn" type="button" class="btn btn-outline-secondary btn-sm mt-2">
                    <i class="fas fa-plus-circle mr-1"></i> Add Acknowledger Role (Optional)
                </button>`).insertAfter(roleSelect);

                        // Handle the button click event
                        $("#show-acknowledger-btn").click(function() {
                            // Show a confirmation dialog
                            if (confirm(
                                    "For budgets over 500,000,000, an Acknowledger is optional. Do you want to add an Acknowledger to this workflow?"
                                )) {
                                // Add the Acknowledger option to the dropdown
                                roleSelect.append(
                                    `<option value="Acknowledger">Acknowledger</option>`);
                                // Select the Acknowledger option
                                roleSelect.val("Acknowledger").trigger('change');
                                // Hide the button
                                $(this).remove();
                            }
                        });
                    }

                    // If no roles available, show message
                    if (data.length === 0) {
                        roleSelect.append(
                            '<option value="" disabled>No roles available for current workflow</option>'
                        );
                        savePicBtn.prop('disabled', true);
                    } else {
                        savePicBtn.prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    console.error('Error loading available roles:', xhr.responseText);
                    roleSelect.empty();
                    roleSelect.append('<option value="">Error loading roles</option>');
                    savePicBtn.prop('disabled', true);
                }
            });
        }

        // Load employees based on unit kerja and role
        function loadEmployees(unitKerja, roleValue) {
            const budget = $('#total_nilai').val() || 0;

            $.ajax({
                url: '/workflow-actions/get-employees',
                type: 'GET',
                data: {
                    unit_kerja: unitKerja,
                    role: roleValue,
                    budget: budget
                },
                success: function(data) {
                    employeeSelect.empty();
                    employeeSelect.append('<option value="">-- Select Employee --</option>');

                    if (data.length > 0) {
                        data.forEach(function(employee) {
                            employeeSelect.append(
                                `<option value="${employee.id}" data-unit="${employee.unit_kerja}">${employee.name}</option>`
                            );
                        });
                        employeeSelect.prop('disabled', false);
                    } else {
                        employeeSelect.append(
                            '<option value="" disabled>No employees found with this role</option>'
                        );
                        employeeSelect.prop('disabled', true);
                    }
                },
                error: function(xhr) {
                    console.error('Error loading employees:', xhr.responseText);
                    employeeSelect.empty();
                    employeeSelect.append('<option value="">Error loading employees</option>');
                    employeeSelect.prop('disabled', true);
                }
            });
        }

        // Load approvers based on unit kerja
        function loadApprovers(unitKerja) {
            const budget = $('#total_nilai').val() || 0;

            $.ajax({
                url: '/workflow-actions/get-employees',
                type: 'GET',
                data: {
                    unit_kerja: unitKerja,
                    role: 'Reviewer-Approver',
                    budget: budget
                },
                success: function(data) {
                    approverSelect.empty();
                    approverSelect.append(
                        '<option value="">-- Select Reviewer-Approver --</option>');

                    if (data.length > 0) {
                        data.forEach(function(employee) {
                            approverSelect.append(
                                `<option value="${employee.id}" data-unit="${employee.unit_kerja}">${employee.name}</option>`
                            );
                        });
                        approverSelect.prop('disabled', false);
                    } else {
                        approverSelect.append(
                            '<option value="" disabled>No reviewer-approvers found</option>');
                        approverSelect.prop('disabled', true);
                    }
                },
                error: function(xhr) {
                    console.error('Error loading approvers:', xhr.responseText);
                    approverSelect.empty();
                    approverSelect.append('<option value="">Error loading approvers</option>');
                    approverSelect.prop('disabled', true);
                }
            });
        }

        // Fetch employee's position/jabatan
        function fetchJabatan(userId, targetElement, targetInput) {
            $.ajax({
                url: '/workflow-actions/fetch-jabatan',
                type: 'GET',
                data: {
                    user_id: userId
                },
                success: function(response) {
                    if (response.success) {
                        targetElement.text(response.nama_posisi);
                        targetInput.val(response.nama_posisi);
                    } else {
                        targetElement.text('Position not found');
                        targetInput.val('');
                    }
                },
                error: function() {
                    targetElement.text('Error loading position');
                    targetInput.val('');
                }
            });
        }

        // Add PIC entry to the workflow
        function addPicEntry(picData) {
            // Hide empty state message when adding entries
            $("#empty-workflow-message").hide();

            // Make sure the table is visible
            $("table", picContainer).show();

            let cssClass = '';
            switch (picData.role) {
                case 'Acknowledger':
                    cssClass = 'role-acknowledger';
                    break;
                case 'Unit Head - Approver':
                    cssClass = 'role-head';
                    break;
                case 'Reviewer-Maker':
                    cssClass = 'role-reviewer-maker';
                    break;
                case 'Reviewer-Approver':
                    cssClass = 'role-reviewer-approver';
                    break;
            }

            // Create table row for the PIC
            const rowHtml = `
            <tr class="pic-entry" data-role="${picData.role}" data-user-id="${picData.userId}" ${picData.pairedWithMaker ? 'data-paired="true"' : ''}>
                <td>
                    <strong>${picData.userName}</strong>
                    <small class="d-block text-muted">${picData.userUnit}</small>
                    <input type="hidden" name="pics[${picIndex}][user_id]" value="${picData.userId}">
                </td>
                <td>
                    <span class="role-badge ${cssClass}">${picData.role}</span>
                    <input type="hidden" name="pics[${picIndex}][role]" value="${picData.role}">
                    ${picData.pairedWithMaker ? '<span class="badge badge-light ml-1">Paired</span>' : ''}
                </td>
                <td>
                    ${picData.jabatan}
                    <input type="hidden" name="pics[${picIndex}][jabatan]" value="${picData.jabatan}">
                </td>
                <td>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="pics[${picIndex}][digital_signature]" value="1">
                        <label class="form-check-label">Use Digital Signature</label>
                    </div>
                </td>
                <td>
                    <textarea name="pics[${picIndex}][notes]" class="form-control form-control-sm" placeholder="Notes (optional)" rows="2"></textarea>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-pic">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;

            // If this is a paired reviewer, add grouping visual indicator
            if (picData.role === 'Reviewer-Maker') {
                // Store the current index for pairing
                $(rowHtml).data('maker-index', picIndex);
                $("#pic-table-body").append(rowHtml);
            } else if (picData.role === 'Reviewer-Approver' && picData.pairedWithMaker) {
                // Add class to show relationship with maker
                const pairRow = $(rowHtml).addClass('reviewer-approver-row');
                $("#pic-table-body").append(pairRow);
            } else {
                // Regular entry (Acknowledger or Unit Head)
                $("#pic-table-body").append(rowHtml);
            }

            picIndex++;

            // Validate workflow after adding
            validateWorkflowWithBudget(totalBudget);
        }

        // Check if add to workflow button should be enabled
        function checkFormValidity() {
            // For regular roles
            let isValid = selectedRole && selectedUserId;

            // For Reviewer-Maker role, also need an approver
            if (selectedRole === 'Reviewer-Maker') {
                isValid = isValid && approverUserId;
            }

            if (isValid) {
                savePicBtn.removeClass('d-none');
            } else {
                savePicBtn.addClass('d-none');
            }
        }

        // Event Handlers

        // Add PIC button
        // Update modal initialization to reflect budget value
        addPicBtn.click(function() {
            // Reset modal
            resetModal();

            // Load available roles (with Acknowledger filtering based on budget)
            loadAvailableRoles();

            // Show modal
            modal.modal('show');
        });

        // Reset modal fields and visibility
        function resetModal() {
            selectedRole = '';
            selectedUserId = '';
            approverUserId = '';

            roleSelect.val('');
            unitKerjaSelect.val(null).trigger('change');
            employeeSelect.empty().prop('disabled', true);
            approverUnitKerjaSelect.val(null).trigger('change');
            approverSelect.empty().prop('disabled', true);

            $("#jabatan-display").text("N/A");
            $("#approver-jabatan-display").text("N/A");
            $("#jabatan-input").val("");
            $("#approver-jabatan-input").val("");

            userSelectionContainer.addClass('d-none');
            reviewerApproverSection.addClass('d-none');
            savePicBtn.addClass('d-none');
        }

        // Role selection event handler - Triggered when a user selects a different role from the dropdown
        // Role selection event handler - Modified to handle Acknowledger role selection
        // Role selection event handler - Modified to handle Acknowledger and Unit Head role selection
        roleSelect.change(function() {
            selectedRole = $(this).val();
            const budget = parseFloat($('#total_nilai').val() || 0);

            if (selectedRole) {
                // Show user selection section
                userSelectionContainer.removeClass('d-none');

                // Remove any previous info messages
                $("#unit-kerja-info").remove();
                $("#role-description-info").remove();

                // Add role-specific descriptions to help the user understand the purpose of each role
                const budgetFormatted = new Intl.NumberFormat('id-ID').format(budget);

                let roleDescription = '';

                if (selectedRole === 'Acknowledger') {
                    roleDescription = `
                    <div id="role-description-info" class="alert alert-primary mb-3 mt-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>About the Acknowledger Role:</strong>
                        <ul class="mb-0 mt-1">
                            <li>Acknowledges the workflow before it proceeds to review/approval stages</li>
                            <li>For budgets over 500,000,000 IDR, this role is optional</li>
                            <li>Current budget: IDR ${budgetFormatted}</li>
                        </ul>
                    </div>
                `;
                } else if (selectedRole === 'Unit Head - Approver') {
                    roleDescription = `
                <div id="role-description-info" class="alert alert-primary mb-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>About the Unit Head - Approver Role:</strong>
                    <ul class="mb-0 mt-1">
                        <li>Department head who approves the workflow</li>
                        <li>For budgets under 500,000,000 IDR, must be from the same unit as the Acknowledger (if one exists)</li>
                        <li>For budgets under 3,000,000,000 IDR, must be from your unit kerja</li>
                        <li>Current budget: IDR ${budgetFormatted}</li>
                    </ul>
                </div>
            `;
                } else if (selectedRole === 'Reviewer-Maker') {
                    roleDescription = `
                <div id="role-description-info" class="alert alert-primary mb-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>About the Reviewer-Maker Role:</strong>
                    <ul class="mb-0 mt-1">
                        <li>Creates a formal review of the workflow</li>
                        <li>Must be paired with a Reviewer-Approver</li>
                        <li>You'll need to select both the Reviewer-Maker and their corresponding Reviewer-Approver</li>
                    </ul>
                </div>
            `;
                }

                // Insert role description at the top of the modal body
                if (roleDescription) {
                    $(roleDescription).insertAfter(roleSelect);
                }

                // Get current user's unit kerja
                const currentUserUnitKerja = "{{ $user->unit_kerja }}";

                // Auto-select unit kerja for Acknowledger role
                if (selectedRole === 'Acknowledger') {
                    // Create the option if it doesn't exist
                    if (unitKerjaSelect.find(`option[value="${currentUserUnitKerja}"]`).length === 0) {
                        const newOption = new Option(currentUserUnitKerja, currentUserUnitKerja, true, true);
                        unitKerjaSelect.append(newOption).trigger('change');
                    } else {
                        unitKerjaSelect.val(currentUserUnitKerja).trigger('change');
                    }

                    // Add info message for Unit Kerja constraint
                    $("<div id='unit-kerja-info' class='alert alert-info mt-2 mb-3'>" +
                        "<i class='fas fa-lock mr-2'></i>" +
                        "<strong>Unit Kerja automatically set</strong><br>" +
                        "For the Acknowledger role, only employees from your unit kerja (" +
                        currentUserUnitKerja + ") can be selected. " +
                        "This ensures proper workflow within your department." +
                        "</div>").insertAfter(unitKerjaSelectionHeader);

                    // Disable the unit kerja dropdown to prevent changes
                    unitKerjaSelect.prop('disabled', true);

                    // Automatically load employees from the current user's unit kerja
                    loadEmployees(currentUserUnitKerja, selectedRole);
                }
                // Auto-select unit kerja for Unit Head - Approver role if budget < 3B
                else if (selectedRole === 'Unit Head - Approver' && budget < 3000000000) {
                    // Create the option if it doesn't exist
                    if (unitKerjaSelect.find(`option[value="${currentUserUnitKerja}"]`).length === 0) {
                        const newOption = new Option(currentUserUnitKerja, currentUserUnitKerja, true, true);
                        unitKerjaSelect.append(newOption).trigger('change');
                    } else {
                        unitKerjaSelect.val(currentUserUnitKerja).trigger('change');
                    }

                    // Add info message for Unit Kerja constraint
                    $("<div id='unit-kerja-info' class='alert alert-info mt-2 mb-3'>" +
                        "<i class='fas fa-lock mr-2'></i>" +
                        "<strong>Unit Kerja automatically set</strong><br>" +
                        "For budgets under 3,000,000,000 IDR, the Unit Head must be from your unit kerja (" +
                        currentUserUnitKerja + "). " +
                        "This ensures proper approval within your department." +
                        "</div>").insertAfter(unitKerjaSelect);

                    // Disable the unit kerja dropdown to prevent changes
                    unitKerjaSelect.prop('disabled', true);

                    // Automatically load employees from the current user's unit kerja
                    loadEmployees(currentUserUnitKerja, selectedRole);
                } else {
                    // For other roles, enable the select
                    unitKerjaSelect.prop('disabled', false);

                    // Reset employee selection
                    employeeSelect.empty().prop('disabled', true);
                }

                // Reset other selections
                approverUnitKerjaSelect.val(null).trigger('change');
                approverSelect.empty().prop('disabled', true);

                // Show/hide reviewer-approver section based on role
                if (selectedRole === 'Reviewer-Maker') {
                    reviewerApproverSection.removeClass('d-none');

                    // Add specific guidance for the reviewer-approver selection
                    if (!$("#reviewer-approver-guidance").length) {
                        const reviewerGuidance = `
                    <div id="reviewer-approver-guidance" class="alert alert-info mb-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Reviewer-Approver Pairing:</strong><br>
                        Select a Reviewer-Approver who will approve the review created by the Reviewer-Maker.
                        Both will be added to your workflow as a paired set.
                    </div>
                `;
                        $(reviewerGuidance).prependTo(reviewerApproverSection);
                    }
                } else {
                    reviewerApproverSection.addClass('d-none');
                    $("#reviewer-approver-guidance").remove();
                }

                checkFormValidity();
            } else {
                // Hide sections if no role selected
                userSelectionContainer.addClass('d-none');
                reviewerApproverSection.addClass('d-none');
                savePicBtn.addClass('d-none');

                // Remove any info messages
                $("#unit-kerja-info").remove();
                $("#role-description-info").remove();
                $("#reviewer-approver-guidance").remove();
            }
        });
        // Unit kerja selection
        unitKerjaSelect.on('select2:select', function(e) {
            const unitKerja = e.params.data.id;
            loadEmployees(unitKerja, selectedRole);
        });

        // Employee selection
        employeeSelect.on('change', function() {
            const userId = $(this).val();
            if (userId) {
                selectedUserId = userId;
                selectedUserName = $(this).find('option:selected').text();
                selectedUserUnit = $(this).find('option:selected').data('unit');
                fetchJabatan(userId, $("#jabatan-display"), $("#jabatan-input"));
                checkFormValidity();
            } else {
                selectedUserId = '';
                checkFormValidity();
            }
        });

        // Approver unit kerja selection
        approverUnitKerjaSelect.on('select2:select', function(e) {
            const unitKerja = e.params.data.id;
            loadApprovers(unitKerja);
        });

        // Approver selection
        approverSelect.on('change', function() {
            const userId = $(this).val();
            if (userId) {
                approverUserId = userId;
                approverUserName = $(this).find('option:selected').text();
                approverUserUnit = $(this).find('option:selected').data('unit');
                fetchJabatan(userId, $("#approver-jabatan-display"), $("#approver-jabatan-input"));
                checkFormValidity();
            } else {
                approverUserId = '';
                checkFormValidity();
            }
        });

        // Save PIC button
        savePicBtn.click(function() {
            if (!selectedRole || !selectedUserId) {
                alert('Please complete all required selections');
                return;
            }

            selectedJabatan = $("#jabatan-input").val();

            if (selectedRole === 'Reviewer-Maker') {
                if (!approverUserId) {
                    alert('Please select a reviewer-approver');
                    return;
                }

                // Add reviewer-maker
                const makerIndex = picIndex;

                addPicEntry({
                    role: selectedRole,
                    userId: selectedUserId,
                    userName: selectedUserName,
                    userUnit: selectedUserUnit,
                    jabatan: selectedJabatan
                });

                // Add reviewer-approver
                approverJabatan = $("#approver-jabatan-input").val();

                addPicEntry({
                    role: 'Reviewer-Approver',
                    userId: approverUserId,
                    userName: approverUserName,
                    userUnit: approverUserUnit,
                    jabatan: approverJabatan,
                    pairedWithMaker: true,
                    pairedWithIndex: makerIndex
                });
            } else {
                // Add regular PIC (Acknowledger or Unit Head)
                addPicEntry({
                    role: selectedRole,
                    userId: selectedUserId,
                    userName: selectedUserName,
                    userUnit: selectedUserUnit,
                    jabatan: selectedJabatan
                });
            }

            modal.modal('hide');
        });

        // Remove PIC button (event delegation)
        $(document).on('click', '.remove-pic', function() {
            const picEntry = $(this).closest('.pic-entry');
            const picRole = picEntry.data('role');

            if (picRole === 'Reviewer-Maker') {
                // If removing a maker, also remove the entire group including approver
                $(this).closest('.reviewer-group').remove();
            } else if (picRole === 'Reviewer-Approver') {
                // If removing an approver, also remove the maker
                $(this).closest('.reviewer-group').remove();
            } else {
                // Regular removal
                picEntry.remove();
            }

            // Revalidate workflow
            validateWorkflowWithBudget(totalBudget);
        });

        // Initialize UI state for empty/non-empty workflow
        function updateWorkflowDisplay() {
            // Check if there are any rows beyond the creator
            const hasAdditionalEntries = $("#pic-table-body tr.pic-entry").length > 1;

            if (hasAdditionalEntries) {
                $("#empty-workflow-message").addClass('d-none');
            } else {
                // Only show empty message if there's just the creator row
                $("#empty-workflow-message").removeClass('d-none');
            }

            // Table is always visible since we have at least the creator row
        }

        // Remove PIC button (event delegation)
        $(document).on('click', '.remove-pic', function() {
            const row = $(this).closest('tr.pic-entry');
            const picRole = row.data('role');

            if (picRole === 'Reviewer-Maker') {
                // If removing a maker, also remove any paired approver
                const relatedApprovers = $('tr.pic-entry[data-paired="true"]');
                relatedApprovers.remove();
            } else if (picRole === 'Reviewer-Approver' && row.data('paired')) {
                // If removing a paired approver, also remove the maker
                // Find makers in the table
                const relatedMakers = $('tr.pic-entry[data-role="Reviewer-Maker"]');
                relatedMakers.remove();
            }

            // Remove the clicked row
            row.remove();

            // Update display (show empty message if needed)
            updateWorkflowDisplay();

            // Revalidate workflow
            validateWorkflowWithBudget(totalBudget);
        });

        // Initialize the budget value for validation
        totalBudget = parseInt($('#total_nilai').val() || 0);

        // Run initial validation
        validateWorkflowWithBudget(totalBudget);

        // Initialize empty state
        updateWorkflowDisplay();
    }
</script>
