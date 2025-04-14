@extends('main.app')

@section('page-breadcrumb')
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Justification Form</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item text-muted active" aria-current="page">Justification Form</li>
                        <li class="breadcrumb-item text-muted" aria-current="page">Create</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    @include('workflows.create.components.styles')
@endpush

@section('page-wrapper')
    @include('main.components.message')

    <div class="card border-primary">
        <div class="card-body">
            <h3 class="card-title">Justification Form</h3>
            <hr>

            <form action="{{ route('workflows.store') }}" method="post" enctype="multipart/form-data" id="workflow-form">
                @csrf

                <div class="row">
                    <!-- Form Section 1 -->
                    <div class="col-md-6 col-12">
                        @include('workflows.create.components.form-basic-info')
                    </div>

                    <!-- Document Upload Section -->
                    <div class="col-12">
                        <div class="form-group">
                            <label>Documents (PDF)</label>
                            @include('workflows.create.components.document-upload-component')
                        </div>
                    </div>

                    <hr>


                    <div class="col-12">
                        <!-- Approval PIC Section -->
                        <h5>Approval PICs</h5>
                        <p class="text-muted mb-3">Set up the approval workflow for this justification form</p>

                        <div class="alert alert-info" id="budget-info-alert">
                            <i class="fas fa-info-circle mr-2"></i>
                            Based on the budget amount, specific approval flow rules will apply.
                        </div>
                    </div>

                    <div class="col-12">
                        @include('workflows.create.components.workflow-container')
                    </div>



                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Submit Workflow</button>
                        <button type="button" id="save-draft-btn" class="btn btn-secondary ml-2">Save as Draft</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- PIC Modal -->
    @include('workflows.create.components.pic-modal')
@endsection

@section('app-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <!-- Initialize core components -->
    <script>
        $(document).ready(function() {
            // Initialize Select2 for the account select
            $('#account').select2();

            // Initialize currency formatter
            initCurrencyFormatter();

            // Initialize workflow components
            initWorkflowComponents();

            // Handle "Save as Draft" button
            $("#save-draft-btn").click(function() {
                // Add a hidden field to indicate this is a draft
                $('<input>').attr({
                    type: 'hidden',
                    name: 'is_draft',
                    value: '1'
                }).appendTo('#workflow-form');

                // Submit the form
                $('#workflow-form').submit();
            });
        });
    </script>

    <!-- Include component scripts -->
    @include('workflows.create.scripts.currency-formatter')
    @include('workflows.create.scripts.workflow-components')
    @include('workflows.create.scripts.document-handling')
@endsection
