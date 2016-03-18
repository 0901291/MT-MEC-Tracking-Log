<?php
require __DIR__.'/../../includes/PHPExcel.php';
require '../../includes/initialize.php';


// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$date = date("d/m/Y H:i:s");

// Set document properties
$objPHPExcel->getProperties()->setCreator("MT Tracklog Excel Export")
    ->setLastModifiedBy("MT Tracklog Excel Export")
    ->setTitle("MT Tracklog Excel Export on ".$date)
    ->setSubject("MT Tracklog Excel Export")
    ->setDescription("MT Tracklog Excel Export on ".$date);

$headers = [
    'id',
    'title',
    'date',
    'description',
    'imgURL',
    'lat',
    'lng',
    'dataTypes',
    'companies',
    'category',
    'state'
];

$alphabet = range('A', 'Z');
$i = 0;
foreach ($headers as $key => $value) {
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alphabet[$i].'1', $value);
    $i++;
}
$data = [];
$json = json_decode(file_get_contents(ROOT.'/api/v1/entry/?api_key='.$_GET['api_key']), true)['items'];
$categories = [];
$totalItems = 0;

foreach ($json as $row) {
    $data[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'date' => $row['date'],
        'description' => $row['description'],
        'imgURL' => $row['imgURL'],
        'lat' => $row['location']['lat'],
        'lng' => $row['location']['lng'],
        'dataTypes' => implode(',', $row['dataTypes']),
        'companies' => implode(',', $row['companies']),
        'category' => $row['category']['name'],
        'state' => $row['state']
    ];
    $category = $row['category']['name'];
    if ($category == "") $category = '-';
    if (key_exists($category, $categories)) {
        $categories[$category]++;
    } else {
        $categories[$category] = 1;
    }
    $totalItems++;
}

$r = 2;
foreach ($data as $row) {
    $c = 0;
    foreach ($row as $value) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($alphabet[$c].$r, $value);
        $c++;
    }
    $r++;
}

$r += 2;

$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$r, 'Category:');
$r++;
foreach ($categories as $category => $count) {
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$r, $category.':')
                                        ->setCellValue('B'.$r, $count);
    $r++;
}

$r++;
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$r, 'Total:')
                                    ->setCellValue('B'.$r, $totalItems);


// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Tracklog Export');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="mttracklog_export_'.$date.'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
