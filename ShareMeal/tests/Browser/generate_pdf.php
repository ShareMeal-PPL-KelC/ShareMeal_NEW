<?php
$pdfContent = "%PDF-1.4\n%âãÏÓ\n1 0 obj\n<< /Type /Catalog >>\nendobj\n";
file_put_contents(__DIR__ . '/dummy.pdf', $pdfContent);
echo "Done";
