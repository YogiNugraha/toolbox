<?php

return [
    [
        'slug' => 'compress-image',
        'name' => 'Compress Gambar',
        'description' => 'Kompres ukuran gambar tanpa mengurangi kualitas visual secara signifikan.',
        'icon' => 'photo',
        'category' => 'Image',
        'component' => 'tools.image-compressor',
    ],
    [
        'slug' => 'convert-image',
        'name' => 'Convert Format Gambar',
        'description' => 'Ubah JPG ke PNG, PNG ke WebP, dan sebaliknya.',
        'icon' => 'arrows-right-left',
        'category' => 'Image',
        'component' => 'tools.image-converter',
    ],
    [
        'slug' => 'pdf-to-word',
        'name' => 'PDF ke Word',
        'description' => 'Konversi file PDF menjadi dokumen Word (.docx) yang bisa diedit.',
        'icon' => 'document-text',
        'category' => 'Document',
        'component' => 'tools.pdf-to-word',
    ],
];
