<?php

return [
    // Button Labels
    'buttons' => [
        'submit' => 'إرسال',
        'cancel' => 'إلغاء',
        'confirm' => 'تأكيد',
        'close' => 'إغلاق',
        'save' => 'حفظ',
        'create' => 'إنشاء',
        'update' => 'تحديث',
        'delete' => 'حذف',
        'edit' => 'تعديل',
        'view' => 'عرض',
        'force_delete' => 'حذف نهائي',
        'restore' => 'استعادة',
        'export' => 'تصدير',
        'import' => 'استيراد',
        'replicate' => 'تكرار',
    ],

    // Export Action
    'export' => [
        'label' => 'تصدير',
        'modal' => [
            'heading' => 'تصدير البيانات',
            'description' => 'اختر التنسيق والخيارات للتصدير.',
            'submit' => 'تصدير',
        ],
        'messages' => [
            'success' => 'تم التصدير بنجاح.',
            'failed' => 'فشل التصدير. يرجى المحاولة مرة أخرى.',
        ],
    ],

    // Import Action
    'import' => [
        'label' => 'استيراد',
        'modal' => [
            'heading' => 'استيراد البيانات',
            'description' => 'قم برفع ملف لاستيراد البيانات. التنسيقات المدعومة: XLSX، CSV.',
            'submit' => 'استيراد',
        ],
        'fields' => [
            'file' => 'الملف',
        ],
        'messages' => [
            'success' => 'تم الاستيراد بنجاح.',
            'failed' => 'فشل الاستيراد. يرجى التحقق من الملف والمحاولة مرة أخرى.',
            'validation_errors' => 'تعذر استيراد بعض الصفوف بسبب أخطاء التحقق.',
        ],
    ],

    // Replicate Action
    'replicate' => [
        'label' => 'تكرار',
        'modal' => [
            'heading' => 'تكرار السجل',
            'description' => 'هل أنت متأكد أنك تريد إنشاء نسخة من هذا السجل؟',
            'submit' => 'تكرار',
        ],
        'messages' => [
            'success' => 'تم تكرار السجل بنجاح.',
            'failed' => 'فشل تكرار السجل. يرجى المحاولة مرة أخرى.',
        ],
    ],

    // Modal
    'modal' => [
        'confirm_title' => 'تأكيد الإجراء',
        'confirm_description' => 'هل أنت متأكد أنك تريد تنفيذ هذا الإجراء؟',
        'confirm_action' => 'تأكيد :action',
        'confirm_action_description' => 'هل أنت متأكد أنك تريد :action؟',
        'this_action' => 'تنفيذ هذا الإجراء',
        'delete_title' => 'تأكيد الحذف',
        'delete_description' => 'هل أنت متأكد أنك تريد حذف هذا العنصر؟ لا يمكن التراجع عن هذا الإجراء.',
        'force_delete_title' => 'حذف نهائي',
        'force_delete_description' => 'هل أنت متأكد أنك تريد حذف هذا السجل نهائياً؟ لا يمكن التراجع عن هذا الإجراء.',
        'restore_title' => 'استعادة السجل',
        'restore_description' => 'هل أنت متأكد أنك تريد استعادة هذا السجل؟',
        'bulk_force_delete_title' => 'حذف المحدد نهائياً',
        'bulk_force_delete_description' => 'هل أنت متأكد أنك تريد حذف السجلات المحددة نهائياً؟ لا يمكن التراجع عن هذا الإجراء.',
        'bulk_restore_title' => 'استعادة المحدد',
        'bulk_restore_description' => 'هل أنت متأكد أنك تريد استعادة السجلات المحددة؟',
    ],

    // States
    'states' => [
        'loading' => 'جاري التحميل...',
        'processing' => 'جاري المعالجة...',
        'success' => 'نجاح!',
        'error' => 'خطأ!',
    ],

    // Confirmation descriptions
    'confirm_delete_description' => 'هل أنت متأكد أنك تريد حذف هذا العنصر؟ لا يمكن التراجع عن هذا الإجراء.',
    'confirm_bulk_delete_description' => 'هل أنت متأكد أنك تريد حذف العناصر المحددة؟ لا يمكن التراجع عن هذا الإجراء.',

    // Messages
    'messages' => [
        'action_completed' => 'تم تنفيذ الإجراء بنجاح',
        'action_failed' => 'فشل الإجراء. يرجى المحاولة مرة أخرى.',
        'unauthorized' => 'غير مصرح لك بتنفيذ هذا الإجراء.',
        'created' => 'تم إنشاء السجل بنجاح',
        'updated' => 'تم تحديث السجل بنجاح',
        'deleted' => 'تم حذف السجل بنجاح',
        'bulk_deleted' => 'تم حذف السجلات بنجاح',
        'force_deleted' => 'تم حذف السجل نهائياً',
        'restored' => 'تم استعادة السجل بنجاح',
        'bulk_force_deleted' => 'تم حذف :count سجل نهائياً',
        'bulk_restored' => 'تم استعادة :count سجل',
        'no_records_selected' => 'لم يتم تحديد أي سجلات',
        'saved' => 'تم حفظ التغييرات بنجاح',
    ],

    // Tooltips
    'tooltips' => [
        'edit' => 'تعديل هذا العنصر',
        'delete' => 'حذف هذا العنصر',
        'view' => 'عرض التفاصيل',
        'download' => 'تنزيل',
        'copy' => 'نسخ إلى الحافظة',
        'force_delete' => 'حذف هذا العنصر نهائياً',
        'restore' => 'استعادة هذا العنصر',
    ],
];
