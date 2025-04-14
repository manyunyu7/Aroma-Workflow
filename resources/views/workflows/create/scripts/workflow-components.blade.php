<script>
    function initWorkflowComponents() {
        // Global variables
        const picContainer = $("#pic-container");
        const addPicBtn = $("#add-pic-btn");
        const modal = $("#pic-modal");
        const roleSelect = $("#role-select");
        const unitKerjaSelect = $("#unit-kerja-select");
        const employeeSelect = $("#employee-select");
        const approverUnitKerjaSelect = $("#approver-unit-kerja-select");
        const approverSelect = $("#approver-select");
        const stepNextBtn = $("#step-next-btn");
        const stepBackBtn = $("#step-back-btn");
        const savePicBtn = $("#save-pic-btn");

        let picIndex = {{ count(old('pics', [1])) }}; // Start with the next index after existing PICs
        let currentStep = 1;
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
        window.checkBudgetChanges = function(budget) {
            totalBudget = budget;

            let budgetInfoHtml = '<i class="fas fa-info-circle mr-2"></i>';
            if (budget < 500000000) {
                budgetInfoHtml +=
                    'Budget under 500,000,000 IDR: Acknowledger and Unit Head must be from the same unit.';
            } else {
                budgetInfoHtml += 'Budget is 500,000,000 IDR or higher: Standard approval rules apply.';
            }

            $("#budget-info-alert").html(budgetInfoHtml);

            // If current workflow violates the new budget rules, show a warning
            validateWorkflowWithBudget(budget);
        };

        // Validate workflow based on budget rules
        function validateWorkflowWithBudget(budget) {
            if (budget < 500000000) {
                // Check if acknowledger and unit head are from the same unit
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
                        if (!$('#unit-mismatch-warning').length) {
                            const warningHtml = `
                                <div id="unit-mismatch-warning" class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Warning:</strong> For budgets under 500,000,000 IDR, the Acknowledger and Unit Head must be from the same unit.
                                    Please update your approvers.
                                </div>
                            `;
                            $('#pic-container').before(warningHtml);
                        }
                    } else {
                        // Remove warning if exists
                        $('#unit-mismatch-warning').remove();
                    }
                }
            } else {
                // For higher budgets, remove warning if exists
                $('#unit-mismatch-warning').remove();
            }
        }

        // Load available roles based on current workflow
        function loadAvailableRoles() {
            const roles = getCurrentRoles();
            const budget = $('#total_nilai').val() || 0;

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

                    data.forEach(function(role) {
                        roleSelect.append(`<option value="${role}">${role}</option>`);
                    });

                    // If no roles available, show message
                    if (data.length === 0) {
                        roleSelect.append(
                            '<option value="" disabled>No roles available for current workflow</option>'
                        );
                        stepNextBtn.prop('disabled', true);
                    } else {
                        stepNextBtn.prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    console.error('Error loading available roles:', xhr.responseText);
                    roleSelect.empty();
                    roleSelect.append('<option value="">Error loading roles</option>');
                    stepNextBtn.prop('disabled', true);
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

        // Show step in the modal
        function showStep(stepNumber) {
            $('.step-container').addClass('d-none');
            $(`#step-${stepNumber}`).removeClass('d-none');

            // Update buttons based on step
            if (stepNumber === 1) {
                stepBackBtn.addClass('d-none');
                stepNextBtn.removeClass('d-none');
                savePicBtn.addClass('d-none');
            } else if (stepNumber === 2) {
                stepBackBtn.removeClass('d-none');

                if (selectedRole === 'Reviewer-Maker') {
                    stepNextBtn.removeClass('d-none');
                    savePicBtn.addClass('d-none');
                } else {
                    stepNextBtn.addClass('d-none');
                    savePicBtn.removeClass('d-none');
                }
            } else if (stepNumber === 3) {
                stepBackBtn.removeClass('d-none');
                stepNextBtn.addClass('d-none');
                savePicBtn.removeClass('d-none');
            }

            currentStep = stepNumber;
        }

        // Add PIC entry to the workflow
        function addPicEntry(picData) {
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

            // Determine where to add the PIC entry
            if (picData.role === 'Reviewer-Approver' && picData.pairedWithMaker) {
                // Add inside the group with the maker
                const groupId = `reviewer-group-${picData.pairedWithIndex}`;

                const approverHtml = `
                    <div class="pic-entry mt-2" data-role="${picData.role}" data-user-id="${picData.userId}">
                        <span class="role-badge ${cssClass}">${picData.role}</span>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <strong>${picData.userName}</strong>
                                <input type="hidden" name="pics[${picIndex}][user_id]" value="${picData.userId}">
                                <input type="hidden" name="pics[${picIndex}][role]" value="${picData.role}">
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">${picData.userUnit}</small>
                                <input type="hidden" name="pics[${picIndex}][jabatan]" value="${picData.jabatan}">
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="pics[${picIndex}][digital_signature]" value="1">
                                    <label class="form-check-label">Use Digital Signature</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-pic">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <textarea name="pics[${picIndex}][notes]" class="form-control" placeholder="Notes (optional)"></textarea>
                            </div>
                        </div>
                    </div>
                `;

                $(`#${groupId}`).append(approverHtml);
            } else if (picData.role === 'Reviewer-Maker') {
                // Create a new reviewer group
                const groupHtml = `
                    <div class="reviewer-group" id="reviewer-group-${picIndex}">
                        <div class="reviewer-header">
                            <h6 class="mb-0">Reviewer Group</h6>
                            <span class="reviewer-badge">Maker + Approver</span>
                        </div>

                        <div class="pic-entry" data-role="${picData.role}" data-user-id="${picData.userId}">
                            <span class="role-badge ${cssClass}">${picData.role}</span>
                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <strong>${picData.userName}</strong>
                                    <input type="hidden" name="pics[${picIndex}][user_id]" value="${picData.userId}">
                                    <input type="hidden" name="pics[${picIndex}][role]" value="${picData.role}">
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">${picData.userUnit}</small>
                                    <input type="hidden" name="pics[${picIndex}][jabatan]" value="${picData.jabatan}">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="pics[${picIndex}][digital_signature]" value="1">
                                        <label class="form-check-label">Use Digital Signature</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-pic">
                                        <i class="fas fa-times"></i> Remove
                                    </button>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <textarea name="pics[${picIndex}][notes]" class="form-control" placeholder="Notes (optional)"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                picContainer.append(groupHtml);
            } else {
                // Regular entry (Acknowledger or Unit Head)
                const entryHtml = `
                    <div class="pic-entry" data-role="${picData.role}" data-user-id="${picData.userId}">
                        <span class="role-badge ${cssClass}">${picData.role}</span>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <strong>${picData.userName}</strong>
                                <input type="hidden" name="pics[${picIndex}][user_id]" value="${picData.userId}">
                                <input type="hidden" name="pics[${picIndex}][role]" value="${picData.role}">
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">${picData.userUnit}</small>
                                <input type="hidden" name="pics[${picIndex}][jabatan]" value="${picData.jabatan}">
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="pics[${picIndex}][digital_signature]" value="1">
                                    <label class="form-check-label">Use Digital Signature</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-pic">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <textarea name="pics[${picIndex}][notes]" class="form-control" placeholder="Notes (optional)"></textarea>
                            </div>
                        </div>
                    </div>
                `;

                picContainer.append(entryHtml);
            }

            picIndex++;

            // Validate workflow after adding
            validateWorkflowWithBudget(totalBudget);
        }

        // Event Handlers

        // Add PIC button
        addPicBtn.click(function() {
            // Reset modal
            unitKerjaSelect.val(null).trigger('change');
            employeeSelect.empty().prop('disabled', true);
            approverUnitKerjaSelect.val(null).trigger('change');
            approverSelect.empty().prop('disabled', true);
            $("#jabatan-display").text("N/A");
            $("#approver-jabatan-display").text("N/A");
            $("#jabatan-input").val("");
            $("#approver-jabatan-input").val("");

            // Load available roles
            loadAvailableRoles();

            // Reset step
            showStep(1);

            // Show modal
            modal.modal('show');
        });

        // Role selection
        roleSelect.change(function() {
            selectedRole = $(this).val();

            // Show/hide reviewer-approver section based on role
            if (selectedRole === 'Reviewer-Maker') {
                $("#reviewer-approver-section").removeClass('d-none');
            } else {
                $("#reviewer-approver-section").addClass('d-none');
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
            }
        });

        // Next step button
        stepNextBtn.click(function() {
            if (currentStep === 1) {
                if (!selectedRole) {
                    alert('Please select a role first');
                    return;
                }
                showStep(2);
            } else if (currentStep === 2) {
                if (!selectedUserId) {
                    alert('Please select an employee first');
                    return;
                }
                selectedJabatan = $("#jabatan-input").val();
                showStep(3);
            }
        });

        // Back button
        stepBackBtn.click(function() {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        });

        // Save PIC button
        savePicBtn.click(function() {
            if (currentStep === 2) {
                if (!selectedUserId) {
                    alert('Please select an employee first');
                    return;
                }

                // Add regular PIC (Acknowledger or Unit Head)
                selectedJabatan = $("#jabatan-input").val();

                addPicEntry({
                    role: selectedRole,
                    userId: selectedUserId,
                    userName: selectedUserName,
                    userUnit: selectedUserUnit,
                    jabatan: selectedJabatan
                });

                modal.modal('hide');
            } else if (currentStep === 3) {
                if (!approverUserId) {
                    alert('Please select a reviewer-approver first');
                    return;
                }

                // Add reviewer-maker
                selectedJabatan = $("#jabatan-input").val();
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

                modal.modal('hide');
            }
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

        // Initialize the budget value for validation
        totalBudget = parseInt($('#total_nilai').val() || 0);

        // Run initial validation
        validateWorkflowWithBudget(totalBudget);
    }

</script>
