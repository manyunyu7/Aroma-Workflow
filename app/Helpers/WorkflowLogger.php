<?php

namespace App\Helpers;

use App\Models\Workflow;
use App\Models\WorkflowLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class WorkflowLogger
{
    /**
     * Log a workflow action
     *
     * @param Workflow $workflow The workflow being acted upon
     * @param string $action The action being performed (use WorkflowLog::getActions() constants)
     * @param string|null $statusBefore Previous status of the workflow
     * @param string|null $statusAfter New status of the workflow
     * @param string|null $role Role of the user performing the action
     * @param string|null $notes Additional notes about the action
     * @param array|null $metadata Any additional metadata
     * @return WorkflowLog|null The created log entry, or null if creation failed
     */
    public static function log(
        Workflow $workflow,
        string $action,
        ?string $statusBefore = null,
        ?string $statusAfter = null,
        ?string $role = null,
        ?string $notes = null,
        ?array $metadata = null
    ): ?WorkflowLog {
        try {
            // Get the current authenticated user
            $userId = Auth::id();

            // Create the log entry
            $log = WorkflowLog::create([
                'workflow_id' => $workflow->id,
                'user_id' => $userId,
                'action' => $action,
                'status_before' => $statusBefore ?? $workflow->getOriginal('status') ?? null,
                'status_after' => $statusAfter ?? $workflow->status ?? null,
                'role' => $role,
                'notes' => $notes,
                'metadata' => $metadata,
                'ip_address' => Request::ip(),
            ]);

            // Also log to Laravel's logging system for server logs
            $logMessage = "Workflow #{$workflow->id} ({$workflow->nomor_pengajuan}): {$action}";
            if ($statusBefore && $statusAfter) {
                $logMessage .= " - Status changed from {$statusBefore} to {$statusAfter}";
            }

            if ($userId) {
                $logMessage .= " by User #{$userId}";
            }

            if ($notes) {
                $logMessage .= " - Notes: {$notes}";
            }

            Log::info($logMessage, [
                'workflow_id' => $workflow->id,
                'user_id' => $userId,
                'metadata' => $metadata,
            ]);

            return $log;
        } catch (\Exception $e) {
            // Log the error
            Log::error("Failed to create workflow log: " . $e->getMessage(), [
                'workflow_id' => $workflow->id,
                'action' => $action,
                'exception' => $e,
            ]);

            return null;
        }
    }

    /**
     * Log a view action when a workflow is accessed
     *
     * @param Workflow $workflow The workflow being viewed
     * @return WorkflowLog|null
     */
    public static function logView(Workflow $workflow): ?WorkflowLog
    {
        return self::log(
            $workflow,
            'VIEW',
            $workflow->status,
            $workflow->status,
            Auth::user()->userRoles->first()->role ?? null,
            'Workflow was viewed'
        );
    }

    /**
     * Log a workflow creation
     *
     * @param Workflow $workflow The created workflow
     * @param bool $isDraft Whether the workflow was saved as a draft
     * @return WorkflowLog|null
     */
    public static function logCreate(Workflow $workflow, bool $isDraft = false): ?WorkflowLog
    {
        $action = $isDraft ? 'SAVE_DRAFT' : 'CREATE';
        $notes = $isDraft ? 'Workflow saved as draft' : 'Workflow created';

        return self::log(
            $workflow,
            $action,
            null,
            $workflow->status,
            'CREATOR',
            $notes,
            [
                'nomor_pengajuan' => $workflow->nomor_pengajuan,
                'total_nilai' => $workflow->total_nilai,
                'jenis_anggaran' => $workflow->jenis_anggaran,
            ]
        );
    }

    /**
     * Log a workflow update
     *
     * @param Workflow $workflow The updated workflow
     * @param array $changedFields Array of fields that were changed
     * @return WorkflowLog|null
     */
    public static function logUpdate(Workflow $workflow, array $changedFields = []): ?WorkflowLog
    {
        return self::log(
            $workflow,
            'UPDATE',
            $workflow->getOriginal('status'),
            $workflow->status,
            Auth::user()->userRoles->first()->role ?? null,
            'Workflow updated',
            [
                'changed_fields' => $changedFields,
            ]
        );
    }

    /**
     * Log a workflow submission
     *
     * @param Workflow $workflow The submitted workflow
     * @return WorkflowLog|null
     */
    public static function logSubmit(Workflow $workflow): ?WorkflowLog
    {
        return self::log(
            $workflow,
            'SUBMIT',
            'DRAFT_CREATOR',
            'WAITING_APPROVAL',
            'CREATOR',
            'Workflow submitted for approval',
            [
                'nomor_pengajuan' => $workflow->nomor_pengajuan,
                'total_nilai' => $workflow->total_nilai,
            ]
        );
    }

    /**
     * Log a workflow approval
     *
     * @param Workflow $workflow The approved workflow
     * @param string $role Role of the approver
     * @param string $notes Approval notes
     * @return WorkflowLog|null
     */
    public static function logApprove(Workflow $workflow, string $role, string $notes = null): ?WorkflowLog
    {
        $statusAfter = $workflow->status; // This might be WAITING_APPROVAL or COMPLETED

        return self::log(
            $workflow,
            'APPROVE',
            'WAITING_APPROVAL',
            $statusAfter,
            $role,
            $notes ?? 'Workflow approved',
            [
                'approval_time' => now()->toDateTimeString(),
            ]
        );
    }

    /**
     * Log a workflow rejection
     *
     * @param Workflow $workflow The rejected workflow
     * @param string $role Role of the rejector
     * @param string $notes Rejection reason
     * @return WorkflowLog|null
     */
    public static function logReject(Workflow $workflow, string $role, string $notes): ?WorkflowLog
    {
        return self::log(
            $workflow,
            'REJECT',
            'WAITING_APPROVAL',
            'DRAFT_CREATOR',
            $role,
            $notes,
            [
                'rejection_time' => now()->toDateTimeString(),
            ]
        );
    }

    /**
     * Log document addition to a workflow
     *
     * @param Workflow $workflow The workflow
     * @param int $documentId ID of the added document
     * @param string $documentName Name of the added document
     * @return WorkflowLog|null
     */
    public static function logAddDocument(Workflow $workflow, int $documentId, string $documentName): ?WorkflowLog
    {
        return self::log(
            $workflow,
            'ADD_DOCUMENT',
            $workflow->status,
            $workflow->status,
            Auth::user()->userRoles->first()->role ?? null,
            "Document added: {$documentName}",
            [
                'document_id' => $documentId,
                'document_name' => $documentName,
            ]
        );
    }

    /**
     * Log document removal from a workflow
     *
     * @param Workflow $workflow The workflow
     * @param int $documentId ID of the removed document
     * @param string $documentName Name of the removed document
     * @return WorkflowLog|null
     */
    public static function logRemoveDocument(Workflow $workflow, int $documentId, string $documentName): ?WorkflowLog
    {
        return self::log(
            $workflow,
            'REMOVE_DOCUMENT',
            $workflow->status,
            $workflow->status,
            Auth::user()->userRoles->first()->role ?? null,
            "Document removed: {$documentName}",
            [
                'document_id' => $documentId,
                'document_name' => $documentName,
            ]
        );
    }

    /**
     * Log approver assignment
     *
     * @param Workflow $workflow The workflow
     * @param int $approverId ID of the assigned approver
     * @param string $approverName Name of the assigned approver
     * @param string $role Role assigned to the approver
     * @return WorkflowLog|null
     */
    public static function logAssignApprover(Workflow $workflow, int $approverId, string $approverName, string $role): ?WorkflowLog
    {
        return self::log(
            $workflow,
            'ASSIGN_APPROVER',
            $workflow->status,
            $workflow->status,
            Auth::user()->userRoles->first()->role ?? null,
            "Approver assigned: {$approverName} as {$role}",
            [
                'approver_id' => $approverId,
                'approver_name' => $approverName,
                'role' => $role,
            ]
        );
    }

    /**
     * Log a system action (not initiated by a user)
     *
     * @param Workflow $workflow The workflow
     * @param string $action Action performed by the system
     * @param string|null $notes Additional notes
     * @param array|null $metadata Additional metadata
     * @return WorkflowLog|null
     */
    public static function logSystemAction(Workflow $workflow, string $action, ?string $notes = null, ?array $metadata = null): ?WorkflowLog
    {
        return self::log(
            $workflow,
            $action,
            $workflow->getOriginal('status') ?? $workflow->status,
            $workflow->status,
            'SYSTEM',
            $notes ?? 'System action',
            $metadata
        );
    }
}
