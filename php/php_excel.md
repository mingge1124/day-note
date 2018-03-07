## PHP_EXCEL 使用
> 包来源：[https://github.com/PHPOffice/PHPExcel](https://github.com/PHPOffice/PHPExcel)   
> 包简介：PHPExcel last version, 1.8.1, was released in 2015. The project is no longer maintained and should not be used anymore.
All users should migrate to its direct successor PhpSpreadsheet, or another alternative.In a few months, the GitHub project will be marked as archived, so everything will be read-only.     

Excel 基本了解
---
1. 每个 excel 文件含有多个 sheet (表)     
2. 每个 sheet 含有多个 cell (单元格)，含有 title (标题)、样式、...等属性
3. 每个 sheet 含有多个 columnDimension (单元格)		
4. 每个 cell 含有多个 column (列) 和 row （行），含有 value （值）、format (格式) 、样式 、...等属性				
6. Excel 2003及以下的版本。一张表最大支持65536行数据，256列。
7. Excel 2007-2010版本。一张表最大支持1048576行，16384列。


基本使用
---
实例化一个excel对象
```
$excel = new PHPExcel();
```

设置excel属性
```
$excel->getProperties()->setCreator("Maarten Balliauw")
					->setLastModifiedBy("Maarten Balliauw")
					->setTitle("PHPExcel Test Document")
					->setSubject("PHPExcel Test Document")
					->setDescription("Test document for PHPExcel, generated using PHP classes.")
					->setKeywords("office PHPExcel php")
					->setCategory("Test result file");
```

设置指定操作sheet，默认index从0开始
```
$excel->setActiveSheetIndex(0);
```


获取当前指向的sheet
```
$sheet = $excel->getActiveSheet();
```        

设置sheet属性
```
$sheet->setTitle('表标题');    
```

获取sheet数据数组
```
$sheet->toArray();
```

数组填充sheet
```
$sheet->fromArray($dataArray, NULL, 'A2');
```


获取行，列对象；rowNumber从1开始，columnNumber从A开始
```
$sheet->getRowDimension(rowNumber);
$sheet->getColumnDimension(columnNumber);
```


设置行高，列宽，自动宽度
```
$sheet->getRowDimension(1)->setRowHeight(10.00);
$sheet->getColumnDimension('A')->setWidth(10.00);	
$sheet->getColumnDimension('B')->setAutoSize(true);
```

获取单元格格式操作对象，参数为坐标
```
$cellStyle = $sheet->getStyle('A5');
```

利用数组配置样式
```
$styleArr = array(
	'font'    => array(
		'bold'      => true
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
	),
	'borders' => array(
		'top'     => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
	),
	'fill' => array(
			'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
			'rotation'   => 90,
			'startcolor' => array(
				'argb' => 'FFA0A0A0'
			),
			'endcolor'   => array(
				'argb' => 'FFFFFFFF'
			)
		)
);
$cellStyle->applyFromArray($styleArr);   //通过数组直接配置，映射关系待研究
```

设置单元格数字格式
```
$cellStyle->getNumerFormat()
		  ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
```		  

设置单元格对齐方式，水平向右，垂直居中，文字自动换行
```
$alignment = $cellStyle->getAlignment();
$alignment->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);  
$alignment->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
$alignment->setWrapText(true);
```

设置单元格字体  
```
$font = $cellStyle->getFont();  
$font->setName('Courier New');  
$font->setSize(10);  
$font->setBold(true);  
$font->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);  
$font->getColor()->setARGB('FF999999'); 
```

设置单元格边框  
```
$border = $cellStyle->getBorders();  
$border->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
$border->getTop()->getColor()->setARGB('FFFF0000'); // color  
$border->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
$border->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
$border->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
```


设置填充颜色  
```
$fill = $cellStyle->getFill();  
$fill->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
$fill->getStartColor()->setARGB('FFEEEEEE');  
```



常用场景
---
解析excel文件，并获取sheet内容
```
function getExcelArr() {
	$file = 'path/测试.xls';
	$file = iconv('utf-8', 'gbk', $file);    //中文处理
	if (!file_exists($file)) {
		return false;
	}

	$excel = \PHPExcel_IOFactory::load($file);
	$arr = $excel->getSheet(0)->toArray();

	return $arr;
}

```

导出excel
```
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");


$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Year')
                              ->setCellValue('B1', 'Quarter')
                              ->setCellValue('C1', 'Country')
                              ->setCellValue('D1', 'Sales');

$dataArray = array(array('2010',	'Q1',	'United States',	790),
                   array('2010',	'Q2',	'United States',	730),
                   array('2010',	'Q3',	'United States',	860),
                   array('2010',	'Q4',	'United States',	850),
                   array('2011',	'Q1',	'United States',	800),
                   array('2011',	'Q2',	'United States',	700),
                   array('2011',	'Q3',	'United States',	900),
                   array('2011',	'Q4',	'United States',	950),
                   array('2010',	'Q1',	'Belgium',			380),
                   array('2010',	'Q2',	'Belgium',			390),
                   array('2010',	'Q3',	'Belgium',			420),
                   array('2010',	'Q4',	'Belgium',			460),
                   array('2011',	'Q1',	'Belgium',			400),
                   array('2011',	'Q2',	'Belgium',			350),
                   array('2011',	'Q3',	'Belgium',			450),
                   array('2011',	'Q4',	'Belgium',			500),

                  );
$objPHPExcel->getActiveSheet()->fromArray($dataArray, NULL, 'A2');

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Simple');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="01simple.xls"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
```

拓展学习链接
---
[http://blog.csdn.net/tim_phper/article/details/77581071](http://blog.csdn.net/tim_phper/article/details/77581071)
[http://www.laruence.com/2008/10/31/574.html](http://www.laruence.com/2008/10/31/574.html)