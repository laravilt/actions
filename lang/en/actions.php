<?php

return [
    // Button Labels
    'buttons' => [
        'submit' => 'Submit',
        'cancel' => 'Cancel',
        'confirm' => 'Confirm',
        'close' => 'Close',
        'save' => 'Save',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
        'edit' => 'Edit',
        'view' => 'View',
        'force_delete' => 'Force Delete',
        'restore' => 'Restore',
        'export' => 'Export',
        'import' => 'Import',
        'replicate' => 'Replicate',
    ],

    // Export Action
    'export' => [
        'label' => 'Export',
        'modal' => [
            'heading' => 'Export Data',
            'description' => 'Select the format and options for your export.',
            'submit' => 'Export',
        ],
        'messages' => [
            'success' => 'Export completed successfully.',
            'failed' => 'Export failed. Please try again.',
        ],
    ],

    // Import Action
    'import' => [
        'label' => 'Import',
        'modal' => [
            'heading' => 'Import Data',
            'description' => 'Upload a file to import data. Supported formats: XLSX, CSV.',
            'submit' => 'Import',
        ],
        'fields' => [
            'file' => 'File',
        ],
        'messages' => [
            'success' => 'Import completed successfully.',
            'failed' => 'Import failed. Please check your file and try again.',
            'validation_errors' => 'Some rows could not be imported due to validation errors.',
        ],
    ],

    // Replicate Action
    'replicate' => [
        'label' => 'Replicate',
        'modal' => [
            'heading' => 'Replicate Record',
            'description' => 'Are you sure you want to create a copy of this record?',
            'submit' => 'Replicate',
        ],
        'messages' => [
            'success' => 'Record replicated successfully.',
            'failed' => 'Failed to replicate record. Please try again.',
        ],
    ],

    // Modal
    'modal' => [
        'confirm_title' => 'Confirm Action',
        'confirm_description' => 'Are you sure you want to perform this action?',
        'confirm_action' => 'Confirm :action',
        'confirm_action_description' => 'Are you sure you want to :action?',
        'this_action' => 'perform this action',
        'delete_title' => 'Delete Confirmation',
        'delete_description' => 'Are you sure you want to delete this item? This action cannot be undone.',
        'force_delete_title' => 'Permanently Delete',
        'force_delete_description' => 'Are you sure you want to permanently delete this record? This action cannot be undone.',
        'restore_title' => 'Restore Record',
        'restore_description' => 'Are you sure you want to restore this record?',
        'bulk_force_delete_title' => 'Permanently Delete Selected',
        'bulk_force_delete_description' => 'Are you sure you want to permanently delete the selected records? This action cannot be undone.',
        'bulk_restore_title' => 'Restore Selected',
        'bulk_restore_description' => 'Are you sure you want to restore the selected records?',
    ],

    // States
    'states' => [
        'loading' => 'Loading...',
        'processing' => 'Processing...',
        'success' => 'Success!',
        'error' => 'Error!',
    ],

    // Confirmation descriptions
    'confirm_delete_description' => 'Are you sure you want to delete this item? This action cannot be undone.',
    'confirm_bulk_delete_description' => 'Are you sure you want to delete the selected items? This action cannot be undone.',

    // Messages
    'messages' => [
        'action_completed' => 'Action completed successfully',
        'action_failed' => 'Action failed. Please try again.',
        'unauthorized' => 'You are not authorized to perform this action.',
        'created' => 'Record created successfully',
        'updated' => 'Record updated successfully',
        'deleted' => 'Record deleted successfully',
        'bulk_deleted' => 'Records deleted successfully',
        'force_deleted' => 'Record permanently deleted',
        'restored' => 'Record restored successfully',
        'bulk_force_deleted' => ':count records permanently deleted',
        'bulk_restored' => ':count records restored',
        'no_records_selected' => 'No records selected',
        'saved' => 'Changes saved successfully',
    ],

    // Tooltips
    'tooltips' => [
        'edit' => 'Edit this item',
        'delete' => 'Delete this item',
        'view' => 'View details',
        'download' => 'Download',
        'copy' => 'Copy to clipboard',
        'force_delete' => 'Permanently delete this item',
        'restore' => 'Restore this item',
    ],
];
