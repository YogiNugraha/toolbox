<?php

return [
    [
        'slug' => 'compress-image',
        'name' => 'Compress Gambar',
        'description' => 'Kompres ukuran gambar tanpa mengurangi kualitas visual secara signifikan.',
        'icon' => 'photo',
        'category' => 'Image',
        'component' => 'tools.image-compressor',
        'lockable_features' => [
            'preset_custom' => 'Kunci Preset Custom (Khusus Pro)',
        ],
    ],
    [
        'slug' => 'convert-image',
        'name' => 'Convert Format Gambar',
        'description' => 'Ubah JPG ke PNG, PNG ke WebP, dan sebaliknya.',
        'icon' => 'arrows-right-left',
        'category' => 'Image',
        'component' => 'tools.image-converter',
        'lockable_features' => [],
    ],
    [
        'slug' => 'pdf-to-word',
        'name' => 'PDF ke Word',
        'description' => 'Konversi file PDF menjadi dokumen Word (.docx) yang bisa diedit.',
        'icon' => 'document-text',
        'category' => 'Document',
        'component' => 'tools.pdf-to-word',
        'lockable_features' => [],
    ],
    [
        'slug' => 'compress-pdf',
        'name' => 'Compress PDF',
        'description' => 'Perkecil ukuran dokumen PDF dengan cepat tanpa merusak tata letak teks dan kualitas bacaan.',
        'icon' => 'document-arrow-down',
        'category' => 'Document',
        'component' => 'tools.pdf-compressor',
        'lockable_features' => [
            'preset_custom' => 'Kunci Preset Custom (Khusus Pro)',
        ],
    ],
];
